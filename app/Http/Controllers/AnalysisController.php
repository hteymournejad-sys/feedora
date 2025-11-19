<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Answer;
use App\Models\Question;

class AnalysisController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $userId = auth()->id();
        $answers = Answer::where('user_id', $userId)->with('question')->get();
        $totalScore = $answers->avg('answer') ?: 0;
        $categoryScores = [];
        $questionDetails = [];

        // محاسبه امتیاز هر دسته‌بندی
        $categories = Question::select('category')->distinct()->pluck('category');
        foreach ($categories as $category) {
            $categoryQuestions = Question::where('category', $category)->pluck('question_id');
            $categoryAnswers = $answers->whereIn('question_id', $categoryQuestions);
            $categoryScores[$category] = $categoryAnswers->avg('answer') ?: 0;
        }

        // جزئیات سوال و جواب
        foreach ($answers as $answer) {
            $questionDetails[] = [
                'question' => $answer->question->text,
                'category' => $answer->question->category,
                'answer' => $answer->answer,
                'recommendation' => $this->getRecommendation($answer->answer),
            ];
        }

        $message = $this->getAnalysisMessage($totalScore);

        $chartLabels = array_keys($categoryScores);
        $chartData = array_values($categoryScores);

        return view('analysis', compact('totalScore', 'categoryScores', 'message', 'chartLabels', 'chartData', 'questionDetails'));
    }

    private function getAnalysisMessage($score)
    {
        if ($score >= 80) return 'عملکرد عالی! سازمان شما در سطح استانداردهای جهانی پیشرو است.';
        if ($score >= 60) return 'عملکرد خوب! پیشرفت قابل‌قبولی مشاهده می‌شود.';
        if ($score >= 40) return 'عملکرد متوسط! نیاز به بهبود در برخی بخش‌ها دارید.';
        return 'عملکرد ضعیف! اقدامات اولیه را آغاز کنید.';
    }

    private function getRecommendation($score)
    {
        if ($score >= 80) return 'عملکرد شما عالی است! همین مسیر را ادامه دهید.';
        if ($score >= 60) return 'خوب است، اما می‌توانید با برنامه‌ریزی بهتر پیشرفت بیشتری کنید.';
        if ($score >= 40) return 'نیاز به بهبود دارید. روی فرآیندهای خود بیشتر کار کنید.';
        return 'نیاز به اقدامات فوری دارید. با یک مشاور صحبت کنید.';
    }
}