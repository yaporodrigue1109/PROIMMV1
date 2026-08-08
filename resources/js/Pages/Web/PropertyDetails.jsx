import { useEffect, useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Building2, CheckCircle2, ChevronLeft, ChevronRight, DoorOpen, ExternalLink, Mail, MapPin, Maximize2, Phone, PlayCircle } from 'lucide-react';

import PublicLayout from './PublicLayout';

const fallbackImages = [
    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&q=85&w=1800',
    'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&q=85&w=1800',
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&q=85&w=1800',
];
const formatMoney = (value) => `${new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Number(value ?? 0))} FCFA`;

export default function PropertyDetails({ property }) {
    const units = (property.buildings ?? []).flatMap((building) => building.units ?? []);
    const agency = property.agency ?? {};
    const propertyImages = (property.images ?? [])
        .map((image) => typeof image === 'string' ? image : image?.url ?? image?.src)
        .filter(Boolean);
    const images = propertyImages.length ? propertyImages : property.image ? [property.image] : fallbackImages;
    const [activeImage, setActiveImage] = useState(0);
    const isLot = property.entity_type === 'lot';
    const isDoor = property.entity_type === 'porte';

    useEffect(() => {
        setActiveImage(0);
    }, [images[0]]);

    const showPreviousImage = () => setActiveImage((current) => (current - 1 + images.length) % images.length);
    const showNextImage = () => setActiveImage((current) => (current + 1) % images.length);

    return (
        <PublicLayout>
            <Head title={property.title} />
            <main className="bg-[#f7fafc]">
                <section className="mx-auto max-w-7xl px-5 pb-8 pt-8">
                    <Link href="/biens" className="inline-flex items-center gap-2 text-sm font-bold text-[#00559b] transition hover:gap-3"><ArrowLeft className="h-4 w-4" /> Retour aux biens</Link>
                    <div className="mt-6 overflow-hidden rounded-[2rem] bg-slate-200 shadow-sm">
                        <div className="relative h-[320px] sm:h-[430px] lg:h-[520px]">
                            <img key={images[activeImage]} src={images[activeImage]} alt={`${property.title} — image ${activeImage + 1}`} className="h-full w-full object-cover transition-opacity duration-500" />
                            <div className="absolute inset-0 bg-gradient-to-t from-[#111f3d]/85 via-transparent to-transparent" />
                            {images.length > 1 ? (
                                <>
                                    <button type="button" onClick={showPreviousImage} className="absolute left-4 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-[#111f3d] shadow-lg backdrop-blur transition hover:bg-white" aria-label="Afficher l’image précédente">
                                        <ChevronLeft className="h-6 w-6" />
                                    </button>
                                    <button type="button" onClick={showNextImage} className="absolute right-4 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-[#111f3d] shadow-lg backdrop-blur transition hover:bg-white" aria-label="Afficher l’image suivante">
                                        <ChevronRight className="h-6 w-6" />
                                    </button>
                                    <div className="absolute right-5 top-5 z-10 rounded-full bg-[#111f3d]/70 px-3 py-1.5 text-xs font-bold text-white backdrop-blur">
                                        {activeImage + 1} / {images.length}
                                    </div>
                                    <div className="absolute left-1/2 top-6 z-10 flex -translate-x-1/2 gap-2">
                                        {images.map((image, index) => (
                                            <button key={`${image}-${index}`} type="button" onClick={() => setActiveImage(index)} className={`h-2 rounded-full transition-all ${index === activeImage ? 'w-7 bg-white' : 'w-2 bg-white/50 hover:bg-white/80'}`} aria-label={`Afficher l’image ${index + 1}`} aria-current={index === activeImage ? 'true' : undefined} />
                                        ))}
                                    </div>
                                </>
                            ) : null}
                            <div className="absolute inset-x-0 bottom-0 p-6 text-white sm:p-10">
                                <div className="flex flex-wrap items-end justify-between gap-6">
                                    <div><span className={`rounded-full px-4 py-2 text-xs font-extrabold uppercase tracking-wide ${property.mode === 'location' ? 'bg-[#00559b]' : 'bg-[#76c206]'}`}>{property.mode === 'location' ? 'À louer' : 'À vendre'}</span><h1 className="mt-4 text-3xl font-extrabold sm:text-5xl">{property.title}</h1><p className="mt-3 flex items-center gap-2 text-sm text-white/75 sm:text-base"><MapPin className="h-5 w-5 text-[#9bd63f]" /> {property.address || 'Adresse disponible sur demande'}</p></div>
                                    <div className="rounded-2xl bg-white/95 px-6 py-4 text-[#111f3d] backdrop-blur"><p className="text-xs font-bold uppercase tracking-wide text-slate-400">Prix à partir de</p><p className="mt-1 text-2xl font-extrabold text-[#00559b]">{formatMoney(property.price)}{property.mode === 'location' ? <span className="text-sm font-medium text-slate-400"> / mois</span> : null}</p></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="mx-auto grid max-w-7xl gap-8 px-5 pb-20 lg:grid-cols-[minmax(0,1fr)_350px]">
                    <div className="space-y-8">
                        <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <Stat icon={Building2} value={isLot ? 'Terrain' : property.buildings_count} label={isLot ? 'Type de bien' : 'Bâtiment(s)'} />
                            <Stat icon={DoorOpen} value={isDoor ? 'Porte' : property.units_count} label={isDoor ? 'Type d’offre' : 'Porte(s)'} />
                            <Stat icon={CheckCircle2} value="Oui" label="Disponible" />
                            <Stat icon={Maximize2} value={property.surface ? `${property.surface} m²` : '—'} label="Superficie" />
                        </div>

                        <ContentCard title="Présentation du bien">
                            <p className="leading-8 text-slate-600">{property.description || 'Ce bien est proposé par une agence partenaire de Pros Immobilier. Contactez l’agence pour recevoir davantage d’informations et organiser une visite.'}</p>
                        </ContentCard>

                        {!isLot ? <ContentCard title={isDoor ? 'Détail de la porte' : 'Composition de la propriété'}>
                            {units.length ? <div className="grid gap-4 sm:grid-cols-2">{units.map((unit) => <article key={unit.id} className="rounded-2xl border border-slate-200 p-5"><div className="flex items-start justify-between gap-3"><div><p className="text-xs font-bold uppercase tracking-wide text-[#76c206]">{unit.type}</p><h3 className="mt-1 font-extrabold text-[#111f3d]">Porte disponible</h3></div><span className={`rounded-full px-3 py-1 text-[11px] font-bold ${unit.available ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'}`}>{unit.available ? 'Disponible' : 'Occupée'}</span></div><div className="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-xs text-slate-500">{unit.surface ? <span>{unit.surface} m²</span> : null}{unit.floor !== null && unit.floor !== undefined ? <span>Étage {unit.floor}</span> : null}</div>{unit.description ? <p className="mt-3 text-sm leading-6 text-slate-500">{unit.description}</p> : null}{isDoor ? <p className="mt-4 border-t border-slate-100 pt-4 font-extrabold text-[#00559b]">{formatMoney(unit.price)}{unit.mode === 'location' ? <span className="text-xs font-medium text-slate-400"> / mois</span> : null}</p> : null}</article>)}</div> : <p className="text-sm text-slate-500">Les informations détaillées sont disponibles auprès de l’agence.</p>}
                        </ContentCard> : null}

                        {property.nearby?.length ? <ContentCard title="À proximité"><div className="flex flex-wrap gap-3">{property.nearby.map((item, index) => <span key={`${item.name}-${index}`} className="rounded-full bg-[#eef7fd] px-4 py-2 text-sm font-semibold text-[#00559b]">{item.name}{item.distance ? ` · ${item.distance} ${item.unit ?? ''}` : ''}</span>)}</div></ContentCard> : null}
                        {property.videos?.length ? <ContentCard title="Vidéos du bien"><div className="space-y-3">{property.videos.map((video) => <a key={video} href={video} target="_blank" rel="noreferrer" className="flex items-center justify-between rounded-2xl border border-slate-200 p-4 text-sm font-bold text-[#00559b] hover:bg-[#f4f9fd]"><span className="flex items-center gap-3"><PlayCircle className="h-5 w-5" /> Voir la vidéo</span><ExternalLink className="h-4 w-4" /></a>)}</div></ContentCard> : null}
                    </div>

                    <aside className="lg:sticky lg:top-28 lg:self-start">
                        <div className="rounded-[1.75rem] bg-[#111f3d] p-7 text-white shadow-xl">

                            <p className="mt-5 text-xs font-bold uppercase tracking-[0.18em] text-[#9bd63f]">Agence en charge</p>
                            <h2 className="mt-2 text-2xl font-extrabold">{agency.name || 'Agence partenaire'}</h2>
                            {agency.address ? <p className="mt-3 flex gap-2 text-sm leading-6 text-white/60"><MapPin className="mt-0.5 h-4 w-4 shrink-0" /> {agency.address}</p> : null}
                            <div className="mt-6 space-y-3">{agency.phone ? <a href={`tel:${agency.phone}`} className="flex items-center gap-3 rounded-xl bg-white/10 p-3 text-sm font-semibold transition hover:bg-white/15"><Phone className="h-4 w-4 text-[#76c206]" /> {agency.phone}</a> : null}{agency.email ? <a href={`mailto:${agency.email}`} className="flex items-center gap-3 rounded-xl bg-white/10 p-3 text-sm font-semibold transition hover:bg-white/15"><Mail className="h-4 w-4 text-[#76c206]" /> <span className="truncate">{agency.email}</span></a> : null}</div>
                            <Link href="/contact" className="mt-6 inline-flex w-full items-center justify-center rounded-lg bg-[#76c206] px-5 py-3.5 text-sm font-extrabold transition hover:bg-[#66aa04]">Demander une visite</Link>
                        </div>
                    </aside>
                </section>
            </main>
        </PublicLayout>
    );
}

function Stat({ icon: Icon, value, label }) { return <div className="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm"><Icon className="h-5 w-5 text-[#76c206]" /><p className="mt-3 text-xl font-extrabold text-[#111f3d]">{value ?? 0}</p><p className="mt-1 text-xs text-slate-500">{label}</p></div>; }
function ContentCard({ title, children }) { return <section className="rounded-[1.75rem] border border-slate-100 bg-white p-6 shadow-sm sm:p-8"><h2 className="text-2xl font-extrabold text-[#111f3d]">{title}</h2><div className="mt-5">{children}</div></section>; }
