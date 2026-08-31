import React from 'react';

import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from './select';

function optionRows(children, rows = []) {
    React.Children.forEach(children, (child) => {
        if (!React.isValidElement(child)) return;

        if (child.type === 'option') {
            const label = React.Children.toArray(child.props.children).join('');
            rows.push({
                value: String(child.props.value ?? label),
                label,
                disabled: Boolean(child.props.disabled),
            });
            return;
        }

        if (child.props?.children) optionRows(child.props.children, rows);
    });

    return rows;
}

/**
 * Remplacement recherchable d'un <select> natif. Son API conserve value,
 * defaultValue, onChange, name et les balises <option> pour faciliter la migration.
 */
export function SearchableSelect({
    value,
    defaultValue,
    onChange,
    name,
    disabled = false,
    className = '',
    children,
    style,
    'aria-label': ariaLabel,
}) {
    const options = React.useMemo(() => optionRows(children), [children]);
    const fallbackValue = String(defaultValue ?? options[0]?.value ?? '');
    const [internalValue, setInternalValue] = React.useState(fallbackValue);
    const controlled = value !== undefined;
    const currentValue = String(controlled ? value : internalValue);

    const changeValue = (nextValue) => {
        if (!controlled) setInternalValue(nextValue);
        onChange?.({ target: { value: nextValue, name } });
    };

    return (
        <div style={style}>
            {name ? <input type="hidden" name={name} value={currentValue} /> : null}
            <Select value={currentValue} onValueChange={changeValue} disabled={disabled}>
                <SelectTrigger className={className} disabled={disabled} aria-label={ariaLabel}>
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    {options.map((option) => (
                        <SelectItem key={option.value} value={option.value} disabled={option.disabled}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}
