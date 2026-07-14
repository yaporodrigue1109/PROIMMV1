import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, BadgeCheck, CalendarDays, Mail, Phone, ShieldCheck, UserRound } from 'lucide-react';
import AgenceLayout from '../../../Layouts/AgenceLayout';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../../../components/ui/card';

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
    return (
        <AgenceLayout title="Mon profil">
            <Head title="Mon profil" />

            <div className="mx-auto flex max-w-5xl flex-col gap-6 pb-10">
                <div className="flex items-center justify-between gap-4">
                    <div className="min-w-0">
                        <h2 className="text-2xl font-semibold text-[#0f172a]">Mon profil</h2>
                       
                    </div>

                    <Button asChild variant="outline" className="rounded-xl border-[#c8d4de]">
                        <Link href="/agence/dashboard">
                            <ArrowLeft className="h-4 w-4" />
                            Retour
                        </Link>
                    </Button>
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
                                <Item label="Téléphone" value={user?.phone} icon={Phone} />
                                <Item label="Dernière connexion" value={user?.updated_at ? new Intl.DateTimeFormat('fr-FR').format(new Date(user.updated_at)) : '—'} icon={CalendarDays} />
                            </div>
                        </CardContent>
                    </Card>

                   
                </div>
            </div>
        </AgenceLayout>
    );
}
