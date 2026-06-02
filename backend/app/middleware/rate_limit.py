"""Rate limiting middleware for authentication endpoints."""

import time
from collections import defaultdict
from typing import Dict, Tuple

from fastapi import Request, HTTPException, status
from starlette.middleware.base import BaseHTTPMiddleware
from starlette.responses import Response


class RateLimitMiddleware(BaseHTTPMiddleware):
    """Rate limiting middleware using in-memory storage."""

    def __init__(
        self,
        app,
        requests_per_minute: int = 60,
        requests_per_hour: int = 1000,
        login_attempts_per_15min: int = 5,
    ):
        super().__init__(app)
        self.requests_per_minute = requests_per_minute
        self.requests_per_hour = requests_per_hour
        self.login_attempts_per_15min = login_attempts_per_15min

        # Storage: {ip: [(timestamp, endpoint), ...]}
        self.requests: Dict[str, list] = defaultdict(list)
        self.login_attempts: Dict[str, list] = defaultdict(list)

    async def dispatch(self, request: Request, call_next) -> Response:
        client_ip = self._get_client_ip(request)
        current_time = time.time()

        # Clean old entries
        self._cleanup(current_time)

        # Check general rate limit
        if not self._check_rate_limit(client_ip, current_time):
            raise HTTPException(
                status_code=status.HTTP_429_TOO_MANY_REQUESTS,
                detail="Previše zahteva. Pokušajte ponovo kasnije.",
            )

        # Check login-specific rate limit
        if request.url.path == "/api/v1/auth/login" and request.method == "POST":
            if not self._check_login_rate_limit(client_ip, current_time):
                raise HTTPException(
                    status_code=status.HTTP_429_TOO_MANY_REQUESTS,
                    detail="Previše neuspelih pokušaja prijave. Pokušajte ponovo za 15 minuta.",
                )

        # Record request
        self.requests[client_ip].append((current_time, request.url.path))

        # Process request
        response = await call_next(request)

        # Add rate limit headers
        response.headers["X-RateLimit-Limit"] = str(self.requests_per_minute)
        response.headers["X-RateLimit-Remaining"] = str(
            max(0, self.requests_per_minute - self._get_minute_count(client_ip, current_time))
        )

        return response

    def _get_client_ip(self, request: Request) -> str:
        """Extract client IP from request."""
        forwarded = request.headers.get("X-Forwarded-For")
        if forwarded:
            return forwarded.split(",")[0].strip()
        return request.client.host if request.client else "unknown"

    def _cleanup(self, current_time: float):
        """Remove old entries."""
        minute_ago = current_time - 60
        hour_ago = current_time - 3600
        fifteen_min_ago = current_time - 900

        for ip in list(self.requests.keys()):
            self.requests[ip] = [
                (ts, ep) for ts, ep in self.requests[ip]
                if ts > hour_ago
            ]
            if not self.requests[ip]:
                del self.requests[ip]

        for ip in list(self.login_attempts.keys()):
            self.login_attempts[ip] = [
                ts for ts in self.login_attempts[ip]
                if ts > fifteen_min_ago
            ]
            if not self.login_attempts[ip]:
                del self.login_attempts[ip]

    def _get_minute_count(self, ip: str, current_time: float) -> int:
        """Get request count in last minute."""
        minute_ago = current_time - 60
        return sum(1 for ts, _ in self.requests.get(ip, []) if ts > minute_ago)

    def _check_rate_limit(self, ip: str, current_time: float) -> bool:
        """Check if request is within rate limits."""
        minute_count = self._get_minute_count(ip, current_time)
        hour_count = len(self.requests.get(ip, []))

        return minute_count < self.requests_per_minute and hour_count < self.requests_per_hour

    def _check_login_rate_limit(self, ip: str, current_time: float) -> bool:
        """Check if login attempt is within rate limit."""
        attempts = self.login_attempts.get(ip, [])
        return len(attempts) < self.login_attempts_per_15min

    def record_failed_login(self, ip: str):
        """Record a failed login attempt."""
        self.login_attempts[ip].append(time.time())
