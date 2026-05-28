# Issue: Penambahan Policy untuk Fitur Unauthorized & Registered

## Backstory
Saat ini, sistem otorisasi berbasis Policy sudah berjalan dengan baik untuk fitur **Authorized** (`AuthorizedPolicy`). Namun, dua fitur lain yaitu **Unauthorized** dan **Registered** belum memiliki Policy, sehingga akses ke menu tersebut belum dikontrol dan semua pengguna yang login dapat melihatnya terlepas dari hak akses mereka.

## Goals
Menambahkan Policy untuk fitur **Unauthorized** dan **Registered** sehingga hanya pengguna dengan izin yang sesuai yang dapat mengakses menu tersebut, baik di halaman maupun di sidebar.

## Referensi Format
Ikuti persis format `AuthorizedPolicy` yang sudah ada di `app/Policies/AuthorizedPolicy.php`. Setiap Policy menggunakan `$user->hasPermissionTo('...')` untuk mengecek izin. Format penamaan permission mengikuti pola:

```
catera:{namaFitur}:{aksi}
```

Contoh untuk fitur `unauthorized`:
- `catera:unauthorized:viewAny`
- `catera:unauthorized:view`
- `catera:unauthorized:create`
- `catera:unauthorized:update`
- `catera:unauthorized:delete`

## Tasks

### 1. Buat `UnauthorizedPolicy`
- Jalankan: `./vendor/bin/sail artisan make:policy UnauthorizedPolicy --model=Unauthorized`
- Isi setiap method (`viewAny`, `view`, `create`, `update`, `delete`) menggunakan `$user->hasPermissionTo('catera:unauthorized:{aksi}')`.
- Method `restore` dan `forceDelete` kembalikan `false` (sama seperti `AuthorizedPolicy`).

### 2. Buat `RegisteredPolicy`
- Jalankan: `./vendor/bin/sail artisan make:policy RegisteredPolicy --model=Registered`
- Isi setiap method menggunakan `$user->hasPermissionTo('catera:registered:{aksi}')`.
- Method `restore` dan `forceDelete` kembalikan `false`.

### 3. Daftarkan Policy di `AppServiceProvider`
- Buka `app/Providers/AppServiceProvider.php`.
- Di dalam method `boot()`, daftarkan kedua Policy menggunakan `Gate::policy()`:
  ```php
  Gate::policy(\App\Models\Unauthorized::class, \App\Policies\UnauthorizedPolicy::class);
  Gate::policy(\App\Models\Registered::class, \App\Policies\RegisteredPolicy::class);
  ```
- Pastikan `AuthorizedPolicy` juga sudah terdaftar di tempat yang sama; jika belum, tambahkan juga.

### 4. Terapkan `@can` pada Sidebar
- Buka `resources/views/layouts/app/sidebar.blade.php`.
- Pada array `$links` di bagian `@php`, tambahkan key `'can'` untuk menu **Unauthorized** dan **Registered**:
  ```php
  ['route' => 'unauthorized.index', 'icon' => 'user-minus', 'label' => 'Unauthorized', 'can' => 'viewAny', 'model' => App\Models\Unauthorized::class],
  ['route' => 'registereds.index',  'icon' => 'clock',      'label' => 'Registered',   'can' => 'viewAny', 'model' => App\Models\Registered::class],
  ```
- Pada loop `@foreach` yang merender link, ubah kondisi pengecekan `@can` agar menggunakan `$link['model']` alih-alih `App\Models\Authorized::class` secara hardcode:
  ```blade
  @if(!isset($link['can']) || auth()->user()->can($link['can'], $link['model']))
  ```

## Notes & Environment
- Gunakan selalu `./vendor/bin/sail artisan` (bukan `php artisan`).
- Setelah selesai, jalankan test suite dengan `./vendor/bin/sail artisan test --compact` untuk memastikan tidak ada regresi.
- Verifikasi secara manual: login dengan user yang **tidak punya** permission `catera:unauthorized:viewAny`, dan pastikan menu "Unauthorized" tidak muncul di sidebar.
