import logging
from contextlib import asynccontextmanager

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from app.config import get_settings
from app.database import init_db
from app.middleware.rate_limit import RateLimitMiddleware
from app.middleware.audit import AuditMiddleware

settings = get_settings()
logger = logging.getLogger(__name__)


@asynccontextmanager
async def lifespan(app: FastAPI):
    logger.info("Starting UZRJ application...")
    await init_db()
    logger.info("Database initialized.")
    yield
    logger.info("Shutting down UZRJ application...")


app = FastAPI(
    title=settings.APP_NAME,
    version=settings.APP_VERSION,
    lifespan=lifespan,
)

# CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.CORS_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Rate limiting
app.add_middleware(
    RateLimitMiddleware,
    requests_per_minute=60,
    requests_per_hour=1000,
    login_attempts_per_15min=5,
)

# Audit logging
app.add_middleware(AuditMiddleware)


@app.get("/health")
async def health_check():
    return {"status": "ok", "version": settings.APP_VERSION}


# API routes
from app.api.v1 import auth, users, email, admin, members

app.include_router(auth.router, prefix="/api/v1/auth", tags=["Auth"])
app.include_router(users.router, prefix="/api/v1/users", tags=["Users"])
app.include_router(email.router, prefix="/api/v1/email", tags=["Email"])
app.include_router(admin.router, prefix="/api/v1/admin", tags=["Admin"])
app.include_router(members.router, prefix="/api/v1/members", tags=["Members"])
