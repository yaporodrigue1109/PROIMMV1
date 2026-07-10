import { useEffect, useMemo, useState } from 'react';
import { Head } from '@inertiajs/react';
import {
    Clock3,
    ClipboardList,
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
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../../components/ui/tabs';

const asArray = (value) => (Array.isArray(value) ? value : []);

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
        name: item?.name ?? 'Sans nom',
        entreprise: item?.entreprise ?? 'Independant',
        email: item?.email ?? '',
        tel1: item?.tel1 ?? '',
        tel2: item?.tel2 ?? '',
        adresse: item?.adresse ?? '',
        statut: Boolean(Number(item?.statut ?? 1)),
        fonction: item?.fonction?.name ?? item?.fonction?.libelle ?? item?.fonction ?? 'Non definie',
        type_piece: item?.type_piece?.name ?? item?.type_piece?.libelle ?? item?.type_piece ?? '',
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
        titre: item?.titre ?? 'Intervention sans titre',
        description: item?.description ?? item?.description_generale ?? '',
        proprietaire: item?.proprietaire?.name ?? item?.proprietaire?.nom ?? 'Non defini',
        propriete: item?.propriete?.description ?? item?.propriete?.name ?? '',
        lot: item?.lot?.name ?? item?.lot?.libelle ?? item?.lot_id ?? '',
        batiment: item?.batiment?.name ?? item?.batiment?.libelle ?? item?.batiment_id ?? '',
        porte: item?.porte?.numero_porte ?? item?.porte?.name ?? item?.porte_id ?? '',
        prise_en_charge_par: item?.prise_en_charge_par ?? '',
        statut: item?.statut ?? 'en_attente',
        montant_global: Number(item?.montant_global ?? 0),
        date_debut: item?.date_debut ?? details[0]?.date_debut ?? item?.created_at ?? null,
        date_fin: item?.date_fin ?? details[0]?.date_fin ?? null,
        details,
        details_count: details.length,
    };
};

