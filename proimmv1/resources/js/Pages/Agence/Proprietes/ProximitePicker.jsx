import * as Collapsible from '@radix-ui/react-collapsible';
import { Check, ChevronDown, X } from 'lucide-react';
import { Button } from '../../../components/ui/button';
import { Input } from '../../../components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../components/ui/select';
import { agenceButtonStyles } from '../../../lib/buttonStyles';
import { cn } from '../../../lib/utils';

const toId = (value) => (value === null || value === undefined || value === '' ? '' : String(value));

const hasValue = (value) => value !== null && value !== undefined && value !== '';

const digitsOnly = (value) => String(value ?? '').replace(/\D+/g, '');

function Field({ label, required, children, error }) {
    return (
        <div className="space-y-1.5">
            <label className="text-sm font-medium text-[#0f172a]">
                {label}
                {required ? <span className="ml-0.5 text-[#b42318]">*</span> : null}
            </label>
            {children}
            <div className="min-h-4">
                {error ? <p className="text-xs leading-4 text-[#b42318]">{error}</p> : null}
            </div>
        </div>
    );
}

export default function ProximitePicker({
    options,
    selected,
    expandedId,
    onToggle,
    onUpdate,
    onRemove,
    onExpand,
    errors,
}) {
    if (!options.length) {
        return <p className="text-sm text-[#94a3b8]">Aucun element disponible.</p>;
    }

    return (
        <div className="grid grid-cols-1 items-start gap-3 md:grid-cols-2 xl:grid-cols-3">
            {options.map((option) => {
                const value = toId(option.id);
                const selectedItem = selected.find((item) => toId(item.id) === value);
                const isActive = Boolean(selectedItem);
                const isExpanded = expandedId === value;
                const isRequired = isActive;

                // Une fois la distance renseignee, la carte est consideree "remplie" :
                // elle reste ouverte en permanence et ne peut plus etre repliee au clic.
                // Seul le bouton X (onRemove) peut encore la retirer de la selection.
                const isFilled = isActive && hasValue(selectedItem?.distance);
                const isOpen = isActive && (isExpanded || isFilled);

                const index = selected.findIndex((item) => toId(item.id) === value);
                const distanceError = index >= 0 ? errors?.[`proximites.${index}.distance`] : null;
                const uniteError = index >= 0 ? errors?.[`proximites.${index}.unite`] : null;

                return (
                    <Collapsible.Root key={value} open={isOpen}>
                        <div
                            className={cn(
                                'w-full self-start overflow-hidden border transition-[border-radius,background-color,box-shadow,border-color] duration-300 ease-out',
                                isOpen ? 'rounded-[1.75rem] border-[#00559b] bg-[#f8fafc] shadow-sm' : 'rounded-full border-[#c8d4de] bg-white'
                            )}
                        >
                            <Collapsible.Trigger asChild>
                                <button
                                    type="button"
                                    onClick={() => {
                                        if (!isActive) {
                                            onToggle(value);
                                            onExpand(value);
                                            return;
                                        }

                                        // Rempli -> on bloque toute fermeture au clic sur l'entete.
                                        if (isFilled) {
                                            return;
                                        }

                                        if (isOpen) {
                                            onToggle(value);
                                            onExpand('');
                                            return;
                                        }

                                        onExpand(value);
                                    }}
                                    className={cn(
                                        'flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition-colors duration-200',
                                        isFilled ? 'cursor-default' : 'cursor-pointer',
                                        isOpen ? 'bg-[#eaf4fb]/70 text-[#00559b]' : 'text-[#5f7182] hover:bg-[#f8fafc] hover:text-[#00559b]'
                                    )}
                                >
                                    <div className="min-w-0">
                                        <p className="text-sm font-semibold">
                                            {option.name}
                                        </p>
                                    </div>

                                    {isFilled ? (
                                        <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#00559b] text-white">
                                            <Check className="h-3 w-3" />
                                        </span>
                                    ) : (
                                        <ChevronDown
                                            className={cn(
                                                'h-4 w-4 shrink-0 transition-transform duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)]',
                                                isOpen && 'rotate-180'
                                            )}
                                        />
                                    )}
                                </button>
                            </Collapsible.Trigger>

                            <Collapsible.Content forceMount>
                                <div
                                    className={cn(
                                        'grid transition-[grid-template-rows] duration-300 ease-out',
                                        isOpen ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'
                                    )}
                                >
                                    <div className="overflow-hidden">
                                        <div className="min-h-0 border-t border-[#e2e8f0] px-4 py-4">
                                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_96px_auto] sm:items-start">
                                                <Field label="Distance" required={isRequired} error={distanceError}>
                                                    <Input
                                                        type="text"
                                                        inputMode="numeric"
                                                        pattern="[0-9]*"
                                                        value={selectedItem?.distance ?? ''}
                                                        onChange={(e) => onUpdate(value, { distance: digitsOnly(e.target.value) })}
                                                        placeholder="Ex: 300"
                                                    />
                                                </Field>

                                                <Field label="Unite" required={isRequired} error={uniteError}>
                                                    <Select
                                                        value={selectedItem?.unite ?? 'm'}
                                                        onValueChange={(nextValue) => onUpdate(value, { unite: nextValue })}
                                                    >
                                                        <SelectTrigger className="w-full">
                                                            <SelectValue placeholder="Unite" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem value="m">m</SelectItem>
                                                            <SelectItem value="km">km</SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </Field>

                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    size="icon"
                                                    className={cn(
                                                        agenceButtonStyles.actionRedIcon,
                                                        'self-start sm:mt-6'
                                                    )}
                                                    onClick={() => onRemove(value)}
                                                >
                                                    <X className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Collapsible.Content>
                        </div>
                    </Collapsible.Root>
                );
            })}
        </div>
    );
}
