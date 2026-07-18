import { useEffect, useMemo, useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import {
    ArrowLeft,
    Building2,
    Check,
    ChevronLeft,
    ChevronDown,
    ChevronRight,
    DoorOpen,
    Home,
    Layers3,
    MapPin,
    Plus,
    Save,
    Search,
    Tag,
    Trash2,
    X,
    Wallet,
    Info
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
import { Separator } from '../../../components/ui/separator';
import { ScrollArea } from '../../../components/ui/scroll-area';
import ProximitePickerCard from './ProximitePicker';
import { agenceButtonStyles } from '../../../lib/buttonStyles';
import { cn } from '../../../lib/utils';
import { ComboboxField as SharedComboboxField } from '../../../components/ui/combobox-field';

// ─────────────────────────────────────────────────────────────
// Factories & helpers
// ─────────────────────────────────────────────────────────────

const emptyTarif = () => ({
    mt_loyer: '',
    mt_vente: '',
    mt_caution: 2,
    mt_avance: 2,
    mt_frais_agence: 1,
    mt_frais_dossier: 0,
    mt_autre_frais: 0,
    mt_caution_cie: 0,
    mt_caution_sodeci: 0,
    date_effet: new Date().toISOString().slice(0, 10),
});

const emptyPorte = () => ({
    porte_id: null,
    numero_porte: '',
    type_porte_id: '',
    superficie_m2: '',
    etage: 0,
    is_allocation: true,
    description: '',
    is_occupe: false,
    is_actif: true,
    equipements: [],
    tarif: emptyTarif(),
});

const emptyBatiment = () => ({
    batiment_id: null,
    name: '',
    description: '',
    nbre_etages: 0,
    portes: [],
});

const buildEmptyLotForm = () => ({
    name: '',
    num_lot: '',
    num_ilot: '',
    superficie: '',
    adresse: '',
    region_id: '',
    ville_id: '',
});

const toId = (value) => (value === null || value === undefined || value === '' ? '' : String(value));

const number = (value) => new Intl.NumberFormat('fr-FR').format(Number(value ?? 0));

const integerValue = (value) => {
    if (value === null || value === undefined || value === '') {
        return '';
    }

    const parsed = Number(value);
    if (!Number.isFinite(parsed)) {
        return '';
    }

    return String(Math.trunc(parsed));
};

const digitsOnly = (value) => String(value ?? '').replace(/\D+/g, '');

const handleIntegerKeyDown = (event) => {
    const blockedKeys = ['e', 'E', '+', '-', '.', ','];
    if (blockedKeys.includes(event.key)) {
        event.preventDefault();
    }
};

const handleIntegerPaste = (event) => {
    const pasted = event.clipboardData?.getData('text') ?? '';
    if (!/^\d+$/.test(pasted)) {
        event.preventDefault();
    }
};

const normalizeTarif = (tarif = {}) => ({
    mt_loyer: integerValue(tarif.mt_loyer),
    mt_vente: integerValue(tarif.mt_vente),
    mt_caution: integerValue(tarif.mt_caution),
    mt_avance: integerValue(tarif.mt_avance),
    mt_frais_agence: integerValue(tarif.mt_frais_agence),
    mt_frais_dossier: integerValue(tarif.mt_frais_dossier),
    mt_autre_frais: integerValue(tarif.mt_autre_frais),
    mt_caution_cie: integerValue(tarif.mt_caution_cie),
    mt_caution_sodeci: integerValue(tarif.mt_caution_sodeci),
    date_effet: tarif.date_effet ?? new Date().toISOString().slice(0, 10),
});

const normalizeProximiteSelection = (value) => {
    if (typeof value === 'string' || typeof value === 'number') {
        const id = toId(value);

        return id
            ? {
                  id,
                  distance: '',
                  unite: 'm',
                  name: '',
                  description: '',
              }
            : null;
    }

    if (!value || typeof value !== 'object') {
        return null;
    }

    const id = toId(value.id ?? value.proximite_id);
    if (!id) {
        return null;
    }

    return {
        id,
        distance: value.distance ?? '',
        unite: value.unite ?? 'm',
        name: value.name ?? '',
        description: value.description ?? '',
    };
};

// ─────────────────────────────────────────────────────────────
// Small UI helpers
// ─────────────────────────────────────────────────────────────

function Field({ label, required, children, error, className, action }) {
    return (
        <div className={cn('space-y-1.5', className)}>
            <div className="flex items-center justify-between gap-3">
                <label className="text-sm font-medium text-[#0f172a]">
                    {label}
                    {required ? <span className="ml-0.5 text-[#b42318]">*</span> : null}
                </label>
                {action ? <div className="shrink-0">{action}</div> : null}
            </div>
            {children}
            {error ? <p className="text-xs text-[#b42318]">{error}</p> : null}
        </div>
    );
}

function ComboboxField({
    label,
    required,
    error,
    value,
    placeholder,
    options,
    open,
    onOpenChange,
    onSearchChange,
    searchValue,
    onSelect,
    emptyLabel,
    className,
}) {
    const selectedLabel = options.find((option) => String(option.value) === String(value))?.label ?? '';

    return (
        <div className={cn('space-y-1.5', className)}>
            <label className="text-sm font-medium text-[#0f172a]">
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
                                            <span className="min-w-0">
                                                <span className="block truncate font-medium">{option.label}</span>
                                                {option.meta ? <span className="block truncate text-xs text-[#5f7182]">{option.meta}</span> : null}
                                            </span>
                                            {active ? <Check className="h-4 w-4 shrink-0" /> : null}
                                        </button>
                                    );
                                })
                            ) : (
                                <p className="px-3 py-4 text-sm text-[#94a3b8]">{emptyLabel}</p>
                            )}
                        </div>
                    </div>
                ) : null}
            </div>
            {error ? <p className="text-xs text-[#b42318]">{error}</p> : null}
        </div>
    );
}

function ToggleChips({ options = [], selected = [], onToggle }) {
    if (!options.length) {
        return <p className="text-sm text-[#94a3b8]">Aucune option disponible.</p>;
    }

    return (
        <div className="flex flex-wrap gap-2">
            {options.map((option) => {
                const value = toId(option.id ?? option.value ?? option);
                const isActive = selected.some((item) => toId(item) === value);

                return (
                    <Button
                        key={value}
                        type="button"
                        variant={isActive ? 'default' : 'outline'}
                        size="sm"
                        className={cn(
                            'rounded-full',
                            isActive
                                ? 'bg-[#00559b] text-white hover:bg-[#004b88]'
                                : 'border-[#c8d4de] bg-white text-[#5f7182] hover:border-[#00559b] hover:text-[#00559b]'
                        )}
                        onClick={() => onToggle(value)}
                    >
                        {option.name ?? option.label ?? String(option)}
                    </Button>
                );
            })}
        </div>
    );
}

function SectionCard({ icon: Icon, title, description, action, children }) {
    return (
        <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardHeader className="flex flex-row items-center justify-between gap-3 border-b border-[#e2e8f0] py-4">
                <div className="flex items-center gap-3">
                    <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eaf4fb] text-[#00559b]">
                        <Icon className="h-5 w-5" />
                    </span>
                    <div>
                        <CardTitle className="text-sm text-[#0f172a]">{title}</CardTitle>
                        {description ? (
                            <CardDescription className="text-xs text-[#5f7182]">{description}</CardDescription>
                        ) : null}
                    </div>
                </div>
                {action}
            </CardHeader>
            <CardContent className="px-6 py-6">{children}</CardContent>
        </Card>
    );
}

