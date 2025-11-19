@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg border-0" style="direction: rtl; text-align: right; border-radius: 15px;">
                <div class="card-header bg-primary text-white text-center py-4" style="border-radius: 15px 15px 0 0;">
                    <h3 class="mb-0">فرم ارزیابی غیر فنی</h3>
                </div>
                <div class="card-body p-5">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('non-technical.store') }}" id="nonTechnicalForm">
                        @csrf
                        <div class="accordion" id="nonTechnicalAccordion">
                            <!-- بخش اطلاعات کلی -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingGeneral">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGeneral" aria-expanded="true" aria-controls="collapseGeneral">
                                        <span class="toggle-icon me-2"><i class="bi bi-dash" style="display: none;"></i></span>
                                        <span class="title-text"><i class="bi bi-info-circle me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="می‌توانید اطلاعات را در هر زمان که لازم است ویرایش کنید"></i> اطلاعات کلی</span>
                                    </button>
                                </h2>
                                <div id="collapseGeneral" class="accordion-collapse collapse show" aria-labelledby="headingGeneral" data-bs-parent="#nonTechnicalAccordion">
                                    <div class="accordion-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="active_users" class="form-label"><i class="bi bi-people me-2"></i> تعداد کاربران فعال</label>
                                                    <input type="number" class="form-control" id="active_users" name="active_users" value="{{ $nonTechData->active_users ?? '' }}" min="0">
                                                    @error('active_users')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="workstations" class="form-label"><i class="bi bi-display me-2"></i> تعداد ایستگاه‌های کاری</label>
                                                    <input type="number" class="form-control" id="workstations" name="workstations" value="{{ $nonTechData->workstations ?? '' }}" min="0">
                                                    @error('workstations')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="full_time_it_staff" class="form-label"><i class="bi bi-person-workspace me-2"></i> تعداد نفرات تمام‌وقت فناوری اطلاعات</label>
                                                    <input type="number" class="form-control" id="full_time_it_staff" name="full_time_it_staff" value="{{ $nonTechData->full_time_it_staff ?? '' }}" min="0">
                                                    @error('full_time_it_staff')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="part_time_it_staff" class="form-label"><i class="bi bi-person-check me-2"></i> تعداد نفرات مشاور یا پاره‌وقت فناوری</label>
                                                    <input type="number" class="form-control" id="part_time_it_staff" name="part_time_it_staff" value="{{ $nonTechData->part_time_it_staff ?? '' }}" min="0">
                                                    @error('part_time_it_staff')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- بخش اطلاعات وابسته به سال -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingYearly">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseYearly" aria-expanded="false" aria-controls="collapseYearly">
                                        <span class="toggle-icon me-2"><i class="bi bi-dash" style="display: none;"></i></span>
                                        <span class="title-text"><i class="bi bi-info-circle me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="لطفاً در این فیلد، اطلاعات مربوط به سال گذشته را وارد کنید"></i> اطلاعات وابسته به سال</span>
                                    </button>
                                </h2>
                                <div id="collapseYearly" class="accordion-collapse collapse" aria-labelledby="headingYearly" data-bs-parent="#nonTechnicalAccordion">
                                    <div class="accordion-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="it_budget" class="form-label"><i class="bi bi-currency-exchange me-2"></i> مبلغ بودجه IT (ریال) - سال گذشته</label>
                                                    <input type="text" class="form-control" id="it_budget" value="{{ optional($nonTechData)->it_budget ? number_format($nonTechData->it_budget, 0, '.', '.') : '' }}" placeholder="مثال: 1.500.000.000">
                                       <input type="hidden" name="it_budget" id="it_budget_hidden" value="{{ optional($nonTechData)->it_budget ?? '' }}">
                                                    @error('it_budget')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="it_expenditure" class="form-label"><i class="bi bi-wallet2 me-2"></i> مبلغ هزینه‌کرد IT (ریال) - سال گذشته</label>
                                                    <input type="text" class="form-control" id="it_expenditure" value="{{ optional($nonTechData)->it_expenditure ? number_format($nonTechData->it_expenditure, 0, '.', '.') : '' }}" placeholder="مثال: 1.500.000.000">
                             <input type="hidden" name="it_expenditure" id="it_expenditure_hidden" value="{{ optional($nonTechData)->it_expenditure ?? '' }}">
                                                    @error('it_expenditure')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="internal_events" class="form-label"><i class="bi bi-calendar-event me-2"></i> تعداد حضور در رویدادهای داخلی - سال گذشته</label>
                                                    <input type="number" class="form-control" id="internal_events" name="internal_events" value="{{ $nonTechData->internal_events ?? '' }}" min="0">
                                                    @error('internal_events')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="external_events" class="form-label"><i class="bi bi-globe me-2"></i> تعداد حضور در رویدادهای خارجی - سال گذشته</label>
                                                    <input type="number" class="form-control" id="external_events" name="external_events" value="{{ $nonTechData->external_events ?? '' }}" min="0">
                                                    @error('external_events')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="it_training_hours" class="form-label"><i class="bi bi-book me-2"></i> مجموع ساعات آموزش برای نفرات IT - سال گذشته</label>
                                                    <input type="number" class="form-control" id="it_training_hours" name="it_training_hours" value="{{ $nonTechData->it_training_hours ?? '' }}" min="0">
                                                    @error('it_training_hours')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="general_training_hours" class="form-label"><i class="bi bi-book-half me-2"></i> مجموع ساعات آموزش فناوری برای سایر کارکنان - سال گذشته</label>
                                                    <input type="number" class="form-control" id="general_training_hours" name="general_training_hours" value="{{ $nonTechData->general_training_hours ?? '' }}" min="0">
                                                    @error('general_training_hours')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- بخش لیست پرسنل فناوری اطلاعات -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingPersonnel">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePersonnel" aria-expanded="false" aria-controls="collapsePersonnel">
                                        <span class="toggle-icon me-2"><i class="bi bi-dash" style="display: none;"></i></span>
                                        <span class="title-text"><i class="bi bi-people-fill me-2"></i> لیست پرسنل فناوری اطلاعات</span>
                                    </button>
                                </h2>
                                <div id="collapsePersonnel" class="accordion-collapse collapse" aria-labelledby="headingPersonnel" data-bs-parent="#nonTechnicalAccordion">
                                    <div class="accordion-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" id="personnelTable">
                                                <thead class="table-primary">
                                                    <tr>
                                                        <th>نام و نام خانوادگی</th>
                                                        <th>تحصیلات</th>
                                                        <th>تخصص</th>
                                                        <th>سمت سازمانی</th>
                                                        <th>سابقه کار (سال)</th>
                                                        <th>دوره‌های تخصصی</th>
                                                        <th>عملیات</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($itPersonnel as $person)
                                                        <tr>
                                                            <td>
                                                                <input type="text" name="personnel[{{ $loop->index }}][full_name]" value="{{ $person->full_name ?? '' }}" class="form-control">
                                                                @error('personnel.' . $loop->index . '.full_name')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </td>
                                                            <td>
                                                                <select name="personnel[{{ $loop->index }}][education]" class="form-control">
                                                                    <option value="">انتخاب کنید</option>
                                                                    <option value="دیپلم" {{ $person->education == 'دیپلم' ? 'selected' : '' }}>دیپلم</option>
                                                                    <option value="فوق‌دیپلم" {{ $person->education == 'فوق‌دیپلم' ? 'selected' : '' }}>فوق‌دیپلم</option>
                                                                    <option value="لیسانس" {{ $person->education == 'لیسانس' ? 'selected' : '' }}>لیسانس</option>
                                                                    <option value="فوق‌لیسانس" {{ $person->education == 'فوق‌لیسانس' ? 'selected' : '' }}>فوق‌لیسانس</option>
                                                                    <option value="دکتری" {{ $person->education == 'دکتری' ? 'selected' : '' }}>دکتری</option>
                                                                    <option value="فوق‌دکتری" {{ $person->education == 'فوق‌دکتری' ? 'selected' : '' }}>فوق‌دکتری</option>
                                                                </select>
                                                                @error('personnel.' . $loop->index . '.education')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </td>
                                                            <td>
                                                                @foreach($expertises as $exp)
                                                                    <div class="form-check">
                                                                        <input type="checkbox" class="form-check-input" name="personnel[{{ $loop->parent->index }}][expertise][]" value="{{ $exp }}" {{ in_array($exp, $person->expertise ?? []) ? 'checked' : '' }}>
                                                                        <label class="form-check-label">{{ $exp }}</label>
                                                                    </div>
                                                                @endforeach
                                                                @error('personnel.' . $loop->index . '.expertise')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </td>
                                                            <td>
                                                                <input type="text" name="personnel[{{ $loop->index }}][position]" value="{{ $person->position ?? '' }}" class="form-control">
                                                                @error('personnel.' . $loop->index . '.position')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </td>
                                                            <td>
                                                                <input type="number" name="personnel[{{ $loop->index }}][work_experience]" value="{{ $person->work_experience ?? '' }}" class="form-control" min="0">
                                                                @error('personnel.' . $loop->index . '.work_experience')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </td>
                                                            <td>
                                                                <textarea name="personnel[{{ $loop->index }}][training_courses]" class="form-control" maxlength="500">{{ $person->training_courses ?? '' }}</textarea>
                                                                @error('personnel.' . $loop->index . '.training_courses')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-trash"></i> حذف</button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <button type="button" class="btn btn-primary mt-3" id="addRow"><i class="bi bi-plus-circle me-2"></i> افزودن ردیف</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-start mt-4 ps-3">
                            <button type="submit" class="btn btn-success btn-md me-4"><i class="bi bi-save me-2"></i> ذخیره تغییرات</button>
                            <a href="{{ route('profile') }}" class="btn btn-secondary btn-md"><i class="bi bi-arrow-right-circle me-2"></i> بازگشت به پروفایل</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- اضافه کردن آیکون‌های Bootstrap -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<!-- اضافه کردن JavaScript بوت‌استرپ برای Accordion و Tooltip -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #ced4da;
        padding: 10px;
    }
    .form-check {
        margin-bottom: 10px;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .btn {
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 14px;
    }
    .btn-primary, .btn-success {
        background: linear-gradient(90deg, #007bff, #00b4db);
        border: none;
    }
    .btn-primary:hover, .btn-success:hover {
        background: linear-gradient(90deg, #0056b3, #0087b8);
    }
    .btn-danger {
        background: linear-gradient(90deg, #dc3545, #e4606d);
        border: none;
    }
    .btn-danger:hover {
        background: linear-gradient(90deg, #a71d2a, #b02a37);
    }
    .btn-secondary {
        background: linear-gradient(90deg, #6c757d, #868e96);
        border: none;
    }
    .btn-secondary:hover {
        background: linear-gradient(90deg, #5a6268, #747b81);
    }
    .accordion-button {
        background-color: #f8f9fa !important;
        color: #212529 !important;
        font-weight: 500;
    }
    .accordion-button:not(.collapsed) .toggle-icon .bi-plus {
        display: none;
    }
    .accordion-button.collapsed .toggle-icon .bi-dash {
        display: none;
    }
    .accordion-button:not(.collapsed) {
        background-color: #e9ecef !important;
    }
    .toggle-icon {
        display: inline-block;
        width: 30px;
        height: 30px;
        line-height: 30px;
        text-align: center;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }
    .toggle-icon.empty {
        background-color: #ffffff;
    }
    .toggle-icon.partial {
        background-color: #ffc107;
    }
    .toggle-icon.complete {
        background-color: #28a745;
    }
    .tooltip-inner {
        background-color: #343a40;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 14px;
        max-width: 250px;
        text-align: right;
    }
    .tooltip .tooltip-arrow::before {
        border-top-color: #343a40;
    }
    .tooltip.show {
        opacity: 1;
        animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // فعال‌سازی Tooltip‌های بوت‌استرپ
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    const expertises = @json($expertises);
    let rowIndex = {{ $itPersonnel->count() }};

    // تابع برای فرمت کردن اعداد به صورت ریالی
    function formatNumber(value) {
        // حذف تمام کاراکترهای غیرعددی
        const cleanValue = value.replace(/[^0-9]/g, '');
        // فرمت کردن به صورت هر سه رقم با نقطه
        return cleanValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // تابع برای به‌روزرسانی مقدار فیلد مخفی و نمایش
    function updateNumberField(inputId, hiddenId) {
        const input = document.getElementById(inputId);
        const hiddenInput = document.getElementById(hiddenId);

        input.addEventListener('input', function() {
            const rawValue = input.value.replace(/[^0-9]/g, '');
            hiddenInput.value = rawValue; // ذخیره مقدار خام در فیلد مخفی
            input.value = formatNumber(rawValue); // نمایش مقدار فرمت‌شده
            updateAccordionStatus(); // به‌روزرسانی وضعیت آکاردئون
        });

        // جلوگیری از ورودی غیرعددی
        input.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key)) {
                e.preventDefault();
            }
        });
    }

    // اعمال فرمت به فیلدهای مبلغ بودجه و هزینه‌کرد IT
    updateNumberField('it_budget', 'it_budget_hidden');
    updateNumberField('it_expenditure', 'it_expenditure_hidden');

    // تابع برای بررسی وضعیت تکمیل فیلدها و به‌روزرسانی رنگ پس‌زمینه
    function updateAccordionStatus() {
        // بررسی بخش اطلاعات کلی
        const generalFields = [
            document.getElementById('active_users'),
            document.getElementById('workstations'),
            document.getElementById('full_time_it_staff'),
            document.getElementById('part_time_it_staff')
        ];
        const generalFilledCount = generalFields.filter(field => field.value.trim() !== '').length;
        const generalToggle = document.querySelector('#headingGeneral .toggle-icon');
        if (generalFilledCount === 0) {
            generalToggle.classList.remove('partial', 'complete');
            generalToggle.classList.add('empty');
        } else if (generalFilledCount < generalFields.length) {
            generalToggle.classList.remove('empty', 'complete');
            generalToggle.classList.add('partial');
        } else {
            generalToggle.classList.remove('empty', 'partial');
            generalToggle.classList.add('complete');
        }

        // بررسی بخش اطلاعات وابسته به سال
        const yearlyFields = [
            document.getElementById('it_budget_hidden'),
            document.getElementById('it_expenditure_hidden'),
            document.getElementById('internal_events'),
            document.getElementById('external_events'),
            document.getElementById('it_training_hours'),
            document.getElementById('general_training_hours')
        ];
        const yearlyFilledCount = yearlyFields.filter(field => field.value.trim() !== '').length;
        const yearlyToggle = document.querySelector('#headingYearly .toggle-icon');
        if (yearlyFilledCount === 0) {
            yearlyToggle.classList.remove('partial', 'complete');
            yearlyToggle.classList.add('empty');
        } else if (yearlyFilledCount < yearlyFields.length) {
            yearlyToggle.classList.remove('empty', 'complete');
            yearlyToggle.classList.add('partial');
        } else {
            yearlyToggle.classList.remove('empty', 'partial');
            yearlyToggle.classList.add('complete');
        }

        // بررسی بخش پرسنل
        const personnelRows = document.querySelectorAll('#personnelTable tbody tr');
        const personnelToggle = document.querySelector('#headingPersonnel .toggle-icon');
        if (personnelRows.length === 0) {
            personnelToggle.classList.remove('partial', 'complete');
            personnelToggle.classList.add('empty');
        } else {
            personnelToggle.classList.remove('empty', 'partial');
            personnelToggle.classList.add('complete');
        }
    }

    // تابع برای افزودن ردیف جدید به جدول پرسنل
    document.getElementById('addRow').addEventListener('click', function() {
        const tableBody = document.querySelector('#personnelTable tbody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <input type="text" name="personnel[${rowIndex}][full_name]" class="form-control">
            </td>
            <td>
                <select name="personnel[${rowIndex}][education]" class="form-control">
                    <option value="">انتخاب کنید</option>
                    <option value="دیپلم">دیپلم</option>
                    <option value="فوق‌دیپلم">فوق‌دیپلم</option>
                    <option value="لیسانس">لیسانس</option>
                    <option value="فوق‌لیسانس">فوق‌لیسانس</option>
                    <option value="دکتری">دکتری</option>
                    <option value="فوق‌دکتری">فوق‌دکتری</option>
                </select>
            </td>
            <td>
                ${expertises.map(exp => `
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="personnel[${rowIndex}][expertise][]" value="${exp}">
                        <label class="form-check-label">${exp}</label>
                    </div>
                `).join('')}
            </td>
            <td>
                <input type="text" name="personnel[${rowIndex}][position]" class="form-control">
            </td>
            <td>
                <input type="number" name="personnel[${rowIndex}][work_experience]" class="form-control" min="0">
            </td>
            <td>
                <textarea name="personnel[${rowIndex}][training_courses]" class="form-control" maxlength="500"></textarea>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-trash"></i> حذف</button>
            </td>
        `;
        tableBody.appendChild(newRow);
        rowIndex++;
        updateAccordionStatus();
    });

    // تابع برای حذف ردیف از جدول پرسنل
    document.querySelector('#personnelTable').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row') || e.target.closest('.remove-row')) {
            e.target.closest('tr').remove();
            updateAccordionStatus();
        }
    });

    // به‌روزرسانی وضعیت رنگ هنگام تغییر در فیلدها
    document.querySelectorAll('#nonTechnicalForm input, #nonTechnicalForm select, #nonTechnicalForm textarea').forEach(field => {
        field.addEventListener('input', updateAccordionStatus);
    });

    // به‌روزرسانی اولیه وضعیت رنگ هنگام لود صفحه
    updateAccordionStatus();
});
</script>
@endsection