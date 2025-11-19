<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportAnalysisController;
use App\Http\Controllers\AIConsoleController;
use App\Http\Controllers\TrendForecastController;

// Authentication Routes (بدون ثبت نام)
Auth::routes(['register' => false]);

// صفحه اصلی به صفحه لاگین هدایت می‌شود
Route::get('/', function () {
    return redirect()->route('login');
})->name('welcome');

// Authenticated Routes
Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');

Route::get('/assessment/get-latest-completed-group', [AssessmentController::class, 'getLatestCompletedGroup'])->name('assessment.get-latest-completed-group');

// Profile Routes
Route::get('/profile', [ProfileController::class, 'index'])->name('profile')->middleware('auth');
Route::match(['post', 'patch'], '/profile/update', [ProfileController::class, 'update'])->name('update-profile')->middleware('auth');
Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('change-password')->middleware('auth');
Route::post('/compare-companies', [ProfileController::class, 'showCompareCompanies'])->name('compare.companies')->middleware('auth');
Route::post('/compare-non-tech-companies', [ProfileController::class, 'showCompareNonTechCompanies'])->name('compare.non_tech_companies')->middleware('auth');
// System Manager Routes
Route::prefix('system-manager')->middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'systemManagerProfile'])->name('system-manager.profile');
    Route::post('/create-user', [ProfileController::class, 'createUser'])->name('system-manager.create-user');
    Route::patch('/update-user/{userId}', [ProfileController::class, 'updateUser'])->name('system-manager.update-user');
    Route::delete('/delete-user/{userId}', [ProfileController::class, 'deleteUser'])->name('system-manager.delete-user');
    Route::patch('/toggle-block-user/{userId}', [ProfileController::class, 'toggleBlockUser'])->name('system-manager.toggle-block-user');
    Route::patch('/update-password/{userId}', [ProfileController::class, 'updatePassword'])->name('system-manager.update-password');
});




Route::get('/forecast/trend', [TrendForecastController::class, 'index'])
    ->name('forecast.trend')
    ->middleware('auth');






Route::get('/ai-console', [AIConsoleController::class, 'index'])->name('ai.console');
Route::post('/ai-console/query', [AIConsoleController::class, 'query'])->name('ai.console.query');







Route::post('/ai/company-question', [AIConsoleController::class, 'companyQuestion'])
    ->name('ai.company.question');
Route::post('/ai/company-compare', [AIConsoleController::class, 'companyCompare'])
    ->name('ai.company.compare');
// UI چت واحد
Route::match(['get', 'post'], '/ai/console/chat', [AIConsoleController::class, 'chatConsole'])
    ->name('ai.console.chat');



Route::middleware('auth')->group(function () {

    // بقیه‌ی routeهای ai...

    // برای راحتی: هم GET هم POST
  Route::match(['get', 'post'], '/ai/lstm/predict', [AIConsoleController::class, 'lstmPredict'])
        ->name('ai.lstm.predict');

    Route::match(['get', 'post'], '/ai/lstm/explain', [AIConsoleController::class, 'lstmExplain'])
        ->name('ai.lstm.explain');
});



Route::post('/ai/console/chat-ajax', [AIConsoleController::class, 'chatAjax'])
    ->name('ai.console.chat.ajax');









// Admin Routes
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/comments/{commentId}/toggle-approval', [ProfileController::class, 'toggleCommentApproval'])->name('admin.comment.toggle-approval');
    Route::delete('/comments/{commentId}', [ProfileController::class, 'deleteComment'])->name('admin.profile.deleteComment');
    Route::get('/profile', [ProfileController::class, 'adminProfile'])->name('admin.profile');
    Route::patch('/toggle-block/{userId}', [ProfileController::class, 'toggleBlockUser'])->name('toggle.block.user');
    Route::post('/update-credit-settings', [ProfileController::class, 'updateCreditSettings'])->name('admin.update-credit-settings');
    Route::post('/create-system-manager', [ProfileController::class, 'createSystemManager'])->name('admin.create-system-manager');
    Route::patch('/update-system-manager-password/{userId}', [ProfileController::class, 'updateSystemManagerPassword'])->name('admin.update-system-manager-password');
    Route::patch('/toggle-block-system-manager/{userId}', [ProfileController::class, 'toggleBlockSystemManager'])->name('admin.toggle-block-system-manager');
    Route::delete('/delete-system-manager/{userId}', [ProfileController::class, 'deleteSystemManager'])->name('admin.delete-system-manager');
    Route::post('/create-user', [ProfileController::class, 'createUser'])->name('admin.create-user');
    Route::patch('/update-user/{userId}', [ProfileController::class, 'updateUser'])->name('admin.update-user');
    Route::delete('/delete-user/{userId}', [ProfileController::class, 'deleteUser'])->name('admin.delete-user');
    Route::patch('/toggle-block-user/{userId}', [ProfileController::class, 'toggleBlockUser'])->name('admin.toggle-block-user');
    Route::patch('/update-password/{userId}', [ProfileController::class, 'updatePassword'])->name('admin.update-password');
    Route::patch('/system-manager/{userId}', [ProfileController::class, 'updateSystemManagerInfo'])->name('admin.update-system-manager');
});

