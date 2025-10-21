{{-- Categories Grid --}}
<style>
.category-card {
    border: 1px solid #7b57ff !important;
    width: 300px;
    height: 220px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px 30px;
    gap: 13px;
    position: relative;
    overflow: hidden;
    box-shadow: 2px 2px 20px rgba(0, 0, 0, 0.062);
    border-radius: 12px;
    transition: all 0.3s ease;
    margin: 0 auto;
}
.category-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #7b57ff;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.category-card:hover .category-icon {
    transform: scale(1.1);
    background-color: #9173ff;
}

.category-icon i {
    font-size: 24px;
    color: white;
}

.category-heading {
    font-size: 1.2em;
    font-weight: 800;
    color: rgb(26, 26, 26);
    text-align: center;
    margin: 0;
}

.category-description {
    text-align: center;
    font-size: 0.7em;
    font-weight: 600;
    color: rgb(99, 99, 99);
    margin: 0;
}

.button-container {
    display: flex;
    gap: 20px;
    flex-direction: row;
    position: absolute;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
}

.edit-button {
    width: 80px;
    height: 30px;
    background-color: #7b57ff;
    transition-duration: .2s;
    border: none;
    color: rgb(241, 241, 241);
    cursor: pointer;
    font-weight: 600;
    border-radius: 20px;
    font-size: 12px;
}

.delete-button {
    width: 80px;
    height: 30px;
    background-color: rgb(218, 218, 218);
    transition-duration: .2s;
    color: rgb(46, 46, 46);
    border: none;
    cursor: pointer;
    font-weight: 600;
    border-radius: 20px;
    font-size: 12px;
}

.delete-button:hover {
    background-color: #ebebeb;
    transition-duration: .2s;
}

.edit-button:hover {
    background-color: #9173ff;
    transition-duration: .2s;
}

.dropdown-toggle {
    background: none !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0;
    margin: 0;
}

.dropdown-toggle:focus {
    box-shadow: none !important;
}

.dropdown-toggle::after {
    display: none;
}

.dropdown-menu {
    border-radius: 12px;
    padding: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border: none;
}

.dropdown-item {
    border-radius: 8px;
    transition: all 0.2s ease;
    padding: 8px 12px;
}

.dropdown-item:hover {
    background-color: #f8fafc !important;
    transform: translateX(2px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .category-card {
        width: 280px;
        height: 200px;
        padding: 15px 20px;
    }
    
    .button-container {
        gap: 10px;
        bottom: 8px;
    }
    
    .edit-button, .delete-button {
        width: 70px;
        height: 28px;
        font-size: 11px;
    }
}
</style>

<div class="row" id="categories-grid">
    @forelse($testCategories as $category)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4 d-flex justify-content-center">
            <div class="category-card" data-category-id="{{ $category->id }}">
                <!-- Action buttons positioned absolutely -->
                <div class="button-container">
                    <button class="edit-button edit-category-btn" 
                            data-id="{{ $category->id }}" 
                            data-name="{{ $category->name }}">
                        {{ localize('global.edit') }}
                    </button>
                    <button class="delete-button delete-category-btn" 
                            data-id="{{ $category->id }}" 
                            data-name="{{ $category->name }}">
                        {{ localize('global.delete') }}
                    </button>
                </div>
                
                <!-- Main content centered -->
                <div class="category-icon">
                    <i class="bx bx-test-tube"></i>
                </div>
                
                <h4 class="category-heading text-primary">{{ $category->name }}</h4>
                
                <p class="category-description">{{ localize('global.laboratory_category') }}</p>
                
                <!-- Hidden ID for reference -->
                <div style="display: none;">{{ $category->id }}</div>
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
