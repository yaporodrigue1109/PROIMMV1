import { Head, useForm } from '@inertiajs/react';
import { LockKeyhole, Mail, ShieldCheck } from 'lucide-react';
import logo from '../../../../../admin/logo/playstore-icon-revised.png';
import { Badge } from '../../../components/ui/badge';
import { Button } from '../../../components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../../components/ui/card';
import { Checkbox } from '../../../components/ui/checkbox';
import { Input } from '../../../components/ui/input';
import { Label } from '../../../components/ui/label';
import { Separator } from '../../../components/ui/separator';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (event) => {
        event.preventDefault();
        post('/admin/login');
    };

    return (
        <div className="flex min-h-screen items-center justify-center px-4 py-10">
            <Head title="Connexion" />

         

                <div className="p-8 md:p-12">
                    <Card className="border-0 shadow-none">
                        <CardHeader className="space-y-4">
                            <div className="flex items-center gap-4">
                                <img
                                    src={logo}
                                    alt="Pros Immobilier"
                                    className="h-16 w-16 rounded-2xl object-contain shadow-sm ring-1 ring-[#c8d4de]"
                                />
                                <div>
                                    <CardTitle className="text-3xl">Connexion</CardTitle>
                                    <CardDescription className="text-base">
                                        Bienvenue. Identifiez-vous pour accéder à votre espace de gestion.
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent>
                            <form onSubmit={submit} className="space-y-5">
                                <div className="space-y-2">
                                    <Label htmlFor="email">Adresse e-mail</Label>
                                    <div className="relative">
                                        <Mail className="pointer-events-none absolute left-3 top-3.5 h-4 w-4 text-[#00559b]" />
                                        <Input
                                            id="email"
                                            type="email"
                                            value={data.email}
                                            onChange={(event) => setData('email', event.target.value)}
                                            className="pl-9"
                                            placeholder="nom@entreprise.com"
                                        />
                                    </div>
                                    {errors.email && <p className="text-sm text-rose-600">{errors.email}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="password">Mot de passe</Label>
                                    <div className="relative">
                                        <LockKeyhole className="pointer-events-none absolute left-3 top-3.5 h-4 w-4 text-[#00559b]" />
                                        <Input
                                            id="password"
                                            type="password"
                                            value={data.password}
                                            onChange={(event) => setData('password', event.target.value)}
                                            className="pl-9"
                                            placeholder="************"
                                        />
                                    </div>
                                </div>

                                <div className="flex items-center justify-between gap-4">
                                    <label className="flex cursor-pointer items-center gap-2 text-sm text-[#0f172a]">
                                        <Checkbox
                                            id="remember"
                                            checked={data.remember}
                                            onChange={(event) => setData('remember', event.target.checked)}
                                        />
                                        <span>Se souvenir de moi</span>
                                    </label>

                                    <a href="#" className="text-sm font-medium text-[#00559b] hover:underline">
                                        Mot de passe oublié ?
                                    </a>
                                </div>

                                <Separator />

                                <Button type="submit" className="h-11 w-full" disabled={processing}>
                                    {processing ? 'Connexion...' : 'Se connecter'}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
       
    );
}
