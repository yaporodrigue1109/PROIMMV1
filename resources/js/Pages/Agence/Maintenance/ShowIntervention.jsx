import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    ClipboardList,
    Wrench,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { agenceButtonStyles } from '../../../lib/buttonStyles';

const currency = (value) =>
    new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const formatDateTime = (value) => {
    if (!value) return '—';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);

    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};

const asArray = (value) => (Array.isArray(value) ? value : []);

function statusMeta(status) {
    const value = String(status ?? '').toLowerCase();

    if (value.includes('cours')) return { label: 'En cours', variant: 'warning' };
    if (value.includes('term')) return { label: 'Terminee', variant: 'success' };
    if (value.includes('annul')) return { label: 'Annulee', variant: 'danger' };
    if (value.includes('plan')) return { label: 'Planifiee', variant: 'secondary' };
    return { label: 'En attente', variant: 'info' };
}

function chargeMeta(value) {
    const key = String(value ?? '').toLowerCase();
    if (key === 'proprietaire') return { label: 'Proprietaire', variant: 'warning' };
    if (key === 'locataire') return { label: 'Locataire', variant: 'info' };
    if (key === 'agence') return { label: 'Agence', variant: 'secondary' };
    return { label: 'Non precisee', variant: 'secondary' };
}

function Tile({ label, value }) {
    return (
        <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
            <p className="text-xs uppercase tracking-wide text-[#94a3b8]">{label}</p>
            <div className="mt-1 text-sm font-semibold text-[#0f172a]">{value}</div>
        </div>
    );
}

