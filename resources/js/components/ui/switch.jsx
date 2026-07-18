import React from 'react';
import { cn } from '../../lib/utils';

const Switch = React.forwardRef(function Switch(
    { className, checked, defaultChecked, onCheckedChange, disabled, ...props },
    ref
) {
    const isControlled = checked !== undefined;
    const [internalChecked, setInternalChecked] = React.useState(Boolean(defaultChecked));
    const isChecked = isControlled ? Boolean(checked) : internalChecked;

    const handleToggle = () => {
        if (disabled) return;

        const nextChecked = !isChecked;

        if (!isControlled) {
            setInternalChecked(nextChecked);
        }

        onCheckedChange?.(nextChecked);
    };

    return (
        <button
            ref={ref}
            type="button"
            role="switch"
            aria-checked={isChecked}
            data-state={isChecked ? 'checked' : 'unchecked'}
            disabled={disabled}
            onClick={handleToggle}
            className={cn(
                'peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                isChecked ? 'bg-[#00559b]' : 'bg-slate-200',
                className
            )}
            {...props}
        >
            <span
                data-state={isChecked ? 'checked' : 'unchecked'}
                className={cn(
                    'pointer-events-none block h-5 w-5 rounded-full bg-white shadow-lg ring-0 transition-transform',
                    isChecked ? 'translate-x-5' : 'translate-x-0'
                )}
            />
        </button>
    );
});

export { Switch };
