"""Seed initial data for UZRJ application."""
import asyncio
from datetime import datetime, timezone

from app.database import async_session, engine, Base
from app.models import User, Odeljenje, StrucnaSprema, RadnoMesto
from app.api.deps import get_password_hash


async def seed():
    """Create initial data."""
    async with engine.begin() as conn:
        await conn.run_sync(Base.metadata.create_all)

    async with async_session() as session:
        # Check if admin already exists
        from sqlalchemy import select
        result = await session.execute(select(User).where(User.email == "admin@uzrj.rs"))
        if result.scalar_one_or_none():
            print("Admin user already exists. Skipping seed.")
            return

        # Create admin user
        admin = User(
            email="admin@uzrj.rs",
            hashed_password=get_password_hash("admin123"),
            role="super_admin",
            is_active=True,
        )
        session.add(admin)

        # Create default odeljenja
        odeljenja = [
            Odeljenje(naziv="Opšta medicina", opis="Opšta medicinska praksa"),
            Odeljenje(naziv="Hirurgija", opis="Hirurško odeljenje"),
            Odeljenje(naziv="Pediatrija", opis="Dečije odeljenje"),
            Odeljenje(naziv="Ginekologija", opis="Ginekološko odeljenje"),
            Odeljenje(naziv="Radiologija", opis="Radiološko odeljenje"),
            Odeljenje(naziv="Laboratorija", opis="Laboratorijska dijagnostika"),
        ]
        session.add_all(odeljenja)

        # Create default strucne spreme
        strucne_spreme = [
            StrucnaSprema(naziv="SSS - Medicinska sestra/tehničar", nivo=1),
            StrucnaSprema(naziv="VŠ - Viša medicinska škola", nivo=2),
            StrucnaSprema(naziv="VSS - Medicinski fakultet", nivo=3),
            StrucnaSprema(naziv="Master - Specijalizacija", nivo=4),
            StrucnaSprema(naziv="Doktor nauka", nivo=5),
        ]
        session.add_all(strucne_spreme)

        # Create default radna mesta
        radna_mesta = [
            RadnoMesto(naziv="Medicinska sestra", opis="Opšte sestrinske dužnosti"),
            RadnoMesto(naziv="Viša medicinska sestra", opis="Napredne sestrinske dužnosti"),
            RadnoMesto(naziv="Glavna sestra", opis="Rukovodilac sestrinskog tima"),
            RadnoMesto(naziv="Lekar opšte prakse", opis="Opšta medicinska praksa"),
            RadnoMesto(naziv="Specijalista", opis="Specijalistička praksa"),
            RadnoMesto(naziv="Direktor", opis="Rukovodilac ustanove"),
        ]
        session.add_all(radna_mesta)

        await session.commit()
        print("Seed data created successfully!")
        print(f"Admin: admin@uzrj.rs / admin123")


if __name__ == "__main__":
    asyncio.run(seed())
