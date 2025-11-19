<?php
namespace App\Repositories;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Support\Text\Normalizer;

class CompanyMatchRepository {
  /** حداکثر 2 شرکت را از users.company_alias تشخیص می‌دهد */
  public function detectCompaniesFromText(string $q): array {
    $q = Normalizer::fa($q);
    $aliases = DB::table('users')->select('id','company_alias')
      ->whereNotNull('company_alias')->pluck('company_alias','id');

    $found = [];
    foreach ($aliases as $id => $alias) {
      $a = Normalizer::fa($alias);
      if (Str::of($q)->contains($a)) $found[$id] = $alias;
      elseif (Str::of($q)->contains(Str::of($a)->replace(' شرکت ','')->toString())) $found[$id] = $alias;
    }
    if (empty($found)) {
      // fallback: LIKE جست‌وجوی تقریبی
      $pieces = collect(explode(' ', $q))->filter(fn($x)=>mb_strlen($x)>=3)->take(3);
      foreach ($pieces as $p) {
        $row = DB::table('users')->select('id','company_alias')
          ->where('company_alias','like',"%$p%")->first();
        if ($row) {$found[$row->id] = $row->company_alias; if (count($found)>=2) break;}
      }
    }
    return array_slice($found, 0, 2, true);
  }
}
