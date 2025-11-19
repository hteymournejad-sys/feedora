<?php
// app/Services/AI/Plugins/StrengthsByCompanyPlugin.php
namespace App\Services\AI\Plugins;

use App\Services\AI\Plugins\Contracts\RetrieverPlugin;
use App\Domain\Scoring\Bucketizer;
use App\Repositories\AssessmentRepository;
use App\Support\Text\Summarizer;

class StrengthsByCompanyPlugin implements RetrieverPlugin {
  public function __construct(
    private AssessmentRepository $repo,
  ) {}

  public function supports(string $question, array $ctx): bool {
    return ($ctx['intent'] ?? null) === 'strengths.byCompany'
        && !empty($ctx['company_ids']);
  }

  public function handle(string $q, array $ctx): array {
    $companyId    = $ctx['company_ids'][0] ?? null;
    $companyAlias = $ctx['company_aliases'][$companyId] ?? '—';
    if (!$companyId) {
      return $this->empty("نقاط قوت شرکت «{$companyAlias}»", "شرکت معتبری تشخیص داده نشد.");
    }

    $groupId = $this->repo->latestCompletedGroupId($companyId);
    if (!$groupId) {
      return $this->empty("نقاط قوت شرکت «{$companyAlias}»", "برای این شرکت ارزیابی کامل‌شده یافت نشد.");
    }

    $rows = $this->repo->answersWithQuestions($groupId);
    $list = [];

    foreach ($rows as $r) {
      if (!Bucketizer::isStrength((int)$r->score)) continue;

      $text = $r->strengths ?: Summarizer::short(trim(($r->description ?? '') . ' ' . ($r->guide ?? '')));
      if ($text === '') continue;

      $list[] = [
        'domain' => (string)$r->domain,
        'item'   => $text,
        'score'  => (int)$r->score,
        'weight' => (int)$r->weight,
      ];
    }

    // اولویت: امتیاز بالاتر و سپس وزن بالاتر
    usort($list, fn($a,$b)=>($b['score']<=>$a['score']) ?: ($b['weight']<=>$a['weight']));
    $list = array_slice($list, 0, 20);

    $date = $this->repo->latestCompletedDate($companyId);
    $summary = $this->buildSummary($list);

    return [
      'title'   => "نقاط قوت شرکت «{$companyAlias}»" . ($date ? " (آخرین ارزیابی: {$date})" : ''),
      'summary' => $summary,
      'tables'  => [
        [
          'title'   => 'Strengths',
          'columns' => ['domain','item','score','weight'],
          'rows'    => array_map('array_values', $list)
        ],
      ],
      'cards' => [
        ['title'=>'Total strengths','value'=>count($list)]
      ],
      'meta' => [
        'companies' => [$companyAlias],
        'last_assessment_dates' => [$companyAlias => $date],
        'limits' => ['per_section'=>20]
      ],
    ];
  }

  private function buildSummary(array $rows): string {
    $n = count($rows);
    if ($n === 0) return "نقطه قوتی مطابق قواعد (۷۰–۱۰۰) یافت نشد.";
    $topDomains = collect($rows)->groupBy('domain')->sortByDesc(fn($g)=>count($g))->keys()->take(3)->implode('، ');
    return "مجموع نقاط قوت: {$n}. حوزه‌های برجسته: {$topDomains}. پیشنهاد: از این مزیت‌ها در پروژه‌های ۹۰ روز آینده بهره‌برداری شود.";
  }

  private function empty(string $title, string $msg): array {
    return ['title'=>$title,'summary'=>$msg,'tables'=>[],'cards'=>[],'meta'=>[]];
  }
}
