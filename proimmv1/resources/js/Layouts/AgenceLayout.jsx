import { useEffect, useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    BarChart3,
    Building2,
    ChevronDown,
    Cog,
    HardHat,
    House,
    KeyRound,
    LogOut,
    Menu,
    PanelLeftClose,
    Settings2,
    UserRound,
    UsersRound,
    WalletCards,
} from 'lucide-react';
import logo from '../../../admin/logo/playstore-icon-revised.png';
import { Button } from '../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '../components/ui/dropdown-menu';
import { Separator } from '../components/ui/separator';
import { agenceButtonStyles } from '../lib/buttonStyles';
import { cn } from '../lib/utils';
const navigation = [
    {
        label: 'Tableau de bord',
        href: '/agence/dashboard',
        icon: House,
        activeMatch: '/agence/dashboard',
    },
    {
        label: 'Propriétés',
        href: '/agence/proprietes',
        icon: Building2,
        activeMatch: '/agence/proprietes',
    },
    {
        label: 'Propriétaires',
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
        label: 'Statistiques',
        href: '/agence/statistiques',
        icon: BarChart3,
        activeMatch: '/agence/statistiques',
    },
    {
        label: 'Paramétrage',
        href: '/agence/parametrage',
        icon: Settings2,
        activeMatch: '/agence/parametrage',
    },
];

export default function AgenceLayout({ title, children }) {
    const page = usePage();
    const { appName, auth, flash } = page.props;
    const currentPath = page.url.split('?')[0];
    const currentUser = auth?.user;
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [logoutConfirmOpen, setLogoutConfirmOpen] = useState(false);
    const [toast, setToast] = useState(null);

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
        if (!toast) {
            return;
        }

        const timer = window.setTimeout(() => setToast(null), 4000);
        return () => window.clearTimeout(timer);
    }, [toast]);

    const handleLogout = () => {
        router.post('/agence/logout');
    };

    const openLogoutConfirm = () => {
        setLogoutConfirmOpen(true);
    };

    const closeLogoutConfirm = () => {
        setLogoutConfirmOpen(false);
    };

    const initials = (name) =>
        String(name ?? 'Agence')
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part.slice(0, 1).toUpperCase())
            .join('') || 'AG';

    return (
        <div className="min-h-screen bg-[#f7fbfe] text-[#0f172a]">
            <Head title={title ? `${title} - ${appName}` : appName} />

            <div className="flex min-h-screen w-full">
              <aside className="fixed left-0 top-0 hidden h-screen w-72 flex-col border-r border-[#c8d4de] bg-white lg:flex">
    <div className="flex h-[73px] items-center border-b border-[#c8d4de] px-5">
        <div className="flex items-center gap-3">
            <img
                src={logo}
                alt="Pros Immobilier"
                className="h-11 w-11 rounded-2xl object-contain shadow-sm ring-1 ring-[#c8d4de]"
            />

            <div className="min-w-0">
                <h2 className="truncate text-base font-semibold text-[#0f172a]">
                    Pros Immobilier
                </h2>
                <p className="truncate text-xs text-[#5f7182]">
                    Espace agence
                </p>
            </div>
        </div>
    </div>

    <div className="flex flex-1 flex-col p-4">
        <nav className="flex flex-col gap-2">
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
                                        src={logo}
                                        alt="Pros Immobilier"
                                        className="h-10 w-10 rounded-xl object-contain shadow-sm ring-1 ring-[#c8d4de]"
                                    />
                                    <div>
                                        <p className="text-sm font-semibold text-[#0f172a]">Pros Immobilier</p>
                                        <p className="text-xs text-[#5f7182]">Espace agence</p>
                                    </div>
                                </div>

                                <Button variant="outline" size="icon" onClick={() => setMobileMenuOpen(false)}>
                                    <PanelLeftClose className="h-4 w-4" />
                                </Button>
                            </div>

                            <div className="flex h-[calc(100%-73px)] flex-col p-4">
                                <nav className="flex flex-col gap-2 overflow-y-auto">
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

                                <div className="mt-4 rounded-2xl border border-[#c8d4de] bg-[#f7fbfe] p-4">
                                    <p className="text-xs uppercase tracking-[0.24em] text-[#5f7182]">Compte connecté</p>
                                    <p className="mt-2 truncate text-sm font-semibold text-[#0f172a]">
                                        {currentUser?.name ?? 'Agence'}
                                    </p>
                                    <p className="truncate text-xs text-[#5f7182]">{currentUser?.email ?? 'N/A'}</p>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        className="mt-4 w-full rounded-xl border-[#c8d4de] hover:border-[#00559b] hover:text-[#00559b]"
                                        asChild
                                    >
                                        <Link href="/agence/profile">
                                            <UserRound className="h-4 w-4" />
                                            Mon profil
                                        </Link>
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        className="mt-3 w-full rounded-xl border-[#c8d4de] hover:border-[#00559b] hover:text-[#00559b]"
                                        onClick={openLogoutConfirm}
                                    >
                                        <LogOut className="h-4 w-4" />
                                        Se déconnecter
                                    </Button>
                                </div>
                            </div>
                        </aside>
                    </div>
                ) : null}

                <main className="flex h-screen flex-1 flex-col overflow-hidden bg-[#f7fbfe] lg:ml-72">
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

                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button
                                        variant="outline"
                                        className="h-11 rounded-xl border-[#c8d4de] bg-white px-3 text-[#0f172a]"
                                    >
                                        <span className="flex h-8 w-8 items-center justify-center rounded-full bg-[#00559b] text-sm font-semibold text-white">
                                            {initials(currentUser?.name)}
                                        </span>

                                        <span className="hidden max-w-36 flex-col items-start leading-tight sm:flex">
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
                                            <div className="flex h-9 w-9 items-center justify-center rounded-full bg-[#00559b] text-sm font-semibold text-white">
                                                {initials(currentUser?.name)}
                                            </div>

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

                                <DropdownMenuItem onClick={openLogoutConfirm} className="text-[#b42318]">
                                    <LogOut className="h-4 w-4" />
                                    <span>Se déconnecter</span>
                                </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>

                    </header>

                    <div className="flex-1 overflow-y-auto p-6">
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
                            aria-label="Fermer la confirmation de déconnexion"
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
                                        Êtes-vous sûr de vouloir vous déconnecter ?
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
                                Se déconnecter
                            </Button>
                            </div>
                        </div>
                    </div>
                ) : null}
                
            </div>

        
        </div>
    
    );
}
