"""Edukacija API endpoints."""

import uuid
from datetime import datetime, timezone, timedelta
from typing import Optional

from fastapi import APIRouter, Depends, HTTPException, status, Query
from sqlalchemy import select, func, and_, extract
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.orm import selectinload

from app.database import get_db
from app.models import (
    User, Member, Edukacija, PrijavaEdukacije, Sertifikat,
)
from app.api.deps import get_current_user, require_role
from app.services.email import EmailService

router = APIRouter()


# ============================================
# EDUKACIJE CRUD
# ============================================

@router.get("/")
async def list_edukacije(
    page: int = Query(1, ge=1),
    per_page: int = Query(20, ge=1, le=100),
    status: Optional[str] = None,
    tip: Optional[str] = None,
    search: Optional[str] = None,
    datum_od: Optional[datetime] = None,
    datum_do: Optional[datetime] = None,
    sort_by: str = Query("datum_pocetka", regex="^(naziv|datum_pocetka|status|cena)$"),
    sort_order: str = Query("asc", regex="^(asc|desc)$"),
    current_user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    """List edukacije with pagination and filters."""
    query = select(Edukacija)

    if search:
        query = query.where(Edukacija.naziv.ilike(f"%{search}%"))
    if status:
        query = query.where(Edukacija.status == status)
    if tip:
        query = query.where(Edukacija.tip == tip)
    if datum_od:
        query = query.where(Edukacija.datum_pocetka >= datum_od)
    if datum_do:
        query = query.where(Edukacija.datum_pocetka <= datum_do)

    # Count
    count_q = select(func.count(Edukacija.id))
    if search:
        count_q = count_q.where(Edukacija.naziv.ilike(f"%{search}%"))
    if status:
        count_q = count_q.where(Edukacija.status == status)
    if tip:
        count_q = count_q.where(Edukacija.tip == tip)
    total = (await db.execute(count_q)).scalar() or 0

    # Sort
    col = getattr(Edukacija, sort_by, Edukacija.datum_pocetka)
    query = query.order_by(col.desc() if sort_order == "desc" else col.asc())

    # Paginate
    offset = (page - 1) * per_page
    result = await db.execute(query.offset(offset).limit(per_page))
    edukacije = result.scalars().all()

    # Get registration counts
    items = []
    for e in edukacije:
        reg_count = (await db.execute(
            select(func.count(PrijavaEdukacije.id))
            .where(PrijavaEdukacije.edukacija_id == e.id)
        )).scalar() or 0

        items.append({
            "id": e.id,
            "naziv": e.naziv,
            "opis": e.opis,
            "tip": e.tip,
            "datum_pocetka": e.datum_pocetka.isoformat() if e.datum_pocetka else None,
            "datum_zavrsetka": e.datum_zavrsetka.isoformat() if e.datum_zavrsetka else None,
            "trajanje_sati": e.trajanje_sati,
            "lokacija": e.lokacija,
            "max_polaznika": e.max_polaznika,
            "cena": float(e.cena) if e.cena else None,
            "bodovi": e.bodovi,
            "status": e.status,
            "prijavljeni": reg_count,
            "created_at": e.created_at.isoformat() if e.created_at else None,
        })

    return {
        "items": items,
        "total": total,
        "page": page,
        "per_page": per_page,
        "pages": (total + per_page - 1) // per_page,
    }


@router.post("/")
async def create_edukacija(
    naziv: str,
    datum_pocetka: datetime,
    opis: Optional[str] = None,
    tip: Optional[str] = None,
    datum_zavrsetka: Optional[datetime] = None,
    trajanje_sati: Optional[int] = None,
    lokacija: Optional[str] = None,
    max_polaznika: Optional[int] = None,
    cena: Optional[float] = None,
    bodovi: Optional[int] = None,
    current_user: User = Depends(require_role("super_admin", "admin", "moderator")),
    db: AsyncSession = Depends(get_db),
):
    """Create a new edukacija."""
    edukacija = Edukacija(
        naziv=naziv,
        opis=opis,
        tip=tip,
        datum_pocetka=datum_pocetka,
        datum_zavrsetka=datum_zavrsetka,
        trajanje_sati=trajanje_sati,
        lokacija=lokacija,
        max_polaznika=max_polaznika,
        cena=cena,
        bodovi=bodovi,
        status="zakazano",
    )
    db.add(edukacija)
    await db.commit()
    await db.refresh(edukacija)

    return {
        "id": edukacija.id,
        "naziv": edukacija.naziv,
        "status": edukacija.status,
        "message": "Edukacija je kreirana",
    }


@router.get("/calendar")
async def get_calendar(
    year: int = Query(..., ge=2020, le=2030),
    month: Optional[int] = Query(None, ge=1, le=12),
    current_user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    """Get edukacije for calendar view."""
    query = select(Edukacija)

    if month:
        query = query.where(
            and_(
                extract("year", Edukacija.datum_pocetka) == year,
                extract("month", Edukacija.datum_pocetka) == month,
            )
        )
    else:
        query = query.where(extract("year", Edukacija.datum_pocetka) == year)

    query = query.order_by(Edukacija.datum_pocetka.asc())
    result = await db.execute(query)
    edukacije = result.scalars().all()

    return [
        {
            "id": e.id,
            "naziv": e.naziv,
            "tip": e.tip,
            "datum_pocetka": e.datum_pocetka.isoformat(),
            "datum_zavrsetka": e.datum_zavrsetka.isoformat() if e.datum_zavrsetka else None,
            "lokacija": e.lokacija,
            "status": e.status,
        }
        for e in edukacije
    ]


@router.get("/stats")
async def get_edukacija_stats(
    current_user: User = Depends(require_role("super_admin", "admin", "moderator")),
    db: AsyncSession = Depends(get_db),
):
    """Get edukacija statistics."""
    now = datetime.now(timezone.utc)

    total = (await db.execute(select(func.count(Edukacija.id)))).scalar() or 0
    upcoming = (await db.execute(
        select(func.count(Edukacija.id)).where(
            and_(Edukacija.datum_pocetka >= now, Edukacija.status != "otkazano")
        )
    )).scalar() or 0
    completed = (await db.execute(
        select(func.count(Edukacija.id)).where(Edukacija.status == "zavrseno")
    )).scalar() or 0

    total_reg = (await db.execute(select(func.count(PrijavaEdukacije.id)))).scalar() or 0
    attended = (await db.execute(
        select(func.count(PrijavaEdukacije.id)).where(PrijavaEdukacije.prisustvo == True)
    )).scalar() or 0
    certificates = (await db.execute(select(func.count(Sertifikat.id)))).scalar() or 0

    return {
        "total": total,
        "upcoming": upcoming,
        "completed": completed,
        "total_registrations": total_reg,
        "attended": attended,
        "certificates_issued": certificates,
    }


@router.get("/{edukacija_id}")
async def get_edukacija(
    edukacija_id: int,
    current_user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    """Get edukacija details."""
    result = await db.execute(
        select(Edukacija).where(Edukacija.id == edukacija_id)
    )
    edukacija = result.scalar_one_or_none()

    if not edukacija:
        raise HTTPException(status_code=404, detail="Edukacija nije pronađena")

    # Get registrations
    reg_result = await db.execute(
        select(PrijavaEdukacije, Member)
        .join(Member, PrijavaEdukacije.member_id == Member.id)
        .where(PrijavaEdukacije.edukacija_id == edukacija_id)
    )
    registrations = [
        {
            "id": p.id,
            "member_id": m.id,
            "member_name": f"{m.prezime} {m.ime}",
            "status": p.status,
            "prisustvo": p.prisustvo,
            "sertifikat_izdat": p.sertifikat_izdat,
        }
        for p, m in reg_result.all()
    ]

    return {
        "id": edukacija.id,
        "naziv": edukacija.naziv,
        "opis": edukacija.opis,
        "tip": edukacija.tip,
        "datum_pocetka": edukacija.datum_pocetka.isoformat() if edukacija.datum_pocetka else None,
        "datum_zavrsetka": edukacija.datum_zavrsetka.isoformat() if edukacija.datum_zavrsetka else None,
        "trajanje_sati": edukacija.trajanje_sati,
        "lokacija": edukacija.lokacija,
        "max_polaznika": edukacija.max_polaznika,
        "cena": float(edukacija.cena) if edukacija.cena else None,
        "bodovi": edukacija.bodovi,
        "status": edukacija.status,
        "registrations": registrations,
        "created_at": edukacija.created_at.isoformat() if edukacija.created_at else None,
    }


@router.put("/{edukacija_id}")
async def update_edukacija(
    edukacija_id: int,
    naziv: Optional[str] = None,
    opis: Optional[str] = None,
    tip: Optional[str] = None,
    datum_pocetka: Optional[datetime] = None,
    datum_zavrsetka: Optional[datetime] = None,
    trajanje_sati: Optional[int] = None,
    lokacija: Optional[str] = None,
    max_polaznika: Optional[int] = None,
    cena: Optional[float] = None,
    bodovi: Optional[int] = None,
    status: Optional[str] = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Update an edukacija."""
    result = await db.execute(select(Edukacija).where(Edukacija.id == edukacija_id))
    edukacija = result.scalar_one_or_none()

    if not edukacija:
        raise HTTPException(status_code=404, detail="Edukacija nije pronađena")

    if naziv is not None:
        edukacija.naziv = naziv
    if opis is not None:
        edukacija.opis = opis
    if tip is not None:
        edukacija.tip = tip
    if datum_pocetka is not None:
        edukacija.datum_pocetka = datum_pocetka
    if datum_zavrsetka is not None:
        edukacija.datum_zavrsetka = datum_zavrsetka
    if trajanje_sati is not None:
        edukacija.trajanje_sati = trajanje_sati
    if lokacija is not None:
        edukacija.lokacija = lokacija
    if max_polaznika is not None:
        edukacija.max_polaznika = max_polaznika
    if cena is not None:
        edukacija.cena = cena
    if bodovi is not None:
        edukacija.bodovi = bodovi
    if status is not None:
        valid = ["zakazano", "u_toku", "zavrseno", "otkazano"]
        if status not in valid:
            raise HTTPException(status_code=400, detail=f"Nevažeći status: {', '.join(valid)}")
        edukacija.status = status

    await db.commit()
    return {"id": edukacija.id, "message": "Edukacija je ažurirana"}


@router.delete("/{edukacija_id}")
async def delete_edukacija(
    edukacija_id: int,
    current_user: User = Depends(require_role("super_admin")),
    db: AsyncSession = Depends(get_db),
):
    """Cancel an edukacija."""
    result = await db.execute(select(Edukacija).where(Edukacija.id == edukacija_id))
    edukacija = result.scalar_one_or_none()

    if not edukacija:
        raise HTTPException(status_code=404, detail="Edukacija nije pronađena")

    edukacija.status = "otkazano"
    await db.commit()
    return {"message": "Edukacija je otkazana"}


# ============================================
# REGISTRACIJA / PRISUSTVO
# ============================================

@router.post("/{edukacija_id}/register")
async def register_member(
    edukacija_id: int,
    member_id: int,
    current_user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    """Register a member for edukacija."""
    # Check edukacija
    result = await db.execute(select(Edukacija).where(Edukacija.id == edukacija_id))
    edukacija = result.scalar_one_or_none()
    if not edukacija:
        raise HTTPException(status_code=404, detail="Edukacija nije pronađena")

    if edukacija.status in ["otkazano", "zavrseno"]:
        raise HTTPException(status_code=400, detail="Edukacija nije dostupna za prijavu")

    # Check capacity
    if edukacija.max_polaznika:
        count = (await db.execute(
            select(func.count(PrijavaEdukacije.id)).where(PrijavaEdukacije.edukacija_id == edukacija_id)
        )).scalar() or 0
        if count >= edukacija.max_polaznika:
            raise HTTPException(status_code=400, detail="Nema slobodnih mesta")

    # Check duplicate
    existing = await db.execute(
        select(PrijavaEdukacije).where(
            and_(
                PrijavaEdukacije.edukacija_id == edukacija_id,
                PrijavaEdukacije.member_id == member_id,
            )
        )
    )
    if existing.scalar_one_or_none():
        raise HTTPException(status_code=400, detail="Član je već prijavljen")

    prijava = PrijavaEdukacije(
        edukacija_id=edukacija_id,
        member_id=member_id,
        status="prijavljen",
        datum_prijave=datetime.now(timezone.utc),
    )
    db.add(prijava)
    await db.commit()

    return {"id": prijava.id, "message": "Uspešno prijavljen"}


@router.post("/{edukacija_id}/unregister")
async def unregister_member(
    edukacija_id: int,
    member_id: int,
    current_user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    """Unregister a member from edukacija."""
    result = await db.execute(
        select(PrijavaEdukacije).where(
            and_(
                PrijavaEdukacije.edukacija_id == edukacija_id,
                PrijavaEdukacije.member_id == member_id,
            )
        )
    )
    prijava = result.scalar_one_or_none()

    if not prijava:
        raise HTTPException(status_code=404, detail="Prijava nije pronađena")

    prijava.status = "otkazan"
    await db.commit()
    return {"message": "Prijava je otkazana"}


@router.post("/{edukacija_id}/attendance")
async def mark_attendance(
    edukacija_id: int,
    member_id: int,
    prisustvo: bool = True,
    ocena: Optional[int] = None,
    current_user: User = Depends(require_role("super_admin", "admin", "moderator")),
    db: AsyncSession = Depends(get_db),
):
    """Mark attendance for a member."""
    result = await db.execute(
        select(PrijavaEdukacije).where(
            and_(
                PrijavaEdukacije.edukacija_id == edukacija_id,
                PrijavaEdukacije.member_id == member_id,
            )
        )
    )
    prijava = result.scalar_one_or_none()

    if not prijava:
        raise HTTPException(status_code=404, detail="Prijava nije pronađena")

    prijava.prisustvo = prisustvo
    prijava.status = "prisustvovao" if prisustvo else "prijavljen"
    if ocena is not None:
        prijava.ocena = ocena

    await db.commit()
    return {"message": "Prisustvo evidentirano"}


# ============================================
# SERTIFIKATI
# ============================================

@router.post("/{edukacija_id}/certificate")
async def issue_certificate(
    edukacija_id: int,
    member_id: int,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Issue a certificate for a member."""
    # Check prijava
    result = await db.execute(
        select(PrijavaEdukacije)
        .options(selectinload(PrijavaEdukacije.edukacija))
        .where(
            and_(
                PrijavaEdukacije.edukacija_id == edukacija_id,
                PrijavaEdukacije.member_id == member_id,
            )
        )
    )
    prijava = result.scalar_one_or_none()

    if not prijava:
        raise HTTPException(status_code=404, detail="Prijava nije pronađena")

    if not prijava.prisustvo:
        raise HTTPException(status_code=400, detail="Član nije prisustvovao edukaciji")

    # Check if already issued
    existing = await db.execute(
        select(Sertifikat).where(Sertifikat.prijava_id == prijava.id)
    )
    if existing.scalar_one_or_none():
        raise HTTPException(status_code=400, detail="Sertifikat je već izdat")

    # Generate certificate number
    broj = f"UZRJ-{edukacija_id}-{member_id}-{uuid.uuid4().hex[:6].upper()}"

    sertifikat = Sertifikat(
        prijava_id=prijava.id,
        broj=broj,
        datum_izdavanja=datetime.now(timezone.utc),
    )
    db.add(sertifikat)

    prijava.sertifikat_izdat = True
    await db.commit()

    return {
        "id": sertifikat.id,
        "broj": broj,
        "message": "Sertifikat je izdat",
    }


@router.get("/{edukacija_id}/certificates")
async def list_certificates(
    edukacija_id: int,
    current_user: User = Depends(get_current_user),
    db: AsyncSession = Depends(get_db),
):
    """List certificates for an edukacija."""
    result = await db.execute(
        select(Sertifikat, PrijavaEdukacije, Member)
        .join(PrijavaEdukacije, Sertifikat.prijava_id == PrijavaEdukacije.id)
        .join(Member, PrijavaEdukacije.member_id == Member.id)
        .where(PrijavaEdukacije.edukacija_id == edukacija_id)
    )

    return [
        {
            "id": s.id,
            "broj": s.broj,
            "datum_izdavanja": s.datum_izdavanja.isoformat() if s.datum_izdavanja else None,
            "member": f"{m.prezime} {m.ime}",
            "member_id": m.id,
        }
        for s, p, m in result.all()
    ]