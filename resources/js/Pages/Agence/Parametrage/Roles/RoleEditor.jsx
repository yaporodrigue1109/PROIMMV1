import { ArrowLeft, Check, KeyRound, Layers3, LockKeyhole, Save, Search } from 'lucide-react';
import { Badge } from '../../../../components/ui/badge';
import { Button } from '../../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../../components/ui/card';
import { Input } from '../../../../components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../../components/ui/select';
import { cn } from '../../../../lib/utils';

const inputClassName =
    'flex h-11 w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white placeholder:text-[#8798a5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

function Field({ label, required = false, children }) {
    return (
        <label className="block space-y-2 text-sm font-medium text-[#0f172a]">
            <span>{label}{required ? <span className="ml-1 text-[#b42318]">*</span> : null}</span>
            {children}
        </label>
    );
}

export default function RoleEditor({
    mode,
    roles,
    newRole,
    setNewRole,
    roleTemplate,
    onRoleTemplateChange,
    errors,
    selectedRole,
    onRoleChange,
    permissionDraft,
    permissionSearch,
    setPermissionSearch,
    filteredPermissionGroups,
    allPermissionKeys,
    sensitivePermissionKeys,
    isProtectedRole = false,
    onSetPermissions,
    onTogglePermission,
    onToggleGroup,
    onSubmit,
    onBack,
}) {
    const isCreate = mode === 'create';
    const editorVisible = isCreate || Boolean(selectedRole);

    return (
        <div className="mx-auto flex max-w-5xl flex-col gap-6 px-4 pb-10 sm:px-6 lg:px-8">
            <header className="flex items-center gap-3">
                <Button type="button" variant="outline" size="icon" className="rounded-xl border-[#c8d4de]" onClick={onBack}>
                    <ArrowLeft className="h-4 w-4" />
                </Button>
                <div>
                    <h1 className="text-xl font-semibold text-[#0f172a]">
                        {isCreate ? 'Créer un rôle' : 'Configurer les permissions'}
                    </h1>
                    <p className="text-sm text-[#5f7182]">
                        {isCreate
                            ? 'Créez un profil personnalisé et choisissez ses accès.'
                            : 'Sélectionnez un rôle puis définissez précisément ses accès.'}
                    </p>
                </div>
            </header>

            <form onSubmit={onSubmit} className="space-y-6">
                <Card className="rounded-3xl border-[#c8d4de] bg-white shadow-sm">
                    <CardHeader className="flex-row items-center gap-3 border-b border-[#e2e8f0] py-4">
                        <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eaf4fb] text-[#00559b]">
                            <KeyRound className="h-5 w-5" />
                        </span>
                        <div>
                            <CardTitle className="text-sm text-[#0f172a]">{isCreate ? 'Informations et accès du rôle' : 'Droits d’accès'}</CardTitle>
                            <CardDescription className="text-xs text-[#5f7182]">Les autorisations sont organisées par module.</CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent className="p-4 sm:p-6">
                        {isProtectedRole ? (
                            <div className="mb-4 flex gap-3 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                                <LockKeyhole className="mt-0.5 h-4 w-4 shrink-0" />
                                <p>
                                    Vous modifiez votre rôle Responsable. Vous pouvez changer ses accès, mais les permissions du module Paramétrage restent obligatoires afin de conserver la gestion de l’agence.
                                </p>
                            </div>
                        ) : null}
                        <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
                            {isCreate ? (
                                <div className="space-y-4">
                                    <Field label="Modèle de départ">
                                        <Select value={roleTemplate} onValueChange={onRoleTemplateChange}>
                                            <SelectTrigger className={inputClassName}><SelectValue placeholder="Choisir un modèle" /></SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="empty">Partir de zéro</SelectItem>
                                                {roles.map((role) => <SelectItem key={role.role_id} value={String(role.role_id)}>Dupliquer — {role.name}</SelectItem>)}
                                            </SelectContent>
                                        </Select>
                                    </Field>
                                    <div className="grid gap-4 lg:grid-cols-2">
                                        <Field label="Nom du rôle" required>
                                            <Input value={newRole.name} onChange={(event) => setNewRole((current) => ({ ...current, name: event.target.value }))} placeholder="Ex. Gestionnaire locatif" className={inputClassName} />
                                            {errors.name ? <span className="block text-xs text-[#b42318]">{errors.name}</span> : null}
                                        </Field>
                                        <Field label="Description">
                                            <Input value={newRole.description} onChange={(event) => setNewRole((current) => ({ ...current, description: event.target.value }))} placeholder="Mission principale de ce rôle" className={inputClassName} />
                                        </Field>
                                    </div>
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    <Field label="Rôle à modifier" required>
                                        <Select value={selectedRole} onValueChange={onRoleChange}>
                                            <SelectTrigger className={inputClassName}><SelectValue placeholder="Sélectionner un rôle personnalisé" /></SelectTrigger>
                                            <SelectContent>
                                                {roles.map((role) => <SelectItem key={role.role_id} value={String(role.role_id)}>{role.name}</SelectItem>)}
                                            </SelectContent>
                                        </Select>
                                    </Field>
                                    {selectedRole ? (
                                        <div className="grid gap-4 lg:grid-cols-2">
                                            <Field label="Nom du rôle" required>
                                                <Input value={newRole.name} onChange={(event) => setNewRole((current) => ({ ...current, name: event.target.value }))} placeholder="Ex. Gestionnaire locatif" className={inputClassName} />
                                                {errors.name ? <span className="block text-xs text-[#b42318]">{errors.name}</span> : null}
                                            </Field>
                                            <Field label="Description">
                                                <Input value={newRole.description} onChange={(event) => setNewRole((current) => ({ ...current, description: event.target.value }))} placeholder="Mission principale de ce rôle" className={inputClassName} />
                                                {errors.description ? <span className="block text-xs text-[#b42318]">{errors.description}</span> : null}
                                            </Field>
                                        </div>
                                    ) : null}
                                </div>
                            )}

                            {editorVisible ? (
                                <div className="mt-4 flex flex-wrap items-center justify-end gap-2 border-t border-[#e2e8f0] pt-4">
                                    <Badge variant="outline" className="mr-auto rounded-full border-[#8dbddd] bg-white text-[#00559b]">{permissionDraft.length} accès sélectionnés</Badge>
                                    {permissionDraft.some((permission) => sensitivePermissionKeys.has(permission)) ? (
                                        <Badge variant="outline" className="rounded-full border-[#f0c36a] bg-[#fffbeb] text-[#9a6700]">
                                            <LockKeyhole className="mr-1 h-3 w-3" />
                                            {permissionDraft.filter((permission) => sensitivePermissionKeys.has(permission)).length} sensibles
                                        </Badge>
                                    ) : null}
                                    <Button type="button" variant="outline" className="h-9 rounded-xl border-[#c8d4de] bg-white text-xs" onClick={() => onSetPermissions([...allPermissionKeys])}>Tout autoriser</Button>
                                    <Button type="button" variant="outline" className="h-9 rounded-xl border-[#c8d4de] bg-white text-xs" onClick={() => onSetPermissions([])}>Tout retirer</Button>
                                </div>
                            ) : null}
                        </div>

                        {editorVisible ? (
                            <div className="mt-5 space-y-4">
                                <div className="relative">
                                    <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#94a3b8]" />
                                    <Input value={permissionSearch} onChange={(event) => setPermissionSearch(event.target.value)} placeholder="Rechercher une permission ou un module..." className={cn(inputClassName, 'pl-10')} />
                                </div>

                                {filteredPermissionGroups.length ? (
                                    <div className="overflow-x-auto rounded-2xl border border-[#e2e8f0] bg-white">
                                        <table className="w-full min-w-[640px] border-collapse text-left text-sm">
                                            <thead className="bg-[#f8fafc] text-xs uppercase tracking-wide text-[#5f7182]">
                                                <tr><th className="border-b border-[#e2e8f0] px-4 py-3">Permission</th><th className="w-40 border-b border-[#e2e8f0] px-4 py-3 text-center">Autorisation</th></tr>
                                            </thead>
                                            <tbody>
                                                {filteredPermissionGroups.flatMap((group) => {
                                                    const selectedCount = group.permissions.filter((permission) => permissionDraft.includes(permission.key)).length;
                                                    const groupSelected = selectedCount === group.permissions.length;
                                                    return [
                                                        <tr key={`${group.label}-heading`} className="bg-[#eaf4fb]/60">
                                                            <td colSpan={2} className="border-b border-[#d7e6f0] px-4 py-3">
                                                                <div className="flex items-center justify-between gap-4">
                                                                    <div className="flex items-center gap-3"><Layers3 className="h-4 w-4 text-[#00559b]" /><div><p className="font-semibold text-[#00559b]">{group.label}</p><p className="text-xs text-[#5f7182]">{selectedCount} sur {group.permissions.length} sélectionnés</p></div></div>
                                                                    <button type="button" onClick={() => onToggleGroup(group)} disabled={group.permissions.every((permission) => permission.is_locked)} className="text-xs font-semibold text-[#00559b] hover:underline disabled:cursor-not-allowed disabled:text-[#94a3b8] disabled:no-underline">{group.permissions.every((permission) => permission.is_locked) ? 'Verrouillé' : groupSelected ? 'Tout décocher' : 'Tout cocher'}</button>
                                                                </div>
                                                            </td>
                                                        </tr>,
                                                        ...group.permissions.map((permission) => {
                                                            const checked = permissionDraft.includes(permission.key);
                                                            return (
                                                                <tr key={permission.key} className={cn('border-b border-[#edf2f6]', checked ? 'bg-[#f4faff]' : 'bg-white')}>
                                                                    <td className="px-4 py-3 font-medium text-[#0f172a]">
                                                                        <div className="flex items-center gap-2">
                                                                            <span>{permission.label}</span>
                                                                            {sensitivePermissionKeys.has(permission.key) ? (
                                                                                <Badge variant="outline" className="rounded-full border-[#f0c36a] bg-[#fffbeb] px-2 py-0 text-[10px] text-[#9a6700]">
                                                                                    <LockKeyhole className="mr-1 h-3 w-3" />Sensible
                                                                                </Badge>
                                                                            ) : null}
                                                                            {permission.is_locked ? (
                                                                                <Badge variant="outline" className="rounded-full border-amber-300 bg-amber-50 px-2 py-0 text-[10px] text-amber-800">
                                                                                    <LockKeyhole className="mr-1 h-3 w-3" />Obligatoire
                                                                                </Badge>
                                                                            ) : null}
                                                                        </div>
                                                                    </td>
                                                                    <td className="px-4 py-3 text-center">
                                                                        <button type="button" disabled={permission.is_locked} onClick={() => onTogglePermission(permission.key)} className={cn('inline-flex h-7 w-7 items-center justify-center rounded-lg border', checked ? 'border-[#00559b] bg-[#00559b] text-white' : 'border-[#c8d4de] bg-white text-transparent', permission.is_locked && 'cursor-not-allowed opacity-70')} aria-label={`${permission.is_locked ? 'Permission obligatoire' : checked ? 'Retirer' : 'Accorder'} ${permission.label}`}><Check className="h-4 w-4" /></button>
                                                                    </td>
                                                                </tr>
                                                            );
                                                        }),
                                                    ];
                                                })}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : <div className="rounded-2xl border border-dashed border-[#c8d4de] bg-[#f8fafc] px-6 py-12 text-center text-sm text-[#5f7182]">Aucune permission trouvée.</div>}
                            </div>
                        ) : <div className="py-16 text-center text-sm text-[#5f7182]">Sélectionnez un rôle pour afficher ses permissions.</div>}
                    </CardContent>
                </Card>

                <div className="flex justify-end">
                    {editorVisible ? <Button type="submit" disabled={isCreate && !newRole.name.trim()} className="rounded-xl bg-[#00559b] text-white hover:bg-[#004980]"><Save className="h-4 w-4" />{isCreate ? 'Créer le rôle' : 'Enregistrer les permissions'}</Button> : null}
                </div>
            </form>
        </div>
    );
}
