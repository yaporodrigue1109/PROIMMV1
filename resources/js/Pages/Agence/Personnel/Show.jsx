import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    BadgeCheck,
    BriefcaseBusiness,
    Building2,
    CalendarDays,
    Clock3,
    Mail,
    MapPin,
    Pencil,
    Phone,
    ShieldCheck,
    UserRound,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Avatar, AvatarFallback } from '../../../components/ui/avatar';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Separator } from '../../../components/ui/separator';
import { agenceButtonStyles } from '../../../lib/buttonStyles';

const asArray = (value) => (Array.isArray(value) ? value : []);

const formatDate = (value) => {
    if (!value) return '—';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return String(value);
    }

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

function currentPhotoUrl(personnel) {
    if (!personnel?.photo) {
        return '';
    }

    if (String(personnel.photo).startsWith('http')) {
        return personnel.photo;
    }

    return `/storage/${personnel.photo}`;
}

function statBadge(statut) {
    if (statut === 'actif') {
        return <Badge variant="success" className="rounded-full text-xs ring-1 ring-[#d8ebb7]">Actif</Badge>;
    }

    if (statut === 'suspendu') {
        return <Badge variant="warning" className="rounded-full text-xs ring-1 ring-[#fde68a]">Suspendu</Badge>;
    }

    return <Badge variant="danger" className="rounded-full text-xs">Inactif</Badge>;
}

function permissionLabel(permission) {
    const labels = {
        view_proprietes: 'Voir les propriétés',
        create_proprietes: 'Créer des propriétés',
        edit_proprietes: 'Modifier des propriétés',
        view_contrats: 'Consulter les contrats',
        create_contrats: 'Créer des contrats',
        edit_contrats: 'Modifier des contrats',
        view_locataires: 'Voir les locataires',
        create_locataires: 'Créer des locataires',
        edit_locataires: 'Modifier les locataires',
        view_proprietaires: 'Voir les propriétaires',
        create_proprietaires: 'Créer des propriétaires',
        edit_proprietaires: 'Modifier les propriétaires',
        view_rapports: 'Voir les rapports',
        export_rapports: 'Exporter les rapports',
        view_caisse: 'Consulter la caisse',
        view_loyer: 'Consulter les loyers',
        view_reversement: 'Voir les reversements',
        manage_personnel: 'Gérer le personnel',
    };

    return labels[permission] ?? String(permission).replaceAll('_', ' ');
}

function InfoRow({ icon: Icon, label, value }) {
    return (
        <div className="flex items-start gap-3 py-2.5">
            <span className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#f1f5f9] text-[#5f7182]">
                <Icon className="h-4 w-4" />
            </span>
            <div className="min-w-0">
                <p className="text-[11px] uppercase tracking-wide text-[#94a3b8]">{label}</p>
                <p className="break-words text-sm font-medium text-[#0f172a]">{value || '—'}</p>
            </div>
        </div>
    );
}