function ProximitePicker({ options, selected, expandedId, onToggle, onUpdate, onRemove, onExpand, errors }) {
    if (!options.length) {
        return <p className="text-sm text-[#94a3b8]">Aucun élément disponible.</p>;
    }

    return (
        <div className="flex flex-wrap gap-3">
            {options.map((option) => {
                const value = toId(option.id);
                const selectedItem = selected.find((item) => toId(item.id) === value);
                const isActive = Boolean(selectedItem);
                const isExpanded = expandedId === value;
                const index = selected.findIndex((item) => toId(item.id) === value);
                const distanceError = index >= 0 ? errors?.[`proximites.${index}.distance`] : null;
                const uniteError = index >= 0 ? errors?.[`proximites.${index}.unite`] : null;

                return (
                    <div
                        key={value}
                        className={cn(
                            'w-full self-start overflow-visible border transition-all duration-200 md:w-[calc(50%-0.375rem)] xl:w-[calc(33.333%-0.5rem)]',
                            isActive ? 'rounded-[1.75rem] border-[#00559b] bg-[#f8fafc] shadow-sm' : 'rounded-full border-[#c8d4de] bg-white'
                        )}
                    >
                        <button
                            type="button"
                            onClick={() => {
                                const nextActive = !isActive;
                                onToggle(value);
                                onExpand(nextActive ? value : '');
                            }}
                            className={cn(
                                'flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition-colors',
                                isActive ? 'bg-[#eaf4fb]/70 text-[#00559b]' : 'text-[#5f7182] hover:bg-[#f8fafc] hover:text-[#00559b]'
                            )}
                        >
                            <div className="min-w-0">
                                <p className="text-sm font-semibold">
                                    <span className="mr-1">~</span>
                                    {option.name}
                                </p>
                                {option.description ? <p className="text-xs text-[#5f7182]">{option.description}</p> : null}
                            </div>
                            <ChevronRight className={cn('h-4 w-4 shrink-0 transition-transform', isExpanded && 'rotate-90')} />
                        </button>

                        <div
                            className={cn(
                                'border-t border-[#e2e8f0] px-4 transition-all duration-300 ease-out motion-reduce:transition-none',
                                isActive && isExpanded
                                    ? 'max-h-80 py-4 opacity-100 translate-y-0'
                                    : 'max-h-0 overflow-visible py-0 opacity-0 -translate-y-1 pointer-events-none'
                            )}
                        >
                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_120px_auto] sm:items-end">
                                    <Field label="Distance" required error={distanceError}>
                                        <Input
                                            type="text"
                                            inputMode="numeric"
                                            pattern="[0-9]*"
                                            value={selectedItem?.distance ?? ''}
                                            onKeyDown={handleIntegerKeyDown}
                                            onPaste={handleIntegerPaste}
                                            onChange={(e) => onUpdate(value, { distance: digitsOnly(e.target.value) })}
                                            placeholder="Ex: 300"
                                        />
                                    </Field>

                                    <Field label="Unité" required error={uniteError}>
                                        <Select
                                            value={selectedItem?.unite ?? 'm'}
                                            onValueChange={(nextValue) => onUpdate(value, { unite: nextValue })}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Unité" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="m">m</SelectItem>
                                                <SelectItem value="km">km</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </Field>

                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="icon"
                                        className={agenceButtonStyles.actionRedIcon}
                                        onClick={() => onRemove(value)}
                                        >
                                        <X className="h-4 w-4" />
                                    </Button>
                            </div>
                        </div>
                    </div>
                );
            })}
        </div>
    );
}

// ─────────────────────────────────────────────────────────────
// Porte block
// ─────────────────────────────────────────────────────────────

