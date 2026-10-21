const formLogin = document.getElementById("form-login");
const pesan = document.getElementById("pesan");

formLogin.addEventListener("submit", function (event) {
    event.preventDefault();

    // Tahap ini hanya memeriksa isian formulir.
    // Login belum terhubung ke PHP dan MySQL.
    const namaPengguna = document.getElementById("nama-pengguna").value.trim();
    const kataSandi = document.getElementById("kata-sandi").value;

    if (namaPengguna === "" || kataSandi === "") {
        pesan.textContent = "Nama pengguna dan kata sandi wajib diisi.";
        return;
    }

    pesan.textContent = "Tampilan login siap. Pemeriksaan akun akan dibuat saat PHP dan MySQL diterapkan.";
});
