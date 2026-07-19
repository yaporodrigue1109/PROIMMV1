import { useEffect, useMemo, useRef, useState } from 'react';
import { Check, ChevronDown, Search, X } from 'lucide-react';
import { cn } from '../../lib/utils';

export function ComboboxField({
    label,
    required = false,
    error = '',
    value = '',
    placeholder = 'Sélectionner',
    options = [],
    onSelect,
    disabled = false,
    className = '',
    emptyLabel = 'Aucun résultat',
    open,
    onOpenChange,
    searchValue,
    onSearchChange,
    searchPlaceholder,
}) {
    const rootRef = useRef(null);
    const [internalOpen, setInternalOpen] = useState(false);
    const [internalSearch, setInternalSearch] = useState('');

    const isOpenControlled = open !== undefined && typeof onOpenChange === 'function';
    const isSearchControlled = searchValue !== undefined && typeof onSearchChange === 'function';

    const isOpen = isOpenControlled ? open : internalOpen;
    const search = isSearchControlled ? searchValue : internalSearch;

    const setOpen = (next) => {
        if (isOpenControlled) {
            onOpenChange(next);
            return;
        }

        setInternalOpen(next);
    };

    const setSearch = (next) => {
        if (isSearchControlled) {
            onSearchChange(next);
            return;
        }

        setInternalSearch(next);
    };

    const selectedLabel = useMemo(
        () => options.find((option) => String(option.value) === String(value))?.label ?? '',
        [options, value]
    );

    const filteredOptions = useMemo(() => {
        const term = String(search ?? '').trim().toLowerCase();
        if (!term) return options;

        return options.filter((option) => {
            const haystack = `${option.label ?? ''} ${option.value ?? ''} ${option.searchText ?? ''}`.toLowerCase();
            return haystack.includes(term);
        });
    }, [options, search]);

    useEffect(() => {
        if (!isOpen && !isSearchControlled) {
            setInternalSearch(selectedLabel);
        }
    }, [isOpen, isSearchControlled, selectedLabel]);

    useEffect(() => {
        const handlePointerDown = (event) => {
            if (rootRef.current && !rootRef.current.contains(event.target)) {
                setOpen(false);
            }
        };

        document.addEventListener('mousedown', handlePointerDown);
        document.addEventListener('touchstart', handlePointerDown);

        return () => {
            document.removeEventListener('mousedown', handlePointerDown);
            document.removeEventListener('touchstart', handlePointerDown);
        };
    }, []);

    const handleToggle = () => {
        if (disabled) return;

        if (isOpen) {
            setOpen(false);
            setSearch(selectedLabel);
            return;
        }

        setSearch(selectedLabel || '');
        setOpen(true);
    };

    return (
        <div ref={rootRef} className={cn('space-y-2', className)}>
            <label className="block text-sm font-semibold text-[#0f172a]">
                {label}
                {required ? <span className="ml-0.5 text-[#b42318]">*</span> : null}
            </label>

            <div className="relative w-full">
                <div
                    className={cn(
                        'flex h-10 w-full items-center justify-between rounded-md border bg-white px-3 text-left shadow-sm transition-all duration-200',
                        disabled
                            ? 'cursor-not-allowed border-[#e2e8f0] bg-slate-50 text-slate-400'
                            : isOpen
                                ? 'border-[#00559b] ring-4 ring-[#00559b]/10 shadow-md'
                                : 'border-[#c8d4de]'
                    )}
                    role="button"
                    tabIndex={disabled ? -1 : 0}
                    onClick={handleToggle}
                    onKeyDown={(event) => {
                        if (disabled) return;
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            handleToggle();
                        }
                    }}
                >
                    <div className="flex min-w-0 items-center gap-2">
                        <Search className={cn('h-4 w-4 shrink-0', disabled ? 'text-slate-300' : 'text-[#94a3b8]')} />
                        <span
                            className={cn(
                                'truncate text-sm font-medium',
                                selectedLabel ? 'text-[#0f172a]' : 'text-[#8798a5]',
                                disabled && 'text-slate-400'
                            )}
                        >
                            {selectedLabel || placeholder}
                        </span>
                    </div>

                    <div className="ml-3 flex items-center gap-2">
                        {!disabled && value ? (
                            <button
                                type="button"
                                className="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[#5f7182] transition hover:bg-[#f1f5f9] hover:text-[#0f172a]"
                                onClick={(event) => {
                                    event.stopPropagation();
                                    onSelect('');
                                    setSearch('');
                                    setOpen(false);
                                }}
                                aria-label={`Effacer ${label}`}
                            >
                                <X className="h-4 w-4" />
                            </button>
                        ) : null}
                        <ChevronDown className={cn('h-4 w-4 shrink-0 text-[#94a3b8] transition-transform duration-200', isOpen && 'rotate-180', disabled && 'text-slate-300')} />
                    </div>
                </div>

                {isOpen && !disabled ? (
                    <div className="absolute left-0 right-0 top-[calc(100%+8px)] z-40 overflow-hidden rounded-md border border-[#c8d4de] bg-white shadow-[0_24px_60px_-18px_rgba(15,23,42,0.22)]">
                        <div className="border-b border-[#e8eef3] p-2.5">
                            <div className="flex h-10 items-center rounded-md border border-[#d7e0e8] bg-[#f8fafc] px-3 focus-within:border-[#00559b] focus-within:bg-white">
                                <Search className="h-4 w-4 shrink-0 text-[#94a3b8]" />
                                <input
                                    value={search ?? ''}
                                    onChange={(event) => setSearch(event.target.value)}
                                    placeholder={searchPlaceholder || `Rechercher ${label.toLowerCase()}...`}
                                    className="h-9 w-full border-0 bg-transparent px-2 text-sm text-[#0f172a] outline-none placeholder:text-[#8798a5]"
                                    autoFocus
                                />
                            </div>
                        </div>

                        <div className="max-h-60 overflow-y-auto p-1.5">
                            {filteredOptions.length ? (
                                filteredOptions.map((option) => {
                                    const active = String(option.value) === String(value);

                                    return (
                                        <button
                                            key={option.value}
                                            type="button"
                                            className={cn(
                                                'flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-sm transition',
                                                active ? 'bg-[#eaf4fb] text-[#00559b]' : 'text-[#0f172a] hover:bg-[#f8fafc]'
                                            )}
                                            onClick={() => {
                                                onSelect(option.value);
                                                setSearch(option.label ?? '');
                                                setOpen(false);
                                            }}
                                        >
                                            <span className="truncate font-medium">{option.label}</span>
                                            {active ? <Check className="h-4 w-4 shrink-0" /> : null}
                                        </button>
                                    );
                                })
                            ) : (
                                <div className="px-3 py-4 text-center text-sm text-[#5f7182]">{emptyLabel}</div>
                            )}
                        </div>
                    </div>
                ) : null}
            </div>

            {error ? <p className="text-xs text-[#b42318]">{error}</p> : null}
        </div>
    );
}
