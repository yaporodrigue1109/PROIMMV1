import { useMemo, useState } from 'react';
import { Link } from '@inertiajs/react';
import { ArrowLeft, PencilLine, ShieldCheck, Sparkles } from 'lucide-react';

import AdminLayout from '../../../Layouts/AdminLayout';
import { Button } from '../../../components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '../../../components/ui/card';
import { Checkbox } from '../../../components/ui/checkbox';
import { Input } from '../../../components/ui/input';
import { Label } from '../../../components/ui/label';

const selectClassName =
    'flex h-11 w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

const textareaClassName =
    'flex min-h-[120px] w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white placeholder:text-[#8798a5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

const formatMoney = (value) =>
    new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Number(value ?? 0)) + ' FCFA';

export default function Form({ mode, module = null }) {
    const isEdit = mode === 'edit';

    const permissionsCatalog = useMemo(
        () => ['Lecture', 'Création', 'Modification', 'Suppression', 'Export', 'Administration'],
        []
    );

    const [form, setForm] = useState({
        nom: module?.nom ?? '',
        code: module?.code ?? '',
        categorie: module?.categorie ?? 'Communication',
        statut: module?.statut ?? 'En attente',
        description: module?.description ?? '',
        prix: Number(module?.prix ?? 15000),
        cycle: module?.cycle ?? 'Mensuel',
        permissions: module?.permissions ?? [],
    });

    const togglePermission = (permission) => {
        setForm((current) => ({
            ...current,
            permissions: current.permissions.includes(permission)
                ? current.permissions.filter((item) => item !== permission)
                : [...current.permissions, permission],
        }));
    };

    return (
        <AdminLayout title={isEdit ? 'Modifier un module' : 'Ajouter un module'}>
            <div className="space-y-6">
                <Card className="rounded-3xl border-slate-200 shadow-sm">
                    <CardContent className="flex flex-col gap-5 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div className="space-y-3">
                    
                            <div>
                                <h1 className="mt-4 text-3xl font-semibold tracking-tight text-slate-900">
                                    {isEdit ? 'Modifier un module' : 'Ajouter un module'}
                                </h1>
                         
                            </div>
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <Button asChild variant="outline" className="mt-4 h-11 rounded-xl border-slate-200 px-4 text-slate-900">
                                <Link href="/admin/modules">
                                    <ArrowLeft className="h-4 w-4" />
                                    Retour
                                </Link>
                            </Button>
                            {isEdit ? (
                                <Button asChild variant="outline" className="h-11 rounded-xl border-slate-200 px-4 text-slate-900">
                                    <Link href={`/admin/modules/${module?.code}`}>
                                        <PencilLine className="h-4 w-4" />
                                        Voir la fiche
                                    </Link>
                                </Button>
                            ) : null}
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <div className="space-y-6">
                        <Card className="rounded-3xl border-slate-200 shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg">Informations generales</CardTitle>
                            </CardHeader>
                            <CardContent className="mt-4 grid gap-4 p-6 sm:grid-cols-2">
                                <Field label="Nom du module" className="sm:col-span-2">
                                    <Input
                                        value={form.nom}
                                        onChange={(event) => setForm({ ...form, nom: event.target.value })}
                                        placeholder="SMS"
                                    />
                                </Field>

                                <Field label="Code module">
                                    <Input
                                        value={form.code}
                                        onChange={(event) => setForm({ ...form, code: event.target.value })}
                                        placeholder="MOD-SMS"
                                    />
                                </Field>

                                <Field label="Categorie">
                                    <select
                                        value={form.categorie}
                                        onChange={(event) => setForm({ ...form, categorie: event.target.value })}
                                        className={selectClassName}
                                    >
                                        {['Communication', 'Espace client', 'Pilotage', 'Publication', 'Support'].map((categorie) => (
                                            <option key={categorie} value={categorie}>
                                                {categorie}
                                            </option>
                                        ))}
                                    </select>
                                </Field>

                                <Field label="Statut">
                                    <select
                                        value={form.statut}
                                        onChange={(event) => setForm({ ...form, statut: event.target.value })}
                                        className={selectClassName}
                                    >
                                        {['Actif', 'En attente', 'Suspendu'].map((statut) => (
                                            <option key={statut} value={statut}>
                                                {statut}
                                            </option>
                                        ))}
                                    </select>
                                </Field>

                                <Field label="Description" className="sm:col-span-2">
                                    <textarea
                                        value={form.description}
                                        onChange={(event) => setForm({ ...form, description: event.target.value })}
                                        className={textareaClassName}
                                        placeholder="Explique le rôle du module"
                                    />
                                </Field>
                            </CardContent>
                        </Card>

                        <Card className="rounded-3xl border-slate-200 shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg">Tarification</CardTitle>
                                <CardDescription>Prix mensuel et cycle de facturation.</CardDescription>
                            </CardHeader>
                            <CardContent className="mt-4 grid gap-4 p-6 sm:grid-cols-2">
                                <Field label="Prix">
                                    <Input
                                        type="number"
                                        value={form.prix}
                                        onChange={(event) => setForm({ ...form, prix: event.target.value })}
                                        placeholder="15000"
                                    />
                                </Field>

                                <Field label="Cycle">
                                    <select
                                        value={form.cycle}
                                        onChange={(event) => setForm({ ...form, cycle: event.target.value })}
                                        className={selectClassName}
                                    >
                                        {['Mensuel', 'Trimestriel', 'Semestriel', 'Annuel'].map((cycle) => (
                                            <option key={cycle} value={cycle}>
                                                {cycle}
                                            </option>
                                        ))}
                                    </select>
                                </Field>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card className="rounded-3xl border-slate-200 shadow-sm">
                            <CardHeader className="border-b border-slate-200">
                                <CardTitle className="text-lg">Permissions</CardTitle>
                            </CardHeader>
                            <CardContent className="mt-4 space-y-3 p-6">
                                {permissionsCatalog.map((permission) => {
                                    const checked = form.permissions.includes(permission);

                                    return (
                                        <label
                                            key={permission}
                                            className={`flex cursor-pointer items-center gap-3 rounded-2xl border px-4 py-3 transition ${
                                                checked
                                                    ? 'border-[#00559b] bg-blue-50'
                                                    : 'border-slate-200 bg-white hover:bg-slate-50'
                                            }`}
                                        >
                                            <Checkbox
                                                checked={checked}
                                                onChange={() => togglePermission(permission)}
                                            />
                                            <span className="text-sm font-medium text-slate-900">{permission}</span>
                                        </label>
                                    );
                                })}
                            </CardContent>
                        </Card>

                        
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}

function Field({ label, className = '', children }) {
    return (
        <label className={`space-y-2 ${className}`}>
            <Label className="text-sm font-medium text-slate-700">{label}</Label>
            {children}
        </label>
    );
}

function SummaryMini({ label, value }) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{label}</p>
            <p className="mt-2 text-base font-semibold text-slate-900">{value}</p>
        </div>
    );
}
