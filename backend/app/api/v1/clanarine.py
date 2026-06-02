"""Clanarine API endpoints."""

from datetime import datetime, timezone, timedelta
from typing import Optional

from fastapi import APIRouter, Depends, HTTPException, status, Query
from sqlalchemy import select, func, and_, or_
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.orm import selectinload

from app.database import get_db
from app.models import User, Member, Clanarina
from app.api.deps import get_current_user, require_role
from app.services.email import EmailService

router = APIRouter()


@router.get("/")
async def list_clanarine(
    page: int = Query(1, ge=1),
    per_page: int = Query(20, ge=1, le=100),
    member_id: Optional[int] = None,
    status: Optional[str] = None,
    period_od: Optional[datetime] = None,
    period_do: Optional[datetime] = None,
    sort_by: str = Query("created_at", regex="^(iznos|datum_uplate|period_do|status|created_at)$"),
    sort_order: str = Query("desc", regex="^(asc|desc)$"),
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """List clanarine with pagination and filters."""
    query = select(Clanarina).options(
        selectinload(Clanarina.member)
    )

    # Apply filters
    if member_id:
        query = query.where(Clanarina.member_id == member_id)

    if status:
        query = query.where(Clanarina.status == status)

    if period_od:
        query = query.where(Clanarina.period_od >= period_od)

    if period_do:
        query = query.where(Clanarina.period_do <= period_do)

    # Get total count
    count_query = select(func.count(Clanarina.id))
    if member_id:
        count_query = count_query.where(Clanarina.member_id == member_id)
    if status:
        count_query = count_query.where(Clanarina.status == status)
    if period_od:
        count_query = count_query.where(Clanarina.period_od >= period_od)
    if period_do:
        count_query = count_query.where(Clanarina.period_do <= period_do)

    total_result = await db.execute(count_query)
    total = total_result.scalar() or 0

    # Apply sorting
    sort_column = getattr(Clanarina, sort_by, Clanarina.created_at)
    if sort_order == "desc":
        query = query.order_by(sort_column.desc())
    else:
        query = query.order_by(sort_column.asc())

    # Apply pagination
    offset = (page - 1) * per_page
    query = query.offset(offset).limit(per_page)

    # Execute query
    result = await db.execute(query)
    clanarine = result.scalars().all()

    return {
        "items": [
            {
                "id": c.id,
                "member": {
                    "id": c.member.id,
                    "ime": c.member.ime,
                    "prezime": c.member.prezime,
                    "email": c.member.email,
                } if c.member else None,
                "iznos": float(c.iznos),
                "valuta": c.valuta,
                "period_od": c.period_od.isoformat() if c.period_od else None,
                "period_do": c.period_do.isoformat() if c.period_do else None,
                "datum_uplate": c.datum_uplate.isoformat() if c.datum_uplate else None,
                "nacin_placanja": c.nacin_placanja,
                "status": c.status,
                "broj_uplatnice": c.broj_uplatnice,
                "napomena": c.napomena,
                "created_at": c.created_at.isoformat() if c.created_at else None,
            }
            for c in clanarine
        ],
        "total": total,
        "page": page,
        "per_page": per_page,
        "pages": (total + per_page - 1) // per_page,
    }


@router.post("/")
async def create_clanarina(
    member_id: int,
    iznos: float,
    period_od: datetime,
    period_do: datetime,
    valuta: str = "RSD",
    nacin_placanja: Optional[str] = None,
    broj_uplatnice: Optional[str] = None,
    napomena: Optional[str] = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Create a new clanarina."""
    # Check if member exists
    result = await db.execute(select(Member).where(Member.id == member_id))
    member = result.scalar_one_or_none()

    if not member:
        raise HTTPException(status_code=404, detail="Član nije pronađen")

    # Create clanarina
    clanarina = Clanarina(
        member_id=member_id,
        iznos=iznos,
        valuta=valuta,
        period_od=period_od,
        period_do=period_do,
        datum_uplate=datetime.now(timezone.utc) if nacin_placanja else None,
        nacin_placanja=nacin_placanja,
        status="placeno" if nacin_placanja else "neplaceno",
        broj_uplatnice=broj_uplatnice,
        napomena=napomena,
    )
    db.add(clanarina)
    await db.commit()
    await db.refresh(clanarina)

    return {
        "id": clanarina.id,
        "member_id": clanarina.member_id,
        "iznos": float(clanarina.iznos),
        "status": clanarina.status,
        "message": "Članarina je kreirana",
    }


@router.get("/stats")
async def get_clanarine_stats(
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Get clanarine statistics."""
    # Total clanarine
    total_result = await db.execute(select(func.count(Clanarina.id)))
    total = total_result.scalar() or 0

    # By status
    status_result = await db.execute(
        select(Clanarina.status, func.count(Clanarina.id))
        .group_by(Clanarina.status)
    )
    by_status = {row[0]: row[1] for row in status_result.all()}

    # Total amount
    amount_result = await db.execute(
        select(func.sum(Clanarina.iznos))
        .where(Clanarina.status == "placeno")
    )
    total_amount = float(amount_result.scalar() or 0)

    # Overdue (neplaćene sa prošlim period_do)
    now = datetime.now(timezone.utc)
    overdue_result = await db.execute(
        select(func.count(Clanarina.id))
        .where(
            and_(
                Clanarina.status == "neplaceno",
                Clanarina.period_do < now,
            )
        )
    )
    overdue = overdue_result.scalar() or 0

    # Due soon (next 30 days)
    thirty_days = now + timedelta(days=30)
    due_soon_result = await db.execute(
        select(func.count(Clanarina.id))
        .where(
            and_(
                Clanarina.status == "neplaceno",
                Clanarina.period_do >= now,
                Clanarina.period_do <= thirty_days,
            )
        )
    )
    due_soon = due_soon_result.scalar() or 0

    # This month revenue
    month_start = now.replace(day=1, hour=0, minute=0, second=0, microsecond=0)
    month_result = await db.execute(
        select(func.sum(Clanarina.iznos))
        .where(
            and_(
                Clanarina.status == "placeno",
                Clanarina.datum_uplate >= month_start,
            )
        )
    )
    month_revenue = float(month_result.scalar() or 0)

    return {
        "total": total,
        "by_status": by_status,
        "total_amount": total_amount,
        "overdue": overdue,
        "due_soon": due_soon,
        "month_revenue": month_revenue,
    }


@router.get("/overdue")
async def get_overdue_clanarine(
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Get overdue (unpaid with past deadline) clanarine."""
    now = datetime.now(timezone.utc)

    result = await db.execute(
        select(Clanarina)
        .options(selectinload(Clanarina.member))
        .where(
            and_(
                Clanarina.status == "neplaceno",
                Clanarina.period_do < now,
            )
        )
        .order_by(Clanarina.period_do.asc())
    )
    clanarine = result.scalars().all()

    return [
        {
            "id": c.id,
            "member": {
                "id": c.member.id,
                "ime": c.member.ime,
                "prezime": c.member.prezime,
                "email": c.member.email,
            } if c.member else None,
            "iznos": float(c.iznos),
            "period_od": c.period_od.isoformat(),
            "period_do": c.period_do.isoformat(),
            "days_overdue": (now - c.period_do).days,
        }
        for c in clanarine
    ]


@router.get("/member/{member_id}")
async def get_member_clanarine(
    member_id: int,
    current_user: User = Depends(require_role("super_admin", "admin", "clan")),
    db: AsyncSession = Depends(get_db),
):
    """Get clanarine for a specific member."""
    # Check if member exists
    result = await db.execute(select(Member).where(Member.id == member_id))
    member = result.scalar_one_or_none()

    if not member:
        raise HTTPException(status_code=404, detail="Član nije pronađen")

    # If user is clan, can only see own clanarine
    if current_user.role == "clan":
        if member.user_id != current_user.id:
            raise HTTPException(status_code=403, detail="Nemate pristup ovim podacima")

    result = await db.execute(
        select(Clanarina)
        .where(Clanarina.member_id == member_id)
        .order_by(Clanarina.period_do.desc())
    )
    clanarine = result.scalars().all()

    return [
        {
            "id": c.id,
            "iznos": float(c.iznos),
            "valuta": c.valuta,
            "period_od": c.period_od.isoformat(),
            "period_do": c.period_do.isoformat(),
            "datum_uplate": c.datum_uplate.isoformat() if c.datum_uplate else None,
            "nacin_placanja": c.nacin_placanja,
            "status": c.status,
        }
        for c in clanarine
    ]


@router.get("/{clanarina_id}")
async def get_clanarina(
    clanarina_id: int,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Get clanarina details."""
    result = await db.execute(
        select(Clanarina)
        .options(selectinload(Clanarina.member))
        .where(Clanarina.id == clanarina_id)
    )
    clanarina = result.scalar_one_or_none()

    if not clanarina:
        raise HTTPException(status_code=404, detail="Članarina nije pronađena")

    return {
        "id": clanarina.id,
        "member": {
            "id": clanarina.member.id,
            "ime": clanarina.member.ime,
            "prezime": clanarina.member.prezime,
            "email": clanarina.member.email,
        } if clanarina.member else None,
        "iznos": float(clanarina.iznos),
        "valuta": clanarina.valuta,
        "period_od": clanarina.period_od.isoformat(),
        "period_do": clanarina.period_do.isoformat(),
        "datum_uplate": clanarina.datum_uplate.isoformat() if clanarina.datum_uplate else None,
        "nacin_placanja": clanarina.nacin_placanja,
        "status": clanarina.status,
        "broj_uplatnice": clanarina.broj_uplatnice,
        "napomena": clanarina.napomena,
        "created_at": clanarina.created_at.isoformat() if clanarina.created_at else None,
        "updated_at": clanarina.updated_at.isoformat() if clanarina.updated_at else None,
    }


@router.put("/{clanarina_id}")
async def update_clanarina(
    clanarina_id: int,
    iznos: Optional[float] = None,
    nacin_placanja: Optional[str] = None,
    broj_uplatnice: Optional[str] = None,
    napomena: Optional[str] = None,
    status: Optional[str] = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Update a clanarina."""
    result = await db.execute(select(Clanarina).where(Clanarina.id == clanarina_id))
    clanarina = result.scalar_one_or_none()

    if not clanarina:
        raise HTTPException(status_code=404, detail="Članarina nije pronađena")

    # Validate status
    if status:
        valid_statuses = ["placeno", "neplaceno", "delimicno"]
        if status not in valid_statuses:
            raise HTTPException(
                status_code=400,
                detail=f"Nevažeći status. Dozvoljeni: {', '.join(valid_statuses)}"
            )
        clanarina.status = status

        # Set datum_uplate if marked as paid
        if status == "placeno" and not clanarina.datum_uplate:
            clanarina.datum_uplate = datetime.now(timezone.utc)

    # Update fields
    if iznos is not None:
        clanarina.iznos = iznos
    if nacin_placanja is not None:
        clanarina.nacin_placanja = nacin_placanja
    if broj_uplatnice is not None:
        clanarina.broj_uplatnice = broj_uplatnice
    if napomena is not None:
        clanarina.napomena = napomena

    await db.commit()

    return {
        "id": clanarina.id,
        "status": clanarina.status,
        "message": "Članarina je ažurirana",
    }


@router.post("/{clanarina_id}/mark-paid")
async def mark_as_paid(
    clanarina_id: int,
    nacin_placanja: str = "uplatnica",
    broj_uplatnice: Optional[str] = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Mark clanarina as paid."""
    result = await db.execute(
        select(Clanarina)
        .options(selectinload(Clanarina.member))
        .where(Clanarina.id == clanarina_id)
    )
    clanarina = result.scalar_one_or_none()

    if not clanarina:
        raise HTTPException(status_code=404, detail="Članarina nije pronađena")

    if clanarina.status == "placeno":
        raise HTTPException(status_code=400, detail="Članarina je već plaćena")

    clanarina.status = "placeno"
    clanarina.datum_uplate = datetime.now(timezone.utc)
    clanarina.nacin_placanja = nacin_placanja
    clanarina.broj_uplatnice = broj_uplatnice

    await db.commit()

    return {
        "id": clanarina.id,
        "status": clanarina.status,
        "datum_uplate": clanarina.datum_uplate.isoformat(),
        "message": "Članarina je označena kao plaćena",
    }


@router.delete("/{clanarina_id}")
async def delete_clanarina(
    clanarina_id: int,
    current_user: User = Depends(require_role("super_admin")),
    db: AsyncSession = Depends(get_db),
):
    """Delete a clanarina."""
    result = await db.execute(select(Clanarina).where(Clanarina.id == clanarina_id))
    clanarina = result.scalar_one_or_none()

    if not clanarina:
        raise HTTPException(status_code=404, detail="Članarina nije pronađena")

    await db.delete(clanarina)
    await db.commit()

    return {"message": "Članarina je obrisana"}


@router.post("/send-reminders")
async def send_reminders(
    days_before: int = Query(7, ge=1, le=30),
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Send reminders for upcoming clanarine deadlines."""
    now = datetime.now(timezone.utc)
    deadline = now + timedelta(days=days_before)

    # Find unpaid clanarine due within deadline
    result = await db.execute(
        select(Clanarina)
        .options(selectinload(Clanarina.member))
        .where(
            and_(
                Clanarina.status == "neplaceno",
                Clanarina.period_do >= now,
                Clanarina.period_do <= deadline,
            )
        )
    )
    clanarine = result.scalars().all()

    # Send reminders
    email_service = EmailService(db)
    sent_count = 0
    errors = []

    for clanarina in clanarine:
        if clanarina.member and clanarina.member.email:
            try:
                await email_service.send_clanarina_reminder(
                    recipient=clanarina.member.email,
                    ime=clanarina.member.ime,
                    prezime=clanarina.member.prezime,
                    iznos=float(clanarina.iznos),
                    period_do=clanarina.period_do,
                )
                sent_count += 1
            except Exception as e:
                errors.append({
                    "member": f"{clanarina.member.ime} {clanarina.member.prezime}",
                    "error": str(e),
                })

    return {
        "total": len(clanarine),
        "sent": sent_count,
        "errors": errors,
        "message": f"Poslato {sent_count} podsetnika",
    }
