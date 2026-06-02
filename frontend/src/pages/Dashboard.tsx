import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
  Users,
  CreditCard,
  GraduationCap,
  BookOpen,
  Bell,
  TrendingUp,
  Calendar,
  FileText,
  ArrowUpRight,
  Activity,
} from 'lucide-react';
import { useAuthStore } from '@/stores/authStore';
import api from '@/services/api';

interface DashboardStats {
  total_members: number;
  active_members: number;
  paid_clanarine: number;
  unpaid_clanarine: number;
  upcoming_edukacije: number;
  total_documents: number;
  unread_notifications: number;
}

interface RecentActivity {
  id: number;
  type: string;
  description: string;
  timestamp: string;
  user?: string;
}

export function DashboardPage() {
  const { user } = useAuthStore();
  const [stats, setStats] = useState<DashboardStats>({
    total_members: 0,
    active_members: 0,
    paid_clanarine: 0,
    unpaid_clanarine: 0,
    upcoming_edukacije: 0,
    total_documents: 0,
    unread_notifications: 0,
  });
  const [recentActivity, setRecentActivity] = useState<RecentActivity[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    loadDashboardData();
  }, []);

  const loadDashboardData = async () => {
    try {
      // In production, these would be real API calls
      // For now, we'll use mock data
      setStats({
        total_members: 156,
        active_members: 142,
        paid_clanarine: 98,
        unpaid_clanarine: 44,
        upcoming_edukacije: 3,
        total_documents: 47,
        unread_notifications: 5,
      });

      setRecentActivity([
        {
          id: 1,
          type: 'member',
          description: 'Novi član: Marko Marković',
          timestamp: '2026-06-02T10:30:00',
          user: 'Admin',
        },
        {
          id: 2,
          type: 'clanarina',
          description: 'Uplata članarine: Jovan Jovanović',
          timestamp: '2026-06-02T09:15:00',
        },
        {
          id: 3,
          type: 'edukacija',
          description: 'Nova edukacija: Prva pomoć',
          timestamp: '2026-06-01T14:00:00',
          user: 'Moderator',
        },
        {
          id: 4,
          type: 'document',
          description: 'Novi dokument: Pravilnik o radu',
          timestamp: '2026-06-01T11:30:00',
        },
      ]);
    } catch (error) {
      console.error('Error loading dashboard:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const statCards = [
    {
      title: 'Ukupno članova',
      value: stats.total_members,
      subtitle: `${stats.active_members} aktivnih`,
      icon: Users,
      href: '/members',
      color: 'text-blue-600',
      bgColor: 'bg-blue-100',
    },
    {
      title: 'Članarine',
      value: stats.paid_clanarine,
      subtitle: `${stats.unpaid_clanarine} neplaćenih`,
      icon: CreditCard,
      href: '/clanarine',
      color: 'text-green-600',
      bgColor: 'bg-green-100',
    },
    {
      title: 'Edukacije',
      value: stats.upcoming_edukacije,
      subtitle: 'predstojeće',
      icon: GraduationCap,
      href: '/edukacije',
      color: 'text-purple-600',
      bgColor: 'bg-purple-100',
    },
    {
      title: 'Dokumenti',
      value: stats.total_documents,
      subtitle: 'u bazi znanja',
      icon: BookOpen,
      href: '/documents',
      color: 'text-orange-600',
      bgColor: 'bg-orange-100',
    },
  ];

  const getActivityIcon = (type: string) => {
    switch (type) {
      case 'member':
        return <Users className="h-4 w-4" />;
      case 'clanarina':
        return <CreditCard className="h-4 w-4" />;
      case 'edukacija':
        return <GraduationCap className="h-4 w-4" />;
      case 'document':
        return <FileText className="h-4 w-4" />;
      default:
        return <Activity className="h-4 w-4" />;
    }
  };

  const formatTime = (timestamp: string) => {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    if (diffHours < 1) {
      const diffMinutes = Math.floor(diffMs / (1000 * 60));
      return `pre ${diffMinutes} min`;
    } else if (diffHours < 24) {
      return `pre ${diffHours}h`;
    } else if (diffDays < 7) {
      return `pre ${diffDays}d`;
    } else {
      return date.toLocaleDateString('sr-RS');
    }
  };

  if (isLoading) {
    return (
      <div className="flex h-64 items-center justify-center">
        <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold">Dashboard</h1>
          <p className="text-muted-foreground">
            Dobrodošli, {user?.email}
          </p>
        </div>
        <div className="flex items-center gap-2">
          <Link
            to="/notifications"
            className="relative rounded-full p-2 hover:bg-accent"
          >
            <Bell className="h-5 w-5" />
            {stats.unread_notifications > 0 && (
              <span className="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-destructive text-xs text-destructive-foreground">
                {stats.unread_notifications}
              </span>
            )}
          </Link>
        </div>
      </div>

      {/* Stats Grid */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        {statCards.map((card) => (
          <Link
            key={card.title}
            to={card.href}
            className="group rounded-lg border bg-card p-6 shadow-sm transition-all hover:shadow-md"
          >
            <div className="flex items-center justify-between">
              <div className={`rounded-lg p-2 ${card.bgColor}`}>
                <card.icon className={`h-6 w-6 ${card.color}`} />
              </div>
              <ArrowUpRight className="h-4 w-4 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100" />
            </div>
            <div className="mt-4">
              <p className="text-sm font-medium text-muted-foreground">
                {card.title}
              </p>
              <p className="mt-1 text-3xl font-bold">{card.value}</p>
              <p className="mt-1 text-sm text-muted-foreground">
                {card.subtitle}
              </p>
            </div>
          </Link>
        ))}
      </div>

      {/* Main Content Grid */}
      <div className="grid gap-6 lg:grid-cols-2">
        {/* Recent Activity */}
        <div className="rounded-lg border bg-card p-6 shadow-sm">
          <div className="flex items-center justify-between">
            <h2 className="text-lg font-semibold">Poslednje aktivnosti</h2>
            <Link
              to="/notifications"
              className="text-sm text-primary hover:underline"
            >
              Vidi sve
            </Link>
          </div>
          <div className="mt-4 space-y-4">
            {recentActivity.map((activity) => (
              <div
                key={activity.id}
                className="flex items-start gap-3 rounded-lg p-3 transition-colors hover:bg-accent"
              >
                <div className="mt-1 rounded-full bg-muted p-2">
                  {getActivityIcon(activity.type)}
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium">{activity.description}</p>
                  <div className="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                    <Calendar className="h-3 w-3" />
                    <span>{formatTime(activity.timestamp)}</span>
                    {activity.user && (
                      <>
                        <span>•</span>
                        <span>{activity.user}</span>
                      </>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Quick Actions */}
        <div className="rounded-lg border bg-card p-6 shadow-sm">
          <h2 className="text-lg font-semibold">Brze akcije</h2>
          <div className="mt-4 grid grid-cols-2 gap-3">
            <Link
              to="/members?action=add"
              className="flex flex-col items-center gap-2 rounded-lg border p-4 transition-colors hover:bg-accent"
            >
              <Users className="h-6 w-6 text-primary" />
              <span className="text-sm font-medium">Dodaj člana</span>
            </Link>
            <Link
              to="/clanarine?action=add"
              className="flex flex-col items-center gap-2 rounded-lg border p-4 transition-colors hover:bg-accent"
            >
              <CreditCard className="h-6 w-6 text-primary" />
              <span className="text-sm font-medium">Evidentiraj uplatu</span>
            </Link>
            <Link
              to="/edukacije?action=add"
              className="flex flex-col items-center gap-2 rounded-lg border p-4 transition-colors hover:bg-accent"
            >
              <GraduationCap className="h-6 w-6 text-primary" />
              <span className="text-sm font-medium">Nova edukacija</span>
            </Link>
            <Link
              to="/documents?action=add"
              className="flex flex-col items-center gap-2 rounded-lg border p-4 transition-colors hover:bg-accent"
            >
              <BookOpen className="h-6 w-6 text-primary" />
              <span className="text-sm font-medium">Novi dokument</span>
            </Link>
          </div>

          {/* Upcoming Events */}
          <div className="mt-6">
            <h3 className="text-sm font-semibold text-muted-foreground">
              Predstojeći događaji
            </h3>
            <div className="mt-3 space-y-3">
              <div className="flex items-center gap-3 rounded-lg border p-3">
                <div className="flex h-10 w-10 flex-col items-center justify-center rounded bg-primary text-primary-foreground">
                  <span className="text-xs font-bold">15</span>
                  <span className="text-[10px]">JUN</span>
                </div>
                <div>
                  <p className="text-sm font-medium">Prva pomoć - Obuka</p>
                  <p className="text-xs text-muted-foreground">
                    09:00 - 14:00 • Sala 1
                  </p>
                </div>
              </div>
              <div className="flex items-center gap-3 rounded-lg border p-3">
                <div className="flex h-10 w-10 flex-col items-center justify-center rounded bg-primary text-primary-foreground">
                  <span className="text-xs font-bold">22</span>
                  <span className="text-[10px]">JUN</span>
                </div>
                <div>
                  <p className="text-sm font-medium">Sastanak udruženja</p>
                  <p className="text-xs text-muted-foreground">
                    10:00 • Konferencijska sala
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Performance Chart Placeholder */}
      <div className="rounded-lg border bg-card p-6 shadow-sm">
        <h2 className="text-lg font-semibold">Statistika članstva</h2>
        <div className="mt-4 flex h-64 items-center justify-center rounded-lg border-2 border-dashed">
          <div className="text-center">
            <TrendingUp className="mx-auto h-12 w-12 text-muted-foreground" />
            <p className="mt-2 text-sm text-muted-foreground">
              Grafikon će biti implementiran sa Recharts
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
