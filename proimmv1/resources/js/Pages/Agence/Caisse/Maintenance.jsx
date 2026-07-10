import { useEffect, useMemo, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Banknote,
    CalendarDays,
    Check,
    ChevronDown,
    Home,
    Loader2,
    Plus,
    Search,
    ShieldCheck,
    Smartphone,
    UserRound,
    Wrench,
    ShoppingBag,
    X,
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

const today = () => new Date().toISOString().slice(0, 10);


const shortcuts = [
    { title: 'Loyers', href: '/agence/caisse/loyer', icon: Home, active: false },
    { title: 'Maintenance', href: '/agence/caisse/maintenance', icon: Wrench, active: true },
    { title: 'Dépenses agence', href: '/agence/caisse/depense-agence', icon: ShoppingBag, active: false },
    { title: 'Vente de biens', href: '/agence/caisse/vente-bien', icon: Banknote, active: false },
];

const statusLabel = (status) => {
    const value = String(status ?? '').toLowerCase();
    if (value.includes('cours')) return { label: 'En cours', variant: 'warning' };
    if (value.includes('term')) return { label: 'Terminée', variant: 'success' };
    if (value.includes('annul')) return { label: 'Annulée', variant: 'danger' };
    return { label: 'En attente', variant: 'info' };
};

const priseEnChargeLabel = (value) => {
    const map = {
        proprietaire: 'Propriétaire',
        locataire: 'Locataire',
        agence: 'Agence',
    };

    return map[String(value ?? '').toLowerCase()] ?? 'Non précisée';
};

const formatFcfa = (value) => `${new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Number(value ?? 0))} FCFA`;

const formatDateFr = (value) => {
    if (!value) return '—';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '—';

    return new Intl.DateTimeFormat('fr-FR').format(date);
};

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
                            {options.length ? options.map((option) => {
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
                            }) : (
                                <div className="px-3 py-2 text-sm text-[#5f7182]">{emptyLabel}</div>
                            )}
                        </div>
                    </div>
                ) : null}
            </div>
        </div>
    );
}

function SectionCard({ icon: Icon, title, description, children, action }) {
    return (
        <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardHeader className="flex flex-row items-center justify-between gap-3 border-b border-[#e2e8f0] py-4">
                <div className="flex items-center gap-3">
                    <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eaf4fb] text-[#00559b]">
                        <Icon className="h-5 w-5" />
                    </span>
                    <div>
                        <CardTitle className="text-sm text-[#0f172a]">{title}</CardTitle>
                        {description ? <CardDescription className="text-xs text-[#5f7182]">{description}</CardDescription> : null}
                    </div>
                </div>
                {action}
            </CardHeader>
            <CardContent className="p-6">{children}</CardContent>
        </Card>
    );
}

function EmptyState({ title, desc }) {
    return (
        <div className="flex flex-col items-center justify-center gap-2 rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] px-4 py-10 text-center">
            <Wrench className="h-6 w-6 text-[#94a3b8]" />
            <p className="text-sm font-semibold text-[#0f172a]">{title}</p>
            <p className="max-w-sm text-sm text-[#5f7182]">{desc}</p>
        </div>
    );
}

function BadgeStatus({ status }) {
    const meta = statusLabel(status);
    const variant = meta.variant === 'info' ? 'secondary' : meta.variant;
    return <Badge variant={variant} className="rounded-full px-2.5 py-1 text-[11px] font-medium">{meta.label}</Badge>;
}

function InfoTile({ label, value }) {
    return (
        <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
            <p className="text-xs uppercase tracking-wide text-[#94a3b8]">{label}</p>
            <div className="mt-1 text-sm font-semibold text-[#0f172a]">{value}</div>
        </div>
    );
}

const blankForm = () => ({
    titre: '',
    description_generale: '',
    proprietaire_id: '',
    lot_id: '',
    batiment_id: '',
    porte_id: '',
    prise_en_charge_par: 'proprietaire',
    detail: {
        type_intervention_id: '',
        maintenancier_id: '',
        date_debut: today(),
        date_fin: '',
        priorite: 'normale',
        prix: '',
        description: '',
    },
});

function normalizeItem(item) {
    return {
        ...item,
        maintenance_id: String(item.maintenance_id ?? ''),
        proprietaire_id: item.proprietaire_id ? String(item.proprietaire_id) : '',
        lot_id: item.lot_id ? String(item.lot_id) : '',
        propriete_id: item.propriete_id ? String(item.propriete_id) : '',
        batiment_id: item.batiment_id ? String(item.batiment_id) : '',
        porte_id: item.porte_id ? String(item.porte_id) : '',
        montant_global: Number(item.montant_global ?? 0),
        details: Array.isArray(item.details)
            ? item.details.map((detail) => ({
                ...detail,
                maintenance_detail_id: String(detail.maintenance_detail_id ?? ''),
                maintenancier_id: detail.maintenancier_id ? String(detail.maintenancier_id) : '',
                type_intervention_id: detail.type_intervention_id ? String(detail.type_intervention_id) : '',
                montant: Number(detail.montant ?? 0),
            }))
            : [],
    };
}

