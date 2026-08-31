import { useEffect, useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { SearchableSelect } from './ui/searchable-select';

const selectClass = 'flex h-10 w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] focus:outline-none focus:ring-2 focus:ring-[#00559b]/20 disabled:opacity-50';

export default function GeographySelects({ regionId = '', cityId = '', onRegionChange, onCityChange, regionName, cityName, errors = {} }) {
    const geography = usePage().props.geography ?? {};
    const countries = Array.isArray(geography.countries) ? geography.countries : [];
    const regions = Array.isArray(geography.regions) ? geography.regions : [];
    const cities = Array.isArray(geography.cities) ? geography.cities : [];
    const inferredCountry = countries.find((country) => regions.some((region) => String(region.id) === String(regionId) && String(region.pays_id) === String(country.id)))?.iso2
        ?? geography.defaultCountryCode
        ?? 'CI';
    const [countryCode, setCountryCode] = useState(inferredCountry);

    useEffect(() => {
        if (regionId) setCountryCode(inferredCountry);
    }, [inferredCountry, regionId]);

    const country = countries.find((item) => item.iso2 === countryCode);
    const availableRegions = useMemo(() => regions.filter((region) => !country || String(region.pays_id) === String(country.id)), [country, regions]);
    const availableCities = useMemo(() => cities.filter((city) => String(city.region_id) === String(regionId)), [cities, regionId]);

    return (
        <>
            {regionName ? <input type="hidden" name={regionName} value={regionId} /> : null}
            {cityName ? <input type="hidden" name={cityName} value={cityId} /> : null}
            <LocationField label="Pays">
                <SearchableSelect value={countryCode} onChange={(event) => {
                    setCountryCode(event.target.value);
                    onRegionChange?.('');
                    onCityChange?.('');
                }} className={selectClass}>
                    {countries.map((item) => <option key={item.iso2} value={item.iso2}>{item.name} ({item.indicatif})</option>)}
                </SearchableSelect>
            </LocationField>
            <LocationField label="Région" error={errors.region_id}>
                <SearchableSelect value={regionId} onChange={(event) => {
                    onRegionChange?.(event.target.value);
                    onCityChange?.('');
                }} className={selectClass}>
                    <option value="">Sélectionner</option>
                    {availableRegions.map((region) => <option key={region.id} value={region.id}>{region.name}</option>)}
                </SearchableSelect>
            </LocationField>
            <LocationField label="Ville" error={errors.ville_id}>
                <SearchableSelect value={cityId} onChange={(event) => onCityChange?.(event.target.value)} disabled={!regionId} className={selectClass}>
                    <option value="">Sélectionner</option>
                    {availableCities.map((city) => <option key={city.id} value={city.id}>{city.name}</option>)}
                </SearchableSelect>
            </LocationField>
        </>
    );
}

function LocationField({ label, error, children }) {
    return <label className="space-y-2"><span className="block text-sm font-medium text-slate-700">{label}</span>{children}{error ? <span className="block text-xs font-medium text-red-600">{error}</span> : null}</label>;
}
