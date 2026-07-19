import { useEffect, useMemo, useRef, useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import {
    Check,
    Clock3,
    ClipboardList,
    ChevronRight,
    Loader2,
    Search,
    ShieldCheck,
    Tag,
    Users,
    Wrench,
    Eye,
    Home,
    Mail,
    MapPin,
    Pencil,
    Phone,
    Plus,
    Power,
    Trash2,
    X,
    UserRound,
} from 'lucide-react';

import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '../../../components/ui/dialog';
import { Input } from '../../../components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
import { ScrollArea } from '../../../components/ui/scroll-area';
import { Tabs, TabsList, TabsTrigger } from '../../../components/ui/tabs';
import { cn } from '../../../lib/utils';
import { ComboboxField as SharedComboboxField } from '../../../components/ui/combobox-field';
import flags from 'react-phone-number-input/flags';
import PhoneInputBase from 'react-phone-number-input';
import 'react-phone-number-input/style.css';

const asArray = (value) => (Array.isArray(value) ? value : []);

const inputClassName =
    'flex h-10 w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-[#8798a5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

const textareaClassName =
    'flex min-h-[120px] w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-[#8798a5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

const formatDate = (value) => {
    if (!value) return '---';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);

    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date);
};

const formatDateTime = (value) => {
    if (!value) return '---';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);

    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};

const toDateInput = (value) => {
    if (!value) return '';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value).slice(0, 10);

    return date.toISOString().slice(0, 10);
};

const toPhoneValue = (value) => (value ? String(value) : '');

const currency = (value) =>
    new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const statusMeta = (status) => {
    const value = String(status ?? '').toLowerCase();

    if (value.includes('cours')) return { label: 'En cours', variant: 'warning' };
    if (value.includes('term')) return { label: 'Termine', variant: 'success' };
    if (value.includes('annul')) return { label: 'Annule', variant: 'danger' };
    if (value.includes('plan')) return { label: 'Planifie', variant: 'secondary' };

    return { label: 'En attente', variant: 'outline' };
};

const chargeMeta = (value) => {
    const key = String(value ?? '').toLowerCase();

    if (key === 'proprietaire') return { label: 'Proprietaire', variant: 'warning' };
    if (key === 'locataire') return { label: 'Locataire', variant: 'secondary' };
    if (key === 'agence') return { label: 'Agence', variant: 'default' };

    return { label: 'Non precisee', variant: 'outline' };
};

const priorityMeta = (value) => {
    const key = String(value ?? '').toLowerCase();

    if (key === 'haute') return { label: 'Priorité haute', shortLabel: 'Haute', variant: 'danger' };
    if (key === 'basse') return { label: 'Priorité basse', shortLabel: 'Basse', variant: 'secondary' };

    return { label: 'Priorité normale', shortLabel: 'Normale', variant: 'outline' };
};

const isLate = (item) => {
    if (!item?.date_fin) return false;
    const statutValue = String(item?.statut ?? '').toLowerCase();
    if (statutValue.includes('term') || statutValue.includes('annul')) return false;

    return new Date(item.date_fin).getTime() < Date.now();
};

const isPieceExpired = (item) => {
    if (!item?.date_validite_piece) return false;

    return new Date(item.date_validite_piece).getTime() < Date.now();
};

const availabilityMeta = (value) =>
    Boolean(value)
        ? { label: 'Disponible', variant: 'success' }
        : { label: 'Indisponible', variant: 'danger' };

const initials = (name, fallback = 'M') => {
    const parts = String(name ?? '')
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    return (
        parts
            .slice(0, 2)
            .map((part) => part[0]?.toUpperCase())
            .join('') || fallback
    );
};

const normalizeMaintenancier = (item) => {
    const id = String(item?.maintenancier_id ?? item?.id ?? crypto.randomUUID());

    return {
        id,
        maintenancier_id: id,
        agence_id: String(item?.agence_id ?? item?.agence?.agence_id ?? ''),
        name: item?.name ?? 'Sans nom',
        entreprise: item?.entreprise ?? 'Independant',
        email: item?.email ?? '',
        tel1: item?.tel1 ?? '',
        tel2: item?.tel2 ?? '',
        adresse: item?.adresse ?? '',
        statut: Boolean(Number(item?.statut ?? 1)),
        fonction_maintenance_id: String(item?.fonction_maintenance_id ?? item?.fonction?.fonction_maintenance_id ?? ''),
        fonction: item?.fonction?.name ?? item?.fonction?.libelle ?? item?.fonction ?? 'Non definie',
        type_piece_id: String(item?.type_piece_id ?? item?.typePiece?.type_pieces_id ?? item?.type_piece?.type_pieces_id ?? ''),
        type_piece:
            item?.typePiece?.name ??
            item?.typePiece?.libelle ??
            item?.type_piece?.name ??
            item?.type_piece?.libelle ??
            item?.type_piece ??
            item?.type_piece_id ??
            '',
        numero_piece: item?.numero_piece ?? '',
        date_validite_piece: item?.date_validite_piece ?? null,
        created_at: item?.created_at ?? null,
        updated_at: item?.updated_at ?? null,
        interventions_count: Number(item?.interventions_count ?? 0),
    };
};

const normalizeMaintenance = (item) => {
    const id = String(item?.maintenance_id ?? item?.id ?? crypto.randomUUID());
    const details = asArray(item?.details);

    return {
        id,
        maintenance_id: id,
        agence_id: String(item?.agence_id ?? item?.agence?.agence_id ?? ''),
        titre: item?.titre ?? 'Intervention sans titre',
        description: item?.description ?? item?.description_generale ?? '',
        proprietaire: item?.proprietaire?.name ?? item?.proprietaire?.nom ?? 'Non defini',
        proprietaire_id: String(item?.proprietaire_id ?? item?.proprietaire?.proprietaire_id ?? ''),
        propriete: item?.propriete?.description ?? item?.propriete?.name ?? '',
        lot: item?.lot?.name ?? item?.lot?.libelle ?? item?.lot_id ?? '',
        batiment: item?.batiment?.name ?? item?.batiment?.libelle ?? item?.batiment_id ?? '',
        porte: item?.porte?.numero_porte ?? item?.porte?.name ?? item?.porte_id ?? '',
        prise_en_charge_par: item?.prise_en_charge_par ?? '',
        statut: item?.statut ?? 'en attente',
        montant_global: Number(item?.montant_global ?? 0),
        date_debut: item?.date_debut ?? details[0]?.date_debut ?? item?.created_at ?? null,
        date_fin: item?.date_fin ?? details[0]?.date_fin ?? null,
        details,
        details_count: details.length,
    };
};

const normalizeType = (item) => {
    const id = String(item?.type_maintenance_id ?? item?.id ?? crypto.randomUUID());
    const category = item?.maintenance_category ?? item?.maintenanceCategory ?? null;

    return {
        id,
        type_maintenance_id: id,
        agence_id: String(item?.agence_id ?? item?.agence?.agence_id ?? ''),
        name: item?.name ?? item?.libelle ?? 'Sans nom',
        maintenance_category_id: String(item?.maintenance_category_id ?? category?.maintenance_category_id ?? ''),
        categorie: category?.name ?? item?.categorie ?? '---',
        category_name: category?.name ?? item?.categorie ?? '---',
        duree_estimee: item?.duree_estimee ?? null,
        description: item?.description ?? '',
        maintenances_count: Number(item?.maintenances_count ?? 0),
    };
};

const normalizeFonction = (item) => {
    const id = String(item?.fonction_maintenance_id ?? item?.id ?? crypto.randomUUID());

    return {
        id,
        fonction_maintenance_id: id,
        agence_id: String(item?.agence_id ?? item?.agence?.agence_id ?? ''),
        name: item?.name ?? item?.libelle ?? 'Sans nom',
        description: item?.description ?? '',
        maintenanciers_count: Number(item?.maintenanciers_count ?? 0),
    };
};

const ACCENT_BLUE = 'bg-[#eaf4fb] text-[#00559b]';
const ACCENT_GREEN = 'bg-[#eef8df] text-[#4d8500]';
const ACCENT_SLATE = 'bg-[#f1f5f9] text-[#5f7182]';
const ACCENT_ORANGE = 'bg-[#fff2e6] text-[#c2410c]';

const CREATE_OPTIONS = [
    {
        formKey: 'intervention',
        section: 'interventions',
        title: 'Intervention',
        description: 'Planifier une nouvelle intervention de maintenance.',
        icon: ClipboardList,
        accent: ACCENT_BLUE,
    },
    {
        formKey: 'maintenancier',
        section: 'maintenanciers',
        title: 'Maintenancier',
        description: 'Ajouter un nouveau prestataire ou technicien.',
        icon: UserRound,
        accent: ACCENT_GREEN,
    },
    {
        formKey: 'fonction',
        section: 'fonctions',
        title: 'Fonction',
        description: 'Créer une fonction de maintenance.',
        icon: ShieldCheck,
        accent: ACCENT_SLATE,
    },
    {
        formKey: 'type',
        section: 'types',
        title: 'Type',
        description: 'Déclarer un type dâ€™intervention.',
        icon: Tag,
        accent: ACCENT_ORANGE,
    },
];

