import { Slot } from '@radix-ui/react-slot';
import { cn } from '../../lib/utils';

const buttonVariants = {
    default: 'bg-[#00559b] text-white shadow-sm shadow-[#00559b]/10 hover:bg-[#004980]',
    secondary: 'bg-[#eaf4fb] text-[#00559b] hover:bg-[#dcecf8]',
    outline: 'border border-[#c8d4de] bg-white/90 text-[#0f172a] shadow-sm hover:border-[#00559b] hover:text-[#00559b]',
    ghost: 'bg-transparent text-[#00559b] hover:bg-[#eaf4fb]',
    destructive: 'bg-[#b42318] text-white hover:bg-[#991b12]',
};

const buttonSizes = {
    default: 'h-10 rounded-xl px-4 py-2',
    sm: 'h-9 rounded-lg px-3',
    lg: 'h-11 rounded-xl px-8',
    icon: 'h-10 w-10 rounded-xl',
};

export function Button({
    className,
    variant = 'default',
    size = 'default',
    asChild = false,
    ...props
}) {
    const Comp = asChild ? Slot : 'button';

    return (
        <Comp
            className={cn(
                'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-xl text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
                buttonVariants[variant] ?? buttonVariants.default,
                buttonSizes[size] ?? buttonSizes.default,
                className
            )}
            {...props}
        />
    );
}
