@extends('layouts.master')

@section('title', localize('global.nurse_notes_management'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ localize('global.nurse_notes_management') }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('nurse-notes.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ localize('global.add_nurse_note') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

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

                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('nurse-notes.index') }}" class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control" placeholder="{{ localize('global.nurse_note_search_placeholder') }}" value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <select name="morphable_type" class="form-control">
                                        <option value="">{{ localize('global.all_types') }}</option>
                                        <option value="App\Models\UnderReview" {{ request('morphable_type') == 'App\Models\UnderReview' ? 'selected' : '' }}>{{ localize('global.under_review') }}</option>
                                        <option value="App\Models\Hospitalization" {{ request('morphable_type') == 'App\Models\Hospitalization' ? 'selected' : '' }}>{{ localize('global.hospitalization') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="nurse_id" class="form-control">
                                        <option value="">{{ localize('global.all_nurses') }}</option>
                                        @foreach($nurses as $nurse)
                                            <option value="{{ $nurse->id }}" {{ request('nurse_id') == $nurse->id ? 'selected' : '' }}>
                                                {{ $nurse->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="shift" class="form-control">
                                        <option value="">{{ localize('global.nurse_note_all_shifts') }}</option>
                                        <option value="am" {{ request('shift') == 'am' ? 'selected' : '' }}>{{ localize('global.nurse_note_am_shift') }}</option>
                                        <option value="pm" {{ request('shift') == 'pm' ? 'selected' : '' }}>{{ localize('global.nurse_note_pm_shift') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control form-control datepicker_dari pdp-el" name="date" id="date" value="{{ request('date') }}">
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.nurse_note_id') }}</th>
                                    <th>{{ localize('global.nurse_note_date') }}</th>
                                    <th>{{ localize('global.nurse_note_nurse') }}</th>
                                    <th>{{ localize('global.nurse_note_type') }}</th>
                                    <th>{{ localize('global.nurse_note_am_time') }}</th>
                                    <th>{{ localize('global.nurse_note_pm_time') }}</th>
                                    <th>{{ localize('global.nurse_note_note') }}</th>
                                    <th>{{ localize('global.nurse_note_created_by') }}</th>
                                    <th>{{ localize('global.nurse_note_created_at') }}</th>
                                    <th>{{ localize('global.nurse_note_actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($nurseNotes as $note)
                                    <tr>
                                        <td>{{ $note->id }}</td>
                                        <td>{{ $note->date ? $note->date->format('Y-m-d') : 'N/A' }}</td>
                                        <td>{{ $note->nurse->full_name ?? 'N/A' }}</td>
                                        <td>
                                            @if($note->morphable_type == 'App\Models\UnderReview')
                                                <span class="badge bg-warning">Under Review</span>
                                            @elseif($note->morphable_type == 'App\Models\Hospitalization')
                                                <span class="badge bg-info">Hospitalization</span>
                                            @else
                                                <span class="badge bg-secondary">{{ class_basename($note->morphable_type) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($note->time_am)
                                                <span class="badge bg-primary">{{ $note->time_am->format('H:i') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($note->time_pm)
                                                <span class="badge bg-primary">{{ $note->time_pm->format('H:i') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($note->note)
                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $note->note }}">
                                                    {{ Str::limit($note->note, 50) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $note->createdBy->name ?? 'N/A' }}</td>
                                        <td>{{ $note->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('nurse-notes.show', $note) }}" class="btn btn-sm btn-info" title="{{ localize('global.view_nurse_note') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('nurse-notes.edit', $note) }}" class="btn btn-sm btn-warning" title="{{ localize('global.edit_nurse_note') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('nurse-notes.destroy', $note) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ localize('global.are_you_sure_delete') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="{{ localize('global.delete_nurse_note') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">{{ localize('global.no_nurse_notes_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($nurseNotes->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $nurseNotes->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
