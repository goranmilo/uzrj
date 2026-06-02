from pydantic import BaseModel, EmailStr
from typing import Optional


class LoginRequest(BaseModel):
    email: EmailStr
    password: str
    totp_code: Optional[str] = None


class RegisterRequest(BaseModel):
    email: EmailStr
    password: str
    ime: str
    prezime: str


class TokenResponse(BaseModel):
    access_token: str
    refresh_token: str
    token_type: str = "bearer"


class RefreshRequest(BaseModel):
    refresh_token: str


class ForgotPasswordRequest(BaseModel):
    email: EmailStr


class ResetPasswordRequest(BaseModel):
    token: str
    new_password: str


class TwoFactorSetupResponse(BaseModel):
    secret: str
    qr_code: str  # Base64 encoded QR code image
    otpauth_url: str


class TwoFactorVerifyRequest(BaseModel):
    code: str


class TwoFactorDisableRequest(BaseModel):
    password: str


class MessageResponse(BaseModel):
    message: str
