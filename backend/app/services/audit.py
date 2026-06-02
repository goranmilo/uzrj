from datetime import datetime, timezone
from typing import Optional

from sqlalchemy.ext.asyncio import AsyncSession

from app.models import AuditLog


class AuditService:
    def __init__(self, db: AsyncSession):
        self.db = db

    async def log(
        self,
        user_id: Optional[int],
        action: str,
        resource: str,
        resource_id: Optional[int] = None,
        details: Optional[str] = None,
        ip_address: Optional[str] = None,
        user_agent: Optional[str] = None,
    ):
        """Create an audit log entry."""
        audit_log = AuditLog(
            user_id=user_id,
            action=action,
            resource=resource,
            resource_id=resource_id,
            details=details,
            ip_address=ip_address,
            user_agent=user_agent,
            created_at=datetime.now(timezone.utc),
        )
        self.db.add(audit_log)
        await self.db.commit()

    async def log_login(self, user_id: int, ip_address: Optional[str] = None):
        """Log user login."""
        await self.log(
            user_id=user_id,
            action="login",
            resource="user",
            resource_id=user_id,
            ip_address=ip_address,
        )

    async def log_logout(self, user_id: int, ip_address: Optional[str] = None):
        """Log user logout."""
        await self.log(
            user_id=user_id,
            action="logout",
            resource="user",
            resource_id=user_id,
            ip_address=ip_address,
        )

    async def log_register(self, user_id: int, ip_address: Optional[str] = None):
        """Log user registration."""
        await self.log(
            user_id=user_id,
            action="register",
            resource="user",
            resource_id=user_id,
            ip_address=ip_address,
        )

    async def log_password_reset(self, user_id: int, ip_address: Optional[str] = None):
        """Log password reset."""
        await self.log(
            user_id=user_id,
            action="password_reset",
            resource="user",
            resource_id=user_id,
            ip_address=ip_address,
        )

    async def log_2fa_enabled(self, user_id: int, ip_address: Optional[str] = None):
        """Log 2FA enabled."""
        await self.log(
            user_id=user_id,
            action="2fa_enabled",
            resource="user",
            resource_id=user_id,
            ip_address=ip_address,
        )

    async def log_2fa_disabled(self, user_id: int, ip_address: Optional[str] = None):
        """Log 2FA disabled."""
        await self.log(
            user_id=user_id,
            action="2fa_disabled",
            resource="user",
            resource_id=user_id,
            ip_address=ip_address,
        )
