"""Email management API endpoints."""

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession

from app.database import get_db
from app.models import User, EmailTemplate, EmailLog
from app.api.deps import get_current_user, require_role
from app.services.email import EmailService
from app.rbac import Permission

router = APIRouter()


@router.get("/templates")
async def list_templates(
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """List all email templates (admin only)."""
    result = await db.execute(select(EmailTemplate).order_by(EmailTemplate.name))
    templates = result.scalars().all()

    return [
        {
            "id": t.id,
            "name": t.name,
            "subject": t.subject,
            "description": t.description,
            "is_active": t.is_active,
            "created_at": t.created_at.isoformat() if t.created_at else None,
        }
        for t in templates
    ]


@router.get("/templates/{template_id}")
async def get_template(
    template_id: int,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Get email template details."""
    result = await db.execute(select(EmailTemplate).where(EmailTemplate.id == template_id))
    template = result.scalar_one_or_none()

    if not template:
        raise HTTPException(status_code=404, detail="Template not found")

    return {
        "id": template.id,
        "name": template.name,
        "subject": template.subject,
        "body_html": template.body_html,
        "body_text": template.body_text,
        "description": template.description,
        "variables": template.variables,
        "is_active": template.is_active,
    }


@router.post("/templates")
async def create_template(
    name: str,
    subject: str,
    body_html: str,
    body_text: str = None,
    description: str = None,
    variables: str = None,
    current_user: User = Depends(require_role("super_admin")),
    db: AsyncSession = Depends(get_db),
):
    """Create a new email template (super_admin only)."""
    # Check if name exists
    result = await db.execute(select(EmailTemplate).where(EmailTemplate.name == name))
    if result.scalar_one_or_none():
        raise HTTPException(status_code=400, detail="Template name already exists")

    template = EmailTemplate(
        name=name,
        subject=subject,
        body_html=body_html,
        body_text=body_text,
        description=description,
        variables=variables,
        is_active=True,
    )
    db.add(template)
    await db.commit()

    return {"id": template.id, "name": template.name, "message": "Template created"}


@router.put("/templates/{template_id}")
async def update_template(
    template_id: int,
    subject: str = None,
    body_html: str = None,
    body_text: str = None,
    description: str = None,
    is_active: bool = None,
    current_user: User = Depends(require_role("super_admin")),
    db: AsyncSession = Depends(get_db),
):
    """Update an email template (super_admin only)."""
    result = await db.execute(select(EmailTemplate).where(EmailTemplate.id == template_id))
    template = result.scalar_one_or_none()

    if not template:
        raise HTTPException(status_code=404, detail="Template not found")

    if subject is not None:
        template.subject = subject
    if body_html is not None:
        template.body_html = body_html
    if body_text is not None:
        template.body_text = body_text
    if description is not None:
        template.description = description
    if is_active is not None:
        template.is_active = is_active

    await db.commit()
    return {"message": "Template updated"}


@router.get("/logs")
async def list_logs(
    skip: int = 0,
    limit: int = 50,
    status: str = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """List email logs (admin only)."""
    query = select(EmailLog).order_by(EmailLog.created_at.desc())

    if status:
        query = query.where(EmailLog.status == status)

    result = await db.execute(query.offset(skip).limit(limit))
    logs = result.scalars().all()

    return [
        {
            "id": log.id,
            "recipient": log.recipient,
            "subject": log.subject,
            "status": log.status,
            "error_message": log.error_message,
            "sent_at": log.sent_at.isoformat() if log.sent_at else None,
            "created_at": log.created_at.isoformat() if log.created_at else None,
        }
        for log in logs
    ]


@router.get("/stats")
async def get_email_stats(
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Get email sending statistics."""
    email_service = EmailService(db)
    return await email_service.get_email_stats()


@router.post("/test")
async def send_test_email(
    recipient: str,
    current_user: User = Depends(require_role("super_admin")),
    db: AsyncSession = Depends(get_db),
):
    """Send a test email (super_admin only)."""
    email_service = EmailService(db)
    result = await email_service.send_email(
        recipient=recipient,
        subject="UZRJ - Test email",
        body_html="<h1>Test email</h1><p>Ovo je test poruka iz UZRJ sistema.</p>",
        body_text="Test email - Ovo je test poruka iz UZRJ sistema.",
    )

    if result.get("success"):
        return {"message": f"Test email sent to {recipient}"}
    else:
        raise HTTPException(status_code=500, detail=result.get("error", "Failed to send"))
