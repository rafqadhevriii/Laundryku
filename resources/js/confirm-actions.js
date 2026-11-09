const dialog = document.querySelector('#action-confirmation');
if (dialog) {
    let pending = null;
    let returnFocus = null;
    let previousOverflow = '';
    for (const form of document.querySelectorAll('form[data-confirm-title]')) {
        form.addEventListener('submit', event => {
            if (form.dataset.confirmApproved === 'true') {
                delete form.dataset.confirmApproved;
                return;
            }
            if (typeof dialog.showModal !== 'function') {
                if (!window.confirm(`${form.dataset.confirmTitle}\n${form.dataset.confirmDescription}`)) event.preventDefault();
                return;
            }
            event.preventDefault();
            pending = { form, submitter: event.submitter };
            returnFocus = event.submitter || document.activeElement;
            dialog.querySelector('#confirmation-title').textContent = form.dataset.confirmTitle;
            dialog.querySelector('#confirmation-description').textContent = form.dataset.confirmDescription;
            previousOverflow = document.body.style.overflow;
            dialog.showModal();
            document.body.style.overflow = 'hidden';
            dialog.querySelector('[data-confirm-cancel]').focus();
        });
    }
    dialog.querySelector('[data-confirm-cancel]').addEventListener('click', () => dialog.close());
    dialog.querySelector('[data-confirm-accept]').addEventListener('click', () => {
        if (!pending) return;
        const { form, submitter } = pending;
        dialog.close();
        form.dataset.confirmApproved = 'true';
        if (submitter) form.requestSubmit(submitter); else form.requestSubmit();
    });
    dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
    dialog.addEventListener('close', () => {
        document.body.style.overflow = previousOverflow;
        if (returnFocus?.isConnected) returnFocus.focus();
        pending = null;
    });
    dialog.addEventListener('keydown', event => {
        if (event.key !== 'Tab') return;
        const first = dialog.querySelector('[data-confirm-cancel]');
        const last = dialog.querySelector('[data-confirm-accept]');
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });
}
