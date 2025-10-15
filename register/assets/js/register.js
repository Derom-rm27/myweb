document.addEventListener('DOMContentLoaded', () => {
    const focusTarget = document.querySelector('[data-initial-focus="true"]');

    if (focusTarget instanceof HTMLElement) {
        focusTarget.focus();
    }
});
