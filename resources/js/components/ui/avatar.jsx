import React from 'react';
import { cn } from '../../lib/utils';

const Avatar = React.forwardRef(function Avatar(
    { className, ...props },
    ref
) {
    return (
        <span
            ref={ref}
            className={cn(
                'relative flex h-10 w-10 shrink-0 overflow-hidden rounded-full bg-slate-100',
                className
            )}
            {...props}
        />
    );
});

const AvatarFallback = React.forwardRef(function AvatarFallback(
    { className, ...props },
    ref
) {
    return (
        <span
            ref={ref}
            className={cn(
                'flex h-full w-full items-center justify-center rounded-full bg-slate-200 text-sm font-medium text-slate-600',
                className
            )}
            {...props}
        />
    );
});

export { Avatar, AvatarFallback };
