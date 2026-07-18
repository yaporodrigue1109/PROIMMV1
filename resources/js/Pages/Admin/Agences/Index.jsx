import { useEffect, useMemo, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { Building2, Search } from 'lucide-react';

import AdminLayout from '../../../Layouts/AdminLayout';

import { Avatar, AvatarFallback } from '../../../components/ui/avatar';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';
import { ScrollArea } from '../../../components/ui/scroll-area';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../../components/ui/tabs';

const statusLabel = {
    active: 'Active',
    en_demo: 'En démo',
    desactive: 'Désactivée',
};

const statusVariant = {
    active: 'default',
    en_demo: 'secondary',
    desactive: 'destructive',
};

const formatMoney = (value) =>
    new Intl.NumberFormat('fr-FR', {
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0)) + ' FCFA';

const formatDate = (value) => {
    if (!value) return 'Non défini';

    try {
        return new Date(value).toLocaleDateString('fr-FR');
    } catch {
        return value;
    }
};

const initials = (name) => {
    const parts = String(name ?? '')
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    return (
        parts
            .slice(0, 2)
            .map((part) => part[0]?.toUpperCase())
            .join('') || 'AG'
    );
};

export default function Index({ agences, agenceStats = {}, selectedAgenceId = '' }) {
    const items = agences?.data ?? agences ?? [];
    const getAgenceRouteId = (agence) => agence?.agence_id ?? agence?.code_agence ?? '';

    const [query, setQuery] = useState('');
    const [status, setStatus] = useState('tous');
    const [selectedAgence, setSelectedAgence] = useState(items[0] ?? null);
    const currentSubscription = selectedAgence?.subscription ?? selectedAgence?.abonnement ?? null;

    useEffect(() => {
        if (!items.length) {
            setSelectedAgence(null);
            return;
        }

        if (selectedAgenceId) {
            const matchedAgence = items.find((agence) => String(getAgenceRouteId(agence)) === String(selectedAgenceId));

            if (matchedAgence) {
                setSelectedAgence(matchedAgence);
                return;
            }
        }

        setSelectedAgence((current) => {
            if (current && items.some((agence) => agence.agence_id === current.agence_id)) {
                return current;
            }

            return items[0] ?? null;
        });
    }, [items, selectedAgenceId]);

    const filteredItems = useMemo(() => {
        return items.filter((agence) => {
            const searchText = [
                agence.name,
                agence.code_agence,
                agence.email1,
                agence.tel1,
                agence.responsable?.name,
            ]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            const matchesSearch =
                !query || searchText.includes(query.toLowerCase());

            const matchesStatus =
                status === 'tous' || agence.statut === status;

            return matchesSearch && matchesStatus;
        });
    }, [items, query, status]);

    const totalAgences = items.length;
    const agencesActives = items.filter((item) => item.statut === 'active').length;
    const agencesDemo = items.filter((item) => item.statut === 'en_demo').length;
    const agencesDesactivees = items.filter(
        (item) => item.statut === 'desactive'
    ).length;

    const stats = selectedAgence
        ? agenceStats[selectedAgence.agence_id] ?? {
              proprietaires: 0,
              locataires: 0,
              utilisateurs: 0,
              biens: 0,
              lots: 0,
              tickets: 0,
              tickets_resolus: 0,
          }
        : {};

    return (
        <AdminLayout title="Agences">
            <section className="space-y-6">
                <Card className="rounded-3xl border-slate-200 shadow-sm">
                    <CardContent className="flex flex-col gap-4 p-6 lg:flex-row lg:items-center lg:justify-between">
                   

                        <div className="max-w-2xl">
                       
                            <h1 className="mt-3 text-3xl font-semibold tracking-tight text-[#0f172a] md:text-4xl">
                                Gestion des agences
                            </h1>
                            <p className="mt-3 max-w-xl text-sm leading-6 text-[#5f7182] md:text-base">
                                 Consultez les agences, leurs responsables et leur état
                                d'abonnement.
                            </p>
                        </div>

                        <Button
                            asChild
                            className="h-11 rounded-xl bg-[#00559b] px-4 text-white hover:bg-[#004980]"
                        >
                            <Link href="/admin/agences/create">
                                <Building2 className="h-4 w-4" />
                                Ajouter une agence
                            </Link>
                        </Button>
                    </CardContent>
                </Card>

               <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <StatCard
                        label="Total agences"
                        value={totalAgences}
                        color="text-slate-600"
                    />

                    <StatCard
                        label="Actives"
                        value={agencesActives}
                        color="text-emerald-600"
                    />

                    <StatCard
                        label="En démo"
                        value={agencesDemo}
                        color="text-amber-600"
                    />

                    <StatCard
                        label="Désactivées"
                        value={agencesDesactivees}
                        color="text-red-600"
                    />
                </div>

                <div className="grid gap-6 xl:grid-cols-[360px_1fr]">
                    <Card className="rounded-3xl border-slate-200 shadow-sm">
                        <CardHeader className="space-y-4 border-b border-slate-200">
                            <CardTitle className="text-base">
                                Agences{' '}
                                <span className="text-slate-400">
                                    {totalAgences}
                                </span>
                            </CardTitle>

                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" />

                                <Input
                                    value={query}
                                    onChange={(event) => setQuery(event.target.value)}
                                    placeholder="Rechercher une agence…"
                                    className="h-11 rounded-2xl bg-slate-50 pl-10"
                                />
                            </div>

                            <div className="flex flex-wrap gap-2">
                                {[
                                    ['tous', 'Toutes'],
                                    ['active', 'Actives'],
                                    ['en_demo', 'En démo'],
                                    ['desactive', 'Désactivées'],
                                ].map(([key, label]) => (
                                    <Button
                                        key={key}
                                        type="button"
                                        size="sm"
                                        variant={status === key ? 'default' : 'outline'}
                                        onClick={() => setStatus(key)}
                                        className="rounded-full"
                                    >
                                        {label}
                                    </Button>
                                ))}
                            </div>
                        </CardHeader>

                        <CardContent className="p-3">
                            <ScrollArea className="h-[650px]">
                                <div className="space-y-2 pr-3">
                                    {filteredItems.map((agence) => {
                                        const isSelected =
                                            selectedAgence?.agence_id ===
                                            agence.agence_id;

                                        return (
                                            <Button
                                                key={agence.agence_id}
                                                type="button"
                                                variant="ghost"
                                                onClick={() => setSelectedAgence(agence)}
                                                className={`h-auto mt-4 w-full justify-start rounded-2xl border p-4 text-left ${
                                                    isSelected
                                                        ? 'border-[#00559b] bg-blue-50 hover:bg-blue-50'
                                                        : 'border-slate-200 bg-white hover:bg-slate-50'
                                                }`}
                                            >
                                                <div className="w-full space-y-3 ">
                                                    <div className="flex items-center justify-between gap-3">
                                                        <span className="text-xs font-semibold text-slate-500">
                                                            {agence.code_agence ?? 'N/A'}
                                                        </span>

                                                        <Badge
                                                            variant={
                                                                statusVariant[
                                                                    agence.statut
                                                                ] ?? 'secondary'
                                                            }
                                                        >
                                                            {statusLabel[
                                                                agence.statut
                                                            ] ?? agence.statut}
                                                        </Badge>
                                                    </div>

                                                    <div className="flex items-center gap-3">
                                                        <Avatar>
                                                            <AvatarFallback className="bg-slate-900 text-white">
                                                                {initials(agence.name)}
                                                            </AvatarFallback>
                                                        </Avatar>

                                                        <div className="min-w-0">
                                                            <p className="truncate text-sm font-semibold text-slate-900">
                                                                {agence.name ??
                                                                    'Agence sans nom'}
                                                            </p>

                                                            <p className="truncate text-xs text-slate-500">
                                                                {agence.responsable
                                                                    ?.name ??
                                                                    'Responsable non défini'}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </Button>
                                        );
                                    })}

                                    {filteredItems.length === 0 && (
                                        <div className="p-6 text-center text-sm text-slate-500">
                                            Aucune agence ne correspond à la recherche.
                                        </div>
                                    )}
                                </div>
                            </ScrollArea>
                        </CardContent>
                    </Card>

                    <Card className="min-h-[calc(100vh-260px)] rounded-3xl border-slate-200 shadow-sm">
                        {selectedAgence ? (
                            <>
                                <CardHeader className="flex flex-col gap-4 border-b border-slate-200 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <CardDescription className="text-xs font-semibold uppercase tracking-[0.2em]">
                                            {selectedAgence.code_agence ??
                                                selectedAgence.agence_id}
                                        </CardDescription>

                                        <CardTitle className="mt-2 text-2xl">
                                            {selectedAgence.name ?? 'Agence sans nom'}
                                        </CardTitle>
                                    </div>

                                    <div className="flex gap-2">
                                        <Button variant="outline" className="rounded-xl">
                                            {selectedAgence.statut === 'active'
                                                ? 'Désactiver'
                                                : 'Activer'}
                                        </Button>

                                        <Button
                                            type="button"
                                            onClick={() => {
                                                const agenceId = getAgenceRouteId(selectedAgence);

                                                if (!agenceId) return;

                                                router.get(`/admin/agences/${agenceId}/edit`);
                                            }}
                                            className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]"
                                        >
                                            Modifier
                                        </Button>
                                    </div>
                                </CardHeader>

                                <Tabs defaultValue="info" className="w-full">
                                    <div className=" border-slate-200 px-6 pt-5 mb-4">
                                        <TabsList>
                                            <TabsTrigger value="info">
                                                Informations
                                            </TabsTrigger>
                                            <TabsTrigger value="abonnement">
                                                Abonnement
                                            </TabsTrigger>
                                            <TabsTrigger value="life">
                                                Vie de l'agence
                                            </TabsTrigger>
                                        </TabsList>
                                    </div>

                                    <div className=" mb-4 grid gap-4 border-b border-slate-200 bg-slate-50 px-6 py-4 md:grid-cols-5">
                                        <MetaItem label="Statut">
                                            <Badge
                                                variant={
                                                    statusVariant[
                                                        selectedAgence.statut
                                                    ] ?? 'secondary'
                                                }
                                            >
                                                {statusLabel[
                                                    selectedAgence.statut
                                                ] ?? selectedAgence.statut}
                                            </Badge>
                                        </MetaItem>

                                        <MetaItem
                                            label="Abonnement"
                                            value={
                                                currentSubscription?.name ??
                                                'Aucun abonnement'
                                            }
                                        />

                                        <MetaItem
                                            label="Début"
                                            value={formatDate(
                                                selectedAgence.abonnement_start
                                            )}
                                        />

                                        <MetaItem
                                            label="Fin"
                                            value={formatDate(
                                                selectedAgence.abonnement_end
                                            )}
                                        />

                                        <MetaItem
                                            label="Total payé"
                                            value={formatMoney(
                                                selectedAgence.montant_total
                                            )}
                                        />
                                    </div>

                                    <TabsContent value="info" className="m-0">
                                        <CardContent className="space-y-6 p-6 mt-4">
                                            <Card className="rounded-2xl border-slate-200">
                                                <CardContent className=" mt-4 flex gap-4 p-5">
                                                    <Avatar className="h-16 w-16">
                                                        <AvatarFallback className="bg-slate-900 text-lg font-bold text-white">
                                                            {initials(
                                                                selectedAgence.name
                                                            )}
                                                        </AvatarFallback>
                                                    </Avatar>

                                                    <div className="grid flex-1 gap-3 md:grid-cols-2">
                                                        <Info
                                                            label="Raison sociale"
                                                            value={selectedAgence.name}
                                                        />
                                                        <Info
                                                            label="Forme juridique"
                                                            value={
                                                                selectedAgence.forme_juridique ??
                                                                selectedAgence.type_entreprise
                                                            }
                                                        />
                                                        <Info
                                                            label="N° d'identification"
                                                            value={
                                                                selectedAgence.numero_identification ??
                                                                selectedAgence.rc_number ??
                                                                selectedAgence.nif
                                                            }
                                                        />
                                                        <Info
                                                            label="Numéro de TVA"
                                                            value={
                                                                selectedAgence.tva_number ??
                                                                selectedAgence.numero_tva
                                                            }
                                                        />
                                                    </div>
                                                </CardContent>
                                            </Card>

                                            <Section title="Coordonnées">
                                                <div className="grid gap-4 md:grid-cols-2">
                                                    <Info
                                                        label="Email"
                                                        value={
                                                            selectedAgence.email1 ??
                                                            selectedAgence.email
                                                        }
                                                    />
                                                    <Info
                                                        label="Téléphone"
                                                        value={
                                                            selectedAgence.responsable
                                                                ?.tel1 ??
                                                            selectedAgence.tel1 ??
                                                            selectedAgence.phone
                                                        }
                                                    />
                                                    <Info
                                                        label="Adresse"
                                                        value={selectedAgence.adresse}
                                                        full
                                                    />
                                                </div>
                                            </Section>

                                            <Section title="Responsable">
                                                <div className="grid gap-4 md:grid-cols-2">
                                                    <Info
                                                        label="Nom"
                                                        value={
                                                            selectedAgence.responsable?.name ??
                                                            selectedAgence.responsable_name ??
                                                            'Non défini'
                                                        }
                                                    />
                                                    <Info
                                                        label="Email"
                                                        value={
                                                            selectedAgence.responsable?.email ??
                                                            selectedAgence.responsable_email ??
                                                            'Non défini'
                                                        }
                                                    />
                                                    <Info
                                                        label="Téléphone principal"
                                                        value={
                                                            selectedAgence.responsable?.tel1 ??
                                                            selectedAgence.responsable_phone ??
                                                            selectedAgence.tel1 ??
                                                            'Non défini'
                                                        }
                                                    />
                                                    <Info
                                                        label="Téléphone secondaire"
                                                        value={
                                                            selectedAgence.responsable?.tel2 ??
                                                            selectedAgence.responsable_phone2 ??
                                                            'Non défini'
                                                        }
                                                    />
                                                </div>
                                            </Section>

                                            <Section title="Informations légales">
                                                <div className="grid gap-4 md:grid-cols-2">
                                                    <Info
                                                        label="Date de création"
                                                        value={formatDate(
                                                            selectedAgence.date_creation ??
                                                                selectedAgence.created_at
                                                        )}
                                                    />
                                                    <Info
                                                        label="Capital social"
                                                        value={formatMoney(
                                                            selectedAgence.capital_social
                                                        )}
                                                    />
                                                    <Info
                                                        label="Siège social"
                                                        value={
                                                            selectedAgence.siege_social ??
                                                            selectedAgence.adresse
                                                        }
                                                        full
                                                    />
                                                </div>
                                            </Section>
                                        </CardContent>
                                    </TabsContent>

                                    <TabsContent value="abonnement" className="m-0">
                                        <CardContent className="space-y-6 p-6">
                                            <Card className="rounded-2xl border-slate-200 bg-slate-50">
                                                <CardContent className="p-5">
                                                    <Badge variant="secondary" className="mt-4">
                                                        {currentSubscription
                                                            ? 'Actif'
                                                            : 'Aucun abonnement'}
                                                    </Badge>

                                                    <h4 className="mt-4 text-xl font-semibold text-slate-900">
                                                        {currentSubscription?.name ??
                                                            'Aucun plan souscrit'}
                                                    </h4>

                                                    <p className="mt-2 text-sm text-slate-500">
                                                        {currentSubscription?.description ??
                                                            "Cette agence n'a pas encore souscrit à un abonnement."}
                                                    </p>

                                                    <div className="mt-5 text-2xl font-semibold text-slate-900">
                                                        {formatMoney(
                                                            currentSubscription?.prix_ht ??
                                                            currentSubscription?.prix ??
                                                            0
                                                        )}
                                                        <span className="text-sm font-normal text-slate-500">
                                                            {' '}
                                                            / mois
                                                        </span>
                                                    </div>
                                                </CardContent>
                                            </Card>

                                            <div className="grid gap-4 md:grid-cols-4">
                                                <MiniStat
                                                    label="Total facturé"
                                                    value={formatMoney(
                                                        selectedAgence.montant_total
                                                    )}
                                                />
                                                <MiniStat
                                                    label="Modules actifs"
                                                    value="0 / 4"
                                                />
                                                <MiniStat
                                                    label="Période en cours"
                                                    value={`${formatDate(
                                                        selectedAgence.abonnement_start
                                                    )} → ${formatDate(
                                                        selectedAgence.abonnement_end
                                                    )}`}
                                                />
                                                <MiniStat
                                                    label="Paiements réussis"
                                                    value="—"
                                                />
                                            </div>
                                        </CardContent>
                                    </TabsContent>

                                    <TabsContent value="life" className="m-0">
                                        <CardContent className="space-y-6 p-6">
                                            <div className="grid gap-4 md:grid-cols-4">
                                                <MiniStat
                                                    label="Locataires"
                                                    value={stats.locataires ?? 0}
                                                />
                                                <MiniStat
                                                    label="Propriétaires"
                                                    value={stats.proprietaires ?? 0}
                                                />
                                                <MiniStat
                                                    label="Biens"
                                                    value={stats.biens ?? 0}
                                                />
                                                <MiniStat
                                                    label="Lots"
                                                    value={stats.lots ?? 0}
                                                />
                                                <MiniStat
                                                    label="Utilisateurs"
                                                    value={stats.utilisateurs ?? 0}
                                                />
                                                <MiniStat
                                                    label="Tickets"
                                                    value={stats.tickets ?? 0}
                                                />
                                                <MiniStat
                                                    label="Tickets résolus"
                                                    value={
                                                        stats.tickets_resolus ?? 0
                                                    }
                                                />
                                                <MiniStat
                                                    label="Taux résolution"
                                                    value={
                                                        stats.tickets > 0
                                                            ? `${Math.round(
                                                                  ((stats.tickets_resolus ??
                                                                      0) /
                                                                      stats.tickets) *
                                                                      100
                                                              )}%`
                                                            : '0%'
                                                    }
                                                />
                                            </div>

                                            <Card className=" rounded-2xl border-slate-200">
                                                <CardContent className="mt-4 p-6 text-center text-sm text-slate-500">
                                                    Cliquez sur “Actualiser” pour charger
                                                    les activités.
                                                </CardContent>
                                            </Card>
                                        </CardContent>
                                    </TabsContent>
                                </Tabs>
                            </>
                        ) : (
                            <CardContent className="flex min-h-[calc(100vh-260px)] items-center justify-center">
                                <div className="text-center">
                                    <h3 className="text-xl font-semibold text-slate-900">
                                        Aucune agence à afficher
                                    </h3>

                                    <p className="mt-2 text-sm text-slate-500">
                                        Créez une agence pour voir sa fiche ici.
                                    </p>
                                </div>
                            </CardContent>
                        )}
                    </Card>
                </div>
            </section>
        </AdminLayout>
    );
}

