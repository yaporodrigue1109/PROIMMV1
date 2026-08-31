import { useMemo, useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Bell, Send } from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';
import { SearchableSelect } from '../../../components/ui/searchable-select';

export default function Index({ announcements = [], properties = [], buildings = [], tenants = [] }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        title: '', message: '', target_type: 'all', target_id: '',
    });
    const [ownerId, setOwnerId] = useState('');
    const [ownerSearch, setOwnerSearch] = useState('');
    const [propertyId, setPropertyId] = useState('');
    const [search, setSearch] = useState('');
    const owners = useMemo(() => Array.from(new Map(properties.map((item) => [item.owner_id, {
        id: item.owner_id,
        name: item.owner_name,
        phone: item.owner_phone,
        phoneSecondary: item.owner_phone_secondary,
    }])).values()).sort((a, b) => a.name.localeCompare(b.name)), [properties]);
    const normalizedOwnerSearch = ownerSearch.trim().toLowerCase();
    const filteredOwners = owners.filter((item) => !normalizedOwnerSearch || `${item.name ?? ''} ${item.phone ?? ''} ${item.phoneSecondary ?? ''}`.toLowerCase().includes(normalizedOwnerSearch));
    const ownerProperties = properties.filter((item) => !ownerId || item.owner_id === ownerId);
    const propertyBuildings = buildings.filter((item) => !propertyId || item.propriete_id === propertyId);
    const normalizedSearch = search.trim().toLowerCase();
    const filteredTenants = tenants.filter((item) => !normalizedSearch || `${item.name ?? ''} ${item.tel1 ?? ''}`.toLowerCase().includes(normalizedSearch));
    const changeTargetType = (value) => {
        setData('target_type', value); setData('target_id', '');
        setOwnerId(''); setOwnerSearch(''); setPropertyId(''); setSearch('');
    };
    const submit = (event) => {
        event.preventDefault();
        post('/agence/annonces', { preserveScroll: true, onSuccess: () => reset() });
    };

    return (
        <AgenceLayout title="Annonces">
            <Head title="Annonces" />
            <div className="mx-auto max-w-6xl space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold text-slate-900">Annonces aux locataires</h1>
                    <p className="text-sm text-slate-500">Informez tous les locataires ou ciblez une propriété, un bâtiment ou une personne.</p>
                </div>
                <Card>
                    <CardHeader><CardTitle>Nouvelle annonce</CardTitle></CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="grid gap-4">
                            <Input value={data.title} onChange={(e) => setData('title', e.target.value)} placeholder="Titre de l’annonce" />
                            {errors.title && <p className="text-sm text-red-600">{errors.title}</p>}
                            <textarea className="min-h-32 rounded-xl border border-slate-300 p-3" value={data.message} onChange={(e) => setData('message', e.target.value)} placeholder="Message adressé aux locataires" />
                            {errors.message && <p className="text-sm text-red-600">{errors.message}</p>}
                            <div className="grid gap-4 md:grid-cols-2">
                                <SearchableSelect className="rounded-xl border border-slate-300 p-3" value={data.target_type} onChange={(e) => changeTargetType(e.target.value)}>
                                    <option value="all">Tous les locataires de l'agence</option>
                                    <option value="property">Une propriété</option>
                                    <option value="building">Un bâtiment</option>
                                    <option value="tenant">Un locataire</option>
                                </SearchableSelect>
                            </div>
                            {(data.target_type === 'property' || data.target_type === 'building') && (
                                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                    <Input value={ownerSearch} onChange={(e) => setOwnerSearch(e.target.value)} placeholder="Nom ou numéro du propriétaire…" />
                                    <SearchableSelect className="rounded-xl border border-slate-300 p-3" value={ownerId} onChange={(e) => { setOwnerId(e.target.value); setPropertyId(''); setData('target_id', ''); }}>
                                        <option value="">Sélectionner le propriétaire…</option>
                                        {filteredOwners.slice(0, 50).map((owner) => <option key={owner.id} value={owner.id}>{owner.name}{owner.phone ? ` — ${owner.phone}` : ''}</option>)}
                                    </SearchableSelect>
                                    <SearchableSelect disabled={!ownerId} className="rounded-xl border border-slate-300 p-3 disabled:bg-slate-100" value={propertyId || (data.target_type === 'property' ? data.target_id : '')} onChange={(e) => { setPropertyId(e.target.value); setData('target_id', data.target_type === 'property' ? e.target.value : ''); }}>
                                        <option value="">Sélectionner une propriété…</option>
                                        {ownerProperties.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                                    </SearchableSelect>
                                    {data.target_type === 'building' && (
                                        <SearchableSelect disabled={!propertyId} className="rounded-xl border border-slate-300 p-3 disabled:bg-slate-100" value={data.target_id} onChange={(e) => setData('target_id', e.target.value)}>
                                            <option value="">Sélectionner un bâtiment…</option>
                                            {propertyBuildings.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                                        </SearchableSelect>
                                    )}
                                </div>
                            )}
                            {data.target_type === 'tenant' && (
                                <div className="grid gap-4 md:grid-cols-2">
                                    <Input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Rechercher par nom ou téléphone…" />
                                    <SearchableSelect className="rounded-xl border border-slate-300 p-3" value={data.target_id} onChange={(e) => setData('target_id', e.target.value)}>
                                        <option value="">Sélectionner un locataire…</option>
                                        {filteredTenants.slice(0, 50).map((item) => <option key={item.id} value={item.id}>{item.name} — {item.tel1}</option>)}
                                    </SearchableSelect>
                                </div>
                            )}
                            {errors.target_id && <p className="text-sm text-red-600">{errors.target_id}</p>}
                            <Button disabled={processing} className="w-fit"><Send className="mr-2 h-4 w-4" />Publier</Button>
                        </form>
                    </CardContent>
                </Card>
                <div className="space-y-3">
                    {announcements.map((item) => (
                        <Card key={item.announcement_id}>
                            <CardContent className="flex gap-4 p-5">
                                <Bell className="mt-1 h-5 w-5 text-[#00559b]" />
                                <div className="flex-1"><h2 className="font-semibold">{item.title}</h2><p className="mt-1 whitespace-pre-wrap text-sm text-slate-600">{item.message}</p><p className="mt-2 text-xs text-slate-500">{item.recipients_count} destinataire(s) · {item.unread_count} non lue(s)</p></div>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </AgenceLayout>
    );
}
