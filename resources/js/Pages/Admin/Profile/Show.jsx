import { useForm } from '@inertiajs/react';
import { LockKeyhole, Save, UserRound } from 'lucide-react';
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

export default function Show({ admin }) {
    const profile = useForm({ name: admin?.name ?? '', email: admin?.email ?? '', phone: admin?.phone ?? '' });
    const password = useForm({ current_password: '', password: '', password_confirmation: '' });
    const updateProfile = (event) => { event.preventDefault(); profile.patch('/admin/profile', { preserveScroll: true }); };
    const updatePassword = (event) => {
        event.preventDefault();
        password.patch('/admin/profile/password', { preserveScroll: true, onSuccess: () => password.reset() });
    };

    return (
        <AdminLayout title="Mon profil">
            <div className="space-y-6">
                <Card className="rounded-3xl border-slate-200 shadow-sm">
                    <CardContent className="flex items-center gap-4 p-6">
                        <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#00559b] text-xl font-bold text-white">{admin?.name?.slice(0, 1)?.toUpperCase() ?? 'A'}</div>
                        <div><p className="text-sm text-slate-500">Administrateur connecté</p><h1 className="text-2xl font-semibold text-slate-900">{admin?.name ?? 'Administrateur'}</h1><Badge className="mt-2" variant={Number(admin?.statut) === 1 ? 'success' : 'secondary'}>{Number(admin?.statut) === 1 ? 'Compte actif' : 'Compte inactif'}</Badge></div>
                    </CardContent>
                </Card>
                <div className="grid gap-6 xl:grid-cols-2">
                    <Card className="rounded-3xl border-slate-200 shadow-sm">
                        <CardHeader className="border-b border-slate-200"><CardTitle className="flex items-center gap-2"><UserRound className="h-5 w-5" /> Informations personnelles</CardTitle><CardDescription>Modifiez les informations de votre compte.</CardDescription></CardHeader>
                        <CardContent className="p-6"><form onSubmit={updateProfile} className="space-y-4">
                            <Field label="Nom complet" value={profile.data.name} onChange={(e) => profile.setData('name', e.target.value)} error={profile.errors.name} />
                            <Field label="Adresse email" type="email" value={profile.data.email} onChange={(e) => profile.setData('email', e.target.value)} error={profile.errors.email} />
                            <PhoneField label="Téléphone" value={profile.data.phone} onChange={(value) => profile.setData('phone', value)} error={profile.errors.phone} />
                            <Button style={{ marginTop: '2%',  }} disabled={profile.processing} className="rounded-xl">
                                <Save className="h-4 w-4" /> Enregistrer les modifications
                            </Button>
                        </form></CardContent>
                    </Card>
                    <Card className="rounded-3xl border-slate-200 shadow-sm">
                        <CardHeader className="border-b border-slate-200"><CardTitle className="flex items-center gap-2"><LockKeyhole className="h-5 w-5" /> Mot de passe</CardTitle><CardDescription>Utilisez au moins 8 caractères.</CardDescription></CardHeader>
                        <CardContent className="p-6"><form onSubmit={updatePassword} className="space-y-4">
                            <Field label="Mot de passe actuel" type="password" value={password.data.current_password} onChange={(e) => password.setData('current_password', e.target.value)} error={password.errors.current_password} />
                            <Field label="Nouveau mot de passe" type="password" value={password.data.password} onChange={(e) => password.setData('password', e.target.value)} error={password.errors.password} />
                            <Field label="Confirmer le nouveau mot de passe" type="password" value={password.data.password_confirmation} onChange={(e) => password.setData('password_confirmation', e.target.value)} />
                            <Button style={{ marginTop: '2%',  }} disabled={password.processing} variant="outline" className="rounded-xl"><LockKeyhole className="h-4 w-4" /> Modifier le mot de passe</Button>
                        </form></CardContent>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    );
}
