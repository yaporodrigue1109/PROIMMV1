import React, { useMemo, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import {
    ArrowLeft,
    Building2,
    FileText,
    History,
    RotateCcw,
    Search,
} from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Button } from '../../../components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '../../../components/ui/card';
import { Badge } from '../../../components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '../../../components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '../../../components/ui/table';
import { cn } from '../../../lib/utils';
import { agenceButtonStyles } from '../../../lib/buttonStyles';

const currency = (value) =>
    new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        maximumFractionDigits: 0,
    }).format(Number(value ?? 0));

const fmtDate = (iso) => {
    if (!iso) return '—';
    const date = new Date(iso);
    if (isNaN(date.getTime())) return '—';
    const day = String(date.getUTCDate()).padStart(2, '0');
    const month = String(date.getUTCMonth() + 1).padStart(2, '0');
    const year = date.getUTCFullYear();
    return `${day}/${month}/${year}`;
};

export default function ReversementHistorique({
    reversements = { data: [], links: [], current_page: 1, last_page: 1, total: 0 },
    proprietaires = [],
    lots = [],
    filters = {},
}) {
    const [proprietaireId, setProprietaireId] = useState(filters.proprietaire_id || 'all');
    const [lotId, setLotId] = useState(filters.lot_id || 'all');

    // Le sélecteur de lot ne propose que les lots du propriétaire choisi (si un propriétaire est choisi)
    const lotsFiltres = useMemo(() => {
        if (!proprietaireId || proprietaireId === 'all') return lots;
        return lots.filter((l) => l.proprietaire_id === proprietaireId);
    }, [lots, proprietaireId]);

    const lancerRecherche = (overrides = {}) => {
        const proprietaire = overrides.proprietaire_id ?? proprietaireId;
        const lot = overrides.lot_id ?? lotId;

        router.get(
            '/agence/reversement/historique',
            {
                proprietaire_id: proprietaire && proprietaire !== 'all' ? proprietaire : undefined,
                lot_id: lot && lot !== 'all' ? lot : undefined,
            },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    };

    const handleProprietaireChange = (value) => {
        setProprietaireId(value);
        // Si le lot sélectionné n'appartient plus au nouveau propriétaire, on le réinitialise
        if (value !== 'all') {
            const stillValid = lots.some((l) => l.id === lotId && l.proprietaire_id === value);
            if (!stillValid) setLotId('all');
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        lancerRecherche();
    };

    const handleReset = () => {
        setProprietaireId('all');
        setLotId('all');
        router.get('/agence/reversement/historique', {}, { preserveState: true, preserveScroll: true, replace: true });
    };

    const openDetail = (id) => {
        router.visit(`/agence/reversement/historique/${id}`);
    };

    const goToPage = (url) => {
        if (!url) return;
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    };

    return (
        <AgenceLayout title="Historique des reversements">
            <Head title="Historique des reversements" />

            <div className="mx-auto flex w-full max-w-7xl flex-col gap-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="flex items-center gap-2 text-2xl font-semibold text-[#0f172a]">
                            <History className="h-6 w-6 text-[#00559b]" />
                            Historique des reversements
                        </h2>
                        <p className="text-sm text-[#5f7182]">
                            Retrouvez tous les reversements déjà effectués aux propriétaires.
                        </p>
                    </div>
                    <Button
                        variant="outline"
                        className={agenceButtonStyles.outline}
                        onClick={() => router.visit('/agence/reversement')}
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Retour au tableau de bord
                    </Button>
                </div>

                {/* Recherche par propriétaire et/ou par lot */}
                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-base text-[#0f172a]">
                            <Search className="h-4 w-4 text-[#00559b]" />
                            Rechercher dans l'historique
                        </CardTitle>
                        <CardDescription className="text-sm text-[#5f7182]">
                            Filtrez par propriétaire et/ou par lot (cours).
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div className="flex flex-col gap-2">
                                <label className="text-sm font-medium text-[#0f172a]">Propriétaire</label>
                                <Select value={proprietaireId} onValueChange={handleProprietaireChange}>
                                    <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                        <SelectValue placeholder="Tous les propriétaires" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Tous les propriétaires</SelectItem>
                                        {proprietaires.map((p) => (
                                            <SelectItem key={p.id} value={p.id}>{p.nom}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="flex flex-col gap-2">
                                <label className="text-sm font-medium text-[#0f172a]">Lot / Cours</label>
                                <Select value={lotId} onValueChange={setLotId}>
                                    <SelectTrigger className="h-11 rounded-xl border-[#c8d4de]">
                                        <SelectValue placeholder="Tous les lots" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Tous les lots</SelectItem>
                                        {lotsFiltres.map((l) => (
                                            <SelectItem key={l.id} value={l.id}>{l.nom}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="flex items-end gap-2">
                                <Button type="submit" className={cn(agenceButtonStyles.primary, 'flex-1')}>
                                    <Search className="mr-2 h-4 w-4" />
                                    Rechercher
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    className={agenceButtonStyles.outline}
                                    onClick={handleReset}
                                    title="Réinitialiser"
                                >
                                    <RotateCcw className="h-4 w-4" />
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                {/* Liste des reversements effectués */}
                <Card className="rounded-2xl border-[#c8d4de] bg-white shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base text-[#0f172a]">Reversements</CardTitle>
                        <CardDescription className="text-sm text-[#5f7182]">
                            {reversements.total ?? reversements.data.length} résultat(s)
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="p-0">
                        {reversements.data.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-12">
                                <Building2 className="h-12 w-12 text-[#c8d4de]" />
                                <p className="mt-4 text-sm font-medium text-[#0f172a]">Aucun reversement trouvé</p>
                                <p className="text-sm text-[#5f7182]">Aucun reversement ne correspond à cette recherche.</p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="border-b border-[#eef3f7]">
                                            <TableHead className="text-xs font-medium text-[#5f7182]">Propriétaire</TableHead>
                                            <TableHead className="text-xs font-medium text-[#5f7182]">Lot</TableHead>
                                            <TableHead className="text-xs font-medium text-[#5f7182]">Période</TableHead>
                                            <TableHead className="text-right text-xs font-medium text-[#5f7182]">Montant reversé</TableHead>
                                            <TableHead className="text-right text-xs font-medium text-[#5f7182]">Montant arriéré</TableHead>
                                            <TableHead className="text-center text-xs font-medium text-[#5f7182]">Statut</TableHead>
                                            <TableHead className="text-right text-xs font-medium text-[#5f7182]">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {reversements.data.map((r) => (
                                            <TableRow key={r.id} className="hover:bg-[#f7fbfe]">
                                                <TableCell className="font-medium text-[#0f172a]">{r.proprietaire_nom}</TableCell>
                                                <TableCell className="text-[#0f172a]">{r.lot_nom}</TableCell>
                                                <TableCell className="text-sm text-[#5f7182]">
                                                    {fmtDate(r.periode_debut)} - {fmtDate(r.periode_fin)}
                                                </TableCell>
                                                <TableCell className="text-right font-medium text-[#4d8500]">
                                                    {currency(r.montant_reverse)}
                                                </TableCell>
                                                <TableCell className="text-right font-medium text-[#b42318]">
                                                    {currency(r.montant_arriere)}
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    <Badge
                                                        variant={
                                                            r.statut === 'reverse'
                                                                ? 'success'
                                                                : r.statut === 'annule'
                                                                    ? 'destructive'
                                                                    : 'warning'
                                                        }
                                                        className="rounded-full px-3 py-1 text-xs font-medium"
                                                    >
                                                        {r.statut === 'reverse' ? 'Reversé' : r.statut === 'annule' ? 'Annulé' : 'En attente'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <Button
                                                        variant="outline"
                                                        size="icon"
                                                        className={agenceButtonStyles.actionBlueIcon}
                                                        onClick={() => openDetail(r.id)}
                                                        title="Voir la fiche"
                                                    >
                                                        <FileText className="h-4 w-4" />
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Pagination */}
                {reversements.links && reversements.links.length > 3 && (
                    <div className="flex flex-wrap justify-center gap-1">
                        {reversements.links.map((link, idx) => (
                            <button
                                key={idx}
                                disabled={!link.url}
                                onClick={() => goToPage(link.url)}
                                className={cn(
                                    'rounded-lg px-3 py-1.5 text-sm',
                                    link.active
                                        ? 'bg-[#00559b] text-white'
                                        : link.url
                                            ? 'border border-[#c8d4de] text-[#0f172a] hover:bg-[#f7fbfe]'
                                            : 'text-[#c8d4de]'
                                )}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AgenceLayout>
    );
}