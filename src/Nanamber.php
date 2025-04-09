<?php

namespace PamungkasAndono\Nanamber;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Nanamber - Generator untuk nomor sekuensial dengan format yang dapat dikonfigurasi.
 *
 * Class ini menyediakan cara untuk menghasilkan nomor sekuensial berdasarkan template,
 * yang dapat mencakup tanggal dan format padding. Ideal untuk penomoran invoice,
 * dokumen, atau item referensi lainnya.
 */
class Nanamber
{
    /**
     * Format template untuk menghasilkan nomor.
     */
    protected string $templateFormat;

    /**
     * Panjang padding untuk bagian nomor.
     */
    protected int $padLength = 4;

    /**
     * Karakter yang digunakan untuk padding.
     */
    protected string $padString = '0';

    /**
     * Jenis padding yang digunakan (STR_PAD_LEFT, STR_PAD_RIGHT, STR_PAD_BOTH).
     */
    protected int $padType = STR_PAD_LEFT;

    /**
     * Instance Carbon untuk operasi tanggal dalam template.
     */
    protected Carbon $date;

    /**
     * Membuat instance Nanamber baru dengan format template tertentu.
     *
     * Template dapat berisi placeholder {number} untuk nilai increment dan
     * placeholder format tanggal seperti {Y-m-d} yang akan diganti dengan tanggal yang sesuai.
     *
     * @param  string|\Closure  $templateFormat  String template atau closure yang mengembalikan string
     * @return static
     *
     * @throws \InvalidArgumentException Jika closure tidak mengembalikan string
     */
    public static function template(string|\Closure $templateFormat): self
    {
        $instance = new static;
        $instance->date = Carbon::now();

        if ($templateFormat instanceof \Closure) {
            $template = $templateFormat($instance->date);
            if (! is_string($template)) {
                throw new \InvalidArgumentException('Callback passed to template() must return a string.');
            }
            $instance->templateFormat = $template;
        } else {
            $instance->templateFormat = $templateFormat;
        }

        return $instance;
    }

    /**
     * Mengatur opsi padding untuk bagian nomor.
     *
     * @param  int  $length  Panjang padding yang diinginkan
     * @param  string  $padString  Karakter yang digunakan untuk padding
     * @param  int  $padType  Jenis padding (STR_PAD_LEFT, STR_PAD_RIGHT, STR_PAD_BOTH)
     * @return $this
     */
    public function pad(int $length, string $padString = '0', int $padType = STR_PAD_LEFT): self
    {
        $this->padLength = $length;
        $this->padString = $padString;
        $this->padType = $padType;

        return $this;
    }

