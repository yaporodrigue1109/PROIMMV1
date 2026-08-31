import { Head, Link } from '@inertiajs/react';
import { ArrowDownCircle, ArrowLeft, ArrowUpCircle, Download } from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../../../components/ui/card';
import { agenceButtonStyles } from '../../../lib/buttonStyles';

const currency = (value) => new Intl.NumberFormat('fr-FR', {
    style: 'currency', currency: 'XOF', maximumFractionDigits: 0,
}).format(Number(value ?? 0));

const typeLabels = { loyer: 'Loyer', vente: 'Vente', maintenance: 'Maintenance', depense: 'Dépense' };

export default function HistoriqueDetail({ session, transactions = [] }) {
    const stats = [
        ['Solde initial', session.opening_balance],
        ['Entrées', session.entries],
        ['Sorties', session.outputs],
        ['Solde théorique', session.theoretical_balance],
        ['Solde réel', session.closing_balance],
        ['Écart', session.difference],
    ];

    return (
        <AgenceLayout title="Détails de la caisse">
            <Head title="Détails de la session de caisse" />
            <div className="mx-auto flex w-full max-w-7xl flex-col gap-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="text-2xl font-semibold text-[#0f172a]">Détails de la session de caisse</h2>
                        <p className="mt-1 text-sm text-[#5f7182]">Du {session.opened_at} au {session.closed_at || 'maintenant'} · Ouverte par {session.opened_by}</p>
                    </div>
                    <div className="flex gap-2">
                        <Button asChild className={agenceButtonStyles.primary}><a href={`/agence/caisse/historique/${session.id}/pdf`} target="_blank" rel="noreferrer"><Download className="h-4 w-4" /> Télécharger le PDF</a></Button>
                        <Button asChild variant="outline" className={agenceButtonStyles.outline}><Link href="/agence/caisse/historique"><ArrowLeft className="h-4 w-4" /> Retour à l’historique</Link></Button>
                    </div>
                </div>

                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                    {stats.map(([label, value]) => (
                        <Card key={label} className="border-[#c8d4de] shadow-sm">
                            <CardContent className="p-4"><p className="text-xs text-[#5f7182]">{label}</p><p className="mt-1 font-semibold text-[#0f172a]">{value === null ? '—' : currency(value)}</p></CardContent>
                        </Card>
                    ))}
                </div>

                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardHeader><CardTitle className="text-base text-[#0f172a]">Paiements et mouvements ({transactions.length})</CardTitle></CardHeader>
                    <CardContent className="overflow-x-auto p-0">
                        <table className="w-full min-w-[900px] text-sm">
                            <thead><tr className="border-y border-[#eef3f7] bg-[#f8fafc] text-left text-xs uppercase text-[#5f7182]">
                                {['Date', 'Sens', 'Type', 'Détail', 'N° reçu / référence', 'Mode', 'Montant'].map((label) => <th key={label} className="px-4 py-3 font-medium">{label}</th>)}
                            </tr></thead>
                            <tbody>
                                {transactions.length ? transactions.map((transaction) => (
                                    <tr key={transaction.id} className="border-b border-[#eef3f7] hover:bg-[#f8fafc]">
                                        <td className="whitespace-nowrap px-4 py-3">{transaction.date}</td>
                                        <td className="px-4 py-3">{transaction.direction === 'in' ? <ArrowDownCircle className="h-5 w-5 text-emerald-600" /> : <ArrowUpCircle className="h-5 w-5 text-red-600" />}</td>
                                        <td className="px-4 py-3"><Badge variant="outline">{typeLabels[transaction.type] || transaction.type}</Badge></td>
                                        <td className="px-4 py-3">{transaction.label}</td>
                                        <td className="px-4 py-3 font-medium">{transaction.reference}</td>
                                        <td className="px-4 py-3">{transaction.mode}</td>
                                        <td className={`px-4 py-3 text-right font-semibold ${transaction.direction === 'in' ? 'text-emerald-700' : 'text-red-700'}`}>{transaction.direction === 'in' ? '+' : '-'} {currency(transaction.amount)}</td>
                                    </tr>
                                )) : <tr><td colSpan={7} className="px-4 py-12 text-center text-[#5f7182]">Aucun paiement enregistré pendant cette session.</td></tr>}
                            </tbody>
                        </table>
                    </CardContent>
                </Card>
            </div>
        </AgenceLayout>
    );
}
