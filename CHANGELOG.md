# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [1.1.0] - 2025-04-10
### Changed
- Mengubah nama paket dari `nanamber` menjadi `laravel-nanamber` agar lebih sesuai dengan konvensi penamaan paket Laravel.
- Memperbarui namespace, service provider, dan facade untuk menyesuaikan dengan nama paket baru.

### Removed
- Menghapus command yang tidak digunakan lagi.

---

## [1.0.0] - 2025-04-09
### Added
- Fitur original untuk generator auto-number.
- Mendukung format template seperti `INV/{number}` baik dengan string ataupun callback function.
- Mendukung format template menggunakan date format seperti `{INV/{Y}/{M}/{number}}`
- Native fitur reset `{number}` berdasarkan pola template. Paling umum menggunakan waktu yang dibuat menggunakan callback function.
- Menambah unit testing dengan Pest
