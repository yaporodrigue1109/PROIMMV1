import { useEffect, useRef, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import {
    Bell,
    Brush,
    Check,
    ChevronLeft,
    ChevronDown,
    ChevronRight,
    FileImage,
    FileText,
    Globe,
    Home,
    Images,
    Layers3,
    LayoutGrid,
    KeyRound,
    LockKeyhole,
    Mail,
    Pencil,
    Save,
    ShieldCheck,
    Sparkles,
    Upload,
    UsersRound,
    Search,
    Trash2,
    TriangleAlert,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import CreateRolePage from './Roles/Create';
import RolePermissionsPage from './Roles/Permissions';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '../../../components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
import { Switch } from '../../../components/ui/switch';
import { Input } from '../../../components/ui/input';
import { Label } from '../../../components/ui/label';
import { Tabs, TabsContent } from '../../../components/ui/tabs';
import { cn } from '../../../lib/utils';
import flags from 'react-phone-number-input/flags';
import PhoneInputBase from 'react-phone-number-input';
import 'react-phone-number-input/style.css';

const inputClassName =
    'flex h-10 w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white placeholder:text-[#8798a5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

const textareaClassName =
    'flex min-h-[120px] w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white placeholder:text-[#8798a5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

const getValue = (object, key, fallback = '') => object?.[key] ?? fallback;

const defaultPreview = (path) => (path ? `/storage/${path}` : '');

const scrollToTop = () => {
    requestAnimationFrame(() => {
        const container = document.getElementById(
            'agence-scroll-container'
        );

        container?.scrollTo({
            top: 0,
            behavior: 'auto',
        });
    });
};

// Ordre + métadonnées de la navigation latérale.
const NAV_ITEMS = [
    { value: 'agence', label: 'Agence', description: "Identité & coordonnées", icon: Home, step: '01' },
    { value: 'general', label: 'Général', description: 'Devise & préférences', icon: Globe, step: '02' },
    { value: 'facturation', label: 'Facturation', description: 'Cycle, taxes & commission', icon: FileText, step: '04' },
    { value: 'visuel', label: 'Visuel', description: 'Logos & cachet', icon: Images, step: '06' },
    { value: 'signatures', label: 'Signatures', description: 'DG, SG & comptabilité', icon: ShieldCheck, step: '08' },
    { value: 'notifications', label: 'Notifications', description: 'Alertes & destinataires', icon: Bell, step: '10' },
    { value: 'roles', label: 'Rôles et permissions', description: 'Accès du personnel', icon: KeyRound, step: '12' },
];

const STATIC_ROLES = [
    {
        role_id: 'role-responsable',
        name: 'Responsable',
        description: "Administration complète de l'agence, des équipes et des paramètres.",
    },
    {
        role_id: 'role-agent',
        name: 'Agent',
        description: 'Gestion opérationnelle des biens, propriétaires, locataires et demandes courantes.',
    },
    {
        role_id: 'role-comptable',
        name: 'Comptable',
        description: 'Suivi des loyers, dépenses, ventes, reversements et indicateurs financiers.',
    },
    {
        role_id: 'role-technicien',
        name: 'Technicien',
        description: 'Prise en charge des maintenances, interventions et tâches techniques.',
    },
];

const ALL_ROLES = STATIC_ROLES.map((role) => role.role_id);
const MANAGEMENT_ROLES = ['role-responsable', 'role-agent'];
const FINANCE_ROLES = ['role-responsable', 'role-comptable'];
const MAINTENANCE_ROLES = ['role-responsable', 'role-agent', 'role-technicien'];

const PERMISSION_GROUPS = [
    {
        label: 'Tableau de bord',
        permissions: [
            { key: 'dashboard.view', label: "Consulter le tableau de bord de l'agence", roles: ALL_ROLES },
        ],
    },
    {
        label: 'Propriétés',
        permissions: [
            { key: 'properties.view', label: 'Consulter les propriétés et leurs détails', roles: ALL_ROLES },
            { key: 'properties.create', label: 'Ajouter une propriété', roles: MANAGEMENT_ROLES },
            { key: 'properties.update', label: 'Modifier une propriété', roles: MANAGEMENT_ROLES },
            { key: 'properties.occupancy', label: "Changer l'état libre ou occupé d'une porte", roles: MANAGEMENT_ROLES },
            { key: 'properties.delete', label: 'Supprimer une propriété', roles: ['role-responsable'] },
            { key: 'properties.catalogs', label: 'Gérer les types, équipements et proximités', roles: ['role-responsable'] },
        ],
    },
    {
        label: 'Propriétaires et lots',
        permissions: [
            { key: 'owners.view', label: 'Consulter les propriétaires et leurs lots', roles: ['role-responsable', 'role-agent', 'role-comptable'] },
            { key: 'owners.create', label: 'Ajouter un propriétaire', roles: MANAGEMENT_ROLES },
            { key: 'owners.update', label: 'Modifier ou activer un propriétaire', roles: MANAGEMENT_ROLES },
            { key: 'owners.lots', label: 'Ajouter et modifier les lots confiés', roles: MANAGEMENT_ROLES },
            { key: 'owners.delete', label: 'Supprimer un propriétaire ou un lot', roles: ['role-responsable'] },
        ],
    },
    {
        label: 'Locataires et baux',
        permissions: [
            { key: 'tenants.view', label: 'Consulter les locataires, contrats et documents', roles: ['role-responsable', 'role-agent', 'role-comptable'] },
            { key: 'tenants.create', label: 'Enregistrer un locataire et son bail', roles: MANAGEMENT_ROLES },
            { key: 'tenants.update', label: 'Modifier un locataire ou son bail', roles: MANAGEMENT_ROLES },
            { key: 'tenants.terminate', label: 'Résilier un bail', roles: ['role-responsable'] },
            { key: 'tenants.delete', label: 'Supprimer un dossier locataire', roles: ['role-responsable'] },
        ],
    },
    {
        label: 'Personnel',
        permissions: [
            { key: 'staff.view', label: 'Consulter le personnel', roles: ['role-responsable'] },
            { key: 'staff.create', label: 'Créer un compte personnel', roles: ['role-responsable'] },
            { key: 'staff.update', label: 'Modifier, activer ou suspendre un compte', roles: ['role-responsable'] },
            { key: 'staff.delete', label: 'Supprimer un membre du personnel', roles: ['role-responsable'] },
            { key: 'staff.permissions', label: 'Consulter les rôles et permissions', roles: ['role-responsable'] },
        ],
    },
    {
        label: 'Maintenance',
        permissions: [
            { key: 'maintenance.view', label: 'Consulter les demandes et interventions', roles: MAINTENANCE_ROLES },
            { key: 'maintenance.create', label: 'Créer une demande ou une intervention', roles: MAINTENANCE_ROLES },
            { key: 'maintenance.update', label: 'Modifier une intervention et ses tâches', roles: MAINTENANCE_ROLES },
            { key: 'maintenance.status', label: "Mettre à jour le statut d'une intervention", roles: MAINTENANCE_ROLES },
            { key: 'maintenance.catalogs', label: 'Gérer maintenanciers, fonctions et types', roles: ['role-responsable'] },
            { key: 'maintenance.delete', label: 'Supprimer les référentiels de maintenance', roles: ['role-responsable'] },
        ],
    },
    {
        label: 'Caisse et loyers',
        permissions: [
            { key: 'cash.view', label: 'Consulter le tableau de caisse', roles: FINANCE_ROLES },
            { key: 'rent.view', label: 'Consulter les loyers et échéances', roles: ['role-responsable', 'role-agent', 'role-comptable'] },
            { key: 'rent.pay', label: 'Enregistrer un paiement de loyer', roles: FINANCE_ROLES },
            { key: 'cash.maintenance', label: 'Enregistrer les opérations de maintenance', roles: FINANCE_ROLES },
            { key: 'cash.expenses', label: "Gérer les dépenses de l'agence", roles: FINANCE_ROLES },
            { key: 'cash.sales', label: 'Enregistrer les ventes de biens', roles: FINANCE_ROLES },
        ],
    },
    {
        label: 'Reversements propriétaires',
        permissions: [
            { key: 'payouts.view', label: 'Consulter les reversements et leur historique', roles: FINANCE_ROLES },
            { key: 'payouts.create', label: 'Préparer un reversement', roles: FINANCE_ROLES },
            { key: 'payouts.update', label: 'Modifier les montants et paiements', roles: FINANCE_ROLES },
            { key: 'payouts.validate', label: 'Valider ou marquer un reversement effectué', roles: ['role-responsable'] },
            { key: 'payouts.cancel', label: 'Annuler ou supprimer un reversement', roles: ['role-responsable'] },
            { key: 'payouts.export', label: 'Télécharger les fiches et historiques PDF', roles: FINANCE_ROLES },
        ],
    },
    {
        label: 'Statistiques',
        permissions: [
            { key: 'statistics.view', label: 'Consulter les indicateurs et rapports', roles: ['role-responsable', 'role-agent', 'role-comptable'] },
            { key: 'statistics.financial', label: 'Consulter les indicateurs financiers détaillés', roles: FINANCE_ROLES },
        ],
    },
    {
        label: 'Support',
        permissions: [
            { key: 'support.view', label: 'Consulter les tickets de support', roles: ALL_ROLES },
            { key: 'support.create', label: 'Créer une demande de support', roles: ALL_ROLES },
            { key: 'support.reply', label: 'Répondre à un ticket', roles: ALL_ROLES },
        ],
    },
    {
        label: 'Paramétrage',
        permissions: [
            { key: 'settings.view', label: "Consulter les paramètres de l'agence", roles: ['role-responsable'] },
            { key: 'settings.agency', label: "Modifier l'identité et les préférences générales", roles: ['role-responsable'] },
            { key: 'settings.billing', label: 'Modifier la facturation, les taxes et commissions', roles: ['role-responsable'] },
            { key: 'settings.branding', label: 'Modifier les logos, cachets et signatures', roles: ['role-responsable'] },
            { key: 'settings.notifications', label: 'Modifier les notifications et destinataires', roles: ['role-responsable'] },
        ],
    },
    {
        label: 'Compte et abonnement',
        permissions: [
            { key: 'profile.view', label: 'Consulter son profil', roles: ALL_ROLES },
            { key: 'subscription.view', label: "Consulter l'abonnement et les reçus", roles: ['role-responsable'] },
            { key: 'subscription.manage', label: "Souscrire ou renouveler l'abonnement", roles: ['role-responsable'] },
        ],
    },
];

const permissionsForRole = (roleId) =>
    PERMISSION_GROUPS.flatMap((group) =>
        group.permissions
            .filter((permission) => permission.roles.includes(roleId))
            .map((permission) => permission.key)
    );

const INITIAL_ROLE_PERMISSIONS = Object.fromEntries(
    STATIC_ROLES.map((role) => [role.role_id, permissionsForRole(role.role_id)])
);



const SENSITIVE_PERMISSION_KEYS = new Set([
    'properties.delete',
    'properties.catalogs',
    'owners.delete',
    'tenants.terminate',
    'tenants.delete',
    'staff.create',
    'staff.update',
    'staff.delete',
    'staff.permissions',
    'maintenance.delete',
    'payouts.validate',
    'payouts.cancel',
    'settings.agency',
    'settings.billing',
    'settings.branding',
    'settings.notifications',
    'subscription.manage',
]);

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

function PhoneInput({ name, defaultValue = '', placeholder = '', disabled = false }) {
    const [value, setValue] = useState(defaultValue ?? '');

    return (
        <>
            <input type="hidden" name={name} value={value ?? ''} />
            <PhoneInputBase
                international
                defaultCountry="CI"
                countrySelectComponent={CountrySelect}
                value={value}
                onChange={setValue}
                disabled={disabled}
                placeholder={placeholder}
                className={cn(
                    'phone-input-custom flex h-10 items-stretch rounded-md border border-[#c8d4de] bg-white shadow-sm transition-colors',
                    'focus-within:border-[#00559b] focus-within:ring-2 focus-within:ring-[#00559b]/20'
                )}
            />
        </>
    );
}

function FormSelect({ name, defaultValue = '', placeholder = 'Sélectionner', children, disabled = false }) {
    const [value, setValue] = useState(defaultValue ?? '');

    return (
        <>
            <input type="hidden" name={name} value={value ?? ''} />
            <Select value={value ?? ''} onValueChange={setValue} disabled={disabled}>
                <SelectTrigger disabled={disabled} className={inputClassName}>
                    <SelectValue placeholder={placeholder} />
                </SelectTrigger>
                <SelectContent>{children}</SelectContent>
            </Select>
        </>
    );
}

function SectionCard({ icon: Icon, title, description, step, children, action }) {
    return (
        <Card className="rounded-3xl border-[#c8d4de] bg-white shadow-sm">
            <CardHeader className="flex flex-col items-start justify-between gap-3 border-b border-[#e2e8f0] py-4 sm:flex-row sm:items-center">
                <div className="flex min-w-0 items-center gap-3">
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
            <CardContent className="p-4 sm:p-6">{children}</CardContent>
        </Card>
    );
}

function ToggleRow({ label, description, name, checked, onToggle }) {
    return (
        <div className="flex flex-col items-start justify-between gap-4 rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4 sm:flex-row sm:items-center">
            <div className="min-w-0">
                <p className="text-sm font-medium text-[#0f172a]">{label}</p>
                <p className="text-xs text-[#5f7182]">{description}</p>
            </div>
            <input type="hidden" name={name} value={checked ? 1 : 0} />
            <Switch checked={checked} onCheckedChange={onToggle} />
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

export default function Index({
    parametrage,
    agence,
    regions = [],
    villes = [],
    modePaiement = [],
    agencyRoles = null,
    permissionGroups = null,
    rolePermissions: initialRolePermissions = null,
}) {
    const permissionGroupsCatalog = Array.isArray(permissionGroups) ? permissionGroups : PERMISSION_GROUPS;
    const initialRoles = Array.isArray(agencyRoles) ? agencyRoles : STATIC_ROLES;
    const initialPermissions = initialRolePermissions && typeof initialRolePermissions === 'object'
        ? initialRolePermissions
        : INITIAL_ROLE_PERMISSIONS;
    const allPermissionKeys = permissionGroupsCatalog.flatMap((group) =>
        group.permissions.map((permission) => permission.key)
    );
    const sensitivePermissionKeys = Array.isArray(permissionGroups)
        ? new Set(
            permissionGroupsCatalog.flatMap((group) =>
                group.permissions
                    .filter((permission) => permission.is_critical)
                    .map((permission) => permission.key)
            )
        )
        : SENSITIVE_PERMISSION_KEYS;
    const initialRolePage = typeof window !== 'undefined'
        ? new URLSearchParams(window.location.search).get('role-page')
        : null;
    const requestedTab = typeof window !== 'undefined'
        ? new URLSearchParams(window.location.search).get('tab')
        : null;
    const initialTab = initialRolePage
        ? 'permission-form'
        : NAV_ITEMS.some((item) => item.value === requestedTab)
            ? requestedTab
            : 'agence';
    const [rolePermissions, setRolePermissions] = useState(initialPermissions);
    const [roleCatalog, setRoleCatalog] = useState(initialRoles);
    const [selectedPermissionRole, setSelectedPermissionRole] = useState('');
    const [permissionDraft, setPermissionDraft] = useState([]);
    const [permissionSearch, setPermissionSearch] = useState('');
    const [roleFormMode, setRoleFormMode] = useState(initialRolePage === 'create' ? 'create' : 'edit');
    const [newRole, setNewRole] = useState({ name: '', description: '' });
    const [roleTemplate, setRoleTemplate] = useState('empty');
    const [roleFormErrors, setRoleFormErrors] = useState({});
    const [responsableConfirmOpen, setResponsableConfirmOpen] = useState(false);
    const responsableConfirmationAccepted = useRef(false);
    const roles = roleCatalog.map((role) => ({
        ...role,
        permissions: rolePermissions[role.role_id] ?? [],
    }));
    const [tab, setTab] = useState(initialTab);
    const [matrixRoleIds, setMatrixRoleIds] = useState(initialRoles.slice(0, 5).map((role) => role.role_id));
    const [matrixPermissionSearch, setMatrixPermissionSearch] = useState('');
    const [matrixFilter, setMatrixFilter] = useState('all');
    const [collapsedPermissionGroups, setCollapsedPermissionGroups] = useState([]);
    const isRoleEditorPage = tab === 'permission-form';

    const updateRolePageUrl = (page = null) => {
        const url = new URL(window.location.href);

        if (page) {
            url.searchParams.set('role-page', page);
        } else {
            url.searchParams.delete('role-page');
        }

        window.history.pushState({}, '', `${url.pathname}${url.search}${url.hash}`);
    };

    useEffect(() => {
        const syncRolePageWithUrl = () => {
            const rolePage = new URLSearchParams(window.location.search).get('role-page');

            if (rolePage === 'create' || rolePage === 'permissions') {
                setRoleFormMode(rolePage === 'create' ? 'create' : 'edit');
                setTab('permission-form');
            } else {
                setTab((current) => current === 'permission-form' ? 'roles' : current);
            }
        };

        window.addEventListener('popstate', syncRolePageWithUrl);
        return () => window.removeEventListener('popstate', syncRolePageWithUrl);
    }, []);

    const changeTab = (value) => {
        setTab(value);

        if (typeof window !== 'undefined' && value !== 'permission-form') {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', value);
            window.history.replaceState({}, '', `${url.pathname}${url.search}${url.hash}`);
        }

        scrollToTop();
    };
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

    const currentRegionId = String(agence?.region_id ?? '');
    const availableCities = villes.filter((ville) => String(ville.region_id ?? '') === currentRegionId);

    const handlePreview = (name) => (event) => {
        const file = event.target.files?.[0];
        if (!file) return;

        const previewUrl = URL.createObjectURL(file);
        setVisuals((current) => ({ ...current, [name]: previewUrl }));
    };


    const returnToRoles = () => {
        updateRolePageUrl();
        changeTab('roles');
    };

    const openPermissionForm = (role = null) => {
        setRoleFormMode('edit');
        setSelectedPermissionRole(role?.role_id ?? '');
        setPermissionDraft(role ? [...(rolePermissions[role.role_id] ?? [])] : []);
        setNewRole(role ? {
            name: role.name ?? '',
            description: role.description ?? '',
        } : { name: '', description: '' });
        setPermissionSearch('');
        setRoleFormErrors({});
        updateRolePageUrl('permissions');
        changeTab('permission-form');
    };
    const openRoleForm = () => {
        setRoleFormMode('create');
        setRoleTemplate('empty');
        setNewRole({ name: '', description: '' });
        setSelectedPermissionRole('');
        setPermissionDraft([]);
        setPermissionSearch('');
        setRoleFormErrors({});
        updateRolePageUrl('create');
        changeTab('permission-form');
    };
    const changePermissionRole = (roleId) => {
        const role = roles.find((item) => item.role_id === roleId);
        setSelectedPermissionRole(roleId);
        setPermissionDraft([...(rolePermissions[roleId] ?? [])]);
        setNewRole({
            name: role?.name ?? '',
            description: role?.description ?? '',
        });
        setRoleFormErrors({});
    };

    const changeRoleTemplate = (templateId) => {
        setRoleTemplate(templateId);
        setPermissionDraft(templateId === 'empty' ? [] : [...(rolePermissions[templateId] ?? [])]);
    };

    const selectedRoleData = roles.find((role) => role.role_id === selectedPermissionRole);
    const editingResponsable = Boolean(selectedRoleData?.is_responsable);
    const lockedPermissionKeys = editingResponsable
        ? permissionGroupsCatalog.flatMap((group) =>
            group.permissions
                .filter((permission) => permission.module_slug === 'parametrage')
                .map((permission) => permission.key)
        )
        : [];

    const deleteRole = (role) => {
        if (!role.is_deletable) return;

        const userCount = Number(role.user_count ?? 0);
        if (userCount > 0) {
            window.alert(`Ce rôle est attribué à ${userCount} utilisateur${userCount > 1 ? 's' : ''}. Réaffectez-les avant de le supprimer.`);
            return;
        }

        if (!window.confirm(`Supprimer le rôle « ${role.name} » ?`)) return;

        router.delete(`/agence/parametrage/roles/${role.role_id}`, {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                setRoleCatalog((current) => current.filter((item) => item.role_id !== role.role_id));
                setMatrixRoleIds((current) => current.filter((roleId) => roleId !== role.role_id));
            },
        });
    };

    const togglePermission = (permissionKey) => {
        if (lockedPermissionKeys.includes(permissionKey)) return;

        setPermissionDraft((current) =>
            current.includes(permissionKey)
                ? current.filter((key) => key !== permissionKey)
                : [...current, permissionKey]
        );
    };

    const togglePermissionGroup = (group) => {
        const groupKeys = group.permissions
            .map((permission) => permission.key)
            .filter((key) => !lockedPermissionKeys.includes(key));
        if (groupKeys.length === 0) return;
        const groupIsSelected = groupKeys.every((key) => permissionDraft.includes(key));

        setPermissionDraft((current) =>
            groupIsSelected
                ? current.filter((key) => !groupKeys.includes(key))
                : Array.from(new Set([...current, ...groupKeys]))
        );
    };

    const filteredPermissionGroups = permissionGroupsCatalog.map((group) => ({
        ...group,
        permissions: group.permissions
            .filter((permission) =>
                `${group.label} ${permission.label}`.toLowerCase().includes(permissionSearch.trim().toLowerCase())
            )
            .map((permission) => ({
                ...permission,
                is_locked: lockedPermissionKeys.includes(permission.key),
            })),
    })).filter((group) => group.permissions.length > 0);

    const setPermissionSelection = (permissions) => {
        setPermissionDraft(Array.from(new Set([...permissions, ...lockedPermissionKeys])));
    };

    const saveRolePermissions = (event) => {
        event.preventDefault();

        if (roleFormMode === 'edit' && editingResponsable && !responsableConfirmationAccepted.current) {
            setResponsableConfirmOpen(true);
            return;
        }

        responsableConfirmationAccepted.current = false;

        const previousPermissions = roleFormMode === 'create'
            ? []
            : (rolePermissions[selectedPermissionRole] ?? []);
        const newSensitivePermissions = permissionDraft.filter(
            (permission) => sensitivePermissionKeys.has(permission) && !previousPermissions.includes(permission)
        );

        if (newSensitivePermissions.length > 0 && !window.confirm(
            `${newSensitivePermissions.length} permission${newSensitivePermissions.length > 1 ? 's sensibles seront accordées' : ' sensible sera accordée'}. Continuer ?`
        )) {
            return;
        }

        if (roleFormMode === 'create') {
            if (!newRole.name.trim()) return;

            const duplicate = roles.some((role) => role.name.trim().toLowerCase() === newRole.name.trim().toLowerCase());
            if (duplicate) {
                setRoleFormErrors({ name: 'Un rôle portant ce nom existe déjà.' });
                return;
            }

            router.post('/agence/parametrage/roles', {
                name: newRole.name.trim(),
                description: newRole.description.trim(),
                permissions: permissionDraft,
            }, {
                preserveScroll: true,
                preserveState: false,
                onSuccess: returnToRoles,
                onError: setRoleFormErrors,
            });
            return;
        }

        if (!selectedPermissionRole) {
            return;
        }

        if (!newRole.name.trim()) {
            setRoleFormErrors({ name: 'Le nom du rôle est obligatoire.' });
            return;
        }

        const selectedRole = roles.find((role) => role.role_id === selectedPermissionRole);
        if (!selectedRole?.is_editable) return;

        const duplicate = roles.some((role) =>
            role.role_id !== selectedPermissionRole
            && role.name.trim().toLowerCase() === newRole.name.trim().toLowerCase()
        );
        if (duplicate) {
            setRoleFormErrors({ name: 'Un rôle portant ce nom existe déjà.' });
            return;
        }

        router.put(`/agence/parametrage/roles/${selectedPermissionRole}`, {
            name: newRole.name.trim(),
            description: newRole.description.trim(),
            permissions: permissionDraft,
        }, {
            preserveScroll: true,
            preserveState: false,
            onSuccess: returnToRoles,
            onError: setRoleFormErrors,
        });
    };

    const permissionEditorVisible = roleFormMode === 'create' || Boolean(selectedPermissionRole);

    const toggleMatrixRole = (roleId) => {
        setMatrixRoleIds((current) => {
            if (current.includes(roleId)) return current.filter((id) => id !== roleId);
            if (current.length >= 5) return current;
            return [...current, roleId];
        });
    };

    const toggleMatrixGroup = (groupLabel) => {
        setCollapsedPermissionGroups((current) =>
            current.includes(groupLabel)
                ? current.filter((label) => label !== groupLabel)
                : [...current, groupLabel]
        );
    };

    const matrixRoles = roles.filter((role) => matrixRoleIds.includes(role.role_id));
    const visibleMatrixGroups = permissionGroupsCatalog.map((group) => ({
        ...group,
        permissions: group.permissions.filter((permission) => {
            const matchesSearch = `${group.label} ${permission.label}`
                .toLowerCase()
                .includes(matrixPermissionSearch.trim().toLowerCase());
            const allowedCount = matrixRoles.filter((role) => role.permissions.includes(permission.key)).length;
            const matchesFilter = matrixFilter === 'granted'
                ? allowedCount > 0
                : matrixFilter === 'differences'
                    ? allowedCount > 0 && allowedCount < matrixRoles.length
                    : true;

            return matchesSearch && matchesFilter;
        }),
    })).filter((group) => group.permissions.length > 0);

    if (isRoleEditorPage) {
        const RolePage = roleFormMode === 'create' ? CreateRolePage : RolePermissionsPage;

        return (
            <AgenceLayout title={roleFormMode === 'create' ? 'Créer un rôle' : 'Configurer les permissions'}>
                <RolePage
                    roles={roleFormMode === 'create' ? roles : roles.filter((role) => role.is_editable)}
                    newRole={newRole}
                    setNewRole={setNewRole}
                    roleTemplate={roleTemplate}
                    onRoleTemplateChange={changeRoleTemplate}
                    errors={roleFormErrors}
                    selectedRole={selectedPermissionRole}
                    onRoleChange={changePermissionRole}
                    permissionDraft={permissionDraft}
                    permissionSearch={permissionSearch}
                    setPermissionSearch={setPermissionSearch}
                    filteredPermissionGroups={filteredPermissionGroups}
                    allPermissionKeys={allPermissionKeys}
                    sensitivePermissionKeys={sensitivePermissionKeys}
                    isProtectedRole={editingResponsable}
                    onSetPermissions={setPermissionSelection}
                    onTogglePermission={togglePermission}
                    onToggleGroup={togglePermissionGroup}
                    onSubmit={saveRolePermissions}
                    onBack={returnToRoles}
                />
                <Dialog open={responsableConfirmOpen} onOpenChange={setResponsableConfirmOpen}>
                    <DialogContent className="max-w-md">
                        <DialogHeader>
                            <div className="mb-2 flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                                <TriangleAlert className="h-5 w-5" />
                            </div>
                            <DialogTitle>Modifier le rôle Responsable ?</DialogTitle>
                            <DialogDescription>
                                Vous modifiez votre rôle Responsable. Vos accès pourront être réduits immédiatement.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter className="mt-5">
                            <Button type="button" variant="outline" onClick={() => setResponsableConfirmOpen(false)}>
                                Annuler
                            </Button>
                            <Button
                                type="button"
                                className="bg-amber-600 text-white hover:bg-amber-700"
                                onClick={() => {
                                    responsableConfirmationAccepted.current = true;
                                    setResponsableConfirmOpen(false);
                                    saveRolePermissions({ preventDefault: () => {} });
                                }}
                            >
                                Continuer
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </AgenceLayout>
        );
    }

    return (
        <AgenceLayout title="Paramétrage">
            <Head title="Paramétrage" />

            <div className={cn(
                'mx-auto flex flex-col gap-6 px-4 pb-10 sm:px-6 lg:px-8',
                isRoleEditorPage ? 'max-w-5xl' : 'max-w-7xl'
            )}>
                {/* En-tête */}
                {isRoleEditorPage ? (
                    <div className="flex flex-col gap-4 border-b border-[#e2e8f0] pb-5 sm:flex-row sm:items-center sm:justify-between">
                        <div className="min-w-0">
                            <button type="button" onClick={returnToRoles} className="mb-3 inline-flex items-center gap-2 text-sm font-medium text-[#00559b] hover:underline">
                                <ChevronLeft className="h-4 w-4" />
                                Rôles et permissions
                            </button>
                            <h2 className="text-2xl font-semibold text-[#0f172a]">
                                {roleFormMode === 'create' ? 'Créer un rôle' : 'Configurer les permissions'}
                            </h2>
                            <p className="mt-1 text-sm text-[#5f7182]">
                                {roleFormMode === 'create'
                                    ? "Créez un profil personnalisé et choisissez ses accès."
                                    : "Sélectionnez un rôle puis définissez précisément ses accès."}
                            </p>
                        </div>
                    </div>
                ) : (
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-2">
                                <h2 className="text-2xl font-semibold text-[#0f172a]">Paramétrage</h2>
                            </div>
                            <p className="mt-1 text-sm text-[#5f7182]">Configurez votre agence, la facturation, les visuels et les notifications.</p>
                        </div>
                    </div>
                )}

                {/* Disposition sidebar + contenu */}
                <div className={cn(
                    'grid items-start gap-6',
                    !isRoleEditorPage && 'xl:grid-cols-[280px_minmax(0,1fr)]'
                )}>
                    {/* Colonne latérale */}
                    {!isRoleEditorPage ? <aside className="flex flex-col gap-4 xl:sticky xl:top-6">
                        <Card className="rounded-3xl border-[#c8d4de] bg-white shadow-sm">
                            <CardContent className="mt-4 p-3">
                                <p className="px-3 pb-2 pt-1 text-[11px] font-semibold uppercase tracking-wide text-[#94a3b8]">
                                    Sections
                                </p>
                                <nav className="grid grid-cols-2 gap-2 sm:grid-cols-3 xl:flex xl:flex-col" aria-label="Navigation du paramétrage">
                                    {NAV_ITEMS.map((item) => {
                                        const ItemIcon = item.icon;
                                        const isActive = tab === item.value;

                                        return (
                                            <button
                                                key={item.value}
                                                type="button"
                                                onClick={() => changeTab(item.value)}
                                                aria-current={isActive ? 'page' : undefined}
                                                className={cn(
                                                    'group flex w-full min-w-0 items-center gap-3 rounded-2xl border px-3 py-2.5 text-left transition',
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

                    </aside> : null}

                    {/* Colonne de contenu */}
                    <div className="min-w-0">
                        <Tabs value={tab} onValueChange={setTab}>
                            <TabsContent value="agence">
                                <form action="/agence/parametrage/agence" method="POST" className="space-y-6">
                                    <input type="hidden" name="_method" value="PUT" />
                                    <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />

                                    <SectionCard icon={Home} title="Informations de l'agence" description="Identité officielle et coordonnées." step="01">
                                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                                            <Field label="Nom de l'agence" required className="md:col-span-2">
                                                <Input name="name" defaultValue={getValue(agence, 'name')} placeholder="Nom officiel de l'agence" className={inputClassName} />
                                            </Field>
                                            <Field label="Sigle / Abréviation">
                                                <Input name="sigle" defaultValue={getValue(agence, 'sigle')} placeholder="Ex: PROIMMV1" className={inputClassName} />
                                            </Field>
                                            <Field label="Numéro RCCM">
                                                <Input name="rccm" defaultValue={getValue(agence, 'rccm')} placeholder="Numéro RCCM" className={inputClassName} />
                                            </Field>
                                            <Field label="Numéro contribuable">
                                                <Input name="num_contribuable" defaultValue={getValue(agence, 'num_contribuable')} placeholder="Numéro contribuable" className={inputClassName} />
                                            </Field>
                                            <Field label="Régime fiscal">
                                                <FormSelect
                                                    name="regime_fiscal"
                                                    defaultValue={getValue(agence, 'regime_fiscal')}
                                                    placeholder="Choisir un régime fiscal"
                                                >
                                                    <SelectItem value="SARL">SARL</SelectItem>
                                                    <SelectItem value="SAS">SAS</SelectItem>
                                                    <SelectItem value="SA">SA</SelectItem>
                                                </FormSelect>
                                            </Field>
                                            <Field label="Adresse" className="md:col-span-2">
                                                <textarea name="adresse" defaultValue={getValue(agence, 'adresse')} rows={3} placeholder="Adresse complète de l'agence" className={textareaClassName} />
                                            </Field>
                                            <Field label="Téléphone 1">
                                                <PhoneInput name="tel1" defaultValue={getValue(agence, 'tel1')} placeholder="Ex: +225 07 00 00 00 00" />
                                            </Field>
                                            <Field label="Téléphone 2">
                                                <PhoneInput name="tel2" defaultValue={getValue(agence, 'tel2')} placeholder="Téléphone secondaire" />
                                            </Field>
                                            <Field label="Email principal">
                                                <Input name="email1" type="email" defaultValue={getValue(agence, 'email1')} placeholder="contact@agence.ci" className={inputClassName} />
                                            </Field>
                                            <Field label="Email secondaire">
                                                <Input name="email2" type="email" defaultValue={getValue(agence, 'email2')} placeholder="secondaire@agence.ci" className={inputClassName} />
                                            </Field>
                                            <Field label="Région">
                                                <FormSelect
                                                    name="region_id"
                                                    defaultValue={String(getValue(agence, 'region_id'))}
                                                    placeholder="Choisir une région"
                                                >
                                                    {regions.map((region) => {
                                                        const regionId = String(region.id ?? region.region_id);
                                                        return (
                                                            <SelectItem key={regionId} value={regionId}>
                                                                {region.name}
                                                            </SelectItem>
                                                        );
                                                    })}
                                                </FormSelect>
                                            </Field>
                                            <Field label="Ville">
                                                <FormSelect
                                                    name="ville_id"
                                                    defaultValue={String(getValue(agence, 'ville_id'))}
                                                    placeholder="Choisir une ville"
                                                >
                                                    {availableCities.map((ville) => {
                                                        const villeId = String(ville.id ?? ville.ville_id);
                                                        return (
                                                            <SelectItem key={villeId} value={villeId}>
                                                                {ville.name}
                                                            </SelectItem>
                                                        );
                                                    })}
                                                </FormSelect>
                                            </Field>
                                            <Field label="Boîte postale">
                                                <Input name="bp" defaultValue={getValue(agence, 'bp')} placeholder="BP 1234" className={inputClassName} />
                                            </Field>
                                            <Field label="Site web">
                                                <Input name="site_web" defaultValue={getValue(agence, 'site_web')} placeholder="https://agence.ci" className={inputClassName} />
                                            </Field>
                                            <Field label="Banque domiciliataire">
                                                <Input name="banque" defaultValue={getValue(agence, 'banque')} placeholder="Nom de la banque" className={inputClassName} />
                                            </Field>
                                            <Field label="Agence bancaire">
                                                <Input name="agence_bancaire" defaultValue={getValue(agence, 'agence_bancaire')} placeholder="Agence, quartier ou ville" className={inputClassName} />
                                            </Field>
                                            <Field label="Numéro de compte (IBAN / RIB)" className="md:col-span-2">
                                                <Input name="rib" defaultValue={getValue(agence, 'rib')} placeholder="RIB ou IBAN" className={inputClassName} />
                                            </Field>
                                        </div>
                                    </SectionCard>

                                    <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
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

                            <TabsContent value="general">
                                <form action="/agence/parametrage/general" method="POST" className="space-y-6">
                                    <input type="hidden" name="_method" value="PUT" />
                                    <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />

                                    <SectionCard icon={Globe} title="Devise & localisation" description="Paramètres globaux de l'interface." step="02">
                                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                                            <Field label="Devise par défaut">
                                                <FormSelect
                                                    name="devise"
                                                    defaultValue={getValue(parametrage, 'devise', 'XOF')}
                                                    placeholder="Choisir une devise"
                                                >
                                                    <SelectItem value="XOF">XOF - Franc CFA (BCEAO)</SelectItem>
                                                    <SelectItem value="EUR">EUR - Euro</SelectItem>
                                                    <SelectItem value="USD">USD - Dollar américain</SelectItem>
                                                </FormSelect>
                                            </Field>
                                            <Field label="Langue de l'interface">
                                                <FormSelect name="langue" defaultValue={getValue(parametrage, 'langue', 'fr')} placeholder="Choisir la langue">
                                                    <SelectItem value="fr">Français</SelectItem>
                                                    <SelectItem value="en">Anglais</SelectItem>
                                                </FormSelect>
                                            </Field>
                                            <Field label="Format de date">
                                                <FormSelect name="format_date" defaultValue={getValue(parametrage, 'format_date', 'd/m/Y')} placeholder="Choisir le format">
                                                    <SelectItem value="d/m/Y">JJ/MM/AAAA</SelectItem>
                                                    <SelectItem value="m/d/Y">MM/JJ/AAAA</SelectItem>
                                                    <SelectItem value="Y-m-d">AAAA-MM-JJ</SelectItem>
                                                </FormSelect>
                                            </Field>
                                            <Field label="Fuseau horaire">
                                                <FormSelect name="timezone" defaultValue={getValue(parametrage, 'timezone', 'Africa/Abidjan')} placeholder="Choisir un fuseau">
                                                    <SelectItem value="Africa/Abidjan">Africa/Abidjan (GMT+0)</SelectItem>
                                                    <SelectItem value="Europe/Paris">Europe/Paris (GMT+1/+2)</SelectItem>
                                                </FormSelect>
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
                                    </SectionCard>

                                    <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
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

                            <TabsContent value="facturation">
                                <form action="/agence/parametrage/facturation" method="POST" className="space-y-6">
                                    <input type="hidden" name="_method" value="PUT" />
                                    <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.content ?? ''} />

                                    <SectionCard icon={FileText} title="Cycle de facturation" description="Périodicité et délais." step="04">
                                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                                            <Field label="Période de facturation">
                                                <FormSelect
                                                    name="periode_facturation"
                                                    defaultValue={getValue(parametrage, 'periode_facturation', 'mensuelle')}
                                                    placeholder="Choisir une période"
                                                >
                                                    <SelectItem value="mensuelle">Mensuelle</SelectItem>
                                                    <SelectItem value="trimestrielle">Trimestrielle</SelectItem>
                                                    <SelectItem value="semestrielle">Semestrielle</SelectItem>
                                                    <SelectItem value="annuelle">Annuelle</SelectItem>
                                                    <SelectItem value="commande">À la commande</SelectItem>
                                                </FormSelect>
                                            </Field>
                                                <Field label="Jour d'émission">
                                                <FormSelect name="jour_emission" defaultValue={getValue(parametrage, 'jour_emission', '1')} placeholder="Choisir un jour">
                                                    <SelectItem value="1">1er du mois</SelectItem>
                                                    <SelectItem value="5">5 du mois</SelectItem>
                                                    <SelectItem value="15">15 du mois</SelectItem>
                                                    <SelectItem value="last">Dernier jour du mois</SelectItem>
                                                </FormSelect>
                                            </Field>
                                            <Field label="Délai limite de paiement (jours)">
                                                <Input type="number" name="delai_paiement" defaultValue={getValue(parametrage, 'delai_paiement', 30)} placeholder="30" className={inputClassName} />
                                            </Field>
                                            <Field label="Pénalité de retard (%/mois)">
                                                <Input type="number" step="0.1" name="penalite_retard" defaultValue={getValue(parametrage, 'penalite_retard', 1.5)} placeholder="1,5" className={inputClassName} />
                                            </Field>
                                            <Field label="Préfixe numéro de facture">
                                                <Input name="prefixe_facture" defaultValue={getValue(parametrage, 'prefixe_facture', 'FAC-')} placeholder="FAC-" className={inputClassName} />
                                            </Field>
                                            <Field label="Prochain numéro de séquence">
                                                <Input type="number" min="1" name="sequence_facture" defaultValue={getValue(parametrage, 'sequence_facture', 1)} placeholder="1" className={inputClassName} />
                                            </Field>
                                        </div>
                                    </SectionCard>

                                    <SectionCard icon={Brush} title="Commission & taxes" description="Calculs financiers de l'agence." step="05">
                                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                                            <Field label="Commission agence (%)">
                                                <Input type="number" step="0.5" min="0" max="100" name="commission" defaultValue={getValue(parametrage, 'commission', 15)} placeholder="15" className={inputClassName} />
                                            </Field>
                                            <Field label="Base de calcul">
                                                <FormSelect name="base_commission" defaultValue={getValue(parametrage, 'base_commission', 'ht')} placeholder="Choisir la base">
                                                    <SelectItem value="ht">Sur le montant HT</SelectItem>
                                                    <SelectItem value="ttc">Sur le montant TTC</SelectItem>
                                                    <SelectItem value="brut">Sur le budget brut</SelectItem>
                                                </FormSelect>
                                            </Field>
                                            <Field label="TVA (%)">
                                                <Input type="number" step="0.5" min="0" name="tva" defaultValue={getValue(parametrage, 'tva', 18)} placeholder="18" className={inputClassName} />
                                            </Field>
                                            <Field label="AIB (%)">
                                                <Input type="number" step="0.5" min="0" name="aib" defaultValue={getValue(parametrage, 'aib', 5)} placeholder="5" className={inputClassName} />
                                            </Field>
                                            <Field label="RAS (%)">
                                                <Input type="number" step="0.5" min="0" name="ras" defaultValue={getValue(parametrage, 'ras', 2)} placeholder="2" className={inputClassName} />
                                            </Field>
                                            <Field label="Acompte minimum exigé (%)">
                                                <Input type="number" min="0" max="100" name="acompte_min" defaultValue={getValue(parametrage, 'acompte_min', 30)} placeholder="30" className={inputClassName} />
                                            </Field>
                                            <Field label="Mode de règlement par défaut" className="md:col-span-2">
                                                <FormSelect
                                                    name="mode_reglement_id"
                                                    defaultValue={String(getValue(parametrage, 'mode_reglement_id', 1))}
                                                    placeholder="Choisir un mode de règlement"
                                                >
                                                    {modePaiement.map((mode) => {
                                                        const modeId = String(mode.id ?? mode.mode_paiement_id);
                                                        return (
                                                            <SelectItem key={modeId} value={modeId}>
                                                                {mode.name}
                                                            </SelectItem>
                                                        );
                                                    })}
                                                </FormSelect>
                                            </Field>
                                        </div>
                                    </SectionCard>

                                    <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
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
                                        <div className="mt-4 grid gap-6 lg:grid-cols-2">
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
                                                    <Input type="number" name="logo_largeur" defaultValue={getValue(parametrage, 'logo_largeur', 200)} placeholder="200" className={inputClassName} />
                                                </Field>
                                                <Field label="Position sur facture">
                                                    <FormSelect name="logo_position" defaultValue={getValue(parametrage, 'logo_position', 'gauche')} placeholder="Choisir une position">
                                                        <SelectItem value="gauche">En-tête gauche</SelectItem>
                                                        <SelectItem value="centre">En-tête centré</SelectItem>
                                                        <SelectItem value="droit">En-tête droit</SelectItem>
                                                    </FormSelect>
                                                </Field>
                                            </div>
                                        </div>
                                    </SectionCard>

                                    <SectionCard icon={FileImage} title="Logos secondaires" description="Tutelle, partenaire, cachet." step="07">
                                        <div className="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
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

                                    <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
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
                                        <div className="mt-4 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                                    {[
                                        { key: 'signature_dg', title: 'Directeur Général (DG)', nom: 'dg_nom', titre: 'dg_titre', defaultTitle: 'Directeur Général' },
                                        { key: 'signature_sg', title: 'Secrétariat Général', nom: 'sg_nom', titre: 'sg_titre', defaultTitle: 'Secrétaire Général(e)' },
                                        { key: 'signature_cpt', title: 'Comptabilité', nom: 'cpt_nom', titre: 'cpt_titre', defaultTitle: 'Responsable Comptable' },
                                            ].map((sig) => (
                                                <div key={sig.key} className="rounded-3xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                                    <UploadBox
                                                        label="Fichier de signature"
                                                        help="PNG avec fond transparent recommandé"
                                                        name={sig.key}
                                                        preview={visuals[sig.key]}
                                                        onChange={handlePreview(sig.key)}
                                                        onClear={() => setVisuals((current) => ({ ...current, [sig.key]: '' }))}
                                                        icon={Upload}
                                                    />
                                                    <div className="mt-4 grid gap-4">
                                                        <Field label="Nom complet">
                                                            <Input name={sig.nom} defaultValue={getValue(parametrage, sig.nom)} placeholder="Nom et prénom" className={inputClassName} />
                                                        </Field>
                                                        <Field label="Titre">
                                                            <Input name={sig.titre} defaultValue={getValue(parametrage, sig.titre, sig.defaultTitle)} placeholder="Fonction / titre" className={inputClassName} />
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

                                    <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
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
                                                <Input type="number" min="1" name="delai_rappel" defaultValue={getValue(parametrage, 'delai_rappel', 7)} placeholder="7" className={inputClassName} />
                                            </Field>
                                            <Field label="Seuil pour copie DG (XOF)">
                                                <Input type="number" step="50000" name="seuil_dg" defaultValue={getValue(parametrage, 'seuil_dg', 1000000)} placeholder="1000000" className={inputClassName} />
                                            </Field>
                                        </div>
                                    </SectionCard>

                                    <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
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

                            <TabsContent value="roles">
                                <div className="space-y-6">
                                    <SectionCard
                                        icon={UsersRound}
                                        title="Rôles du personnel"
                                        step="12"
                                        action={(
                                            <div className="flex flex-wrap gap-2">
                                                <Button type="button" variant="outline" onClick={() => openPermissionForm()} className="rounded-xl border-[#c8d4de] bg-white text-[#00559b] hover:border-[#00559b]">
                                                    <KeyRound className="h-4 w-4" />
                                                    Modifier les permissions
                                                </Button>
                                                <Button type="button" onClick={openRoleForm} className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]">
                                                    <UsersRound className="h-4 w-4" />
                                                    Créer un rôle
                                                </Button>
                                            </div>
                                        )}
                                    >
                                        <div className="grid gap-4 md:grid-cols-2">
                                            {roles.map((role) => (
                                                <div
                                                    key={role.role_id}
                                                    className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4"
                                                >
                                                    <div className="flex items-start justify-between gap-3">
                                                        <div className="min-w-0">
                                                            <div className="flex items-center gap-2">
                                                                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#eaf4fb] text-[#00559b]">
                                                                    <ShieldCheck className="h-4 w-4" />
                                                                </span>
                                                                <div className="min-w-0">
                                                                    <p className="truncate text-sm font-semibold text-[#0f172a]">{role.name}</p>
                                                                    <Badge variant="outline" className={cn(
                                                                        'mt-1 rounded-full px-2 py-0 text-[10px]',
                                                                        role.is_custom
                                                                            ? 'border-[#8dbddd] bg-white text-[#00559b]'
                                                                            : 'border-[#c8d4de] bg-[#f1f5f9] text-[#5f7182]'
                                                                    )}>
                                                                        {role.is_custom ? 'Personnalisé' : 'Prédéfini'}
                                                                    </Badge>
                                                                </div>
                                                            </div>
                                                            <p className="mt-3 text-xs leading-5 text-[#5f7182]">{role.description}</p>
                                                        </div>
                                                        <Badge variant="outline" className="shrink-0 rounded-full border-[#c8d4de] bg-white text-xs">
                                                            {role.permissions.length} accès
                                                        </Badge>
                                                    </div>
                                                    <div className="mt-4 flex flex-wrap items-center gap-2 border-t border-[#e2e8f0] pt-3">
                                                        <span className="mr-auto text-xs text-[#5f7182]">
                                                            {Number(role.user_count ?? 0)} utilisateur{Number(role.user_count ?? 0) > 1 ? 's' : ''}
                                                        </span>
                                                        {role.is_editable ? (
                                                            <>
                                                                <Button type="button" variant="outline" size="sm" className="h-8 rounded-lg border-[#8dbddd] text-xs text-[#00559b] hover:bg-[#eaf4fb]" onClick={() => openPermissionForm(role)}>
                                                                    <Pencil className="h-3.5 w-3.5" />Modifier
                                                                </Button>
                                                                {role.is_deletable ? (
                                                                    <Button type="button" variant="outline" size="sm" className="h-8 rounded-lg border-[#f1b8b5] text-xs text-[#b42318] hover:bg-[#fef2f2] disabled:cursor-not-allowed disabled:opacity-50" disabled={Number(role.user_count ?? 0) > 0} title={Number(role.user_count ?? 0) > 0 ? 'Ce rôle est attribué à un utilisateur.' : 'Supprimer ce rôle'} onClick={() => deleteRole(role)}>
                                                                        <Trash2 className="h-3.5 w-3.5" />Supprimer
                                                                    </Button>
                                                                ) : null}
                                                            </>
                                                        ) : null}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </SectionCard>

                                    <SectionCard
                                        icon={KeyRound}
                                        title="Matrice des permissions"
                                        description="Visualisez les accès attribués à chaque rôle de l'agence."
                                        step="13"
                                    >
                                        <div className="mb-4 space-y-4 rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                          

                                            <div>
                                                <div className="mb-2 flex items-center justify-between gap-3">
                                                    <p className="text-xs font-semibold uppercase tracking-wide text-[#5f7182]">Rôles comparés</p>
                                                    <span className="text-xs text-[#5f7182]">{matrixRoleIds.length}/5 sélectionnés</span>
                                                </div>
                                                <div className="flex flex-wrap gap-2">
                                                    {roles.map((role) => {
                                                        const selected = matrixRoleIds.includes(role.role_id);
                                                        const disabled = !selected && matrixRoleIds.length >= 5;
                                                        return (
                                                            <button key={role.role_id} type="button" disabled={disabled} onClick={() => toggleMatrixRole(role.role_id)} className={cn(
                                                                'inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium transition',
                                                                selected ? 'border-[#00559b] bg-[#eaf4fb] text-[#00559b]' : 'border-[#c8d4de] bg-white text-[#5f7182]',
                                                                disabled && 'cursor-not-allowed opacity-40'
                                                            )}>
                                                                <span className={cn('flex h-4 w-4 items-center justify-center rounded border', selected ? 'border-[#00559b] bg-[#00559b] text-white' : 'border-[#c8d4de]')}>
                                                                    {selected ? <Check className="h-3 w-3" /> : null}
                                                                </span>
                                                                {role.name}
                                                            </button>
                                                        );
                                                    })}
                                                </div>
                                            </div>
                                        </div>

                                        {matrixRoles.length ? (
                                            <div className="max-h-[620px] overflow-auto rounded-2xl border border-[#e2e8f0]">
                                                <table className="w-full min-w-[720px] border-separate border-spacing-0 text-left text-sm">
                                                    <thead className="sticky top-0 z-20 bg-[#f8fafc] text-xs uppercase tracking-wide text-[#5f7182] shadow-sm">
                                                        <tr>
                                                            <th className="sticky left-0 z-30 min-w-72 border-b border-r border-[#e2e8f0] bg-[#f8fafc] px-4 py-3 font-semibold">Permission</th>
                                                            {matrixRoles.map((role) => <th key={role.role_id} className="min-w-36 border-b border-[#e2e8f0] px-4 py-3 text-center font-semibold">{role.name}</th>)}
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {visibleMatrixGroups.flatMap((group) => {
                                                            const collapsed = collapsedPermissionGroups.includes(group.label);
                                                            return [
                                                                <tr key={`${group.label}-heading`} className="bg-[#eaf4fb]">
                                                                    <td colSpan={matrixRoles.length + 1} className="sticky left-0 px-4 py-2">
                                                                        <button type="button" onClick={() => toggleMatrixGroup(group.label)} className="flex w-full items-center gap-2 text-left text-xs font-semibold uppercase tracking-wide text-[#00559b]">
                                                                            {collapsed ? <ChevronRight className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
                                                                            {group.label}<span className="font-normal text-[#5f7182]">({group.permissions.length})</span>
                                                                        </button>
                                                                    </td>
                                                                </tr>,
                                                                ...(collapsed ? [] : group.permissions.map((permission) => (
                                                                    <tr key={permission.key} className="bg-white hover:bg-[#f8fafc]">
                                                                        <td className="sticky left-0 z-10 border-b border-r border-[#edf2f6] bg-inherit px-4 py-3 font-medium text-[#0f172a]">
                                                                            <div className="flex items-center gap-2">
                                                                                <span>{permission.label}</span>
                                                                                {sensitivePermissionKeys.has(permission.key) ? <LockKeyhole className="h-3.5 w-3.5 shrink-0 text-[#9a6700]" aria-label="Permission sensible" /> : null}
                                                                            </div>
                                                                        </td>
                                                                        {matrixRoles.map((role) => {
                                                                            const allowed = role.permissions.includes(permission.key);
                                                                            return <td key={`${role.role_id}-${permission.key}`} className="border-b border-[#edf2f6] px-4 py-3 text-center"><span className={cn('inline-flex h-7 w-7 items-center justify-center rounded-full', allowed ? 'bg-[#e9f5d7] text-[#4d8500]' : 'bg-[#f1f5f9] text-[#94a3b8]')} title={allowed ? 'Autorisé' : 'Non autorisé'}>{allowed ? <Check className="h-4 w-4" /> : <span>—</span>}</span></td>;
                                                                        })}
                                                                    </tr>
                                                                ))),
                                                            ];
                                                        })}
                                                    </tbody>
                                                </table>
                                            </div>
                                        ) : <div className="rounded-2xl border border-dashed border-[#c8d4de] p-10 text-center text-sm text-[#5f7182]">Sélectionnez au moins un rôle à comparer.</div>}

                                        {matrixRoles.length && !visibleMatrixGroups.length ? <div className="mt-3 rounded-xl bg-[#f8fafc] p-4 text-center text-sm text-[#5f7182]">Aucune permission ne correspond aux filtres.</div> : null}
                                    </SectionCard>
                                </div>
                            </TabsContent>

                            <TabsContent value="permission-form">
                                <form onSubmit={saveRolePermissions} className="space-y-6">
                                    <SectionCard
                                        icon={KeyRound}
                                        title={roleFormMode === 'create' ? 'Informations et accès du rôle' : 'Droits d’accès'}
                                        description={roleFormMode === 'create'
                                            ? "Définissez le rôle puis attribuez-lui ses accès."
                                            : 'Choisissez un profil et adaptez précisément ses accès par module.'}
                                    >
                                        <div className="shrink-0 rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                                            {roleFormMode === 'create' ? (
                                                <div className="grid gap-4 lg:grid-cols-2">
                                                    <Field label="Nom du rôle" required>
                                                        <Input
                                                            value={newRole.name}
                                                            onChange={(event) => setNewRole((current) => ({ ...current, name: event.target.value }))}
                                                            placeholder="Ex. Gestionnaire locatif"
                                                            className={cn(inputClassName, 'h-11 bg-white')}
                                                        />
                                                        {roleFormErrors.name ? <p className="mt-1 text-xs text-[#b42318]">{roleFormErrors.name}</p> : null}
                                                    </Field>
                                                    <Field label="Description">
                                                        <Input
                                                            value={newRole.description}
                                                            onChange={(event) => setNewRole((current) => ({ ...current, description: event.target.value }))}
                                                            placeholder="Mission principale de ce rôle"
                                                            className={cn(inputClassName, 'h-11 bg-white')}
                                                        />
                                                        {roleFormErrors.description ? <p className="mt-1 text-xs text-[#b42318]">{roleFormErrors.description}</p> : null}
                                                    </Field>
                                                </div>
                                            ) : (
                                                <Field label="Rôle à configurer" required>
                                                    <Select value={selectedPermissionRole} onValueChange={changePermissionRole}>
                                                        <SelectTrigger className={cn(inputClassName, 'h-11 bg-white')}>
                                                            <SelectValue placeholder="Sélectionner un rôle" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {roles.map((role) => (
                                                                <SelectItem key={role.role_id} value={String(role.role_id)}>
                                                                    {role.name}
                                                                </SelectItem>
                                                            ))}
                                                        </SelectContent>
                                                    </Select>
                                                </Field>
                                            )}

                                            {permissionEditorVisible ? (
                                                <div className="mt-4 flex flex-wrap items-center justify-end gap-2 border-t border-[#e2e8f0] pt-4">
                                                    <Badge variant="outline" className="mr-auto rounded-full border-[#8dbddd] bg-white text-[#00559b]">
                                                        {permissionDraft.length} accès sélectionnés
                                                    </Badge>
                                                    <Button type="button" variant="outline" className="h-9 rounded-xl border-[#c8d4de] bg-white text-xs" onClick={() => setPermissionDraft([...allPermissionKeys])}>
                                                        Tout autoriser
                                                    </Button>
                                                    <Button type="button" variant="outline" className="h-9 rounded-xl border-[#c8d4de] bg-white text-xs" onClick={() => setPermissionDraft([])}>
                                                        Tout retirer
                                                    </Button>
                                                </div>
                                            ) : null}
                                        </div>

                                        {permissionEditorVisible ? (
                                            <div className="mt-5 min-w-0 space-y-4">
                                                <div className="relative shrink-0">
                                                    <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#94a3b8]" />
                                                    <Input
                                                        value={permissionSearch}
                                                        onChange={(event) => setPermissionSearch(event.target.value)}
                                                        placeholder="Rechercher une permission ou un module..."
                                                        className={cn(inputClassName, 'h-11 pl-10')}
                                                    />
                                                </div>

                                                <div>
                                                    {filteredPermissionGroups.length ? (
                                                        <div className="overflow-x-auto rounded-2xl border border-[#e2e8f0] bg-white">
                                                            <table className="w-full min-w-[640px] border-collapse text-left text-sm">
                                                                <thead className="bg-[#f8fafc] text-xs uppercase tracking-wide text-[#5f7182]">
                                                                    <tr>
                                                                        <th className="border-b border-[#e2e8f0] px-4 py-3 font-semibold">
                                                                            Permission
                                                                        </th>
                                                                        <th className="w-40 border-b border-[#e2e8f0] px-4 py-3 text-center font-semibold">
                                                                            Autorisation
                                                                        </th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    {filteredPermissionGroups.flatMap((group) => {
                                                                        const selectedCount = group.permissions.filter((permission) =>
                                                                            permissionDraft.includes(permission.key)
                                                                        ).length;
                                                                        const groupIsSelected = selectedCount === group.permissions.length;

                                                                        return [
                                                                            <tr key={`${group.label}-heading`} className="bg-[#eaf4fb]/60">
                                                                                <td colSpan={2} className="border-b border-[#d7e6f0] px-4 py-3">
                                                                                    <div className="flex items-center justify-between gap-4">
                                                                                        <div className="flex min-w-0 items-center gap-3">
                                                                                            <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-[#00559b] shadow-sm">
                                                                                                <Layers3 className="h-4 w-4" />
                                                                                            </span>
                                                                                            <div className="min-w-0">
                                                                                                <p className="font-semibold text-[#00559b]">{group.label}</p>
                                                                                                <p className="text-xs text-[#5f7182]">
                                                                                                    {selectedCount} sur {group.permissions.length} sélectionné{selectedCount > 1 ? 's' : ''}
                                                                                                </p>
                                                                                            </div>
                                                                                        </div>
                                                                                        <button
                                                                                            type="button"
                                                                                            onClick={() => togglePermissionGroup(group)}
                                                                                            className="shrink-0 text-xs font-semibold text-[#00559b] hover:underline"
                                                                                        >
                                                                                            {groupIsSelected ? 'Tout décocher' : 'Tout cocher'}
                                                                                        </button>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>,
                                                                            ...group.permissions.map((permission) => {
                                                                                const checked = permissionDraft.includes(permission.key);

                                                                                return (
                                                                                    <tr
                                                                                        key={permission.key}
                                                                                        className={cn(
                                                                                            'border-b border-[#edf2f6] transition last:border-b-0',
                                                                                            checked ? 'bg-[#f4faff]' : 'bg-white hover:bg-[#f8fafc]'
                                                                                        )}
                                                                                    >
                                                                                        <td className="px-4 py-3 font-medium text-[#0f172a]">
                                                                                            {permission.label}
                                                                                        </td>
                                                                                        <td className="px-4 py-3 text-center">
                                                                                            <label className="inline-flex cursor-pointer items-center justify-center">
                                                                                                <input
                                                                                                    type="checkbox"
                                                                                                    checked={checked}
                                                                                                    onChange={() => togglePermission(permission.key)}
                                                                                                    className="sr-only"
                                                                                                />
                                                                                                <span
                                                                                                    className={cn(
                                                                                                        'flex h-7 w-7 items-center justify-center rounded-lg border transition',
                                                                                                        checked
                                                                                                            ? 'border-[#00559b] bg-[#00559b] text-white'
                                                                                                            : 'border-[#c8d4de] bg-white text-transparent hover:border-[#00559b]'
                                                                                                    )}
                                                                                                >
                                                                                                    <Check className="h-4 w-4" />
                                                                                                </span>
                                                                                                <span className="sr-only">
                                                                                                    {checked ? 'Retirer' : 'Accorder'} la permission {permission.label}
                                                                                                </span>
                                                                                            </label>
                                                                                        </td>
                                                                                    </tr>
                                                                                );
                                                                            }),
                                                                        ];
                                                                    })}
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    ) : (
                                                        <div className="rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] px-6 py-12 text-center">
                                                            <Search className="mx-auto h-8 w-8 text-[#94a3b8]" />
                                                            <p className="mt-3 text-sm font-semibold text-[#0f172a]">Aucune permission trouvée</p>
                                                            <p className="mt-1 text-xs text-[#5f7182]">Essayez un autre terme de recherche.</p>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        ) : (
                                            <div className="flex min-h-64 flex-1 items-center justify-center px-6 py-12 text-center">
                                                <div>
                                                    <span className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#eaf4fb] text-[#00559b]">
                                                        <KeyRound className="h-6 w-6" />
                                                    </span>
                                                    <p className="mt-4 text-sm font-semibold text-[#0f172a]">Sélectionnez d'abord un rôle</p>
                                                    <p className="mt-1 text-xs text-[#5f7182]">Les permissions apparaîtront après votre sélection.</p>
                                                </div>
                                            </div>
                                        )}
                                    </SectionCard>

                                    <div className="flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            className="rounded-xl border-[#c8d4de]"
                                            onClick={returnToRoles}
                                        >
                                            <ChevronLeft className="h-4 w-4" />
                                            Retour à la matrice
                                        </Button>
                                        {permissionEditorVisible ? (
                                            <Button
                                                type="submit"
                                                disabled={roleFormMode === 'create' && !newRole.name.trim()}
                                                className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]"
                                            >
                                                <Save className="h-4 w-4" />
                                                {roleFormMode === 'create' ? 'Créer le rôle' : 'Enregistrer les permissions'}
                                            </Button>
                                        ) : null}
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
