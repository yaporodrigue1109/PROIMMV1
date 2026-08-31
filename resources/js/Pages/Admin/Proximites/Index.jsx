import { router, useForm } from '@inertiajs/react';
import { MapPinned, Pencil, Plus, Search, Trash2, X } from 'lucide-react';
import { useState } from 'react';

import AdminLayout from '../../../Layouts/AdminLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';

export default function Index({ proximites = { data: [], links: [] }, filters = {}, resource = {} }) {
    const title = resource.title ?? 'Proximités globales';
    const singular = resource.singular ?? 'proximité';
    const description = resource.description ?? 'Ces éléments sont visibles par toutes les agences, en lecture seule.';
    const endpoint = resource.endpoint ?? '/admin/proximites';
    const [editing, setEditing] = useState(null);
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState(filters.search ?? '');
    const form = useForm({ name: '', description: '' });
    const rows = Array.isArray(proximites.data) ? proximites.data : [];

    const showForm = (item = null) => {
        setEditing(item);
        form.setData({ name: item?.name ?? '', description: item?.description ?? '' });
        form.clearErrors();
        setOpen(true);
    };

    const closeForm = () => {
        setOpen(false);
        setEditing(null);
        form.reset();
    };

    const submit = (event) => {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: closeForm };
        if (editing) form.put(`${endpoint}/${editing.id}`, options);
        else form.post(endpoint, options);
    };

    const runSearch = (event) => {
        event.preventDefault();
        router.get(endpoint, { search }, { preserveState: true, replace: true });
    };

    const remove = (item) => {
        if (window.confirm(`Supprimer ${singular} « ${item.name} » ?`)) {
            router.delete(`${endpoint}/${item.id}`, { preserveScroll: true });
        }
    };

    return (
        <AdminLayout title={title}>
            <div className="space-y-5">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-slate-900">{title}</h1>
                        <p className="mt-1 text-sm text-slate-500">{description}</p>
                    </div>
                    <Button onClick={() => showForm()} className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]">
                        <Plus className="h-4 w-4" /> Ajouter
                    </Button>
                </div>

                <Card className="rounded-2xl border-[#c8d4de]">
                    <CardHeader>
                        <form onSubmit={runSearch} className="flex gap-2">
                            <Input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Rechercher une proximité…" />
                            <Button type="submit" variant="outline"><Search className="h-4 w-4" /></Button>
                        </form>
                    </CardHeader>
                    <CardContent>
                        <div className="divide-y divide-slate-200">
                            {rows.map((item) => (
                                <div key={item.id} className="flex items-center justify-between gap-4 py-4">
                                    <div className="flex min-w-0 items-start gap-3">
                                        <span className="rounded-xl bg-blue-50 p-2 text-[#00559b]"><MapPinned className="h-5 w-5" /></span>
                                        <div className="min-w-0">
                                            <p className="font-semibold text-slate-900">{item.name}</p>
                                            <p className="mt-1 text-sm text-slate-500">{item.description || 'Aucune description'}</p>
                                        </div>
                                    </div>
                                    <div className="flex gap-2">
                                        <Button variant="outline" size="icon" onClick={() => showForm(item)}><Pencil className="h-4 w-4" /></Button>
                                        <Button variant="outline" size="icon" className="text-red-600" onClick={() => remove(item)}><Trash2 className="h-4 w-4" /></Button>
                                    </div>
                                </div>
                            ))}
                            {!rows.length ? <p className="py-12 text-center text-sm text-slate-500">Aucun élément global enregistré.</p> : null}
                        </div>

                        {Array.isArray(proximites.links) && proximites.links.length > 3 ? (
                            <div className="mt-5 flex flex-wrap gap-2">
                                {proximites.links.map((link, index) => (
                                    <Button key={index} variant={link.active ? 'default' : 'outline'} size="sm" disabled={!link.url} onClick={() => link.url && router.get(link.url)}>
                                        <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                    </Button>
                                ))}
                            </div>
                        ) : null}
                    </CardContent>
                </Card>
            </div>

            {open ? (
                <div className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/45 p-4">
                    <Card className="w-full max-w-lg rounded-2xl bg-white shadow-xl">
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle>{editing ? `Modifier ${singular}` : `Nouveau ${singular} global`}</CardTitle>
                            <Button variant="ghost" size="icon" onClick={closeForm}><X className="h-4 w-4" /></Button>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submit} className="space-y-4">
                                <label className="block space-y-2">
                                    <span className="text-sm font-medium">Nom *</span>
                                    <Input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
                                    {form.errors.name ? <span className="text-xs text-red-600">{form.errors.name}</span> : null}
                                </label>
                                <label className="block space-y-2">
                                    <span className="text-sm font-medium">Description</span>
                                    <textarea value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} rows={4} className="w-full rounded-xl border border-[#c8d4de] px-3 py-2 text-sm" />
                                    {form.errors.description ? <span className="text-xs text-red-600">{form.errors.description}</span> : null}
                                </label>
                                <div className="flex justify-end gap-2">
                                    <Button type="button" variant="outline" onClick={closeForm}>Annuler</Button>
                                    <Button disabled={form.processing} className="bg-[#00559b] text-white hover:bg-[#004980]">Enregistrer</Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            ) : null}
        </AdminLayout>
    );
}
