"""Seed email templates into the database."""

import asyncio
from datetime import datetime, timezone

from app.database import async_session, engine, Base
from app.models import EmailTemplate


# Default email templates
EMAIL_TEMPLATES = [
    {
        "name": "welcome",
        "subject": "Dobrodošli u {{ app_name }}",
        "body_html": None,  # Will be loaded from file
        "description": "Email dobrodošlice za nove članove",
        "variables": '["ime", "prezime", "email", "app_name"]',
    },
    {
        "name": "password_reset",
        "subject": "Resetovanje lozinke - {{ app_name }}",
        "body_html": None,
        "description": "Email za resetovanje lozinke",
        "variables": '["reset_link", "app_name"]',
    },
    {
        "name": "2fa_code",
        "subject": "Vaš 2FA kod - {{ app_name }}",
        "body_html": None,
        "description": "2FA verifikacioni kod",
        "variables": '["code", "app_name"]',
    },
    {
        "name": "clanarina_reminder",
        "subject": "Podsetnik za članarinu - {{ app_name }}",
        "body_html": None,
        "description": "Podsetnik za plaćanje članarine",
        "variables": '["ime", "prezime", "iznos", "valuta", "rok", "app_name"]',
    },
    {
        "name": "edukacija_invitation",
        "subject": "Poziv na edukaciju - {{ app_name }}",
        "body_html": None,
        "description": "Poziv za prisustvo edukaciji",
        "variables": '["ime", "edukacija_naziv", "datum", "lokacija", "app_name"]',
    },
    {
        "name": "edukacija_reminder",
        "subject": "Podsetnik za edukaciju - {{ app_name }}",
        "body_html": None,
        "description": "Podsetnik za edukaciju sutra",
        "variables": '["ime", "edukacija_naziv", "datum", "lokacija", "app_name"]',
    },
    {
        "name": "notification",
        "subject": "{{ naslov }} - {{ app_name }}",
        "body_html": None,
        "description": "Generička notifikacija",
        "variables": '["ime", "naslov", "poruka", "app_name"]',
    },
]


async def seed_email_templates():
    """Seed email templates from files."""
    import os

    async with engine.begin() as conn:
        await conn.run_sync(Base.metadata.create_all)

    async with async_session() as session:
        for template_data in EMAIL_TEMPLATES:
            # Check if template exists
            from sqlalchemy import select
            result = await session.execute(
                select(EmailTemplate).where(EmailTemplate.name == template_data["name"])
            )
            if result.scalar_one_or_none():
                print(f"Template '{template_data['name']}' already exists. Skipping.")
                continue

            # Load HTML from file
            template_file = f"app/templates/email/{template_data['name']}.html"
            if os.path.exists(template_file):
                with open(template_file, "r") as f:
                    body_html = f.read()
            else:
                print(f"Warning: Template file not found: {template_file}")
                body_html = f"<h1>{template_data['name']}</h1><p>Template not implemented yet.</p>"

            template = EmailTemplate(
                name=template_data["name"],
                subject=template_data["subject"],
                body_html=body_html,
                description=template_data["description"],
                variables=template_data["variables"],
                is_active=True,
                created_at=datetime.now(timezone.utc),
            )
            session.add(template)
            print(f"Created template: {template_data['name']}")

        await session.commit()
        print("\nEmail templates seeded successfully!")


if __name__ == "__main__":
    asyncio.run(seed_email_templates())
