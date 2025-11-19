@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card" style="direction: rtl; text-align: right;">
                <div class="card-header">تحلیل نتایج</div>
                <div class="card-body">
                    <h5>میانگین کل امتیاز: {{ number_format($totalScore, 2) }}</h5>
                    <p>{{ $message }}</p>

                    <h5>امتیازات بر اساس دسته‌بندی:</h5>
                    <ul style="padding-right: 20px;">
                        @foreach ($categoryScores as $category => $score)
                            <li>{{ $category }}: {{ number_format($score, 2) }}</li>
                        @endforeach
                    </ul>

                    <div style="direction: ltr;">
                        <canvas id="categoryChart" width="400" height="200"></canvas>
                        <canvas id="pieChart" width="400" height="200" class="mt-4"></canvas>
                    </div>

                    <h5 class="mt-4">جزئیات سوالات و جواب‌ها:</h5>
                    <table class="table table-bordered" style="direction: rtl; text-align: right;">
                        <thead>
                            <tr>
                                <th>سوال</th>
                                <th>دسته‌بندی</th>
                                <th>امتیاز</th>
                                <th>توصیه</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($questionDetails as $detail)
                                <tr>
                                    <td>{{ $detail['question'] }}</td>
                                    <td>{{ $detail['category'] }}</td>
                                    <td>{{ $detail['answer'] }}</td>
                                    <td>{{ $detail['recommendation'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <a href="{{ route('questions') }}" class="btn btn-primary">بازگشت به سوالات</a>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // نمودار میله‌ای
    const ctx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'امتیاز دسته‌بندی‌ها',
                data: @json($chartData),
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        font: {
                            family: 'Vazir',
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            family: 'Vazir',
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        font: {
                            family: 'Vazir',
                        }
                    }
                }
            }
        }
    });

    // نمودار دایره‌ای
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    const pieChart = new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'توزیع امتیازات',
                data: @json($chartData),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)',
                    'rgba(255, 159, 64, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            family: 'Vazir',
                        }
                    }
                }
            }
        }
    });
</script>
@endsection