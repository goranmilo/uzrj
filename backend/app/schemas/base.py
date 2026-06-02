from pydantic import BaseModel, EmailStr
from datetime import datetime
from typing import Optional


class BaseSchema(BaseModel):
    class Config:
        from_attributes = True


class TimestampMixin(BaseSchema):
    created_at: Optional[datetime] = None
    updated_at: Optional[datetime] = None


class UserBase(BaseSchema):
    email: EmailStr
    role: str = "clan"
    is_active: bool = True


class UserCreate(UserBase):
    password: str


class UserResponse(UserBase):
    id: int
    created_at: datetime
    two_factor_enabled: bool = False


class TokenResponse(BaseSchema):
    access_token: str
    refresh_token: str
    token_type: str = "bearer"


class TokenPayload(BaseSchema):
    sub: str
    role: str
    exp: int
