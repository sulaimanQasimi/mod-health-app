/**
 * Sync the count badge on the nearest parent accordion header.
 * Supports plain totals or completed/total (e.g. lab "3/10").
 *
 * @param {Element|string|null} elOrSelector - Element inside the accordion (or selector)
 * @param {{ total?: number, completed?: number|null, badgeClass?: string|null }} options
 */
export function updateNearestAccordionBadge(elOrSelector, options = {}) {
    const { total = 0, completed = null, badgeClass = null } = options;

    const root =
        typeof elOrSelector === 'string'
            ? document.querySelector(elOrSelector)
            : elOrSelector;

    if (!root || !(root instanceof Element)) {
        return;
    }

    const accordionItem = root.closest('.accordion-item');
    if (!accordionItem) {
        return;
    }

    const button = accordionItem.querySelector('.accordion-header .accordion-button');
    if (!button) {
        return;
    }

    let badge = button.querySelector('.accordion-count-badge');
    if (!badge) {
        badge = button.querySelector('.badge.ms-2');
        if (badge) {
            badge.classList.add('accordion-count-badge');
        }
    }

    const safeTotal = Number(total) || 0;
    const hasCompleted = completed !== null && completed !== undefined;
    const safeCompleted = hasCompleted ? Number(completed) || 0 : null;
    const text = hasCompleted ? `${safeCompleted}/${safeTotal}` : String(safeTotal);

    let resolvedClass = badgeClass;
    if (!resolvedClass) {
        if (hasCompleted) {
            if (safeTotal > 0 && safeCompleted >= safeTotal) {
                resolvedClass = 'bg-success';
            } else if (safeCompleted > 0) {
                resolvedClass = 'bg-warning text-dark';
            } else {
                resolvedClass = 'bg-primary';
            }
        } else {
            resolvedClass = 'bg-primary';
        }
    }

    if (!badge) {
        badge = document.createElement('span');
        button.appendChild(badge);
    }

    badge.className = `badge ${resolvedClass} ms-2 accordion-count-badge`;
    badge.textContent = text;
}

/**
 * Convenience for Vue components that live inside an accordion body.
 */
export function syncVueAccordionBadge(vueInstance, options = {}) {
    const el = vueInstance?.$el;
    if (el && el.nodeType === 1) {
        updateNearestAccordionBadge(el, options);
        return;
    }

    // Comment/text root fallback: walk up from mount parent if available
    const parent = vueInstance?.$el?.parentElement;
    if (parent) {
        updateNearestAccordionBadge(parent, options);
    }
}
