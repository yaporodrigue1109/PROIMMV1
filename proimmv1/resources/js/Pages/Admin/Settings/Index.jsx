import { useMemo, useState } from 'react';
import { Check, Cog, Globe, Layers3, Palette, Save, ShieldCheck, Sparkles, Upload } from 'lucide-react';

import AdminLayout from '../../../Layouts/AdminLayout';
import { Badge } from '../../../components/ui/badge';
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
import { Tabs, TabsList, TabsTrigger } from '../../../components/ui/tabs';

const inputClassName =
    'flex h-11 w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

const textareaClassName =
    'flex min-h-[120px] w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white placeholder:text-[#8798a5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

const formatMoney = (value) =>
    new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Number(value ?? 0)) + ' FCFA';

const durationChoices = [1, 3, 6, 12, 24, 36];

export default function Index({ setting = {}, tarifs = {} }) {
    const [tab, setTab] = useState('entreprise');

    const activeModules = useMemo(
        () => (tarifs.modules ?? []).filter((module) => module.actif),
        [tarifs.modules]
    );

    return (
        <AdminLayout title="Configuration">
            <div className="space-y-6">
                <Card className="rounded-3xl border-slate-200 shadow-sm">
                    <CardContent className="flex flex-col gap-5 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div className="max-w-3xl space-y-3">
                            <div className="inline-flex items-center gap-2 rounded-full border border-[#d8e3eb] bg-[#eef6fb] px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-[#00559b]">
                                <Sparkles className="h-3.5 w-3.5" />
                                Configuration globale
                            </div>
                            <div>
                                <h1 className="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                                    Configuration de l&apos;application
                                </h1>
                                <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-500 md:text-base">
                                    Mettez a jour l&apos;identite de l&apos;entreprise, la tarification et les modules disponibles depuis une interface plus claire.
                                </p>
                            </div>
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <Button type="button" variant="outline" className="h-11 rounded-xl border-slate-200 px-4 text-slate-900">
                                <Cog className="h-4 w-4" />
                                Raccourcis
                            </Button>
                            <Button type="button" className="h-11 rounded-xl bg-[#00559b] px-4 text-white hover:bg-[#004980]">
                                <Save className="h-4 w-4" />
                                Enregistrer
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card className="rounded-3xl border-slate-200 shadow-sm">
                    <CardContent className="p-4">
                        <Tabs value={tab} onValueChange={setTab} className="w-full">
                            <TabsList className="grid h-auto w-full grid-cols-2 rounded-2xl bg-slate-100 p-1">
                                <TabsTrigger value="entreprise" className="rounded-xl">
                                    Configuration de l&apos;entreprise
                                </TabsTrigger>
                                <TabsTrigger value="tarifaire" className="rounded-xl">
                                    Configuration tarifaire
                                </TabsTrigger>
                            </TabsList>
                        </Tabs>
                    </CardContent>
                </Card>

                {tab === 'entreprise' ? (
                    <div className="grid gap-6 xl:grid-cols-[1fr_340px]">
                        <form id="settings-form" action="/admin/settings" method="POST" encType="multipart/form-data" className="space-y-6">
                            <input type="hidden" name="_method" value="PUT" />
                            <div className="space-y-6">
                                <Section title="Identite generale" step="01" icon={Palette}>
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <Field label="Nom commercial">
                                            <Input name="name" defaultValue={setting.name ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="Raison sociale">
                                            <Input name="raison_social" defaultValue={setting.raison_social ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="Email principal">
                                            <Input name="email1" type="email" defaultValue={setting.email1 ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="Email secondaire">
                                            <Input name="email2" type="email" defaultValue={setting.email2 ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="Telephone principal">
                                            <Input name="contact1" defaultValue={setting.contact1 ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="Telephone secondaire">
                                            <Input name="contact2" defaultValue={setting.contact2 ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="Telephone tertiaire">
                                            <Input name="contact3" defaultValue={setting.contact3 ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="Boite postale">
                                            <Input name="boite_postal" defaultValue={setting.boite_postal ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="Adresse" className="md:col-span-2">
                                            <textarea name="adresse" defaultValue={setting.adresse ?? ''} className={textareaClassName} />
                                        </Field>
                                    </div>
                                </Section>

                                <Section title="Localisation et affichage" step="02" icon={Globe}>
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <Field label="Site web">
                                            <Input name="site_web" defaultValue={setting.site_web ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="Langue par defaut">
                                            <select name="langue" defaultValue={setting.langue ?? 'fr'} className={inputClassName}>
                                                <option value="fr">Francais</option>
                                                <option value="en">Anglais</option>
                                            </select>
                                        </Field>
                                    </div>
                                </Section>

                                <Section title="Informations legales" step="03" icon={ShieldCheck}>
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <Field label="Numero RCCM">
                                            <Input name="num_rccm" defaultValue={setting.num_rccm ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="Numero de compte courant">
                                            <Input name="num_cc" defaultValue={setting.num_cc ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="Numero CNPS">
                                            <Input name="num_cnps" defaultValue={setting.num_cnps ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="Capital social">
                                            <Input name="capital" type="number" defaultValue={setting.capital ?? ''} className={inputClassName} />
                                        </Field>
                                    </div>
                                </Section>

                                <Section title="Politiques et conditions" step="04" icon={ShieldCheck}>
                                    <div className="grid gap-4">
                                        <Field label="Politique de confidentialite">
                                            <textarea
                                                name="politique_confidentialite"
                                                defaultValue={setting.politique_confidentialite ?? ''}
                                                className={textareaClassName}
                                            />
                                        </Field>
                                        <Field label="Conditions generales">
                                            <textarea
                                                name="condition_generale"
                                                defaultValue={setting.condition_generale ?? ''}
                                                className={textareaClassName}
                                            />
                                        </Field>
                                        <Field label="CGU">
                                            <textarea name="cgu" defaultValue={setting.cgu ?? ''} className={textareaClassName} />
                                        </Field>
                                    </div>
                                </Section>

                                <Section title="Reseaux sociaux" step="05" icon={Globe}>
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <Field label="Facebook">
                                            <Input name="facebook" defaultValue={setting.facebook ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="Instagram">
                                            <Input name="instagram" defaultValue={setting.instagram ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="LinkedIn">
                                            <Input name="linkedin" defaultValue={setting.linkedin ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="Twitter">
                                            <Input name="twitter" defaultValue={setting.twitter ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="Google Business" className="md:col-span-2">
                                            <Input name="google" defaultValue={setting.google ?? ''} className={inputClassName} />
                                        </Field>
                                    </div>
                                </Section>
                            </div>
                        </form>

                        <aside className="space-y-6">
                            <Card className="rounded-3xl border-slate-200 shadow-sm">
                                <CardHeader className="border-b border-slate-200">
                                    <CardTitle className="text-lg">Identite visuelle</CardTitle>
                                    <CardDescription>Logo et apercu rapide de la configuration.</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4 p-6">
                                    {setting.logo ? (
                                        <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                            <img
                                                src={`/storage/${setting.logo}`}
                                                alt="Logo actuel"
                                                className="mx-auto max-h-24 object-contain"
                                            />
                                        </div>
                                    ) : (
                                        <div className="rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">
                                            Aucun logo configure.
                                        </div>
                                    )}

                                    <label className="block space-y-2">
                                        <Label className="text-sm font-medium text-slate-700">Logo</Label>
                                        <Input type="file" name="logo" className="h-11 rounded-md border-slate-200" />
                                    </label>

                                    <div className="grid gap-3">
                                        <Summary label="Nom" value={setting.name ?? 'Non defini'} />
                                        <Summary label="Email" value={setting.email1 ?? 'N/A'} />
                                        <Summary label="Telephone" value={setting.contact1 ?? 'N/A'} />
                                    </div>

                                    <Button type="submit" form="settings-form" className="h-11 w-full rounded-xl bg-[#00559b] text-white hover:bg-[#004980]">
                                        <Upload className="h-4 w-4" />
                                        Sauvegarder
                                    </Button>
                                </CardContent>
                            </Card>
                        </aside>
                    </div>
                ) : (
                    <div className="grid gap-6 xl:grid-cols-[1fr_340px]">
                        <form id="tarification-form" action="/admin/settings/tarification" method="POST" className="space-y-6">
                            <input type="hidden" name="_method" value="PUT" />
                            <div className="space-y-6">
                                <Section title="Plan principal" step="01" icon={Layers3}>
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <Field label="Nom du plan">
                                            <Input name="plan_nom" defaultValue={tarifs.plan_nom ?? ''} className={inputClassName} />
                                        </Field>
                                        <Field label="Prix mensuel">
                                            <Input
                                                name="plan_prix_mensuel"
                                                type="number"
                                                min="0"
                                                step="100"
                                                defaultValue={tarifs.plan_prix_mensuel ?? 0}
                                                className={inputClassName}
                                            />
                                        </Field>
                                        <Field label="Delai de grace">
                                            <Input
                                                name="delai_grace"
                                                type="number"
                                                min="0"
                                                defaultValue={tarifs.delai_grace ?? 0}
                                                className={inputClassName}
                                            />
                                        </Field>
                                        <Field label="Cycle de facturation">
                                            <select name="cycle_facturation" defaultValue={tarifs.cycle_facturation ?? 'mensuel'} className={inputClassName}>
                                                <option value="mensuel">Mensuel</option>
                                                <option value="trimestriel">Trimestriel</option>
                                                <option value="semestriel">Semestriel</option>
                                                <option value="annuel">Annuel</option>
                                            </select>
                                        </Field>
                                        <Field label="Description du plan" className="md:col-span-2">
                                            <textarea
                                                name="plan_description"
                                                defaultValue={tarifs.plan_description ?? ''}
                                                className={textareaClassName}
                                            />
                                        </Field>
                                    </div>
                                </Section>

                                <Section title="Durees disponibles" step="02" icon={Cog}>
                                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                        {durationChoices.map((value) => {
                                            const checked = (tarifs.durees ?? []).includes(value);
                                            return (
                                                <label
                                                    key={value}
                                                    className={`flex items-center gap-3 rounded-2xl border px-4 py-3 ${
                                                        checked ? 'border-[#00559b] bg-blue-50' : 'border-slate-200 bg-white'
                                                    }`}
                                                >
                                                    <Checkbox name="durees[]" value={value} defaultChecked={checked} />
                                                    <span className="text-sm font-medium text-slate-900">{value} mois</span>
                                                </label>
                                            );
                                        })}
                                    </div>
                                </Section>

                                <Section title="Modules tarifaires" step="03" icon={Layers3}>
                                    <div className="space-y-3">
                                        {(tarifs.modules ?? []).map((module) => (
                                            <div key={module.id} className="rounded-2xl border border-slate-200 bg-white p-4">
                                                <div className="grid gap-4 md:grid-cols-[1fr_160px_140px] md:items-center">
                                                    <label className="flex items-center gap-3">
                                                        <Checkbox
                                                            name={`modules[${module.id}][actif]`}
                                                            value="1"
                                                            defaultChecked={Boolean(module.actif)}
                                                        />
                                                        <span className="text-sm font-semibold text-slate-900">{module.label}</span>
                                                    </label>

                                                    <Input
                                                        name={`modules[${module.id}][label]`}
                                                        defaultValue={module.label}
                                                        className={inputClassName}
                                                    />

                                                    <Input
                                                        name={`modules[${module.id}][prix_mensuel]`}
                                                        type="number"
                                                        min="0"
                                                        defaultValue={module.prix_mensuel}
                                                        className={inputClassName}
                                                    />
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </Section>
                            </div>
                        </form>

                        <aside className="space-y-6">
                            <Card className="rounded-3xl border-slate-200 shadow-sm">
                                <CardHeader className="border-b border-slate-200">
                                    <CardTitle className="text-lg">Resume tarifaire</CardTitle>
                                    <CardDescription>Apercu rapide du plan et des modules actifs.</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4 p-6">
                                    <Summary label="Plan" value={tarifs.plan_nom ?? 'N/A'} />
                                    <Summary label="Prix mensuel" value={formatMoney(tarifs.plan_prix_mensuel ?? 0)} />
                                    <Summary label="Durees actives" value={`${(tarifs.durees ?? []).length} option(s)`} />
                                    <Summary label="Modules actifs" value={`${activeModules.length} module(s)`} />

                                    <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <p className="text-sm font-medium text-slate-500">Modules actifs</p>
                                        <div className="mt-3 flex flex-wrap gap-2">
                                            {activeModules.map((module) => (
                                                <Badge key={module.id} variant="outline" className="rounded-full">
                                                    {module.label}
                                                </Badge>
                                            ))}
                                        </div>
                                    </div>

                                    <Button type="submit" form="tarification-form" className="h-11 w-full rounded-xl bg-[#00559b] text-white hover:bg-[#004980]">
                                        <Save className="h-4 w-4" />
                                        Enregistrer la tarification
                                    </Button>
                                </CardContent>
                            </Card>
                        </aside>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}

function Section({ title, step, icon: Icon, children }) {
    return (
        <Card className="rounded-3xl border-slate-200 shadow-sm">
            <CardHeader className="border-b border-slate-200">
                <div className="flex items-start justify-between gap-3">
                    <div>
                        <div className="inline-flex items-center gap-2 rounded-full border border-[#d8e3eb] bg-[#eef6fb] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-[#00559b]">
                            <Icon className="h-3.5 w-3.5" />
                            {step}
                        </div>
                        <CardTitle className="mt-3 text-lg">{title}</CardTitle>
                    </div>
                </div>
            </CardHeader>
            <CardContent className="p-6">{children}</CardContent>
        </Card>
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

function Summary({ label, value }) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{label}</p>
            <p className="mt-2 text-sm font-semibold text-slate-900">{value}</p>
        </div>
    );
}
