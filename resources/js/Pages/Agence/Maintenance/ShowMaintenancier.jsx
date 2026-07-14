import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    BriefcaseBusiness,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { agenceButtonStyles } from '../../../lib/buttonStyles';

const asArray = (value) => (Array.isArray(value) ? value : []);

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

function Tile({ label, value }) {
    return (
        <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
            <p className="text-xs uppercase tracking-wide text-[#94a3b8]">{label}</p>
            <div className="mt-1 text-sm font-semibold text-[#0f172a]">{value}</div>
        </div>
    );
}

export default function ShowMaintenancier({ maintenancier = {} }) {
    const interventions = asArray(maintenancier?.maintenances ?? maintenancier?.interventions);
    const available = Boolean(maintenancier?.statut);
    const initials = String(maintenancier?.name ?? 'M')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase() || 'M';

    return (
        <AgenceLayout title="Detail maintenancier">
            <Head title="Detail maintenancier" />

            <div className="mx-auto flex max-w-7xl flex-col gap-6 pb-10">
                <div className="rounded-[28px] border border-[#c8d4de] bg-white p-6 shadow-sm">
                    <div className="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div className="flex items-start gap-4">
                            <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#eaf4fb] text-[#00559b]">
                                <span className="text-lg font-bold">{initials}</span>
                            </div>

                            <div className="min-w-0">
                                <p className="text-xs uppercase tracking-[0.24em] text-[#5f7182]">Maintenancier</p>
                                <h2 className="truncate text-2xl font-semibold tracking-tight text-[#0f172a]">
                                    {maintenancier?.name ?? 'Maintenancier'}
                                </h2>
                                <p className="mt-2 flex flex-wrap items-center gap-2 text-sm text-[#5f7182]">
                                    <span className="font-medium text-[#0f172a]">{maintenancier?.entreprise ?? 'Independant'}</span>
                                    <span>·</span>
                                    <span>{maintenancier?.fonction?.name ?? 'Fonction non definie'}</span>
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
                    <Tile label="Entreprise" value={maintenancier?.entreprise ?? 'Independant'} />
                    <Tile label="Fonction" value={maintenancier?.fonction?.name ?? 'Non definie'} />
                    <Tile label="Interventions" value={interventions.length} />
                    <Tile
                        label="Disponibilite"
                        value={
                            <Badge variant={available ? 'success' : 'danger'} className="rounded-full px-2.5 py-1 text-[11px] font-medium">
                                {available ? 'Disponible' : 'Indisponible'}
                            </Badge>
                        }
                    />
                </div>

                <div className="grid grid-cols-1 gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                    <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                        <CardHeader className="border-b border-[#e2e8f0] py-4">
                            <CardTitle className="text-sm text-[#0f172a]">Coordonnees</CardTitle>
                            <CardDescription className="text-xs text-[#5f7182]">
                                Informations de contact du maintenancier.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4 p-4">
                            <Tile label="Email" value={maintenancier?.email ?? '—'} />
                            <Tile label="Telephone 1" value={maintenancier?.tel1 ?? '—'} />
                            <Tile label="Telephone 2" value={maintenancier?.tel2 ?? '—'} />
                            <Tile label="Adresse" value={maintenancier?.adresse ?? '—'} />

                            <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <Tile label="Piece" value={maintenancier?.type_piece?.name ?? maintenancier?.type_piece_id ?? '—'} />
                                <Tile label="Numero piece" value={maintenancier?.numero_piece ?? '—'} />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                        <CardHeader className="border-b border-[#e2e8f0] py-4">
                            <CardTitle className="text-sm text-[#0f172a]">Profil professionnel</CardTitle>
                            <CardDescription className="text-xs text-[#5f7182]">
                                Specialites et reperes du profil.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4 p-4">
                            <Tile label="Fonction" value={maintenancier?.fonction?.name ?? '—'} />

                            <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Specialites</p>
                                <div className="mt-3 flex flex-wrap gap-2">
                                    {String(maintenancier?.specialites ?? '')
                                        .split(',')
                                        .map((item) => item.trim())
                                        .filter(Boolean).length ? (
                                        String(maintenancier?.specialites ?? '')
                                            .split(',')
                                            .map((item) => item.trim())
                                            .filter(Boolean)
                                            .map((item) => (
                                                <Badge key={item} variant="secondary" className="rounded-full px-2.5 py-1 text-[11px] font-medium">
                                                    {item}
                                                </Badge>
                                            ))
                                    ) : (
                                        <span className="text-sm text-[#5f7182]">Aucune specialite renseignee.</span>
                                    )}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <Tile label="Creation" value={formatDateTime(maintenancier?.created_at)} />
                                <Tile label="Mise a jour" value={formatDateTime(maintenancier?.updated_at)} />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardHeader className="border-b border-[#e2e8f0] py-4">
                        <CardTitle className="text-sm text-[#0f172a]">Dernieres interventions</CardTitle>
                        <CardDescription className="text-xs text-[#5f7182]">
                            {interventions.length} intervention(s) associee(s) a ce maintenancier.
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="p-4">
                        {interventions.length ? (
                            <div className="space-y-3">
                                {interventions.map((item, index) => {
                                    const title = item?.titre ?? item?.type_intervention?.name ?? 'Intervention';
                                    const status = String(item?.statut ?? '').toLowerCase();
                                    const badgeVariant = status.includes('term') ? 'success' : status.includes('cours') ? 'warning' : status.includes('annul') ? 'danger' : 'secondary';

                                    return (
                                        <div key={String(item?.maintenance_id ?? item?.id ?? index)} className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                            <div className="flex flex-wrap items-start justify-between gap-3">
                                                <div className="min-w-0">
                                                    <p className="text-sm font-semibold text-[#0f172a]">{title}</p>
                                                    <p className="mt-1 text-xs text-[#5f7182]">
                                                        {item?.proprietaire?.name ?? item?.proprietaire ?? 'Proprietaire non defini'}
                                                    </p>
                                                </div>
                                                <Badge variant={badgeVariant} className="rounded-full px-2.5 py-1 text-[11px] font-medium">
                                                    {item?.statut ?? 'En attente'}
                                                </Badge>
                                            </div>

                                            <div className="mt-3 grid grid-cols-1 gap-3 md:grid-cols-4">
                                                <Tile label="Montant" value={new Intl.NumberFormat('fr-FR').format(Number(item?.montant_global ?? item?.prix ?? 0))} />
                                                <Tile label="Debut" value={formatDateTime(item?.date_debut)} />
                                                <Tile label="Fin" value={formatDateTime(item?.date_fin)} />
                                                <Tile label="Type" value={item?.type_intervention?.name ?? item?.type_intervention_id ?? '—'} />
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        ) : (
                            <div className="flex flex-col items-center justify-center gap-2 rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] px-4 py-10 text-center">
                                <BriefcaseBusiness className="h-6 w-6 text-[#94a3b8]" />
                                <p className="text-sm font-semibold text-[#0f172a]">Aucune intervention</p>
                                <p className="max-w-sm text-sm text-[#5f7182]">Ce maintenancier n a pas encore d historique associe.</p>
                            </div>
                        )}
                    </CardContent>
                </Card>

                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardHeader className="border-b border-[#e2e8f0] py-4">
                        <CardTitle className="text-sm text-[#0f172a]">Repere rapide</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-3 p-4 md:grid-cols-4">
                        <Tile label="Email" value={maintenancier?.email ?? '—'} />
                        <Tile label="Telephone" value={maintenancier?.tel1 ?? '—'} />
                        <Tile label="Fonction" value={maintenancier?.fonction?.name ?? '—'} />
                        <Tile label="Disponibilite" value={available ? 'Disponible' : 'Indisponible'} />
                    </CardContent>
                </Card>
            </div>
        </AgenceLayout>
    );
}
