import { useMemo, useState } from 'react';
import { router } from '@inertiajs/react';
import {
    ArrowLeft,
    CheckCircle2,
    Circle,
    Clock,
    Filter,
    Inbox,
    LifeBuoy,
    Mail,
    Paperclip,
    Search,
    Send,
    Tag,
    User as UserIcon,
} from 'lucide-react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../../../components/ui/card';
import { Separator } from '../../../components/ui/separator';
import { cn } from '../../../lib/utils';

const STATUS = {
    open: { label: 'Ouvert', className: 'bg-[#e6f0f9] text-[#00559b] ring-1 ring-inset ring-[#b8d2ea]' },
    pending: { label: 'En attente', className: 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200' },
    resolved: { label: 'Résolu', className: 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200' },
    closed: { label: 'Fermé', className: 'bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200' },
};

const PRIORITY = {
    high: { label: 'Haute', dot: 'bg-rose-500', text: 'text-rose-600' },
    medium: { label: 'Moyenne', dot: 'bg-amber-500', text: 'text-amber-600' },
    low: { label: 'Basse', dot: 'bg-emerald-500', text: 'text-emerald-600' },
};

const FILTERS = [
    { key: 'all', label: 'Tous', icon: Inbox },
    { key: 'open', label: 'Ouverts', icon: Circle },
    { key: 'pending', label: 'En attente', icon: Clock },
    { key: 'resolved', label: 'Résolus', icon: CheckCircle2 },
];

const MOCK_TICKETS = [
    {
        id: 'T-1042',
        subject: "Impossible d'accéder à mon abonnement",
        requester: { name: 'Sofia Martel', email: 'sofia.martel@agence-lyon.fr', agency: 'Agence Lyon Part-Dieu' },
        status: 'open',
        priority: 'high',
        category: 'Abonnement',
        updatedAt: 'Il y a 12 min',
        unread: true,
        messages: [
            {
                id: 1,
                author: 'Sofia Martel',
                role: 'client',
                at: "Aujourd'hui, 09:14",
                body: "Bonjour, depuis ce matin je ne peux plus accéder à mon abonnement Premium. La page reste bloquée sur un chargement infini. Pouvez-vous m'aider rapidement, j'ai plusieurs mandats à publier aujourd'hui.",
            },
            {
                id: 2,
                author: 'Support Pros Immobilier',
                role: 'agent',
                at: "Aujourd'hui, 09:22",
                body: "Bonjour Sofia, merci pour votre message. Nous regardons cela immédiatement. Pouvez-vous nous préciser le navigateur que vous utilisez ?",
            },
        ],
    },
    {
        id: 'T-1041',
        subject: 'Demande de facture pour janvier',
        requester: { name: 'Karim Benali', email: 'k.benali@immo-nord.fr', agency: 'Immo Nord' },
        status: 'pending',
        priority: 'low',
        category: 'Facturation',
        updatedAt: 'Il y a 1 h',
        unread: false,
        messages: [
            {
                id: 1,
                author: 'Karim Benali',
                role: 'client',
                at: "Aujourd'hui, 08:03",
                body: "Bonjour, je souhaiterais recevoir la facture correspondant au mois de janvier pour ma comptabilité. Merci d'avance.",
            },
        ],
    },
    {
        id: 'T-1040',
        subject: 'Bug affichage photos sur les annonces',
        requester: { name: 'Laura Fontaine', email: 'laura@habitat-sud.fr', agency: 'Habitat Sud' },
        status: 'open',
        priority: 'medium',
        category: 'Technique',
        updatedAt: 'Il y a 3 h',
        unread: true,
        messages: [
            {
                id: 1,
                author: 'Laura Fontaine',
                role: 'client',
                at: 'Hier, 17:41',
                body: "Les photos de mes annonces apparaissent en très basse résolution depuis la dernière mise à jour. Est-ce un problème connu ?",
            },
        ],
    },
    {
        id: 'T-1039',
        subject: "Ajout d'un utilisateur à mon agence",
        requester: { name: 'Marc Dubois', email: 'm.dubois@agence-paris.fr', agency: 'Agence Paris 15' },
        status: 'resolved',
        priority: 'low',
        category: 'Compte',
        updatedAt: 'Hier',
        unread: false,
        messages: [
            {
                id: 1,
                author: 'Marc Dubois',
                role: 'client',
                at: 'Hier, 11:20',
                body: "Comment ajouter un nouveau collaborateur à mon espace agence ?",
            },
            {
                id: 2,
                author: 'Support Pros Immobilier',
                role: 'agent',
                at: 'Hier, 11:35',
                body: "Bonjour Marc, rendez-vous dans Configuration > Utilisateurs puis cliquez sur « Inviter ». Le collaborateur recevra un e-mail d'activation. Je reste disponible.",
            },
        ],
    },
    {
        id: 'T-1038',
        subject: 'Résiliation abonnement module Statistiques',
        requester: { name: 'Nadia Cherif', email: 'nadia.cherif@immo-ouest.fr', agency: 'Immo Ouest' },
        status: 'closed',
        priority: 'medium',
        category: 'Abonnement',
        updatedAt: 'Il y a 2 j',
        unread: false,
        messages: [
            {
                id: 1,
                author: 'Nadia Cherif',
                role: 'client',
                at: 'Lun. 14:02',
                body: "Je souhaite résilier le module Statistiques à la fin de la période en cours.",
            },
            {
                id: 2,
                author: 'Support Pros Immobilier',
                role: 'agent',
                at: 'Lun. 14:30',
                body: "C'est bien noté, la résiliation prendra effet à la fin de votre cycle de facturation. Ticket clôturé.",
            },
        ],
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

function PriorityTag({ priority }) {
    const p = PRIORITY[priority] ?? PRIORITY.low;
    return (
        <span className={cn('inline-flex items-center gap-1.5 text-xs font-medium', p.text)}>
            <span className={cn('h-2 w-2 rounded-full', p.dot)} />
            {p.label}
        </span>
    );
}

function initials(name) {
    return name
        ?.split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

function TicketList({ tickets, activeId, onSelect }) {
    return (
        <div className="flex flex-col">
            {tickets.map((ticket) => {
                const isActive = ticket.id === activeId;
                return (
                    <button
                        key={ticket.id}
                        type="button"
                        onClick={() => onSelect(ticket.id)}
                        className={cn(
                            'flex w-full flex-col gap-1.5 border-b border-[#eef3f8] px-4 py-3 text-left transition-colors',
                            isActive ? 'bg-[#e6f0f9]' : 'hover:bg-[#f7fbfe]'
                        )}
                    >
                        <div className="flex items-center justify-between gap-2">
                            <div className="flex min-w-0 items-center gap-2">
                                {ticket.unread ? <span className="h-2 w-2 shrink-0 rounded-full bg-[#00559b]" /> : null}
                                <span
                                    className={cn(
                                        'truncate text-sm',
                                        ticket.unread ? 'font-semibold text-[#0f172a]' : 'font-medium text-[#334155]'
                                    )}
                                >
                                    {ticket.requester.name}
                                </span>
                            </div>
                            <span className="shrink-0 text-[11px] text-[#5f7182]">{ticket.updatedAt}</span>
                        </div>

                        <p className="truncate text-sm text-[#334155]">{ticket.subject}</p>

                        <div className="flex items-center justify-between gap-2">
                            <StatusBadge status={ticket.status} />
                            <PriorityTag priority={ticket.priority} />
                        </div>
                    </button>
                );
            })}

            {tickets.length === 0 ? (
                <div className="flex flex-col items-center gap-2 px-4 py-12 text-center text-sm text-[#5f7182]">
                    <Inbox className="h-6 w-6 text-[#94a7b8]" />
                    Aucun ticket ne correspond à votre recherche.
                </div>
            ) : null}
        </div>
    );
}

function Conversation({ ticket, onBack, onSend, onStatusChange }) {
    const [reply, setReply] = useState('');

    if (!ticket) {
        return (
            <div className="flex h-full flex-col items-center justify-center gap-3 text-center text-[#5f7182]">
                <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#e6f0f9] text-[#00559b]">
                    <Mail className="h-6 w-6" />
                </div>
                <p className="text-sm font-medium text-[#0f172a]">Sélectionnez un ticket</p>
                <p className="max-w-xs text-sm">Choisissez une conversation dans la liste pour afficher les échanges et répondre.</p>
            </div>
        );
    }

    const handleSend = () => {
        const value = reply.trim();
        if (!value) return;
        onSend(ticket.id, value);
        setReply('');
    };

    return (
        <div className="flex h-full flex-col">
            <div className="flex shrink-0 items-start justify-between gap-4 border-b border-[#eef3f8] px-5 py-4">
                <div className="flex min-w-0 items-start gap-3">
                    <Button variant="outline" size="icon" className="lg:hidden" onClick={onBack}>
                        <ArrowLeft className="h-4 w-4" />
                    </Button>

                    <div className="min-w-0">
                        <div className="flex flex-wrap items-center gap-2">
                            <span className="text-xs font-semibold text-[#5f7182]">{ticket.id}</span>
                            <StatusBadge status={ticket.status} />
                        </div>
                        <h2 className="mt-1 truncate text-base font-semibold text-[#0f172a]">{ticket.subject}</h2>
                        <div className="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[#5f7182]">
                            <span className="inline-flex items-center gap-1">
                                <UserIcon className="h-3.5 w-3.5" />
                                {ticket.requester.name}
                            </span>
                            <span className="inline-flex items-center gap-1">
                                <Tag className="h-3.5 w-3.5" />
                                {ticket.category}
                            </span>
                            <PriorityTag priority={ticket.priority} />
                        </div>
                    </div>
                </div>

                <div className="hidden shrink-0 items-center gap-2 sm:flex">
                    {ticket.status !== 'resolved' ? (
                        <Button
                            variant="outline"
                            className="h-9 rounded-xl border-[#c8d4de] text-[#0f172a]"
                            onClick={() => onStatusChange(ticket.id, 'resolved')}
                        >
                            <CheckCircle2 className="h-4 w-4" />
                            Marquer résolu
                        </Button>
                    ) : (
                        <Button
                            variant="outline"
                            className="h-9 rounded-xl border-[#c8d4de] text-[#0f172a]"
                            onClick={() => onStatusChange(ticket.id, 'open')}
                        >
                            <Circle className="h-4 w-4" />
                            Rouvrir
                        </Button>
                    )}
                </div>
            </div>

            <div className="flex-1 space-y-4 overflow-y-auto bg-[#f7fbfe] px-5 py-5">
                {ticket.messages.map((message) => {
                    const isAgent = message.role === 'agent';
                    return (
                        <div key={message.id} className={cn('flex gap-3', isAgent ? 'flex-row' : 'flex-row-reverse')}>
                            <span
                                className={cn(
                                    'flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-semibold',
                                    isAgent ? 'bg-[#00559b] text-white' : 'bg-white text-[#00559b] ring-1 ring-[#c8d4de]'
                                )}
                            >
                                {isAgent ? 'PI' : initials(message.author)}
                            </span>

                            <div className={cn('max-w-[78%]', isAgent ? 'items-start' : 'items-end text-right')}>
                                <div className="mb-1 flex items-center gap-2 text-xs text-[#5f7182]">
                                    <span className="font-medium text-[#334155]">{message.author}</span>
                                    <span>{message.at}</span>
                                </div>
                                <div
                                    className={cn(
                                        'rounded-2xl px-4 py-3 text-sm leading-relaxed shadow-sm',
                                        isAgent
                                            ? 'bg-[#00559b] text-white'
                                            : 'border border-[#e2eaf1] bg-white text-[#0f172a]'
                                    )}
                                >
                                    {message.body}
                                </div>
                            </div>
                        </div>
                    );
                })}
            </div>

            <div className="shrink-0 border-t border-[#eef3f8] bg-white p-4">
                <div className="rounded-xl border border-[#c8d4de] bg-white focus-within:ring-2 focus-within:ring-[#00559b]/30">
                    <textarea
                        value={reply}
                        onChange={(event) => setReply(event.target.value)}
                        onKeyDown={(event) => {
                            if (
                                event.key === 'Enter' &&
                                (event.metaKey || event.ctrlKey) &&
                                !event.nativeEvent.isComposing &&
                                event.keyCode !== 229
                            ) {
                                event.preventDefault();
                                handleSend();
                            }
                        }}
                        rows={3}
                        placeholder="Rédigez votre réponse..."
                        className="w-full resize-none rounded-xl bg-transparent px-4 py-3 text-sm text-[#0f172a] placeholder:text-[#94a7b8] focus:outline-none"
                    />
                    <div className="flex items-center justify-between border-t border-[#eef3f8] px-3 py-2">
                        <Button variant="ghost" size="sm" className="h-8 rounded-lg text-[#5f7182]">
                            <Paperclip className="h-4 w-4" />
                            Joindre
                        </Button>
                        <div className="flex items-center gap-2">
                            <span className="hidden text-[11px] text-[#94a7b8] sm:inline">Ctrl + Entrée pour envoyer</span>
                            <Button
                                className="h-9 rounded-xl bg-[#00559b] text-white hover:bg-[#004980]"
                                onClick={handleSend}
                                disabled={!reply.trim()}
                            >
                                <Send className="h-4 w-4" />
                                Envoyer
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default function Tickets({ tickets: initialTickets = [], stats = {} }) {
    const [tickets, setTickets] = useState(initialTickets);
    const [filter, setFilter] = useState('all');
    const [search, setSearch] = useState('');
    const [activeId, setActiveId] = useState(initialTickets[0]?.id ?? null);
    const [mobileShowConversation, setMobileShowConversation] = useState(false);

    const filteredTickets = useMemo(() => {
        const term = search.trim().toLowerCase();
        return tickets.filter((ticket) => {
            const matchesFilter = filter === 'all' ? true : ticket.status === filter;
            const matchesSearch =
                term.length === 0 ||
                ticket.subject.toLowerCase().includes(term) ||
                ticket.requester.name.toLowerCase().includes(term) ||
                ticket.id.toLowerCase().includes(term);
            return matchesFilter && matchesSearch;
        });
    }, [tickets, filter, search]);

    const activeTicket = useMemo(
        () => tickets.find((ticket) => ticket.id === activeId) ?? null,
        [tickets, activeId]
    );

    const counts = useMemo(
        () => ({
            all: tickets.length,
            open: tickets.filter((t) => t.status === 'open').length,
            pending: tickets.filter((t) => t.status === 'pending').length,
            resolved: tickets.filter((t) => t.status === 'resolved').length,
        }),
        [tickets]
    );

    const handleSelect = (id) => {
        setActiveId(id);
        setMobileShowConversation(true);
        setTickets((prev) => prev.map((t) => (t.id === id ? { ...t, unread: false } : t)));
    };

    const handleSend = (id, body) => {
        const ticket = tickets.find((item) => item.id === id);
        if (!ticket) return;
        router.post(`/admin/tickets/${ticket.uuid ?? id}/reponses`, { message: body }, { preserveScroll: true });
        setTickets((prev) =>
            prev.map((ticket) =>
                ticket.id === id
                    ? {
                          ...ticket,
                          status: ticket.status === 'closed' ? 'open' : ticket.status,
                          updatedAt: "À l'instant",
                          messages: [
                              ...ticket.messages,
                              {
                                  id: ticket.messages.length + 1,
                                  author: 'Support Pros Immobilier',
                                  role: 'agent',
                                  at: "À l'instant",
                                  body,
                              },
                          ],
                      }
                    : ticket
            )
        );
    };

    const handleStatusChange = (id, status) => {
        const ticket = tickets.find((item) => item.id === id);
        if (!ticket) return;
        router.patch(`/admin/tickets/${ticket.uuid ?? id}/statut`, { status }, { preserveScroll: true });
        setTickets((prev) => prev.map((ticket) => (ticket.id === id ? { ...ticket, status } : ticket)));
    };

    return (
        <AdminLayout title="Tickets">
            <div className="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
                {[
                    { label: 'Tickets ouverts', value: counts.open, icon: Inbox, tone: 'text-[#00559b]', bg: 'bg-[#e6f0f9]' },
                    { label: 'En attente', value: counts.pending, icon: Clock, tone: 'text-amber-600', bg: 'bg-amber-50' },
                    { label: 'Résolus', value: counts.resolved, icon: CheckCircle2, tone: 'text-emerald-600', bg: 'bg-emerald-50' },
                    { label: 'Total', value: counts.all, icon: LifeBuoy, tone: 'text-[#5f7182]', bg: 'bg-slate-100' },
                ].map((stat) => {
                    const Icon = stat.icon;
                    return (
                        <Card key={stat.label} className="rounded-2xl border-[#c8d4de] shadow-sm">
                            <CardContent className="mt-6 flex items-center gap-3 p-4">
                                <span className={cn('flex h-10 w-10 items-center justify-center rounded-xl', stat.bg, stat.tone)}>
                                    <Icon className="h-5 w-5" />
                                </span>
                                <div>
                                    <p className="text-xs text-[#5f7182]">{stat.label}</p>
                                    <p className="text-xl font-semibold text-[#0f172a]">{stat.value}</p>
                                </div>
                            </CardContent>
                        </Card>
                    );
                })}
            </div>

            <Card className="overflow-hidden rounded-2xl border-[#c8d4de] shadow-sm">
                <div className="grid min-h-[560px] grid-cols-1 lg:grid-cols-[360px_1fr]">
                    {/* Liste */}
                    <div
                        className={cn(
                            'flex flex-col border-[#eef3f8] lg:border-r',
                            mobileShowConversation ? 'hidden lg:flex' : 'flex'
                        )}
                    >
                        <CardHeader className="space-y-3 border-b border-[#eef3f8] p-4">
                            <CardTitle className="flex items-center gap-2 text-base">
                                <LifeBuoy className="h-4 w-4 text-[#00559b]" />
                                Boîte de réception
                            </CardTitle>

                            <div className="flex items-center gap-2 rounded-xl border border-[#c8d4de] bg-white px-3 focus-within:ring-2 focus-within:ring-[#00559b]/30">
                                <Search className="h-4 w-4 text-[#94a7b8]" />
                                <input
                                    value={search}
                                    onChange={(event) => setSearch(event.target.value)}
                                    placeholder="Rechercher un ticket, un client..."
                                    className="h-10 w-full bg-transparent text-sm text-[#0f172a] placeholder:text-[#94a7b8] focus:outline-none"
                                />
                            </div>

                            <div className="flex flex-wrap items-center gap-1.5">
                                <span className="inline-flex items-center gap-1 pr-1 text-[11px] font-medium uppercase tracking-wide text-[#94a7b8]">
                                    <Filter className="h-3.5 w-3.5" />
                                </span>
                                {FILTERS.map((f) => {
                                    const isActive = filter === f.key;
                                    return (
                                        <button
                                            key={f.key}
                                            type="button"
                                            onClick={() => setFilter(f.key)}
                                            className={cn(
                                                'inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium transition-colors',
                                                isActive
                                                    ? 'bg-[#00559b] text-white'
                                                    : 'bg-[#f1f6fb] text-[#5f7182] hover:bg-[#e6f0f9]'
                                            )}
                                        >
                                            {f.label}
                                            {f.key !== 'all' && counts[f.key] > 0 ? (
                                                <span
                                                    className={cn(
                                                        'rounded-full px-1.5 text-[10px]',
                                                        isActive ? 'bg-white/20' : 'bg-white text-[#5f7182]'
                                                    )}
                                                >
                                                    {counts[f.key]}
                                                </span>
                                            ) : null}
                                        </button>
                                    );
                                })}
                            </div>
                        </CardHeader>

                        <div className="max-h-[460px] flex-1 overflow-y-auto">
                            <TicketList tickets={filteredTickets} activeId={activeId} onSelect={handleSelect} />
                        </div>
                    </div>

                    {/* Conversation */}
                    <div className={cn('min-h-[560px]', mobileShowConversation ? 'flex' : 'hidden lg:flex')}>
                        <div className="flex w-full flex-col">
                            <Conversation
                                ticket={activeTicket}
                                onBack={() => setMobileShowConversation(false)}
                                onSend={handleSend}
                                onStatusChange={handleStatusChange}
                            />
                        </div>
                    </div>
                </div>
            </Card>
        </AdminLayout>
    );
}
