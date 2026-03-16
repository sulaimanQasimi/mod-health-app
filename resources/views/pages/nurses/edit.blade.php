@extends('layouts.master')

@section('title', localize('global.edit_nurse'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">{{ localize('global.edit_nurse') }}: {{ $nurse->full_name }}</h4>
                        <a href="{{ route('nurses.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ localize('global.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('nurses.update', $nurse) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <!-- User Account -->
                            <div class="col-12">
                                <h5 class="mb-3">{{ localize('global.user_account') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">{{ localize('global.link_to_user_account') }}</label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id">
                                        <option value="">{{ localize('global.nurse_without_login_access') }}</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id', $nurse->user_id) == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">{{ localize('global.give_login_access') }}</div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <!-- Personal Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ localize('global.personal_information') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">{{ localize('global.first_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" value="{{ old('first_name', $nurse->first_name) }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="last_name" class="form-label">{{ localize('global.last_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" name="last_name" value="{{ old('last_name', $nurse->last_name) }}" required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="gender" class="form-label">{{ localize('global.gender') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                        <option value="">{{ localize('global.select_gender') }}</option>
                                        <option value="male" {{ old('gender', $nurse->gender) == 'male' ? 'selected' : '' }}>{{ localize('global.male') }}</option>
                                        <option value="female" {{ old('gender', $nurse->gender) == 'female' ? 'selected' : '' }}>{{ localize('global.female') }}</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="date_of_birth" class="form-label">{{ localize('global.date_of_birth') }}</label>
                                    <input type="text" autocomplete="off" class="form-control datepicker_dari @error('date_of_birth') is-invalid @enderror" 
                                           id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $nurse->date_of_birth ? $nurse->date_of_birth->format('Y-m-d') : '') }}" readonly>
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ localize('global.contact_information') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">{{ localize('global.phone') }}</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $nurse->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">{{ localize('global.email') }}</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $nurse->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">{{ localize('global.address') }}</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="3">{{ old('address', $nurse->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <!-- Employment Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ localize('global.employment_information') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">{{ localize('global.employee_id') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('employee_id') is-invalid @enderror" 
                                           id="employee_id" name="employee_id" value="{{ old('employee_id', $nurse->employee_id) }}" required>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="branch_id" class="form-label">{{ localize('global.branch') }}</label>
                                    <select class="form-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                                        <option value="">{{ localize('global.select_branch') }}</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id', $nurse->branch_id) == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="department_id" class="form-label">{{ localize('global.department') }}</label>
                                    <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id">
                                        <option value="">{{ localize('global.select_department') }}</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id', $nurse->department_id) == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="specialization" class="form-label">{{ localize('global.specialization') }}</label>
                                    <input type="text" class="form-control @error('specialization') is-invalid @enderror" 
                                           id="specialization" name="specialization" value="{{ old('specialization', $nurse->specialization) }}" 
                                           placeholder="e.g., ICU, Pediatrics, Surgery">
                                    @error('specialization')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Work Details -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ localize('global.work_details') }}</h5>
                                
                                <div class="mb-3">
                                    <label for="shift" class="form-label">{{ localize('global.shift') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('shift') is-invalid @enderror" id="shift" name="shift" required>
                                        <option value="">{{ localize('global.select_shift') }}</option>
                                        <option value="morning" {{ old('shift', $nurse->shift) == 'morning' ? 'selected' : '' }}>{{ localize('global.morning') }}</option>
                                        <option value="evening" {{ old('shift', $nurse->shift) == 'evening' ? 'selected' : '' }}>{{ localize('global.evening') }}</option>
                                        <option value="night" {{ old('shift', $nurse->shift) == 'night' ? 'selected' : '' }}>{{ localize('global.night') }}</option>
                                    </select>
                                    @error('shift')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="employment_status" class="form-label">{{ localize('global.employment_status') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('employment_status') is-invalid @enderror" id="employment_status" name="employment_status" required>
                                        <option value="">{{ localize('global.select_status') }}</option>
                                        <option value="active" {{ old('employment_status', $nurse->employment_status) == 'active' ? 'selected' : '' }}>{{ localize('global.active') }}</option>
                                        <option value="inactive" {{ old('employment_status', $nurse->employment_status) == 'inactive' ? 'selected' : '' }}>{{ localize('global.inactive') }}</option>
                                        <option value="on_leave" {{ old('employment_status', $nurse->employment_status) == 'on_leave' ? 'selected' : '' }}>{{ localize('global.on_leave') }}</option>
                                    </select>
                                    @error('employment_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="date_of_joining" class="form-label">{{ localize('global.date_of_joining') }}</label>
                                    <input type="text" autocomplete="off" class="form-control datepicker_dari @error('date_of_joining') is-invalid @enderror" 
                                           id="date_of_joining" name="date_of_joining" value="{{ old('date_of_joining', $nurse->date_of_joining ? $nurse->date_of_joining->format('Y-m-d') : '') }}">
                                    @error('date_of_joining')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('nurses.index') }}" class="btn btn-secondary">{{ localize('global.cancel') }}</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ localize('global.update_nurse') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Persian date picker for date of birth
        var dateOfBirthValue = $('#date_of_birth').val();
        $('#date_of_birth').persianDatepicker({
            formatDate: 'YYYY-MM-DD',
            calendar: {
                persian: {
                    locale: 'en',
                    showHint: true,
                    leapYearMode: 'algorithmic'
                }
            },
            checkDate: function(unix) {
                // Set maximum date to today (date of birth cannot be in the future)
                var today = new Date();
                var todayUnix = today.getTime();
                return unix <= todayUnix;
            }
        });
        
        // Set the value if it exists
        if (dateOfBirthValue) {
            $('#date_of_birth').val(dateOfBirthValue);
        }
        
        // Set maximum date for date of joining to today
        document.getElementById('date_of_joining').max = new Date().toISOString().split('T')[0];
    });
</script>
@endpush
