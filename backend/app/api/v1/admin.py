"""Admin API endpoints for organization management."""

from fastapi import APIRouter, Depends, HTTPException, status, Query
from sqlalchemy import select, func
from sqlalchemy.ext.asyncio import AsyncSession
from typing import Optional

from app.database import get_db
from app.models import User, Odeljenje, StrucnaSprema, RadnoMesto
from app.api.deps import get_current_user, require_role
from app.rbac import Permission

router = APIRouter()


# ============================================
# ODELJENJA
# ============================================

@router.get("/odeljenja")
async def list_odeljenja(
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=100),
    is_active: Optional[bool] = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """List all departments."""
    query = select(Odeljenje).order_by(Odeljenje.naziv)

    if is_active is not None:
        query = query.where(Odeljenje.is_active == is_active)

    result = await db.execute(query.offset(skip).limit(limit))
    odeljenja = result.scalars().all()

    return [
        {
            "id": o.id,
            "naziv": o.naziv,
            "opis": o.opis,
            "is_active": o.is_active,
            "created_at": o.created_at.isoformat() if o.created_at else None,
        }
        for o in odeljenja
    ]


@router.post("/odeljenja")
async def create_odeljenje(
    naziv: str,
    opis: Optional[str] = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Create a new department."""
    # Check if name exists
    result = await db.execute(select(Odeljenje).where(Odeljenje.naziv == naziv))
    if result.scalar_one_or_none():
        raise HTTPException(status_code=400, detail="Odeljenje sa ovim nazivom već postoji")

    odeljenje = Odeljenje(naziv=naziv, opis=opis, is_active=True)
    db.add(odeljenje)
    await db.commit()

    return {
        "id": odeljenje.id,
        "naziv": odeljenje.naziv,
        "opis": odeljenje.opis,
        "is_active": odeljenje.is_active,
        "message": "Odeljenje je kreirano",
    }


@router.get("/odeljenja/{odeljenje_id}")
async def get_odeljenje(
    odeljenje_id: int,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Get department details."""
    result = await db.execute(select(Odeljenje).where(Odeljenje.id == odeljenje_id))
    odeljenje = result.scalar_one_or_none()

    if not odeljenje:
        raise HTTPException(status_code=404, detail="Odeljenje nije pronađeno")

    return {
        "id": odeljenje.id,
        "naziv": odeljenje.naziv,
        "opis": odeljenje.opis,
        "is_active": odeljenje.is_active,
        "created_at": odeljenje.created_at.isoformat() if odeljenje.created_at else None,
    }


@router.put("/odeljenja/{odeljenje_id}")
async def update_odeljenje(
    odeljenje_id: int,
    naziv: Optional[str] = None,
    opis: Optional[str] = None,
    is_active: Optional[bool] = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Update a department."""
    result = await db.execute(select(Odeljenje).where(Odeljenje.id == odeljenje_id))
    odeljenje = result.scalar_one_or_none()

    if not odeljenje:
        raise HTTPException(status_code=404, detail="Odeljenje nije pronađeno")

    # Check if new name already exists
    if naziv and naziv != odeljenje.naziv:
        existing = await db.execute(select(Odeljenje).where(Odeljenje.naziv == naziv))
        if existing.scalar_one_or_none():
            raise HTTPException(status_code=400, detail="Odeljenje sa ovim nazivom već postoji")
        odeljenje.naziv = naziv

    if opis is not None:
        odeljenje.opis = opis
    if is_active is not None:
        odeljenje.is_active = is_active

    await db.commit()

    return {
        "id": odeljenje.id,
        "naziv": odeljenje.naziv,
        "opis": odeljenje.opis,
        "is_active": odeljenje.is_active,
        "message": "Odeljenje je ažurirano",
    }


@router.delete("/odeljenja/{odeljenje_id}")
async def delete_odeljenje(
    odeljenje_id: int,
    current_user: User = Depends(require_role("super_admin")),
    db: AsyncSession = Depends(get_db),
):
    """Delete (deactivate) a department."""
    result = await db.execute(select(Odeljenje).where(Odeljenje.id == odeljenje_id))
    odeljenje = result.scalar_one_or_none()

    if not odeljenje:
        raise HTTPException(status_code=404, detail="Odeljenje nije pronađeno")

    # Soft delete - just deactivate
    odeljenje.is_active = False
    await db.commit()

    return {"message": "Odeljenje je deaktivirano"}


# ============================================
# STRUČNE SPREME
# ============================================

@router.get("/strucne-spreme")
async def list_strucne_spreme(
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=100),
    is_active: Optional[bool] = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """List all professional qualifications."""
    query = select(StrucnaSprema).order_by(StrucnaSprema.nivo, StrucnaSprema.naziv)

    if is_active is not None:
        query = query.where(StrucnaSprema.is_active == is_active)

    result = await db.execute(query.offset(skip).limit(limit))
    spreme = result.scalars().all()

    return [
        {
            "id": s.id,
            "naziv": s.naziv,
            "nivo": s.nivo,
            "is_active": s.is_active,
            "created_at": s.created_at.isoformat() if s.created_at else None,
        }
        for s in spreme
    ]


@router.post("/strucne-spreme")
async def create_strucna_sprema(
    naziv: str,
    nivo: Optional[int] = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Create a new professional qualification."""
    # Check if name exists
    result = await db.execute(select(StrucnaSprema).where(StrucnaSprema.naziv == naziv))
    if result.scalar_one_or_none():
        raise HTTPException(status_code=400, detail="Stručna sprema sa ovim nazivom već postoji")

    sprema = StrucnaSprema(naziv=naziv, nivo=nivo, is_active=True)
    db.add(sprema)
    await db.commit()

    return {
        "id": sprema.id,
        "naziv": sprema.naziv,
        "nivo": sprema.nivo,
        "is_active": sprema.is_active,
        "message": "Stručna sprema je kreirana",
    }


@router.get("/strucne-spreme/{sprema_id}")
async def get_strucna_sprema(
    sprema_id: int,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Get professional qualification details."""
    result = await db.execute(select(StrucnaSprema).where(StrucnaSprema.id == sprema_id))
    sprema = result.scalar_one_or_none()

    if not sprema:
        raise HTTPException(status_code=404, detail="Stručna sprema nije pronađena")

    return {
        "id": sprema.id,
        "naziv": sprema.naziv,
        "nivo": sprema.nivo,
        "is_active": sprema.is_active,
        "created_at": sprema.created_at.isoformat() if sprema.created_at else None,
    }


@router.put("/strucne-spreme/{sprema_id}")
async def update_strucna_sprema(
    sprema_id: int,
    naziv: Optional[str] = None,
    nivo: Optional[int] = None,
    is_active: Optional[bool] = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Update a professional qualification."""
    result = await db.execute(select(StrucnaSprema).where(StrucnaSprema.id == sprema_id))
    sprema = result.scalar_one_or_none()

    if not sprema:
        raise HTTPException(status_code=404, detail="Stručna sprema nije pronađena")

    # Check if new name already exists
    if naziv and naziv != sprema.naziv:
        existing = await db.execute(select(StrucnaSprema).where(StrucnaSprema.naziv == naziv))
        if existing.scalar_one_or_none():
            raise HTTPException(status_code=400, detail="Stručna sprema sa ovim nazivom već postoji")
        sprema.naziv = naziv

    if nivo is not None:
        sprema.nivo = nivo
    if is_active is not None:
        sprema.is_active = is_active

    await db.commit()

    return {
        "id": sprema.id,
        "naziv": sprema.naziv,
        "nivo": sprema.nivo,
        "is_active": sprema.is_active,
        "message": "Stručna sprema je ažurirana",
    }


@router.delete("/strucne-spreme/{sprema_id}")
async def delete_strucna_sprema(
    sprema_id: int,
    current_user: User = Depends(require_role("super_admin")),
    db: AsyncSession = Depends(get_db),
):
    """Delete (deactivate) a professional qualification."""
    result = await db.execute(select(StrucnaSprema).where(StrucnaSprema.id == sprema_id))
    sprema = result.scalar_one_or_none()

    if not sprema:
        raise HTTPException(status_code=404, detail="Stručna sprema nije pronađena")

    sprema.is_active = False
    await db.commit()

    return {"message": "Stručna sprema je deaktivirana"}


# ============================================
# RADNA MESTA
# ============================================

@router.get("/radna-mesta")
async def list_radna_mesta(
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=100),
    is_active: Optional[bool] = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """List all job positions."""
    query = select(RadnoMesto).order_by(RadnoMesto.naziv)

    if is_active is not None:
        query = query.where(RadnoMesto.is_active == is_active)

    result = await db.execute(query.offset(skip).limit(limit))
    mesta = result.scalars().all()

    return [
        {
            "id": m.id,
            "naziv": m.naziv,
            "opis": m.opis,
            "is_active": m.is_active,
            "created_at": m.created_at.isoformat() if m.created_at else None,
        }
        for m in mesta
    ]


@router.post("/radna-mesta")
async def create_radno_mesto(
    naziv: str,
    opis: Optional[str] = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Create a new job position."""
    # Check if name exists
    result = await db.execute(select(RadnoMesto).where(RadnoMesto.naziv == naziv))
    if result.scalar_one_or_none():
        raise HTTPException(status_code=400, detail="Radno mesto sa ovim nazivom već postoji")

    mesto = RadnoMesto(naziv=naziv, opis=opis, is_active=True)
    db.add(mesto)
    await db.commit()

    return {
        "id": mesto.id,
        "naziv": mesto.naziv,
        "opis": mesto.opis,
        "is_active": mesto.is_active,
        "message": "Radno mesto je kreirano",
    }


@router.get("/radna-mesta/{mesto_id}")
async def get_radno_mesto(
    mesto_id: int,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Get job position details."""
    result = await db.execute(select(RadnoMesto).where(RadnoMesto.id == mesto_id))
    mesto = result.scalar_one_or_none()

    if not mesto:
        raise HTTPException(status_code=404, detail="Radno mesto nije pronađeno")

    return {
        "id": mesto.id,
        "naziv": mesto.naziv,
        "opis": mesto.opis,
        "is_active": mesto.is_active,
        "created_at": mesto.created_at.isoformat() if mesto.created_at else None,
    }


@router.put("/radna-mesta/{mesto_id}")
async def update_radno_mesto(
    mesto_id: int,
    naziv: Optional[str] = None,
    opis: Optional[str] = None,
    is_active: Optional[bool] = None,
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Update a job position."""
    result = await db.execute(select(RadnoMesto).where(RadnoMesto.id == mesto_id))
    mesto = result.scalar_one_or_none()

    if not mesto:
        raise HTTPException(status_code=404, detail="Radno mesto nije pronađeno")

    # Check if new name already exists
    if naziv and naziv != mesto.naziv:
        existing = await db.execute(select(RadnoMesto).where(RadnoMesto.naziv == naziv))
        if existing.scalar_one_or_none():
            raise HTTPException(status_code=400, detail="Radno mesto sa ovim nazivom već postoji")
        mesto.naziv = naziv

    if opis is not None:
        mesto.opis = opis
    if is_active is not None:
        mesto.is_active = is_active

    await db.commit()

    return {
        "id": mesto.id,
        "naziv": mesto.naziv,
        "opis": mesto.opis,
        "is_active": mesto.is_active,
        "message": "Radno mesto je ažurirano",
    }


@router.delete("/radna-mesta/{mesto_id}")
async def delete_radno_mesto(
    mesto_id: int,
    current_user: User = Depends(require_role("super_admin")),
    db: AsyncSession = Depends(get_db),
):
    """Delete (deactivate) a job position."""
    result = await db.execute(select(RadnoMesto).where(RadnoMesto.id == mesto_id))
    mesto = result.scalar_one_or_none()

    if not mesto:
        raise HTTPException(status_code=404, detail="Radno mesto nije pronađeno")

    mesto.is_active = False
    await db.commit()

    return {"message": "Radno mesto je deaktivirano"}


# ============================================
# STATISTICS
# ============================================

@router.get("/stats")
async def get_admin_stats(
    current_user: User = Depends(require_role("super_admin", "admin")),
    db: AsyncSession = Depends(get_db),
):
    """Get admin statistics."""
    # Count active odeljenja
    odeljenja_result = await db.execute(
        select(func.count(Odeljenje.id)).where(Odeljenje.is_active == True)
    )
    odeljenja_count = odeljenja_result.scalar() or 0

    # Count active strucne spreme
    spreme_result = await db.execute(
        select(func.count(StrucnaSprema.id)).where(StrucnaSprema.is_active == True)
    )
    spreme_count = spreme_result.scalar() or 0

    # Count active radna mesta
    mesta_result = await db.execute(
        select(func.count(RadnoMesto.id)).where(RadnoMesto.is_active == True)
    )
    mesta_count = mesta_result.scalar() or 0

    return {
        "odeljenja": odeljenja_count,
        "strucne_spreme": spreme_count,
        "radna_mesta": mesta_count,
    }
