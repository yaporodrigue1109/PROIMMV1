import { useEffect, useMemo, useRef, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import {
    ArrowLeft,
    Check,
    CalendarDays,
    FileText,
    Home,
    ChevronRight,
    Plus,
    Save,
    Trash2,
    UserRound,
    Wallet,
} from 'lucide-react';
import { usePage } from '@inertiajs/react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Button } from '../../../components/ui/button';
import { Checkbox } from '../../../components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
import { Separator } from '../../../components/ui/separator';
import { agenceButtonStyles } from '../../../lib/buttonStyles';
import { cn } from '../../../lib/utils';
import flags from 'react-phone-number-input/flags';
import PhoneInputBase from 'react-phone-number-input';
import 'react-phone-number-input/style.css';

const today = () => new Date().toISOString().slice(0, 10);

const toId = (value) => (value === null || value === undefined || value === '' ? '' : String(value));

const asArray = (value) => (Array.isArray(value) ? value : []);

const normalizePhoneValue = (value) => (value ? String(value) : '');

const toDateInput = (value) => {
    if (!value) return '';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value).slice(0, 10);

    return date.toISOString().slice(0, 10);
};

const toMonthInput = (value) => {
    if (!value) return '';
    if (typeof value === 'string' && /^\d{4}-\d{2}$/.test(value)) return value;

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    return `${year}-${month}`;
};

const formatArriereMonthLabel = (value) => {
    const normalized = toMonthInput(value);
    if (!normalized) return '';

    const date = new Date(`${normalized}-01T00:00:00`);
    if (Number.isNaN(date.getTime())) return normalized;

    return new Intl.DateTimeFormat('fr-FR', { month: 'long', year: 'numeric' })
        .format(date)
        .toLowerCase()
        .replace(/\s+/g, '-');
};

const DEFAULT_PERIODICITE_PAIEMENT_ID = '3';

const buildArriereMonthOptions = (count = 24) => {
    const options = [];
    const baseDate = new Date();

    for (let offset = 1; offset <= count; offset += 1) {
        const date = new Date(baseDate.getFullYear(), baseDate.getMonth() - offset, 1);
        const value = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
        options.push({
            value,
            label: formatArriereMonthLabel(value),
        });
    }

    return options;
};

const formatMoney = (value) => new Intl.NumberFormat('fr-FR').format(Number(value ?? 0));

const toTitleCase = (value) =>
    String(value ?? '')
        .toLowerCase()
        .split(/\s+/)
        .filter(Boolean)
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');

const calculateMontantGlobalGarantie = ({
    loyerNet = 0,
    caution = 0,
    avance = 0,
    agence = 0,
    cautionCie = 0,
    cautionSodeci = 0,
    fraisDeDossier = 0,
}) =>
    toMoneyNumber(loyerNet) * (toMoneyNumber(caution) + toMoneyNumber(avance) + toMoneyNumber(agence))
    + toMoneyNumber(cautionCie)
    + toMoneyNumber(cautionSodeci)
    + toMoneyNumber(fraisDeDossier);

const normalizeWholeNumberInput = (value) => String(value ?? '').replace(/[^\d]/g, '');

const toWholeNumber = (value) => {
    if (value === '' || value === null || value === undefined) return null;

    const parsed = Number.parseInt(String(value), 10);
    return Number.isNaN(parsed) ? null : parsed;
};

const normalizePeopleCount = (value, minValue = 0) => {
    const parsed = toWholeNumber(value);
    if (parsed === null) return '';

    return String(Math.max(parsed, minValue));
};

const emptyArriere = () => ({
    mois: '',
    montant: '',
});

const emptyDepotVersement = (modePaiementId = '') => ({
    montant: '',
    date_versement: today(),
    mode_paiement_id: modePaiementId,
});

const toMoneyNumber = (value) => Number(value ?? 0) || 0;

const normalizeDepotVersement = (versement, modePaiementId = '') => ({
    montant: versement?.montant ?? '',
    date_versement: toDateInput(versement?.date_versement) || today(),
    mode_paiement_id: toId(versement?.mode_paiement_id) || modePaiementId,
});

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

function InfoCard({ label, value }) {
    return (
        <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] px-3 py-2">
            <p className="text-xs uppercase tracking-wide text-[#94a3b8]">{label}</p>
            <p className="mt-1 text-sm font-semibold text-[#0f172a]">{value || 'Non renseigné'}</p>
        </div>
    );
}

function BooleanRadioField({ label, name, value, onChange, className, trueLabel = 'Oui', falseLabel = 'Non' }) {
    return (
        <div className={cn('space-y-2', className)}>
            <p className="block text-sm font-medium text-[#0f172a]">{label}</p>
            <div className="flex flex-wrap gap-4">
                <label className="flex cursor-pointer items-center gap-2 rounded-xl border border-[#e2e8f0] bg-white px-3 py-2 text-sm text-[#0f172a] transition-colors hover:border-[#00559b]">
                    <input
                        type="radio"
                        name={name}
                        checked={value === true}
                        onChange={() => onChange(true)}
                        className="h-4 w-4 border-[#c8d4de] text-[#00559b] focus:ring-[#00559b]"
                    />
                    {trueLabel}
                </label>
                <label className="flex cursor-pointer items-center gap-2 rounded-xl border border-[#e2e8f0] bg-white px-3 py-2 text-sm text-[#0f172a] transition-colors hover:border-[#00559b]">
                    <input
                        type="radio"
                        name={name}
                        checked={value === false}
                        onChange={() => onChange(false)}
                        className="h-4 w-4 border-[#c8d4de] text-[#00559b] focus:ring-[#00559b]"
                    />
                    {falseLabel}
                </label>
            </div>
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
                        {description ? <CardDescription className="text-xs text-[#5f7182]">{description}</CardDescription> : null}
                    </div>
                </div>
                {action}
            </CardHeader>
            <CardContent className="p-6">{children}</CardContent>
        </Card>
    );
}

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

const PhoneInput = ({ label, required, error, value, onChange, placeholder }) => {
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
};

function formatContractDoor(door) {
    if (!door) return 'Aucune porte sélectionnée';

    const type = door.typePorte?.libelle ?? door.typePorte?.name ?? door.type_porte?.name ?? 'Type non précisé';
    return `${door.numero_porte ?? 'Porte'}`;
}

function contractDefaultsFromDoor(door) {
    const loyerNet = door?.mt_loyer ?? door?.tarifActif?.mt_loyer ?? '';
    const caution = door?.caution ?? door?.tarifActif?.mt_caution ?? 0;
    const avance = door?.avance ?? door?.tarifActif?.mt_avance ?? 0;
    const agence = door?.agence ?? door?.tarifActif?.mt_frais_agence ?? 0;
    const cautionCie = door?.mt_caution_cie ?? door?.tarifActif?.mt_caution_cie ?? 0;
    const cautionSodeci = door?.mt_caution_sodeci ?? door?.tarifActif?.mt_caution_sodeci ?? 0;
    const fraisDeDossier = door?.mt_frais_dossier ?? door?.tarifActif?.mt_frais_dossier ?? 0;

    return {
        loyer_net: loyerNet,
        caution,
        avance,
        agence,
        caution_cie: cautionCie,
        caution_sodeci: cautionSodeci,
        frais_de_dossier: fraisDeDossier,
        montant_global_garantie: calculateMontantGlobalGarantie({
            loyerNet,
            caution,
            avance,
            agence,
            cautionCie,
            cautionSodeci,
            fraisDeDossier,
        }),
    };
}

