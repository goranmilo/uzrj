from datetime import datetime, timezone
from sqlalchemy import Column, DateTime, ForeignKey, Integer, String
from sqlalchemy.orm import relationship
from app.database import Base


class Member(Base):
    __tablename__ = "members"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    ime = Column(String(100), nullable=False)
    prezime = Column(String(100), nullable=False)
    jmbg = Column(String(13), unique=True, nullable=True)
    email = Column(String(255), nullable=True)
    telefon = Column(String(20), nullable=True)
    adresa = Column(String(255), nullable=True)
    datum_rodjenja = Column(DateTime, nullable=True)
    strucna_sprema_id = Column(Integer, ForeignKey("strucne_spreme.id"), nullable=True)
    radno_mesto_id = Column(Integer, ForeignKey("radna_mesta.id"), nullable=True)
    odeljenje_id = Column(Integer, ForeignKey("odeljenja.id"), nullable=True)
    datum_uclanjenja = Column(DateTime, default=lambda: datetime.now(timezone.utc))
    status = Column(String(50), default="aktivan")
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))
    updated_at = Column(DateTime, default=lambda: datetime.now(timezone.utc), onupdate=lambda: datetime.now(timezone.utc))

    user = relationship("User", backref="member")
    strucna_sprema = relationship("StrucnaSprema", backref="members")
    radno_mesto = relationship("RadnoMesto", backref="members")
    odeljenje = relationship("Odeljenje", backref="members")
