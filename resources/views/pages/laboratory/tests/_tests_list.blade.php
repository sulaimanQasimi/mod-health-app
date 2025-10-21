{{-- Tests Table --}}
<div class="table-responsive">
    <table class="table table-striped">
        <thead class="table-primary-dark">
            <tr>
                <th width="5%">#</th>
                <th width="25%">{{ localize('global.test_name') }}</th>
                <th width="20%">{{ localize('global.category') }}</th>
                <th width="15%">{{ localize('global.parameters') }}</th>
                <th width="15%">{{ localize('global.created_at') }}</th>
                <th width="20%">{{ localize('global.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($labTests as $test)
                <tr class="test-row" data-test-id="{{ $test->id }}">
                    <td>{{ $loop->iteration + ($labTests->currentPage() - 1) * $labTests->perPage() }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="bx bx-test-tube me-2 text-primary"></i>
                            <strong>{{ $test->name }}</strong>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-info">{{ $test->category->name ?? '-' }}</span>
                    </td>
                    <td>
                        <span class="badge bg-success">{{ $test->parameters->count() }} {{ localize('global.parameters') }}</span>
                    </td>
                    <td>
                        <small class="text-muted">{{ \Verta($test->created_at)->formatJalaliDatetime() }}</small>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary edit-test-btn" 
                                    data-id="{{ $test->id }}" 
                                    data-name="{{ $test->name }}" 
                                    data-category="{{ $test->category_id }}"
                                    title="{{ localize('global.edit') }}">
                                <i class="bx bx-edit"></i>
                            </button>
                            
                            <button type="button" class="btn btn-sm btn-outline-info view-parameters-btn" 
                                    data-id="{{ $test->id }}" 
                                    data-name="{{ $test->name }}"
                                    title="{{ localize('global.view_parameters') }}">
                                <i class="bx bx-list-ul"></i>
                            </button>
                            
                            <button type="button" class="btn btn-sm btn-outline-danger delete-test-btn" 
                                    data-id="{{ $test->id }}" 
                                    data-name="{{ $test->name }}"
                                    title="{{ localize('global.delete') }}">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="mb-4">
                            <i class="bx bx-test-tube display-1 text-muted"></i>
                        </div>
                        <h4 class="text-muted">{{ localize('global.no_tests_found') }}</h4>
                        <p class="text-muted">{{ localize('global.no_tests_message') }}</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                            <i class="bx bx-plus me-2"></i>{{ localize('global.add_first_test') }}
                        </button>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($labTests->hasPages())
    <div class="d-flex justify-content-center mt-4">
        <nav aria-label="Tests pagination">
            {{ $labTests->appends(request()->query())->links('pagination::bootstrap-4') }}
        </nav>
    </div>
@endif
