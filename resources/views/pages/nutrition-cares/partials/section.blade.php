<div class="col-md-12 mt-4">
    <h5 class="mb-4 p-3 bg-label-primary">
        <i class="bx bx-food-menu p-1"></i>{{ localize('global.nutrition_care') }}
    </h5>
    <div class="d-flex gap-2 mb-3">
        @can('create', \App\Models\NutritionCare::class)
            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                data-bs-target="#createNutritionCareModal">
                <i class="bx bx-plus"></i> {{ localize('global.create_nutrition_care') }}
            </button>
        @endcan
    </div>

    <!-- Nutrition Care Data Container -->
    <div id="nutrition-care-data-container">
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ localize('global.loading') }}...</span>
            </div>
            <p class="mt-2 text-muted">{{ localize('global.loading_nutrition_care_data') }}...</p>
        </div>
    </div>
</div>

{{-- Include modal partials --}}
@include('pages.nutrition-cares.partials.create-modal')
@include('pages.nutrition-cares.partials.edit-modal')
@include('pages.nutrition-cares.partials.view-modal')

{{-- Modals are now included from separate partials above --}}

<!-- Include the nutrition care JavaScript -->
<script src="{{ asset('js/nutrition-care.js') }}" defer></script>

<script>
// Configuration for NutritionCareManager
window.nutritionCareConfig = {
    morphModel: @json($morphModel),
    morphableType: '{{ str_replace('\\', '\\\\', get_class($morphModel)) }}',
    morphableId: '{{ $morphModel->id }}',
    routes: {
        index: '{{ route("nutrition-cares.index") }}',
        show: '{{ route("nutrition-cares.show", ":id") }}',
        store: '{{ route("nutrition-cares.store") }}',
        update: '{{ route("nutrition-cares.update", ":id") }}',
        destroy: '{{ route("nutrition-cares.destroy", ":id") }}',
        print: '{{ route("nutrition-cares.print", ":id") }}'
    },
    csrfToken: '{{ csrf_token() }}'
};
</script>
