import { useState } from 'react';
import {
    flexRender,
    getCoreRowModel,
    getSortedRowModel,
    useReactTable,
} from '@tanstack/react-table';
import { SlidersHorizontal, Search, X } from 'lucide-react';
import { Button } from './button';
import { Checkbox } from './checkbox';
import { DataTablePagination } from './data-table-pagination';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from './dropdown-menu';
import { Input } from './input';
import { Separator } from './separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './table';
import { cn } from '../../lib/utils';

/**
 * DataTable inspiré de shadcn/ui, adapté à une source Inertia/Laravel.
 *
 * - tri côté client sur le lot courant
 * - pagination Inertia optionnelle
 * - barre d'outils avec recherche / filtres / visibilité des colonnes
 * - rendu de table minimaliste et réutilisable
 */
export function DataTable({
    columns,
    data,
    title,
    description,
    pagination,
    only = [],
    onPageChange,
    searchValue,
    onSearchChange,
    searchPlaceholder = 'Rechercher...',
    filtersSlot,
    onResetFilters,
    emptyState,
    className,
    toolbarClassName,
    tableClassName,
    showColumnVisibility = true,
    sorting,
    onSortingChange,
}) {
    const [internalSorting, setInternalSorting] = useState([]);
    const [columnVisibility, setColumnVisibility] = useState({});

    const controlledSorting = sorting ?? internalSorting;
    const handleSortingChange = onSortingChange ?? setInternalSorting;

    const table = useReactTable({
        data,
        columns,
        state: {
            sorting: controlledSorting,
            columnVisibility,
        },
        enableSortingRemoval: false,
        onSortingChange: handleSortingChange,
        onColumnVisibilityChange: setColumnVisibility,
        getCoreRowModel: getCoreRowModel(),
        getSortedRowModel: getSortedRowModel(),
    });

    const visibleLeafColumns = table.getVisibleLeafColumns();
    const hasSearch = typeof onSearchChange === 'function';
    const hasFilters = Boolean(filtersSlot);
    const hasToolbar = title || description || hasSearch || hasFilters || showColumnVisibility || onResetFilters;

    return (
        <div className={cn('rounded-2xl border border-[#c8d4de] bg-white shadow-sm', className)}>
            {hasToolbar ? (
                <div className={cn('flex flex-col gap-4 p-4 sm:p-5', toolbarClassName)}>
                    <div className="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                        <div className="space-y-1">
                            {title ? <h3 className="text-base font-semibold text-[#0f172a]">{title}</h3> : null}
                            {description ? <p className="max-w-3xl text-sm text-[#5f7182]">{description}</p> : null}
                        </div>

                        <div className="flex flex-wrap items-center gap-2">
                            {onResetFilters ? (
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    className="rounded-xl border-[#c8d4de] text-[#0f172a]"
                                    onClick={onResetFilters}
                                >
                                    <X className="h-4 w-4" />
                                    Réinitialiser
                                </Button>
                            ) : null}

                            {showColumnVisibility ? (
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            className="rounded-xl border-[#c8d4de] text-[#0f172a]"
                                        >
                                            <SlidersHorizontal className="h-4 w-4" />
                                            Colonnes
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent className="w-64">
                                        <div className="px-2 py-1.5 text-xs font-semibold uppercase tracking-[0.16em] text-[#5f7182]">
                                            Affichage
                                        </div>
                                        <DropdownMenuSeparator />
                                        {table
                                            .getAllLeafColumns()
                                            .filter((column) => column.getCanHide())
                                            .map((column) => {
                                                const meta = column.columnDef.meta ?? {};
                                                const label = meta.label ?? column.id;

                                                return (
                                                    <DropdownMenuItem
                                                        asChild
                                                        key={column.id}
                                                        className="justify-between gap-3"
                                                        onClick={(event) => {
                                                            column.toggleVisibility(!column.getIsVisible());
                                                        }}
                                                    >
                                                        <div className="flex w-full items-center justify-between gap-3">
                                                            <span className="truncate">{label}</span>
                                                            <Checkbox
                                                                checked={column.getIsVisible()}
                                                                onChange={() => column.toggleVisibility(!column.getIsVisible())}
                                                                onClick={(event) => event.stopPropagation()}
                                                            />
                                                        </div>
                                                    </DropdownMenuItem>
                                                );
                                            })}
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            ) : null}
                        </div>
                    </div>

                    <div className="flex flex-col gap-3">
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            {hasSearch ? (
                                <div className="relative w-full lg:max-w-md lg:flex-1">
                                    <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#94a3b8]" />
                                    <Input
                                        value={searchValue ?? ''}
                                        onChange={(e) => onSearchChange?.(e.target.value)}
                                        placeholder={searchPlaceholder}
                                        className="h-10 rounded-xl border-[#c8d4de] pl-9"
                                    />
                                </div>
                            ) : null}

                            {hasFilters ? <div className="w-full lg:ml-auto lg:w-auto">{filtersSlot}</div> : null}
                        </div>
                    </div>
                </div>
            ) : null}

            {hasToolbar ? <Separator className="bg-[#e2e8f0]" /> : null}

            <div className={cn('w-full overflow-x-auto overscroll-x-contain touch-pan-x', tableClassName)}>
                <Table className="min-w-[1040px] table-fixed">
                    <TableHeader>
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id} className="hover:bg-transparent">
                                {headerGroup.headers.map((header) => {
                                    const meta = header.column.columnDef.meta ?? {};

                                    return (
                                        <TableHead
                                            key={header.id}
                                            className={cn(
                                                meta.headerClassName,
                                                header.column.getCanSort() ? 'select-none' : '',
                                                'bg-[#f8fafc]'
                                            )}
                                        >
                                            {header.isPlaceholder
                                                ? null
                                                : flexRender(header.column.columnDef.header, header.getContext())}
                                        </TableHead>
                                    );
                                })}
                            </TableRow>
                        ))}
                    </TableHeader>

                    <TableBody>
                        {table.getRowModel().rows?.length ? (
                            table.getRowModel().rows.map((row) => (
                                <TableRow key={row.id} data-state={row.getIsSelected() && 'selected'}>
                                    {row.getVisibleCells().map((cell) => {
                                        const meta = cell.column.columnDef.meta ?? {};

                                        return (
                                            <TableCell key={cell.id} className={meta.cellClassName}>
                                                {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                            </TableCell>
                                        );
                                    })}
                                </TableRow>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell colSpan={visibleLeafColumns.length || columns.length} className="py-12">
                                    {emptyState ?? (
                                        <div className="flex flex-col items-center justify-center gap-2 text-center">
                                            <p className="text-sm font-medium text-[#0f172a]">Aucun résultat.</p>
                                            <p className="text-sm text-[#5f7182]">
                                                Aucun élément ne correspond à vos critères de recherche.
                                            </p>
                                        </div>
                                    )}
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>

            {pagination ? (
                <>
                    <Separator className="bg-[#e2e8f0]" />
                    <DataTablePagination paginator={pagination} only={only} onPageChange={onPageChange} />
                </>
            ) : null}
        </div>
    );
}
