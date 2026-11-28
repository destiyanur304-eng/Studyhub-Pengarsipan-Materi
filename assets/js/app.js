document.addEventListener("DOMContentLoaded", function () {

    /* ---------- Konfirmasi logout ---------- */
    document.querySelectorAll('form[action*="logout.php"]').forEach(function (form) {
        form.addEventListener("submit", function (event) {
            if (!confirm("Apakah kamu yakin ingin keluar dari StudyHub?")) {
                event.preventDefault();
            }
        });
    });

    /* ---------- Baris atas tabel: kolom cari ---------- */
    let toolbar = null;

    document.querySelectorAll("[data-search]").forEach(function (card) {

        const wrapper = card.querySelector(".table-wrapper");
        if (!wrapper) {
            return;
        }

        toolbar = document.createElement("div");
        toolbar.className = "table-toolbar";

        const input = document.createElement("input");
        input.type = "search";
        input.className = "table-search";
        input.placeholder = card.dataset.search;

        toolbar.appendChild(input);
        wrapper.parentNode.insertBefore(toolbar, wrapper);

        input.addEventListener("input", function () {
            const kata = input.value.toLowerCase().trim();

            card.querySelectorAll("tbody tr").forEach(function (row) {
                // Lewati baris pesan "belum ada data"
                if (row.querySelector("td[colspan]")) {
                    return;
                }
                row.style.display = row.textContent.toLowerCase().includes(kata) ? "" : "none";
            });
        });
    });

    /* ---------- Pop-up (modal) untuk form tambah/edit ---------- */
    document.querySelectorAll("[data-modal]").forEach(function (card) {

        const title = card.dataset.modal;
        const sedangEdit = new URLSearchParams(window.location.search).has("edit");

        const overlay = document.createElement("div");
        overlay.className = "modal-overlay";

        const closeBtn = document.createElement("button");
        closeBtn.type = "button";
        closeBtn.className = "modal-close";
        closeBtn.setAttribute("aria-label", "Tutup");
        closeBtn.innerHTML = "&times;";

        card.classList.add("modal-box");
        card.prepend(closeBtn);
        card.parentNode.insertBefore(overlay, card);
        overlay.appendChild(card);

        function buka() {
            overlay.classList.add("show");
            document.body.classList.add("modal-open");
        }

        function tutup() {
            // Mode edit: kembali ke halaman tanpa ?edit
            if (sedangEdit) {
                window.location.href = window.location.pathname;
                return;
            }
            overlay.classList.remove("show");
            document.body.classList.remove("modal-open");
        }

        // Tombol "+ Tambah ..." diletakkan di atas daftar (samping kolom cari)
        const openBtn = document.createElement("button");
        openBtn.type = "button";
        openBtn.className = "btn-open-modal";
        openBtn.textContent = "+ " + title;
        openBtn.addEventListener("click", buka);

        if (toolbar) {
            toolbar.appendChild(openBtn);
        } else {
            const topbar = document.querySelector(".topbar");
            if (topbar) {
                topbar.appendChild(openBtn);
            }
        }

        closeBtn.addEventListener("click", tutup);

        overlay.addEventListener("click", function (event) {
            if (event.target === overlay) {
                tutup();
            }
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape" && overlay.classList.contains("show")) {
                tutup();
            }
        });

        // Mode edit: pop-up terbuka otomatis
        if (sedangEdit) {
            buka();
        }
    });

    /* ---------- Validasi sisi klien ---------- */
    document.querySelectorAll("form").forEach(function (form) {
        form.addEventListener("submit", function (event) {

            const errors = [];

            const nim = form.querySelector('[name="nim"]');
            if (nim && !/^[0-9]+$/.test(nim.value.trim())) {
                errors.push("NIM hanya boleh berisi angka.");
            }

            const password = form.querySelector('[name="password"]');
            if (password && password.value !== "" && password.value.length < 8) {
                errors.push("Password minimal 8 karakter.");
            }

            const link = form.querySelector('[name="link_drive"]');
            if (link) {
                try {
                    const url = new URL(link.value.trim());
                    if (url.protocol !== "http:" && url.protocol !== "https:") {
                        throw new Error();
                    }
                } catch (e) {
                    errors.push("Tautan Google Drive harus berupa URL yang valid (http/https).");
                }
            }

            const tahun = form.querySelector('[name="tahun_arsip"]');
            if (tahun && tahun.tagName === "INPUT" && tahun.type !== "hidden") {
                const t = parseInt(tahun.value, 10);
                if (isNaN(t) || t < 2000 || t > 2155) {
                    errors.push("Tahun arsip tidak valid.");
                }
            }

            if (errors.length > 0) {
                event.preventDefault();
                alert(errors.join("\n"));
            }
        });
    });

});