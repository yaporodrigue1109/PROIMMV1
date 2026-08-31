import { useForm } from '@inertiajs/react';
import { ShieldCheck, UserPlus } from 'lucide-react';
import AdminLayout from '../../../Layouts/AdminLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Input } from '../../../components/ui/input';
import { PhoneInput } from '../../../components/ui/phone-input';

function Field({ label, error, ...props }) {
    return <label className="space-y-2 text-sm font-medium text-slate-700"><span>{label}</span><Input {...props} className="h-11 rounded-xl border-slate-200" />{error ? <span className="block text-xs text-red-600">{error}</span> : null}</label>;
}

function PhoneField({ label, error, value, onChange }) {
    return (
        <label className="space-y-2 text-sm font-medium text-slate-700">
            <span>{label}</span>
            <PhoneInput value={value} onChange={onChange} className="h-11 rounded-xl" placeholder="07 00 00 00 00" />
            {error ? <span className="block text-xs text-red-600">{error}</span> : null}
        </label>
    );
}

export default function Index({ administrateurs = [] }) {
    const form = useForm({ name: '', email: '', phone: '', password: '', password_confirmation: '', statut: true });
    const submit = (event) => { event.preventDefault(); form.post('/admin/administrateurs', { preserveScroll: true, onSuccess: () => form.reset() }); };

    return (
        <AdminLayout title="Administrateurs">
            <div className="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                <Card className="rounded-3xl border-slate-200 shadow-sm">
                    <CardHeader className="border-b border-slate-200"><CardTitle>Administrateurs</CardTitle><CardDescription>{administrateurs.length} compte(s) enregistré(s)</CardDescription></CardHeader>
                    <CardContent className="space-y-3 p-6">
                        {administrateurs.map((item) => <div key={item.id_admin} className="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div className="flex items-center gap-3"><div className="flex h-11 w-11 items-center justify-center rounded-xl bg-[#00559b] font-semibold text-white">{item.name?.slice(0, 1)?.toUpperCase() ?? 'A'}</div><div><p className="font-semibold text-slate-900">{item.name}</p><p className="text-sm text-slate-500">{item.email}</p>{item.phone ? <p className="text-xs text-slate-500">{item.phone}</p> : null}</div></div>
                            <Badge variant={Number(item.statut) === 1 ? 'success' : 'secondary'}>{Number(item.statut) === 1 ? 'Actif' : 'Inactif'}</Badge>
                        </div>)}
                    </CardContent>
                </Card>
                <Card className="h-fit rounded-3xl border-slate-200 shadow-sm">
                    <CardHeader className="border-b border-slate-200"><CardTitle className="flex items-center gap-2"><UserPlus className="h-5 w-5" /> Ajouter un administrateur</CardTitle><CardDescription>Créez un nouveau compte d'accès à l'administration.</CardDescription></CardHeader>
                    <CardContent className="p-6"><form onSubmit={submit} className="space-y-4">
                        <Field label="Nom complet" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} error={form.errors.name} />
                        <Field label="Adresse email" type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} error={form.errors.email} />
                        <PhoneField label="Téléphone" value={form.data.phone} onChange={(value) => form.setData('phone', value)} error={form.errors.phone} />
                        <Field label="Mot de passe" type="password" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} error={form.errors.password} />
                        <Field label="Confirmer le mot de passe" type="password" value={form.data.password_confirmation} onChange={(e) => form.setData('password_confirmation', e.target.value)} />
                        <label className="flex items-center gap-3 rounded-xl border border-slate-200 p-3 text-sm text-slate-700"><input type="checkbox" checked={form.data.statut} onChange={(e) => form.setData('statut', e.target.checked)} /> Compte actif immédiatement</label>
                        <Button disabled={form.processing} className="w-full rounded-xl bg-[#00559b] text-white"><ShieldCheck className="h-4 w-4" /> Créer l'administrateur</Button>
                    </form></CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
