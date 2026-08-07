import { ArrowUpRight, MapPin, Maximize2 } from 'lucide-react';
import { Link } from '@inertiajs/react';

const fallbackImages = [
    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=80&w=1000',
    'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=80&w=1000',
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=80&w=1000',
];

export default function PropertyCards({ properties = [] }) {
    return (
        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {properties.map((property, index) => (
                <Link key={property.id} href={`/biens/${property.id}`} className="group overflow-hidden rounded-[8px] bg-white shadow-[0_14px_45px_rgba(17,31,61,0.08)] transition hover:-translate-y-1" aria-label={`Voir le détail de ${property.title}`}>
                    <div className="relative h-60 overflow-hidden bg-slate-100">
                        <img src={property.image || fallbackImages[index % fallbackImages.length]} alt={property.title} className="h-full w-full object-cover transition duration-700 group-hover:scale-105" />
                        <span className={`absolute left-4 top-4 rounded-full px-3 py-1.5 text-[11px] font-bold uppercase tracking-wide text-white ${property.mode === 'location' ? 'bg-[#00559b]' : 'bg-[#76c206]'}`}>{property.mode === 'location' ? 'À louer' : 'À vendre'}</span>
                        <span className="absolute right-4 top-4 rounded-full bg-white/90 p-2 text-[#111f3d] backdrop-blur"><ArrowUpRight className="h-4 w-4" /></span>
                    </div>
                    <div className="p-6">
                        <h3 className="truncate text-xl font-bold text-[#111f3d]">{property.title}</h3>
                        <p className="mt-2 flex items-center gap-2 text-sm text-slate-500"><MapPin className="h-4 w-4 text-[#00559b]" /> {property.address || 'Adresse sur demande'}</p>
                        <div className="mt-5 flex items-end justify-between border-t border-slate-100 pt-5">
                            <div><p className="text-xs text-slate-400">À partir de</p><p className="text-lg font-extrabold text-[#00559b]">{new Intl.NumberFormat('fr-FR').format(property.price || 0)} FCFA{property.mode === 'location' ? <span className="text-xs font-medium text-slate-400"> / mois</span> : null}</p></div>
                            {property.surface ? <span className="flex items-center gap-1 text-xs text-slate-500"><Maximize2 className="h-3.5 w-3.5" /> {property.surface} m²</span> : null}
                        </div>
                    </div>
                </Link>
            ))}
            {!properties.length ? <div className="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center"><Building2Fallback /><p className="mt-4 font-semibold text-[#111f3d]">Aucun bien disponible pour le moment.</p><p className="mt-1 text-sm text-slate-400">De nouvelles opportunités seront bientôt publiées.</p></div> : null}
        </div>
    );
}

function Building2Fallback() {
    return <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#eaf4fb] text-2xl">⌂</div>;
}