export default function ShowIntervention({ intervention = {} }) {
    const details = asArray(intervention.details);
    const status = statusMeta(intervention.statut);
    const charge = chargeMeta(intervention.prise_en_charge_par);
    const owner = intervention?.proprietaire?.name ?? 'Non defini';
    const typeIntervention = intervention?.type_intervention?.name ?? 'Type non defini';
    const progress = Number(intervention?.pourcentage_avancement ?? 0);

    return (
        <AgenceLayout title="Detail intervention">
            <Head title="Detail intervention" />

            <div className="mx-auto flex max-w-7xl flex-col gap-6 pb-10">
                <div className="rounded-[28px] border border-[#c8d4de] bg-white p-6 shadow-sm">
                    <div className="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div className="flex items-start gap-4">
                            <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#eaf4fb] text-[#00559b]">
                                <Wrench className="h-8 w-8" />
                            </div>

                            <div className="min-w-0">
                                <p className="text-xs uppercase tracking-[0.24em] text-[#5f7182]">Intervention</p>
                                <h2 className="truncate text-2xl font-semibold tracking-tight text-[#0f172a]">
                                    {intervention?.titre ?? 'Intervention sans titre'}
                                </h2>
                                <p className="mt-2 flex flex-wrap items-center gap-2 text-sm text-[#5f7182]">
                                    <span className="font-medium text-[#0f172a]">{typeIntervention}</span>
                                    <span>·</span>
                                    <Badge variant={status.variant} className="rounded-full px-2.5 py-1 text-[11px] font-medium">
                                        {status.label}
                                    </Badge>
                                </p>
                            </div>
                        </div>

                        <div className="flex flex-wrap gap-2">
                            <Button asChild variant="outline" className={agenceButtonStyles.outline}>
                                <Link href="/agence/maintenance">
                                    <ArrowLeft className="h-4 w-4" />
                                    Retour
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <Tile label="Montant total" value={currency(intervention?.montant_global)} />
                    <Tile label="Prise en charge" value={<Badge variant={charge.variant} className="rounded-full px-2.5 py-1 text-[11px] font-medium">{charge.label}</Badge>} />
                    <Tile label="Proprietaire" value={owner} />
                    <Tile label="Statut" value={<Badge variant={status.variant} className="rounded-full px-2.5 py-1 text-[11px] font-medium">{status.label}</Badge>} />
                </div>

                <div className="grid grid-cols-1 gap-6 xl:grid-cols-[1.1fr_0.95fr]">
                    <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                        <CardHeader className="border-b border-[#e2e8f0] py-4">
                            <CardTitle className="text-sm text-[#0f172a]">Informations generales</CardTitle>
                            <CardDescription className="text-xs text-[#5f7182]">
                                Les informations essentielles de l intervention.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4 p-4">
                            <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Description generale</p>
                                <p className="mt-2 text-sm leading-6 text-[#0f172a]">
                                    {intervention?.description ?? 'Aucune description'}
                                </p>
                            </div>

                            <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <Tile label="Type d intervention" value={intervention?.type_intervention?.name ?? '—'} />
                                <Tile label="Categorie" value={intervention?.type_intervention?.categorie ?? '—'} />
                                <Tile label="Maintenancier" value={intervention?.maintenancier?.name ?? intervention?.maintenancier_id ?? '—'} />
                                <Tile label="Priorite" value={intervention?.priorite ?? '—'} />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                        <CardHeader className="border-b border-[#e2e8f0] py-4">
                            <CardTitle className="text-sm text-[#0f172a]">Localisation et planning</CardTitle>
                            <CardDescription className="text-xs text-[#5f7182]">
                                Les reperes sur le bien concerne et les dates de suivi.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4 p-4">
                            <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <Tile label="Lot" value={intervention?.lot?.nom ?? intervention?.lot?.name ?? intervention?.lot_id ?? '—'} />
                                <Tile label="Batiment" value={intervention?.batiment?.nom ?? intervention?.batiment?.name ?? intervention?.batiment_id ?? '—'} />
                                <Tile label="Porte" value={intervention?.porte?.numero ?? intervention?.porte?.numero_porte ?? intervention?.porte_id ?? '—'} />
                                <Tile label="Reference" value={String(intervention?.maintenance_id ?? intervention?.id ?? '—').slice(0, 10)} />
                            </div>

                            <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <Tile label="Debut" value={formatDateTime(intervention?.date_debut)} />
                                <Tile label="Fin prevue" value={formatDateTime(intervention?.date_fin)} />
                            </div>

                            {progress ? (
                                <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                    <div className="flex items-center justify-between text-sm">
                                        <span className="text-[#5f7182]">Avancement</span>
                                        <strong className="text-[#0f172a]">{progress}%</strong>
                                    </div>
                                    <div className="mt-3 h-2 overflow-hidden rounded-full bg-[#e2e8f0]">
                                        <div className="h-full rounded-full bg-[#00559b]" style={{ width: `${progress}%` }} />
                                    </div>
                                </div>
                            ) : null}
                        </CardContent>
                    </Card>
                </div>

                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardHeader className="border-b border-[#e2e8f0] py-4">
                        <CardTitle className="text-sm text-[#0f172a]">Details des travaux</CardTitle>
                        <CardDescription className="text-xs text-[#5f7182]">
                            {details.length} ligne(s) de travail sur cette intervention.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="p-4">
                        {details.length ? (
                            <div className="space-y-3">
                                {details.map((item, index) => (
                                    <div key={String(item?.id ?? index)} className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                        <div className="flex flex-wrap items-start justify-between gap-3">
                                            <div className="min-w-0">
                                                <p className="text-sm font-semibold text-[#0f172a]">
                                                    {item?.type_intervention?.name ?? item?.type_intervention?.libelle ?? item?.type_intervention_id ?? 'Travail'}
                                                </p>
                                                <p className="mt-1 text-xs text-[#5f7182]">
                                                    {item?.description ?? 'Aucune description'}
                                                </p>
                                            </div>
                                            <Badge variant={statusMeta(item?.statut).variant} className="rounded-full px-2.5 py-1 text-[11px] font-medium">
                                                {statusMeta(item?.statut).label}
                                            </Badge>
                                        </div>

                                        <div className="mt-3 grid grid-cols-1 gap-3 md:grid-cols-4">
                                            <Tile label="Maintenancier" value={item?.maintenancier?.name ?? item?.maintenancier_id ?? '—'} />
                                            <Tile label="Prix" value={currency(item?.prix)} />
                                            <Tile label="Debut" value={formatDateTime(item?.date_debut)} />
                                            <Tile label="Fin" value={formatDateTime(item?.date_fin)} />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="flex flex-col items-center justify-center gap-2 rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] px-4 py-10 text-center">
                                <ClipboardList className="h-6 w-6 text-[#94a3b8]" />
                                <p className="text-sm font-semibold text-[#0f172a]">Aucun detail</p>
                                <p className="max-w-sm text-sm text-[#5f7182]">Cette intervention ne contient pas encore de ligne de travail.</p>
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardHeader className="border-b border-[#e2e8f0] py-4">
                        <CardTitle className="text-sm text-[#0f172a]">Repere rapide</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-3 p-4 md:grid-cols-4">
                        <Tile label="Charge" value={charge.label} />
                        <Tile label="Statut" value={status.label} />
                        <Tile label="Lot" value={intervention?.lot?.nom ?? intervention?.lot_id ?? '—'} />
                        <Tile label="Porte" value={intervention?.porte?.numero ?? intervention?.porte_id ?? '—'} />
                    </CardContent>
                </Card>
            </div>
        </AgenceLayout>
    );
}
