import { useEffect, useMemo, useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import {
    ArrowDownCircle,
    ArrowUpCircle,
    Banknote,
    Calendar,
    CreditCard,
    Download,
    Eye,
    Home,
    History,
    Plus,
    ShoppingBag,
    Smartphone,
    Wallet,
    Wrench,
    X,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '../../../components/ui/card';
import { DataTable } from '../../../components/ui/data-table';
import { DataTableColumnHeader } from '../../../components/ui/data-table-column-header';
import { cn } from '../../../lib/utils';
import { agenceButtonStyles } from '../../../lib/buttonStyles';

const currency = (value) =>
    new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const number = (value) => new Intl.NumberFormat('fr-FR').format(Number(value ?? 0));

const TABS = [
    { id: 'transactions', label: 'Transactions', icon: CreditCard },
    { id: 'loyers', label: 'Loyers', icon: Home },
    { id: 'maintenance', label: 'Maintenance', icon: Wrench },
    { id: 'depenses', label: 'Dépenses agence', icon: ShoppingBag },
    { id: 'ventes', label: 'Vente de biens', icon: Banknote },
    { id: 'summary', label: 'Résumé par mode de paiement', icon: Wallet },
];

const MOUVEMENT_OPTIONS = [
    {
        title: 'Paiement loyer',
        description: 'Enregistrer un paiement de loyer',
        href: '/agence/caisse/loyer',
        icon: Home,
        accent: 'bg-[#eaf4fb] text-[#00559b]',
    },
    {
        title: 'Vente de biens',
        description: 'Enregistrer une vente de bien immobilier',
        href: '/agence/caisse/vente-bien',
        icon: Banknote,
        accent: 'bg-[#eef8df] text-[#4d8500]',
    },
    {
        title: 'Maintenance',
        description: 'Enregistrer une dépense maintenance',
        href: '/agence/caisse/maintenance',
        icon: Wrench,
        accent: 'bg-[#f1f5f9] text-[#5f7182]',
    },
    {
        title: 'Dépense agence',
        description: "Enregistrer une dépense générale de l'agence",
        href: '/agence/caisse/depense-agence',
        icon: ShoppingBag,
        accent: 'bg-[#0f172a]/5 text-[#0f172a]',
    },
];

const PAYMENT_ICONS = {
    Wallet,
    Smartphone,
    CreditCard,
    Banknote,
};

export default function Caisse({
    caisseOuverte: caisseOuverteProp = true,
    soldeOuverture = 0,
    totalEntrees = 0,
    totalSorties = 0,
    transactions = [
        { id: 'TRX-0001', date: '11/05/2026', time: '09:30', type: 'in', label: 'Paiement loyer — Kouamé Jean (Appt. B2, Mai 2026)', reference: 'TRX-0001', amount: 85000 },
        { id: 'TRX-0002', date: '11/05/2026', time: '11:00', type: 'out', label: 'Paiement facture — CIE (FAC-2026-0041, Électricité)', reference: 'TRX-0002', amount: 28000 },
        { id: 'TRX-0003', date: '11/05/2026', time: '08:00', type: 'out', label: 'Maintenance — Plomberie, Villa C3 Riviera (Kouassi & Fils)', reference: 'TRX-0003', amount: 15000 },
        { id: 'TRX-0004', date: '11/05/2026', time: '14:20', type: 'out', label: 'Dépense — Fournitures, Ramettes de papier A4', reference: 'TRX-0004', amount: 4500 },
    ],
    loyers = [
        { id: 1, date: '11/05/2026', time: '09:30', tenant: 'Kouamé Jean', owner: 'M. Kouassi', lot: 'Lot 12', door: 'Porte B2', period: 'Mai 2026', amount: 85000, mode: 'Espèces' },
    ],
    maintenance = [
        { id: 1, date: '11/05/2026', time: '08:00', owner: 'M. Kouassi', lot: 'Lot 12', door: 'Porte C3', type: 'Plomberie', provider: 'Kouassi & Fils', cost: 15000, status: 'Terminée' },
    ],
    depenses = [
        { id: 1, date: '11/05/2026', time: '14:20', category: 'Fournitures', label: 'Ramettes de papier A4', proof: 'Reçu', amount: 4500 },
        { id: 2, date: '11/05/2026', time: '11:00', category: 'Paiement facture', label: 'CIE — FAC-2026-0041, Électricité', proof: 'Facture', amount: 28000 },
    ],
    ventes = [
        { id: 1, date: '11/05/2026', time: '15:00', client: 'Koné Moussa', property: 'Terrain — Bingerville', reference: 'VTE-0001', amount: 2500000, status: 'Payée' },
    ],
    summary = [
        { mode: 'Espèces', total: 54000, count: 2, commission: 5400, net: 48600, icon: Wallet, accent: 'bg-[#eaf4fb] text-[#00559b]' },
        { mode: 'WAVE', total: 50000, count: 1, commission: 5000, net: 45000, icon: Smartphone, accent: 'bg-[#00559b]/10 text-[#00559b]' },
        { mode: 'Orange Money', total: 50000, count: 1, commission: 5000, net: 45000, icon: CreditCard, accent: 'bg-[#fff2e6] text-[#c2410c]' },
    ],
}) {
    const { currentAgency, flash = {} } = usePage().props;
    const isDemo = Boolean(currentAgency?.is_demo);
    const [demoCaisseOuverte, setDemoCaisseOuverte] = useState(caisseOuverteProp);
    const [demoNotice, setDemoNotice] = useState('');
    const caisseOuverte = isDemo ? demoCaisseOuverte : caisseOuverteProp;
    const [openCashForm, setOpenCashForm] = useState(false);
    const [closeCashForm, setCloseCashForm] = useState(false);
    const [activeTab, setActiveTab] = useState('transactions');
    const [txFilter, setTxFilter] = useState('all');
    const [mouvementOpen, setMouvementOpen] = useState(false);
    const ouvertureForm = useForm({
        solde_ouverture: soldeOuverture,
        observation: '',
    });
    const fermetureForm = useForm({
        solde_fermeture: soldeOuverture + totalEntrees - totalSorties,
        observation: '',
    });

    useEffect(() => {
        setDemoCaisseOuverte(caisseOuverteProp);
        setDemoNotice('');
        setOpenCashForm(false);
        setCloseCashForm(false);
        ouvertureForm.clearErrors();
        fermetureForm.clearErrors();
    }, [caisseOuverteProp]);

    const ouvrirCaisse = () => {
        if (!isDemo) {
            ouvertureForm.post('/agence/caisse/ouvrir');
            return;
        }

        if (Number(ouvertureForm.data.solde_ouverture) < 0) {
            ouvertureForm.setError('solde_ouverture', "Le solde d'ouverture doit être positif ou nul.");
            return;
        }

        ouvertureForm.clearErrors();
        setDemoCaisseOuverte(true);
        setOpenCashForm(false);
        setDemoNotice("Simulation réussie : la caisse est ouverte. Aucune donnée n'a été enregistrée.");
    };

    const fermerCaisse = () => {
        if (!isDemo) {
            fermetureForm.post('/agence/caisse/fermer');
            return;
        }

        if (Number(fermetureForm.data.solde_fermeture) < 0) {
            fermetureForm.setError('solde_fermeture', 'Le solde de fermeture doit être positif ou nul.');
            return;
        }

        fermetureForm.clearErrors();
        setDemoCaisseOuverte(false);
        setCloseCashForm(false);
        setDemoNotice("Simulation réussie : la caisse est fermée. Aucune donnée n'a été enregistrée.");
    };

    const soldeTheorique = soldeOuverture + totalEntrees - totalSorties;

    const stats = [
        { label: 'Solde d\'ouverture', value: currency(soldeOuverture), icon: Wallet, accent: 'bg-[#eaf4fb] text-[#00559b]' },
        { label: 'Total entrées', value: currency(totalEntrees), icon: ArrowUpCircle, accent: 'bg-[#eef8df] text-[#4d8500]' },
        { label: 'Total sorties', value: currency(totalSorties), icon: ArrowDownCircle, accent: 'bg-[#fdecec] text-[#b42318]' },
        { label: 'Solde théorique', value: currency(soldeTheorique), icon: Banknote, accent: 'bg-[#eaf4fb] text-[#00559b]' },
    ];

    const filteredTransactions = useMemo(() => {
        if (txFilter === 'in') return transactions.filter((t) => t.type === 'in');
        if (txFilter === 'out') return transactions.filter((t) => t.type === 'out');
        return transactions;
    }, [transactions, txFilter]);

    const summaryTotals = summary.reduce(
        (acc, row) => ({
            entries: acc.entries + Number(row.entries || 0),
            outputs: acc.outputs + Number(row.outputs || 0),
            balance: acc.balance + Number(row.balance || 0),
            count: acc.count + row.count,
            entries_count: acc.entries_count + Number(row.entries_count || 0),
            outputs_count: acc.outputs_count + Number(row.outputs_count || 0),
        }),
        { entries: 0, outputs: 0, balance: 0, count: 0, entries_count: 0, outputs_count: 0 }
    );

    const transactionColumns = useMemo(
        () => [
            {
                id: 'date',
                accessorFn: (row) => `${row.date} ${row.time}`,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Date"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Date' },
                cell: ({ row }) => (
                    <div>
                        <span className="block text-sm text-[#0f172a]">{row.original.date}</span>
                        <span className="block text-xs text-[#5f7182]">{row.original.time}</span>
                    </div>
                ),
            },
            {
                id: 'type',
                accessorFn: (row) => row.type,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Type"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Type' },
                cell: ({ row }) => (
                    <Tone tone={row.original.type === 'in' ? 'success' : 'danger'}>
                        {row.original.type === 'in' ? 'Entrée' : 'Sortie'}
                    </Tone>
                ),
            },
            {
                id: 'label',
                accessorFn: (row) => row.label,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Libellé"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Libellé' },
                cell: ({ row }) => row.original.label,
            },
            {
                id: 'entry',
                accessorFn: (row) => (row.type === 'in' ? row.amount : 0),
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Entrée"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="justify-end"
                    />
                ),
                meta: { label: 'Entrée', headerClassName: 'text-right', cellClassName: 'text-right' },
                cell: ({ row }) =>
                    row.original.type === 'in' ? (
                        <span className="font-semibold text-[#4d8500]">+ {currency(row.original.amount)}</span>
                    ) : (
                        '—'
                    ),
            },
            {
                id: 'exit',
                accessorFn: (row) => (row.type === 'out' ? row.amount : 0),
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Sortie"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="justify-end"
                    />
                ),
                meta: { label: 'Sortie', headerClassName: 'text-right', cellClassName: 'text-right' },
                cell: ({ row }) =>
                    row.original.type === 'out' ? (
                        <span className="font-semibold text-[#b42318]">- {currency(row.original.amount)}</span>
                    ) : (
                        '—'
                    ),
            },
            {
                id: 'actions',
                header: () => <span className="block text-right">Actions</span>,
                enableHiding: false,
                meta: { label: 'Actions', headerClassName: 'text-right', cellClassName: 'text-right whitespace-nowrap' },
                cell: () => <ViewButton />,
            },
        ],
        []
    );

    const loyerColumns = useMemo(
        () => [
            {
                id: 'date',
                accessorFn: (row) => `${row.date} ${row.time}`,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Date"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Date' },
                cell: ({ row }) => (
                    <div>
                        <span className="block text-sm text-[#0f172a]">{row.original.date}</span>
                        <span className="block text-xs text-[#5f7182]">{row.original.time}</span>
                    </div>
                ),
            },
            {
                id: 'tenant',
                accessorFn: (row) => row.tenant,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Locataire"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Locataire' },
                cell: ({ row }) => row.original.tenant,
            },
            {
                id: 'owner',
                accessorFn: (row) => row.owner,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Propriétaire"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Propriétaire' },
                cell: ({ row }) => row.original.owner,
            },
            {
                id: 'lot',
                accessorFn: (row) => row.lot,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Lot"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Lot' },
                cell: ({ row }) => row.original.lot,
            },
            {
                id: 'door',
                accessorFn: (row) => row.door,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Porte"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Porte' },
                cell: ({ row }) => row.original.door,
            },
            {
                id: 'period',
                accessorFn: (row) => row.period,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Période"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Période' },
                cell: ({ row }) => row.original.period,
            },
            {
                id: 'amount',
                accessorFn: (row) => row.amount,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Montant"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="justify-end"
                    />
                ),
                meta: { label: 'Montant', headerClassName: 'text-right', cellClassName: 'text-right' },
                cell: ({ row }) => <span className="font-semibold text-[#4d8500]">{currency(row.original.amount)}</span>,
            },
            {
                id: 'breakdown',
                accessorFn: (row) => row.breakdown?.map((item) => item.label).join(' ') ?? '',
                header: () => <span>Détail de l’encaissement</span>,
                meta: { label: 'Détail de l’encaissement' },
                cell: ({ row }) => row.original.breakdown?.length ? (
                    <div className="min-w-56 space-y-1">
                        {row.original.breakdown.map((item) => (
                            <div key={item.label} className="flex items-center justify-between gap-3 text-xs">
                                <span className="text-[#5f7182]">{item.label}</span>
                                <span className="font-medium text-[#0f172a]">{currency(item.amount)}</span>
                            </div>
                        ))}
                    </div>
                ) : <span className="text-xs text-[#94a3b8]">Loyer périodique</span>,
            },
            {
                id: 'mode',
                accessorFn: (row) => row.mode,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Mode"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Mode' },
                cell: ({ row }) => <Tone tone="info">{row.original.mode}</Tone>,
            },
            {
                id: 'actions',
                header: () => <span className="block text-right">Actions</span>,
                enableHiding: false,
                meta: { label: 'Actions', headerClassName: 'text-right', cellClassName: 'text-right whitespace-nowrap' },
                cell: ({ row }) => (
                    <a
                        href={row.original.receipt_url}
                        download
                        className="inline-flex items-center gap-1 rounded-lg border border-[#c8d4de] px-2.5 py-1.5 text-xs font-medium text-[#00559b] transition hover:bg-[#eaf4fb]"
                        title={`Télécharger le reçu ${row.original.receipt_number}`}
                    >
                        <Download className="h-3.5 w-3.5" />
                        Reçu
                    </a>
                ),
            },
        ],
        []
    );

    const maintenanceColumns = useMemo(
        () => [
            {
                id: 'date',
                accessorFn: (row) => `${row.date} ${row.time}`,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Date"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Date' },
                cell: ({ row }) => (
                    <div>
                        <span className="block text-sm text-[#0f172a]">{row.original.date}</span>
                        <span className="block text-xs text-[#5f7182]">{row.original.time}</span>
                    </div>
                ),
            },
            {
                id: 'property',
                accessorFn: (row) => row.property,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Bien"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Bien' },
                cell: ({ row }) => row.original.property,
            },
            {
                id: 'type',
                accessorFn: (row) => row.type,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Type"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Type' },
                cell: ({ row }) => row.original.type,
            },
            {
                id: 'provider',
                accessorFn: (row) => row.provider,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Prestataire"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Prestataire' },
                cell: ({ row }) => row.original.provider,
            },
            {
                id: 'cost',
                accessorFn: (row) => row.cost,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Coût"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="justify-end"
                    />
                ),
                meta: { label: 'Coût', headerClassName: 'text-right', cellClassName: 'text-right' },
                cell: ({ row }) => <span className="font-semibold text-[#b42318]">{currency(row.original.cost)}</span>,
            },
            {
                id: 'status',
                accessorFn: (row) => row.status,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Statut"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Statut' },
                cell: ({ row }) => <Tone tone="success">{row.original.status}</Tone>,
            },
            {
                id: 'actions',
                header: () => <span className="block text-right">Actions</span>,
                enableHiding: false,
                meta: { label: 'Actions', headerClassName: 'text-right', cellClassName: 'text-right whitespace-nowrap' },
                cell: () => <ViewButton />,
            },
        ],
        []
    );

    const depenseColumns = useMemo(
        () => [
            {
                id: 'date',
                accessorFn: (row) => `${row.date} ${row.time}`,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Date"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Date' },
                cell: ({ row }) => (
                    <div>
                        <span className="block text-sm text-[#0f172a]">{row.original.date}</span>
                        <span className="block text-xs text-[#5f7182]">{row.original.time}</span>
                    </div>
                ),
            },
            {
                id: 'category',
                accessorFn: (row) => row.category,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Catégorie"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Catégorie' },
                cell: ({ row }) => row.original.category,
            },
            {
                id: 'label',
                accessorFn: (row) => row.label,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Libellé"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Libellé' },
                cell: ({ row }) => row.original.label,
            },
            {
                id: 'proof',
                accessorFn: (row) => row.proof,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Justificatif"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Justificatif' },
                cell: ({ row }) => <Tone tone="info">{row.original.proof}</Tone>,
            },
            {
                id: 'amount',
                accessorFn: (row) => row.amount,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Montant"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="justify-end"
                    />
                ),
                meta: { label: 'Montant', headerClassName: 'text-right', cellClassName: 'text-right' },
                cell: ({ row }) => <span className="font-semibold text-[#b42318]">{currency(row.original.amount)}</span>,
            },
            {
                id: 'actions',
                header: () => <span className="block text-right">Actions</span>,
                enableHiding: false,
                meta: { label: 'Actions', headerClassName: 'text-right', cellClassName: 'text-right whitespace-nowrap' },
                cell: () => <ViewButton />,
            },
        ],
        []
    );

    const venteColumns = useMemo(
        () => [
            {
                id: 'date',
                accessorFn: (row) => `${row.date} ${row.time}`,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Date"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Date' },
                cell: ({ row }) => (
                    <div>
                        <span className="block text-sm text-[#0f172a]">{row.original.date}</span>
                        <span className="block text-xs text-[#5f7182]">{row.original.time}</span>
                    </div>
                ),
            },
            {
                id: 'client',
                accessorFn: (row) => row.client,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Client"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Client' },
                cell: ({ row }) => (
                    <div>
                        <span className="block font-medium text-[#0f172a]">{row.original.client}</span>
                        <span className="mt-0.5 block text-xs text-[#5f7182]">{row.original.client_phone}</span>
                    </div>
                ),
            },
            {
                id: 'owner',
                accessorFn: (row) => row.owner,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Propriétaire"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Propriétaire' },
                cell: ({ row }) => row.original.owner,
            },
            {
                id: 'property',
                accessorFn: (row) => row.property,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Bien"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Bien' },
                cell: ({ row }) => row.original.property,
            },
            {
                id: 'amount',
                accessorFn: (row) => row.amount,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Montant"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="justify-end"
                    />
                ),
                meta: { label: 'Montant', headerClassName: 'text-right', cellClassName: 'text-right' },
                cell: ({ row }) => <span className="font-semibold text-[#4d8500]">{currency(row.original.amount)}</span>,
            },
            {
                id: 'status',
                accessorFn: (row) => row.status,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Statut"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Statut' },
                cell: ({ row }) => <Tone tone="success">{row.original.status}</Tone>,
            },
            {
                id: 'actions',
                header: () => <span className="block text-right">Actions</span>,
                enableHiding: false,
                meta: { label: 'Actions', headerClassName: 'text-right', cellClassName: 'text-right whitespace-nowrap' },
                cell: ({ row }) => (
                    <a
                        href={row.original.receipt_url}
                        download
                        className="inline-flex items-center gap-1 rounded-lg border border-[#c8d4de] px-2.5 py-1.5 text-xs font-medium text-[#00559b] transition hover:bg-[#eaf4fb]"
                        title={`Télécharger le reçu ${row.original.receipt_number}`}
                    >
                        <Download className="h-3.5 w-3.5" />
                        Reçu
                    </a>
                ),
            },
        ],
        []
    );

    const summaryRows = useMemo(() => {
        if (!summary.length) return [];

        return [
            ...summary.map((row) => ({
                ...row,
                isTotal: false,
            })),
            {
                mode: 'TOTAL',
                entries: summaryTotals.entries,
                outputs: summaryTotals.outputs,
                balance: summaryTotals.balance,
                count: summaryTotals.count,
                entries_count: summaryTotals.entries_count,
                outputs_count: summaryTotals.outputs_count,
                isTotal: true,
            },
        ];
    }, [summary, summaryTotals]);

    const summaryColumns = useMemo(
        () => [
            {
                id: 'mode',
                accessorFn: (row) => row.mode,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Mode de paiement"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Mode de paiement' },
                cell: ({ row }) => (
                    <span className={cn('font-medium text-[#0f172a]', row.original.isTotal && 'font-semibold')}>
                        {row.original.mode}
                    </span>
                ),
            },
            {
                id: 'entries',
                accessorFn: (row) => row.entries,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Entrées"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="justify-end"
                    />
                ),
                meta: { label: 'Entrées', headerClassName: 'text-right', cellClassName: 'text-right' },
                cell: ({ row }) => (
                    <span className={cn('text-[#4d8500]', row.original.isTotal && 'font-semibold')}>
                        + {currency(row.original.entries)}
                    </span>
                ),
            },
            {
                id: 'count',
                accessorFn: (row) => row.count,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Nb transactions"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="justify-end"
                    />
                ),
                meta: { label: 'Nb transactions', headerClassName: 'text-right', cellClassName: 'text-right' },
                cell: ({ row }) => (
                    <span className={cn('text-[#0f172a]', row.original.isTotal && 'font-semibold')}>
                        {number(row.original.count)}
                    </span>
                ),
            },
            {
                id: 'outputs',
                accessorFn: (row) => row.outputs,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Sorties"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="justify-end"
                    />
                ),
                meta: { label: 'Sorties', headerClassName: 'text-right', cellClassName: 'text-right' },
                cell: ({ row }) => (
                    <span className={cn('text-[#b42318]', row.original.isTotal && 'font-semibold')}>
                        - {currency(row.original.outputs)}
                    </span>
                ),
            },
            {
                id: 'balance',
                accessorFn: (row) => row.balance,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Solde net"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="justify-end"
                    />
                ),
                meta: { label: 'Solde net', headerClassName: 'text-right', cellClassName: 'text-right' },
                cell: ({ row }) => (
                    <span className={cn(Number(row.original.balance) >= 0 ? 'text-[#00559b]' : 'text-[#b42318]', row.original.isTotal && 'font-semibold')}>
                        {currency(row.original.balance)}
                    </span>
                ),
            },
        ],
        [summaryTotals]
    );

    return (
        <AgenceLayout title="Caisse">
            <Head title="Caisse" />

            <div className="mx-auto flex w-full max-w-7xl flex-col gap-6">
                {isDemo ? (
                    <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Mode démonstration : l’ouverture et la fermeture de la caisse sont simulées dans cette page et ne sont pas enregistrées.
                    </div>
                ) : null}

                {demoNotice ? (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {demoNotice}
                    </div>
                ) : null}

                {flash.cash_report_url ? (
                    <div className="flex flex-col gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 sm:flex-row sm:items-center sm:justify-between">
                        <span>La caisse est clôturée. Le rapport des activités de cette session est prêt.</span>
                        <Button asChild size="sm" className={agenceButtonStyles.primary}>
                            <a href={flash.cash_report_url} target="_blank" rel="noreferrer"><Download className="h-4 w-4" /> Télécharger le rapport PDF</a>
                        </Button>
                    </div>
                ) : null}

                {/* En-tête */}
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                       
                        <h2 className="text-2xl font-semibold text-[#0f172a]">Gestion financière</h2>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        {caisseOuverte ? (
                            <>
                            <Button asChild variant="outline" className={agenceButtonStyles.outline}>
                                <Link href="/agence/caisse/historique">
                                    <History className="h-4 w-4" />
                                    Historique
                                </Link>
                            </Button>
                            <Button variant="outline" className={agenceButtonStyles.outline}>
                                <Calendar className="h-4 w-4" />
                                {new Date().toLocaleDateString('fr-FR')}
                            </Button>

                            <Button
                                variant="outline"
                                className="h-11 rounded-xl border-[#f4c7c3] px-4 text-[#b42318] hover:bg-[#fdecec]"
                                onClick={() => setCloseCashForm(true)}
                            >
                                Fermer la caisse
                            </Button>

                            <Button className={agenceButtonStyles.primary} onClick={() => setMouvementOpen(true)}>
                                <Plus className="h-4 w-4" />
                                Nouveau mouvement
                            </Button>
                            </>
                        ) : null}
                    </div>
                </div>

                {/* Caisse fermée */}
                {!caisseOuverte ? (
                    <Card className="mx-auto w-full max-w-2xl rounded-2xl border-[#c8d4de] bg-white text-center shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-[#0f172a]">Caisse fermée</CardTitle>
                            <CardDescription className="text-[#5f7182]">
                                Vous devez ouvrir la caisse avant d&apos;enregistrer des paiements.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-4">
                            {!openCashForm ? (
                                <div className="flex justify-center">
                                    <Button className={agenceButtonStyles.primary} onClick={() => setOpenCashForm(true)}>
                                        Ouvrir la caisse
                                    </Button>
                                </div>
                            ) : (
                                <div className="flex flex-col gap-4 text-left">
                                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <label className="flex flex-col gap-2">
                                            <span className="text-sm font-medium text-[#0f172a]">Solde d&apos;ouverture</span>
                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                value={ouvertureForm.data.solde_ouverture}
                                                onChange={(event) => ouvertureForm.setData('solde_ouverture', event.target.value)}
                                                className="h-11 rounded-xl border border-[#c8d4de] bg-white px-3 text-sm text-[#0f172a] outline-none focus:border-[#00559b]"
                                            />
                                            {ouvertureForm.errors.solde_ouverture ? <span className="text-xs text-[#b42318]">{ouvertureForm.errors.solde_ouverture}</span> : null}
                                        </label>
                                        <label className="flex flex-col gap-2 sm:col-span-2">
                                            <span className="text-sm font-medium text-[#0f172a]">Observation</span>
                                            <textarea
                                                placeholder="Observation facultative..."
                                                value={ouvertureForm.data.observation}
                                                onChange={(event) => ouvertureForm.setData('observation', event.target.value)}
                                                className="min-h-[80px] rounded-xl border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] outline-none focus:border-[#00559b]"
                                            />
                                            {ouvertureForm.errors.observation ? <span className="text-xs text-[#b42318]">{ouvertureForm.errors.observation}</span> : null}
                                        </label>
                                    </div>
                                    <div className="flex justify-center gap-2">
                                        <Button variant="outline" className={agenceButtonStyles.outline} onClick={() => setOpenCashForm(false)}>
                                            Annuler
                                        </Button>
                                        <Button
                                            className={agenceButtonStyles.primary}
                                            disabled={ouvertureForm.processing}
                                            onClick={ouvrirCaisse}
                                        >
                                            {ouvertureForm.processing ? 'Ouverture...' : 'Valider l\'ouverture'}
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                ) : null}

                {/* Formulaire d'arrêté de caisse */}
                {caisseOuverte && closeCashForm ? (
                    <Card className="mx-auto w-full max-w-2xl rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                        <CardHeader className="text-center">
                            <CardTitle className="text-[#0f172a]">Arrêté de caisse</CardTitle>
                            <CardDescription className="text-[#5f7182]">
                                Clôture journalière de la caisse agence.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-4">
                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <ClosureRow label="Solde ouverture" value={currency(soldeOuverture)} />
                                <ClosureRow label="Total entrées" value={`+ ${currency(totalEntrees)}`} tone="success" />
                                <ClosureRow label="Total sorties" value={`- ${currency(totalSorties)}`} tone="danger" />
                                <ClosureRow label="Solde théorique" value={currency(soldeTheorique)} />
                            </div>

                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <label className="flex flex-col gap-2">
                                    <span className="text-sm font-medium text-[#0f172a]">Solde réel de fermeture</span>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        value={fermetureForm.data.solde_fermeture}
                                        onChange={(event) => fermetureForm.setData('solde_fermeture', event.target.value)}
                                        className="h-11 rounded-xl border border-[#c8d4de] bg-white px-3 text-sm text-[#0f172a] outline-none focus:border-[#00559b]"
                                    />
                                    {fermetureForm.errors.solde_fermeture ? <span className="text-xs text-[#b42318]">{fermetureForm.errors.solde_fermeture}</span> : null}
                                </label>
                                <label className="flex flex-col gap-2 sm:col-span-2">
                                    <span className="text-sm font-medium text-[#0f172a]">Observation de fermeture</span>
                                    <textarea
                                        placeholder="Observation facultative..."
                                        value={fermetureForm.data.observation}
                                        onChange={(event) => fermetureForm.setData('observation', event.target.value)}
                                        className="min-h-[80px] rounded-xl border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] outline-none focus:border-[#00559b]"
                                    />
                                    {fermetureForm.errors.observation ? <span className="text-xs text-[#b42318]">{fermetureForm.errors.observation}</span> : null}
                                </label>
                            </div>

                            <div className="flex justify-center gap-2">
                                <Button variant="outline" className={agenceButtonStyles.outline} onClick={() => setCloseCashForm(false)}>
                                    Annuler
                                </Button>
                                <Button
                                    className={agenceButtonStyles.primary}
                                    disabled={fermetureForm.processing}
                                    onClick={fermerCaisse}
                                >
                                    {fermetureForm.processing ? 'Clôture...' : 'Valider & clôturer'}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                ) : null}

                {/* Contenu principal */}
                {caisseOuverte && !closeCashForm ? (
                    <>
                        {/* Stats */}
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            {stats.map((stat) => {
                                const Icon = stat.icon;
                                return (
                                    <Card key={stat.label} className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                            <CardDescription className="text-sm font-medium text-[#5f7182]">
                                                {stat.label}
                                            </CardDescription>
                                            <span className={cn('flex h-10 w-10 items-center justify-center rounded-xl', stat.accent)}>
                                                <Icon className="h-5 w-5" />
                                            </span>
                                        </CardHeader>
                                        <CardContent>
                                            <p className="text-xl font-semibold text-[#0f172a]">{stat.value}</p>
                                        </CardContent>
                                    </Card>
                                );
                            })}
                        </div>

                        {/* Onglets */}
                        <div className="flex flex-wrap gap-2">
                            {TABS.map((tab) => {
                                const Icon = tab.icon;
                                const isActive = activeTab === tab.id;
                                return (
                                    <button
                                        key={tab.id}
                                        type="button"
                                        onClick={() => setActiveTab(tab.id)}
                                        className={cn(
                                            'inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-medium transition',
                                            isActive
                                                ? 'border-[#00559b] bg-[#00559b] text-white'
                                                : 'border-[#c8d4de] bg-white text-[#0f172a] hover:border-[#00559b]'
                                        )}
                                    >
                                        <Icon className="h-4 w-4" />
                                        {tab.label}
                                    </button>
                                );
                            })}
                        </div>

                        {/* Transactions */}
                        {activeTab === 'transactions' ? (
                            <DataTable
                                title="Mouvements"
                                columns={transactionColumns}
                                data={filteredTransactions}
                                filtersSlot={
                                    <div className="flex flex-wrap gap-2">
                                        {[
                                            { key: 'all', label: 'Toutes' },
                                            { key: 'in', label: 'Entrées' },
                                            { key: 'out', label: 'Sorties' },
                                        ].map((filter) => (
                                            <button
                                                key={filter.key}
                                                type="button"
                                                onClick={() => setTxFilter(filter.key)}
                                                className={cn(
                                                    'rounded-full border px-3 py-1 text-xs font-medium transition',
                                                    txFilter === filter.key
                                                        ? 'border-[#00559b] bg-[#eaf4fb] text-[#00559b]'
                                                        : 'border-[#c8d4de] bg-white text-[#5f7182] hover:border-[#00559b]'
                                                )}
                                            >
                                                {filter.label}
                                            </button>
                                        ))}
                                    </div>
                                }
                                onResetFilters={() => setTxFilter('all')}
                                emptyState={
                                    <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                                        <p className="text-sm font-medium text-[#0f172a]">Aucun mouvement trouvé.</p>
                                        <p className="text-sm text-[#5f7182]">Aucune écriture ne correspond au filtre sélectionné.</p>
                                    </div>
                                }
                                showColumnVisibility={false}
                            />
                        ) : null}

                        {/* Loyers */}
                        {activeTab === 'loyers' ? (
                            <DataTable
                                title="Paiements de loyers"
                                columns={loyerColumns}
                                data={loyers}
                                emptyState={
                                    <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                                        <p className="text-sm font-medium text-[#0f172a]">Aucun paiement trouvé.</p>
                                        <p className="text-sm text-[#5f7182]">Aucun loyer n’a encore été enregistré.</p>
                                    </div>
                                }
                                showColumnVisibility={false}
                            />
                        ) : null}

                        {/* Maintenance */}
                        {activeTab === 'maintenance' ? (
                            <DataTable
                                title="Interventions maintenance"
                                columns={maintenanceColumns}
                                data={maintenance}
                                emptyState={
                                    <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                                        <p className="text-sm font-medium text-[#0f172a]">Aucune intervention trouvée.</p>
                                        <p className="text-sm text-[#5f7182]">Aucune opération de maintenance n’a encore été enregistrée.</p>
                                    </div>
                                }
                                showColumnVisibility={false}
                            />
                        ) : null}

                        {/* Dépenses agence */}
                        {activeTab === 'depenses' ? (
                            <DataTable
                                title="Dépenses agence"
                                columns={depenseColumns}
                                data={depenses}
                                emptyState={
                                    <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                                        <p className="text-sm font-medium text-[#0f172a]">Aucune dépense trouvée.</p>
                                        <p className="text-sm text-[#5f7182]">Aucune dépense d’agence n’a encore été enregistrée.</p>
                                    </div>
                                }
                                showColumnVisibility={false}
                            />
                        ) : null}

                        {/* Vente de biens */}
                        {activeTab === 'ventes' ? (
                            <DataTable
                                title="Ventes de biens"
                                columns={venteColumns}
                                data={ventes}
                                emptyState={
                                    <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                                        <p className="text-sm font-medium text-[#0f172a]">Aucune vente trouvée.</p>
                                        <p className="text-sm text-[#5f7182]">Aucune vente de bien n’a encore été enregistrée.</p>
                                    </div>
                                }
                                showColumnVisibility={false}
                            />
                        ) : null}

                        {/* Résumé par mode de paiement */}
                        {activeTab === 'summary' ? (
                            <div className="flex flex-col gap-6">
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                                    {summary.map((row) => {
                                        const Icon = typeof row.icon === 'string'
                                            ? (PAYMENT_ICONS[row.icon] ?? Wallet)
                                            : (row.icon ?? Wallet);
                                        return (
                                            <Card key={row.mode} className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                                                <CardHeader className="flex flex-row items-center gap-3 space-y-0">
                                                    <span className={cn('flex h-12 w-12 items-center justify-center rounded-xl', row.accent)}>
                                                        <Icon className="h-6 w-6" />
                                                    </span>
                                                    <CardTitle className="text-base text-[#0f172a]">{row.mode}</CardTitle>
                                                </CardHeader>
                                                <CardContent className="flex flex-col gap-2 text-sm">
                                                    <SummaryRow label={`Entrées (${number(row.entries_count)})`} value={<strong className="text-[#4d8500]">+ {currency(row.entries)}</strong>} />
                                                    <SummaryRow label={`Sorties (${number(row.outputs_count)})`} value={<strong className="text-[#b42318]">- {currency(row.outputs)}</strong>} />
                                                    <SummaryRow label="Nombre de mouvements" value={number(row.count)} />
                                                    <SummaryRow label="Solde net" value={<strong className={Number(row.balance) >= 0 ? 'text-[#00559b]' : 'text-[#b42318]'}>{currency(row.balance)}</strong>} />
                                                </CardContent>
                                            </Card>
                                        );
                                    })}
                                </div>

                                <DataTable
                                    title="Récapitulatif global"
                                    columns={summaryColumns}
                                    data={summaryRows}
                                    emptyState={
                                        <div className="flex flex-col items-center justify-center gap-2 py-12 text-center">
                                            <p className="text-sm font-medium text-[#0f172a]">Aucun récapitulatif disponible.</p>
                                            <p className="text-sm text-[#5f7182]">Aucune donnée de paiement n’est encore disponible.</p>
                                        </div>
                                    }
                                    showColumnVisibility={false}
                                />
                            </div>
                        ) : null}
                    </>
                ) : null}

            </div>

            {/* Modal Nouveau mouvement */}
            {mouvementOpen ? (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <button
                        type="button"
                        aria-label="Fermer"
                        className="absolute inset-0 bg-slate-950/40"
                        onClick={() => setMouvementOpen(false)}
                    />
                    <div className="relative z-10 w-full max-w-lg rounded-2xl border border-[#c8d4de] bg-white shadow-2xl">
                        <div className="flex items-center justify-between border-b border-[#c8d4de] px-5 py-4">
                            <h3 className="text-lg font-semibold text-[#0f172a]">Nouveau mouvement</h3>
                            <Button variant="outline" size="icon" onClick={() => setMouvementOpen(false)}>
                                <X className="h-4 w-4" />
                            </Button>
                        </div>
                        <div className="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2">
                            {MOUVEMENT_OPTIONS.map((option) => {
                                const Icon = option.icon;
                                return (
                                    <a
                                        key={option.title}
                                        href={option.href}
                                        className="flex items-center gap-3 rounded-2xl border border-[#c8d4de] bg-white p-4 transition hover:border-[#00559b] hover:shadow-md hover:shadow-[#00559b]/5"
                                    >
                                        <span className={cn('flex h-11 w-11 items-center justify-center rounded-xl', option.accent)}>
                                            <Icon className="h-5 w-5" />
                                        </span>
                                        <span className="flex flex-col">
                                            <strong className="text-sm text-[#0f172a]">{option.title}</strong>
                                            <span className="text-xs text-[#5f7182]">{option.description}</span>
                                        </span>
                                    </a>
                                );
                            })}
                        </div>
                    </div>
                </div>
            ) : null}
        </AgenceLayout>
    );
}

function Tone({ tone = 'info', children }) {
    const variantByTone = {
        success: 'success',
        info: 'warning',
        danger: 'danger',
    };

    return (
        <Badge
            variant={variantByTone[tone] ?? 'warning'}
            className="rounded-full px-2.5 py-1 text-[11px] font-medium"
        >
            {children}
        </Badge>
    );
}

function ViewButton() {
    return (
        <Button
            type="button"
            variant="outline"
            size="icon"
            className={agenceButtonStyles.actionBlueIcon}
            title="Voir"
        >
            <Eye className="h-4 w-4" />
        </Button>
    );
}

function SummaryRow({ label, value }) {
    return (
        <div className="flex items-center justify-between border-b border-dashed border-[#eef3f7] py-1 last:border-0">
            <span className="text-[#5f7182]">{label} :</span>
            <span className="text-[#0f172a]">{value}</span>
        </div>
    );
}

function ClosureRow({ label, value, tone }) {
    return (
        <div className="rounded-xl border border-[#eef3f7] bg-[#f7fbfe] p-3">
            <span className="block text-xs text-[#5f7182]">{label}</span>
            <strong
                className={cn(
                    'text-sm',
                    tone === 'success' && 'text-[#4d8500]',
                    tone === 'danger' && 'text-[#b42318]',
                    !tone && 'text-[#0f172a]'
                )}
            >
                {value}
            </strong>
        </div>
    );
}

function TableCard({ title, filters, activeFilter, onFilter, head, empty, children }) {
    return (
        <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
            <CardHeader className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <CardTitle className="text-base text-[#0f172a]">{title}</CardTitle>
                {filters ? (
                    <div className="flex flex-wrap gap-2">
                        {filters.map((filter) => (
                            <button
                                key={filter.key}
                                type="button"
                                onClick={() => onFilter?.(filter.key)}
                                className={cn(
                                    'rounded-full border px-3 py-1 text-xs font-medium transition',
                                    activeFilter === filter.key
                                        ? 'border-[#00559b] bg-[#eaf4fb] text-[#00559b]'
                                        : 'border-[#c8d4de] bg-white text-[#5f7182] hover:border-[#00559b]'
                                )}
                            >
                                {filter.label}
                            </button>
                        ))}
                    </div>
                ) : null}
            </CardHeader>
            <Separator className="bg-[#eef3f7]" />
            <CardContent className="overflow-x-auto p-0">
                <table className="w-full min-w-[720px] text-sm">
                    <thead>
                        <tr className="border-b border-[#eef3f7] text-left text-xs uppercase tracking-wide text-[#5f7182]">
                            {head.map((label, index) => (
                                <th
                                    key={index}
                                    className={cn(
                                        'px-6 py-3 font-medium',
                                        (label === 'Entrée' || label === 'Sortie' || label === 'Montant' || label === 'Coût') && 'text-right'
                                    )}
                                >
                                    {label}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        {empty ? (
                            <tr>
                                <td colSpan={head.length} className="px-6 py-8 text-center text-sm text-[#5f7182]">
                                    Aucune écriture trouvée.
                                </td>
                            </tr>
                        ) : (
                            children
                        )}
                    </tbody>
                </table>
            </CardContent>
        </Card>
    );
}