function StatCard({ icon: Icon, label, value, accent = '#00559b', tint = '#eaf4fb' }) {
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

function SectionCard({ icon: Icon, title, description, children, action }) {
    return (
        <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardHeader className="flex flex-row items-center justify-between gap-3 border-b border-[#e2e8f0] py-4">
                <div className="flex items-center gap-3">
                    <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eaf4fb] text-[#00559b]">
                        <Icon className="h-5 w-5" />
                    </span>
                    <div>
                        <CardTitle className="text-sm text-[#0f172a]">{title}</CardTitle>
                        {description ? <CardDescription className="text-xs text-[#5f7182]">{description}</CardDescription> : null}
                    </div>
                </div>
                {action}
            </CardHeader>
            <CardContent className="p-6">{children}</CardContent>
        </Card>
    );
}

export default function Show({ personnel = null, permissions = [] }) {
    const profilePhoto = currentPhotoUrl(personnel);
    const permissionList = asArray(permissions);
    const roleName = personnel?.role?.name ?? personnel?.role_id ?? 'Personnel';

    return (
        <AgenceLayout title="Fiche personnel">
            <Head title="Fiche personnel" />

            <div className="mx-auto flex w-full max-w-7xl flex-col gap-6 pb-10">
                <div className="flex flex-col gap-4 rounded-3xl border border-[#c8d4de] bg-white p-5 shadow-sm lg:flex-row lg:items-center lg:justify-between">
                    <div className="flex items-center gap-4">
                        <Avatar className="h-20 w-20 border border-[#c8d4de] bg-[#eaf4fb]">
                            {profilePhoto ? (
                                <img src={profilePhoto} alt={personnel?.name ?? 'Personnel'} className="h-full w-full object-cover" />
                            ) : (
                                <AvatarFallback className="bg-[#eaf4fb] text-base font-bold text-[#00559b]">
                                    {initials(personnel?.name)}
                                </AvatarFallback>
                            )}
                        </Avatar>

                        <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-2">
                                <h1 className="truncate text-2xl font-semibold text-[#0f172a]">
                                    {personnel?.name ?? 'Personnel'}
                                </h1>
                                {statBadge(personnel?.statut)}
                            </div>

                            <p className="mt-1 text-sm text-[#5f7182]">
                                {roleName}
                                {personnel?.agence?.name ? ` • ${personnel.agence.name}` : ''}
                            </p>

                            <div className="mt-3 flex flex-wrap gap-2">
                                <Badge variant="outline" className="rounded-full border-[#c8d4de] text-xs">
                                    <ShieldCheck className="mr-1 h-3 w-3" />
                                    {permissionList.length} permission{permissionList.length > 1 ? 's' : ''}
                                </Badge>
                                <Badge variant="outline" className="rounded-full border-[#c8d4de] text-xs">
                                    <BriefcaseBusiness className="mr-1 h-3 w-3" />
                                    {personnel?.role_id ?? 'Sans rôle'}
                                </Badge>
                                <Badge variant="outline" className="rounded-full border-[#c8d4de] text-xs">
                                    <BadgeCheck className="mr-1 h-3 w-3" />
                                    {personnel?.is_responsable ? 'Responsable' : 'Personnel'}
                                </Badge>
                            </div>
                        </div>
                    </div>

                    <div className="flex flex-wrap gap-3">
                        <Button asChild variant="outline" className={agenceButtonStyles.outline}>
                            <Link href="/agence/personnel">
                                <ArrowLeft className="h-4 w-4" />
                                Retour
                            </Link>
                        </Button>

                        <Button asChild className={agenceButtonStyles.primary}>
                            <Link href={personnel?.id_users ? `/agence/personnel/${personnel.id_users}/edit` : '/agence/personnel'}>
                                <Pencil className="h-4 w-4" />
                                Modifier
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard icon={UserRound} label="Statut" value={personnel?.statut ?? 'actif'} accent="#4d8500" tint="#eef8df" />
                    <StatCard icon={ShieldCheck} label="Permissions" value={String(permissionList.length)} accent="#b45309" tint="#fffbeb" />
                    <StatCard icon={Building2} label="Agence" value={personnel?.agence?.name ?? 'Non liée'} accent="#00559b" tint="#eaf4fb" />
                    <StatCard icon={Clock3} label="Créé le" value={formatDate(personnel?.created_at)} accent="#5f7182" tint="#f1f5f9" />
                </div>

                <div className="grid grid-cols-1 gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                    <div className="space-y-6">
                        <SectionCard
                            icon={UserRound}
                            title="Informations personnelles"
                            description="Identité et coordonnées du membre."
                        >
                            <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <InfoRow icon={UserRound} label="Nom complet" value={personnel?.name} />
                                <InfoRow icon={BriefcaseBusiness} label="Rôle" value={personnel?.role?.name ?? personnel?.role_id} />
                                <InfoRow icon={Phone} label="Téléphone principal" value={personnel?.tel1} />
                                <InfoRow icon={Phone} label="Téléphone secondaire" value={personnel?.tel2} />
                                <InfoRow icon={Mail} label="Email" value={personnel?.email} />
                                <InfoRow icon={MapPin} label="Adresse" value={personnel?.adresse} />
                            </div>
                        </SectionCard>

                        <SectionCard
                            icon={ShieldCheck}
                            title="Accès et sécurité"
                            description="Informations liées au compte et aux autorisations."
                        >
                            <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <InfoRow icon={BadgeCheck} label="Compte" value={personnel?.statut ?? 'actif'} />
                                <InfoRow icon={BadgeCheck} label="Responsable d'agence" value={personnel?.is_responsable ? 'Oui' : 'Non'} />
                                <InfoRow icon={CalendarDays} label="Créé le" value={formatDate(personnel?.created_at)} />
                                <InfoRow icon={CalendarDays} label="Mis à jour le" value={formatDate(personnel?.updated_at)} />
                            </div>

                            <Separator className="my-5" />

                            <div>
                                <div className="mb-3 flex items-center justify-between">
                                    <div>
                                        <p className="text-sm font-semibold text-[#0f172a]">Permissions</p>
                                        <p className="text-xs text-[#5f7182]">
                                            Permissions calculées à partir du rôle du personnel.
                                        </p>
                                    </div>
                                </div>

                                {permissionList.length > 0 ? (
                                    <div className="flex flex-wrap gap-2">
                                        {permissionList.map((permission) => (
                                            <Badge key={permission} variant="outline" className="rounded-full border-[#c8d4de] text-xs">
                                                {permissionLabel(permission)}
                                            </Badge>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] p-4 text-sm text-[#5f7182]">
                                        Aucune permission calculée pour ce rôle.
                                    </div>
                                )}
                            </div>
                        </SectionCard>
                    </div>

                    <div className="space-y-6">
                        <SectionCard
                            icon={Building2}
                            title="Agence"
                            description="Affectation actuelle du personnel."
                        >
                            <div className="space-y-1.5">
                                <p className="text-sm font-semibold text-[#0f172a]">
                                    {personnel?.agence?.name ?? 'Aucune agence associée'}
                                </p>
                                <p className="text-sm text-[#5f7182]">
                                    {personnel?.agence?.adresse ?? 'Adresse non renseignée'}
                                </p>
                            </div>
                        </SectionCard>

                        <SectionCard
                            icon={Clock3}
                            title="Chronologie"
                            description="Repères de création et de mise à jour."
                        >
                            <div className="space-y-4">
                                <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                    <p className="text-[11px] uppercase tracking-wide text-[#94a3b8]">Date de création</p>
                                    <p className="mt-1 text-sm font-medium text-[#0f172a]">{formatDate(personnel?.created_at)}</p>
                                </div>

                                <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                    <p className="text-[11px] uppercase tracking-wide text-[#94a3b8]">Dernière mise à jour</p>
                                    <p className="mt-1 text-sm font-medium text-[#0f172a]">{formatDate(personnel?.updated_at)}</p>
                                </div>
                            </div>
                        </SectionCard>
                    </div>
                </div>
            </div>
        </AgenceLayout>
    );
}
