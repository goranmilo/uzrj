import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
  Users,
  Plus,
  Search,
  Filter,
  Download,
  MoreHorizontal,
  Edit,
  Trash2,
  Eye,
  ChevronLeft,
  ChevronRight,
  ChevronsUpDown,
} from 'lucide-react';
import api from '@/services/api';

interface Member {
  id: number;
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
  strucna_sprema: { id: number; naziv: string } | null;
  radno_mesto: { id: number; naziv: string } | null;
}

interface MembersResponse {
  items: Member[];
  total: number;
  page: number;
  per_page: number;
  pages: number;
}

interface Filters {
  search: string;
  odeljenje_id: string;
  strucna_sprema_id: string;
  radno_mesto_id: string;
  status: string;
  sort_by: string;
  sort_order: string;
}

export function MembersPage() {
  const [members, setMembers] = useState<Member[]>([]);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [pages, setPages] = useState(1);
  const [isLoading, setIsLoading] = useState(true);
  const [showFilters, setShowFilters] = useState(false);
  const [selectedMembers, setSelectedMembers] = useState<number[]>([]);

  const [filters, setFilters] = useState<Filters>({
    search: '',
    odeljenje_id: '',
    strucna_sprema_id: '',
    radno_mesto_id: '',
    status: '',
    sort_by: 'prezime',
    sort_order: 'asc',
  });

  // Filter options (would be fetched from API)
  const [odeljenja, setOdeljenja] = useState<{ id: number; naziv: string }[]>([]);
  const [spreme, setSpreme] = useState<{ id: number; naziv: string }[]>([]);
  const [mesta, setMesta] = useState<{ id: number; naziv: string }[]>([]);

  useEffect(() => {
    loadFilterOptions();
  }, []);

  useEffect(() => {
    loadMembers();
  }, [page, filters]);

  const loadFilterOptions = async () => {
    try {
      const [odeljenjaRes, spremeRes, mestaRes] = await Promise.all([
        api.get('/admin/odeljenja'),
        api.get('/admin/strucne-spreme'),
        api.get('/admin/radna-mesta'),
      ]);
      setOdeljenja(odeljenjaRes.data);
      setSpreme(spremeRes.data);
      setMesta(mestaRes.data);
    } catch (error) {
      console.error('Error loading filter options:', error);
    }
  };

  const loadMembers = async () => {
    setIsLoading(true);
    try {
      const params = new URLSearchParams({
        page: page.toString(),
        per_page: '20',
        sort_by: filters.sort_by,
        sort_order: filters.sort_order,
      });

      if (filters.search) params.append('search', filters.search);
      if (filters.odeljenje_id) params.append('odeljenje_id', filters.odeljenje_id);
      if (filters.strucna_sprema_id) params.append('strucna_sprema_id', filters.strucna_sprema_id);
      if (filters.radno_mesto_id) params.append('radno_mesto_id', filters.radno_mesto_id);
      if (filters.status) params.append('status', filters.status);

      const response = await api.get<MembersResponse>(`/members?${params}`);
      setMembers(response.data.items);
      setTotal(response.data.total);
      setPages(response.data.pages);
    } catch (error) {
      console.error('Error loading members:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleFilterChange = (key: keyof Filters, value: string) => {
    setFilters((prev) => ({ ...prev, [key]: value }));
    setPage(1);
  };

  const handleSort = (column: string) => {
    setFilters((prev) => ({
      ...prev,
      sort_by: column,
      sort_order: prev.sort_by === column && prev.sort_order === 'asc' ? 'desc' : 'asc',
    }));
  };

  const handleExport = async () => {
    try {
      const params = new URLSearchParams();
      if (filters.odeljenje_id) params.append('odeljenje_id', filters.odeljenje_id);
      if (filters.status) params.append('status', filters.status);

      const response = await api.get(`/members/export?${params}`, {
        responseType: 'blob',
      });

      // Create download link
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `clanovi_${new Date().toISOString().slice(0, 10)}.csv`);
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (error) {
      console.error('Error exporting:', error);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Da li ste sigurni da želite da deaktivirate ovog člana?')) {
      return;
    }

    try {
      await api.delete(`/members/${id}`);
      loadMembers();
    } catch (error) {
      console.error('Error deleting member:', error);
    }
  };

  const handleBulkAction = async (action: string) => {
    if (selectedMembers.length === 0) return;

    if (action === 'export') {
      // Export selected members
      console.log('Export selected:', selectedMembers);
    }
  };

  const toggleSelectAll = () => {
    if (selectedMembers.length === members.length) {
      setSelectedMembers([]);
    } else {
      setSelectedMembers(members.map((m) => m.id));
    }
  };

  const toggleSelectMember = (id: number) => {
    setSelectedMembers((prev) =>
      prev.includes(id) ? prev.filter((m) => m !== id) : [...prev, id]
    );
  };

  const getStatusBadge = (status: string) => {
    const styles: Record<string, string> = {
      aktivan: 'bg-green-100 text-green-800',
      neaktivan: 'bg-gray-100 text-gray-800',
      suspendovan: 'bg-red-100 text-red-800',
    };

    return (
      <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${styles[status] || styles.neaktivan}`}>
        {status}
      </span>
    );
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold">Članovi</h1>
          <p className="text-muted-foreground">
            Ukupno: {total} članova
          </p>
        </div>
        <div className="flex items-center gap-2">
          <button
            onClick={handleExport}
            className="flex items-center gap-2 rounded-lg border px-4 py-2 text-sm hover:bg-accent"
          >
            <Download size={16} />
            Eksport CSV
          </button>
          <Link
            to="/members/new"
            className="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90"
          >
            <Plus size={16} />
            Dodaj člana
          </Link>
        </div>
      </div>

      {/* Search and Filters */}
      <div className="flex items-center gap-4">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <input
            type="text"
            placeholder="Pretraži po imenu, prezimenu, email-u ili JMBG-u..."
            value={filters.search}
            onChange={(e) => handleFilterChange('search', e.target.value)}
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

      {/* Filters Panel */}
      {showFilters && (
        <div className="rounded-lg border bg-card p-4">
          <div className="grid grid-cols-4 gap-4">
            <div>
              <label className="text-sm font-medium">Odeljenje</label>
              <select
                value={filters.odeljenje_id}
                onChange={(e) => handleFilterChange('odeljenjeje_id', e.target.value)}
                className="mt-1 w-full rounded-md border bg-background px-3 py-2 text-sm"
              >
                <option value="">Sva odeljenja</option>
                {odeljenja.map((o) => (
                  <option key={o.id} value={o.id}>
                    {o.naziv}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="text-sm font-medium">Stručna sprema</label>
              <select
                value={filters.strucna_sprema_id}
                onChange={(e) => handleFilterChange('strucna_sprema_id', e.target.value)}
                className="mt-1 w-full rounded-md border bg-background px-3 py-2 text-sm"
              >
                <option value="">Sve spreme</option>
                {spreme.map((s) => (
                  <option key={s.id} value={s.id}>
                    {s.naziv}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="text-sm font-medium">Radno mesto</label>
              <select
                value={filters.radno_mesto_id}
                onChange={(e) => handleFilterChange('radno_mesto_id', e.target.value)}
                className="mt-1 w-full rounded-md border bg-background px-3 py-2 text-sm"
              >
                <option value="">Sva mesta</option>
                {mesta.map((m) => (
                  <option key={m.id} value={m.id}>
                    {m.naziv}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="text-sm font-medium">Status</label>
              <select
                value={filters.status}
                onChange={(e) => handleFilterChange('status', e.target.value)}
                className="mt-1 w-full rounded-md border bg-background px-3 py-2 text-sm"
              >
                <option value="">Svi statusi</option>
                <option value="aktivan">Aktivan</option>
                <option value="neaktivan">Neaktivan</option>
                <option value="suspendovan">Suspendovan</option>
              </select>
            </div>
          </div>
        </div>
      )}

      {/* Bulk Actions */}
      {selectedMembers.length > 0 && (
        <div className="flex items-center gap-4 rounded-lg border bg-card p-4">
          <span className="text-sm">
            Izabrano: {selectedMembers.length} članova
          </span>
          <button
            onClick={() => handleBulkAction('export')}
            className="text-sm text-primary hover:underline"
          >
            Eksportuj izabrane
          </button>
        </div>
      )}

      {/* Table */}
      <div className="rounded-lg border bg-card">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b bg-muted/50">
                <th className="px-4 py-3 text-left">
                  <input
                    type="checkbox"
                    checked={selectedMembers.length === members.length && members.length > 0}
                    onChange={toggleSelectAll}
                    className="rounded"
                  />
                </th>
                <th
                  className="cursor-pointer px-4 py-3 text-left text-sm font-medium"
                  onClick={() => handleSort('prezime')}
                >
                  <div className="flex items-center gap-1">
                    Ime i prezime
                    <ChevronsUpDown size={14} />
                  </div>
                </th>
                <th className="px-4 py-3 text-left text-sm font-medium">JMBG</th>
                <th className="px-4 py-3 text-left text-sm font-medium">Email</th>
                <th className="px-4 py-3 text-left text-sm font-medium">Odeljenje</th>
                <th className="px-4 py-3 text-left text-sm font-medium">Stručna sprema</th>
                <th
                  className="cursor-pointer px-4 py-3 text-left text-sm font-medium"
                  onClick={() => handleSort('status')}
                >
                  <div className="flex items-center gap-1">
                    Status
                    <ChevronsUpDown size={14} />
                  </div>
                </th>
                <th className="px-4 py-3 text-right text-sm font-medium">Akcije</th>
              </tr>
            </thead>
            <tbody>
              {isLoading ? (
                <tr>
                  <td colSpan={8} className="px-4 py-8 text-center">
                    <div className="flex items-center justify-center">
                      <div className="h-6 w-6 animate-spin rounded-full border-2 border-primary border-t-transparent" />
                    </div>
                  </td>
                </tr>
              ) : members.length === 0 ? (
                <tr>
                  <td colSpan={8} className="px-4 py-8 text-center text-muted-foreground">
                    Nema pronađenih članova
                  </td>
                </tr>
              ) : (
                members.map((member) => (
                  <tr key={member.id} className="border-b hover:bg-muted/50">
                    <td className="px-4 py-3">
                      <input
                        type="checkbox"
                        checked={selectedMembers.includes(member.id)}
                        onChange={() => toggleSelectMember(member.id)}
                        className="rounded"
                      />
                    </td>
                    <td className="px-4 py-3">
                      <Link
                        to={`/members/${member.id}`}
                        className="font-medium text-primary hover:underline"
                      >
                        {member.prezime} {member.ime}
                      </Link>
                    </td>
                    <td className="px-4 py-3 text-sm text-muted-foreground">
                      {member.jmbg || '-'}
                    </td>
                    <td className="px-4 py-3 text-sm">
                      {member.email || '-'}
                    </td>
                    <td className="px-4 py-3 text-sm">
                      {member.odeljenje?.naziv || '-'}
                    </td>
                    <td className="px-4 py-3 text-sm">
                      {member.strucna_sprema?.naziv || '-'}
                    </td>
                    <td className="px-4 py-3">
                      {getStatusBadge(member.status)}
                    </td>
                    <td className="px-4 py-3 text-right">
                      <div className="flex items-center justify-end gap-2">
                        <Link
                          to={`/members/${member.id}`}
                          className="rounded p-1 hover:bg-accent"
                          title="Pregled"
                        >
                          <Eye size={16} />
                        </Link>
                        <Link
                          to={`/members/${member.id}/edit`}
                          className="rounded p-1 hover:bg-accent"
                          title="Izmeni"
                        >
                          <Edit size={16} />
                        </Link>
                        <button
                          onClick={() => handleDelete(member.id)}
                          className="rounded p-1 text-destructive hover:bg-destructive/10"
                          title="Deaktiviraj"
                        >
                          <Trash2 size={16} />
                        </button>
                      </div>
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
                className="rounded p-1 hover:bg-accent disabled:opacity-50"
              >
                <ChevronLeft size={20} />
              </button>
              {Array.from({ length: Math.min(5, pages) }, (_, i) => {
                const pageNum = Math.max(1, Math.min(pages - 4, page - 2)) + i;
                return (
                  <button
                    key={pageNum}
                    onClick={() => setPage(pageNum)}
                    className={`rounded px-3 py-1 text-sm ${
                      pageNum === page
                        ? 'bg-primary text-primary-foreground'
                        : 'hover:bg-accent'
                    }`}
                  >
                    {pageNum}
                  </button>
                );
              })}
              <button
                onClick={() => setPage((p) => Math.min(pages, p + 1))}
                disabled={page === pages}
                className="rounded p-1 hover:bg-accent disabled:opacity-50"
              >
                <ChevronRight size={20} />
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
