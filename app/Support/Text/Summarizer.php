<?php
// app/Support/Text/Summarizer.php
namespace App\Support\Text;

class Summarizer {
  /** خلاصه‌ی خیلی کوتاه و بی‌خطر (بدون LLM) */
  public static function short(?string $text, int $maxWords = 36): string {
    $t = trim((string)$text);
    if ($t === '') return '';
    // تبدیل فاصله‌های غیرمعمول
    $t = preg_replace('/\s+/u', ' ', $t);
    $parts = preg_split('/\s/u', $t);
    if (count($parts) <= $maxWords) return $t;
    return implode(' ', array_slice($parts, 0, $maxWords)) . ' …';
  }
}
