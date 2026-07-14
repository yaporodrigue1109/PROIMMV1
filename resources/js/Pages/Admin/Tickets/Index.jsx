import { useMemo, useState } from 'react';
import { ArrowRight, CircleAlert, LifeBuoy, MessageSquare, Plus, Search, Ticket as TicketIcon, CheckCircle2 } from 'lucide-react';

import AdminLayout from '../../../Layouts/AdminLayout';
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

const priorityVariant = {
    Urgente: 'danger',
    Haute: 'warning',
    Normale: 'secondary',
    Basse: 'outline',
};

const statusVariant = {
    Ouvert: 'danger',
    'En cours': 'warning',
    Résolu: 'success',
    Fermé: 'outline',
};

export default function Index() {
    const tickets = [
        { id: '0048', name: 'Konan Djibril', subtitle: 'Impossible de créer une mission', priority: 'Urgente', status: 'Ouvert', time: '12 min', agency: 'Agence Plateau', email: 'konan@example.ci' },
        { id: '0047', name: 'Yao Tanoh', subtitle: 'Facture non générée après mission', priority: 'Haute', status: 'En cours', time: '1h', agency: 'Agence Cocody', email: 'yao@example.ci' },
        { id: '0046', name: 'Fatou Ouattara', subtitle: 'Problème de connexion au compte', priority: 'Normale', status: 'Résolu', time: '3h', agency: 'Agence Marcory', email: 'fatou@example.ci' },
        { id: '0045', name: 'Moussa Bamba', subtitle: 'Véhicule non listé dans le parc', priority: 'Basse', status: 'Ouvert', time: '1 jour', agency: 'Agence Yopougon', email: 'moussa@example.ci' },
        { id: '0044', name: 'Sandrine Coulibaly', subtitle: 'Rapport de caisse incorrect', priority: 'Haute', status: 'Fermé', time: '2 jours', agency: 'Agence Bingerville', email: 'sandrine@example.ci' },
    ];

    const [query, setQuery] = useState('');
    const [filter, setFilter] = useState('Tous');
    const [selectedId, setSelectedId] = useState('0048');

    const filteredTickets = useMemo(() => {
        return tickets.filter((ticket) => {
            const matchesQuery = !query || [ticket.id, ticket.name, ticket.subtitle, ticket.agency].join(' ').toLowerCase().includes(query.toLowerCase());
            const matchesFilter = filter === 'Tous' || ticket.status === filter;
            return matchesQuery && matchesFilter;
        });
    }, [query, filter]);

    const selectedTicket = filteredTickets.find((ticket) => ticket.id === selectedId) ?? filteredTickets[0] ?? null;

    return (
        <AdminLayout title="Tickets">
            <div className="space-y-6">
                <Card className="rounded-3xl border-slate-200 shadow-sm">
                    <CardContent className="flex flex-col gap-5 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div className="max-w-3xl space-y-3">
                            <div className="inline-flex items-center gap-2 rounded-full border border-[#d8e3eb] bg-[#eef6fb] px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-[#00559b]">
                                <LifeBuoy className="h-3.5 w-3.5" />
                                Support
                            </div>
                            <div>
                                <h1 className="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                                    Tickets de support
                                </h1>
                                <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-500 md:text-base">
                                    Consultez, filtrez et traitez les demandes support clients dans une vue unifiee et plus moderne.
                                </p>
                            </div>
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <Button className="h-11 rounded-xl bg-[#00559b] px-4 text-white hover:bg-[#004980]">
                                <Plus className="h-4 w-4" />
                                Nouveau ticket
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <Metric label="Total tickets" value="48" icon={TicketIcon} />
                    <Metric label="Ouverts" value="21" tone="text-[#00559b]" icon={CircleAlert} />
                    <Metric label="En cours" value="12" tone="text-amber-600" icon={MessageSquare} />
                    <Metric label="Resolus" value="15" tone="text-emerald-600" icon={CheckCircle2} />
                </div>

                <div className="grid gap-6 xl:grid-cols-[0.92fr_1.08fr]">
                    <Card className="rounded-3xl border-slate-200 shadow-sm">
                        <CardHeader className="space-y-4 border-b border-slate-200 pb-5">
                            <div className="flex items-center justify-between gap-3">
                                <div>
                                    <CardTitle className="text-lg">Tickets</CardTitle>
                                    <CardDescription className="mt-1">
                                        {filteredTickets.length} ticket(s) trouve(s)
                                    </CardDescription>
                                </div>
                            </div>

                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <Input
                                    value={query}
                                    onChange={(event) => setQuery(event.target.value)}
                                    placeholder="Rechercher un ticket, un client..."
                                    className="h-11 rounded-2xl bg-slate-50 pl-10"
                                />
                            </div>

                            <div className="flex flex-wrap gap-2">
                                {['Tous', 'Ouvert', 'En cours', 'Résolu', 'Fermé'].map((item) => (
                                    <Button
                                        key={item}
                                        type="button"
                                        size="sm"
                                        variant={filter === item ? 'default' : 'outline'}
                                        onClick={() => setFilter(item)}
                                        className="rounded-full"
                                    >
                                        {item}
                                    </Button>
                                ))}
                            </div>
                        </CardHeader>

                        <CardContent className="p-3">
                            <div className="space-y-2">
                                {filteredTickets.map((ticket) => {
                                    const isSelected = selectedTicket?.id === ticket.id;

                                    return (
                                        <button
                                            key={ticket.id}
                                            type="button"
                                            onClick={() => setSelectedId(ticket.id)}
                                            className={`w-full rounded-2xl border p-4 text-left transition ${
                                                isSelected
                                                    ? 'border-[#00559b] bg-blue-50'
                                                    : 'border-slate-200 bg-white hover:bg-slate-50'
                                            }`}
                                        >
                                            <div className="flex items-center justify-between gap-3">
                                                <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">#TK-{ticket.id}</p>
                                                <span className="text-xs text-slate-500">{ticket.time}</span>
                                            </div>
                                            <p className="mt-2 text-sm font-semibold text-slate-900">{ticket.name}</p>
                                            <p className="mt-1 text-sm text-slate-500">{ticket.subtitle}</p>
                                            <div className="mt-3 flex flex-wrap gap-2">
                                                <Badge variant={priorityVariant[ticket.priority] ?? 'outline'} className="rounded-full">
                                                    {ticket.priority}
                                                </Badge>
                                                <Badge variant={statusVariant[ticket.status] ?? 'outline'} className="rounded-full">
                                                    {ticket.status}
                                                </Badge>
                                            </div>
                                        </button>
                                    );
                                })}
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="rounded-3xl border-slate-200 shadow-sm">
                        {selectedTicket ? (
                            <>
                                <CardHeader className="border-b border-slate-200">
                                    <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            <p className="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">
                                                #TK-{selectedTicket.id} · {selectedTicket.agency}
                                            </p>
                                            <CardTitle className="mt-2 text-2xl">{selectedTicket.subtitle}</CardTitle>
                                        </div>

                                        <div className="flex flex-wrap gap-2">
                                            <Button variant="outline" className="rounded-xl border-slate-200">
                                                Changer statut
                                            </Button>
                                            <Button className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]">
                                                Resoudre
                                            </Button>
                                        </div>
                                    </div>
                                </CardHeader>

                                <CardContent className="space-y-6 p-6">
                                    <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                        <Mini label="Priorite" value={selectedTicket.priority} variant={priorityVariant[selectedTicket.priority] ?? 'outline'} />
                                        <Mini label="Statut" value={selectedTicket.status} variant={statusVariant[selectedTicket.status] ?? 'outline'} />
                                        <Mini label="Derniere activite" value={selectedTicket.time} />
                                        <Mini label="Agence" value={selectedTicket.agency} />
                                    </div>

                                    <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <p className="text-sm font-medium text-slate-500">Client</p>
                                        <p className="mt-2 text-lg font-semibold text-slate-900">{selectedTicket.name}</p>
                                        <p className="mt-1 text-sm text-slate-500">{selectedTicket.email}</p>
                                    </div>

                                    <div className="rounded-2xl border border-slate-200 p-4 text-sm leading-6 text-slate-700">
                                        Depuis ce matin, impossible de creer une nouvelle mission depuis le tableau de bord.
                                        Le bouton de creation reste grise apres authentification. Le probleme persiste sur Chrome et Firefox.
                                    </div>

                                    <div className="space-y-4">
                                        <ThreadMessage author="Konan Djibril" time="08h14" text="Bonjour, le bouton de creation d'une mission est grise depuis ce matin. J'ai vide le cache et redemarre sans succes." />
                                        <ThreadMessage author="Support" time="08h31" agent text="Bonjour Konan, merci pour votre retour. Nous regardons le probleme. Pouvez-vous preciser votre navigateur et sa version ?" />
                                    </div>

                                    <div className="rounded-2xl border border-slate-200 bg-white p-4">
                                        <textarea
                                            className="min-h-[110px] w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm outline-none focus:border-[#00559b]"
                                            placeholder="Ecrire une reponse..."
                                        />
                                        <div className="mt-3 flex justify-end">
                                            <Button className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]">
                                                Envoyer
                                                <ArrowRight className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </CardContent>
                            </>
                        ) : (
                            <CardContent className="flex min-h-[520px] items-center justify-center p-10 text-center">
                                <div className="max-w-sm space-y-3">
                                    <TicketIcon className="mx-auto h-10 w-10 text-slate-300" />
                                    <h3 className="text-lg font-semibold text-slate-900">Aucun ticket selectionne</h3>
                                    <p className="text-sm leading-6 text-slate-500">
                                        Choisissez un ticket dans la liste pour afficher son detail complet.
                                    </p>
                                </div>
                            </CardContent>
                        )}
                    </Card>
                </div>
            </div>
        </AdminLayout>
    );
}

