import logging
from contextlib import asynccontextmanager

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from app.config import get_settings
from app.database import init_db

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


@app.get("/health")
async def health_check():
    return {"status": "ok", "version": settings.APP_VERSION}


# API routes will be added here
# from app.api.v1 import auth, members, clanarine, edukacije, documents, admin, dashboard
# app.include_router(auth.router, prefix="/api/v1/auth", tags=["Auth"])
# app.include_router(members.router, prefix="/api/v1/members", tags=["Members"])
# ...
