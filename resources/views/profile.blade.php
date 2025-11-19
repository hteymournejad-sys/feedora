@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card bg-white p-6 rounded-lg shadow-lg animate__animated animate__fadeIn" style="direction: rtl; text-align: right;">
                <div class="card-header" style="background-color: #4b5e6d; color: #ffffff;">پروفایل کاربر - {{ auth()->user()->company_alias }}</div>
                <div class="card-body">
                    <div class="row">
                        <!-- Sidebar: Navigation Tabs -->
                        <div class="col-md-3" style="border-left: 2px solid #ccc; padding-left: 15px;">
                            <style>
                                /* استایل‌های اختصاصی برای منوی پروفایل */
                                .profile-nav {
                                    background: linear-gradient(to right, #1a252f 0%, #4a6a8f 100%);
                                    border-radius: 12px;
                                    padding: 15px;
                                }
                                .profile-nav .nav-link {
                                    color: #ffffff !important;
                                    border-radius: 8px;
                                    margin-bottom: 8px;
                                    padding: 12px 15px;
                                    transition: background-color 0.3s ease;
                                    display: flex;
                                    align-items: center;
                                    gap: 10px;
                                }
                                .profile-nav .nav-link:hover {
                                    background-color: #162531;
                                }
                                .profile-nav .nav-link.active {
                                    background-color: #162531;
                                    position: relative;
                                }
                                .profile-nav .nav-link.active::after {
                                    content: '\f285'; /* آیکون فلش از Bootstrap Icons */
                                    font-family: 'bootstrap-icons';
                                    color: #0059d8;
                                    position: absolute;
                                    right: 10px;
                                    font-size: 14px;
                                }
                                .profile-nav .nav-link i {
                                    font-size: 16px;
                                    color: #ffffff;
                                }
                                .profile-nav hr {
                                    border-top: 1px solid rgba(255, 255, 255, 0.3);
                                    margin: 12px 0;
                                }
                            </style>
                            <div class="nav flex-column nav-pills profile-nav" id="profileTabs" role="tablist" aria-orientation="vertical" style="text-align: left !important; direction: ltr !important;">
                                <button class="nav-link {{ $activeTab == 'settings' ? 'active' : '' }}" id="settings-tab" data-bs-toggle="pill" data-bs-target="#settings" type="button" role="tab" aria-controls="settings" aria-selected="{{ $activeTab == 'settings' ? 'true' : 'false' }}" style="text-align: left !important;">
                                    <i class="bi bi-check2-square"></i> شروع ارزیابی فنی
                                </button>
                                <button class="nav-link {{ $activeTab == 'non-tech-settings' ? 'active' : '' }}" id="non-tech-settings-tab" data-bs-toggle="pill" data-bs-target="#non-tech-settings" type="button" role="tab" aria-controls="non-tech-settings" aria-selected="{{ $activeTab == 'non-tech-settings' ? 'true' : 'false' }}" style="text-align: left !important;">
                                    <i class="bi bi-check2-square"></i> شروع ارزیابی غیر فنی
                                </button>
                                <hr style="margin: 10px 0; border-top: 1px solid #ccc;">
                                <button class="nav-link {{ $activeTab == 'assessments' ? 'active' : '' }}" id="assessments-tab" data-bs-toggle="pill" data-bs-target="#assessments" type="button" role="tab" aria-controls="assessments" aria-selected="{{ $activeTab == 'assessments' ? 'true' : 'false' }}" style="text-align: left !important;">
                                    <i class="bi bi-bar-chart-line"></i> نتایج ارزیابی فنی
                                </button>
                                <button class="nav-link {{ $activeTab == 'non-tech-assessments' ? 'active' : '' }}" id="non-tech-assessments-tab" data-bs-toggle="pill" data-bs-target="#non-tech-assessments" type="button" role="tab" aria-controls="non-tech-assessments" aria-selected="{{ $activeTab == 'non-tech-assessments' ? 'true' : 'false' }}" style="text-align: left !important;">
                                    <i class="bi bi-bar-chart-line"></i> نتایج ارزیابی غیر فنی
                                </button>
                                <hr style="margin: 10px 0; border-top: 1px solid #ccc;">
                                @if (auth()->user()->role === 'holding')
  <button class="nav-link {{ $activeTab == 'ranking-companies' ? 'active' : '' }}" id="ranking-companies-tab" data-bs-toggle="pill" data-bs-target="#ranking-companies" type="button" role="tab" aria-controls="ranking-companies" aria-selected="{{ $activeTab == 'ranking-companies' ? 'true' : 'false' }}" style="text-align: left !important;">
        <i class="bi bi-bar-chart"></i> رتبه‌بندی شرکت‌ها
    </button>

<button class="nav-link {{ $activeTab == 'forecast-trend' ? 'active' : '' }}" id="forecast-trend-tab" data-bs-toggle="pill" data-bs-target="#forecast-trend" type="button" role="tab" aria-controls="forecast-trend" aria-selected="{{ $activeTab == 'forecast-trend' ? 'true' : 'false' }}" style="text-align: left !important;">
                                        <i class="bi bi-graph-up"></i> پیش‌بینی روند نتایج
                                    </button>
           
<a href="{{ url('/ai-console') }}" 
   class="nav-link {{ $activeTab == 'ai-console' ? 'active' : '' }}" 
   id="ai-console-tab"
   target="_blank"
   rel="noopener"
   style="text-align: left !important; display: flex; align-items: center; gap: 10px;">
    <i class="bi bi-robot"></i> دستیار هوش مصنوعی
</a>



<button class="nav-link {{ $activeTab == 'compare-companies' ? 'active' : '' }}" id="compare-companies-tab" data-bs-toggle="pill" data-bs-target="#compare-companies" type="button" role="tab" aria-controls="compare-companies" aria-selected="{{ $activeTab == 'compare-companies' ? 'true' : 'false' }}" style="text-align: left !important;">
                                        <i class="bi bi-bar-chart"></i> مقایسه فنی شرکت‌ها
                                    </button>
                     
<button class="nav-link {{ $activeTab == 'compare-non-tech-companies' ? 'active' : '' }}" id="compare-non-tech-companies-tab" data-bs-toggle="pill" data-bs-target="#compare-non-tech-companies" type="button" role="tab" aria-controls="compare-non-tech-companies" aria-selected="{{ $activeTab == 'compare-non-tech-companies' ? 'true' : 'false' }}" style="text-align: left !important;">
                                        <i class="bi bi-bar-chart"></i> مقایسه غیر فنی شرکت‌ها
                                    </button>

  <button class="nav-link {{ $activeTab == 'subsidiary-assessments' ? 'active' : '' }}" id="subsidiary-assessments-tab" data-bs-toggle="pill" data-bs-target="#subsidiary-assessments" type="button" role="tab" aria-controls="subsidiary-assessments" aria-selected="{{ $activeTab == 'subsidiary-assessments' ? 'true' : 'false' }}" style="text-align: left !important;">
                                        <i class="bi bi-bar-chart-line"></i> نتایج ارزیابی فنی شرکت‌ها
                                    </button>
                       
      <button class="nav-link {{ $activeTab == 'subsidiary-non-tech-assessments' ? 'active' : '' }}" id="subsidiary-non-tech-assessments-tab" data-bs-toggle="pill" data-bs-target="#subsidiary-non-tech-assessments" type="button" role="tab" aria-controls="subsidiary-non-tech-assessments" aria-selected="{{ $activeTab == 'subsidiary-non-tech-assessments' ? 'true' : 'false' }}" style="text-align: left !important;">
                                        <i class="bi bi-bar-chart-line"></i> نتایج ارزیابی غیر فنی شرکت‌ها
                                    </button>
                                   
                                   
 


                                    <hr style="margin: 10px 0; border-top: 1px solid #ccc;">
                                    <button class="nav-link {{ $activeTab == 'tree-view' ? 'active' : '' }}" id="tree-view-tab" data-bs-toggle="pill" data-bs-target="#tree-view" type="button" role="tab" aria-controls="tree-view" aria-selected="{{ $activeTab == 'tree-view' ? 'true' : 'false' }}" style="text-align: left !important;">
                                        <i class="bi bi-diagram-3"></i> نمایش درختی شرکت‌ها
                                    </button>
                                @endif
                                <button class="nav-link {{ $activeTab == 'user-info' ? 'active' : '' }}" id="user-info-tab" data-bs-toggle="pill" data-bs-target="#user-info" type="button" role="tab" aria-controls="user-info" aria-selected="{{ $activeTab == 'user-info' ? 'true' : 'false' }}" style="text-align: left !important;">
                                    <i class="bi bi-person"></i> اطلاعات کاربری
                                </button>
                            </div>
                        </div>

                        <!-- Main Content: Tab Panels -->
                        <div class="col-md-9">
                            <div class="tab-content" id="profileTabsContent">
                                <!-- Settings Tab -->
                                <div class="tab-pane fade {{ $activeTab == 'settings' ? 'show active' : '' }}" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5>جزئیات ارزیابی فنی</h5>
                                            <table class="table">
                                                <tbody>
                                                    <tr><td>دفعات باقیمانده ارزیابی</td><td>{{ auth()->user()->remaining_evaluations }}</td></tr>
                                                    <tr><td>روزهای باقیمانده ارزیابی</td><td>{{ auth()->user()->remaining_days }}</td></tr>
                                                    <tr><td>دفعات خودارزیابی تاکنون</td><td>{{ auth()->user()->self_assessments }}</td></tr>
                                                </tbody>
                                            </table>
                                            <a href="{{ route('assessment.domains') }}" class="btn btn-success">شروع ارزیابی فنی</a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Non-Tech Settings Tab -->
                                <div class="tab-pane fade {{ $activeTab == 'non-tech-settings' ? 'show active' : '' }}" id="non-tech-settings" role="tabpanel" aria-labelledby="non-tech-settings-tab">
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5>جزئیات ارزیابی غیر فنی</h5>
                                            <table class="table">
                                                <tbody>
                                                    <tr><td>تعداد ایستگاه‌های کاری</td><td>{{ $nonTechData->workstations ?? 'نامشخص' }}</td></tr>
                                                    <tr><td>تعداد کاربران فعال</td><td>{{ $nonTechData->active_users ?? 'نامشخص' }}</td></tr>
                                                    <tr><td>تعداد نفرات تمام‌وقت فناوری اطلاعات</td><td>{{ $nonTechData->full_time_it_staff ?? 'نامشخص' }}</td></tr>
                                                    <tr><td>تعداد نفرات مشاور یا پاره‌وقت فناوری</td><td>{{ $nonTechData->part_time_it_staff ?? 'نامشخص' }}</td></tr>
                                                </tbody>
                                            </table>
                                            <a href="{{ route('non-technical.form') }}" class="btn btn-success">شروع ارزیابی غیر‌فنی</a>
                                        </div>
                                    </div>
                                </div>

                                <!-- User Info Tab -->
                                <div class="tab-pane fade {{ $activeTab == 'user-info' ? 'show active' : '' }}" id="user-info" role="tabpanel" aria-labelledby="user-info-tab">
                                    <h4>اطلاعات کاربری</h4>
                                    <table class="table">
                                        <tbody>
                                            <tr><td><strong>ایمیل:</strong></td><td>{{ auth()->user()->email }}</td></tr>
                                            <tr><td><strong>نام:</strong></td><td>{{ auth()->user()->first_name ?? 'ثبت نشده' }}</td></tr>
                                            <tr><td><strong>نام خانوادگی:</strong></td><td>{{ auth()->user()->last_name ?? 'ثبت نشده' }}</td></tr>
                                            <tr><td><strong>شماره موبایل:</strong></td><td>{{ auth()->user()->mobile ?? 'ثبت نشده' }}</td></tr>
                                            <tr><td><strong>کد ملی:</strong></td><td>{{ auth()->user()->national_code ?? 'ثبت نشده' }}</td></tr>
                                        </tbody>
                                    </table>

                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">ویرایش اطلاعات</button>

                                    <div class="card mb-3 mt-3">
                                        <div class="card-body">
                                            <h5>تغییر رمز عبور</h5>
                                            <form method="POST" action="{{ route('change-password') }}">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="current_password" class="form-label">رمز عبور فعلی</label>
                                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                                    @error('current_password')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label for="new_password" class="form-label">رمز عبور جدید</label>
                                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                                    @error('new_password')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label for="new_password_confirmation" class="form-label">تکرار رمز عبور جدید</label>
                                                    <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                                                </div>
                                                <button type="submit" class="btn btn-primary">تغییر رمز عبور</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Assessments Tab -->
                                <div class="tab-pane fade {{ $activeTab == 'assessments' ? 'show active' : '' }}" id="assessments" role="tabpanel" aria-labelledby="assessments-tab">
                                    <h4>نتایج ارزیابی فنی</h4>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ردیف</th>
                                                <th>تاریخ انجام</th>
                                                <th>امتیاز نهایی</th>
                                                <th>گزارش تحلیلی</th>
                                                <th>جزئیات گزارش</th>
                                                <th>مقایسه با قبلی</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($assessmentHistory->isEmpty())
                                                <tr>
                                                    <td colspan="6" class="text-center">هیچ ارزیابی‌ای ثبت نشده است.</td>
                                                </tr>
                                            @else
                                                @foreach($assessmentHistory as $index => $assessment)
                                                    <tr>
                                                        <td>{{ $assessmentHistory->count() - $index }}</td>
                                                        <td>
                                                            @php
                                                                $jalaliDate = 'نامشخص';
                                                                try {
                                                                    $jalaliDate = \Morilog\Jalali\Jalalian::fromDateTime($assessment->created_at)->format('j F Y');
                                                                } catch (\Exception $e) {
                                                                    \Log::error('Failed to convert assessment date to Jalali', [
                                                                        'user_id' => auth()->user()->id,
                                                                        'assessment_group_id' => $assessment->assessment_group_id,
                                                                        'miladi_date' => $assessment->created_at->format('Y-m-d'),
                                                                        'error' => $e->getMessage(),
                                                                        'timestamp' => now()
                                                                    ]);
                                                                    $jalaliDate = \Carbon\Carbon::parse($assessment->created_at)->format('Y/m/d');
                                                                }
                                                            @endphp
                                                            {{ $jalaliDate }}
                                                        </td>
                                                        <td>{{ round($assessment->final_score, 2) }}</td>
                                                        <td>
                                                            <a href="{{ route('report.analysis', ['group_id' => $assessment->assessment_group_id]) }}" class="btn btn-sm btn-success">گزارش تحلیلی</a>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('assessment.report', ['assessment_group_id' => $assessment->assessment_group_id]) }}" class="btn btn-sm btn-info" onclick="console.log('Generated URL: ' + '{{ route('assessment.report', ['assessment_group_id' => $assessment->assessment_group_id]) }}')">
                                                                مشاهده جزئیات گزارش
                                                            </a>
                                                        </td>
                                                        <td>
                                                            @if($index < $assessmentHistory->count() - 1)
                                                                <a href="{{ route('assessment.compare', ['group_id' => $assessment->assessment_group_id]) }}" class="btn btn-sm btn-warning">مقایسه با قبلی</a>
                                                            @else
                                                                <button class="btn btn-sm btn-warning" disabled>مقایسه ممکن نیست</button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Non-Tech Assessments Tab -->
                                <div class="tab-pane fade {{ $activeTab == 'non-tech-assessments' ? 'show active' : '' }}" id="non-tech-assessments" role="tabpanel" aria-labelledby="non-tech-assessments-tab">
                                    <h4>نتایج آخرین ارزیابی غیر فنی</h4>
                                    <hr style="margin: 10px 0; border-top: 1px solid #ccc;">
                                    @if(!$nonTechData)
                                        <p class="text-center text-danger">هیچ ارزیابی غیر فنی‌ای ثبت نشده است.</p>
                                    @else
                                        <!-- جدول اول: اطلاعات کلی -->
                                        <h5>اطلاعات کلی</h5>
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>عنوان</th>
                                                    <th>مقدار</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr><td>تعداد کاربران فعال</td><td>{{ $nonTechData->active_users ?? 'نامشخص' }}</td></tr>
                                                <tr><td>تعداد ایستگاه‌های کاری</td><td>{{ $nonTechData->workstations ?? 'نامشخص' }}</td></tr>
                                                <tr><td>تعداد نفرات تمام‌وقت فناوری اطلاعات</td><td>{{ $nonTechData->full_time_it_staff ?? 'نامشخص' }}</td></tr>
                                                <tr><td>تعداد نفرات مشاور یا پاره‌وقت فناوری</td><td>{{ $nonTechData->part_time_it_staff ?? 'نامشخص' }}</td></tr>
                                            </tbody>
                                        </table>
                                        <hr style="margin: 10px 0; border-top: 1px solid #ccc;">
                                        <!-- جدول دوم: اطلاعات وابسته به سال -->
                                        <h5>اطلاعات وابسته به سال (سال: {{ $nonTechData->year ? \Morilog\Jalali\Jalalian::forge($nonTechData->year . '-07-01')->format('Y') : 'نامشخص' }})</h5>
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>عنوان</th>
                                                    <th>مقدار</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr><td>مبلغ بودجه IT (ریال)</td><td>{{ $nonTechData->it_budget ? number_format($nonTechData->it_budget, 0, '.', '.') : 'نامشخص' }}</td></tr>
                                                <tr><td>مبلغ هزینه‌کرد IT (ریال)</td><td>{{ $nonTechData->it_expenditure ? number_format($nonTechData->it_expenditure, 0, '.', '.') : 'نامشخص' }}</td></tr>
                                                <tr><td>تعداد حضور در رویدادهای داخلی</td><td>{{ $nonTechData->internal_events ?? 'نامشخص' }}</td></tr>
                                                <tr><td>تعداد حضور در رویدادهای خارجی</td><td>{{ $nonTechData->external_events ?? 'نامشخص' }}</td></tr>
                                                <tr><td>مجموع ساعات آموزش برای نفرات IT</td><td>{{ $nonTechData->it_training_hours ?? 'نامشخص' }}</td></tr>
                                                <tr><td>مجموع ساعات آموزش فناوری برای سایر کارکنان</td><td>{{ $nonTechData->general_training_hours ?? 'نامشخص' }}</td></tr>
                                            </tbody>
                                        </table>
                                        <hr style="margin: 10px 0; border-top: 1px solid #ccc;">
                                        <!-- جدول سوم: لیست پرسنل فناوری اطلاعات -->
                                        <h5>لیست پرسنل فناوری اطلاعات</h5>
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>نام و نام خانوادگی</th>
                                                    <th>تحصیلات</th>
                                                    <th>تخصص</th>
                                                    <th>سمت سازمانی</th>
                                                    <th>سابقه کار (سال)</th>
                                                    <th>دوره‌های تخصصی</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($itPersonnel->isEmpty())
                                                    <tr>
                                                        <td colspan="6" class="text-center">هیچ پرسنلی ثبت نشده است.</td>
                                                    </tr>
                                                @else
                                                    @foreach($itPersonnel as $person)
                                                        <tr>
                                                            <td>{{ $person->full_name }}</td>
                                                            <td>{{ $person->education }}</td>
                                                            <td>
                                                                @php
                                                                    $expertises = is_string($person->expertise) ? json_decode($person->expertise, true) : (is_array($person->expertise) ? $person->expertise : []);
                                                                    echo is_array($expertises) && !empty($expertises) ? implode(', ', $expertises) : 'نامشخص';
                                                                @endphp
                                                            </td>
                                                            <td>{{ $person->position }}</td>
                                                            <td>{{ $person->work_experience }}</td>
                                                            <td>{{ $person->training_courses ?? 'نامشخص' }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    @endif
                                </div>

                                <!-- Subsidiary Assessments Tab -->
                                <div class="tab-pane fade {{ $activeTab == 'subsidiary-assessments' ? 'show active' : '' }}" id="subsidiary-assessments" role="tabpanel" aria-labelledby="subsidiary-assessments-tab">
                                    <h4>نتایج ارزیابی فنی شرکت‌های زیرمجموعه</h4>


<!-- Filter Form -->
<div class="card mb-3">
    <div class="card-body">
        <h5>فیلتر شرکت‌ها</h5>
        <form method="GET" action="{{ route('profile') }}" id="filter-subsidiary-assessments-form">
            @csrf
            <input type="hidden" name="active_tab" value="subsidiary-assessments">
            <div class="row g-3 align-items-center">
                <!-- فیلتر سطح سلسله‌مراتب -->
                <div class="col-md-4">
                    <label class="form-label">سطح سلسله‌مراتب</label>
                    <select class="form-select" name="hierarchy_level" id="hierarchy_level" onchange="this.form.submit();">
                        <option value="" {{ !request('hierarchy_level') ? 'selected' : '' }}>همه سطوح</option>
                        <option value="1" {{ request('hierarchy_level') == '1' ? 'selected' : '' }}>مستقیم (سطح 1)</option>
                        <option value="2" {{ request('hierarchy_level') == '2' ? 'selected' : '' }}>غیرمستقیم (سطح 2)</option>
                        <option value="3" {{ request('hierarchy_level') == '3' ? 'selected' : '' }}>غیرمستقیم (سطح 3)</option>
                        <option value="4" {{ request('hierarchy_level') == '4' ? 'selected' : '' }}>غیرمستقیم (سطح 4)</option>
                    </select>
                </div>
                <!-- فیلتر هلدینگ -->
                <div class="col-md-4">
                    <label class="form-label">هلدینگ</label>
                    <select class="form-select" name="holding_id" id="holding_id" onchange="this.form.submit();">
                        <option value="" {{ !request('holding_id') ? 'selected' : '' }}>همه هلدینگ‌ها</option>
                        @foreach($holdings as $holding)
                            <option value="{{ $holding->id }}" {{ request('holding_id') == $holding->id ? 'selected' : '' }}>{{ $holding->company_alias }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- فیلتر نوع شرکت -->
                <div class="col-md-4">
                    <label class="form-label">نوع شرکت</label>
                    <select class="form-select" name="company_type" id="company_type" onchange="this.form.submit();">
                        <option value="" {{ !request('company_type') ? 'selected' : '' }}>همه انواع</option>
                        @foreach(['تولیدی', 'پخش', 'دانشگاهی', 'پروژه‌ای', 'خدماتی', 'تحقیقاتی', 'بانکی', 'سرمایه‌گذاری'] as $type)
                            <option value="{{ $type }}" {{ request('company_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Filter Status -->
<div class="alert alert-info mb-3">
    نمایش شرکت‌ها
    @php
        $filters = [];
        if (request('hierarchy_level')) {
            $filters[] = 'در سطح سلسله‌مراتب: ' . (request('hierarchy_level') == '1' ? 'مستقیم' : 'غیرمستقیم (سطح ' . request('hierarchy_level') . ')');
        }
        if (request('holding_id')) {
            $filters[] = 'زیرمجموعه هلدینگ: ' . ($holdings->firstWhere('id', request('holding_id'))->company_alias ?? 'نامشخص');
        }
        if (request('company_type')) {
            $filters[] = 'با نوع شرکت: ' . request('company_type');
        }
        echo $filters ? implode(' و ', $filters) : 'بدون فیلتر';
    @endphp
</div>


                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ردیف</th>
                                                <th>نام شرکت</th>
                                                <th>تاریخ انجام</th>
                                                <th>امتیاز نهایی</th>
                                                <th>گزارش تحلیلی</th>
                                                <th>جزئیات گزارش</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if($subsidiaryAssessments->isEmpty())
                                                <tr>
                                                    <td colspan="6" class="text-center">هیچ ارزیابی‌ای برای شرکت‌های زیرمجموعه ثبت نشده است.</td>
                                                </tr>
                                            @else
                                                @foreach($subsidiaryAssessments as $index => $assessment)
                                                    <tr>
                                                        <td>{{ $subsidiaryAssessments->count() - $index }}</td>
                                                        <td>{{ $assessment->user->company_alias ?? 'نامشخص' }}</td>
                                                        <td>
                                                            @php
                                                                $jalaliDate = 'نامشخص';
                                                                try {
                                                                    $jalaliDate = \Morilog\Jalali\Jalalian::fromDateTime($assessment->created_at)->format('j F Y');
                                                                } catch (\Exception $e) {
                                                                    \Log::error('Failed to convert assessment date to Jalali', [
                                                                        'user_id' => $assessment->user_id,
                                                                        'assessment_group_id' => $assessment->assessment_group_id,
                                                                        'miladi_date' => $assessment->created_at->format('Y-m-d'),
                                                                        'error' => $e->getMessage(),
                                                                        'timestamp' => now()
                                                                    ]);
                                                                    $jalaliDate = \Carbon\Carbon::parse($assessment->created_at)->format('Y/m/d');
                                                                }
                                                            @endphp
                                                            {{ $jalaliDate }}
                                                        </td>
                                                        <td>{{ round($assessment->final_score, 2) }}</td>
                                                        <td>
                                                            <a href="{{ route('report.analysis', ['group_id' => $assessment->assessment_group_id]) }}" class="btn btn-sm btn-success">گزارش تحلیلی</a>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('assessment.report', ['assessment_group_id' => $assessment->assessment_group_id]) }}" class="btn btn-sm btn-info">مشاهده جزئیات گزارش</a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Subsidiary Non-Tech Assessments Tab -->
                                <div class="tab-pane fade {{ $activeTab == 'subsidiary-non-tech-assessments' ? 'show active' : '' }}" id="subsidiary-non-tech-assessments" role="tabpanel" aria-labelledby="subsidiary-non-tech-assessments-tab">
                                    <h4>نتایج تجمیعی ارزیابی غیر فنی شرکت‌های زیرمجموعه</h4>
                                    
                                    @if(!isset($aggregatedNonTech) || $aggregatedNonTech->active_users == 0)
                                        <p class="text-center text-danger">هیچ ارزیابی غیر فنی‌ای برای شرکت‌های زیرمجموعه ثبت نشده است.</p>
                                    @else
                                        <hr style="margin: 10px 0; border-top: 1px solid #ccc;">
                                        <!-- جدول اول: اطلاعات کلی (تجمیعی) -->
                                        <h5>اطلاعات کلی (تجمیعی)</h5>
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>عنوان</th>
                                                    <th>مقدار تجمیعی</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr><td>تعداد کاربران فعال</td><td>{{ $aggregatedNonTech->active_users ?? 'نامشخص' }}</td></tr>
                                                <tr><td>تعداد ایستگاه‌های کاری</td><td>{{ $aggregatedNonTech->workstations ?? 'نامشخص' }}</td></tr>
                                                <tr><td>تعداد نفرات تمام‌وقت فناوری اطلاعات</td><td>{{ $aggregatedNonTech->full_time_it_staff ?? 'نامشخص' }}</td></tr>
                                                <tr><td>تعداد نفرات مشاور یا پاره‌وقت فناوری</td><td>{{ $aggregatedNonTech->part_time_it_staff ?? 'نامشخص' }}</td></tr>
                                            </tbody>
                                        </table>
                                        <hr style="margin: 10px 0; border-top: 1px solid #ccc;">
                                        <!-- جدول دوم: اطلاعات وابسته به سال (تجمیعی) -->
                                        <h5>اطلاعات وابسته به سال (تجمیعی - سال: {{ $aggregatedNonTech->year ? \Morilog\Jalali\Jalalian::fromDateTime(\Carbon\Carbon::createFromFormat('Y', $aggregatedNonTech->year))->format('Y') : 'نامشخص' }})</h5>
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>عنوان</th>
                                                    <th>مقدار تجمیعی</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr><td>مبلغ بودجه IT (ریال)</td><td>{{ $aggregatedNonTech->it_budget ? number_format($aggregatedNonTech->it_budget, 0, '.', '.') : 'نامشخص' }}</td></tr>
                                             <tr><td>مبلغ هزینه‌کرد IT (ریال)</td><td>{{ $aggregatedNonTech->it_expenditure ? number_format($aggregatedNonTech->it_expenditure, 0, '.', '.') : 'نامشخص' }}</td></tr>
                                                <tr><td>تعداد حضور در رویدادهای داخلی</td><td>{{ $aggregatedNonTech->internal_events ?? 'نامشخص' }}</td></tr>
                                                <tr><td>تعداد حضور در رویدادهای خارجی</td><td>{{ $aggregatedNonTech->external_events ?? 'نامشخص' }}</td></tr>
                                                <tr><td>مجموع ساعات آموزش برای نفرات IT</td><td>{{ $aggregatedNonTech->it_training_hours ?? 'نامشخص' }}</td></tr>
                                                <tr><td>مجموع ساعات آموزش فناوری برای سایر کارکنان</td><td>{{ $aggregatedNonTech->general_training_hours ?? 'نامشخص' }}</td></tr>
                                            </tbody>
                                        </table>
                                        <hr style="margin: 10px 0; border-top: 1px solid #ccc;">
                                      <!-- جدول سوم: لیست پرسنل فناوری اطلاعات (تمام شرکت‌های زیرمجموعه) -->
<h5>لیست پرسنل فناوری اطلاعات (تمام شرکت‌های زیرمجموعه)</h5>
<table class="table table-striped">
    <thead>
        <tr>
            <th>نام شرکت</th>
            <th>نام و نام خانوادگی</th>
            <th>تحصیلات</th>
            <th>تخصص</th>
            <th>سمت سازمانی</th>
            <th>سابقه کار (سال)</th>
            <th>دوره‌های تخصصی</th>
        </tr>
    </thead>
    <tbody>
        @if($allSubsidiaryItPersonnel->isEmpty())
            <tr>
                <td colspan="7" class="text-center">هیچ پرسنلی در شرکت‌های زیرمجموعه ثبت نشده است.</td>
            </tr>
        @else
            @foreach($allSubsidiaryItPersonnel as $person)
                <tr>
                    <td>{{ $person->user->company_alias ?? 'نامشخص (ID پرسنل: ' . $person->id . ')' }}</td>
                    <td>{{ $person->full_name }}</td>
                    <td>{{ $person->education }}</td>
                    <td>
                        @php
                            $expertises = is_string($person->expertise) ? json_decode($person->expertise, true) : (is_array($person->expertise) ? $person->expertise : []);
                            echo is_array($expertises) && !empty($expertises) ? implode(', ', $expertises) : 'نامشخص';
                        @endphp
                    </td>
                    <td>{{ $person->position }}</td>
                    <td>{{ $person->work_experience }}</td>
                    <td>{{ $person->training_courses ?? 'نامشخص' }}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
                                    @endif
                                </div>

                                <!-- Compare Companies Tab -->
                                <div class="tab-pane fade {{ $activeTab == 'compare-companies' ? 'show active' : '' }}" id="compare-companies" role="tabpanel" aria-labelledby="compare-companies-tab">
                                    <h4>مقایسه شرکت‌های زیرمجموعه</h4>

                                    <!-- Filter Form -->
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5>فیلتر شرکت‌ها</h5>
                                            <form method="GET" action="{{ route('profile') }}" id="filter-companies-form">
                                                @csrf
                                                <input type="hidden" name="active_tab" value="compare-companies">
                                                <div class="row g-3 align-items-center">
                                                    <!-- فیلتر سطح سلسله‌مراتب -->
                                                    <div class="col-md-4">
                                                        <label class="form-label">سطح سلسله‌مراتب</label>
                                                        <select class="form-select" name="hierarchy_level" id="hierarchy_level" onchange="this.form.submit();">
                                                            <option value="" {{ !request('hierarchy_level') ? 'selected' : '' }}>همه سطوح</option>
                                                            <option value="1" {{ request('hierarchy_level') == '1' ? 'selected' : '' }}>مستقیم (سطح 1)</option>
                                                            <option value="2" {{ request('hierarchy_level') == '2' ? 'selected' : '' }}>غیرمستقیم (سطح 2)</option>
                                                            <option value="3" {{ request('hierarchy_level') == '3' ? 'selected' : '' }}>غیرمستقیم (سطح 3)</option>
                                                            <option value="4" {{ request('hierarchy_level') == '4' ? 'selected' : '' }}>غیرمستقیم (سطح 4)</option>
                                                        </select>
                                                    </div>
                                                    <!-- فیلتر هلدینگ -->
                                                    <div class="col-md-4">
                                                        <label class="form-label">هلدینگ</label>
                                                        <select class="form-select" name="holding_id" id="holding_id" onchange="this.form.submit();">
                                                            <option value="" {{ !request('holding_id') ? 'selected' : '' }}>همه هلدینگ‌ها</option>
                                                            @foreach($holdings as $holding)
                                                                <option value="{{ $holding->id }}" {{ request('holding_id') == $holding->id ? 'selected' : '' }}>{{ $holding->company_alias }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <!-- فیلتر نوع شرکت -->
                                                    <div class="col-md-4">
                                                        <label class="form-label">نوع شرکت</label>
                                                        <select class="form-select" name="company_type" id="company_type" onchange="this.form.submit();">
                                                            <option value="" {{ !request('company_type') ? 'selected' : '' }}>همه انواع</option>
                                                            @foreach(['تولیدی', 'پخش', 'دانشگاهی', 'پروژه‌ای', 'خدماتی', 'تحقیقاتی', 'بانکی', 'سرمایه‌گذاری'] as $type)
                                                                <option value="{{ $type }}" {{ request('company_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Filter Status -->
                                    <div class="alert alert-info mb-3">
                                        نمایش شرکت‌ها
                                        @php
                                            $filters = [];
                                            if (request('hierarchy_level')) {
                                                $filters[] = 'در سطح سلسله‌مراتب: ' . (request('hierarchy_level') == '1' ? 'مستقیم' : 'غیرمستقیم (سطح ' . request('hierarchy_level') . ')');
                                            }
                                            if (request('holding_id')) {
                                                $filters[] = 'زیرمجموعه هلدینگ: ' . ($holdings->firstWhere('id', request('holding_id'))->company_alias ?? 'نامشخص');
                                            }
                                            if (request('company_type')) {
                                                $filters[] = 'با نوع شرکت: ' . request('company_type');
                                            }
                                            echo $filters ? implode(' و ', $filters) : 'بدون فیلتر';
                                        @endphp
                                    </div>

                                    <!-- Comparison Form -->
                                    <form method="POST" action="{{ route('compare.companies') }}" id="compare-companies-form">
                                        @csrf
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>انتخاب</th>
                                                    <th>نام شرکت</th>
                                                    <th>سطح سلسله‌مراتب</th>
                                                    <th>تاریخ آخرین ارزیابی</th>
                                                    <th>امتیاز آخرین ارزیابی</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($companiesForComparison->isEmpty())
                                                    <tr>
                                                        <td colspan="5" class="text-center">هیچ شرکتی برای مقایسه وجود ندارد.</td>
                                                    </tr>
                                                @else
                                                    @foreach($companiesForComparison as $company)
                                                        <tr>
                                                            <td>
                                                                <input type="checkbox" name="company_ids[]" value="{{ $company['id'] }}">
                                                            </td>
                                                            <td>{{ $company['company_alias'] }}</td>
                                                            <td>
                                                                @php
                                                                    $level = 0;
                                                                    $current = \App\Models\User::find($company['id']);
                                                                    while ($current && $current->parent_id) {
                                                                        $level++;
                                                                        $current = \App\Models\User::find($current->parent_id);
                                                                    }
                                                                @endphp
                                                                {{ $level == 1 ? 'مستقیم' : 'غیرمستقیم (سطح ' . $level . ')' }}
                                                            </td>
                                                            <td>{{ $company['latest_assessment_date'] }}</td>
                                                            <td>{{ $company['latest_assessment_score'] ?? 'نامشخص' }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                        <button type="submit" form="compare-companies-form" class="btn btn-primary mt-3">مقایسه شرکت‌های انتخاب‌شده</button>
                                    </form>
                                </div>

                                <!-- Compare Non-Tech Companies Tab -->
                                <div class="tab-pane fade {{ $activeTab == 'compare-non-tech-companies' ? 'show active' : '' }}" id="compare-non-tech-companies" role="tabpanel" aria-labelledby="compare-non-tech-companies-tab">
                                    <h4>مقایسه غیر فنی شرکت‌های زیرمجموعه</h4>

                                    <!-- Filter Form -->
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5>فیلتر شرکت‌ها</h5>
                                            <form method="GET" action="{{ route('profile') }}" id="filter-non-tech-companies-form">
                                                @csrf
                                                <input type="hidden" name="active_tab" value="compare-non-tech-companies">
                                                <div class="row g-3 align-items-center">
                                                    <!-- فیلتر سطح سلسله‌مراتب -->
                                                    <div class="col-md-4">
                                                        <label class="form-label">سطح سلسله‌مراتب</label>
                                                        <select class="form-select" name="hierarchy_level" id="hierarchy_level" onchange="this.form.submit();">
                                                            <option value="" {{ !request('hierarchy_level') ? 'selected' : '' }}>همه سطوح</option>
                                                            <option value="1" {{ request('hierarchy_level') == '1' ? 'selected' : '' }}>مستقیم (سطح 1)</option>
                                                            <option value="2" {{ request('hierarchy_level') == '2' ? 'selected' : '' }}>غیرمستقیم (سطح 2)</option>
                                                            <option value="3" {{ request('hierarchy_level') == '3' ? 'selected' : '' }}>غیرمستقیم (سطح 3)</option>
                                                            <option value="4" {{ request('hierarchy_level') == '4' ? 'selected' : '' }}>غیرمستقیم (سطح 4)</option>
                                                        </select>
                                                    </div>
                                                    <!-- فیلتر هلدینگ -->
                                                    <div class="col-md-4">
                                                        <label class="form-label">هلدینگ</label>
                                                        <select class="form-select" name="holding_id" id="holding_id" onchange="this.form.submit();">
                                                            <option value="" {{ !request('holding_id') ? 'selected' : '' }}>همه هلدینگ‌ها</option>
                                                            @foreach($holdings as $holding)
                                                                <option value="{{ $holding->id }}" {{ request('holding_id') == $holding->id ? 'selected' : '' }}>{{ $holding->company_alias }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <!-- فیلتر نوع شرکت -->
                                                    <div class="col-md-4">
                                                        <label class="form-label">نوع شرکت</label>
                                                        <select class="form-select" name="company_type" id="company_type" onchange="this.form.submit();">
                                                            <option value="" {{ !request('company_type') ? 'selected' : '' }}>همه انواع</option>
                                                            @foreach(['تولیدی', 'پخش', 'دانشگاهی', 'پروژه‌ای', 'خدماتی', 'تحقیقاتی', 'بانکی', 'سرمایه‌گذاری'] as $type)
                                                                <option value="{{ $type }}" {{ request('company_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Filter Status -->
                                    <div class="alert alert-info mb-3">
                                        نمایش شرکت‌ها
                                        @php
                                            $filters = [];
                                            if (request('hierarchy_level')) {
                                                $filters[] = 'در سطح سلسله‌مراتب: ' . (request('hierarchy_level') == '1' ? 'مستقیم' : 'غیرمستقیم (سطح ' . request('hierarchy_level') . ')');
                                            }
                                            if (request('holding_id')) {
                                                $filters[] = 'زیرمجموعه هلدینگ: ' . ($holdings->firstWhere('id', request('holding_id'))->company_alias ?? 'نامشخص');
                                            }
                                            if (request('company_type')) {
                                                $filters[] = 'با نوع شرکت: ' . request('company_type');
                                            }
                                            echo $filters ? implode(' و ', $filters) : 'بدون فیلتر';
                                        @endphp
                                    </div>

                                    <form method="POST" action="{{ route('compare.non_tech_companies') }}">
                                        @csrf
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>انتخاب</th>
                                                    <th>نام شرکت</th>
                                                    <th>سطح سلسله‌مراتب</th>
                                                    <th>تعداد کاربران فعال</th>
                                                    <th>تعداد نفرات تمام‌وقت فناوری</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($companiesForNonTechComparison->isEmpty())
                                                    <tr>
                                                        <td colspan="5" class="text-center">هیچ شرکتی برای مقایسه وجود ندارد.</td>
                                                    </tr>
                                                @else
                                                    @foreach($companiesForNonTechComparison as $company)
                                                        <tr>
                                                            <td>
                                                                <input type="checkbox" name="company_ids[]" value="{{ $company['id'] }}">
                                                            </td>
                                                            <td>{{ $company['company_alias'] }}</td>
                                                            <td>
                                                                @php
                                                                    $level = 0;
                                                                    $current = \App\Models\User::find($company['id']);
                                                                    while ($current && $current->parent_id) {
                                                                        $level++;
                                                                        $current = \App\Models\User::find($current->parent_id);
                                                                    }
                                                                @endphp
                                                                {{ $level == 1 ? 'مستقیم' : 'غیرمستقیم (سطح ' . $level . ')' }}
                                                            </td>
                                                            <td>{{ $company['active_users'] ?? 'نامشخص' }}</td>
                                                            <td>{{ $company['full_time_it_staff'] ?? 'نامشخص' }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                        <button type="submit" class="btn btn-primary">مقایسه شرکت‌های انتخاب‌شده</button>
                                    </form>
                                </div>

@if (auth()->user()->role === 'holding')
    <!-- تب جدید: رتبه‌بندی شرکت‌ها -->
    <div class="tab-pane fade {{ $activeTab == 'ranking-companies' ? 'show active' : '' }}" id="ranking-companies" role="tabpanel" aria-labelledby="ranking-companies-tab">
        <h4>رتبه‌بندی شرکت‌های زیرمجموعه</h4>
        <hr style="margin: 10px 0; border-top: 1px solid #ccc;">
        
        <!-- جدول 5 شرکت برتر -->
        <table class="table table-striped">
    <thead>
        <tr>
            <th>رتبه</th>
            <th>نام شرکت</th>
            <th>امتیاز آخرین ارزیابی</th>
            <th>تاریخ آخرین ارزیابی</th>
        </tr>
    </thead>
    <tbody>
        @if($topCompanies->isEmpty())
            <tr>
                <td colspan="4" class="text-center">هیچ ارزیابی‌ای برای رتبه‌بندی وجود ندارد.</td>
            </tr>
        @else
            @foreach($topCompanies as $index => $company)
                <tr>
                    <td>{{ $index + 1 }}</td> <!-- رتبه: 1 تا 5 -->
                    <td>{{ $company['company_alias'] }}</td>
                    <td>{{ $company['latest_assessment_score'] ?? 'نامشخص' }}</td>
                    <td>{{ $company['latest_assessment_date'] }}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
        
        <!-- دکمه مشاهده رتبه‌بندی کامل (آبی رنگ) -->
        <a href="{{ route('profile.ranking') }}" class="btn btn-primary mt-3">مشاهده رتبه‌بندی شرکت‌ها</a>
    </div>
@endif





@if (auth()->user()->role === 'holding')
                                    <!-- تب جدید: پیش‌بینی روند نتایج -->
                                    <div class="tab-pane fade {{ $activeTab == 'forecast-trend' ? 'show active' : '' }}" id="forecast-trend" role="tabpanel" aria-labelledby="forecast-trend-tab">
                                        <h4>پیش‌بینی روند نتایج شرکت‌های زیرمجموعه</h4>

                                        <!-- Filter Form -->
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <h5>فیلتر شرکت‌ها</h5>
                                                <form method="GET" action="{{ route('profile') }}" id="filter-forecast-trend-form">
                                                    @csrf
                                                    <input type="hidden" name="active_tab" value="forecast-trend">
                                                    <div class="row g-3 align-items-center">
                                                        <!-- فیلتر سطح سلسله‌مراتب -->
                                                        <div class="col-md-4">
                                                            <label class="form-label">سطح سلسله‌مراتب</label>
                                                            <select class="form-select" name="hierarchy_level" id="hierarchy_level" onchange="this.form.submit();">
                                                                <option value="" {{ !request('hierarchy_level') ? 'selected' : '' }}>همه سطوح</option>
                                                                <option value="1" {{ request('hierarchy_level') == '1' ? 'selected' : '' }}>مستقیم (سطح 1)</option>
                                                                <option value="2" {{ request('hierarchy_level') == '2' ? 'selected' : '' }}>غیرمستقیم (سطح 2)</option>
                                                                <option value="3" {{ request('hierarchy_level') == '3' ? 'selected' : '' }}>غیرمستقیم (سطح 3)</option>
                                                                <option value="4" {{ request('hierarchy_level') == '4' ? 'selected' : '' }}>غیرمستقیم (سطح 4)</option>
                                                            </select>
                                                        </div>
                                                        <!-- فیلتر هلدینگ -->
                                                        <div class="col-md-4">
                                                            <label class="form-label">هلدینگ</label>
                                                            <select class="form-select" name="holding_id" id="holding_id" onchange="this.form.submit();">
                                                                <option value="" {{ !request('holding_id') ? 'selected' : '' }}>همه هلدینگ‌ها</option>
                                                                @foreach($holdings as $holding)
                                                                    <option value="{{ $holding->id }}" {{ request('holding_id') == $holding->id ? 'selected' : '' }}>{{ $holding->company_alias }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <!-- فیلتر نوع شرکت -->
                                                        <div class="col-md-4">
                                                            <label class="form-label">نوع شرکت</label>
                                                            <select class="form-select" name="company_type" id="company_type" onchange="this.form.submit();">
                                                                <option value="" {{ !request('company_type') ? 'selected' : '' }}>همه انواع</option>
                                                                @foreach(['تولیدی', 'پخش', 'دانشگاهی', 'پروژه‌ای', 'خدماتی', 'تحقیقاتی', 'بانکی', 'سرمایه‌گذاری'] as $type)
                                                                    <option value="{{ $type }}" {{ request('company_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Filter Status -->
                                        <div class="alert alert-info mb-3">
                                            نمایش شرکت‌ها
                                            @php
                                                $filters = [];
                                                if (request('hierarchy_level')) {
                                                    $filters[] = 'در سطح سلسله‌مراتب: ' . (request('hierarchy_level') == '1' ? 'مستقیم' : 'غیرمستقیم (سطح ' . request('hierarchy_level') . ')');
                                                }
                                                if (request('holding_id')) {
                                                    $filters[] = 'زیرمجموعه هلدینگ: ' . ($holdings->firstWhere('id', request('holding_id'))->company_alias ?? 'نامشخص');
                                                }
                                                if (request('company_type')) {
                                                    $filters[] = 'با نوع شرکت: ' . request('company_type');
                                                }
                                                echo $filters ? implode(' و ', $filters) : 'بدون فیلتر';
                                            @endphp
                                        </div>

                                        <table class="table table-striped">
    <thead>
        <tr>
            <th>ردیف</th>
            <th>نام شرکت</th>
            <th>تعداد ارزیابی‌ها</th>
            <th>تاریخ آخرین ارزیابی</th>
            <th>امتیاز نهایی</th>
            <th>کلید پیش‌بینی روند</th>
        </tr>
    </thead>
    <tbody>
        @if($companiesForComparison->isEmpty())
            <tr>
                <td colspan="6" class="text-center">هیچ شرکتی برای پیش‌بینی روند وجود ندارد.</td>
            </tr>
        @else
            @foreach($companiesForComparison as $index => $company)
                @php
                    // فرض: $company['user'] یک شیء User است (یا $company['id'] شناسه کاربر است)
                    $user = \App\Models\User::find($company['id']);
                    $assessmentCount = $user ? $user->self_assessments : 0;
                @endphp
                <tr>
                    <td>{{ $companiesForComparison->count() - $index }}</td>
                    <td>{{ $company['company_alias'] }}</td>
                    <td>{{ $assessmentCount }}</td>
                    <td>{{ $company['latest_assessment_date'] ?? 'نامشخص' }}</td>
                    <td>{{ $company['latest_assessment_score'] ?? 'نامشخص' }}</td>
                    <td>
                        <a href="{{ route('forecast.trend', ['company_id' => $company['id']]) }}"
                           class="btn btn-sm btn-primary">پیش‌بینی روند</a>
                    </td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
                                    </div>
                                @endif



<!-- تب جدید: دستیار هوش مصنوعی فیدورا -->
                                <div class="tab-pane fade {{ $activeTab == 'ai-console' ? 'show active' : '' }}" id="ai-console" role="tabpanel" aria-labelledby="ai-console-tab">
                                    <!-- محتوای تب اگر نیاز باشد، اما چون لینک است، می‌تواند خالی باشد -->
                                </div>



                                <!-- Tree View Tab -->
                                <div class="tab-pane fade {{ $activeTab == 'tree-view' ? 'show active' : '' }}" id="tree-view" role="tabpanel" aria-labelledby="tree-view-tab">
                                    <h4>نمایش درختی شرکت‌های زیرمجموعه</h4>
                                    <div id="company-tree" style="direction: rtl; text-align: right; padding: 20px;">
                                        <style>
                                            #company-tree ul {
                                                list-style-type: none;
                                                padding-right: 20px;
                                            }
                                            #company-tree li {
                                                margin: 10px 0;
                                                position: relative;
                                            }
                                            #company-tree details {
                                                margin-right: 20px;
                                            }
                                            #company-tree summary {
                                                cursor: pointer;
                                                font-weight: bold;
                                                padding: 5px;
                                            }
                                            #company-tree summary::marker {
                                                content: "➕ "; /* آیکون + برای حالت بسته */
                                            }
                                            #company-tree details[open] summary::marker {
                                                content: "➖ "; /* آیکون - برای حالت باز */
                                            }
                                            #company-tree .subsidiary {
                                                margin-right: 30px;
                                                color: #555;
                                            }
                                        </style>
                                        <ul>
                                            @forelse($tree as $node)
                                                @include('tree-node', ['node' => $node, 'level' => 0])
                                            @empty
                                                <li>هیچ شرکت یا هلدینگ زیرمجموعه‌ای یافت نشد.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Welcome Modal -->
                        <div class="modal fade" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="welcomeModalLabel">خوش آمدید!</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>برای شروع کار با سامانه ارزیابی، پیشنهاد می‌کنیم دستورالعمل راهنما را مطالعه کنید.</p>
                                        <a href="{{ asset('storage/instructions/Instruction.pdf') }}" class="btn btn-primary" target="_blank">
                                            <i class="bi bi-download me-2"></i> دانلود راهنما
                                        </a>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Toasts for Notifications -->
                        @if(session('success'))
                            <div class="toast align-items-center text-bg-success border-0 position-fixed bottom-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="d-flex">
                                    <div class="toast-body">{{ session('success') }}</div>
                                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                </div>
                            </div>
                        @endif
                        @if(session('notification'))
                            <div class="toast align-items-center text-bg-success border-0 position-fixed bottom-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="d-flex">
                                    <div class="toast-body">{{ session('notification') }}</div>
                                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                </div>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="d-flex">
                                    <div class="toast-body">{{ session('error') }}</div>
                                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Modal -->
        <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProfileModalLabel">ویرایش اطلاعات</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('update-profile') }}" id="editProfileForm">
                            @csrf
                            @method('PATCH')
                            <div class="mb-3">
                                <label for="first_name" class="form-label">نام</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="{{ auth()->user()->first_name ?? '' }}" required>
                                @error('first_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="last_name" class="form-label">نام خانوادگی</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="{{ auth()->user()->last_name ?? '' }}" required>
                                @error('last_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="mobile" class="form-label">شماره موبایل</label>
                                <input type="text" class="form-control" id="mobile" name="mobile" value="{{ auth()->user()->mobile ?? '' }}" required>
                                @error('mobile')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="national_code" class="form-label">کد ملی</label>
                                <input type="text" class="form-control" id="national_code" name="national_code" value="{{ auth()->user()->national_code ?? '' }}" required>
                                @error('national_code')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

        <!-- Floating Button Styles -->
        <style>
            .btn-floating {
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                transition: transform 0.3s ease, background-color 0.3s ease, border-color 0.3s ease;
                cursor: pointer;
            }
            .btn-floating:hover {
                transform: scale(1.15);
                background-color: #ff6200;
                border-color: #ff6200;
            }
        </style>

        <!-- JavaScript for Toasts and Tab Management -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize Toasts
                const toastElList = document.querySelectorAll('.toast');
                toastElList.forEach(toastEl => {
                    const toast = new bootstrap.Toast(toastEl, {
                        autohide: true,
                        delay: 3000
                    });
                    toast.show();
                });

                // Set Active Tab
                const activeTab = '{{ $activeTab }}';
                const tabButton = document.querySelector(`#${activeTab}-tab`);
                const tabPane = document.querySelector(`#${activeTab}`);

                if (tabButton && tabPane) {
                    document.querySelectorAll('.nav-link.active').forEach(el => {
                        el.classList.remove('active');
                        el.setAttribute('aria-selected', 'false');
                    });
                    document.querySelectorAll('.tab-pane.show.active').forEach(el => {
                        el.classList.remove('show', 'active');
                    });

                    tabButton.classList.add('active');
                    tabButton.setAttribute('aria-selected', 'true');
                    tabPane.classList.add('show', 'active');

                    window.scrollTo(0, 0);
                }

                // Show Welcome Modal for New Users
                if (!localStorage.getItem('welcomeModalShown')) {
                    const welcomeModal = new bootstrap.Modal(document.getElementById('welcomeModal'));
                    welcomeModal.show();
                    localStorage.setItem('welcomeModalShown', 'true');
                }
            });
        </script>
    </div>
@endsection