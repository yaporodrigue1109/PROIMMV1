import { useMemo, useState } from 'react';
import { MapPin, Search, X } from 'lucide-react';

import PublicLayout from './PublicLayout';
import PropertyCards from './PropertyCards';

const normalize = (value) => String(value ?? '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLocaleLowerCase('fr');

export default function Properties({ properties = [], mode = '' }) {
    const [type, setType] = useState(mode || 'all');
    const [locality, setLocality] = useState('');
    const [propertyType, setPropertyType] = useState('all');

    const propertyTypes = useMemo(() => Array.from(new Set(
        properties.map((property) => property.property_type).filter(Boolean),
    )).sort((left, right) => left.localeCompare(right, 'fr')), [properties]);

    const filtered = useMemo(() => {
        const query = normalize(locality.trim());

        return properties.filter((property) => {
            const matchesType = type === 'all' || property.mode === type;
            const matchesPropertyType = propertyType === 'all' || property.property_type === propertyType;
            const matchesLocality = !query || normalize(property.address).includes(query);

            return matchesType && matchesPropertyType && matchesLocality;
        });
    }, [locality, properties, propertyType, type]);

    return (
        <PublicLayout>
            <main className="mx-auto max-w-7xl px-5 py-14">
                <p className="font-medium text-[#00559b]">Nos annonces</p>
                <h1 className="text-4xl font-bold">Trouvez le bien qui vous ressemble</h1>

                <div className="my-8 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div className="grid items-center gap-3 md:grid-cols-2 lg:grid-cols-[minmax(260px,1fr)_230px_auto]">
                    <div className="relative">
                        <MapPin className="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[#00559b]" />
                        <input
                            type="search"
                            value={locality}
                            onChange={(event) => setLocality(event.target.value)}
                            placeholder="Localité, commune ou quartier"
                            aria-label="Rechercher un bien par localité"
                            className="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-12 pr-11 text-sm text-[#111f3d] outline-none transition placeholder:text-slate-400 focus:border-[#00559b] focus:bg-white focus:ring-4 focus:ring-[#00559b]/10"
                        />
                        {locality ? (
                            <button type="button" onClick={() => setLocality('')} aria-label="Effacer la localité" className="absolute right-3 top-1/2 -translate-y-1/2 rounded-full p-1.5 text-slate-400 hover:bg-slate-200 hover:text-[#111f3d]">
                                <X className="h-4 w-4" />
                            </button>
                        ) : null}
                    </div>
                    <select
                        value={propertyType}
                        onChange={(event) => setPropertyType(event.target.value)}
                        aria-label="Filtrer par type de bien"
                        className="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium text-[#111f3d] outline-none transition focus:border-[#00559b] focus:bg-white focus:ring-4 focus:ring-[#00559b]/10"
                    >
                        <option value="all">Tous les types de biens</option>
                        {propertyTypes.map((item) => <option key={item} value={item}>{item}</option>)}
                    </select>
                    <div className="flex flex-wrap gap-2 md:col-span-2 lg:col-span-1 lg:flex-nowrap">
                        {[['all', 'Tous'], ['location', 'À louer'], ['vente', 'À vendre']].map(([value, label]) => (
                            <button key={value} type="button" onClick={() => setType(value)} className={`whitespace-nowrap rounded-lg px-3.5 py-3 text-sm font-semibold transition ${type === value ? 'bg-[#00559b] text-white' : 'border border-slate-200 bg-white text-slate-600 hover:border-[#00559b] hover:text-[#00559b]'}`}>
                                {label}
                            </button>
                        ))}
                    </div>
                    </div>
                </div>

                {locality || propertyType !== 'all' ? (
                    <p className="mb-5 flex items-center gap-2 text-sm text-slate-500">
                        <Search className="h-4 w-4" />
                        {filtered.length} résultat{filtered.length > 1 ? 's' : ''}
                        {locality ? ` pour « ${locality} »` : ''}
                        {propertyType !== 'all' ? ` · ${propertyType}` : ''}
                    </p>
                ) : null}

                <PropertyCards properties={filtered} />
            </main>
        </PublicLayout>
    );
}
