"""Celery tasks for background email sending."""

import asyncio
from datetime import datetime, timezone

from celery import Celery

from app.config import get_settings

settings = get_settings()

# Create Celery app
celery_app = Celery(
    "uzrj",
    broker=settings.REDIS_URL,
    backend=settings.REDIS_URL,
)

# Celery configuration
celery_app.conf.update(
    task_serializer="json",
    accept_content=["json"],
    result_serializer="json",
    timezone="Europe/Belgrade",
    enable_utc=True,
    task_track_started=True,
    task_time_limit=300,  # 5 minutes
    task_soft_time_limit=240,  # 4 minutes
    worker_prefetch_multiplier=1,
    worker_max_tasks_per_child=100,
)


@celery_app.task(bind=True, max_retries=3, default_retry_delay=60)
def send_email_task(self, recipient: str, subject: str, body_html: str, body_text: str = None):
    """Background task for sending emails."""
    import aiosmtplib
    from email.mime.text import MIMEText
    from email.mime.multipart import MIMEMultipart

    async def _send():
        message = MIMEMultipart("alternative")
        message["From"] = f"{settings.SMTP_FROM_NAME} <{settings.SMTP_FROM_EMAIL}>"
        message["To"] = recipient
        message["Subject"] = subject

        if body_text:
            message.attach(MIMEText(body_text, "plain", "utf-8"))

        message.attach(MIMEText(body_html, "html", "utf-8"))

        await aiosmtplib.send(
            message,
            hostname=settings.SMTP_HOST,
            port=settings.SMTP_PORT,
            username=settings.SMTP_USER,
            password=settings.SMTP_PASSWORD,
            use_tls=settings.SMTP_PORT == 465,
            start_tls=settings.SMTP_PORT == 587,
        )

    try:
        asyncio.run(_send())
        return {"success": True, "recipient": recipient}
    except Exception as exc:
        # Retry on failure
        raise self.retry(exc=exc)


@celery_app.task(bind=True, max_retries=3, default_retry_delay=60)
def send_template_email_task(self, recipient: str, template_name: str, variables: dict):
    """Background task for sending template emails."""
    from app.database import async_session
    from app.services.email import EmailService

    async def _send():
        async with async_session() as db:
            email_service = EmailService(db)
            return await email_service.send_template_email(
                recipient=recipient,
                template_name=template_name,
                variables=variables,
            )

    try:
        result = asyncio.run(_send())
        return result
    except Exception as exc:
        raise self.retry(exc=exc)


@celery_app.task
def send_bulk_email_task(recipients: list, template_name: str, variables: dict):
    """Send email to multiple recipients."""
    results = []
    for recipient in recipients:
        try:
            result = send_template_email_task.delay(recipient, template_name, variables)
            results.append({"recipient": recipient, "task_id": result.id, "status": "queued"})
        except Exception as e:
            results.append({"recipient": recipient, "error": str(e)})
    return results


@celery_app.task
def send_clanarina_reminders_task():
    """Send reminders for upcoming clanarina deadlines."""
    from app.database import async_session
    from app.models import Member, Clanarina
    from sqlalchemy import select, and_
    from datetime import timedelta

    async def _send_reminders():
        async with async_session() as db:
            # Find clanarine due in next 7 days
            now = datetime.now(timezone.utc)
            week_later = now + timedelta(days=7)

            result = await db.execute(
                select(Clanarina, Member).join(
                    Member, Clanarina.member_id == Member.id
                ).where(
                    and_(
                        Clanarina.status == "neplaceno",
                        Clanarina.period_do <= week_later,
                        Clanarina.period_do >= now,
                        Member.email.isnot(None),
                    )
                )
            )
            clanarine = result.all()

            email_service = EmailService(db)
            sent_count = 0

            for clanarina, member in clanarine:
                await email_service.send_clanarina_reminder(
                    recipient=member.email,
                    ime=member.ime,
                    prezime=member.prezime,
                    iznos=float(clanarina.iznos),
                    period_do=clanarina.period_do,
                )
                sent_count += 1

            return {"sent": sent_count, "total": len(clanarine)}

    return asyncio.run(_send_reminders())


@celery_app.task
def send_edukacija_reminders_task():
    """Send reminders for edukacija happening tomorrow."""
    from app.database import async_session
    from app.models import Edukacija, PrijavaEdukacije, Member
    from sqlalchemy import select, and_
    from datetime import timedelta

    async def _send_reminders():
        async with async_session() as db:
            # Find edukacije happening tomorrow
            now = datetime.now(timezone.utc)
            tomorrow = now + timedelta(days=1)
            day_after = now + timedelta(days=2)

            result = await db.execute(
                select(Edukacija).where(
                    and_(
                        Edukacija.datum_pocetka >= tomorrow,
                        Edukacija.datum_pocetka < day_after,
                        Edukacija.status != "otkazano",
                    )
                )
            )
            edukacije = result.scalars().all()

            email_service = EmailService(db)
            sent_count = 0

            for edukacija in edukacije:
                # Get all registered members
                prijave_result = await db.execute(
                    select(PrijavaEdukacije, Member).join(
                        Member, PrijavaEdukacije.member_id == Member.id
                    ).where(
                        and_(
                            PrijavaEdukacije.edukacija_id == edukacija.id,
                            PrijavaEdukacije.status.in_(["prijavljen", "potvrdjen"]),
                            Member.email.isnot(None),
                        )
                    )
                )
                prijave = prijave_result.all()

                for prijava, member in prijave:
                    await email_service.send_edukacija_reminder(
                        recipient=member.email,
                        ime=member.ime,
                        edukacija_naziv=edukacija.naziv,
                        datum=edukacija.datum_pocetka,
                        lokacija=edukacija.lokacija or "Biće naknadno objavljeno",
                    )
                    sent_count += 1

            return {"sent": sent_count}

    return asyncio.run(_send_reminders())


# Periodic tasks schedule
celery_app.conf.beat_schedule = {
    "send-clanarina-reminders": {
        "task": "app.tasks.send_clanarina_reminders_task",
        "schedule": 86400.0,  # Every 24 hours
    },
    "send-edukacija-reminders": {
        "task": "app.tasks.send_edukacija_reminders_task",
        "schedule": 86400.0,  # Every 24 hours
    },
}
