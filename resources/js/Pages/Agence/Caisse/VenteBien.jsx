import { useEffect, useMemo, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Banknote,
    CalendarDays,
    Check,
    ChevronDown,
    Home,
    Layers3,
    Loader2,
    MapPin,
    Plus,
    Search,
    Smartphone,
    Tags,
    Trash2,
    UserRound,
    Wrench,
    X,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent } from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';
import { agenceButtonStyles } from '../../../lib/buttonStyles';
import { cn } from '../../../lib/utils';

const currency = (value) =>
    new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const shortcuts = [
    { title: 'Loyers', href: '/agence/caisse/loyer', icon: Home, active: false },
    { title: 'Maintenance', href: '/agence/caisse/maintenance', icon: Wrench, active: false },
    { title: 'Dépenses agence', href: '/agence/caisse/depense-agence', icon: Banknote, active: false },
    { title: 'Vente de biens', href: '/agence/caisse/vente-bien', icon: Tags, active: true },
];

const paymentModes = ['Espèces', 'Wave', 'Orange Money', 'Virement bancaire', 'Chèque'];

const today = () => new Date().toISOString().slice(0, 10);

function normalize(value) {
    return String(value || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

function makeLineId(prefix = 'line') {
    return `${prefix}-${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

function hasSaleEligibleItems(property) {
    return Boolean(
        property?.forSale ||
        property?.buildings?.some((building) => building?.forSale || building?.doors?.some((door) => door?.forSale)) ||
        property?.doors?.some((door) => door?.forSale)
    );
}

function PropertyCard({ property, active, onClick }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={cn(
                'w-full rounded-2xl border p-4 text-left transition',
                active
                    ? 'border-[#00559b] bg-[#eaf4fb] shadow-sm'
                    : 'border-[#c8d4de] bg-white hover:border-[#00559b] hover:shadow-sm'
            )}
        >
            <div className="flex items-start justify-between gap-3">
                <div className="min-w-0">
                    <strong className="block truncate text-sm text-[#0f172a]">{property.title}</strong>
                    <span className="mt-1 block text-xs text-[#5f7182]">{property.location}</span>
                </div>
                <Badge variant={property.badge === 'Réservé' ? 'warning' : 'success'} className="rounded-full px-2.5 py-1 text-[11px]">
                    {property.badge}
                </Badge>
            </div>

            <div className="mt-3 flex items-center justify-between gap-3">
                <span className="text-xs text-[#5f7182]">{property.type}</span>
                <strong className="text-sm text-[#4d8500]">{currency(property.price)}</strong>
            </div>
        </button>
    );
}

function ComboboxField({ label, required, value, placeholder, options, open, onOpenChange, onSearchChange, searchValue, onSelect, emptyLabel }) {
    const selectedLabel = options.find((option) => String(option.value) === String(value))?.label ?? '';

    return (
        <div className="space-y-1.5 w-full">
            <label className="block text-sm font-medium text-[#0f172a]">
                {label}
                {required ? <span className="ml-0.5 text-[#b42318]">*</span> : null}
            </label>

            <div className="relative w-full">
                <div
                    className={cn(
                        'flex h-11 w-full items-center justify-between rounded-xl border bg-white px-3 text-left transition',
                        open ? 'border-[#00559b]' : 'border-[#c8d4de]'
                    )}
                    role="button"
                    tabIndex={0}
                    onClick={() => onOpenChange(!open)}
                    onKeyDown={(event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            onOpenChange(!open);
                        }
                    }}
                >
                    <div className="flex min-w-0 items-center gap-2">
                        <Search className="h-4 w-4 shrink-0 text-[#94a3b8]" />
                        <span className={cn('truncate text-sm', selectedLabel ? 'text-[#0f172a]' : 'text-[#94a3b8]')}>
                            {selectedLabel || placeholder}
                        </span>
                    </div>

                    <div className="ml-3 flex items-center gap-2">
                        {value ? (
                            <button
                                type="button"
                                className="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[#5f7182] transition hover:bg-[#f1f5f9] hover:text-[#0f172a]"
                                onClick={(event) => {
                                    event.stopPropagation();
                                    onSelect('');
                                    onSearchChange('');
                                    onOpenChange(false);
                                }}
                                aria-label={`Effacer ${label}`}
                            >
                                <X className="h-4 w-4" />
                            </button>
                        ) : null}
                        <ChevronDown className="h-4 w-4 shrink-0 text-[#94a3b8]" />
                    </div>
                </div>

                {open ? (
                    <div className="mt-2 w-full overflow-hidden rounded-xl border border-[#c8d4de] bg-white shadow-lg">
                        <div className="border-b border-[#e2e8f0] p-2">
                            <div className="flex h-10 items-center rounded-lg border border-[#c8d4de] bg-white px-3 focus-within:border-[#00559b]">
                                <Search className="h-4 w-4 shrink-0 text-[#94a3b8]" />
                                <input
                                    value={searchValue}
                                    onChange={(event) => onSearchChange(event.target.value)}
                                    placeholder={`Rechercher ${label.toLowerCase()}...`}
                                    className="h-9 w-full border-0 bg-transparent px-2 text-sm text-[#0f172a] outline-none placeholder:text-[#8798a5]"
                                    autoFocus
                                />
                            </div>
                        </div>

                        <div className="max-h-60 overflow-y-auto p-1">
                            {options.length ? (
                                options.map((option) => {
                                    const active = String(option.value) === String(value);
                                    return (
                                        <button
                                            key={option.value}
                                            type="button"
                                            className={cn(
                                                'flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm transition',
                                                active ? 'bg-[#eaf4fb] text-[#00559b]' : 'text-[#0f172a] hover:bg-[#f8fafc]'
                                            )}
                                            onClick={() => {
                                                onSelect(option.value);
                                                onSearchChange(option.label);
                                                onOpenChange(false);
                                            }}
                                        >
                                            <span className="truncate">{option.label}</span>
                                            {active ? <Check className="h-4 w-4" /> : null}
                                        </button>
                                    );
                                })
                            ) : (
                                <div className="px-3 py-2 text-sm text-[#5f7182]">{emptyLabel}</div>
                            )}
                        </div>
                    </div>
                ) : null}
            </div>
        </div>
    );
}

function InfoTile({ label, value, accent, icon: Icon }) {
    return (
        <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
            <div className="flex items-center gap-2 text-xs uppercase tracking-wide text-[#94a3b8]">
                {Icon ? <Icon className={cn('h-3.5 w-3.5', accent)} /> : null}
                <span>{label}</span>
            </div>
            <strong className={cn('mt-2 block text-sm', accent)}>{value}</strong>
        </div>
    );
}

function ScheduleLine({ line, onChange, onRemove, showRemove = true }) {
    return (
        <div className="grid grid-cols-1 gap-3 rounded-2xl border border-[#dbe7ee] bg-[#f8fafc] p-4 md:grid-cols-[1.2fr_1fr_1fr_1fr_auto]">
            <label className="space-y-1">
                <span className="text-xs text-[#5f7182]">Libellé</span>
                <Input value={line.label} onChange={(e) => onChange({ ...line, label: e.target.value })} className="h-11 rounded-xl border-[#c8d4de]" />
            </label>

            <label className="space-y-1">
                <span className="text-xs text-[#5f7182]">Montant</span>
                <Input
                    type="number"
                    min="0"
                    value={line.amount}
                    onChange={(e) => onChange({ ...line, amount: e.target.value })}
                    className="h-11 rounded-xl border-[#c8d4de]"
                />
            </label>

            <label className="space-y-1">
                <span className="text-xs text-[#5f7182]">Date prévue</span>
                <Input type="date" value={line.date} onChange={(e) => onChange({ ...line, date: e.target.value })} className="h-11 rounded-xl border-[#c8d4de]" />
            </label>

            <label className="space-y-1">
                <span className="text-xs text-[#5f7182]">Mode</span>
                <select
                    value={line.mode}
                    onChange={(e) => onChange({ ...line, mode: e.target.value })}
                    className="h-11 w-full rounded-xl border border-[#c8d4de] bg-white px-3 text-sm text-[#0f172a] outline-none focus:border-[#00559b]"
                >
                    {paymentModes.map((mode) => (
                        <option key={mode} value={mode}>
                            {mode}
                        </option>
                    ))}
                </select>
            </label>

            {showRemove ? (
                <div className="flex items-end">
                    <Button type="button" variant="outline" size="icon" className="h-11 w-11 rounded-xl border-[#c8d4de]" onClick={onRemove}>
                        <Trash2 className="h-4 w-4 text-[#b42318]" />
                    </Button>
                </div>
            ) : null}
        </div>
    );
}

export default function VenteBien({ caisseOuverte = true, saleOwners = [] }) {
    const [ownerValue, setOwnerValue] = useState('');
    const [ownerSearch, setOwnerSearch] = useState('');
    const [ownerOpen, setOwnerOpen] = useState(false);
    const [selectedIndex, setSelectedIndex] = useState(0);
    const [modalOpen, setModalOpen] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [flash, setFlash] = useState(null);
    const [paymentPlan, setPaymentPlan] = useState('complete');
    const [saleDate, setSaleDate] = useState(today());
    const [buyerForm, setBuyerForm] = useState({
        name: '',
        phone: '',
        email: '',
        address: '',
        idType: 'CNI',
        idNumber: '',
    });
    const [completePayment, setCompletePayment] = useState({
        amount: '',
        mode: 'Espèces',
    });
    const [tranches, setTranches] = useState([
        { id: makeLineId('tranche'), label: 'Tranche 1', amount: '', date: '', mode: 'Espèces' },
        { id: makeLineId('tranche'), label: 'Tranche 2', amount: '', date: '', mode: 'Espèces' },
    ]);
    const [customPayments, setCustomPayments] = useState([
        { id: makeLineId('custom'), label: 'Paiement prévu', amount: '', date: '', mode: 'Espèces' },
    ]);
    const [monthly, setMonthly] = useState({
        deposit: 0,
        count: 6,
        firstDate: '',
    });

    const saleEligibleOwners = useMemo(() => (Array.isArray(saleOwners) ? saleOwners : []), [saleOwners]);

    const selectedOwner = useMemo(
        () => saleEligibleOwners.find((owner) => owner.id === ownerValue) ?? null,
        [ownerValue, saleEligibleOwners]
    );

    const saleProperties = useMemo(() => {
        if (!selectedOwner) return [];
        return selectedOwner.properties.filter(hasSaleEligibleItems);
    }, [selectedOwner]);

    const emptyProperty = useMemo(
        () => ({
            id: null,
            title: 'Aucun bien disponible',
            location: '',
            type: '',
            price: 0,
            commission: 0,
            ownerAmount: 0,
            buyer: '',
            status: '',
            badge: 'Indisponible',
            reference: '',
            date: '',
            observation: 'Aucun bien en vente n’est actuellement proposé pour ce propriétaire.',
        }),
        []
    );

    const selectedProperty = saleProperties[selectedIndex] || saleProperties[0] || emptyProperty;

    useEffect(() => {
        setSelectedIndex(0);
    }, [ownerValue]);

    const ownerOptions = useMemo(
        () =>
            saleEligibleOwners.map((owner) => ({
                value: owner.id,
                label: owner.name,
                search: `${owner.name} ${owner.phone}`.toLowerCase(),
            })),
        []
    );

    const searchableOwnerOptions = useMemo(() => {
        const term = ownerSearch.trim().toLowerCase();
        if (!term) return ownerOptions;
        return ownerOptions.filter((item) => item.search.includes(term));
    }, [ownerOptions, ownerSearch]);

    const ownerSelectedLabel = ownerOptions.find((opt) => opt.value === ownerValue)?.label ?? '';

    useEffect(() => {
        if (!selectedProperty) return;

        setBuyerForm({
            name: selectedProperty.buyer !== 'Aucun acheteur' ? selectedProperty.buyer : '',
            phone: '',
            email: '',
            address: '',
            idType: 'CNI',
            idNumber: '',
        });
        setCompletePayment((current) => ({ ...current, amount: selectedProperty.price.toString() }));
        setMonthly((current) => ({ ...current, firstDate: current.firstDate || today() }));
    }, [selectedProperty]);

    useEffect(() => {
        if (ownerOpen) {
            setOwnerSearch(ownerSelectedLabel);
        }
    }, [ownerOpen, ownerSelectedLabel]);

    const saleReference = selectedProperty?.reference || 'VTE-2026-0001';

    const plannedTotal = useMemo(() => {
        if (!selectedProperty) return 0;
        if (paymentPlan === 'complete') return Number(completePayment.amount || 0);
        if (paymentPlan === 'monthly') return selectedProperty.price;
        if (paymentPlan === 'tranches') {
            return tranches.reduce((sum, line) => sum + Number(line.amount || 0), 0);
        }
        return customPayments.reduce((sum, line) => sum + Number(line.amount || 0), 0);
    }, [completePayment.amount, customPayments, paymentPlan, selectedProperty, tranches]);

    const remaining = Math.max((selectedProperty?.price || 0) - plannedTotal, 0);
    const commission = selectedProperty?.commission || 0;
    const ownerAmount = selectedProperty?.ownerAmount || 0;
    const monthlyAmount = Math.max(
        Math.ceil(Math.max((selectedProperty?.price || 0) - Number(monthly.deposit || 0), 0) / Math.max(Number(monthly.count || 1), 1)),
        0
    );

    const filteredProperties = saleProperties;

    const resetPaymentPlan = (type) => {
        setPaymentPlan(type);
        setFlash(null);
    };

    const addScheduleLine = (setter, prefix) => {
        setter((current) => [...current, { id: makeLineId(prefix), label: `${prefix === 'tranche' ? 'Tranche' : 'Paiement'} ${current.length + 1}`, amount: '', date: '', mode: 'Espèces' }]);
    };

    const removeScheduleLine = (setter, id) => {
        setter((current) => current.filter((line) => line.id !== id));
    };

    const saveSale = async () => {
        setSubmitting(true);
        setFlash(null);

        try {
            await new Promise((resolve) => setTimeout(resolve, 500));
            setFlash({ type: 'success', message: 'Accord de vente enregistré en statique.' });
            setModalOpen(false);
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <AgenceLayout title="Vente de biens">
            <Head title="Vente de biens" />

            <div className="mx-auto flex max-w-6xl flex-col gap-6 pb-10">
                <div className="flex items-center gap-3">
                    <Button asChild variant="outline" size="icon" className="rounded-xl border-[#c8d4de]">
                        <Link href="/agence/caisse">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>

                    <div className="min-w-0">
                        <div className="flex flex-wrap items-center gap-2">
                            <h2 className="text-xl font-semibold text-[#0f172a]">Vente de biens</h2>
                          
                        </div>
                        
                    </div>
                </div>

                <div className="flex flex-wrap justify-center gap-2">
                    {shortcuts.map((shortcut) => {
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

                {flash ? (
                    <div
                        className={cn(
                            'rounded-xl px-4 py-3 text-sm',
                            flash.type === 'success'
                                ? 'border border-[#c8e6c9] bg-[#eef8df] text-[#245b00]'
                                : flash.type === 'error'
                                    ? 'border border-[#f4c7c3] bg-[#fdecec] text-[#b42318]'
                                    : 'border border-[#dbe7ee] bg-[#f8fafc] text-[#5f7182]'
                        )}
                    >
                        {flash.message}
                    </div>
                ) : null}

                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardContent className="mt-6 p-6">
                        <ComboboxField
                            label="Propriétaire"
                            value={ownerValue}
                            placeholder="Rechercher un propriétaire en vente..."
                            searchValue={ownerSearch}
                            open={ownerOpen}
                            onOpenChange={setOwnerOpen}
                            onSearchChange={setOwnerSearch}
                            options={searchableOwnerOptions}
                            emptyLabel="Aucun propriétaire disponible à la vente"
                            onSelect={(value) => setOwnerValue(value)}
                        />
                    </CardContent>
                </Card>

                {ownerValue ? (
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-[3fr_1fr]">
                        <div className="rounded-2xl border border-[#c8d4de] bg-white shadow-sm">
                            <div className="flex items-center justify-between border-b border-[#e2e8f0] px-6 py-4">
                                <div>
                                    <h3 className="text-base font-semibold text-[#0f172a]">{selectedOwner?.name ?? 'Propriétaire'}</h3>
                                    <p className="text-sm text-[#5f7182]">{selectedOwner?.phone ?? ''}</p>
                                </div>
                                <span className="rounded-full bg-[#eaf4fb] px-3 py-1 text-xs font-semibold text-[#00559b]">
                                    {filteredProperties.length} bien(s)
                                </span>
                            </div>

                            <div className="grid gap-6 p-6 xl:grid-cols-[1fr_1.4fr]">
                                <div className="space-y-3">
                                    {filteredProperties.map((property, index) => (
                                        <PropertyCard
                                            key={property.id}
                                            property={property}
                                            active={selectedProperty?.id === property.id}
                                            onClick={() => setSelectedIndex(filteredProperties.findIndex((item) => item.id === property.id))}
                                        />
                                    ))}
                                </div>

                                <div className="rounded-2xl border border-[#dbe7ee] bg-[#f8fafc] p-5">
                                    <div className="flex items-start justify-between gap-4">
                                        <div className="min-w-0">
                                            <h3 className="text-lg font-semibold text-[#0f172a]">{selectedProperty.title}</h3>
                                            <p className="mt-1 flex items-center gap-2 text-sm text-[#5f7182]">
                                                <MapPin className="h-4 w-4" />
                                                {selectedProperty.location}
                                            </p>
                                        </div>
                                        <Badge variant={selectedProperty.badge === 'Réservé' ? 'warning' : 'success'} className="rounded-full px-3 py-1 text-[11px]">
                                            {selectedProperty.badge}
                                        </Badge>
                                    </div>

                                    <div className="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <InfoTile label="Type de bien" value={selectedProperty.type} icon={Layers3} accent="text-[#00559b]" />
                                        <InfoTile label="Prix de vente" value={currency(selectedProperty.price)} icon={Banknote} accent="text-[#4d8500]" />
                                        <InfoTile label="Commission agence" value={currency(selectedProperty.commission)} icon={Smartphone} accent="text-[#c2410c]" />
                                        <InfoTile label="Montant propriétaire" value={currency(selectedProperty.ownerAmount)} icon={UserRound} accent="text-[#0f172a]" />
                                        <InfoTile label="Client acheteur" value={selectedProperty.buyer} icon={UserRound} accent="text-[#0f172a]" />
                                        <InfoTile label="Statut vente" value={selectedProperty.status} icon={CalendarDays} accent="text-[#0f172a]" />
                                    </div>

                                    <div className="mt-5 rounded-2xl border border-[#e2e8f0] bg-white p-4">
                                        <h4 className="text-sm font-semibold text-[#0f172a]">Détails de la vente</h4>

                                        <div className="mt-3 space-y-3 text-sm">
                                            <div className="flex items-center justify-between gap-3">
                                                <span className="text-[#5f7182]">Référence vente</span>
                                                <strong className="text-[#0f172a]">{saleReference}</strong>
                                            </div>
                                            <div className="flex items-center justify-between gap-3">
                                                <span className="text-[#5f7182]">Date prévue</span>
                                                <strong className="text-[#0f172a]">{selectedProperty.date}</strong>
                                            </div>
                                            <div className="rounded-xl bg-[#f8fafc] p-3 text-[#0f172a]">
                                                {selectedProperty.observation}
                                            </div>
                                        </div>
                                    </div>

                                    <div className="mt-5 flex justify-end">
                                        <Button type="button" className={agenceButtonStyles.primary} onClick={() => setModalOpen(true)}>
                                            Créer l’accord de vente
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="lg:sticky lg:top-4 self-start rounded-2xl border border-[#c8d4de] bg-white p-5 shadow-sm">
                            <div className="flex items-center justify-between gap-3 border-b border-[#e2e8f0] pb-4">
                                <div>
                                    <h3 className="text-base font-semibold text-[#0f172a]">Résumé de la vente</h3>
                                    <p className="text-sm text-[#5f7182]">Montants calculés à partir du bien sélectionné</p>
                                </div>
                            </div>

                            <div className="mt-4 grid grid-cols-1 gap-3">
                                <InfoTile label="Prix du bien" value={currency(selectedProperty.price)} icon={Banknote} accent="text-[#4d8500]" />
                                <InfoTile label="Commission" value={currency(commission)} icon={Smartphone} accent="text-[#c2410c]" />
                                <InfoTile label="Montant propriétaire" value={currency(ownerAmount)} icon={UserRound} accent="text-[#0f172a]" />
                                <InfoTile label="Total prévu" value={currency(plannedTotal)} icon={Check} accent="text-[#00559b]" />
                                <InfoTile label="Reste à couvrir" value={currency(remaining)} icon={CalendarDays} accent="text-[#b42318]" />
                            </div>
                        </div>
                    </div>
                ) : (
                    <Card className="rounded-2xl border-dashed border-[#c8d4de] bg-white shadow-sm">
                        <CardContent className="px-6 py-10 text-center text-sm text-[#5f7182]">
                            Commencez par rechercher un propriétaire. Le formulaire s’affichera dès qu’il sera trouvé.
                        </CardContent>
                    </Card>
                )}
            </div>

            {modalOpen ? (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <button type="button" aria-label="Fermer" className="absolute inset-0 bg-slate-950/40" onClick={() => setModalOpen(false)} />
                    <div className="relative z-10 w-full max-w-5xl rounded-2xl border border-[#c8d4de] bg-white shadow-2xl">
                        <div className="flex items-center justify-between border-b border-[#c8d4de] px-5 py-4">
                            <div>
                                <h3 className="text-lg font-semibold text-[#0f172a]">Accord de vente du bien</h3>
                                <p className="text-sm text-[#5f7182]">{selectedProperty.title} - {selectedProperty.location}</p>
                            </div>
                            <Button variant="outline" size="icon" onClick={() => setModalOpen(false)}>
                                <X className="h-4 w-4" />
                            </Button>
                        </div>

                        <div className="max-h-[80vh] overflow-y-auto p-5">
                            <div className="space-y-6">
                                <section className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-5">
                                    <div className="mb-4">
                                        <h4 className="text-sm font-semibold text-[#0f172a]">Informations de la vente</h4>
                                        <p className="text-sm text-[#5f7182]">Bien, montant et référence de l’accord.</p>
                                    </div>

                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <label className="space-y-1.5">
                                            <span className="block text-sm font-medium text-[#0f172a]">Référence vente</span>
                                            <Input value={saleReference} disabled className="h-11 rounded-xl border-[#c8d4de]" />
                                        </label>

                                        <label className="space-y-1.5">
                                            <span className="block text-sm font-medium text-[#0f172a]">Bien concerné</span>
                                            <Input value={`${selectedProperty.title} - ${selectedProperty.location}`} disabled className="h-11 rounded-xl border-[#c8d4de]" />
                                        </label>

                                        <label className="space-y-1.5">
                                            <span className="block text-sm font-medium text-[#0f172a]">Prix de vente</span>
                                            <Input value={selectedProperty.price} disabled className="h-11 rounded-xl border-[#c8d4de]" />
                                        </label>

                                        <label className="space-y-1.5">
                                            <span className="block text-sm font-medium text-[#0f172a]">Date de l’accord</span>
                                            <Input type="date" value={saleDate} onChange={(e) => setSaleDate(e.target.value)} className="h-11 rounded-xl border-[#c8d4de]" />
                                        </label>
                                    </div>
                                </section>

                                <section className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-5">
                                    <div className="mb-4">
                                        <h4 className="text-sm font-semibold text-[#0f172a]">Informations de l’acheteur</h4>
                                        <p className="text-sm text-[#5f7182]">Identité complète du client acheteur.</p>
                                    </div>

                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <label className="space-y-1.5">
                                            <span className="block text-sm font-medium text-[#0f172a]">Nom complet *</span>
                                            <Input value={buyerForm.name} onChange={(e) => setBuyerForm((current) => ({ ...current, name: e.target.value }))} placeholder="Nom complet de l’acheteur" className="h-11 rounded-xl border-[#c8d4de]" />
                                        </label>

                                        <label className="space-y-1.5">
                                            <span className="block text-sm font-medium text-[#0f172a]">Téléphone *</span>
                                            <Input value={buyerForm.phone} onChange={(e) => setBuyerForm((current) => ({ ...current, phone: e.target.value }))} placeholder="Numéro de téléphone" className="h-11 rounded-xl border-[#c8d4de]" />
                                        </label>

                                        <label className="space-y-1.5">
                                            <span className="block text-sm font-medium text-[#0f172a]">Email</span>
                                            <Input value={buyerForm.email} onChange={(e) => setBuyerForm((current) => ({ ...current, email: e.target.value }))} placeholder="Adresse email" className="h-11 rounded-xl border-[#c8d4de]" />
                                        </label>

                                        <label className="space-y-1.5">
                                            <span className="block text-sm font-medium text-[#0f172a]">Adresse</span>
                                            <Input value={buyerForm.address} onChange={(e) => setBuyerForm((current) => ({ ...current, address: e.target.value }))} placeholder="Adresse de résidence" className="h-11 rounded-xl border-[#c8d4de]" />
                                        </label>

                                        <label className="space-y-1.5">
                                            <span className="block text-sm font-medium text-[#0f172a]">Type de pièce</span>
                                            <select
                                                value={buyerForm.idType}
                                                onChange={(e) => setBuyerForm((current) => ({ ...current, idType: e.target.value }))}
                                                className="h-11 w-full rounded-xl border border-[#c8d4de] bg-white px-3 text-sm text-[#0f172a] outline-none focus:border-[#00559b]"
                                            >
                                                <option>CNI</option>
                                                <option>Passeport</option>
                                                <option>Permis de conduire</option>
                                                <option>Carte consulaire</option>
                                                <option>Autre</option>
                                            </select>
                                        </label>

                                        <label className="space-y-1.5">
                                            <span className="block text-sm font-medium text-[#0f172a]">Numéro de pièce</span>
                                            <Input value={buyerForm.idNumber} onChange={(e) => setBuyerForm((current) => ({ ...current, idNumber: e.target.value }))} placeholder="Numéro de la pièce" className="h-11 rounded-xl border-[#c8d4de]" />
                                        </label>
                                    </div>
                                </section>

                                <section className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-5">
                                    <div className="mb-4">
                                        <h4 className="text-sm font-semibold text-[#0f172a]">Mode de paiement convenu</h4>
                                        <p className="text-sm text-[#5f7182]">Choisissez la méthode selon l’entente entre les parties.</p>
                                    </div>

                                    <div className="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                                        {[
                                            { key: 'complete', title: 'Paiement complet', text: 'Le client paie tout en une seule fois.' },
                                            { key: 'tranches', title: 'Paiement par tranches', text: 'Plusieurs montants avec des dates précises.' },
                                            { key: 'monthly', title: 'Paiement mensuel', text: 'Acompte puis mensualités automatiques.' },
                                            { key: 'custom', title: 'Plan personnalisé', text: 'Accord libre selon la négociation.' },
                                        ].map((option) => (
                                            <button
                                                key={option.key}
                                                type="button"
                                                onClick={() => resetPaymentPlan(option.key)}
                                                className={cn(
                                                    'rounded-2xl border p-4 text-left transition',
                                                    paymentPlan === option.key
                                                        ? 'border-[#00559b] bg-[#eaf4fb]'
                                                        : 'border-[#c8d4de] bg-white hover:border-[#00559b]'
                                                )}
                                            >
                                                <strong className="block text-sm text-[#0f172a]">{option.title}</strong>
                                                <span className="mt-2 block text-xs text-[#5f7182]">{option.text}</span>
                                            </button>
                                        ))}
                                    </div>
                                </section>

                                {paymentPlan === 'complete' ? (
                                    <section className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-5">
                                        <div className="mb-4">
                                            <h4 className="text-sm font-semibold text-[#0f172a]">Paiement complet</h4>
                                        </div>

                                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                            <label className="space-y-1.5">
                                                <span className="block text-sm font-medium text-[#0f172a]">Montant encaissé</span>
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={completePayment.amount}
                                                    onChange={(e) => setCompletePayment((current) => ({ ...current, amount: e.target.value }))}
                                                    className="h-11 rounded-xl border-[#c8d4de]"
                                                />
                                            </label>

                                            <label className="space-y-1.5">
                                                <span className="block text-sm font-medium text-[#0f172a]">Mode de paiement</span>
                                                <select
                                                    value={completePayment.mode}
                                                    onChange={(e) => setCompletePayment((current) => ({ ...current, mode: e.target.value }))}
                                                    className="h-11 w-full rounded-xl border border-[#c8d4de] bg-white px-3 text-sm text-[#0f172a] outline-none focus:border-[#00559b]"
                                                >
                                                    {paymentModes.map((mode) => (
                                                        <option key={mode}>{mode}</option>
                                                    ))}
                                                </select>
                                            </label>
                                        </div>
                                    </section>
                                ) : null}

                                {paymentPlan === 'tranches' ? (
                                    <section className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-5">
                                        <div className="mb-4 flex items-center justify-between gap-3">
                                            <div>
                                                <h4 className="text-sm font-semibold text-[#0f172a]">Paiement par tranches</h4>
                                                <p className="text-sm text-[#5f7182]">Ajoutez les tranches selon l’accord.</p>
                                            </div>
                                            <Button type="button" variant="outline" className="rounded-xl border-[#c8d4de]" onClick={() => addScheduleLine(setTranches, 'tranche')}>
                                                <Plus className="h-4 w-4" />
                                                Ajouter une tranche
                                            </Button>
                                        </div>

                                        <div className="space-y-3">
                                            {tranches.map((line) => (
                                                <ScheduleLine
                                                    key={line.id}
                                                    line={line}
                                                    onChange={(updated) => setTranches((current) => current.map((item) => (item.id === line.id ? updated : item)))}
                                                    onRemove={() => removeScheduleLine(setTranches, line.id)}
                                                />
                                            ))}
                                        </div>
                                    </section>
                                ) : null}

                                {paymentPlan === 'monthly' ? (
                                    <section className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-5">
                                        <div className="mb-4">
                                            <h4 className="text-sm font-semibold text-[#0f172a]">Paiement mensuel</h4>
                                        </div>

                                        <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                            <label className="space-y-1.5">
                                                <span className="block text-sm font-medium text-[#0f172a]">Acompte</span>
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={monthly.deposit}
                                                    onChange={(e) => setMonthly((current) => ({ ...current, deposit: e.target.value }))}
                                                    className="h-11 rounded-xl border-[#c8d4de]"
                                                />
                                            </label>

                                            <label className="space-y-1.5">
                                                <span className="block text-sm font-medium text-[#0f172a]">Nombre de mensualités</span>
                                                <Input
                                                    type="number"
                                                    min="1"
                                                    value={monthly.count}
                                                    onChange={(e) => setMonthly((current) => ({ ...current, count: e.target.value }))}
                                                    className="h-11 rounded-xl border-[#c8d4de]"
                                                />
                                            </label>

                                            <label className="space-y-1.5">
                                                <span className="block text-sm font-medium text-[#0f172a]">Date première mensualité</span>
                                                <Input
                                                    type="date"
                                                    value={monthly.firstDate}
                                                    onChange={(e) => setMonthly((current) => ({ ...current, firstDate: e.target.value }))}
                                                    className="h-11 rounded-xl border-[#c8d4de]"
                                                />
                                            </label>
                                        </div>

                                        <div className="mt-4 flex items-center justify-between rounded-2xl border border-dashed border-[#c8d4de] bg-white px-4 py-3">
                                            <span className="text-sm text-[#5f7182]">Mensualité estimée</span>
                                            <strong className="text-lg text-[#4d8500]">{currency(monthlyAmount)}</strong>
                                        </div>
                                    </section>
                                ) : null}

                                {paymentPlan === 'custom' ? (
                                    <section className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-5">
                                        <div className="mb-4 flex items-center justify-between gap-3">
                                            <div>
                                                <h4 className="text-sm font-semibold text-[#0f172a]">Plan personnalisé</h4>
                                                <p className="text-sm text-[#5f7182]">Accord libre selon la négociation.</p>
                                            </div>
                                            <Button type="button" variant="outline" className="rounded-xl border-[#c8d4de]" onClick={() => addScheduleLine(setCustomPayments, 'custom')}>
                                                <Plus className="h-4 w-4" />
                                                Ajouter un paiement
                                            </Button>
                                        </div>

                                        <div className="space-y-3">
                                            {customPayments.map((line) => (
                                                <ScheduleLine
                                                    key={line.id}
                                                    line={line}
                                                    onChange={(updated) => setCustomPayments((current) => current.map((item) => (item.id === line.id ? updated : item)))}
                                                    onRemove={() => removeScheduleLine(setCustomPayments, line.id)}
                                                />
                                            ))}
                                        </div>
                                    </section>
                                ) : null}

                                <section className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-5">
                                    <div className="mb-4">
                                        <h4 className="text-sm font-semibold text-[#0f172a]">Résumé de la vente</h4>
                                    </div>

                                    <div className="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                                        <InfoTile label="Prix de vente" value={currency(selectedProperty.price)} icon={Banknote} accent="text-[#4d8500]" />
                                        <InfoTile label="Total planifié" value={currency(plannedTotal)} icon={Check} accent="text-[#00559b]" />
                                        <InfoTile label="Reste à régler" value={currency(remaining)} icon={CalendarDays} accent="text-[#b42318]" />
                                        <InfoTile label="Commission" value={currency(commission)} icon={Smartphone} accent="text-[#c2410c]" />
                                    </div>
                                </section>
                            </div>
                        </div>

                        <div className="flex items-center justify-between gap-3 border-t border-[#c8d4de] px-5 py-4">
                            <Button type="button" variant="outline" className="rounded-xl border-[#c8d4de]" onClick={() => setModalOpen(false)}>
                                Annuler
                            </Button>
                            <Button type="button" className={agenceButtonStyles.primary} disabled={submitting} onClick={saveSale}>
                                {submitting ? (
                                    <>
                                        <Loader2 className="h-4 w-4 animate-spin" />
                                        Enregistrement...
                                    </>
                                ) : (
                                    <>
                                        <Check className="h-4 w-4" />
                                        Valider l’accord
                                    </>
                                )}
                            </Button>
                        </div>
                    </div>
                </div>
            ) : null}
        </AgenceLayout>
    );
}
