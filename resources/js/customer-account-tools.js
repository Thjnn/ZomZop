// Các trang thông báo, địa chỉ và ưu đãi; không thay thế JavaScript hồ sơ/đơn hàng.
function initializeAccountTools() {
    const normalize = value => String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').replace(/Đ/g, 'D').toLowerCase().trim();
    const coupons = document.querySelector('[data-coupon-browser]');
    if (coupons && !coupons.dataset.initialized) {
        coupons.dataset.initialized = '1';
        const search = coupons.querySelector('[data-coupon-search]');
        const scope = coupons.querySelector('[data-coupon-scope]');
        const type = coupons.querySelector('[data-coupon-type]');
        const cards = Array.from(coupons.querySelectorAll('[data-coupon-card]'));
        function filter() {
            let count = 0;
            cards.forEach(card => {
                const show = normalize(card.dataset.search).includes(normalize(search.value)) &&
                    (scope.value === 'all' || card.dataset.scope === scope.value) &&
                    (type.value === 'all' || card.dataset.type === type.value);
                card.hidden = !show;
                if (show) count++;
            });
            coupons.querySelector('[data-coupon-count]').textContent = count;
            coupons.querySelector('[data-coupon-empty]').hidden = count > 0;
        }
        search.addEventListener('input', filter);
        scope.addEventListener('change', filter);
        type.addEventListener('change', filter);
        coupons.querySelector('[data-coupon-reset]').addEventListener('click', () => {
            search.value = ''; scope.value = 'all'; type.value = 'all'; filter(); search.focus();
        });
        async function copyCode(code) {
            if (navigator.clipboard && window.isSecureContext) {
                try { await navigator.clipboard.writeText(code); return true; } catch (_) { /* Thử fallback cho HTTP Laragon. */ }
            }
            const helper = document.createElement('textarea');
            helper.value = code; helper.style.position = 'fixed'; helper.style.left = '-9999px';
            document.body.appendChild(helper); helper.focus(); helper.select();
            let copied = false;
            try { copied = document.execCommand('copy'); } catch (_) { copied = false; }
            helper.remove(); return copied;
        }
        coupons.querySelectorAll('[data-copy-code]').forEach(button => button.addEventListener('click', async () => {
            const success = await copyCode(button.dataset.copyCode);
            const feedback = coupons.querySelector('[data-copy-feedback]');
            feedback.textContent = success ? 'Đã sao chép mã ' + button.dataset.copyCode + '. Nhập mã tại bước thanh toán.' : 'Không thể tự sao chép. Hãy chọn và chép mã ' + button.dataset.copyCode + ' trên thẻ ưu đãi.';
            if (success) {
                button.textContent = 'Đã sao chép';
                setTimeout(() => { button.textContent = 'Sao chép mã'; }, 1800);
            }
            button.focus();
        }));
        filter();
    }
    const book = document.querySelector('[data-address-book]');
    if (book && !book.dataset.initialized) {
        book.dataset.initialized = '1';
        const dialog = book.querySelector('[data-address-dialog]');
        const form = dialog.querySelector('[data-address-form]');
        const method = form.querySelector('[data-address-method]');
        let lastFocus = null;
        function mode(id) {
            method.disabled = !id;
            form.elements.editing_id.value = id || '';
            form.action = id ? form.dataset.base + '/' + encodeURIComponent(id) : form.dataset.create;
            document.getElementById('ac-address-title').textContent = id ? 'Chỉnh sửa địa chỉ' : 'Thêm địa chỉ';
        }
        function show() { lastFocus = document.activeElement; if (!dialog.open) dialog.showModal(); }
        function fill(address) {
            form.reset();
            mode(address ? address.id : null);
            ['name', 'phone', 'address', 'note'].forEach(name => { form.elements[name].value = address ? (address[name] || '') : ''; });
            form.elements.is_default.checked = address ? Boolean(address.is_default) : false;
            const label = address ? address.label : 'Nhà';
            form.querySelectorAll('input[name="label"]').forEach(field => { field.checked = field.value === label; });
            show();
        }
        book.querySelectorAll('[data-address-add]').forEach(button => button.addEventListener('click', () => fill(null)));
        book.querySelectorAll('[data-address-edit]').forEach(button => button.addEventListener('click', () => fill(JSON.parse(button.dataset.addressEdit))));
        dialog.querySelectorAll('[data-address-close]').forEach(button => button.addEventListener('click', () => dialog.close()));
        dialog.addEventListener('close', () => { lastFocus?.focus(); });
        // Escape và quản lý focus dùng hành vi sẵn có của dialog trình duyệt.
        book.querySelector('[data-address-search]').addEventListener('input', event => {
            let count = 0;
            book.querySelectorAll('[data-address-card]').forEach(card => {
                card.hidden = !normalize(card.dataset.search).includes(normalize(event.target.value));
                if (!card.hidden) count++;
            });
            book.querySelector('[data-address-empty]').hidden = count > 0 || !event.target.value.trim();
        });
        if (form.dataset.openInitial === '1') {
            mode(form.elements.editing_id.value);
            show();
        }
    }
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initializeAccountTools, { once: true });
else initializeAccountTools();
