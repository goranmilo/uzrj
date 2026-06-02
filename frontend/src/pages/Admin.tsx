import { useState, useEffect } from 'react';
import {
  Building2,
  GraduationCap,
  Briefcase,
  Plus,
  Edit,
  Trash2,
  Save,
  X,
  Check,
} from 'lucide-react';
import api from '@/services/api';

interface Odeljenje {
  id: number;
  naziv: string;
  opis: string | null;
  is_active: boolean;
  created_at: string;
}

interface StrucnaSprema {
  id: number;
  naziv: string;
  nivo: number | null;
  is_active: boolean;
  created_at: string;
}

interface RadnoMesto {
  id: number;
  naziv: string;
  opis: string | null;
  is_active: boolean;
  created_at: string;
}

type Tab = 'odeljenja' | 'spreme' | 'mesta';

export function AdminPage() {
  const [activeTab, setActiveTab] = useState<Tab>('odeljenja');
  const [isLoading, setIsLoading] = useState(true);

  // Data
  const [odeljenja, setOdeljenja] = useState<Odeljenje[]>([]);
  const [spreme, setSpreme] = useState<StrucnaSprema[]>([]);
  const [mesta, setMesta] = useState<RadnoMesto[]>([]);

  // Edit state
  const [editingId, setEditingId] = useState<number | null>(null);
  const [editForm, setEditForm] = useState<any>({});

  // New item state
  const [showNewForm, setShowNewForm] = useState(false);
  const [newForm, setNewForm] = useState<any>({});

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    setIsLoading(true);
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
      console.error('Error loading data:', error);
    } finally {
      setIsLoading(false);
    }
  };

  // ============================================
  // ODELJENJA
  // ============================================

  const handleCreateOdeljenje = async () => {
    try {
      await api.post('/admin/odeljenja', null, {
        params: { naziv: newForm.naziv, opis: newForm.opis },
      });
      setShowNewForm(false);
      setNewForm({});
      loadData();
    } catch (error) {
      console.error('Error creating odeljenje:', error);
    }
  };

  const handleUpdateOdeljenje = async (id: number) => {
    try {
      await api.put(`/admin/odeljenja/${id}`, null, {
        params: {
          naziv: editForm.naziv,
          opis: editForm.opis,
          is_active: editForm.is_active,
        },
      });
      setEditingId(null);
      setEditForm({});
      loadData();
    } catch (error) {
      console.error('Error updating odeljenje:', error);
    }
  };

  const handleDeleteOdeljenje = async (id: number) => {
    if (!confirm('Da li ste sigurni da želite da deaktivirate ovo odeljenje?')) {
      return;
    }
    try {
      await api.delete(`/admin/odeljenja/${id}`);
      loadData();
    } catch (error) {
      console.error('Error deleting odeljenje:', error);
    }
  };

  // ============================================
  // STRUČNE SPREME
  // ============================================

  const handleCreateSprema = async () => {
    try {
      await api.post('/admin/strucne-spreme', null, {
        params: { naziv: newForm.naziv, nivo: newForm.nivo },
      });
      setShowNewForm(false);
      setNewForm({});
      loadData();
    } catch (error) {
      console.error('Error creating sprema:', error);
    }
  };

  const handleUpdateSprema = async (id: number) => {
    try {
      await api.put(`/admin/strucne-spreme/${id}`, null, {
        params: {
          naziv: editForm.naziv,
          nivo: editForm.nivo,
          is_active: editForm.is_active,
        },
      });
      setEditingId(null);
      setEditForm({});
      loadData();
    } catch (error) {
      console.error('Error updating sprema:', error);
    }
  };

  const handleDeleteSprema = async (id: number) => {
    if (!confirm('Da li ste sigurni da želite da deaktivirate ovu stručnu spremu?')) {
      return;
    }
    try {
      await api.delete(`/admin/strucne-spreme/${id}`);
      loadData();
    } catch (error) {
      console.error('Error deleting sprema:', error);
    }
  };

  // ============================================
  // RADNA MESTA
  // ============================================

  const handleCreateMesto = async () => {
    try {
      await api.post('/admin/radna-mesta', null, {
        params: { naziv: newForm.naziv, opis: newForm.opis },
      });
      setShowNewForm(false);
      setNewForm({});
      loadData();
    } catch (error) {
      console.error('Error creating mesto:', error);
    }
  };

  const handleUpdateMesto = async (id: number) => {
    try {
      await api.put(`/admin/radna-mesta/${id}`, null, {
        params: {
          naziv: editForm.naziv,
          opis: editForm.opis,
          is_active: editForm.is_active,
        },
      });
      setEditingId(null);
      setEditForm({});
      loadData();
    } catch (error) {
      console.error('Error updating mesto:', error);
    }
  };

  const handleDeleteMesto = async (id: number) => {
    if (!confirm('Da li ste sigurni da želite da deaktivirate ovo radno mesto?')) {
      return;
    }
    try {
      await api.delete(`/admin/radna-mesta/${id}`);
      loadData();
    } catch (error) {
      console.error('Error deleting mesto:', error);
    }
  };

  // ============================================
  // RENDER HELPERS
  // ============================================

  const startEdit = (item: any) => {
    setEditingId(item.id);
    setEditForm({ ...item });
  };

  const cancelEdit = () => {
    setEditingId(null);
    setEditForm({});
  };

  const startNew = () => {
    setShowNewForm(true);
    setNewForm({});
  };

  const cancelNew = () => {
    setShowNewForm(false);
    setNewForm({});
  };

  const tabs = [
    { id: 'odeljenja' as Tab, name: 'Odeljenja', icon: Building2 },
    { id: 'spreme' as Tab, name: 'Stručne spreme', icon: GraduationCap },
    { id: 'mesta' as Tab, name: 'Radna mesta', icon: Briefcase },
  ];

  const renderOdeljenja = () => (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-semibold">Odeljenja</h2>
        <button
          onClick={startNew}
          className="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90"
        >
          <Plus size={16} />
          Dodaj odeljenje
        </button>
      </div>

      <div className="rounded-lg border bg-card">
        <table className="w-full">
          <thead>
            <tr className="border-b bg-muted/50">
              <th className="px-4 py-3 text-left text-sm font-medium">Naziv</th>
              <th className="px-4 py-3 text-left text-sm font-medium">Opis</th>
              <th className="px-4 py-3 text-left text-sm font-medium">Status</th>
              <th className="px-4 py-3 text-right text-sm font-medium">Akcije</th>
            </tr>
          </thead>
          <tbody>
            {showNewForm && (
              <tr className="border-b bg-accent/50">
                <td className="px-4 py-3">
                  <input
                    type="text"
                    value={newForm.naziv || ''}
                    onChange={(e) => setNewForm({ ...newForm, naziv: e.target.value })}
                    placeholder="Naziv odeljenja"
                    className="w-full rounded border bg-background px-2 py-1 text-sm"
                  />
                </td>
                <td className="px-4 py-3">
                  <input
                    type="text"
                    value={newForm.opis || ''}
                    onChange={(e) => setNewForm({ ...newForm, opis: e.target.value })}
                    placeholder="Opis (opciono)"
                    className="w-full rounded border bg-background px-2 py-1 text-sm"
                  />
                </td>
                <td className="px-4 py-3">
                  <span className="text-sm text-muted-foreground">Aktivno</span>
                </td>
                <td className="px-4 py-3 text-right">
                  <div className="flex items-center justify-end gap-2">
                    <button
                      onClick={handleCreateOdeljenje}
                      className="rounded p-1 text-green-600 hover:bg-green-100"
                      title="Sačuvaj"
                    >
                      <Check size={16} />
                    </button>
                    <button
                      onClick={cancelNew}
                      className="rounded p-1 hover:bg-accent"
                      title="Otkaži"
                    >
                      <X size={16} />
                    </button>
                  </div>
                </td>
              </tr>
            )}
            {odeljenja.map((o) => (
              <tr key={o.id} className="border-b hover:bg-muted/50">
                <td className="px-4 py-3">
                  {editingId === o.id ? (
                    <input
                      type="text"
                      value={editForm.naziv || ''}
                      onChange={(e) => setEditForm({ ...editForm, naziv: e.target.value })}
                      className="w-full rounded border bg-background px-2 py-1 text-sm"
                    />
                  ) : (
                    <span className="font-medium">{o.naziv}</span>
                  )}
                </td>
                <td className="px-4 py-3">
                  {editingId === o.id ? (
                    <input
                      type="text"
                      value={editForm.opis || ''}
                      onChange={(e) => setEditForm({ ...editForm, opis: e.target.value })}
                      className="w-full rounded border bg-background px-2 py-1 text-sm"
                    />
                  ) : (
                    <span className="text-sm text-muted-foreground">{o.opis || '-'}</span>
                  )}
                </td>
                <td className="px-4 py-3">
                  {editingId === o.id ? (
                    <select
                      value={editForm.is_active ? 'true' : 'false'}
                      onChange={(e) => setEditForm({ ...editForm, is_active: e.target.value === 'true' })}
                      className="rounded border bg-background px-2 py-1 text-sm"
                    >
                      <option value="true">Aktivno</option>
                      <option value="false">Neaktivno</option>
                    </select>
                  ) : (
                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${o.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                      {o.is_active ? 'Aktivno' : 'Neaktivno'}
                    </span>
                  )}
                </td>
                <td className="px-4 py-3 text-right">
                  <div className="flex items-center justify-end gap-2">
                    {editingId === o.id ? (
                      <>
                        <button
                          onClick={() => handleUpdateOdeljenje(o.id)}
                          className="rounded p-1 text-green-600 hover:bg-green-100"
                          title="Sačuvaj"
                        >
                          <Check size={16} />
                        </button>
                        <button
                          onClick={cancelEdit}
                          className="rounded p-1 hover:bg-accent"
                          title="Otkaži"
                        >
                          <X size={16} />
                        </button>
                      </>
                    ) : (
                      <>
                        <button
                          onClick={() => startEdit(o)}
                          className="rounded p-1 hover:bg-accent"
                          title="Izmeni"
                        >
                          <Edit size={16} />
                        </button>
                        <button
                          onClick={() => handleDeleteOdeljenje(o.id)}
                          className="rounded p-1 text-destructive hover:bg-destructive/10"
                          title="Deaktiviraj"
                        >
                          <Trash2 size={16} />
                        </button>
                      </>
                    )}
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );

  const renderSpreme = () => (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-semibold">Stručne spreme</h2>
        <button
          onClick={startNew}
          className="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90"
        >
          <Plus size={16} />
          Dodaj stručnu spremu
        </button>
      </div>

      <div className="rounded-lg border bg-card">
        <table className="w-full">
          <thead>
            <tr className="border-b bg-muted/50">
              <th className="px-4 py-3 text-left text-sm font-medium">Naziv</th>
              <th className="px-4 py-3 text-left text-sm font-medium">Nivo</th>
              <th className="px-4 py-3 text-left text-sm font-medium">Status</th>
              <th className="px-4 py-3 text-right text-sm font-medium">Akcije</th>
            </tr>
          </thead>
          <tbody>
            {showNewForm && (
              <tr className="border-b bg-accent/50">
                <td className="px-4 py-3">
                  <input
                    type="text"
                    value={newForm.naziv || ''}
                    onChange={(e) => setNewForm({ ...newForm, naziv: e.target.value })}
                    placeholder="Naziv stručne spreme"
                    className="w-full rounded border bg-background px-2 py-1 text-sm"
                  />
                </td>
                <td className="px-4 py-3">
                  <input
                    type="number"
                    value={newForm.nivo || ''}
                    onChange={(e) => setNewForm({ ...newForm, nivo: parseInt(e.target.value) || null })}
                    placeholder="Nivo (1-5)"
                    min="1"
                    max="5"
                    className="w-20 rounded border bg-background px-2 py-1 text-sm"
                  />
                </td>
                <td className="px-4 py-3">
                  <span className="text-sm text-muted-foreground">Aktivno</span>
                </td>
                <td className="px-4 py-3 text-right">
                  <div className="flex items-center justify-end gap-2">
                    <button
                      onClick={handleCreateSprema}
                      className="rounded p-1 text-green-600 hover:bg-green-100"
                      title="Sačuvaj"
                    >
                      <Check size={16} />
                    </button>
                    <button
                      onClick={cancelNew}
                      className="rounded p-1 hover:bg-accent"
                      title="Otkaži"
                    >
                      <X size={16} />
                    </button>
                  </div>
                </td>
              </tr>
            )}
            {spreme.map((s) => (
              <tr key={s.id} className="border-b hover:bg-muted/50">
                <td className="px-4 py-3">
                  {editingId === s.id ? (
                    <input
                      type="text"
                      value={editForm.naziv || ''}
                      onChange={(e) => setEditForm({ ...editForm, naziv: e.target.value })}
                      className="w-full rounded border bg-background px-2 py-1 text-sm"
                    />
                  ) : (
                    <span className="font-medium">{s.naziv}</span>
                  )}
                </td>
                <td className="px-4 py-3">
                  {editingId === s.id ? (
                    <input
                      type="number"
                      value={editForm.nivo || ''}
                      onChange={(e) => setEditForm({ ...editForm, nivo: parseInt(e.target.value) || null })}
                      min="1"
                      max="5"
                      className="w-20 rounded border bg-background px-2 py-1 text-sm"
                    />
                  ) : (
                    <span className="text-sm">{s.nivo || '-'}</span>
                  )}
                </td>
                <td className="px-4 py-3">
                  {editingId === s.id ? (
                    <select
                      value={editForm.is_active ? 'true' : 'false'}
                      onChange={(e) => setEditForm({ ...editForm, is_active: e.target.value === 'true' })}
                      className="rounded border bg-background px-2 py-1 text-sm"
                    >
                      <option value="true">Aktivno</option>
                      <option value="false">Neaktivno</option>
                    </select>
                  ) : (
                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${s.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                      {s.is_active ? 'Aktivno' : 'Neaktivno'}
                    </span>
                  )}
                </td>
                <td className="px-4 py-3 text-right">
                  <div className="flex items-center justify-end gap-2">
                    {editingId === s.id ? (
                      <>
                        <button
                          onClick={() => handleUpdateSprema(s.id)}
                          className="rounded p-1 text-green-600 hover:bg-green-100"
                          title="Sačuvaj"
                        >
                          <Check size={16} />
                        </button>
                        <button
                          onClick={cancelEdit}
                          className="rounded p-1 hover:bg-accent"
                          title="Otkaži"
                        >
                          <X size={16} />
                        </button>
                      </>
                    ) : (
                      <>
                        <button
                          onClick={() => startEdit(s)}
                          className="rounded p-1 hover:bg-accent"
                          title="Izmeni"
                        >
                          <Edit size={16} />
                        </button>
                        <button
                          onClick={() => handleDeleteSprema(s.id)}
                          className="rounded p-1 text-destructive hover:bg-destructive/10"
                          title="Deaktiviraj"
                        >
                          <Trash2 size={16} />
                        </button>
                      </>
                    )}
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );

  const renderMesta = () => (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h2 className="text-lg font-semibold">Radna mesta</h2>
        <button
          onClick={startNew}
          className="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm text-primary-foreground hover:bg-primary/90"
        >
          <Plus size={16} />
          Dodaj radno mesto
        </button>
      </div>

      <div className="rounded-lg border bg-card">
        <table className="w-full">
          <thead>
            <tr className="border-b bg-muted/50">
              <th className="px-4 py-3 text-left text-sm font-medium">Naziv</th>
              <th className="px-4 py-3 text-left text-sm font-medium">Opis</th>
              <th className="px-4 py-3 text-left text-sm font-medium">Status</th>
              <th className="px-4 py-3 text-right text-sm font-medium">Akcije</th>
            </tr>
          </thead>
          <tbody>
            {showNewForm && (
              <tr className="border-b bg-accent/50">
                <td className="px-4 py-3">
                  <input
                    type="text"
                    value={newForm.naziv || ''}
                    onChange={(e) => setNewForm({ ...newForm, naziv: e.target.value })}
                    placeholder="Naziv radnog mesta"
                    className="w-full rounded border bg-background px-2 py-1 text-sm"
                  />
                </td>
                <td className="px-4 py-3">
                  <input
                    type="text"
                    value={newForm.opis || ''}
                    onChange={(e) => setNewForm({ ...newForm, opis: e.target.value })}
                    placeholder="Opis (opciono)"
                    className="w-full rounded border bg-background px-2 py-1 text-sm"
                  />
                </td>
                <td className="px-4 py-3">
                  <span className="text-sm text-muted-foreground">Aktivno</span>
                </td>
                <td className="px-4 py-3 text-right">
                  <div className="flex items-center justify-end gap-2">
                    <button
                      onClick={handleCreateMesto}
                      className="rounded p-1 text-green-600 hover:bg-green-100"
                      title="Sačuvaj"
                    >
                      <Check size={16} />
                    </button>
                    <button
                      onClick={cancelNew}
                      className="rounded p-1 hover:bg-accent"
                      title="Otkaži"
                    >
                      <X size={16} />
                    </button>
                  </div>
                </td>
              </tr>
            )}
            {mesta.map((m) => (
              <tr key={m.id} className="border-b hover:bg-muted/50">
                <td className="px-4 py-3">
                  {editingId === m.id ? (
                    <input
                      type="text"
                      value={editForm.naziv || ''}
                      onChange={(e) => setEditForm({ ...editForm, naziv: e.target.value })}
                      className="w-full rounded border bg-background px-2 py-1 text-sm"
                    />
                  ) : (
                    <span className="font-medium">{m.naziv}</span>
                  )}
                </td>
                <td className="px-4 py-3">
                  {editingId === m.id ? (
                    <input
                      type="text"
                      value={editForm.opis || ''}
                      onChange={(e) => setEditForm({ ...editForm, opis: e.target.value })}
                      className="w-full rounded border bg-background px-2 py-1 text-sm"
                    />
                  ) : (
                    <span className="text-sm text-muted-foreground">{m.opis || '-'}</span>
                  )}
                </td>
                <td className="px-4 py-3">
                  {editingId === m.id ? (
                    <select
                      value={editForm.is_active ? 'true' : 'false'}
                      onChange={(e) => setEditForm({ ...editForm, is_active: e.target.value === 'true' })}
                      className="rounded border bg-background px-2 py-1 text-sm"
                    >
                      <option value="true">Aktivno</option>
                      <option value="false">Neaktivno</option>
                    </select>
                  ) : (
                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${m.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`}>
                      {m.is_active ? 'Aktivno' : 'Neaktivno'}
                    </span>
                  )}
                </td>
                <td className="px-4 py-3 text-right">
                  <div className="flex items-center justify-end gap-2">
                    {editingId === m.id ? (
                      <>
                        <button
                          onClick={() => handleUpdateMesto(m.id)}
                          className="rounded p-1 text-green-600 hover:bg-green-100"
                          title="Sačuvaj"
                        >
                          <Check size={16} />
                        </button>
                        <button
                          onClick={cancelEdit}
                          className="rounded p-1 hover:bg-accent"
                          title="Otkaži"
                        >
                          <X size={16} />
                        </button>
                      </>
                    ) : (
                      <>
                        <button
                          onClick={() => startEdit(m)}
                          className="rounded p-1 hover:bg-accent"
                          title="Izmeni"
                        >
                          <Edit size={16} />
                        </button>
                        <button
                          onClick={() => handleDeleteMesto(m.id)}
                          className="rounded p-1 text-destructive hover:bg-destructive/10"
                          title="Deaktiviraj"
                        >
                          <Trash2 size={16} />
                        </button>
                      </>
                    )}
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );

  if (isLoading) {
    return (
      <div className="flex h-64 items-center justify-center">
        <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold">Administracija</h1>
        <p className="text-muted-foreground">
          Upravljanje organizacionom strukturom
        </p>
      </div>

      {/* Tabs */}
      <div className="flex gap-1 rounded-lg border bg-card p-1">
        {tabs.map((tab) => (
          <button
            key={tab.id}
            onClick={() => {
              setActiveTab(tab.id);
              setEditingId(null);
              setShowNewForm(false);
            }}
            className={`flex flex-1 items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-medium transition-colors ${
              activeTab === tab.id
                ? 'bg-primary text-primary-foreground'
                : 'hover:bg-accent'
            }`}
          >
            <tab.icon size={16} />
            {tab.name}
          </button>
        ))}
      </div>

      {/* Content */}
      {activeTab === 'odeljenja' && renderOdeljenja()}
      {activeTab === 'spreme' && renderSpreme()}
      {activeTab === 'mesta' && renderMesta()}
    </div>
  );
}
