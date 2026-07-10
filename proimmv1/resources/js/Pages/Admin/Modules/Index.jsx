import { Link } from '@inertiajs/react';
import {
    ChevronDown,
    ChevronRight,
    GripVertical,
    PencilLine,
    Plus,
    Power,
    PowerOff,
} from 'lucide-react';
import { useMemo, useState } from 'react';

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

const statusMeta = {
    Actif: { variant: 'success', label: 'Actif' },
    Désactivé: { variant: 'danger', label: 'Désactivé' },
};

export default function Index({ menus = [] }) {
    const [dragEnabled, setDragEnabled] = useState(false);
    const [openedParents, setOpenedParents] = useState(() => new Set());
    const [draggedRow, setDraggedRow] = useState(null);
    const [dropIndicator, setDropIndicator] = useState(null);

    const [items, setItems] = useState(() =>
        (menus ?? []).map((menu, index) => ({
            ...menu,
            order: menu.order ?? index + 1,
            submenus: (menu.submenus ?? []).map((submenu, subIndex) => ({
                ...submenu,
                order: submenu.order ?? `${index + 1}.${subIndex + 1}`,
            })),
        })),
    );

    const rows = useMemo(() => {
        return items.flatMap((menu) => {
            const parentRow = {
                ...menu,
                rowType: 'parent',
                rowKey: `parent-${menu.parent_id ?? menu.id ?? menu.code}`,
            };

            const isOpen = openedParents.has(menu.parent_id ?? menu.id ?? menu.code);

            const submenuRows = isOpen
                ? (menu.submenus ?? []).map((submenu) => ({
                    ...submenu,
                    rowType: 'submenu',
                    parentId: menu.parent_id ?? menu.id ?? menu.code,
                    rowKey: `submenu-${submenu.submenu_id ?? submenu.id ?? submenu.code}`,
                }))
                : [];

            return [parentRow, ...submenuRows];
        });
    }, [items, openedParents]);

    const toggleParent = (parentId) => {
        setOpenedParents((current) => {
            const next = new Set(current);

            if (next.has(parentId)) {
                next.delete(parentId);
            } else {
                next.add(parentId);
            }

            return next;
        });
    };

    const toggleActive = (row) => {
        setItems((current) =>
            current.map((menu) => {
                const menuId = menu.parent_id ?? menu.id ?? menu.code;

                if (row.rowType === 'parent' && menuId === (row.parent_id ?? row.id ?? row.code)) {
                    return {
                        ...menu,
                        active: !menu.active,
                    };
                }

                if (row.rowType === 'submenu' && menuId === row.parentId) {
                    return {
                        ...menu,
                        submenus: (menu.submenus ?? []).map((submenu) => {
                            const submenuId = submenu.submenu_id ?? submenu.id ?? submenu.code;
                            const rowSubmenuId = row.submenu_id ?? row.id ?? row.code;

                            if (submenuId === rowSubmenuId) {
                                return {
                                    ...submenu,
                                    active: !submenu.active,
                                };
                            }

                            return submenu;
                        }),
                    };
                }

                return menu;
            }),
        );
    };

    const handleDragStart = (event, row) => {
        if (!dragEnabled) return;

        setDraggedRow(row);
        event.dataTransfer.effectAllowed = 'move';
    };

    const handleDragOver = (event, targetRow) => {
        if (!dragEnabled || !draggedRow) return;

        const sameType = draggedRow.rowType === targetRow.rowType;
        const sameParent =
            draggedRow.rowType === 'parent' ||
            draggedRow.parentId === targetRow.parentId;

        if (sameType && sameParent) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';

            const rect = event.currentTarget.getBoundingClientRect();
            const position = event.clientY < rect.top + rect.height / 2 ? 'before' : 'after';

            setDropIndicator({
                rowKey: targetRow.rowKey,
                position,
            });
        }
    };

    const handleDrop = (event, targetRow) => {
        event.preventDefault();

        if (!dragEnabled || !draggedRow) return;

        if (draggedRow.rowType !== targetRow.rowType) {
            setDraggedRow(null);
            return;
        }

        if (draggedRow.rowType === 'submenu' && draggedRow.parentId !== targetRow.parentId) {
            setDraggedRow(null);
            return;
        }

        if (draggedRow.rowKey === targetRow.rowKey) {
            setDraggedRow(null);
            setDropIndicator(null);
            return;
        }

        if (draggedRow.rowType === 'parent') {
            reorderParents(draggedRow, targetRow);
        } else {
            reorderSubmenus(draggedRow, targetRow);
        }

        setDraggedRow(null);
        setDropIndicator(null);
    };

    const reorderParents = (sourceRow, targetRow) => {
        setItems((current) => {
            const next = [...current];

            const sourceId = sourceRow.parent_id ?? sourceRow.id ?? sourceRow.code;
            const targetId = targetRow.parent_id ?? targetRow.id ?? targetRow.code;

            const sourceIndex = next.findIndex(
                (menu) => (menu.parent_id ?? menu.id ?? menu.code) === sourceId,
            );

            const targetIndex = next.findIndex(
                (menu) => (menu.parent_id ?? menu.id ?? menu.code) === targetId,
            );

            if (sourceIndex === -1 || targetIndex === -1) return current;

            const [moved] = next.splice(sourceIndex, 1);
            next.splice(targetIndex, 0, moved);

            return next.map((menu, index) => ({
                ...menu,
                order: index + 1,
                submenus: (menu.submenus ?? []).map((submenu, subIndex) => ({
                    ...submenu,
                    order: `${index + 1}.${subIndex + 1}`,
                })),
            }));
        });
    };

    const reorderSubmenus = (sourceRow, targetRow) => {
        setItems((current) =>
            current.map((menu) => {
                const menuId = menu.parent_id ?? menu.id ?? menu.code;

                if (menuId !== sourceRow.parentId) {
                    return menu;
                }

                const submenus = [...(menu.submenus ?? [])];

                const sourceId = sourceRow.submenu_id ?? sourceRow.id ?? sourceRow.code;
                const targetId = targetRow.submenu_id ?? targetRow.id ?? targetRow.code;

                const sourceIndex = submenus.findIndex(
                    (submenu) => (submenu.submenu_id ?? submenu.id ?? submenu.code) === sourceId,
                );

                const targetIndex = submenus.findIndex(
                    (submenu) => (submenu.submenu_id ?? submenu.id ?? submenu.code) === targetId,
                );

                if (sourceIndex === -1 || targetIndex === -1) {
                    return menu;
                }

                const [moved] = submenus.splice(sourceIndex, 1);
                submenus.splice(targetIndex, 0, moved);

                return {
                    ...menu,
                    submenus: submenus.map((submenu, index) => ({
                        ...submenu,
                        order: `${menu.order}.${index + 1}`,
                    })),
                };
            }),
        );
    };

    return (
        <AdminLayout title="Modules">
            <div className="space-y-6">
                <Card className="rounded-3xl border-slate-200 shadow-sm">
                    <CardHeader className="space-y-4 border-b border-slate-200 pb-5">
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <CardTitle className="text-lg">
                                    Gestion des menus sidebar
                                </CardTitle>
                                <CardDescription className="mt-1">
                                    Activez, désactivez et réorganisez les menus.
                                </CardDescription>
                            </div>

                            <div className="flex flex-wrap gap-3">
                              <Button
    type="button"
    variant={dragEnabled ? 'default' : 'outline'}
    className={[
        'h-11 rounded-xl px-4',
        dragEnabled
            ? 'bg-[#00559b] text-white hover:bg-[#004980]'
            : 'border-slate-200 text-slate-900',
    ].join(' ')}
    onClick={() => setDragEnabled((value) => !value)}
>
    <GripVertical className="h-4 w-4" />
    {dragEnabled ? 'Déplacement activé' : 'Activer le déplacement'}
</Button>

                                <Button
                                    asChild
                                    className="h-11 rounded-xl bg-[#00559b] px-4 text-white hover:bg-[#004980]"
                                >
                                    <Link href="/admin/modules/create">
                                        <Plus className="h-4 w-4" />
                                        Ajouter un module
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent className="p-0">
                        <div className="overflow-hidden rounded-b-3xl">
                            <table className="w-full text-sm">
                                <thead className="text-left text-xs uppercase tracking-[0.2em] text-slate-500">
                                    <tr className="border-b border-slate-200">
                                        <th className="w-12 bg-slate-50 px-6 py-4 font-medium"></th>
                                        <th className="bg-slate-50 px-6 py-4 font-medium">Menu</th>
                                        <th className="bg-slate-50 px-6 py-4 font-medium">Sous-menus</th>
                                        <th className="bg-slate-50 px-6 py-4 font-medium">Ordre</th>
                                        <th className="bg-slate-50 px-6 py-4 font-medium">État</th>
                                        <th className="bg-slate-50 px-6 py-4 font-medium">Action</th>
                                    </tr>
                                </thead>

                                <tbody className="divide-y divide-slate-200">
                                    {rows.map((row) => {
                                        const rowId = row.parent_id ?? row.id ?? row.code;
                                        const submenuCount = row.submenus?.length ?? 0;
                                        const hasSubmenus = row.rowType === 'parent' && submenuCount > 0;
                                        const isOpen = openedParents.has(rowId);

                                        const menuStatus = row.active ? 'Actif' : 'Désactivé';
                                        const badgeMeta = statusMeta[menuStatus] ?? statusMeta.Désactivé;

                                        return (
                                            <tr
                                                key={row.rowKey}
                                                draggable={dragEnabled && row.rowType === 'parent'}
                                                onDragStart={(event) => handleDragStart(event, row)}
                                                onDragOver={(event) => handleDragOver(event, row)}
                                                onDrop={(event) => handleDrop(event, row)}
                                                onDragEnd={() => {
                                                    setDraggedRow(null);
                                                    setDropIndicator(null);
                                                }}
                                                className={[
                                                    row.rowType === 'submenu' ? 'bg-slate-50/60' : 'bg-white',
                                                    dragEnabled ? 'cursor-move' : '',
                                                    'transition-all duration-200 ease-out',
                                                    draggedRow?.rowKey === row.rowKey ? 'scale-[0.985] opacity-50' : '',
                                                    dropIndicator?.rowKey === row.rowKey
                                                        ? dropIndicator.position === 'before'
                                                            ? 'shadow-[inset_0_2px_0_0_rgba(14,165,233,0.95)] bg-sky-50/70'
                                                            : 'shadow-[inset_0_-2px_0_0_rgba(14,165,233,0.95)] bg-sky-50/70'
                                                        : '',
                                                ].join(' ')}
                                            >
                                                <td className="px-6 py-4">
                                                    <span
                                                        className={[
                                                            'inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-400',
                                                            dragEnabled ? 'bg-white text-slate-700' : 'bg-slate-50 opacity-40',
                                                            'transition-transform duration-200 ease-out',
                                                            draggedRow?.rowKey === row.rowKey ? 'scale-110 text-sky-600' : '',
                                                        ].join(' ')}
                                                    >
                                                        <GripVertical className="h-4 w-4" />
                                                    </span>
                                                </td>

                                                <td className="px-6 py-4">
                                                    {row.rowType === 'parent' ? (
                                                        <button
                                                            type="button"
                                                            onClick={() => hasSubmenus && toggleParent(rowId)}
                                                            className={[
                                                                'flex w-full items-center gap-3 text-left',
                                                                hasSubmenus ? 'cursor-pointer' : 'cursor-default',
                                                            ].join(' ')}
                                                        >
                                                            <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-[#eef6fb] text-[#00559b]">
                                                                {hasSubmenus ? (
                                                                    isOpen ? (
                                                                        <ChevronDown className="h-4 w-4" />
                                                                    ) : (
                                                                        <ChevronRight className="h-4 w-4" />
                                                                    )
                                                                ) : (
                                                                    <span className="text-sm font-semibold">M</span>
                                                                )}
                                                            </span>

                                                            <span>
                                                                <span className="block font-semibold text-slate-900">
                                                                    {row.label}
                                                                </span>
                                                                <span className="block text-xs text-slate-500">
                                                                    {row.code}
                                                                </span>
                                                            </span>
                                                        </button>
                                                    ) : (
                                                        <div className="flex items-center gap-3 pl-8">
                                                            <span className="text-slate-400">↳</span>
                                                            <div>
                                                                <p className="font-semibold text-slate-900">
                                                                    {row.label}
                                                                </p>
                                                                <p className="text-xs text-slate-500">{row.code}</p>
                                                            </div>
                                                        </div>
                                                    )}
                                                </td>

                                                <td className="px-6 py-4 text-slate-600">
                                                    {row.rowType === 'parent' ? submenuCount : '—'}
                                                </td>

                                                <td className="px-6 py-4 font-medium text-slate-900">
                                                    {row.order}
                                                </td>

                                                <td className="px-6 py-4">
                                                    <Badge variant={badgeMeta.variant} className="rounded-full">
                                                        {badgeMeta.label}
                                                    </Badge>
                                                </td>

                                                <td className="px-6 py-4">
                                                    <div className="flex flex-wrap gap-2">
                                                        <Button
                                                            type="button"
                                                            variant="outline"
                                                            size="sm"
                                                            className="rounded-full border-slate-200"
                                                            onClick={() => toggleActive(row)}
                                                        >
                                                            {row.active ? (
                                                                <PowerOff className="h-4 w-4" />
                                                            ) : (
                                                                <Power className="h-4 w-4" />
                                                            )}
                                                            {row.active ? 'Désactiver' : 'Activer'}
                                                        </Button>

                                                        {row.rowType === 'parent' && (
                                                            <Button
                                                                asChild
                                                                variant="outline"
                                                                size="sm"
                                                                className="rounded-full border-slate-200"
                                                            >
                                                                <Link href={`/admin/modules/${row.code}/edit`}>
                                                                    <PencilLine className="h-4 w-4" />
                                                                    Modifier
                                                                </Link>
                                                            </Button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
