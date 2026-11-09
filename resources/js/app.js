import './bootstrap';

document.documentElement.classList.add('js');

const drawer = document.querySelector('#mobile-navigation');
const opener = document.querySelector('[data-nav-open]');
if (drawer && opener && typeof drawer.showModal === 'function') {
    opener.hidden = false;
    const close = () => drawer.close();
    opener.addEventListener('click', () => {
        drawer.showModal();
        opener.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
        drawer.querySelector('[data-nav-close]').focus();
    });
    drawer.querySelector('[data-nav-close]').addEventListener('click', close);
    drawer.addEventListener('keydown', event => {
        if (event.key !== 'Tab') return;
        const items = Array.from(drawer.querySelectorAll('a[href], button:not([disabled]), [tabindex="0"]'))
            .filter(item => item.getClientRects().length);
        const first = items[0];
        const last = items.at(-1);
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last?.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first?.focus();
        }
    });
    drawer.addEventListener('click', event => { if (event.target === drawer) close(); });
    drawer.addEventListener('close', () => {
        opener.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        if (opener.getClientRects().length) opener.focus();
    });
    window.matchMedia('(min-width: 1025px)').addEventListener('change', event => {
        if (event.matches && drawer.open) close();
    });
} else {
    // Keep the native details navigation available if dialog is unsupported.
    document.querySelector('.navigation-fallback')?.classList.add('fallback-required');
}

for (const toggle of document.querySelectorAll('[data-password-toggle]')) {
    const field = document.getElementById(toggle.dataset.passwordToggle);
    if (!field) continue;
    toggle.hidden = false;
    const label = toggle.getAttribute('aria-label');
    toggle.addEventListener('click', () => {
        const reveal = field.type === 'password';
        field.type = reveal ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(reveal));
        toggle.setAttribute('aria-label', reveal ? label.replace('Tampilkan', 'Sembunyikan') : label);
    });
}

for (const form of document.querySelectorAll('[data-pending-form]')) {
    form.addEventListener('submit', () => {
        if (!form.checkValidity()) return;
        form.setAttribute('aria-busy', 'true');
        const button = form.querySelector('[data-pending-label]');
        if (!button) return;
        button.dataset.originalLabel = button.querySelector('span').textContent;
        button.querySelector('span').textContent = button.dataset.pendingLabel;
        // Let native form submission collect its fields before disabling the button.
        queueMicrotask(() => { button.disabled = true; });
    });
}
window.addEventListener('pageshow', () => {
    for (const form of document.querySelectorAll('[data-pending-form]')) {
        form.removeAttribute('aria-busy');
        const button = form.querySelector('[data-pending-label]');
        if (button) {
            button.disabled = false;
            if (button.dataset.originalLabel) button.querySelector('span').textContent = button.dataset.originalLabel;
        }
    }
});

// Existing large tables keep their content and gain a labelled keyboard scroll area.
for (const table of document.querySelectorAll('.main table')) {
    const parent = table.parentElement;
    const scroll = parent.classList.contains('table-wrap') || /auto|scroll/.test(getComputedStyle(parent).overflowX)
        ? parent : table.parentNode.insertBefore(document.createElement('div'), table);
    if (scroll !== parent) scroll.appendChild(table);
    scroll.classList.add('table-scroll');
    const update = () => {
        const overflows = scroll.scrollWidth > scroll.clientWidth + 1;
        let hint = scroll.previousElementSibling;
        if (overflows && !scroll.hasAttribute('data-scroll-ready')) {
            hint = document.createElement('p');
            hint.className = 'table-scroll-hint';
            hint.id = `table-scroll-hint-${Array.from(document.querySelectorAll('.table-scroll')).indexOf(scroll)}`;
            hint.textContent = 'Geser tabel ke samping untuk melihat kolom lainnya.';
            scroll.before(hint);
            scroll.dataset.scrollReady = 'true';
            scroll.setAttribute('aria-describedby', hint.id);
        }
        if (overflows) {
            scroll.setAttribute('tabindex', '0');
            scroll.setAttribute('role', 'region');
            scroll.setAttribute('aria-label', 'Tabel yang dapat digeser');
        } else {
            scroll.removeAttribute('tabindex');
            scroll.removeAttribute('role');
            scroll.removeAttribute('aria-label');
        }
        if (hint?.classList.contains('table-scroll-hint')) hint.hidden = !overflows;
    };
    new ResizeObserver(update).observe(scroll);
    update();
}
