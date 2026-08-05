import { useEffect, useMemo, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import {
    CheckCircle2,
    ChevronRight,
    Clock,
    Inbox,
    LifeBuoy,
    Plus,
    Search,
    Tag,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent } from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';
import { cn } from '../../../lib/utils';

const STATUS = {
    open: { label: 'Ouvert', className: 'bg-[#e6f0f9] text-[#00559b] ring-1 ring-inset ring-[#b8d2ea]' },
    pending: { label: 'En attente', className: 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200' },
    resolved: { label: 'Résolu', className: 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200' },
    closed: { label: 'Fermé', className: 'bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200' },
};

const FILTERS = [
    { key: 'all', label: 'Toutes' },
    { key: 'open', label: 'Ouverts' },
    { key: 'pending', label: 'En attente' },
    { key: 'resolved', label: 'Résolus' },
    { key: 'closed', label: 'Fermés' },
];

const MOCK_REQUESTS = [
    {
        id: 'T-1042',
        subject: "Impossible d'accéder à mon abonnement",
        category: 'Abonnement',
        status: 'open',
        excerpt: "Depuis ce matin je reçois une erreur 403 quand j'ouvre la page abonnement.",
        createdAt: '26 juil. 2026',
        updatedAt: 'Il y a 12 min',
        replies: 2,
    },
    {
        id: 'T-1037',
        subject: 'Export des mandats en PDF',
        category: 'Technique',
        status: 'pending',
        excerpt: "L'export PDF génère un fichier vide pour les mandats de plus de 20 pages.",
        createdAt: '25 juil. 2026',
        updatedAt: 'Il y a 2 h',
        replies: 4,
    },
    {
        id: 'T-1030',
        subject: 'Mise à jour des coordonnées bancaires',
        category: 'Facturation',
        status: 'resolved',
        excerpt: 'Merci de mettre à jour notre IBAN pour les prochains prélèvements.',
        createdAt: '23 juil. 2026',
        updatedAt: 'Hier',
        replies: 3,
    },
    {
        id: 'T-1021',
        subject: 'Ajout de trois collaborateurs',
        category: 'Compte',
        status: 'closed',
        excerpt: 'Nous souhaitons ajouter trois nouveaux agents à notre espace.',
        createdAt: '18 juil. 2026',
        updatedAt: '20 juil. 2026',
        replies: 5,
    },
    {
        id: 'T-1018',
        subject: 'Problème de synchronisation du calendrier',
        category: 'Technique',
        status: 'open',
        excerpt: 'Les rendez-vous créés sur mobile ne remontent pas sur le portail.',
        createdAt: '16 juil. 2026',
        updatedAt: 'Il y a 5 h',
        replies: 1,
    },
];

function StatusBadge({ status }) {
    const s = STATUS[status] ?? STATUS.open;
    return (
        <span className={cn('inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', s.className)}>
            {s.label}
        </span>
    );
}

function StatCard({ icon: Icon, label, value, tone }) {
    return (
        <Card className="rounded-2xl border-[#c8d4de]">
            <CardContent className="mt-6 flex items-center gap-3 p-4">
                <span className={cn('flex h-10 w-10 items-center justify-center rounded-xl', tone)}>
                    <Icon className="h-5 w-5" />
                </span>
                <div>
                    <p className="text-xl font-semibold leading-none text-[#0f172a]">{value}</p>
                    <p className="mt-1 text-xs text-[#5f7182]">{label}</p>
                </div>
            </CardContent>
        </Card>
    );
}

export default function Demandes({ tickets = { data: [] }, stats = {} }) {
    const [search, setSearch] = useState('');
    const [filter, setFilter] = useState('all');

    useEffect(() => {
        const refreshTickets = () => router.reload({ only: ['tickets', 'stats'], preserveScroll: true });
        window.addEventListener('focus', refreshTickets);
        return () => window.removeEventListener('focus', refreshTickets);
    }, []);

    const requests = tickets.data.map((ticket) => ({
        id: ticket.reference,
        uuid: ticket.support_ticket_id,
        subject: ticket.sujet,
        category: ticket.categorie,
        status: ticket.statut,
        unread: ticket.agence_read_at === null,
        excerpt: ticket.description,
        createdAt: new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(new Date(ticket.created_at)),
        updatedAt: ticket.updated_at,
        replies: ticket.messages_count ?? 0,
    }));

    const counts = useMemo(() => {
        return {
            all: Number(stats.all ?? requests.length), open: Number(stats.open ?? 0), pending: Number(stats.pending ?? 0), resolved: Number(stats.resolved ?? 0), closed: Number(stats.closed ?? 0),
        };
    }, [stats, requests.length]);

    const filtered = useMemo(() => {
        const term = search.trim().toLowerCase();
        return requests.filter((request) => {
            const matchStatus = filter === 'all' || request.status === filter;
            const matchSearch =
                term.length === 0 ||
                request.subject.toLowerCase().includes(term) ||
                request.id.toLowerCase().includes(term) ||
                request.category.toLowerCase().includes(term);
            return matchStatus && matchSearch;
        });
    }, [search, filter, requests]);

    return (
        <AgenceLayout title="Mes demandes">
            <div className="flex flex-col gap-5">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-lg font-semibold text-[#0f172a]">Toutes les demandes</h1>
                     
                    </div>
                    <Button
                        asChild
                        className="h-10 rounded-xl bg-[#00559b] text-white hover:bg-[#004980]"
                    >
                        <Link href="/agence/support/nouveau">
                            <Plus className="h-4 w-4" />
                            Nouvelle demande
                        </Link>
                    </Button>
                </div>

                <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <StatCard icon={Inbox} label="Total" value={counts.all} tone="bg-[#e6f0f9] text-[#00559b]" />
                    <StatCard icon={LifeBuoy} label="Ouverts" value={counts.open} tone="bg-[#e6f0f9] text-[#00559b]" />
                    <StatCard icon={Clock} label="En attente" value={counts.pending} tone="bg-amber-50 text-amber-600" />
                    <StatCard
                        icon={CheckCircle2}
                        label="Résolus"
                        value={counts.resolved}
                        tone="bg-emerald-50 text-emerald-600"
                    />
                </div>

                <Card className="rounded-2xl border-[#c8d4de]">
                    <CardContent className="mt-4 flex flex-col gap-4 p-4">
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div className="relative w-full lg:max-w-sm">
                                <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#94a7b8]" />
                                <Input
                                    type="text"
                                    value={search}
                                    onChange={(event) => setSearch(event.target.value)}
                                    placeholder="Rechercher par sujet, n° ou catégorie"
                                    className="h-10 rounded-xl border-[#c8d4de] pl-9"
                                />
                            </div>

                            <div className="flex flex-wrap gap-2">
                                {FILTERS.map((item) => {
                                    const isActive = item.key === filter;
                                    return (
                                        <button
                                            key={item.key}
                                            type="button"
                                            onClick={() => setFilter(item.key)}
                                            className={cn(
                                                'inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors',
                                                isActive
                                                    ? 'border-[#00559b] bg-[#00559b] text-white'
                                                    : 'border-[#c8d4de] bg-white text-[#5f7182] hover:border-[#00559b]/40 hover:bg-[#f7fbfe]'
                                            )}
                                        >
                                            {item.label}
                                            <span
                                                className={cn(
                                                    'rounded-md px-1.5 py-0.5 text-[10px]',
                                                    isActive ? 'bg-white/20 text-white' : 'bg-[#eef3f8] text-[#5f7182]'
                                                )}
                                            >
                                                {counts[item.key]}
                                            </span>
                                        </button>
                                    );
                                })}
                            </div>
                        </div>

                        <div className="overflow-hidden rounded-xl border border-[#e2eaf1]">
                            {filtered.length === 0 ? (
                                <div className="flex flex-col items-center justify-center gap-2 px-4 py-12 text-center">
                                    <span className="flex h-12 w-12 items-center justify-center rounded-full bg-[#f7fbfe] text-[#94a7b8]">
                                        <Inbox className="h-6 w-6" />
                                    </span>
                                    <p className="text-sm font-medium text-[#334155]">Aucune demande trouvée</p>
                                    <p className="text-xs text-[#5f7182]">
                                        Essayez un autre filtre ou une autre recherche.
                                    </p>
                                </div>
                            ) : (
                                <ul className="divide-y divide-[#eef3f8]">
                                    {filtered.map((request) => {
                                        return (
                                            <li key={request.id}>
                                                <Link
                                                    href={`/agence/support/${request.id}`}
                                                    className="flex items-center gap-4 px-4 py-3.5 transition-colors hover:bg-[#f7fbfe]"
                                                >
                                                    <div className="min-w-0 flex-1">
                                                        <div className="flex items-center gap-2">
                                                            {request.unread ? <span className="h-2 w-2 shrink-0 rounded-full bg-[#00559b]" /> : null}
                                                            <span className="text-xs font-medium text-[#94a7b8]">
                                                                {request.id}
                                                            </span>
                                                            <span className="inline-flex items-center gap-1 rounded-md bg-[#f1f6fb] px-1.5 py-0.5 text-[10px] font-medium text-[#5f7182]">
                                                                <Tag className="h-3 w-3" />
                                                                {request.category}
                                                            </span>
                                                        </div>
                                                        <p className={cn('mt-0.5 truncate text-sm text-[#0f172a]', request.unread ? 'font-semibold' : 'font-medium')}>
                                                            {request.subject}
                                                        </p>
                                                        <p className="truncate text-xs text-[#5f7182]">
                                                            {request.excerpt}
                                                        </p>
                                                    </div>
                                                    <div className="hidden shrink-0 flex-col items-end gap-1 sm:flex">
                                                        <StatusBadge status={request.status} />
                                                        <span className="text-[11px] text-[#94a7b8]">
                                                            {request.updatedAt}
                                                        </span>
                                                    </div>
                                                    <ChevronRight className="h-4 w-4 shrink-0 text-[#c8d4de]" />
                                                </Link>
                                            </li>
                                        );
                                    })}
                                </ul>
                            )}
                        </div>

                        <p className="text-xs text-[#94a7b8]">
                            {filtered.length} demande{filtered.length > 1 ? 's' : ''} affichée
                            {filtered.length > 1 ? 's' : ''} sur {MOCK_REQUESTS.length}.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </AgenceLayout>
    );
}