function PorteBlock({
    index,
    porte,
    typesPorte,
    equipements,
    errorKey,
    errors,
    onChange,
    onRemove,
    canRemove,
    nbreEtages = 0,
}) {
    const [showDetails, setShowDetails] = useState(false);
    const setPorte = (patch) => onChange({ ...porte, ...patch });
    const setTarif = (patch) => onChange({ ...porte, tarif: { ...porte.tarif, ...patch } });
    const totalEtages = Number(nbreEtages ?? 0);
    const etageValue = Number(porte.etage ?? 0);
    const isLocation = porte.is_allocation !== false;
    const prixFieldKey = isLocation ? 'mt_loyer' : 'mt_vente';
    const prixFieldLabel = isLocation ? 'Loyer' : 'Prix de vente';
    const prixFieldPlaceholder = isLocation ? '0' : 'Ex: 35000000';
    const prixFieldError = errors?.[`${errorKey}.tarif.${prixFieldKey}`];
    const visibleComplementFields = isLocation
        ? [
              {
                  key: 'mt_caution',
                  label: 'Caution (mois)',
                  required: true,
                  error: errors?.[`${errorKey}.tarif.mt_caution`],
                  value: porte.tarif.mt_caution,
                  patchKey: 'mt_caution',
                  placeholder: 'Ex: 2',
              },
              {
                  key: 'mt_avance',
                  label: 'Avance (mois)',
                  required: true,
                  error: errors?.[`${errorKey}.tarif.mt_avance`],
                  value: porte.tarif.mt_avance,
                  patchKey: 'mt_avance',
                  placeholder: 'Ex: 2',
              },
              {
                  key: 'mt_frais_agence',
                  label: "Frais d'agence (mois)",
                  required: true,
                  error: errors?.[`${errorKey}.tarif.mt_frais_agence`],
                  value: porte.tarif.mt_frais_agence,
                  patchKey: 'mt_frais_agence',
                  placeholder: 'Ex: 1',
              },
              {
                  key: 'mt_caution_cie',
                  label: 'Caution CIE (montant)',
                  required: false,
                  error: errors?.[`${errorKey}.tarif.mt_caution_cie`],
                  value: porte.tarif.mt_caution_cie,
                  patchKey: 'mt_caution_cie',
                  placeholder: '0',
              },
              {
                  key: 'mt_caution_sodeci',
                  label: 'Caution SODECI (montant)',
                  required: false,
                  error: errors?.[`${errorKey}.tarif.mt_caution_sodeci`],
                  value: porte.tarif.mt_caution_sodeci,
                  patchKey: 'mt_caution_sodeci',
                  placeholder: '0',
              },
              {
                  key: 'mt_autre_frais',
                  label: 'Autres frais (montant)',
                  required: false,
                  error: errors?.[`${errorKey}.tarif.mt_autre_frais`],
                  value: porte.tarif.mt_autre_frais,
                  patchKey: 'mt_autre_frais',
                  placeholder: '0',
              },
              {
                  key: 'mt_frais_dossier',
                  label: 'Frais de dossier (montant)',
                  required: false,
                  error: errors?.[`${errorKey}.tarif.mt_frais_dossier`],
                  value: porte.tarif.mt_frais_dossier,
                  patchKey: 'mt_frais_dossier',
                  placeholder: '0',
              },
          ]
        : [
              {
                  key: 'mt_frais_dossier',
                  label: 'Frais de dossier (montant)',
                  required: false,
                  error: errors?.[`${errorKey}.tarif.mt_frais_dossier`],
                  value: porte.tarif.mt_frais_dossier,
                  patchKey: 'mt_frais_dossier',
                  placeholder: '0',
              },
          ];

    const toggleEquipement = (id) => {
        const exists = porte.equipements.some((item) => toId(item) === id);
        const next = exists
            ? porte.equipements.filter((item) => toId(item) !== id)
            : [...porte.equipements, id];
        setPorte({ equipements: next });
    };

    useEffect(() => {
        if (totalEtages <= 0 && etageValue !== 0) {
            setPorte({ etage: 0 });
            return;
        }

        if (totalEtages > 0 && etageValue > totalEtages) {
            setPorte({ etage: 0 });
        }
    }, [etageValue, setPorte, totalEtages]);

    const etageOptions = useMemo(
        () =>
            totalEtages > 0
                ? Array.from({ length: totalEtages + 1 }, (_, value) => ({
                      value: String(value),
                      label: value === 0 ? '0 - RDC' : String(value),
                  }))
                : [],
        [totalEtages]
    );

    return (
        <div className="rounded-2xl border border-[#c8d4de] bg-[#f8fafc] p-4">
            <div className="flex items-start justify-between gap-3">
                <div className="min-w-0">
                    <div className="flex items-center gap-2 text-sm font-semibold text-[#0f172a]">
                        <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-[#eaf4fb] text-[#00559b]">
                            <DoorOpen className="h-4 w-4" />
                        </span>
                        Porte #{index + 1}
                    </div>
                    <p className="mt-1 text-xs text-[#5f7182]">
                        {porte.numero_porte || 'Numéro à renseigner'} · {porte.type_porte_id ? 'Type choisi' : 'Type à choisir'} ·{' '}
                        {porte.tarif[prixFieldKey] ? `${number(porte.tarif[prixFieldKey])} FCFA` : `${prixFieldLabel} à saisir`}
                    </p>
                </div>
                <div className="flex items-center gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        className={agenceButtonStyles.outline}
                        onClick={() => setShowDetails((value) => !value)}
                    >
                        <ChevronRight className={cn('h-4 w-4 transition-transform', showDetails && 'rotate-90')} />
                        {showDetails ? 'Masquer' : 'Détails'}
                    </Button>
                    {canRemove ? (
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            className={agenceButtonStyles.actionRedIcon}
                            onClick={onRemove}
                        >
                            <Trash2 className="h-4 w-4" />
                        </Button>
                    ) : null}
                </div>
            </div>

            <Separator className="my-4" />

            <div className="mb-3 flex items-center gap-2 text-sm font-semibold text-[#0f172a]">
    <Info className="h-4 w-4 text-[#00559b]" /> Informations de base
</div>

<div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">

    <Field
                            label="Mode de mise en marché"
                            required
                            error={errors?.[`${errorKey}.is_allocation`]}
                            className="md:col-span-3"
                        >
                            <div className="flex flex-wrap gap-3">
                                <label className={cn(
                                    'flex items-center gap-2 rounded-xl border px-3 py-2 text-sm transition',
                                    porte.is_allocation
                                        ? 'border-[#00559b] bg-[#eaf4fb] text-[#00559b]'
                                        : 'border-[#c8d4de] bg-white text-[#0f172a]'
                                )}>
                                    <input
                                        type="radio"
                                        name={`${errorKey}-allocation`}
                                        checked={porte.is_allocation === true}
                                        onChange={() => setPorte({ is_allocation: true })}
                                        className="h-4 w-4 accent-[#00559b]"
                                    />
                                    <span>
                                        <span className="block font-medium">Location</span>
                                        <span className="block text-xs text-[#94a3b8]">Disponible à la location</span>
                                    </span>
                                </label>

                                <label className={cn(
                                    'flex items-center gap-2 rounded-xl border px-3 py-2 text-sm transition',
                                    porte.is_allocation === false
                                        ? 'border-[#00559b] bg-[#eaf4fb] text-[#00559b]'
                                        : 'border-[#c8d4de] bg-white text-[#0f172a]'
                                )}>
                                    <input
                                        type="radio"
                                        name={`${errorKey}-allocation`}
                                        checked={porte.is_allocation === false}
                                        onChange={() => setPorte({ is_allocation: false })}
                                        className="h-4 w-4 accent-[#00559b]"
                                    />
                                    <span>
                                        <span className="block font-medium">Vente</span>
                                        <span className="block text-xs text-[#94a3b8]">Disponible à la vente</span>
                                    </span>
                                </label>
                            </div>
                        </Field>

    <Field label="Numéro de porte" required error={errors?.[`${errorKey}.numero_porte`]}>
        <Input
            value={porte.numero_porte}
            onChange={(e) => setPorte({ numero_porte: e.target.value })}
            placeholder="Ex: A-01"
        />
    </Field>

    <Field label="Type de porte" required error={errors?.[`${errorKey}.type_porte_id`]}>
        <Select value={toId(porte.type_porte_id)} onValueChange={(value) => setPorte({ type_porte_id: value })}>
            <SelectTrigger>
                <SelectValue placeholder="Sélectionner" />
            </SelectTrigger>
            <SelectContent>
                {typesPorte.map((type) => (
                    <SelectItem key={toId(type.id)} value={toId(type.id)}>
                        {type.name}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    </Field>

    <Field label={prixFieldLabel} required error={prixFieldError}>
        <Input
            type="text"
            min="0"
            step="1"
            inputMode="numeric"
            pattern="[0-9]*"
            value={porte.tarif[prixFieldKey]}
            onKeyDown={handleIntegerKeyDown}
            onPaste={handleIntegerPaste}
            onChange={(e) => setTarif({ [prixFieldKey]: digitsOnly(e.target.value) })}
            placeholder={prixFieldPlaceholder}
        />
    </Field>
</div>

<div className="rounded-xl mt-4">
    <div className="mb-3 flex items-center gap-2 text-sm font-semibold text-[#0f172a]">
        <Wallet className="h-4 w-4 text-[#00559b]" /> Frais complémentaires
    </div>

    <div className={cn('grid grid-cols-1 gap-4', isLocation ? 'md:grid-cols-2 lg:grid-cols-3' : 'md:grid-cols-1')}>
        {visibleComplementFields.map((field) => (
            <Field key={field.key} label={field.label} required={field.required} error={field.error}>
                <Input
                    type="text"
                    min="0"
                    step="1"
                    inputMode="numeric"
                    pattern="[0-9]*"
                    value={field.value}
                    onKeyDown={handleIntegerKeyDown}
                    onPaste={handleIntegerPaste}
                    onChange={(e) => setTarif({ [field.patchKey]: digitsOnly(e.target.value) })}
                    placeholder={field.placeholder}
                />
            </Field>
        ))}
    </div>
</div>

            {showDetails ? (
                <div className="mt-4 space-y-5 rounded-xl border border-dashed border-[#c8d4de] bg-white p-4">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <Field label="Superficie (m²)">
                            <Input
                                type="number"
                                min="0"
                                step="1"
                                inputMode="numeric"
                                pattern="[0-9]*"
                                value={porte.superficie_m2}
                                onKeyDown={handleIntegerKeyDown}
                                onPaste={handleIntegerPaste}
                                onChange={(e) => setPorte({ superficie_m2: digitsOnly(e.target.value) })}
                                placeholder="0"
                            />
                        </Field>

                        {totalEtages > 0 ? (
                            <Field label="Étage">
                                <Select value={toId(porte.etage)} onValueChange={(value) => setPorte({ etage: value })}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Sélectionner" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {etageOptions.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>
                        ) : null}

                        <Field label="Description">
                            <Input
                                value={porte.description}
                                onChange={(e) => setPorte({ description: e.target.value })}
                                placeholder="Description facultative"
                            />
                        </Field>

                        
                    </div>

                    <div className="space-y-2">
                        <p className="text-sm font-medium text-[#0f172a]">Équipements</p>
                        <ToggleChips options={equipements} selected={porte.equipements} onToggle={toggleEquipement} />
                    </div>

                    
                </div>
            ) : null}
        </div>
    );
}

// ─────────────────────────────────────────────────────────────
// Batiment block
// ─────────────────────────────────────────────────────────────

function BatimentBlock({ index, batiment, typesPorte, equipements, errors, onChange, onRemove, canRemove, mode = 'full' }) {
    const setBatiment = (patch) => onChange({ ...batiment, ...patch });
    const [showStructureOptions, setShowStructureOptions] = useState(false);

    const updatePorte = (porteIndex, nextPorte) => {
        const portes = batiment.portes.map((porte, i) => (i === porteIndex ? nextPorte : porte));
        setBatiment({ portes });
    };

    const addPorte = () => setBatiment({ portes: [...batiment.portes, emptyPorte()] });

    const removePorte = (porteIndex) =>
        setBatiment({ portes: batiment.portes.filter((_, i) => i !== porteIndex) });

    return (
        <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardHeader className="flex flex-row items-center justify-between gap-3 border-b border-[#e2e8f0] py-4">
                <div className="flex items-center gap-3">
                    <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eef8df] text-[#4d8500]">
                        <Building2 className="h-5 w-5" />
                    </span>
                    <div>
                        <CardTitle className="text-sm text-[#0f172a]">
                            Bâtiment #{index + 1}
                            {batiment.name ? ` · ${batiment.name}` : ''}
                        </CardTitle>
                        <CardDescription className="text-xs text-[#5f7182]">
                            {mode === 'doors' ? 'Configuration des portes' : 'Structure du bâtiment'}
                        </CardDescription>
                    </div>
                </div>
                {canRemove && mode !== 'doors' ? (
                    <Button
                        type="button"
                        variant="destructive"
                        size="sm"
                        className={agenceButtonStyles.danger}
                        onClick={onRemove}
                    >
                        <Trash2 className="h-4 w-4" /> Supprimer
                    </Button>
                ) : null}
            </CardHeader>

            <CardContent className="space-y-5 p-5">
                <div className={cn('grid grid-cols-1 gap-4', mode === 'full' ? 'md:grid-cols-2' : 'md:grid-cols-1')}>
                    {mode !== 'doors' ? (
                        <Field className="mt-4" label="Nom du bâtiment" required error={errors?.[`batiments.${index}.name`]}>
                            <Input
                                value={batiment.name}
                                onChange={(e) => setBatiment({ name: e.target.value })}
                                placeholder="Ex: Bâtiment A"
                            />
                        </Field>
                    ) : null}

                    {mode === 'full' || mode === 'structure' ? (
                        <div className="rounded-xl border border-dashed border-[#c8d4de] bg-[#f8fafc] p-4">
                            <button
                                type="button"
                                onClick={() => setShowStructureOptions((value) => !value)}
                                className="flex w-full items-center justify-between gap-3 text-left text-sm font-semibold text-[#00559b]"
                            >
                                <span>Option pour bâtiment avec étage</span>
                                <ChevronDown
                                    className={cn(
                                        'h-4 w-4 shrink-0 transition-transform duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]',
                                        showStructureOptions && 'rotate-180'
                                    )}
                                />
                            </button>

                            <div
                                className={cn(
                                    'grid overflow-hidden transition-[grid-template-rows,opacity] duration-300 ease-out',
                                    showStructureOptions ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'
                                )}
                            >
                                <div className="min-h-0 pt-4">
                                    <div className="mb-2 ml-1 mr-1 grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <Field label="Nombre d'étages">
                                            <Input
                                                type="number"
                                                min="0"
                                                value={batiment.nbre_etages}
                                                onChange={(e) => setBatiment({ nbre_etages: e.target.value })}
                                                placeholder="0"
                                            />
                                        </Field>
                                        <Field label="Description">
                                            <Input
                                                value={batiment.description}
                                                onChange={(e) => setBatiment({ description: e.target.value })}
                                                placeholder="Description facultative"
                                            />
                                        </Field>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ) : null}
                </div>

                {mode === 'doors' ? (
                    <>
                        <div className="flex items-center justify-between">
                            <p className="text-sm font-semibold text-[#0f172a]">Portes</p>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                className={agenceButtonStyles.actionBlue}
                                onClick={addPorte}
                            >
                                <Plus className="h-3.5 w-3.5" /> Ajouter une porte
                            </Button>
                        </div>

                        <div className="space-y-4">
                            {batiment.portes.map((porte, porteIndex) => (
                                <PorteBlock
                                    key={porte.porte_id ?? `porte-${index}-${porteIndex}`}
                                    index={porteIndex}
                                    porte={porte}
                                    typesPorte={typesPorte}
                                    equipements={equipements}
                                    errors={errors}
                                    errorKey={`batiments.${index}.portes.${porteIndex}`}
                                    onChange={(next) => updatePorte(porteIndex, next)}
                                    onRemove={() => removePorte(porteIndex)}
                                    canRemove={true}
                                    nbreEtages={batiment.nbre_etages}
                                />
                            ))}
                        </div>
                    </>
                ) : null}
            </CardContent>
        </Card>
    );
}

// ─────────────────────────────────────────────────────────────
// Stepper
// ─────────────────────────────────────────────────────────────

function Stepper({ steps, current, completed, onStepClick, allowAllSteps = false }) {
    return (
        <div className="pb-1">
            <ol className="flex flex-col gap-3 lg:grid lg:grid-cols-5 lg:gap-2.5">
                {steps.map((step, index) => {
                    const isActive = index === current;
                    const isDone = completed.includes(index);
                    const isReachable = allowAllSteps || index <= current || isDone;

                    return (
                        <li key={step.key} className="flex items-center gap-3 lg:min-w-0 lg:w-full">
                            <button
                                type="button"
                                onClick={() => (isReachable ? onStepClick(index) : null)}
                                disabled={!isReachable}
                                className={cn(
                                    'flex w-full items-center gap-3 rounded-xl border px-3 py-2.5 text-left transition-colors lg:gap-2 lg:px-2.5 lg:py-2',
                                    isActive
                                        ? 'border-[#00559b] bg-[#eaf4fb]'
                                        : isDone
                                          ? 'border-[#c8e6c9] bg-white hover:border-[#4d8500]'
                                          : 'border-[#e2e8f0] bg-white',
                                    isReachable ? 'cursor-pointer' : 'cursor-not-allowed opacity-70'
                                )}
                            >
                                <span
                                    className={cn(
                                        'flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold lg:h-7 lg:w-7 lg:text-xs',
                                        isActive
                                            ? 'bg-[#00559b] text-white'
                                            : isDone
                                              ? 'bg-[#4d8500] text-white'
                                              : 'bg-[#eef2f6] text-[#5f7182]'
                                    )}
                                >
                                    {isDone && !isActive ? <Check className="h-4 w-4" /> : index + 1}
                                </span>
                                <span className="min-w-0">
                                    <span
                                        className={cn(
                                            'block truncate text-sm font-medium lg:text-[13px]',
                                            isActive ? 'text-[#00559b]' : 'text-[#0f172a]'
                                        )}
                                    >
                                        {step.title}
                                    </span>
                                    <span className="block truncate text-xs text-[#94a3b8] lg:text-[11px]">{step.subtitle}</span>
                                </span>
                            </button>
                            {index < steps.length - 1 ? (
                                <ChevronRight className="hidden h-4 w-4 shrink-0 text-[#c8d4de] sm:block" />
                            ) : null}
                        </li>
                    );
                })}
            </ol>
        </div>
    );
}

function ReviewRow({ label, value }) {
    return (
        <div className="flex items-start justify-between gap-4 py-1.5">
            <span className="text-sm text-[#5f7182]">{label}</span>
            <span className="text-right text-sm font-medium text-[#0f172a]">{value || '—'}</span>
        </div>
    );
}

function formatPorteEtageRecap(etage, totalEtages) {
    const floor = Number(etage ?? 0);
    const buildingFloors = Number(totalEtages ?? 0);

    if (buildingFloors <= 0) {
        return '';
    }

    return floor === 0 ? ' · 0 - RDC' : ` · Étage ${floor}`;
}

// ─────────────────────────────────────────────────────────────
// Page
// ─────────────────────────────────────────────────────────────

export default function Form({
    mode = 'create',
    propriete = null,
    typesPropriete = [],
    typesPorte = [],
    proprietaires = [],
    lots = [],
    equipements = [],
    proximites = [],
    regions = [],
    villes = [],
}) {
    const isEdit = mode === 'edit';
    const [localLots, setLocalLots] = useState(() => (Array.isArray(lots) ? lots : []));
    const [isLotModalOpen, setIsLotModalOpen] = useState(false);
    const [isSavingLot, setIsSavingLot] = useState(false);
    const [lotForm, setLotForm] = useState(buildEmptyLotForm());
    const [lotErrors, setLotErrors] = useState({});

    const { data, setData, post, put, processing, errors } = useForm({
        description: propriete?.description ?? '',
        type_propriete_id: toId(propriete?.type_propriete_id),
        proprietaire_id: toId(propriete?.proprietaire_id),
        lot_id: toId(propriete?.lot_id),
        is_allocation: propriete?.is_allocation ?? true,
        is_actif: propriete?.is_actif ?? true,
        proximites: Array.isArray(propriete?.proximites)
            ? propriete.proximites.map(normalizeProximiteSelection).filter(Boolean)
            : [],
        batiments:
            Array.isArray(propriete?.batiments) && propriete.batiments.length > 0
                ? propriete.batiments.map((batiment) => ({
                      batiment_id: batiment.batiment_id ?? null,
                      name: batiment.name ?? '',
                      description: batiment.description ?? '',
                      nbre_etages: batiment.nbre_etages ?? 0,
                      portes:
                          Array.isArray(batiment.portes) && batiment.portes.length > 0
                              ? batiment.portes.map((porte) => ({
                                    porte_id: porte.porte_id ?? null,
                                    numero_porte: porte.numero_porte ?? '',
                                    type_porte_id: toId(porte.type_porte_id),
                                    superficie_m2: integerValue(porte.superficie_m2),
                                    etage: porte.etage ?? 0,
                                    is_allocation: porte.is_allocation ?? true,
                                    description: porte.description ?? '',
                                    is_occupe: Boolean(porte.is_occupe),
                                    is_actif: porte.is_actif ?? true,
                                    equipements: Array.isArray(porte.equipements) ? porte.equipements.map(toId) : [],
                                    tarif: normalizeTarif({ ...emptyTarif(), ...(porte.tarif ?? {}) }),
                                }))
                              : [emptyPorte()],
                  }))
                : [emptyBatiment()],
    });

    useEffect(() => {
        setLocalLots(Array.isArray(lots) ? lots : []);
    }, [lots]);

    const lotOptions = useMemo(
        () =>
            localLots.map((lot) => ({
                value: toId(lot.id ?? lot.propreietaire_lot_id),
                label: lot.name,
                adresse: lot.adresse,
                proprietaire_id: toId(lot.proprietaire_id),
            })),
        [localLots]
    );

    const ownerOptions = useMemo(
        () =>
            (Array.isArray(proprietaires) ? proprietaires : []).map((owner) => ({
                value: toId(owner.id),
                label: owner.name ?? 'Propriétaire',
                meta: owner.tel1 ? `Tél: ${owner.tel1}` : '',
            })),
        [proprietaires]
    );

    const proximiteOptions = useMemo(
        () =>
            (Array.isArray(proximites) ? proximites : []).map((item) => ({
                id: toId(item.id),
                name: item.name,
                description: item.description,
            })),
        [proximites]
    );

    const proximiteOptionsMap = useMemo(
        () => new Map(proximiteOptions.map((item) => [toId(item.id), item])),
        [proximiteOptions]
    );

    const regionOptions = useMemo(
        () =>
            (Array.isArray(regions) ? regions : []).map((region) => ({
                value: toId(region.id ?? region.region_id),
                label: region.name,
            })),
        [regions]
    );

    const villeOptions = useMemo(
        () =>
            (Array.isArray(villes) ? villes : [])
                .filter((ville) => !lotForm.region_id || toId(ville.region_id) === toId(lotForm.region_id))
                .map((ville) => ({
                    value: toId(ville.id ?? ville.ville_id),
                    label: ville.name,
                })),
        [lotForm.region_id, villes]
    );

    const selectedProprietaireId = toId(data.proprietaire_id);
    const [ownerOpen, setOwnerOpen] = useState(false);
    const [ownerSearch, setOwnerSearch] = useState('');
    const filteredOwnerOptions = useMemo(() => {
        const search = ownerSearch.trim().toLowerCase();

        if (!search) {
            return ownerOptions;
        }

        return ownerOptions.filter((owner) => {
            const haystack = `${owner.label} ${owner.meta ?? ''}`.toLowerCase();
            return haystack.includes(search);
        });
    }, [ownerOptions, ownerSearch]);

    const availableLotOptions = useMemo(
        () => lotOptions.filter((lot) => lot.proprietaire_id === selectedProprietaireId),
        [lotOptions, selectedProprietaireId]
    );

    const selectedLot = availableLotOptions.find((lot) => lot.value === toId(data.lot_id));

    const openLotModal = () => {
        if (!selectedProprietaireId) {
            return;
        }

        setLotForm(buildEmptyLotForm());
        setLotErrors({});
        setIsLotModalOpen(true);
    };

    const closeLotModal = () => {
        if (isSavingLot) {
            return;
        }

        setIsLotModalOpen(false);
        setLotErrors({});
    };

    const handleLotSubmit = async (event) => {
        event.preventDefault();

        if (!selectedProprietaireId) {
            setLotErrors({ general: 'Choisissez d’abord un propriétaire.' });
            return;
        }

        setIsSavingLot(true);
        setLotErrors({});

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const response = await fetch(`/agence/proprietaire/lots/${selectedProprietaireId}`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(lotForm),
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok || payload.success === false) {
                setLotErrors(payload?.errors ?? {});
                if (!payload?.errors) {
                    setLotErrors({ general: payload?.message ?? "Impossible d'enregistrer le lot." });
                }
                return;
            }

            const createdLot = payload?.lot ?? null;
            if (createdLot) {
                setLocalLots((current) => [...current, createdLot]);
                setData('lot_id', toId(createdLot.propreietaire_lot_id ?? createdLot.id));
            }

            setIsLotModalOpen(false);
            setLotForm(buildEmptyLotForm());
        } catch (error) {
            setLotErrors({ general: "Une erreur est survenue lors de l'enregistrement." });
        } finally {
            setIsSavingLot(false);
        }
    };

    const totalPortes = data.batiments.reduce((acc, batiment) => acc + batiment.portes.length, 0);
    const selectedProximites = useMemo(
        () =>
            data.proximites
                .map(normalizeProximiteSelection)
                .filter(Boolean)
                .map((item) => ({
                    ...item,
                    ...proximiteOptionsMap.get(toId(item.id)),
                })),
        [data.proximites, proximiteOptionsMap]
    );

    useEffect(() => {
        if (!selectedProprietaireId) {
            if (data.lot_id) {
                setData('lot_id', '');
            }
            return;
        }

        if (data.lot_id && !availableLotOptions.some((lot) => lot.value === toId(data.lot_id))) {
            setData('lot_id', '');
        }
    }, [availableLotOptions, data.lot_id, selectedProprietaireId, setData]);

    const handleLotChange = (value) => {
        setData('lot_id', value);
    };

    const toggleProximite = (id) => {
        const exists = selectedProximites.some((item) => toId(item.id) === id);

        setData(
            'proximites',
            exists
                ? selectedProximites.filter((item) => toId(item.id) !== id)
                : [...selectedProximites, { id, distance: '', unite: 'm' }]
        );
    };

    const updateProximite = (id, patch) => {
        setData(
            'proximites',
            selectedProximites.map((item) => (toId(item.id) === id ? { ...item, ...patch } : item))
        );
    };

    const removeProximite = (id) => {
        setData(
            'proximites',
            selectedProximites.filter((item) => toId(item.id) !== id)
        );
    };

    const updateBatiment = (index, nextBatiment) => {
        setData(
            'batiments',
            data.batiments.map((batiment, i) => (i === index ? nextBatiment : batiment))
        );
    };

    const addBatiment = () => setData('batiments', [...data.batiments, emptyBatiment()]);

    const removeBatiment = (index) =>
        setData(
            'batiments',
            data.batiments.filter((_, i) => i !== index)
        );

    // ── Steps ────────────────────────────────────────────────
    const steps = [
        { key: 'general', title: 'Général', subtitle: 'Infos clés' },
        { key: 'proximites', title: 'Proximités', subtitle: 'Points d\u2019intérêt' },
        { key: 'batiments', title: 'Bâtiments', subtitle: 'Structure' },
        { key: 'portes', title: 'Saisie des portes', subtitle: 'Configuration' },
        { key: 'recap', title: 'Récapitulatif', subtitle: 'Vérification finale' },
    ];

    const [current, setCurrent] = useState(0);
    const [completed, setCompleted] = useState([]);
    const [stepErrors, setStepErrors] = useState({});
    const [selectedBatimentIndex, setSelectedBatimentIndex] = useState(0);
    const [expandedProximiteId, setExpandedProximiteId] = useState('');

    useEffect(() => {
        if (!data.batiments.length) {
            setSelectedBatimentIndex(0);
            return;
        }

        setSelectedBatimentIndex((index) => Math.min(index, data.batiments.length - 1));
    }, [data.batiments.length]);

    const isLastStep = current === steps.length - 1;

    // Client-side required checks before advancing
    const validateStep = (index) => {
        const localErrors = {};

        if (index === 0) {
            if (!toId(data.proprietaire_id)) localErrors.proprietaire_id = 'Sélectionnez un propriétaire.';
            if (!toId(data.lot_id)) localErrors.lot_id = 'Sélectionnez un lot.';
        }

        if (index === 1) {
            selectedProximites.forEach((item, proxIndex) => {
                if (item.distance === '' || item.distance === null || item.distance === undefined) {
                    localErrors[`proximites.${proxIndex}.distance`] = 'La distance est obligatoire.';
                }

                if (!item.unite) {
                    localErrors[`proximites.${proxIndex}.unite`] = 'Choisissez une unité.';
                }
            });
        }

        if (index === 2) {
            data.batiments.forEach((batiment, bIndex) => {
                if (!batiment.name.trim()) localErrors[`batiments.${bIndex}.name`] = 'Nom requis.';
            });
        }

        if (index === 3) {
            data.batiments.forEach((batiment, bIndex) => {
                batiment.portes.forEach((porte, pIndex) => {
                    const prefix = `batiments.${bIndex}.portes.${pIndex}`;
                    if (!porte.numero_porte.trim()) localErrors[`${prefix}.numero_porte`] = 'Numéro requis.';
                    if (!toId(porte.type_porte_id)) localErrors[`${prefix}.type_porte_id`] = 'Type requis.';
                    if (porte.is_allocation !== true && porte.is_allocation !== false) {
                        localErrors[`${prefix}.is_allocation`] = 'Choisissez Location ou Vente.';
                    }
                    if (porte.is_allocation === true) {
                        if (porte.tarif.mt_loyer === '' || porte.tarif.mt_loyer === null) {
                            localErrors[`${prefix}.tarif.mt_loyer`] = 'Loyer requis.';
                        }
                        if (porte.tarif.mt_caution === '' || porte.tarif.mt_caution === null) {
                            localErrors[`${prefix}.tarif.mt_caution`] = 'Caution requise.';
                        }
                        if (porte.tarif.mt_avance === '' || porte.tarif.mt_avance === null) {
                            localErrors[`${prefix}.tarif.mt_avance`] = 'Avance requise.';
                        }
                        if (porte.tarif.mt_frais_agence === '' || porte.tarif.mt_frais_agence === null) {
                            localErrors[`${prefix}.tarif.mt_frais_agence`] = "Frais d'agence requis.";
                        }
                    } else {
                        if (porte.tarif.mt_vente === '' || porte.tarif.mt_vente === null) {
                            localErrors[`${prefix}.tarif.mt_vente`] = 'Prix de vente requis.';
                        }
                    }
                });
            });
        }

        return localErrors;
    };

    const goTo = (index) => {
        setStepErrors({});
        setCurrent(index);
        if (typeof window !== 'undefined') window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const handleNext = () => {
        const localErrors = validateStep(current);
        if (Object.keys(localErrors).length > 0) {
            setStepErrors(localErrors);
            return;
        }
        setCompleted((prev) => (prev.includes(current) ? prev : [...prev, current]));
        goTo(Math.min(current + 1, steps.length - 1));
    };

    const handlePrev = () => goTo(Math.max(current - 1, 0));

    // Merge server-side (Inertia) and client-side step errors for display
    const mergedErrors = { ...errors, ...stepErrors };
    const selectedBatiment = data.batiments[selectedBatimentIndex] ?? data.batiments[0] ?? null;

    const handleSubmit = () => {
        // Only submit from the final step
        if (!isLastStep) {
            handleNext();
            return;
        }

        if (isEdit && propriete?.id) {
            put(`/agence/proprietes/update/${propriete.id}`, { preserveScroll: true });
        } else {
            post('/agence/proprietes/store', { preserveScroll: true });
        }
    };

    // Lookup names for the recap step
    const nameById = (list, id) => (list.find((item) => toId(item.id) === toId(id))?.name ?? '');
    const proprietaireName = nameById(proprietaires, data.proprietaire_id);
    const proximiteNames = selectedProximites.map((item) => ({
        ...item,
        name: proximiteOptionsMap.get(toId(item.id))?.name ?? item.name ?? '',
        description: proximiteOptionsMap.get(toId(item.id))?.description ?? item.description ?? '',
    }));

    const handleBack = () => {
        if (typeof window !== 'undefined' && window.history.length > 1) {
            window.history.back();
            return;
        }

        router.visit('/agence/proprietes');
    };

    return (
        <AgenceLayout title={isEdit ? 'Modifier une propriété' : 'Nouvelle propriété'}>
            <Head title={isEdit ? 'Modifier une propriété' : 'Nouvelle propriété'} />

            <div className="mx-auto flex max-w-5xl flex-col gap-6 pb-10">
                {/* Header */}
                <div className="flex items-center gap-3">
                    <Button type="button" variant="outline" size="icon" className="rounded-xl border-[#c8d4de]" onClick={handleBack}>
                        <ArrowLeft className="h-4 w-4" />
                    </Button>
                    <div>
                        <h1 className="text-xl font-semibold text-[#0f172a]">
                            {isEdit ? 'Modifier la propriété' : 'Nouvelle propriété'}
                        </h1>
                    
                    </div>
                </div>

                {/* Stepper */}
                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardContent className="pt-6 p-4">
                        <Stepper
                            steps={steps}
                            current={current}
                            completed={completed}
                            onStepClick={goTo}
                            allowAllSteps={isEdit}
                        />
                    </CardContent>
                </Card>

                {/* Step 1: Infos générales */}
                {current === 0 ? (
                    <SectionCard
                        icon={Home}
                        title="Infos générales"
                        description="Identité et lot"
                    >
                        <div className="mt-4 grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
                                <SharedComboboxField
                                    label="Propriétaire"
                                    required
                                    value={toId(data.proprietaire_id)}
                                    placeholder="Choisir un propriétaire"
                                    options={filteredOwnerOptions}
                                    open={ownerOpen}
                                    onOpenChange={(nextOpen) => {
                                        setOwnerOpen(nextOpen);
                                        if (!nextOpen) {
                                            setOwnerSearch('');
                                        }
                                    }}
                                    searchValue={ownerSearch}
                                    onSearchChange={setOwnerSearch}
                                    onSelect={(value) => {
                                        setData('proprietaire_id', value);
                                        setData('lot_id', '');
                                    }}
                                    emptyLabel="Aucun propriétaire trouvé."
                                />
                            

                           <Field
                                label="Lot"
                                required
                                error={mergedErrors.lot_id}
                                action={
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        className={cn(
                                            agenceButtonStyles.outline,
                                            '!h-5 !min-h-0 gap-1 rounded-full border-[#c8d4de] bg-[#f8fafc] px-2 py-0 text-[11px] font-medium text-[#00559b] shadow-none hover:border-[#00559b] hover:bg-[#eaf4fb]'
                                        )}
                                        onClick={openLotModal}
                                        disabled={!selectedProprietaireId}
                                    >
                                        <Plus className="h-3 w-3" />
                                        Nouveau lot
                                    </Button>
                                }
                            >
                                <Select
                                    className="w-full"
                                    value={toId(data.lot_id)}
                                    onValueChange={handleLotChange}
                                    disabled={!selectedProprietaireId}
                                >
                                    <SelectTrigger disabled={!selectedProprietaireId}>
                                        <SelectValue placeholder="Choisir" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {availableLotOptions.map((lot) => (
                                            <SelectItem key={lot.value} value={lot.value}>
                                                {lot.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {!selectedProprietaireId ? (
                                    <p className="text-xs text-[#94a3b8]">
                                        Choisissez d'abord un propriétaire pour afficher ses lots.
                                    </p>
                                ) : null}
                            </Field>

                            <Field label="Note" className="md:col-span-2">
                                <textarea
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    rows={3}
                                    className="min-h-20 w-full rounded-xl border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] outline-none placeholder:text-[#94a3b8] focus:border-[#00559b] focus:ring-2 focus:ring-[#00559b]/20"
                                    placeholder="Courte note"
                                />
                            </Field>
                        </div>

                        {selectedLot?.adresse ? (
                            <div className="mt-4 flex items-center gap-2 rounded-xl border border-[#e2e8f0] bg-[#f8fafc] px-3 py-2 text-sm text-[#5f7182]">
                                <MapPin className="h-4 w-4 text-[#00559b]" />
                                <span className="truncate">{selectedLot.adresse}</span>
                            </div>
                        ) : null}

                        

                    </SectionCard>
                ) : null}

                {/* Step 2: Proximités */}
                {current === 1 ? (
                    <SectionCard
                        icon={Tag}
                        title="Proximités"
                        description="Points d'intérêt à proximité de la propriété"
                    >
                        <div className="mt-4">
                            <ProximitePickerCard
                                options={proximiteOptions}
                                selected={selectedProximites}
                                expandedId={expandedProximiteId}
                                onToggle={toggleProximite}
                                onUpdate={updateProximite}
                                onRemove={removeProximite}
                                onExpand={setExpandedProximiteId}
                                errors={mergedErrors}
                            />
                        </div>
                    </SectionCard>
                ) : null}

                {/* Step 3: Bâtiments */}
                {current === 2 ? (
                    <div className="flex flex-col gap-6">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2 text-sm font-semibold text-[#0f172a]">
                                <Layers3 className="h-4 w-4 text-[#00559b]" />
                                Bâtiments
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                className={agenceButtonStyles.actionBlue}
                                onClick={addBatiment}
                            >
                                <Plus className="h-4 w-4" /> Ajouter un bâtiment
                            </Button>
                        </div>

                       

                        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            {data.batiments.map((batiment, index) => (
                                <BatimentBlock
                                    key={batiment.batiment_id ?? `batiment-${index}`}
                                    index={index}
                                    batiment={batiment}
                                    typesPorte={typesPorte}
                                    equipements={equipements}
                                    errors={mergedErrors}
                                    onChange={(next) => updateBatiment(index, next)}
                                    onRemove={() => removeBatiment(index)}
                                    canRemove={data.batiments.length > 1}
                                    mode="structure"
                                />
                            ))}
                        </div>
                    </div>
                ) : null}

                {/* Step 4: Portes */}
                {current === 3 ? (
                    <div className="flex flex-col gap-6">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2 text-sm font-semibold text-[#0f172a]">
                                <DoorOpen className="h-4 w-4 text-[#00559b]" />
                                Saisie des portes
                            </div>
                        </div>

                       
                        <div className="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,3fr)]">
                            <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                                <CardHeader className="border-b border-[#e2e8f0] py-4">
                                    <CardTitle className="flex items-center gap-2 text-sm text-[#0f172a]">
                                        <Building2 className="h-4 w-4 text-[#00559b]" />
                                        Bâtiments
                                    </CardTitle>
                                    <CardDescription className="text-xs text-[#5f7182]">
                                        Sélectionne un bâtiment pour afficher ses portes.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="mt-4 p-4">
                                    <ScrollArea className="max-h-[70vh] pr-1">
                                        <div className="space-y-2">
                                            {data.batiments.map((batiment, index) => {
                                                const isSelected = index === selectedBatimentIndex;

                                                return (
                                                    <button
                                                        key={batiment.batiment_id ?? `batiment-selector-${index}`}
                                                        type="button"
                                                        onClick={() => setSelectedBatimentIndex(index)}
                                                        className={cn(
                                                            'flex w-full items-start justify-between gap-3 rounded-2xl border p-3 text-left transition-colors',
                                                            isSelected
                                                                ? 'border-[#00559b] bg-[#eaf4fb] shadow-sm'
                                                                : 'border-[#e2e8f0] bg-white hover:border-[#00559b]/40 hover:bg-[#f8fafc]'
                                                        )}
                                                    >
                                                        <div className="min-w-0">
                                                            <div className="flex items-center gap-2">
                                                                <span
                                                                    className={cn(
                                                                        'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg',
                                                                        isSelected ? 'bg-[#00559b] text-white' : 'bg-[#eef2f6] text-[#5f7182]'
                                                                    )}
                                                                >
                                                                    <Building2 className="h-4 w-4" />
                                                                </span>
                                                                <div className="min-w-0">
                                                                    <p
                                                                        className={cn(
                                                                            'truncate text-sm font-semibold',
                                                                            isSelected ? 'text-[#00559b]' : 'text-[#0f172a]'
                                                                        )}
                                                                    >
                                                                        {batiment.name || `Bâtiment #${index + 1}`}
                                                                    </p>
                                                                    <p className="text-xs text-[#5f7182]">
                                                                        {batiment.portes.length} porte{batiment.portes.length > 1 ? 's' : ''}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <span
                                                            className={cn(
                                                                'mt-0.5 rounded-full px-2.5 py-1 text-xs font-medium',
                                                                isSelected
                                                                    ? 'bg-white text-[#00559b]'
                                                                    : 'bg-[#f1f5f9] text-[#5f7182]'
                                                            )}
                                                        >
                                                            {isSelected ? 'Sélectionné' : 'Choisir'}
                                                        </span>
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </ScrollArea>
                                </CardContent>
                            </Card>

                            <div className="space-y-4">
                                {selectedBatiment ? (
                                    <BatimentBlock
                                        key={selectedBatiment.batiment_id ?? `batiment-doors-${selectedBatimentIndex}`}
                                        index={selectedBatimentIndex}
                                        batiment={selectedBatiment}
                                        typesPorte={typesPorte}
                                        equipements={equipements}
                                        errors={mergedErrors}
                                        onChange={(next) => updateBatiment(selectedBatimentIndex, next)}
                                        onRemove={() => removeBatiment(selectedBatimentIndex)}
                                        canRemove={data.batiments.length > 1}
                                        mode="doors"
                                    />
                                ) : (
                                    <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                                        <CardContent className="p-6 text-sm text-[#5f7182]">
                                            Aucun bâtiment disponible pour la saisie des portes.
                                        </CardContent>
                                    </Card>
                                )}
                            </div>
                        </div>
                    </div>
                ) : null}

                {/* Step 5: Récapitulatif */}
                {current === 4 ? (
                    <div className="flex flex-col gap-6">
                        <SectionCard icon={Home} title="Informations générales" description="Récapitulatif">
                            <div className="mt-4 divide-y divide-[#eef2f6]">
                                <ReviewRow label="Propriétaire" value={proprietaireName} />
                                <ReviewRow label="Lot" value={selectedLot?.label} />
                                <ReviewRow label="Adresse" value={selectedLot?.adresse} />
                            </div>
                        </SectionCard>

                        <SectionCard icon={Tag} title="Proximités" description={`${proximiteNames.length} sélectionnée(s)`}>
                            {proximiteNames.length ? (
                                <div className="mt-4 space-y-2">
                                    {proximiteNames.map((item) => (
                                        <div
                                            key={item.id}
                                            className="flex flex-col gap-1 rounded-xl border border-[#e2e8f0] bg-[#f8fafc] px-3 py-2 sm:flex-row sm:items-center sm:justify-between"
                                        >
                                            <div className="min-w-0">
                                                <p className="text-sm font-medium text-[#0f172a]">
                                                    <span className="mr-1 text-[#00559b]">~</span>
                                                    {item.name}
                                                </p>
                                                {item.description ? (
                                                    <p className="text-xs text-[#5f7182]">{item.description}</p>
                                                ) : null}
                                            </div>
                                            <p className="text-sm font-semibold text-[#00559b]">
                                                {item.distance || '—'} {item.unite || ''}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="mt-2 text-sm text-[#94a3b8]">Aucune proximité sélectionnée.</p>
                            )}
                        </SectionCard>

                        <SectionCard icon={Layers3} title="Bâtiments" description={`${data.batiments.length} bâtiment(s)`}>
                            <div className="mt-4 space-y-3">
                                {data.batiments.map((batiment, index) => (
                                    <div
                                        key={batiment.batiment_id ?? `recap-bat-${index}`}
                                        className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4"
                                    >
                                        <div className="flex items-center gap-2 text-sm font-semibold text-[#0f172a]">
                                            <Building2 className="h-4 w-4 text-[#4d8500]" />
                                            {batiment.name || `Bâtiment #${index + 1}`}
                                        </div>
                                        <p className="mt-1 text-xs text-[#94a3b8]">
                                            {batiment.portes.length} porte{batiment.portes.length > 1 ? 's' : ''}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </SectionCard>

                        <SectionCard
                            icon={DoorOpen}
                            title="Portes"
                            description={`${number(totalPortes)} porte(s) au total`}
                        >
                            <div className="space-y-3">
                                {data.batiments.map((batiment, index) => (
                                    <div
                                        key={batiment.batiment_id ?? `recap-bat-${index}`}
                                        className="mt-4 rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4"
                                    >
                                        <div className="flex items-center gap-2 text-sm font-semibold text-[#0f172a]">
                                            <Building2 className="h-4 w-4 text-[#4d8500]" />
                                            {batiment.name || `Bâtiment #${index + 1}`}
                                            <span className="text-xs font-normal text-[#94a3b8]">
                                                · {batiment.portes.length} porte{batiment.portes.length > 1 ? 's' : ''}
                                            </span>
                                        </div>
                                        <div className="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                            {batiment.portes.map((porte, pIndex) => (
                                                <div
                                                    key={porte.porte_id ?? `recap-porte-${index}-${pIndex}`}
                                                    className="flex items-center justify-between rounded-lg border border-[#e2e8f0] bg-white px-3 py-2 text-sm"
                                                >
                                                    <span className="flex items-center gap-2 text-[#0f172a]">
                                                        <DoorOpen className="h-4 w-4 text-[#00559b]" />
                                                        {porte.numero_porte || `Porte #${pIndex + 1}`}
                                                        {formatPorteEtageRecap(porte.etage, batiment.nbre_etages)}
                                                    </span>
                                                    <div className="flex flex-col items-end gap-0.5 text-right">
                                                        <span className="font-medium text-[#0f172a]">
                                                            {porte.is_allocation
                                                                ? porte.tarif.mt_loyer
                                                                    ? `${number(porte.tarif.mt_loyer)} FCFA`
                                                                    : '—'
                                                                : porte.tarif.mt_vente
                                                                    ? `${number(porte.tarif.mt_vente)} FCFA`
                                                                    : '—'}
                                                        </span>
                                                        <span className="text-[11px] text-[#94a3b8]">
                                                            {porte.is_allocation ? 'Location' : 'Vente'}
                                                        </span>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </SectionCard>
                    </div>
                ) : null}

                {isLotModalOpen ? (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
                        <div className="w-full max-w-2xl rounded-3xl border border-[#c8d4de] bg-white shadow-2xl">
                            <div className="flex items-start justify-between gap-4 border-b border-[#e2e8f0] px-6 py-5">
                                <div>
                                    <h3 className="text-lg font-semibold text-[#0f172a]">
                                        Ajouter un lot pour{' '}
                                        {ownerOptions.find((owner) => owner.value === selectedProprietaireId)?.label ?? 'ce propriétaire'}
                                    </h3>
                                </div>
                                <Button type="button" variant="outline" size="icon" className={agenceButtonStyles.outline} onClick={closeLotModal} disabled={isSavingLot}>
                                    <X className="h-4 w-4" />
                                </Button>
                            </div>

                            <form onSubmit={handleLotSubmit} className="px-6 py-6">
                                {lotErrors.general ? <p className="mb-4 text-sm text-[#b42318]">{lotErrors.general}</p> : null}

                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <Field label="Nom du lot" required error={lotErrors.name} >
                                        <Input
                                            value={lotForm.name}
                                            onChange={(e) => setLotForm((current) => ({ ...current, name: e.target.value }))}
                                            placeholder="Lot principal"
                                        />
                                    </Field>

                                    <Field label="Superficie (m²)" error={lotErrors.superficie}>
                                        <Input
                                            type="text"
                                            inputMode="numeric"
                                            pattern="[0-9]*"
                                            value={lotForm.superficie}
                                            onKeyDown={handleIntegerKeyDown}
                                            onPaste={handleIntegerPaste}
                                            onChange={(e) =>
                                                setLotForm((current) => ({
                                                    ...current,
                                                    superficie: digitsOnly(e.target.value),
                                                }))
                                            }
                                            placeholder="150"
                                        />
                                    </Field>

                                    <Field label="Région" error={lotErrors.region_id}>
                                        <Select
                                            value={toId(lotForm.region_id)}
                                            onValueChange={(value) =>
                                                setLotForm((current) => ({
                                                    ...current,
                                                    region_id: value,
                                                    ville_id: '',
                                                }))
                                            }
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Sélectionner" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {regionOptions.map((region) => (
                                                    <SelectItem key={region.value} value={region.value}>
                                                        {region.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </Field>

                                    <Field label="Ville" error={lotErrors.ville_id}>
                                        <Select value={toId(lotForm.ville_id)} onValueChange={(value) => setLotForm((current) => ({ ...current, ville_id: value }))} disabled={!lotForm.region_id}>
                                            <SelectTrigger disabled={!lotForm.region_id}>
                                                <SelectValue placeholder="Sélectionner" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {villeOptions.map((ville) => (
                                                    <SelectItem key={ville.value} value={ville.value}>
                                                        {ville.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </Field>

                                    <Field label="N° lot" error={lotErrors.num_lot}>
                                        <Input
                                            value={lotForm.num_lot}
                                            onChange={(e) => setLotForm((current) => ({ ...current, num_lot: e.target.value }))}
                                            placeholder="001"
                                        />
                                    </Field>

                                    <Field label="N° îlot" error={lotErrors.num_ilot}>
                                        <Input
                                            value={lotForm.num_ilot}
                                            onChange={(e) => setLotForm((current) => ({ ...current, num_ilot: e.target.value }))}
                                            placeholder="A"
                                        />
                                    </Field>

                                    

                                    <Field label="Adresse" error={lotErrors.adresse} className="md:col-span-2">
                                        <Input
                                            value={lotForm.adresse}
                                            onChange={(e) => setLotForm((current) => ({ ...current, adresse: e.target.value }))}
                                            placeholder="Cocody Riviera 3"
                                        />
                                    </Field>

                                    
                                </div>

                                <div className="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                    <Button type="button" variant="outline" className={agenceButtonStyles.outline} onClick={closeLotModal} disabled={isSavingLot}>
                                        Annuler
                                    </Button>
                                    <Button type="submit" className={agenceButtonStyles.primary} disabled={isSavingLot}>
                                        {isSavingLot ? 'Enregistrement...' : 'Enregistrer'}
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                ) : null}

                {/* Navigation footer */}
                <div className="flex flex-col-reverse gap-3 border-t border-[#e2e8f0] pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <div className="w-full sm:w-auto">
                        {current > 0 ? (
                            <Button
                                type="button"
                                variant="outline"
                                className={cn(agenceButtonStyles.outline, 'w-full sm:w-auto')}
                                onClick={handlePrev}
                            >
                                <ChevronLeft className="h-4 w-4" /> Précédent
                            </Button>
                        ) : (
                            <Button
                                asChild
                                type="button"
                                variant="outline"
                                className={cn(agenceButtonStyles.outline, 'w-full sm:w-auto')}
                            >
                                <Link href="/agence/proprietes">Annuler</Link>
                            </Button>
                        )}
                    </div>

                    <div className="flex w-full gap-2 sm:w-auto sm:justify-end">
                        {isLastStep ? (
                            <Button
                                type="button"
                                className={cn(agenceButtonStyles.primary, 'w-full sm:w-auto')}
                                disabled={processing}
                                onClick={handleSubmit}
                            >
                                <Save className="h-4 w-4" />
                                {isEdit ? 'Enregistrer les modifications' : 'Créer la propriété'}
                            </Button>
                        ) : (
                            <Button
                                type="button"
                                className={cn(agenceButtonStyles.primary, 'w-full sm:w-auto')}
                                onClick={handleNext}
                            >
                                Suivant <ChevronRight className="h-4 w-4" />
                            </Button>
                        )}
                    </div>
                </div>
            </div>
        </AgenceLayout>
    );
}
