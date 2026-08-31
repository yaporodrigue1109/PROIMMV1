import { useEffect, useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    BarChart3,
    BadgeCheck,
    Building2,
    ChevronDown,
    HardHat,
    House,
    CircleHelp,
    Copy,
    KeyRound,
    LogOut,
    Menu,
    PanelLeftClose,
    MessageSquareMore,
    Bell,
    Settings2,
    History,
    UserRound,
    UsersRound,
    WalletCards,
} from 'lucide-react';

import appLogo from '../../../admin/logo/playstore-icon-revised.png';
import { Button } from '../components/ui/button';
import { Card, CardContent } from '../components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '../components/ui/dropdown-menu';
import { agenceButtonStyles } from '../lib/buttonStyles';
import { cn } from '../lib/utils';

const fallbackNavigation = [
    {
        label: 'Tableau de bord',
        href: '/agence/dashboard',
        icon: House,
        activeMatch: '/agence/dashboard',
    },
    {
        label: 'Proprietes',
        href: '/agence/proprietes',
        icon: Building2,
        activeMatch: '/agence/proprietes',
    },
    {
        label: 'Proprietaires',
        href: '/agence/proprietaire',
        icon: KeyRound,
        activeMatch: '/agence/proprietaire',
    },
    {
        label: 'Locataires',
        href: '/agence/locataires',
        icon: UserRound,
        activeMatch: '/agence/locataires',
    },
    {
        label: 'Personnel',
        href: '/agence/personnel',
        icon: UsersRound,
        activeMatch: '/agence/personnel',
    },
    {
        label: 'Maintenance',
        href: '/agence/maintenance',
        icon: HardHat,
        activeMatch: '/agence/maintenance',
    },
    {
        label: 'Caisse',
        href: '/agence/caisse',
        icon: WalletCards,
        activeMatch: '/agence/caisse',
    },
    {
        label: 'Reversement',
        href: '/agence/reversement',
        icon: WalletCards,
        activeMatch: '/agence/reversement',
    },
    {
        label: 'Statistiques',
        href: '/agence/statistiques',
        icon: BarChart3,
        activeMatch: '/agence/statistiques',
    },
    {
        label: 'Support',
        href: '/agence/support',
        icon: MessageSquareMore,
        activeMatch: '/agence/support',
    },
    {
        label: 'Parametrage',
        href: '/agence/parametrage',
        icon: Settings2,
        activeMatch: '/agence/parametrage',
    },
    {
        label: 'Gestion des logs',
        href: '/agence/logs',
        icon: History,
        activeMatch: '/agence/logs',
    },
      {
        label: 'Annonces',
        href: '/agence/annonces',
        icon: Bell,
        activeMatch: '/agence/annonces',
    },
];

const navigationIcons = {
    dashboard: House,
    proprietes: Building2,
    proprietaire: KeyRound,
    proprietaires: KeyRound,
    locataires: UserRound,
    personnel: UsersRound,
    maintenance: HardHat,
    annonce: Bell,
    annonces: Bell,
    caisse: WalletCards,
    reversement: WalletCards,
    statistiques: BarChart3,
    support: MessageSquareMore,
    parametrage: Settings2,
    logs: History,
    aide: CircleHelp,
};

const subscriptionRoute = '/agence/abonnement';

const agencyLogoUrl = (value) => {
    if (!value) return '';
    const path = String(value).trim();
    if (!path) return '';

    if (/^https?:\/\//i.test(path)) {
        try {
            const url = new URL(path);
            return ['localhost', '127.0.0.1'].includes(url.hostname) ? `${url.pathname}${url.search}` : path;
        } catch {
            return path;
        }
    }

    if (path.startsWith('/')) return path;
    if (path.startsWith('storage/') || path.startsWith('admin/') || path.startsWith('assets/')) return `/${path}`;
    return `/storage/${path.replace(/^public\//, '')}`;
};