const normalizeType = (item) => {
    const id = String(item?.type_maintenance_id ?? item?.id ?? crypto.randomUUID());

    return {
        id,
        type_maintenance_id: id,
        name: item?.name ?? item?.libelle ?? 'Sans nom',
        categorie: item?.categorie ?? '---',
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
        name: item?.name ?? item?.libelle ?? 'Sans nom',
        categorie: item?.categorie ?? '---',
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
        description: 'Déclarer un type d’intervention.',
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

function EmptyState({ title, desc }) {
    return (
        <div className="flex flex-col items-center justify-center gap-2 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-10 text-center">
            <Search className="h-6 w-6 text-slate-400" />
            <p className="text-sm font-semibold text-slate-900">{title}</p>
            <p className="max-w-sm text-sm text-slate-500">{desc}</p>
        </div>
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
    categorie: '',
    description: '',
    duree_estimee: '',
});

const blankInterventionForm = () => ({
    titre: '',
    description_generale: '',
    proprietaire_id: '',
    prise_en_charge_par: 'proprietaire',
    type_intervention_id: '',
    maintenancier_id: '',
    date_debut: today(),
    date_fin: '',
    priorite: 'normale',
    prix: '',
    description: '',
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
}) {
    const maintenanceRows = useMemo(() => asArray(maintenances).map(normalizeMaintenance), [maintenances]);
    const maintenancierRows = useMemo(() => asArray(maintenanciers).map(normalizeMaintenancier), [maintenanciers]);
    const typeRows = useMemo(() => asArray(typesMaintenance).map(normalizeType), [typesMaintenance]);
    const fonctionRows = useMemo(() => asArray(fonctionsMaintenance).map(normalizeFonction), [fonctionsMaintenance]);

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

    const [section, setSection] = useState('maintenanciers');
    const [query, setQuery] = useState('');
    const [selectedMaintenancierId, setSelectedMaintenancierId] = useState('');
    const [selectedMaintenanceId, setSelectedMaintenanceId] = useState('');
    const [selectedTypeId, setSelectedTypeId] = useState('');
    const [selectedFonctionId, setSelectedFonctionId] = useState('');
    const [creationModalOpen, setCreationModalOpen] = useState(false);
    const [activeForm, setActiveForm] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const [formFeedback, setFormFeedback] = useState(null);
    const [maintenancierForm, setMaintenancierForm] = useState(blankMaintenancierForm());
    const [fonctionForm, setFonctionForm] = useState(blankFonctionForm());
    const [typeForm, setTypeForm] = useState(blankTypeForm());
    const [interventionForm, setInterventionForm] = useState(blankInterventionForm());

    const proprietorOptions = useMemo(
        () =>
            asArray(proprietaires).map((item) => ({
                value: String(item?.proprietaire_id ?? item?.id ?? ''),
                label: item?.name ?? item?.nom ?? 'Sans nom',
            })),
        [proprietaires]
    );

    const typePieceOptions = useMemo(
        () =>
            asArray(typePiece).map((item) => ({
                value: String(item?.type_pieces_id ?? item?.type_piece_id ?? item?.id ?? ''),
                label: item?.name ?? item?.libelle ?? 'Sans nom',
            })),
        [typePiece]
    );

    const maintenancierOptions = useMemo(
        () =>
            asArray(maintenancierStatiques.length ? maintenancierStatiques : maintenancierRows).map((item) => ({
                value: String(item?.maintenancier_id ?? item?.id ?? ''),
                label: item?.name ?? 'Sans nom',
            })),
        [maintenancierRows, maintenancierStatiques]
    );

    const typeInterventionOptions = useMemo(
        () =>
            asArray(typesInterventionStatiques.length ? typesInterventionStatiques : typeRows).map((item) => ({
                value: String(item?.type_maintenance_id ?? item?.id ?? ''),
                label: item?.name ?? 'Sans nom',
            })),
        [typeRows, typesInterventionStatiques]
    );

    const filteredMaintenanciers = useMemo(() => {
        const term = query.trim().toLowerCase();

        return maintenancierRows.filter((item) => {
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
    }, [maintenancierRows, query]);

    const filteredMaintenanceRows = useMemo(() => {
        const term = query.trim().toLowerCase();

        return maintenanceRows.filter((item) => {
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
    }, [maintenanceRows, query]);

    const filteredTypeRows = useMemo(() => {
        const term = query.trim().toLowerCase();

        return typeRows.filter((item) => {
            if (!term) return true;

            const haystack = [item.name, item.categorie, item.description]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            return haystack.includes(term);
        });
    }, [typeRows, query]);

    const filteredFonctionRows = useMemo(() => {
        const term = query.trim().toLowerCase();

        return fonctionRows.filter((item) => {
            if (!term) return true;

            const haystack = [item.name, item.categorie, item.description]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            return haystack.includes(term);
        });
    }, [fonctionRows, query]);

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

    const currentRows = {
        maintenanciers: filteredMaintenanciers,
        interventions: filteredMaintenanceRows,
        fonctions: filteredFonctionRows,
        types: filteredTypeRows,
    }[section];

    useEffect(() => {
        if (!currentRows.length) return;

        if (section === 'maintenanciers' && !currentRows.some((item) => item.id === selectedMaintenancierId)) {
            setSelectedMaintenancierId(currentRows[0].id);
        }

        if (section === 'interventions' && !currentRows.some((item) => item.id === selectedMaintenanceId)) {
            setSelectedMaintenanceId(currentRows[0].id);
        }

        if (section === 'types' && !currentRows.some((item) => item.id === selectedTypeId)) {
            setSelectedTypeId(currentRows[0].id);
        }

        if (section === 'fonctions' && !currentRows.some((item) => item.id === selectedFonctionId)) {
            setSelectedFonctionId(currentRows[0].id);
        }
    }, [
        currentRows,
        section,
        selectedFonctionId,
        selectedMaintenanceId,
        selectedMaintenancierId,
        selectedTypeId,
    ]);

    const selectedMaintenancier =
        filteredMaintenanciers.find((item) => item.id === selectedMaintenancierId) ??
        filteredMaintenanciers[0] ??
        maintenancierRows[0] ??
        null;

    const selectedMaintenance =
        filteredMaintenanceRows.find((item) => item.id === selectedMaintenanceId) ??
        filteredMaintenanceRows[0] ??
        maintenanceRows[0] ??
        null;

    const selectedType =
        filteredTypeRows.find((item) => item.id === selectedTypeId) ??
        filteredTypeRows[0] ??
        typeRows[0] ??
        null;

    const selectedFonction =
        filteredFonctionRows.find((item) => item.id === selectedFonctionId) ??
        filteredFonctionRows[0] ??
        fonctionRows[0] ??
        null;

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

        if (formKey === 'maintenancier') setMaintenancierForm(blankMaintenancierForm());
        if (formKey === 'fonction') setFonctionForm(blankFonctionForm());
        if (formKey === 'type') setTypeForm(blankTypeForm());
        if (formKey === 'intervention') setInterventionForm(blankInterventionForm());
    };

    const closeFormModal = () => {
        setActiveForm('');
        setFormFeedback(null);
        setSubmitting(false);
    };

    const submitForm = async (event) => {
        event.preventDefault();
        if (!activeForm) return;

        setSubmitting(true);
        setFormFeedback(null);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

        const payloadByForm = {
            maintenancier: {
                url: '/agence/maintenance/maintenancier/store',
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
                successMessage: 'Maintenancier créé avec succès.',
            },
            fonction: {
                url: '/agence/maintenance/fonction/store',
                body: {
                    name: fonctionForm.name,
                    description: fonctionForm.description,
                },
                successMessage: 'Fonction créée avec succès.',
            },
            type: {
                url: '/agence/maintenance/type/store',
                body: {
                    name: typeForm.name,
                    categorie: typeForm.categorie,
                    description: typeForm.description,
                    duree_estimee: typeForm.duree_estimee || null,
                },
                successMessage: 'Type de maintenance créé avec succès.',
            },
            intervention: {
                url: '/agence/maintenance',
                body: {
                    titre: interventionForm.titre,
                    description_generale: interventionForm.description_generale,
                    proprietaire_id: interventionForm.proprietaire_id,
                    prise_en_charge_par: interventionForm.prise_en_charge_par,
                    details: [
                        {
                            type_intervention_id: interventionForm.type_intervention_id,
                            maintenancier_id: interventionForm.maintenancier_id,
                            date_debut: interventionForm.date_debut,
                            date_fin: interventionForm.date_fin || null,
                            priorite: interventionForm.priorite,
                            prix: Number(interventionForm.prix || 0),
                            description: interventionForm.description,
                        },
                    ],
                },
                successMessage: 'Intervention créée avec succès.',
            },
        };

        const current = payloadByForm[activeForm];

        try {
            const response = await fetch(current.url, {
                method: 'POST',
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

                <div className="grid gap-6 xl:grid-cols-[360px_1fr]">
                    <Card className="rounded-3xl border-slate-200 shadow-sm">
                        <CardHeader className="space-y-4 border-b border-slate-200">
                            <CardTitle className="text-base">
                                {sectionConfig[section].label}{' '}
                                <span className="text-slate-400">{sectionConfig[section].count}</span>
                            </CardTitle>

                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" />
                                <Input
                                    value={query}
                                    onChange={(event) => setQuery(event.target.value)}
                                    placeholder={`Rechercher ${sectionConfig[section].label.toLowerCase()}...`}
                                    className="h-11 rounded-2xl bg-slate-50 pl-10"
                                />
                            </div>

                            
                        </CardHeader>

                        <CardContent className="p-3">
                            <ScrollArea className="h-[650px]">
                                <div className="space-y-2 pr-3">
                                    {section === 'maintenanciers' &&
                                        filteredMaintenanciers.map((item) => {
                                            const isSelected = selectedMaintenancier?.id === item.id;
                                            const meta = availabilityMeta(item.statut);

                                            return (
                                                <Button
                                                    key={item.id}
                                                    type="button"
                                                    variant="ghost"
                                                    onClick={() => setSelectedMaintenancierId(item.id)}
                                                    className={`h-auto w-full justify-start rounded-2xl border p-4 text-left ${
                                                        isSelected
                                                            ? 'border-[#00559b] bg-blue-50 hover:bg-blue-50'
                                                            : 'border-slate-200 bg-white hover:bg-slate-50'
                                                    }`}
                                                >
                                                    <div className="w-full space-y-3">
                                                        <div className="flex items-center justify-between gap-3">
                                                            <span className="text-xs font-semibold text-slate-500">
                                                                {item.entreprise}
                                                            </span>
                                                            <Badge variant={meta.variant}>{meta.label}</Badge>
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
                                                </Button>
                                            );
                                        })}

                                    {section === 'interventions' &&
                                        filteredMaintenanceRows.map((item) => {
                                            const isSelected = selectedMaintenance?.id === item.id;
                                            const meta = statusMeta(item.statut);
                                            const charge = chargeMeta(item.prise_en_charge_par);

                                            return (
                                                <Button
                                                    key={item.id}
                                                    type="button"
                                                    variant="ghost"
                                                    onClick={() => setSelectedMaintenanceId(item.id)}
                                                    className={`h-auto w-full justify-start rounded-2xl border p-4 text-left ${
                                                        isSelected
                                                            ? 'border-[#00559b] bg-blue-50 hover:bg-blue-50'
                                                            : 'border-slate-200 bg-white hover:bg-slate-50'
                                                    }`}
                                                >
                                                    <div className="w-full space-y-3">
                                                        <div className="flex items-center justify-between gap-3">
                                                            <span className="text-xs font-semibold text-slate-500">
                                                                {item.proprietaire}
                                                            </span>
                                                            <Badge variant={meta.variant}>{meta.label}</Badge>
                                                        </div>

                                                        <div className="min-w-0">
                                                            <p className="truncate text-sm font-semibold text-slate-900">
                                                                {item.titre}
                                                            </p>
                                                            <p className="mt-1 truncate text-xs text-slate-500">
                                                                {item.lot || item.batiment || item.porte || 'Localisation non definie'}
                                                            </p>
                                                        </div>

                                                        <div className="flex items-center justify-between gap-3 text-xs text-slate-500">
                                                            <span>{charge.label}</span>
                                                            <span>{currency(item.montant_global)}</span>
                                                        </div>
                                                    </div>
                                                </Button>
                                            );
                                        })}

                                    {section === 'fonctions' &&
                                        filteredFonctionRows.map((item) => {
                                            const isSelected = selectedFonction?.id === item.id;

                                            return (
                                                <Button
                                                    key={item.id}
                                                    type="button"
                                                    variant="ghost"
                                                    onClick={() => setSelectedFonctionId(item.id)}
                                                    className={`h-auto w-full justify-start rounded-2xl border p-4 text-left ${
                                                        isSelected
                                                            ? 'border-[#00559b] bg-blue-50 hover:bg-blue-50'
                                                            : 'border-slate-200 bg-white hover:bg-slate-50'
                                                    }`}
                                                >
                                                    <div className="w-full space-y-3">
                                                        <div className="flex items-center justify-between gap-3">
                                                            <span className="text-xs font-semibold text-slate-500">
                                                                {item.categorie}
                                                            </span>
                                                            <Badge variant="outline">
                                                                {item.maintenanciers_count} maintenancier(s)
                                                            </Badge>
                                                        </div>

                                                        <div className="min-w-0">
                                                            <p className="truncate text-sm font-semibold text-slate-900">
                                                                {item.name}
                                                            </p>
                                                            <p className="mt-1 truncate text-xs text-slate-500">
                                                                {item.description || 'Aucune description'}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </Button>
                                            );
                                        })}

                                    {section === 'types' &&
                                        filteredTypeRows.map((item) => {
                                            const isSelected = selectedType?.id === item.id;

                                            return (
                                                <Button
                                                    key={item.id}
                                                    type="button"
                                                    variant="ghost"
                                                    onClick={() => setSelectedTypeId(item.id)}
                                                    className={`h-auto w-full justify-start rounded-2xl border p-4 text-left ${
                                                        isSelected
                                                            ? 'border-[#00559b] bg-blue-50 hover:bg-blue-50'
                                                            : 'border-slate-200 bg-white hover:bg-slate-50'
                                                    }`}
                                                >
                                                    <div className="w-full space-y-3">
                                                        <div className="flex items-center justify-between gap-3">
                                                            <span className="text-xs font-semibold text-slate-500">
                                                                {item.categorie}
                                                            </span>
                                                            <Badge variant="outline">
                                                                {item.maintenances_count} intervention(s)
                                                            </Badge>
                                                        </div>

                                                        <div className="min-w-0">
                                                            <p className="truncate text-sm font-semibold text-slate-900">
                                                                {item.name}
                                                            </p>
                                                            <p className="mt-1 truncate text-xs text-slate-500">
                                                                {item.description || 'Aucune description'}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </Button>
                                            );
                                        })}

                                    {!currentRows.length && (
                                        <div className="p-6 text-center text-sm text-slate-500">
                                            Aucun element ne correspond a la recherche.
                                        </div>
                                    )}
                                </div>
                            </ScrollArea>
                        </CardContent>
                    </Card>

                    <Card className="min-h-[calc(100vh-260px)] rounded-3xl border-slate-200 shadow-sm">
                        {section === 'maintenanciers' && selectedMaintenancier ? (
                            <>
                                <CardHeader className="flex flex-col gap-4 border-b border-slate-200 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <CardDescription className="text-xs font-semibold uppercase tracking-[0.2em]">
                                            {selectedMaintenancier.entreprise}
                                        </CardDescription>
                                        <CardTitle className="mt-2 text-2xl">{selectedMaintenancier.name}</CardTitle>
                                        <p className="mt-2 text-sm text-slate-500">{selectedMaintenancier.fonction}</p>
                                    </div>

                                    <div className="flex flex-wrap gap-2">
                                        <Badge variant={availabilityMeta(selectedMaintenancier.statut).variant}>
                                            {availabilityMeta(selectedMaintenancier.statut).label}
                                        </Badge>
                                        <Badge variant="outline">{selectedMaintenancier.interventions_count} intervention(s)</Badge>
                                    </div>
                                </CardHeader>

                                <Tabs defaultValue="infos" className="w-full">
                                    <div className="border-slate-200 px-6 pt-5 mb-4">
                                        <TabsList>
                                            <TabsTrigger value="infos">Informations</TabsTrigger>
                                            <TabsTrigger value="interventions">Interventions</TabsTrigger>
                                        </TabsList>
                                    </div>

                                    <TabsContent value="infos" className="m-0">
                                        <CardContent className="space-y-6 p-6">
                                            <Card className="rounded-2xl border-slate-200">
                                                <CardContent className="mt-4 flex gap-4 p-5">
                                                    <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-900 text-lg font-bold text-white">
                                                        {initials(selectedMaintenancier.name, 'M')}
                                                    </div>

                                                    <div className="grid flex-1 gap-3 md:grid-cols-2">
                                                        <InfoBlock label="Raison sociale" value={selectedMaintenancier.entreprise} />
                                                        <InfoBlock label="Fonction" value={selectedMaintenancier.fonction} />
                                                        <InfoBlock label="Email" value={selectedMaintenancier.email || '---'} />
                                                        <InfoBlock label="Telephone" value={selectedMaintenancier.tel1 || '---'} />
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
                                                        <InfoBlock label="Telephone 2" value={selectedMaintenancier.tel2 || '---'} />
                                                        <InfoBlock label="Piece" value={selectedMaintenancier.type_piece || '---'} />
                                                        <InfoBlock label="Numero piece" value={selectedMaintenancier.numero_piece || '---'} />
                                                        <InfoBlock label="Date de validite" value={formatDate(selectedMaintenancier.date_validite_piece)} />
                                                        <InfoBlock label="Adresse" value={selectedMaintenancier.adresse || '---'} full />
                                                    </div>
                                                </CardContent>
                                            </Card>
                                        </CardContent>
                                    </TabsContent>

                                    <TabsContent value="interventions" className="m-0">
                                        <CardContent className="space-y-6 p-6">
                                            <div className="grid gap-4 md:grid-cols-4">
                                                <StatCard
                                                    label="Total"
                                                    value={selectedMaintenancier.interventions_count}
                                                    icon={ClipboardList}
                                                    accent="text-slate-600"
                                                />
                                                <StatCard
                                                    label="Disponibilite"
                                                    value={availabilityMeta(selectedMaintenancier.statut).label}
                                                    icon={Users}
                                                    accent="text-[#00559b]"
                                                />
                                                <StatCard
                                                    label="Fonction"
                                                    value={selectedMaintenancier.fonction}
                                                    icon={ShieldCheck}
                                                    accent="text-emerald-600"
                                                />
                                                <StatCard
                                                    label="Pieces"
                                                    value={selectedMaintenancier.type_piece || '---'}
                                                    icon={Tag}
                                                    accent="text-amber-600"
                                                />
                                            </div>

                                            <Card className="rounded-2xl border-slate-200">
                                                <CardContent className="mt-4 p-6 text-center text-sm text-slate-500">
                                                    Les interventions liees a ce maintenancier sont calculees depuis les
                                                    details de maintenance.
                                                </CardContent>
                                            </Card>
                                        </CardContent>
                                    </TabsContent>
                                </Tabs>
                            </>
                        ) : null}

                        {section === 'interventions' && selectedMaintenance ? (
                            <>
                                <CardHeader className="flex flex-col gap-4 border-b border-slate-200 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <CardDescription className="text-xs font-semibold uppercase tracking-[0.2em]">
                                            {selectedMaintenance.proprietaire}
                                        </CardDescription>
                                        <CardTitle className="mt-2 text-2xl">{selectedMaintenance.titre}</CardTitle>
                                        <p className="mt-2 text-sm text-slate-500">
                                            {selectedMaintenance.lot || selectedMaintenance.batiment || selectedMaintenance.porte || 'Localisation non definie'}
                                        </p>
                                    </div>

                                    <div className="flex flex-wrap gap-2">
                                        <Badge variant={statusMeta(selectedMaintenance.statut).variant}>
                                            {statusMeta(selectedMaintenance.statut).label}
                                        </Badge>
                                        <Badge variant={chargeMeta(selectedMaintenance.prise_en_charge_par).variant}>
                                            {chargeMeta(selectedMaintenance.prise_en_charge_par).label}
                                        </Badge>
                                    </div>
                                </CardHeader>

                                <Tabs defaultValue="infos" className="w-full">
                                    <div className="border-slate-200 px-6 pt-5 mb-4">
                                        <TabsList>
                                            <TabsTrigger value="infos">Informations</TabsTrigger>
                                            <TabsTrigger value="details">Details</TabsTrigger>
                                        </TabsList>
                                    </div>

                                    <TabsContent value="infos" className="m-0">
                                        <CardContent className="space-y-6 p-6">
                                            <div className="grid gap-4 md:grid-cols-4">
                                                <StatCard label="Montant" value={currency(selectedMaintenance.montant_global)} icon={Wrench} accent="text-[#00559b]" />
                                                <StatCard label="Details" value={selectedMaintenance.details_count} icon={ClipboardList} accent="text-slate-600" />
                                                <StatCard label="Debut" value={formatDate(selectedMaintenance.date_debut)} icon={Loader2} accent="text-amber-600" />
                                                <StatCard label="Fin" value={formatDate(selectedMaintenance.date_fin)} icon={Tag} accent="text-emerald-600" />
                                            </div>

                                            <Card className="rounded-2xl border-slate-200">
                                                <CardHeader>
                                                    <CardTitle className="text-sm uppercase tracking-[0.18em] text-slate-500">
                                                        Description
                                                    </CardTitle>
                                                </CardHeader>
                                                <CardContent>
                                                    <p className="text-sm leading-6 text-slate-700">
                                                        {selectedMaintenance.description || 'Aucune description'}
                                                    </p>
                                                </CardContent>
                                            </Card>
                                        </CardContent>
                                    </TabsContent>

                                    <TabsContent value="details" className="m-0">
                                        <CardContent className="space-y-3 p-6">
                                            {selectedMaintenance.details.length ? (
                                                selectedMaintenance.details.map((detail, index) => {
                                                    const detailStatus = statusMeta(detail?.statut);

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
                                                                    <p className="mt-1 text-xs text-slate-500">
                                                                        {detail?.maintenancier?.name ??
                                                                            detail?.maintenancier_id ??
                                                                            'Maintenancier non defini'}
                                                                    </p>
                                                                </div>
                                                                <Badge variant={detailStatus.variant}>{detailStatus.label}</Badge>
                                                            </div>

                                                            <div className="mt-3 grid gap-3 md:grid-cols-4">
                                                                <InfoBlock label="Prix" value={currency(detail?.montant ?? detail?.prix)} />
                                                                <InfoBlock label="Priorite" value={detail?.priorite ?? '---'} />
                                                                <InfoBlock label="Debut" value={formatDateTime(detail?.date_debut)} />
                                                                <InfoBlock label="Fin" value={formatDateTime(detail?.date_fin)} />
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
                                    </TabsContent>
                                </Tabs>
                            </>
                        ) : null}

                        {section === 'fonctions' && selectedFonction ? (
                            <>
                                <CardHeader className="flex flex-col gap-4 border-b border-slate-200 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <CardDescription className="text-xs font-semibold uppercase tracking-[0.2em]">
                                            {selectedFonction.categorie}
                                        </CardDescription>
                                        <CardTitle className="mt-2 text-2xl">{selectedFonction.name}</CardTitle>
                                        <p className="mt-2 text-sm text-slate-500">{selectedFonction.description || 'Aucune description'}</p>
                                    </div>

                                    <Badge variant="outline">{selectedFonction.maintenanciers_count} maintenancier(s)</Badge>
                                </CardHeader>

                                <CardContent className="space-y-6 p-6">
                                    <div className="grid gap-4 md:grid-cols-4">
                                        <StatCard label="Maintenanciers" value={selectedFonction.maintenanciers_count} icon={Users} accent="text-[#00559b]" />
                                        <StatCard label="Categorie" value={selectedFonction.categorie} icon={ShieldCheck} accent="text-slate-600" />
                                        <StatCard label="Description" value={selectedFonction.description ? 'Oui' : 'Non'} icon={ClipboardList} accent="text-amber-600" />
                                        <StatCard label="Section" value="Fonctions" icon={Tag} accent="text-emerald-600" />
                                    </div>

                                    <Card className="rounded-2xl border-slate-200">
                                        <CardContent className="mt-4 p-6 text-center text-sm text-slate-500">
                                            La liste des maintenanciers associes a cette fonction peut etre affichee depuis
                                            le filtre de gauche.
                                        </CardContent>
                                    </Card>
                                </CardContent>
                            </>
                        ) : null}

                        {section === 'types' && selectedType ? (
                            <>
                                <CardHeader className="flex flex-col gap-4 border-b border-slate-200 lg:flex-row lg:items-start lg:justify-between">
                                    <div>
                                        <CardDescription className="text-xs font-semibold uppercase tracking-[0.2em]">
                                            {selectedType.categorie}
                                        </CardDescription>
                                        <CardTitle className="mt-2 text-2xl">{selectedType.name}</CardTitle>
                                        <p className="mt-2 text-sm text-slate-500">{selectedType.description || 'Aucune description'}</p>
                                    </div>

                                    <Badge variant="outline">{selectedType.maintenances_count} intervention(s)</Badge>
                                </CardHeader>

                                <CardContent className="space-y-6 p-6">
                                    <div className="grid gap-4 md:grid-cols-4">
                                        <StatCard label="Interventions" value={selectedType.maintenances_count} icon={ClipboardList} accent="text-[#00559b]" />
                                        <StatCard label="Categorie" value={selectedType.categorie} icon={Tag} accent="text-slate-600" />
                                        <StatCard label="Duree estimee" value={selectedType.duree_estimee ?? '---'} icon={Loader2} accent="text-amber-600" />
                                        <StatCard label="Section" value="Types" icon={ShieldCheck} accent="text-emerald-600" />
                                    </div>

                                    <Card className="rounded-2xl border-slate-200">
                                        <CardContent className="mt-4 p-6 text-center text-sm text-slate-500">
                                            Les usages de ce type sont bases sur les lignes de maintenance enregistrees.
                                        </CardContent>
                                    </Card>
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
                                <DialogTitle className="text-[#0f172a]">Nouveau maintenancier</DialogTitle>
                                <DialogDescription className="text-[#5f7182]">
                                    Ajoutez un nouveau prestataire de maintenance.
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
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                        placeholder="Nom complet"
                                    />
                                </Field>

                                <Field label="Entreprise">
                                    <Input
                                        value={maintenancierForm.entreprise}
                                        onChange={(event) => setMaintenancierForm((current) => ({ ...current, entreprise: event.target.value }))}
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                        placeholder="Entreprise ou indépendant"
                                    />
                                </Field>

                                <Field label="Fonction" required>
                                    <Select
                                        value={maintenancierForm.fonction_maintenance_id}
                                        onValueChange={(value) => setMaintenancierForm((current) => ({ ...current, fonction_maintenance_id: value }))}
                                    >
                                        <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
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
                                        <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
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

                                <Field label="Téléphone 1" required>
                                    <Input
                                        value={maintenancierForm.tel1}
                                        onChange={(event) => setMaintenancierForm((current) => ({ ...current, tel1: event.target.value }))}
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                        placeholder="Téléphone principal"
                                    />
                                </Field>

                                <Field label="Téléphone 2">
                                    <Input
                                        value={maintenancierForm.tel2}
                                        onChange={(event) => setMaintenancierForm((current) => ({ ...current, tel2: event.target.value }))}
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                        placeholder="Téléphone secondaire"
                                    />
                                </Field>

                                <Field label="Email">
                                    <Input
                                        type="email"
                                        value={maintenancierForm.email}
                                        onChange={(event) => setMaintenancierForm((current) => ({ ...current, email: event.target.value }))}
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                        placeholder="Adresse email"
                                    />
                                </Field>

                                <Field label="Numéro de pièce" required>
                                    <Input
                                        value={maintenancierForm.numero_piece}
                                        onChange={(event) => setMaintenancierForm((current) => ({ ...current, numero_piece: event.target.value }))}
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                        placeholder="Numéro CNI / passeport"
                                    />
                                </Field>

                                <Field label="Date de validité">
                                    <Input
                                        type="date"
                                        value={maintenancierForm.date_validite_piece}
                                        onChange={(event) => setMaintenancierForm((current) => ({ ...current, date_validite_piece: event.target.value }))}
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                    />
                                </Field>

                                <Field label="Statut">
                                    <Select
                                        value={maintenancierForm.statut}
                                        onValueChange={(value) => setMaintenancierForm((current) => ({ ...current, statut: value }))}
                                    >
                                        <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                            <SelectValue placeholder="Sélectionner" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="1">Actif</SelectItem>
                                            <SelectItem value="0">Inactif</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field label="Adresse" className="md:col-span-2">
                                    <textarea
                                        value={maintenancierForm.adresse}
                                        onChange={(event) => setMaintenancierForm((current) => ({ ...current, adresse: event.target.value }))}
                                        rows={3}
                                        className="min-h-[110px] rounded-xl border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] outline-none transition focus:border-[#00559b]"
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
                                    {submitting ? 'Enregistrement...' : 'Créer'}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>

                <Dialog open={activeForm === 'fonction'} onOpenChange={(open) => (!open ? closeFormModal() : null)}>
                    <DialogContent className="sm:max-w-xl max-h-[90vh] overflow-hidden p-0">
                        <div className="border-b border-[#c8d4de] px-5 py-4">
                            <DialogHeader>
                                <DialogTitle className="text-[#0f172a]">Nouvelle fonction</DialogTitle>
                                <DialogDescription className="text-[#5f7182]">
                                    Définissez une fonction de maintenance.
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
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                        placeholder="Ex. Plomberie"
                                    />
                                </Field>

                                <Field label="Description">
                                    <textarea
                                        value={fonctionForm.description}
                                        onChange={(event) => setFonctionForm((current) => ({ ...current, description: event.target.value }))}
                                        rows={4}
                                        className="min-h-[120px] rounded-xl border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] outline-none transition focus:border-[#00559b]"
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
                                    {submitting ? 'Enregistrement...' : 'Créer'}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>

                <Dialog open={activeForm === 'type'} onOpenChange={(open) => (!open ? closeFormModal() : null)}>
                    <DialogContent className="sm:max-w-xl max-h-[90vh] overflow-hidden p-0">
                        <div className="border-b border-[#c8d4de] px-5 py-4">
                            <DialogHeader>
                                <DialogTitle className="text-[#0f172a]">Nouveau type</DialogTitle>
                                <DialogDescription className="text-[#5f7182]">
                                    Déclarez un type d&apos;intervention.
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
                                        value={typeForm.name}
                                        onChange={(event) => setTypeForm((current) => ({ ...current, name: event.target.value }))}
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                        placeholder="Ex. Électricité"
                                    />
                                </Field>

                                <Field label="Catégorie">
                                    <Input
                                        value={typeForm.categorie}
                                        onChange={(event) => setTypeForm((current) => ({ ...current, categorie: event.target.value }))}
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                        placeholder="Catégorie"
                                    />
                                </Field>

                                <Field label="Durée estimée">
                                    <Input
                                        type="number"
                                        min="0"
                                        value={typeForm.duree_estimee}
                                        onChange={(event) => setTypeForm((current) => ({ ...current, duree_estimee: event.target.value }))}
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                        placeholder="En heures"
                                    />
                                </Field>

                                <Field label="Description" className="md:col-span-2">
                                    <textarea
                                        value={typeForm.description}
                                        onChange={(event) => setTypeForm((current) => ({ ...current, description: event.target.value }))}
                                        rows={4}
                                        className="min-h-[120px] rounded-xl border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] outline-none transition focus:border-[#00559b]"
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
                                    {submitting ? 'Enregistrement...' : 'Créer'}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>

                <Dialog open={activeForm === 'intervention'} onOpenChange={(open) => (!open ? closeFormModal() : null)}>
                    <DialogContent className="sm:max-w-3xl max-h-[90vh] overflow-hidden p-0">
                        <div className="border-b border-[#c8d4de] px-5 py-4">
                            <DialogHeader>
                                <DialogTitle className="text-[#0f172a]">Nouvelle intervention</DialogTitle>
                                <DialogDescription className="text-[#5f7182]">
                                    Programmez une intervention avec un détail de travail.
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
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                        placeholder="Ex. Réparation fuite"
                                    />
                                </Field>

                                <Field label="Propriétaire" required>
                                    <Select
                                        value={interventionForm.proprietaire_id}
                                        onValueChange={(value) => setInterventionForm((current) => ({ ...current, proprietaire_id: value }))}
                                    >
                                        <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                            <SelectValue placeholder="Sélectionner" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {proprietorOptions.map((item) => (
                                                <SelectItem key={item.value} value={item.value}>
                                                    {item.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field label="Prise en charge">
                                    <Select
                                        value={interventionForm.prise_en_charge_par}
                                        onValueChange={(value) => setInterventionForm((current) => ({ ...current, prise_en_charge_par: value }))}
                                    >
                                        <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                            <SelectValue placeholder="Sélectionner" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="proprietaire">Propriétaire</SelectItem>
                                            <SelectItem value="locataire">Locataire</SelectItem>
                                            <SelectItem value="agence">Agence</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field label="Type d'intervention" required>
                                    <Select
                                        value={interventionForm.type_intervention_id}
                                        onValueChange={(value) => setInterventionForm((current) => ({ ...current, type_intervention_id: value }))}
                                    >
                                        <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                            <SelectValue placeholder="Sélectionner" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {typeInterventionOptions.map((item) => (
                                                <SelectItem key={item.value} value={item.value}>
                                                    {item.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field label="Maintenancier" required>
                                    <Select
                                        value={interventionForm.maintenancier_id}
                                        onValueChange={(value) => setInterventionForm((current) => ({ ...current, maintenancier_id: value }))}
                                    >
                                        <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                            <SelectValue placeholder="Sélectionner" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {maintenancierOptions.map((item) => (
                                                <SelectItem key={item.value} value={item.value}>
                                                    {item.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field label="Date de début" required>
                                    <Input
                                        type="date"
                                        value={interventionForm.date_debut}
                                        onChange={(event) => setInterventionForm((current) => ({ ...current, date_debut: event.target.value }))}
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                    />
                                </Field>

                                <Field label="Date de fin">
                                    <Input
                                        type="date"
                                        value={interventionForm.date_fin}
                                        onChange={(event) => setInterventionForm((current) => ({ ...current, date_fin: event.target.value }))}
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                    />
                                </Field>

                                <Field label="Priorité">
                                    <Select
                                        value={interventionForm.priorite}
                                        onValueChange={(value) => setInterventionForm((current) => ({ ...current, priorite: value }))}
                                    >
                                        <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
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
                                        value={interventionForm.prix}
                                        onChange={(event) => setInterventionForm((current) => ({ ...current, prix: event.target.value }))}
                                        className="h-11 rounded-xl border-[#c8d4de]"
                                        placeholder="Montant"
                                    />
                                </Field>

                                <Field label="Description" className="md:col-span-2">
                                    <textarea
                                        value={interventionForm.description}
                                        onChange={(event) => setInterventionForm((current) => ({ ...current, description: event.target.value }))}
                                        rows={3}
                                        className="min-h-[110px] rounded-xl border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] outline-none transition focus:border-[#00559b]"
                                        placeholder="Détail du travail"
                                    />
                                </Field>

                                <Field label="Description générale" className="md:col-span-2">
                                    <textarea
                                        value={interventionForm.description_generale}
                                        onChange={(event) => setInterventionForm((current) => ({ ...current, description_generale: event.target.value }))}
                                        rows={3}
                                        className="min-h-[110px] rounded-xl border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] outline-none transition focus:border-[#00559b]"
                                        placeholder="Contexte général"
                                    />
                                </Field>
                                </div>
                            </div>

                            <div className="mb-6 flex shrink-0 flex-col gap-3 border-t border-[#e2e8f0] bg-white px-5 py-4 pb-6 sm:flex-row sm:justify-end">
                                <Button type="button" variant="outline" onClick={closeFormModal} className="rounded-xl border-[#c8d4de]">
                                    Annuler
                                </Button>
                                <Button type="submit" className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]" disabled={submitting}>
                                    {submitting ? 'Enregistrement...' : 'Créer'}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </section>
        </AgenceLayout>
    );
}
