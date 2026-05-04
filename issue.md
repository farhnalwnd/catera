# [UI/UX] Implementasi Tema Medis & Modernisasi Dashboard

## Target
- Tema Website (Global)
- UI Dashboard (`resources/views/pages/dashboard/index.blade.php`)

## Deskripsi Tugas
Issue ini bertujuan untuk mengubah tema aplikasi agar lebih sesuai dengan konteks medis (Healthcare) dan memperbarui tampilan Dashboard agar terlihat lebih modern, bersih, dan profesional. 

Berdasarkan hasil analisis dari *UI/UX Pro Max*, berikut adalah Design System yang harus diterapkan:

### Design System: Medical Clean (Accessible & Ethical)
- **Konsep:** Minimalis, fokus pada kontras tinggi, whitespace luas, dan *accessibility* (WCAG compliant).
- **Palet Warna Utama:**
  - **Primary:** `#0891B2` (Cyan/Teal medis)
  - **Secondary:** `#22D3EE`
  - **CTA/Success:** `#22C55E` (Health Green)
  - **Background:** `#F0FDFA` (Light Cyan untuk latar belakang halaman)
  - **Text:** `#134E4A` (Dark Cyan/Slate untuk teks utama)
- **Tipografi:**
  - **Heading:** `Figtree` (Font modern & bersih)
  - **Body:** `Noto Sans` (Tingkat keterbacaan tinggi)
- **Anti-patterns (JANGAN digunakan):**
  - Warna neon terang mencolok
  - Animasi yang berlebihan
  - Gradien ungu/pink

### Daftar Pekerjaan (Tasks)
1. **Update CSS / Tema Global:**
   - Sesuaikan konfigurasi warna variabel CSS pada `resources/css/app.css` (Tailwind v4) dengan palet medis di atas.
   - Pastikan font utama pada stylesheet sudah mengakomodasi `Figtree` untuk heading dan `Noto Sans` untuk body text.
2. **Modernisasi Dashboard:**
   - Terapkan gaya kartu (card) yang lebih modern dengan *padding* dan *whitespace* yang cukup.
   - Perbarui warna grafik ApexCharts pada dashboard agar selaras dengan palet medis (gunakan turunan warna biru/teal).
   - Pastikan elemen interaktif memiliki umpan balik visual (hover effect) yang halus (transisi 150-300ms).
3. **Penyempurnaan UI/UX:**
   - Pastikan warna kontras aman di *Light Mode* dan *Dark Mode* (jangan memaksakan warna terang di mode gelap jika tidak nyaman dibaca).
   - Pastikan semua elemen *clickable* memiliki `cursor-pointer`.

## Catatan Penting
- Proyek ini berjalan di atas Docker. **WAJIB** menggunakan perintah `sail artisan` (BUKAN `php artisan`).
- Lakukan pengecekan tampilan menggunakan `sail artisan view:clear` jika terjadi caching.
- Sebelum commit, jalankan `./vendor/bin/pint --format agent` untuk standarisasi kode sesuai konvensi proyek.
- Fokus hanya pada perubahan User Interface (CSS, class Tailwind, warna grafik). Jangan mengubah logika bisnis, *query database*, atau relasi komponen.

## Kriteria Selesai (Definition of Done)
- Warna tema secara keseluruhan berubah menjadi nuansa medis yang bersih dan profesional.
- Dashboard terlihat jauh lebih modern dengan tata letak yang rapi.
- *Hover states* berfungsi baik tanpa merusak struktur layout.
- Perubahan kompatibel dengan skema mode terang dan gelap.
