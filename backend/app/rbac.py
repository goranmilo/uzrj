"""Role-Based Access Control (RBAC) configuration."""

from enum import Enum
from typing import Set


class Role(str, Enum):
    SUPER_ADMIN = "super_admin"
    ADMIN = "admin"
    MODERATOR = "moderator"
    CLAN = "clan"
    GUEST = "guest"


class Permission(str, Enum):
    # User management
    USER_CREATE = "user:create"
    USER_READ = "user:read"
    USER_UPDATE = "user:update"
    USER_DELETE = "user:delete"
    USER_MANAGE_ROLES = "user:manage_roles"

    # Members
    MEMBER_CREATE = "member:create"
    MEMBER_READ = "member:read"
    MEMBER_UPDATE = "member:update"
    MEMBER_DELETE = "member:delete"
    MEMBER_EXPORT = "member:export"

    # Članarina
    CLANARINA_CREATE = "clanarina:create"
    CLANARINA_READ = "clanarina:read"
    CLANARINA_UPDATE = "clanarina:update"
    CLANARINA_DELETE = "clanarina:delete"
    CLANARINA_OWN_READ = "clanarina:own_read"

    # Edukacija
    EDUKACIJA_CREATE = "edukacija:create"
    EDUKACIJA_READ = "edukacija:read"
    EDUKACIJA_UPDATE = "edukacija:update"
    EDUKACIJA_DELETE = "edukacija:delete"
    EDUKACIJA_REGISTER = "edukacija:register"

    # Documents
    DOCUMENT_CREATE = "document:create"
    DOCUMENT_READ = "document:read"
    DOCUMENT_UPDATE = "document:update"
    DOCUMENT_DELETE = "document:delete"
    DOCUMENT_PUBLISH = "document:publish"

    # Notifications
    NOTIFICATION_CREATE = "notification:create"
    NOTIFICATION_READ = "notification:read"
    NOTIFICATION_MANAGE = "notification:manage"

    # Email templates
    EMAIL_TEMPLATE_CREATE = "email_template:create"
    EMAIL_TEMPLATE_READ = "email_template:read"
    EMAIL_TEMPLATE_UPDATE = "email_template:update"
    EMAIL_TEMPLATE_DELETE = "email_template:delete"

    # Admin (organization)
    ADMIN_ODELJENJE = "admin:odeljenje"
    ADMIN_STRUCNA_SPREMA = "admin:strucna_sprema"
    ADMIN_RADNO_MESTO = "admin:radno_mesto"
    ADMIN_SETTINGS = "admin:settings"

    # Audit
    AUDIT_READ = "audit:read"

    # Dashboard
    DASHBOARD_VIEW = "dashboard:view"


# Role hierarchy (higher index = more permissions)
ROLE_HIERARCHY = {
    Role.GUEST: 0,
    Role.CLAN: 1,
    Role.MODERATOR: 2,
    Role.ADMIN: 3,
    Role.SUPER_ADMIN: 4,
}

# Define permissions for each role
_ROLE_PERMISSIONS: dict[Role, set[Permission]] = {
    Role.GUEST: {
        Permission.DOCUMENT_READ,
    },
    Role.CLAN: {
        Permission.USER_READ,
        Permission.CLANARINA_OWN_READ,
        Permission.EDUKACIJA_READ,
        Permission.DOCUMENT_READ,
        Permission.NOTIFICATION_READ,
        Permission.DASHBOARD_VIEW,
    },
    Role.MODERATOR: {
        Permission.USER_READ,
        Permission.CLANARINA_READ,
        Permission.EDUKACIJA_CREATE,
        Permission.EDUKACIJA_READ,
        Permission.EDUKACIJA_UPDATE,
        Permission.DOCUMENT_CREATE,
        Permission.DOCUMENT_READ,
        Permission.DOCUMENT_UPDATE,
        Permission.NOTIFICATION_CREATE,
        Permission.NOTIFICATION_READ,
        Permission.NOTIFICATION_MANAGE,
        Permission.DASHBOARD_VIEW,
    },
    Role.ADMIN: {
        Permission.USER_CREATE,
        Permission.USER_READ,
        Permission.USER_UPDATE,
        Permission.MEMBER_CREATE,
        Permission.MEMBER_READ,
        Permission.MEMBER_UPDATE,
        Permission.MEMBER_DELETE,
        Permission.MEMBER_EXPORT,
        Permission.CLANARINA_CREATE,
        Permission.CLANARINA_READ,
        Permission.CLANARINA_UPDATE,
        Permission.CLANARINA_DELETE,
        Permission.EDUKACIJA_CREATE,
        Permission.EDUKACIJA_READ,
        Permission.EDUKACIJA_UPDATE,
        Permission.EDUKACIJA_DELETE,
        Permission.DOCUMENT_CREATE,
        Permission.DOCUMENT_READ,
        Permission.DOCUMENT_UPDATE,
        Permission.DOCUMENT_DELETE,
        Permission.DOCUMENT_PUBLISH,
        Permission.NOTIFICATION_CREATE,
        Permission.NOTIFICATION_READ,
        Permission.NOTIFICATION_MANAGE,
        Permission.EMAIL_TEMPLATE_READ,
        Permission.EMAIL_TEMPLATE_UPDATE,
        Permission.ADMIN_ODELJENJE,
        Permission.ADMIN_STRUCNA_SPREMA,
        Permission.ADMIN_RADNO_MESTO,
        Permission.AUDIT_READ,
        Permission.DASHBOARD_VIEW,
    },
    Role.SUPER_ADMIN: set(Permission),  # All permissions
}


# Role hierarchy
_ROLE_HIERARCHY: dict[Role, list[Role]] = {
    Role.GUEST: [Role.GUEST],
    Role.CLAN: [Role.GUEST, Role.CLAN],
    Role.MODERATOR: [Role.GUEST, Role.CLAN, Role.MODERATOR],
    Role.ADMIN: [Role.GUEST, Role.CLAN, Role.MODERATOR, Role.ADMIN],
    Role.SUPER_ADMIN: [Role.GUEST, Role.CLAN, Role.MODERATOR, Role.ADMIN, Role.SUPER_ADMIN],
}


def get_permissions(role: Role) -> set[Permission]:
    """Get all permissions for a role."""
    return _ROLE_PERMISSIONS.get(role, set())


def has_permission(role: Role, permission: Permission) -> bool:
    """Check if a role has a specific permission."""
    return permission in get_permissions(role)


def require_any_permission(*permissions: Permission):
    """Decorator that checks if user has any of the specified permissions."""
    def decorator(func):
        func._required_permissions = permissions
        func._require_all = False
        return func
    return decorator


def require_all_permissions(*permissions: Permission):
    """Decorator that checks if user has all of the specified permissions."""
    def decorator(func):
        func._required_permissions = permissions
        func._require_all = True
        return func
    return decorator
