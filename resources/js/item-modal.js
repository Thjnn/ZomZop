// resources/js/item-modal.js

let modalQty = 1;
let modalItem = null;

// ==================== HELPERS ====================

function isLoggedIn() {
    return (
        document.querySelector('meta[name="auth-check"]')?.content === "true"
    );
}

function redirectToLogin() {
    window.location.href = document.querySelector(
        'meta[name="login-url"]',
    )?.content;
}

function showToast(message, icon = "⚠️", duration = 3000) {
    const toast = document.getElementById("toast");
    if (!toast) return;
    document.getElementById("toast-icon").textContent = icon;
    document.getElementById("toast-message").textContent = message;
    toast.classList.remove("opacity-0", "invisible", "-translate-y-2");
    toast.classList.add("opacity-100", "visible", "translate-y-0");
    setTimeout(() => {
        toast.classList.add("opacity-0", "invisible", "-translate-y-2");
        toast.classList.remove("opacity-100", "visible", "translate-y-0");
    }, duration);
}

function requireLogin() {
    showToast("Vui lòng đăng nhập để tiếp tục!", "🔐");
    setTimeout(() => redirectToLogin(), 1500);
}

function addToCart(menuItemId, quantity, note) {
    fetch("/cart/add", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content,
        },
        body: JSON.stringify({ menu_item_id: menuItemId, quantity, note }),
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                showToast(data.message, "🛒");
                const badge = document.getElementById("cart-badge");
                if (badge) badge.textContent = data.count;
            } else if (data.redirect) {
                showToast(data.message, "⚠️");
                setTimeout(() => (window.location.href = data.redirect), 1500);
            }
        })
        .catch((err) => console.error("Lỗi thêm giỏ hàng:", err));
}

// ==================== MODAL ====================

async function openItemModal(id) {
    modalQty = 1;
    document.getElementById("modal-qty").textContent = 1;

    try {
        const res = await fetch(`/menu-items/${id}/detail`);
        const item = await res.json();
        modalItem = item;

        // Gán data-item-id cho modal để nút favorite hoạt động
        document.getElementById("item-modal").dataset.itemId = id;

        document.getElementById("modal-image").src = item.image_url;
        document.getElementById("modal-image").alt = item.name;
        document.getElementById("modal-name").textContent = item.name;
        document.getElementById("modal-category").textContent =
            item.category ?? "";
        document.getElementById("modal-description").textContent =
            item.description ?? "Chưa có mô tả.";

        if (item.is_on_sale) {
            document
                .getElementById("modal-price-sale")
                .classList.remove("hidden");
            document.getElementById("modal-price-sale").classList.add("flex");
            document
                .getElementById("modal-price-normal")
                .classList.add("hidden");
            document.getElementById("modal-base-price").textContent =
                item.display_base_price;
            document.getElementById("modal-discounted-price").textContent =
                item.display_price;
            document.getElementById("modal-badge").textContent =
                `Giảm ${item.discount_percent}%`;
            document.getElementById("modal-badge").classList.remove("hidden");
        } else {
            document.getElementById("modal-price-sale").classList.add("hidden");
            document
                .getElementById("modal-price-sale")
                .classList.remove("flex");
            document
                .getElementById("modal-price-normal")
                .classList.remove("hidden");
            document.getElementById("modal-normal-price").textContent =
                item.display_price;
            document.getElementById("modal-badge").classList.add("hidden");
        }

        document.getElementById("item-modal").classList.remove("hidden");
        document.body.style.overflow = "hidden";
    } catch (err) {
        console.error("Lỗi load sản phẩm:", err);
    }
}

function closeItemModalDirect() {
    document.getElementById("item-modal").classList.add("hidden");
    document.body.style.overflow = "";
}

function updateModalTotal() {
    if (!modalItem) return;
    const total = modalItem.discounted_price * modalQty;
    const formatted = total.toLocaleString("vi-VN") + " đ";
    if (modalItem.is_on_sale) {
        document.getElementById("modal-discounted-price").textContent =
            formatted;
    } else {
        document.getElementById("modal-normal-price").textContent = formatted;
    }
}

