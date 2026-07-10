import { useMemo, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import {
    Bell,
    Brush,
    FileImage,
    FileText,
    Globe,
    Home,
    Images,
    Layers3,
    LayoutGrid,
    Mail,
    Save,
    ShieldCheck,
    Sparkles,
    Upload,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';
import { Label } from '../../../components/ui/label';
import { Tabs, TabsContent } from '../../../components/ui/tabs';
import { cn } from '../../../lib/utils';

const inputClassName =
    'flex h-11 w-full rounded-xl border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white placeholder:text-[#8798a5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

const textareaClassName =
    'flex min-h-[120px] w-full rounded-xl border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white placeholder:text-[#8798a5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

const getValue = (object, key, fallback = '') => object?.[key] ?? fallback;

const defaultPreview = (path) => (path ? `/storage/${path}` : '');

// Ordre + métadonnées de la navigation latérale.
const NAV_ITEMS = [
    { value: 'agence', label: 'Agence', description: "Identité & coordonnées", icon: Home, step: '01' },
    { value: 'general', label: 'Général', description: 'Devise & préférences', icon: Globe, step: '02' },
    { value: 'facturation', label: 'Facturation', description: 'Cycle, taxes & commission', icon: FileText, step: '04' },
    { value: 'visuel', label: 'Visuel', description: 'Logos & cachet', icon: Images, step: '06' },
    { value: 'signatures', label: 'Signatures', description: 'DG, SG & comptabilité', icon: ShieldCheck, step: '08' },
    { value: 'notifications', label: 'Notifications', description: 'Alertes & destinataires', icon: Bell, step: '10' },
];

function Field({ label, required, children, className }) {
    return (
        <label className={cn('space-y-1.5', className)}>
            <span className="block text-sm font-medium text-[#0f172a]">
                {label}
                {required ? <span className="ml-0.5 text-[#b42318]">*</span> : null}
            </span>
            {children}
        </label>
    );
}

function SectionCard({ icon: Icon, title, description, step, children, action }) {
    return (
        <Card className="rounded-3xl border-[#c8d4de] bg-white shadow-sm">
            <CardHeader className="flex flex-row items-center justify-between gap-3 border-b border-[#e2e8f0] py-4">
                <div className="flex items-center gap-3">
                    <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eaf4fb] text-[#00559b]">
                        <Icon className="h-5 w-5" />
                    </span>
                    <div>
                        <CardTitle className="text-sm text-[#0f172a]">
                            {title}
                            {step ? <span className="ml-2 text-[11px] font-semibold uppercase tracking-wide text-[#94a3b8]">{step}</span> : null}
                        </CardTitle>
                        {description ? <CardDescription className="text-xs text-[#5f7182]">{description}</CardDescription> : null}
                    </div>
                </div>
                {action}
            </CardHeader>
            <CardContent className="p-6">{children}</CardContent>
        </Card>
    );
}

function ToggleRow({ label, description, name, checked, onToggle }) {
    return (
        <div className="flex items-center justify-between gap-4 rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
            <div className="min-w-0">
                <p className="text-sm font-medium text-[#0f172a]">{label}</p>
                <p className="text-xs text-[#5f7182]">{description}</p>
            </div>
            <input type="hidden" name={name} value={checked ? 1 : 0} />
            <button
                type="button"
                onClick={() => onToggle(!checked)}
                className={cn(
                    'relative h-7 w-12 rounded-full border transition',
                    checked ? 'border-[#76c206] bg-[#76c206]' : 'border-[#c8d4de] bg-white'
                )}
                aria-pressed={checked}
            >
                <span
                    className={cn(
                        'absolute top-0.5 h-6 w-6 rounded-full bg-white shadow-sm transition',
                        checked ? 'left-5' : 'left-0.5'
                    )}
                />
            </button>
        </div>
    );
}

function UploadBox({ label, help, name, preview, onChange, onClear, icon: Icon }) {
    const inputId = `file-${name}`;

    return (
        <div className="space-y-2">
            <div className="flex items-center justify-between">
                <Label htmlFor={inputId} className="text-sm font-medium text-[#0f172a]">
                    {label}
                </Label>
                {preview ? (
                    <button type="button" onClick={onClear} className="text-xs font-medium text-[#b42318] hover:underline">
                        Effacer
                    </button>
                ) : null}
            </div>

            <label
                htmlFor={inputId}
                className="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] px-4 py-6 text-center transition hover:border-[#00559b]"
            >
                {preview ? (
                    <img src={preview} alt={label} className="max-h-24 object-contain" />
                ) : (
                    <>
                        <span className="flex h-11 w-11 items-center justify-center rounded-full bg-[#eaf4fb] text-[#00559b]">
                            <Icon className="h-5 w-5" />
                        </span>
                        <div>
                            <p className="text-sm font-medium text-[#0f172a]">{label}</p>
                            <p className="text-xs text-[#5f7182]">{help}</p>
                        </div>
                    </>
                )}
            </label>

            <Input id={inputId} name={name} type="file" accept="image/*" className="hidden" onChange={onChange} />
        </div>
    );
}

export default function Index({ parametrage, agence, regions = [], villes = [], modePaiement = [] }) {
    const [tab, setTab] = useState('agence');
    const [visuals, setVisuals] = useState({
        logo: defaultPreview(getValue(parametrage, 'logo')),
        logo_tutelle: defaultPreview(getValue(parametrage, 'logo_tutelle')),
        logo_partenaire: defaultPreview(getValue(parametrage, 'logo_partenaire')),
        cachet: defaultPreview(getValue(parametrage, 'cachet')),
        signature_dg: defaultPreview(getValue(parametrage, 'signature_dg')),
        signature_sg: defaultPreview(getValue(parametrage, 'signature_sg')),
        signature_cpt: defaultPreview(getValue(parametrage, 'signature_cpt')),
    });

    const [generalFlags, setGeneralFlags] = useState({
        sauvegarde_auto: Boolean(parametrage?.sauvegarde_auto ?? true),
        double_validation: Boolean(parametrage?.double_validation ?? true),
        journal_activites: Boolean(parametrage?.journal_activites ?? true),
        multi_session: Boolean(parametrage?.multi_session ?? false),
    });

    const [signatureRules, setSignatureRules] = useState({
        sig_dg_facture: Boolean(parametrage?.sig_dg_facture ?? true),
        sig_double: Boolean(parametrage?.sig_double ?? true),
        cachet_auto: Boolean(parametrage?.cachet_auto ?? false),
    });

    const [notificationFlags, setNotificationFlags] = useState({
        notif_rappel: Boolean(parametrage?.notif_rappel ?? true),
        notif_retard: Boolean(parametrage?.notif_retard ?? true),
        notif_recu: Boolean(parametrage?.notif_recu ?? false),
    });

    const pills = useMemo(
        () => [
            { label: 'Région', value: agence?.region?.name ?? '—' },
            { label: 'Ville', value: agence?.ville?.name ?? '—' },
            { label: 'Devise', value: parametrage?.devise ?? 'XOF' },
            { label: 'Fuseau', value: parametrage?.timezone ?? 'Africa/Abidjan' },
        ],
        [agence, parametrage]
    );

    const currentRegionId = String(agence?.region_id ?? '');
    const availableCities = villes.filter((ville) => String(ville.region_id ?? '') === currentRegionId);

    const handlePreview = (name) => (event) => {
        const file = event.target.files?.[0];
        if (!file) return;

        const previewUrl = URL.createObjectURL(file);
        setVisuals((current) => ({ ...current, [name]: previewUrl }));
    };

    const activeItem = NAV_ITEMS.find((item) => item.value === tab) ?? NAV_ITEMS[0];

    return (
        <AgenceLayout title="Paramétrage">
            <Head title="Paramétrage" />

            <div className="mx-auto flex max-w-7xl flex-col gap-6 pb-10">
                {/* En-tête */}
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div className="min-w-0">
                        <div className="flex flex-wrap items-center gap-2">
                            <h2 className="text-2xl font-semibold text-[#0f172a]">Paramétrage</h2>
                          
                        </div>
                        <p className="mt-1 text-sm text-[#5f7182]">Configurez votre agence, la facturation, les visuels et les notifications.</p>
                    </div>

                  
                </div>

                {/* Disposition sidebar + contenu */}
                <div className="grid items-start gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
                    {/* Colonne latérale */}
                    <aside className="flex flex-col gap-4 lg:sticky lg:top-6">
                        <Card className="rounded-3xl border-[#c8d4de] bg-white shadow-sm">
                            <CardContent className="mt-4 p-3">
                                <p className="px-3 pb-2 pt-1 text-[11px] font-semibold uppercase tracking-wide text-[#94a3b8]">
                                    Sections
                                </p>
                                <nav className="flex gap-2 overflow-x-auto lg:flex-col lg:overflow-visible" aria-label="Navigation du paramétrage">
                                    {NAV_ITEMS.map((item) => {
                                        const ItemIcon = item.icon;
                                        const isActive = tab === item.value;

                                        return (
                                            <button
                                                key={item.value}
                                                type="button"
                                                onClick={() => setTab(item.value)}
                                                aria-current={isActive ? 'page' : undefined}
                                                className={cn(
                                                    'group flex min-w-[150px] items-center gap-3 rounded-2xl border px-3 py-2.5 text-left transition lg:min-w-0',
                                                    isActive
                                                        ? 'border-[#00559b] bg-[#eaf4fb]'
                                                        : 'border-transparent hover:border-[#e2e8f0] hover:bg-[#f8fafc]'
                                                )}
                                            >
                                                <span
                                                    className={cn(
                                                        'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition',
                                                        isActive ? 'bg-[#00559b] text-white' : 'bg-[#eaf4fb] text-[#00559b]'
                                                    )}
                                                >
                                                    <ItemIcon className="h-4 w-4" />
                                                </span>
                                                <span className="min-w-0">
                                                    <span className={cn('block text-sm font-medium', isActive ? 'text-[#00559b]' : 'text-[#0f172a]')}>
                                                        {item.label}
                                                    </span>
                                                    <span className="hidden truncate text-xs text-[#5f7182] lg:block">{item.description}</span>
                                                </span>
                                            </button>
                                        );
                                    })}
                                </nav>
                            </CardContent>
                        </Card>

                        {/* Résumé rapide */}
                        <Card className="hidden rounded-3xl border-[#c8d4de] bg-white shadow-sm lg:block">
                            <CardContent className="mt-4 grid grid-cols-2 gap-3 p-4">
                                {pills.map((pill) => (
                                    <div key={pill.label}>
                                        <span className="block text-xs uppercase tracking-wide text-[#94a3b8]">{pill.label}</span>
                                        <strong className="mt-1 block text-sm text-[#0f172a]">{pill.value}</strong>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>
                    </aside>

                    {/* Colonne de contenu */}
                    <div className="min-w-0">
                        {/* Fil d'ariane de section (mobile + desktop) */}
                        <div className="mb-4 flex items-center gap-2 text-sm text-[#5f7182]">
                            <span className="font-medium text-[#0f172a]">Paramétrage</span>
                            <span aria-hidden>/</span>
                            <span className="font-medium text-[#00559b]">{activeItem.label}</span>
                        </div>

                        <Tabs value={tab} onValueChange={setTab}>
                            <TabsContent value="agence">
                                <form action="/agence/parametrage/agence" method="POST" className="space-y-6">
                                    <input type="hidden" name="_method" value="PUT" />
                                    <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />

                                    <SectionCard icon={Home} title="Informations de l'agence" description="Identité officielle et coordonnées." step="01">
                                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                                            <Field label="Nom de l'agence" required className="md:col-span-2">
                                                <Input name="name" defaultValue={getValue(agence, 'name')} className={inputClassName} />
                                            </Field>
                                            <Field label="Sigle / Abréviation">
                                                <Input name="sigle" defaultValue={getValue(agence, 'sigle')} className={inputClassName} />
                                            </Field>
                                            <Field label="Numéro RCCM">
                                                <Input name="rccm" defaultValue={getValue(agence, 'rccm')} className={inputClassName} />
                                            </Field>
                                            <Field label="Numéro contribuable">
                                                <Input name="num_contribuable" defaultValue={getValue(agence, 'num_contribuable')} className={inputClassName} />
                                            </Field>
                                            <Field label="Régime fiscal">
                                                <select name="regime_fiscal" defaultValue={getValue(agence, 'regime_fiscal')} className={inputClassName}>
                                                    <option value="">Sélectionner</option>
                                                    <option value="SARL">SARL</option>
                                                    <option value="SAS">SAS</option>
                                                    <option value="SA">SA</option>
                                                </select>
                                            </Field>
                                            <Field label="Adresse" className="md:col-span-2">
                                                <textarea name="adresse" defaultValue={getValue(agence, 'adresse')} rows={3} className={textareaClassName} />
                                            </Field>
                                            <Field label="Téléphone 1">
                                                <Input name="tel1" type="tel" defaultValue={getValue(agence, 'tel1')} className={inputClassName} />
                                            </Field>
                                            <Field label="Téléphone 2">
                                                <Input name="tel2" type="tel" defaultValue={getValue(agence, 'tel2')} className={inputClassName} />
                                            </Field>
                                            <Field label="Email principal">
                                                <Input name="email1" type="email" defaultValue={getValue(agence, 'email1')} className={inputClassName} />
                                            </Field>
                                            <Field label="Email secondaire">
                                                <Input name="email2" type="email" defaultValue={getValue(agence, 'email2')} className={inputClassName} />
                                            </Field>
                                            <Field label="Région">
                                                <select name="region_id" defaultValue={getValue(agence, 'region_id')} className={inputClassName}>
                                                    <option value="">Sélectionner</option>
                                                    {regions.map((region) => (
                                                        <option key={region.id ?? region.region_id} value={region.id ?? region.region_id}>
                                                            {region.name}
                                                        </option>
                                                    ))}
                                                </select>
                                            </Field>
                                            <Field label="Ville">
                                                <select name="ville_id" defaultValue={getValue(agence, 'ville_id')} className={inputClassName}>
                                                    <option value="">Sélectionner</option>
                                                    {availableCities.map((ville) => (
                                                        <option key={ville.id ?? ville.ville_id} value={ville.id ?? ville.ville_id}>
                                                            {ville.name}
                                                        </option>
                                                    ))}
                                                </select>
                                            </Field>
                                            <Field label="Boîte postale">
                                                <Input name="bp" defaultValue={getValue(agence, 'bp')} className={inputClassName} />
                                            </Field>
                                            <Field label="Site web">
                                                <Input name="site_web" defaultValue={getValue(agence, 'site_web')} className={inputClassName} />
                                            </Field>
                                            <Field label="Banque domiciliataire">
                                                <Input name="banque" defaultValue={getValue(agence, 'banque')} className={inputClassName} />
                                            </Field>
                                            <Field label="Agence bancaire">
                                                <Input name="agence_bancaire" defaultValue={getValue(agence, 'agence_bancaire')} className={inputClassName} />
                                            </Field>
                                            <Field label="Numéro de compte (IBAN / RIB)" className="md:col-span-2">
                                                <Input name="rib" defaultValue={getValue(agence, 'rib')} className={inputClassName} />
                                            </Field>
                                        </div>

                                        <div className="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                                            <Button type="button" variant="outline" className="rounded-xl border-[#c8d4de]" onClick={() => window.location.reload()}>
                                                Annuler
                                            </Button>
                                            <Button type="submit" className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]">
                                                <Save className="h-4 w-4" />
                                                Enregistrer
                                            </Button>
                                        </div>
                                    </SectionCard>
                                </form>
                            </TabsContent>

                            <TabsContent value="general">
                                <form action="/agence/parametrage/general" method="POST" className="space-y-6">
                                    <input type="hidden" name="_method" value="PUT" />
                                    <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />

                                    <SectionCard icon={Globe} title="Devise & localisation" description="Paramètres globaux de l'interface." step="02">
                                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                                            <Field label="Devise par défaut">
                                                <select name="devise" defaultValue={getValue(parametrage, 'devise', 'XOF')} className={inputClassName}>
                                                    <option value="XOF">XOF - Franc CFA (BCEAO)</option>
                                                    <option value="EUR">EUR - Euro</option>
                                                    <option value="USD">USD - Dollar américain</option>
                                                </select>
                                            </Field>
                                            <Field label="Langue de l'interface">
                                                <select name="langue" defaultValue={getValue(parametrage, 'langue', 'fr')} className={inputClassName}>
                                                    <option value="fr">Français</option>
                                                    <option value="en">Anglais</option>
                                                </select>
                                            </Field>
                                            <Field label="Format de date">
                                                <select name="format_date" defaultValue={getValue(parametrage, 'format_date', 'd/m/Y')} className={inputClassName}>
                                                    <option value="d/m/Y">JJ/MM/AAAA</option>
                                                    <option value="m/d/Y">MM/JJ/AAAA</option>
                                                    <option value="Y-m-d">AAAA-MM-JJ</option>
                                                </select>
                                            </Field>
                                            <Field label="Fuseau horaire">
                                                <select name="timezone" defaultValue={getValue(parametrage, 'timezone', 'Africa/Abidjan')} className={inputClassName}>
                                                    <option value="Africa/Abidjan">Africa/Abidjan (GMT+0)</option>
                                                    <option value="Europe/Paris">Europe/Paris (GMT+1/+2)</option>
                                                </select>
                                            </Field>
                                        </div>
                                    </SectionCard>

                                    <SectionCard icon={Layers3} title="Préférences système" description="Automatisations et protections." step="03">
                                        <div className="mt-4 space-y-3">
                                            <ToggleRow
                                                label="Sauvegarde automatique"
                                                description="Enregistrer les modifications toutes les 5 minutes"
                                                name="sauvegarde_auto"
                                                checked={generalFlags.sauvegarde_auto}
                                                onToggle={(value) => setGeneralFlags((current) => ({ ...current, sauvegarde_auto: value }))}
                                            />
                                            <ToggleRow
                                                label="Mode double validation"
                                                description="Exiger une confirmation avant toute suppression"
                                                name="double_validation"
                                                checked={generalFlags.double_validation}
                                                onToggle={(value) => setGeneralFlags((current) => ({ ...current, double_validation: value }))}
                                            />
                                            <ToggleRow
                                                label="Journal d'activités"
                                                description="Enregistrer toutes les actions des utilisateurs"
                                                name="journal_activites"
                                                checked={generalFlags.journal_activites}
                                                onToggle={(value) => setGeneralFlags((current) => ({ ...current, journal_activites: value }))}
                                            />
                                            <ToggleRow
                                                label="Accès multi-session"
                                                description="Autoriser la connexion simultanée sur plusieurs appareils"
                                                name="multi_session"
                                                checked={generalFlags.multi_session}
                                                onToggle={(value) => setGeneralFlags((current) => ({ ...current, multi_session: value }))}
                                            />
                                        </div>

                                        <div className="mt-6 flex justify-end gap-3">
                                            <Button type="button" variant="outline" className="rounded-xl border-[#c8d4de]" onClick={() => window.location.reload()}>
                                                Annuler
                                            </Button>
                                            <Button type="submit" className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]">
                                                <Save className="h-4 w-4" />
                                                Enregistrer
                                            </Button>
                                        </div>
                                    </SectionCard>
                                </form>
                            </TabsContent>

                            <TabsContent value="facturation">
                                <form action="/agence/parametrage/facturation" method="POST" className="space-y-6">
                                    <input type="hidden" name="_method" value="PUT" />
                                    <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />

                                    <SectionCard icon={FileText} title="Cycle de facturation" description="Périodicité et délais." step="04">
                                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                                            <Field label="Période de facturation">
                                                <select name="periode_facturation" defaultValue={getValue(parametrage, 'periode_facturation', 'mensuelle')} className={inputClassName}>
                                                    <option value="mensuelle">Mensuelle</option>
                                                    <option value="trimestrielle">Trimestrielle</option>
                                                    <option value="semestrielle">Semestrielle</option>
                                                    <option value="annuelle">Annuelle</option>
                                                    <option value="commande">À la commande</option>
                                                </select>
                                            </Field>
                                            <Field label="Jour d'émission">
                                                <select name="jour_emission" defaultValue={getValue(parametrage, 'jour_emission', '1')} className={inputClassName}>
                                                    <option value="1">1er du mois</option>
                                                    <option value="5">5 du mois</option>
                                                    <option value="15">15 du mois</option>
                                                    <option value="last">Dernier jour du mois</option>
                                                </select>
                                            </Field>
                                            <Field label="Délai limite de paiement (jours)">
                                                <Input type="number" name="delai_paiement" defaultValue={getValue(parametrage, 'delai_paiement', 30)} className={inputClassName} />
                                            </Field>
                                            <Field label="Pénalité de retard (%/mois)">
                                                <Input type="number" step="0.1" name="penalite_retard" defaultValue={getValue(parametrage, 'penalite_retard', 1.5)} className={inputClassName} />
                                            </Field>
                                            <Field label="Préfixe numéro de facture">
                                                <Input name="prefixe_facture" defaultValue={getValue(parametrage, 'prefixe_facture', 'FAC-')} className={inputClassName} />
                                            </Field>
                                            <Field label="Prochain numéro de séquence">
                                                <Input type="number" min="1" name="sequence_facture" defaultValue={getValue(parametrage, 'sequence_facture', 1)} className={inputClassName} />
                                            </Field>
                                        </div>
                                    </SectionCard>

                                    <SectionCard icon={Brush} title="Commission & taxes" description="Calculs financiers de l'agence." step="05">
                                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                                            <Field label="Commission agence (%)">
                                                <Input type="number" step="0.5" min="0" max="100" name="commission" defaultValue={getValue(parametrage, 'commission', 15)} className={inputClassName} />
                                            </Field>
                                            <Field label="Base de calcul">
                                                <select name="base_commission" defaultValue={getValue(parametrage, 'base_commission', 'ht')} className={inputClassName}>
                                                    <option value="ht">Sur le montant HT</option>
                                                    <option value="ttc">Sur le montant TTC</option>
                                                    <option value="brut">Sur le budget brut</option>
                                                </select>
                                            </Field>
                                            <Field label="TVA (%)">
                                                <Input type="number" step="0.5" min="0" name="tva" defaultValue={getValue(parametrage, 'tva', 18)} className={inputClassName} />
                                            </Field>
                                            <Field label="AIB (%)">
                                                <Input type="number" step="0.5" min="0" name="aib" defaultValue={getValue(parametrage, 'aib', 5)} className={inputClassName} />
                                            </Field>
                                            <Field label="RAS (%)">
                                                <Input type="number" step="0.5" min="0" name="ras" defaultValue={getValue(parametrage, 'ras', 2)} className={inputClassName} />
                                            </Field>
                                            <Field label="Acompte minimum exigé (%)">
                                                <Input type="number" min="0" max="100" name="acompte_min" defaultValue={getValue(parametrage, 'acompte_min', 30)} className={inputClassName} />
                                            </Field>
                                            <Field label="Mode de règlement par défaut" className="md:col-span-2">
                                                <select name="mode_reglement_id" defaultValue={getValue(parametrage, 'mode_reglement_id', 1)} className={inputClassName}>
                                                    {modePaiement.map((mode) => (
                                                        <option key={mode.id ?? mode.mode_paiement_id} value={mode.id ?? mode.mode_paiement_id}>
                                                            {mode.name}
                                                        </option>
                                                    ))}
                                                </select>
                                            </Field>
                                        </div>
                                    </SectionCard>

                                    <div className="flex justify-end gap-3">
                                        <Button type="button" variant="outline" className="rounded-xl border-[#c8d4de]" onClick={() => window.location.reload()}>
                                            Annuler
                                        </Button>
                                        <Button type="submit" className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]">
                                            <Save className="h-4 w-4" />
                                            Enregistrer
                                        </Button>
                                    </div>
                                </form>
                            </TabsContent>

                            <TabsContent value="visuel">
                                <form action="/agence/parametrage/logos" method="POST" encType="multipart/form-data" className="space-y-6">
                                    <input type="hidden" name="_method" value="PUT" />
                                    <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />

                                    <SectionCard icon={Images} title="Logo principal" description="Image de marque et aperçu rapide." step="06">
                                        <div className="mt-4 grid gap-6 xl:grid-cols-[1fr_1fr]">
                                            <UploadBox
                                                label="Logo principal"
                                                help="PNG, SVG - max 2 Mo"
                                                name="logo"
                                                preview={visuals.logo}
                                                onChange={handlePreview('logo')}
                                                onClear={() => setVisuals((current) => ({ ...current, logo: '' }))}
                                                icon={Upload}
                                            />
                                            <div className="space-y-4">
                                                <Field label="Largeur sur facture (px)">
                                                    <Input type="number" name="logo_largeur" defaultValue={getValue(parametrage, 'logo_largeur', 200)} className={inputClassName} />
                                                </Field>
                                                <Field label="Position sur facture">
                                                    <select name="logo_position" defaultValue={getValue(parametrage, 'logo_position', 'gauche')} className={inputClassName}>
                                                        <option value="gauche">En-tête gauche</option>
                                                        <option value="centre">En-tête centré</option>
                                                        <option value="droit">En-tête droit</option>
                                                    </select>
                                                </Field>
                                            </div>
                                        </div>
                                    </SectionCard>

                                    <SectionCard icon={FileImage} title="Logos secondaires" description="Tutelle, partenaire, cachet." step="07">
                                        <div className="mt-4 grid gap-4 md:grid-cols-3">
                                            <UploadBox
                                                label="Logo tutelle"
                                                help="PNG, SVG"
                                                name="logo_tutelle"
                                                preview={visuals.logo_tutelle}
                                                onChange={handlePreview('logo_tutelle')}
                                                onClear={() => setVisuals((current) => ({ ...current, logo_tutelle: '' }))}
                                                icon={FileImage}
                                            />
                                            <UploadBox
                                                label="Logo partenaire"
                                                help="PNG, SVG"
                                                name="logo_partenaire"
                                                preview={visuals.logo_partenaire}
                                                onChange={handlePreview('logo_partenaire')}
                                                onClear={() => setVisuals((current) => ({ ...current, logo_partenaire: '' }))}
                                                icon={FileImage}
                                            />
                                            <UploadBox
                                                label="Cachet / Tampon"
                                                help="PNG, SVG"
                                                name="cachet"
                                                preview={visuals.cachet}
                                                onChange={handlePreview('cachet')}
                                                onClear={() => setVisuals((current) => ({ ...current, cachet: '' }))}
                                                icon={FileImage}
                                            />
                                        </div>
                                    </SectionCard>

                                    <div className="flex justify-end gap-3">
                                        <Button type="button" variant="outline" className="rounded-xl border-[#c8d4de]" onClick={() => window.location.reload()}>
                                            Annuler
                                        </Button>
                                        <Button type="submit" className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]">
                                            <Save className="h-4 w-4" />
                                            Enregistrer
                                        </Button>
                                    </div>
                                </form>
                            </TabsContent>

                            <TabsContent value="signatures">
                                <form action="/agence/parametrage/signatures" method="POST" encType="multipart/form-data" className="space-y-6">
                                    <input type="hidden" name="_method" value="PUT" />
                                    <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />

                                    <SectionCard icon={ShieldCheck} title="Signatures officielles" description="DG, secrétariat, comptabilité." step="08">
                                        <div className="mt-4 grid gap-6 xl:grid-cols-3">
                                    {[
                                        { key: 'signature_dg', title: 'Directeur Général (DG)', nom: 'dg_nom', titre: 'dg_titre', defaultTitle: 'Directeur Général' },
                                        { key: 'signature_sg', title: 'Secrétariat Général', nom: 'sg_nom', titre: 'sg_titre', defaultTitle: 'Secrétaire Général(e)' },
                                        { key: 'signature_cpt', title: 'Comptabilité', nom: 'cpt_nom', titre: 'cpt_titre', defaultTitle: 'Responsable Comptable' },
                                    ].map((sig) => (
                                                <div key={sig.key} className="rounded-3xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                                    <div className="mb-3 text-sm font-medium text-[#0f172a]">{sig.title}</div>
                                                    <UploadBox
                                                        label={sig.title}
                                                        help="PNG avec fond transparent recommandé"
                                                        name={sig.key}
                                                        preview={visuals[sig.key]}
                                                        onChange={handlePreview(sig.key)}
                                                        onClear={() => setVisuals((current) => ({ ...current, [sig.key]: '' }))}
                                                        icon={Upload}
                                                    />
                                                    <div className="mt-4 grid gap-4">
                                                        <Field label="Nom complet">
                                                            <Input name={sig.nom} defaultValue={getValue(parametrage, sig.nom)} className={inputClassName} />
                                                        </Field>
                                                        <Field label="Titre">
                                                            <Input name={sig.titre} defaultValue={getValue(parametrage, sig.titre, sig.defaultTitle)} className={inputClassName} />
                                                        </Field>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </SectionCard>

                                    <SectionCard icon={LayoutGrid} title="Règles d'apposition" description="Automatisation des documents." step="09">
                                        <div className="mt-4 space-y-3">
                                            <ToggleRow
                                                label="Signature DG sur toutes les factures"
                                                description="Apposer automatiquement sur chaque facture émise"
                                                name="sig_dg_facture"
                                                checked={signatureRules.sig_dg_facture}
                                                onToggle={(value) => setSignatureRules((current) => ({ ...current, sig_dg_facture: value }))}
                                            />
                                            <ToggleRow
                                                label="Double signature (DG + Comptabilité)"
                                                description="Exiger deux signatures pour les montants supérieurs au seuil"
                                                name="sig_double"
                                                checked={signatureRules.sig_double}
                                                onToggle={(value) => setSignatureRules((current) => ({ ...current, sig_double: value }))}
                                            />
                                            <ToggleRow
                                                label="Cachet automatique"
                                                description="Apposer le cachet de l'agence sur chaque document"
                                                name="cachet_auto"
                                                checked={signatureRules.cachet_auto}
                                                onToggle={(value) => setSignatureRules((current) => ({ ...current, cachet_auto: value }))}
                                            />
                                        </div>
                                    </SectionCard>

                                    <div className="flex justify-end gap-3">
                                        <Button type="button" variant="outline" className="rounded-xl border-[#c8d4de]" onClick={() => window.location.reload()}>
                                            Annuler
                                        </Button>
                                        <Button type="submit" className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]">
                                            <Save className="h-4 w-4" />
                                            Enregistrer
                                        </Button>
                                    </div>
                                </form>
                            </TabsContent>

                            <TabsContent value="notifications">
                                <form action="/agence/parametrage/notifications" method="POST" className="space-y-6">
                                    <input type="hidden" name="_method" value="PUT" />
                                    <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />

                                    <SectionCard icon={Bell} title="Alertes de facturation" description="Notifications automatiques." step="10">
                                        <div className="mt-4 space-y-3">
                                            <ToggleRow
                                                label="Rappel avant échéance"
                                                description="Envoyer un email X jours avant la date limite de paiement"
                                                name="notif_rappel"
                                                checked={notificationFlags.notif_rappel}
                                                onToggle={(value) => setNotificationFlags((current) => ({ ...current, notif_rappel: value }))}
                                            />
                                            <ToggleRow
                                                label="Alerte de retard de paiement"
                                                description="Notifier le service comptable dès le dépassement de l'échéance"
                                                name="notif_retard"
                                                checked={notificationFlags.notif_retard}
                                                onToggle={(value) => setNotificationFlags((current) => ({ ...current, notif_retard: value }))}
                                            />
                                            <ToggleRow
                                                label="Confirmation de réception de paiement"
                                                description="Envoyer un reçu automatique au client après enregistrement"
                                                name="notif_recu"
                                                checked={notificationFlags.notif_recu}
                                                onToggle={(value) => setNotificationFlags((current) => ({ ...current, notif_recu: value }))}
                                            />
                                        </div>
                                    </SectionCard>

                                    <SectionCard icon={Mail} title="Destinataires par défaut" description="Diffusion des alertes." step="11">
                                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                                            <Field label="Email comptabilité" className="md:col-span-2">
                                                <Input
                                                    type="email"
                                                    name="email_compta"
                                                    defaultValue={getValue(parametrage, 'email_compta')}
                                                    placeholder="comptabilite@agence.ci"
                                                    className={inputClassName}
                                                />
                                            </Field>
                                            <Field label="Email DG" className="md:col-span-2">
                                                <Input
                                                    type="email"
                                                    name="email_dg"
                                                    defaultValue={getValue(parametrage, 'email_dg')}
                                                    placeholder="dg@agence.ci"
                                                    className={inputClassName}
                                                />
                                            </Field>
                                            <Field label="Délai de rappel (jours avant échéance)">
                                                <Input type="number" min="1" name="delai_rappel" defaultValue={getValue(parametrage, 'delai_rappel', 7)} className={inputClassName} />
                                            </Field>
                                            <Field label="Seuil pour copie DG (XOF)">
                                                <Input type="number" step="50000" name="seuil_dg" defaultValue={getValue(parametrage, 'seuil_dg', 1000000)} className={inputClassName} />
                                            </Field>
                                        </div>
                                    </SectionCard>

                                    <div className="flex justify-end gap-3">
                                        <Button type="button" variant="outline" className="rounded-xl border-[#c8d4de]" onClick={() => window.location.reload()}>
                                            Annuler
                                        </Button>
                                        <Button type="submit" className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]">
                                            <Save className="h-4 w-4" />
                                            Enregistrer
                                        </Button>
                                    </div>
                                </form>
                            </TabsContent>
                        </Tabs>
                    </div>
                </div>
            </div>
        </AgenceLayout>
    );
}
