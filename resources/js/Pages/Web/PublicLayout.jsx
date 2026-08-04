import { Link, usePage } from '@inertiajs/react';
import { Clock3, Mail, MapPin, Menu, Phone, X } from 'lucide-react';
import { useState } from 'react';

const navigation = [
    { label: 'Accueil', href: '/' },
    { label: 'Nos biens', href: '/biens' },
    { label: 'Tarifs', href: '/tarifs' },
    { label: 'À propos', href: '/a-propos' },
    { label: 'Contact', href: '/contact' },
];

export default function PublicLayout({ children }) {
    const { url } = usePage();
    const [menuOpen, setMenuOpen] = useState(false);
    const pathname = url.split('?')[0];

    return (
        <div className="min-h-screen bg-white font-sans text-[#16243d]">
            <div className="hidden bg-[#111f3d] text-white lg:block">
                <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-2.5 text-xs">
                    <div className="flex items-center gap-6 text-white/75">
                        <span className="flex items-center gap-2"><Mail className="h-3.5 w-3.5 text-[#76c206]" /> contact@prosimmobilier.ci</span>
                        <span className="flex items-center gap-2"><Phone className="h-3.5 w-3.5 text-[#76c206]" /> +225 07 00 00 00 00</span>
                    </div>
                    <div className="flex items-center gap-5 text-white/75">
                        <span className="flex items-center gap-2"><Clock3 className="h-3.5 w-3.5 text-[#76c206]" /> Lun - Ven : 08h00 - 18h00</span>
                        <Link href="/contact" className="transition hover:text-white">Aide & support</Link>
                    </div>
                </div>
            </div>

            <header className="sticky top-0 z-50 border-b border-slate-100 bg-white/95 shadow-[0_8px_30px_rgba(15,31,61,0.06)] backdrop-blur">
                <nav className="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 lg:px-6">
                    <Link href="/" className="flex items-center gap-3">
                        <img src="/admin/logo/playstore-icon-revised.png" alt="Pros Immobilier" className="h-11 w-11 rounded-xl object-contain" />
                        <div>
                            <p className="text-lg font-extrabold leading-none tracking-tight text-[#111f3d]">PROS IMMOBILIER</p>
                            <p className="mt-1 text-[10px] font-bold uppercase tracking-[0.22em] text-[#76c206]">Votre patrimoine, notre expertise</p>
                        </div>
                    </Link>

                    <div className="hidden items-center gap-8 lg:flex">
                        {navigation.map((item) => {
                            const active = pathname === item.href || (item.href !== '/' && pathname.startsWith(item.href));
                            return (
                                <Link key={item.href} href={item.href} className={`relative py-7 text-sm font-semibold transition ${active ? 'text-[#00559b]' : 'text-[#334155] hover:text-[#00559b]'}`}>
                                    {item.label}
                                    {active ? <span className="absolute inset-x-0 bottom-4 mx-auto h-0.5 w-5 rounded bg-[#76c206]" /> : null}
                                </Link>
                            );
                        })}
                    </div>

                    <div className="hidden items-center gap-3 lg:flex">
                        <Link href="/agence/login" className="rounded-full border border-[#00559b]/20 px-5 py-2.5 text-sm font-semibold text-[#00559b] transition hover:bg-[#eef7fd]">Connexion</Link>
                        <Link href="/contact" className="rounded-full bg-[#76c206] px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-[#76c206]/20 transition hover:-translate-y-0.5 hover:bg-[#66aa04]">Confier un bien</Link>
                    </div>

                    <button type="button" onClick={() => setMenuOpen((open) => !open)} className="rounded-xl border border-slate-200 p-2.5 text-[#111f3d] lg:hidden" aria-label="Ouvrir le menu">
                        {menuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
                    </button>
                </nav>

                {menuOpen ? (
                    <div className="border-t border-slate-100 bg-white px-5 py-5 shadow-xl lg:hidden">
                        <div className="flex flex-col gap-1">
                            {navigation.map((item) => <Link key={item.href} href={item.href} onClick={() => setMenuOpen(false)} className="rounded-xl px-4 py-3 text-sm font-semibold hover:bg-slate-50">{item.label}</Link>)}
                            <Link href="/agence/login" className="mt-3 rounded-xl bg-[#00559b] px-4 py-3 text-center text-sm font-bold text-white">Connexion agence</Link>
                        </div>
                    </div>
                ) : null}
            </header>

            {children}

            <footer className="bg-[#0d1931] text-white">
                <div className="mx-auto grid max-w-7xl gap-10 px-6 py-16 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <div className="flex items-center gap-3"><img src="/admin/logo/playstore-icon-revised.png" alt="" className="h-11 w-11 rounded-xl" /><span className="font-extrabold">PROS IMMOBILIER</span></div>
                        <p className="mt-5 text-sm leading-7 text-white/60">La plateforme qui rapproche agences, propriétaires et locataires pour une gestion immobilière plus simple et plus sûre.</p>
                        <div className="mt-6 flex gap-2"><span className="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-xs font-bold">f</span><span className="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-xs font-bold">ig</span></div>
                    </div>
                    <div><h3 className="font-bold">Navigation</h3><div className="mt-5 flex flex-col gap-3 text-sm text-white/60">{navigation.map((item) => <Link key={item.href} href={item.href} className="hover:text-[#76c206]">{item.label}</Link>)}</div></div>
                    <div><h3 className="font-bold">Nos services</h3><div className="mt-5 flex flex-col gap-3 text-sm text-white/60"><span>Gestion locative</span><span>Location de biens</span><span>Vente immobilière</span><span>Suivi des loyers</span><span>Accompagnement propriétaire</span></div></div>
                    <div><h3 className="font-bold">Nous contacter</h3><div className="mt-5 space-y-4 text-sm text-white/60"><p className="flex gap-3"><MapPin className="h-5 w-5 shrink-0 text-[#76c206]" /> Abidjan, Côte d’Ivoire</p><p className="flex gap-3"><Phone className="h-5 w-5 shrink-0 text-[#76c206]" /> +225 07 00 00 00 00</p><p className="flex gap-3"><Mail className="h-5 w-5 shrink-0 text-[#76c206]" /> contact@prosimmobilier.ci</p></div></div>
                </div>
                <div className="border-t border-white/10 px-6 py-5 text-center text-xs text-white/45">© {new Date().getFullYear()} Pros Immobilier. Tous droits réservés.</div>
            </footer>
        </div>
    );
}
