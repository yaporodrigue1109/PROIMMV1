import React from 'react';
import { createPortal } from 'react-dom';
import { ChevronDown } from 'lucide-react';
import { cn } from '../../lib/utils';

const SelectContext = React.createContext(null);
const MENU_MAX_HEIGHT = 288;

function getNodeText(children) {
    return React.Children.toArray(children)
        .map((child) => {
            if (typeof child === 'string' || typeof child === 'number') {
                return String(child);
            }

            if (React.isValidElement(child) && typeof child.props.children !== 'undefined') {
                return getNodeText(child.props.children);
            }

            return '';
        })
        .join('')
        .trim();
}

function collectOptionLabels(children, labels = new Map()) {
    React.Children.forEach(children, (child) => {
        if (!React.isValidElement(child)) {
            return;
        }

        if (child.props && Object.prototype.hasOwnProperty.call(child.props, 'value')) {
            labels.set(child.props.value, getNodeText(child.props.children));
        }

        if (child.props && child.props.children) {
            collectOptionLabels(child.props.children, labels);
        }
    });

    return labels;
}

function Select({
    value,
    defaultValue,
    onValueChange,
    className,
    disabled = false,
    children,
    ...props
}) {
    const rootRef = React.useRef(null);
    const triggerRef = React.useRef(null);
    const contentRef = React.useRef(null);
    const [internalValue, setInternalValue] = React.useState(defaultValue ?? '');
    const [open, setOpen] = React.useState(false);
    const [items, setItems] = React.useState(new Map());
    const staticItems = React.useMemo(() => collectOptionLabels(children), [children]);

    const isControlled = value !== undefined;
    const currentValue = isControlled ? value : internalValue;

    const updateValue = React.useCallback(
        (nextValue) => {
            if (!isControlled) {
                setInternalValue(nextValue);
            }

            onValueChange?.(nextValue);
        },
        [isControlled, onValueChange]
    );

    React.useEffect(() => {
        function handlePointerDown(event) {
            const target = event.target;
            const clickedInsideRoot = rootRef.current && rootRef.current.contains(target);
            const clickedInsideContent = contentRef.current && contentRef.current.contains(target);

            if (!clickedInsideRoot && !clickedInsideContent) {
                setOpen(false);
            }
        }

        function handleEscape(event) {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        }

        document.addEventListener('mousedown', handlePointerDown);
        document.addEventListener('touchstart', handlePointerDown);
        document.addEventListener('keydown', handleEscape);

        return () => {
            document.removeEventListener('mousedown', handlePointerDown);
            document.removeEventListener('touchstart', handlePointerDown);
            document.removeEventListener('keydown', handleEscape);
        };
    }, []);

    // FIX #2: quand la liste d'options change (ex: région -> ville en cascade),
    // on repart d'une Map vide plutôt que d'accumuler les anciennes entrées.
    // Cela évite qu'un label obsolète (d'une ville d'une région précédente)
    // reste caché dans le cache et soit ré-affiché par erreur.
    React.useEffect(() => {
        setItems(new Map());
    }, [children]);

    const contextValue = React.useMemo(
        () => ({
            value: currentValue,
            open,
            setOpen: disabled ? () => {} : setOpen,
            triggerRef,
            contentRef,
            selectValue: updateValue,
            registerItem: (itemValue, label) => {
                setItems((prev) => {
                    if (prev.get(itemValue) === label) {
                        return prev;
                    }
                    const next = new Map(prev);
                    next.set(itemValue, label);
                    return next;
                });
            },
            unregisterItem: (itemValue) => {
                setItems((prev) => {
                    if (!prev.has(itemValue)) {
                        return prev;
                    }
                    const next = new Map(prev);
                    next.delete(itemValue);
                    return next;
                });
            },
            getLabel: (itemValue) => items.get(itemValue) ?? staticItems.get(itemValue),
        }),
        [currentValue, disabled, items, open, staticItems, updateValue]
    );

    return (
        <SelectContext.Provider value={contextValue}>
            <div ref={rootRef} className={cn('relative block w-full align-top', className)} {...props}>
                {children}
            </div>
        </SelectContext.Provider>
    );
}

