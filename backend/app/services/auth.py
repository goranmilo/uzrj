from datetime import datetime, timedelta, timezone
from typing import Optional

import pyotp
import qrcode
import io
import base64
from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession

from app.models import User, RefreshToken
from app.api.deps import (
    get_password_hash,
    verify_password,
    create_access_token,
    create_refresh_token,
    decode_token,
)
from app.config import get_settings

settings = get_settings()


class AuthService:
    def __init__(self, db: AsyncSession):
        self.db = db

    async def authenticate_user(self, email: str, password: str) -> Optional[User]:
        """Authenticate user by email and password."""
        result = await self.db.execute(select(User).where(User.email == email))
        user = result.scalar_one_or_none()

        if not user:
            return None
        if not verify_password(password, user.hashed_password):
            return None
        if not user.is_active:
            return None

        return user

    async def verify_2fa(self, user: User, code: str) -> bool:
        """Verify TOTP code for 2FA."""
        if not user.two_factor_enabled or not user.two_factor_secret:
            return True  # 2FA not enabled, skip verification

        totp = pyotp.TOTP(user.two_factor_secret)
        return totp.verify(code, valid_window=1)

    async def login(self, email: str, password: str, totp_code: Optional[str] = None) -> dict:
        """Login user and return tokens."""
        user = await self.authenticate_user(email, password)
        if not user:
            return {"error": "Pogrešan email ili lozinka"}

        # Check if 2FA is required
        if user.two_factor_enabled:
            if not totp_code:
                return {"error": "2FA required", "requires_2fa": True}

            if not await self.verify_2fa(user, totp_code):
                return {"error": "Neispravan 2FA kod"}

        # Generate tokens
        access_token = create_access_token(subject=str(user.id), role=user.role)
        refresh_token = create_refresh_token(subject=str(user.id))

        # Store refresh token
        db_refresh_token = RefreshToken(
            user_id=user.id,
            token=refresh_token,
            expires_at=datetime.now(timezone.utc) + timedelta(days=settings.REFRESH_TOKEN_EXPIRE_DAYS),
        )
        self.db.add(db_refresh_token)
        await self.db.commit()

        return {
            "access_token": access_token,
            "refresh_token": refresh_token,
            "token_type": "bearer",
            "user": {
                "id": user.id,
                "email": user.email,
                "role": user.role,
                "two_factor_enabled": user.two_factor_enabled,
            },
        }

    async def register(self, email: str, password: str, ime: str, prezime: str) -> dict:
        """Register a new user."""
        # Check if email already exists
        result = await self.db.execute(select(User).where(User.email == email))
        if result.scalar_one_or_none():
            return {"error": "Email adresa je već registrovana"}

        # Create user
        user = User(
            email=email,
            hashed_password=get_password_hash(password),
            role="clan",
            is_active=True,
        )
        self.db.add(user)
        await self.db.flush()

        # Create member profile
        from app.models import Member
        member = Member(
            user_id=user.id,
            ime=ime,
            prezime=prezime,
            email=email,
            status="aktivan",
        )
        self.db.add(member)
        await self.db.commit()

        # Generate tokens
        access_token = create_access_token(subject=str(user.id), role=user.role)
        refresh_token = create_refresh_token(subject=str(user.id))

        # Store refresh token
        db_refresh_token = RefreshToken(
            user_id=user.id,
            token=refresh_token,
            expires_at=datetime.now(timezone.utc) + timedelta(days=settings.REFRESH_TOKEN_EXPIRE_DAYS),
        )
        self.db.add(db_refresh_token)
        await self.db.commit()

        return {
            "access_token": access_token,
            "refresh_token": refresh_token,
            "token_type": "bearer",
            "user": {
                "id": user.id,
                "email": user.email,
                "role": user.role,
            },
        }

    async def refresh_token(self, refresh_token: str) -> dict:
        """Refresh access token using refresh token."""
        # Find refresh token in database
        result = await self.db.execute(
            select(RefreshToken).where(
                RefreshToken.token == refresh_token,
                RefreshToken.expires_at > datetime.now(timezone.utc),
            )
        )
        db_token = result.scalar_one_or_none()

        if not db_token:
            return {"error": "Nevažeći ili istekao refresh token"}

        # Get user
        result = await self.db.execute(select(User).where(User.id == db_token.user_id))
        user = result.scalar_one_or_none()

        if not user or not user.is_active:
            return {"error": "Korisnik nije pronađen ili je deaktiviran"}

        # Delete old refresh token
        await self.db.delete(db_token)

        # Generate new tokens
        new_access_token = create_access_token(subject=str(user.id), role=user.role)
        new_refresh_token = create_refresh_token(subject=str(user.id))

        # Store new refresh token
        new_db_token = RefreshToken(
            user_id=user.id,
            token=new_refresh_token,
            expires_at=datetime.now(timezone.utc) + timedelta(days=settings.REFRESH_TOKEN_EXPIRE_DAYS),
        )
        self.db.add(new_db_token)
        await self.db.commit()

        return {
            "access_token": new_access_token,
            "refresh_token": new_refresh_token,
            "token_type": "bearer",
        }

    async def logout(self, user_id: int) -> dict:
        """Logout user by invalidating all refresh tokens."""
        result = await self.db.execute(
            select(RefreshToken).where(RefreshToken.user_id == user_id)
        )
        tokens = result.scalars().all()

        for token in tokens:
            await self.db.delete(token)

        await self.db.commit()
        return {"message": "Uspešno ste se odjavili"}

    async def setup_2fa(self, user: User) -> dict:
        """Setup 2FA for user."""
        # Generate secret
        secret = pyotp.random_base32()

        # Generate TOTP URI
        totp = pyotp.TOTP(secret)
        otpauth_url = totp.provisioning_uri(
            name=user.email,
            issuer_name=settings.TOTP_ISSUER,
        )

        # Generate QR code
        qr = qrcode.QRCode(version=1, box_size=10, border=5)
        qr.add_data(otpauth_url)
        qr.make(fit=True)

        img = qr.make_image(fill_color="black", back_color="white")
        buffer = io.BytesIO()
        img.save(buffer, format="PNG")
        qr_code_base64 = base64.b64encode(buffer.getvalue()).decode()

        # Store secret temporarily (not enabled until verified)
        user.two_factor_secret = secret
        await self.db.commit()

        return {
            "secret": secret,
            "qr_code": f"data:image/png;base64,{qr_code_base64}",
            "otpauth_url": otpauth_url,
        }

    async def verify_2fa_setup(self, user: User, code: str) -> dict:
        """Verify 2FA code and enable 2FA."""
        if not user.two_factor_secret:
            return {"error": "2FA nije podešen. Prvo pozovite /2fa/setup"}

        totp = pyotp.TOTP(user.two_factor_secret)
        if not totp.verify(code, valid_window=1):
            return {"error": "Neispravan 2FA kod"}

        # Enable 2FA
        user.two_factor_enabled = True
        await self.db.commit()

        return {"message": "2FA je uspešno aktiviran"}

    async def disable_2fa(self, user: User, password: str) -> dict:
        """Disable 2FA for user."""
        # Verify password
        if not verify_password(password, user.hashed_password):
            return {"error": "Pogrešna lozinka"}

        # Disable 2FA
        user.two_factor_enabled = False
        user.two_factor_secret = None
        await self.db.commit()

        return {"message": "2FA je deaktiviran"}

    async def forgot_password(self, email: str) -> dict:
        """Send password reset email."""
        result = await self.db.execute(select(User).where(User.email == email))
        user = result.scalar_one_or_none()

        if not user:
            # Don't reveal if email exists
            return {"message": "Ako postoji nalog sa tom email adresom, poslat je link za resetovanje lozinke"}

        # Generate reset token (short-lived)
        reset_token = create_access_token(
            subject=str(user.id),
            role="reset",
            expires_delta=timedelta(hours=1),
        )

        # TODO: Send email with reset link
        # For now, just return the token (in production, send via email)
        return {
            "message": "Ako postoji nalog sa tom email adresom, poslat je link za resetovanje lozinke",
            "debug_token": reset_token,  # Remove in production
        }

    async def reset_password(self, token: str, new_password: str) -> dict:
        """Reset password using token."""
        try:
            payload = decode_token(token)
            if payload.get("role") != "reset":
                return {"error": "Nevažeći token"}

            user_id = payload.get("sub")
            result = await self.db.execute(select(User).where(User.id == int(user_id)))
            user = result.scalar_one_or_none()

            if not user:
                return {"error": "Korisnik nije pronađen"}

            # Update password
            user.hashed_password = get_password_hash(new_password)

            # Invalidate all refresh tokens
            result = await self.db.execute(
                select(RefreshToken).where(RefreshToken.user_id == user.id)
            )
            tokens = result.scalars().all()
            for token in tokens:
                await self.db.delete(token)

            await self.db.commit()

            return {"message": "Lozinka je uspešno promenjena"}
        except Exception:
            return {"error": "Nevažeći ili istekao token"}
