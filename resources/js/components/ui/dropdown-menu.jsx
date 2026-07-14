import { Slot } from '@radix-ui/react-slot';
import { useEffect, useRef, useState, createContext, useContext } from 'react';
import { cn } from '../../lib/utils';

const DropdownMenuContext = createContext(null);

function useDropdownMenuContext() {
    const context = useContext(DropdownMenuContext);

    if (!context) {
        throw new Error('DropdownMenu components must be used within <DropdownMenu>.');
    }

    return context;
}

export function DropdownMenu({ children }) {
    const [open, setOpen] = useState(false);
    const rootRef = useRef(null);

    useEffect(() => {
        const handlePointerDown = (event) => {
            if (rootRef.current && !rootRef.current.contains(event.target)) {
                setOpen(false);
            }
        };

        const handleKeyDown = (event) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        };

        document.addEventListener('pointerdown', handlePointerDown);
        document.addEventListener('keydown', handleKeyDown);

        return () => {
            document.removeEventListener('pointerdown', handlePointerDown);
            document.removeEventListener('keydown', handleKeyDown);
        };
    }, []);

    return (
        <DropdownMenuContext.Provider value={{ open, setOpen, rootRef }}>
            <div ref={rootRef} className="relative inline-flex">
                {children}
            </div>
        </DropdownMenuContext.Provider>
    );
}

export function DropdownMenuTrigger({ asChild = false, children, ...props }) {
    const { open, setOpen } = useDropdownMenuContext();
    const Comp = asChild ? Slot : 'button';

    return (
        <Comp
            type={asChild ? undefined : 'button'}
            aria-expanded={open}
            onClick={() => setOpen((value) => !value)}
            {...props}
        >
            {children}
        </Comp>
    );
}

export function DropdownMenuContent({ className, align = 'end', children }) {
    const { open } = useDropdownMenuContext();

    if (!open) {
        return null;
    }

    return (
        <div
            className={cn(
                'absolute top-full z-50 mt-2 min-w-56 overflow-hidden rounded-xl border border-[#c8d4de] bg-white p-1 shadow-lg',
                align === 'end' ? 'right-0' : 'left-0',
                className
            )}
        >
            {children}
        </div>
    );
}

export function DropdownMenuLabel({ className, children }) {
    return <div className={cn('px-2 py-1.5 text-sm font-medium text-[#0f172a]', className)}>{children}</div>;
}

export function DropdownMenuSeparator({ className }) {
    return <div className={cn('my-1 h-px bg-[#c8d4de]', className)} />;
}

export function DropdownMenuItem({ className, asChild = false, onClick, children, ...props }) {
    const { setOpen } = useDropdownMenuContext();
    const Comp = asChild ? Slot : 'button';

    const handleClick = (event) => {
        onClick?.(event);
        setOpen(false);
    };

    return (
        <Comp
            type={asChild ? undefined : 'button'}
            className={cn(
                'flex w-full items-center gap-2 rounded-lg px-2 py-2 text-sm text-[#0f172a] transition-colors hover:bg-[#f5f9fc] focus-visible:bg-[#f5f9fc] focus-visible:outline-none',
                className
            )}
            onClick={handleClick}
            {...props}
        >
            {children}
        </Comp>
    );
}
