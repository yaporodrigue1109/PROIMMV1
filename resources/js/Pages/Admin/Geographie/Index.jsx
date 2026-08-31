import { router, useForm } from '@inertiajs/react';
import { Globe2, Map, MapPin, Pencil, Plus, Trash2, X } from 'lucide-react';
import { useMemo, useState } from 'react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';
import { SearchableSelect } from '../../../components/ui/searchable-select';

const configs = {
    pays: { title: 'Pays', singular: 'pays', icon: Globe2, endpoint: 'pays' },
    regions: { title: 'Régions', singular: 'région', icon: Map, endpoint: 'regions' },
    villes: { title: 'Villes', singular: 'ville', icon: MapPin, endpoint: 'villes' },
};

export default function Index({ pays = [], regions = [], villes = [] }) {
    const [tab, setTab] = useState('pays');
    const [editing, setEditing] = useState(null);
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState('');
    const form = useForm({ name: '', iso2: '', indicatif: '', actif: true, pays_id: '', region_id: '' });
    const cfg = configs[tab];
    const rows = { pays, regions, villes }[tab] ?? [];
    const filtered = useMemo(() => rows.filter((row) => `${row.name} ${row.iso2 ?? ''} ${row.pays?.name ?? ''} ${row.region?.name ?? ''}`.toLowerCase().includes(search.toLowerCase())), [rows, search]);
    const regionsByCountry = useMemo(() => {
        return filtered.reduce((groups, region) => {
            const country = region.pays?.name ?? 'Sans pays';
            if (!groups[country]) groups[country] = [];
            groups[country].push(region);
            return groups;
        }, {});
    }, [filtered]);

    const showForm = (item = null) => {
        setEditing(item); form.setData({ name: item?.name ?? '', iso2: item?.iso2 ?? '', indicatif: item?.indicatif ?? '', actif: item?.actif ?? true, pays_id: item?.pays_id ?? '', region_id: item?.region_id ?? '' }); form.clearErrors(); setOpen(true);
    };
    const close = () => { setOpen(false); setEditing(null); form.reset(); };
    const submit = (e) => { e.preventDefault(); const url = `/admin/geographie/${cfg.endpoint}${editing ? `/${editing.id}` : ''}`; form[editing ? 'put' : 'post'](url, { preserveScroll: true, onSuccess: close }); };
    const remove = (item) => window.confirm(`Supprimer ${cfg.singular} « ${item.name} » ?`) && router.delete(`/admin/geographie/${cfg.endpoint}/${item.id}`, { preserveScroll: true });

    return <AdminLayout title="Pays, régions et villes"><div className="space-y-5">
        <div><h1 className="text-2xl font-bold text-slate-900">Gestion géographique</h1><p className="mt-1 text-sm text-slate-500">Gérez les pays, leurs régions et leurs villes.</p></div>
        <div className="flex flex-wrap gap-2">{Object.entries(configs).map(([key, item]) => <Button key={key} variant={tab === key ? 'default' : 'outline'} onClick={() => { setTab(key); setSearch(''); }} className={tab === key ? 'bg-[#00559b]' : ''}>{item.title}</Button>)}</div>
        <Card className="rounded-2xl border-[#c8d4de]"><CardHeader className="flex flex-row items-center gap-3"><Input value={search} onChange={(e) => setSearch(e.target.value)} placeholder={`Rechercher ${cfg.singular}…`} /><Button onClick={() => showForm()} className="bg-[#00559b] text-white"><Plus className="h-4 w-4" /> Ajouter</Button></CardHeader><CardContent>
            {tab === 'regions' ? <div className="space-y-6">
                {Object.entries(regionsByCountry).sort(([a], [b]) => a.localeCompare(b, 'fr')).map(([country, countryRegions]) => <section key={country} className="overflow-hidden rounded-2xl border border-slate-200">
                    <div className="flex items-center justify-between bg-[#eef6fc] px-4 py-3"><div className="flex items-center gap-2 font-semibold text-[#00559b]"><Globe2 className="h-4 w-4" />{country}</div><span className="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-600">{countryRegions.length} région(s)</span></div>
                    <div className="grid grid-cols-[minmax(0,1fr)_130px_100px] border-b bg-slate-50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500"><span>Région</span><span className="text-center">Villes</span><span className="text-right">Actions</span></div>
                    <div className="divide-y divide-slate-100">{countryRegions.sort((a, b) => a.name.localeCompare(b.name, 'fr')).map((item) => <div key={item.id} className="grid grid-cols-[minmax(0,1fr)_130px_100px] items-center px-4 py-3"><div className="flex min-w-0 items-center gap-3"><span className="rounded-lg bg-blue-50 p-2 text-[#00559b]"><Map className="h-4 w-4" /></span><span className="truncate font-medium text-slate-900">{item.name}</span></div><span className="text-center text-sm text-slate-600">{item.villes_count ?? 0}</span><div className="flex justify-end gap-2"><Button variant="outline" size="icon" onClick={() => showForm(item)}><Pencil className="h-4 w-4" /></Button><Button variant="outline" size="icon" className="text-red-600" onClick={() => remove(item)}><Trash2 className="h-4 w-4" /></Button></div></div>)}</div>
                </section>)}
                {!filtered.length && <p className="py-10 text-center text-sm text-slate-500">Aucune région trouvée.</p>}
            </div> : <div className="divide-y">
                {filtered.map((item) => { const Icon = cfg.icon; return <div key={item.id} className="flex items-center justify-between gap-4 py-4"><div className="flex items-center gap-3"><span className="rounded-xl bg-blue-50 p-2 text-[#00559b]"><Icon className="h-5 w-5" /></span><div><p className="font-semibold">{item.name}</p><p className="text-xs text-slate-500">{tab === 'pays' ? `${item.iso2} · ${item.indicatif} · ${item.regions_count ?? 0} région(s)` : item.region?.name ?? 'Sans région'}</p></div></div><div className="flex gap-2"><Button variant="outline" size="icon" onClick={() => showForm(item)}><Pencil className="h-4 w-4" /></Button><Button variant="outline" size="icon" className="text-red-600" onClick={() => remove(item)}><Trash2 className="h-4 w-4" /></Button></div></div>; })}
                {!filtered.length && <p className="py-10 text-center text-sm text-slate-500">Aucun résultat.</p>}
            </div>}
        </CardContent></Card>
        {open && <div className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/45 p-4"><Card className="w-full max-w-lg"><CardHeader className="flex flex-row items-center justify-between"><CardTitle>{editing ? 'Modifier' : 'Ajouter'} {cfg.singular}</CardTitle><Button variant="ghost" size="icon" onClick={close}><X className="h-4 w-4" /></Button></CardHeader><CardContent><form onSubmit={submit} className="space-y-4">
            <Field label="Nom *" error={form.errors.name}><Input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} /></Field>
            {tab === 'pays' && <><Field label="Code ISO (2 lettres) *" error={form.errors.iso2}><Input maxLength={2} value={form.data.iso2} onChange={(e) => form.setData('iso2', e.target.value.toUpperCase())} /></Field><Field label="Indicatif téléphonique *" error={form.errors.indicatif}><Input placeholder="+225" value={form.data.indicatif} onChange={(e) => form.setData('indicatif', e.target.value)} /></Field><label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={Boolean(form.data.actif)} onChange={(e) => form.setData('actif', e.target.checked)} /> Pays actif</label></>}
            {tab === 'regions' && <Field label="Pays *" error={form.errors.pays_id}><SearchableSelect value={form.data.pays_id} onChange={(e) => form.setData('pays_id', e.target.value)}><option value="">Sélectionner</option>{pays.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}</SearchableSelect></Field>}
            {tab === 'villes' && <Field label="Région *" error={form.errors.region_id}><SearchableSelect value={form.data.region_id} onChange={(e) => form.setData('region_id', e.target.value)}><option value="">Sélectionner</option>{regions.map((r) => <option key={r.id} value={r.id}>{r.name} — {r.pays?.name}</option>)}</SearchableSelect></Field>}
            <div className="flex justify-end gap-2"><Button type="button" variant="outline" onClick={close}>Annuler</Button><Button disabled={form.processing} className="bg-[#00559b] text-white">Enregistrer</Button></div>
        </form></CardContent></Card></div>}
    </div></AdminLayout>;
}

function Field({ label, error, children }) { return <label className="block space-y-2"><span className="text-sm font-medium">{label}</span>{children}{error && <span className="text-xs text-red-600">{error}</span>}</label>; }
