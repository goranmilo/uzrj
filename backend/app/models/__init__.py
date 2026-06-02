from datetime import datetime, timezone
from sqlalchemy import Boolean, Column, DateTime, Integer, String, Text, ForeignKey, Numeric
from sqlalchemy.orm import relationship
from app.database import Base


# ============================================
# CLANOVI I ORGANIZACIJA
# ============================================

class User(Base):
    __tablename__ = "users"

    id = Column(Integer, primary_key=True, index=True)
    email = Column(String(255), unique=True, index=True, nullable=False)
    hashed_password = Column(String(255), nullable=False)
    role = Column(String(50), default="clan", nullable=False, index=True)
    is_active = Column(Boolean, default=True)
    two_factor_secret = Column(String(32), nullable=True)
    two_factor_enabled = Column(Boolean, default=False)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))
    updated_at = Column(DateTime, default=lambda: datetime.now(timezone.utc), onupdate=lambda: datetime.now(timezone.utc))

    # Relationships
    member = relationship("Member", back_populates="user", uselist=False)
    refresh_tokens = relationship("RefreshToken", back_populates="user", cascade="all, delete-orphan")
    audit_logs = relationship("AuditLog", back_populates="user")


class RefreshToken(Base):
    __tablename__ = "refresh_tokens"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id", ondelete="CASCADE"), nullable=False)
    token = Column(String(500), unique=True, index=True, nullable=False)
    expires_at = Column(DateTime, nullable=False)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))

    user = relationship("User", back_populates="refresh_tokens")


class Member(Base):
    __tablename__ = "members"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=True, unique=True)
    ime = Column(String(100), nullable=False, index=True)
    prezime = Column(String(100), nullable=False, index=True)
    jmbg = Column(String(13), unique=True, nullable=True)
    email = Column(String(255), nullable=True, index=True)
    telefon = Column(String(20), nullable=True)
    adresa = Column(String(255), nullable=True)
    datum_rodjenja = Column(DateTime, nullable=True)
    strucna_sprema_id = Column(Integer, ForeignKey("strucne_spreme.id"), nullable=True)
    radno_mesto_id = Column(Integer, ForeignKey("radna_mesta.id"), nullable=True)
    odeljenje_id = Column(Integer, ForeignKey("odeljenja.id"), nullable=True)
    datum_uclanjenja = Column(DateTime, default=lambda: datetime.now(timezone.utc))
    status = Column(String(50), default="aktivan", nullable=False, index=True)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))
    updated_at = Column(DateTime, default=lambda: datetime.now(timezone.utc), onupdate=lambda: datetime.now(timezone.utc))

    # Relationships
    user = relationship("User", back_populates="member")
    strucna_sprema = relationship("StrucnaSprema", back_populates="members")
    radno_mesto = relationship("RadnoMesto", back_populates="members")
    odeljenje = relationship("Odeljenje", back_populates="members")
    clanarine = relationship("Clanarina", back_populates="member", cascade="all, delete-orphan")
    prijave_edukacija = relationship("PrijavaEdukacije", back_populates="member", cascade="all, delete-orphan")


class Odeljenje(Base):
    __tablename__ = "odeljenja"

    id = Column(Integer, primary_key=True, index=True)
    naziv = Column(String(200), nullable=False, unique=True)
    opis = Column(Text, nullable=True)
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))

    members = relationship("Member", back_populates="odeljenje")


class StrucnaSprema(Base):
    __tablename__ = "strucne_spreme"

    id = Column(Integer, primary_key=True, index=True)
    naziv = Column(String(200), nullable=False, unique=True)
    nivo = Column(Integer, nullable=True)  # 1-SSS, 2-VŠ, 3-VSS, 4-Master, 5-Doktor
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))

    members = relationship("Member", back_populates="strucna_sprema")


class RadnoMesto(Base):
    __tablename__ = "radna_mesta"

    id = Column(Integer, primary_key=True, index=True)
    naziv = Column(String(200), nullable=False, unique=True)
    opis = Column(Text, nullable=True)
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))

    members = relationship("Member", back_populates="radno_mesto")