export default function Maintenance({
    maintenances = [],
    proprietaires = [],
    lots = [],
    batiments = [],
    portes = [],
    typesIntervention = [],
    maintenanciers = [],
}) {
    const [proprietaireFilter, setProprietaireFilter] = useState('');
    const [lotFilter, setLotFilter] = useState('');
    const [selectedIndex, setSelectedIndex] = useState(0);
    const [paymentModalOpen, setPaymentModalOpen] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [flash, setFlash] = useState(null);
    const [form, setForm] = useState(blankForm());
    const [ownerSearch, setOwnerSearch] = useState('');
    const [ownerSelectOpen, setOwnerSelectOpen] = useState(false);

    const [ownerFilterSearch, setOwnerFilterSearch] = useState('');
    const [ownerFilterOpen, setOwnerFilterOpen] = useState(false);

    const rows = useMemo(() => maintenances.map(normalizeItem), [maintenances]);

    const filteredRows = useMemo(() => {
        return rows.filter((row) => {
            if (proprietaireFilter && String(row.proprietaire_id) !== String(proprietaireFilter)) {
                return false;
            }

            if (lotFilter && String(row.lot_id) !== String(lotFilter)) {
                return false;
            }

            return true;
        });
    }, [lotFilter, proprietaireFilter, rows]);

    const displayedRows = useMemo(() => filteredRows.slice(0, 2), [filteredRows]);

    const selectedMaintenance = useMemo(
        () => displayedRows[selectedIndex] ?? null,
        [displayedRows, selectedIndex]
    );

    const canShowMaintenanceDetails = Boolean(proprietaireFilter && lotFilter);

    const ownerOptions = useMemo(() => proprietaires.map((item) => ({
        value: String(item.proprietaire_id),
        label: item.name ?? item.proprietaire_id,
    })), [proprietaires]);

    const searchableOwnerFilterOptions = useMemo(() => {
    const term = ownerFilterSearch.trim().toLowerCase();

    if (!term) return ownerOptions;

    return ownerOptions.filter((item) =>
        item.label.toLowerCase().includes(term)
    );
}, [ownerOptions, ownerFilterSearch]);

    const ownerSelectedLabel = ownerOptions.find((opt) => opt.value === form.proprietaire_id)?.label ?? '';
    const searchableOwnerOptions = useMemo(() => {
        const term = ownerSearch.trim().toLowerCase();
        if (!term) return ownerOptions;
        return ownerOptions.filter((item) => item.label.toLowerCase().includes(term));
    }, [ownerOptions, ownerSearch]);

    const lotOptions = useMemo(() => lots.filter((item) => !form.proprietaire_id || String(item.proprietaire_id) === String(form.proprietaire_id)).map((item) => ({
        value: String(item.propreietaire_lot_id),
        label: item.name ?? item.propreietaire_lot_id,
        proprietaire_id: String(item.proprietaire_id ?? ''),
    })), [lots, form.proprietaire_id]);

    const filteredLotOptions = useMemo(() => lots
        .filter((item) => !proprietaireFilter || String(item.proprietaire_id) === String(proprietaireFilter))
        .map((item) => ({
            value: String(item.propreietaire_lot_id),
            label: item.name ?? item.propreietaire_lot_id,
        })), [lots, proprietaireFilter]);

    const buildingOptions = useMemo(() => batiments.filter((item) => !form.batiment_id || String(item.batiment_id) === String(form.batiment_id) || !form.proprietaire_id).map((item) => ({
        value: String(item.batiment_id),
        label: item.name ?? item.batiment_id,
        propriete_id: String(item.propriete_id ?? ''),
    })), [batiments, form.batiment_id, form.proprietaire_id]);

    const porteOptions = useMemo(() => portes.filter((item) => !form.batiment_id || String(item.batiment_id) === String(form.batiment_id)).map((item) => ({
        value: String(item.porte_id),
        label: item.numero_porte ?? item.porte_id,
        batiment_id: String(item.batiment_id ?? ''),
    })), [portes, form.batiment_id]);

    useEffect(() => {
        if (displayedRows.length === 0) {
            setSelectedIndex(0);
            return;
        }

        if (selectedIndex >= displayedRows.length) {
            setSelectedIndex(0);
        }
    }, [displayedRows, selectedIndex]);

    useEffect(() => {
        setSelectedIndex(0);
    }, [lotFilter, proprietaireFilter]);

    useEffect(() => {
        if (paymentModalOpen) {
            setOwnerSearch(ownerSelectedLabel);
        }
    }, [ownerSelectedLabel, paymentModalOpen]);

    const stats = [
        { label: 'Interventions', value: displayedRows.length ? `${displayedRows.length}` : '0', icon: Wrench, accent: 'bg-[#eaf4fb] text-[#00559b]' },
        { label: 'Montant total', value: formatFcfa(displayedRows.reduce((sum, item) => sum + Number(item.montant_global ?? 0), 0)), icon: Banknote, accent: 'bg-[#eef8df] text-[#4d8500]' },
        { label: 'Sélection', value: selectedMaintenance ? selectedMaintenance.titre ?? '—' : '—', icon: ShieldCheck, accent: 'bg-[#fff2e6] text-[#c2410c]' },
        { label: 'Propriétaire', value: ownerOptions.find((opt) => opt.value === proprietaireFilter)?.label ?? 'Tous', icon: UserRound, accent: 'bg-[#f1f5f9] text-[#5f7182]' },
    ];

    const openModal = () => {
        const source = selectedMaintenance;

        setForm({
            titre: source?.titre ?? '',
            description_generale: source?.description ?? '',
            proprietaire_id: source?.proprietaire_id ?? proprietaireFilter ?? '',
            lot_id: source?.lot_id ?? lotFilter ?? '',
            batiment_id: source?.batiment_id ?? '',
            porte_id: source?.porte_id ?? '',
            prise_en_charge_par: source?.prise_en_charge_par ?? 'proprietaire',
            detail: {
                type_intervention_id: '',
                maintenancier_id: '',
                date_debut: today(),
                date_fin: '',
                priorite: 'normale',
                prix: Number(source?.montant_global ?? 0) || '',
                description: source?.description ?? '',
            },
        });

        setPaymentModalOpen(true);
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setSubmitting(true);
        setFlash(null);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

            const response = await fetch('/agence/maintenance', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    titre: form.titre,
                    description_generale: form.description_generale,
                    proprietaire_id: form.proprietaire_id,
                    lot_id: form.lot_id || null,
                    batiment_id: form.batiment_id || null,
                    porte_id: form.porte_id || null,
                    prise_en_charge_par: form.prise_en_charge_par,
                    details: [
                        {
                            type_intervention_id: form.detail.type_intervention_id,
                            maintenancier_id: form.detail.maintenancier_id,
                            date_debut: form.detail.date_debut,
                            date_fin: form.detail.date_fin || null,
                            priorite: form.detail.priorite,
                            prix: Number(form.detail.prix || 0),
                            description: form.detail.description,
                        },
                    ],
                }),
            });

            const payload = await response.json().catch(() => null);

            if (!response.ok || !payload?.success && response.status >= 400) {
                throw new Error(payload?.message ?? 'La maintenance a échoué.');
            }

            setFlash({ type: 'success', message: payload?.message ?? 'Maintenance enregistrée avec succès.' });
            setPaymentModalOpen(false);
        } catch (error) {
            setFlash({ type: 'error', message: error.message || 'Erreur lors de la maintenance.' });
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <AgenceLayout title="Maintenance caisse">
            <Head title="Maintenance caisse" />

            <div className="mx-auto flex max-w-6xl flex-col gap-6 pb-10">
                <div className="flex items-center gap-3">
                    <Button asChild variant="outline" size="icon" className="rounded-xl border-[#c8d4de]">
                        <Link href="/agence/caisse">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>

                    <div>
                        <h2 className="text-xl font-semibold text-[#0f172a]">Maintenance</h2>
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
                                : 'border border-[#f4c7c3] bg-[#fdecec] text-[#b42318]'
                        )}
                    >
                        {flash.message}
                    </div>
                ) : null}

                <SectionCard icon={Home} title="Filtre de maintenance" description="Choisissez un propriétaire puis sa propriété associée.">
                    <div className="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <ComboboxField
    label="Propriétaire"
    value={proprietaireFilter}
    placeholder="Tous les propriétaires"
    searchValue={ownerFilterSearch}
    open={ownerFilterOpen}
    onOpenChange={setOwnerFilterOpen}
    onSearchChange={setOwnerFilterSearch}
    options={searchableOwnerFilterOptions}
    emptyLabel="Aucun propriétaire trouvé"
    onSelect={(value) => {
        setProprietaireFilter(value);
        setLotFilter('');
    }}
