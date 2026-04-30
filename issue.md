# Masalah Pencarian Case-Sensitive di Halaman Authorized

Dokumen ini berisi penjelasan dan instruksi detail bagi *junior developer* atau *AI model* untuk memperbaiki masalah pencarian yang bersifat *case-sensitive* (membedakan huruf besar dan kecil) pada modul Authorized.

## Latar Belakang Masalah (Root Cause)
Pencarian saat ini pada tabel `authorizeds` (dan relasinya di tabel `users`) menggunakan klausa atau operator `like`. 
Karena sistem database yang digunakan pada proyek ini adalah **PostgreSQL** (terlihat dari riwayat penggunaan fungsi `to_tsvector` sebelumnya), operator `like` di PostgreSQL secara *default* beroperasi secara **case-sensitive** (membedakan kapitalisasi). 

Sebagai contoh, jika mencari dengan *keyword* `Budi`, sistem akan menggunakan query:
`WHERE first_name LIKE 'Budi%'`
Data dengan nama `budi` tidak akan ditemukan karena huruf 'b' kecil tidak sama dengan 'B' besar menurut standar `LIKE` di PostgreSQL. Pada sistem database lain seperti MySQL, `LIKE` secara otomatis bersifat *case-insensitive*, tetapi di PostgreSQL kita harus menggunakan operator khusus.

## Tujuan (Goals)
1. Menjadikan pencarian tidak peduli terhadap huruf besar/kecil (*case-insensitive*).
2. Menerapkan *best practice* Laravel untuk PostgreSQL tanpa merusak logika yang ada.

## Best Practice / Langkah Penyelesaian
*Best practice* untuk melakukan pencarian *case-insensitive* di PostgreSQL menggunakan Eloquent Laravel adalah mengganti operator `'like'` menjadi `'ilike'` (Insensitive LIKE). 

### Instruksi Implementasi

**1. Halaman Authorized (`resources/views/pages/authorized/index.blade.php`)**
Buka file tersebut dan cari bagian *query* pencarian (sekitar baris 60-70). Ubah semua `'like'` menjadi `'ilike'`:

```php
->when($this->search, function ($query) {
    $query->where(function ($q) {
        $q->where('uuid', 'ilike', $this->search . '%')
          ->orWhere('group', 'ilike', $this->search . '%')
          ->orWhereHas('user', function ($userQuery) {
              $userQuery->where('first_name', 'ilike', $this->search . '%')
                        ->orWhere('last_name', 'ilike', $this->search . '%')
                        ->orWhere('nik', 'ilike', $this->search . '%');
          });
    });
})
```

**2. Halaman Registered (`resources/views/pages/registered/index.blade.php`)**
Pastikan melakukan perubahan yang sama (mengganti `'like'` menjadi `'ilike'`) pada *query* pencarian di modul Registered agar konsisten:

Untuk pencarian utama (sekitar baris 61-70):
```php
->when($this->search, function ($query) {
    $query->whereHas('authorized.user', function ($q) {
        $q->where('first_name', 'ilike', $this->search . "%")
          ->orWhere('last_name', 'ilike', $this->search . "%")
          ->orWhere('nik', 'ilike', $this->search . "%");
    })->orWhereHas('authorized', function ($q) {
        $q->where('uuid', 'ilike', $this->search . "%");
    });
})
```

Untuk pencarian pada modal Add (sekitar baris 75-85) jika ada yang masih menggunakan `like`:
```php
->when($this->addAuthorizedUuidSearch, function ($query) {
    $query->where(function ($q) {
        $q->whereFullText(['uuid', 'group'], $this->addAuthorizedUuidSearch.' * ', ['mode' => 'boolean'])
          ->orWhereHas('user', function ($userQuery) {
              $userQuery->where('first_name', 'ilike', '%' . $this->addAuthorizedUuidSearch . '%')
                        ->orWhere('last_name', 'ilike', '%' . $this->addAuthorizedUuidSearch . '%');
          });
    });
})
```

*(Catatan: Jangan mengubah fungsi `whereFullText` karena itu ditangani secara otomatis oleh *engine* database).*

## Acceptance Criteria
- [ ] Pencarian di modul Authorized dapat menemukan data meskipun menggunakan variasi huruf besar atau kecil (contoh: "budi", "BUDI", "Budi" akan memunculkan hasil yang sama).
- [ ] Implementasi menggunakan standar *best practice* (`ilike` operator untuk PostgreSQL) agar efisien dan mudah dipelihara.
- [ ] Error atau isu pencarian tidak berfungsi akibat perbedaan kapitalisasi sudah tidak muncul lagi.
