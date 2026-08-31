import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, CalendarDays, LockKeyhole, Mail, MapPin, PencilLine, Phone, ShieldCheck, UserRound } from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../../../components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '../../../components/ui/dialog';
import { Input } from '../../../components/ui/input';
import { Label } from '../../../components/ui/label';

function Item({ label, value, icon: Icon }) {
    return (
        <div className="rounded-2xl border border-[#e2e8f0] bg-[#f8fafc] p-4">
            <div className="flex items-center gap-2 text-xs uppercase tracking-wide text-[#94a3b8]">
                <Icon className="h-3.5 w-3.5 text-[#00559b]" />
                <span>{label}</span>
            </div>
            <strong className="mt-2 block text-sm text-[#0f172a]">{value || '—'}</strong>
        </div>
    );
}

export default function Index({ user }) {
    const [editOpen, setEditOpen] = useState(false);
    const [passwordOpen, setPasswordOpen] = useState(false);
    const form = useForm({
        name: user?.name ?? '',
        email: user?.email ?? '',
        tel1: user?.tel1 ?? '',
        tel2: user?.tel2 ?? '',
        adresse: user?.adresse ?? '',
    });
    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const openEditor = () => {
        form.setData({
            name: user?.name ?? '',
            email: user?.email ?? '',
            tel1: user?.tel1 ?? '',
            tel2: user?.tel2 ?? '',
            adresse: user?.adresse ?? '',
        });
        form.clearErrors();
        setEditOpen(true);
    };

    const submit = (event) => {
        event.preventDefault();
        form.patch('/agence/profile', {
            preserveScroll: true,
            onSuccess: () => setEditOpen(false),
        });
    };

    const openPasswordEditor = () => {
        passwordForm.reset();
        passwordForm.clearErrors();
        setPasswordOpen(true);
    };

    const submitPassword = (event) => {
        event.preventDefault();
        passwordForm.patch('/agence/profile/password', {
            preserveScroll: true,
            onSuccess: () => {
                passwordForm.reset();
                setPasswordOpen(false);
            },
        });
    };

    return (
        <AgenceLayout title="Mon profil">
            <Head title="Mon profil" />

            <div className="mx-auto flex max-w-5xl flex-col gap-6 pb-10">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="min-w-0">
                        <h2 className="text-2xl font-semibold text-[#0f172a]">Mon profil</h2>
                       
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        <Button type="button" onClick={openEditor} className="rounded-xl bg-[#00559b] hover:bg-[#00477f]">
                            <PencilLine className="h-4 w-4" />
                            Modifier le profil
                        </Button>
                        <Button type="button" variant="outline" onClick={openPasswordEditor} className="rounded-xl border-[#c8d4de]">
                            <LockKeyhole className="h-4 w-4" />
                            Modifier le mot de passe
                        </Button>
                        <Button asChild variant="outline" className="rounded-xl border-[#c8d4de]">
                            <Link href="/agence/dashboard">
                                <ArrowLeft className="h-4 w-4" />
                                Retour
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <Card className="rounded-3xl border-[#c8d4de] bg-white shadow-sm">
                        <CardHeader className="border-b border-[#e2e8f0]">
                            <CardTitle className="text-lg text-[#0f172a]">Informations du compte</CardTitle>
                        </CardHeader>

                        <CardContent className="p-6">
                            <div className="mt-4 grid gap-4 sm:grid-cols-2">
                                <Item label="Nom complet" value={user?.name} icon={UserRound} />
                                <Item label="Email" value={user?.email} icon={Mail} />
                                <Item label="Téléphone" value={user?.tel1} icon={Phone} />
                                <Item label="Téléphone secondaire" value={user?.tel2} icon={Phone} />
                                <Item label="Adresse" value={user?.adresse} icon={MapPin} />
                                <Item label="Dernière connexion" value={user?.updated_at ? new Intl.DateTimeFormat('fr-FR').format(new Date(user.updated_at)) : '—'} icon={CalendarDays} />
                            </div>
                        </CardContent>
                    </Card>

                   
                </div>
            </div>

            <Dialog open={editOpen} onOpenChange={setEditOpen}>
                <DialogContent className="max-h-[90vh] max-w-2xl overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Modifier le profil</DialogTitle>
                        <DialogDescription>
                            Mettez à jour les informations associées à votre compte.
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={submit} className="mt-5 space-y-5">
                        <ProfileField label="Nom complet" error={form.errors.name}>
                            <Input
                                required
                                autoFocus
                                autoComplete="name"
                                value={form.data.name}
                                onChange={(event) => form.setData('name', event.target.value)}
                            />
                        </ProfileField>

                        <ProfileField label="Adresse e-mail" error={form.errors.email}>
                            <Input
                                required
                                type="email"
                                autoComplete="email"
                                value={form.data.email}
                                onChange={(event) => form.setData('email', event.target.value)}
                            />
                        </ProfileField>

                        <ProfileField label="Téléphone" error={form.errors.tel1}>
                            <Input
                                required
                                type="tel"
                                autoComplete="tel"
                                value={form.data.tel1}
                                onChange={(event) => form.setData('tel1', event.target.value)}
                            />
                        </ProfileField>

                        <ProfileField label="Téléphone secondaire" error={form.errors.tel2}>
                            <Input
                                type="tel"
                                autoComplete="tel-national"
                                value={form.data.tel2}
                                onChange={(event) => form.setData('tel2', event.target.value)}
                                placeholder="Optionnel"
                            />
                        </ProfileField>

                        <ProfileField label="Adresse" error={form.errors.adresse}>
                            <Input
                                autoComplete="street-address"
                                value={form.data.adresse}
                                onChange={(event) => form.setData('adresse', event.target.value)}
                                placeholder="Votre adresse"
                            />
                        </ProfileField>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" onClick={() => setEditOpen(false)} disabled={form.processing}>
                                Annuler
                            </Button>
                            <Button type="submit" disabled={form.processing} className="bg-[#00559b] hover:bg-[#00477f]">
                                {form.processing ? 'Enregistrement…' : 'Enregistrer les modifications'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={passwordOpen} onOpenChange={setPasswordOpen}>
                <DialogContent className="max-w-lg">
                    <DialogHeader>
                        <div className="mb-1 flex items-center gap-3">
                            <span className="rounded-xl bg-[#eaf3fb] p-2 text-[#00559b]">
                                <ShieldCheck className="h-5 w-5" />
                            </span>
                            <DialogTitle>Modifier le mot de passe</DialogTitle>
                        </div>
                        <DialogDescription>
                            Confirmez votre mot de passe actuel avant d’en choisir un nouveau.
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={submitPassword} className="mt-5 space-y-5">
                        <ProfileField label="Mot de passe actuel" error={passwordForm.errors.current_password}>
                            <Input
                                required
                                autoFocus
                                type="password"
                                autoComplete="current-password"
                                value={passwordForm.data.current_password}
                                onChange={(event) => passwordForm.setData('current_password', event.target.value)}
                            />
                        </ProfileField>

                        <ProfileField label="Nouveau mot de passe" error={passwordForm.errors.password}>
                            <Input
                                required
                                type="password"
                                minLength={8}
                                autoComplete="new-password"
                                value={passwordForm.data.password}
                                onChange={(event) => passwordForm.setData('password', event.target.value)}
                            />
                        </ProfileField>

                        <ProfileField label="Confirmation du mot de passe" error={passwordForm.errors.password_confirmation}>
                            <Input
                                required
                                type="password"
                                minLength={8}
                                autoComplete="new-password"
                                value={passwordForm.data.password_confirmation}
                                onChange={(event) => passwordForm.setData('password_confirmation', event.target.value)}
                            />
                        </ProfileField>

                        <DialogFooter className="pt-2">
                            <Button type="button" variant="outline" onClick={() => setPasswordOpen(false)} disabled={passwordForm.processing}>
                                Annuler
                            </Button>
                            <Button type="submit" disabled={passwordForm.processing} className="bg-[#00559b] hover:bg-[#00477f]">
                                <LockKeyhole className="h-4 w-4" />
                                {passwordForm.processing ? 'Modification…' : 'Modifier le mot de passe'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AgenceLayout>
    );
}

function ProfileField({ label, error, children }) {
    return (
        <div className="space-y-2">
            <Label className="block">{label}</Label>
            {children}
            {error ? <p className="text-sm text-[#b42318]">{error}</p> : null}
        </div>
    );
}