const isActiveSubscription = (agence) => {
    if (!agence?.abonnement_id || !agence?.abonnement_end) {
        return false;
    }

    const endDate = new Date(agence.abonnement_end);
    if (Number.isNaN(endDate.getTime())) {
        return false;
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    return endDate >= today;
};

function DemoModeBanner() {
    return (
        <div className="border-b border-amber-200 bg-amber-50 px-4 py-2 text-center text-xs font-medium text-amber-800">
            Mode démonstration — les données affichées sont fictives. Elles seront retirées lors de votre premier abonnement.
        </div>
    );
}

function ExpiredSubscriptionBanner() {
    return (
        <div className="border-b border-red-200 bg-red-50 px-4 py-2 text-center text-xs font-medium text-red-800">
            Abonnement expiré — l'espace est en mode consultation. Renouvelez votre abonnement pour effectuer des actions.
        </div>
    );
}

function AdminImpersonationBanner({ impersonation, responsibleName }) {
    if (!impersonation?.active) return null;

    return (
        <div className="flex shrink-0 flex-col items-start justify-between gap-2 border-b border-orange-200 bg-orange-50 px-5 py-3 text-sm text-orange-900 sm:flex-row sm:items-center md:px-8">
            <span>
                <strong>Administrateur en consultation :</strong>{' '}
                {impersonation.adminName ?? 'Administrateur'} consulte l’agence{' '}
                {impersonation.agencyName ?? 'sélectionnée'} via le compte de{' '}
                {responsibleName ?? 'son responsable'}.
            </span>
            <Button
                type="button"
                size="sm"
                className="rounded-xl bg-orange-800 font-semibold text-white hover:bg-orange-900"
                onClick={() => router.post(impersonation.stopUrl ?? '/agence/retour-administration')}
            >
                Retour à l’administration
            </Button>
        </div>
    );
}

function SubscriptionPromoCard() {
    return (
        <Card className="overflow-hidden rounded-[1.5rem] border border-[#d3dce5] bg-[linear-gradient(180deg,rgba(0,85,155,0.08)_0%,rgba(255,255,255,0.96)_100%)] shadow-[0_12px_32px_rgba(15,23,42,0.08)]">
            <CardContent className="p-4">
                <div className="mt-4 space-y-3">
                    <p className="text-sm font-semibold text-slate-900">Abonnement requis</p>
                    <p className="text-sm leading-5 text-slate-500">
                        Activez votre abonnement pour continuer a utiliser toutes les fonctionnalites de
                        l'application.
                    </p>
                </div>

                <Button
                    asChild
                    className="mt-4 h-11 w-full rounded-2xl bg-[#00559b] font-semibold text-white hover:bg-[#00457c]"
                >
                    <Link href={subscriptionRoute}>S'abonner</Link>
                </Button>
            </CardContent>
        </Card>
    );
}

function AccountFooter({ currentUser, onLogout, showAppLogo = false }) {
    const initials = (name) =>
        String(name ?? 'Agence')
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part.slice(0, 1).toUpperCase())
            .join('') || 'AG';

    return (
        <div className="mt-4 rounded-[1.5rem] border border-[#d3dce5] bg-[#f8fbfe] p-4">
            <div className="flex items-center gap-3">
                {showAppLogo ? (
                    <img src={appLogo} alt="Pros Immobilier" className="h-11 w-11 rounded-xl object-contain shadow-sm ring-1 ring-[#c8d4de]" />
                ) : (
                    <div className="flex h-11 w-11 items-center justify-center rounded-full bg-[#00559b] text-sm font-semibold text-white">
                        {initials(currentUser?.name)}
                    </div>
                )}

                <div className="min-w-0">
                    <p className="truncate text-sm font-semibold text-[#0f172a]">
                        {currentUser?.name ?? 'Agence'}
                    </p>
                    <p className="truncate text-xs text-[#5f7182]">
                        {currentUser?.email ?? 'agence@example.com'}
                    </p>
                </div>
            </div>

            <div className="mt-4 space-y-2">
                <Button asChild className="h-11 w-full rounded-xl bg-[#00559b] font-semibold text-white hover:bg-[#00457c]">
                    <Link href="/agence/abonnement/consultation">
                        <BadgeCheck className="mr-2 h-4 w-4" />
                        Mon abonnement
                    </Link>
                </Button>

                <div className="flex gap-2">
                    <Button asChild variant="outline" className="h-11 flex-1 rounded-xl border-[#c8d4de]">
                        <Link href="/agence/profile">
                            <UserRound className="mr-2 h-4 w-4" />
                            Profil
                        </Link>
                    </Button>

                    <Button variant="destructive" className="h-11 flex-1 rounded-xl" onClick={onLogout}>
                        <LogOut className="mr-2 h-4 w-4" />
                        Sortir
                    </Button>
                </div>
            </div>
        </div>
    );
}

