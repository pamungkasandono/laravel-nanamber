# Nanamber Documentation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pamungkasandono/nanamber.svg?style=flat-square)](https://packagist.org/packages/pamungkasandono/nanamber)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/pamungkasandono/nanamber/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/pamungkasandono/nanamber/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/pamungkasandono/nanamber/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/pamungkasandono/nanamber/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/pamungkasandono/nanamber.svg?style=flat-square)](https://packagist.org/packages/pamungkasandono/nanamber)

Nanamber adalah library Laravel yang menyediakan cara mudah untuk menghasilkan nomor sekuensial dengan format yang dapat dikonfigurasi. Library ini menawarkan solusi untuk kasus-kasus seperti pembuatan nomor invoice, nomor referensi transaksi, kode tracking, dan lainnya yang membutuhkan nomor berurutan dengan format yang spesifik.

## Fitur

-   Menghasilkan nomor sekuensial dengan format yang dapat dikonfigurasi
-   Dukungan placeholder tanggal dalam format
-   Padding yang dapat disesuaikan
-   Kemampuan untuk menghasilkan nomor secara batch
-   Nilai counter yang disimpan dalam database
-   Reset nilai counter

## Instalasi

### Persyaratan

-   PHP 8.0+
-   Laravel 8.0+

### Cara Instalasi

Anda dapat menginstal library ini via Composer:

```bash
composer require pamungkasandono/nanamber
```

### Publikasi Config

Publikasikan file konfigurasi dengan menjalankan:

```bash
php artisan vendor:publish --provider="PamungkasAndono\Nanamber\NanamberServiceProvider" --tag="config"
```

### Migrasi Database

Nanamber membutuhkan tabel database untuk menyimpan counter. Jalankan migrasi untuk membuat tabel tersebut:

```bash
php artisan migrate --path=vendor/pamungkasandono/nanamber/database/migrations
```

Migrasi akan membuat tabel `auto_numbers` dengan struktur berikut:

```php
Schema::create('auto_numbers', function (Blueprint $table) {
    $table->id();
    $table->string('template_format')->unique();
    $table->integer('last_value')->default(0);
    $table->timestamps();
});
```

## Penggunaan Dasar

### Menghasilkan Nomor Sederhana

Contoh paling dasar menggunakan Nanamber:

```php
use PamungkasAndono\Nanamber\Nanamber;

// Menghasilkan nomor dengan format: 2025040001
$number = Nanamber::template('{Y}{m}{number}')->generate();
```

### Format Template

Template dapat berisi:

-   `{number}` - Akan diganti dengan nomor sekuensial
-   Format tanggal dalam kurung kurawal `{Y}`, `{m}`, `{d}`, dll. - Akan diganti dengan tanggal saat ini sesuai format
-   Teks statis - Akan tetap ada dalam output

Contoh:

```php
// Format: INV/2025/04/0001
$invoice = Nanamber::template('INV/{Y}/{m}/{number}')->generate();

// Format: DO-202504-0001
$deliveryOrder = Nanamber::template('DO-{Y}{m}-{number}')->generate();
```

### Mengatur Padding

Secara default, bagian nomor diatur dengan padding 4 digit dengan karakter '0'. Anda dapat mengubahnya:

```php
// Format: INV-2025-001 (Padding 3 digit)
$invoice = Nanamber::template('INV-{Y}-{number}')
    ->pad(3)
    ->generate();

// Format: INV-2025-1*** (Padding karakter * dengan posisi right)
$invoice = Nanamber::template('INV-{Y}-{number}')
    ->pad(4, '*', STR_PAD_RIGHT)
    ->generate();
```

## Penggunaan Lanjutan

### Menggunakan Closure untuk Template yang Dinamis

Anda dapat menggunakan Closure untuk membuat template yang lebih dinamis:

```php
use Illuminate\Support\Str;

$buyer_id = '129';
$invoice = Nanamber::template(function ($date) use ($buyer_id) {
    return 'INV/' . $date->format('Y') . '/' . Str::padLeft($buyer_id, 4, '0') . '/{number}';
})->generate();

// Hasilnya: INV/2025/0129/0001
```

### Mengatur Tanggal Khusus

Secara default, Nanamber menggunakan tanggal saat ini. Anda dapat mengatur tanggal khusus:

```php
use Illuminate\Support\Carbon;

// Menggunakan tanggal spesifik untuk menghasilkan nomor
$date = Carbon::create(2023, 12, 25);
$invoice = Nanamber::template('INV/{Y}/{m}/{d}/{number}')
    ->setDate($date)
    ->generate();

// Hasilnya: INV/2023/12/25/0001
```

### Menghasilkan Nomor Secara Batch

