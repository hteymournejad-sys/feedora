<?php
namespace App\Support\Text;

class Normalizer {
  public static function fa(string $s): string {
    $s = trim($s);
    $map = [
      'ي' => 'ی', 'ك' => 'ک', '‌' => ' ', 'ـ' => '',
      '‏' => '', 'ۛ' => '', 'ة' => 'ه'
    ];
    return strtr($s, $map);
  }
}
