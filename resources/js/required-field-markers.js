const REQUIRED_CONTROL_SELECTOR = '[required], [aria-required="true"]';
const MARKER_CLASS = 'required-field-label';

function hasVisibleAsterisk(label) {
    return label.textContent.includes('*');
}

function findLabel(control) {
    if (control.id) {
        const escapedId = window.CSS?.escape
            ? window.CSS.escape(control.id)
            : control.id.replace(/(["\\])/g, '\\$1');
        const explicitLabel = document.querySelector(`label[for="${escapedId}"]`);

        if (explicitLabel) {
            return explicitLabel;
        }
    }

    const wrappingLabel = control.closest('label');
    if (wrappingLabel) {
        const nestedLabels = Array.from(wrappingLabel.querySelectorAll('label'));
        return nestedLabels.at(-1) ?? wrappingLabel;
    }

    let container = control.parentElement;
    while (container && container.tagName !== 'FORM') {
        const labels = Array.from(container.children).filter(
            (child) => child.tagName === 'LABEL',
        );

        if (labels.length) {
            return labels.at(-1);
        }

        container = container.parentElement;
    }

    return null;
}

function refreshRequiredFieldMarkers(root = document) {
    const controls = root.matches?.(REQUIRED_CONTROL_SELECTOR)
        ? [root, ...root.querySelectorAll(REQUIRED_CONTROL_SELECTOR)]
        : root.querySelectorAll(REQUIRED_CONTROL_SELECTOR);

    controls.forEach((control) => {
        if (control.matches('[type="hidden"]')) {
            return;
        }

        const label = findLabel(control);
        if (label) {
            label.classList.toggle(MARKER_CLASS, !hasVisibleAsterisk(label));
        }
    });
}

function initializeRequiredFieldMarkers() {
    refreshRequiredFieldMarkers();

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes') {
                const previousLabel = findLabel(mutation.target);
                if (previousLabel && !mutation.target.matches(REQUIRED_CONTROL_SELECTOR)) {
                    previousLabel.classList.remove(MARKER_CLASS);
                }
                refreshRequiredFieldMarkers(mutation.target.closest('form') ?? document);
                return;
            }

            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    refreshRequiredFieldMarkers(node);
                }
            });
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['required', 'aria-required'],
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeRequiredFieldMarkers, { once: true });
} else {
    initializeRequiredFieldMarkers();
}