Untuk keperluan performa, Anda dapat menghasilkan beberapa nomor sekaligus:

```php
// Menghasilkan 5 nomor invoice sekaligus
$invoices = Nanamber::template('INV/{Y}/{m}/{number}')
    ->generateBatch(5);

// $invoices adalah instance Collection yang berisi 5 nomor
// ['INV/2025/04/0001', 'INV/2025/04/0002', ..., 'INV/2025/04/0005']
```

### Reset Nilai Counter

Untuk kasus tertentu, Anda mungkin perlu mengatur ulang nilai counter:

```php
// Reset counter ke nilai 0
Nanamber::template('INV/{Y}/{number}')->resetValue();

// Reset counter ke nilai tertentu
Nanamber::template('INV/{Y}/{number}')->resetValue(999);
// Nomor selanjutnya yang dihasilkan: INV/2025/1000
```

## Kasus Penggunaan

### Nomor Invoice dengan Reset Bulanan

Buat nomor invoice yang direset setiap bulan:

```php
// Di controller Anda:
public function createInvoice()
{
    $currentMonth = now()->format('m');
    $currentYear = now()->format('Y');

    // Cek apakah sudah bulan baru
    if ($this->isNewMonth()) {
        // Reset counter untuk template bulan ini
        Nanamber::template("INV/{$currentYear}/{$currentMonth}/{number}")->resetValue(0);
    }

    // Generate nomor invoice
    $invoiceNumber = Nanamber::template("INV/{$currentYear}/{$currentMonth}/{number}")->generate();

    // Buat invoice dengan nomor yang dihasilkan
    $invoice = Invoice::create([
        'invoice_number' => $invoiceNumber,
        // ...data lainnya
    ]);

    return $invoice;
}

private function isNewMonth()
{
    // Implementasi logika untuk mendeteksi bulan baru
    // Contoh: cek apakah ada invoice di bulan ini
    return Invoice::whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count() === 0;
}
```

### Nomor Referensi Departemen

Buat nomor referensi yang memiliki awalan sesuai departemen:

```php
public function createReference($departmentCode)
{
    $departmentPrefixes = [
        'hr' => 'HR',
        'fin' => 'FIN',
        'ops' => 'OPS',
        'mkt' => 'MKT',
    ];

    $prefix = $departmentPrefixes[$departmentCode] ?? 'REF';

    $referenceNumber = Nanamber::template("{$prefix}-{Y}{m}-{number}")
        ->pad(5)
        ->generate();

    return $referenceNumber;

    // Contoh hasil:
    // HR-202504-00001
    // FIN-202504-00001
    // OPS-202504-00001
}
```

### Nomor Faktur dengan Cabang

Buat nomor faktur yang mencakup kode cabang:

```php
public function createInvoiceNumber($branchId)
{
    $branches = [
        1 => 'JKT',
        2 => 'BDG',
        3 => 'SBY',
        4 => 'MDN',
    ];

    $branchCode = $branches[$branchId] ?? 'UNK';
    $year = now()->format('Y');
    $month = now()->format('m');

    // Template yang berbeda untuk setiap cabang
    $invoiceNumber = Nanamber::template("{$branchCode}/{$year}/{$month}/{number}")
        ->generate();

    return $invoiceNumber;

    // Contoh hasil:
    // JKT/2025/04/0001
    // BDG/2025/04/0001
    // SBY/2025/04/0001
}
```

## Konfigurasi

Anda dapat mengubah konfigurasi default Nanamber di file `config/nanamber.php`:

```php
return [
    // Nama tabel database untuk menyimpan format template dan nilai sekuensial
    'table' => 'auto_numbers',

    // Nama kolom untuk menyimpan format template
    'field_template' => 'template_format',

    // Nama kolom untuk menyimpan nilai sekuensial terakhir
    'field_value' => 'last_value',

    // Nama kolom untuk menyimpan timestamp pembuatan
    'field_created_at' => 'created_at',

    // Nama kolom untuk menyimpan timestamp pembaruan terakhir
    'field_updated_at' => 'updated_at',
];
```

## Pertimbangan Performa

-   Nanamber menggunakan locking database untuk mencegah race condition saat menghasilkan nomor berurutan.
-   Untuk kebutuhan high-volume, gunakan `generateBatch()` untuk mengurangi jumlah transaksi database.

## Kontribusi

Kontribusi sangat diterima. Silakan membuat issue atau pull request di [GitHub repository](https://github.com/pamungkasandono/nanamber).

## Lisensi

Library ini dirilis di bawah lisensi MIT.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

-   [Pamungkas Andono](https://github.com/pamungkasandono)
-   [Risna Berti](https://github.com/RisnaBerti)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
