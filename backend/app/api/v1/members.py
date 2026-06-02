"""Members API endpoints."""

import csv
import io
from datetime import datetime, timezone
from typing import Optional

from fastapi import APIRouter, Depends, HTTPException, status, Query
from fastapi.responses import StreamingResponse
from sqlalchemy import select, func, or_, and_
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.orm import selectinload

from app.database import get_db
from app.models import User, Member, Odeljenje, StrucnaSprema, RadnoMesto
from app.api.deps import get_current_user, require_role
from app.rbac import Permission

router = APIRouter()


@router.get("/")
async def list_members(
    page: int = Query(1, ge=1),
    per_page: int = Query(20, ge=1, le=100),
    search: Optional[str] = None,
    odeljenje_id: Optional[int] = None,
    strucna_sprema_id: Optional[int] = None,
    radno_mesto_id: Optional[int] = None,
    status: Optional[str] = None,
    sort_by: str = Query("prezime", regex="^(ime|prezime|datum_uclanjenja|status)$"),
    sort_order: str = Query("asc", regex="^(asc|desc)$"),
    current_user: User = Depends(require_role("super_admin", "admin", "moderator")),
    db: AsyncSession = Depends(get_db),
):
    """List members with pagination, filters, and sorting."""
    # Base query
    query = select(Member).options(
        selectinload(Member.odeljenje),
        selectinload(Member.strucna_sprema),
        selectinload(Member.radno_mesto),
    )

    # Apply filters
    if search:
        search_term = f"%{search}%"
        query = query.where(
            or_(
                Member.ime.ilike(search_term),
                Member.prezime.ilike(search_term),
                Member.email.ilike(search_term),
                Member.jmbg.ilike(search_term),
                Member.telefon.ilike(search_term),
            )
        )

    if odeljenje_id:
        query = query.where(Member.odeljenje_id == odeljenje_id)

    if strucna_sprema_id:
        query = query.where(Member.strucna_sprema_id == strucna_sprema_id)

    if radno_mesto_id:
        query = query.where(Member.radno_mesto_id == radno_mesto_id)

    if status:
        query = query.where(Member.status == status)

    # Get total count
    count_query = select(func.count(Member.id))
    if search:
        search_term = f"%{search}%"
        count_query = count_query.where(
            or_(
                Member.ime.ilike(search_term),
                Member.prezime.ilike(search_term),
                Member.email.ilike(search_term),
                Member.jmbg.ilike(search_term),
            )
        )
    if odeljenje_id:
        count_query = count_query.where(Member.odeljenje_id == odeljenje_id)
    if strucna_sprema_id:
        count_query = count_query.where(Member.strucna_sprema_id == strucna_sprema_id)
    if radno_mesto_id:
        count_query = count_query.where(Member.radno_mesto_id == radno_mesto_id)
    if status:
        count_query = count_query.where(Member.status == status)

    total_result = await db.execute(count_query)
    total = total_result.scalar() or 0

    # Apply sorting
    sort_column = getattr(Member, sort_by, Member.prezime)
    if sort_order == "desc":
        query = query.order_by(sort_column.desc())
    else:
        query = query.order_by(sort_column.asc())

    # Apply pagination
    offset = (page - 1) * per_page
    query = query.offset(offset).limit(per_page)

    # Execute query
    result = await db.execute(query)
    members = result.scalars().all()

    return {
        "items": [
            {
                "id": m.id,
                "ime": m.ime,
                "prezime": m.prezime,
                "jmbg": m.jmbg,
                "email": m.email,
                "telefon": m.telefon,
                "adresa": m.adresa,
                "datum_rodjenja": m.datum_rodjenja.isoformat() if m.datum_rodjenja else None,
                "datum_uclanjenja": m.datum_uclanjenja.isoformat() if m.datum_uclanjenja else None,
                "status": m.status,
                "odeljenje": {
                    "id": m.odeljenje.id,
                    "naziv": m.odeljenje.naziv,
                } if m.odeljenje else None,
                "strucna_sprema": {
                    "id": m.strucna_sprema.id,
                    "naziv": m.strucna_sprema.naziv,
                } if m.strucna_sprema else None,
                "radno_mesto": {
                    "id": m.radno_mesto.id,
                    "naziv": m.radno_mesto.naziv,
                } if m.radno_mesto else None,
                "created_at": m.created_at.isoformat() if m.created_at else None,
            }
            for m in members
        ],
        "total": total,
        "page": page,
        "per_page": per_page,
        "pages": (total + per_page - 1) // per_page,
    }


