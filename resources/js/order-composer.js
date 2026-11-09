const form = document.querySelector('[data-order-form]');
if (form) {
    const items = form.querySelector('[data-order-items]');
    const template = form.querySelector('[data-order-template]');
    const add = form.querySelector('[data-order-add]');
    const customer = form.querySelector('[data-order-customer]');
    const announcement = form.querySelector('[data-order-announcement]');
    const rows = () => Array.from(items.querySelectorAll('[data-order-line]'));
    let nextIndex = Math.max(-1, ...rows().map(row => Number(row.dataset.index)).filter(Number.isFinite)) + 1;
    add.hidden = false;
    function refresh() {
        const list = form.querySelector('[data-summary-lines]');
        list.replaceChildren();
        let longest = 0;
        for (const [position, row] of rows().entries()) {
            row.querySelector('[data-row-number]').textContent = String(position + 1);
            const remove = row.querySelector('[data-remove-service]');
            remove.hidden = false;
            remove.disabled = rows().length === 1;
            remove.setAttribute('aria-label', `Hapus layanan baris ${position + 1}`);
            const option = row.querySelector('[data-service-select]').selectedOptions[0];
            const quantity = row.querySelector('[data-service-quantity]');
            const perItem = option?.dataset.unit === 'item';
            quantity.step = perItem ? '1' : '0.01';
            quantity.min = perItem ? '1' : '0.01';
            row.querySelector('[data-quantity-label]').textContent = option?.value ? (perItem ? 'Jumlah barang' : 'Berat (kg)') : 'Berat / jumlah';
            row.querySelector('[data-quantity-hint]').textContent = perItem ? 'Gunakan jumlah bulat, minimal satu barang.' : 'Masukkan berat dalam kg, hingga dua angka desimal.';
            if (!option?.value) {
                row.querySelector('[data-service-info]').textContent = 'Pilih layanan untuk melihat tarif dan durasinya.';
                continue;
            }
            const hours = Number(option.dataset.hours);
            longest = Math.max(longest, Number.isFinite(hours) ? hours : 0);
            const tariff = `${option.dataset.priceLabel}/${option.dataset.unit}`;
            row.querySelector('[data-service-info]').textContent = `Tarif ${tariff} · durasi ${option.dataset.hours} jam.`;
            const entry = document.createElement('li');
            const name = document.createElement('strong');
            name.textContent = option.dataset.label;
            const details = document.createElement('span');
            details.textContent = `${quantity.value || '—'} ${option.dataset.unit} · tarif ${tariff}`;
            entry.append(name, details);
            list.append(entry);
        }
        if (!list.children.length) {
            const empty = document.createElement('li');
            empty.textContent = 'Belum ada layanan dipilih.';
            list.append(empty);
        }
        form.querySelector('[data-summary-customer]').textContent = customer.value ? customer.selectedOptions[0].textContent : 'Pilih pelanggan untuk pesanan ini.';
        form.querySelector('[data-summary-duration]').textContent = longest ? `Layanan terlama: ${longest} jam sejak pesanan dibuat. Waktu selesai resmi ditetapkan saat disimpan.` : 'Estimasi mengikuti durasi layanan yang dipilih.';
        add.disabled = rows().length >= 20;
    }
    add.addEventListener('click', () => {
        if (rows().length >= 20) return;
        const row = template.content.firstElementChild.cloneNode(true);
        for (const element of [row, ...row.querySelectorAll('*')]) {
            for (const attribute of ['id', 'name', 'for', 'aria-describedby', 'data-index']) {
                if (element.hasAttribute(attribute)) element.setAttribute(attribute, element.getAttribute(attribute).replaceAll('__INDEX__', String(nextIndex)));
            }
        }
        nextIndex++;
        items.append(row);
        refresh();
        row.querySelector('select').focus();
        announcement.textContent = `${rows().length} layanan. Baris baru ditambahkan.`;
    });
    items.addEventListener('click', event => {
        const button = event.target.closest('[data-remove-service]');
        if (!button || rows().length <= 1) return;
        const row = button.closest('[data-order-line]');
        const position = rows().indexOf(row);
        row.remove();
        refresh();
        rows()[Math.min(position, rows().length - 1)].querySelector('select').focus();
        announcement.textContent = `${rows().length} layanan. Baris dihapus.`;
    });
    form.addEventListener('input', refresh);
    form.addEventListener('change', refresh);

    refresh();
}
