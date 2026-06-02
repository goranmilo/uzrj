"""Audit logging middleware."""

import json
import time
from datetime import datetime, timezone

from fastapi import Request
from starlette.middleware.base import BaseHTTPMiddleware
from starlette.responses import Response

from app.database import async_session
from app.models import AuditLog
from app.api.deps import decode_token


class AuditMiddleware(BaseHTTPMiddleware):
    """Middleware for logging API requests to audit log."""

    # Endpoints to audit
    AUDITED_METHODS = {"POST", "PUT", "DELETE", "PATCH"}
    AUDITED_PATHS = {
        "/api/v1/auth/login",
        "/api/v1/auth/register",
        "/api/v1/auth/logout",
        "/api/v1/auth/2fa/setup",
        "/api/v1/auth/2fa/verify",
        "/api/v1/auth/2fa/disable",
        "/api/v1/users",
        "/api/v1/members",
        "/api/v1/admin",
    }

    async def dispatch(self, request: Request, call_next) -> Response:
        # Skip non-audited requests
        if not self._should_audit(request):
            return await call_next(request)

        # Get user info from token
        user_id = None
        try:
            auth_header = request.headers.get("Authorization")
            if auth_header and auth_header.startswith("Bearer "):
                token = auth_header[7:]
                payload = decode_token(token)
                user_id = int(payload.get("sub", 0))
        except Exception:
            pass

        # Record start time
        start_time = time.time()

        # Process request
        response = await call_next(request)

        # Calculate duration
        duration = time.time() - start_time

        # Determine action
        action = self._get_action(request)

        # Log to audit
        if action:
            try:
                async with async_session() as session:
                    audit_log = AuditLog(
                        user_id=user_id,
                        action=action,
                        resource=self._get_resource(request.url.path),
                        details=json.dumps({
                            "method": request.method,
                            "path": str(request.url.path),
                            "status_code": response.status_code,
                            "duration": round(duration, 3),
                        }),
                        ip_address=self._get_client_ip(request),
                        user_agent=request.headers.get("User-Agent", "")[:500],
                        created_at=datetime.now(timezone.utc),
                    )
                    session.add(audit_log)
                    await session.commit()
            except Exception:
                # Don't fail the request if audit logging fails
                pass

        return response

    def _should_audit(self, request: Request) -> bool:
        """Check if request should be audited."""
        # Always audit auth endpoints
        if request.url.path.startswith("/api/v1/auth/"):
            return True

        # Audit mutating operations on protected endpoints
        if request.method in self.AUDITED_METHODS:
            for path in self.AUDITED_PATHS:
                if request.url.path.startswith(path):
                    return True

        return False

    def _get_action(self, request: Request) -> str:
        """Determine audit action from request."""
        path = request.url.path
        method = request.method

        if path == "/api/v1/auth/login":
            return "login"
        elif path == "/api/v1/auth/register":
            return "register"
        elif path == "/api/v1/auth/logout":
            return "logout"
        elif path == "/api/v1/auth/2fa/setup":
            return "2fa_setup"
        elif path == "/api/v1/auth/2fa/verify":
            return "2fa_verify"
        elif path == "/api/v1/auth/2fa/disable":
            return "2fa_disable"
        elif method == "POST":
            return "create"
        elif method in ("PUT", "PATCH"):
            return "update"
        elif method == "DELETE":
            return "delete"

        return ""

    def _get_resource(self, path: str) -> str:
        """Extract resource name from path."""
        parts = path.strip("/").split("/")
        if len(parts) >= 3:
            return parts[2]  # e.g., "members" from "/api/v1/members"
        return "unknown"

    def _get_client_ip(self, request: Request) -> str:
        """Extract client IP from request."""
        forwarded = request.headers.get("X-Forwarded-For")
        if forwarded:
            return forwarded.split(",")[0].strip()
        return request.client.host if request.client else "unknown"
