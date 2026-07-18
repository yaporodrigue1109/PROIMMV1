import { cn } from '../../lib/utils';

const badgeVariants = {
    default: 'border-transparent bg-[#76c206] text-white',
    secondary: 'border-transparent bg-[#00559b] text-white',
    outline: 'border-[#c8d4de] text-[#0f172a]',
    success: 'border-transparent bg-[#e6f5d0] text-[#4f8f00]',
    warning: 'border-transparent bg-[#e1edf6] text-[#00559b]',
    danger: 'border-transparent bg-rose-100 text-rose-700',
    destructive: 'border-transparent bg-rose-100 text-rose-700',
};

export function Badge({ className, variant = 'default', ...props }) {
    return (
        <span
            className={cn(
                'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors',
                badgeVariants[variant] ?? badgeVariants.default,
                className
            )}
            {...props}
        />
    );
}
