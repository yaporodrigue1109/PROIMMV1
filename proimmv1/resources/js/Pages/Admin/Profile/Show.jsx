import { Badge } from '../../../components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import AdminLayout from '../../../Layouts/AdminLayout';

const formatStatus = (value) => (Number(value) === 1 ? 'Active' : 'Disabled');

export default function Show({ admin }) {
    return (
        <AdminLayout title="Profile">
            <div className="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                <Card className="border-[#c8d4de]">
                    <CardHeader>
                        <CardDescription className="text-[#5f7182]">Identity</CardDescription>
                        <CardTitle className="text-2xl text-[#0f172a]">Account details</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <div className="grid gap-4 sm:grid-cols-2">
                            {[
                                ['Full name', admin?.name ?? 'Administrator'],
                                ['Email address', admin?.email ?? 'Not provided'],
                                ['Phone number', admin?.phone ?? 'Not provided'],
                                ['Status', formatStatus(admin?.statut)],
                            ].map(([label, value]) => (
                                <div key={label} className="rounded-2xl border border-[#c8d4de] bg-[#f7fbfe] p-4">
                                    <p className="text-sm text-[#5f7182]">{label}</p>
                                    <p className="mt-2 font-medium text-[#0f172a]">{value}</p>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                <Card className="border-[#00559b] bg-[linear-gradient(180deg,#00559b_0%,#003f72_100%)] text-white">
                    <CardHeader>
                        <CardDescription className="text-white/70">Account</CardDescription>
                        <CardTitle className="text-2xl text-white">Admin profile</CardTitle>
                    </CardHeader>

                    <CardContent className="space-y-4">
                        <p className="text-sm leading-6 text-white/80">
                            This page will later host avatar upload, password changes and profile settings.
                        </p>

                        <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <p className="text-xs uppercase tracking-[0.24em] text-white/60">Identifier</p>
                            <p className="mt-2 text-lg font-semibold">{admin?.id_admin ?? 'N/A'}</p>
                        </div>

                        <Badge variant="secondary" className="bg-white/10 text-white">
                            Ready for edit actions
                        </Badge>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