function buildInitialData(locataire, isEdit) {
    const contrat = asArray(locataire?.contrats).find((item) => item.is_active) ?? asArray(locataire?.contrats)[0] ?? null;
    const versementsDepotGarantie = asArray(contrat?.versements_depot_garantie);
    const hasRepresentant = Boolean(
        contrat?.has_representant ||
            contrat?.name_representant ||
            contrat?.adresse_representant ||
            contrat?.contant_representant
    );

    return {
        name: locataire?.name ? toTitleCase(locataire.name) : '',
        tel1: normalizePhoneValue(locataire?.tel1),
        tel2: normalizePhoneValue(locataire?.tel2),
        email: locataire?.email ? String(locataire.email).toLowerCase() : '',
        genre_id: toId(locataire?.genre_id),
        date_naissance: toDateInput(locataire?.date_naissance),
        lieu_naissance: locataire?.lieu_naissance ?? '',
        nationalite: locataire?.nationalite ?? '',
        profession: locataire?.profession ?? '',
        adresse: locataire?.adresse ?? '',
        region_id: toId(locataire?.region_id),
        ville_id: toId(locataire?.ville_id),
        type_piece_id: toId(locataire?.type_piece_id),
        num_piece: locataire?.num_piece ? String(locataire.num_piece).toUpperCase() : '',
        date_expiration_piece: toDateInput(locataire?.date_expiration_piece),
        photo: null,
        image_pice: null,
        a_des_arrieres: false,
        is_new: contrat?.is_new ?? !isEdit,
        arrieres: [],
        contrat: {
            proprietaire_id: toId(contrat?.proprietaire_id),
            lot_id: toId(contrat?.lot_id ?? contrat?.propriete?.lot_id),
            propriete_id: toId(contrat?.propriete_id),
            batiment_id: toId(contrat?.batiment_id),
            porte_id: toId(contrat?.porte_id),
            loyer_net: contrat?.loyer_net ?? '',
            caution: contrat?.caution ?? 0,
            avance: contrat?.avance ?? 0,
            agence: contrat?.agence ?? 0,
            caution_cie: contrat?.caution_cie ?? 0,
            caution_sodeci: contrat?.caution_sodeci ?? 0,
            frais_de_dossier: contrat?.frais_de_dossier ?? 0,
            montant_global_garantie: contrat?.montant_global_garantie ?? 0,
            date_debut_bail: toDateInput(contrat?.date_debut_bail) || today(),
            date_entree: toDateInput(contrat?.date_entree) || today(),
            date_signature_bail: toDateInput(contrat?.date_signature_bail) || today(),
            nbre_personne: contrat?.nbre_personne ?? 1,
            nbre_enfant: contrat?.nbre_enfant ?? 0,
            periodicite_paiement_id: toId(contrat?.periodicite_paiement_id ?? contrat?.mode_paiement_id ?? DEFAULT_PERIODICITE_PAIEMENT_ID),
            mode_paiement_id: toId(contrat?.mode_paiement_id),
            has_representant: hasRepresentant,
            name_representant: contrat?.name_representant ? toTitleCase(contrat.name_representant) : '',
            adresse_representant: contrat?.adresse_representant ?? '',
            contant_representant: contrat?.contant_representant ?? '',
            versements_depot_garantie: versementsDepotGarantie.length
                ? versementsDepotGarantie.map((versement) => normalizeDepotVersement(versement))
                : [emptyDepotVersement()],
        },
    };
}