function StatCard({ label, value, color = 'text-slate-500' }) {
    return (
        <Card className="rounded-2xl border border-slate-200 shadow-sm">
            <CardContent className="flex min-h-[140px] flex-col justify-center px-8 py-7">
                <p className={`text-sm font-semibold ${color}`}>
                    {label}
                </p>

                <p className="mt-2 text-5xl font-bold tracking-tight text-slate-900">
                    {value}
                </p>
            </CardContent>
        </Card>
    );
}

function Section({ title, children }) {
    return (
        <Card className="rounded-2xl border-slate-200">
            <CardHeader>
                <CardTitle className="text-sm uppercase tracking-[0.18em] text-slate-500">
                    {title}
                </CardTitle>
            </CardHeader>

            <CardContent>{children}</CardContent>
        </Card>
    );
}

function Info({ label, value, full = false }) {
    return (
        <div className={full ? 'md:col-span-2' : ''}>
            <p className="text-xs text-slate-500">{label}</p>
            <p className="mt-1 text-sm font-semibold text-slate-900">
                {value || 'Non spécifié'}
            </p>
        </div>
    );
}

function MetaItem({ label, value, children }) {
    return (
        <div>
            <p className="text-xs text-slate-500">{label}</p>
            <div className="mt-1 text-sm font-semibold text-slate-900">
                {children ?? value}
            </div>
        </div>
    );
}

function MiniStat({ label, value }) {
    return (
        <Card className="rounded-2xl border-slate-200 shadow-sm">
            <CardContent className="mt-4 p-5">
                <p className="text-sm text-slate-500">{label}</p>
                <p className="mt-2 text-xl font-semibold text-slate-900">
                    {value}
                </p>
            </CardContent>
        </Card>
    );
}
