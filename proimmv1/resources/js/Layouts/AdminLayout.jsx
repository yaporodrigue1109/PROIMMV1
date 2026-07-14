import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import logo from '../../../admin/logo/playstore-icon-revised.png';
import {
    BarChart3,
    Building2,
    ChevronDown,
    CircleGauge,
    Layers3,
    LogOut,
    PanelLeftClose,
    Menu,
    Settings,
    Ticket,
    LifeBuoy,
    UserRound,
} from 'lucide-react';
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
import { cn } from '../lib/utils';

const navigation = [
    {
        section: 'Principaux',
        items: [
            { label: 'Tableau de bord', href: '/admin/dashboard', icon: BarChart3, activeMatch: '/admin/dashboard' },
            { label: 'Agences', href: '/admin/agences', icon: Building2, activeMatch: '/admin/agences' },
            { label: 'Abonnements', href: '/admin/abonnements', icon: Ticket, activeMatch: '/admin/abonnements' },
        ],
    },
    {
        section: 'Administration',
        items: [
            { label: 'Modules', href: '/admin/modules', icon: Layers3, activeMatch: '/admin/modules' },
            { label: 'Configuration', href: '/admin/settings', icon: Settings, activeMatch: '/admin/settings' },
            { label: 'Statistiques', href: '/admin/statistiques', icon: CircleGauge, activeMatch: '/admin/statistiques' },
            { label: 'Tickets', href: '/admin/tickets', icon: LifeBuoy, activeMatch: '/admin/tickets' },
        ],
    },
];