export default function Form({
    mode = 'create',
    locataire = null,
    genres = [],
    typePiece = [],
    regions = [],
    villes = [],
    proprio = [],
    periodicitePaiement = [],
    modePaiement = [],
}) {
    const isEdit = mode === 'edit';
    const { flash } = usePage().props;
    const serverError = flash?.error ?? '';

    const { data, setData, post, put, processing, errors } = useForm(buildInitialData(locataire, isEdit));
    const [showArrieres, setShowArrieres] = useState(Boolean(data.a_des_arrieres));
    const [current, setCurrent] = useState(0);
    const [completed, setCompleted] = useState([]);
    const [stepErrors, setStepErrors] = useState({});
    const arriereMonthOptions = useMemo(() => buildArriereMonthOptions(24), []);

    const ownerOptions = useMemo(() => asArray(proprio), [proprio]);

    const selectedOwner = useMemo(
        () => ownerOptions.find((owner) => toId(owner.proprietaire_id) === toId(data.contrat.proprietaire_id)) ?? null,
        [data.contrat.proprietaire_id, ownerOptions]
    );

    const lotOptions = useMemo(
        () => asArray(selectedOwner?.lots),
        [selectedOwner]
    );

    const selectedLot = useMemo(
        () => lotOptions.find((lot) => toId(lot.propreietaire_lot_id ?? lot.id) === toId(data.contrat.lot_id)) ?? null,
        [data.contrat.lot_id, lotOptions]
    );

    const propertyOptions = useMemo(
        () => asArray(selectedLot?.proprietes),
        [selectedLot]
    );

    const selectedProperty = useMemo(
        () => propertyOptions.find((property) => toId(property.propriete_id) === toId(data.contrat.propriete_id)) ?? null,
        [data.contrat.propriete_id, propertyOptions]
    );

    const buildingOptions = useMemo(
        () => asArray(selectedProperty?.batiments),
        [selectedProperty]
    );

    const selectedBuilding = useMemo(
        () => buildingOptions.find((building) => toId(building.batiment_id) === toId(data.contrat.batiment_id)) ?? null,
        [buildingOptions, data.contrat.batiment_id]
    );

    const doorOptions = useMemo(
        () => asArray(selectedBuilding?.portes),
        [selectedBuilding]
    );

    const selectedDoor = useMemo(
        () => doorOptions.find((door) => toId(door.porte_id) === toId(data.contrat.porte_id)) ?? null,
        [doorOptions, data.contrat.porte_id]
    );

    const canShowContractAmounts = Boolean(
        selectedOwner && selectedLot && selectedProperty && selectedBuilding && selectedDoor
    );
    const showDepotGarantie = Boolean(data.is_new);
    const totalArrieres = useMemo(
        () => data.arrieres.reduce((sum, arriere) => sum + toMoneyNumber(arriere.montant), 0),
        [data.arrieres]
    );

    const handleLocataireTypeChange = (checked) => {
        const isNew = Boolean(checked);

        setData('is_new', isNew);
        setData('contrat', {
            ...data.contrat,
            versements_depot_garantie: isNew
                ? (data.contrat.versements_depot_garantie.length ? data.contrat.versements_depot_garantie : [emptyDepotVersement(modePaiementOptions[0]?.value ?? '')])
                : [],
        });
    };

    const handleArriereVisibilityChange = (checked) => {
        const hasArrieres = Boolean(checked);

        setShowArrieres(hasArrieres);
        setData('a_des_arrieres', hasArrieres);
        setData('arrieres', hasArrieres ? (data.arrieres.length ? data.arrieres : [emptyArriere()]) : []);
    };

    const handleDepotVersementMontantBlur = (index) => {
        const montant = toMoneyNumber(data.contrat.versements_depot_garantie[index]?.montant);
        const plafond = toMoneyNumber(data.contrat.montant_global_garantie);

        if (montant > plafond) {
            updateDepotVersement(index, { montant: String(plafond) });
        }
    };

    const updateContrat = (patch) => {
        setData('contrat', { ...data.contrat, ...patch });
    };

    const updateRepresentantVisibility = (checked) => {
        updateContrat({
            has_representant: checked,
            ...(checked
                ? {}
                : {
                      name_representant: '',
                      adresse_representant: '',
                      contant_representant: '',
                  }),
        });
    };

    const updateArriere = (index, patch) => {
        setData(
            'arrieres',
            data.arrieres.map((item, i) => (i === index ? { ...item, ...patch } : item))
        );
    };

    const addArriere = () => setData('arrieres', [...data.arrieres, emptyArriere()]);

    const removeArriere = (index) => {
        const next = data.arrieres.filter((_, i) => i !== index);
        setData('arrieres', next.length ? next : [emptyArriere()]);
    };

    const updateDepotVersement = (index, patch) => {
        updateContrat({
            versements_depot_garantie: data.contrat.versements_depot_garantie.map((item, i) =>
                i === index ? { ...item, ...patch } : item
            ),
        });
    };

    const normalizePeopleFields = (patch = {}) => {
        const nextPersonne = patch.nbre_personne ?? data.contrat.nbre_personne;
        const nextEnfant = patch.nbre_enfant ?? data.contrat.nbre_enfant;

        const normalizedPersonne = normalizePeopleCount(nextPersonne, 1);
        const normalizedPersonneNumber = toWholeNumber(normalizedPersonne) ?? 1;
        const maxEnfant = Math.max(normalizedPersonneNumber - 1, 0);
        const normalizedEnfant = normalizePeopleCount(nextEnfant, 0);
        const normalizedEnfantNumber = toWholeNumber(normalizedEnfant);

        return {
            nbre_personne: normalizedPersonne,
            nbre_enfant:
                normalizedEnfantNumber === null
                    ? ''
                    : String(Math.min(normalizedEnfantNumber, maxEnfant)),
        };
    };

    const handleOwnerChange = (value) => {
        updateContrat({
            proprietaire_id: value,
            lot_id: '',
            propriete_id: '',
            batiment_id: '',
            porte_id: '',
            versements_depot_garantie: [emptyDepotVersement(modePaiementOptions[0]?.value ?? '')],
            ...contractDefaultsFromDoor(null),
        });
    };

    const handleLotChange = (value) => {
        updateContrat({
            lot_id: value,
            propriete_id: '',
            batiment_id: '',
            porte_id: '',
            versements_depot_garantie: [emptyDepotVersement(modePaiementOptions[0]?.value ?? '')],
            ...contractDefaultsFromDoor(null),
        });
    };

    const handlePropertyChange = (value) => {
        updateContrat({
            propriete_id: value,
            batiment_id: '',
            porte_id: '',
            versements_depot_garantie: [emptyDepotVersement(modePaiementOptions[0]?.value ?? '')],
            ...contractDefaultsFromDoor(null),
        });
    };

    const handleBuildingChange = (value) => {
        updateContrat({
            batiment_id: value,
            porte_id: '',
            versements_depot_garantie: [emptyDepotVersement(modePaiementOptions[0]?.value ?? '')],
            ...contractDefaultsFromDoor(null),
        });
    };

    const handleDoorChange = (value) => {
        const door = doorOptions.find((item) => toId(item.porte_id) === value) ?? null;
        updateContrat({
            porte_id: value,
            versements_depot_garantie: [emptyDepotVersement(modePaiementOptions[0]?.value ?? '')],
            ...contractDefaultsFromDoor(door),
        });
    };

    const handleRegionChange = (value) => {
        setData((current) => ({ ...current, region_id: value, ville_id: '' }));
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        const locataireId = locataire?.locataire_id ?? locataire?.id;
        const hasFiles = data.photo instanceof File || data.image_pice instanceof File;

        if (isEdit && locataireId) {
            put(`/agence/locataires/${locataireId}`, {
                preserveScroll: true,
                forceFormData: hasFiles,
            });
            return;
        }

        post('/agence/locataires', {
            preserveScroll: true,
            forceFormData: hasFiles,
        });
    };

    const regionOptions = useMemo(
        () => asArray(regions).map((region) => ({ value: toId(region.id), label: region.name })),
        [regions]
    );

    const villeOptions = useMemo(
        () => asArray(villes)
            .filter((ville) => !data.region_id || toId(ville.region_id) === toId(data.region_id))
            .map((ville) => ({ value: toId(ville.id), label: ville.name })),
        [data.region_id, villes]
    );

    const typePieceOptions = useMemo(
        () => asArray(typePiece).map((piece) => ({ value: toId(piece.type_pieces_id ?? piece.id), label: piece.name })),
        [typePiece]
    );

    const modePaiementOptions = useMemo(
        () => asArray(modePaiement).map((item) => ({ value: toId(item.id), label: item.name })),
        [modePaiement]
    );

    const periodicitePaiementOptions = useMemo(
        () => asArray(periodicitePaiement).map((item) => ({ value: toId(item.id), label: item.name })),
        [periodicitePaiement]
    );

    const defaultPeriodicitePaiement = useMemo(
        () =>
            periodicitePaiementOptions.find((option) => option.value === DEFAULT_PERIODICITE_PAIEMENT_ID) ??
            periodicitePaiementOptions.find((option) => option.label.toLowerCase().includes('mensuel')) ??
            periodicitePaiementOptions[0] ??
            null,
        [periodicitePaiementOptions]
    );

    useEffect(() => {
        if (!toId(data.contrat.periodicite_paiement_id) && defaultPeriodicitePaiement) {
            updateContrat({ periodicite_paiement_id: defaultPeriodicitePaiement.value });
        }
    }, [data.contrat.periodicite_paiement_id, defaultPeriodicitePaiement]);

    useEffect(() => {
        if (!modePaiementOptions.length) return;

        const defaultMode = modePaiementOptions[0].value;
        const versements = data.contrat.versements_depot_garantie ?? [];
        const normalized = versements.map((versement) => ({
            ...versement,
            mode_paiement_id: toId(versement.mode_paiement_id) || defaultMode,
        }));

        const changed =
            normalized.length !== versements.length ||
            normalized.some((versement, index) => toId(versements[index]?.mode_paiement_id) !== toId(versement.mode_paiement_id));

        if (changed) {
            updateContrat({ versements_depot_garantie: normalized });
        }
    }, [data.contrat.versements_depot_garantie, modePaiementOptions]);

    const currentContract = useMemo(
        () => asArray(locataire?.contrats).find((item) => item.is_active) ?? asArray(locataire?.contrats)[0] ?? null,
        [locataire]
    );

    const fieldErrors = { ...errors, ...stepErrors };

    const steps = [
        { key: 'identity', title: 'Identité', subtitle: 'Coordonnées' },
        { key: 'piece', title: 'Pièce', subtitle: 'Documents' },
        { key: 'contract', title: 'Contrat', subtitle: 'Location' },
        { key: 'arrears', title: 'Arriérés', subtitle: 'Historique' },
        { key: 'recap', title: 'Récapitulatif', subtitle: 'Vérification' },
    ];

    const goTo = (index) => {
        setStepErrors({});
        setCurrent(index);
        if (typeof window !== 'undefined') window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const validateStep = (index) => {
        const localErrors = {};

        if (index === 0) {
            if (!data.name.trim()) localErrors.name = 'Le nom du locataire est obligatoire.';
            if (!data.tel1.trim()) localErrors.tel1 = 'Le téléphone principal est obligatoire.';
            if (!toId(data.genre_id)) localErrors.genre_id = 'Le genre est obligatoire.';
        }

        if (index === 1) {
            if (!toId(data.type_piece_id)) localErrors.type_piece_id = 'Le type de pièce est obligatoire.';
            if (!data.num_piece.trim()) localErrors.num_piece = 'Le numéro de pièce est obligatoire.';
            if (!data.date_expiration_piece) localErrors.date_expiration_piece = "La date d'expiration est obligatoire.";
            if (data.date_expiration_piece && data.date_expiration_piece < today()) {
                localErrors.date_expiration_piece = "La date d'expiration ne peut pas être antérieure à aujourd'hui.";
            }
        }

        if (index === 2 && !isEdit) {
            if (!toId(data.contrat.proprietaire_id)) localErrors['contrat.proprietaire_id'] = 'Sélectionnez un propriétaire.';
            if (!toId(data.contrat.lot_id)) localErrors['contrat.lot_id'] = 'Sélectionnez un lot.';
            if (!toId(data.contrat.propriete_id)) localErrors['contrat.propriete_id'] = 'Sélectionnez une propriété.';
            if (!toId(data.contrat.batiment_id)) localErrors['contrat.batiment_id'] = 'Sélectionnez un bâtiment.';
            if (!toId(data.contrat.porte_id)) localErrors['contrat.porte_id'] = 'Sélectionnez une porte.';
            if (!data.contrat.date_entree) localErrors['contrat.date_entree'] = "La date d'entrée est obligatoire.";
            if (!toId(data.contrat.periodicite_paiement_id)) localErrors['contrat.periodicite_paiement_id'] = 'Sélectionnez une périodicité de paiement.';

            const nbrePersonne = toWholeNumber(data.contrat.nbre_personne);
            const nbreEnfant = toWholeNumber(data.contrat.nbre_enfant);

            if (nbrePersonne === null) {
                localErrors['contrat.nbre_personne'] = 'Le nombre de personnes est obligatoire.';
            } else if (nbrePersonne < 1) {
                localErrors['contrat.nbre_personne'] = 'Le nombre de personnes doit être au moins de 1.';
            }

            if (nbreEnfant === null) {
                localErrors['contrat.nbre_enfant'] = "Le nombre d'enfants est obligatoire.";
            } else if (nbreEnfant < 0) {
                localErrors['contrat.nbre_enfant'] = "Le nombre d'enfants doit être supérieur ou égal à 0.";
            }

            if (nbrePersonne !== null && nbreEnfant !== null && nbreEnfant >= nbrePersonne) {
                localErrors['contrat.nbre_enfant'] = "Le nombre d'enfants doit être strictement inférieur au nombre de personnes.";
            }

            if (data.is_new && !data.contrat.versements_depot_garantie.length) {
                localErrors['contrat.versements_depot_garantie'] = 'Ajoutez au moins un versement du dépôt de garantie.';
            }

            const montantGlobalGarantie = toMoneyNumber(data.contrat.montant_global_garantie);

            if (data.is_new) {
                data.contrat.versements_depot_garantie.forEach((versement, versementIndex) => {
                    if (versement.montant === '' || versement.montant === null || versement.montant === undefined) {
                        localErrors[`contrat.versements_depot_garantie.${versementIndex}.montant`] = 'Le montant versé est obligatoire.';
                    } else if (toMoneyNumber(versement.montant) > montantGlobalGarantie) {
                        localErrors[`contrat.versements_depot_garantie.${versementIndex}.montant`] = 'Le montant versé ne peut pas dépasser la garantie globale.';
                    }
                    if (!versement.date_versement) {
                        localErrors[`contrat.versements_depot_garantie.${versementIndex}.date_versement`] = 'La date de versement est obligatoire.';
                    }
                    if (!toId(versement.mode_paiement_id)) {
                        localErrors[`contrat.versements_depot_garantie.${versementIndex}.mode_paiement_id`] = 'Sélectionnez un mode de paiement.';
                    }
                });
            }
        }

        if (index === 3 && !isEdit && showArrieres) {
            if (!data.arrieres.length) {
                localErrors['arrieres.0.mois'] = 'Ajoutez au moins un arriéré.';
            }

            data.arrieres.forEach((arriere, arriereIndex) => {
                if (!arriere.mois) localErrors[`arrieres.${arriereIndex}.mois`] = 'Le mois est obligatoire.';
                if (arriere.mois && !/^\d{4}-\d{2}$/.test(arriere.mois)) {
                    localErrors[`arrieres.${arriereIndex}.mois`] = 'Le mois doit être au format AAAA-MM.';
                }
                if (arriere.montant === '' || arriere.montant === null || arriere.montant === undefined) {
                    localErrors[`arrieres.${arriereIndex}.montant`] = 'Le montant arriéré est obligatoire.';
                }
            });
        }

        return localErrors;
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

    const isLastStep = current === steps.length - 1;

    return (
        <AgenceLayout title={isEdit ? 'Modifier le locataire' : 'Nouveau locataire'}>
            <Head title={isEdit ? 'Modifier le locataire' : 'Nouveau locataire'} />

            <form
                onSubmit={(e) => {
                    e.preventDefault();

                    if (!isLastStep) {
                        handleNext();
                        return;
                    }

                    handleSubmit(e);
                }}
                className="mx-auto flex max-w-6xl flex-col gap-6 pb-10"
            >
                <div className="flex items-center gap-3">
                    <Button asChild type="button" variant="outline" size="icon" className="rounded-xl border-[#c8d4de]">
                        <Link href="/agence/locataires">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-xl font-semibold text-[#0f172a]">
                            {isEdit ? 'Modifier le locataire' : 'Nouveau locataire'}
                        </h1>
                        <p className="text-sm text-[#5f7182]">
                            {isEdit
                                ? 'Les informations de contrat restent affichées à titre indicatif.'
                                : 'Renseignez l’identité du locataire et le contrat de location.'}
                        </p>
                    </div>
                </div>

                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardContent className="mt-7 p-4">
                        <Stepper
                            steps={steps}
                            current={current}
                            completed={completed}
                            onStepClick={goTo}
                            allowAllSteps={isEdit}
                        />
                    </CardContent>
                </Card>

                {serverError ? (
                    <Card className="rounded-2xl border-[#f5c2c7] bg-[#fff5f5] shadow-sm">
                        <CardContent className="p-4">
                            <p className="text-sm font-semibold text-[#b42318]">Erreur Laravel</p>
                            <p className="mt-1 text-sm text-[#7f1d1d]">{serverError}</p>
                        </CardContent>
                    </Card>
                ) : null}

                {Object.keys(fieldErrors).length > 0 ? (
                    <Card className="rounded-2xl border-[#f5c2c7] bg-[#fff5f5] shadow-sm">
                        <CardContent className="p-4">
                            <p className="mt-4 mb-2 text-sm font-semibold text-[#b42318]">
                                Veuillez corriger les erreurs ci-dessous.
                            </p>
                            <ul className="space-y-1 text-sm text-[#7f1d1d]">
                                {Object.values(fieldErrors)
                                    .slice(0, 8)
                                    .map((message, index) => (
                                        <li key={`${message}-${index}`}>• {message}</li>
                                    ))}
                            </ul>
                        </CardContent>
                    </Card>
                ) : null}

                {current === 0 ? (
                    <SectionCard icon={UserRound} title="Identité" description="Informations personnelles du locataire.">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">

                            <Field label="Genre" required className="mt-4 md:col-span-2" error={fieldErrors.genre_id}>
                                <Select value={toId(data.genre_id)} onValueChange={(value) => setData('genre_id', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Sélectionner" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {asArray(genres).map((genre) => (
                                            <SelectItem key={toId(genre.id)} value={toId(genre.id)}>
                                                {genre.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>


                            <Field label="Nom complet" required error={fieldErrors.name} className="mt-4 md:col-span-2">
                                <Input
                                    value={data.name}
                                    onChange={(e) => setData('name', toTitleCase(e.target.value))}
                                    placeholder="Ex: KOUASSI Aya Marie"
                                />
                            </Field>

                            

                            <PhoneInput
                                label="Téléphone 1"
                                required
                                error={fieldErrors.tel1}
                                value={data.tel1}
                                onChange={(e) => setData('tel1', e.target.value ?? '')}
                                placeholder="07 00 00 00 00"
                            />

                            <PhoneInput
                                label="Téléphone 2"
                                error={fieldErrors.tel2}
                                value={data.tel2}
                                onChange={(e) => setData('tel2', e.target.value ?? '')}
                                placeholder="05 00 00 00 00"
                            />

                            <Field label="Email" className="md:col-span-2" error={fieldErrors.email}>
                                <Input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value.toLowerCase())}
                                    placeholder="email@exemple.com"
                                />
                            </Field>

                            <Field label="Profession" className="md:col-span-2" error={fieldErrors.profession}>
                                <Input
                                    value={data.profession}
                                    onChange={(e) => setData('profession', toTitleCase(e.target.value))}
                                    placeholder="Ex: Commerçant"
                                />
                            </Field>

                            <Field label="Nationalité" className="md:col-span-2" error={fieldErrors.nationalite}>
                                <Input
                                    value={data.nationalite}
                                    onChange={(e) => setData('nationalite', e.target.value.toUpperCase())}
                                    placeholder="Ex: Ivoirien"
                                />
                            </Field>

                            <Field label="Date de naissance" error={fieldErrors.date_naissance}>
                                <Input
                                    type="date"
                                    value={data.date_naissance}
                                    onChange={(e) => setData('date_naissance', e.target.value)}
                                />
                            </Field>

                            <Field label="Lieu de naissance" error={fieldErrors.lieu_naissance}>
                                <Input
                                    value={data.lieu_naissance}
                                    onChange={(e) => setData('lieu_naissance', e.target.value)}
                                    placeholder="Ex: Abidjan"
                                />
                            </Field>

                            <Field label="Région" error={fieldErrors.region_id}>
                                <Select
                                    value={toId(data.region_id)}
                                    onValueChange={handleRegionChange}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Sélectionner" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {regionOptions.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>

                            <Field label="Ville" error={fieldErrors.ville_id}>
                                <Select
                                    value={toId(data.ville_id)}
                                    onValueChange={(value) => setData('ville_id', value)}
                                    disabled={!data.region_id}
                                >
                                    <SelectTrigger disabled={!data.region_id}>
                                        <SelectValue placeholder="Sélectionner" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {villeOptions.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>

                            <Field label="Adresse" error={fieldErrors.adresse} className="md:col-span-4">
                                <Input
                                    value={data.adresse}
                                    onChange={(e) => setData('adresse', e.target.value)}
                                    placeholder="Adresse du locataire"
                                />
                            </Field>
                        </div>
                    </SectionCard>
                ) : null}

                {current === 1 ? (
                    <SectionCard icon={FileText} title="Pièce d'identité" description="Type de pièce et documents du locataire.">
                        <div className="grid grid-cols-1  gap-4 md:grid-cols-2 lg:grid-cols-2">
                            <Field label="Type de pièce" className="mt-4" required error={fieldErrors.type_piece_id}>
                                <Select value={toId(data.type_piece_id)} onValueChange={(value) => setData('type_piece_id', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Sélectionner" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {typePieceOptions.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>

                            <Field label="Numéro de pièce" className="mt-4" required error={fieldErrors.num_piece}>
                                <Input
                                    value={data.num_piece}
                                    onChange={(e) => setData('num_piece', e.target.value.toUpperCase())}
                                    placeholder="CNI, Passeport..."
                                />
                            </Field>

                            <Field label="Date d'expiration" required error={fieldErrors.date_expiration_piece}>
                                <Input
                                    type="date"
                                    value={data.date_expiration_piece}
                                    onChange={(e) => setData('date_expiration_piece', e.target.value)}
                                />
                            </Field>

                            <Field label="Photo du locataire" error={fieldErrors.photo}>
                                <Input type="file" accept="image/*" onChange={(e) => setData('photo', e.target.files?.[0] ?? null)} />
                            </Field>

                            <Field label="Photo de la pièce" error={fieldErrors.image_pice}>
                                <Input
                                    type="file"
                                    accept="image/*"
                                    onChange={(e) => setData('image_pice', e.target.files?.[0] ?? null)}
                                />
                            </Field>

                           
                        </div>
                    </SectionCard>
                ) : null}

                {current === 2 ? (
                    !isEdit ? (
                        <SectionCard
                            icon={Home}
                            title="Contrat de location"
                            description="Sélectionnez le propriétaire, puis le lot, la propriété, le bâtiment et la porte."
                        >
                            <div className="mt-4 space-y-6">
                                <div className="rounded-2xl border border-[#e2e8f0] bg-white p-4">
                                <div className="mb-5 flex items-center gap-2 text-sm font-semibold text-[#0f172a]">
                                    <Home className="h-4 w-4 text-[#00559b]" />
                                    Informations de la propriété
                                </div>

                                <div className="space-y-6">
                                    {/* Sélection de la propriété */}
                                    <div className="space-y-4">
                                        <Field
                                            label="Propriétaire"
                                            required
                                            error={fieldErrors['contrat.proprietaire_id']}
                                        >
                                            <Select
                                                value={toId(data.contrat.proprietaire_id)}
                                                onValueChange={handleOwnerChange}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Sélectionner un propriétaire" />
                                                </SelectTrigger>

                                                <SelectContent>
                                                    {ownerOptions.map((owner) => (
                                                        <SelectItem
                                                            key={toId(owner.proprietaire_id)}
                                                            value={toId(owner.proprietaire_id)}
                                                        >
                                                            {owner.name}
                                                            {owner.tel1 ? ` - ${owner.tel1}` : ''}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </Field>

                                        <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                            <Field
                                                label="Lot"
                                                required
                                                error={fieldErrors['contrat.lot_id']}
                                            >
                                                <Select
                                                    value={toId(data.contrat.lot_id)}
                                                    onValueChange={handleLotChange}
                                                    disabled={!selectedOwner}
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue placeholder="Sélectionner un lot" />
                                                    </SelectTrigger>

                                                    <SelectContent>
                                                        {lotOptions.map((lot) => (
                                                            <SelectItem
                                                                key={toId(
                                                                    lot.propreietaire_lot_id ?? lot.id
                                                                )}
                                                                value={toId(
                                                                    lot.propreietaire_lot_id ?? lot.id
                                                                )}
                                                            >
                                                                {lot.name}
                                                                {lot.adresse ? ` — ${lot.adresse}` : ''}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            </Field>

                                            <Field
                                                label="Propriété"
                                                required
                                                error={fieldErrors['contrat.propriete_id']}
                                            >
                                                <Select
                                                    value={toId(data.contrat.propriete_id)}
                                                    onValueChange={handlePropertyChange}
                                                    disabled={!selectedLot}
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue placeholder="Sélectionner une propriété" />
                                                    </SelectTrigger>

                                                    <SelectContent>
                                                        {propertyOptions.map((property) => (
                                                            <SelectItem
                                                                key={toId(property.propriete_id)}
                                                                value={toId(property.propriete_id)}
                                                            >
                                                                {property.reference}
                                                                {property.adresse_complete
                                                                    ? ` — ${property.adresse_complete}`
                                                                    : ''}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            </Field>

                                            <Field
                                                label="Bâtiment"
                                                required
                                                error={fieldErrors['contrat.batiment_id']}
                                            >
                                                <Select
                                                    value={toId(data.contrat.batiment_id)}
                                                    onValueChange={handleBuildingChange}
                                                    disabled={!selectedProperty}
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue placeholder="Sélectionner un bâtiment" />
                                                    </SelectTrigger>

                                                    <SelectContent>
                                                        {buildingOptions.map((building) => (
                                                            <SelectItem
                                                                key={toId(building.batiment_id)}
                                                                value={toId(building.batiment_id)}
                                                            >
                                                                {building.name}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            </Field>

                                            <Field
                                                label="Porte"
                                                required
                                                error={fieldErrors['contrat.porte_id']}
                                            >
                                                <Select
                                                    value={toId(data.contrat.porte_id)}
                                                    onValueChange={handleDoorChange}
                                                    disabled={!selectedBuilding}
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue placeholder="Sélectionner une porte" />
                                                    </SelectTrigger>

                                                    <SelectContent>
                                                        {doorOptions.map((door) => (
                                                            <SelectItem
                                                                key={toId(door.porte_id)}
                                                                value={toId(door.porte_id)}
                                                            >
                                                                {formatContractDoor(door)}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            </Field>
                                        </div>
                                    </div>

                                    {/* Résumé de la porte */}
                                    <div className="rounded-xl border border-[#dbe7f0] bg-[#f8fafc] px-4 py-3">
                                        {selectedDoor ? (
                                            <p className="text-sm text-[#475569]">
                                                <span className="font-semibold text-[#0f172a]">
                                                    {formatContractDoor(selectedDoor)}
                                                </span>

                                                <span className="mx-2 text-[#cbd5e1]">•</span>

                                                Loyer mensuel de{' '}
                                                <span className="font-semibold text-[#00559b]">
                                                    {formatMoney(
                                                        selectedDoor.mt_loyer ??
                                                            selectedDoor.tarifActif?.mt_loyer ??
                                                            0
                                                    )}{' '}
                                                    FCFA
                                                </span>
                                            </p>
                                        ) : (
                                            <p className="text-sm text-[#64748b]">
                                                Choisissez une porte pour pré-remplir les montants du contrat.
                                            </p>
                                        )}
                                    </div>

                                    {canShowContractAmounts ? (
                                        <>
                                            <Separator />

                                            {/* Montants du contrat */}
                                            <div>
                                        <div className="mb-4">
                                            <h3 className="text-sm font-semibold text-[#0f172a]">
                                                Montants du contrat
                                            </h3>

                                        
                                        </div>

                                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                                            <Field
                                                label="Garantie globale (FCFA)"
                                                required
                                                error={fieldErrors['contrat.montant_global_garantie']}
                                            >
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={data.contrat.montant_global_garantie}
                                                    onChange={(e) =>
                                                        updateContrat({
                                                            montant_global_garantie: e.target.value,
                                                        })
                                                    }
                                                    placeholder="0"
                                                    disabled
                                                />
                                            </Field>

                                            <Field
                                                label="Loyer net (FCFA)"
                                                required
                                                error={fieldErrors['contrat.loyer_net']}
                                            >
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={data.contrat.loyer_net}
                                                    onChange={(e) =>
                                                        updateContrat({
                                                            loyer_net: e.target.value,
                                                        })
                                                    }
                                                    placeholder="0"
                                                    disabled
                                                />
                                            </Field>

                                            <Field
                                                label="Caution (mois)"
                                                error={fieldErrors['contrat.caution']}
                                            >
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={data.contrat.caution}
                                                    onChange={(e) =>
                                                        updateContrat({
                                                            caution: e.target.value,
                                                        })
                                                    }
                                                    disabled
                                                />
                                            </Field>

                                            <Field
                                                label="Avance (mois)"
                                                error={fieldErrors['contrat.avance']}
                                            >
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={data.contrat.avance}
                                                    onChange={(e) =>
                                                        updateContrat({
                                                            avance: e.target.value,
                                                        })
                                                    }
                                                    disabled
                                                />
                                            </Field>

                                            <Field
                                                label="Agence (mois)"
                                                error={fieldErrors['contrat.agence']}
                                            >
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={data.contrat.agence}
                                                    onChange={(e) =>
                                                        updateContrat({
                                                            agence: e.target.value,
                                                        })
                                                    }
                                                    disabled
                                                />
                                            </Field>

                                            <Field
                                                label="Caution CIE"
                                                error={fieldErrors['contrat.caution_cie']}
                                            >
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={data.contrat.caution_cie}
                                                    onChange={(e) =>
                                                        updateContrat({
                                                            caution_cie: e.target.value,
                                                        })
                                                    }
                                                    disabled
                                                />
                                            </Field>

                                            <Field
                                                label="Caution SODECI"
                                                error={fieldErrors['contrat.caution_sodeci']}
                                            >
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={data.contrat.caution_sodeci}
                                                    onChange={(e) =>
                                                        updateContrat({
                                                            caution_sodeci: e.target.value,
                                                        })
                                                    }
                                                    disabled
                                                />
                                            </Field>

                                            <Field
                                                label="Frais de dossier"
                                                error={fieldErrors['contrat.frais_de_dossier']}
                                            >
                                                <Input
                                                    type="number"
                                                    min="0"
                                                    value={data.contrat.frais_de_dossier}
                                                    onChange={(e) =>
                                                        updateContrat({
                                                            frais_de_dossier: e.target.value,
                                                        })
                                                    }
                                                    disabled
                                                />
                                            </Field>
                                                </div>
                                            </div>
                                        </>
                                    ) : null}
                            </div>

                            
   

        

                               
                            </div>
                            </div>

{!isEdit ? (
                                <div className="mt-4 mb-4">
                                    <BooleanRadioField
                                        label="Type de locataire"
                                        name="recap_is_new_contract"
                                        value={Boolean(data.is_new)}
                                        onChange={handleLocataireTypeChange}
                                        trueLabel="Nouveau locataire"
                                        falseLabel="Ancien locataire"
                                    />
                                </div>
                            ) : null}

                            {showDepotGarantie ? (
                                <div className="mt-4 mb-4 rounded-2xl border border-[#e2e8f0] bg-white p-4">
                                        <div className="mb-4 flex items-center justify-between gap-3">
                                            <div className="flex items-center gap-2 text-sm font-semibold text-[#0f172a]">
                                                <Wallet className="h-4 w-4 text-[#00559b]" />
                                                Versements du dépôt de garantie
                                            </div>
                                        </div>

                                        <div className="space-y-4">
                                            {data.contrat.versements_depot_garantie.map((versement, index) => (
                                                <div key={`versement-${index}`} className="rounded-2xl border border-[#e2e8f0] bg-white p-4">
                                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-[1fr_1fr_1fr_1.2fr]">
                                                        <Field
                                                            label="Garantie globale (FCFA)"
                                                            className="md:col-span-2 xl:col-span-1"
                                                        >
                                                            <Input
                                                                type="number"
                                                                value={data.contrat.montant_global_garantie}
                                                                readOnly
                                                                disabled
                                                            />
                                                        </Field>

                                                        <Field
                                                            label="Montant versé (FCFA)"
                                                            required
                                                            className="md:col-span-1"
                                                            error={fieldErrors[`contrat.versements_depot_garantie.${index}.montant`]}
                                                        >
                                                            <Input
                                                                type="number"
                                                                min="0"
                                                                value={versement.montant}
                                                                onChange={(e) => updateDepotVersement(index, { montant: e.target.value })}
                                                                onBlur={() => handleDepotVersementMontantBlur(index)}
                                                                placeholder="Entrer le montant"
                                                            />
                                                        </Field>

                                                        <Field
                                                            label="Date de versement"
                                                            required
                                                            error={fieldErrors[`contrat.versements_depot_garantie.${index}.date_versement`]}
                                                        >
                                                            <Input
                                                                type="date"
                                                                value={versement.date_versement}
                                                                onChange={(e) =>
                                                                    updateDepotVersement(index, { date_versement: e.target.value })
                                                                }
                                                            />
                                                        </Field>

                                                        <Field
                                                            label="Mode de paiement"
                                                            required
                                                            className="md:col-span-1"
                                                            error={fieldErrors[`contrat.versements_depot_garantie.${index}.mode_paiement_id`]}
                                                        >
                                                            <Select
                                                                value={toId(versement.mode_paiement_id)}
                                                                onValueChange={(value) =>
                                                                    updateDepotVersement(index, { mode_paiement_id: value })
                                                                }
                                                            >
                                                                <SelectTrigger>
                                                                    <SelectValue placeholder="Sélectionner" />
                                                                </SelectTrigger>
                                                                <SelectContent>
                                                                    {modePaiementOptions.map((option) => (
                                                                        <SelectItem key={`versement-mode-${option.value}`} value={option.value}>
                                                                            {option.label}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectContent>
                                                            </Select>
                                                        </Field>

                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                </div>
                            ) : (
                                <div className="mt-4 mb-4 rounded-2xl border border-dashed border-[#dbe7f0] bg-[#f8fafc] p-4 text-sm text-[#5f7182]">
                                    Le dépôt de garantie s'affiche uniquement pour un nouveau locataire.
                                </div>
                            )}

                            

                                    <div className="rounded-2xl mt-4 border border-[#e2e8f0] bg-white p-4">
                                      <div className="mb-4 flex items-center gap-2 text-sm font-semibold text-[#0f172a]">
                                          <Wallet className="h-4 w-4 text-[#00559b]" />
                                          Détails du bail
                                      </div>

                                      <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                                        <label className="flex items-center gap-2 rounded-xl border border-[#e2e8f0] bg-[#f8fafc] px-3 py-2 text-sm text-[#0f172a] md:col-span-2 xl:col-span-3">
                                            <Checkbox
                                                checked={Boolean(data.contrat.has_representant)}
                                                onChange={(e) => updateRepresentantVisibility(e.target.checked)}
                                            />
                                            Le locataire a un représentant ?
                                        </label>

                                        <Field label="Périodicité de paiement" required error={fieldErrors['contrat.periodicite_paiement_id']}>
                                            <Select
                                                value={toId(data.contrat.periodicite_paiement_id)}
                                                onValueChange={(value) => updateContrat({ periodicite_paiement_id: value })}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Sélectionner" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {periodicitePaiementOptions.map((option) => (
                                                        <SelectItem key={option.value} value={option.value}>
                                                            {option.label}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </Field>

                                        
                                        <Field label="Date de signature du bail" error={fieldErrors['contrat.date_signature_bail']}>
                                            <Input
                                                type="date"
                                                value={data.contrat.date_signature_bail}
                                                onChange={(e) => updateContrat({ date_signature_bail: e.target.value })}
                                            />
                                        </Field>

                                          <Field label="Date de début bail" error={fieldErrors['contrat.date_debut_bail']}>
                                            <Input
                                                type="date"
                                                value={data.contrat.date_debut_bail}
                                                onChange={(e) => updateContrat({ date_debut_bail: e.target.value })}
                                            />
                                        </Field>

                                        <Field label="Date d'entrée" required error={fieldErrors['contrat.date_entree']}>
                                            <Input
                                                type="date"
                                                value={data.contrat.date_entree}
                                                onChange={(e) => updateContrat({ date_entree: e.target.value })}
                                            />
                                        </Field>
                                   

                                        <Field label="Nombre de personnes (adultes + enfants)" error={fieldErrors['contrat.nbre_personne']}>
                                            <Input
                                                type="text"
                                                inputMode="numeric"
                                                pattern="[0-9]*"
                                                value={data.contrat.nbre_personne}
                                                onChange={(e) => updateContrat({ nbre_personne: normalizeWholeNumberInput(e.target.value) })}
                                                onBlur={() => updateContrat(normalizePeopleFields({ nbre_personne: data.contrat.nbre_personne }))}
                                            />
                                        </Field>

                                        <Field label="Nombre d'enfants" error={fieldErrors['contrat.nbre_enfant']}>
                                            <Input
                                                type="text"
                                                inputMode="numeric"
                                                pattern="[0-9]*"
                                                value={data.contrat.nbre_enfant}
                                                onChange={(e) => updateContrat({ nbre_enfant: normalizeWholeNumberInput(e.target.value) })}
                                                onBlur={() => updateContrat(normalizePeopleFields({ nbre_enfant: data.contrat.nbre_enfant }))}
                                            />
                                        </Field>

                                      

                                        {data.contrat.has_representant ? (
                                            <>
                                        <Field label="Nom du représentant" error={fieldErrors['contrat.name_representant']}>
                                            <Input
                                                value={data.contrat.name_representant}
                                                onChange={(e) => updateContrat({ name_representant: toTitleCase(e.target.value) })}
                                                placeholder="Facultatif"
                                            />
                                        </Field>

                                        <PhoneInput
                                            label="Téléphone du représentant"
                                            error={fieldErrors['contrat.contant_representant']}
                                            value={data.contrat.contant_representant}
                                            onChange={(e) => updateContrat({ contant_representant: e.target.value })}
                                            placeholder="Ex: +225 07 00 00 11 11"
                                        />

                                        <Field
                                            label="Adresse du représentant"
                                            error={fieldErrors['contrat.adresse_representant']}
                                        >
                                            <Input
                                                value={data.contrat.adresse_representant}
                                                onChange={(e) => updateContrat({ adresse_representant: e.target.value })}
                                                placeholder="Facultatif"
                                            />
                                        </Field>
                                            </>
                                        ) : null}
                                     </div>
                                 </div>

                                
                        </SectionCard>
                    ) : (
                        <SectionCard
                            icon={Home}
                            title="Contrat actuel"
                            description="Les montants sont verrouillés. Seuls les champs administratifs ci-dessous restent modifiables."
                        >
                            {currentContract ? (
                                <div className="space-y-6">
                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-2">
                                        <Card className="mt-4 rounded-2xl border-[#c8d4de] bg-[#f8fafc] shadow-none">
                                            <CardContent className="mt-4 space-y-2 p-4">
                                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Propriétaire</p>
                                                <p className="text-sm font-semibold text-[#0f172a]">
                                                    {currentContract.proprietaire?.name ?? 'Non renseigné'}
                                                </p>
                                                <div>
                                                    <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Date de signature du bail</p>
                                                    <p className="text-sm font-semibold text-[#0f172a]">
                                                        {toDateInput(currentContract.date_signature_bail) || 'Non renseignée'}
                                                    </p>
                                                </div>
                                            </CardContent>
                                        </Card>

                                        <Card className="mt-4 rounded-2xl border-[#c8d4de] bg-[#f8fafc] shadow-none">
                                            <CardContent className="mt-4 space-y-2 p-4">
                                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Lot</p>
                                                <p className="text-sm font-semibold text-[#0f172a]">
                                                    {currentContract.lot?.name ?? 'Non renseigné'}
                                                </p>
                                            </CardContent>
                                        </Card>

                                        <Card className="rounded-2xl border-[#c8d4de] bg-[#f8fafc] shadow-none">
                                            <CardContent className="mt-4 space-y-2 p-4">
                                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Propriété</p>
                                                <p className="text-sm font-semibold text-[#0f172a]">
                                                    {currentContract.propriete?.reference ?? 'Non renseignée'}
                                                </p>
                                            </CardContent>
                                        </Card>

                                        <Card className="rounded-2xl border-[#c8d4de] bg-[#f8fafc] shadow-none">
                                            <CardContent className="mt-4 space-y-2 p-4">
                                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Porte</p>
                                                <p className="text-sm font-semibold text-[#0f172a]">
                                                    {currentContract.porte?.numero_porte ?? 'Non renseignée'}
                                                </p>
                                            </CardContent>
                                        </Card>

                                        <Card className="rounded-2xl border-[#c8d4de] bg-[#f8fafc] shadow-none md:col-span-2 lg:col-span-2">
                                            <CardContent className="mt-4 grid grid-cols-1 gap-4 p-4 md:grid-cols-2">
                                                <div>
                                            <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Montant global de garantie</p>
                                            <p className="text-sm font-semibold text-[#0f172a]">
                                                {formatMoney(
                                                    calculateMontantGlobalGarantie({
                                                        loyerNet: currentContract.porte?.mt_loyer ?? 0,
                                                        caution: currentContract.caution ?? 0,
                                                        avance: currentContract.avance ?? 0,
                                                        agence: currentContract.agence ?? 0,
                                                        cautionCie: currentContract.caution_cie ?? 0,
                                                        cautionSodeci: currentContract.caution_sodeci ?? 0,
                                                        fraisDeDossier: currentContract.frais_de_dossier ?? 0,
                                                    })
                                                )}{' '}
                                                FCFA
                                            </p>
                                        </div>
                                                <div>
                                                    <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Loyer net</p>
                                                    <p className="text-sm font-semibold text-[#0f172a]">
                                                        {formatMoney(currentContract.porte?.mt_loyer ?? 0)} FCFA
                                                    </p>
                                                </div>
                                                <div>
                                                    <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Caution (mois)</p>
                                                    <p className="text-sm font-semibold text-[#0f172a]">
                                                        {currentContract.caution ?? 0}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Avance (mois)</p>
                                                    <p className="text-sm font-semibold text-[#0f172a]">
                                                        {currentContract.avance ?? 0}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Agence (mois)</p>
                                                    <p className="text-sm font-semibold text-[#0f172a]">
                                                        {currentContract.agence ?? 0}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Caution CIE</p>
                                                    <p className="text-sm font-semibold text-[#0f172a]">
                                                        {formatMoney(currentContract.caution_cie ?? 0)} FCFA
                                                    </p>
                                                </div>
                                                <div>
                                                    <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Caution SODECI</p>
                                                    <p className="text-sm font-semibold text-[#0f172a]">
                                                        {formatMoney(currentContract.caution_sodeci ?? 0)} FCFA
                                                    </p>
                                                </div>
                                                <div>
                                                    <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Frais de dossier</p>
                                                    <p className="text-sm font-semibold text-[#0f172a]">
                                                        {formatMoney(currentContract.frais_de_dossier ?? 0)} FCFA
                                                    </p>
                                                </div>
                                                <div>
                                                    <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Périodicité de paiement</p>
                                                    <p className="text-sm font-semibold text-[#0f172a]">
                                                        {periodicitePaiementOptions.find(
                                                            (option) =>
                                                                option.value ===
                                                                toId(
                                                                    currentContract.periodicite_paiement_id ??
                                                                        currentContract.mode_paiement_id
                                                                )
                                                        )?.label ?? '—'}
                                                    </p>
                                                </div>
                                            </CardContent>
                                        </Card>
                                    </div>

                                    <Separator />

                                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <InfoCard label="Nombre de personnes" value={currentContract.nbre_personne} />
                                        <InfoCard label="Nombre d'enfants" value={currentContract.nbre_enfant} />
                                        <InfoCard label="Date de début bail" value={toDateInput(currentContract.date_debut_bail)} />
                                        <InfoCard label="Date d'entrée" value={toDateInput(currentContract.date_entree)} />
                                        {Boolean(
                                            currentContract.has_representant ||
                                                currentContract.name_representant ||
                                                currentContract.adresse_representant ||
                                                currentContract.contant_representant
                                        ) ? (
                                            <>
                                                <InfoCard label="Nom du représentant" value={currentContract.name_representant} />
                                                <InfoCard
                                                    label="Téléphone du représentant"
                                                    value={currentContract.contant_representant}
                                                />
                                                <InfoCard
                                                    label="Adresse du représentant"
                                                    value={currentContract.adresse_representant}
                                                />
                                            </>
                                        ) : null}
                                    </div>
                                    {currentContract.is_new && asArray(currentContract.versements_depot_garantie).length ? (
                                        <>
                                            <Separator />
                                            <div>
                                                <p className="mb-3 text-sm font-semibold text-[#0f172a]">
                                                    Versements du dépôt de garantie
                                                </p>
                                                <div className="space-y-2">
                                                    {asArray(currentContract.versements_depot_garantie).map((versement, index) => (
                                                        <div
                                                            key={`current-versement-${index}`}
                                                            className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] px-3 py-2 text-sm text-[#0f172a]"
                                                        >
                                                            {formatMoney(versement.montant ?? 0)} FCFA · {toDateInput(versement.date_versement) || '—'} ·{' '}
                                                            {modePaiementOptions.find((option) => option.value === toId(versement.mode_paiement_id))?.label ?? '—'}
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        </>
                                    ) : currentContract.is_new ? null : (
                                        <>
                                            <Separator />
                                            <p className="text-sm text-[#5f7182]">Aucun dépôt de garantie à afficher pour ce locataire.</p>
                                        </>
                                    )}
                                </div>
                            ) : (
                                <p className="text-sm text-[#5f7182]">Aucun contrat actif trouvé.</p>
                            )}
                        </SectionCard>
                    )
                ) : null}

                {current === 3 ? (
                    !isEdit ? (
                        <SectionCard
                            icon={CalendarDays}
                            title="Arriérés"
                            description="Indiquez si le locataire est nouveau ou ancien, puis saisissez les arriérés si nécessaire."
                        >
                            {!Boolean(data.is_new) ? (
                                <div className="mt-4 mb-4">
                                    <BooleanRadioField
                                        label="Ce locataire a des arriérés"
                                        name="recap_a_des_arrieres"
                                        value={showArrieres}
                                        onChange={handleArriereVisibilityChange}
                                        trueLabel="Oui"
                                        falseLabel="Non"
                                    />
                                </div>
                            ) : null}

                            {showArrieres ? (
                                <>
                                    <div className="mb-4 flex justify-end">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            className={agenceButtonStyles.actionBlue}
                                            onClick={addArriere}
                                        >
                                            <Plus className="h-4 w-4" />
                                            Ajouter un mois
                                        </Button>
                                    </div>
                                    <div className="space-y-4">
                                        {data.arrieres.map((arriere, index) => (
                                            <div key={`arriere-${index}`} className="rounded-2xl border border-[#c8d4de] bg-[#f8fafc] p-4">
                                                <div className="grid grid-cols-1 gap-4 md:grid-cols-[1fr_1fr_auto]">
                                                    <Field label="Mois" required error={fieldErrors[`arrieres.${index}.mois`]}>
                                                        <Select
                                                            value={toMonthInput(arriere.mois)}
                                                            onValueChange={(value) => updateArriere(index, { mois: value })}
                                                        >
                                                            <SelectTrigger>
                                                                <SelectValue placeholder="Sélectionner un mois" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                {arriereMonthOptions.map((option) => (
                                                                    <SelectItem key={option.value} value={option.value}>
                                                                        {option.label}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectContent>
                                                        </Select>
                                                    </Field>

                                                    <Field label="Montant arriéré (FCFA)" required error={fieldErrors[`arrieres.${index}.montant`]}>
                                                        <Input
                                                            type="number"
                                                            min="0"
                                                            value={arriere.montant}
                                                            onChange={(e) => updateArriere(index, { montant: e.target.value })}
                                                            placeholder="Ex: 50000"
                                                        />
                                                    </Field>

                                                <div className="flex items-end">
                                                        <Button
                                                            type="button"
                                                            variant="outline"
                                                            size="icon"
                                                            className={agenceButtonStyles.actionRedIcon}
                                                            onClick={() => removeArriere(index)}
                                                            disabled={data.arrieres.length === 1}
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                    <div className="mt-6 rounded-2xl border border-[#dbe7f0] bg-[#f8fafc] px-4 py-3">
                                        <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Montant global des arriérés</p>
                                        <p className="mt-1 text-lg font-semibold text-[#0f172a]">{formatMoney(totalArrieres)} FCFA</p>
                                    </div>
                                </>
                            ) : Boolean(data.is_new) ? (
                                <div className="mt-6 rounded-2xl border border-dashed border-[#dbe7f0] bg-[#f8fafc] p-4 text-sm text-[#5f7182]">
                                    Ce locataire est marqué comme nouveau. Il n&apos;y a pas d&apos;arriéré à saisir ici, vous pouvez passer à l&apos;étape suivante.
                                </div>
                            ) : null}
                        </SectionCard>
                    ) : (
                        <SectionCard
                            icon={CalendarDays}
                            title="Arriérés"
                            description="Les arriérés ne sont pas modifiables depuis l’édition."
                        >
                            <p className="mt-4 text-sm text-[#5f7182]">
                                Ce formulaire conserve les arriérés en lecture seule.
                            </p>
                        </SectionCard>
                    )
                ) : null}

                {current === 4 ? (
                    <div className="flex flex-col gap-6">
                        <SectionCard icon={UserRound} title="Identité" description="Récapitulatif du locataire">
                            <div className="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                <InfoCard label="Nom complet" value={data.name} />
                                <InfoCard label="Téléphone 1" value={data.tel1} />
                                <InfoCard label="Téléphone 2" value={data.tel2} />
                                <InfoCard label="Région" value={regionOptions.find((option) => option.value === toId(data.region_id))?.label} />
                                <InfoCard label="Ville" value={villeOptions.find((option) => option.value === toId(data.ville_id))?.label} />
                                <InfoCard label="Adresse" value={data.adresse} />
                            </div>
                        </SectionCard>

                        <SectionCard icon={Home} title="Contrat" description="Lien propriétaire et logement">
                            <div className="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                <InfoCard label="Propriétaire" value={selectedOwner?.name} />
                                <InfoCard label="Lot" value={selectedLot?.name} />
                                <InfoCard label="Propriété" value={selectedProperty?.reference} />
                                <InfoCard label="Bâtiment" value={selectedBuilding?.name} />
                                <InfoCard label="Porte" value={selectedDoor ? formatContractDoor(selectedDoor) : ''} />
                                <InfoCard
                                    label="Montant global de garantie"
                                    value={
                                        data.contrat.montant_global_garantie
                                            ? `${formatMoney(data.contrat.montant_global_garantie)} FCFA`
                                            : ''
                                    }
                                />
                                <InfoCard label="Loyer net" value={data.contrat.loyer_net ? `${formatMoney(data.contrat.loyer_net)} FCFA` : ''} />
                                <InfoCard
                                    label="Date de signature du bail"
                                    value={data.contrat.date_signature_bail ? toDateInput(data.contrat.date_signature_bail) : ''}
                                />
                            </div>
                        </SectionCard>

                        {data.contrat.versements_depot_garantie.length ? (
                            <SectionCard icon={Wallet} title="Dépôt de garantie" description="Versements enregistrés">
                                <div className="mt-6 space-y-2">
                                    {data.contrat.versements_depot_garantie.map((versement, index) => (
                                        <div
                                            key={`recap-depot-${index}`}
                                            className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] px-3 py-2 text-sm text-[#0f172a]"
                                        >
                                            {formatMoney(versement.montant ?? 0)} FCFA · {toDateInput(versement.date_versement) || 'Non renseigné'} ·{' '}
                                            {modePaiementOptions.find((option) => option.value === toId(versement.mode_paiement_id))?.label ?? 'Non renseigné'}
                                        </div>
                                    ))}
                                </div>
                            </SectionCard>
                        ) : null}

                        {!isEdit ? (
                            <SectionCard icon={CalendarDays} title="Arriérés" description="Mois déclarés">
                                {showArrieres && data.arrieres.length ? (
                                    <div className="mt-4 space-y-2">
                                        {data.arrieres.map((arriere, index) => (
                                            <div key={`recap-arriere-${index}`} className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] px-3 py-2 text-sm text-[#0f172a]">
                                                {toMonthInput(arriere.mois) || 'Non renseigné'} · {arriere.montant ? `${formatMoney(arriere.montant)} FCFA` : 'Non renseigné'}
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="mt-4 text-sm text-[#5f7182]">Aucun arriéré déclaré.</p>
                                )}
                            </SectionCard>
                        ) : null}
                    </div>
                ) : null}

                <div className="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="w-full sm:w-auto">
                        {current > 0 ? (
                            <Button
                                type="button"
                                variant="outline"
                                className={cn(agenceButtonStyles.outline, 'w-full sm:w-auto')}
                                onClick={handlePrev}
                            >
                                Précédent
                            </Button>
                        ) : (
                            <Button asChild type="button" variant="outline" className={cn(agenceButtonStyles.outline, 'w-full sm:w-auto')}>
                                <Link href="/agence/locataires">Annuler</Link>
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
                                {processing ? 'Enregistrement...' : isEdit ? 'Mettre à jour' : 'Enregistrer'}
                            </Button>
                        ) : (
                            <Button type="button" className={cn(agenceButtonStyles.primary, 'w-full sm:w-auto')} onClick={handleNext}>
                                Suivant
                                <ChevronRight className="h-4 w-4" />
                            </Button>
                        )}
                    </div>
                </div>
            </form>
        </AgenceLayout>
    );
}
