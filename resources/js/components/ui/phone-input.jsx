import { Check, ChevronRight } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import PhoneInputBase from 'react-phone-number-input';
import flags from 'react-phone-number-input/flags';
import 'react-phone-number-input/style.css';

import { cn } from '../../lib/utils';

function CountrySelect({ value, onChange, options }) {
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const containerRef = useRef(null);

    const filteredOptions = options.filter(
        (option) => !option.divider && option.label.toLowerCase().includes(query.toLowerCase()),
    );

    useEffect(() => {
        function handleClickOutside(event) {
            if (containerRef.current && !containerRef.current.contains(event.target)) {
                setOpen(false);
                setQuery('');
            }
        }

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const Flag = value ? flags[value] : null;

    return (
        <div ref={containerRef} className="relative shrink-0">
            <button
                type="button"
                onClick={() => setOpen((isOpen) => !isOpen)}
                className="flex h-full items-center gap-1.5 rounded-l-md border-r border-[#c8d4de] bg-white px-2.5 text-sm text-[#5f7182] transition-colors hover:bg-[#f8fafc]"
            >
                {Flag ? <Flag title={value} className="h-4 w-5 rounded-sm object-cover" /> : <span className="h-4 w-5" />}
                <ChevronRight className="h-3.5 w-3.5 rotate-90" />
            </button>

            {open ? (
                <div className="absolute left-0 top-[calc(100%+4px)] z-50 w-64 overflow-hidden rounded-md border border-[#c8d4de] bg-white shadow-lg">
                    <div className="flex items-center gap-2 border-b border-[#e2e8f0] px-2.5 py-2">
                        <input
                            autoFocus
                            value={query}
                            onChange={(event) => setQuery(event.target.value)}
                            placeholder="Rechercher un pays..."
                            className="w-full border-0 bg-transparent text-sm text-[#0f172a] outline-none placeholder:text-[#94a3b8]"
                        />
                    </div>
                    <ul className="max-h-60 overflow-y-auto py-1">
                        {filteredOptions.length === 0 ? (
                            <li className="px-3 py-2 text-sm text-[#94a3b8]">Aucun résultat</li>
                        ) : (
                            filteredOptions.map((option) => {
                                const CountryFlag = flags[option.value];

                                return (
                                    <li key={option.value}>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                onChange(option.value);
                                                setOpen(false);
                                                setQuery('');
                                            }}
                                            className={cn(
                                                'flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-[#eaf4fb]',
                                                value === option.value && 'bg-[#eaf4fb]',
                                            )}
                                        >
                                            {CountryFlag ? (
                                                <CountryFlag className="h-4 w-5 shrink-0 rounded-sm object-cover" />
                                            ) : (
                                                <span className="h-4 w-5 shrink-0" />
                                            )}
                                            <span className="flex-1 truncate text-[#0f172a]">{option.label}</span>
                                            {value === option.value ? <Check className="h-3.5 w-3.5 shrink-0 text-[#00559b]" /> : null}
                                        </button>
                                    </li>
                                );
                            })
                        )}
                    </ul>
                </div>
            ) : null}
        </div>
    );
}

function PhoneInput({ className, value, onChange, ...props }) {
    return (
        <PhoneInputBase
            international
            defaultCountry="CI"
            countrySelectComponent={CountrySelect}
            value={value || undefined}
            onChange={(nextValue) => onChange?.(nextValue ?? '')}
            className={cn(
                'phone-input-custom flex h-10 items-stretch rounded-md border border-[#c8d4de] bg-white shadow-sm transition-colors',
                'focus-within:border-[#00559b] focus-within:ring-2 focus-within:ring-[#00559b]/20',
                className,
            )}
            {...props}
        />
    );
}

export { PhoneInput };
