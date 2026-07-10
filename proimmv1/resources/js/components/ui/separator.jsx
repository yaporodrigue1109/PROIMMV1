import { cn } from '../../lib/utils';

export function Separator({ className, orientation = 'horizontal', ...props }) {
    return (
        <div
            className={cn(
                orientation === 'horizontal' ? 'h-px w-full' : 'h-full w-px',
                'shrink-0 bg-[#c8d4de]',
                className
            )}
            {...props}
        />
    );
}