export default function AgenceLayout({ title, children }) {
    const page = usePage();
    const { appName, auth, branding, flash, navigationModules, agencyImpersonation, agencyPreferences } = page.props;
    const currentPath = page.url.split('?')[0];
    const currentUser = auth?.user;
    const currentAgency = currentUser?.agence;
    const agencyCode = String(currentAgency?.code_agence ?? '').trim();
    const canShareAgencyCode = Boolean(agencyCode) && !currentAgency?.is_demo;
    const currentAgencyLogo = agencyLogoUrl(currentAgency?.logo);
    const hasAgencyLogo = Boolean(currentAgencyLogo);
    const brandLogo = hasAgencyLogo ? currentAgencyLogo : appLogo;
    const favicon = currentAgencyLogo || agencyLogoUrl(branding?.logoUrl) || appLogo;
    const brandName = hasAgencyLogo ? currentAgency?.name : 'Pros Immobilier';
    const hasActiveSubscription = isActiveSubscription(currentAgency);
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [logoutConfirmOpen, setLogoutConfirmOpen] = useState(false);
    const [toast, setToast] = useState(null);
    const moduleNavigation = Array.isArray(navigationModules)
        ? navigationModules.map((item) => ({
            ...item,
            icon: navigationIcons[item.slug] ?? Menu,
        }))
        : fallbackNavigation;
    const navigation = [
        ...moduleNavigation,
        ...(!moduleNavigation.some((item) => item.activeMatch === '/agence/logs') ? [{
            label: 'Gestion des logs', href: '/agence/logs', icon: History, activeMatch: '/agence/logs',
        }] : []),
        {
            label: 'Aide',
            href: '/agence/aide',
            icon: CircleHelp,
            activeMatch: '/agence/aide',
        },
    ];

    useEffect(() => {
        const fromFlash = flash?.success
            ? { type: 'success', message: flash.success }
            : flash?.error
                ? { type: 'error', message: flash.error }
                : null;

        if (fromFlash) {
            setToast(fromFlash);
        }
    }, [flash?.error, flash?.success]);

    useEffect(() => {
        const handleNotify = (event) => {
            const detail = event?.detail ?? {};
            if (!detail.message) {
                return;
            }

            setToast({
                type: detail.type === 'error' ? 'error' : 'success',
                message: detail.message,
            });
        };

        window.addEventListener('agence:notify', handleNotify);
        return () => window.removeEventListener('agence:notify', handleNotify);
    }, []);

    useEffect(() => {
        if (!agencyPreferences?.doubleValidation) return undefined;

        const confirmationMessage = 'Confirmation de sécurité : voulez-vous vraiment effectuer cette suppression ? Cette action peut être irréversible.';
        const stopBeforeVisit = router.on('before', (event) => {
            const method = String(event.detail?.visit?.method ?? '').toLowerCase();

            if (method === 'delete' && !window.confirm(confirmationMessage)) {
                event.preventDefault();
            }
        });

        const confirmNativeDelete = (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;

            const spoofedMethod = String(new FormData(form).get('_method') ?? form.method).toLowerCase();
            if (spoofedMethod === 'delete' && !window.confirm(confirmationMessage)) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        };

        document.addEventListener('submit', confirmNativeDelete, true);

        return () => {
            stopBeforeVisit();
            document.removeEventListener('submit', confirmNativeDelete, true);
        };
    }, [agencyPreferences?.doubleValidation]);

    useEffect(() => {
        if (!toast) {
            return;
        }

        const timer = window.setTimeout(() => setToast(null), 4000);
        return () => window.clearTimeout(timer);
    }, [toast]);

    const handleLogout = () => {
        router.post('/agence/logout');
    };

    const closeLogoutConfirm = () => {
        setLogoutConfirmOpen(false);
    };

    const handleCopyAgencyCode = async () => {
        if (!canShareAgencyCode) return;

        try {
            if (navigator.clipboard?.writeText && window.isSecureContext) {
                await navigator.clipboard.writeText(agencyCode);
            } else {
                const input = document.createElement('textarea');
                input.value = agencyCode;
                input.setAttribute('readonly', '');
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                input.remove();
            }

            setToast({
                type: 'success',
                message: `Code agence ${agencyCode} copié.`,
            });
        } catch {
            setToast({
                type: 'error',
                message: "Impossible de copier automatiquement le code de l'agence.",
            });
        }
    };

    return (
        <div className="min-h-screen bg-[#f7fbfe] text-[#0f172a]">
            <Head title={title ? `${title} - ${appName}` : appName}>
                <link rel="icon" href={favicon} />
                <link rel="shortcut icon" href={favicon} />
            </Head>

            <div className="flex min-h-screen w-full">
                <aside className="fixed left-0 top-0 hidden h-screen w-72 flex-col border-r border-[#c8d4de] bg-white lg:flex">
                    <div className="flex h-[73px] items-center border-b border-[#c8d4de] px-5">
                        <div className="flex items-center gap-3">
                            <img
                                src={brandLogo}
                                alt={brandName}
                                className="h-11 w-11 rounded-2xl object-contain shadow-sm ring-1 ring-[#c8d4de]"
                            />

                            <div className="min-w-0">
                                <h2 className="truncate text-base font-semibold text-[#0f172a]">
                                    {brandName}
                                </h2>
                                <p className="truncate text-xs text-[#5f7182]">
                                    {hasAgencyLogo ? 'Agence connectée' : (currentAgency?.name ?? 'Espace agence')}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="flex flex-1 flex-col overflow-hidden p-4">
                        <nav className="flex flex-col gap-2 overflow-y-auto pr-1">
                            {navigation.map((item) => {
                                const isActive =
                                    currentPath === item.activeMatch ||
                                    currentPath.startsWith(`${item.activeMatch}/`);
                                const Icon = item.icon;

                                return (
                                    <Button
                                        key={item.href}
                                        asChild
                                        variant={isActive ? 'default' : 'ghost'}
                                        className={cn(
                                            'h-11 justify-start px-3 text-sm',
                                            isActive ? agenceButtonStyles.tabActive : agenceButtonStyles.tabInactive
                                        )}
                                    >
                                        <Link href={item.href}>
                                            <Icon className="h-4 w-4" />
                                            {item.label}
                                        </Link>
                                    </Button>
                                );
                            })}
                        </nav>

                        {!hasActiveSubscription ? (
                            <div className="shrink-0 pt-4">
                                <SubscriptionPromoCard />
                            </div>
                        ) : null}
                    </div>
                </aside>

                {mobileMenuOpen ? (
                    <div className="fixed inset-0 z-50 lg:hidden">
                        <button
                            type="button"
                            aria-label="Fermer le menu"
                            className="absolute inset-0 bg-slate-950/40"
                            onClick={() => setMobileMenuOpen(false)}
                        />

                        <aside className="absolute left-0 top-0 h-full w-[86vw] max-w-sm border-r border-[#c8d4de] bg-white shadow-2xl">
                            <div className="flex items-center justify-between border-b border-[#c8d4de] px-5 py-4">
                                <div className="flex items-center gap-3">
                                    <img
                                        src={brandLogo}
                                        alt={brandName}
                                        className="h-10 w-10 rounded-xl object-contain shadow-sm ring-1 ring-[#c8d4de]"
                                    />
                                    <div className="min-w-0">
                                        <p className="max-w-52 truncate text-sm font-semibold text-[#0f172a]">{brandName}</p>
                                        <p className="max-w-52 truncate text-xs text-[#5f7182]">
                                            {hasAgencyLogo ? 'Agence connectée' : (currentAgency?.name ?? 'Espace agence')}
                                        </p>
                                    </div>
                                </div>

                                <Button variant="outline" size="icon" onClick={() => setMobileMenuOpen(false)}>
                                    <PanelLeftClose className="h-4 w-4" />
                                </Button>
                            </div>

                            <div className="flex h-[calc(100%-73px)] flex-col overflow-hidden p-4">
                                <nav className="flex flex-col gap-2 overflow-y-auto pr-1">
                                    {navigation.map((item) => {
                                        const isActive =
                                            currentPath === item.activeMatch ||
                                            currentPath.startsWith(`${item.activeMatch}/`);
                                        const Icon = item.icon;

                                        return (
                                            <Button
                                                key={item.href}
                                                asChild
                                                variant={isActive ? 'default' : 'ghost'}
                                                className={cn(
                                                    'h-11 justify-start px-3 text-sm',
                                                    isActive ? agenceButtonStyles.tabActive : agenceButtonStyles.tabInactive
                                                )}
                                                onClick={() => setMobileMenuOpen(false)}
                                            >
                                                <Link href={item.href}>
                                                    <Icon className="h-4 w-4" />
                                                    {item.label}
                                                </Link>
                                            </Button>
                                        );
                                    })}
                                </nav>

                                {!hasActiveSubscription ? (
                                    <div className="shrink-0 pt-4">
                                        <SubscriptionPromoCard />
                                    </div>
                                ) : null}

                                <AccountFooter currentUser={currentUser} onLogout={handleLogout} showAppLogo={hasAgencyLogo} />
                            </div>
                        </aside>
                    </div>
                ) : null}

                <main className="flex h-screen flex-1 flex-col overflow-hidden bg-[#f7fbfe] lg:ml-72">
                    <AdminImpersonationBanner
                        impersonation={agencyImpersonation}
                        responsibleName={currentUser?.name}
                    />
                    <header className="sticky top-0 z-30 flex h-[73px] shrink-0 items-center justify-between gap-4 border-b border-[#c8d4de] bg-white px-5 md:px-8">
                        <div className="flex min-w-0 items-center gap-3">
                            <Button variant="outline" size="icon" className="lg:hidden" onClick={() => setMobileMenuOpen(true)}>
                                <Menu className="h-4 w-4" />
                            </Button>

                            <div className="min-w-0">
                                <h1 className="truncate text-lg font-semibold text-[#0f172a]">
                                    {title ?? 'Dashboard'}
                                </h1>
                            </div>
                        </div>

                        <div className="flex shrink-0 items-center gap-2">
                            {canShareAgencyCode ? (
                                <Button
                                    type="button"
                                    variant="outline"
                                    className="h-11 rounded-xl border-[#c8d4de] bg-[#f8fbfe] px-3 text-[#0f172a] hover:bg-[#eaf4fb]"
                                    onClick={handleCopyAgencyCode}
                                    title="Copier le code de l'agence"
                                >
                                    <span className="hidden text-xs text-[#5f7182] md:inline">Code agence</span>
                                    <span className="font-mono text-sm font-bold text-[#00559b]">{agencyCode}</span>
                                    <Copy className="h-4 w-4 text-[#00559b]" />
                                </Button>
                            ) : null}

                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="outline"
                                    className="h-11 rounded-xl border-[#c8d4de] bg-white px-3 text-[#0f172a]"
                                >
                                    {hasAgencyLogo ? (
                                        <img
                                            src={appLogo}
                                            alt="Pros Immobilier"
                                            className="h-8 w-8 rounded-lg object-contain ring-1 ring-[#c8d4de]"
                                        />
                                    ) : (
                                        <span className="flex h-8 w-8 items-center justify-center rounded-full bg-[#00559b] text-sm font-semibold text-white">
                                            {String(currentUser?.name ?? 'Agence')
                                                .split(/\s+/)
                                                .filter(Boolean)
                                                .slice(0, 2)
                                                .map((part) => part.slice(0, 1).toUpperCase())
                                                .join('') || 'AG'}
                                        </span>
                                    )}

                                    <span className="hidden max-w-36 flex-col items-start leading-tight sm:flex">
                                        {agencyImpersonation?.active ? (
                                            <span className="mb-0.5 rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-orange-800">
                                                Session admin
                                            </span>
                                        ) : null}
                                        <span className="truncate text-sm font-medium">
                                            {currentUser?.name ?? 'Agence'}
                                        </span>
                                    </span>

                                    <ChevronDown className="h-4 w-4 text-[#5f7182]" />
                                </Button>
                            </DropdownMenuTrigger>

                            <DropdownMenuContent className="w-64">
                                <DropdownMenuLabel>
                                    <div className="flex items-center gap-3">
                                        {hasAgencyLogo ? (
                                            <img
                                                src={appLogo}
                                                alt="Pros Immobilier"
                                                className="h-9 w-9 rounded-lg object-contain ring-1 ring-[#c8d4de]"
                                            />
                                        ) : (
                                            <div className="flex h-9 w-9 items-center justify-center rounded-full bg-[#00559b] text-sm font-semibold text-white">
                                                {String(currentUser?.name ?? 'Agence')
                                                    .split(/\s+/)
                                                    .filter(Boolean)
                                                    .slice(0, 2)
                                                    .map((part) => part.slice(0, 1).toUpperCase())
                                                    .join('') || 'AG'}
                                            </div>
                                        )}

                                        <div className="min-w-0">
                                            <p className="truncate text-sm font-medium text-[#0f172a]">
                                                {currentUser?.name ?? 'Agence'}
                                            </p>
                                            <p className="truncate text-xs text-[#5f7182]">
                                                {currentUser?.email ?? 'agence@example.com'}
                                            </p>
                                        </div>
                                    </div>
                                </DropdownMenuLabel>

                                <DropdownMenuSeparator />

                                <DropdownMenuItem asChild>
                                    <Link href="/agence/profile" className="flex w-full items-center gap-2">
                                        <UserRound className="h-4 w-4" />
                                        <span>Mon profil</span>
                                    </Link>
                                </DropdownMenuItem>

                                <DropdownMenuItem asChild>
                                    <Link href="/agence/abonnement/consultation" className="flex w-full items-center gap-2">
                                        <BadgeCheck className="h-4 w-4" />
                                        <span>Mon abonnement</span>
                                    </Link>
                                </DropdownMenuItem>

                                <DropdownMenuItem onClick={() => setLogoutConfirmOpen(true)} className="text-[#b42318]">
                                    <LogOut className="h-4 w-4" />
                                    <span>Se deconnecter</span>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                        </div>
                    </header>

                    {currentAgency?.abonnement_expire ? <ExpiredSubscriptionBanner /> : null}
                    {currentAgency?.is_demo ? <DemoModeBanner /> : null}

                    <div id="agence-scroll-container" className="min-h-0 flex-1 overflow-y-auto p-6">
                        {toast ? (
                            <div
                                className={cn(
                                    'fixed right-6 top-6 z-[70] w-[min(92vw,24rem)] rounded-2xl border px-4 py-3 shadow-2xl',
                                    toast.type === 'error'
                                        ? 'border-rose-200 bg-rose-50 text-rose-900'
                                        : 'border-emerald-200 bg-emerald-50 text-emerald-900'
                                )}
                                role="status"
                                aria-live="polite"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <p className="text-sm font-medium">{toast.message}</p>
                                    <button
                                        type="button"
                                        className="text-xs font-semibold uppercase tracking-wide opacity-70 transition hover:opacity-100"
                                        onClick={() => setToast(null)}
                                    >
                                        Fermer
                                    </button>
                                </div>
                            </div>
                        ) : null}
                        {children}
                    </div>
                </main>

                {logoutConfirmOpen ? (
                    <div className="fixed inset-0 z-[60] flex items-center justify-center px-4">
                        <button
                            type="button"
                            aria-label="Fermer la confirmation de deconnexion"
                            className="absolute inset-0 bg-slate-950/45"
                            onClick={closeLogoutConfirm}
                        />

                        <div
                            role="dialog"
                            aria-modal="true"
                            aria-labelledby="logout-confirm-title"
                            aria-describedby="logout-confirm-description"
                            className="relative z-10 w-full max-w-md rounded-3xl border border-[#c8d4de] bg-white p-6 shadow-2xl"
                        >
                            <div className="flex items-start gap-4">
                                <div className="min-w-0">
                                    <h3
                                        id="logout-confirm-title"
                                        className="text-lg font-semibold text-[#0f172a]"
                                    >
                                        Etes-vous sur de vouloir vous deconnecter ?
                                    </h3>
                                </div>
                            </div>

                            <div className="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                <Button
                                    type="button"
                                    variant="outline"
                                    className="rounded-xl border-[#c8d4de] hover:border-[#00559b] hover:text-[#00559b]"
                                    onClick={closeLogoutConfirm}
                                >
                                    Annuler
                                </Button>
                                <Button
                                    type="button"
                                    variant="destructive"
                                    className="rounded-xl"
                                    onClick={handleLogout}
                                >
                                    <LogOut className="mr-2 h-4 w-4" />
                                    Se deconnecter
                                </Button>
                            </div>
                        </div>
                    </div>
                ) : null}
            </div>
        </div>
    );
}
