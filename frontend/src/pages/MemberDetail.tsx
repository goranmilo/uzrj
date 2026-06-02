import { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import {
  ArrowLeft,
  Edit,
  Trash2,
  Mail,
  Phone,
  MapPin,
  Calendar,
  Building2,
  GraduationCap,
  Briefcase,
} from 'lucide-react';
import api from '@/services/api';

interface Member {
  id: number;
  user_id: number | null;
  ime: string;
  prezime: string;
  jmbg: string | null;
  email: string | null;
  telefon: string | null;
  adresa: string | null;
  datum_rodjenja: string | null;
  datum_uclanjenja: string;
  status: string;
  odeljenje: { id: number; naziv: string } | null;
  strucna_sprema: { id: number; naziv: string; nivo: number } | null;
  radno_mesto: { id: number; naziv: string } | null;
  user: { id: number; email: string; role: string } | null;
  created_at: string;
  updated_at: string;
}

export function MemberDetailPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [member, setMember] = useState<Member | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    loadMember();
  }, [id]);

  const loadMember = async () => {
    try {
      const response = await api.get<Member>(`/members/${id}`);
      setMember(response.data);
    } catch (error) {
      console.error('Error loading member:', error);
      navigate('/members');
    } finally {
      setIsLoading(false);
    }
  };

  const handleDelete = async () => {
    if (!confirm('Da li ste sigurni da želite da deaktivirate ovog člana?')) {
      return;
    }

    try {
      await api.delete(`/members/${id}`);
      navigate('/members');
    } catch (error) {
      console.error('Error deleting member:', error);
    }
  };

  const getStatusBadge = (status: string) => {
    const styles: Record<string, string> = {
      aktivan: 'bg-green-100 text-green-800',
      neaktivan: 'bg-gray-100 text-gray-800',
      suspendovan: 'bg-red-100 text-red-800',
    };

    return (
      <span className={`inline-flex items-center rounded-full px-3 py-1 text-sm font-medium ${styles[status] || styles.neaktivan}`}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </span>
    );
  };

  if (isLoading) {
    return (
      <div className="flex h-64 items-center justify-center">
        <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent" />
      </div>
    );
  }

  if (!member) {
    return null;
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-4">
          <Link
            to="/members"
            className="rounded-lg border p-2 hover:bg-accent"
          >
            <ArrowLeft size={20} />
          </Link>
          <div>
            <h1 className="text-3xl font-bold">
              {member.ime} {member.prezime}
            </h1>
            <p className="text-muted-foreground">
              Član od {new Date(member.datum_uclanjenja).toLocaleDateString('sr-RS')}
            </p>
          </div>
        </div>
        <div className="flex items-center gap-2">
          <Link
            to={`/members/${id}/edit`}
            className="flex items-center gap-2 rounded-lg border px-4 py-2 text-sm hover:bg-accent"
          >
            <Edit size={16} />
            Izmeni
          </Link>
          <button
            onClick={handleDelete}
            className="flex items-center gap-2 rounded-lg border border-destructive px-4 py-2 text-sm text-destructive hover:bg-destructive/10"
          >
            <Trash2 size={16} />
            Deaktiviraj
          </button>
        </div>
      </div>

      {/* Status */}
      <div className="flex items-center gap-4">
        {getStatusBadge(member.status)}
        {member.user && (
          <span className="text-sm text-muted-foreground">
            Nalog: {member.user.email} ({member.user.role})
          </span>
        )}
      </div>

      {/* Content */}
      <div className="grid gap-6 lg:grid-cols-2">
        {/* Personal Info */}
        <div className="rounded-lg border bg-card p-6">
          <h2 className="mb-4 text-lg font-semibold">Lični podaci</h2>
          <div className="space-y-4">
            <div className="flex items-start gap-3">
              <Mail className="mt-0.5 h-5 w-5 text-muted-foreground" />
              <div>
                <p className="text-sm font-medium">Email</p>
                <p className="text-sm text-muted-foreground">
                  {member.email || 'Nije unet'}
                </p>
              </div>
            </div>

            <div className="flex items-start gap-3">
              <Phone className="mt-0.5 h-5 w-5 text-muted-foreground" />
              <div>
                <p className="text-sm font-medium">Telefon</p>
                <p className="text-sm text-muted-foreground">
                  {member.telefon || 'Nije unet'}
                </p>
              </div>
            </div>

            <div className="flex items-start gap-3">
              <MapPin className="mt-0.5 h-5 w-5 text-muted-foreground" />
              <div>
                <p className="text-sm font-medium">Adresa</p>
                <p className="text-sm text-muted-foreground">
                  {member.adresa || 'Nije uneta'}
                </p>
              </div>
            </div>

            <div className="flex items-start gap-3">
              <Calendar className="mt-0.5 h-5 w-5 text-muted-foreground" />
              <div>
                <p className="text-sm font-medium">Datum rođenja</p>
                <p className="text-sm text-muted-foreground">
                  {member.datum_rodjenja
                    ? new Date(member.datum_rodjenja).toLocaleDateString('sr-RS')
                    : 'Nije unet'}
                </p>
              </div>
            </div>

            <div className="flex items-start gap-3">
              <div className="mt-0.5 h-5 w-5 text-muted-foreground">ID</div>
              <div>
                <p className="text-sm font-medium">JMBG</p>
                <p className="text-sm text-muted-foreground">
                  {member.jmbg || 'Nije unet'}
                </p>
              </div>
            </div>
          </div>
        </div>

        {/* Work Info */}
        <div className="rounded-lg border bg-card p-6">
          <h2 className="mb-4 text-lg font-medium">Radni podaci</h2>
          <div className="space-y-4">
            <div className="flex items-start gap-3">
              <Building2 className="mt-0.5 h-5 w-5 text-muted-foreground" />
              <div>
                <p className="text-sm font-medium">Odeljenje</p>
                <p className="text-sm text-muted-foreground">
                  {member.odeljenje?.naziv || 'Nije dodeljeno'}
                </p>
              </div>
            </div>

            <div className="flex items-start gap-3">
              <GraduationCap className="mt-0.5 h-5 w-5 text-muted-foreground" />
              <div>
                <p className="text-sm font-medium">Stručna sprema</p>
                <p className="text-sm text-muted-foreground">
                  {member.strucna_sprema?.naziv || 'Nije uneta'}
                </p>
              </div>
            </div>

            <div className="flex items-start gap-3">
              <Briefcase className="mt-0.5 h-5 w-5 text-muted-foreground" />
              <div>
                <p className="text-sm font-medium">Radno mesto</p>
                <p className="text-sm text-muted-foreground">
                  {member.radno_mesto?.naziv || 'Nije uneto'}
                </p>
              </div>
            </div>

            <div className="flex items-start gap-3">
              <Calendar className="mt-0.5 h-5 w-5 text-muted-foreground" />
              <div>
                <p className="text-sm font-medium">Datum učlanjenja</p>
                <p className="text-sm text-muted-foreground">
                  {new Date(member.datum_uclanjenja).toLocaleDateString('sr-RS')}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Metadata */}
      <div className="rounded-lg border bg-card p-6">
        <h2 className="mb-4 text-lg font-semibold">Informacije o sistemu</h2>
        <div className="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p className="text-muted-foreground">Kreiran</p>
            <p>{new Date(member.created_at).toLocaleString('sr-RS')}</p>
          </div>
          <div>
            <p className="text-muted-foreground">Poslednja izmena</p>
            <p>{new Date(member.updated_at).toLocaleString('sr-RS')}</p>
          </div>
          <div>
            <p className="text-muted-foreground">ID člana</p>
            <p>{member.id}</p>
          </div>
          <div>
            <p className="text-muted-foreground">User ID</p>
            <p>{member.user_id || 'Nema nalog'}</p>
          </div>
        </div>
      </div>
    </div>
  );
}
