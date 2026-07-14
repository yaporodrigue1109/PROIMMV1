import { useEffect, useMemo, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Banknote,
    CalendarDays,
    Home,
    Loader2,
    Search,
    ShieldCheck,
    Smartphone,
    UserRound,
    Wallet,
    Wrench,
    ShoppingBag,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '../../../components/ui/dialog';
import { Input } from '../../../components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
import { agenceButtonStyles } from '../../../lib/buttonStyles';
import { cn } from '../../../lib/utils';

const currency = (value) =>
    new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const paymentShortcuts = [
    { title: 'Loyers', href: '/agence/caisse/loyer', icon: Home, active: true },
    { title: 'Maintenance', href: '/agence/caisse/maintenance', icon: Wrench, active: false },
    { title: 'Dépenses agence', href: '/agence/caisse/depense-agence', icon: ShoppingBag, active: false },
    { title: 'Vente de biens', href: '/agence/caisse/vente-bien', icon: Banknote, active: false },
];

function Field({ label, required, error, children, className }) {
    return (
        <div className={cn('space-y-1.5', className)}>
            <label className="block text-sm font-medium text-[#0f172a]">
                {label}
                {required ? <span className="ml-0.5 text-[#b42318]">*</span> : null}
            </label>
            {children}
            {error ? <p className="text-xs text-[#b42318]">{error}</p> : null}
        </div>
    );
}

function SectionCard({ icon: Icon, title, description, children }) {
    return (
        <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardHeader className="flex flex-row items-center gap-3 border-b border-[#e2e8f0] py-4">
                <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eaf4fb] text-[#00559b]">
                    <Icon className="h-5 w-5" />
                </span>
                <div>
                    <CardTitle className="text-sm text-[#0f172a]">{title}</CardTitle>
                    {description ? <CardDescription className="text-xs text-[#5f7182]">{description}</CardDescription> : null}
                </div>
            </CardHeader>
            <CardContent className="p-6">{children}</CardContent>
        </Card>
    );
}

function EmptyState({ title, desc }) {
    return (
        <div className="flex flex-col items-center justify-center gap-2 rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] px-4 py-10 text-center">
            <Search className="h-6 w-6 text-[#94a3b8]" />
            <p className="text-sm font-semibold text-[#0f172a]">{title}</p>
            <p className="max-w-sm text-sm text-[#5f7182]">{desc}</p>
        </div>
    );
}

function StatusBadge({ status }) {
    const text = String(status ?? '').toLowerCase();

    if (text.includes('retard') || text.includes('impaye')) {
        return <Badge variant="danger" className="rounded-full px-2.5 py-1 text-[11px] font-medium">En retard</Badge>;
    }

    if (text.includes('partiel')) {
        return <Badge variant="warning" className="rounded-full px-2.5 py-1 text-[11px] font-medium">Partiel</Badge>;
    }

    return <Badge variant="success" className="rounded-full px-2.5 py-1 text-[11px] font-medium">A jour</Badge>;
}

function PaymentStatusBadge({ item }) {
    if (!item) return null;

    const text = String(item.className ?? '').toLowerCase();

    if (text.includes('danger')) {
        return <Badge variant="danger" className="rounded-full px-2.5 py-1 text-[11px] font-medium">{item.label}</Badge>;
    }

    if (text.includes('warning')) {
        return <Badge variant="warning" className="rounded-full px-2.5 py-1 text-[11px] font-medium">{item.label}</Badge>;
    }

    return <Badge variant="success" className="rounded-full px-2.5 py-1 text-[11px] font-medium">{item.label}</Badge>;
}

function InfoTile({ label, value }) {
    return (
        <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
            <p className="text-xs uppercase tracking-wide text-[#94a3b8]">{label}</p>
            <div className="mt-1 text-sm font-semibold text-[#0f172a]">{value}</div>
        </div>
    );
}

export default function Loyer({ modePaiement = [] }) {
    const [query, setQuery] = useState('');
    const [searching, setSearching] = useState(false);
    const [searchError, setSearchError] = useState('');
    const [tenantData, setTenantData] = useState(null);
    const [selectedIndex, setSelectedIndex] = useState(0);
    const [montant, setMontant] = useState('');
    const [modePaiementId, setModePaiementId] = useState(modePaiement?.[0]?.id ? String(modePaiement[0].id) : '');
    const [commentaire, setCommentaire] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const [flash, setFlash] = useState(null);
    const [paymentModalOpen, setPaymentModalOpen] = useState(false);

    const selectedRental = useMemo(() => tenantData?.rentals?.[selectedIndex] ?? null, [tenantData, selectedIndex]);

    useEffect(() => {
        const trimmed = query.trim();

        if (trimmed.length < 2) {
            setSearching(false);
            setSearchError('');
            setTenantData(null);
            setSelectedIndex(0);
            return undefined;
        }

        const controller = new AbortController();
        const timeoutId = window.setTimeout(async () => {
            setSearching(true);
            setSearchError('');

            try {
                const response = await fetch(`/agence/caisse/loyer/search?q=${encodeURIComponent(trimmed)}`, {
                    signal: controller.signal,
                    headers: { Accept: 'application/json' },
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload?.message ?? 'Recherche impossible.');
                }

                if (payload?.data?.rentals?.length) {
                    setTenantData(payload.data);
                    setSelectedIndex(0);
                } else {
                    setTenantData(null);
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    setSearchError(error.message || 'Erreur lors de la recherche.');
                    setTenantData(null);
                }
            } finally {
                if (!controller.signal.aborted) {
                    setSearching(false);
                }
            }
        }, 350);

        return () => {
            controller.abort();
            window.clearTimeout(timeoutId);
        };
    }, [query]);

    useEffect(() => {
        if (!selectedRental) {
            setMontant('');
            setPaymentModalOpen(false);
            return;
        }

        setMontant(String(selectedRental.due > 0 ? selectedRental.due : selectedRental.rent));
    }, [selectedRental]);

    const stats = [
        {
            label: 'Locataire',
            value: tenantData?.name ?? '—',
            icon: UserRound,
            accent: 'bg-[#eaf4fb] text-[#00559b]',
        },
        {
            label: 'Baux trouvés',
            value: tenantData?.rentals?.length ? `${tenantData.rentals.length}` : '—',
            icon: Banknote,
            accent: 'bg-[#eef8df] text-[#4d8500]',
        },
        {
            label: 'Mode paiement',
            value: modePaiement.find((item) => String(item.id) === modePaiementId)?.name ?? '—',
            icon: Wallet,
            accent: 'bg-[#fff2e6] text-[#c2410c]',
        },
        {
            label: 'Statut',
            value: selectedRental ? selectedRental.status : '—',
            icon: ShieldCheck,
            accent: 'bg-[#f1f5f9] text-[#5f7182]',
        },
    ];

    const handleSubmit = async (event) => {
        event.preventDefault();

        if (!selectedRental) {
            setFlash({ type: 'error', message: 'Sélectionnez d’abord un locataire.' });
            return;
        }

        const numericAmount = Number(montant);

        if (!numericAmount || numericAmount <= 0) {
            setFlash({ type: 'error', message: 'Saisissez un montant valide.' });
            return;
        }

        if (!modePaiementId) {
            setFlash({ type: 'error', message: 'Choisissez un mode de paiement.' });
            return;
        }

        setSubmitting(true);
        setFlash(null);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

            const response = await fetch('/agence/caisse/loyer/pay', { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    locataire_id: selectedRental.locataire_id,
                    batiment_id: selectedRental.batiment_id,
                    agence_id: selectedRental.agence_id,
                    propriete_id: selectedRental.propriete_id,
                    lot_id: selectedRental.lot_id,
                    porte_id: selectedRental.porte_id,
                    montant: numericAmount,
                    mode_paiement_id: Number(modePaiementId),
                    commentaire:'test',
                }),
            });

            const payload = await response.json().catch(() => null);

            if (!response.ok || !payload?.success) {
                const validationErrors = payload?.errors
                    ? Object.values(payload.errors).flat().join(' ')
                    : '';

                throw new Error(validationErrors || payload?.message || 'Le paiement a échoué.');
            }

            setFlash({ type: 'success', message: payload.message ?? 'Paiement enregistré avec succès.' });
            setCommentaire('');
            setTenantData(null);
            setQuery('');
            setPaymentModalOpen(false);
        } catch (error) {
            setFlash({ type: 'error', message: error.message || 'Erreur lors du paiement.' });
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <AgenceLayout title="Paiement loyer">
            <Head title="Paiement loyer" />

            <div className="mx-auto flex max-w-6xl flex-col gap-6 pb-10">
                <div className="flex items-center gap-3">
                    <Button asChild variant="outline" size="icon" className="rounded-xl border-[#c8d4de]">
                        <Link href="/agence/caisse">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>

                    <div>
                        <h2 className="text-xl font-semibold text-[#0f172a]">Encaissement du loyer</h2>
                    </div>
                </div>

                <div className="flex flex-wrap justify-center gap-2">
                    {paymentShortcuts.map((shortcut) => {
                        const Icon = shortcut.icon;

                        return (
                            <Link
                                key={shortcut.title}
                                href={shortcut.href}
                                className={cn(
                                    'flex items-center gap-2 rounded-xl border px-4 py-3 text-sm font-medium transition',
                                    shortcut.active
                                        ? 'border-[#00559b] bg-[#00559b] text-white'
                                        : 'border-[#c8d4de] bg-white text-[#0f172a] hover:border-[#00559b] hover:text-[#00559b]'
                                )}
                            >
                                <Icon className="h-4 w-4" />
                                {shortcut.title}
                            </Link>
                        );
                    })}
                </div>

                <SectionCard icon={Search} title="Recherche locataire" description="Tapez le nom ou le téléphone.">
                    <div className="mt-4 space-y-4">
                        <Field label="Rechercher" className="space-y-2">
                            <div className="relative">
                                <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#94a3b8]" />
                                <Input
                                    value={query}
                                    onChange={(event) => setQuery(event.target.value)}
                                    placeholder="Nom du locataire, téléphone..."
                                    className="h-11 rounded-xl border-[#c8d4de] pl-9"
                                />
                            </div>
                        </Field>

                        {searching ? (
                            <div className="flex items-center gap-2 rounded-xl border border-[#e2e8f0] bg-[#f8fafc] px-4 py-3 text-sm text-[#5f7182]">
                                <Loader2 className="h-4 w-4 animate-spin text-[#00559b]" />
                                Recherche en cours...
                            </div>
                        ) : null}

                        {searchError ? (
                            <div className="rounded-xl border border-[#f4c7c3] bg-[#fdecec] px-4 py-3 text-sm text-[#b42318]">
                                {searchError}
                            </div>
                        ) : null}

                        {!searching && query.trim().length < 2 ? (
                            <EmptyState
                                title="Commencez la recherche"
                                desc="Tapez au moins 2 caractères pour afficher les locataires correspondants."
                            />
                        ) : null}

                        {!searching && query.trim().length >= 2 && !tenantData ? (
                            <EmptyState
                                title="Aucun résultat"
                                desc="Aucun locataire actif ne correspond à la recherche saisie."
                            />
                        ) : null}
                    </div>
                </SectionCard>

                {tenantData?.rentals?.length ? (
                    <div className="grid grid-cols-1 gap-6 xl:grid-cols-[1.1fr_1.4fr]">
                        <div className="flex flex-col gap-4">
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                {stats.map((stat) => {
                                    const Icon = stat.icon;

                                    return (
                                        <Card key={stat.label} className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                                            <CardContent className="mt-6 flex items-center gap-3 p-4">
                                                <span className={cn('flex h-11 w-11 shrink-0 items-center justify-center rounded-xl', stat.accent)}>
                                                    <Icon className="h-5 w-5" />
                                                </span>
                                                <div className="min-w-0">
                                                    <p className="truncate text-xl font-bold text-[#0f172a]">{stat.value}</p>
                                                    <p className="truncate text-[11px] uppercase tracking-wide text-[#94a3b8]">{stat.label}</p>
                                                </div>
                                            </CardContent>
                                        </Card>
                                    );
                                })}
                            </div>

                            <SectionCard
                                icon={UserRound}
                                title={tenantData.name}
                                description={`${tenantData.rentals.length} bail${tenantData.rentals.length > 1 ? 's' : ''} trouvé${tenantData.rentals.length > 1 ? 's' : ''}`}
                            >
                                <div className="space-y-2">
                                    {tenantData.rentals.map((rental, index) => {
                                        const isActive = index === selectedIndex;

                                        return (
                                            <button
                                                key={`${rental.locataire_agence_id}-${rental.porte_id}`}
                                                type="button"
                                                onClick={() => setSelectedIndex(index)}
                                                className={cn(
                                                    'w-full rounded-2xl border p-4 text-left transition',
                                                    isActive
                                                        ? 'border-[#00559b] bg-[#eaf4fb] shadow-sm'
                                                        : 'border-[#c8d4de] bg-white hover:border-[#00559b]/40 hover:shadow-sm'
                                                )}
                                            >
                                                <div className="flex items-start justify-between gap-3">
                                                    <div className="min-w-0">
                                                        <p className={cn('text-sm font-semibold', isActive ? 'text-[#00559b]' : 'text-[#0f172a]')}>
                                                            {rental.property}
                                                        </p>
                                                        <p className="mt-1 text-xs text-[#5f7182]">{rental.location || 'Localisation non précisée'}</p>
                                                        <p className="mt-1 text-xs text-[#5f7182]">{rental.period || 'Période inconnue'}</p>
                                                    </div>
                                                    <StatusBadge status={rental.status} />
                                                </div>
                                                <div className="mt-3 grid grid-cols-2 gap-2 text-xs">
                                                    <div className="rounded-xl bg-white/80 px-3 py-2">
                                                        <span className="block text-[#5f7182]">Loyer</span>
                                                        <strong className="text-[#0f172a]">{currency(rental.rent)}</strong>
                                                    </div>
                                                    <div className="rounded-xl bg-white/80 px-3 py-2">
                                                        <span className="block text-[#5f7182]">Restant dû</span>
                                                        <strong className="text-[#0f172a]">{currency(rental.due)}</strong>
                                                    </div>
                                                </div>
                                            </button>
                                        );
                                    })}
                                </div>
                            </SectionCard>
                        </div>

                        <div className="flex flex-col gap-6">
                            <SectionCard
                                icon={Banknote}
                                title="Détails du bail"
                                description="Les informations du bail sélectionné s’affichent ici."
                            >
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <InfoTile label="Bien" value={selectedRental.property} />
                                    <InfoTile label="Localisation" value={selectedRental.location || '—'} />
                                    <InfoTile label="Période" value={selectedRental.period || '—'} />
                                    <InfoTile label="Dernier paiement" value={selectedRental.lastPayment || '—'} />
                                    <InfoTile label="Retard" value={selectedRental.delay || '—'} />
                                    <InfoTile label="Statut paiement" value={<PaymentStatusBadge item={selectedRental.paymentStatus} />} />
                                    <InfoTile label="Loyer mensuel" value={currency(selectedRental.rent)} />
                                    <InfoTile label="Montant dû" value={currency(selectedRental.due)} />
                                </div>

                                <div className="mt-5 flex justify-end">
                                    <Button
                                        type="button"
                                        onClick={() => setPaymentModalOpen(true)}
                                        className={agenceButtonStyles.primary}
                                        disabled={!selectedRental}
                                    >
                                        <Wallet className="h-4 w-4" />
                                        Payer le loyer
                                    </Button>
                                </div>
                            </SectionCard>

                            {selectedRental?.history?.length ? (
                                <SectionCard
                                    icon={CalendarDays}
                                    title="Historique récent"
                                    description="Les derniers paiements enregistrés pour ce bail."
                                >
                                    <div className="space-y-2">
                                        {selectedRental.history.map((item) => (
                                            <div
                                                key={`${item.period}-${item.status}-${item.amount}`}
                                              //  className="flex items-center justify-between gap-3 rounded-xl border border-[#e2e8f0] bg-[#20c997] px-4 py-3"
                                            className={`flex items-center justify-between gap-3 rounded-xl border border-[#e2e8f0] bg-[${item.className || '#ffffff'}] px-4 py-3`}
                                                
                                            >
                                                <div>
                                                    <p className="text-sm font-medium text-[#0f172a]">{item.period}</p>
                                                    <p className="text-xs text-[#5f7182]">{item.status}</p>
                                                </div>
                                                {/* <div className={['text-right', item.className || '—'].join(' ')}>
                                                    <p className="text-sm font-semibold text-[#0f172a]">{currency(item.amount)}</p>
                                                    
                         
                                                </div> */}
                                                <div className={['text-right', item.className || '—'].join(' ')}>
                                                    <p className="text-sm font-semibold text-[#0f172a]">{currency(item.amount)}</p>
                                                    
                         
                                                </div> 
                                            </div>
                                        ))}
                                    </div>
                                </SectionCard>
                            ) : null}
                        </div>
                    </div>
                ) : null}

                <Dialog open={paymentModalOpen} onOpenChange={setPaymentModalOpen}>
                    <DialogContent className="sm:max-w-2xl">
                        <DialogHeader>
                            <DialogTitle className="text-[#0f172a]">Payer le loyer</DialogTitle>
                            <DialogDescription className="text-[#5f7182]">
                                {selectedRental
                                    ? `Encaissement pour ${selectedRental.property} - ${selectedRental.location || 'Localisation non précisée'}`
                                    : 'Sélectionnez d’abord un bail.'}
                            </DialogDescription>
                        </DialogHeader>

                        <form className="space-y-5 pt-2" onSubmit={handleSubmit}>
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <Field label="Montant" required>
                                    <Input
                                        type="number"
                                        min="1"
                                        value={montant}
                                        onChange={(event) => setMontant(event.target.value)}
                                        placeholder="Montant encaissé"
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                    />
                                </Field>

                                <Field label="Mode de paiement" required>
                                    <Select value={modePaiementId} onValueChange={setModePaiementId}>
                                        <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                            <SelectValue placeholder="Sélectionner un mode" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {modePaiement.map((item) => (
                                                <SelectItem key={String(item.id)} value={String(item.id)}>
                                                    {item.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>

                              {/*  <Field label="Observation" className="md:col-span-2">
                                    <textarea
                                        value={commentaire}
                                        onChange={(event) => setCommentaire(event.target.value)}
                                        rows={4}
                                        placeholder="Observation facultative..."
                                        className="min-h-[110px] rounded-xl border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] outline-none transition focus:border-[#00559b]"
                                    />
                                </Field> */}
                            </div>

                            <div className="flex flex-col gap-3 rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div className="flex items-center gap-3 text-sm text-[#5f7182]">
                                    <CalendarDays className="h-4 w-4 text-[#00559b]" />
                                    <span>
                                        {selectedRental
                                            ? `Paiement pour ${selectedRental.property}`
                                            : 'Sélectionnez un bail pour préremplir le montant'}
                                    </span>
                                </div>

                                <Button type="submit" className={agenceButtonStyles.primary} disabled={submitting || !selectedRental}>
                                    {submitting ? (
                                        <>
                                            <Loader2 className="h-4 w-4 animate-spin" />
                                            Enregistrement...
                                        </>
                                    ) : (
                                        <>
                                            <Smartphone className="h-4 w-4" />
                                            Valider le paiement
                                        </>
                                    )}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </AgenceLayout>
    );
}
