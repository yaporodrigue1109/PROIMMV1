import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Plus, Save, ShieldAlert, X } from 'lucide-react';

import AdminLayout from '../../../Layouts/AdminLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent } from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
import { Switch } from '../../../components/ui/switch';
import { agenceButtonStyles } from '../../../lib/buttonStyles';
import { cn } from '../../../lib/utils';

const defaultActions = [
    { id: 1, label: 'Voir', slug: 'view', order: 1, is_critical: false },
    { id: 2, label: 'Créer', slug: 'create', order: 2, is_critical: false },
    { id: 3, label: 'Modifier', slug: 'edit', order: 3, is_critical: false },
    { id: 4, label: 'Supprimer', slug: 'delete', order: 4, is_critical: true },
];

const fallbackParentModules = [
    'Tableau de bord',
    'Propriétés',
    'Propriétaires',
    'Locataires',
    'Personnel',
    'Maintenance',
    'Caisse',
    'Reversement',
    'Statistiques',
    'Support',
    'Paramétrage',
];

export default function Form({ mode, module = null, parentModules = [] }) {
    const isEdit = mode === 'edit';
    const parentOptions = parentModules.length > 0
        ? parentModules
        : fallbackParentModules.map((label) => ({ value: label, label }));
    const initialActions = Array.isArray(module?.actions) && module.actions.length > 0
        ? module.actions.map((action, index) => ({
            ...action,
            id: action.id ?? action.module_action_id ?? index + 1,
            label: action.label ?? action.name ?? '',
            order: action.order ?? action.order_index ?? index + 1,
            is_critical: Boolean(action.is_critical),
        }))
        : defaultActions;
    const { data: form, setData, post, put, processing, errors } = useForm({
        name: module?.name ?? module?.nom ?? '',
        slug: module?.slug ?? module?.code?.toLowerCase().replace(/^mod-/, '') ?? '',
        route: module?.route ?? '',
        icon: module?.icon ?? '',
        parent: module?.parent_id ?? module?.parent ?? 'none',
        status: module?.statut ?? (module?.is_active === false ? 'Inactif' : 'Actif'),
        actions: initialActions,
    });
    const actions = form.actions;

    const updateField = (field, value) => {
        setData(field, value);
    };

    const updateAction = (id, field, value) => {
        setData(
            'actions',
            actions.map((action) => (action.id === id ? { ...action, [field]: value } : action)),
        );
    };

    const addAction = () => {
        setData('actions', [
            ...actions,
            {
                id: Date.now(),
                label: '',
                slug: '',
                order: actions.length > 0 ? Math.max(...actions.map((action) => Number(action.order) || 0)) + 1 : 1,
                is_active: true,
                is_critical: false,
            },
        ]);
    };

    const removeAction = (id) => {
        setData('actions', actions.filter((action) => action.id !== id));
    };

    const handleSubmit = (event) => {
        event.preventDefault();

        if (isEdit) {
            put(`/admin/modules/${module.slug ?? module.code}`, { preserveScroll: true });
            return;
        }

        post('/admin/modules', { preserveScroll: true });
    };

    return (
        <AdminLayout title={isEdit ? 'Modifier un module' : 'Ajouter un module'}>
            <div className="mb-6 flex items-center gap-3">
                <Button
                    asChild
                    type="button"
                    variant="outline"
                    size="icon"
                    className="rounded-xl border-[#c8d4de]"
                >
                    <Link href="/admin/modules" aria-label="Retour à la liste" title="Retour à la liste">
                        <ArrowLeft className="h-4 w-4" />
                    </Link>
                </Button>
                <h1 className="text-xl font-semibold text-[#0f172a]">
                    {isEdit ? 'Modifier un module' : 'Ajouter un module'}
                </h1>
            </div>

            <Card className="overflow-hidden rounded-3xl border-slate-200 bg-white shadow-sm">
                <CardContent className="p-6 lg:p-7">
                    <form onSubmit={handleSubmit}>
                        <div className="grid gap-x-8 gap-y-5 md:grid-cols-2">
                            <Field label="Nom du module" required error={errors.name}>
                                <Input
                                    value={form.name}
                                    onChange={(event) => updateField('name', event.target.value)}
                                    placeholder="Ex: Gestion des missions"
                                />
                            </Field>

                            <Field
                                label="Slug"
                                required
                                hint="Identifiant technique utilisé pour les permissions."
                                error={errors.slug}
                            >
                                <Input
                                    value={form.slug}
                                    onChange={(event) => updateField('slug', event.target.value)}
                                    placeholder="Ex: missions"
                                />
                            </Field>

                            <Field label="Route" error={errors.route}>
                                <Input
                                    value={form.route}
                                    onChange={(event) => updateField('route', event.target.value)}
                                    placeholder="Ex: users.gestion-missions.index"
                                />
                            </Field>

                            <Field
                                label="Icône (chemin SVG)"
                                hint="Chemin vers un fichier SVG dans le dossier public."
                                error={errors.icon}
                            >
                                <Input
                                    value={form.icon}
                                    onChange={(event) => updateField('icon', event.target.value)}
                                    placeholder="Ex: admin/icones_module/missions.svg"
                                />
                            </Field>

                            <Field label="Module parent" error={errors.parent_id ?? errors.parent}>
                                <Select
                                    value={form.parent}
                                    onValueChange={(value) => updateField('parent', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Aucun (module principal)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">Aucun (module principal)</SelectItem>
                                        {parentOptions.map((parent) => (
                                            <SelectItem key={parent.value} value={String(parent.value)}>
                                                {parent.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </Field>

                            <Field label="Statut" required error={errors.status}>
                                <Select
                                    value={form.status}
                                    onValueChange={(value) => updateField('status', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Sélectionner" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Actif">Actif</SelectItem>
                                        <SelectItem value="Inactif">Inactif</SelectItem>
                                    </SelectContent>
                                </Select>
                            </Field>
                        </div>

                        <div className="my-7 border-t border-slate-200" />

                        <section aria-labelledby="module-actions-title">
                            <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 id="module-actions-title" className="text-lg font-semibold text-slate-900">
                                        Actions du module
                                    </h2>
                                    <p className="mt-0.5 text-sm text-slate-600">
                                        Exemple : voir, créer, modifier, annuler, valider, exporter...
                                    </p>
                                </div>

                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={addAction}
                                    className="h-10 rounded-xl border-[#00559b] px-4 font-semibold text-[#00559b] shadow-none hover:bg-[#eaf4fb] hover:text-[#004980]"
                                >
                                    <Plus className="h-4 w-4" />
                                    Ajouter une action
                                </Button>
                            </div>

                            <div className="space-y-2.5">
                                {actions.map((action, index) => (
                                    <div
                                        key={action.id}
                                        className={cn(
                                            'grid gap-2.5 rounded-xl border p-3 sm:grid-cols-[1fr_1fr_0.55fr_0.72fr_auto]',
                                            action.is_critical ? 'border-amber-300 bg-amber-50/60' : 'border-slate-200 bg-white'
                                        )}
                                    >
                                        <div>
                                            <Input
                                                aria-label={`Nom de l'action ${index + 1}`}
                                                value={action.label}
                                                onChange={(event) => updateAction(action.id, 'label', event.target.value)}
                                                placeholder="Nom de l'action"
                                            />
                                            <FieldError message={errors[`actions.${index}.label`]} />
                                        </div>
                                        <div>
                                            <Input
                                                aria-label={`Slug de l'action ${index + 1}`}
                                                value={action.slug}
                                                onChange={(event) => updateAction(action.id, 'slug', event.target.value)}
                                                placeholder="Slug"
                                            />
                                            <FieldError message={errors[`actions.${index}.slug`]} />
                                        </div>
                                        <div>
                                            <Input
                                                aria-label={`Ordre de l'action ${index + 1}`}
                                                type="number"
                                                min="0"
                                                value={action.order}
                                                onChange={(event) => updateAction(action.id, 'order', event.target.value)}
                                            />
                                            <FieldError message={errors[`actions.${index}.order`]} />
                                        </div>
                                        <div className="flex h-10 items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-3">
                                            <span className="flex items-center gap-1.5 text-xs font-medium text-slate-700">
                                                <ShieldAlert className={cn('h-4 w-4', action.is_critical ? 'text-amber-600' : 'text-slate-400')} />
                                                Critique
                                            </span>
                                            <Switch
                                                aria-label={`Action critique ${action.label || index + 1}`}
                                                checked={Boolean(action.is_critical)}
                                                onCheckedChange={(checked) => updateAction(action.id, 'is_critical', checked)}
                                            />
                                        </div>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="icon"
                                            aria-label={`Supprimer l'action ${action.label || index + 1}`}
                                            onClick={() => removeAction(action.id)}
                                            className="h-10 w-full rounded-xl border-red-400 text-red-500 shadow-none hover:border-red-500 hover:bg-red-50 hover:text-red-600 sm:w-10"
                                        >
                                            <X className="h-4 w-4" />
                                        </Button>
                                    </div>
                                ))}
                            </div>
                        </section>

                        <div className="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div className="w-full sm:w-auto">
                                <Button
                                    asChild
                                    type="button"
                                    variant="outline"
                                    className={cn(agenceButtonStyles.outline, 'w-full sm:w-auto')}
                                >
                                    <Link href="/admin/modules">Annuler</Link>
                                </Button>
                            </div>

                            <div className="flex w-full gap-2 sm:w-auto sm:justify-end">
                                <Button
                                    type="submit"
                                    className={cn(agenceButtonStyles.primary, 'w-full sm:w-auto')}
                                    disabled={processing}
                                >
                                    <Save className="h-4 w-4" />
                                    {processing ? 'Enregistrement...' : isEdit ? 'Mettre à jour' : 'Enregistrer'}
                                </Button>
                            </div>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AdminLayout>
    );
}

function Field({ label, required = false, hint = null, error = null, children }) {
    return (
        <div className="space-y-1.5">
            <label className="block text-sm font-medium text-[#0f172a]">
                {label}
                {required ? <span className="ml-0.5 text-[#b42318]">*</span> : null}
            </label>
            {children}
            {hint ? <p className="text-xs text-[#5f7182]">{hint}</p> : null}
            <FieldError message={error} />
        </div>
    );
}

function FieldError({ message }) {
    return message ? <p className="mt-1 text-xs text-[#b42318]">{message}</p> : null;
}
