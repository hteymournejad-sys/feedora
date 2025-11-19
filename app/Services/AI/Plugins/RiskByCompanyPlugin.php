<?php
// app/Services/AI/Plugins/RiskByCompanyPlugin.php
namespace App\Services\AI\Plugins;

use App\Services\AI\Plugins\Contracts\RetrieverPlugin;
use App\Domain\Scoring\Bucketizer;
use App\Repositories\AssessmentRepository;
use App\Support\Text\Summarizer;

class RiskByCompanyPlugin implements RetrieverPlugin {
  public function __construct(
    private AssessmentRepository $repo,
  ) {}

  public function supports(string $question, array $ctx): bool {
    return ($ctx['intent'] ?? null) === 'risk.byCompany'
        && !empty($ctx['company_ids']);
  }

  public function handle(string $q, array $ctx): array {
    $companyId    = $ctx['company_ids'][0] ?? null;
    $companyAlias = $ctx['company_aliases'][$companyId] ?? '—';
    if (!$companyId) {
      return $this->empty("ریسک‌های شرکت «{$companyAlias}»", "شرکت معتبری تشخیص داده نشد.");
    }

    $groupId = $this->repo->latestCompletedGroupId($companyId);
    if (!$groupId) {
      return $this->empty("ریسک‌های شرکت «{$companyAlias}»", "برای این شرکت ارزیابی کامل‌شده یافت نشد.");
    }

    $rows = $this->repo->answersWithQuestions($groupId);
    $buckets = ['high'=>[], 'medium'=>[], 'low'=>[]];

    foreach ($rows as $r) {
      $bucket = Bucketizer::riskBucket($r->domain, (int)$r->weight, (int)$r->score);
      if (!$bucket) continue;

      // متن را از risks بگیر؛ اگر نبود از description+guide خلاصه کن
      $text = $r->risks ?: Summarizer::short(trim(($r->description ?? '') . ' ' . ($r->guide ?? '')));
      if ($text === '') continue;

      $buckets[$bucket][] = [
        'domain' => (string)$r->domain,
        'item'   => $text,
        'score'  => (int)$r->score,
        'weight' => (int)$r->weight,
      ];
    }

    // مرتب‌سازی: High با وزن بالاتر اول؛ سپس Medium/Low مشابه
    foreach (['high','medium','low'] as $k) {
      usort($buckets[$k], fn($a,$b)=>($b['weight']<=>$a['weight']) ?: ($a['score']<=>$b['score']));
      $buckets[$k] = array_slice($buckets[$k], 0, 20); // limit
    }

    $date = $this->repo->latestCompletedDate($companyId);
    $summary = $this->buildSummary($buckets);

    return [
      'title'   => "ریسک‌های شرکت «{$companyAlias}»" . ($date ? " (آخرین ارزیابی: {$date})" : ''),
      'summary' => $summary,
      'tables'  => [
        [
          'title'   => 'High',
          'columns' => ['domain','item','score','weight'],
          'rows'    => array_map('array_values', $buckets['high'])
        ],
        [
          'title'   => 'Medium',
          'columns' => ['domain','item','score','weight'],
          'rows'    => array_map('array_values', $buckets['medium'])
        ],
        [
          'title'   => 'Low',
          'columns' => ['domain','item','score','weight'],
          'rows'    => array_map('array_values', $buckets['low'])
        ],
      ],
      'cards' => [
        ['title'=>'High risks','value'=>count($buckets['high'])],
        ['title'=>'Medium risks','value'=>count($buckets['medium'])],
        ['title'=>'Low risks','value'=>count($buckets['low'])],
      ],
      'meta' => [
        'companies' => [$companyAlias],
        'last_assessment_dates' => [$companyAlias => $date],
        'limits' => ['per_section'=>20]
      ],
    ];
  }

  private function buildSummary(array $b): string {
    $hi = count($b['high']); $md = count($b['medium']); $lo = count($b['low']);
    $line = "ریسک‌ها — High: {$hi} | Medium: {$md} | Low: {$lo}.";
    if ($hi > 0) {
      return $line . " تمرکز فوری بر ریسک‌های High با وزن بالا در حوزه‌های امنیت/زیرساخت/سامانه‌ها توصیه می‌شود.";
    }
    if ($md > 0) {
      return $line . " ریسک بحرانی مشاهده نشد؛ کاهش موارد Medium با اقدام‌های ۳۰–۶۰ روزه در اولویت باشد.";
    }
    return $line . " وضعیت پایدار است؛ پایش دوره‌ای و جلوگیری از بازگشت ریسک‌ها توصیه می‌شود.";
  }

  private function empty(string $title, string $msg): array {
    return ['title'=>$title,'summary'=>$msg,'tables'=>[],'cards'=>[],'meta'=>[]];
  }
}
