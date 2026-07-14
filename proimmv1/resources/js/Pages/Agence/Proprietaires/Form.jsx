import { useEffect, useMemo, useRef, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Check, ChevronDown, ChevronRight, FileImage, IdCard, Home, Save, Search, ShieldCheck, UserRound } from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Checkbox } from '../../../components/ui/checkbox';
import { Input } from '../../../components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
import { agenceButtonStyles } from '../../../lib/buttonStyles';
import { cn } from '../../../lib/utils';
import flags from 'react-phone-number-input/flags';

import PhoneInputBase from 'react-phone-number-input';
import 'react-phone-number-input/style.css';

const toId = (value) => (value === null || value === undefined || value === '' ? '' : String(value));

const asArray = (value) => (Array.isArray(value) ? value : []);

const toDateInput = (value) => {
    if (!value) return '';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value).slice(0, 10);

    return date.toISOString().slice(0, 10);
};

const normalizePhoneValue = (value) => (value ? String(value) : '');

function findLiaison(proprietaire) {
    return asArray(proprietaire?.agences).find((item) => item?.agence_id) ?? asArray(proprietaire?.agences)[0] ?? null;
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
                <ChevronDown className="h-3.5 w-3.5" />
            </button>

            {open ? (
                <div className="absolute left-0 top-[calc(100%+4px)] z-50 w-64 overflow-hidden rounded-md border border-[#c8d4de] bg-white shadow-lg">
                    <div className="flex items-center gap-2 border-b border-[#e2e8f0] px-2.5 py-2">
                        <Search className="h-4 w-4 shrink-0 text-[#94a3b8]" />
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

const textareaClassName =
    'min-h-24 w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] shadow-sm outline-none transition-colors placeholder:text-[#94a3b8] focus:border-[#00559b] focus:ring-2 focus:ring-[#00559b]/20';

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
            <ol className="flex flex-col gap-3 lg:grid lg:grid-cols-4 lg:gap-2.5">
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

function buildInitialData(proprietaire) {
    const liaison = findLiaison(proprietaire);
    const hasRepresentant = Boolean(
        liaison?.name_representant ||
            liaison?.adresse_representant ||
            liaison?.tel1_representant ||
            liaison?.tel2_representant ||
            liaison?.email_representant ||
            liaison?.genre_representant_id ||
            liaison?.type_pieces_representant_id ||
            liaison?.numpiece_representant ||
            liaison?.photo_representant
    );

    return {
        name: proprietaire?.name ?? '',
        tel1: normalizePhoneValue(proprietaire?.tel1),
        tel2: normalizePhoneValue(proprietaire?.tel2),
        type_pieces_id: toId(proprietaire?.type_pieces_id ?? proprietaire?.type_piece_id),
        numpiece: proprietaire?.numpiece ?? '',
        email: proprietaire?.email ?? '',
        profession: proprietaire?.profession ?? '',
        nationalite: proprietaire?.nationalite ?? '',
        date_naiss: toDateInput(proprietaire?.date_naiss),
        lieu_naiss: proprietaire?.lieu_naiss ?? '',
        region_id: toId(proprietaire?.region_id),
        ville_id: toId(proprietaire?.ville_id),
        adresse: proprietaire?.adresse ?? '',
        photo: null,
        genre_id: toId(proprietaire?.genre_id),
        date_expiration_piece: toDateInput(proprietaire?.date_expiration_piece),
        has_representant: hasRepresentant,
        genre_representant_id: toId(liaison?.genre_representant_id),
        name_representant: liaison?.name_representant ?? '',
        adresse_representant: liaison?.adresse_representant ?? '',
        tel1_representant: normalizePhoneValue(liaison?.tel1_representant),
        tel2_representant: normalizePhoneValue(liaison?.tel2_representant),
        email_representant: liaison?.email_representant ?? '',
        type_pieces_representant_id: toId(liaison?.type_pieces_representant_id),
        numpiece_representant: liaison?.numpiece_representant ?? '',
        photo_representant: null,
    };
}

function summarizeValue(value) {
    return value ? value : '—';
}

function getTodayDateInput() {
    const date = new Date();
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    return date.toISOString().slice(0, 10);
}

function currentPhotoUrl(proprietaire) {
    if (!proprietaire?.photo) {
        return '';
    }

    if (String(proprietaire.photo).startsWith('http')) {
        return proprietaire.photo;
    }

    return `/storage/${proprietaire.photo}`;
}

export default function Form({ mode = 'create', proprietaire = null, genres = [], typePiece = [], regions = [], villes = [] }) {
    const isEdit = mode === 'edit';
    const liaison = useMemo(() => findLiaison(proprietaire), [proprietaire]);
    const { data, setData, post, transform, processing, errors } = useForm(buildInitialData(proprietaire));
    const [current, setCurrent] = useState(0);
    const [completed, setCompleted] = useState([]);
    const [stepErrors, setStepErrors] = useState({});
    const [showRepresentant, setShowRepresentant] = useState(Boolean(data.has_representant));
    const [newPhotoPreview, setNewPhotoPreview] = useState('');
    const [newRepresentantPhotoPreview, setNewRepresentantPhotoPreview] = useState('');
    const todayDate = getTodayDateInput();

    const steps = [
        { key: 'identity', title: 'Identité', subtitle: 'Coordonnées' },
        { key: 'piece', title: 'Pièce', subtitle: 'Document' },
        { key: 'representant', title: 'Représentant', subtitle: 'Liaison agence' },
        { key: 'resume', title: 'Résumé', subtitle: 'Validation' },
    ];

    const regionOptions = useMemo(() => asArray(regions).map((region) => ({ value: toId(region.id), label: region.name })), [regions]);
    const villeOptions = useMemo(
        () =>
            asArray(villes)
                .filter((ville) => !data.region_id || toId(ville.region_id) === toId(data.region_id))
                .map((ville) => ({ value: toId(ville.id), label: ville.name })),
        [data.region_id, villes]
    );
    const genreOptions = useMemo(() => asArray(genres).map((genre) => ({ value: toId(genre.id), label: genre.name })), [genres]);
    const typePieceOptions = useMemo(
        () => asArray(typePiece).map((piece) => ({ value: toId(piece.type_pieces_id ?? piece.id), label: piece.name })),
        [typePiece]
    );
    const selectedGenreLabel = useMemo(() => asArray(genreOptions).find((option) => option.value === toId(data.genre_id))?.label ?? '—', [genreOptions, data.genre_id]);
    const selectedRepresentativeGenreLabel = useMemo(
        () => asArray(genreOptions).find((option) => option.value === toId(data.genre_representant_id))?.label ?? '—',
        [genreOptions, data.genre_representant_id]
    );
    const selectedTypePieceLabel = useMemo(() => asArray(typePieceOptions).find((option) => option.value === toId(data.type_pieces_id))?.label ?? '—', [typePieceOptions, data.type_pieces_id]);
    const selectedRepresentativeTypePieceLabel = useMemo(
        () => asArray(typePieceOptions).find((option) => option.value === toId(data.type_pieces_representant_id))?.label ?? '—',
        [typePieceOptions, data.type_pieces_representant_id]
    );
    const selectedRegionLabel = useMemo(() => asArray(regionOptions).find((option) => option.value === toId(data.region_id))?.label ?? '—', [regionOptions, data.region_id]);
    const selectedVilleLabel = useMemo(() => asArray(villeOptions).find((option) => option.value === toId(data.ville_id))?.label ?? '—', [villeOptions, data.ville_id]);

    const fieldErrors = { ...errors, ...stepErrors };

    const goTo = (index) => {
        setStepErrors({});
        setCurrent(index);
        if (typeof window !== 'undefined') window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const updateRepresentantVisibility = (checked) => {
        setShowRepresentant(checked);
        setData('has_representant', checked);

        if (!checked) {
            setData('genre_representant_id', '');
            setData('name_representant', '');
            setData('adresse_representant', '');
            setData('tel1_representant', '');
            setData('tel2_representant', '');
            setData('email_representant', '');
            setData('type_pieces_representant_id', '');
            setData('numpiece_representant', '');
            setData('photo_representant', null);
            setNewRepresentantPhotoPreview('');
        }
    };

    useEffect(() => {
        if (data.region_id && data.ville_id && !villeOptions.some((option) => option.value === toId(data.ville_id))) {
            setData('ville_id', '');
        }
    }, [data.region_id, data.ville_id, setData, villeOptions]);

    useEffect(() => {
        if (!data.photo) {
            setNewPhotoPreview('');
            return;
        }

        const objectUrl = URL.createObjectURL(data.photo);
        setNewPhotoPreview(objectUrl);

        return () => URL.revokeObjectURL(objectUrl);
    }, [data.photo]);

    useEffect(() => {
        if (!data.photo_representant) {
            setNewRepresentantPhotoPreview('');
            return;
        }

        const objectUrl = URL.createObjectURL(data.photo_representant);
        setNewRepresentantPhotoPreview(objectUrl);

        return () => URL.revokeObjectURL(objectUrl);
    }, [data.photo_representant]);

    const validateStep = (index) => {
        const localErrors = {};

        if (index === 0) {
            if (!data.name.trim()) localErrors.name = 'Le nom du propriétaire est obligatoire.';
            if (!data.tel1.trim()) localErrors.tel1 = 'Le téléphone principal est obligatoire.';
        }

        if (index === 1) {
            if (!toId(data.type_pieces_id)) localErrors.type_pieces_id = 'Le type de pièce est obligatoire.';
            if (!data.numpiece.trim()) localErrors.numpiece = 'Le numéro de pièce est obligatoire.';
            if (!data.date_expiration_piece) {
                localErrors.date_expiration_piece = 'La date d\'expiration est obligatoire.';
            } else if (data.date_expiration_piece <= todayDate) {
                localErrors.date_expiration_piece = 'La date d\'expiration doit Ãªtre supérieure Ã  aujourd\'hui.';
            }
        }

        return localErrors;
    };

    const validateRepresentantStep = () => {
        const localErrors = {};

        if (!showRepresentant) {
            return localErrors;
        }

        if (!data.name_representant.trim()) localErrors.name_representant = 'Le nom du représentant est obligatoire.';
        if (!data.tel1_representant.trim()) localErrors.tel1_representant = 'Le premier téléphone du représentant est obligatoire.';
        if (!toId(data.type_pieces_representant_id)) localErrors.type_pieces_representant_id = 'Le type de pièce du représentant est obligatoire.';
        if (!data.numpiece_representant.trim()) localErrors.numpiece_representant = 'Le numéro de pièce du représentant est obligatoire.';

        return localErrors;
    };

    const handleNext = () => {
        const localErrors = current === 2 ? validateRepresentantStep() : validateStep(current);
        if (Object.keys(localErrors).length) {
            setStepErrors(localErrors);
            return;
        }

        setCompleted((previous) => (previous.includes(current) ? previous : [...previous, current]));
        goTo(Math.min(current + 1, steps.length - 1));
    };

    const validateAllSteps = () => ({
        ...validateStep(0),
        ...validateStep(1),
        ...validateRepresentantStep(),
    });

    const handleSubmit = (e) => {
        e.preventDefault();

        const localErrors = validateAllSteps();
        if (Object.keys(localErrors).length) {
            setStepErrors(localErrors);
            if (localErrors.name || localErrors.tel1) {
                setCurrent(0);
            } else if (
                localErrors.name_representant ||
                localErrors.tel1_representant ||
                localErrors.type_pieces_representant_id ||
                localErrors.numpiece_representant
            ) {
                setCurrent(2);
            } else {
                setCurrent(1);
            }
            if (typeof window !== 'undefined') window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        const submitOptions = {
            preserveScroll: true,
            forceFormData: true,
        };

        if (isEdit) {
            transform((payload) => ({
                ...payload,
                _method: 'put',
            }));
            post(`/agence/proprietaire/${proprietaire.proprietaire_id}`, submitOptions);
            return;
        }

        post('/agence/proprietaire', submitOptions);
    };

    const isLastStep = current === steps.length - 1;

    return (
        <AgenceLayout title={isEdit ? 'Modifier un propriétaire' : 'Nouveau propriétaire'}>
            <Head title={isEdit ? 'Modifier un propriétaire' : 'Nouveau propriétaire'} />

            <form
                onSubmit={(e) => {
                    e.preventDefault();

                    const submitter = e.nativeEvent?.submitter;
                    const submitterType = submitter?.getAttribute?.('type');

                    if (submitterType !== 'submit') {
                        return;
                    }

                    handleSubmit(e);
                }}
                className="mx-auto flex w-full max-w-6xl flex-col gap-6 pb-10"
            >
                <div className="flex items-center gap-3">
                    <Button asChild type="button" variant="outline" size="icon" className="rounded-xl border-[#c8d4de]">
                        <Link href="/agence/proprietaire">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>

                    <div>
                        <h1 className="text-xl font-semibold text-[#0f172a]">{isEdit ? 'Modifier un propriétaire' : 'Nouveau propriétaire'}</h1>
                     
                    </div>
                </div>

                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardContent className="mt-7 p-4">
                        <Stepper steps={steps} current={current} completed={completed} onStepClick={goTo} allowAllSteps={isEdit} />
                    </CardContent>
                </Card>

                {Object.keys(fieldErrors).length > 0 ? (
                    <Card className="rounded-2xl border-[#f5c2c7] bg-[#fff5f5] shadow-sm">
                        <CardContent className="p-4">
                            <p className="mt-4 mb-2 text-sm font-semibold text-[#b42318]">Veuillez corriger les erreurs ci-dessous.</p>
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
                    <SectionCard icon={UserRound} title="Informations personnelles" description="Identité et coordonnées du propriétaire.">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">

                            <Field label="Genre" className="mt-2" error={fieldErrors.genre_id}>
                                <Select value={toId(data.genre_id)} onValueChange={(value) => setData('genre_id', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Sélectionner" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Aucun</SelectItem>
                                        {genreOptions.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>

                            <Field label="Nom et prénom" required error={fieldErrors.name} className="mt-2">
                                <Input value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder="Kouassi Jean" />
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

                            <Field label="Email" error={fieldErrors.email}>
                                <Input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="email@exemple.com"
                                />
                            </Field>

                            <Field label="Profession" error={fieldErrors.profession}>
                                <Input value={data.profession} onChange={(e) => setData('profession', e.target.value)} placeholder="Commerçant" />
                            </Field>

                            <Field label="Nationalité" error={fieldErrors.nationalite}>
                                <Input value={data.nationalite} onChange={(e) => setData('nationalite', e.target.value)} placeholder="Ivoirienne" />
                            </Field>

                            <Field label="Date de naissance" error={fieldErrors.date_naiss}>
                                <Input type="date" value={data.date_naiss} onChange={(e) => setData('date_naiss', e.target.value)} />
                            </Field>

                          

                            <Field label="Région" error={fieldErrors.region_id}>
                                <Select
                                    value={toId(data.region_id)}
                                    onValueChange={(value) => setData((current) => ({ ...current, region_id: value, ville_id: '' }))}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Sélectionner" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Aucune</SelectItem>
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
                                    <SelectTrigger>
                                        <SelectValue placeholder={data.region_id ? 'Sélectionner' : "Sélectionnez d'abord une région"} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Aucune</SelectItem>
                                        {villeOptions.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>

                            <Field label="Lieu de naissance" error={fieldErrors.lieu_naiss}>
                                <Input value={data.lieu_naiss} onChange={(e) => setData('lieu_naiss', e.target.value)} placeholder="Abidjan" />
                            </Field>

                            <Field label="Adresse" error={fieldErrors.adresse} className="md:col-span-2">
                                <textarea
                                    className={textareaClassName}
                                    rows={3}
                                    value={data.adresse}
                                    onChange={(e) => setData('adresse', e.target.value)}
                                    placeholder="Cocody, Abidjan"
                                />
                            </Field>

                            
                        </div>
                    </SectionCard>
                ) : null}

                {current === 1 ? (
                    <SectionCard icon={IdCard} title="Pièce d'identité" description="Document, numéro et photo du propriétaire.">
                        <div className="mt-2 grid grid-cols-1 gap-4 md:grid-cols-2">
                            <Field label="Type de pièce" required error={fieldErrors.type_pieces_id}>
                                <Select value={toId(data.type_pieces_id)} onValueChange={(value) => setData('type_pieces_id', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Sélectionner" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Aucun</SelectItem>
                                        {typePieceOptions.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>

                            <Field label="Numéro de pièce" required error={fieldErrors.numpiece}>
                                <Input value={data.numpiece} onChange={(e) => setData('numpiece', e.target.value)} placeholder="CI000000000" />
                            </Field>

                            <Field label="Date d'expiration" error={fieldErrors.date_expiration_piece}>
                                <Input
                                    type="date"
                                    min={todayDate}
                                    value={data.date_expiration_piece}
                                    onChange={(e) => setData('date_expiration_piece', e.target.value)}
                                />
                            </Field>

                            {isEdit ? (
                                <div className="md:col-span-2 grid grid-cols-1 gap-4 lg:grid-cols-2">
                                    <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                        <p className="mb-3 text-sm font-medium text-[#0f172a]">Photo actuelle</p>
                                        {currentPhotoUrl(proprietaire) ? (
                                            <div className="overflow-hidden rounded-lg border border-[#e2e8f0] bg-white">
                                                <img
                                                    src={currentPhotoUrl(proprietaire)}
                                                    alt="Photo actuelle du propriétaire"
                                                    className="h-56 w-full object-cover"
                                                />
                                            </div>
                                        ) : (
                                            <div className="flex h-56 items-center justify-center rounded-lg border border-dashed border-[#c8d4de] bg-white text-sm text-[#5f7182]">
                                                Aucune photo enregistrée.
                                            </div>
                                        )}
                                    </div>

                                    <Field label="Nouvelle photo" error={fieldErrors.photo}>
                                        <div className="rounded-xl border border-dashed border-[#c8d4de] bg-[#f8fafc] p-4">
                                            <Input
                                                type="file"
                                                accept="image/*"
                                                onChange={(e) => {
                                                    const file = e.target.files?.[0] ?? null;
                                                    setData('photo', file);
                                                }}
                                            />

                                            <div className="mt-3 overflow-hidden rounded-lg border border-[#e2e8f0] bg-white">
                                                {newPhotoPreview ? (
                                                    <img
                                                        src={newPhotoPreview}
                                                        alt="Aperçu de la nouvelle photo"
                                                        className="h-56 w-full object-cover"
                                                    />
                                                ) : (
                                                    <div className="flex h-56 items-center justify-center px-4 text-center text-sm text-[#5f7182]">
                                                        <div className="space-y-2">
                                                            <FileImage className="mx-auto h-5 w-5 text-[#94a3b8]" />
                                                            <p>Sélectionnez une image pour remplacer la photo actuelle.</p>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </Field>
                                </div>
                            ) : (
                                <div className="md:col-span-2">
                                    <Field label="Photo du propriétaire" error={fieldErrors.photo}>
                                        <div className="rounded-xl border border-dashed border-[#c8d4de] bg-[#f8fafc] p-4">
                                            <Input
                                                type="file"
                                                accept="image/*"
                                                onChange={(e) => {
                                                    const file = e.target.files?.[0] ?? null;
                                                    setData('photo', file);
                                                }}
                                            />

                                            <div className="mt-3 overflow-hidden rounded-lg border border-[#e2e8f0] bg-white">
                                                {newPhotoPreview ? (
                                                    <img
                                                        src={newPhotoPreview}
                                                        alt="Aperçu de la photo"
                                                        className="h-56 w-full object-cover"
                                                    />
                                                ) : (
                                                    <div className="flex h-56 items-center justify-center px-4 text-center text-sm text-[#5f7182]">
                                                        <div className="space-y-2">
                                                            <FileImage className="mx-auto h-5 w-5 text-[#94a3b8]" />
                                                            <p>Aucune photo sélectionnée.</p>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </Field>
                                </div>
                            )}
                        </div>
                    </SectionCard>
                ) : null}

                {current === 2 ? (
                    <SectionCard icon={Home} title="Représentant">
                        <div className="space-y-4">
                            <label className="mt-4 flex items-center gap-2 text-sm text-[#0f172a]">
                                <Checkbox checked={showRepresentant} onChange={(e) => updateRepresentantVisibility(e.target.checked)} />
                                Le propriétaire a un représentant
                            </label>

                            {showRepresentant ? (
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">

                                    <Field label="Genre" error={fieldErrors.genre_representant_id}>
                                        <Select
                                            value={toId(data.genre_representant_id)}
                                            onValueChange={(value) => setData('genre_representant_id', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Sélectionner" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="">Aucun</SelectItem>
                                                {genreOptions.map((option) => (
                                                    <SelectItem key={option.value} value={option.value}>
                                                        {option.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </Field>

                                    <Field label="Nom du représentant" error={fieldErrors.name_representant} >
                                        <Input
                                            value={data.name_representant}
                                            onChange={(e) => setData('name_representant', e.target.value)}
                                            placeholder="Nom du représentant"
                                        />
                                    </Field>

                                    <PhoneInput
                                        label="Téléphone 1"
                                        required
                                        error={fieldErrors.tel1_representant}
                                        value={data.tel1_representant}
                                        onChange={(e) => setData('tel1_representant', e.target.value ?? '')}
                                        placeholder="07 00 00 00 00"
                                    />

                                    <PhoneInput
                                        label="Téléphone 2"
                                        error={fieldErrors.tel2_representant}
                                        value={data.tel2_representant}
                                        onChange={(e) => setData('tel2_representant', e.target.value ?? '')}
                                        placeholder="05 00 00 00 00"
                                    />


                                    
                                    <Field label="Type de pièce" required error={fieldErrors.type_pieces_representant_id}>
                                        <Select
                                            value={toId(data.type_pieces_representant_id)}
                                            onValueChange={(value) => setData('type_pieces_representant_id', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Sélectionner" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="">Aucun</SelectItem>
                                                {typePieceOptions.map((option) => (
                                                    <SelectItem key={option.value} value={option.value}>
                                                        {option.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </Field>

                                    <Field label="Numéro de pièce" required error={fieldErrors.numpiece_representant}>
                                        <Input
                                            value={data.numpiece_representant}
                                            onChange={(e) => setData('numpiece_representant', e.target.value)}
                                            placeholder="CI000000000"
                                        />
                                    </Field>

                                    

                                 

                                    

                                    

                                    <Field label="Email" error={fieldErrors.email_representant} className="md:col-span-2">
                                        <Input
                                            type="email"
                                            value={data.email_representant}
                                            onChange={(e) => setData('email_representant', e.target.value)}
                                            placeholder="representant@exemple.com"
                                        />
                                    </Field>


                                    <Field label="Adresse du représentant" error={fieldErrors.adresse_representant} className="md:col-span-2">
                                        <textarea
                                            className={textareaClassName}
                                            rows={3}
                                            value={data.adresse_representant}
                                            onChange={(e) => setData('adresse_representant', e.target.value)}
                                            placeholder="Adresse du représentant"
                                        />
                                    </Field>

                                    <Field label="Photo du représentant" error={fieldErrors.photo_representant} className="md:col-span-2">
                                        <div className="rounded-xl border border-dashed border-[#c8d4de] bg-[#f8fafc] p-4">
                                            <Input
                                                type="file"
                                                accept="image/*"
                                                onChange={(e) => {
                                                    const file = e.target.files?.[0] ?? null;
                                                    setData('photo_representant', file);
                                                }}
                                            />

                                            <div className="mt-3 overflow-hidden rounded-lg border border-[#e2e8f0] bg-white">
                                                {newRepresentantPhotoPreview || liaison?.photo_representant ? (
                                                    <img
                                                        src={
                                                            newRepresentantPhotoPreview ||
                                                            (String(liaison.photo_representant).startsWith('http')
                                                                ? liaison.photo_representant
                                                                : `/storage/${liaison.photo_representant}`)
                                                        }
                                                        alt="Aperçu de la photo du représentant"
                                                        className="h-56 w-full object-cover"
                                                    />
                                                ) : (
                                                    <div className="flex h-56 items-center justify-center px-4 text-center text-sm text-[#5f7182]">
                                                        <div className="space-y-2">
                                                            <FileImage className="mx-auto h-5 w-5 text-[#94a3b8]" />
                                                            <p>Sélectionnez une image pour la photo du représentant.</p>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </Field>


                                       
                                </div>
                            ) : (
                                <p className="text-sm text-[#5f7182]">Cochez la case si le propriétaire dispose d'un représentant.</p>
                            )}
                        </div>
                    </SectionCard>
                ) : null}

                {current === 3 ? (
                    <SectionCard icon={ShieldCheck} title="Résumé">
                        <div className="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                            <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Nom</p>
                                <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(data.name)}</p>
                            </div>
                            <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Téléphone</p>
                                <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(data.tel1)}</p>
                            </div>
                            <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Genre</p>
                                <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(selectedGenreLabel)}</p>
                            </div>
                            <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Genre du représentant</p>
                                <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(selectedRepresentativeGenreLabel)}</p>
                            </div>
                            <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Pièce</p>
                                <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(selectedTypePieceLabel)}</p>
                            </div>
                            <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Numéro de pièce</p>
                                <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(data.numpiece)}</p>
                            </div>
                            <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Expiration pièce</p>
                                <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(data.date_expiration_piece)}</p>
                            </div>
                            <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Région</p>
                                <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(selectedRegionLabel)}</p>
                            </div>
                            <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Ville</p>
                                <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(selectedVilleLabel)}</p>
                            </div>
                            <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Représentant</p>
                                <p className="text-sm font-semibold text-[#0f172a]">{showRepresentant ? 'Oui' : 'Non'}</p>
                            </div>
                            <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4 md:col-span-2 xl:col-span-3">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Adresse</p>
                                <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(data.adresse)}</p>
                            </div>
                            <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4 md:col-span-2 xl:col-span-3">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Photo</p>
                                <p className="text-sm font-semibold text-[#0f172a]">
                                    {isEdit && data.photo ? data.photo.name : isEdit && proprietaire?.photo ? 'Photo actuelle conservée' : 'Aucune photo'}
                                </p>
                            </div>

                            {showRepresentant ? (
                                <div className="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-[#c8d4de] bg-white p-4">
                                    <p className="text-sm font-semibold text-[#0f172a]">Représentant</p>
                                    <div className="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                                        <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4 md:col-span-2 xl:col-span-3">
                                            <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Nom du représentant</p>
                                            <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(data.name_representant)}</p>
                                        </div>
                                        <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                            <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Genre du représentant</p>
                                            <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(selectedRepresentativeGenreLabel)}</p>
                                        </div>
                                        <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4 md:col-span-2 xl:col-span-3">
                                            <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Adresse du représentant</p>
                                            <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(data.adresse_representant)}</p>
                                        </div>
                                        <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                            <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Téléphone 1 du représentant</p>
                                            <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(data.tel1_representant)}</p>
                                        </div>
                                        <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                            <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Téléphone 2 du représentant</p>
                                            <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(data.tel2_representant)}</p>
                                        </div>
                                        <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4 md:col-span-2 xl:col-span-3">
                                            <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Email du représentant</p>
                                            <p className="text-sm font-semibold text-[#0f172a] break-words">{summarizeValue(data.email_representant)}</p>
                                        </div>
                                        <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4 md:col-span-2 xl:col-span-3">
                                            <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Pièce du représentant</p>
                                            <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(selectedRepresentativeTypePieceLabel)}</p>
                                        </div>
                                        <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                            <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Numéro de pièce du représentant</p>
                                            <p className="text-sm font-semibold text-[#0f172a]">{summarizeValue(data.numpiece_representant)}</p>
                                        </div>
                                        <div className="rounded-xl border border-[#e2e8f0] bg-[#f8fafc] p-4 md:col-span-2 xl:col-span-3">
                                            <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Photo du représentant</p>
                                            <p className="text-sm font-semibold text-[#0f172a]">
                                                {isEdit && data.photo_representant ? data.photo_representant.name : isEdit && liaison?.photo_representant ? 'Photo actuelle conservée' : 'Aucune photo'}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            ) : null}
                        </div>
                    </SectionCard>
                ) : null}

                <div className="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <Button asChild type="button" variant="outline" className={agenceButtonStyles.outline}>
                        <Link href="/agence/proprietaire">Annuler</Link>
                    </Button>

                    <div className="flex gap-3">
                        {current > 0 ? (
                            <Button
                                type="button"
                                variant="outline"
                                className={agenceButtonStyles.outline}
                                onClick={() => goTo(current - 1)}
                            >
                                Précédent
                            </Button>
                        ) : null}

                        {!isLastStep ? (
                            <Button
                                type="button"
                                className={cn('gap-2', agenceButtonStyles.primary)}
                                onClick={(e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    handleNext();
                                }}
                            >
                                Suivant
                                <ChevronRight className="h-4 w-4" />
                            </Button>
                        ) : (
                            <Button type="submit" className={cn('gap-2', agenceButtonStyles.primary)} disabled={processing}>
                                <Save className="h-4 w-4" />
                                {processing ? 'Enregistrement...' : isEdit ? 'Enregistrer les modifications' : 'Créer le propriétaire'}
                            </Button>
                        )}
                    </div>
                </div>
            </form>
        </AgenceLayout>
    );
}
