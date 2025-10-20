{{-- Categories Grid --}}
<div class="row" id="categories-grid">
    @forelse($testCategories as $category)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card h-100 category-card" data-category-id="{{ $category->id }}">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0 text-truncate" title="{{ $category->name }}">
                            {{ $category->name }}
                        </h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item edit-category-btn" href="#" 
                                       data-id="{{ $category->id }}" 
                                       data-name="{{ $category->name }}">
                                        <i class="bx bx-edit me-2"></i>{{ localize('global.edit') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger delete-category-btn" href="#" 
                                       data-id="{{ $category->id }}" 
                                       data-name="{{ $category->name }}">
                                        <i class="bx bx-trash me-2"></i>{{ localize('global.delete') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="mt-auto">
                        <small class="text-muted">
                            <i class="bx bx-calendar me-1"></i>
                            {{ $category->created_at->format('M d, Y') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bx bx-folder-open display-1 text-muted"></i>
                </div>
                <h4 class="text-muted">{{ localize('global.no_categories_found') }}</h4>
                <p class="text-muted">{{ localize('global.no_categories_message') }}</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="bx bx-plus me-2"></i>{{ localize('global.add_first_category') }}
                </button>
            </div>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($testCategories->hasPages())
    <div class="d-flex justify-content-center mt-4">
        <nav aria-label="Categories pagination">
            {{ $testCategories->appends(request()->query())->links('pagination::bootstrap-4') }}
        </nav>
    </div>
@endif