function Metric({ label, value, tone = 'text-slate-900', icon: Icon }) {
    return (
        <Card className="border-slate-200 shadow-sm">
            <CardContent className="p-5">
                <div className="flex items-center justify-between gap-3">
                    <p className="text-sm text-slate-500">{label}</p>
                    <Icon className="h-4 w-4 text-slate-400" />
                </div>
                <p className={`mt-2 text-2xl font-semibold ${tone}`}>{value}</p>
            </CardContent>
        </Card>
    );
}

function Mini({ label, value, variant = 'outline' }) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{label}</p>
            <Badge variant={variant} className="mt-3 rounded-full">{value}</Badge>
        </div>
    );
}

function ThreadMessage({ author, time, text, agent = false }) {
    return (
        <div className={`flex gap-3 ${agent ? 'justify-end' : ''}`}>
            {agent ? <div className="mt-1 flex h-9 w-9 items-center justify-center rounded-full bg-emerald-600 text-xs font-semibold text-white">SP</div> : <div className="mt-1 flex h-9 w-9 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white">KD</div>}
            <div className={`max-w-[calc(100%-3rem)] rounded-2xl border p-4 ${agent ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-white'}`}>
                <p className="text-sm font-semibold text-slate-900">{author}</p>
                <p className="mt-1 text-sm leading-6 text-slate-700">{text}</p>
                <p className="mt-2 text-xs text-slate-500">{time}</p>
            </div>
        </div>
    );
}
