import { useMemo, useState } from 'react';
import { Head } from '@inertiajs/react';
import {
    ArrowDownCircle,
    ArrowUpCircle,
    Banknote,
    Calendar,
    CreditCard,
    Eye,
    Home,
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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '../../../components/ui/dropdown-menu';
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
        href: '/agence/caisse/vente',
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
        { id: 1, date: '11/05/2026', time: '09:30', tenant: 'Kouamé Jean', property: 'Appt. B2 — Cocody', period: 'Mai 2026', amount: 85000, mode: 'Espèces' },
    ],
    maintenance = [
        { id: 1, date: '11/05/2026', time: '08:00', property: 'Villa C3 — Riviera', type: 'Plomberie', provider: 'Kouassi & Fils', cost: 15000, status: 'Terminée' },
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
    const [caisseOuverte, setCaisseOuverte] = useState(caisseOuverteProp);
    const [openCashForm, setOpenCashForm] = useState(false);
    const [closeCashForm, setCloseCashForm] = useState(false);
    const [activeTab, setActiveTab] = useState('transactions');
    const [txFilter, setTxFilter] = useState('all');
    const [mouvementOpen, setMouvementOpen] = useState(false);

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
            total: acc.total + row.total,
            count: acc.count + row.count,
            commission: acc.commission + row.commission,
            net: acc.net + row.net,
        }),
        { total: 0, count: 0, commission: 0, net: 0 }
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
                id: 'reference',
                accessorFn: (row) => row.reference,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Référence"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Référence' },
                cell: ({ row }) => <span className="text-[#5f7182]">{row.original.reference}</span>,
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
                cell: () => <ViewButton />,
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
                cell: ({ row }) => row.original.client,
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
                id: 'reference',
                accessorFn: (row) => row.reference,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Référence"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                    />
                ),
                meta: { label: 'Référence' },
                cell: ({ row }) => <span className="text-[#5f7182]">{row.original.reference}</span>,
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
                cell: () => <ViewButton />,
            },
        ],
        []
    );

    const summaryRows = useMemo(
        () => [
            ...summary.map((row) => ({
                ...row,
                isTotal: false,
            })),
            {
                mode: 'TOTAL',
                total: summaryTotals.total,
                count: summaryTotals.count,
                commission: summaryTotals.commission,
                net: summaryTotals.net,
                isTotal: true,
            },
        ],
        [summary, summaryTotals]
    );

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
                id: 'total',
                accessorFn: (row) => row.total,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Montant total"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="justify-end"
                    />
                ),
                meta: { label: 'Montant total', headerClassName: 'text-right', cellClassName: 'text-right' },
                cell: ({ row }) => (
                    <span className={cn('text-[#0f172a]', row.original.isTotal && 'font-semibold')}>
                        {currency(row.original.total)}
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
                id: 'commission',
                accessorFn: (row) => row.commission,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Commission agence"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="justify-end"
                    />
                ),
                meta: { label: 'Commission agence', headerClassName: 'text-right', cellClassName: 'text-right' },
                cell: ({ row }) => (
                    <span className={cn('text-[#0f172a]', row.original.isTotal && 'font-semibold')}>
                        {currency(row.original.commission)}
                    </span>
                ),
            },
            {
                id: 'net',
                accessorFn: (row) => row.net,
                header: ({ column }) => (
                    <DataTableColumnHeader
                        title="Net propriétaire"
                        sortDirection={column.getIsSorted()}
                        onSort={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                        className="justify-end"
                    />
                ),
                meta: { label: 'Net propriétaire', headerClassName: 'text-right', cellClassName: 'text-right' },
                cell: ({ row }) => (
                    <span className={cn('text-[#0f172a]', row.original.isTotal && 'font-semibold')}>
                        {currency(row.original.net)}
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
                {/* En-tête */}
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                       
                        <h2 className="text-2xl font-semibold text-[#0f172a]">Gestion financière</h2>
                    </div>

                    {caisseOuverte ? (
                        <div className="flex flex-wrap gap-2">
                            <Button variant="outline" className={agenceButtonStyles.outline}>
                                <Calendar className="h-4 w-4" />
                                {new Date().toLocaleDateString('fr-FR')}
                            </Button>

                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button variant="outline" className={agenceButtonStyles.outline}>
                                        Actions
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" className="w-56">
                                    <DropdownMenuItem>Rapport journalier</DropdownMenuItem>
                                    <DropdownMenuItem>Journal de caisse du jour</DropdownMenuItem>
                                    <DropdownMenuItem>Solde de caisse</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>

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
                        </div>
                    ) : null}
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
                                                defaultValue={soldeOuverture}
                                                className="h-11 rounded-xl border border-[#c8d4de] bg-white px-3 text-sm text-[#0f172a] outline-none focus:border-[#00559b]"
                                            />
                                        </label>
                                        <label className="flex flex-col gap-2 sm:col-span-2">
                                            <span className="text-sm font-medium text-[#0f172a]">Observation</span>
                                            <textarea
                                                placeholder="Observation facultative..."
                                                className="min-h-[80px] rounded-xl border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] outline-none focus:border-[#00559b]"
                                            />
                                        </label>
                                    </div>
                                    <div className="flex justify-center gap-2">
                                        <Button variant="outline" className={agenceButtonStyles.outline} onClick={() => setOpenCashForm(false)}>
                                            Annuler
                                        </Button>
                                        <Button
                                            className={agenceButtonStyles.primary}
                                            onClick={() => {
                                                setCaisseOuverte(true);
                                                setOpenCashForm(false);
                                            }}
                                        >
                                            Valider l&apos;ouverture
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
                                        defaultValue={soldeTheorique}
                                        className="h-11 rounded-xl border border-[#c8d4de] bg-white px-3 text-sm text-[#0f172a] outline-none focus:border-[#00559b]"
                                    />
                                </label>
                                <label className="flex flex-col gap-2 sm:col-span-2">
                                    <span className="text-sm font-medium text-[#0f172a]">Observation de fermeture</span>
                                    <textarea
                                        placeholder="Observation facultative..."
                                        className="min-h-[80px] rounded-xl border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] outline-none focus:border-[#00559b]"
                                    />
                                </label>
                            </div>

                            <div className="flex justify-center gap-2">
                                <Button variant="outline" className={agenceButtonStyles.outline} onClick={() => setCloseCashForm(false)}>
                                    Annuler
                                </Button>
                                <Button
                                    className={agenceButtonStyles.primary}
                                    onClick={() => {
                                        setCaisseOuverte(false);
                                        setCloseCashForm(false);
                                    }}
                                >
                                    Valider &amp; clôturer
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
                                        const Icon = row.icon;
                                        return (
                                            <Card key={row.mode} className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                                                <CardHeader className="flex flex-row items-center gap-3 space-y-0">
                                                    <span className={cn('flex h-12 w-12 items-center justify-center rounded-xl', row.accent)}>
                                                        <Icon className="h-6 w-6" />
                                                    </span>
                                                    <CardTitle className="text-base text-[#0f172a]">{row.mode}</CardTitle>
                                                </CardHeader>
                                                <CardContent className="flex flex-col gap-2 text-sm">
                                                    <SummaryRow label="Montant total" value={<strong className="text-[#4d8500]">{currency(row.total)}</strong>} />
                                                    <SummaryRow label="Nombre transactions" value={number(row.count)} />
                                                    <SummaryRow label="Part agence (10%)" value={currency(row.commission)} />
                                                    <SummaryRow label="Part propriétaires" value={currency(row.net)} />
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
