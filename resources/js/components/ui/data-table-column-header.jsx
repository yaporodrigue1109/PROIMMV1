import { ArrowDown, ArrowUp, ChevronsUpDown } from 'lucide-react';
import { Button } from './button';
import { cn } from '../../lib/utils';

/**
 * Header de colonne triable, tel que défini dans la doc shadcn/ui,
 * mais découplé de TanStack column.getIsSorted() puisque le tri
 * est géré manuellement (piloté par Laravel/Inertia).
 *
 * @param {string} title - libellé affiché
 * @param {'asc'|'desc'|false} sortDirection - direction actuelle du tri pour CETTE colonne
 * @param {function} onSort - callback au clic
 */
export function DataTableColumnHeader({ title, sortDirection, onSort, className }) {
    if (!onSort) {
        return <span className={cn('text-sm font-medium', className)}>{title}</span>;
    }

    const Icon = sortDirection === 'asc' ? ArrowUp : sortDirection === 'desc' ? ArrowDown : ChevronsUpDown;

    return (
        <Button
            type="button"
            variant="ghost"
            size="sm"
            className={cn('-ml-3 h-8 data-[state=open]:bg-accent', className)}
            onClick={onSort}
        >
            <span>{title}</span>
            <Icon className="ml-2 h-4 w-4" />
        </Button>
    );
}