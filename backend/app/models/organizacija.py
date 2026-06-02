from datetime import datetime, timezone
from sqlalchemy import Boolean, Column, DateTime, Integer, String, Text
from app.database import Base


class Odeljenje(Base):
    __tablename__ = "odeljenja"

    id = Column(Integer, primary_key=True, index=True)
    naziv = Column(String(200), nullable=False, unique=True)
    opis = Column(Text, nullable=True)
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))


class StrucnaSprema(Base):
    __tablename__ = "strucne_spreme"

    id = Column(Integer, primary_key=True, index=True)
    naziv = Column(String(200), nullable=False, unique=True)
    nivo = Column(Integer, nullable=True)
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))


class RadnoMesto(Base):
    __tablename__ = "radna_mesta"

    id = Column(Integer, primary_key=True, index=True)
    naziv = Column(String(200), nullable=False, unique=True)
    opis = Column(Text, nullable=True)
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))
