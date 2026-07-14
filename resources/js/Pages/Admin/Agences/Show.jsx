import { Link } from '@inertiajs/react';
import { Building2, ChevronLeft, PencilLine } from 'lucide-react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';

const statusTone = {
    active: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    en_demo: 'bg-amber-50 text-amber-700 border-amber-200',
    desactive: 'bg-rose-50 text-rose-700 border-rose-200',
};

const statusLabel = {
    active: 'Active',
    en_demo: 'En démo',
    desactive: 'Désactivée',
};

const formatMoney = (value) =>
    new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Number(value ?? 0)) + ' FCFA';

export default function Show({ agence, transactions = [], totalEncaisse = 0 }) {
    const items = Array.isArray(transactions?.data) ? transactions.data : transactions;
    const abonnement = agence?.abonnement;
    const responsable = agence?.responsable;
    const region = agence?.region;
    const ville = agence?.ville;

    return (
        <AdminLayout title="Détail agence">
            <div className="space-y-6">
                <Card className="border-slate-200 shadow-sm">
                    <CardContent className="flex flex-col gap-4 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div className="flex items-center gap-4">
                            <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#00559b] text-lg font-semibold text-white">
                                {String(agence?.name ?? 'AG').slice(0, 2).toUpperCase()}
                            </div>
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-[0.26em] text-slate-500">Agence</p>
                                <h1 className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{agence?.name ?? 'Agence sans nom'}</h1>
                                <p className="mt-1 text-sm text-slate-500">{agence?.code_agence ?? 'N/A'}</p>
                            </div>
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <Button asChild variant="outline" className="h-11 rounded-xl border-slate-200 px-4 text-slate-900">
                                <Link href="/admin/agences">
                                    <ChevronLeft className="h-4 w-4" />
                                    Retour
                                </Link>
                            </Button>
                            <Button asChild className="h-11 rounded-xl bg-[#00559b] px-4 text-white hover:bg-[#004980]">
                                <Link href={`/admin/agences/${agence?.agence_id}/edit`}>
                                    <PencilLine className="h-4 w-4" />
                                    Modifier
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {[
                        ['Formule', abonnement?.name ?? 'Aucun abonnement'],
                        ['Statut', statusLabel[agence?.statut] ?? agence?.statut],
                        ['Responsable', responsable?.name ?? 'Non défini'],
                        ['Total encaissé', formatMoney(totalEncaisse)],
                    ].map(([label, value]) => (
                        <Card key={label} className="border-slate-200 shadow-sm">
                            <CardContent className="p-5">
                                <p className="text-sm text-slate-500">{label}</p>
                                <p className="mt-2 text-lg font-semibold text-slate-900">{value}</p>
                            </CardContent>
                        </Card>
                    ))}
                </section>

                <div className="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
                    <Card className="border-slate-200 shadow-sm">
                        <CardHeader className="border-b border-slate-200">
                            <CardTitle className="text-xl text-slate-900">Informations générales</CardTitle>
                            <CardDescription className="text-slate-500">Données administratives et localisation</CardDescription>
                        </CardHeader>
                        <CardContent className="p-0">
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <tbody className="divide-y divide-slate-200">
                                        <Row label="Nom agence" value={agence?.name ?? 'Non défini'} />
                                        <Row label="Code" value={agence?.code_agence ?? 'N/A'} />
                                        <Row label="Email agence" value={agence?.email1 ?? 'N/A'} />
                                        <Row label="Téléphone agence" value={agence?.tel1 ?? 'N/A'} />
                                        <Row label="Adresse" value={agence?.adresse ?? 'Non définie'} />
                                        <Row label="Région" value={region?.name ?? 'Non définie'} />
                                        <Row label="Ville" value={ville?.name ?? 'Non définie'} />
                                        <Row label="Durée abonnement" value={agence?.duree_mois ? `${agence.duree_mois} mois` : 'N/A'} />
                                        <Row label="Date création" value={agence?.created_at ? new Date(agence.created_at).toLocaleString('fr-FR') : 'N/A'} />
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-slate-200 shadow-sm">
                        <CardHeader className="border-b border-slate-200">
                            <CardTitle className="text-xl text-slate-900">Aperçu abonnement</CardTitle>
                            <CardDescription className="text-slate-500">Plan en cours et modules activés</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4 p-6">
                            <div className={`inline-flex rounded-full border px-3 py-1 text-xs font-medium ${statusTone[agence?.statut] ?? statusTone.en_demo}`}>
                                {statusLabel[agence?.statut] ?? agence?.statut}
                            </div>
                            <div>
                                <p className="text-sm text-slate-500">Plan</p>
                                <p className="mt-1 text-lg font-semibold text-slate-900">{abonnement?.name ?? 'Aucun abonnement'}</p>
                            </div>
                            <div>
                                <p className="text-sm text-slate-500">Description</p>
                                <p className="mt-1 text-sm text-slate-600">{abonnement?.description ?? 'Aucune description disponible.'}</p>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                {(agence?.modules_payants ?? []).length > 0 ? (
                                    agence.modules_payants.map((module) => (
                                        <Badge key={module.nom ?? module.name} variant="outline" className="rounded-full border-emerald-200 bg-emerald-50 text-emerald-700">
                                            {module.nom ?? module.name}
                                        </Badge>
                                    ))
                                ) : (
                                    <Badge variant="outline" className="rounded-full border-slate-200 bg-slate-50 text-slate-600">
                                        Aucun module actif
                                    </Badge>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card className="border-slate-200 shadow-sm">
                    <CardHeader className="border-b border-slate-200">
                        <CardTitle className="text-xl text-slate-900">Transactions récentes</CardTitle>
                        <CardDescription className="text-slate-500">Dernières opérations liées à l'agence</CardDescription>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-slate-50 text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                    <tr>
                                        <th className="px-6 py-4 font-medium">Référence</th>
                                        <th className="px-6 py-4 font-medium">Période</th>
                                        <th className="px-6 py-4 font-medium">Montant</th>
                                        <th className="px-6 py-4 font-medium">Statut</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-200">
                                    {items.map((transaction) => (
                                        <tr key={transaction.transaction_id}>
                                            <td className="px-6 py-4 text-slate-900">{transaction.reference ?? transaction.transaction_id}</td>
                                            <td className="px-6 py-4 text-slate-600">
                                                {transaction.periode_debut ? new Date(transaction.periode_debut).toLocaleDateString('fr-FR') : 'N/A'}
                                                {' '}→{' '}
                                                {transaction.periode_fin ? new Date(transaction.periode_fin).toLocaleDateString('fr-FR') : 'N/A'}
                                            </td>
                                            <td className="px-6 py-4 font-medium text-slate-900">{formatMoney(transaction.montant_ttc)}</td>
                                            <td className="px-6 py-4">
                                                <Badge
                                                    variant="outline"
                                                    className={`rounded-full ${
                                                        transaction.statut === 'validee'
                                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                                            : transaction.statut === 'en_attente'
                                                            ? 'border-amber-200 bg-amber-50 text-amber-700'
                                                            : 'border-slate-200 bg-slate-50 text-slate-600'
                                                    }`}
                                                >
                                                    {transaction.statut ?? 'Inconnu'}
                                                </Badge>
                                            </td>
                                        </tr>
                                    ))}
                                    {items.length === 0 ? (
                                        <tr>
                                            <td colSpan="4" className="px-6 py-10 text-center text-slate-500">
                                                Aucune transaction disponible.
                                            </td>
                                        </tr>
                                    ) : null}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}

function Row({ label, value }) {
    return (
        <tr>
            <th className="w-1/3 px-6 py-4 text-left font-medium text-slate-500">{label}</th>
            <td className="px-6 py-4 text-slate-900">{value}</td>
        </tr>
    );
}
