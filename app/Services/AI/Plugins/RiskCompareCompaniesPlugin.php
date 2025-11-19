<?php
// app/Services/AI/Plugins/RiskCompareCompaniesPlugin.php
namespace App\Services\AI\Plugins;

use App\Services\AI\Plugins\Contracts\RetrieverPlugin;
use App\Domain\Scoring\Bucketizer;
use App\Repositories\AssessmentRepository;
use App\Support\Text\Summarizer;

class RiskCompareCompaniesPlugin implements RetrieverPlugin {
  public function __construct(
    private AssessmentRepository $repo,
  ) {}

  public function supports(string $question, array $ctx): bool {
    return ($ctx['intent'] ?? null) === 'risk.compareCompanies'
        && !empty($ctx['company_ids']) && count($ctx['company_ids']) >= 2;
  }

  public function handle(string $q, array $ctx): array {
    $ids    = array_values($ctx['company_ids']);
    $idA    = $ids[0]; $idB = $ids[1];
    $aliasA = $ctx['company_aliases'][$idA] ?? 'A';
    $aliasB = $ctx['company_aliases'][$idB] ?? 'B';

    // آخرین ارزیابی کامل هر طرف
    $grpA = $this->repo->latestCompletedGroupId($idA);
    $grpB = $this->repo->latestCompletedGroupId($idB);
    if (!$grpA || !$grpB) {
      return $this->empty("مقایسه ریسک‌ها: {$aliasA} در برابر {$aliasB}",
        "برای هر دو شرکت ارزیابی کامل‌شده موجود نیست.");
    }

    // استخراج ریسک‌ها برای هر شرکت
    $riskA = $this->collectRisks($grpA);
    $riskB = $this->collectRisks($grpB);

    // تمرکز طبق خواسته شما: high اولویت دارد؛ سپس medium/low در صورت لزوم
    // دامنه‌های مشترک و منحصربه‌فرد
    $domainsA = collect($riskA['all'])->pluck('domain')->unique()->values();
    $domainsB = collect($riskB['all'])->pluck('domain')->unique()->values();

    $common   = $domainsA->intersect($domainsB)->values();
    $onlyA    = $domainsA->diff($domainsB)->values();
    $onlyB    = $domainsB->diff($domainsA)->values();

    // ساخت سه جدول خروجی (ترجیحاً Highها را نشان بدهیم؛ اگر نبود از Medium)
    $tblCommon = $this->buildCommonTable($riskA, $riskB, $common);
    $tblOnlyA  = $this->buildSingleSideTable($riskA, $onlyA, $aliasA);
    $tblOnlyB  = $this->buildSingleSideTable($riskB, $onlyB, $aliasB);

    // کارت‌های شمارش
    $cards = [
      ['title'=>"$aliasA — High",   'value'=>count($riskA['high'])],
      ['title'=>"$aliasA — Medium", 'value'=>count($riskA['medium'])],
      ['title'=>"$aliasA — Low",    'value'=>count($riskA['low'])],
      ['title'=>"$aliasB — High",   'value'=>count($riskB['high'])],
      ['title'=>"$aliasB — Medium", 'value'=>count($riskB['medium'])],
      ['title'=>"$aliasB — Low",    'value'=>count($riskB['low'])],
    ];

    $dateA = $this->repo->latestCompletedDate($idA);
    $dateB = $this->repo->latestCompletedDate($idB);
    $summary = $this->buildSummary($aliasA, $aliasB, $riskA, $riskB);

    return [
      'title'   => "مقایسه ریسک‌ها: {$aliasA} در برابر {$aliasB}" .
                   (($dateA || $dateB) ? " (آخرین ارزیابی: {$aliasA}={$dateA} | {$aliasB}={$dateB})" : ''),
      'summary' => $summary,
      'tables'  => [
        $tblCommon,
        $tblOnlyA,
        $tblOnlyB,
      ],
      'cards' => $cards,
      'meta'  => [
        'companies' => [$aliasA, $aliasB],
        'last_assessment_dates' => [$aliasA=>$dateA, $aliasB=>$dateB],
        'limits' => ['per_section'=>20]
      ],
    ];
  }

