
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جزئیات صورتحساب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://v1.fontapi.ir/css/Vazirmatn');

        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 1.2rem;
            font-size: 1.25rem;
            font-weight: bold;
            text-align: center;
        }
        .card-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.8rem;
        }
        .card-text {
            color: #555;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }
        .inline-info {
            font-size: 0.9rem;
            color: #555;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }
        .inline-info span {
            margin-left: 0.5rem;
        }
        .table {
            background-color: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .table th, .table td {
            padding: 0.8rem;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f8f9fa;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 10px;
            padding: 0.6rem 1.8rem;
            transition: background-color 0.2s;
            float: left;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-download {
            background-color: #28a745;
            border: none;
            border-radius: 10px;
            padding: 0.6rem 1.8rem;
            transition: background-color 0.2s;
            color: white;
            float: right;
        }
        .btn-download:hover {
            background-color: #218838;
        }
        .logo-img {
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.8);
            display: block;
            margin: 0 auto;
        }
        .company-name {
            text-align: center;
            font-size: 1.1rem;
            font-weight: 700;
            color: #333;
            margin-top: 0.5rem;
            margin-bottom: 1rem;
        }
        .container {
            padding-top: 1.5rem;
            padding-bottom: 2rem;
        }
        .btn-container {
            margin-top: 1rem;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10"-->
                <div class="card" id="invoice-card">
                    <div class="card-header">جزئیات صورتحساب</div>
                    <div class="card-body">
                        <!-- کادر اطلاعات فروشنده -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title text-center">اطلاعات فروشنده</h5>
                                <img src="{{ asset('images/Logo.png') }}" alt="لوگوی فیدورا" class="logo-img" style="width: 120px; height: auto;">
                                <div class="company-name">سامانه فیدورا</div>
                                <p class="card-text text-center">آدرس: تهران - شهرک گلستان - خیابان کوهک - بلوار یاس - کوچه عبدوست - شماره یک</p>
                                <div class="inline-info">
                                    <span>کدپستی: 1493764849</span> |
                                    <span>تلفن ثابت: 44757540</span> |
                                    <span>ایمیل: info@feedora.ir</span> |
                                    <span>وب‌سایت: <a href="https://www.feedora.ir" target="_blank">www.feedora.ir</a></span>
                                </div>
                            </div>
                        </div>

                        <!-- کادر اطلاعات خریدار -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">اطلاعات خریدار</h5>
                                @if ($payment->user)
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <td><strong>نام:</strong> {{ $payment->user->first_name ?? 'ثبت نشده' }}</td>
                                                <td><strong>نام خانوادگی:</strong> {{ $payment->user->last_name ?? 'ثبت نشده' }}</td>
                                                <td><strong>شماره تماس:</strong> {{ $payment->user->mobile ?? 'ثبت نشده' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>ایمیل:</strong> {{ $payment->user->email ?? 'ثبت نشده' }}</td>
                                                <td><strong>کد ملی:</strong> {{ $payment->user->national_code ?? 'ثبت نشده' }}</td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-danger">کاربر مرتبط با این پرداخت یافت نشد.</p>
                                @endif
                            </div>
                        </div>

                        <!-- اطلاعات صورتحساب -->
                        <h4 class="mb-3">صورتحساب پرداخت</h4>
                        <table class="table table-striped table-bordered">
                            <tbody>
                                <tr>
                                    <td><strong>شماره صورتحساب:</strong></td>
                                    <td>{{ $payment->invoice_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>مبلغ واریزی:</strong></td>
                                    <td>{{ number_format($payment->amount) }} تومان</td>
                                </tr>
                                <tr>
                                    <td><strong>وضعیت پرداخت:</strong></td>
                                    <td>{{ $payment->status }}</td>
                                </tr>
                                <tr>
                                    <td><strong>تاریخ پرداخت:</strong></td>
                                    <td>{{ $payment->payment_date }}</td>
                                </tr>
                                <tr>
                                    <td><strong>شناسه پرداخت:</strong></td>
                                    <td>{{ $payment->payment_id }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="btn-container">
                            <a href="{{ route('profile') }}?active_tab=payments" class="btn btn-primary">پروفایل کاربر</a>
                            <button class="btn btn-download" onclick="downloadInvoice()">دانلود فاکتور</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        function downloadInvoice() {
            const invoiceCard = document.getElementById('invoice-card');
            html2canvas(invoiceCard, {
                scale: 2, // افزایش کیفیت تصویر
                useCORS: true, // برای لود تصاویر (مثل لوگو)
                backgroundColor: '#ffffff' // پس‌زمینه سفید برای PDF
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');
                const pdfWidth = 210; // عرض A4 در میلی‌متر
                const pdfHeight = 297; // ارتفاع A4 در میلی‌متر
                const margin = 10; // حاشیه 10 میلی‌متر
                const imgWidth = pdfWidth - 2 * margin; // عرض تصویر با حاشیه
                const imgHeight = canvas.height * imgWidth / canvas.width; // حفظ نسبت تصویر

                // اگر تصویر بلندتر از صفحه A4 باشد، مقیاس‌بندی می‌کنیم
                let finalHeight = imgHeight;
                if (imgHeight > pdfHeight - 2 * margin) {
                    finalHeight = pdfHeight - 2 * margin;
                }
                pdf.addImage(imgData, 'PNG', margin, margin, imgWidth, finalHeight);
                pdf.save('invoice-{{ $payment->invoice_number }}.pdf');
            }).catch(error => {
                console.error('Error generating invoice PDF:', error);
                alert('خطا در تولید فایل PDF صورتحساب. لطفاً دوباره امتحان کنید.');
            });
        }
    </script>
</body>
</html>