// Add these routes
Route::get('/non-technical/form', [App\Http\Controllers\ProfileController::class, 'showNonTechnicalForm'])->name('non-technical.form');
Route::post('/non-technical/store', [App\Http\Controllers\ProfileController::class, 'storeNonTechnical'])->name('non-technical.store');


Route::get('/profile/ranking', [ProfileController::class, 'showCompanyRanking'])->name('profile.ranking');

// Report Routes
Route::get('/report', function () {
    return view('report');
});
Route::get('/assessment/report/{assessment_group_id?}', [AssessmentController::class, 'report'])->name('assessment.report')->middleware('auth');
Route::get('/report/analysis', [ReportAnalysisController::class, 'analysisReport'])->name('report.analysis');
Route::get('/assessment/group/report/{assessment_group_id}', [AssessmentController::class, 'groupReport'])->name('assessment.group.report');
Route::get('/assessment/print-report/{assessment_group_id}', [AssessmentController::class, 'printReport'])->name('report.print');
Route::post('/assessment/report/pdf', [PdfController::class, 'downloadReport'])->name('assessment.report.pdf');
Route::patch('profile/update', [ProfileController::class, 'update'])->name('update-profile');
// Invoice Routes
Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('view-invoice');

// Payment Routes
Route::post('/payment/start', [PaymentController::class, 'start'])->name('payment.start');
Route::get('/payment-inquiry/{authority}', [PaymentController::class, 'inquiry'])->name('payment.inquiry');
Route::get('/payment/{id}/invoice', [PaymentController::class, 'downloadInvoice'])->name('download-invoice')->middleware('auth');
Route::post('/payment/store', [PaymentController::class, 'storePayment'])->name('payment.store')->middleware('auth');

// Assessment Routes
Route::get('/assessment/compare', [AssessmentController::class, 'compareReports'])->name('assessment.compare');
Route::get('/assessment/{assessmentId}/question/{questionId}/previous', [AssessmentController::class, 'previousQuestion'])->name('assessment.previous');
Route::get('/assessment/{id}/pdf', function ($id) {
    return "دانلود PDF برای ارزیابی $id (به زودی پیاده‌سازی می‌شود)";
})->name('assessment.pdf');
Route::get('/assessment/{id}/details', function ($id) {
    return "صفحه جزئیات برای ارزیابی $id (به زودی پیاده‌سازی می‌شود)";
})->name('assessment.details');
Route::get('/assessment/{id}/compare', function ($id) {
    return "مقایسه ارزیابی $id با قبلی (به زودی پیاده‌سازی می‌شود)";
})->name('compare-assessments');

// Question Routes
Route::get('/questions', [QuestionController::class, 'index'])->middleware('auth')->name('questions');
Route::post('/questions', [QuestionController::class, 'store'])->middleware('auth');
Route::get('/questions/exit', [QuestionController::class, 'exit'])->middleware('auth')->name('questions.exit');
Route::get('/questions/import', function () {
    return view('questions.import');
})->name('questions.import.form')->middleware('auth');
Route::post('/questions/import', [QuestionController::class, 'import'])->name('questions.import')->middleware('auth');

// Analysis Route
Route::get('/analysis', [AnalysisController::class, 'index'])->middleware('auth')->name('analysis');

// Assessment Creation Route
Route::post('/assessment/create', [AssessmentController::class, 'showQuestions'])->middleware('auth')->name('assessment.create');

// Authenticated Assessment Routes
Route::middleware('auth')->group(function () {
    Route::get('/assessment/domains', [AssessmentController::class, 'showDomains'])->name('assessment.domains');
    Route::post('/assessment/check-domain', [AssessmentController::class, 'checkDomain'])->name('assessment.check-domain');
    Route::get('/assessment/{assessment}/questions', [AssessmentController::class, 'showQuestions'])->name('assessment.auth.questions');
    Route::get('/assessment/{assessment}/previous/{question}', [AssessmentController::class, 'previousQuestion'])->name('assessment.auth.previous');
    Route::get('/assessment/{assessment}/exit', [AssessmentController::class, 'exitAssessment'])->name('assessment.exit');
    Route::get('/assessment/{assessment}/finalize', [AssessmentController::class, 'finalize'])->name('assessment.finalize');
    Route::get('/assessment/{assessment}/resume', [AssessmentController::class, 'resumeAssessment'])->name('assessment.resume');
    Route::post('/assessment/{assessment}/questions/{question}', [AssessmentController::class, 'storeAnswer'])->name('assessment.store');
    Route::get('/assessment/questions', [AssessmentController::class, 'showQuestions'])->name('assessment.questions');
    Route::post('/assessment/{assessmentId}/question/{questionId}/answer', [AssessmentController::class, 'storeAnswer'])->name('assessment.answer');
    Route::get('/assessment/{assessmentId}/finalize', [AssessmentController::class, 'finalize'])->name('assessment.finalize.id');
    Route::get('/assessment/{assessmentId}/result', [AssessmentController::class, 'showResult'])->name('assessment.result');
    Route::get('/assessment/{assessmentId}/exit', [AssessmentController::class, 'exitAssessment'])->name('assessment.exit.id');
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});