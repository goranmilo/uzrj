"""Email service for sending emails via SMTP."""

import logging
from datetime import datetime, timezone
from typing import Optional

import aiosmtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from jinja2 import Environment, FileSystemLoader
from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession

from app.config import get_settings
from app.models import EmailTemplate, EmailLog

settings = get_settings()
logger = logging.getLogger(__name__)

# Jinja2 environment for email templates
template_env = Environment(
    loader=FileSystemLoader("app/templates"),
    autoescape=True,
)


class EmailService:
    def __init__(self, db: AsyncSession):
        self.db = db

    async def send_email(
        self,
        recipient: str,
        subject: str,
        body_html: str,
        body_text: Optional[str] = None,
        template_id: Optional[int] = None,
    ) -> dict:
        """Send an email via SMTP."""
        # Create message
        message = MIMEMultipart("alternative")
        message["From"] = f"{settings.SMTP_FROM_NAME} <{settings.SMTP_FROM_EMAIL}>"
        message["To"] = recipient
        message["Subject"] = subject

        # Add text part
        if body_text:
            message.attach(MIMEText(body_text, "plain", "utf-8"))

        # Add HTML part
        message.attach(MIMEText(body_html, "html", "utf-8"))

        # Log email
        email_log = EmailLog(
            recipient=recipient,
            template_id=template_id,
            subject=subject,
            status="queued",
            created_at=datetime.now(timezone.utc),
        )
        self.db.add(email_log)
        await self.db.commit()

        try:
            # Send email
            await aiosmtplib.send(
                message,
                hostname=settings.SMTP_HOST,
                port=settings.SMTP_PORT,
                username=settings.SMTP_USER,
                password=settings.SMTP_PASSWORD,
                use_tls=settings.SMTP_PORT == 465,
                start_tls=settings.SMTP_PORT == 587,
            )

            # Update log
            email_log.status = "sent"
            email_log.sent_at = datetime.now(timezone.utc)
            await self.db.commit()

            logger.info(f"Email sent to {recipient}: {subject}")
            return {"success": True, "message": "Email je uspešno poslat"}

        except Exception as e:
            # Update log with error
            email_log.status = "failed"
            email_log.error_message = str(e)[:500]
            await self.db.commit()

            logger.error(f"Failed to send email to {recipient}: {e}")
            return {"success": False, "error": str(e)}

    async def send_template_email(
        self,
        recipient: str,
        template_name: str,
        variables: dict,
    ) -> dict:
        """Send an email using a template."""
        # Get template from database
        result = await self.db.execute(
            select(EmailTemplate).where(
                EmailTemplate.name == template_name,
                EmailTemplate.is_active == True,
            )
        )
        template = result.scalar_one_or_none()

        if not template:
            logger.error(f"Email template not found: {template_name}")
            return {"success": False, "error": f"Template '{template_name}' not found"}

        # Render template
        try:
            subject_template = template_env.from_string(template.subject)
            subject = subject_template.render(**variables)

            html_template = template_env.from_string(template.body_html)
            body_html = html_template.render(**variables)

            body_text = None
            if template.body_text:
                text_template = template_env.from_string(template.body_text)
                body_text = text_template.render(**variables)

        except Exception as e:
            logger.error(f"Failed to render template {template_name}: {e}")
            return {"success": False, "error": f"Template render error: {e}"}

        # Send email
        return await self.send_email(
            recipient=recipient,
            subject=subject,
            body_html=body_html,
            body_text=body_text,
            template_id=template.id,
        )

    async def send_welcome_email(self, recipient: str, ime: str, prezime: str) -> dict:
        """Send welcome email to new member."""
        return await self.send_template_email(
            recipient=recipient,
            template_name="welcome",
            variables={
                "ime": ime,
                "prezime": prezime,
                "email": recipient,
                "app_name": settings.APP_NAME,
            },
        )

    async def send_password_reset_email(self, recipient: str, reset_token: str) -> dict:
        """Send password reset email."""
        reset_link = f"{settings.FRONTEND_URL}/reset-password?token={reset_token}"
        return await self.send_template_email(
            recipient=recipient,
            template_name="password_reset",
            variables={
                "reset_link": reset_link,
                "app_name": settings.APP_NAME,
            },
        )

    async def send_2fa_code_email(self, recipient: str, code: str) -> dict:
        """Send 2FA code via email (backup method)."""
        return await self.send_template_email(
            recipient=recipient,
            template_name="2fa_code",
            variables={
                "code": code,
                "app_name": settings.APP_NAME,
            },
        )

    async def send_clanarina_reminder(
        self,
        recipient: str,
        ime: str,
        prezime: str,
        iznos: float,
        period_do: datetime,
    ) -> dict:
        """Send clanarina reminder email."""
        return await self.send_template_email(
            recipient=recipient,
            template_name="clanarina_reminder",
            variables={
                "ime": ime,
                "prezime": prezime,
                "iznos": iznos,
                "valuta": "RSD",
                "rok": period_do.strftime("%d.%m.%Y."),
                "app_name": settings.APP_NAME,
            },
        )

    async def send_edukacija_invitation(
        self,
        recipient: str,
        ime: str,
        edukacija_naziv: str,
        datum: datetime,
        lokacija: str,
    ) -> dict:
        """Send edukacija invitation email."""
        return await self.send_template_email(
            recipient=recipient,
            template_name="edukacija_invitation",
            variables={
                "ime": ime,
                "edukacija_naziv": edukacija_naziv,
                "datum": datum.strftime("%d.%m.%Y. %H:%M"),
                "lokacija": lokacija,
                "app_name": settings.APP_NAME,
            },
        )

    async def send_edukacija_reminder(
        self,
        recipient: str,
        ime: str,
        edukacija_naziv: str,
        datum: datetime,
        lokacija: str,
    ) -> dict:
        """Send edukacija reminder email."""
        return await self.send_template_email(
            recipient=recipient,
            template_name="edukacija_reminder",
            variables={
                "ime": ime,
                "edukacija_naziv": edukacija_naziv,
                "datum": datum.strftime("%d.%m.%Y. %H:%M"),
                "lokacija": lokacija,
                "app_name": settings.APP_NAME,
            },
        )

    async def send_notification_email(
        self,
        recipient: str,
        ime: str,
        naslov: str,
        poruka: str,
    ) -> dict:
        """Send general notification email."""
        return await self.send_template_email(
            recipient=recipient,
            template_name="notification",
            variables={
                "ime": ime,
                "naslov": naslov,
                "poruka": poruka,
                "app_name": settings.APP_NAME,
            },
        )

    async def get_email_stats(self) -> dict:
        """Get email sending statistics."""
        from sqlalchemy import func

        # Total emails
        total_result = await self.db.execute(
            select(func.count(EmailLog.id))
        )
        total = total_result.scalar() or 0

        # Sent emails
        sent_result = await self.db.execute(
            select(func.count(EmailLog.id)).where(EmailLog.status == "sent")
        )
        sent = sent_result.scalar() or 0

        # Failed emails
        failed_result = await self.db.execute(
            select(func.count(EmailLog.id)).where(EmailLog.status == "failed")
        )
        failed = failed_result.scalar() or 0

        # Queued emails
        queued_result = await self.db.execute(
            select(func.count(EmailLog.id)).where(EmailLog.status == "queued")
        )
        queued = queued_result.scalar() or 0

        return {
            "total": total,
            "sent": sent,
            "failed": failed,
            "queued": queued,
            "success_rate": round((sent / total * 100) if total > 0 else 0, 2),
        }
