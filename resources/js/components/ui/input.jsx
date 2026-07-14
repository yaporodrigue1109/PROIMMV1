import React from 'react';
import { cn } from '../../lib/utils';

const Input = React.forwardRef(function Input({ className, type = 'text', ...props }, ref) {
    return (
        <input
            type={type}
            ref={ref}
            className={cn(
                'flex h-10 w-full rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-[#8798a5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                className
            )}
            {...props}
        />
    );
});

export { Input };
