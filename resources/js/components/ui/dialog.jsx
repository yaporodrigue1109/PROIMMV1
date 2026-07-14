import React from 'react';
import { createPortal } from 'react-dom';
import { X } from 'lucide-react';
import { cn } from '../../lib/utils';

const DialogContext = React.createContext(null);

function Dialog({ open: controlledOpen, defaultOpen = false, onOpenChange, children }) {
    const [internalOpen, setInternalOpen] = React.useState(defaultOpen);
    const isControlled = controlledOpen !== undefined;
    const open = isControlled ? controlledOpen : internalOpen;

    const setOpen = React.useCallback(
        (nextOpen) => {
            if (!isControlled) {
                setInternalOpen(nextOpen);
            }

            onOpenChange?.(nextOpen);
        },
        [isControlled, onOpenChange]
    );

    return <DialogContext.Provider value={{ open, setOpen }}>{children}</DialogContext.Provider>;
}

function DialogTrigger({ asChild = false, children, ...props }) {
    const context = React.useContext(DialogContext);

    if (asChild && React.isValidElement(children)) {
        return React.cloneElement(children, {
            ...props,
            onClick: (event) => {
                children.props.onClick?.(event);
                context?.setOpen?.(true);
            },
        });
    }

    return (
        <button type="button" onClick={() => context?.setOpen?.(true)} {...props}>
            {children}
        </button>
    );
}

function DialogPortal({ children }) {
    if (typeof document === 'undefined') {
        return null;
    }

    return createPortal(children, document.body);
}

function DialogContent({ className, children, ...props }) {
    const context = React.useContext(DialogContext);

    React.useEffect(() => {
        if (!context?.open) return undefined;

        const handleKeyDown = (event) => {
            if (event.key === 'Escape') {
                context.setOpen(false);
            }
        };

        document.addEventListener('keydown', handleKeyDown);
        document.body.style.overflow = 'hidden';

        return () => {
            document.removeEventListener('keydown', handleKeyDown);
            document.body.style.overflow = '';
        };
    }, [context]);

    if (!context?.open) {
        return null;
    }

    return (
        <DialogPortal>
            <div
                className="fixed inset-0 z-50 flex items-center justify-center bg-[#0f172a]/45 p-4"
                onMouseDown={(event) => {
                    if (event.target === event.currentTarget) {
                        context.setOpen(false);
                    }
                }}
            >
                <div
                    role="dialog"
                    aria-modal="true"
                    className={cn('relative w-full rounded-2xl border border-[#c8d4de] bg-white p-5 shadow-2xl', className)}
                    {...props}
                >
                    <button
                        type="button"
                        aria-label="Fermer"
                        className="absolute right-4 top-4 rounded-md p-1 text-[#5f7182] transition hover:bg-[#f1f5f9] hover:text-[#0f172a]"
                        onClick={() => context.setOpen(false)}
                    >
                        <X className="h-4 w-4" />
                    </button>
                    {children}
                </div>
            </div>
        </DialogPortal>
    );
}

function DialogHeader({ className, ...props }) {
    return <div className={cn('flex flex-col gap-2 text-left', className)} {...props} />;
}

function DialogTitle({ className, ...props }) {
    return <h2 className={cn('text-lg font-semibold text-[#0f172a]', className)} {...props} />;
}

function DialogDescription({ className, ...props }) {
    return <p className={cn('text-sm leading-relaxed text-[#5f7182]', className)} {...props} />;
}

function DialogFooter({ className, ...props }) {
    return <div className={cn('flex flex-col-reverse gap-2 sm:flex-row sm:justify-end', className)} {...props} />;
}

function DialogClose({ asChild = false, children, ...props }) {
    const context = React.useContext(DialogContext);

    if (asChild && React.isValidElement(children)) {
        return React.cloneElement(children, {
            ...props,
            onClick: (event) => {
                children.props.onClick?.(event);
                context?.setOpen?.(false);
            },
        });
    }

    return (
        <button type="button" onClick={() => context?.setOpen?.(false)} {...props}>
            {children}
        </button>
    );
}

export { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger };
