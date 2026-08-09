import * as React from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { DayPicker, type DayPickerProps } from 'react-day-picker';

import { cn } from '../../lib/utils';

export type CalendarProps = DayPickerProps;

function Calendar({ className, classNames, showOutsideDays = true, ...props }: CalendarProps) {
    return (
        <DayPicker
            showOutsideDays={showOutsideDays}
            className={cn('p-3', className)}
            classNames={{
                months: 'relative flex flex-col gap-5 sm:flex-row sm:gap-6',
                month: 'space-y-4',
                month_caption: 'relative flex h-9 items-center justify-center px-9',
                caption_label: 'text-sm font-semibold capitalize text-slate-900',
                nav: 'absolute inset-x-0 top-0 z-10 flex items-center justify-between',
                button_previous: cn(
                    'inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#d8e1ea] bg-white text-slate-600',
                    'transition-colors hover:border-[#00559b] hover:bg-[#eaf4fb] hover:text-[#00559b]',
                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:opacity-50',
                ),
                button_next: cn(
                    'inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#d8e1ea] bg-white text-slate-600',
                    'transition-colors hover:border-[#00559b] hover:bg-[#eaf4fb] hover:text-[#00559b]',
                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:opacity-50',
                ),
                month_grid: 'w-full border-collapse',
                weekdays: 'flex',
                weekday: 'w-9 rounded-md text-center text-xs font-medium text-slate-500',
                week: 'mt-1 flex w-full',
                day: 'relative h-9 w-9 p-0 text-center text-sm [&:has([aria-selected])]:bg-[#eaf4fb] first:[&:has([aria-selected])]:rounded-l-lg last:[&:has([aria-selected])]:rounded-r-lg',
                day_button: cn(
                    'inline-flex h-9 w-9 items-center justify-center rounded-lg font-normal text-slate-700 transition-colors',
                    'hover:bg-[#eaf4fb] hover:text-[#00559b] focus-visible:relative focus-visible:z-20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b]',
                ),
                range_start: 'rounded-l-lg bg-[#00559b] text-white [&>button]:bg-[#00559b] [&>button]:text-white [&>button]:hover:bg-[#004980] [&>button]:hover:text-white',
                range_end: 'rounded-r-lg bg-[#00559b] text-white [&>button]:bg-[#00559b] [&>button]:text-white [&>button]:hover:bg-[#004980] [&>button]:hover:text-white',
                range_middle: 'rounded-none bg-[#eaf4fb] [&>button]:rounded-none [&>button]:bg-transparent [&>button]:text-[#00559b]',
                selected: '[&>button]:font-semibold',
                today: '[&>button]:border [&>button]:border-[#00559b] [&>button]:font-semibold [&>button]:text-[#00559b]',
                outside: 'text-slate-400 opacity-55',
                disabled: 'text-slate-300 opacity-50',
                hidden: 'invisible',
                ...classNames,
            }}
            components={{
                Chevron: ({ orientation, className: chevronClassName }) =>
                    orientation === 'left' ? (
                        <ChevronLeft aria-hidden="true" className={cn('h-4 w-4', chevronClassName)} />
                    ) : (
                        <ChevronRight aria-hidden="true" className={cn('h-4 w-4', chevronClassName)} />
                    ),
            }}
            {...props}
        />
    );
}
Calendar.displayName = 'Calendar';

export { Calendar };
