import { useEffect, useMemo, useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import {
    ArrowLeft,
    BadgeCheck,
    Camera,
    Check,
    ChevronRight,
    Save,
    ShieldCheck,
    UserRound,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
import { agenceButtonStyles } from '../../../lib/buttonStyles';
import { cn } from '../../../lib/utils';

const asArray = (value) => (Array.isArray(value) ? value : []);

const toId = (value) => (value === null || value === undefined || value === '' ? '' : String(value));

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

function buildInitialData(personnel, isEdit) {
    return {
        name: personnel?.name ?? '',
        adresse: personnel?.adresse ?? '',
        email: personnel?.email ?? '',
        tel1: personnel?.tel1 ?? '',
        tel2: personnel?.tel2 ?? '',
        role_id: toId(personnel?.role_id ?? personnel?.role?.role_id ?? personnel?.role?.id),
        password: '',
        statut: personnel?.statut ?? 'actif',
        photo: null,
        is_edit: Boolean(isEdit),
    };
}

function resolvePersonnelId(personnel) {
    return personnel?.id_users ?? personnel?.user_id ?? personnel?.id ?? '';
}

function currentPhotoUrl(personnel) {
    if (!personnel?.photo) {
        return '';
    }

    if (String(personnel.photo).startsWith('http')) {
        return personnel.photo;
    }

    return `/storage/${personnel.photo}`;
}

export default function Form({ mode = 'create', personnel = null, roles = [] }) {
    const isEdit = mode === 'edit';
    const { data, setData, post, put, processing, errors } = useForm(buildInitialData(personnel, isEdit));
    const [current, setCurrent] = useState(0);
    const [completed, setCompleted] = useState([]);
    const [stepErrors, setStepErrors] = useState({});
    const [preview, setPreview] = useState(currentPhotoUrl(personnel));

    const roleOptions = useMemo(
        () =>
            asArray(roles).map((role) => ({
                value: toId(role?.role_id ?? role?.id ?? role?.value),
                label: role?.name ?? role?.libelle ?? role?.nom ?? 'N/A',
            })),
        [roles]
    );

    const steps = [
        { key: 'identity', title: 'Identité', subtitle: 'Nom et contact' },
        { key: 'account', title: 'Compte', subtitle: 'Rôle et mot de passe' },
        { key: 'photo', title: 'Photo', subtitle: 'Aperçu et upload' },
        { key: 'resume', title: 'Résumé', subtitle: 'Validation finale' },
    ];

    useEffect(() => {
        if (!data.photo) {
            return;
        }

        const objectUrl = URL.createObjectURL(data.photo);
        setPreview(objectUrl);

        return () => URL.revokeObjectURL(objectUrl);
    }, [data.photo]);

    const fieldErrors = { ...errors, ...stepErrors };
    const isLastStep = current === steps.length - 1;

    const goTo = (index) => {
        setStepErrors({});
        setCurrent(index);

        if (typeof window !== 'undefined') {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };

    const validateStep = (index) => {
        const localErrors = {};

        if (index === 0) {
            if (!data.name.trim()) localErrors.name = 'Le nom du membre est obligatoire.';
            if (!data.adresse.trim()) localErrors.adresse = 'L\'adresse est obligatoire.';
            if (!data.tel1.trim()) localErrors.tel1 = 'Le contact principal est obligatoire.';
        }

        if (index === 1) {
            if (!toId(data.role_id)) localErrors.role_id = 'Le rôle est obligatoire.';
            if (!isEdit && !data.password.trim()) localErrors.password = 'Le mot de passe est obligatoire.';
        }

        return localErrors;
    };

    const handleNext = () => {
        const localErrors = validateStep(current);

        if (Object.keys(localErrors).length) {
            setStepErrors(localErrors);
            return;
        }

        setCompleted((previous) => (previous.includes(current) ? previous : [...previous, current]));
        goTo(Math.min(current + 1, steps.length - 1));
    };

    const handleSubmit = (e) => {
        e?.preventDefault?.();

        const options = {
            preserveScroll: true,
            forceFormData: true,
        };

        if (isEdit) {
            put(`/agence/personnel/${resolvePersonnelId(personnel)}`, options);
            return;
        }

        post('/agence/personnel', options);
    };

    return (
        <AgenceLayout title={isEdit ? 'Modifier un membre du personnel' : 'Nouveau membre du personnel'}>
            <Head title={isEdit ? 'Modifier un membre du personnel' : 'Nouveau membre du personnel'} />

            <form
                onSubmit={(e) => {
                    e.preventDefault();

                    if (!isLastStep) {
                        handleNext();
                    }
                }}
                className="mx-auto flex w-full max-w-6xl flex-col gap-6 pb-10"
            >
                <div className="flex items-center gap-3">
                    <Button asChild type="button" variant="outline" size="icon" className="rounded-xl border-[#c8d4de]">
                        <Link href="/agence/personnel">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>

                    <div>
                        <h1 className="text-xl font-semibold text-[#0f172a]">
                            {isEdit ? 'Modifier un membre du personnel' : 'Nouveau membre du personnel'}
                        </h1>
                        <p className="text-sm text-[#5f7182]">
                            {isEdit
                                ? 'Mettez à  jour les informations du membre.'
                                : 'Renseignez les informations du membre.'}
                        </p>
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
                            <p className="mb-2 text-sm font-semibold text-[#b42318]">Veuillez corriger les erreurs ci-dessous.</p>
                            <ul className="space-y-1 text-sm text-[#7f1d1d]">
                                {Object.values(fieldErrors)
                                    .slice(0, 8)
                                    .map((message, index) => (
                                        <li key={`${message}-${index}`}>â€¢ {message}</li>
                                    ))}
                            </ul>
                        </CardContent>
                    </Card>
                ) : null}

                {current === 0 ? (
                    <SectionCard
                        icon={UserRound}
                        title="Informations personnelles"
                        description="Identité et coordonnées du membre."
                    >
                        <div className="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                            <Field label="Nom et prénom" required error={fieldErrors.name} className="md:col-span-2">
                                <Input
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="Kouamé Jean-Baptiste"
                                />
                            </Field>

                            <Field label="Adresse" required error={fieldErrors.adresse} className="md:col-span-2">
                                <textarea
                                    className={textareaClassName}
                                    rows={3}
                                    value={data.adresse}
                                    onChange={(e) => setData('adresse', e.target.value)}
                                    placeholder="Cocody Riviera 3, Abidjan"
                                />
                            </Field>

                            <Field label="Téléphone 1" required error={fieldErrors.tel1}>
                                <Input
                                    value={data.tel1}
                                    onChange={(e) => setData('tel1', e.target.value)}
                                    placeholder="+225 07 01 23 45 67"
                                />
                            </Field>

                            <Field label="Téléphone 2" error={fieldErrors.tel2}>
                                <Input
                                    value={data.tel2}
                                    onChange={(e) => setData('tel2', e.target.value)}
                                    placeholder="+225 05 44 78 12 99"
                                />
                            </Field>

                            <Field label="Email" error={fieldErrors.email} className="md:col-span-2">
                                <Input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="exemple@agence.ci"
                                />
                            </Field>
                        </div>
                    </SectionCard>
                ) : null}

                {current === 1 ? (
                    <SectionCard icon={ShieldCheck} title="Compte et sécurité" description="Rôle, statut et mot de passe du membre.">
                        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <Field label="Rôle" required error={fieldErrors.role_id}>
                                <Select value={toId(data.role_id)} onValueChange={(value) => setData('role_id', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Sélectionner" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {roleOptions.map((role) => (
                                            <SelectItem key={role.value} value={role.value}>
                                                {role.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>

                            {isEdit ? (
                                <Field label="Statut" error={fieldErrors.statut}>
                                    <Select value={toId(data.statut)} onValueChange={(value) => setData('statut', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Sélectionner" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="actif">Actif</SelectItem>
                                            <SelectItem value="inactif">Inactif</SelectItem>
                                            <SelectItem value="suspendu">Suspendu</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </Field>
                            ) : null}

                            <Field
                                label={`Mot de passe ${isEdit ? '' : ''}`}
                                required={!isEdit}
                                error={fieldErrors.password}
                                className="md:col-span-2"
                            >
                                <Input
                                    type="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="********"
                                    autoComplete="new-password"
                                />
                            </Field>
                        </div>
                    </SectionCard>
                ) : null}

                {current === 2 ? (
                    <SectionCard icon={Camera} title="Photo" description="Ajoutez une photo au profil du membre.">
                        <div className="mt-4 grid gap-6 lg:grid-cols-[180px_minmax(0,1fr)]">
                            <div className="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] p-4">
                                <div className="flex h-28 w-28 items-center justify-center overflow-hidden rounded-2xl bg-white">
                                    {preview ? (
                                        <img src={preview} alt="Aperçu de la photo" className="h-full w-full object-cover" />
                                    ) : (
                                        <UserRound className="h-10 w-10 text-[#94a3b8]" />
                                    )}
                                </div>
                                <p className="text-xs text-[#5f7182] text-center">
                                    PNG, JPG ou WEBP
                                </p>
                            </div>

                            <div className="mt-4 space-y-4">
                                <Field label="Choisir une photo" error={fieldErrors.photo}>
                                    <Input
                                        type="file"
                                        accept="image/*"
                                        onChange={(e) => setData('photo', e.target.files?.[0] ?? null)}
                                    />
                                </Field>

                                <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4 text-sm text-[#5f7182]">
                                    La photo est optionnelle, mais elle facilite l'identification rapide du membre.
                                </div>
                            </div>
                        </div>
                    </SectionCard>
                ) : null}

                {current === 3 ? (
                    <SectionCard icon={BadgeCheck} title="Résumé" description="Vérifiez les informations avant d'enregistrer.">
                        <div className="mt-4 grid gap-3 md:grid-cols-2">
                            <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Nom</p>
                                <p className="mt-1 text-sm font-medium text-[#0f172a]">{data.name || 'â€”'}</p>
                            </div>

                            <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Téléphone 1</p>
                                <p className="mt-1 text-sm font-medium text-[#0f172a]">{data.tel1 || 'â€”'}</p>
                            </div>

                            <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Rôle</p>
                                <p className="mt-1 text-sm font-medium text-[#0f172a]">
                                    {roleOptions.find((option) => option.value === toId(data.role_id))?.label || 'â€”'}
                                </p>
                            </div>

                            <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                <p className="text-xs uppercase tracking-wide text-[#94a3b8]">Statut</p>
                                <p className="mt-1 text-sm font-medium text-[#0f172a]">{data.statut || 'actif'}</p>
                            </div>
                        </div>
                    </SectionCard>
                ) : null}

                                <div className="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <Button asChild type="button" variant="outline" className={agenceButtonStyles.outline}>
                        <Link href="/agence/personnel">Annuler</Link>
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
                            <Button type="button" className={cn('gap-2', agenceButtonStyles.primary)} onClick={handleNext}>
                                Suivant
                                <ChevronRight className="h-4 w-4" />
                            </Button>
                        ) : (
                            <Button
                                type="button"
                                className={cn('gap-2', agenceButtonStyles.primary)}
                                onClick={handleSubmit}
                                disabled={processing}
                            >
                                <Save className="h-4 w-4" />
                                {processing ? 'Enregistrement...' : isEdit ? 'Enregistrer les modifications' : 'Créer le membre'}
                            </Button>
                        )}
                    </div>
                </div>
            </form>
        </AgenceLayout>
    );
}
