import { useMemo, useRef, useState } from 'react';
import { Link } from '@inertiajs/react';
import {
    CheckCircle2,
    ArrowLeft,
    ChevronRight,
    Clock,
    FileText,
    LifeBuoy,
    Paperclip,
    Send,
    Sparkles,
    Tag,
    Trash2,
    X,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Separator } from '../../../components/ui/separator';
import { cn } from '../../../lib/utils';

const CATEGORIES = [
    { key: 'technique', label: 'Technique', description: 'Bug, erreur, affichage' },
    { key: 'abonnement', label: 'Abonnement', description: 'Offre, modules, options' },
    { key: 'facturation', label: 'Facturation', description: 'Facture, paiement, TVA' },
    { key: 'compte', label: 'Compte', description: 'Utilisateurs, accès, droits' },
    { key: 'autre', label: 'Autre', description: 'Toute autre demande' },
];

const STATUS = {
    open: { label: 'Ouvert', className: 'bg-[#e6f0f9] text-[#00559b] ring-1 ring-inset ring-[#b8d2ea]' },
    pending: { label: 'En attente', className: 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200' },
    resolved: { label: 'Résolu', className: 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200' },
};

const MOCK_MY_REQUESTS = [
    {
        id: 'T-1042',
        subject: "Impossible d'accéder à mon abonnement",
        category: 'Abonnement',
        status: 'open',
        updatedAt: 'Il y a 12 min',
    },
    {
        id: 'T-1037',
        subject: 'Export des mandats en PDF',
        category: 'Technique',
        status: 'pending',
        updatedAt: 'Il y a 2 h',
    },
    {
        id: 'T-1030',
        subject: 'Mise à jour des coordonnées bancaires',
        category: 'Facturation',
        status: 'resolved',
        updatedAt: 'Hier',
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

function formatSize(bytes) {
    if (bytes < 1024) return `${bytes} o`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} Ko`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} Mo`;
}

export default function NouvelleDemande({ support }) {
    const fileInputRef = useRef(null);
    const [category, setCategory] = useState('technique');
    const [subject, setSubject] = useState('');
    const [message, setMessage] = useState('');
    const [files, setFiles] = useState([]);
    const [submitted, setSubmitted] = useState(null);

    const canSubmit = subject.trim().length > 2 && message.trim().length > 5;
    const remaining = 2000 - message.length;

    const selectedCategory = useMemo(
        () => CATEGORIES.find((c) => c.key === category) ?? CATEGORIES[0],
        [category]
    );

    const handleFiles = (list) => {
        const next = Array.from(list).map((file, index) => ({
            id: `${Date.now()}-${index}`,
            name: file.name,
            size: file.size,
        }));
        setFiles((prev) => [...prev, ...next].slice(0, 5));
    };

    const removeFile = (id) => setFiles((prev) => prev.filter((f) => f.id !== id));

    const resetForm = () => {
        setSubject('');
        setMessage('');
        setFiles([]);
        setCategory('technique');
    };

    const handleSubmit = (event) => {
        event.preventDefault();
        if (!canSubmit) return;
        setSubmitted({
            id: `T-${Math.floor(1043 + Math.random() * 50)}`,
            subject: subject.trim(),
            category: selectedCategory.label,
        });
        resetForm();
    };

    return (
        <AgenceLayout title="Nouvelle demande">
            <div className="mx-auto flex min-h-full w-full max-w-5xl flex-col justify-center">
                <div className="flex flex-col gap-5">
                    <div className="flex items-center gap-3">
                        <Button asChild type="button" variant="outline" size="icon" className="rounded-xl border-[#c8d4de]">
                            <Link href="/agence/support">
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                    </div>

                    {submitted ? (
                        <Card className="rounded-2xl border-emerald-200 bg-emerald-50/60">
                            <CardContent className="flex items-start gap-3 p-4">
                                <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                                    <CheckCircle2 className="h-5 w-5" />
                                </span>
                                <div className="flex-1">
                                    <p className="text-sm font-semibold text-[#0f172a]">
                                        Demande envoyée · {submitted.id}
                                    </p>
                                    <p className="mt-0.5 text-sm text-[#4b5b6b]">
                                        Votre demande « {submitted.subject} » a bien été transmise à notre équipe support.
                                        Vous recevrez une réponse par e-mail.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => setSubmitted(null)}
                                    className="rounded-lg p-1 text-[#5f7182] hover:bg-emerald-100"
                                    aria-label="Fermer la confirmation"
                                >
                                    <X className="h-4 w-4" />
                                </button>
                            </CardContent>
                        </Card>
                    ) : null}

                    <Card className="rounded-2xl border-[#c8d4de]">
                        <CardHeader className="border-b border-[#eef3f8]">
                            <CardTitle className="flex items-center gap-2 text-base text-[#0f172a]">
                                <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-[#e6f0f9] text-[#00559b]">
                                    <LifeBuoy className="h-4 w-4" />
                                </span>
                                Décrivez votre demande
                            </CardTitle>
                        </CardHeader>

                        <CardContent className="p-5">
                            <form onSubmit={handleSubmit} className="mt-4 flex flex-col gap-6">
                                <div>
                                    <label className="mb-2 block text-sm font-medium text-[#334155]">Catégorie</label>
                                    <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                        {CATEGORIES.map((item) => {
                                            const isActive = item.key === category;
                                            return (
                                                <button
                                                    key={item.key}
                                                    type="button"
                                                    onClick={() => setCategory(item.key)}
                                                    className={cn(
                                                        'flex flex-col gap-0.5 rounded-xl border px-3 py-2.5 text-left transition-colors',
                                                        isActive
                                                            ? 'border-[#00559b] bg-[#e6f0f9]'
                                                            : 'border-[#c8d4de] bg-white hover:border-[#00559b]/40 hover:bg-[#f7fbfe]'
                                                    )}
                                                >
                                                    <span
                                                        className={cn(
                                                            'text-sm font-medium',
                                                            isActive ? 'text-[#00559b]' : 'text-[#334155]'
                                                        )}
                                                    >
                                                        {item.label}
                                                    </span>
                                                    <span className="text-[11px] text-[#5f7182]">{item.description}</span>
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>

                                <div>
                                    <label htmlFor="subject" className="mb-2 block text-sm font-medium text-[#334155]">
                                        Sujet
                                    </label>
                                    <input
                                        id="subject"
                                        type="text"
                                        value={subject}
                                        onChange={(event) => setSubject(event.target.value)}
                                        maxLength={120}
                                        placeholder="Résumez votre demande en quelques mots"
                                        className="w-full rounded-xl border border-[#c8d4de] bg-white px-4 py-2.5 text-sm text-[#0f172a] placeholder:text-[#94a7b8] focus:border-[#00559b] focus:outline-none focus:ring-2 focus:ring-[#00559b]/20"
                                    />
                                </div>

                                <div>
                                    <div className="mb-2 flex items-center justify-between">
                                        <label htmlFor="message" className="block text-sm font-medium text-[#334155]">
                                            Message
                                        </label>
                                        <span className={cn('text-[11px]', remaining < 100 ? 'text-rose-500' : 'text-[#94a7b8]')}>
                                            {remaining} caractères restants
                                        </span>
                                    </div>
                                    <textarea
                                        id="message"
                                        value={message}
                                        onChange={(event) => setMessage(event.target.value.slice(0, 2000))}
                                        rows={7}
                                        placeholder="Décrivez votre problème ou votre demande le plus précisément possible : contexte, étapes, message d'erreur éventuel..."
                                        className="w-full resize-none rounded-xl border border-[#c8d4de] bg-white px-4 py-3 text-sm leading-relaxed text-[#0f172a] placeholder:text-[#94a7b8] focus:border-[#00559b] focus:outline-none focus:ring-2 focus:ring-[#00559b]/20"
                                    />
                                </div>

                                <div>
                                    <label className="mb-2 block text-sm font-medium text-[#334155]">
                                        Pièces jointes
                                        <span className="ml-1 font-normal text-[#94a7b8]">(5 fichiers max)</span>
                                    </label>

                                    <button
                                        type="button"
                                        onClick={() => fileInputRef.current?.click()}
                                        className="flex w-full flex-col items-center justify-center gap-1.5 rounded-xl border border-dashed border-[#c8d4de] bg-[#f7fbfe] px-4 py-6 text-center transition-colors hover:border-[#00559b]/50 hover:bg-[#e6f0f9]/50"
                                    >
                                        <Paperclip className="h-5 w-5 text-[#00559b]" />
                                        <span className="text-sm font-medium text-[#334155]">
                                            Cliquez pour ajouter des fichiers
                                        </span>
                                        <span className="text-[11px] text-[#5f7182]">PNG, JPG, PDF — 10 Mo max par fichier</span>
                                    </button>
                                    <input
                                        ref={fileInputRef}
                                        type="file"
                                        multiple
                                        className="hidden"
                                        onChange={(event) => {
                                            if (event.target.files) handleFiles(event.target.files);
                                            event.target.value = '';
                                        }}
                                    />

                                    {files.length > 0 ? (
                                        <ul className="mt-3 flex flex-col gap-2">
                                            {files.map((file) => (
                                                <li
                                                    key={file.id}
                                                    className="flex items-center gap-3 rounded-xl border border-[#e2eaf1] bg-white px-3 py-2"
                                                >
                                                    <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#e6f0f9] text-[#00559b]">
                                                        <FileText className="h-4 w-4" />
                                                    </span>
                                                    <div className="min-w-0 flex-1">
                                                        <p className="truncate text-sm text-[#0f172a]">{file.name}</p>
                                                        <p className="text-[11px] text-[#5f7182]">{formatSize(file.size)}</p>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        onClick={() => removeFile(file.id)}
                                                        className="rounded-lg p-1.5 text-[#5f7182] hover:bg-rose-50 hover:text-rose-600"
                                                        aria-label={`Retirer ${file.name}`}
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </button>
                                                </li>
                                            ))}
                                        </ul>
                                    ) : null}
                                </div>

                                <Separator className="bg-[#eef3f8]" />

                                <div className="flex flex-col-reverse items-stretch gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        className="h-10 rounded-xl text-[#5f7182]"
                                        onClick={resetForm}
                                    >
                                        Réinitialiser
                                    </Button>
                                    <Button
                                        type="submit"
                                        className="h-10 rounded-xl bg-[#00559b] text-white hover:bg-[#004980] disabled:opacity-50"
                                        disabled={!canSubmit}
                                    >
                                        <Send className="h-4 w-4" />
                                        Envoyer la demande
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AgenceLayout>
    );
}
