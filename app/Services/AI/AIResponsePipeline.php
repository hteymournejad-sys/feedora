<?php
namespace App\Services\AI;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use App\Support\Text\Normalizer;
use App\Repositories\CompanyMatchRepository;

class AIResponsePipeline {
  public function __construct(private CompanyMatchRepository $companies) {}

  public function ask(string $question, array $options=[]): array {
    $q = Normalizer::fa($question);

    // 1) intent
    $intent = $this->inferIntent($q);

    // 2) شرکت‌ها
    $found = $this->companies->detectCompaniesFromText($q);
    $ctx = [
      'intent' => $this->resolveIntentWithCompanyCount($intent, count($found)),
      'company_ids' => array_keys($found),
      'company_aliases' => $found,
      'maxBlocks' => $options['maxBlocks'] ?? 6,
    ];

    // 3) اجرای پلاگین‌ها (بدون تغییر UniversalRetriever)
    $out = [];
    foreach (Config::get('ai_plugins.plugins', []) as $clazz) {
      /** @var \App\Services\AI\Plugins\Contracts\RetrieverPlugin $p */
      $p = App::make($clazz);
      if (!$p->supports($q, $ctx)) continue;
      $out[] = $p->handle($q, $ctx);
    }

    // اگر پلاگین مرتبطی پیدا نشد، می‌توانید:
    // - خروجی UniversalRetriever را بازگردانید (آنی)
    // - یا یک پیام راهنما برگردانید.
    return $this->merge($out);
  }

  private function inferIntent(string $q): string {
    $map = Config::get('ai_plugins.intent_map');
    $has = fn(array $keys)=> collect($keys)->first(fn($k)=>mb_stripos($q,$k)!==false);

    if ($has($map['compare'] ?? [])) return 'compare';
    if ($has($map['swot'] ?? []))    return 'swot';
    if ($has($map['risk'] ?? []))    return 'risk.byCompany';
    if ($has($map['strengths'] ?? [])) return 'strengths.byCompany';
    if ($has($map['weaknesses'] ?? [])) return 'weaknesses.byCompany';
    return 'risk.byCompany'; // پیش‌فرض
  }

  private function resolveIntentWithCompanyCount(string $intent, int $n): string {
    if ($intent==='compare' && $n>=2) return 'risk.compareCompanies'; // پیش‌فرض مقایسه ریسک
    if ($intent==='swot' && $n>=2)    return 'swot.compare';
    if ($intent==='swot')             return 'swot.byCompany';
    return $intent;
  }

  private function merge(array $parts): array {
    if (empty($parts)) {
      return ['title'=>'','summary'=>'موردی برای این پرسش یافت نشد.','tables'=>[],'cards'=>[],'meta'=>[]];
    }
    // ادغام ساده: اولین قطعه، سپس tables/cards بقیه
    $base = array_shift($parts);
    foreach ($parts as $p) {
      $base['tables'] = array_merge($base['tables'] ?? [], $p['tables'] ?? []);
      $base['cards']  = array_merge($base['cards'] ?? [],  $p['cards'] ?? []);
    }
    return $base;
  }
}
