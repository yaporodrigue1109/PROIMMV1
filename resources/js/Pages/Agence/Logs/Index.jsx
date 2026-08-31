import { useMemo, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { History, KeyRound, RotateCcw, UserRound, UsersRound } from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../../../components/ui/card';
import { Badge } from '../../../components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../../../components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
import { agenceButtonStyles } from '../../../lib/buttonStyles';

const date = (value) => value ? new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(new Date(value)) : '—';

export default function Index({ owners = [], tenants = [], personnel = [], availableDoors = [] }) {
    const [doorByTenant, setDoorByTenant] = useState({});
    const doorIds = useMemo(() => new Set(availableDoors.map((door) => String(door.id))), [availableDoors]);

    const restoreTenant = (tenant) => {
        const previousAvailable = tenant.previous_door_id && doorIds.has(String(tenant.previous_door_id));
        const selectedDoor = doorByTenant[tenant.id] || (previousAvailable ? String(tenant.previous_door_id) : '');
        if (!selectedDoor) return;
        const previousMessage = selectedDoor === String(tenant.previous_door_id)
            ? `Réaffecter ${tenant.name} à son ancienne porte ?`
            : `Réactiver ${tenant.name} dans la porte sélectionnée ?`;
        if (!window.confirm(previousMessage)) return;
        router.patch(`/agence/logs/locataires/${tenant.id}/restore`, { porte_id: selectedDoor }, { preserveScroll: true });
    };

    return (
        <AgenceLayout title="Gestion des logs">
            <Head title="Gestion des logs" />
            <div className="mx-auto flex w-full max-w-7xl flex-col gap-6">
                <div>
                    <h2 className="flex items-center gap-2 text-2xl font-semibold text-[#0f172a]"><History className="h-6 w-6" /> Gestion des logs</h2>
                    <p className="mt-1 text-sm text-[#5f7182]">Comptes désactivés et outils de réactivation.</p>
                </div>

                <Tabs defaultValue="tenants">
                    <TabsList className="rounded-xl border border-[#c8d4de] bg-[#f1f5f9]">
                        <TabsTrigger value="tenants">Locataires ({tenants.length})</TabsTrigger>
                        <TabsTrigger value="owners">Propriétaires ({owners.length})</TabsTrigger>
                        <TabsTrigger value="personnel">Personnel ({personnel.length})</TabsTrigger>
                    </TabsList>

                    <TabsContent value="tenants" className="mt-5 grid gap-4">
                        {tenants.length === 0 ? <Empty label="Aucun locataire désactivé" /> : tenants.map((tenant) => {
                            const previousAvailable = tenant.previous_door_id && doorIds.has(String(tenant.previous_door_id));
                            const selected = doorByTenant[tenant.id] || (previousAvailable ? String(tenant.previous_door_id) : '');
                            return (
                                <Card key={tenant.id} className="rounded-2xl border-[#c8d4de]">
                                    <CardContent className="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
                                        <div className="flex gap-3">
                                            <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eaf4fb] text-[#00559b]"><UserRound className="h-5 w-5" /></span>
                                            <div><p className="font-semibold text-[#0f172a]">{tenant.name}</p><p className="text-xs text-[#5f7182]">{tenant.code} · {tenant.phone || 'Sans téléphone'} · Désactivé le {date(tenant.disabled_at)}</p><p className="mt-1 text-xs text-[#94a3b8]">Ancien logement : {tenant.previous_property || '—'} · Porte {tenant.previous_door || '—'}</p></div>
                                        </div>
                                        <div className="flex min-w-0 flex-col gap-2 sm:flex-row sm:items-center">
                                            <Select value={selected} onValueChange={(value) => setDoorByTenant((current) => ({ ...current, [tenant.id]: value }))}>
                                                <SelectTrigger className="w-full sm:w-[330px]"><SelectValue placeholder="Choisir une porte libre" /></SelectTrigger>
                                                <SelectContent>{availableDoors.map((door) => <SelectItem key={door.id} value={String(door.id)}>{door.id === tenant.previous_door_id ? `Ancienne porte · ${door.label}` : door.label}</SelectItem>)}</SelectContent>
                                            </Select>
                                            <Button disabled={!selected} className={agenceButtonStyles.primary} onClick={() => restoreTenant(tenant)}><RotateCcw className="h-4 w-4" /> Réactiver</Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </TabsContent>

                    <TabsContent value="owners" className="mt-5 grid gap-4">
                        {owners.length === 0 ? <Empty label="Aucun propriétaire désactivé" /> : owners.map((owner) => (
                            <Card key={owner.link_id} className="rounded-2xl border-[#c8d4de]">
                                <CardContent className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                                    <div className="flex gap-3"><span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#eef8df] text-[#4d8500]"><KeyRound className="h-5 w-5" /></span><div><p className="font-semibold text-[#0f172a]">{owner.name}</p><p className="text-xs text-[#5f7182]">{owner.code} · {owner.phone || 'Sans téléphone'} · Désactivé le {date(owner.disabled_at)}</p>{owner.deleted ? <Badge variant="outline" className="mt-1">Supprimé</Badge> : null}</div></div>
                                    <Button className={agenceButtonStyles.primary} onClick={() => window.confirm(`Réactiver ${owner.name} avec ses propriétés ?`) && router.patch(`/agence/logs/proprietaires/${owner.link_id}/restore`, {}, { preserveScroll: true })}><RotateCcw className="h-4 w-4" /> Réactiver avec ses propriétés</Button>
                                </CardContent>
                            </Card>
                        ))}
                    </TabsContent>

                    <TabsContent value="personnel" className="mt-5 grid gap-4">
                        {personnel.length === 0 ? <Empty label="Aucun membre du personnel désactivé" /> : personnel.map((member) => (
                            <Card key={member.id} className="rounded-2xl border-[#c8d4de]">
                                <CardContent className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                                    <div className="flex gap-3"><span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#fff2e6] text-[#c2410c]"><UsersRound className="h-5 w-5" /></span><div><p className="font-semibold text-[#0f172a]">{member.name}</p><p className="text-xs text-[#5f7182]">{member.role} · {member.email || member.phone || 'Contact non renseigné'} · Désactivé le {date(member.disabled_at)}</p></div></div>
                                    <Button className={agenceButtonStyles.primary} onClick={() => window.confirm(`Réactiver le compte de ${member.name} ?`) && router.patch(`/agence/logs/personnel/${member.id}/restore`, {}, { preserveScroll: true })}><RotateCcw className="h-4 w-4" /> Réactiver</Button>
                                </CardContent>
                            </Card>
                        ))}
                    </TabsContent>
                </Tabs>
            </div>
        </AgenceLayout>
    );
}

function Empty({ label }) {
    return <Card className="rounded-2xl border-dashed border-[#c8d4de]"><CardHeader><CardTitle className="text-center text-sm text-[#5f7182]">{label}</CardTitle></CardHeader></Card>;
}
