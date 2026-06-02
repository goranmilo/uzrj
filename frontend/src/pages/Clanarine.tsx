import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
  CreditCard,
  Plus,
  Search,
  Filter,
  Download,
  Check,
  Clock,
  AlertCircle,
  DollarSign,
  Send,
} from 'lucide-react';
import api from '@/services/api';

interface Clanarina {
  id: number;
  member: {
    id: number;
    ime: string;
    prezime: string;
    email: string | null;
  } | null;
  iznos: number;
  valuta: string;
  period_od: string;
  period_do: string;
  datum_uplate: string | null;
  nacin_placanja: string | null;
  status: string;
  broj_uplatnice: string | null;
  napomena: string | null;
  created_at: string;
}

interface ClanarineResponse {
  items: Clanarina[];
  total: number;
  page: number;
  per_page: number;
  pages: number;
}

interface Stats {
  total: number;
  by_status: Record<string, number>;
  total_amount: number;
  overdue: number;
  due_soon: number;
  month_revenue: number;
}

export function ClanarinePage() {
  const [clanarine, setClanarine] = useState<Clanarina[]>([]);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [pages, setPages] = useState(1);
  const [isLoading, setIsLoading] = useState(true);
  const [showFilters, setShowFilters] = useState(false);
  const [stats, setStats] = useState<Stats | null>(null);
  const [showNewForm, setShowNewForm] = useState(false);

  const [filters, setFilters] = useState({
    search: '',
    status: '',
    sort_by: 'created_at',
    sort_order: 'desc',
  });

  // New clanarina form
  const [newForm, setNewForm] = useState({
    member_id: '',
    iznos: '',
    period_od: '',
    period_do: '',
    nacin_placanja: '',
    napomena: '',
  });

  // Members for dropdown
  const [members, setMembers] = useState<{ id: number; ime: string; prezime: string }[]>([]);

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

      if (filters.status) params.append('status', filters.status);

      const [clanarineRes, statsRes, membersRes] = await Promise.all([
        api.get<ClanarineResponse>(`/clanarine?${params}`),
        api.get<Stats>('/clanarine/stats'),
        api.get('/members?per_page=100'),
      ]);

      setClanarine(clanarineRes.data.items);
      setTotal(clanarineRes.data.total);
      setPages(clanarineRes.data.pages);
      setStats(statsRes.data);
      setMembers(membersRes.data.items);
    } catch (error) {
      console.error('Error loading data:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleCreate = async () => {
    try {
      await api.post('/clanarine', {
        member_id: parseInt(newForm.member_id),
        iznos: parseFloat(newForm.iznos),
        period_od: newForm.period_od,
        period_do: newForm.period_do,
        nacin_placanja: newForm.nacin_placanja || undefined,
        napomena: newForm.napomena || undefined,
      });
      setShowNewForm(false);
      setNewForm({ member_id: '', iznos: '', period_od: '', period_do: '', nacin_placanja: '', napomena: '' });
      loadData();
    } catch (error) {
      console.error('Error creating clanarina:', error);
    }
  };

  const handleMarkPaid = async (id: number) => {
    try {
      await api.post(`/clanarine/${id}/mark-paid`);
      loadData();
    } catch (error) {
      console.error('Error marking as paid:', error);
    }
  };

  const handleSendReminders = async () => {
    try {
      const response = await api.post('/clanarine/send-reminders');
      alert(`Poslato ${response.data.sent} podsetnika`);
    } catch (error) {
      console.error('Error sending reminders:', error);
    }
  };

  const getStatusBadge = (status: string) => {
    const styles: Record<string, string> = {
      placeno: 'bg-green-100 text-green-800',
      neplaceno: 'bg-red-100 text-red-800',
      delimicno: 'bg-yellow-100 text-yellow-800',
    };

    const icons: Record<string, typeof Check> = {
      placeno: Check,
      neplaceno: AlertCircle,
      delimicno: Clock,
    };

    const Icon = icons[status] || Clock;

    return (
      <span className={`inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium ${styles[status] || styles.neplaceno}`}>
        <Icon size={12} />
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </span>
    );
  };

  const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('sr-RS');
  };

  const formatCurrency = (amount: number, currency: string = 'RSD') => {
    return `${amount.toLocaleString('sr-RS')} ${currency}`;
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold">Članarine</h1>
          <p className="text-muted-foreground">
            Ukupno: {total} članarina
          </p>
        </div>
        <div className="flex items-center gap-2">
          <button
            onClick={handleSendReminders}
            className="flex items-center gap-2 rounded-lg border px-4 py-2 text-sm hover:bg-accent"
          >
            <Send size={16} />
            Pošalji podsetnike
          </button>
          <button
            onClick={() => setShowNewForm(true)}
            className="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90"
          >
            <Plus size={16} />
            Nova članarina
          </button>
        </div>
      </div>

      {/* Stats Cards */}
      {stats && (
        <div className="grid gap-4 md:grid-cols-4">
          <div className="rounded-lg border bg-card p-4">
            <div className="flex items-center gap-3">
              <div className="rounded-lg bg-green-100 p-2">
                <DollarSign className="h-5 w-5 text-green-600" />
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Ukupan prihod</p>
                <p className="text-xl font-bold">{formatCurrency(stats.total_amount)}</p>
              </div>
            </div>
          </div>

          <div className="rounded-lg border bg-card p-4">
            <div className="flex items-center gap-3">
              <div className="rounded-lg bg-blue-100 p-2">
                <CreditCard className="h-5 w-5 text-blue-600" />
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Mesečni prihod</p>
                <p className="text-xl font-bold">{formatCurrency(stats.month_revenue)}</p>
              </div>
            </div>
          </div>

          <div className="rounded-lg border bg-card p-4">
            <div className="flex items-center gap-3">
              <div className="rounded-lg bg-red-100 p-2">
                <AlertCircle className="h-5 w-5 text-red-600" />
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Dospelih</p>
                <p className="text-xl font-bold">{stats.overdue}</p>
              </div>
            </div>
          </div>

          <div className="rounded-lg border bg-card p-4">
            <div className="flex items-center gap-3">
              <div className="rounded-lg bg-yellow-100 p-2">
                <Clock className="h-5 w-5 text-yellow-600" />
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Uskoro dospevaju</p>
                <p className="text-xl font-bold">{stats.due_soon}</p>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* New Form Modal */}
      {showNewForm && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
          <div className="w-full max-w-md rounded-lg bg-card p-6">
            <h2 className="mb-4 text-lg font-semibold">Nova članarina</h2>

            <div className="space-y-4">
              <div>
                <label className="text-sm font-medium">Član</label>
                <select
                  value={newForm.member_id}
                  onChange={(e) => setNewForm({ ...newForm, member_id: e.target.value })}
                  className="mt-1 w-full rounded-md border bg-background px-3 py-2"
                >
                  <option value="">Izaberite člana</option>
                  {members.map((m) => (
                    <option key={m.id} value={m.id}>
                      {m.prezime} {m.ime}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="text-sm font-medium">Iznos (RSD)</label>
                <input
                  type="number"
                  value={newForm.iznos}
                  onChange={(e) => setNewForm({ ...newForm, iznos: e.target.value })}
                  placeholder="0"
                  className="mt-1 w-full rounded-md border bg-background px-3 py-2"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-sm font-medium">Period od</label>
                  <input
                    type="date"
                    value={newForm.period_od}
                    onChange={(e) => setNewForm({ ...newForm, period_od: e.target.value })}
                    className="mt-1 w-full rounded-md border bg-background px-3 py-2"
                  />
                </div>
                <div>
                  <label className="text-sm font-medium">Period do</label>
                  <input
                    type="date"
                    value={newForm.period_do}
                    onChange={(e) => setNewForm({ ...newForm, period_do: e.target.value })}
                    className="mt-1 w-full rounded-md border bg-background px-3 py-2"
                  />
                </div>
              </div>

              <div>
                <label className="text-sm font-medium">Način plaćanja</label>
                <select
                  value={newForm.nacin_placanja}
                  onChange={(e) => setNewForm({ ...newForm, nacin_placanja: e.target.value })}
                  className="mt-1 w-full rounded-md border bg-background px-3 py-2"
                >
                  <option value="">Nije plaćeno</option>
                  <option value="gotovina">Gotovina</option>
                  <option value="uplatnica">Uplatnica</option>
                  <option value="kartica">Kartica</option>
                </select>
              </div>

              <div>
                <label className="text-sm font-medium">Napomena</label>
                <textarea
                  value={newForm.napomena}
                  onChange={(e) => setNewForm({ ...newForm, napomena: e.target.value })}
                  placeholder="Opciono..."
                  rows={2}
                  className="mt-1 w-full rounded-md border bg-background px-3 py-2"
                />
              </div>
            </div>

            <div className="mt-6 flex justify-end gap-2">
              <button
                onClick={() => setShowNewForm(false)}
                className="rounded-lg border px-4 py-2 text-sm hover:bg-accent"
              >
                Otkaži
              </button>
              <button
                onClick={handleCreate}
                disabled={!newForm.member_id || !newForm.iznos || !newForm.period_od || !newForm.period_do}
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
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="text-sm font-medium">Status</label>
              <select
                value={filters.status}
                onChange={(e) => setFilters({ ...filters, status: e.target.value })}
                className="mt-1 w-full rounded-md border bg-background px-3 py-2 text-sm"
              >
                <option value="">Svi statusi</option>
                <option value="placeno">Plaćeno</option>
                <option value="neplaceno">Neplaćeno</option>
                <option value="delimicno">Delimično</option>
              </select>
            </div>
          </div>
        </div>
      )}

      {/* Table */}
      <div className="rounded-lg border bg-card">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b bg-muted/50">
                <th className="px-4 py-3 text-left text-sm font-medium">Član</th>
                <th className="px-4 py-3 text-left text-sm font-medium">Iznos</th>
                <th className="px-4 py-3 text-left text-sm font-medium">Period</th>
                <th className="px-4 py-3 text-left text-sm font-medium">Status</th>
                <th className="px-4 py-3 text-left text-sm font-medium">Datum uplate</th>
                <th className="px-4 py-3 text-right text-sm font-medium">Akcije</th>
              </tr>
            </thead>
            <tbody>
              {isLoading ? (
                <tr>
                  <td colSpan={6} className="px-4 py-8 text-center">
                    <div className="flex items-center justify-center">
                      <div className="h-6 w-6 animate-spin rounded-full border-2 border-primary border-t-transparent" />
                    </div>
                  </td>
                </tr>
              ) : clanarine.length === 0 ? (
                <tr>
                  <td colSpan={6} className="px-4 py-8 text-center text-muted-foreground">
                    Nema članarina
                  </td>
                </tr>
              ) : (
                clanarine.map((c) => (
                  <tr key={c.id} className="border-b hover:bg-muted/50">
                    <td className="px-4 py-3">
                      {c.member ? (
                        <Link
                          to={`/members/${c.member.id}`}
                          className="font-medium text-primary hover:underline"
                        >
                          {c.member.prezime} {c.member.ime}
                        </Link>
                      ) : (
                        <span className="text-muted-foreground">Nepoznat</span>
                      )}
                    </td>
                    <td className="px-4 py-3 font-medium">
                      {formatCurrency(c.iznos, c.valuta)}
                    </td>
                    <td className="px-4 py-3 text-sm">
                      {formatDate(c.period_od)} - {formatDate(c.period_do)}
                    </td>
                    <td className="px-4 py-3">
                      {getStatusBadge(c.status)}
                    </td>
                    <td className="px-4 py-3 text-sm">
                      {c.datum_uplate ? formatDate(c.datum_uplate) : '-'}
                    </td>
                    <td className="px-4 py-3 text-right">
                      {c.status !== 'placeno' && (
                        <button
                          onClick={() => handleMarkPaid(c.id)}
                          className="rounded p-1 text-green-600 hover:bg-green-100"
                          title="Označi kao plaćeno"
                        >
                          <Check size={16} />
                        </button>
                      )}
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        {/* Pagination */}
        {pages > 1 && (
          <div className="flex items-center justify-between border-t px-4 py-3">
            <div className="text-sm text-muted-foreground">
              Stranica {page} od {pages}
            </div>
            <div className="flex items-center gap-2">
              <button
                onClick={() => setPage((p) => Math.max(1, p - 1))}
                disabled={page === 1}
                className="rounded border px-3 py-1 text-sm hover:bg-accent disabled:opacity-50"
              >
                Prethodna
              </button>
              <button
                onClick={() => setPage((p) => Math.min(pages, p + 1))}
                disabled={page === pages}
                className="rounded border px-3 py-1 text-sm hover:bg-accent disabled:opacity-50"
              >
                Sledeća
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