    /**
     * Mengatur tanggal yang digunakan untuk placeholder tanggal dalam template.
     *
     * Method ini cocok untuk mengatur backdated data atau generate sesuai periode tertentu.
     *
     * @param  Carbon  $date  Instance Carbon untuk tanggal yang diinginkan
     * @return $this
     */
    public function setDate(Carbon $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Menambah nilai terakhir dari template yang ditentukan dan mengembalikan nilai baru.
     *
     * Method ini melakukan operasi database dalam transaksi, dengan locking untuk
     * memastikan keunikan dan integritas nilai.
     *
     * @param  int  $plus  Jumlah yang akan ditambahkan ke nilai terakhir
     * @return int Nilai setelah diincrement
     */
    protected function incrementValue(int $plus = 1): int
    {
        $table = config('nanamber.table', 'auto_numbers');
        $templateField = config('nanamber.field_template', 'template_format');
        $valueField = config('nanamber.field_value', 'last_value');
        $createdAtField = config('nanamber.field_created_at', 'created_at');
        $updatedAtField = config('nanamber.field_updated_at', 'updated_at');

        return DB::transaction(function () use ($table, $templateField, $valueField, $createdAtField, $updatedAtField, $plus) {
            $record = DB::table($table)
                ->where($templateField, $this->templateFormat)
                ->lockForUpdate()
                ->first();

            $now = Carbon::now();

            if (! $record) {
                DB::table($table)->insert([
                    $templateField => $this->templateFormat,
                    $valueField => 0,
                    $createdAtField => $now,
                    $updatedAtField => $now,
                ]);

                $record = DB::table($table)
                    ->where($templateField, $this->templateFormat)
                    ->lockForUpdate()
                    ->first();
            }

            $newValue = $record->{$valueField} + $plus;

            DB::table($table)
                ->where($templateField, $this->templateFormat)
                ->update([
                    $valueField => $newValue,
                    $updatedAtField => $now,
                ]);

            return $newValue;
        }, 5);
    }

    /**
     * Menerapkan format template pada nomor yang diberikan.
     *
     * Menggantikan placeholder {number} dengan nilai numerik yang diberikan,
     * dan placeholder format tanggal dengan nilai tanggal yang diformatkan.
     *
     * @param  string  $number  Nomor yang akan diterapkan ke template
     * @return string Nomor yang diformat sesuai template
     */
    protected function applyTemplate(string $number): string
    {
        $template = $this->templateFormat;

        $template = str_replace('{number}', $number, $template);

        preg_match_all('/\{(?!number\})([^}]+)\}/', $template, $matches);

        foreach ($matches[1] as $format) {
            try {
                $formatted = $this->date->format($format);
                $template = str_replace('{'.$format.'}', $formatted, $template);
            } catch (\Exception $e) {
                continue;
            }
        }

        return $template;
    }

    /**
     * Menghasilkan nomor sekuensial berikutnya berdasarkan template yang dikonfigurasi.
     *
     * @return string Nomor sekuensial yang diformat
     */
    public function generate(): string
    {
        $nextValue = $this->incrementValue();
        $number = str_pad($nextValue, $this->padLength, $this->padString, $this->padType);

        return $this->applyTemplate($number);
    }

    /**
     * Menghasilkan batch nomor sekuensial berdasarkan template yang dikonfigurasi.
     *
     * @param  int  $howMuch  Jumlah nomor yang akan dihasilkan
     * @return \Illuminate\Support\Collection Koleksi nomor sekuensial yang diformat
     */
    public function generateBatch(int $howMuch): Collection
    {
        $end = $this->incrementValue($howMuch);
        $start = $end - $howMuch + 1;

        $results = collect([]);

        for ($i = $start; $i <= $end; $i++) {
            $number = str_pad($i, $this->padLength, $this->padString, $this->padType);
            $results->push($this->applyTemplate($number));
        }

        return $results;
    }

    /**
     * Mengatur ulang nilai terakhir untuk template yang ditentukan.
     *
     * @param  int  $newValue  Nilai baru untuk template ini
     * @return int Nilai yang diatur
     */
    public function resetValue(int $newValue = 0): int
    {
        $table = config('nanamber.table', 'auto_numbers');
        $templateField = config('nanamber.field_template', 'template_format');
        $valueField = config('nanamber.field_value', 'last_value');
        $createdAtField = config('nanamber.field_created_at', 'created_at');
        $updatedAtField = config('nanamber.field_updated_at', 'updated_at');

        return DB::transaction(function () use ($table, $templateField, $valueField, $createdAtField, $updatedAtField, $newValue) {
            $record = DB::table($table)
                ->where($templateField, $this->templateFormat)
                ->lockForUpdate()
                ->first();

            $now = Carbon::now();

            if (! $record) {
                DB::table($table)->insert([
                    $templateField => $this->templateFormat,
                    $valueField => $newValue,
                    $createdAtField => $now,
                    $updatedAtField => $now,
                ]);
            } else {
                DB::table($table)
                    ->where($templateField, $this->templateFormat)
                    ->update([
                        $valueField => $newValue,
                        $updatedAtField => $now,
                    ]);
            }

            return $newValue;
        }, 5);
    }
}
