@extends('layouts.app')

@section('content')
<div class="container" style="direction: rtl; text-align: right;">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">گزارش ارزیابی</div>
                <div class="card-body">
                    <!-- اطلاعات کلی شرکت -->
                    <div class="mb-4">
                        <h4>اطلاعات شرکت</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th>نام شرکت</th>
                                <td>{{ $company_name }}</td>
                            </tr>
                            <tr>
                                <th>نوع شرکت</th>
                                <td>{{ $company_type }}</td>
                            </tr>
                            <tr>
                                <th>اندازه شرکت</th>
                                <td>{{ $company_size }}</td>
                            </tr>
                            <tr>
                                <th>تاریخ گزارش</th>
                                <td>{{ $report_date }}</td>
                            </tr>
                            <tr>
                                <th>امتیاز نهایی</th>
                                <td>{{ round($finalScore, 2) }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- انتخاب گروه ارزیابی -->
                    <div class="mb-4">
                        <form action="{{ route('assessment.report') }}" method="GET">
                            <div class="form-group">
                                <label for="assessment_group_id">انتخاب گروه ارزیابی:</label>
                                <select name="assessment_group_id" id="assessment_group_id" class="form-control" onchange="this.form.submit()">
                                    @foreach($completedGroups as $group)
                                        <option value="{{ $group->assessment_group_id }}" {{ $group->assessment_group_id == $assessment_group_id ? 'selected' : '' }}>
                                            {{ $group->created_at->format('Y/m/d H:i') }} - امتیاز: {{ round($group->final_score, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    <!-- نمایش نمودار عملکرد حوزه‌ها -->
                    <div class="mb-4">
                        <h4>عملکرد حوزه‌ها</h4>
                        <canvas id="domainChart"></canvas>
                    </div>

                    <!-- نمایش زیرمجموعه‌ها -->
                    <div class="mb-4">
                        <h4>عملکرد زیرمجموعه‌ها</h4>
                        @foreach($subcategories as $domain => $subs)
                            <h5>{{ $domain }}</h5>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>زیرمجموعه</th>
                                        <th>عملکرد (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subs as $sub)
                                        <tr>
                                            <td>{{ $sub['name'] }}</td>
                                            <td>{{ $sub['performance'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endforeach
                    </div>

                    <!-- نقاط قوت -->
                    <div class="mb-4">
                        <h4>نقاط قوت</h4>
                        @if(count($strengths) > 0)
                            <ul>
                                @foreach($strengths as $strength)
                                    <li>{{ $strength['content'] }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>نقاط قوتی یافت نشد.</p>
                        @endif
                    </div>

                    <!-- ریسک‌ها -->
                    <div class="mb-4">
                        <h4>ریسک‌ها</h4>
                        <h5>ریسک‌های بالا</h5>
                        @if(count($highRisks) > 0)
                            <ul>
                                @foreach($highRisks as $risk)
                                    <li>{{ $risk['content'] }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>ریسک بالایی یافت نشد.</p>
                        @endif

                        <h5>ریسک‌های متوسط</h5>
                        @if(count($mediumRisks) > 0)
                            <ul>
                                @foreach($mediumRisks as $risk)
                                    <li>{{ $risk['content'] }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>ریسک متوسطی یافت نشد.</p>
                        @endif

                        <h5>ریسک‌های پایین</h5>
                        @if(count($lowRisks) > 0)
                            <ul>
                                @foreach($lowRisks as $risk)
                                    <li>{{ $risk['content'] }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>ریسک پایینی یافت نشد.</p>
                        @endif
                    </div>

                    <!-- فرصت‌های بهبود -->
                    <div class="mb-4">
                        <h4>فرصت‌های بهبود</h4>
                        @if(count($improvementOpportunities) > 0)
                            <ul>
                                @foreach($improvementOpportunities as $opportunity)
                                    <li>{{ $opportunity['content'] }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>فرصت بهبود یافت نشد.</p>
                        @endif
                    </div>

                    <!-- وضعیت در حال توسعه -->
                    <div class="mb-4">
                        <h4>وضعیت در حال توسعه</h4>
                        @if(count($developingStatus) > 0)
                            <ul>
                                @foreach($developingStatus as $status)
                                    <li>{{ $status['content'] }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>وضعیت در حال توسعه‌ای یافت نشد.</p>
                        @endif
                    </div>

                    <!-- پیشنهادات -->
                    <div class="mb-4">
                        <h4>پیشنهادات</h4>
                        @if(count($suggestions) > 0)
                            <ul>
                                @foreach($suggestions as $suggestion)
                                    <li>{{ $suggestion['content'] }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p>پیشنهادی یافت نشد.</p>
                        @endif
                    </div>

                    <!-- دکمه دانلود PDF -->
                    <div class="text-center">
                        <form action="{{ route('assessment.report.pdf') }}" method="POST">
                            @csrf
                            <input type="hidden" name="assessment_group_id" value="{{ $assessment_group_id }}">
                            <button type="submit" class="btn btn-primary">دانلود گزارش (PDF)</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('domainChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($labels),
            datasets: [{
                label: 'عملکرد (%)',
                data: @json($dataValues),
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
</script>
@endsection
@endsection