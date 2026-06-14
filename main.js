document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-faq]").forEach((button) => {
        button.addEventListener("click", () => {
            const item = button.closest(".faq-item");
            if (!item) return;

            const list = item.parentElement;
            list.querySelectorAll(".faq-item.open").forEach((openItem) => {
                if (openItem !== item) openItem.classList.remove("open");
            });

            item.classList.toggle("open");
        });
    });

    document.querySelectorAll("[data-confirm]").forEach((element) => {
        element.addEventListener("click", (event) => {
            const message = element.getAttribute("data-confirm") || "Are you sure?";
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
});
