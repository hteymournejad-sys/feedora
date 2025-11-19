<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run()
    {
        $questions = [
            [
                'domain' => 'حاکمیت فناوری اطلاعات',
                'text' => 'آیا سازمان شما دارای سیاست‌های مشخصی برای مدیریت فناوری اطلاعات است؟',
                'weight' => 2,
                'applicable_small' => true,
                'applicable_medium' => true,
                'applicable_large' => true,
                'applicable_manufacturing' => true,
                'applicable_service' => true,
                'applicable_distribution' => true,
                'applicable_investment' => true,
                'risks' => 'عدم وجود سیاست‌ها می‌تواند منجر به ناهماهنگی و افزایش ریسک شود.',
                'strengths' => 'وجود سیاست‌ها باعث هماهنگی و کارایی بیشتر می‌شود.',
                'current_status' => 'در حال حاضر ممکن است سیاست‌ها ناقص باشند.',
                'improvement_opportunities' => 'تدوین و اجرای سیاست‌های جامع فناوری اطلاعات.',
            ],
            [
                'domain' => 'زیرساخت فناوری',
                'text' => 'آیا زیرساخت‌های شبکه سازمان شما به‌روز و امن هستند؟',
                'weight' => 3,
                'applicable_small' => true,
                'applicable_medium' => true,
                'applicable_large' => true,
                'applicable_manufacturing' => true,
                'applicable_service' => true,
                'applicable_distribution' => true,
                'applicable_investment' => true,
                'risks' => 'زیرساخت قدیمی ممکن است منجر به قطعی و مشکلات امنیتی شود.',
                'strengths' => 'زیرساخت به‌روز باعث پایداری و امنیت بیشتر می‌شود.',
                'current_status' => 'زیرساخت‌ها نیاز به ارتقا دارند.',
                'improvement_opportunities' => 'سرمایه‌گذاری در به‌روزرسانی زیرساخت‌های شبکه.',
            ],
            [
                'domain' => 'مدیریت ریسک و امنیت اطلاعات',
                'text' => 'آیا سازمان شما برنامه‌ای برای مدیریت ریسک‌های امنیتی دارد؟',
                'weight' => 2,
                'applicable_small' => true,
                'applicable_medium' => true,
                'applicable_large' => true,
                'applicable_manufacturing' => true,
                'applicable_service' => true,
                'applicable_distribution' => true,
                'applicable_investment' => true,
                'risks' => 'عدم مدیریت ریسک می‌تواند منجر به خسارات مالی و اطلاعاتی شود.',
                'strengths' => 'مدیریت ریسک باعث کاهش خطرات و افزایش امنیت می‌شود.',
                'current_status' => 'برنامه مدیریت ریسک وجود ندارد یا ناقص است.',
                'improvement_opportunities' => 'ایجاد برنامه جامع مدیریت ریسک و امنیت اطلاعات.',
            ],
            [
                'domain' => 'خدمات پشتیبانی',
                'text' => 'آیا خدمات پشتیبانی فناوری اطلاعات به‌صورت 24/7 ارائه می‌شود؟',
                'weight' => 1,
                'applicable_small' => true,
                'applicable_medium' => true,
                'applicable_large' => true,
                'applicable_manufacturing' => true,
                'applicable_service' => true,
                'applicable_distribution' => true,
                'applicable_investment' => true,
                'risks' => 'عدم پشتیبانی 24/7 می‌تواند باعث تأخیر در رفع مشکلات شود.',
                'strengths' => 'پشتیبانی مداوم باعث افزایش رضایت کاربران می‌شود.',
                'current_status' => 'پشتیبانی فقط در ساعات اداری ارائه می‌شود.',
                'improvement_opportunities' => 'ارائه خدمات پشتیبانی به‌صورت 24/7.',
            ],
            [
                'domain' => 'سامانه‌های کاربردی',
                'text' => 'آیا سامانه‌های کاربردی سازمان شما با نیازهای روز هم‌خوانی دارند؟',
                'weight' => 2,
                'applicable_small' => true,
                'applicable_medium' => true,
                'applicable_large' => true,
                'applicable_manufacturing' => true,
                'applicable_service' => true,
                'applicable_distribution' => true,
                'applicable_investment' => true,
                'risks' => 'سامانه‌های قدیمی ممکن است کارایی را کاهش دهند.',
                'strengths' => 'سامانه‌های به‌روز باعث افزایش بهره‌وری می‌شوند.',
                'current_status' => 'برخی سامانه‌ها قدیمی هستند.',
                'improvement_opportunities' => 'به‌روزرسانی یا جایگزینی سامانه‌های قدیمی.',
            ],
            [
                'domain' => 'تحول دیجیتال',
                'text' => 'آیا سازمان شما برنامه‌ای برای تحول دیجیتال و دیجیتالی‌سازی فرایندها دارد؟',
                'weight' => 3,
                'applicable_small' => true,
                'applicable_medium' => true,
                'applicable_large' => true,
                'applicable_manufacturing' => true,
                'applicable_service' => true,
                'applicable_distribution' => true,
                'applicable_investment' => true,
                'risks' => 'عدم تحول دیجیتال می‌تواند باعث عقب‌ماندگی از رقبا شود.',
                'strengths' => 'تحول دیجیتال باعث افزایش کارایی و نوآوری می‌شود.',
                'current_status' => 'تحول دیجیتال به‌صورت محدود انجام شده است.',
                'improvement_opportunities' => 'سرمایه‌گذاری در پروژه‌های تحول دیجیتال.',
            ],
            [
                'domain' => 'هوشمندسازی',
                'text' => 'آیا از فناوری‌های هوشمند (مثل هوش مصنوعی) در سازمان استفاده می‌شود؟',
                'weight' => 2,
                'applicable_small' => true,
                'applicable_medium' => true,
                'applicable_large' => true,
                'applicable_manufacturing' => true,
                'applicable_service' => true,
                'applicable_distribution' => true,
                'applicable_investment' => true,
                'risks' => 'عدم استفاده از فناوری‌های هوشمند باعث کاهش کارایی می‌شود.',
                'strengths' => 'هوشمندسازی باعث بهبود تصمیم‌گیری و کارایی می‌شود.',
                'current_status' => 'استفاده از فناوری‌های هوشمند محدود است.',
                'improvement_opportunities' => 'سرمایه‌گذاری در فناوری‌های هوشمند.',
            ],
        ];

        foreach ($questions as $question) {
            Question::create($question);
        }
    }
}