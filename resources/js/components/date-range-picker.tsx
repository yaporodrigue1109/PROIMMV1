import * as React from 'react';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';
import { CalendarIcon } from 'lucide-react';
import type { DateRange } from 'react-day-picker';

import { cn } from '../lib/utils';
import { Button } from './ui/button';
import { Calendar } from './ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from './ui/popover';

export interface DateRangePickerProps {
    value?: DateRange;
    onChange: (value: DateRange | undefined) => void;
    placeholder?: string;
    disabled?: boolean;
    className?: string;
}

function useDesktopCalendar() {
    const [isDesktop, setIsDesktop] = React.useState(false);

    React.useEffect(() => {
        const mediaQuery = window.matchMedia('(min-width: 768px)');
        const update = () => setIsDesktop(mediaQuery.matches);

        update();
        mediaQuery.addEventListener('change', update);

        return () => mediaQuery.removeEventListener('change', update);
    }, []);

    return isDesktop;
}

export function DateRangePicker({
    value,
    onChange,
    placeholder = 'Sélectionner une période',
    disabled = false,
    className,
}: DateRangePickerProps) {
    const [open, setOpen] = React.useState(false);
    const isDesktop = useDesktopCalendar();
    const selectingEnd = React.useRef(Boolean(value?.from && !value.to));

    const label = React.useMemo(() => {
        if (!value?.from) return placeholder;

        const from = format(value.from, 'dd MMMM yyyy', { locale: fr });
        if (!value.to) return `${from} – …`;

        return `${from} – ${format(value.to, 'dd MMMM yyyy', { locale: fr })}`;
    }, [placeholder, value]);

    const handleOpenChange = (nextOpen: boolean) => {
        if (nextOpen) {
            selectingEnd.current = Boolean(value?.from && !value.to);
        }

        setOpen(nextOpen);
    };

    const handleSelect = (range: DateRange | undefined, selectedDay: Date) => {
        if (!selectingEnd.current) {
            selectingEnd.current = true;
            onChange({ from: selectedDay, to: undefined });

            return;
        }

        onChange(range);

        if (range?.from && range.to) {
            selectingEnd.current = false;
            setOpen(false);
        }
    };

    const handleReset = () => {
        selectingEnd.current = false;
        onChange(undefined);
        setOpen(false);
    };

    return (
        <Popover open={open} onOpenChange={handleOpenChange}>
            <PopoverTrigger asChild>
                <Button
                    type="button"
                    variant="outline"
                    disabled={disabled}
                    aria-label={value?.from ? `Période sélectionnée : ${label}` : placeholder}
                    aria-expanded={open}
                    className={cn(
                        'h-10 w-full min-w-0 justify-start rounded-xl border-[#d8e1ea] bg-white px-3 text-left font-normal shadow-sm sm:w-auto sm:min-w-[285px]',
                        !value?.from && 'text-slate-500',
                        className,
                    )}
                >
                    <CalendarIcon aria-hidden="true" className="h-4 w-4 shrink-0 text-[#00559b]" />
                    <span className="min-w-0 truncate">{label}</span>
                </Button>
            </PopoverTrigger>

            <PopoverContent
                align="end"
                sideOffset={8}
                collisionPadding={16}
                role="dialog"
                aria-label="Sélectionner une période"
                className="w-[calc(100vw-2rem)] max-w-fit overflow-x-auto p-0 sm:w-auto"
            >
                <Calendar
                    autoFocus
                    mode="range"
                    defaultMonth={value?.from}
                    selected={value}
                    onSelect={handleSelect}
                    numberOfMonths={isDesktop ? 2 : 1}
                    locale={fr}
                    navLayout="around"
                />

                <div className="flex justify-end border-t border-[#e2e8f0] p-3">
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        className="text-slate-600"
                        onClick={handleReset}
                        disabled={!value?.from}
                    >
                        Réinitialiser
                    </Button>
                </div>
            </PopoverContent>
        </Popover>
    );
}

export type { DateRange } from 'react-day-picker';
