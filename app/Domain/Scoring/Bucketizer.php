<?php
namespace App\Domain\Scoring;

class Bucketizer {
  /** domain (fa), weight:int, score:int -> 'high'|'medium'|'low'|null */
  public static function riskBucket(string $domain, int $weight, int $score): ?string {
    if (!in_array($score,[10,20,30,40,50,70,80,90,100])) return null;

    $d = trim($domain);
    // فقط برای امتیازهای 10/20/30 ریسک طبقه‌بندی می‌شود
    if (in_array($score,[10,20,30])) {
      if (in_array($d, ['امنیت اطلاعات و مدیریت ریسک'])) {
        if ($weight <= 5) return 'low';
        if ($weight <= 7) return 'medium';
        return 'high';
      }
      if (in_array($d, ['زیرساخت فناوری','سامانه‌های کاربردی'])) {
        if ($weight <= 5) return 'low';
        if ($weight <= 8) return 'medium';
        return 'high';
      }
      if (in_array($d, ['تحول دیجیتال','هوشمندسازی','خدمات پشتیبانی','حاکمیت فناوری اطلاعات'])) {
        if ($weight <= 7) return 'low';
        return 'medium';
      }
    }
    return null;
  }

  public static function isImprovement(int $score): bool {
    return in_array($score,[40,50]);
  }
  public static function isStrength(int $score): bool {
    return in_array($score,[70,80,90,100]);
  }
}
