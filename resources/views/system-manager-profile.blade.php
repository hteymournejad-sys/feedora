@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card" style="direction: rtl; text-align: right;">
                <div class="card-header">پروفایل مدیر سامانه</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3" style="border-left: 2px solid #ccc; padding-left: 15px;">
                            <div class="nav flex-column nav-pills" id="systemManagerTabs" role="tablist" aria-orientation="vertical" style="text-align: left !important; direction: ltr !important;">
                                <button class="nav-link {{ $activeTab == 'create-users' ? 'active' : '' }}" id="create-users-tab" data-bs-toggle="pill" data-bs-target="#create-users" type="button" role="tab" aria-controls="create-users" aria-selected="{{ $activeTab == 'create-users' ? 'true' : 'false' }}">ایجاد کاربران</button>
                                <button class="nav-link {{ $activeTab == 'edit-users' ? 'active' : '' }}" id="edit-users-tab" data-bs-toggle="pill" data-bs-target="#edit-users" type="button" role="tab" aria-controls="edit-users" aria-selected="{{ $activeTab == 'edit-users' ? 'true' : 'false' }}">ویرایش کاربران</button>
                                <button class="nav-link {{ $activeTab == 'delete-deactivate-users' ? 'active' : '' }}" id="delete-deactivate-users-tab" data-bs-toggle="pill" data-bs-target="#delete-deactivate-users" type="button" role="tab" aria-controls="delete-deactivate-users" aria-selected="{{ $activeTab == 'delete-deactivate-users' ? 'true' : 'false' }}">حذف و غیر فعال کاربران</button>
                                <button class="nav-link {{ $activeTab == 'tree-view' ? 'active' : '' }}" id="tree-view-tab" data-bs-toggle="pill" data-bs-target="#tree-view" type="button" role="tab" aria-controls="tree-view" aria-selected="{{ $activeTab == 'tree-view' ? 'true' : 'false' }}">نمایش درختی</button>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="tab-content" id="systemManagerTabsContent">
                                <!-- تب ایجاد کاربران -->
                                <div class="tab-pane fade {{ $activeTab == 'create-users' ? 'show active' : '' }}" id="create-users" role="tabpanel" aria-labelledby="create-users-tab">
                                   
                                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createUserModal">
                                        ایجاد کاربر جدید
                                    </button>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                
                                                <th>ایمیل</th>
                                                <th>نام شرکت</th>
                                               
                                               
                                                <th>نقش</th>
                                                <th>والد</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($lastUsers as $lastUser)
                                                <tr>
                                                    
                                                    <td>{{ $lastUser->email }}</td>
                                                    <td>{{ $lastUser->company_alias }}</td>
                                                    
                                                   
                                                    <td>{{ $lastUser->role }}</td>
                                                    <td>{{ $lastUser->parent ? $lastUser->parent->company_alias : 'هیچ' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center">هیچ کاربری یافت نشد.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <!-- تب ویرایش کاربران -->
                                <div class="tab-pane fade {{ $activeTab == 'edit-users' ? 'show active' : '' }}" id="edit-users" role="tabpanel" aria-labelledby="edit-users-tab">
                                    
                                    <!-- فرم جستجو -->
                                    <form action="{{ route('system-manager.profile') }}" method="GET" class="mb-3">
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control" placeholder="جستجو بر اساس نام شرکت" value="{{ request('search') }}">
                                            <input type="hidden" name="active_tab" value="edit-users">
                                            <button type="submit" class="btn btn-primary">جستجو</button>
                                        </div>
                                    </form>
                                    <!-- نمایش نتایج جستجو -->
                                    <div class="mb-3">
                                        @forelse($allUsers as $user)
                                            <div class="card mb-2">
                                                <div class="card-body d-flex justify-content-between align-items-center">
                                                    <div>
                                                        
                                                         
                                                        <strong>نام شرکت:</strong> {{ $user->company_alias }} |
                                                        <strong>ایمیل:</strong> {{ $user->email }} 
                                                    </div>
                                                    <div>
                                                        <button type="button" class="btn btn-sm btn-primary edit-user-btn" data-user-id="{{ $user->id }}" data-user-data='{{ json_encode([
                                                          
                                                            
                                                            "email" => $user->email,
                                                            "company_alias" => $user->company_alias, 
                                                            "company_type" => is_array($user->company_type) ? $user->company_type : (json_decode($user->company_type ?? '[]', true) ?: []),
                                                            "company_size" => $user->company_size,
                                                            "role" => $user->role,
                                                            "parent_id" => $user->parent_id
                                                        ]) }}'>ویرایش اطلاعات</button>
                                                        <button type="button" class="btn btn-sm btn-warning edit-password-btn" data-user-id="{{ $user->id }}">ویرایش رمز عبور</button>
                                                    </div>
                                                </div>
                                                <!-- فرم ویرایش اطلاعات -->
                                                <div class="edit-user-form" id="edit-user-form-{{ $user->id }}" style="display: none; padding: 15px;">
                                                    <h5>ویرایش اطلاعات کاربر</h5>
                                                    <form action="{{ route('system-manager.update-user', $user->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                        <div class="mb-3">
                                                            <label for="edit_company_alias_{{ $user->id }}" class="form-label">نام سازمان یا شرکت *</label>
                                                            <input type="text" class="form-control" id="edit_company_alias_{{ $user->id }}" name="company_alias" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="edit_email_{{ $user->id }}" class="form-label">نام کاربری (ایمیل) *</label>
                                                            <input type="email" class="form-control" id="edit_email_{{ $user->id }}" name="email" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">نوع شرکت یا سازمان *</label>
                                                            <div class="row">
                                                                @foreach(['تولیدی', 'خدماتی', 'پخش', 'تحقیقاتی', 'دانشگاهی', 'بانکی', 'پروژه ای', 'سرمایه گذاری'] as $type)
                                                                    <div class="col-md-6">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="checkbox" name="company_type[]" value="{{ $type }}" id="edit_company_type_{{ $user->id }}_{{ $type }}">
                                                                            <label class="form-check-label" for="edit_company_type_{{ $user->id }}_{{ $type }}">{{ $type }}</label>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="edit_company_size_{{ $user->id }}" class="form-label">اندازه شرکت یا سازمان *</label>
                                                            <select class="form-control" id="edit_company_size_{{ $user->id }}" name="company_size" required>
                                                                <option value="">انتخاب کنید</option>
                                                                @foreach(['بزرگ', 'متوسط', 'کوچک'] as $size)
                                                                    <option value="{{ $size }}">{{ $size }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="edit_role_{{ $user->id }}" class="form-label">نوع کاربری *</label>
                                                            <select class="form-control" id="edit_role_{{ $user->id }}" name="role" required>
                                                                <option value="">انتخاب کنید</option>
                                                                <option value="normal">شرکتی</option>
                                                                <option value="holding">هلدینگی</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3" id="edit_parent_id_container_{{ $user->id }}">
                                                            <label for="edit_parent_id_{{ $user->id }}" class="form-label">سازمان بالادستی *</label>
                                                            <select class="form-control" id="edit_parent_id_{{ $user->id }}" name="parent_id">
                                                                <option value="">هیچ (هلدینگ اصلی)</option>
                                                                @foreach($holdings as $holding)
                                                                    <option value="{{ $holding->id }}">{{ $holding->company_alias }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                                                            <button type="button" class="btn btn-secondary cancel-edit" data-user-id="{{ $user->id }}">لغو</button>
                                                        </div>
                                                    </form>
                                                </div>
                                                <!-- فرم ویرایش رمز عبور -->
                                                <div class="edit-password-form" id="edit-password-form-{{ $user->id }}" style="display: none; padding: 15px;">
                                                    <h5>ویرایش رمز عبور</h5>
                                                    <form action="{{ route('system-manager.update-password', $user->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                        <div class="mb-3">
                                                            <label for="edit_password_{{ $user->id }}" class="form-label">رمز عبور جدید *</label>
                                                            <input type="password" class="form-control" id="edit_password_{{ $user->id }}" name="password" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="edit_password_confirmation_{{ $user->id }}" class="form-label">تکرار رمز عبور *</label>
                                                            <input type="password" class="form-control" id="edit_password_confirmation_{{ $user->id }}" name="password_confirmation" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <button type="submit" class="btn btn-primary">ذخیره رمز عبور</button>
                                                            <button type="button" class="btn btn-secondary cancel-password-edit" data-user-id="{{ $user->id }}">لغو</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-center">هیچ کاربری یافت نشد.</p>
                                        @endforelse
                                    </div>
                                </div>
                                <!-- تب حذف و غیر فعال کاربران -->
                                <div class="tab-pane fade {{ $activeTab == 'delete-deactivate-users' ? 'show active' : '' }}" id="delete-deactivate-users" role="tabpanel" aria-labelledby="delete-deactivate-users-tab">
                                   
                                    <!-- فرم جستجو -->
                                    <form action="{{ route('system-manager.profile') }}" method="GET" class="mb-3">
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control" placeholder="جستجو بر اساس نام شرکت" value="{{ request('search') }}">
                                            <input type="hidden" name="active_tab" value="delete-deactivate-users">
                                            <button type="submit" class="btn btn-primary">جستجو</button>
                                        </div>
                                    </form>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                 
                                                <th>ایمیل</th>
                                                <th>نام شرکت</th>
                                                <th>عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($allUsers as $user)
                                                <tr>
                                                    
                                                    <td>{{ $user->email }}</td>
                                                    <td>{{ $user->company_alias }}</td>
                                                    <td>
                                                        <form action="{{ route('system-manager.delete-user', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('آیا مطمئن هستید که می‌خواهید این کاربر را حذف کنید؟');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                                                        </form>
                                                        <form action="{{ route('system-manager.toggle-block-user', $user->id) }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-sm {{ $user->is_blocked ? 'btn-success' : 'btn-warning' }}">{{ $user->is_blocked ? 'فعال کردن' : 'غیرفعال کردن' }}</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">هیچ کاربری یافت نشد.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <!-- تب نمایش درختی -->
                                <div class="tab-pane fade {{ $activeTab == 'tree-view' ? 'show active' : '' }}" id="tree-view" role="tabpanel" aria-labelledby="tree-view-tab">
                                    <h4>نمایش درختی شرکت‌ها</h4>
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
                                                <li>هیچ هلدینگ یا شرکت یافت نشد.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- مودال ایجاد کاربر -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createUserModalLabel">ایجاد کاربر جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('system-manager.create-user') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="company_alias" class="form-label">نام سازمان یا شرکت *</label>
                        <input type="text" class="form-control" id="company_alias" name="company_alias" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">نام کاربری (ایمیل) *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">رمز عبور *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">تکرار رمز عبور *</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نوع شرکت یا سازمان *</label>
                        <div class="row">
                            @foreach(['تولیدی', 'خدماتی', 'پخش', 'تحقیقاتی', 'دانشگاهی', 'بانکی', 'پروژه ای', 'سرمایه گذاری'] as $type)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="company_type[]" value="{{ $type }}" id="company_type_{{ $type }}">
                                        <label class="form-check-label" for="company_type_{{ $type }}">{{ $type }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="company_size" class="form-label">اندازه شرکت یا سازمان *</label>
                        <select class="form-control" id="company_size" name="company_size" required>
                            <option value="">انتخاب کنید</option>
                            <option value="بزرگ">بزرگ</option>
                            <option value="متوسط">متوسط</option>
                            <option value="کوچک">کوچک</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">نوع کاربری *</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">انتخاب کنید</option>
                            <option value="normal">شرکتی</option>
                            <option value="holding">هلدینگی</option>
                        </select>
                    </div>
                    <div class="mb-3" id="parent_id_container">
                        <label for="parent_id" class="form-label">سازمان بالادستی *</label>
                        <select class="form-control" id="parent_id" name="parent_id">
                            <option value="">هیچ (هلدینگ اصلی)</option>
                            @foreach($holdings as $holding)
                                <option value="{{ $holding->id }}">{{ $holding->company_alias }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                    <button type="submit" class="btn btn-primary">ایجاد کاربر</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Role and parent_id validation for create user modal
    const createRoleSelect = document.getElementById('role');
    const createParentInput = document.getElementById('parent_id');

    function updateCreateParentField() {
        // برای هر دو نقش normal و holding، parent_id اختیاری است
        createParentInput.removeAttribute('required');
    }

    createRoleSelect.addEventListener('change', updateCreateParentField);
    updateCreateParentField();

    // Handle edit user buttons
    const editUserButtons = document.querySelectorAll('.edit-user-btn');
    const editPasswordButtons = document.querySelectorAll('.edit-password-btn');

    editUserButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const userData = JSON.parse(this.getAttribute('data-user-data'));

            // Hide all forms
            document.querySelectorAll('.edit-user-form, .edit-password-form').forEach(form => {
                form.style.display = 'none';
            });

            // Show the edit user form for this user
            const editForm = document.getElementById('edit-user-form-' + userId);
            editForm.style.display = 'block';

            // Populate form fields
            document.getElementById('edit_company_alias_' + userId).value = userData.company_alias;
            document.getElementById('edit_email_' + userId).value = userData.email;
            document.getElementById('edit_company_size_' + userId).value = userData.company_size || '';
            document.getElementById('edit_role_' + userId).value = userData.role || '';
            document.getElementById('edit_parent_id_' + userId).value = userData.parent_id || '';

            // Update company type checkboxes
            document.querySelectorAll('#edit-user-form-' + userId + ' input[name="company_type[]"]').forEach(checkbox => {
                checkbox.checked = userData.company_type.includes(checkbox.value);
            });

            // Update parent field requirement
            const editRoleSelect = document.getElementById('edit_role_' + userId);
            const editParentInput = document.getElementById('edit_parent_id_' + userId);
            function updateEditParentField() {
                // برای هر دو نقش normal و holding، parent_id اختیاری است
                editParentInput.removeAttribute('required');
            }
            editRoleSelect.addEventListener('change', updateEditParentField);
            updateEditParentField();
        });
    });

    // Handle edit password buttons
    editPasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');

            // Hide all forms
            document.querySelectorAll('.edit-user-form, .edit-password-form').forEach(form => {
                form.style.display = 'none';
            });

            // Show the edit password form for this user
            const editPasswordForm = document.getElementById('edit-password-form-' + userId);
            editPasswordForm.style.display = 'block';
        });
    });

    // Handle cancel buttons for edit user forms
    document.querySelectorAll('.cancel-edit').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const editForm = document.getElementById('edit-user-form-' + userId);
            editForm.style.display = 'none';
            editForm.querySelector('form').reset();
        });
    });

    // Handle cancel buttons for edit password forms
    document.querySelectorAll('.cancel-password-edit').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const editPasswordForm = document.getElementById('edit-password-form-' + userId);
            editPasswordForm.style.display = 'none';
            editPasswordForm.querySelector('form').reset();
        });
    });
});
</script>
@endsection