import { useState, useEffect } from 'react';
import { Link, useLocation, useNavigate, Outlet } from 'react-router-dom';
import {
  LayoutDashboard,
  Users,
  CreditCard,
  GraduationCap,
  BookOpen,
  Bell,
  Settings,
  Menu,
  X,
  LogOut,
  ChevronLeft,
  Search,
  User,
  Moon,
  Sun,
} from 'lucide-react';
import { useAuthStore } from '@/stores/authStore';
import { cn } from '@/lib/utils';
import api from '@/services/api';

const navigation = [
  { name: 'Dashboard', href: '/', icon: LayoutDashboard },
  { name: 'Članovi', href: '/members', icon: Users },
  { name: 'Članarina', href: '/clanarine', icon: CreditCard },
  { name: 'Edukacija', href: '/edukacije', icon: GraduationCap },
  { name: 'Baza znanja', href: '/documents', icon: BookOpen },
  { name: 'Notifikacije', href: '/notifications', icon: Bell },
];

const adminNavigation = [
  { name: 'Administracija', href: '/admin', icon: Settings },
];

export function MainLayout() {
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');
  const [unreadNotifications, setUnreadNotifications] = useState(0);
  const [isDarkMode, setIsDarkMode] = useState(false);
  const [showUserMenu, setShowUserMenu] = useState(false);

  const location = useLocation();
  const navigate = useNavigate();
  const { user, logout } = useAuthStore();

  const isAdmin = user?.role === 'super_admin' || user?.role === 'admin';

  useEffect(() => {
    loadNotificationCount();
  }, []);

  const loadNotificationCount = async () => {
    try {
      setUnreadNotifications(5);
    } catch (error) {
      console.error('Error loading notifications:', error);
    }
  };

  const handleLogout = async () => {
    try {
      await api.post('/auth/logout');
    } catch (error) {
      // Ignore errors
    }
    logout();
    navigate('/login');
  };

  const toggleDarkMode = () => {
    setIsDarkMode(!isDarkMode);
    document.documentElement.classList.toggle('dark');
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      console.log('Search:', searchQuery);
    }
  };

  return (
    <div className="flex h-screen bg-background">
      {/* Sidebar */}
      <aside
        className={cn(
          'flex flex-col border-r bg-card transition-all duration-300',
          sidebarOpen ? 'w-64' : 'w-16'
        )}
      >
        {/* Logo */}
        <div className="flex h-16 items-center justify-between border-b px-4">
          {sidebarOpen && (
            <Link to="/" className="flex items-center gap-2">
              <span className="text-xl font-bold text-primary">UZRJ</span>
            </Link>
          )}
          <button
            onClick={() => setSidebarOpen(!sidebarOpen)}
            className="rounded p-1 hover:bg-accent"
          >
            {sidebarOpen ? <ChevronLeft size={20} /> : <Menu size={20} />}
          </button>
        </div>

        {/* Navigation */}
        <nav className="flex-1 space-y-1 overflow-y-auto p-2">
          {navigation.map((item) => {
            const isActive = location.pathname === item.href;
            return (
              <Link
                key={item.name}
                to={item.href}
                className={cn(
                  'flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors',
                  isActive
                    ? 'bg-primary text-primary-foreground'
                    : 'hover:bg-accent'
                )}
                title={!sidebarOpen ? item.name : undefined}
              >
                <item.icon size={20} />
                {sidebarOpen && <span>{item.name}</span>}
                {item.name === 'Notifikacije' && unreadNotifications > 0 && (
                  <span className="ml-auto flex h-5 w-5 items-center justify-center rounded-full bg-destructive text-xs text-destructive-foreground">
                    {unreadNotifications}
                  </span>
                )}
              </Link>
            );
          })}

          {isAdmin && (
            <>
              <div className="my-2 border-t" />
              {adminNavigation.map((item) => {
                const isActive = location.pathname === item.href;
                return (
                  <Link
                    key={item.name}
                    to={item.href}
                    className={cn(
                      'flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors',
                      isActive
                        ? 'bg-primary text-primary-foreground'
                        : 'hover:bg-accent'
                    )}
                    title={!sidebarOpen ? item.name : undefined}
                  >
                    <item.icon size={20} />
                    {sidebarOpen && <span>{item.name}</span>}
                  </Link>
                );
              })}
            </>
          )}
        </nav>

        {/* User info */}
        <div className="border-t p-4">
          <div className="flex items-center justify-between">
            {sidebarOpen && (
              <div className="flex items-center gap-3 min-w-0">
                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-primary-foreground">
                  <User size={16} />
                </div>
                <div className="flex-1 min-w-0">
                  <p className="truncate text-sm font-medium">{user?.email}</p>
                  <p className="truncate text-xs text-muted-foreground capitalize">
                    {user?.role?.replace('_', ' ')}
                  </p>
                </div>
              </div>
            )}
            <button
              onClick={handleLogout}
              className="rounded p-2 hover:bg-accent"
              title="Odjavi se"
            >
              <LogOut size={20} />
            </button>
          </div>
        </div>
      </aside>

      {/* Main content */}
      <main className="flex flex-1 flex-col overflow-hidden">
        {/* Header */}
        <header className="flex h-16 items-center justify-between border-b bg-card px-6">
          {/* Search */}
          <form onSubmit={handleSearch} className="flex-1 max-w-md">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <input
                type="text"
                placeholder="Pretraži..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full rounded-lg border bg-background py-2 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
              />
            </div>
          </form>

          {/* Right side actions */}
          <div className="flex items-center gap-2">
            {/* Dark mode toggle */}
            <button
              onClick={toggleDarkMode}
              className="rounded-lg p-2 hover:bg-accent"
              title={isDarkMode ? 'Svetli režim' : 'Tamni režim'}
            >
              {isDarkMode ? <Sun size={20} /> : <Moon size={20} />}
            </button>

            {/* Notifications */}
            <Link
              to="/notifications"
              className="relative rounded-lg p-2 hover:bg-accent"
            >
              <Bell size={20} />
              {unreadNotifications > 0 && (
                <span className="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-destructive text-xs text-destructive-foreground">
                  {unreadNotifications}
                </span>
              )}
            </Link>

            {/* User menu */}
            <div className="relative">
              <button
                onClick={() => setShowUserMenu(!showUserMenu)}
                className="flex items-center gap-2 rounded-lg p-2 hover:bg-accent"
              >
                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-primary-foreground">
                  <User size={16} />
                </div>
              </button>

              {showUserMenu && (
                <div className="absolute right-0 top-full z-50 mt-2 w-48 rounded-lg border bg-card shadow-lg">
                  <div className="p-2">
                    <p className="px-3 py-2 text-sm font-medium">{user?.email}</p>
                    <p className="px-3 py-1 text-xs text-muted-foreground capitalize">
                      {user?.role?.replace('_', ' ')}
                    </p>
                  </div>
                  <div className="border-t p-1">
                    <Link
                      to="/profile"
                      className="flex items-center gap-2 rounded px-3 py-2 text-sm hover:bg-accent"
                      onClick={() => setShowUserMenu(false)}
                    >
                      <User size={16} />
                      Profil
                    </Link>
                    <Link
                      to="/settings"
                      className="flex items-center gap-2 rounded px-3 py-2 text-sm hover:bg-accent"
                      onClick={() => setShowUserMenu(false)}
                    >
                      <Settings size={16} />
                      Podešavanja
                    </Link>
                    <button
                      onClick={handleLogout}
                      className="flex w-full items-center gap-2 rounded px-3 py-2 text-sm hover:bg-accent"
                    >
                      <LogOut size={16} />
                      Odjavi se
                    </button>
                  </div>
                </div>
              )}
            </div>
          </div>
        </header>

        {/* Page content */}
        <div className="flex-1 overflow-auto p-6">
          <Outlet />
        </div>
      </main>
    </div>
  );
}
