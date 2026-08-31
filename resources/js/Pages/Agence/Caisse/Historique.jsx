import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { ArrowLeft, Download, Filter, History, RotateCcw } from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { agenceButtonStyles } from '../../../lib/buttonStyles';
import { cn } from '../../../lib/utils';

const currency = (value) => new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XOF',
    maximumFractionDigits: 0,
}).format(Number(value ?? 0));

export default function Historique({ sessions = [], filters = {} }) {
    const [dateDebut, setDateDebut] = useState(filters.date_debut || '');
    const [dateFin, setDateFin] = useState(filters.date_fin || '');
    const query = new URLSearchParams();
    if (filters.date_debut) query.set('date_debut', filters.date_debut);
    if (filters.date_fin) query.set('date_fin', filters.date_fin);
    const pdfUrl = `/agence/caisse/historique-pdf${query.toString() ? `?${query}` : ''}`;

    const applyFilters = (event) => {
        event.preventDefault();
        router.get('/agence/caisse/historique', { date_debut: dateDebut || undefined, date_fin: dateFin || undefined }, { preserveState: true, replace: true });
    };

    const resetFilters = () => {
        const today = new Date();
        setDateDebut(new Date(today.getFullYear(), today.getMonth(), 1).toLocaleDateString('en-CA'));
        setDateFin(new Date(today.getFullYear(), today.getMonth() + 1, 0).toLocaleDateString('en-CA'));
        router.get('/agence/caisse/historique', {}, { preserveState: true, replace: true });
    };

    return (
        <AgenceLayout title="Historique de caisse">
            <Head title="Historique de caisse" />

            <div className="mx-auto flex w-full max-w-7xl flex-col gap-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="flex items-center gap-2 text-2xl font-semibold text-[#0f172a]">
                            <History className="h-6 w-6 text-[#00559b]" />
                            Historique de caisse
                        </h2>
                        <p className="mt-1 text-sm text-[#5f7182]">Consultation des sessions de caisse du mois en cours.</p>
                    </div>
                    <div className="flex gap-2">
                        <Button asChild className={agenceButtonStyles.primary}><a href={pdfUrl} target="_blank" rel="noreferrer"><Download className="h-4 w-4" /> Télécharger le PDF</a></Button>
                        <Button asChild variant="outline" className={agenceButtonStyles.outline}><Link href="/agence/caisse"><ArrowLeft className="h-4 w-4" /> Retour à la caisse</Link></Button>
                    </div>
                </div>

                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardContent className="p-4">
                        <form onSubmit={applyFilters} className="flex flex-col gap-3 md:flex-row md:items-end">
                            <label className="flex flex-1 flex-col gap-1.5"><span className="text-sm font-medium text-[#0f172a]">Date de début</span><input type="date" value={dateDebut} onChange={(e) => setDateDebut(e.target.value)} max={dateFin || undefined} className="h-11 rounded-xl border border-[#c8d4de] px-3 text-sm outline-none focus:border-[#00559b]" /></label>
                            <label className="flex flex-1 flex-col gap-1.5"><span className="text-sm font-medium text-[#0f172a]">Date de fin</span><input type="date" value={dateFin} onChange={(e) => setDateFin(e.target.value)} min={dateDebut || undefined} className="h-11 rounded-xl border border-[#c8d4de] px-3 text-sm outline-none focus:border-[#00559b]" /></label>
                            <Button type="submit" className={agenceButtonStyles.primary}><Filter className="h-4 w-4" /> Filtrer</Button>
                            <Button type="button" variant="outline" className={agenceButtonStyles.outline} onClick={resetFilters}><RotateCcw className="h-4 w-4" /> Réinitialiser</Button>
                        </form>
                    </CardContent>
                </Card>

                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base text-[#0f172a]">Sessions de caisse</CardTitle>
                        <CardDescription className="text-[#5f7182]">Ouvertures, clôtures et écarts constatés.</CardDescription>
                    </CardHeader>
                    <CardContent className="overflow-x-auto p-0">
                        <table className="w-full min-w-[1150px] text-sm">
                            <thead>
                                <tr className="border-y border-[#eef3f7] bg-[#f8fafc] text-left text-xs uppercase tracking-wide text-[#5f7182]">
                                    {['Ouverture', 'Fermeture', 'Caissier', 'Solde initial', 'Entrées', 'Sorties', 'Solde théorique', 'Solde réel', 'Écart', 'Statut', 'Action'].map((label) => (
                                        <th key={label} className="px-4 py-3 font-medium">{label}</th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {sessions.length ? sessions.map((session) => (
                                    <tr key={session.id} className="border-b border-[#eef3f7] text-[#0f172a] hover:bg-[#f8fafc]">
                                        <td className="px-4 py-3">{session.opened_at || '—'}</td>
                                        <td className="px-4 py-3">{session.closed_at || '—'}</td>
                                        <td className="px-4 py-3">
                                            <span className="block">{session.opened_by}</span>
                                            {session.closed_by ? <span className="text-xs text-[#5f7182]">Clôture : {session.closed_by}</span> : null}
                                        </td>
                                        <td className="px-4 py-3">{currency(session.opening_balance)}</td>
                                        <td className="px-4 py-3 font-medium text-[#4d8500]">+ {currency(session.entries)}</td>
                                        <td className="px-4 py-3 font-medium text-[#b42318]">- {currency(session.outputs)}</td>
                                        <td className="px-4 py-3">{currency(session.theoretical_balance)}</td>
                                        <td className="px-4 py-3">{session.closing_balance === null ? '—' : currency(session.closing_balance)}</td>
                                        <td className={cn('px-4 py-3 font-medium', Number(session.difference) === 0 ? 'text-[#4d8500]' : 'text-[#b42318]')}>
                                            {session.difference === null ? '—' : currency(session.difference)}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge className={session.status === 'Ouverte' ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-100' : 'bg-blue-100 text-blue-700 hover:bg-blue-100'}>
                                                {session.status}
                                            </Badge>
                                        </td>
                                        <td className="px-4 py-3">
                                            <Button asChild size="sm" variant="outline" className="rounded-lg border-[#c8d4de] text-[#00559b]">
                                                <Link href={`/agence/caisse/historique/${session.id}`}>Voir les détails</Link>
                                            </Button>
                                        </td>
                                    </tr>
                                )) : (
                                    <tr><td colSpan={11} className="px-4 py-12 text-center text-[#5f7182]">Aucune session de caisse enregistrée.</td></tr>
                                )}
                            </tbody>
                        </table>
                    </CardContent>
                </Card>
            </div>
        </AgenceLayout>
    );
}
