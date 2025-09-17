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

    @if($morphModel->nutritionCares->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ localize('global.id') }}</th>
                        <th>{{ localize('global.patient_name') }}</th>
                        <th>{{ localize('global.nurse') }}</th>
                        <th>{{ localize('global.observations') }}</th>
                        <th>{{ localize('global.interventions') }}</th>
                        <th>{{ localize('global.nutrition_care_full_note') }}</th>
                        <th>{{ localize('global.date_signature') }}</th>
                        <th>{{ localize('global.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($morphModel->nutritionCares as $nutritionCare)
                        <tr>
                            <td>{{ $nutritionCare->id }}</td>
                            <td>{{ $nutritionCare->patient_name }}</td>
                            <td>{{ $nutritionCare->nurse->full_name ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $observations = [];
                                    if ($nutritionCare->cough)
                                        $observations[] = localize('global.cough');
                                    if ($nutritionCare->sound)
                                        $observations[] = localize('global.sound');
                                    if ($nutritionCare->fluid_swallowing_ability)
                                        $observations[] = localize('global.fluid_swallowing_ability');
                                    if ($nutritionCare->weight)
                                        $observations[] = localize('global.weight');
                                    if ($nutritionCare->amount_and_type_of_nutrition)
                                        $observations[] = localize('global.amount_and_type_of_nutrition');
                                    if ($nutritionCare->diarrhea)
                                        $observations[] = localize('global.diarrhea');
                                    if ($nutritionCare->heart_failure_and_kidney_disease)
                                        $observations[] = localize('global.heart_failure_and_kidney_disease');
                                    if ($nutritionCare->remaining_materials)
                                        $observations[] = localize('global.remaining_materials');
                                    if ($nutritionCare->type_of_tube)
                                        $observations[] = localize('global.type_of_tube');
                                @endphp
                                {{ implode(', ', $observations) ?: '-' }}
                            </td>
                            <td>
                                @php
                                    $interventions = [];
                                    if ($nutritionCare->constipation)
                                        $interventions[] = localize('global.constipation');
                                    if ($nutritionCare->nutrition_is_provided)
                                        $interventions[] = localize('global.nutrition_is_provided');
                                    if ($nutritionCare->mouth_hygiene)
                                        $interventions[] = localize('global.mouth_hygiene');
                                    if ($nutritionCare->oral_nutrition_advices)
                                        $interventions[] = localize('global.oral_nutrition_advices');
                                    if ($nutritionCare->voice_exercise)
                                        $interventions[] = localize('global.voice_exercise');
                                    if ($nutritionCare->swallowing_exercise)
                                        $interventions[] = localize('global.swallowing_exercise');
                                    if ($nutritionCare->aspiration_prevention_proceeded)
                                        $interventions[] = localize('global.aspiration_prevention_proceeded');
                                @endphp
                                {{ implode(', ', $interventions) ?: '-' }}
                            </td>
                            <td>
                                @if($nutritionCare->nutrition_care_full_note)
                                    <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                        title="{{ $nutritionCare->nutrition_care_full_note }}">
                                        {{ Str::limit($nutritionCare->nutrition_care_full_note, 50) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $nutritionCare->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    @can('view', $nutritionCare)
                                        <a href="{{ route('nutrition-cares.show', $nutritionCare) }}"
                                            class="btn btn-sm btn-info"
                                            title="{{ localize('global.view') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('nutrition-cares.print', $nutritionCare) }}"
                                            class="btn btn-sm btn-primary"
                                            title="{{ localize('global.print') }}" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    @endcan
                                    @can('update', $nutritionCare)
                                        <a href="{{ route('nutrition-cares.edit', $nutritionCare) }}"
                                            class="btn btn-sm btn-warning"
                                            title="{{ localize('global.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $nutritionCare)
                                        <form action="{{ route('nutrition-cares.destroy', $nutritionCare) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('{{ localize('global.are_you_sure_delete') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                title="{{ localize('global.delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-4">
            <div class="mb-3">
                <i class="bx bx-food-menu bx-lg text-muted"></i>
            </div>
            <h5 class="text-muted">{{ localize('global.no_nutrition_care_found') }}</h5>
            <p class="text-muted">{{ localize('global.add_first_nutrition_care') }}</p>
            @can('create', \App\Models\NutritionCare::class)
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#createNutritionCareModal">
                    <i class="bx bx-plus"></i> {{ localize('global.create_nutrition_care') }}
                </button>
            @endcan
        </div>
    @endif
</div>