  /** استخراج ریسک‌ها با سطل‌بندی و اولویت weight */
  private function collectRisks(int $groupId): array {
    $rows = $this->repo->answersWithQuestions($groupId);

    $parts = ['high'=>[], 'medium'=>[], 'low'=>[], 'all'=>[]];
    foreach ($rows as $r) {
      $bucket = Bucketizer::riskBucket($r->domain, (int)$r->weight, (int)$r->score);
      if (!$bucket) continue;

      $text = $r->risks ?: Summarizer::short(trim(($r->description ?? '') . ' ' . ($r->guide ?? '')));
      if ($text === '') continue;

      $item = [
        'domain' => (string)$r->domain,
        'item'   => $text,
        'score'  => (int)$r->score,
        'weight' => (int)$r->weight,
      ];
      $parts[$bucket][] = $item;
      $parts['all'][]   = $item;
    }

    foreach (['high','medium','low','all'] as $k) {
      usort($parts[$k], fn($a,$b)=>($b['weight']<=>$a['weight']) ?: ($a['score']<=>$b['score']));
      $parts[$k] = array_slice($parts[$k], 0, 50); // سقف امن‌تر برای پردازش داخلی
    }
    return $parts;
  }

  private function buildCommonTable(array $A, array $B, $commonDomains): array {
    // ترجیحاً Highها را نمایش بدهیم؛ اگر نبود از Medium همان دامنه
    $rows = [];
    foreach ($commonDomains as $d) {
      $topA = $this->pickTopFromDomain($A, $d);
      $topB = $this->pickTopFromDomain($B, $d);
      if (!$topA && !$topB) continue;

      $rows[] = [
        $d,
        $topA ? $topA['item'] : '—',
        $topA ? $topA['score'] : null,
        $topA ? $topA['weight'] : null,
        $topB ? $topB['item'] : '—',
        $topB ? $topB['score'] : null,
        $topB ? $topB['weight'] : null,
      ];
    }

    // محدودسازی نمایش
    $rows = array_slice($rows, 0, 20);
    return [
      'title'   => 'دامنه‌های مشترک (نمونه ریسک‌ها با اولویت High)',
      'columns' => ['domain', 'A_item', 'A_score', 'A_weight', 'B_item', 'B_score', 'B_weight'],
      'rows'    => $rows
    ];
  }

  private function buildSingleSideTable(array $side, $domains, string $alias): array {
    $rows = [];
    foreach ($domains as $d) {
      $top = $this->pickTopFromDomain($side, $d);
      if (!$top) continue;
      $rows[] = [$d, $top['item'], $top['score'], $top['weight']];
    }
    $rows = array_slice($rows, 0, 20);
    return [
      'title'   => "دامنه‌های ویژهٔ {$alias}",
      'columns' => ['domain','item','score','weight'],
      'rows'    => $rows
    ];
  }

  /** انتخاب بهترین آیتم یک دامنه: High اگر نبود Medium، اگر نبود Low */
  private function pickTopFromDomain(array $side, string $domain): ?array {
    foreach (['high','medium','low'] as $lvl) {
      $hit = collect($side[$lvl])->first(fn($r)=>$r['domain']===$domain);
      if ($hit) return $hit;
    }
    return null;
  }

  private function buildSummary(string $aliasA, string $aliasB, array $A, array $B): string {
    $ha = count($A['high']); $hb = count($B['high']);
    $ma = count($A['medium']); $mb = count($B['medium']);
    $la = count($A['low']);    $lb = count($B['low']);

    $lead = $ha === $hb ? "ریسک‌های High دو طرف نزدیک است." :
            ($ha > $hb ? "{$aliasA} ریسک‌های High بیشتری دارد." : "{$aliasB} ریسک‌های High بیشتری دارد.");

    $hint = "پیشنهاد: ابتدا روی کاهش High در طرف پرریسک‌تر تمرکز شود؛ سپس دامنه‌های مشترک با وزن بالا هدف‌گذاری گردد.";
    return "{$lead} (High: {$aliasA}={$ha} | {$aliasB}={$hb} ؛ Medium: {$aliasA}={$ma} | {$aliasB}={$mb}). {$hint}";
  }

  private function empty(string $title, string $msg): array {
    return ['title'=>$title,'summary'=>$msg,'tables'=>[],'cards'=>[],'meta'=>[]];
  }
}