# ============================================
# ČLANARINA
# ============================================

class Clanarina(Base):
    __tablename__ = "clanarine"

    id = Column(Integer, primary_key=True, index=True)
    member_id = Column(Integer, ForeignKey("members.id", ondelete="CASCADE"), nullable=False, index=True)
    iznos = Column(Numeric(10, 2), nullable=False)
    valuta = Column(String(3), default="RSD")
    period_od = Column(DateTime, nullable=False)
    period_do = Column(DateTime, nullable=False)
    datum_uplate = Column(DateTime, nullable=True)
    nacin_placanja = Column(String(50), nullable=True)  # gotovina, uplatnica, kartica
    status = Column(String(50), default="neplaceno", nullable=False, index=True)  # placeno, neplaceno, delimicno
    broj_uplatnice = Column(String(100), nullable=True)
    napomena = Column(Text, nullable=True)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))
    updated_at = Column(DateTime, default=lambda: datetime.now(timezone.utc), onupdate=lambda: datetime.now(timezone.utc))

    member = relationship("Member", back_populates="clanarine")


# ============================================
# EDUKACIJA
# ============================================

class Edukacija(Base):
    __tablename__ = "edukacije"

    id = Column(Integer, primary_key=True, index=True)
    naziv = Column(String(300), nullable=False)
    opis = Column(Text, nullable=True)
    tip = Column(String(50), nullable=True)  # seminar, radionica, konferencija, kurs
    datum_pocetka = Column(DateTime, nullable=False)
    datum_zavrsetka = Column(DateTime, nullable=True)
    trajanje_sati = Column(Integer, nullable=True)
    lokacija = Column(String(255), nullable=True)
    max_polaznika = Column(Integer, nullable=True)
    cena = Column(Numeric(10, 2), nullable=True)
    bodovi = Column(Integer, nullable=True)  # strukovni bodovi
    status = Column(String(50), default="zakazano", nullable=False)  # zakazano, u_toku, zavrseno, otkazano
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))
    updated_at = Column(DateTime, default=lambda: datetime.now(timezone.utc), onupdate=lambda: datetime.now(timezone.utc))

    prijave = relationship("PrijavaEdukacije", back_populates="edukacija", cascade="all, delete-orphan")


class PrijavaEdukacije(Base):
    __tablename__ = "prijave_edukacija"

    id = Column(Integer, primary_key=True, index=True)
    edukacija_id = Column(Integer, ForeignKey("edukacije.id", ondelete="CASCADE"), nullable=False, index=True)
    member_id = Column(Integer, ForeignKey("members.id", ondelete="CASCADE"), nullable=False, index=True)
    status = Column(String(50), default="prijavljen", nullable=False)  # prijavljen, potvrdjen, otkazan, prisustvovao
    prisustvo = Column(Boolean, nullable=True)
    ocena = Column(Integer, nullable=True)
    sertifikat_izdat = Column(Boolean, default=False)
    datum_prijave = Column(DateTime, default=lambda: datetime.now(timezone.utc))

    edukacija = relationship("Edukacija", back_populates="prijave")
    member = relationship("Member", back_populates="prijave_edukacija")
    sertifikat = relationship("Sertifikat", back_populates="prijava", uselist=False)


class Sertifikat(Base):
    __tablename__ = "sertifikati"

    id = Column(Integer, primary_key=True, index=True)
    prijava_id = Column(Integer, ForeignKey("prijave_edukacija.id", ondelete="CASCADE"), unique=True, nullable=False)
    broj = Column(String(100), unique=True, nullable=False)
    datum_izdavanja = Column(DateTime, default=lambda: datetime.now(timezone.utc))
    pdf_path = Column(String(500), nullable=True)

    prijava = relationship("PrijavaEdukacije", back_populates="sertifikat")


# ============================================
# BAZA ZNANJA (DOKUMENTI)
# ============================================