/>
                        <Field label="Propriété associée">
                            <Select
                                value={lotFilter || undefined}
                                onValueChange={setLotFilter}
                            >
                                <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                    <SelectValue placeholder="Toutes les propriétés" />
                                </SelectTrigger>
                                <SelectContent>
                                    {filteredLotOptions.map((item) => (
                                        <SelectItem key={item.value} value={item.value}>{item.label}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>
                    </div>

                    <div className="mt-4 flex justify-end">
                        <Button
                            type="button"
                            variant="outline"
                            className="rounded-xl border-[#c8d4de] text-[#0f172a] hover:bg-[#f8fafc]"
                            onClick={() => {
                                setProprietaireFilter('');
                                setLotFilter('');
                            }}
                        >
                            Réinitialiser
                        </Button>
                    </div>

                </SectionCard>

                <div className="grid grid-cols-1 gap-6 xl:grid-cols-[1.05fr_1.25fr]">
                    <div className="flex flex-col gap-6">
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
                            icon={Wrench}
                            title="Maintenances"
                            description={`${displayedRows.length} dossier${displayedRows.length > 1 ? 's' : ''}`}
                        >
                            <div className="space-y-4">
                                {displayedRows.length ? displayedRows.map((item, index) => {
                                    const active = index === selectedIndex;
                                    const owner = ownerOptions.find((opt) => opt.value === item.proprietaire_id)?.label ?? item.proprietaire_id ?? '—';
                                    const propertyName = lots.find((lot) => String(lot.propreietaire_lot_id) === String(item.lot_id))?.name ?? item.lot_id ?? '—';
                                    const status = statusLabel(item.statut);

                                    return (
                                        <button
                                            key={item.maintenance_id}
                                            type="button"
                                            onClick={() => setSelectedIndex(index)}
                                            className={cn(
                                                'w-full rounded-3xl border bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md',
                                                active ? 'border-[#00559b] ring-2 ring-[#00559b]/20' : 'border-[#d7e2ea]'
                                            )}
                                        >
                                            <div className="flex items-start justify-between gap-4">
                                                <div className="min-w-0">
                                                    <p className="text-lg font-semibold text-[#0f172a]">{item.titre || 'Maintenance sans titre'}</p>
                                                    <p className="mt-1 text-sm text-[#5f7182]">{propertyName}</p>
                                                </div>
                                                <Badge variant={status.variant === 'info' ? 'secondary' : status.variant} className="rounded-full px-2.5 py-1 text-[11px] font-medium">
                                                    {status.label}
                                                </Badge>
                                            </div>

                                            <div className="mt-4 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                                                <div className="rounded-2xl bg-[#f8fafc] px-4 py-3">
                                                    <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Propriétaire</span>
                                                    <strong className="text-[#0f172a]">{owner}</strong>
                                                </div>
                                                <div className="rounded-2xl bg-[#f8fafc] px-4 py-3">
                                                    <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Montant global</span>
                                                    <strong className="text-[#0f172a]">{currency(item.montant_global)}</strong>
                                                </div>
                                                <div className="rounded-2xl bg-[#f8fafc] px-4 py-3">
                                                    <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Prise en charge</span>
                                                    <strong className="text-[#0f172a]">{priseEnChargeLabel(item.prise_en_charge_par)}</strong>
                                                </div>
                                                <div className="rounded-2xl bg-[#f8fafc] px-4 py-3">
                                                    <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Créée le</span>
                                                    <strong className="text-[#0f172a]">{formatDateFr(item.created_at)}</strong>
                                                </div>
                                            </div>

                                            {item.description ? (
                                                <div className="mt-4 rounded-2xl border border-[#e2e8f0] bg-[#fbfdff] p-4">
                                                    <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Description</p>
                                                    <p className="mt-2 text-sm text-[#0f172a]">{item.description}</p>
                                                </div>
                                            ) : null}
                                        </button>
                                    );
                                }) : (
                                    <EmptyState title="Aucune intervention" desc="Aucune maintenance n’est encore enregistrée pour cette agence." />
                                )}
                            </div>
                        </SectionCard>
                    </div>

                    <div className="flex flex-col gap-6">
                        <SectionCard
                            icon={Banknote}
                            title="Détails de la maintenance"
                            description="La maintenance sélectionnée s’affiche ici."
                            action={(
                                <Button type="button" onClick={openModal} className={agenceButtonStyles.primary} disabled={!selectedMaintenance}>
                                    <Plus className="h-4 w-4" />
                                    Nouvelle maintenance
                                </Button>
                            )}
                        >
                            {selectedMaintenance ? (
                                <div className="space-y-4">
                                    <div className="rounded-3xl border border-[#dbe7ee] bg-[#f8fafc] p-5">
                                        <p className="text-sm uppercase tracking-wide text-[#94a3b8]">Titre</p>
                                        <h3 className="mt-1 text-xl font-semibold text-[#0f172a]">{selectedMaintenance.titre || 'Maintenance sans titre'}</h3>
                                        <p className="mt-2 text-sm text-[#5f7182]">
                                            {lots.find((lot) => String(lot.propreietaire_lot_id) === String(selectedMaintenance.lot_id))?.name ?? selectedMaintenance.lot_id ?? '—'}
                                        </p>
                                    </div>

                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <InfoTile label="Propriétaire" value={ownerOptions.find((opt) => opt.value === selectedMaintenance.proprietaire_id)?.label ?? selectedMaintenance.proprietaire_id ?? '—'} />
                                        <InfoTile label="Prise en charge" value={priseEnChargeLabel(selectedMaintenance.prise_en_charge_par)} />
                                        <InfoTile label="Bâtiment" value={batiments.find((item) => String(item.batiment_id) === String(selectedMaintenance.batiment_id))?.name ?? selectedMaintenance.batiment_id ?? '—'} />
                                        <InfoTile label="Porte" value={portes.find((item) => String(item.porte_id) === String(selectedMaintenance.porte_id))?.numero_porte ?? selectedMaintenance.porte_id ?? '—'} />
                                        <InfoTile label="Statut" value={<BadgeStatus status={selectedMaintenance.statut} />} />
                                        <InfoTile label="Montant global" value={currency(selectedMaintenance.montant_global)} />
                                    </div>

                                    <div className="rounded-3xl border border-[#e2e8f0] bg-white p-5">
                                        <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Description</p>
                                        <p className="mt-2 text-sm leading-6 text-[#0f172a]">
                                            {selectedMaintenance.description || 'Aucune description renseignée.'}
                                        </p>
                                    </div>
                                </div>
                            ) : (
                                <EmptyState
                                    title="Sélectionnez une maintenance"
                                    desc="Cliquez sur une ligne de la liste pour afficher ses détails."
                                />
                            )}

                            <div className="mt-5 flex justify-end">
                                <Button type="button" onClick={openModal} className={agenceButtonStyles.primary} disabled={!selectedMaintenance}>
                                    <Smartphone className="h-4 w-4" />
                                    Ouvrir le formulaire
                                </Button>
                            </div>
                        </SectionCard>
                    </div>
                </div>

                {false ? (
                    <SectionCard
                        icon={Wrench}
                        title="Maintenances"
                        description={`${Math.min(2, staticMaintenanceCards.length)} dossier${Math.min(2, staticMaintenanceCards.length) > 1 ? 's' : ''}`}
                    >
                        <div className="space-y-4">
                            {staticMaintenanceCards.slice(0, 2).map((card, index) => (
                                <button
                                    key={`${card.title}-${index}`}
                                    type="button"
                                    onClick={() => setSelectedIndex(index)}
                                    className={cn(
                                        'w-full rounded-3xl border border-[#d7e2ea] bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md',
                                        index === selectedIndex ? 'ring-2 ring-[#00559b]/20' : ''
                                    )}
                                >
                                    <div className="flex items-start justify-between gap-4">
                                        <div className="min-w-0 flex-1">
                                            <p className="text-lg font-semibold text-[#0f172a]">{card.title}</p>
                                            <p className="mt-1 text-sm text-[#5f7182]">{card.location}</p>
                                        </div>
                                        <Badge variant="warning" className="rounded-full px-2.5 py-1 text-[11px] font-medium">
                                            {card.paymentStatus}
                                        </Badge>
                                    </div>

                                    <div className="mt-4 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                                        <div className="rounded-2xl bg-[#f8fafc] px-4 py-3">
                                            <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">À payer</span>
                                            <strong className="text-[#0f172a]">{card.amount}</strong>
                                        </div>
                                        <div className="rounded-2xl bg-[#f8fafc] px-4 py-3">
                                            <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Propriétaire</span>
                                            <strong className="text-[#0f172a]">Propriétaire {card.owner}</strong>
                                        </div>
                                        <div className="rounded-2xl bg-[#f8fafc] px-4 py-3">
                                            <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Type maintenance</span>
                                            <strong className="text-[#0f172a]">{card.type}</strong>
                                        </div>
                                        <div className="rounded-2xl bg-[#f8fafc] px-4 py-3">
                                            <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Date intervention</span>
                                            <strong className="text-[#0f172a]">{card.date}</strong>
                                        </div>
                                        <div className="rounded-2xl bg-[#f8fafc] px-4 py-3">
                                            <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Prestataire</span>
                                            <strong className="text-[#0f172a]">{card.prestataire}</strong>
                                        </div>
                                        <div className="rounded-2xl bg-[#f8fafc] px-4 py-3">
                                            <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Statut paiement</span>
                                            <strong className="text-[#0f172a]">{card.paymentStatus}</strong>
                                        </div>
                                    </div>

                                    <div className="mt-4 rounded-2xl border border-[#e2e8f0] bg-[#fbfdff] p-4">
                                        <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Description</p>
                                        <p className="mt-2 text-sm text-[#0f172a]">{card.description}</p>
                                    </div>

                                    <div className="mt-4 rounded-2xl border border-[#dbe7ee] bg-[#f8fafc] p-4">
                                        <p className="text-sm font-semibold text-[#0f172a]">Détails du paiement</p>
                                        <div className="mt-3 space-y-2">
                                            <div className="flex items-center justify-between rounded-xl bg-white px-3 py-2 text-sm font-semibold text-[#0f172a]">
                                                <span>{card.amount}</span>
                                                <span>Total</span>
                                            </div>
                                            {card.paymentLines.map((detail, detailIndex) => (
                                                <div key={`${card.title}-${detailIndex}`} className="flex items-start justify-between gap-4 rounded-xl bg-white px-3 py-2 text-sm">
                                                    <span className="min-w-0 truncate text-[#0f172a]">{detail.label}</span>
                                                    <strong className="shrink-0 text-[#0f172a]">{detail.amount}</strong>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </button>
                            ))}
                        </div>
                    </SectionCard>
                ) : null}

                {false && canShowMaintenanceDetails && (
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
                                icon={Wrench}
                                title="Maintenances"
                                description={`${displayedRows.length} dossier${displayedRows.length > 1 ? 's' : ''}`}
                            >
                                <div className="space-y-4">
                                    {displayedRows.length ? displayedRows.map((item, index) => {
                                        const active = index === selectedIndex;
                                        const owner = item?.proprietaire?.name ?? ownerOptions.find((opt) => opt.value === item.proprietaire_id)?.label ?? item.proprietaire_id ?? '—';
                                        const paymentStatus = paymentStatusMeta(item.statut);
                                        const paymentLines = getPaymentLines(item);
                                        const totalLabel = formatFcfa(item.montant_global);

                                        return (
                                            <button
                                                key={item.maintenance_id}
                                                type="button"
                                                onClick={() => setSelectedIndex(index)}
                                                className={cn(
                                                    'w-full rounded-3xl border border-[#d7e2ea] bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md',
                                                    active ? 'ring-2 ring-[#00559b]/20' : ''
                                                )}
                                            >
                                                <div className="flex items-start justify-between gap-4">
                                                    <div className="min-w-0 flex-1">
                                                        <p className="text-lg font-semibold text-[#0f172a]">{item.titre || 'Maintenance sans titre'}</p>
                                                        <p className="mt-1 text-sm text-[#5f7182]">{getLocationLabel(item)}</p>
                                                    </div>
                                                    <Badge variant={paymentStatus.variant} className="rounded-full px-2.5 py-1 text-[11px] font-medium">
                                                        {paymentStatus.label}
                                                    </Badge>
                                                </div>

                                                <div className="mt-4 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                                                    <div className="rounded-2xl bg-[#f8fafc] px-4 py-3">
                                                        <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">À payer</span>
                                                        <strong className="text-[#0f172a]">{totalLabel}</strong>
                                                    </div>
                                                    <div className="rounded-2xl bg-[#f8fafc] px-4 py-3">
                                                        <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Propriétaire</span>
                                                        <strong className="text-[#0f172a]">Propriétaire {owner}</strong>
                                                    </div>
                                                    <div className="rounded-2xl bg-[#f8fafc] px-4 py-3">
                                                        <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Type maintenance</span>
                                                        <strong className="text-[#0f172a]">{getTypeLabel(item)}</strong>
                                                    </div>
                                                    <div className="rounded-2xl bg-[#f8fafc] px-4 py-3">
                                                        <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Date intervention</span>
                                                        <strong className="text-[#0f172a]">{getMainDateLabel(item)}</strong>
                                                    </div>
                                                    <div className="rounded-2xl bg-[#f8fafc] px-4 py-3">
                                                        <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Prestataire</span>
                                                        <strong className="text-[#0f172a]">{getPrestataireLabel(item)}</strong>
                                                    </div>
                                                    <div className="rounded-2xl bg-[#f8fafc] px-4 py-3">
                                                        <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">Statut paiement</span>
                                                        <strong className="text-[#0f172a]">{paymentStatus.label}</strong>
                                                    </div>
                                                </div>

                                                <div className="mt-4 rounded-2xl border border-[#e2e8f0] bg-[#fbfdff] p-4">
                                                    <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Description</p>
                                                    <p className="mt-2 text-sm text-[#0f172a]">
                                                        {getDescriptionLabel(item)}
                                                    </p>
                                                </div>

                                                <div className="mt-4 rounded-2xl border border-[#dbe7ee] bg-[#f8fafc] p-4">
                                                    <p className="text-sm font-semibold text-[#0f172a]">Détails du paiement</p>
                                                    <div className="mt-3 space-y-2">
                                                        <div className="flex items-center justify-between rounded-xl bg-white px-3 py-2 text-sm font-semibold text-[#0f172a]">
                                                            <span>{totalLabel}</span>
                                                            <span>Total</span>
                                                        </div>
                                                        {paymentLines.length ? paymentLines.map((detail, detailIndex) => (
                                                            <div key={detail.maintenance_detail_id || `${item.maintenance_id}-${detailIndex}`} className="flex items-start justify-between gap-4 rounded-xl bg-white px-3 py-2 text-sm">
                                                                <span className="min-w-0 truncate text-[#0f172a]">
                                                                    {detail.note || detail.type_intervention?.name || `Ligne ${detailIndex + 1}`}
                                                                </span>
                                                                <strong className="shrink-0 text-[#0f172a]">{formatFcfa(detail.montant)}</strong>
                                                            </div>
                                                        )) : (
                                                            <div className="rounded-xl bg-white px-3 py-2 text-sm text-[#5f7182]">
                                                                Aucun détail de paiement.
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            </button>
                                        );
                                    }) : (
                                        <EmptyState
                                            title="Aucune intervention"
                                            desc="Aucune maintenance n’est encore enregistrée pour cette agence."
                                        />
                                    )}
                                </div>
                            </SectionCard>
                        </div>

                        <div className="flex flex-col gap-6">
                            <SectionCard
                                icon={Banknote}
                                title="Détails de la maintenance"
                                description="Les informations de la maintenance sélectionnée s’affichent ici."
                                action={(
                                    <Button type="button" onClick={openModal} className={agenceButtonStyles.primary} disabled={!selectedMaintenance && filteredRows.length === 0}>
                                        <Plus className="h-4 w-4" />
                                        Nouvelle maintenance
                                    </Button>
                                )}
                            >
                                {selectedMaintenance ? (
                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <InfoTile label="Titre" value={selectedMaintenance.titre || '—'} />
                                        <InfoTile label="Propriétaire" value={selectedMaintenance.proprietaire?.name ?? ownerOptions.find((opt) => opt.value === selectedMaintenance.proprietaire_id)?.label ?? selectedMaintenance.proprietaire_id ?? '—'} />
                                        <InfoTile label="Propriété associée" value={selectedMaintenance.lot?.name ?? selectedMaintenance.lot_id ?? '—'} />
                                        <InfoTile label="Bâtiment" value={selectedMaintenance.batiment?.name ?? selectedMaintenance.batiment_id ?? '—'} />
                                        <InfoTile label="Porte" value={portes.find((item) => String(item.porte_id) === String(selectedMaintenance.porte_id))?.numero_porte ?? selectedMaintenance.porte_id ?? '—'} />
                                        <InfoTile label="Prise en charge" value={priseEnChargeLabel(selectedMaintenance.prise_en_charge_par)} />
                                        <InfoTile label="Statut" value={<BadgeStatus status={selectedMaintenance.statut} />} />
                                        <InfoTile label="Montant global" value={currency(selectedMaintenance.montant_global)} />
                                    </div>
                                ) : (
                                    <EmptyState
                                        title="Sélectionnez une maintenance"
                                        desc="Cliquez sur une ligne de la liste pour afficher ses détails."
                                    />
                                )}

                                <div className="mt-5 flex justify-end">
                                    <Button
                                        type="button"
                                        onClick={openModal}
                                        className={agenceButtonStyles.primary}
                                        disabled={!selectedMaintenance && filteredRows.length === 0}
                                    >
                                        <Smartphone className="h-4 w-4" />
                                        Ouvrir le formulaire
                                    </Button>
                                </div>
                            </SectionCard>
                        </div>
                    </div>
                )}

                <Dialog open={paymentModalOpen} onOpenChange={setPaymentModalOpen}>
                    <DialogContent className="sm:max-w-4xl">
                        <DialogHeader>
                            <DialogTitle className="text-[#0f172a]">Créer une maintenance</DialogTitle>
                            <DialogDescription className="text-[#5f7182]">
                                {selectedMaintenance
                                    ? `Prérempli à partir de ${selectedMaintenance.titre || 'la sélection courante'}`
                                    : 'Renseignez les informations de la maintenance puis validez.'}
                            </DialogDescription>
                        </DialogHeader>

                        <form className="space-y-5 pt-2" onSubmit={handleSubmit}>
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <ComboboxField
                                    label="Propriétaire"
                                    required
                                    value={form.proprietaire_id}
                                    placeholder="Rechercher un propriétaire..."
                                    searchValue={ownerSearch}
                                    open={ownerSelectOpen}
                                    onOpenChange={setOwnerSelectOpen}
                                    onSearchChange={setOwnerSearch}
                                    options={searchableOwnerOptions}
                                    emptyLabel="Aucun propriétaire trouvé"
                                    onSelect={(value) => setForm((current) => ({ ...current, proprietaire_id: value, lot_id: '', batiment_id: '', porte_id: '' }))}
                                />

                                <Field label="Propriété associée">
                                    <Select
                                        value={form.lot_id}
                                        disabled={!form.proprietaire_id}
                                        onValueChange={(value) => setForm((current) => ({ ...current, lot_id: value, batiment_id: '', porte_id: '' }))}
                                    >
                                        <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                            <SelectValue placeholder={form.proprietaire_id ? 'Sélectionner' : 'Choisissez un propriétaire d’abord'} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {lotOptions.map((item) => (
                                                <SelectItem key={item.value} value={item.value}>{item.label}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>

                            </div>

                            {canShowMaintenanceDetails ? (
                                <>
                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <Field label="Titre" required>
                                    <Input
                                        value={form.titre}
                                        onChange={(event) => setForm((current) => ({ ...current, titre: event.target.value }))}
                                        placeholder="Ex. Réparation plomberie"
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                    />
                                </Field>

                                <Field label="Bâtiment">
                                    <Select
                                        value={form.batiment_id}
                                        onValueChange={(value) => setForm((current) => ({ ...current, batiment_id: value, porte_id: '' }))}
                                    >
                                        <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                            <SelectValue placeholder="Sélectionner" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {buildingOptions.map((item) => (
                                                <SelectItem key={item.value} value={item.value}>{item.label}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field label="Porte">
                                    <Select
                                        value={form.porte_id}
                                        onValueChange={(value) => setForm((current) => ({ ...current, porte_id: value }))}
                                    >
                                        <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                            <SelectValue placeholder="Sélectionner" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {porteOptions.map((item) => (
                                                <SelectItem key={item.value} value={item.value}>{item.label}</SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field label="Prise en charge" required>
                                    <Select
                                        value={form.prise_en_charge_par}
                                        onValueChange={(value) => setForm((current) => ({ ...current, prise_en_charge_par: value }))}
                                    >
                                        <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                            <SelectValue placeholder="Sélectionner" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="proprietaire">Propriétaire</SelectItem>
                                            <SelectItem value="locataire">Locataire</SelectItem>
                                            <SelectItem value="agence">Agence</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field label="Type d'intervention" required>
                                    <Select
                                        value={form.detail.type_intervention_id}
                                        onValueChange={(value) => setForm((current) => ({ ...current, detail: { ...current.detail, type_intervention_id: value } }))}
                                    >
                                        <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                            <SelectValue placeholder="Sélectionner" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {typesIntervention.map((item) => (
                                                <SelectItem key={String(item.type_maintenance_id)} value={String(item.type_maintenance_id)}>
                                                    {item.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field label="Maintenancier" required>
                                    <Select
                                        value={form.detail.maintenancier_id}
                                        onValueChange={(value) => setForm((current) => ({ ...current, detail: { ...current.detail, maintenancier_id: value } }))}
                                    >
                                        <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                            <SelectValue placeholder="Sélectionner" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {maintenanciers.map((item) => (
                                                <SelectItem key={String(item.maintenancier_id)} value={String(item.maintenancier_id)}>
                                                    {item.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field label="Date début" required>
                                    <Input
                                        type="date"
                                        value={form.detail.date_debut}
                                        onChange={(event) => setForm((current) => ({ ...current, detail: { ...current.detail, date_debut: event.target.value } }))}
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                    />
                                </Field>

                                <Field label="Date fin">
                                    <Input
                                        type="date"
                                        value={form.detail.date_fin}
                                        onChange={(event) => setForm((current) => ({ ...current, detail: { ...current.detail, date_fin: event.target.value } }))}
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                    />
                                </Field>

                                <Field label="Priorité">
                                    <Select
                                        value={form.detail.priorite}
                                        onValueChange={(value) => setForm((current) => ({ ...current, detail: { ...current.detail, priorite: value } }))}
                                    >
                                        <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                            <SelectValue placeholder="Sélectionner" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="basse">Basse</SelectItem>
                                            <SelectItem value="normale">Normale</SelectItem>
                                            <SelectItem value="haute">Haute</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field label="Prix" required>
                                    <Input
                                        type="number"
                                        min="0"
                                        value={form.detail.prix}
                                        onChange={(event) => setForm((current) => ({ ...current, detail: { ...current.detail, prix: event.target.value } }))}
                                        placeholder="Montant"
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                    />
                                </Field>

                                <Field label="Description" className="md:col-span-2">
                                    <textarea
                                        value={form.description_generale}
                                        onChange={(event) => setForm((current) => ({ ...current, description_generale: event.target.value, detail: { ...current.detail, description: event.target.value } }))}
                                        rows={3}
                                        placeholder="Décris l’intervention..."
                                        className="min-h-[100px] rounded-xl border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] outline-none transition focus:border-[#00559b]"
                                    />
                                </Field>
                            </div>

                            <div className="flex flex-col gap-3 rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div className="flex items-center gap-3 text-sm text-[#5f7182]">
                                    <CalendarDays className="h-4 w-4 text-[#00559b]" />
                                    <span>La maintenance sera enregistrée avec un seul détail de prise en charge.</span>
                                </div>

                                <Button type="submit" className={agenceButtonStyles.primary} disabled={submitting}>
                                    {submitting ? (
                                        <>
                                            <Loader2 className="h-4 w-4 animate-spin" />
                                            Enregistrement...
                                        </>
                                    ) : (
                                        <>
                                            <Smartphone className="h-4 w-4" />
                                            Enregistrer
                                        </>
                                    )}
                                </Button>
                            </div>
                                </>
                            ) : (
                                <div className="rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] px-4 py-5 text-sm text-[#5f7182]">
                                    Sélectionnez un propriétaire et une propriété associée pour afficher les détails de la maintenance.
                                </div>
                            )}
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </AgenceLayout>
    );
}