@router.post("/")
async def create_member(
    ime: str,
    prezime: str,
    email: Optional[str] = None,
    jmbg: Optional[str] = None,
    telefon: Optional[str] = None,
    adresa: Optional[str] = None,
    datum_rodjenja: Optional[datetime] = None,
    odeljenje_id: Optional[int] = None,
    strucna_sprema_id: Optional[int] = None,
    radno_mesto_id: Optional[int] = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Create a new member."""
    # Check if JMBG already exists
    if jmbg:
        result = await db.execute(select(Member).where(Member.jmbg == jmbg))
        if result.scalar_one_or_none():
            raise HTTPException(status_code=400, detail="Član sa ovim JMBG-om već postoji")

    # Validate foreign keys
    if odeljenje_id:
        odeljenje = await db.execute(select(Odeljenje).where(Odeljenje.id == odeljenje_id))
        if not odeljenje.scalar_one_or_none():
            raise HTTPException(status_code=400, detail="Odeljenje nije pronađeno")

    if strucna_sprema_id:
        sprema = await db.execute(select(StrucnaSprema).where(StrucnaSprema.id == strucna_sprema_id))
        if not sprema.scalar_one_or_none():
            raise HTTPException(status_code=400, detail="Stručna sprema nije pronađena")

    if radno_mesto_id:
        mesto = await db.execute(select(RadnoMesto).where(RadnoMesto.id == radno_mesto_id))
        if not mesto.scalar_one_or_none():
            raise HTTPException(status_code=400, detail="Radno mesto nije pronađeno")

    # Create member
    member = Member(
        ime=ime,
        prezime=prezime,
        email=email,
        jmbg=jmbg,
        telefon=telefon,
        adresa=adresa,
        datum_rodjenja=datum_rodjenja,
        odeljenje_id=odeljenje_id,
        strucna_sprema_id=strucna_sprema_id,
        radno_mesto_id=radno_mesto_id,
        datum_uclanjenja=datetime.now(timezone.utc),
        status="aktivan",
    )
    db.add(member)
    await db.commit()
    await db.refresh(member)

    return {
        "id": member.id,
        "ime": member.ime,
        "prezime": member.prezime,
        "email": member.email,
        "status": member.status,
        "message": "Član je uspešno kreiran",
    }


@router.get("/export")
async def export_members(
    format: str = Query("csv", regex="^(csv)$"),
    odeljenje_id: Optional[int] = None,
    status: Optional[str] = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Export members to CSV."""
    query = select(Member).options(
        selectinload(Member.odeljenje),
        selectinload(Member.strucna_sprema),
        selectinload(Member.radno_mesto),
    )

    if odeljenje_id:
        query = query.where(Member.odeljenje_id == odeljenje_id)
    if status:
        query = query.where(Member.status == status)

    query = query.order_by(Member.prezime, Member.ime)

    result = await db.execute(query)
    members = result.scalars().all()

    # Create CSV
    output = io.StringIO()
    writer = csv.writer(output)

    # Header
    writer.writerow([
        "ID", "Ime", "Prezime", "JMBG", "Email", "Telefon", "Adresa",
        "Datum rođenja", "Odeljenje", "Stručna sprema", "Radno mesto",
        "Datum učlanjenja", "Status",
    ])

    # Data
    for m in members:
        writer.writerow([
            m.id,
            m.ime,
            m.prezime,
            m.jmbg or "",
            m.email or "",
            m.telefon or "",
            m.adresa or "",
            m.datum_rodjenja.strftime("%d.%m.%Y.") if m.datum_rodjenja else "",
            m.odeljenje.naziv if m.odeljenje else "",
            m.strucna_sprema.naziv if m.strucna_sprema else "",
            m.radno_mesto.naziv if m.radno_mesto else "",
            m.datum_uclanjenja.strftime("%d.%m.%Y.") if m.datum_uclanjenja else "",
            m.status,
        ])

    output.seek(0)

    # Return as streaming response
    return StreamingResponse(
        iter([output.getvalue()]),
        media_type="text/csv",
        headers={
            "Content-Disposition": f"attachment; filename=clanovi_{datetime.now().strftime('%Y%m%d_%H%M%S')}.csv"
        },
    )


@router.get("/stats")
async def get_member_stats(
    current_user: User = Depends(require_role("super_admin", "admin", "moderator")),
    db: AsyncSession = Depends(get_db),
):
    """Get member statistics."""
    # Total members
    total_result = await db.execute(select(func.count(Member.id)))
    total = total_result.scalar() or 0

    # By status
    status_result = await db.execute(
        select(Member.status, func.count(Member.id))
        .group_by(Member.status)
    )
    by_status = {row[0]: row[1] for row in status_result.all()}

    # By odeljenje
    odeljenje_result = await db.execute(
        select(Odeljenje.naziv, func.count(Member.id))
        .join(Member, Odeljenje.id == Member.odeljenje_id)
        .group_by(Odeljenje.naziv)
    )
    by_odeljenje = {row[0]: row[1] for row in odeljenje_result.all()}

    # By strucna sprema
    sprema_result = await db.execute(
        select(StrucnaSprema.naziv, func.count(Member.id))
        .join(Member, StrucnaSprema.id == Member.strucna_sprema_id)
        .group_by(StrucnaSprema.naziv)
    )
    by_sprema = {row[0]: row[1] for row in sprema_result.all()}

    # Recent members (last 30 days)
    from datetime import timedelta
    thirty_days_ago = datetime.now(timezone.utc) - timedelta(days=30)
    recent_result = await db.execute(
        select(func.count(Member.id))
        .where(Member.created_at >= thirty_days_ago)
    )
    recent = recent_result.scalar() or 0

    return {
        "total": total,
        "by_status": by_status,
        "by_odeljenje": by_odeljenje,
        "by_strucna_sprema": by_sprema,
        "recent_30_days": recent,
    }


@router.get("/{member_id}")
async def get_member(
    member_id: int,
    current_user: User = Depends(require_role("super_admin", "admin", "moderator")),
    db: AsyncSession = Depends(get_db),
):
    """Get member details."""
    result = await db.execute(
        select(Member)
        .options(
            selectinload(Member.odeljenje),
            selectinload(Member.strucna_sprema),
            selectinload(Member.radno_mesto),
            selectinload(Member.user),
        )
        .where(Member.id == member_id)
    )
    member = result.scalar_one_or_none()

    if not member:
        raise HTTPException(status_code=404, detail="Član nije pronađen")

    return {
        "id": member.id,
        "user_id": member.user_id,
        "ime": member.ime,
        "prezime": member.prezime,
        "jmbg": member.jmbg,
        "email": member.email,
        "telefon": member.telefon,
        "adresa": member.adresa,
        "datum_rodjenja": member.datum_rodjenja.isoformat() if member.datum_rodjenja else None,
        "datum_uclanjenja": member.datum_uclanjenja.isoformat() if member.datum_uclanjenja else None,
        "status": member.status,
        "odeljenje": {
            "id": member.odeljenje.id,
            "naziv": member.odeljenje.naziv,
        } if member.odeljenje else None,
        "strucna_sprema": {
            "id": member.strucna_sprema.id,
            "naziv": member.strucna_sprema.naziv,
            "nivo": member.strucna_sprema.nivo,
        } if member.strucna_sprema else None,
        "radno_mesto": {
            "id": member.radno_mesto.id,
            "naziv": member.radno_mesto.naziv,
        } if member.radno_mesto else None,
        "user": {
            "id": member.user.id,
            "email": member.user.email,
            "role": member.user.role,
        } if member.user else None,
        "created_at": member.created_at.isoformat() if member.created_at else None,
        "updated_at": member.updated_at.isoformat() if member.updated_at else None,
    }


@router.put("/{member_id}")
async def update_member(
    member_id: int,
    ime: Optional[str] = None,
    prezime: Optional[str] = None,
    email: Optional[str] = None,
    jmbg: Optional[str] = None,
    telefon: Optional[str] = None,
    adresa: Optional[str] = None,
    datum_rodjenja: Optional[datetime] = None,
    odeljenje_id: Optional[int] = None,
    strucna_sprema_id: Optional[int] = None,
    radno_mesto_id: Optional[int] = None,
    status: Optional[str] = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Update a member."""
    result = await db.execute(select(Member).where(Member.id == member_id))
    member = result.scalar_one_or_none()

    if not member:
        raise HTTPException(status_code=404, detail="Član nije pronađen")

    # Check JMBG uniqueness
    if jmbg and jmbg != member.jmbg:
        existing = await db.execute(select(Member).where(Member.jmbg == jmbg))
        if existing.scalar_one_or_none():
            raise HTTPException(status_code=400, detail="Član sa ovim JMBG-om već postoji")
        member.jmbg = jmbg

    # Validate status
    if status:
        valid_statuses = ["aktivan", "neaktivan", "suspendovan"]
        if status not in valid_statuses:
            raise HTTPException(
                status_code=400,
                detail=f"Nevažeći status. Dozvoljeni: {', '.join(valid_statuses)}"
            )
        member.status = status

    # Update fields
    if ime is not None:
        member.ime = ime
    if prezime is not None:
        member.prezime = prezime
    if email is not None:
        member.email = email
    if telefon is not None:
        member.telefon = telefon
    if adresa is not None:
        member.adresa = adresa
    if datum_rodjenja is not None:
        member.datum_rodjenja = datum_rodjenja

    # Update foreign keys with validation
    if odeljenje_id is not None:
        if odeljenje_id:
            odeljenje = await db.execute(select(Odeljenje).where(Odeljenje.id == odeljenje_id))
            if not odeljenje.scalar_one_or_none():
                raise HTTPException(status_code=400, detail="Odeljenje nije pronađeno")
        member.odeljenje_id = odeljenje_id if odeljenje_id else None

    if strucna_sprema_id is not None:
        if strucna_sprema_id:
            sprema = await db.execute(select(StrucnaSprema).where(StrucnaSprema.id == strucna_sprema_id))
            if not sprema.scalar_one_or_none():
                raise HTTPException(status_code=400, detail="Stručna sprema nije pronađena")
        member.strucna_sprema_id = strucna_sprema_id if strucna_sprema_id else None

    if radno_mesto_id is not None:
        if radno_mesto_id:
            mesto = await db.execute(select(RadnoMesto).where(RadnoMesto.id == radno_mesto_id))
            if not mesto.scalar_one_or_none():
                raise HTTPException(status_code=400, detail="Radno mesto nije pronađeno")
        member.radno_mesto_id = radno_mesto_id if radno_mesto_id else None

    await db.commit()

    return {
        "id": member.id,
        "ime": member.ime,
        "prezime": member.prezime,
        "status": member.status,
        "message": "Član je ažuriran",
    }


@router.delete("/{member_id}")
async def delete_member(
    member_id: int,
    current_user: User = Depends(require_role("super_admin")),
    db: AsyncSession = Depends(get_db),
):
    """Delete (deactivate) a member."""
    result = await db.execute(select(Member).where(Member.id == member_id))
    member = result.scalar_one_or_none()

    if not member:
        raise HTTPException(status_code=404, detail="Član nije pronađen")

    # Soft delete - deactivate
    member.status = "neaktivan"
    await db.commit()

    return {"message": "Član je deaktiviran"}