function StatCard({ icon: Icon, label, value, accent = ACCENT_BLUE }) {
    return (
        <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardContent className="mt-6 flex items-center gap-3 p-4">
                <span className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ${accent}`}>
                    <Icon className="h-5 w-5" />
                </span>
                <div className="min-w-0">
                    <p className="text-xl font-bold text-[#0f172a]">{value}</p>
                    <p className="truncate text-[11px] uppercase tracking-wide text-[#5f7182]">{label}</p>
                </div>
            </CardContent>
        </Card>
    );
}

function InfoBlock({ label, value, full = false }) {
    return (
        <div className={full ? 'md:col-span-2' : ''}>
            <p className="text-xs text-slate-500">{label}</p>
            <p className="mt-1 text-sm font-semibold text-slate-900">{value || 'Non specifie'}</p>
        </div>
    );
}

function Field({ label, required = false, error = '', children, className = '' }) {
    return (
        <div className={`space-y-1.5 ${className}`.trim()}>
            <label className="block text-sm font-medium text-[#0f172a]">
                {label}
                {required ? <span className="ml-0.5 text-[#b42318]">*</span> : null}
            </label>
            {children}
            {error ? <p className="text-xs text-[#b42318]">{error}</p> : null}
        </div>
    );
}

function ComboboxField({ label, required = false, disabled = false, value, placeholder, options, open, onOpenChange, onSearchChange, searchValue, onSelect, emptyLabel }) {
    const selectedLabel = options.find((option) => String(option.value) === String(value))?.label ?? '';
    const containerRef = useRef(null);

    useEffect(() => {
        const handlePointerDown = (event) => {
            if (containerRef.current && !containerRef.current.contains(event.target)) {
                onOpenChange(false);
            }
        };

        document.addEventListener('mousedown', handlePointerDown);
        document.addEventListener('touchstart', handlePointerDown);

        return () => {
            document.removeEventListener('mousedown', handlePointerDown);
            document.removeEventListener('touchstart', handlePointerDown);
        };
    }, [onOpenChange]);

    return (
        <div ref={containerRef} className="relative w-full space-y-1.5">
            <label className="block text-sm font-medium text-[#0f172a]">
                {label}
                {required ? <span className="ml-0.5 text-[#b42318]">*</span> : null}
            </label>

            <div className="relative w-full">
                <div
                    className={cn(
                        'flex h-10 w-full items-center justify-between rounded-md border bg-white px-3 text-left transition',
                        disabled
                            ? 'cursor-not-allowed border-[#e2e8f0] bg-slate-50 text-slate-400'
                            : open
                                ? 'border-[#00559b]'
                                : 'border-[#c8d4de]'
                    )}
                    role="button"
                    tabIndex={disabled ? -1 : 0}
                    onClick={() => {
                        if (!disabled) onOpenChange(!open);
                    }}
                    onKeyDown={(event) => {
                        if (disabled) return;
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            onOpenChange(!open);
                        }
                    }}
                >
                    <div className="flex min-w-0 items-center gap-2">
                        <Search className={cn('h-4 w-4 shrink-0', disabled ? 'text-slate-300' : 'text-[#94a3b8]')} />
                        <span className={cn('truncate text-sm', selectedLabel ? 'text-[#0f172a]' : 'text-[#8798a5]', disabled && 'text-slate-400')}>
                            {selectedLabel || placeholder}
                        </span>
                    </div>

                    <div className="ml-3 flex items-center gap-2">
                        {!disabled && value ? (
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
                        <ChevronRight className={cn('h-4 w-4 shrink-0 rotate-90', disabled ? 'text-slate-300' : 'text-[#94a3b8]')} />
                    </div>
                </div>

                {open && !disabled ? (
                    <div className="absolute left-0 right-0 top-full z-30 mt-2 w-full overflow-hidden rounded-xl border border-[#c8d4de] bg-white shadow-lg">
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

function EmptyState({ title, desc }) {
    return (
        <div className="flex flex-col items-center justify-center gap-2 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-10 text-center">
            <Search className="h-6 w-6 text-slate-400" />
            <p className="text-sm font-semibold text-slate-900">{title}</p>
            <p className="max-w-sm text-sm text-slate-500">{desc}</p>
        </div>
    );
}

function CountrySelect({ value, onChange, options }) {
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const containerRef = useRef(null);

    const filtered = options.filter(
        (option) => !option.divider && option.label.toLowerCase().includes(query.toLowerCase())
    );

    useEffect(() => {
        function handleClickOutside(e) {
            if (containerRef.current && !containerRef.current.contains(e.target)) {
                setOpen(false);
                setQuery('');
            }
        }

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const Flag = value ? flags[value] : null;

    return (
        <div className="relative shrink-0" ref={containerRef}>
            <button
                type="button"
                onClick={() => setOpen((o) => !o)}
                className="flex h-full items-center gap-1.5 rounded-l-md border-r border-[#c8d4de] bg-white px-2.5 text-sm text-[#5f7182] transition-colors hover:bg-[#f8fafc]"
            >
                {Flag ? <Flag title={value} className="h-4 w-5 rounded-sm object-cover" /> : <span className="h-4 w-5" />}
                <ChevronRight className="h-3.5 w-3.5 rotate-90" />
            </button>

            {open ? (
                <div className="absolute left-0 top-[calc(100%+4px)] z-50 w-64 overflow-hidden rounded-md border border-[#c8d4de] bg-white shadow-lg">
                    <div className="flex items-center gap-2 border-b border-[#e2e8f0] px-2.5 py-2">
                        <input
                            autoFocus
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder="Rechercher un pays..."
                            className="w-full border-0 bg-transparent text-sm text-[#0f172a] outline-none placeholder:text-[#94a3b8]"
                        />
                    </div>
                    <ul className="max-h-60 overflow-y-auto py-1">
                        {filtered.length === 0 ? (
                            <li className="px-3 py-2 text-sm text-[#94a3b8]">Aucun résultat</li>
                        ) : (
                            filtered.map((option) => {
                                const CFlag = flags[option.value];
                                return (
                                    <li key={option.value}>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                onChange(option.value);
                                                setOpen(false);
                                                setQuery('');
                                            }}
                                            className={cn(
                                                'flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-[#eaf4fb]',
                                                value === option.value ? 'bg-[#eaf4fb]' : ''
                                            )}
                                        >
                                            {CFlag ? (
                                                <CFlag className="h-4 w-5 shrink-0 rounded-sm object-cover" />
                                            ) : (
                                                <span className="h-4 w-5 shrink-0" />
                                            )}
                                            <span className="flex-1 truncate text-[#0f172a]">{option.label}</span>
                                            {value === option.value ? <Check className="h-3.5 w-3.5 shrink-0 text-[#00559b]" /> : null}
                                        </button>
                                    </li>
                                );
                            })
                        )}
                    </ul>
                </div>
            ) : null}
        </div>
    );
}

function PhoneInput({ label, required = false, error = '', value, onChange, placeholder }) {
    return (
        <Field label={label} required={required} error={error}>
            <PhoneInputBase
                international
                defaultCountry="CI"
                countrySelectComponent={CountrySelect}
                value={value}
                onChange={(val) => onChange({ target: { value: val ?? '' } })}
                placeholder={placeholder}
                className={cn(
                    'phone-input-custom flex h-10 items-stretch rounded-md border border-[#c8d4de] bg-white shadow-sm transition-colors',
                    'focus-within:border-[#00559b] focus-within:ring-2 focus-within:ring-[#00559b]/20'
                )}
            />
        </Field>
    );
}

const today = () => new Date().toISOString().slice(0, 10);

const blankMaintenancierForm = () => ({
    fonction_maintenance_id: '',
    entreprise: '',
    name: '',
    tel1: '',
    tel2: '',
    email: '',
    adresse: '',
    type_piece_id: '',
    numero_piece: '',
    date_validite_piece: '',
    statut: '1',
});

const blankFonctionForm = () => ({
    name: '',
    description: '',
});

const blankTypeForm = () => ({
    name: '',
    maintenance_category_id: '',
    description: '',
    duree_estimee: '',
});

const typeFormFromItem = (item) => ({
    name: item?.name ?? '',
    maintenance_category_id: item?.maintenance_category_id ?? '',
    description: item?.description ?? '',
    duree_estimee: item?.duree_estimee ?? '',
});

const blankInterventionAction = () => ({
    id: crypto.randomUUID(),
    maintenance_category_id: 'all',
    type_intervention_id: '',
    maintenancier_id: '',
    date_debut: today(),
    date_fin: '',
    priorite: 'normale',
    prix: '',
    description: '',
});

const blankInterventionForm = () => ({
    titre: '',
    proprietaire_id: '',
    lot_id: '',
    propriete_id: '',
    batiment_id: '',
    porte_id: '',
    prise_en_charge_par: 'proprietaire',
    actions: [blankInterventionAction()],
});

const interventionFormFromItem = (item) => ({
    titre: item?.titre ?? '',
    proprietaire_id: item?.proprietaire_id ?? '',
    lot_id: '',
    propriete_id: '',
    batiment_id: '',
    porte_id: '',
    prise_en_charge_par: item?.prise_en_charge_par ?? 'proprietaire',
    actions: asArray(item?.details).length
        ? asArray(item.details).map((detail) => ({
              id: detail?.maintenance_detail_id ?? detail?.id ?? crypto.randomUUID(),
              maintenance_category_id: String(
                  detail?.typeIntervention?.maintenance_category_id ??
                      detail?.type_intervention?.maintenance_category_id ??
                      detail?.maintenance_category_id ??
                      'all'
              ),
              type_intervention_id: String(detail?.type_intervention_id ?? detail?.typeIntervention?.type_maintenance_id ?? ''),
              maintenancier_id: String(detail?.maintenancier_id ?? detail?.maintenancier?.maintenancier_id ?? ''),
              date_debut: toDateInput(detail?.date_debut) || today(),
              date_fin: toDateInput(detail?.date_fin),
              priorite: detail?.priorite ?? 'normale',
              prix: detail?.montant ?? detail?.prix ?? '',
              description: detail?.description ?? detail?.note ?? '',
          }))
        : [blankInterventionAction()],
});

export default function Index({
    maintenances = [],
    maintenanciers = [],
    typesMaintenance = [],
    fonctionsMaintenance = [],
    proprietaires = [],
    typesInterventionStatiques = [],
    maintenancierStatiques = [],
    typePiece = [],
    maintenanceCategories = [],
    lots = [],
    proprietes = [],
    batiments = [],
    portes = [],
}) {
    const maintenanceRows = useMemo(() => asArray(maintenances).map(normalizeMaintenance), [maintenances]);
    const maintenancierRows = useMemo(() => asArray(maintenanciers).map(normalizeMaintenancier), [maintenanciers]);
    const typeRows = useMemo(() => asArray(typesMaintenance).map(normalizeType), [typesMaintenance]);
    const fonctionRows = useMemo(() => asArray(fonctionsMaintenance).map(normalizeFonction), [fonctionsMaintenance]);
    const currentAgenceId = String(usePage()?.props?.auth?.user?.agence_id ?? '');

    const detailRows = useMemo(() => {
        return maintenanceRows.flatMap((maintenance) =>
            asArray(maintenance.details).map((detail) => ({
                ...detail,
                maintenance_id: maintenance.maintenance_id,
                maintenance_titre: maintenance.titre,
                maintenance_statut: maintenance.statut,
            }))
        );
    }, [maintenanceRows]);

    const [section, setSection] = useState(() => sessionStorage.getItem('maintenance.active-section') ?? 'maintenanciers');
    const [query, setQuery] = useState('');
    const [typeCategoryFilter, setTypeCategoryFilter] = useState('all');
    const [selectedMaintenancierId, setSelectedMaintenancierId] = useState('');
    const [selectedMaintenanceId, setSelectedMaintenanceId] = useState('');
    const [maintenancierPage, setMaintenancierPage] = useState(1);
    const [maintenancierFonctionFilter, setMaintenancierFonctionFilter] = useState('all');
    const [maintenancierAvailabilityFilter, setMaintenancierAvailabilityFilter] = useState('all');
    const [maintenancierSort, setMaintenancierSort] = useState('name_asc');
    const [maintenancierDetailTab, setMaintenancierDetailTab] = useState('infos');
    const [typePage, setTypePage] = useState(1);
    const [maintenanceStatusFilter, setMaintenanceStatusFilter] = useState('all');
    const [maintenanceSort, setMaintenanceSort] = useState('date_desc');
    const [maintenancePage, setMaintenancePage] = useState(1);
    const [creationModalOpen, setCreationModalOpen] = useState(false);
    const [activeForm, setActiveForm] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const [formFeedback, setFormFeedback] = useState(null);
    const [deleteTarget, setDeleteTarget] = useState(null);
    const [deleteSubmitting, setDeleteSubmitting] = useState(false);
    const [availabilityTarget, setAvailabilityTarget] = useState(null);
    const [availabilitySubmitting, setAvailabilitySubmitting] = useState(false);
    const [statusTarget, setStatusTarget] = useState(null);
    const [statusSubmitting, setStatusSubmitting] = useState(false);
    const [maintenancierFormMode, setMaintenancierFormMode] = useState('create');
    const [maintenancierFormId, setMaintenancierFormId] = useState('');
    const [fonctionFormMode, setFonctionFormMode] = useState('create');
    const [fonctionFormId, setFonctionFormId] = useState('');
    const [typeFormMode, setTypeFormMode] = useState('create');
    const [typeFormId, setTypeFormId] = useState('');
    const [interventionFormMode, setInterventionFormMode] = useState('create');
    const [interventionFormId, setInterventionFormId] = useState('');
    const [maintenancierForm, setMaintenancierForm] = useState(blankMaintenancierForm());
    const [fonctionForm, setFonctionForm] = useState(blankFonctionForm());
    const [typeForm, setTypeForm] = useState(blankTypeForm());
    const [interventionForm, setInterventionForm] = useState(blankInterventionForm());
    const [proprietaireSearch, setProprietaireSearch] = useState('');
    const [proprietaireOpen, setProprietaireOpen] = useState(false);
    const [maintenancierSearch, setMaintenancierSearch] = useState('');
    const [maintenancierOpen, setMaintenancierOpen] = useState(false);
    const [maintenancierTargetId, setMaintenancierTargetId] = useState('');
    const [lotSearch, setLotSearch] = useState('');
    const [lotOpen, setLotOpen] = useState(false);
    const [proprieteSearch, setProprieteSearch] = useState('');
    const [proprieteOpen, setProprieteOpen] = useState(false);
    const [batimentSearch, setBatimentSearch] = useState('');
    const [batimentOpen, setBatimentOpen] = useState(false);
    const [porteSearch, setPorteSearch] = useState('');
    const [porteOpen, setPorteOpen] = useState(false);

    const formatProprietorLabel = (item) => {
        const name = item?.name ?? item?.nom ?? 'Sans nom';
        const phone = item?.tel1 ?? item?.telephone ?? item?.phone ?? '';

        return phone ? `${name} - ${phone}` : name;
    };

    const formatLotLabel = (item) => {
        const name = item?.name ?? item?.num_lot ?? 'Sans nom';
        const area = item?.adresse ?? item?.num_ilot ?? '';

        return area ? `${name} - ${area}` : name;
    };

    const formatProprieteLabel = (item) => {
        const reference = item?.reference ?? item?.description ?? 'Sans nom';
        const location = item?.adresse_complete ?? '';

        return location ? `${reference} - ${location}` : reference;
    };

    const formatBatimentLabel = (item) => {
        const name = item?.name ?? 'Sans nom';
        const propriete = item?.propriete?.reference ?? item?.propriete?.description ?? '';

        return propriete ? `${name} - ${propriete}` : name;
    };

    const formatPorteLabel = (item) => {
        const numero = item?.numero_porte ?? item?.name ?? 'Porte';
        const batiment = item?.batiment?.name ?? '';

        return batiment ? `${numero} - ${batiment}` : numero;
    };

    const renderBadgeStack = (badges) => {
        const visibleBadges = badges.filter(Boolean);

        if (!visibleBadges.length) {
            return null;
        }

        if (visibleBadges.length <= 2) {
            return (
                <div className="flex w-fit flex-wrap items-center gap-1">
                    {visibleBadges.map((badge, index) => (
                        <Badge key={`${badge.label}-${index}`} variant={badge.variant}>
                            {badge.label}
                        </Badge>
                    ))}
                </div>
            );
        }

        return (
            <div className="flex w-fit flex-wrap items-center gap-1">
                {visibleBadges.map((badge, index) => (
                    <div key={`${badge.label}-${index}`} className={index === 2 ? 'basis-full' : ''}>
                        <Badge variant={badge.variant}>{badge.label}</Badge>
                    </div>
                ))}
            </div>
        );
    };

    useEffect(() => {
        sessionStorage.setItem('maintenance.active-section', section);
    }, [section]);

    const proprietorOptions = useMemo(
        () =>
            asArray(proprietaires?.data ?? proprietaires).map((item) => ({
                value: String(item?.proprietaire_id ?? item?.id ?? ''),
                label: formatProprietorLabel(item),
            })),
        [proprietaires]
    );

    const searchableProprietorOptions = useMemo(() => {
        const term = proprietaireSearch.trim().toLowerCase();

        if (!term) {
            return proprietorOptions;
        }

        return proprietorOptions.filter((item) => item.label.toLowerCase().includes(term));
    }, [proprietaireSearch, proprietorOptions]);

    const lotOptions = useMemo(
        () =>
            asArray(lots).map((item) => ({
                value: String(item?.propreietaire_lot_id ?? item?.id ?? ''),
                label: formatLotLabel(item),
                proprietaire_id: String(item?.proprietaire_id ?? ''),
            })),
        [lots]
    );

    const proprieteOptions = useMemo(
        () =>
            asArray(proprietes).map((item) => ({
                value: String(item?.propriete_id ?? item?.id ?? ''),
                label: formatProprieteLabel(item),
                lot_id: String(item?.lot_id ?? ''),
                proprietaire_id: String(item?.proprietaire_id ?? ''),
            })),
        [proprietes]
    );

    const batimentOptions = useMemo(
        () =>
            asArray(batiments).map((item) => ({
                value: String(item?.batiment_id ?? item?.id ?? ''),
                label: formatBatimentLabel(item),
                propriete_id: String(item?.propriete_id ?? item?.propriete?.propriete_id ?? ''),
            })),
        [batiments]
    );

    const porteOptions = useMemo(
        () =>
            asArray(portes).map((item) => ({
                value: String(item?.porte_id ?? item?.id ?? ''),
                label: formatPorteLabel(item),
                batiment_id: String(item?.batiment_id ?? item?.batiment?.batiment_id ?? ''),
                propriete_id: String(item?.batiment?.propriete_id ?? item?.batiment?.propriete?.propriete_id ?? ''),
            })),
        [portes]
    );

    const typePieceOptions = useMemo(
        () =>
            asArray(typePiece).map((item) => ({
                value: String(item?.type_pieces_id ?? item?.type_piece_id ?? item?.id ?? ''),
                label: item?.name ?? item?.libelle ?? 'Sans nom',
            })),
        [typePiece]
    );

    const maintenanceCategoryOptions = useMemo(
        () =>
            asArray(maintenanceCategories).map((item) => ({
                value: String(item?.maintenance_category_id ?? item?.id ?? ''),
                label: item?.name ?? 'Sans nom',
            })),
        [maintenanceCategories]
    );

    const maintenancierOptions = useMemo(
        () =>
            asArray(maintenancierStatiques.length ? maintenancierStatiques : maintenancierRows).map((item) => ({
                value: String(item?.maintenancier_id ?? item?.id ?? ''),
                label: `${item?.name ?? 'Sans nom'}${item?.tel1 || item?.tel2 ? ` - ${item?.tel1 ?? item?.tel2}` : ''}`,
            })),
        [maintenancierRows, maintenancierStatiques]
    );

    const searchableMaintenancierOptions = useMemo(() => {
        const term = maintenancierSearch.trim().toLowerCase();

        if (!term) {
            return maintenancierOptions;
        }

        return maintenancierOptions.filter((item) => item.label.toLowerCase().includes(term));
    }, [maintenancierOptions, maintenancierSearch]);

    const searchableLotOptions = useMemo(() => {
        const term = lotSearch.trim().toLowerCase();

        let rows = lotOptions;

        if (interventionForm.proprietaire_id) {
            rows = rows.filter((item) => String(item.proprietaire_id ?? '') === String(interventionForm.proprietaire_id));
        }

        if (term) {
            rows = rows.filter((item) => item.label.toLowerCase().includes(term));
        }

        return rows;
    }, [interventionForm.proprietaire_id, lotOptions, lotSearch]);

    const searchableProprieteOptions = useMemo(() => {
        const term = proprieteSearch.trim().toLowerCase();

        let rows = proprieteOptions;

        if (interventionForm.proprietaire_id) {
            rows = rows.filter((item) => String(item.proprietaire_id ?? '') === String(interventionForm.proprietaire_id));
        }

        if (interventionForm.lot_id) {
            rows = rows.filter((item) => String(item.lot_id ?? '') === String(interventionForm.lot_id));
        }

        if (term) {
            rows = rows.filter((item) => item.label.toLowerCase().includes(term));
        }

        return rows;
    }, [interventionForm.lot_id, interventionForm.proprietaire_id, proprieteOptions, proprieteSearch]);

    const searchableBatimentOptions = useMemo(() => {
        const term = batimentSearch.trim().toLowerCase();

        let rows = batimentOptions;

        if (interventionForm.propriete_id) {
            rows = rows.filter((item) => String(item.propriete_id ?? '') === String(interventionForm.propriete_id));
        }

        if (term) {
            rows = rows.filter((item) => item.label.toLowerCase().includes(term));
        }

        return rows;
    }, [batimentOptions, batimentSearch, interventionForm.propriete_id]);

    const searchablePorteOptions = useMemo(() => {
        const term = porteSearch.trim().toLowerCase();

        let rows = porteOptions;

        if (interventionForm.batiment_id) {
            rows = rows.filter((item) => String(item.batiment_id ?? '') === String(interventionForm.batiment_id));
        } else if (interventionForm.propriete_id) {
            rows = rows.filter((item) => String(item.propriete_id ?? '') === String(interventionForm.propriete_id));
        }

        if (term) {
            rows = rows.filter((item) => item.label.toLowerCase().includes(term));
        }

        return rows;
    }, [interventionForm.batiment_id, interventionForm.propriete_id, porteOptions, porteSearch]);

    const typeInterventionOptions = useMemo(
        () =>
            asArray(typesInterventionStatiques.length ? typesInterventionStatiques : typeRows).map((item) => ({
                value: String(item?.type_maintenance_id ?? item?.id ?? ''),
                label: item?.name ?? 'Sans nom',
                maintenance_category_id: String(item?.maintenance_category_id ?? ''),
            })),
        [typeRows, typesInterventionStatiques]
    );

    const getTypeInterventionOptionsByCategory = (categoryId) => {
        if (!categoryId || categoryId === 'all') {
            return typeInterventionOptions;
        }

        return typeInterventionOptions.filter((item) => String(item.maintenance_category_id ?? '') === String(categoryId));
    };

    const filteredMaintenanciers = useMemo(() => {
        const term = query.trim().toLowerCase();

        let rows = maintenancierRows.filter((item) => {
            if (currentAgenceId && item.agence_id && item.agence_id !== currentAgenceId) return false;

            const matchesFonction =
                maintenancierFonctionFilter === 'all' ||
                String(item.fonction_maintenance_id ?? '') === maintenancierFonctionFilter;

            if (!matchesFonction) return false;

            const matchesAvailability =
                maintenancierAvailabilityFilter === 'all' ||
                (maintenancierAvailabilityFilter === 'disponible' && item.statut) ||
                (maintenancierAvailabilityFilter === 'indisponible' && !item.statut);

            if (!matchesAvailability) return false;

            if (!term) return true;

            const haystack = [
                item.name,
                item.entreprise,
                item.email,
                item.tel1,
                item.tel2,
                item.fonction,
                item.adresse,
            ]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            return haystack.includes(term);
        });

        rows = [...rows].sort((a, b) => {
            if (maintenancierSort === 'interventions_desc') {
                return Number(b.interventions_count ?? 0) - Number(a.interventions_count ?? 0);
            }
            if (maintenancierSort === 'fonction') {
                return String(a.fonction ?? '').localeCompare(String(b.fonction ?? ''));
            }
            if (maintenancierSort === 'piece_expiree') {
                return Number(isPieceExpired(b)) - Number(isPieceExpired(a));
            }
            return String(a.name ?? '').localeCompare(String(b.name ?? ''));
        });

        return rows;
    }, [currentAgenceId, maintenancierRows, query, maintenancierFonctionFilter, maintenancierAvailabilityFilter, maintenancierSort]);

    const filteredMaintenanceRows = useMemo(() => {
        const term = query.trim().toLowerCase();

        let rows = maintenanceRows.filter((item) => {
            if (currentAgenceId && item.agence_id && item.agence_id !== currentAgenceId) return false;

            const matchesStatus =
                maintenanceStatusFilter === 'all' ||
                String(item.statut ?? '').toLowerCase().includes(maintenanceStatusFilter);

            if (!matchesStatus) return false;

            if (!term) return true;

            const haystack = [
                item.titre,
                item.description,
                item.proprietaire,
                item.propriete,
                item.lot,
                item.batiment,
                item.porte,
                item.prise_en_charge_par,
                item.statut,
            ]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            return haystack.includes(term);
        });

        rows = [...rows].sort((a, b) => {
            if (maintenanceSort === 'date_asc') {
                return new Date(a.date_debut ?? 0) - new Date(b.date_debut ?? 0);
            }
            if (maintenanceSort === 'montant_desc') {
                return Number(b.montant_global ?? 0) - Number(a.montant_global ?? 0);
            }
            if (maintenanceSort === 'priorite') {
                const order = { haute: 0, normale: 1, basse: 2 };
                const aDetail = a.details?.[0]?.priorite ?? 'normale';
                const bDetail = b.details?.[0]?.priorite ?? 'normale';
                return (order[aDetail] ?? 1) - (order[bDetail] ?? 1);
            }
            return new Date(b.date_debut ?? 0) - new Date(a.date_debut ?? 0);
        });

        return rows;
    }, [currentAgenceId, maintenanceRows, query, maintenanceStatusFilter, maintenanceSort]);

    const filteredTypeRows = useMemo(() => {
        const term = query.trim().toLowerCase();
        const category = typeCategoryFilter.trim();

        return typeRows.filter((item) => {
            if (currentAgenceId && item.agence_id && item.agence_id !== currentAgenceId) return false;

            const haystack = [item.name, item.category_name, item.description]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            const matchesCategory =
                category === 'all' || String(item.maintenance_category_id ?? '') === category || String(item.category_name ?? '').toLowerCase() === category;

            return matchesCategory && (!term || haystack.includes(term));
        });
    }, [currentAgenceId, typeRows, query, typeCategoryFilter]);

    const typePageSize = 10;
    const typePageCount = Math.max(1, Math.ceil(filteredTypeRows.length / typePageSize));
    const paginatedTypeRows = useMemo(
        () => filteredTypeRows.slice((typePage - 1) * typePageSize, typePage * typePageSize),
        [filteredTypeRows, typePage]
    );

    const maintenancierPageSize = 5;
    const maintenancierPageCount = Math.max(1, Math.ceil(filteredMaintenanciers.length / maintenancierPageSize));
    const paginatedMaintenancierRows = useMemo(
        () => filteredMaintenanciers.slice((maintenancierPage - 1) * maintenancierPageSize, maintenancierPage * maintenancierPageSize),
        [filteredMaintenanciers, maintenancierPage]
    );

    const maintenancePageSize = 8;
    const maintenancePageCount = Math.max(1, Math.ceil(filteredMaintenanceRows.length / maintenancePageSize));
    const paginatedMaintenanceRows = useMemo(
        () => filteredMaintenanceRows.slice((maintenancePage - 1) * maintenancePageSize, maintenancePage * maintenancePageSize),
        [filteredMaintenanceRows, maintenancePage]
    );

    const filteredFonctionRows = useMemo(() => {
        const term = query.trim().toLowerCase();

        return fonctionRows.filter((item) => {
            if (currentAgenceId && item.agence_id && item.agence_id !== currentAgenceId) return false;

            if (!term) return true;

            const haystack = [item.name, item.description]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            return haystack.includes(term);
        });
    }, [currentAgenceId, fonctionRows, query]);

    const sectionConfig = useMemo(() => {
        return {
            maintenanciers: {
                label: 'Maintenanciers',
                count: filteredMaintenanciers.length,
                icon: Users,
            },
            interventions: {
                label: 'Interventions',
                count: filteredMaintenanceRows.length,
                icon: ClipboardList,
            },
            fonctions: {
                label: 'Fonctions',
                count: filteredFonctionRows.length,
                icon: ShieldCheck,
            },
            types: {
                label: 'Types',
                count: filteredTypeRows.length,
                icon: Tag,
            },
        };
    }, [filteredFonctionRows.length, filteredMaintenanceRows.length, filteredMaintenanciers.length, filteredTypeRows.length]);

    const sectionCreateMap = {
        maintenanciers: 'maintenancier',
        interventions: 'intervention',
        fonctions: 'fonction',
        types: 'type',
    };

    const currentRows = {
        maintenanciers: paginatedMaintenancierRows,
        interventions: paginatedMaintenanceRows,
        fonctions: filteredFonctionRows,
        types: filteredTypeRows,
    }[section];

    useEffect(() => {
        if (section !== 'maintenanciers') return;

        setMaintenancierPage(1);
    }, [query, section, maintenancierFonctionFilter, maintenancierAvailabilityFilter, maintenancierSort]);

    useEffect(() => {
        if (section !== 'types') return;

        setTypePage(1);
    }, [query, section, typeCategoryFilter]);

    useEffect(() => {
        if (section !== 'interventions') return;

        setMaintenancePage(1);
    }, [query, section, maintenanceStatusFilter, maintenanceSort]);

    useEffect(() => {
        if (typePage > typePageCount) {
            setTypePage(typePageCount);
        }
    }, [typePage, typePageCount]);

    useEffect(() => {
        if (maintenancierPage > maintenancierPageCount) {
            setMaintenancierPage(maintenancierPageCount);
        }
    }, [maintenancierPage, maintenancierPageCount]);

    useEffect(() => {
        if (maintenancePage > maintenancePageCount) {
            setMaintenancePage(maintenancePageCount);
        }
    }, [maintenancePage, maintenancePageCount]);

    useEffect(() => {
        if (!currentRows.length) return;

        if (section === 'maintenanciers' && !currentRows.some((item) => item.id === selectedMaintenancierId)) {
            setSelectedMaintenancierId(currentRows[0].id);
        }

        if (section === 'interventions' && !currentRows.some((item) => item.id === selectedMaintenanceId)) {
            setSelectedMaintenanceId(currentRows[0].id);
        }

    }, [
        currentRows,
        section,
        selectedMaintenanceId,
        selectedMaintenancierId,
    ]);

    useEffect(() => {
        if (section === 'maintenanciers' && selectedMaintenancier) {
            setMaintenancierDetailTab('infos');
        }
    }, [section, selectedMaintenancierId]);

    const selectedMaintenancier =
        paginatedMaintenancierRows.find((item) => item.id === selectedMaintenancierId) ??
        paginatedMaintenancierRows[0] ??
        filteredMaintenanciers.find((item) => item.id === selectedMaintenancierId) ??
        filteredMaintenanciers[0] ??
        maintenancierRows[0] ??
        null;

    const selectedMaintenance =
        paginatedMaintenanceRows.find((item) => item.id === selectedMaintenanceId) ??
        paginatedMaintenanceRows[0] ??
        filteredMaintenanceRows.find((item) => item.id === selectedMaintenanceId) ??
        filteredMaintenanceRows[0] ??
        maintenanceRows[0] ??
        null;

    const selectedMaintenancierInterventions = useMemo(() => {
        const maintenancierId = String(selectedMaintenancier?.id ?? selectedMaintenancierId ?? '');

        return detailRows
            .filter((detail) => String(detail.maintenancier_id ?? '') === maintenancierId)
            .slice()
            .sort((a, b) => {
                const aDate = new Date(a.date_debut ?? a.created_at ?? 0).getTime();
                const bDate = new Date(b.date_debut ?? b.created_at ?? 0).getTime();
                return Number.isNaN(bDate) - Number.isNaN(aDate) || bDate - aDate;
            });
    }, [detailRows, selectedMaintenancier?.id, selectedMaintenancierId]);

    const totalMaintenanciers = maintenancierRows.length;
    const totalMaintenance = maintenanceRows.length;
    const enCours = maintenanceRows.filter((item) => String(item.statut).includes('cours')).length;
    const totalTypes = typeRows.length;

    const cards = [
        { label: 'Maintenanciers', value: totalMaintenanciers, icon: UserRound, accent: ACCENT_GREEN },
        { label: 'Interventions', value: totalMaintenance, icon: ClipboardList, accent: ACCENT_BLUE },
        { label: 'En cours', value: enCours, icon: Clock3, accent: ACCENT_SLATE },
        { label: 'Types', value: totalTypes, icon: Tag, accent: ACCENT_ORANGE },
    ];

    const openCreationForm = (formKey) => {
        setFormFeedback(null);
        setCreationModalOpen(false);
        setActiveForm(formKey);

        if (formKey === 'maintenancier') {
            setMaintenancierFormMode('create');
            setMaintenancierFormId('');
            setMaintenancierForm(blankMaintenancierForm());
        }
        if (formKey === 'fonction') {
            setFonctionFormMode('create');
            setFonctionFormId('');
            setFonctionForm(blankFonctionForm());
        }
        if (formKey === 'type') {
            setTypeFormMode('create');
            setTypeFormId('');
            setTypeForm(blankTypeForm());
        }
        if (formKey === 'intervention') {
            setInterventionFormMode('create');
            setInterventionFormId('');
            setInterventionForm(blankInterventionForm());
            setProprietaireSearch('');
            setProprietaireOpen(false);
            setMaintenancierSearch('');
            setMaintenancierOpen(false);
            setMaintenancierTargetId('');
            setLotSearch('');
            setLotOpen(false);
            setProprieteSearch('');
            setProprieteOpen(false);
            setBatimentSearch('');
            setBatimentOpen(false);
            setPorteSearch('');
            setPorteOpen(false);
        }
    };

    const typeRowById = (id) => filteredTypeRows.find((item) => item.id === id) ?? typeRows.find((item) => item.id === id) ?? null;

    const updateInterventionAction = (actionId, patch) => {
        setInterventionForm((current) => ({
            ...current,
            actions: current.actions.map((action) => (action.id === actionId ? { ...action, ...patch } : action)),
        }));
    };

    const addInterventionAction = () => {
        setInterventionForm((current) => ({
            ...current,
            actions: [...current.actions, blankInterventionAction()],
        }));
    };

    const removeInterventionAction = (actionId) => {
        setInterventionForm((current) => ({
            ...current,
            actions:
                current.actions.length > 1
                    ? current.actions.filter((action) => action.id !== actionId)
                    : [blankInterventionAction()],
        }));
    };

    const maintenancierFormFromItem = (item) => ({
        fonction_maintenance_id: item?.fonction_maintenance_id ?? item?.fonction?.fonction_maintenance_id ?? '',
        entreprise: item?.entreprise ?? '',
        name: item?.name ?? '',
        tel1: item?.tel1 ?? '',
        tel2: item?.tel2 ?? '',
        email: item?.email ?? '',
        adresse: item?.adresse ?? '',
        type_piece_id: item?.type_piece_id ?? '',
        numero_piece: item?.numero_piece ?? '',
        date_validite_piece: toDateInput(item?.date_validite_piece),
        statut: item?.statut ? '1' : '0',
    });

    const fonctionRowFromItem = (item) => ({
        name: item?.name ?? '',
        description: item?.description ?? '',
    });

    const openMaintenancierEdit = (item) => {
        setFormFeedback(null);
        setCreationModalOpen(false);
        setMaintenancierFormMode('edit');
        setMaintenancierFormId(item.id);
        setMaintenancierForm(maintenancierFormFromItem(item));
        setActiveForm('maintenancier');
    };

    const openFonctionEdit = (item) => {
        setFormFeedback(null);
        setCreationModalOpen(false);
        setFonctionFormMode('edit');
        setFonctionFormId(item.id);
        setFonctionForm(fonctionRowFromItem(item));
        setActiveForm('fonction');
    };

    const openTypeEdit = (item) => {
        setFormFeedback(null);
        setCreationModalOpen(false);
        setTypeFormMode('edit');
        setTypeFormId(item.id);
        setTypeForm(typeFormFromItem(item));
        setActiveForm('type');
    };

    const openMaintenanceEdit = (item) => {
        setFormFeedback(null);
        setCreationModalOpen(false);
        setInterventionFormMode('edit');
        setInterventionFormId(item.id);
        setInterventionForm(interventionFormFromItem(item));
        setProprietaireSearch('');
        setProprietaireOpen(false);
        setMaintenancierSearch('');
        setMaintenancierOpen(false);
        setLotSearch('');
        setLotOpen(false);
        setProprieteSearch('');
        setProprieteOpen(false);
        setBatimentSearch('');
        setBatimentOpen(false);
        setPorteSearch('');
        setPorteOpen(false);
        setActiveForm('intervention');
    };

    const deleteType = async (item) => {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const response = await fetch(`/agence/maintenance/type/${item.id}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            const contentType = response.headers.get('content-type') ?? '';
            const payload = contentType.includes('application/json') ? await response.json().catch(() => null) : null;

            if (!response.ok || payload?.success === false) {
                throw new Error(payload?.message ?? 'Suppression impossible.');
            }

            return true;
        } catch (error) {
            setFormFeedback({ type: 'error', message: error.message || 'Une erreur est survenue.' });
            return false;
        }
    };

    const deleteFonction = async (item) => {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const response = await fetch(`/agence/maintenance/fonction/${item.id}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            const contentType = response.headers.get('content-type') ?? '';
            const payload = contentType.includes('application/json') ? await response.json().catch(() => null) : null;

            if (!response.ok || payload?.success === false) {
                throw new Error(payload?.message ?? 'Suppression impossible.');
            }

            return true;
        } catch (error) {
            setFormFeedback({ type: 'error', message: error.message || 'Une erreur est survenue.' });
            return false;
        }
    };

    const deleteMaintenancier = async (item) => {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const response = await fetch(`/agence/maintenance/maintenancier/${item.id}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            const contentType = response.headers.get('content-type') ?? '';
            const payload = contentType.includes('application/json') ? await response.json().catch(() => null) : null;

            if (!response.ok || payload?.success === false) {
                throw new Error(payload?.message ?? 'Suppression impossible.');
            }

            return true;
        } catch (error) {
            setFormFeedback({ type: 'error', message: error.message || 'Une erreur est survenue.' });
            return false;
        }
    };

    const deleteMaintenance = async (item) => {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const response = await fetch(`/agence/maintenance/${item.id}`, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            const contentType = response.headers.get('content-type') ?? '';
            const payload = contentType.includes('application/json') ? await response.json().catch(() => null) : null;

            if (!response.ok || payload?.success === false) {
                throw new Error(payload?.message ?? 'Suppression impossible.');
            }

            return true;
        } catch (error) {
            setFormFeedback({ type: 'error', message: error.message || 'Une erreur est survenue.' });
            return false;
        }
    };

    const toggleMaintenancierAvailability = async (item) => {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const nextStatut = item.statut ? '0' : '1';
            const response = await fetch(`/agence/maintenance/maintenancier/${item.id}/statut`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ statut: nextStatut }),
            });

            const contentType = response.headers.get('content-type') ?? '';
            const payload = contentType.includes('application/json') ? await response.json().catch(() => null) : null;

            if (!response.ok || payload?.success === false) {
                throw new Error(payload?.message ?? 'Mise à jour de la disponibilité impossible.');
            }

            window.location.reload();
        } catch (error) {
            setFormFeedback({ type: 'error', message: error.message || 'Une erreur est survenue.' });
        }
    };

    const updateMaintenanceStatus = async (item, statut) => {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const isDetail = Boolean(item?.maintenance_detail_id ?? item?.type_intervention_id ?? item?.maintenancier_id);
            const endpoint = isDetail
                ? `/agence/maintenance/detail/${item.maintenance_detail_id ?? item.id}/statut`
                : `/agence/maintenance/${item.id}/statut`;
            const response = await fetch(endpoint, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ statut }),
            });

            const contentType = response.headers.get('content-type') ?? '';
            const payload = contentType.includes('application/json') ? await response.json().catch(() => null) : null;

            if (!response.ok || payload?.success === false) {
                throw new Error(payload?.message ?? 'Mise à jour du statut impossible.');
            }

            window.location.reload();
            return true;
        } catch (error) {
            setFormFeedback({ type: 'error', message: error.message || 'Une erreur est survenue.' });
            return false;
        }
    };

    const openAvailabilityConfirm = (item) => {
        setFormFeedback(null);
        setAvailabilityTarget(item);
    };

    const closeAvailabilityConfirm = () => {
        if (availabilitySubmitting) return;
        setAvailabilityTarget(null);
    };

    const confirmAvailabilityChange = async () => {
        if (!availabilityTarget || availabilitySubmitting) return;

        setAvailabilitySubmitting(true);
        const ok = await toggleMaintenancierAvailability(availabilityTarget);
        if (ok !== false) {
            setAvailabilityTarget(null);
        }
        setAvailabilitySubmitting(false);
    };

    const getNextMaintenanceStep = (status) => {
        const value = String(status ?? '').toLowerCase();

        if (value.includes('annul') || value.includes('term')) {
            return null;
        }

        if (value.includes('cours') || value.includes('plan')) {
            return {
                label: 'Marquer terminé',
                nextStatus: 'terminer',
            };
        }

        return {
            label: 'Passer à en cours',
            nextStatus: 'en_cours',
        };
    };

    const getCancelMaintenanceAction = (status) => {
        const value = String(status ?? '').toLowerCase();

        if (value.includes('annul') || value.includes('term')) {
            return null;
        }

        return {
            label: 'Annuler',
            nextStatus: 'annuler',
        };
    };

    const openStatusConfirm = (item, step = null) => {
        const nextStep = step ?? getNextMaintenanceStep(item?.statut) ?? getCancelMaintenanceAction(item?.statut);

        if (!nextStep) {
            return;
        }

        setFormFeedback(null);
        setStatusTarget({
            item,
            nextStatus: nextStep.nextStatus,
            label: nextStep.label,
        });
    };

    const closeStatusConfirm = () => {
        if (statusSubmitting) return;
        setStatusTarget(null);
    };

    const confirmStatusChange = async () => {
        if (!statusTarget?.item || statusSubmitting) return;

        setStatusSubmitting(true);
        const ok = await updateMaintenanceStatus(statusTarget.item, statusTarget.nextStatus);
        if (ok !== false) {
            setStatusTarget(null);
        }
        setStatusSubmitting(false);
    };

    const openDeleteConfirm = (type, item) => {
        setFormFeedback(null);
        setDeleteTarget({ type, item });
    };

    const closeDeleteConfirm = () => {
        if (deleteSubmitting) return;
        setDeleteTarget(null);
    };

    const confirmDelete = async () => {
        if (!deleteTarget?.item || deleteSubmitting) return;

        setDeleteSubmitting(true);

        try {
            let ok = false;

            if (deleteTarget.type === 'type') {
                ok = await deleteType(deleteTarget.item);
            } else if (deleteTarget.type === 'fonction') {
                ok = await deleteFonction(deleteTarget.item);
            } else if (deleteTarget.type === 'maintenancier') {
                ok = await deleteMaintenancier(deleteTarget.item);
            } else if (deleteTarget.type === 'maintenance') {
                ok = await deleteMaintenance(deleteTarget.item);
            }

            if (ok) {
                setDeleteTarget(null);
                window.location.reload();
            }
        } catch (error) {
            setFormFeedback({ type: 'error', message: error.message || 'Une erreur est survenue.' });
        } finally {
            setDeleteSubmitting(false);
        }
    };

    const closeFormModal = () => {
        setActiveForm('');
        setFormFeedback(null);
        setSubmitting(false);
        setMaintenancierFormMode('create');
        setMaintenancierFormId('');
        setFonctionFormMode('create');
        setFonctionFormId('');
        setTypeFormMode('create');
        setTypeFormId('');
        setInterventionFormMode('create');
        setInterventionFormId('');
        setProprietaireSearch('');
        setProprietaireOpen(false);
        setMaintenancierSearch('');
        setMaintenancierOpen(false);
        setLotSearch('');
        setLotOpen(false);
        setProprieteSearch('');
        setProprieteOpen(false);
        setBatimentSearch('');
        setBatimentOpen(false);
        setPorteSearch('');
        setPorteOpen(false);
        setMaintenancierTargetId('');
    };

    const submitForm = async (event) => {
        event.preventDefault();
        if (!activeForm) return;

        setSubmitting(true);
        setFormFeedback(null);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

        const payloadByForm = {
            maintenancier: {
                url: maintenancierFormMode === 'edit' ? `/agence/maintenance/maintenancier/${maintenancierFormId}` : '/agence/maintenance/maintenancier/store',
                method: maintenancierFormMode === 'edit' ? 'PUT' : 'POST',
                body: {
                    fonction_maintenance_id: maintenancierForm.fonction_maintenance_id,
                    entreprise: maintenancierForm.entreprise,
                    name: maintenancierForm.name,
                    tel1: maintenancierForm.tel1,
                    tel2: maintenancierForm.tel2,
                    email: maintenancierForm.email,
                    adresse: maintenancierForm.adresse,
                    type_piece_id: maintenancierForm.type_piece_id,
                    numero_piece: maintenancierForm.numero_piece,
                    date_validite_piece: maintenancierForm.date_validite_piece || null,
                    statut: maintenancierForm.statut,
                },
                successMessage: maintenancierFormMode === 'edit' ? 'Maintenancier mis à jour avec succès.' : 'Maintenancier créé avec succès.',
            },
            fonction: {
                url: fonctionFormMode === 'edit' ? `/agence/maintenance/fonction/${fonctionFormId}` : '/agence/maintenance/fonction/store',
                method: fonctionFormMode === 'edit' ? 'PUT' : 'POST',
                body: {
                    name: fonctionForm.name,
                    description: fonctionForm.description,
                },
                successMessage: fonctionFormMode === 'edit' ? 'Fonction mise à  jour avec succès.' : 'Fonction créée avec succès.',
            },
            type: {
                url: typeFormMode === 'edit' ? `/agence/maintenance/type/${typeFormId}` : '/agence/maintenance/type/store',
                method: typeFormMode === 'edit' ? 'PUT' : 'POST',
                body: {
                    name: typeForm.name,
                    maintenance_category_id: typeForm.maintenance_category_id,
                    description: typeForm.description,
                    duree_estimee: typeForm.duree_estimee || null,
                },
                successMessage: typeFormMode === 'edit' ? 'Type de maintenance mis à  jour avec succès.' : 'Type de maintenance créé avec succès.',
            },
            intervention: {
                url: interventionFormMode === 'edit' ? `/agence/maintenance/${interventionFormId}` : '/agence/maintenance',
                method: interventionFormMode === 'edit' ? 'PUT' : 'POST',
                body: {
                    titre: interventionForm.titre,
                    proprietaire_id: interventionForm.proprietaire_id,
                    lot_id: interventionForm.lot_id || null,
                    propriete_id: interventionForm.propriete_id || null,
                    batiment_id: interventionForm.batiment_id || null,
                    porte_id: interventionForm.porte_id || null,
                    prise_en_charge_par: interventionForm.prise_en_charge_par,
                    details: interventionForm.actions.map((action) => ({
                        type_intervention_id: action.type_intervention_id,
                        maintenancier_id: action.maintenancier_id,
                        date_debut: action.date_debut,
                        date_fin: action.date_fin || null,
                        priorite: action.priorite,
                        prix: Number(action.prix || 0),
                        description: action.description,
                    })),
                },
                successMessage: interventionFormMode === 'edit' ? 'Intervention mise à jour avec succès.' : 'Intervention créée avec succès.',
            },
        };

        const current = payloadByForm[activeForm];

        try {
            const response = await fetch(current.url, {
                method: current.method ?? 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(current.body),
            });

            const contentType = response.headers.get('content-type') ?? '';
            const payload = contentType.includes('application/json') ? await response.json().catch(() => null) : null;

            if (!response.ok || payload?.success === false) {
                throw new Error(payload?.message ?? 'Enregistrement impossible.');
            }

            setFormFeedback({ type: 'success', message: payload?.message ?? current.successMessage });
            closeFormModal();
            setTimeout(() => window.location.reload(), 250);
        } catch (error) {
            setFormFeedback({ type: 'error', message: error.message || 'Une erreur est survenue.' });
            setSubmitting(false);
        }
    };

    return (
        <AgenceLayout title="Maintenance">
            <Head title="Maintenance" />

            <section className="space-y-6">
              

                 <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                    <div>
                                       
                                        <h2 className="text-2xl font-semibold text-[#0f172a]">Gestion de la maintenance</h2>
                                    </div>
                
                                    <Button
                                        type="button"
                                        onClick={() => setCreationModalOpen(true)}
                                        className="h-11 rounded-xl bg-[#00559b] px-4 text-white hover:bg-[#004980]"
                                    >
                                            <Plus className="h-4 w-4" /> Nouveau 
                                    </Button>
                                </div>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {cards.map((card) => (
                        <StatCard key={card.label} {...card} />
                    ))}
                </div>

                <Tabs value={section} onValueChange={setSection} className="w-full">
                                <TabsList className="flex h-10 w-full items-center justify-start rounded-xl bg-slate-100 p-1 text-slate-500">
                                    {Object.entries(sectionConfig).map(([key, config]) => {
                                        const Icon = config.icon;

                                        return (
                                            <TabsTrigger
                                                key={key}
                                                value={key}
                                                className="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2"
                                            >
                                                <Icon className="h-4 w-4" />
                                                {config.label}
                                            </TabsTrigger>
                                        );
                                    })}
                                </TabsList>
                            </Tabs>

                <div className={section === 'types' || section === 'fonctions' ? 'grid gap-6' : 'grid gap-6 xl:grid-cols-[360px_1fr]'}>
                    <Card className="rounded-3xl border-slate-300 shadow-sm">
                        <CardHeader className="space-y-4 border-b border-slate-200">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <CardTitle className="text-base">
                                    {sectionConfig[section].label}{' '}
                                    <span className="text-slate-400">{sectionConfig[section].count}</span>
                                </CardTitle>

                                {sectionCreateMap[section] ? (
                                    <Button
                                        type="button"
                                        onClick={() => openCreationForm(sectionCreateMap[section])}
                                        className="h-9 rounded-xl bg-[#00559b] px-3 text-white hover:bg-[#004980]"
                                    >
                                        <Plus className="mr-2 h-4 w-4" />
                                        Ajouter
                                    </Button>
                                ) : null}
                            </div>

                            <div className={section === 'types' ? 'grid gap-3 lg:grid-cols-[1fr_240px]' : 'space-y-0'}>
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" />
                                    <Input
                                        value={query}
                                        onChange={(event) => setQuery(event.target.value)}
                                        placeholder={`Rechercher ${sectionConfig[section].label.toLowerCase()}...`}
                                        className={cn(inputClassName, 'pl-10')}
                                    />
                                </div>

                                {section === 'types' ? (
                                    <Select value={typeCategoryFilter} onValueChange={setTypeCategoryFilter}>
                                        <SelectTrigger className={inputClassName}>
                                            <SelectValue placeholder="Filtrer par catégorie" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">Toutes les catégories</SelectItem>
                                            {maintenanceCategoryOptions.map((option) => (
                                                <SelectItem key={option.value} value={option.value}>
                                                    {option.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                ) : null}

                                {section === 'maintenanciers' ? (
                                    <div className="mt-3 grid gap-3 sm:grid-cols-2">
                                        <Select value={maintenancierFonctionFilter} onValueChange={setMaintenancierFonctionFilter}>
                                            <SelectTrigger className={inputClassName}>
                                                <SelectValue placeholder="Filtrer par fonction" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">Fonctions</SelectItem>
                                                {fonctionRows.map((item) => (
                                                    <SelectItem key={item.id} value={item.id}>
                                                        {item.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>

                                        <Select value={maintenancierAvailabilityFilter} onValueChange={setMaintenancierAvailabilityFilter}>
                                            <SelectTrigger className={inputClassName}>
                                                <SelectValue placeholder="Disponibilité" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">Status</SelectItem>
                                                <SelectItem value="disponible">Disponible</SelectItem>
                                                <SelectItem value="indisponible">Indisponible</SelectItem>
                                            </SelectContent>
                                        </Select>

                                        
                                    </div>
                                ) : null}

                                {section === 'interventions' ? (
                                    <div className="mt-3 grid gap-3 sm:grid-cols-2">
                                        <Select value={maintenanceStatusFilter} onValueChange={setMaintenanceStatusFilter}>
                                            <SelectTrigger className={inputClassName}>
                                                <SelectValue placeholder="Filtrer par statut" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">Tous les statuts</SelectItem>
                                                <SelectItem value="en attente">En attente</SelectItem>
                                                <SelectItem value="en cours">En cours</SelectItem>
                                                <SelectItem value="terminer">Terminé</SelectItem>
                                                <SelectItem value="annuler">Annulé</SelectItem>
                                            </SelectContent>
                                        </Select>

                                        <Select value={maintenanceSort} onValueChange={setMaintenanceSort}>
                                            <SelectTrigger className={inputClassName}>
                                                <SelectValue placeholder="Trier par" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="date_desc">Date (récent → ancien)</SelectItem>
                                                <SelectItem value="date_asc">Date (ancien → récent)</SelectItem>
                                                <SelectItem value="montant_desc">Montant (décroissant)</SelectItem>
                                                <SelectItem value="priorite">Priorité</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                ) : null}

                            
                            </div>
                        </CardHeader>

                        <CardContent className="p-3">
                            <ScrollArea className={section === 'types' ? 'h-auto max-h-none' : 'h-[650px]'}>
                                <div className={section === 'types' ? 'space-y-4 pr-3' : 'space-y-2 pr-3'}>
                                    {section === 'maintenanciers' &&
                                        paginatedMaintenancierRows.map((item) => {
                                            const isSelected = selectedMaintenancier?.id === item.id;
                                            const meta = availabilityMeta(item.statut);
                                            const pieceExpired = isPieceExpired(item);

                                            return (
                                                <div
                                                    key={item.id}
                                                    role="button"
                                                    tabIndex={0}
                                                    onClick={() => {
                                                        setSelectedMaintenancierId(item.id);
                                                        setMaintenancierDetailTab('infos');
                                                    }}
                                                    onKeyDown={(event) => {
                                                        if (event.key === 'Enter' || event.key === ' ') {
                                                            event.preventDefault();
                                                            setSelectedMaintenancierId(item.id);
                                                            setMaintenancierDetailTab('infos');
                                                        }
                                                    }}
                                                    className={`mt-4 w-full cursor-pointer rounded-2xl border p-4 text-left outline-none transition focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 ${
                                                        isSelected
                                                            ? 'border-[#00559b] bg-blue-50'
                                                            : 'border-slate-200 bg-white hover:bg-slate-50'
                                                    }`}
                                                >
                                                    <div className="space-y-3">
                                                        <div className="flex items-center justify-between gap-3">
                                                            <span className="text-xs font-semibold text-slate-500">
                                                                {item.entreprise}
                                                            </span>
                                                            <div className="flex items-center gap-1.5">
                                                                {pieceExpired ? <Badge variant="danger">Pièce expirée</Badge> : null}
                                                                <Badge variant={meta.variant}>{meta.label}</Badge>
                                                            </div>
                                                        </div>

                                                        <div className="flex items-center gap-3">
                                                            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">
                                                                {initials(item.name, 'M')}
                                                            </div>

                                                            <div className="min-w-0">
                                                                <p className="truncate text-sm font-semibold text-slate-900">
                                                                    {item.name}
                                                                </p>
                                                                <p className="truncate text-xs text-slate-500">
                                                                    {item.fonction}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div className="mt-3 flex items-center justify-between gap-3 border-t border-slate-100 pt-3 text-xs text-slate-500">
                                                        <div className="flex items-center gap-3">
                                                            {item.tel1 ? (
                                                                <a
                                                                    href={`tel:${item.tel1}`}
                                                                    onClick={(event) => event.stopPropagation()}
                                                                    className="inline-flex items-center gap-1 text-[#00559b] hover:underline"
                                                                >
                                                                    <Phone className="h-3.5 w-3.5" />
                                                                    {item.tel1}
                                                                </a>
                                                            ) : null}
                                                            {item.email ? (
                                                                <a
                                                                    href={`mailto:${item.email}`}
                                                                    onClick={(event) => event.stopPropagation()}
                                                                    className="inline-flex items-center gap-1 text-[#00559b] hover:underline"
                                                                >
                                                                    <Mail className="h-3.5 w-3.5" />
                                                                </a>
                                                            ) : null}
                                                        </div>

                                                            <button
                                                                type="button"
                                                                title={item.statut ? 'Marquer indisponible' : 'Marquer disponible'}
                                                                onClick={(event) => {
                                                                    event.stopPropagation();
                                                                    openAvailabilityConfirm(item);
                                                                }}
                                                                className={cn(
                                                                'inline-flex items-center gap-1 rounded-full border px-2 py-1 transition',
                                                                item.statut
                                                                    ? 'border-[#c8e6c9] text-[#245b00] hover:bg-[#eef8df]'
                                                                    : 'border-[#f4c7c3] text-[#b42318] hover:bg-[#fdecec]'
                                                            )}
                                                        >
                                                            <Power className="h-3.5 w-3.5" />
                                                        </button>
                                                    </div>
                                                </div>
                                            );
                                        })}

                                    {section === 'maintenanciers' && filteredMaintenanciers.length === 0 ? (
                                        <div className="py-6">
                                            <EmptyState
                                                title="Aucun maintenancier trouvé"
                                                desc="Aucun maintenancier ne correspond à la recherche ou aux filtres sélectionnés."
                                            />
                                        </div>
                                    ) : null}

                                    {section === 'maintenanciers' && filteredMaintenanciers.length > maintenancierPageSize ? (
                                        <div className="flex flex-col gap-3 border-t border-[#e2e8f0] pt-4 sm:flex-row sm:items-center sm:justify-between">
                                            <p className="text-sm text-slate-500">
                                                Page {maintenancierPage} sur {maintenancierPageCount} · {filteredMaintenanciers.length} maintenancier(s)
                                            </p>

                                            <div className="flex items-center gap-2">
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    className="rounded-xl border-[#c8d4de]"
                                                    disabled={maintenancierPage <= 1}
                                                    onClick={() => setMaintenancierPage((current) => Math.max(1, current - 1))}
                                                >
                                                    Précédent
                                                </Button>

                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    className="rounded-xl border-[#c8d4de]"
                                                    disabled={maintenancierPage >= maintenancierPageCount}
                                                    onClick={() => setMaintenancierPage((current) => Math.min(maintenancierPageCount, current + 1))}
                                                >
                                                    Suivant
                                                </Button>
                                            </div>
                                        </div>
                                    ) : null}

                                    {section === 'interventions' &&
                                        paginatedMaintenanceRows.map((item) => {
                                            const isSelected = selectedMaintenance?.id === item.id;
                                            const meta = statusMeta(item.statut);
                                            const charge = chargeMeta(item.prise_en_charge_par);
                                            const priority = priorityMeta(item.details?.[0]?.priorite);
                                            const late = isLate(item);

                                            return (
                                                <Button
                                                    key={item.id}
                                                    type="button"
                                                    variant="ghost"
                                                    onClick={() => setSelectedMaintenanceId(item.id)}
                                                    className={`h-auto mt-4 w-full justify-start rounded-2xl border p-4 text-left ${
                                                        isSelected
                                                            ? 'border-[#00559b] bg-blue-50 hover:bg-blue-50'
                                                            : 'border-slate-200 bg-white hover:bg-slate-50'
                                                    }`}
                                                >
                                                    <div className="w-full space-y-3">
                                                        <div className="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                                                            
                                                            {renderBadgeStack([
                                                                late ? { variant: 'danger', label: 'En retard' } : null,
                                                                { variant: meta.variant, label: meta.label },
                                                                { variant: priority.variant, label: priority.label },
                                                            ])}
                                                        </div>

                                                        <div className="min-w-0">
                                                         
                                                         <div className="min-w-0 flex-1">
                                                                <p className="whitespace-normal break-words text-sm font-semibold leading-tight text-slate-900">
                                                                    {item.titre}
                                                                </p>
                                                            </div>
                                                            
                                                            <span className="text-xs font-semibold text-slate-500">
                                                               Propriétaire: {item.proprietaire}
                                                            </span>

                                                            <p className="mt-1 truncate text-xs text-slate-500">
                                                               Lot: {item.lot || item.batiment || item.porte || 'Localisation non definie'}
                                                            </p>
                                                        </div>

                                                        <div className="flex items-center justify-between gap-3 text-xs text-slate-500">
                                                            <span className="flex items-center gap-1">
                                                                <Clock3 className="h-3.5 w-3.5" />
                                                                {formatDate(item.date_debut)}
                                                                {item.date_fin ? ` → ${formatDate(item.date_fin)}` : ''}
                                                            </span>
                                                            
                                                        </div>

                                                        <div className="flex items-center justify-between gap-3 text-xs text-slate-500">
                                                           
                                                            <span className="font-semibold text-slate-700">{currency(item.montant_global)}</span>
                                                        </div>
                                                    </div>
                                                </Button>
                                            );
                                        })}

                                    {section === 'interventions' && filteredMaintenanceRows.length === 0 ? (
                                        <div className="py-6">
                                            <EmptyState
                                                title="Aucune intervention trouvée"
                                                desc="Aucune intervention ne correspond à la recherche ou au filtre sélectionné."
                                            />
                                        </div>
                                    ) : null}

                                    {section === 'interventions' && filteredMaintenanceRows.length > maintenancePageSize ? (
                                        <div className="flex flex-col gap-3 border-t border-[#e2e8f0] pt-4 sm:flex-row sm:items-center sm:justify-between">
                                            <p className="text-sm text-slate-500">
                                                Page {maintenancePage} sur {maintenancePageCount} · {filteredMaintenanceRows.length} intervention(s)
                                            </p>

                                            <div className="flex items-center gap-2">
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    className="rounded-xl border-[#c8d4de]"
                                                    disabled={maintenancePage <= 1}
                                                    onClick={() => setMaintenancePage((current) => Math.max(1, current - 1))}
                                                >
                                                    Précédent
                                                </Button>

                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    className="rounded-xl border-[#c8d4de]"
                                                    disabled={maintenancePage >= maintenancePageCount}
                                                    onClick={() => setMaintenancePage((current) => Math.min(maintenancePageCount, current + 1))}
                                                >
                                                    Suivant
                                                </Button>
                                            </div>
                                        </div>
                                    ) : null}

                                    {section === 'fonctions' && filteredFonctionRows.length > 0 && (
                                        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                            {filteredFonctionRows.map((item) => (
                                                <Card
                                                    key={item.id}
                                                    className="mt-4 group relative overflow-hidden rounded-2xl border border-[#c8d4de] bg-white shadow-sm transition-all duration-200 hover:border-[#00559b] hover:shadow-lg hover:shadow-[#00559b]/10"
                                                >
                                                    <CardContent className="mt-4 flex h-full min-h-[240px] flex-col p-5">
                                                        <div className="flex items-start gap-3">
                                                            <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#eaf4fb] text-[#00559b] transition-colors group-hover:bg-[#00559b] group-hover:text-white">
                                                                <ShieldCheck className="h-5 w-5" />
                                                            </div>
                                                        </div>

                                                        <div className="mt-5 min-w-0">
                                                            <h3 className="line-clamp-2 text-base font-semibold leading-snug text-[#0f172a]">
                                                                {item.name}
                                                            </h3>

                                                            <p className="mt-2 line-clamp-3 text-sm leading-6 text-[#5f7182]">
                                                                {item.description || 'Aucune description renseignée pour cette fonction.'}
                                                            </p>
                                                        </div>

                                                        <div className="mb-4 mt-auto flex items-center gap-2 border-t border-[#e2e8f0] pt-4">
                                                            <Button
                                                                type="button"
                                                                variant="outline"
                                                                onClick={() => openFonctionEdit(item)}
                                                                className="h-10 flex-1 rounded-xl border-[#c8d4de] text-[#334155] hover:border-[#00559b] hover:bg-[#eaf4fb] hover:text-[#00559b]"
                                                            >
                                                                <Pencil className="mr-2 h-4 w-4" />
                                                                Modifier
                                                            </Button>

                                                            <Button
                                                                type="button"
                                                                variant="outline"
                                                                title="Supprimer"
                                                                aria-label={`Supprimer ${item.name}`}
                                                                onClick={() => openDeleteConfirm('fonction', item)}
                                                                className="h-10 w-12 shrink-0 rounded-xl border-[#fecaca] p-0 text-[#dc2626] hover:border-[#dc2626] hover:bg-[#fef2f2] hover:text-[#dc2626]"
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                            </Button>
                                                        </div>
                                                    </CardContent>
                                                </Card>
                                            ))}
                                        </div>
                                    )}

                                    {section === 'types' && filteredTypeRows.length > 0 && (
                                        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                            {paginatedTypeRows.map((item) => {
                                                const interventionsCount = item.maintenances_count ?? 0;

                                                return (
                                                    <Card
                                                        key={item.id}
                                                        className="mt-4 group relative overflow-hidden rounded-2xl border border-[#c8d4de] bg-white  transition-all duration-200 hover:border-[#00559b] hover:shadow-lg hover:shadow-[#00559b]/10"
                                                    >
                                                        <CardContent className="mt-4 flex h-full min-h-[250px] flex-col p-5">
                                                            <div className="flex items-start justify-between gap-3">
                                                                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#eaf4fb] text-[#00559b] transition-colors group-hover:bg-[#00559b] group-hover:text-white">
                                                                    <Tag className="h-5 w-5" />
                                                                </div>

                                                                <Badge
                                                                    variant="outline"
                                                                    className="max-w-[70%] truncate rounded-full border-[#c8d4de] bg-[#f8fbfe] px-3 py-1 text-xs font-medium text-[#00559b]"
                                                                >
                                                                    {item.categorie}
                                                                </Badge>
                                                            </div>

                                                            <div className="mt-5 min-w-0">
                                                                <h3 className="line-clamp-2 text-base font-semibold leading-snug text-[#0f172a]">
                                                                    {item.name}
                                                                </h3>

                                                                <p className="mt-2 line-clamp-3 text-sm leading-6 text-[#5f7182]">
                                                                    {item.description || 'Aucune description renseignée pour ce type.'}
                                                                </p>
                                                            </div>

                                                          

                                                                    <h4 className="mt-2 line-clamp-2 text-base leading-snug text-[#0f172a]">
                                                                        Durée: {item.duree_estimee
                                                                            ? `${item.duree_estimee} h`
                                                                            : 'Non estimée'}
                                                                    </h4>

                                                                    <h4 className="mt-2 line-clamp-2 text-base leading-snug text-[#0f172a]">
                                                                        Interventions: {interventionsCount}
                                                                    </h4>

                                           

                                                            {/* Actions */}
                                                            <div className="mt-4 flex items-center gap-2 border-t border-[#e2e8f0] pt-4">
                                                                <Button
                                                                    type="button"
                                                                    variant="outline"
                                                                    onClick={() => openTypeEdit(item)}
                                                                    className="h-10 flex-1 rounded-xl border-[#c8d4de] text-[#334155] hover:border-[#00559b] hover:bg-[#eaf4fb] hover:text-[#00559b]"
                                                                >
                                                                    <Pencil className="mr-2 h-4 w-4" />
                                                                    Modifier
                                                                </Button>

                                                                <Button
                                                                    type="button"
                                                                    variant="outline"
                                                                    title="Supprimer"
                                                                    aria-label={`Supprimer ${item.name}`}
                                                                    onClick={() => openDeleteConfirm('type', item)}
                                                                    className="h-10 w-12 shrink-0 rounded-xl border-[#fecaca] p-0 text-[#dc2626] hover:border-[#dc2626] hover:bg-[#fef2f2] hover:text-[#dc2626]"
                                                                >
                                                                    <Trash2 className="h-4 w-4" />
                                                                </Button>
                                                            </div>
                                                        </CardContent>
                                                    </Card>
                                                );
                                            })}
                                        </div>
                                    )}

                                    {section === 'types' && filteredTypeRows.length === 0 ? (
                                        <div className="py-6">
                                            <EmptyState
                                                title="Aucun type trouvé"
                                                desc="Aucun type ne correspond à la recherche ou au filtre sélectionné."
                                            />
                                        </div>
                                    ) : null}

                                    {section === 'types' && filteredTypeRows.length > typePageSize ? (
                                        <div className="flex flex-col gap-3 border-t border-[#e2e8f0] pt-4 sm:flex-row sm:items-center sm:justify-between">
                                            <p className="text-sm text-slate-500">
                                                Page {typePage} sur {typePageCount} Â· {filteredTypeRows.length} type(s)
                                            </p>

                                            <div className="flex items-center gap-2">
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    className="rounded-xl border-[#c8d4de]"
                                                    disabled={typePage <= 1}
                                                    onClick={() => setTypePage((current) => Math.max(1, current - 1))}
                                                >
                                                    Précédent
                                                </Button>

                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    className="rounded-xl border-[#c8d4de]"
                                                    disabled={typePage >= typePageCount}
                                                    onClick={() => setTypePage((current) => Math.min(typePageCount, current + 1))}
                                                >
                                                    Suivant
                                                </Button>
                                            </div>
                                        </div>
                                    ) : null}
                                </div>
                            </ScrollArea>
                        </CardContent>
                    </Card>

                    <Card
                        className={`rounded-3xl border-slate-300 shadow-sm ${
                            section === 'types' || section === 'fonctions' ? 'hidden' : ''
                        }`}
                    >
                        {section === 'maintenanciers' && selectedMaintenancier ? (
                            <>
                                <CardHeader className="mt-4 flex flex-col gap-4 border-b border-slate-200">
                                    <div className="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            <CardTitle className="mt-2 text-2xl">{selectedMaintenancier.name}</CardTitle>
                                            <p className="mt-2 text-sm text-slate-500">{selectedMaintenancier.fonction}</p>
                                        </div>

                                        <div className="flex flex-wrap items-center gap-2 lg:justify-end">
                                            {isPieceExpired(selectedMaintenancier) ? <Badge variant="danger">Pièce expirée</Badge> : null}
                                            <Badge variant={availabilityMeta(selectedMaintenancier.statut).variant}>
                                                {availabilityMeta(selectedMaintenancier.statut).label}
                                            </Badge>
                                            <Badge variant="outline">{selectedMaintenancier.interventions_count} intervention(s)</Badge>
                                        </div>
                                    </div>

                                    <div className="flex flex-wrap items-center gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => openAvailabilityConfirm(selectedMaintenancier)}
                                            className={cn(
                                                'h-9 rounded-xl px-3',
                                                selectedMaintenancier.statut
                                                    ? 'border-[#c8e6c9] text-[#245b00] hover:border-[#4d8500] hover:bg-[#eef8df]'
                                                    : 'border-[#f4c7c3] text-[#b42318] hover:border-[#dc2626] hover:bg-[#fdecec]'
                                            )}
                                        >
                                            <Power className="mr-2 h-4 w-4" />
                                            {selectedMaintenancier.statut ? 'Marquer indisponible' : 'Marquer disponible'}
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => openMaintenancierEdit(selectedMaintenancier)}
                                            className="h-9 rounded-xl border-[#c8d4de] text-[#334155] hover:border-[#00559b] hover:bg-[#eaf4fb] hover:text-[#00559b]"
                                        >
                                            <Pencil className="mr-2 h-4 w-4" />
                                            Modifier
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => openDeleteConfirm('maintenancier', selectedMaintenancier)}
                                            className="h-9 rounded-xl border-[#fecaca] px-3 text-[#dc2626] hover:border-[#dc2626] hover:bg-[#fef2f2] hover:text-[#dc2626]"
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </CardHeader>

                                <div className="flex min-h-0 flex-1 flex-col overflow-hidden">
                                    <div className="mb-4 border-slate-200 px-6 pt-5">
                                        <div className="inline-flex rounded-xl bg-slate-100 p-1 text-slate-500">
                                            <button
                                                type="button"
                                                onClick={() => setMaintenancierDetailTab('infos')}
                                                className={cn(
                                                    'inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2',
                                                    maintenancierDetailTab === 'infos'
                                                        ? 'bg-white text-slate-900 shadow-sm'
                                                        : 'text-slate-500 hover:text-slate-900'
                                                )}
                                            >
                                                Informations
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => setMaintenancierDetailTab('interventions')}
                                                className={cn(
                                                    'inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2',
                                                    maintenancierDetailTab === 'interventions'
                                                        ? 'bg-white text-slate-900 shadow-sm'
                                                        : 'text-slate-500 hover:text-slate-900'
                                                )}
                                            >
                                                Interventions
                                            </button>
                                        </div>
                                    </div>

                                    <ScrollArea className="min-h-0 flex-1">
                                        {maintenancierDetailTab === 'infos' ? (
                                            <CardContent className="space-y-6 p-6">
                                            <Card className="rounded-2xl border-slate-200">
                                                <CardContent className="mt-4 flex gap-4 p-5">
                                                    <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-900 text-lg font-bold text-white">
                                                        {initials(selectedMaintenancier.name, 'M')}
                                                    </div>

                                                    <div className="grid flex-1 gap-3 md:grid-cols-2">
                                                        <InfoBlock label="Raison sociale" value={selectedMaintenancier.entreprise} />
                                                        <InfoBlock label="Fonction" value={selectedMaintenancier.fonction} />
                                                        <div>
                                                            <p className="text-xs text-slate-500">Email</p>
                                                            {selectedMaintenancier.email ? (
                                                                <a
                                                                    href={`mailto:${selectedMaintenancier.email}`}
                                                                    className="mt-1 inline-flex items-center gap-1 text-sm font-semibold text-[#00559b] hover:underline"
                                                                >
                                                                    <Mail className="h-3.5 w-3.5" />
                                                                    {selectedMaintenancier.email}
                                                                </a>
                                                            ) : (
                                                                <p className="mt-1 text-sm font-semibold text-slate-900">Non spécifié</p>
                                                            )}
                                                        </div>
                                                        <div>
                                                            <p className="text-xs text-slate-500">Telephone</p>
                                                            {selectedMaintenancier.tel1 ? (
                                                                <a
                                                                    href={`tel:${selectedMaintenancier.tel1}`}
                                                                    className="mt-1 inline-flex items-center gap-1 text-sm font-semibold text-[#00559b] hover:underline"
                                                                >
                                                                    <Phone className="h-3.5 w-3.5" />
                                                                    {selectedMaintenancier.tel1}
                                                                </a>
                                                            ) : (
                                                                <p className="mt-1 text-sm font-semibold text-slate-900">Non spécifié</p>
                                                            )}
                                                        </div>
                                                    </div>
                                                </CardContent>
                                            </Card>

                                            <Card className="rounded-2xl border-slate-200">
                                                <CardHeader>
                                                    <CardTitle className="text-sm uppercase tracking-[0.18em] text-slate-500">
                                                        Coordonnees
                                                    </CardTitle>
                                                </CardHeader>
                                                <CardContent>
                                                    <div className="grid gap-4 md:grid-cols-2">
                                                        <InfoBlock label="Telephone 2" value={selectedMaintenancier.tel2 || 'Non spécifié'} />
                                                        <InfoBlock label="Piece" value={selectedMaintenancier.type_piece || 'Non spécifié'} />
                                                        <InfoBlock label="Numero piece" value={selectedMaintenancier.numero_piece || 'Non spécifié'} />
                                                        <InfoBlock label="Date de validite" value={formatDate(selectedMaintenancier.date_validite_piece)} />
                                                        <InfoBlock label="Adresse" value={selectedMaintenancier.adresse || 'Non spécifié'} full />
                                                    </div>
                                                </CardContent>
                                            </Card>
                                            </CardContent>
                                        ) : null}

                                        {maintenancierDetailTab === 'interventions' ? (
                                            <CardContent className="space-y-6 p-6">
                                            <div className="grid gap-4 md:grid-cols-4">
                                                <StatCard
                                                    label="Total"
                                                    value={selectedMaintenancierInterventions.length}
                                                    icon={ClipboardList}
                                                    accent="text-slate-600"
                                                />
                                                <StatCard
                                                    label="En cours"
                                                    value={selectedMaintenancierInterventions.filter((detail) => String(detail.statut ?? '').includes('cours')).length}
                                                    icon={Clock3}
                                                    accent="text-[#00559b]"
                                                />
                                                <StatCard
                                                    label="Terminées"
                                                    value={selectedMaintenancierInterventions.filter((detail) => String(detail.statut ?? '').includes('term')).length}
                                                    icon={Check}
                                                    accent="text-emerald-600"
                                                />
                                                <StatCard
                                                    label="En attente"
                                                    value={selectedMaintenancierInterventions.filter((detail) => String(detail.statut ?? '').includes('attente')).length}
                                                    icon={Users}
                                                    accent="text-amber-600"
                                                />
                                            </div>

                                            <Card className="rounded-2xl border-slate-200">
                                                <CardHeader className="border-b border-slate-200">
                                                    <CardTitle className="text-sm uppercase tracking-[0.18em] text-slate-500">
                                                        Lignes d’intervention
                                                    </CardTitle>
                                                    <CardDescription>
                                                        Interventions réellement affectées à ce maintenancier.
                                                    </CardDescription>
                                                </CardHeader>

                                                <CardContent className="mt-4 space-y-3 p-4">
                                                    {selectedMaintenancierInterventions.length ? (
                                                        selectedMaintenancierInterventions.map((detail) => {
                                                            const detailStatus = statusMeta(detail?.statut);
                                                            const priority = priorityMeta(detail?.priorite);
                                                            const maintenanceStatus = statusMeta(detail?.maintenance_statut);
                                                            const late = isLate(detail);
                                                            const typeName =
                                                                detail?.typeIntervention?.name ??
                                                                detail?.type_intervention?.name ??
                                                                'Type non défini';

                                                            return (
                                                                <button
                                                                    key={String(detail?.maintenance_detail_id ?? detail?.id)}
                                                                    type="button"
                                                                    onClick={() => {
                                                                        if (detail?.maintenance_id) {
                                                                            setSelectedMaintenanceId(String(detail.maintenance_id));
                                                                            setSection('interventions');
                                                                        }
                                                                    }}
                                                                    className="w-full rounded-2xl border border-slate-200 bg-white p-4 text-left transition hover:border-[#00559b] hover:bg-[#f8fbfe]"
                                                                >
                                                                    <div className="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                                                        <div className="min-w-0">
                                                                            <p className="whitespace-normal break-words text-sm font-semibold leading-tight text-slate-900">
                                                                                {detail?.maintenance_titre ?? 'Intervention'}
                                                                            </p>
                                                                            <p className="mt-1 text-xs text-slate-500">{typeName}</p>
                                                                        </div>

                                                                        {renderBadgeStack([
                                                                            late ? { variant: 'danger', label: 'En retard' } : null,
                                                                            { variant: priority.variant, label: priority.label },
                                                                            { variant: detailStatus.variant, label: detailStatus.label },
                                                                            { variant: maintenanceStatus.variant, label: maintenanceStatus.label },
                                                                        ])}
                                                                    </div>

                                                                    <div className="mt-3 grid gap-3 md:grid-cols-3">
                                                                        <InfoBlock label="Début" value={formatDateTime(detail?.date_debut)} />
                                                                        <InfoBlock label="Fin" value={formatDateTime(detail?.date_fin)} />
                                                                        <InfoBlock
                                                                            label="Référence"
                                                                            value={detail?.maintenance_id?.slice(0, 8)?.toUpperCase() ?? '---'}
                                                                        />
                                                                    </div>

                                                                    <div className="mt-3 flex items-center justify-between gap-3 border-t border-slate-100 pt-3 text-xs text-slate-500">
                                                                        <span>Voir l’intervention parente</span>
                                                                        <span className="inline-flex items-center gap-1 text-[#00559b]">
                                                                            <Eye className="h-3.5 w-3.5" />
                                                                            Ouvrir
                                                                        </span>
                                                                    </div>
                                                                </button>
                                                            );
                                                        })
                                                    ) : (
                                                        <EmptyState
                                                            title="Aucune intervention liée"
                                                            desc="Ce maintenancier n’a pas encore de ligne d’intervention affectée."
                                                        />
                                                    )}
                                                </CardContent>
                                                </Card>
                                            </CardContent>
                                        ) : null}
                                    </ScrollArea>
                                </div>
                            </>
                        ) : null}

                        {section === 'interventions' && selectedMaintenance ? (
                            <>
                                <CardHeader className="flex flex-col gap-4 border-b border-slate-200">
                                    <div className="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            
                                            <CardTitle className="mt-2 text-2xl">{selectedMaintenance.titre}</CardTitle>
                                            
                                            <p className="mt-2 text-sm text-slate-500">
                                                Propriétaire : {selectedMaintenance.proprietaire}
                                            </p>
                                            
                                            <p className="mt-2 text-sm text-slate-500">
                                                Propriété : {selectedMaintenance.lot || selectedMaintenance.batiment || selectedMaintenance.porte || 'Localisation non définie'}
                                            </p>
                                            
                                            <p className="mt-2 text-sm font-semibold text-[#00559b]">
                                                Total : {currency(selectedMaintenance.montant_global)}
                                            </p>
                                        </div>

                                        <div className="flex flex-wrap items-center gap-2 lg:justify-end">
                                            <Badge variant={statusMeta(selectedMaintenance.statut).variant}>
                                                {statusMeta(selectedMaintenance.statut).label}
                                            </Badge>
                                            <Badge variant={chargeMeta(selectedMaintenance.prise_en_charge_par).variant}>
                                                {chargeMeta(selectedMaintenance.prise_en_charge_par).label}
                                            </Badge>
                                        </div>
                                    </div>

                                    <div className="flex flex-wrap items-center gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => openMaintenanceEdit(selectedMaintenance)}
                                            className="h-9 rounded-xl border-[#c8d4de] text-[#334155] hover:border-[#00559b] hover:bg-[#eaf4fb] hover:text-[#00559b]"
                                        >
                                            <Pencil className="mr-2 h-4 w-4" />
                                            Modifier
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => openDeleteConfirm('maintenance', selectedMaintenance)}
                                            className="h-9 rounded-xl border-[#fecaca] px-3 text-[#dc2626] hover:border-[#dc2626] hover:bg-[#fef2f2] hover:text-[#dc2626]"
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </CardHeader>

                                <CardContent className=" mt-4 space-y-3 p-6">
                                    {selectedMaintenance.details.length ? (
                                        selectedMaintenance.details.map((detail, index) => {
                                            const detailStatus = statusMeta(detail?.statut);
                                            const priority = priorityMeta(detail?.priorite);
                                            const late = isLate(detail);
                                            const maintenancierPhone = detail?.maintenancier?.tel1;
                                            const maintenancierEmail = detail?.maintenancier?.email;

                                            return (
                                                <div
                                                    key={String(detail?.maintenance_detail_id ?? detail?.id ?? index)}
                                                    className="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                                                >
                                                    <div className="flex flex-wrap items-start justify-between gap-3">
                                                        <div className="min-w-0">
                                                            <p className="text-sm font-semibold text-slate-900">
                                                                {detail?.typeIntervention?.name ??
                                                                    detail?.type_intervention?.name ??
                                                                    detail?.type_intervention_id ??
                                                                    'Travail'}
                                                            </p>
                                                            <div className="mt-1 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                                                <span>
                                                                    {detail?.maintenancier?.name ?? detail?.maintenancier_id ?? 'Maintenancier non defini'}
                                                                </span>
                                                                {maintenancierPhone ? (
                                                                    <a
                                                                        href={`tel:${maintenancierPhone}`}
                                                                        className="inline-flex items-center gap-1 text-[#00559b] hover:underline"
                                                                    >
                                                                        <Phone className="h-3.5 w-3.5" />
                                                                        {maintenancierPhone}
                                                                    </a>
                                                                ) : null}
                                                                {maintenancierEmail ? (
                                                                    <a
                                                                        href={`mailto:${maintenancierEmail}`}
                                                                        className="inline-flex items-center gap-1 text-[#00559b] hover:underline"
                                                                    >
                                                                        <Mail className="h-3.5 w-3.5" />
                                                                        {maintenancierEmail}
                                                                    </a>
                                                                ) : null}
                                                            </div>
                                                        </div>
                                                        {renderBadgeStack([
                                                            late ? { variant: 'danger', label: 'En retard' } : null,
                                                            { variant: priority.variant, label: priority.label },
                                                            { variant: detailStatus.variant, label: detailStatus.label },
                                                        ])}
                                                    </div>

                                                    <div className="mt-3 grid gap-3 md:grid-cols-4">
                                                        <InfoBlock label="Prix" value={currency(detail?.montant ?? detail?.prix)} />
                                                        <InfoBlock label="Priorite" value={priority.shortLabel ?? priority.label} />
                                                        <InfoBlock label="Debut" value={formatDateTime(detail?.date_debut)} />
                                                        <InfoBlock label="Fin" value={formatDateTime(detail?.date_fin)} />
                                                    </div>

                                                    <div className="mt-4 flex flex-wrap gap-2">
                                                        {getNextMaintenanceStep(detail?.statut) ? (
                                                            <Button
                                                                type="button"
                                                                variant="outline"
                                                                onClick={() => openStatusConfirm(detail, getNextMaintenanceStep(detail?.statut))}
                                                                className="h-9 rounded-xl border-[#c8d4de] text-[#334155] hover:border-[#00559b] hover:bg-[#eaf4fb] hover:text-[#00559b]"
                                                            >
                                                                <ChevronRight className="mr-2 h-4 w-4" />
                                                                {getNextMaintenanceStep(detail?.statut).label}
                                                            </Button>
                                                        ) : null}

                                                        {getCancelMaintenanceAction(detail?.statut) ? (
                                                            <Button
                                                                type="button"
                                                                variant="outline"
                                                                onClick={() => openStatusConfirm(detail, getCancelMaintenanceAction(detail?.statut))}
                                                                className="h-9 rounded-xl border-[#fecaca] text-[#dc2626] hover:border-[#dc2626] hover:bg-[#fef2f2] hover:text-[#dc2626]"
                                                            >
                                                                <X className="mr-2 h-4 w-4" />
                                                                {getCancelMaintenanceAction(detail?.statut).label}
                                                            </Button>
                                                        ) : null}
                                                    </div>
                                                </div>
                                            );
                                        })
                                    ) : (
                                        <EmptyState
                                            title="Aucun detail"
                                            desc="Cette intervention ne contient pas encore de ligne de travail."
                                        />
                                    )}
                                </CardContent>
                            </>
                        ) : null}

                        {!currentRows.length && (
                            <CardContent className="flex min-h-[calc(100vh-260px)] items-center justify-center">
                                <div className="text-center">
                                    <h3 className="text-xl font-semibold text-slate-900">Aucune donnees à afficher</h3>
                                   
                                </div>
                            </CardContent>
                        )}
                    </Card>
                </div>

                <Dialog open={creationModalOpen} onOpenChange={setCreationModalOpen}>
                    <DialogContent className="sm:max-w-3xl p-0 overflow-hidden">
                        <div className="border-b border-[#c8d4de] px-5 py-4">
                            <DialogHeader>
                                <DialogTitle className="text-[#0f172a]">Nouveau</DialogTitle>
                                <DialogDescription className="text-[#5f7182]">
                                    Choisissez le type d&apos;élément que vous voulez ajouter.
                                </DialogDescription>
                            </DialogHeader>
                        </div>

                        <div className="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2">
                            {CREATE_OPTIONS.map((option) => {
                                const Icon = option.icon;

                                return (
                                    <button
                                        key={option.section}
                                        type="button"
                                        onClick={() => openCreationForm(option.formKey)}
                                        className="flex items-center gap-3 rounded-2xl border border-[#c8d4de] bg-white p-4 text-left transition hover:border-[#00559b] hover:shadow-md hover:shadow-[#00559b]/5"
                                    >
                                        <span className={`flex h-11 w-11 items-center justify-center rounded-xl ${option.accent}`}>
                                            <Icon className="h-5 w-5" />
                                        </span>
                                        <span className="flex min-w-0 flex-col">
                                            <strong className="text-sm text-[#0f172a]">{option.title}</strong>
                                            <span className="text-xs text-[#5f7182]">{option.description}</span>
                                        </span>
                                    </button>
                                );
                            })}
                        </div>
                    </DialogContent>
                </Dialog>

                <Dialog open={activeForm === 'maintenancier'} onOpenChange={(open) => (!open ? closeFormModal() : null)}>
                    <DialogContent className="sm:max-w-3xl max-h-[90vh] overflow-hidden p-0">
                        <div className="border-b border-[#c8d4de] px-5 py-4">
                            <DialogHeader>
                                <DialogTitle className="text-[#0f172a]">
                                    {maintenancierFormMode === 'edit' ? 'Modifier le maintenancier' : 'Nouveau maintenancier'}
                                </DialogTitle>
                                <DialogDescription className="text-[#5f7182]">
                                    {maintenancierFormMode === 'edit'
                                        ? 'Corrigez les informations du maintenancier puis validez.'
                                        : 'Ajoutez un nouveau prestataire de maintenance.'}
                                </DialogDescription>
                            </DialogHeader>
                        </div>

                        <form className="flex max-h-[calc(90vh-76px)] flex-col overflow-hidden" onSubmit={submitForm}>
                            {formFeedback ? (
                                <div
                                    className={`rounded-xl px-4 py-3 text-sm ${
                                        formFeedback.type === 'success'
                                            ? 'border border-[#c8e6c9] bg-[#eef8df] text-[#245b00]'
                                            : 'border border-[#f4c7c3] bg-[#fdecec] text-[#b42318]'
                                    }`}
                                >
                                    {formFeedback.message}
                                </div>
                            ) : null}

                            <div className="flex-1 space-y-5 overflow-y-auto p-5 pr-3">
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <Field label="Nom" required>
                                    <Input
                                        value={maintenancierForm.name}
                                        onChange={(event) => setMaintenancierForm((current) => ({ ...current, name: event.target.value }))}
                                        className={inputClassName}
                                        placeholder="Nom complet"
                                    />
                                </Field>

                                <Field label="Entreprise">
                                    <Input
                                        value={maintenancierForm.entreprise}
                                        onChange={(event) => setMaintenancierForm((current) => ({ ...current, entreprise: event.target.value }))}
                                        className={inputClassName}
                                        placeholder="Entreprise ou indépendant"
                                    />
                                </Field>

                                <Field label="Fonction" required>
                                    <Select
                                        value={maintenancierForm.fonction_maintenance_id}
                                        onValueChange={(value) => setMaintenancierForm((current) => ({ ...current, fonction_maintenance_id: value }))}
                                    >
                                        <SelectTrigger className={inputClassName}>
                                            <SelectValue placeholder="Sélectionner" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {fonctionRows.map((item) => (
                                                <SelectItem key={item.id} value={item.id}>
                                                    {item.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field label="Type de pièce" required>
                                    <Select
                                        value={maintenancierForm.type_piece_id}
                                        onValueChange={(value) => setMaintenancierForm((current) => ({ ...current, type_piece_id: value }))}
                                    >
                                        <SelectTrigger className={inputClassName}>
                                            <SelectValue placeholder="Sélectionner" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {typePieceOptions.map((item) => (
                                                <SelectItem key={item.value} value={item.value}>
                                                    {item.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>

                                 <Field label="Numéro de pièce" required>
                                    <Input
                                        value={maintenancierForm.numero_piece}
                                        onChange={(event) => setMaintenancierForm((current) => ({ ...current, numero_piece: event.target.value }))}
                                        className={inputClassName}
                                        placeholder="Numéro CNI / passeport"
                                    />
                                </Field>

                                <Field label="Date de validité de la pièce" required>
                                    <Input
                                        type="date"
                                        value={maintenancierForm.date_validite_piece}
                                        onChange={(event) => setMaintenancierForm((current) => ({ ...current, date_validite_piece: event.target.value }))}
                                        className={inputClassName}
                                    />
                                </Field>

                                <PhoneInput
                                    label="Téléphone 1"
                                    required
                                    value={toPhoneValue(maintenancierForm.tel1)}
                                    onChange={(event) => setMaintenancierForm((current) => ({ ...current, tel1: event.target.value }))}
                                    placeholder="07 00 00 00 00"
                                />

                                <PhoneInput
                                    label="Téléphone 2"
                                    value={toPhoneValue(maintenancierForm.tel2)}
                                    onChange={(event) => setMaintenancierForm((current) => ({ ...current, tel2: event.target.value }))}
                                    placeholder="05 00 00 00 00"
                                />

                                <Field label="Email">
                                    <Input
                                        type="email"
                                        value={maintenancierForm.email}
                                        onChange={(event) => setMaintenancierForm((current) => ({ ...current, email: event.target.value }))}
                                        className={inputClassName}
                                        placeholder="Adresse email"
                                    />
                                </Field>

                               

                                

                                <Field label="Adresse" className="md:col-span-2">
                                    <textarea
                                        value={maintenancierForm.adresse}
                                        onChange={(event) => setMaintenancierForm((current) => ({ ...current, adresse: event.target.value }))}
                                        rows={3}
                                        className={textareaClassName}
                                        placeholder="Adresse complète"
                                    />
                                </Field>
                                </div>
                            </div>

                            <div className="mb-6 flex shrink-0 flex-col gap-3 border-t border-[#e2e8f0] bg-white px-5 py-4 pb-6 sm:flex-row sm:justify-end">
                                <Button type="button" variant="outline" onClick={closeFormModal} className="rounded-xl border-[#c8d4de]">
                                    Annuler
                                </Button>
                                <Button type="submit" className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]" disabled={submitting}>
                                    {submitting ? 'Enregistrement...' : maintenancierFormMode === 'edit' ? 'Mettre à jour' : 'Créer'}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>

                <Dialog open={Boolean(deleteTarget)} onOpenChange={(open) => (!open ? closeDeleteConfirm() : null)}>
                    <DialogContent className="sm:max-w-lg overflow-hidden p-0">
                        <div className="border-b border-[#c8d4de] px-5 py-4">
                            <DialogHeader>
                                <DialogTitle className="text-[#0f172a]">Confirmer la suppression</DialogTitle>
                                <DialogDescription className="text-[#5f7182]">
                                    {deleteTarget?.item?.name || deleteTarget?.item?.titre
                                        ? `Voulez-vous vraiment supprimer "${deleteTarget.item.name ?? deleteTarget.item.titre}" ? Cette action est irreversible.`
                                        : 'Cette action est irreversible.'}
                                </DialogDescription>
                            </DialogHeader>
                        </div>

                        <div className="flex flex-col gap-3 px-5 py-5 sm:flex-row sm:justify-end">
                            <Button type="button" variant="outline" onClick={closeDeleteConfirm} className="rounded-xl border-[#c8d4de]">
                                Annuler
                            </Button>
                            <Button
                                type="button"
                                onClick={confirmDelete}
                                disabled={deleteSubmitting}
                                className="rounded-xl bg-[#dc2626] text-white hover:bg-[#b91c1c]"
                            >
                                {deleteSubmitting ? 'Suppression...' : 'Supprimer'}
                            </Button>
                        </div>
                    </DialogContent>
                </Dialog>

                <Dialog open={Boolean(availabilityTarget)} onOpenChange={(open) => (!open ? closeAvailabilityConfirm() : null)}>
                    <DialogContent className="sm:max-w-lg overflow-hidden p-0">
                        <div className="border-b border-[#c8d4de] px-5 py-4">
                            <DialogHeader>
                                <DialogTitle className="text-[#0f172a]">Confirmer la disponibilité</DialogTitle>
                                <DialogDescription className="text-[#5f7182]">
                                    {availabilityTarget?.name
                                        ? `Voulez-vous vraiment ${
                                              availabilityTarget.statut ? 'marquer indisponible' : 'marquer disponible'
                                          } "${availabilityTarget.name}" ?`
                                        : 'Voulez-vous vraiment changer la disponibilité de ce maintenancier ?'}
                                </DialogDescription>
                            </DialogHeader>
                        </div>

                        <div className="flex flex-col gap-3 px-5 py-5 sm:flex-row sm:justify-end">
                            <Button type="button" variant="outline" onClick={closeAvailabilityConfirm} className="rounded-xl border-[#c8d4de]">
                                Annuler
                            </Button>
                            <Button
                                type="button"
                                onClick={confirmAvailabilityChange}
                                disabled={availabilitySubmitting}
                                className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]"
                            >
                                {availabilitySubmitting ? 'Mise à jour...' : 'Confirmer'}
                            </Button>
                        </div>
                    </DialogContent>
                </Dialog>

                <Dialog open={Boolean(statusTarget)} onOpenChange={(open) => (!open ? closeStatusConfirm() : null)}>
                    <DialogContent className="sm:max-w-lg overflow-hidden p-0">
                        <div className="border-b border-[#c8d4de] px-5 py-4">
                            <DialogHeader>
                                <DialogTitle className="text-[#0f172a]">Confirmer le changement de statut</DialogTitle>
                                <DialogDescription className="text-[#5f7182]">
                                    {statusTarget?.item
                                        ? `Voulez-vous vraiment ${statusTarget.label?.toLowerCase()} pour "${statusTarget.item?.typeIntervention?.name ?? statusTarget.item?.type_intervention?.name ?? 'cette ligne'}" ?`
                                        : 'Voulez-vous vraiment valider ce changement d’étape ?'}
                                </DialogDescription>
                            </DialogHeader>
                        </div>

                        <div className="flex flex-col gap-3 px-5 py-5 sm:flex-row sm:justify-end">
                            <Button type="button" variant="outline" onClick={closeStatusConfirm} className="rounded-xl border-[#c8d4de]">
                                Annuler
                            </Button>
                            <Button
                                type="button"
                                onClick={confirmStatusChange}
                                disabled={statusSubmitting}
                                className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]"
                            >
                                {statusSubmitting ? 'Mise à jour...' : 'Confirmer'}
                            </Button>
                        </div>
                    </DialogContent>
                </Dialog>

                <Dialog open={activeForm === 'fonction'} onOpenChange={(open) => (!open ? closeFormModal() : null)}>
                    <DialogContent className="sm:max-w-xl max-h-[90vh] overflow-hidden p-0">
                        <div className="border-b border-[#c8d4de] px-5 py-4">
                            <DialogHeader>
                                <DialogTitle className="text-[#0f172a]">
                                    {fonctionFormMode === 'edit' ? 'Modifier la fonction' : 'Nouvelle fonction'}
                                </DialogTitle>
                                <DialogDescription className="text-[#5f7182]">
                                    {fonctionFormMode === 'edit'
                                        ? 'Corrigez les informations de la fonction puis validez.'
                                        : 'Définissez une fonction de maintenance.'}
                                </DialogDescription>
                            </DialogHeader>
                        </div>

                        <form className="flex max-h-[calc(90vh-76px)] flex-col overflow-hidden" onSubmit={submitForm}>
                            {formFeedback ? (
                                <div
                                    className={`rounded-xl px-4 py-3 text-sm ${
                                        formFeedback.type === 'success'
                                            ? 'border border-[#c8e6c9] bg-[#eef8df] text-[#245b00]'
                                            : 'border border-[#f4c7c3] bg-[#fdecec] text-[#b42318]'
                                    }`}
                                >
                                    {formFeedback.message}
                                </div>
                            ) : null}

                            <div className="flex-1 space-y-5 overflow-y-auto p-5 pr-3">
                                <div className="grid gap-4">
                                <Field label="Nom" required>
                                    <Input
                                        value={fonctionForm.name}
                                        onChange={(event) => setFonctionForm((current) => ({ ...current, name: event.target.value }))}
                                        className={inputClassName}
                                        placeholder="Ex. Plomberie"
                                    />
                                </Field>

                                <Field label="Description">
                                    <textarea
                                        value={fonctionForm.description}
                                        onChange={(event) => setFonctionForm((current) => ({ ...current, description: event.target.value }))}
                                        rows={4}
                                        className={textareaClassName}
                                        placeholder="Description facultative"
                                    />
                                </Field>
                                </div>
                            </div>

                            <div className="mb-4 flex shrink-0 flex-col gap-3 border-t border-[#e2e8f0] bg-white px-5 py-4 pb-6 sm:flex-row sm:justify-end">
                                <Button type="button" variant="outline" onClick={closeFormModal} className="rounded-xl border-[#c8d4de]">
                                    Annuler
                                </Button>
                                <Button type="submit" className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]" disabled={submitting}>
                                    {submitting ? 'Enregistrement...' : fonctionFormMode === 'edit' ? 'Mettre à  jour' : 'Créer'}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>

                <Dialog open={activeForm === 'type'} onOpenChange={(open) => (!open ? closeFormModal() : null)}>
                    <DialogContent className="sm:max-w-xl max-h-[90vh] overflow-hidden p-0">
                        <div className="border-b border-[#c8d4de] px-5 py-4">
                            <DialogHeader>
                                <DialogTitle className="text-[#0f172a]">
                                    {typeFormMode === 'edit' ? 'Modifier le type' : 'Nouveau type'}
                                </DialogTitle>
                                <DialogDescription className="text-[#5f7182]">
                                    {typeFormMode === 'edit'
                                        ? 'Corrigez les informations du type puis validez.'
                                        : 'Déclarez un type d&apos;intervention.'}
                                </DialogDescription>
                            </DialogHeader>
                        </div>

                        <form className="flex max-h-[calc(90vh-76px)] flex-col overflow-hidden" onSubmit={submitForm}>
                            {formFeedback ? (
                                <div
                                    className={`mt-4 rounded-xl px-4 py-3 text-sm ${
                                        formFeedback.type === 'success'
                                            ? 'border border-[#c8e6c9] bg-[#eef8df] text-[#245b00]'
                                            : 'border border-[#f4c7c3] bg-[#fdecec] text-[#b42318]'
                                    }`}
                                >
                                    {formFeedback.message}
                                </div>
                            ) : null}

                            <div className="flex-1 space-y-5 overflow-y-auto p-5 pr-3">
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <Field label="Nom" required>
                                    <Input
                                        value={typeForm.name}
                                        onChange={(event) => setTypeForm((current) => ({ ...current, name: event.target.value }))}
                                        className={inputClassName}
                                        placeholder="Ex. Électricité"
                                    />
                                </Field>

                                <Field label="Catégorie">
                                    <Select
                                        value={typeForm.maintenance_category_id}
                                        onValueChange={(value) => setTypeForm((current) => ({ ...current, maintenance_category_id: value }))}
                                    >
                                        <SelectTrigger className={inputClassName}>
                                            <SelectValue placeholder="Choisir une catégorie" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {maintenanceCategoryOptions.map((option) => (
                                                <SelectItem key={option.value} value={option.value}>
                                                    {option.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field label="Durée estimée" className="md:col-span-2">
                                    <Input
                                        type="number"
                                        min="0"
                                        value={typeForm.duree_estimee}
                                        onChange={(event) => setTypeForm((current) => ({ ...current, duree_estimee: event.target.value }))}
                                        className={inputClassName}
                                        placeholder="En heures"
                                    />
                                </Field>

                                <Field label="Description" className="md:col-span-2">
                                    <textarea
                                        value={typeForm.description}
                                        onChange={(event) => setTypeForm((current) => ({ ...current, description: event.target.value }))}
                                        rows={4}
                                        className={textareaClassName}
                                        placeholder="Description du type"
                                    />
                                </Field>
                                </div>
                            </div>

                            <div className="mb-4 flex shrink-0 flex-col gap-3 border-t border-[#e2e8f0] bg-white px-5 py-4 pb-6 sm:flex-row sm:justify-end">
                                <Button type="button" variant="outline" onClick={closeFormModal} className="rounded-xl border-[#c8d4de]">
                                    Annuler
                                </Button>
                                <Button type="submit" className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]" disabled={submitting}>
                                    {submitting ? 'Enregistrement...' : typeFormMode === 'edit' ? 'Mettre à  jour' : 'Créer'}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>

                <Dialog open={activeForm === 'intervention'} onOpenChange={(open) => (!open ? closeFormModal() : null)}>
                    <DialogContent className="sm:max-w-3xl max-h-[90vh] overflow-hidden p-0">
                        <div className="border-b border-[#c8d4de] px-5 py-4">
                            <DialogHeader>
                                <DialogTitle className="text-[#0f172a]">
                                    {interventionFormMode === 'edit' ? 'Modifier l\u2019intervention' : 'Nouvelle intervention'}
                                </DialogTitle>
                                <DialogDescription className="text-[#5f7182]">
                                    {interventionFormMode === 'edit'
                                        ? 'Corrigez les informations de l\u2019intervention puis validez.'
                                        : 'Programmez une intervention avec une ou plusieurs lignes d’intervention.'}
                                </DialogDescription>
                            </DialogHeader>
                        </div>

                        <form className="flex max-h-[calc(90vh-76px)] flex-col overflow-hidden" onSubmit={submitForm}>
                            {formFeedback ? (
                                <div
                                    className={`rounded-xl px-4 py-3 text-sm ${
                                        formFeedback.type === 'success'
                                            ? 'border border-[#c8e6c9] bg-[#eef8df] text-[#245b00]'
                                            : 'border border-[#f4c7c3] bg-[#fdecec] text-[#b42318]'
                                    }`}
                                >
                                    {formFeedback.message}
                                </div>
                            ) : null}

                            <div className="flex-1 space-y-5 overflow-y-auto p-5 pr-3">
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <Field label="Titre" required>
                                        <Input
                                            value={interventionForm.titre}
                                            onChange={(event) => setInterventionForm((current) => ({ ...current, titre: event.target.value }))}
                                            className={inputClassName}
                                            placeholder="Ex. Réparation fuite"
                                        />
                                    </Field>

                                    <SharedComboboxField
                                        label="Propriétaire"
                                        required
                                        value={interventionForm.proprietaire_id}
                                        placeholder="Sélectionner un propriétaire"
                                        options={searchableProprietorOptions}
                                        open={proprietaireOpen}
                                        onOpenChange={setProprietaireOpen}
                                        onSearchChange={setProprietaireSearch}
                                        searchValue={proprietaireSearch}
                                        onSelect={(value) => {
                                            setInterventionForm((current) => ({
                                                ...current,
                                                proprietaire_id: value,
                                                lot_id: '',
                                                propriete_id: '',
                                                batiment_id: '',
                                                porte_id: '',
                                            }));
                                            setLotSearch('');
                                            setProprieteSearch('');
                                            setBatimentSearch('');
                                            setPorteSearch('');
                                        }}
                                        emptyLabel="Aucun propriétaire trouvé"
                                    />

                                    <SharedComboboxField
                                        label="Lot"
                                        disabled={!interventionForm.proprietaire_id}
                                        value={interventionForm.lot_id}
                                        placeholder="Sélectionner un lot"
                                        options={searchableLotOptions}
                                        open={lotOpen}
                                        onOpenChange={setLotOpen}
                                        onSearchChange={setLotSearch}
                                        searchValue={lotSearch}
                                        onSelect={(value) => {
                                            setInterventionForm((current) => ({
                                                ...current,
                                                lot_id: value,
                                                propriete_id: '',
                                                batiment_id: '',
                                                porte_id: '',
                                            }));
                                            setProprieteSearch('');
                                            setBatimentSearch('');
                                            setPorteSearch('');
                                        }}
                                        emptyLabel="Aucun lot trouvé"
                                    />

                                    <SharedComboboxField
                                        label="Propriété"
                                        disabled={!interventionForm.lot_id}
                                        value={interventionForm.propriete_id}
                                        placeholder="Sélectionner une propriété"
                                        options={searchableProprieteOptions}
                                        open={proprieteOpen}
                                        onOpenChange={setProprieteOpen}
                                        onSearchChange={setProprieteSearch}
                                        searchValue={proprieteSearch}
                                        onSelect={(value) => {
                                            setInterventionForm((current) => ({
                                                ...current,
                                                propriete_id: value,
                                                batiment_id: '',
                                                porte_id: '',
                                            }));
                                            setBatimentSearch('');
                                            setPorteSearch('');
                                        }}
                                        emptyLabel="Aucune propriété trouvée"
                                    />

                                    <SharedComboboxField
                                        label="Bâtiment"
                                        disabled={!interventionForm.propriete_id}
                                        value={interventionForm.batiment_id}
                                        placeholder="Sélectionner un bâtiment"
                                        options={searchableBatimentOptions}
                                        open={batimentOpen}
                                        onOpenChange={setBatimentOpen}
                                        onSearchChange={setBatimentSearch}
                                        searchValue={batimentSearch}
                                        onSelect={(value) => {
                                            setInterventionForm((current) => ({
                                                ...current,
                                                batiment_id: value,
                                                porte_id: '',
                                            }));
                                            setPorteSearch('');
                                        }}
                                        emptyLabel="Aucun bâtiment trouvé"
                                    />

                                    <SharedComboboxField
                                        label="Porte"
                                        disabled={!interventionForm.batiment_id}
                                        value={interventionForm.porte_id}
                                        placeholder="Sélectionner une porte"
                                        options={searchablePorteOptions}
                                        open={porteOpen}
                                        onOpenChange={setPorteOpen}
                                        onSearchChange={setPorteSearch}
                                        searchValue={porteSearch}
                                        onSelect={(value) => setInterventionForm((current) => ({ ...current, porte_id: value }))}
                                        emptyLabel="Aucune porte trouvée"
                                    />

                                    <Field label="Prise en charge">
                                        <Select
                                            value={interventionForm.prise_en_charge_par}
                                            onValueChange={(value) => setInterventionForm((current) => ({ ...current, prise_en_charge_par: value }))}
                                        >
                                            <SelectTrigger className={inputClassName}>
                                                <SelectValue placeholder="Sélectionner" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="proprietaire">Propriétaire</SelectItem>
                                                <SelectItem value="locataire">Locataire</SelectItem>
                                                <SelectItem value="agence">Agence</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </Field>

                                </div>

                                <div className="space-y-4 rounded-2xl border border-slate-200 bg-white p-4">
                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <h3 className="text-sm font-semibold text-slate-900">Lignes d’intervention</h3>
                                            <p className="text-xs text-slate-500">Ajoutez une ou plusieurs lignes pour cette intervention.</p>
                                        </div>

                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={addInterventionAction}
                                            className="h-9 rounded-xl border-[#c8d4de] text-[#334155] hover:border-[#00559b] hover:bg-[#eaf4fb] hover:text-[#00559b]"
                                        >
                                            <Plus className="mr-2 h-4 w-4" />
                                            Ajouter une ligne
                                        </Button>
                                    </div>

                                    <div className="space-y-4">
                                        {interventionForm.actions.map((action, index) => {
                                            const actionTypeOptions = getTypeInterventionOptionsByCategory(action.maintenance_category_id);
                                            const isMaintenancierOpen = maintenancierOpen && maintenancierTargetId === action.id;

                                            return (
                                                <div key={action.id} className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                        <div>
                                                            <p className="text-sm font-semibold text-slate-900">Ligne {index + 1}</p>
                                                            <p className="text-xs text-slate-500">Définissez le travail à réaliser pour cette ligne.</p>
                                                        </div>

                                                        <Button
                                                            type="button"
                                                            variant="outline"
                                                            onClick={() => removeInterventionAction(action.id)}
                                                            className="h-9 rounded-xl border-[#fecaca] text-[#dc2626] hover:border-[#dc2626] hover:bg-[#fef2f2] hover:text-[#dc2626]"
                                                        >
                                                            <Trash2 className="mr-2 h-4 w-4" />
                                                            Supprimer
                                                        </Button>
                                                    </div>

                                                    <div className="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                                                        <Field label="Catégorie">
                                                            <Select
                                                                value={action.maintenance_category_id}
                                                                onValueChange={(value) =>
                                                                    updateInterventionAction(action.id, {
                                                                        maintenance_category_id: value,
                                                                        type_intervention_id: '',
                                                                    })
                                                                }
                                                            >
                                                                <SelectTrigger className={inputClassName}>
                                                                    <SelectValue placeholder="Toutes les catégories" />
                                                                </SelectTrigger>
                                                                <SelectContent>
                                                                    <SelectItem value="all">Toutes les catégories</SelectItem>
                                                                    {maintenanceCategoryOptions.map((item) => (
                                                                        <SelectItem key={item.value} value={item.value}>
                                                                            {item.label}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectContent>
                                                            </Select>
                                                        </Field>

                                                        <Field label="Type d'intervention" required>
                                                            <Select
                                                                value={action.type_intervention_id}
                                                                onValueChange={(value) => {
                                                                    const selectedType = typeInterventionOptions.find((item) => item.value === value);

                                                                    updateInterventionAction(action.id, {
                                                                        type_intervention_id: value,
                                                                        maintenance_category_id: selectedType?.maintenance_category_id || action.maintenance_category_id,
                                                                    });
                                                                }}
                                                            >
                                                                <SelectTrigger className={inputClassName}>
                                                                    <SelectValue placeholder="Sélectionner" />
                                                                </SelectTrigger>
                                                                <SelectContent>
                                                                    {actionTypeOptions.map((item) => (
                                                                        <SelectItem key={item.value} value={item.value}>
                                                                            {item.label}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectContent>
                                                            </Select>
                                                        </Field>

                                                        <SharedComboboxField
                                                            label="Maintenancier"
                                                            required
                                                            value={action.maintenancier_id}
                                                            placeholder="Sélectionner un maintenancier"
                                                            options={searchableMaintenancierOptions}
                                                            open={isMaintenancierOpen}
                                                            onOpenChange={(open) => {
                                                                if (open) {
                                                                    setMaintenancierTargetId(action.id);
                                                                    setMaintenancierSearch('');
                                                                    setMaintenancierOpen(true);
                                                                    return;
                                                                }

                                                                if (maintenancierTargetId === action.id) {
                                                                    setMaintenancierOpen(false);
                                                                    setMaintenancierTargetId('');
                                                                    setMaintenancierSearch('');
                                                                }
                                                            }}
                                                            onSearchChange={(value) => {
                                                                if (maintenancierTargetId === action.id) {
                                                                    setMaintenancierSearch(value);
                                                                }
                                                            }}
                                                            searchValue={isMaintenancierOpen ? maintenancierSearch : ''}
                                                            onSelect={(value) => updateInterventionAction(action.id, { maintenancier_id: value })}
                                                            emptyLabel="Aucun maintenancier trouvé"
                                                        />

                                                        <Field label="Date de début" required>
                                                            <Input
                                                                type="date"
                                                                value={action.date_debut}
                                                                onChange={(event) =>
                                                                    updateInterventionAction(action.id, { date_debut: event.target.value })
                                                                }
                                                                className={inputClassName}
                                                            />
                                                        </Field>

                                                        <Field label="Date de fin">
                                                            <Input
                                                                type="date"
                                                                value={action.date_fin}
                                                                onChange={(event) => updateInterventionAction(action.id, { date_fin: event.target.value })}
                                                                className={inputClassName}
                                                            />
                                                        </Field>

                                                        <Field label="Priorité">
                                                            <Select
                                                                value={action.priorite}
                                                                onValueChange={(value) => updateInterventionAction(action.id, { priorite: value })}
                                                            >
                                                                <SelectTrigger className={inputClassName}>
                                                                    <SelectValue placeholder="Sélectionner" />
                                                                </SelectTrigger>
                                                                <SelectContent>
                                                                    <SelectItem value="basse">Basse</SelectItem>
                                                                    <SelectItem value="normale">Normale</SelectItem>
                                                                    <SelectItem value="haute">Haute</SelectItem>
                                                                </SelectContent>
                                                            </Select>
                                                        </Field>

                                                        <Field label="Prix">
                                                            <Input
                                                                type="number"
                                                                min="0"
                                                                value={action.prix}
                                                                onChange={(event) => updateInterventionAction(action.id, { prix: event.target.value })}
                                                                className={inputClassName}
                                                                placeholder="Montant"
                                                            />
                                                        </Field>

                                                        <Field label="Description" className="md:col-span-2">
                                                            <textarea
                                                                value={action.description}
                                                                onChange={(event) =>
                                                                    updateInterventionAction(action.id, { description: event.target.value })
                                                                }
                                                                rows={3}
                                                                className={textareaClassName}
                                                                placeholder="Détail du travail"
                                                            />
                                                        </Field>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            </div>

                            <div className="mb-6 flex shrink-0 flex-col gap-3 border-t border-[#e2e8f0] bg-white px-5 py-4 pb-6 sm:flex-row sm:justify-end">
                                <Button type="button" variant="outline" onClick={closeFormModal} className="rounded-xl border-[#c8d4de]">
                                    Annuler
                                </Button>
                                <Button type="submit" className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]" disabled={submitting}>
                                    {submitting ? 'Enregistrement...' : interventionFormMode === 'edit' ? 'Mettre à jour' : 'Créer'}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </section>
        </AgenceLayout>
    );
}
