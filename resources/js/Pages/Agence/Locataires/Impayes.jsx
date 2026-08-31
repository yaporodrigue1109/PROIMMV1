import { useEffect, useRef, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Download, Eye } from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
import { agenceButtonStyles } from '../../../lib/buttonStyles';

const money = (v) => `${new Intl.NumberFormat('fr-FR').format(Number(v || 0))} FCFA`;

export default function Impayes({ locataires, filters = {}, proprietaires = [], total = 0 }) {
    const [search, setSearch] = useState(filters.search || '');
    const [proprietaireId, setProprietaireId] = useState(filters.proprietaire_id || '');
    const didMount = useRef(false);
    const params = new URLSearchParams(); if (filters.search) params.set('search', filters.search); if (filters.proprietaire_id) params.set('proprietaire_id', filters.proprietaire_id);
    const pdfUrl = `/agence/locataires/impayes/pdf${params.toString() ? `?${params}` : ''}`;
    useEffect(() => {
        if (!didMount.current) { didMount.current = true; return undefined; }
        const timeout = window.setTimeout(() => {
            router.get('/agence/locataires/impayes', { search: search || undefined, proprietaire_id: proprietaireId || undefined }, { preserveState: true, preserveScroll: true, replace: true });
        }, 300);
        return () => window.clearTimeout(timeout);
    }, [search, proprietaireId]);
    return <AgenceLayout title="Locataires en impayé"><Head title="Locataires en impayé" />
        <div className="mx-auto flex w-full max-w-7xl flex-col gap-6">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><h2 className="text-2xl font-semibold text-[#0f172a]">Locataires en impayé</h2><p className="text-sm text-[#5f7182]">Toutes les échéances impayées et partielles.</p></div><div className="flex gap-2"><Button asChild className={agenceButtonStyles.primary}><a href={pdfUrl} target="_blank" rel="noreferrer"><Download className="h-4 w-4" /> Imprimer la liste</a></Button><Button asChild variant="outline" className={agenceButtonStyles.outline}><Link href="/agence/locataires"><ArrowLeft className="h-4 w-4" /> Locataires</Link></Button></div></div>
            <Card className="border-red-200 bg-red-50"><CardContent className="p-5"><p className="text-sm text-red-700">Total général des impayés</p><p className="text-2xl font-bold text-red-800">{money(total)}</p></CardContent></Card>
            <Card className="rounded-2xl border-[#c8d4de]"><CardHeader><div className="flex flex-col gap-2 md:flex-row"><Input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Rechercher un locataire..." className="max-w-md" /><Select value={proprietaireId || '__all__'} onValueChange={(value) => setProprietaireId(value === '__all__' ? '' : value)}><SelectTrigger className="h-10 w-full rounded-xl border-[#c8d4de] md:w-[260px]"><SelectValue placeholder="Tous les propriétaires" /></SelectTrigger><SelectContent><SelectItem value="__all__">Tous les propriétaires</SelectItem>{proprietaires.map(p=><SelectItem key={p.id} value={String(p.id)}>{p.name}</SelectItem>)}</SelectContent></Select></div></CardHeader><CardContent className="overflow-x-auto p-0"><table className="w-full min-w-[760px] text-sm"><thead><tr className="border-y bg-slate-50 text-left text-xs uppercase text-slate-500">{['Locataire','Code','Téléphone','Échéances','Montant impayé','Action'].map(x=><th key={x} className="px-4 py-3">{x}</th>)}</tr></thead><tbody>{locataires.data.length ? locataires.data.map(row=><tr key={row.locataire_id} className="border-b"><td className="px-4 py-3 font-semibold">{row.name}</td><td className="px-4 py-3">{row.code}</td><td className="px-4 py-3">{row.tel1 || '—'}</td><td className="px-4 py-3">{row.impayes_count}</td><td className="px-4 py-3 font-semibold text-red-700">{money(row.montant_impaye)}</td><td className="px-4 py-3"><Button asChild size="sm" variant="outline"><Link href={`/agence/locataires/impayes/${row.locataire_id}`}><Eye className="h-4 w-4" /> Détails</Link></Button></td></tr>) : <tr><td colSpan="6" className="p-12 text-center text-slate-500">Aucun locataire en impayé.</td></tr>}</tbody></table></CardContent></Card>
        </div></AgenceLayout>;
}
