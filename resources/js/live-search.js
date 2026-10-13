// Ô tìm kiếm header: gõ tới đâu hiện thẻ món tới đó.
// Trang /search: cập nhật lưới ngay trên trang; trang khác: khung xổ xuống dưới header.
const form = document.getElementById("live-search-form");
const panel = document.getElementById("live-search-panel");
const backdrop = document.getElementById("live-search-backdrop");

if (form && panel) {
    const input = form.querySelector('input[name="q"]');
    const pageResults = document.getElementById("search-results");
    const target = pageResults ?? panel.firstElementChild;
    let timer, controller;

    const hide = () => [panel, backdrop].forEach((el) => el?.classList.add("hidden"));
    const show = () => [panel, backdrop].forEach((el) => el?.classList.remove("hidden"));

    input.addEventListener("input", () => {
        clearTimeout(timer);
        const q = input.value.trim();
        if (!q && !pageResults) return hide();

        timer = setTimeout(() => {
            controller?.abort(); // bỏ kết quả của lần gõ trước còn đang chờ
            controller = new AbortController();
            const url = `${form.action}?q=${encodeURIComponent(q)}`;

            fetch(url, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
                signal: controller.signal,
            })
                .then((res) => res.text())
                .then((html) => {
                    target.innerHTML = html;
                    if (pageResults) history.replaceState(null, "", url);
                    else show();
                })
                .catch(() => {});
        }, 250);
    });

    input.addEventListener("focus", () => {
        if (!pageResults && input.value.trim() && target.innerHTML.trim()) show();
    });
    document.addEventListener("click", (e) => {
        // Không đóng khi bấm trong khung hoặc trong modal chi tiết món
        if (!form.contains(e.target) && !panel.contains(e.target) && !e.target.closest("#item-modal")) hide();
    });
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") hide();
    });
}
