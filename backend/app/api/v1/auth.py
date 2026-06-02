from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.ext.asyncio import AsyncSession

from app.database import get_db
from app.models import User
from app.api.deps import get_current_user, require_role
from app.schemas.auth import (
    LoginRequest,
    RegisterRequest,
    TokenResponse,
    RefreshRequest,
    ForgotPasswordRequest,
    ResetPasswordRequest,
    TwoFactorSetupResponse,
    TwoFactorVerifyRequest,
    TwoFactorDisableRequest,
    MessageResponse,
)
from app.services.auth import AuthService

router = APIRouter()


@router.post("/login", response_model=dict)
async def login(request: LoginRequest, db: AsyncSession = Depends(get_db)):
    """Authenticate user and return tokens."""
    auth_service = AuthService(db)
    result = await auth_service.login(
        email=request.email,
        password=request.password,
        totp_code=request.totp_code,
    )

    if "error" in result:
        if result.get("requires_2fa"):
            raise HTTPException(
                status_code=status.HTTP_401_UNAUTHORIZED,
                detail="2FA required",
            )
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail=result["error"],
        )

    return result


@router.post("/register", response_model=dict)
async def register(request: RegisterRequest, db: AsyncSession = Depends(get_db)):
    """Register a new user."""
    auth_service = AuthService(db)
    result = await auth_service.register(
        email=request.email,
        password=request.password,
        ime=request.ime,
        prezime=request.prezime,
    )

    if "error" in result:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=result["error"],
        )

    return result


@router.post("/refresh", response_model=TokenResponse)
async def refresh_token(request: RefreshRequest, db: AsyncSession = Depends(get_db)):
    """Refresh access token using refresh token."""
    auth_service = AuthService(db)
    result = await auth_service.refresh_token(request.refresh_token)

    if "error" in result:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail=result["error"],
        )

    return result


@router.post("/logout", response_model=MessageResponse)
async def logout(
    current_user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    """Logout user by invalidating all refresh tokens."""
    auth_service = AuthService(db)
    result = await auth_service.logout(current_user.id)
    return result


@router.post("/forgot-password", response_model=dict)
async def forgot_password(request: ForgotPasswordRequest, db: AsyncSession = Depends(get_db)):
    """Send password reset email."""
    auth_service = AuthService(db)
    result = await auth_service.forgot_password(request.email)
    return result


@router.post("/reset-password", response_model=MessageResponse)
async def reset_password(request: ResetPasswordRequest, db: AsyncSession = Depends(get_db)):
    """Reset password using token."""
    auth_service = AuthService(db)
    result = await auth_service.reset_password(request.token, request.new_password)

    if "error" in result:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=result["error"],
        )

    return result


@router.post("/2fa/setup", response_model=TwoFactorSetupResponse)
async def setup_2fa(
    current_user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    """Setup 2FA for current user."""
    auth_service = AuthService(db)
    result = await auth_service.setup_2fa(current_user)
    return result


@router.post("/2fa/verify", response_model=MessageResponse)
async def verify_2fa(
    request: TwoFactorVerifyRequest,
    current_user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    """Verify 2FA code and enable 2FA."""
    auth_service = AuthService(db)
    result = await auth_service.verify_2fa_setup(current_user, request.code)

    if "error" in result:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=result["error"],
        )

    return result


@router.post("/2fa/disable", response_model=MessageResponse)
async def disable_2fa(
    request: TwoFactorDisableRequest,
    current_user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    """Disable 2FA for current user."""
    auth_service = AuthService(db)
    result = await auth_service.disable_2fa(current_user, request.password)

    if "error" in result:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=result["error"],
        )

    return result


@router.get("/me")
async def get_current_user_info(current_user: User = Depends(get_current_user)):
    """Get current user information."""
    return {
        "id": current_user.id,
        "email": current_user.email,
        "role": current_user.role,
        "is_active": current_user.is_active,
        "two_factor_enabled": current_user.two_factor_enabled,
        "created_at": current_user.created_at.isoformat() if current_user.created_at else None,
    }
