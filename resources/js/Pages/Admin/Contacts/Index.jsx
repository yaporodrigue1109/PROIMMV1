import { useEffect, useMemo, useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import {
    CheckCircle2,
    Clock3,
    Inbox,
    Mail,
    MessageSquareText,
    Phone,
    Search,
    Send,
    UserRound,
} from 'lucide-react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../../../components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
import RichTextEditor from '../../../components/rich-text-editor';
import { cn } from '../../../lib/utils';

const STATUS = {
    new: { label: 'Nouveau', className: 'bg-blue-50 text-blue-700 ring-blue-200' },
    in_progress: { label: 'En cours', className: 'bg-amber-50 text-amber-700 ring-amber-200' },
    processed: { label: 'Traité', className: 'bg-emerald-50 text-emerald-700 ring-emerald-200' },
    closed: { label: 'Fermé', className: 'bg-slate-100 text-slate-600 ring-slate-200' },
};

const REQUEST_TYPES = {
    demo: 'Démonstration',
    inscription: 'Inscription / abonnement',
    support: 'Assistance / support',
    partenariat: 'Partenariat',
    autre: 'Autre demande',
};

function StatusBadge({ status }) {
    const value = STATUS[status] ?? STATUS.new;
    return <Badge className={cn('rounded-full ring-1', value.className)}>{value.label}</Badge>;
}

export default function Contacts({ contacts = { data: [] }, stats = {} }) {
    const rows = Array.isArray(contacts.data) ? contacts.data : [];
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('all');
    const [activeId, setActiveId] = useState(rows[0]?.id ?? null);
    const replyForm = useForm({ subject: '', message: '' });

    useEffect(() => {
        if (!rows.some((contact) => contact.id === activeId)) {
            setActiveId(rows[0]?.id ?? null);
        }
    }, [rows, activeId]);

    const filtered = useMemo(() => {
        const term = search.trim().toLowerCase();

        return rows.filter((contact) => {
            const matchesStatus = status === 'all' || contact.status === status;
            const haystack = `${contact.name} ${contact.email} ${contact.subject}`.toLowerCase();
            return matchesStatus && (!term || haystack.includes(term));
        });
    }, [rows, search, status]);

    const activeContact = rows.find((contact) => contact.id === activeId) ?? null;

    useEffect(() => {
        replyForm.setData({
            subject: activeContact ? `Re: ${activeContact.subject}` : '',
            message: '',
        });
        replyForm.clearErrors();
    }, [activeId]);

    const updateStatus = (nextStatus) => {
        if (!activeContact || nextStatus === activeContact.status) return;

        router.patch(`/admin/contacts/${activeContact.id}/statut`, { status: nextStatus }, {
            preserveScroll: true,
        });
    };

    const sendReply = (event) => {
        event.preventDefault();
        if (!activeContact) return;

        replyForm.post(`/admin/contacts/${activeContact.id}/reponses`, {
            preserveScroll: true,
            onSuccess: () => replyForm.setData('message', ''),
        });
    };

    return (
        <AdminLayout title="Contacts">
            <div className="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
                {[
                    { label: 'Nouveaux', value: stats.new ?? 0, icon: Inbox, tone: 'text-blue-700', bg: 'bg-blue-50' },
                    { label: 'En cours', value: stats.inProgress ?? 0, icon: Clock3, tone: 'text-amber-700', bg: 'bg-amber-50' },
                    { label: 'Traités', value: stats.processed ?? 0, icon: CheckCircle2, tone: 'text-emerald-700', bg: 'bg-emerald-50' },
                    { label: 'Total', value: stats.all ?? 0, icon: MessageSquareText, tone: 'text-slate-600', bg: 'bg-slate-100' },
                ].map((item) => {
                    const Icon = item.icon;
                    return (
                        <Card key={item.label} className="rounded-2xl border-[#c8d4de] shadow-sm">
                            <CardContent className="mt-6 flex items-center gap-3 p-4">
                                <span className={cn('flex h-10 w-10 items-center justify-center rounded-xl', item.bg, item.tone)}>
                                    <Icon className="h-5 w-5" />
                                </span>
                                <div>
                                    <p className="text-xs text-[#5f7182]">{item.label}</p>
                                    <p className="text-xl font-semibold text-[#0f172a]">{item.value}</p>
                                </div>
                            </CardContent>
                        </Card>
                    );
                })}
            </div>

            <Card className="overflow-hidden rounded-2xl border-[#c8d4de] shadow-sm">
                <div className="grid min-h-[620px] grid-cols-1 lg:grid-cols-[380px_1fr]">
                    <div className="border-b border-[#eef3f8] lg:border-b-0 lg:border-r">
                        <CardHeader className="space-y-3 border-b border-[#eef3f8] p-4">
                            <CardTitle className="text-base">Messages du site web</CardTitle>
                            <div className="flex items-center gap-2 rounded-xl border border-[#c8d4de] px-3">
                                <Search className="h-4 w-4 text-[#94a3b8]" />
                                <input
                                    value={search}
                                    onChange={(event) => setSearch(event.target.value)}
                                    placeholder="Nom, e-mail ou objet..."
                                    className="h-10 w-full bg-transparent text-sm outline-none"
                                />
                            </div>
                            <Select value={status} onValueChange={setStatus}>
                                <SelectTrigger className="rounded-xl">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Tous les statuts</SelectItem>
                                    {Object.entries(STATUS).map(([key, value]) => (
                                        <SelectItem key={key} value={key}>{value.label}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </CardHeader>

                        <div className="max-h-[520px] overflow-y-auto">
                            {filtered.map((contact) => (
                                <button
                                    key={contact.id}
                                    type="button"
                                    onClick={() => setActiveId(contact.id)}
                                    className={cn(
                                        'w-full border-b border-[#eef3f8] px-4 py-4 text-left transition',
                                        activeId === contact.id ? 'bg-[#eaf4fb]' : 'hover:bg-[#f8fafc]',
                                    )}
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <p className="truncate text-sm font-semibold text-[#0f172a]">{contact.name}</p>
                                        <StatusBadge status={contact.status} />
                                    </div>
                                    <p className="mt-1 truncate text-sm text-[#334155]">{contact.subject}</p>
                                    <div className="mt-2 flex items-center justify-between gap-2 text-xs text-[#64748b]">
                                        <span>{REQUEST_TYPES[contact.requestType] ?? contact.requestType}</span>
                                        <span>{contact.createdAt}</span>
                                    </div>
                                </button>
                            ))}

                            {filtered.length === 0 ? (
                                <div className="px-5 py-12 text-center text-sm text-[#64748b]">
                                    Aucun message trouvé.
                                </div>
                            ) : null}
                        </div>
                    </div>

                    <div className="p-5 md:p-7">
                        {activeContact ? (
                            <div className="mx-auto max-w-3xl space-y-6">
                                <div className="flex flex-col gap-4 border-b border-[#eef3f8] pb-5 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <StatusBadge status={activeContact.status} />
                                            <span className="text-xs font-medium text-[#64748b]">
                                                {REQUEST_TYPES[activeContact.requestType] ?? activeContact.requestType}
                                            </span>
                                        </div>
                                        <h2 className="mt-3 text-xl font-semibold text-[#0f172a]">{activeContact.subject}</h2>
                                        <p className="mt-1 text-sm text-[#64748b]">Reçu le {activeContact.createdAt}</p>
                                    </div>

                                    <Select value={activeContact.status} onValueChange={updateStatus}>
                                        <SelectTrigger className="w-full rounded-xl sm:w-[180px]">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {Object.entries(STATUS).map(([key, value]) => (
                                                <SelectItem key={key} value={key}>{value.label}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="grid gap-3 sm:grid-cols-2">
                                    <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                        <p className="flex items-center gap-2 text-sm font-semibold text-[#0f172a]">
                                            <UserRound className="h-4 w-4 text-[#00559b]" /> {activeContact.name}
                                        </p>
                                        <a href={`mailto:${activeContact.email}`} className="mt-3 flex items-center gap-2 text-sm text-[#00559b] hover:underline">
                                            <Mail className="h-4 w-4" /> {activeContact.email}
                                        </a>
                                        {activeContact.phone ? (
                                            <a href={`tel:${activeContact.phone}`} className="mt-2 flex items-center gap-2 text-sm text-[#00559b] hover:underline">
                                                <Phone className="h-4 w-4" /> {activeContact.phone}
                                            </a>
                                        ) : null}
                                    </div>
                                    <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4 text-sm text-[#64748b]">
                                        <p className="font-semibold text-[#0f172a]">Suivi</p>
                                        <p className="mt-3">Statut : {STATUS[activeContact.status]?.label}</p>
                                        <p className="mt-1">Traité le : {activeContact.processedAt ?? '—'}</p>
                                    </div>
                                </div>

                                <div className="rounded-2xl border border-[#dbe5ee] bg-white p-5 shadow-sm">
                                    <p className="text-xs font-semibold uppercase tracking-wide text-[#64748b]">Message</p>
                                    <p className="mt-4 whitespace-pre-wrap text-sm leading-7 text-[#334155]">{activeContact.message}</p>
                                </div>

                                {activeContact.replies?.length ? (
                                    <div className="space-y-3">
                                        <p className="text-sm font-semibold text-[#0f172a]">Historique des réponses</p>
                                        {activeContact.replies.map((reply) => (
                                            <div key={reply.id} className="rounded-xl border border-[#dbe5ee] bg-[#f8fafc] p-4">
                                                <div className="flex flex-wrap items-center justify-between gap-2">
                                                    <p className="text-sm font-semibold text-[#0f172a]">{reply.subject}</p>
                                                    <Badge className={cn(
                                                        'rounded-full ring-1',
                                                        reply.status === 'sent'
                                                            ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                                                            : reply.status === 'failed'
                                                                ? 'bg-rose-50 text-rose-700 ring-rose-200'
                                                                : reply.status === 'logged'
                                                                    ? 'bg-blue-50 text-blue-700 ring-blue-200'
                                                                    : 'bg-amber-50 text-amber-700 ring-amber-200',
                                                    )}>
                                                        {reply.status === 'sent'
                                                            ? 'Envoyé'
                                                            : reply.status === 'failed'
                                                                ? 'Échec'
                                                                : reply.status === 'logged'
                                                                    ? 'Journalisé — SMTP requis'
                                                                    : 'En attente'}
                                                    </Badge>
                                                </div>
                                                <p className="mt-1 text-xs text-[#64748b]">
                                                    Par {reply.admin} · {reply.sentAt ?? reply.createdAt}
                                                </p>
                                                <div className="mt-3 text-sm leading-6 text-[#334155]" dangerouslySetInnerHTML={{ __html: reply.message }} />
                                            </div>
                                        ))}
                                    </div>
                                ) : null}

                                <form onSubmit={sendReply} className="space-y-4 rounded-2xl border border-[#c8d4de] bg-white p-5">
                                    <div>
                                        <label className="text-sm font-semibold text-[#0f172a]">Objet de la réponse</label>
                                        <input
                                            value={replyForm.data.subject}
                                            onChange={(event) => replyForm.setData('subject', event.target.value)}
                                            className="mt-2 h-11 w-full rounded-xl border border-[#c8d4de] px-3 text-sm outline-none focus:ring-2 focus:ring-[#00559b]/30"
                                        />
                                        {replyForm.errors.subject ? <p className="mt-1 text-xs text-rose-600">{replyForm.errors.subject}</p> : null}
                                    </div>
                                    <div>
                                        <label className="text-sm font-semibold text-[#0f172a]">Message</label>
                                        <RichTextEditor
                                            value={replyForm.data.message}
                                            onChange={(value) => replyForm.setData('message', value)}
                                            height={280}
                                            placeholder="Rédigez votre réponse..."
                                        />
                                        {replyForm.errors.message ? <p className="mt-1 text-xs text-rose-600">{replyForm.errors.message}</p> : null}
                                    </div>
                                    <Button
                                        type="submit"
                                        disabled={replyForm.processing || !replyForm.data.message.trim()}
                                        className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]"
                                    >
                                        <Send className="h-4 w-4" />
                                        {replyForm.processing ? 'Envoi en cours…' : 'Envoyer et conserver la trace'}
                                    </Button>
                                </form>

                                <div className="flex flex-wrap gap-2">
                                    {activeContact.phone ? (
                                        <Button asChild variant="outline" className="rounded-xl border-[#c8d4de]">
                                            <a href={`tel:${activeContact.phone}`}>
                                                <Phone className="h-4 w-4" /> Appeler
                                            </a>
                                        </Button>
                                    ) : null}
                                </div>
                            </div>
                        ) : (
                            <div className="flex min-h-[500px] flex-col items-center justify-center gap-3 text-center text-[#64748b]">
                                <MessageSquareText className="h-10 w-10 text-[#94a3b8]" />
                                <p className="font-medium text-[#0f172a]">Aucun message sélectionné</p>
                            </div>
                        )}
                    </div>
                </div>
            </Card>
        </AdminLayout>
    );
}