export default function AdminLayout({ title, children }) {
    const page = usePage();
    const { auth, appName, admin, flash } = page.props;
    const currentPath = page.url.split('?')[0];
    const currentAdmin = admin ?? auth?.admin;
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    const handleLogout = () => {
        router.post('/admin/logout');
    };

    const closeMobileMenu = () => {
        setMobileMenuOpen(false);
    };

    return (
        <div className="min-h-screen bg-transparent text-[#0f172a]">
            <Head title={title ? `${title} - ${appName}` : appName} />

            <div className="mx-auto flex min-h-screen w-full max-w-[1680px] gap-2 p-3">
                <aside className="hidden w-64 shrink-0 lg:block">
                    <Card className="fixed left-3 top-3 flex h-[calc(100vh-1.5rem)] w-64 flex-col rounded-2xl border-[#c8d4de] bg-white/95 shadow-sm backdrop-blur">
                        <CardHeader className="space-y-3 p-4">
                            <div className="flex items-center gap-3">
                                <img
                                    src={logo}
                                    alt="Pros Immobilier"
                                    className="h-10 w-10 rounded-xl object-contain shadow-sm ring-1 ring-[#c8d4de]"
                                />

                                <div className="min-w-0">
                                    <CardTitle className="truncate text-base">Pros Immobilier</CardTitle>
                                    <CardDescription className="truncate text-xs">Espace d'administration</CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent className="flex flex-1 flex-col gap-3 p-4">
                            <Separator />

                            <nav className="flex flex-col gap-4">
                                {navigation.map((group) => (
                                    <div key={group.section} className="space-y-2">
                                        <p className="px-3 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#5f7182]">
                                            {group.section}
                                        </p>

                                        <div className="flex flex-col gap-2">
                                            {group.items.map((item) => {
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
                                                            'h-10 justify-start rounded-xl px-3 text-sm',
                                                            isActive
                                                                ? 'bg-[#00559b] text-white hover:bg-[#004980]'
                                                                : 'text-[#0f172a]'
                                                        )}
                                                    >
                                                        <Link href={item.href}>
                                                            <Icon className="h-4 w-4" />
                                                            {item.label}
                                                        </Link>
                                                    </Button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                ))}
                            </nav>

                        </CardContent>
                    </Card>
                </aside>

                {mobileMenuOpen ? (
                    <div className="fixed inset-0 z-50 lg:hidden">
                        <button
                            type="button"
                            aria-label="Fermer le menu"
                            className="absolute inset-0 bg-slate-950/40"
                            onClick={closeMobileMenu}
                        />

                        <aside className="absolute left-0 top-0 h-full w-[85vw] max-w-sm border-r border-[#c8d4de] bg-white shadow-2xl">
                            <div className="flex items-center justify-between border-b border-[#c8d4de] px-5 py-4">
                                <div className="flex items-center gap-3">
                                    <img
                                        src={logo}
                                        alt="Pros Immobilier"
                                        className="h-10 w-10 rounded-xl object-contain shadow-sm ring-1 ring-[#c8d4de]"
                                    />

                                    <div className="min-w-0">
                                        <p className="truncate text-sm font-semibold text-[#0f172a]">Pros Immobilier</p>
                                        <p className="truncate text-xs text-[#5f7182]">Admin workspace</p>
                                    </div>
                                </div>

                                <Button variant="outline" size="icon" onClick={closeMobileMenu}>
                                    <PanelLeftClose className="h-4 w-4" />
                                </Button>
                            </div>

                            <div className="flex h-[calc(100%-73px)] flex-col p-4">
                                <nav className="flex flex-col gap-4 overflow-y-auto">
                                    {navigation.map((group) => (
                                        <div key={group.section} className="space-y-2">
                                            <p className="px-3 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#5f7182]">
                                                {group.section}
                                            </p>

                                            <div className="flex flex-col gap-2">
                                                {group.items.map((item) => {
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
                                                                'h-10 justify-start rounded-xl px-3 text-sm',
                                                                isActive
                                                                    ? 'bg-[#00559b] text-white hover:bg-[#004980]'
                                                                    : 'text-[#0f172a]'
                                                            )}
                                                            onClick={closeMobileMenu}
                                                        >
                                                            <Link href={item.href}>
                                                                <Icon className="h-4 w-4" />
                                                                {item.label}
                                                            </Link>
                                                        </Button>
                                                    );
                                                })}
                                            </div>
                                        </div>
                                    ))}
                                </nav>
                            </div>
                        </aside>
                    </div>
                ) : null}

                <main className="ml-0 flex h-[calc(100vh-1.5rem)] w-full flex-1 flex-col overflow-hidden rounded-2xl border border-[#c8d4de] bg-white shadow-sm lg:ml-0">
                    <header className="sticky top-0 z-30 flex shrink-0 items-center justify-between gap-4 border-b border-[#c8d4de] bg-white/95 px-5 py-4 backdrop-blur md:px-8">
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
                                        {currentAdmin?.name?.slice(0, 1)?.toUpperCase() ?? 'A'}
                                    </span>

                                    <span className="hidden max-w-36 flex-col items-start leading-tight sm:flex">
                                        <span className="truncate text-sm font-medium">
                                            {currentAdmin?.name ?? 'Administrateur'}
                                        </span>
                                        <span className="truncate text-xs text-[#5f7182]">
                                            {currentAdmin?.email ?? 'admin@example.com'}
                                        </span>
                                    </span>

                                    <ChevronDown className="h-4 w-4 text-[#5f7182]" />
                                </Button>
                            </DropdownMenuTrigger>

                            <DropdownMenuContent className="w-64">
                                <DropdownMenuLabel>
                                    <div className="flex items-center gap-3">
                                        <div className="flex h-9 w-9 items-center justify-center rounded-full bg-[#00559b] text-sm font-semibold text-white">
                                            {currentAdmin?.name?.slice(0, 1)?.toUpperCase() ?? 'A'}
                                        </div>

                                        <div className="min-w-0">
                                            <p className="truncate text-sm font-medium text-[#0f172a]">
                                                {currentAdmin?.name ?? 'Administrateur'}
                                            </p>
                                            <p className="truncate text-xs text-[#5f7182]">
                                                {currentAdmin?.email ?? 'admin@example.com'}
                                            </p>
                                        </div>
                                    </div>
                                </DropdownMenuLabel>

                                <DropdownMenuSeparator />

                                <DropdownMenuItem asChild>
                                    <Link href="/admin/profile">
                                        <UserRound className="h-4 w-4" />
                                        <span>Voir le profil</span>
                                    </Link>
                                </DropdownMenuItem>

                                <DropdownMenuItem onClick={handleLogout} className="text-[#b42318]">
                                    <LogOut className="h-4 w-4" />
                                    <span>Se deconnecter</span>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                    </header>

                    <section className="flex-1 overflow-y-auto bg-[#f7fbfe] px-5 py-6 md:px-8">
                        {flash?.success ? (
                            <div className="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-sm">
                                {flash.success}
                            </div>
                        ) : null}

                        {flash?.error ? (
                            <div className="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900 shadow-sm">
                                {flash.error}
                            </div>
                        ) : null}

                        {children}
                    </section>
                </main>
            </div>
        </div>
    );
}