class Kategorija(Base):
    __tablename__ = "kategorije"

    id = Column(Integer, primary_key=True, index=True)
    naziv = Column(String(200), nullable=False)
    parent_id = Column(Integer, ForeignKey("kategorije.id"), nullable=True)
    opis = Column(Text, nullable=True)
    sort_order = Column(Integer, default=0)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))

    parent = relationship("Kategorija", remote_side=[id], backref="children")
    dokumenti = relationship("Dokument", back_populates="kategorija")


class Tag(Base):
    __tablename__ = "tags"

    id = Column(Integer, primary_key=True, index=True)
    naziv = Column(String(100), unique=True, nullable=False)

    dokumenti = relationship("Dokument", secondary="document_tags", back_populates="tags")


class DocumentTag(Base):
    __tablename__ = "document_tags"

    document_id = Column(Integer, ForeignKey("dokumenti.id", ondelete="CASCADE"), primary_key=True)
    tag_id = Column(Integer, ForeignKey("tags.id", ondelete="CASCADE"), primary_key=True)


class Dokument(Base):
    __tablename__ = "dokumenti"

    id = Column(Integer, primary_key=True, index=True)
    naslov = Column(String(300), nullable=False, index=True)
    sadrzaj = Column(Text, nullable=True)
    kategorija_id = Column(Integer, ForeignKey("kategorije.id"), nullable=True, index=True)
    author_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    file_path = Column(String(500), nullable=True)
    file_type = Column(String(50), nullable=True)  # pdf, doc, image
    file_size = Column(Integer, nullable=True)
    version = Column(Integer, default=1)
    is_published = Column(Boolean, default=False)
    view_count = Column(Integer, default=0)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))
    updated_at = Column(DateTime, default=lambda: datetime.now(timezone.utc), onupdate=lambda: datetime.now(timezone.utc))

    kategorija = relationship("Kategorija", back_populates="dokumenti")
    author = relationship("User")
    tags = relationship("Tag", secondary="document_tags", back_populates="dokumenti")


# ============================================
# NOTIFIKACIJE
# ============================================

class EmailTemplate(Base):
    __tablename__ = "email_templates"

    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(100), unique=True, nullable=False)
    subject = Column(String(300), nullable=False)
    body_html = Column(Text, nullable=False)
    body_text = Column(Text, nullable=True)
    description = Column(Text, nullable=True)
    variables = Column(Text, nullable=True)  # JSON lista dostupnih varijabli
    is_active = Column(Boolean, default=True)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))
    updated_at = Column(DateTime, default=lambda: datetime.now(timezone.utc), onupdate=lambda: datetime.now(timezone.utc))


class EmailLog(Base):
    __tablename__ = "email_logs"

    id = Column(Integer, primary_key=True, index=True)
    recipient = Column(String(255), nullable=False, index=True)
    template_id = Column(Integer, ForeignKey("email_templates.id"), nullable=True)
    subject = Column(String(300), nullable=False)
    status = Column(String(50), default="queued", nullable=False)  # queued, sent, failed, bounced
    error_message = Column(Text, nullable=True)
    sent_at = Column(DateTime, nullable=True)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))

    template = relationship("EmailTemplate")


class Notification(Base):
    __tablename__ = "notifications"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id", ondelete="CASCADE"), nullable=False, index=True)
    type = Column(String(50), nullable=False)  # clanarina, edukacija, sistem, obavestenje
    title = Column(String(300), nullable=False)
    message = Column(Text, nullable=True)
    link = Column(String(500), nullable=True)
    is_read = Column(Boolean, default=False, index=True)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))

    user = relationship("User")


# ============================================
# AUDIT LOG
# ============================================

class AuditLog(Base):
    __tablename__ = "audit_log"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=True)
    action = Column(String(100), nullable=False, index=True)  # login, logout, create, update, delete
    resource = Column(String(100), nullable=False)  # user, member, clanarina, edukacija
    resource_id = Column(Integer, nullable=True)
    details = Column(Text, nullable=True)  # JSON detalji
    ip_address = Column(String(45), nullable=True)
    user_agent = Column(String(500), nullable=True)
    created_at = Column(DateTime, default=lambda: datetime.now(timezone.utc))

    user = relationship("User", back_populates="audit_logs")
