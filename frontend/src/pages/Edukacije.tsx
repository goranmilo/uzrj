import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
  GraduationCap,
  Plus,
  Search,
  Filter,
  Calendar,
  List,
  Users,
  Clock,
  MapPin,
  Award,
  Check,
  X,
  Eye,
  Edit,
  AlertCircle,
} from 'lucide-react';
import api from '@/services/api';

interface Edukacija {
  id: number;
  naziv: string;
  opis: string | null;
  tip: string | null;
  datum_pocetka: string;
  datum_zavrsetka: string | null;
  trajanje_sati: number | null;
  lokacija: string | null;
  max_polaznika: number | null;
  cena: number | null;
  bodovi: number | null;
  status: string;
  prijavljeni: number;
  created_at: string;
}

interface Stats {
  total: number;
  upcoming: number;
  completed: number;
  total_registrations: number;
  attended: number;
  certificates_issued: number;
}

type ViewMode = 'list' | 'calendar';

export function EdukacijePage() {
  const [edukacije, setEdukacije] = useState<Edukacija[]>([]);
  const [stats, setStats] = useState<Stats | null>(null);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [pages, setPages] = useState(1);
  const [isLoading, setIsLoading] = useState(true);
  const [viewMode, setViewMode] = useState<ViewMode>('list');
  const [showFilters, setShowFilters] = useState(false);
  const [showNewForm, setShowNewForm] = useState(false);

  const [filters, setFilters] = useState({
    search: '',
    status: '',
    tip: '',
    sort_by: 'datum_pocetka',
    sort_order: 'asc',
  });

  const [newForm, setNewForm] = useState({
    naziv: '',
    opis: '',
    tip: 'seminar',
    datum_pocetka: '',
    datum_zavrsetka: '',
    trajanje_sati: '',
    lokacija: '',
    max_polaznika: '',
    cena: '',
    bodovi: '',
  });

  useEffect(() => {
    loadData();
  }, [page, filters]);

  const loadData = async () => {
    setIsLoading(true);
    try {
      const params = new URLSearchParams({
        page: page.toString(),
        per_page: '20',
        sort_by: filters.sort_by,
        sort_order: filters.sort_order,
      });
      if (filters.search) params.append('search', filters.search);
      if (filters.status) params.append('status', filters.status);
      if (filters.tip) params.append('tip', filters.tip);

      const [edRes, statsRes] = await Promise.all([
        api.get(`/edukacije?${params}`),
        api.get('/edukacije/stats'),
      ]);
      setEdukacije(edRes.data.items);
      setTotal(edRes.data.total);
      setPages(edRes.data.pages);
      setStats(statsRes.data);
    } catch (error) {
      console.error('Error loading data:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleCreate = async () => {
    try {
      const params = new URLSearchParams();
      params.append('naziv', newForm.naziv);
      params.append('datum_pocetka', newForm.datum_pocetka);
      if (newForm.opis) params.append('opis', newForm.opis);
      if (newForm.tip) params.append('tip', newForm.tip);
      if (newForm.datum_zavrsetka) params.append('datum_zavrsetka', newForm.datum_zavrsetka);
      if (newForm.trajanje_sati) params.append('trajanje_sati', newForm.trajanje_sati);
      if (newForm.lokacija) params.append('lokacija', newForm.lokacija);
      if (newForm.max_polaznika) params.append('max_polaznika', newForm.max_polaznika);
      if (newForm.cena) params.append('cena', newForm.cena);
      if (newForm.bodovi) params.append('bodovi', newForm.bodovi);

      await api.post(`/edukacije?${params}`);
      setShowNewForm(false);
      setNewForm({ naziv: '', opis: '', tip: 'seminar', datum_pocetka: '', datum_zavrsetka: '', trajanje_sati: '', lokacija: '', max_polaznika: '', cena: '', bodovi: '' });
      loadData();
    } catch (error) {
      console.error('Error creating edukacija:', error);
    }
  };

  const handleCancel = async (id: number) => {
    if (!confirm('Da li ste sigurni da želite da otkažete ovu edukaciju?')) return;
    try {
      await api.delete(`/edukacije/${id}`);
      loadData();
    } catch (error) {
      console.error('Error cancelling edukacija:', error);
    }
  };

  const getStatusBadge = (status: string) => {
    const styles: Record<string, string> = {
      zakazano: 'bg-blue-100 text-blue-800',
      u_toku: 'bg-yellow-100 text-yellow-800',
      zavrseno: 'bg-green-100 text-green-800',
      otkazano: 'bg-red-100 text-red-800',
    };
    const labels: Record<string, string> = {
      zakazano: 'Zakazano',
      u_toku: 'U toku',
      zavrseno: 'Završeno',
      otkazano: 'Otkazano',
    };
    return (
      <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${styles[status] || ''}`}>
        {labels[status] || status}
      </span>
    );
  };

  const getTipBadge = (tip: string | null) => {
    if (!tip) return null;
    const styles: Record<string, string> = {
      seminar: 'bg-purple-100 text-purple-800',
      radionica: 'bg-orange-100 text-orange-800',
      konferencija: 'bg-blue-100 text-blue-800',
      kurs: 'bg-green-100 text-green-800',
    };
    return (
      <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${styles[tip] || 'bg-gray-100 text-gray-800'}`}>
        {tip}
      </span>
    );
  };

  const formatDate = (date: string) => new Date(date).toLocaleDateString('sr-RS');
  const formatDateTime = (date: string) => new Date(date).toLocaleString('sr-RS');

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold">Edukacija</h1>
          <p className="text-muted-foreground">Ukupno: {total} edukacija</p>
        </div>
        <div className="flex items-center gap-2">
          <div className="flex rounded-lg border">
            <button
              onClick={() => setViewMode('list')}
              className={`flex items-center gap-1 rounded-l-lg px-3 py-2 text-sm ${viewMode === 'list' ? 'bg-primary text-primary-foreground' : 'hover:bg-accent'}`}
            >
              <List size={16} />
              Lista
            </button>
            <button
              onClick={() => setViewMode('calendar')}
              className={`flex items-center gap-1 rounded-r-lg px-3 py-2 text-sm ${viewMode === 'calendar' ? 'bg-primary text-primary-foreground' : 'hover:bg-accent'}`}
            >
              <Calendar size={16} />
              Kalendar
            </button>
          </div>
          <button
            onClick={() => setShowNewForm(true)}
            className="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90"
          >
            <Plus size={16} />
            Nova edukacija
          </button>
        </div>
      </div>

      {/* Stats */}
      {stats && (
        <div className="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
          {[
            { label: 'Ukupno', value: stats.total, icon: GraduationCap, color: 'text-blue-600', bg: 'bg-blue-100' },
            { label: 'Predstojeće', value: stats.upcoming, icon: Calendar, color: 'text-purple-600', bg: 'bg-purple-100' },
            { label: 'Završene', value: stats.completed, icon: Check, color: 'text-green-600', bg: 'bg-green-100' },
            { label: 'Prijava', value: stats.total_registrations, icon: Users, color: 'text-orange-600', bg: 'bg-orange-100' },
            { label: 'Prisustvo', value: stats.attended, icon: Users, color: 'text-teal-600', bg: 'bg-teal-100' },
            { label: 'Sertifikati', value: stats.certificates_issued, icon: Award, color: 'text-yellow-600', bg: 'bg-yellow-100' },
          ].map((s) => (
            <div key={s.label} className="rounded-lg border bg-card p-4">
              <div className="flex items-center gap-2">
                <div className={`rounded-lg p-1.5 ${s.bg}`}>
                  <s.icon className={`h-4 w-4 ${s.color}`} />
                </div>
                <div>
                  <p className="text-xs text-muted-foreground">{s.label}</p>
                  <p className="text-lg font-bold">{s.value}</p>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* New Form Modal */}
      {showNewForm && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
          <div className="w-full max-w-lg rounded-lg bg-card p-6 max-h-[90vh] overflow-y-auto">
            <h2 className="mb-4 text-lg font-semibold">Nova edukacija</h2>
            <div className="space-y-4">
              <div>
                <label className="text-sm font-medium">Naziv *</label>
                <input
                  type="text" value={newForm.naziv}
                  onChange={(e) => setNewForm({ ...newForm, naziv: e.target.value })}
                  placeholder="Naziv edukacije"
                  className="mt-1 w-full rounded-md border bg-background px-3 py-2"
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-sm font-medium">Tip</label>
                  <select
                    value={newForm.tip}
                    onChange={(e) => setNewForm({ ...newForm, tip: e.target.value })}
                    className="mt-1 w-full rounded-md border bg-background px-3 py-2"
                  >
                    <option value="seminar">Seminar</option>
                    <option value="radionica">Radionica</option>
                    <option value="konferencija">Konferencija</option>
                    <option value="kurs">Kurs</option>
                  </select>
                </div>
                <div>
                  <label className="text-sm font-medium">Lokacija</label>
                  <input
                    type="text" value={newForm.lokacija}
                    onChange={(e) => setNewForm({ ...newForm, lokacija: e.target.value })}
                    placeholder="Lokacija"
                    className="mt-1 w-full rounded-md border bg-background px-3 py-2"
                  />
                </div>
              </div>
              <div>
                <label className="text-sm font-medium">Opis</label>
                <textarea
                  value={newForm.opis}
                  onChange={(e) => setNewForm({ ...newForm, opis: e.target.value })}
                  placeholder="Opis edukacije..."
                  rows={3}
                  className="mt-1 w-full rounded-md border bg-background px-3 py-2"
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-sm font-medium">Datum početka *</label>
                  <input
                    type="datetime-local" value={newForm.datum_pocetka}
                    onChange={(e) => setNewForm({ ...newForm, datum_pocetka: e.target.value })}
                    className="mt-1 w-full rounded-md border bg-background px-3 py-2"
                  />
                </div>
                <div>
                  <label className="text-sm font-medium">Datum završetka</label>
                  <input
                    type="datetime-local" value={newForm.datum_zavrsetka}
                    onChange={(e) => setNewForm({ ...newForm, datum_zavrsetka: e.target.value })}
                    className="mt-1 w-full rounded-md border bg-background px-3 py-2"
                  />
                </div>
              </div>
              <div className="grid grid-cols-3 gap-4">
                <div>
                  <label className="text-sm font-medium">Trajanje (h)</label>
                  <input
                    type="number" value={newForm.trajanje_sati}
                    onChange={(e) => setNewForm({ ...newForm, trajanje_sati: e.target.value })}
                    placeholder="0"
                    className="mt-1 w-full rounded-md border bg-background px-3 py-2"
                  />
                </div>
                <div>
                  <label className="text-sm font-medium">Maks. polaznika</label>
                  <input
                    type="number" value={newForm.max_polaznika}
                    onChange={(e) => setNewForm({ ...newForm, max_polaznika: e.target.value })}
                    placeholder="Bez limita"
                    className="mt-1 w-full rounded-md border bg-background px-3 py-2"
                  />
                </div>
                <div>
                  <label className="text-sm font-medium">Bodovi</label>
                  <input
                    type="number" value={newForm.bodovi}
                    onChange={(e) => setNewForm({ ...newForm, bodovi: e.target.value })}
                    placeholder="0"
                    className="mt-1 w-full rounded-md border bg-background px-3 py-2"
                  />
                </div>
              </div>
              <div>
                <label className="text-sm font-medium">Cena (RSD)</label>
                <input
                  type="number" value={newForm.cena}
                  onChange={(e) => setNewForm({ ...newForm, cena: e.target.value })}
                  placeholder="Besplatno"
                  className="mt-1 w-full rounded-md border bg-background px-3 py-2"
                />
              </div>
            </div>
            <div className="mt-6 flex justify-end gap-2">
              <button onClick={() => setShowNewForm(false)} className="rounded-lg border px-4 py-2 text-sm hover:bg-accent">Otkaži</button>
              <button
                onClick={handleCreate}
                disabled={!newForm.naziv || !newForm.datum_pocetka}
                className="rounded-lg bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
              >
                Kreiraj
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Filters */}
      <div className="flex items-center gap-4">
        <div className="relative flex-1 max-w-md">
          <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <input
            type="text"
            placeholder="Pretraži edukacije..."
            value={filters.search}
            onChange={(e) => { setFilters({ ...filters, search: e.target.value }); setPage(1); }}
            className="w-full rounded-lg border bg-background py-2 pl-10 pr-4 text-sm"
          />
        </div>
        <button
          onClick={() => setShowFilters(!showFilters)}
          className={`flex items-center gap-2 rounded-lg border px-4 py-2 text-sm ${showFilters ? 'bg-accent' : 'hover:bg-accent'}`}
        >
          <Filter size={16} />
          Filteri
        </button>
      </div>

      {showFilters && (
        <div className="rounded-lg border bg-card p-4">
          <div className="grid grid-cols-3 gap-4">
            <div>
              <label className="text-sm font-medium">Status</label>
              <select value={filters.status} onChange={(e) => { setFilters({ ...filters, status: e.target.value }); setPage(1); }}
                className="mt-1 w-full rounded-md border bg-background px-3 py-2 text-sm">
                <option value="">Svi statusi</option>
                <option value="zakazano">Zakazano</option>
                <option value="u_toku">U toku</option>
                <option value="zavrseno">Završeno</option>
                <option value="otkazano">Otkazano</option>
              </select>
            </div>
            <div>
              <label className="text-sm font-medium">Tip</label>
              <select value={filters.tip} onChange={(e) => { setFilters({ ...filters, tip: e.target.value }); setPage(1); }}
                className="mt-1 w-full rounded-md border bg-background px-3 py-2 text-sm">
                <option value="">Svi tipovi</option>
                <option value="seminar">Seminar</option>
                <option value="radionica">Radionica</option>
                <option value="konferencija">Konferencija</option>
                <option value="kurs">Kurs</option>
              </select>
            </div>
          </div>
        </div>
      )}

      {/* List View */}
      {viewMode === 'list' && (
        <div className="rounded-lg border bg-card">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b bg-muted/50">
                  <th className="px-4 py-3 text-left text-sm font-medium">Naziv</th>
                  <th className="px-4 py-3 text-left text-sm font-medium">Tip</th>
                  <th className="px-4 py-3 text-left text-sm font-medium">Datum</th>
                  <th className="px-4 py-3 text-left text-sm font-medium">Lokacija</th>
                  <th className="px-4 py-3 text-left text-sm font-medium">Polaznici</th>
                  <th className="px-4 py-3 text-left text-sm font-medium">Status</th>
                  <th className="px-4 py-3 text-right text-sm font-medium">Akcije</th>
                </tr>
              </thead>
              <tbody>
                {isLoading ? (
                <tr>
                  <td colSpan={7} className="px-4 py-8 text-center">
                    <div className="h-6 w-6 animate-spin rounded-full border-2 border-primary border-t-transparent" />
                  </td>
                </tr>
              ) : edukacije.length === 0 ? (
                <tr>
                  <td colSpan={7} className="px-4 py-8 text-center text-muted-foreground">
                    Nema edukacija
                  </td>
                </tr>
              ) : (
                edukacije.map((e) => (
                  <tr key={e.id} className="border-b hover:bg-muted/50">
                    <td className="px-4 py-3">
                      <Link to={`/edukacije/${e.id}`} className="font-medium text-primary hover:underline">
                        {e.naziv}
                      </Link>
                      {e.bodovi && (
                        <p className="mt-0.5 text-xs text-muted-foreground">{e.bodovi} bodova</p>
                      )}
                    </td>
                    <td className="px-4 py-3">{getTipBadge(e.tip)}</td>
                    <td className="px-4 py-3 text-sm">
                      {formatDateTime(e.datum_pocetka)}
                      {e.trajanje_sati && (
                        <p className="text-xs text-muted-foreground">{e.trajanje_sati}h</p>
                      )}
                    </td>
                    <td className="px-4 py-3 text-sm">{e.lokacija || '-'}</td>
                    <td className="px-4 py-3 text-sm">
                      <div className="flex items-center gap-1">
                        <Users size={14} className="text-muted-foreground" />
                        <span>{e.prijavljeni}{e.max_polaznika ? `/${e.max_polaznika}` : ''}</span>
                      </div>
                    </td>
                    <td className="px-4 py-3">{getStatusBadge(e.status)}</td>
                    <td className="px-4 py-3 text-right">
                      <div className="flex items-center justify-end gap-2">
                        <Link to={`/edukacije/${e.id}`} className="rounded p-1 hover:bg-accent" title="Pregled">
                          <Eye size={16} />
                        </Link>
                        {e.status === 'zakazano' && (
                          <button onClick={() => handleCancel(e.id)} className="rounded p-1 text-destructive hover:bg-destructive/10" title="Otkaži">
                            <X size={16} />
                          </button>
                        )}
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
        {pages > 1 && (
          <div className="flex items-center justify-between border-t px-4 py-3">
            <div className="text-sm text-muted-foreground">Stranica {page} od {pages}</div>
            <div className="flex items-center gap-2">
              <button onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page === 1} className="rounded border px-3 py-1 text-sm hover:bg-accent disabled:opacity-50">Prethodna</button>
              <button onClick={() => setPage((p) => Math.min(pages, p + 1))} disabled={page === pages} className="rounded border px-3 py-1 text-sm hover:bg-accent disabled:opacity-50">Sledeća</button>
            </div>
          </div>
        )}
      </div>
      )}

      {/* Calendar View Placeholder */}
      {viewMode === 'calendar' && (
        <div className="rounded-lg border bg-card p-8">
          <div className="flex h-64 items-center justify-center">
            <div className="text-center">
              <Calendar className="mx-auto h-12 w-12 text-muted-foreground" />
              <p className="mt-4 text-lg font-medium">Kalendar pogled</p>
              <p className="mt-2 text-sm text-muted-foreground">
                Kalendar sa edukacijama biće implementiran sa FullCalendar bibliotekom
              </p>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}