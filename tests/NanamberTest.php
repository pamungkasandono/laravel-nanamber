<?php

use Illuminate\Support\Carbon;
use PamungkasAndono\Laravel\Nanamber;

test('struktur tabel memiliki kolom inti dan kolom timestamp', function () {
    $columns = Schema::getColumnListing(config('nanamber.table'));

    expect($columns)->toContain(config('nanamber.field_template'));
    expect($columns)->toContain(config('nanamber.field_value'));

    if (config('nanamber.field_created_at')) {
        expect($columns)->toContain(config('nanamber.field_created_at'));
    }

    if (config('nanamber.field_updated_at')) {
        expect($columns)->toContain(config('nanamber.field_updated_at'));
    }
});

test('generate nomor pertama ketika belum ada data', function () {
    $serial = Nanamber::template('{Y}{m}{number}')->generate();
    expect($serial)->toMatch('/^\d{6}0001$/');
});

test('generate nomor kedua, seharusnya bertambah', function () {
    Nanamber::template('{Y}{m}{number}')->generate();
    $serial = Nanamber::template('{Y}{m}{number}')->generate();
    expect($serial)->toMatch('/^\d{6}0002$/');
});

test('generate nomor secara batch', function () {
    $serials = Nanamber::template('{Y}{m}{number}')->generateBatch(3);
    expect($serials)->toHaveCount(3);
    expect($serials[0])->toEndWith('0001');
    expect($serials[2])->toEndWith('0003');
});

test('reset urutan value ke angka tertentu', function () {
    $maker = Nanamber::template('{Y}{m}{number}');
    $maker->resetValue(99);
    $serial = $maker->generate();
    expect($serial)->toEndWith('0100');
});

test('padding custom bekerja dengan benar', function () {
    $serial = Nanamber::template('{number}')
        ->pad(3, '*', STR_PAD_BOTH)
        ->generate();

    expect($serial)->toBe('*1*');
});

test('template berbasis closure berfungsi', function () {
    $serial = Nanamber::template(function ($date) {
        return $date->format('ym').'-{number}';
    })->generate();

    expect($serial)->toMatch('/^\d{4}-0001$/');
});

test('template closure yang return-nya nilai selain string akan menyebabkan exception', function () {
    $this->expectException(InvalidArgumentException::class);

    Nanamber::template(fn () => 1234)->generate();
});

test('set tanggal custom dan hasil generate sesuai dengan input tanggal', function () {
    $date = Carbon::create(2022, 12, 1);
    $serial = Nanamber::template('{Y}{m}{number}')
        ->setDate($date)
        ->generate();

    expect($serial)->toStartWith('202212');
});
