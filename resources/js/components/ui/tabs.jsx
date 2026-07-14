import React from 'react';
import { cn } from '../../lib/utils';

const TabsContext = React.createContext(null);

function Tabs({ defaultValue, value, onValueChange, className, children, ...props }) {
    const [internalValue, setInternalValue] = React.useState(defaultValue);
    const isControlled = value !== undefined;
    const activeValue = isControlled ? value : internalValue;

    const handleValueChange = (nextValue) => {
        if (!isControlled) {
            setInternalValue(nextValue);
        }

        onValueChange?.(nextValue);
    };

    return (
        <TabsContext.Provider value={{ value: activeValue, onValueChange: handleValueChange }}>
            <div className={cn(className)} {...props}>
                {children}
            </div>
        </TabsContext.Provider>
    );
}

function TabsList({ className, ...props }) {
    return (
        <div
            role="tablist"
            className={cn(
                'inline-flex h-10 items-center justify-center rounded-xl bg-slate-100 p-1 text-slate-500',
                className
            )}
            {...props}
        />
    );
}

function TabsTrigger({ className, value, ...props }) {
    const context = React.useContext(TabsContext);
    const active = context?.value === value;

    return (
        <button
            type="button"
            role="tab"
            aria-selected={active}
            data-state={active ? 'active' : 'inactive'}
            onClick={() => context?.onValueChange?.(value)}
            className={cn(
                'inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
                active
                    ? 'bg-white text-slate-900 shadow-sm'
                    : 'text-slate-500 hover:text-slate-900',
                className
            )}
            {...props}
        />
    );
}

function TabsContent({ className, value, children, ...props }) {
    const context = React.useContext(TabsContext);

    if (context?.value !== value) {
        return null;
    }

    return (
        <div
            role="tabpanel"
            className={cn('outline-none', className)}
            {...props}
        >
            {children}
        </div>
    );
}

export { Tabs, TabsList, TabsTrigger, TabsContent };