function SelectTrigger({ className, children, ...props }) {
    const context = React.useContext(SelectContext);

    return (
        <button
            ref={context?.triggerRef}
            type="button"
            aria-expanded={context?.open}
            aria-haspopup="listbox"
            onClick={() => context?.setOpen?.(!context.open)}
            disabled={props.disabled}
            className={cn(
                'flex h-10 w-full items-center justify-between rounded-md border border-[#c8d4de] bg-white px-3 py-2 text-sm text-[#0f172a] ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#00559b] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                className
            )}
            {...props}
        >
            <span className="min-w-0 flex-1 truncate text-left">{children}</span>
            <ChevronDown className="ml-2 h-4 w-4 shrink-0 opacity-60" />
        </button>
    );
}

function SelectValue({ placeholder, children }) {
    const context = React.useContext(SelectContext);
    const label = context?.value ? context.getLabel(context.value) : '';

    if (children) {
        return children;
    }

    return <span>{label || placeholder || ''}</span>;
}

function SelectContent({ className, children, ...props }) {
    const context = React.useContext(SelectContext);
    const [position, setPosition] = React.useState(null);

    React.useEffect(() => {
        if (!context?.open) {
            setPosition(null);
            return;
        }

        const updatePosition = () => {
            const trigger = context?.triggerRef?.current;
            if (!trigger) {
                return;
            }

            const rect = trigger.getBoundingClientRect();
            const gap = 8;
            const spaceBelow = window.innerHeight - rect.bottom - gap;
            const spaceAbove = rect.top - gap;
            const openUp = spaceBelow < MENU_MAX_HEIGHT && spaceAbove > spaceBelow;

            // FIX #1: on n'ancre plus le menu avec un "top" calculé en supposant
            // une hauteur maximale (288px). On ancre plutôt par le bas ("bottom")
            // quand le menu s'ouvre vers le haut, pour que sa position réelle
            // suive sa hauteur réelle (basée sur le nombre d'options filtrées),
            // au lieu de laisser un grand vide entre le menu et le champ.
            setPosition({
                left: rect.left,
                width: rect.width,
                maxHeight: openUp ? Math.min(spaceAbove, MENU_MAX_HEIGHT) : Math.min(spaceBelow, MENU_MAX_HEIGHT),
                placement: openUp ? 'top' : 'bottom',
                top: openUp ? undefined : rect.bottom + gap,
                bottom: openUp ? window.innerHeight - rect.top + gap : undefined,
            });
        };

        updatePosition();
        window.addEventListener('resize', updatePosition);
        window.addEventListener('scroll', updatePosition, true);

        return () => {
            window.removeEventListener('resize', updatePosition);
            window.removeEventListener('scroll', updatePosition, true);
        };
    }, [context?.open, context?.triggerRef, children]);

    if (!context?.open || !position) {
        return null;
    }

    return createPortal(
        <div
            ref={context?.contentRef}
            role="listbox"
            className={cn(
                'fixed z-[70] max-h-72 overflow-auto rounded-md border border-[#c8d4de] bg-white p-1 shadow-lg',
                className
            )}
            style={{
                left: position.left,
                width: position.width,
                maxHeight: position.maxHeight,
                ...(position.placement === 'top'
                    ? { bottom: position.bottom }
                    : { top: position.top }),
            }}
            {...props}
        >
            {children}
        </div>,
        document.body
    );
}

function SelectItem({ className, value, children, ...props }) {
    const context = React.useContext(SelectContext);
    const label = getNodeText(children);

    React.useEffect(() => {
        context?.registerItem?.(value, label);

        // FIX #2 (suite): nettoyage au démontage de l'item, indispensable
        // pour un select en cascade (ex: ville filtrée par région) où les
        // SelectItem sont montés/démontés dynamiquement.
        return () => {
            context?.unregisterItem?.(value);
        };
    }, [context, label, value]);

    const selected = context?.value === value;

    return (
        <button
            type="button"
            role="option"
            aria-selected={selected}
            onClick={() => {
                context?.selectValue?.(value);
                context?.setOpen?.(false);
            }}
            className={cn(
                'flex w-full items-center rounded-sm px-2.5 py-2 text-left text-sm outline-none transition-colors hover:bg-[#eef5fb] focus:bg-[#eef5fb]',
                selected && 'bg-[#eef5fb] font-medium text-[#00559b]',
                className
            )}
            {...props}
        >
            {children}
        </button>
    );
}

export { Select, SelectContent, SelectItem, SelectTrigger, SelectValue };