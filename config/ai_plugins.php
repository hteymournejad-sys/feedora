<?php

return [
  // ثبت پلاگین‌ها (ترتیب مهم است)
  'plugins' => [
    App\Services\AI\Plugins\RiskByCompanyPlugin::class,
    App\Services\AI\Plugins\StrengthsByCompanyPlugin::class,
    App\Services\AI\Plugins\WeaknessesByCompanyPlugin::class,
    App\Services\AI\Plugins\RiskCompareCompaniesPlugin::class,
    App\Services\AI\Plugins\StrengthsCompareCompaniesPlugin::class,
    App\Services\AI\Plugins\SwotByCompanyPlugin::class,
    App\Services\AI\Plugins\SwotComparePlugin::class,
 App\Services\AI\Plugins\RiskByCompanyPlugin::class,
    App\Services\AI\Plugins\StrengthsByCompanyPlugin::class,
    App\Services\AI\Plugins\RiskCompareCompaniesPlugin::class,
  ],

  // نگاشت واژه‌ها به intent پیشنهادی
  'intent_map' => [
    'risk'       => ['ریسک','خطر','تهدید'],
    'strengths'  => ['قوت','مزیت','نقاط قوت'],
    'weaknesses' => ['ضعف','بهبود','فرصت بهبود'],
    'compare'    => ['مقایسه','compare','در برابر','vs'],
    'swot'       => ['swot','اسوات','تحلیل قوت و ضعف'],
  ],
];
