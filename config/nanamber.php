<?php

/**
 * Konfigurasi untuk package Nanamber.
 *
 * File konfigurasi ini berisi pengaturan untuk package Nanamber, yang menyediakan
 * generator nomor sekuensial dengan format yang dapat dikustomisasi.
 * Termasuk konfigurasi tabel database dan nama kolom yang digunakan untuk menyimpan
 * dan mengelola template format dan nilai terakhir.
 */

return [
    /**
     * Nama tabel database untuk menyimpan format template dan nilai sekuensial.
     */
    'table' => 'auto_numbers',

    /**
     * Nama kolom untuk menyimpan format template.
     * Kolom ini menyimpan string template yang digunakan untuk generate nomor.
     */
    'field_template' => 'template_format',

    /**
     * Nama kolom untuk menyimpan nilai sekuensial terakhir.
     * Kolom ini menyimpan nilai numerik terakhir untuk setiap template.
     */
    'field_value' => 'last_value',

    /**
     * Nama kolom untuk menyimpan timestamp pembuatan.
     * Mencatat kapan format template pertama kali dibuat.
     */
    'field_created_at' => 'created_at',

    /**
     * Nama kolom untuk menyimpan timestamp pembaruan terakhir.
     * Diperbarui setiap kali nilai sekuensial berubah.
     */
    'field_updated_at' => 'updated_at',
];
