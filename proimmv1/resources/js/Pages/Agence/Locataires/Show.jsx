import { useMemo, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    CalendarDays,
    CheckCircle2,
    CircleDollarSign,
    FileText,
    Home,
    MapPin,
    Pencil,
    Phone,
    UserRound,
    Wallet,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Avatar, AvatarFallback } from '../../../components/ui/avatar';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Separator } from '../../../components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../../components/ui/tabs';
import { agenceButtonStyles } from '../../../lib/buttonStyles';
import { cn } from '../../../lib/utils';

const COLORS = {
    blue: '#00559b',
    green: '#76c206',
    greenDark: '#4d8500',
    slate: '#5f7182',
    border: '#c8d4de',
    ink: '#0f172a',
    amber: '#b45309',
};

const asArray = (value) => (Array.isArray(value) ? value : []);

const number = (value) => new Intl.NumberFormat('fr-FR').format(Number(value ?? 0));

const money = (value) => `${number(value)} FCFA`;

const months = (value) => {
    const numeric = Number(value ?? 0);
    return `${number(numeric)} mois`;
};

const formatDate = (value) => {
    if (!value) return 'Non renseigné';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);

    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(date);
};

function initials(name) {
    return String(name ?? '')
        .split(' ')
        .filter(Boolean)
        .map((word) => word[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

function normalizeContract(contract) {
    return {
        id: contract?.locataire_agence_id ?? contract?.id ?? crypto.randomUUID(),
        is_active: Boolean(contract?.is_active),
        is_new: Boolean(contract?.is_new),
        periodicite_paiement: contract?.periodicitePaiement?.name ?? contract?.periodicite_paiement?.name ?? '',
        mode_paiement: contract?.modePaiement?.name ?? contract?.mode_paiement?.name ?? '',
        montant_global_garantie: Number(contract?.montant_global_garantie ?? 0),
        loyer_net: Number(contract?.loyer_net ?? 0),
        caution: Number(contract?.caution ?? 0),
        avance: Number(contract?.avance ?? 0),
        agence: Number(contract?.agence ?? 0),
        caution_cie: Number(contract?.caution_cie ?? 0),
        caution_sodeci: Number(contract?.caution_sodeci ?? 0),
        frais_de_dossier: Number(contract?.frais_de_dossier ?? contract?.frais_annexe ?? 0),
        versements_depot_garantie: asArray(contract?.versements_depot_garantie),
        proprietaire: {
            name: contract?.proprietaire?.name ?? 'N/A',
            tel1: contract?.proprietaire?.tel1 ?? '',
        },
        propriete: {
            reference: contract?.propriete?.reference ?? 'N/A',
            adresse_complete: contract?.propriete?.adresse_complete ?? '',
        },
        batiment: {
            name: contract?.batiment?.name ?? 'N/A',
        },
        lot: {
            name: contract?.lot?.name ?? '',
        },
        porte: {
            numero_porte: contract?.porte?.numero_porte ?? 'N/A',
        },
        nbre_personne: Number(contract?.nbre_personne ?? 0),
        nbre_enfant: Number(contract?.nbre_enfant ?? 0),
        date_debut_bail: contract?.date_debut_bail ?? null,
        date_fin_bail: contract?.date_fin_bail ?? null,
    };
}

function normalizeLoyer(loyer) {
    return {
        id: loyer?.loyer_id ?? loyer?.id ?? crypto.randomUUID(),
        periode: loyer?.periode ?? '',
        statut: loyer?.statut ?? '',
        montant_a_payer: Number(loyer?.montant_a_payer ?? 0),
        montant_paye: Number(loyer?.montant_paye ?? loyer?.montant_payer ?? 0),
        montant_restant: Number(loyer?.montant_restant ?? 0),
        date_paiement: loyer?.date_paiement ?? null,
        date_limit_paiement: loyer?.date_limit_paiement ?? null,
        modePaiement: loyer?.modePaiement?.name ?? loyer?.mode_paiement ?? '',
    };
}

function normalizeTransaction(transaction) {
    return {
        id: transaction?.transaction_agence_id ?? transaction?.id ?? crypto.randomUUID(),
        montant_total_verse: Number(
            transaction?.montant_total_verse ?? transaction?.montant_loyer_payer ?? transaction?.montant_paye ?? 0
        ),
        montant_loyer_payer: Number(transaction?.montant_loyer_payer ?? 0),
        montant_arriere_paye: Number(transaction?.montant_arriere_paye ?? 0),
        date_transaction: transaction?.date_transaction ?? transaction?.created_at ?? null,
        reference_paiement: transaction?.reference_paiement ?? '',
        commentaire: transaction?.commentaire ?? '',
        modePaiement: transaction?.modePaiement?.name ?? transaction?.mode_paiement ?? '',
    };
}

function StatCard({ icon: Icon, label, value, accent = COLORS.blue, tint = '#eaf4fb' }) {
    return (
        <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardContent className="mt-6 flex items-center gap-3 p-4">
                <span
                    className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                    style={{ backgroundColor: tint, color: accent }}
                >
                    <Icon className="h-5 w-5" />
                </span>
                <div className="min-w-0">
                    <p className="text-xl font-bold text-[#0f172a]">{value}</p>
                    <p className="truncate text-[11px] uppercase tracking-wide text-[#94a3b8]">{label}</p>
                </div>
            </CardContent>
        </Card>
    );
}

function InfoRow({ icon: Icon, label, value }) {
    return (
        <div className="flex items-start gap-3 py-2.5">
            <span className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#f1f5f9] text-[#5f7182]">
                <Icon className="h-4 w-4" />
            </span>
            <div className="min-w-0">
                <p className="text-[11px] uppercase tracking-wide text-[#94a3b8]">{label}</p>
                <p className="break-words text-sm font-medium text-[#0f172a]">{value || 'Non renseigné'}</p>
            </div>
        </div>
    );
}

function StatusBadge({ contract }) {
    if (!contract) {
        return (
            <Badge variant="outline" className="rounded-full border-[#c8d4de] text-xs">
                Sans contrat
            </Badge>
        );
    }

    return contract.is_active ? (
        <Badge variant="success" className="rounded-full text-xs ring-1 ring-[#d8ebb7]">
            Actif
        </Badge>
    ) : (
        <Badge variant="danger" className="rounded-full text-xs ring-1 ring-[#fde68a]">
            Résilié
        </Badge>
    );
}

function ContractCard({ contract }) {
    return (
        <div className="rounded-2xl border border-[#c8d4de] bg-[#f8fafc] p-4">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div className="min-w-0">
                    <div className="flex flex-wrap items-center gap-2">
                        <p className="text-sm font-semibold text-[#0f172a]">{contract.propriete.reference}</p>
                        <StatusBadge contract={contract} />
                       
                    </div>
                    <p className="mt-1 text-sm text-[#5f7182]">
                        {contract.propriete.adresse_complete || 'Adresse non précisée'}
                    </p>
                    <p className="mt-1 text-xs text-[#94a3b8]">
                        {contract.batiment.name}
                        {contract.porte.numero_porte ? ` · Porte ${contract.porte.numero_porte}` : ''}
                        {contract.lot.name ? ` · ${contract.lot.name}` : ''}
                    </p>
                </div>

                <div className="flex flex-wrap gap-1.5">
                    <Badge variant="outline" className="rounded-full border-[#c8d4de] text-xs">
                        {contract.is_new ? 'Nouveau locataire' : 'Ancien locataire'}
                    </Badge>
                    {contract.periodicite_paiement ? (
                        <Badge variant="outline" className="rounded-full border-[#c8d4de] text-xs">
                            {contract.periodicite_paiement}
                        </Badge>
                    ) : null}
                    {contract.mode_paiement ? (
                        <Badge variant="outline" className="rounded-full border-[#c8d4de] text-xs">
                            {contract.mode_paiement}
                        </Badge>
                    ) : null}
                    <Badge variant="outline" className="rounded-full border-[#c8d4de] text-xs">
                        {contract.nbre_personne} personne{contract.nbre_personne > 1 ? 's' : ''}
                    </Badge>
                    <Badge variant="outline" className="rounded-full border-[#c8d4de] text-xs">
                        {contract.nbre_enfant} enfant{contract.nbre_enfant > 1 ? 's' : ''}
                    </Badge>
                </div>
            </div>

            <Separator className="my-4" />

                <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <InfoRow
                    icon={UserRound}
                    label="Type de locataire"
                    value={contract.is_new ? 'Nouveau locataire' : 'Ancien locataire'}
                />
                <InfoRow
                    icon={CalendarDays}
                    label="Périodicité de paiement"
                    value={contract.periodicite_paiement}
                />
                <InfoRow icon={Wallet} label="Mode de paiement" value={contract.mode_paiement} />
                <InfoRow icon={CalendarDays} label="Début du bail" value={formatDate(contract.date_debut_bail)} />
                <InfoRow icon={CalendarDays} label="Fin du bail" value={formatDate(contract.date_fin_bail)} />
                <InfoRow icon={Wallet} label="Loyer net" value={money(contract.loyer_net)} />
                <InfoRow icon={Wallet} label="Montant global de garantie" value={money(contract.montant_global_garantie)} />
                <InfoRow icon={Wallet} label="Caution" value={months(contract.caution)} />
                <InfoRow icon={Wallet} label="Avance" value={months(contract.avance)} />
                <InfoRow icon={Wallet} label="Frais agence" value={months(contract.agence)} />
                <InfoRow icon={Wallet} label="Caution Cie" value={money(contract.caution_cie)} />
                <InfoRow icon={Wallet} label="Caution Sodeci" value={money(contract.caution_sodeci)} />
                <InfoRow icon={Wallet} label="Frais de dossier" value={money(contract.frais_de_dossier)} />
            </div>
        </div>
    );
}

function MoneyRow({ label, value, strong = false }) {
    return (
        <div className="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-3">
            <span className="min-w-0 text-sm text-[#5f7182]">{label}</span>
            <span className={cn('whitespace-nowrap text-right text-sm', strong ? 'font-bold text-[#0f172a]' : 'font-medium text-[#0f172a]')}>
                {value}
            </span>
        </div>
    );
}

function LoyerRow({ loyer }) {
    const badge =
        loyer.statut === 'Paiement total' ? (
            <Badge variant="success" className="rounded-full text-xs ring-1 ring-[#d8ebb7]">
                Payé
            </Badge>
        ) : loyer.statut === 'Paiement partiel' ? (
            <Badge variant="warning" className="rounded-full text-xs ring-1 ring-[#fde68a]">
                Partiel
            </Badge>
        ) : loyer.statut === 'Paiement en cours' ? (
            <Badge variant="outline" className="rounded-full border-[#c8d4de] text-xs">
                En cours
            </Badge>
        ) : (
            <Badge variant="danger" className="rounded-full text-xs ring-1 ring-[#fde68a]">
                En retard
            </Badge>
        );

    return (
        <div className="rounded-2xl border border-[#e2e8f0] bg-white p-4">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p className="text-sm font-semibold text-[#0f172a]">{loyer.periode || 'Période inconnue'}</p>
                    <p className="mt-1 text-xs text-[#5f7182]">
                        Échéance: {formatDate(loyer.date_limit_paiement)}
                        {loyer.modePaiement ? ` · ${loyer.modePaiement}` : ''}
                    </p>
                </div>
                {badge}
            </div>

            <Separator className="my-4" />

            <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <MoneyRow label="À payer" value={money(loyer.montant_a_payer)} strong />
                <MoneyRow label="Payé" value={money(loyer.montant_paye)} />
                <MoneyRow label="Restant" value={money(loyer.montant_restant)} />
            </div>
        </div>
    );
}

function TransactionRow({ transaction }) {
    return (
        <div className="rounded-2xl border border-[#e2e8f0] bg-white p-4">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div className="min-w-0">
                    <p className="text-sm font-semibold text-[#0f172a]">{money(transaction.montant_total_verse)}</p>
                    <p className="mt-1 text-xs text-[#5f7182]">
                        {formatDate(transaction.date_transaction)}
                        {transaction.modePaiement ? ` · ${transaction.modePaiement}` : ''}
                    </p>
                </div>
                <Badge variant="outline" className="rounded-full border-[#c8d4de] text-xs">
                    Transaction
                </Badge>
            </div>

            <Separator className="my-4" />

            <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <MoneyRow label="Loyer" value={money(transaction.montant_loyer_payer)} />
                <MoneyRow label="Arriéré" value={money(transaction.montant_arriere_paye)} />
                <MoneyRow label="Référence" value={transaction.reference_paiement || 'Non renseigné'} />
            </div>

            {transaction.commentaire ? (
                <p className="mt-3 text-sm text-[#5f7182]">{transaction.commentaire}</p>
            ) : null}
        </div>
    );
}

export default function Show({ locataire = null }) {
    const [activeTab, setActiveTab] = useState('overview');

    const tenant = useMemo(() => {
        const contracts = asArray(locataire?.contrats).map(normalizeContract);
        const activeContract = contracts.find((item) => item.is_active) ?? contracts[0] ?? null;
        const loyers = asArray(locataire?.loyers).map(normalizeLoyer);
        const transactions = asArray(locataire?.transactions).map(normalizeTransaction);

        return {
            id: locataire?.locataire_id ?? locataire?.id ?? '',
            name: locataire?.name ?? 'N/A',
            code: locataire?.code ?? 'N/A',
            tel1: locataire?.tel1 ?? '',
            tel2: locataire?.tel2 ?? '',
            email: locataire?.email ?? '',
            adresse: locataire?.adresse ?? '',
            nationalite: locataire?.nationalite ?? '',
            profession: locataire?.profession ?? '',
            num_piece: locataire?.num_piece ?? '',
            date_naissance: locataire?.date_naissance ?? null,
            date_expiration_piece: locataire?.date_expiration_piece ?? null,
            lieu_naissance: locataire?.lieu_naissance ?? '',
            region: locataire?.region?.name ?? '',
            ville: locataire?.ville?.name ?? '',
            genre: locataire?.genre?.name ?? '',
            photo: locataire?.photo ?? '',
            image_pice: locataire?.image_pice ?? '',
            contracts,
            activeContract,
            loyers,
            transactions,
        };
    }, [locataire]);

    const totalTransactions = tenant.transactions.reduce((acc, transaction) => acc + transaction.montant_total_verse, 0);
    const totalLoyers = tenant.loyers.reduce((acc, loyer) => acc + loyer.montant_a_payer, 0);
    const totalRestant = tenant.loyers.reduce((acc, loyer) => acc + loyer.montant_restant, 0);

    const handleResilier = () => {
        if (!tenant.id) return;

        if (!window.confirm('Voulez-vous vraiment résilier le contrat actif de ce locataire ?')) {
            return;
        }

        router.patch(`/agence/locataires/${tenant.id}/resilier`, {}, {
            preserveScroll: true,
        });
    };

    return (
        <AgenceLayout title={`Locataire ${tenant.name}`}>
            <Head title={`Locataire ${tenant.name}`} />

            <div className="mx-auto flex w-full max-w-7xl flex-col gap-6 pb-10">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div className="flex flex-1 items-start gap-3">
                        <Button asChild type="button" variant="outline" size="icon" className="rounded-xl border-[#c8d4de]">
                            <Link href="/agence/locataires">
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>

                        <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-2">
                                <h1 className="truncate text-2xl font-semibold text-[#0f172a]">{tenant.name}</h1>
                                <StatusBadge contract={tenant.activeContract} />
                            </div>
                            <p className="mt-1 font-mono text-xs text-[#5f7182]">{tenant.code}</p>
                            <p className="mt-1 text-sm text-[#5f7182]">
                                {tenant.profession || 'Profession non renseignée'}
                                {tenant.ville ? ` · ${tenant.ville}` : ''}
                            </p>
                        </div>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        <Button asChild className={agenceButtonStyles.primary}>
                            <Link href={`/agence/locataires/${tenant.id}/edit`}>
                                <Pencil className="mr-2 h-4 w-4" /> Modifier
                            </Link>
                        </Button>

                        {tenant.activeContract?.is_active ? (
                            <Button
                                type="button"
                                variant="outline"
                                className={agenceButtonStyles.outline}
                                onClick={handleResilier}
                            >
                                <CircleDollarSign className="mr-2 h-4 w-4" />
                                Résilier
                            </Button>
                        ) : null}
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <StatCard
                        icon={FileText}
                        label="Contrats"
                        value={number(tenant.contracts.length)}
                        accent={COLORS.blue}
                        tint="#eaf4fb"
                    />
                    <StatCard
                        icon={Wallet}
                        label="Loyers"
                        value={money(totalLoyers)}
                        accent={COLORS.greenDark}
                        tint="#eef8df"
                    />
                    <StatCard
                        icon={CircleDollarSign}
                        label="Transactions"
                        value={money(totalTransactions)}
                        accent={COLORS.amber}
                        tint="#fffbeb"
                    />
                    <StatCard
                        icon={CheckCircle2}
                        label="Restant à payer"
                        value={money(totalRestant)}
                        accent="#b42318"
                        tint="#fff4f4"
                    />
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm lg:col-span-1">
                        <CardHeader className="border-b border-[#e2e8f0] py-4">
                            <CardTitle className="text-sm text-[#0f172a]">Informations personnelles</CardTitle>
                        </CardHeader>
                        <CardContent className="mt-6 p-5">
                            <div className="flex items-center gap-3 rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-3">
                                <Avatar className="h-12 w-12 border border-[#c8d4de] bg-[#eaf4fb]">
                                    <AvatarFallback className="bg-[#eaf4fb] text-sm font-bold text-[#00559b]">
                                        {initials(tenant.name)}
                                    </AvatarFallback>
                                </Avatar>
                                <div className="min-w-0">
                                    <p className="truncate text-sm font-semibold text-[#0f172a]">{tenant.name}</p>
                                    <p className="truncate text-xs text-[#5f7182]">{tenant.email || 'Aucun e-mail'}</p>
                                </div>
                            </div>

                            <Separator className="mt-6" />

                            <InfoRow icon={Phone} label="Téléphone 1" value={tenant.tel1} />
                            <InfoRow icon={Phone} label="Téléphone 2" value={tenant.tel2} />
                            <InfoRow icon={MapPin} label="Adresse" value={tenant.adresse} />
                            <InfoRow icon={UserRound} label="Genre" value={tenant.genre} />
                            <InfoRow icon={Home} label="Nationalité" value={tenant.nationalite} />
                            <InfoRow icon={CalendarDays} label="Date de naissance" value={formatDate(tenant.date_naissance)} />
                            <InfoRow icon={CalendarDays} label="Expiration pièce" value={formatDate(tenant.date_expiration_piece)} />
                            <InfoRow icon={FileText} label="Numéro de pièce" value={tenant.num_piece} />
                            <InfoRow icon={MapPin} label="Lieu de naissance" value={tenant.lieu_naissance} />
                            <InfoRow icon={MapPin} label="Région / Ville" value={[tenant.region, tenant.ville].filter(Boolean).join(' / ')} />
                        </CardContent>
                    </Card>

                    <div className="lg:col-span-2">
                        <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
                            <TabsList className="mb-4 flex h-auto w-full flex-wrap justify-start gap-1 bg-[#eef2f6] p-1">
                                <TabsTrigger value="overview" className="data-[state=active]:bg-white data-[state=active]:text-[#00559b]">
                                    Vue d’ensemble
                                </TabsTrigger>
                                <TabsTrigger value="contrats" className="data-[state=active]:bg-white data-[state=active]:text-[#00559b]">
                                    Contrats
                                </TabsTrigger>
                                <TabsTrigger value="loyers" className="data-[state=active]:bg-white data-[state=active]:text-[#00559b]">
                                    Loyers
                                </TabsTrigger>
                                <TabsTrigger value="transactions" className="data-[state=active]:bg-white data-[state=active]:text-[#00559b]">
                                    Transactions
                                </TabsTrigger>
                            </TabsList>

                            <TabsContent value="overview" className="mt-0 space-y-4">
                                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                                    <CardHeader className="border-b border-[#e2e8f0] py-4">
                                        <CardTitle className="text-sm text-[#0f172a]">Contrat actif</CardTitle>
                                        <CardDescription className="text-xs text-[#5f7182]">
                                            Le contrat utilisé pour l’accès rapide au bail en cours.
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="mt-4 p-5">
                                        {tenant.activeContract ? (
                                            <>
                                                <ContractCard contract={tenant.activeContract} />
                                                {tenant.activeContract.is_new && asArray(tenant.activeContract.versements_depot_garantie).length ? (
                                                    <div className="mt-4 rounded-2xl border border-[#e2e8f0] bg-white p-4">
                                                        <p className="text-sm font-semibold text-[#0f172a]">
                                                            Versements du dépôt de garantie
                                                        </p>
                                                        <div className="mt-3 space-y-2">
                                                            {asArray(tenant.activeContract.versements_depot_garantie).map((versement, index) => (
                                                                <div
                                                                    key={`depot-${index}`}
                                                                    className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] px-3 py-2 text-sm text-[#0f172a]"
                                                                >
                                                                    {money(versement.montant ?? 0)} · {formatDate(versement.date_versement)} ·{' '}
                                                                    {versement.modePaiement?.name ??
                                                                        versement.mode_paiement?.name ??
                                                                        (versement.mode_paiement_id ? `Mode #${versement.mode_paiement_id}` : 'Non renseigné')}
                                                                </div>
                                                            ))}
                                                        </div>
                                                    </div>
                                                ) : null}
                                            </>
                                        ) : (
                                            <div className="rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] px-4 py-10 text-center">
                                                <p className="text-sm font-semibold text-[#0f172a]">Aucun contrat actif</p>
                                                <p className="text-sm text-[#5f7182]">
                                                    Ce locataire n’a pas de contrat actif dans cette agence.
                                                </p>
                                            </div>
                                        )}
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="contrats" className="mt-0 space-y-4">
                                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                                    <CardHeader className="border-b border-[#e2e8f0] py-4">
                                        <CardTitle className="text-sm text-[#0f172a]">Historique des contrats</CardTitle>
                                        <CardDescription className="text-xs text-[#5f7182]">
                                            Tous les baux synchronisés pour ce locataire.
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="mt-4 space-y-4 p-5">
                                        {tenant.contracts.length ? (
                                            tenant.contracts.map((contract) => <ContractCard key={contract.id} contract={contract} />)
                                        ) : (
                                            <div className="rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] px-4 py-10 text-center">
                                                <p className="text-sm font-semibold text-[#0f172a]">Aucun contrat</p>
                                                <p className="text-sm text-[#5f7182]">Aucun contrat n’a été trouvé pour ce locataire.</p>
                                            </div>
                                        )}
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="loyers" className="mt-0 space-y-4">
                                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                                    <CardHeader className="border-b border-[#e2e8f0] py-4">
                                        <CardTitle className="text-sm text-[#0f172a]">Loyers</CardTitle>
                                        <CardDescription className="text-xs text-[#5f7182]">
                                            Derniers enregistrements de paiement du locataire.
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="mt-4 space-y-4 p-5">
                                        {tenant.loyers.length ? (
                                            tenant.loyers.map((loyer) => <LoyerRow key={loyer.id} loyer={loyer} />)
                                        ) : (
                                            <div className="rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] px-4 py-10 text-center">
                                                <p className="text-sm font-semibold text-[#0f172a]">Aucun loyer</p>
                                                <p className="text-sm text-[#5f7182]">Aucune ligne de loyer n’a été synchronisée.</p>
                                            </div>
                                        )}
                                    </CardContent>
                                </Card>
                            </TabsContent>

                            <TabsContent value="transactions" className="mt-0 space-y-4">
                                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                                    <CardHeader className="border-b border-[#e2e8f0] py-4">
                                        <CardTitle className="text-sm text-[#0f172a]">Transactions</CardTitle>
                                        <CardDescription className="text-xs text-[#5f7182]">
                                            Historique récent des versements liés au locataire.
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="mt-4 space-y-4 p-5">
                                        {tenant.transactions.length ? (
                                            tenant.transactions.map((transaction) => (
                                                <TransactionRow key={transaction.id} transaction={transaction} />
                                            ))
                                        ) : (
                                            <div className="rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] px-4 py-10 text-center">
                                                <p className="text-sm font-semibold text-[#0f172a]">Aucune transaction</p>
                                                <p className="text-sm text-[#5f7182]">
                                                    Aucune transaction n’a encore été enregistrée pour ce locataire.
                                                </p>
                                            </div>
                                        )}
                                    </CardContent>
                                </Card>
                            </TabsContent>
                        </Tabs>
                    </div>
                </div>
            </div>
        </AgenceLayout>
    );
}