// ==================== EVENTS ====================

document.addEventListener("DOMContentLoaded", () => {
    // Overlay đóng modal
    document
        .getElementById("modal-overlay")
        .addEventListener("click", closeItemModalDirect);

    // Nút X đóng modal
    document
        .getElementById("modal-close-btn")
        .addEventListener("click", closeItemModalDirect);

    // Nút -
    document.getElementById("modal-qty-minus").addEventListener("click", () => {
        modalQty = Math.max(1, modalQty - 1);
        document.getElementById("modal-qty").textContent = modalQty;
        updateModalTotal();
    });

    // Nút +
    document.getElementById("modal-qty-plus").addEventListener("click", () => {
        modalQty += 1;
        document.getElementById("modal-qty").textContent = modalQty;
        updateModalTotal();
    });

    // Nút thêm vào giỏ trong modal
    document
        .getElementById("modal-add-cart-btn")
        .addEventListener("click", () => {
            if (!modalItem) return;
            addToCart(modalItem.id, modalQty, "");
            closeItemModalDirect();
        });

    // ESC đóng modal
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeItemModalDirect();
    });

    // Load trạng thái yêu thích ban đầu
    if (isLoggedIn()) {
        fetch("/favorites/ids")
            .then((res) => res.json())
            .then((data) => {
                data.ids.forEach((id) => {
                    document
                        .querySelectorAll(`[data-item-id="${id}"]`)
                        .forEach((el) => {
                            const btn = el.querySelector(".btn-favorite");
                            if (btn) btn.classList.add("text-red-500");
                        });
                });
            });
    }

    // Click toàn trang — 1 listener duy nhất
    document.addEventListener("click", (e) => {
        // Nút Thêm trên card → thêm giỏ, không mở modal
        const addBtn = e.target.closest(".btn-add-cart");
        if (addBtn) {
            e.stopPropagation();
            addToCart(addBtn.dataset.id, 1, "");
            return;
        }

        // Nút tim trên card / modal
        const favBtn = e.target.closest(".btn-favorite");
        if (favBtn) {
            e.stopPropagation();
            if (!isLoggedIn()) {
                requireLogin();
                return;
            }

            const card = favBtn.closest("[data-item-id]");
            const itemId = card?.dataset.itemId;
            if (!itemId) return;

            fetch("/favorites/toggle", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    )?.content,
                },
                body: JSON.stringify({ menu_item_id: itemId }),
            })
                .then((res) => res.json())
                .then((data) => {
                    // Cập nhật TẤT CẢ nút tim của item này (cả card + modal)
                    document.querySelectorAll(".btn-favorite").forEach((btn) => {
                        const parent = btn.closest("[data-item-id]");
                        if (parent?.dataset.itemId === itemId) {
                            if (data.favorited) {
                                btn.classList.add("text-red-500");
                            } else {
                                btn.classList.remove("text-red-500");
                            }
                        }
                    });

                    showToast(
                        data.favorited
                            ? "Đã thêm vào yêu thích!"
                            : "Đã xóa khỏi yêu thích!",
                        data.favorited ? "❤️" : "🤍",
                    );
                    // Cập nhật số trên icon tim header
                    const favCountEl = document.getElementById("fav-count");
                    if (favCountEl) favCountEl.textContent = data.count;
                })
                .catch((err) => console.error(err));

            return;
        }

        // Click vào card → mở modal (bỏ qua nếu click trong modal hoặc trong trang cart)
        if (e.target.closest("#item-modal")) return;
        if (e.target.closest(".cart-item")) return;
        if (e.target.closest(".btn-cart-minus, .btn-cart-plus, .btn-cart-remove")) return;
        const card = e.target.closest("[data-item-id]");
        if (!card) return;
        openItemModal(card.dataset.itemId);
    });
});
