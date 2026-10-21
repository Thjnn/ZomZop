/* Progressive enhancement: các form vẫn gửi bình thường khi không có JS. */
function initializeCustomerProfile() {
    const root = document.getElementById('zz-profile');
    if (!root || root.dataset.initialized === 'true') return;
    root.dataset.initialized = 'true';
    const form = root.querySelector('#zz-info-form');
    const input = root.querySelector('#zz-avatar-input');
    const preview = root.querySelector('#zz-avatar-preview');
    const hint = root.querySelector('#zz-avatar-hint');
    const error = root.querySelector('#zz-avatar-error');
    const cancel = root.querySelector('#zz-avatar-cancel');
    const saveHint = root.querySelector('#zz-save-hint');
    const originalAvatar = preview.innerHTML;
    const originalHint = hint.textContent;
    let objectUrl = null;
    let revision = 0;
    const snapshot = () => Array.from(new FormData(form).entries())
        .filter(([key]) => key !== '_token' && key !== 'avatar')
        .map(([key, value]) => [key, String(value)]);
    const baseline = JSON.stringify(snapshot());
    const updateHint = () => {
        const dirty = JSON.stringify(snapshot()) !== baseline || input.files.length > 0;
        saveHint.textContent = dirty ? 'Bạn có thay đổi chưa lưu.' : 'Bấm lưu để cập nhật thông tin.';
    };
    const clearPreview = () => {
        revision++;
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;
        preview.innerHTML = originalAvatar;
        hint.textContent = originalHint;
        error.textContent = '';
        cancel.hidden = true;
    };
    input.addEventListener('change', () => {
        clearPreview();
        const file = input.files[0];
        if (!file) { updateHint(); return; }
        if (!['image/jpeg', 'image/png', 'image/webp', 'image/gif'].includes(file.type) || file.size > 2 * 1024 * 1024) {
            input.value = '';
            error.textContent = 'Chọn ảnh JPG, PNG, WebP hoặc GIF có dung lượng tối đa 2 MB.';
            updateHint();
            return;
        }
        const token = revision;
        objectUrl = URL.createObjectURL(file);
        const image = new Image();
        image.alt = 'Ảnh đại diện xem trước, chưa lưu';
        image.onload = () => {
            if (token !== revision) return;
            preview.replaceChildren(image);
            hint.textContent = 'Đã chọn ' + file.name + '. Bấm “Lưu ảnh và thông tin” để lưu lên tài khoản.';
            cancel.hidden = false;
        };
        image.onerror = () => {
            if (token !== revision) return;
            input.value = '';
            clearPreview();
            error.textContent = 'Không đọc được ảnh này. Vui lòng chọn ảnh khác.';
            updateHint();
        };
        image.src = objectUrl;
        updateHint();
    });
    cancel.addEventListener('click', () => { input.value = ''; clearPreview(); updateHint(); });
    form.addEventListener('input', updateHint);
    form.addEventListener('change', updateHint);
    // Reset về giá trị trong lần tải trang này (kể cả old input khi server trả lỗi).
    form.addEventListener('reset', () => { clearPreview(); setTimeout(updateHint, 0); });
    root.querySelectorAll('[data-password-toggle]').forEach(button => {
        button.hidden = false;
        const field = document.getElementById(button.dataset.passwordToggle);
        const label = root.querySelector(`label[for="${field.id}"]`).textContent.toLowerCase();
        button.addEventListener('click', () => {
            const visible = field.type === 'password';
            field.type = visible ? 'text' : 'password';
            button.textContent = visible ? 'Ẩn' : 'Hiện';
            button.setAttribute('aria-pressed', String(visible));
            button.setAttribute('aria-label', (visible ? 'Ẩn ' : 'Hiện ') + label);
        });
    });
    const passwordForm = root.querySelector('#zz-password-form');
    const password = passwordForm.elements.password;
    const confirmation = passwordForm.elements.password_confirmation;
    const checkConfirmation = () => confirmation.setCustomValidity(
        confirmation.value && confirmation.value !== password.value ? 'Mật khẩu nhập lại chưa khớp.' : ''
    );
    password.addEventListener('input', checkConfirmation);
    confirmation.addEventListener('input', checkConfirmation);

    const tabBar = root.querySelector('.zz-tabs');
    const tabButtons = Array.from(root.querySelectorAll('[data-account-tab]'));
    if (tabBar && tabButtons.length) {
        tabBar.hidden = false;
        tabBar.setAttribute('role', 'tablist');
        function selectAccountTab(button, focus = false) {
            tabButtons.forEach(item => {
                const active = item === button;
                item.setAttribute('aria-selected', String(active));
                item.tabIndex = active ? 0 : -1;
                document.getElementById(item.dataset.accountTab).hidden = !active;
            });
            if (focus) button.focus();
        }
        tabButtons.forEach((button, index) => {
            const panel = document.getElementById(button.dataset.accountTab);
            button.setAttribute('role', 'tab');
            button.setAttribute('aria-controls', panel.id);
            panel.setAttribute('role', 'tabpanel');
            panel.setAttribute('aria-labelledby', button.id);
            button.addEventListener('click', () => selectAccountTab(button));
            button.addEventListener('keydown', event => {
                let next = index;
                if (event.key === 'ArrowRight') next = (index + 1) % tabButtons.length;
                else if (event.key === 'ArrowLeft') next = (index + tabButtons.length - 1) % tabButtons.length;
                else if (event.key === 'Home') next = 0;
                else if (event.key === 'End') next = tabButtons.length - 1;
                else return;
                event.preventDefault();
                selectAccountTab(tabButtons[next], true);
            });
        });
        const passwordNotice = root.querySelector('#zz-security .zz-error') || root.querySelector('[data-password-saved]');
        selectAccountTab(tabButtons[passwordNotice ? 1 : 0]);
    }
    if (root.querySelector('#zz-security .zz-error')) {
        root.querySelector('#zz-security input[type=password]')?.focus({ preventScroll: true });
        root.querySelector('#zz-security').scrollIntoView({ block: 'center' });
    }
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCustomerProfile, { once: true });
} else {
    initializeCustomerProfile();
}
