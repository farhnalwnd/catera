# [UI/UX] Penyesuaian Warna Sidebar, Tabel, dan Search Tab

## Deskripsi
Terdapat kebutuhan untuk menyesuaikan tampilan antarmuka (UI) aplikasi agar lebih sesuai dengan branding dan adaptif terhadap sistem tema (Light/Dark mode). Fokus pengerjaan ada pada komponen navigasi (Sidebar), tabel data, dan elemen pencarian (Search Tab).

## Target Komponen
- Sidebar (Navigasi Kiri / Utama)
- Tabel Data (terutama di dalam `/resources/views/pages/`)
- Search Tab / Input Pencarian

## Daftar Tugas (Tasks)

- [ ] **Ubah Warna Sidebar:**
  Implementasikan warna latar belakang (background) pada Sidebar menggunakan warna biru dengan kode hex `#6DC5EE`. Pastikan warna teks, ikon, dan elemen *hover* di dalam sidebar disesuaikan agar tetap memiliki kontras yang baik dan mudah dibaca.

- [ ] **Adaptasi Tema (Light/Dark Mode) pada Tabel dan Pencarian:**
  Perbaiki styling pada komponen Tabel dan Search Tab agar sepenuhnya patuh terhadap sistem tema aplikasi.
  - **Aturan Ketat:** Jangan pernah menggunakan *hardcoded colors* yang melawan tema (contoh: memaksa warna hitam pekat di light mode, atau putih terang di dark mode yang tidak sesuai dengan *guidelines*).
  - Gunakan utilitas class dari Tailwind CSS atau Flux UI yang mendukung pergantian tema secara dinamis (misalnya `bg-white dark:bg-zinc-900`, `text-zinc-900 dark:text-zinc-100`).
  - Lakukan pengecekan dan hapus class statis yang mencegah elemen-elemen ini beradaptasi dengan baik saat mode tema (gelap/terang) diubah.

## Catatan Eksekusi (Notes)
- **Environment:** Proyek ini berjalan di atas Docker menggunakan **Laravel Sail**.
- **Perintah CLI:** Setiap menjalankan perintah terminal yang berhubungan dengan Laravel (seperti membersihkan cache view, menjalankan migrasi, atau testing), Anda **WAJIB** menggunakan awalan `sail artisan`. Dilarang menggunakan `php artisan` secara langsung.
