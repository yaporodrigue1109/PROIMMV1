import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import {
    ArrowLeft,
    MessageSquare,
    Send,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../../../components/ui/card';
import { Separator } from '../../../components/ui/separator';
import { Textarea } from '../../../components/ui/textarea';
import { cn } from '../../../lib/utils';

const STATUS = {
    open: { label: 'Ouvert', className: 'bg-[#e6f0f9] text-[#00559b] ring-1 ring-inset ring-[#b8d2ea]' },
    pending: { label: 'En attente', className: 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200' },
    resolved: { label: 'Résolu', className: 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200' },
    closed: { label: 'Fermé', className: 'bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200' },
};

function StatusBadge({ status }) {
    const entry = STATUS[status] ?? STATUS.open;
    return (
        <span className={cn('inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', entry.className)}>
            {entry.label}
        </span>
    );
}

export default function Show({ ticket = {} }) {
    const [messages, setMessages] = useState(() => ticket.messages ?? []);
    const [draft, setDraft] = useState('');
    const endRef = useRef(null);
    const isLocked = ticket.status === 'resolved';

    useEffect(() => {
        endRef.current?.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }, [messages.length]);

    useEffect(() => {
        setMessages(ticket.messages ?? []);
    }, [ticket.messages]);

    useEffect(() => {
        const refreshTicket = () => router.reload({ only: ['ticket'], preserveScroll: true });
        window.addEventListener('focus', refreshTicket);
        return () => window.removeEventListener('focus', refreshTicket);
    }, []);

  

    const canSend = !isLocked && draft.trim().length > 0;

    const handleSend = (event) => {
        event.preventDefault();

        const body = draft.trim();
        if (!body || isLocked) {
            return;
        }

        router.post(
            `/agence/support/${ticket.uuid ?? ticket.id}/reponses`,
            { message: body },
            {
                preserveScroll: true,
                onSuccess: () => setDraft(''),
            },
        );
    };

    const handleKeyDown = (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            handleSend(event);
        }
    };

    return (
        <AgenceLayout title={`Demande ${ticket.id ?? ''}`.trim()}>
            <Head title={`Demande ${ticket.id ?? ''}`.trim()} />

            <div className="mx-auto flex min-h-full w-full max-w-5xl flex-col justify-center gap-6">
                <div className="flex items-center gap-3">
                    <Button asChild type="button" variant="outline" size="icon" className="rounded-xl border-[#c8d4de]">
                        <Link href="/agence/support">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div className="space-y-1">
                        <div className="flex flex-wrap items-center gap-2">
                            <Badge variant="outline" className="rounded-full border-[#c8d4de] bg-white text-[#0f172a]">
                                {ticket.id ?? 'Ticket'}
                            </Badge>
                            <StatusBadge status={ticket.status} />
                        </div>
                        <h1 className="text-xl font-semibold text-[#0f172a]">
                            {ticket.subject ?? 'Détail de la demande'}
                        </h1>
                    </div>
                </div>

                <div className="flex justify-center">
                    <Card className="w-full max-w-4xl overflow-hidden rounded-2xl border-[#c8d4de]">
                        <CardHeader className="border-b border-[#eef3f8]">
                            <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <CardTitle className="flex items-center gap-2 text-sm text-[#0f172a]">
                                        <MessageSquare className="h-4 w-4 text-[#5f7182]" />
                                        Conversation
                                    </CardTitle>
                                </div>
                            
                            </div>
                        </CardHeader>

                        <CardContent className="flex min-h-[620px] flex-col p-0">
                            <div className="flex-1 space-y-4 overflow-y-auto p-5">
                                <div className="rounded-2xl border border-[#e2eaf1] bg-[#f9fbfd] p-4">
                                    <p className="text-sm font-semibold text-[#0f172a]">Résumé de la demande</p>
                                    <p className="mt-1 text-sm leading-6 text-[#334155]">
                                        {ticket.description ?? 'Aucune description disponible.'}
                                    </p>
                                </div>

                                {messages.map((message, index) => {
                                    const isClient = message.author === 'Client';
                                    const isAgency = message.author === 'Agence';

                                    return (
                                        <div
                                            key={`${message.time}-${index}`}
                                            className={cn('flex', isClient ? 'justify-start' : 'justify-end')}
                                        >
                                            <div
                                                className={cn(
                                                    'max-w-[85%] rounded-2xl border p-4 shadow-sm',
                                                    isClient
                                                        ? 'border-[#e2eaf1] bg-white'
                                                        : isAgency
                                                            ? 'border-[#cfe3f5] bg-[#eaf4fb]'
                                                            : 'border-[#d7e7cf] bg-[#f2f9e8]'
                                                )}
                                            >
                                                <div className="mb-2 flex items-center justify-between gap-4">
                                                    <p className="text-sm font-semibold text-[#0f172a]">{message.author}</p>
                                                    <span className="text-[11px] text-[#94a3b8]">{message.time}</span>
                                                </div>
                                                <p className="text-sm leading-6 text-[#334155]">{message.body}</p>
                                            </div>
                                        </div>
                                    );
                                })}

                                <div ref={endRef} />
                            </div>

                            <Separator />

                            {isLocked ? (
                                <div className="p-5">
                                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm text-emerald-700">
                                        Ce ticket est résolu. Il n&apos;est plus possible d&apos;ajouter une réponse.
                                    </div>
                                </div>
                            ) : (
                                <form onSubmit={handleSend} className="space-y-3 p-5">
                                    <Textarea
                                        value={draft}
                                        onChange={(event) => setDraft(event.target.value)}
                                        onKeyDown={handleKeyDown}
                                        placeholder="Écrire une réponse... (Entrée pour envoyer, Shift+Entrée pour une nouvelle ligne)"
                                        className="min-h-[120px] resize-none rounded-xl border-[#c8d4de] bg-white"
                                    />
                                    <div className="flex flex-col gap-3 sm:flex-end">
                                        <Button
                                            type="submit"
                                            className="h-10 rounded-xl bg-[#00559b] text-white hover:bg-[#004980]"
                                            disabled={!canSend}
                                        >
                                            <Send className="h-4 w-4" />
                                            Envoyer
                                        </Button>
                                    </div>
                                </form>
                            )}
                        </CardContent>
                    </Card>

                    
                </div>
            </div>
        </AgenceLayout>
    );
}
