import React from 'react';
import { cn } from '../../lib/utils';
import { Check } from 'lucide-react';

const Checkbox = React.forwardRef(function Checkbox(
    { className, checked, defaultChecked, onChange, onCheckedChange, ...props },
    ref
) {
    const handleChange = onChange ?? onCheckedChange;
    const isControlled = checked !== undefined;

    return (
        <span className="relative inline-flex items-center">
            <input
                ref={ref}
                type="checkbox"
                checked={checked}
                defaultChecked={defaultChecked}
                onChange={handleChange}
                readOnly={isControlled && !handleChange}
                className={cn(
                    'peer h-4 w-4 shrink-0 rounded-sm border border-[#c8d4de] bg-white shadow-sm transition-colors checked:border-[#76c206] checked:bg-[#76c206] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                    className
                )}
                {...props}
            />
            <Check className="pointer-events-none absolute left-0 top-0 h-4 w-4 scale-0 text-white transition peer-checked:scale-100" />
        </span>
    );
});

export { Checkbox };
