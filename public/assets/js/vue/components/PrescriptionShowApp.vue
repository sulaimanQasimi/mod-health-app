<template>
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
      <!-- Toast notifications -->
      <div v-if="toast.show" :class="['toast', 'show', 'position-fixed', 'top-0', 'end-0', 'p-3', toast.type === 'success' ? 'bg-success' : 'bg-danger']" style="z-index: 1055;">
        <div class="toast-body text-white">
          {{ toast.message }}
        </div>
      </div>

      <div class="col-xl">
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ localize('global.prescription_details') }}</h5>
            <div class="pt-3 pt-md-0 text-end">
              <div class="btn-group" role="group">
                <a :href="`/prescriptions/thermal-receipt/${prescriptionId}`" 
                   class="btn btn-success" target="_blank">
                  <i class="bx bx-printer"></i> {{ localize('global.thermal_print') }}
                </a>
                <a class="btn btn-danger" href="javascript:history.back()" type="button">
                  <span class="text-white">
                    <span class="d-none d-sm-inline-block">{{ localize('global.back') }}</span>
                  </span>
                </a>
              </div>
            </div>
          </div>

          <div class="card-body" v-if="!loading">
            <!-- Prescription Header Table -->
            <table class="table">
              <thead>
                <tr>
                  <th>{{ localize('global.number') }}</th>
                  <th>{{ localize('global.patient_name') }}</th>
                  <th>{{ localize('global.status') }}</th>
                  <th>{{ localize('global.actions') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>{{ prescription.id }}</td>
                  <td>{{ prescription.patient?.name }}</td>
                  <td>
                    <span v-if="prescription.is_completed == '0'" class="badge bg-danger">
                      {{ localize('global.not_delivered') }}
                    </span>
                    <span v-else class="badge bg-success">
                      {{ localize('global.delivered') }}
                    </span>
                  </td>
                  <td>
                    <div v-if="prescription.is_completed == '0'" class="d-flex justify-content-center text-center mt-2">
                      <button @click="completePrescription" 
                              class="btn btn-success" 
                              :disabled="loading"
                              title="{{ localize('global.complete_prescription') }}">
                        <span><i class="bx bx-check-shield"></i></span>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Prescription Items Table -->
            <h5 class="mb-4 p-3 bg-label-primary mt-4">
              <i class="bx bx-notepad p-1"></i>{{ localize('global.prescription_details') }}
            </h5>

            <!-- Bulk Operations Toolbar -->
            <div v-if="selectedItems.size > 0" class="alert alert-info d-flex justify-content-between align-items-center mb-3">
              <div>
                <i class="bx bx-info-circle me-2"></i>
                {{ selectedItems.size }} {{ localize('global.items_selected') }}
              </div>
              <div class="btn-group btn-group-sm">
                <button @click="bulkMarkDelivered" 
                        class="btn btn-success" 
                        :disabled="loading">
                  <i class="bx bx-check me-1"></i>
                  {{ localize('global.bulk_mark_delivered') }}
                </button>
                <button @click="bulkMarkNotDelivered" 
                        class="btn btn-warning" 
                        :disabled="loading">
                  <i class="bx bx-x me-1"></i>
                  {{ localize('global.bulk_mark_not_delivered') }}
                </button>
                <button @click="selectedItems.clear(); selectAll = false" 
                        class="btn btn-outline-secondary">
                  <i class="bx bx-x me-1"></i>
                  {{ localize('global.clear_selection') }}
                </button>
              </div>
            </div>

            
            <div class="table-responsive">
              <table class="table table-hover bg-none">
                <thead class="table-none">
                  <tr>
                    <th width="50" class="d-none d-md-table-cell">
                      <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               :checked="selectAll"
                               @change="toggleSelectAll"
                               :disabled="loading">
                      </div>
                    </th>
                    <th>{{ localize('global.number') }}</th>
                    <th class="d-none d-lg-table-cell">{{ localize('global.type') }}</th>
                    <th>{{ localize('global.name') }}</th>
                    <th class="d-none d-md-table-cell">{{ localize('global.usage_type') }}</th>
                    <th class="d-none d-sm-table-cell">{{ localize('global.dosage') }}</th>
                    <th class="d-none d-sm-table-cell">{{ localize('global.frequency') }}</th>
                    <th class="d-none d-sm-table-cell">{{ localize('global.amount') }}</th>
                    <th>{{ localize('global.status') }}</th>
                    <th>{{ localize('global.alternatives') }}</th>
                  </tr>
                </thead>
              <tbody>
                <template v-for="(item, index) in prescription.prescription_items" :key="item.id">
                  <!-- Original Prescription Item -->
                  <tr>
                    <td class="d-none d-md-table-cell">
                      <div v-if="!item.selected_alternative" class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               :checked="selectedItems.has(item.id)"
                               @change="toggleItemSelection(item.id)"
                               :disabled="loading">
                      </div>
                      <div v-else class="text-center">
                        <i class="bx bx-info-circle text-muted"></i>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2">{{ index + 1 }}</span>
                        <i v-if="item.selected_alternative" class="bx bx-info-circle text-warning" 
                           :title="localize('global.original_not_used')"></i>
                        <i v-else-if="item.is_delivered == '1'" class="bx bx-check-circle text-success" 
                           :title="localize('global.delivered')"></i>
                        <i v-else class="bx bx-x-circle text-danger" 
                           :title="localize('global.not_delivered')"></i>
                      </div>
                    </td>
                    <td class="d-none d-lg-table-cell">
                      <span class="badge bg-info">{{ item.medicine_type?.type || '-' }}</span>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <i class="bx bx-pill me-2 text-primary"></i>
                        <div>
                          <div class="fw-semibold">{{ item.medicine?.name }}</div>
                          <span v-if="item.selected_alternative" class="badge bg-warning">
                            <i class="bx bx-info-circle me-1"></i>{{ localize('global.original') }}
                          </span>
                          <!-- Mobile view: show additional info -->
                          <div class="d-md-none mt-1">
                            <small class="text-muted">
                              <span class="badge bg-info me-1">{{ item.medicine_type?.type || '-' }}</span>
                              <span class="badge bg-secondary me-1">{{ item.usage_type?.name }}</span>
                            </small>
                            <div class="mt-1">
                              <small class="text-muted">
                                {{ localize('global.dosage') }}: {{ item.dosage }} | 
                                {{ localize('global.frequency') }}: {{ item.frequency }} | 
                                {{ localize('global.amount') }}: {{ item.amount }}
                              </small>
                            </div>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="d-none d-md-table-cell">
                      <span class="badge bg-secondary">{{ item.usage_type?.name }}</span>
                    </td>
                    <td class="d-none d-sm-table-cell">
                      <span class="fw-semibold">{{ item.dosage }}</span>
                    </td>
                    <td class="d-none d-sm-table-cell">
                      <span class="fw-semibold">{{ item.frequency }}</span>
                    </td>
                    <td class="d-none d-sm-table-cell">
                      <span class="fw-semibold">{{ item.amount }}</span>
                    </td>
                    <td>
                      <div v-if="item.selected_alternative" class="text-center">
                        <span class="badge bg-warning">
                          <i class="bx bx-x-circle me-1"></i>{{ localize('global.not_used') }}
                        </span>
                      </div>
                      <div v-else class="text-center">
                        <button @click="toggleItemStatus(item)" 
                                class="btn btn-sm"
                                :class="item.is_delivered == '0' ? 'btn-danger' : 'btn-success'"
                                :disabled="loading"
                                :title="item.is_delivered == '0' ? localize('global.mark_delivered') : localize('global.mark_not_delivered')">
                          <i :class="item.is_delivered == '1' ? 'bx bx-check' : 'bx bx-x'"></i>
                        </button>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex flex-column flex-sm-row gap-1">
                        <div class="btn-group btn-group-sm" role="group">
                          <button type="button" 
                                  class="btn btn-info" 
                                  @click="openAlternativesModal(item)"
                                  :title="localize('global.alternatives')">
                            <i class="bx bx-list-ul"></i>
                            <span class="d-none d-sm-inline ms-1">{{ localize('global.alternatives') }}</span>
                          </button>
                        </div>
                        <span v-if="item.alternative_items?.length > 0" 
                              class="badge bg-primary align-self-start">
                          {{ item.alternative_items.length }}
                        </span>
                      </div>
                    </td>
                  </tr>

                  <!-- Selected Alternative Item (if exists) -->
                  <tr v-if="item.selected_alternative">
                    <td class="d-none d-md-table-cell">
                      <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               :checked="selectedItems.has(item.selected_alternative.id)"
                               @change="toggleItemSelection(item.selected_alternative.id)"
                               :disabled="loading">
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <span class="badge bg-success me-2">{{ index + 1 }}.1</span>
                        <i v-if="item.selected_alternative.is_delivered == '1'" class="bx bx-check-circle text-success" 
                           :title="localize('global.delivered')"></i>
                        <i v-else class="bx bx-x-circle text-danger" 
                           :title="localize('global.not_delivered')"></i>
                      </div>
                    </td>
                    <td class="d-none d-lg-table-cell">
                      <span class="badge bg-info">{{ item.selected_alternative.medicine_type?.type }}</span>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <i class="bx bx-check-circle me-2 text-success"></i>
                        <div>
                          <div class="fw-semibold">{{ item.selected_alternative.medicine?.name }}</div>
                          <span class="badge bg-success">{{ localize('global.selected_alternative') }}</span>
                          <small v-if="item.selected_alternative.notes" class="text-muted d-block mt-1">
                            <i class="bx bx-note me-1"></i>{{ item.selected_alternative.notes }}
                          </small>
                          <!-- Mobile view: show additional info -->
                          <div class="d-md-none mt-1">
                            <small class="text-muted">
                              <span class="badge bg-info me-1">{{ item.selected_alternative.medicine_type?.type }}</span>
                              <span class="badge bg-secondary me-1">{{ item.selected_alternative.usage_type?.name }}</span>
                            </small>
                            <div class="mt-1">
                              <small class="text-muted">
                                {{ localize('global.dosage') }}: {{ item.selected_alternative.dosage }} | 
                                {{ localize('global.frequency') }}: {{ item.selected_alternative.frequency }} | 
                                {{ localize('global.amount') }}: {{ item.selected_alternative.amount }}
                              </small>
                            </div>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="d-none d-md-table-cell">
                      <span class="badge bg-secondary">{{ item.selected_alternative.usage_type?.name }}</span>
                    </td>
                    <td class="d-none d-sm-table-cell">
                      <span class="fw-semibold">{{ item.selected_alternative.dosage }}</span>
                    </td>
                    <td class="d-none d-sm-table-cell">
                      <span class="fw-semibold">{{ item.selected_alternative.frequency }}</span>
                    </td>
                    <td class="d-none d-sm-table-cell">
                      <span class="fw-semibold">{{ item.selected_alternative.amount }}</span>
                    </td>
                    <td>
                      <div class="text-center">
                        <button @click="toggleAlternativeStatus(item.selected_alternative)" 
                                class="btn btn-sm"
                                :class="item.selected_alternative.is_delivered == '0' ? 'btn-danger' : 'btn-success'"
                                :disabled="loading"
                                :title="item.selected_alternative.is_delivered == '0' ? localize('global.mark_delivered') : localize('global.mark_not_delivered')">
                          <i :class="item.selected_alternative.is_delivered == '1' ? 'bx bx-check' : 'bx bx-x'"></i>
                        </button>
                      </div>
                    </td>
                    <td>
                      <div class="btn-group btn-group-sm d-flex flex-column flex-sm-row" role="group">
                        <button @click="deselectAlternative(item.selected_alternative)" 
                                class="btn btn-warning fw-bold mb-1 mb-sm-0" 
                                :disabled="loading"
                                :title="localize('global.deselect_alternative')">
                          <i class="bx bx-x"></i>
                          <span class="d-none d-sm-inline ms-1">{{ localize('global.deselect') }}</span>
                        </button>
                        <button type="button" 
                                class="btn btn-info" 
                                @click="openAlternativesModal(item)"
                                :title="localize('global.alternatives')">
                          <i class="bx bx-list-ul"></i>
                          <span class="d-none d-sm-inline ms-1">{{ localize('global.alternatives') }}</span>
                        </button>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="card-body text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">{{ localize('global.loading') }}...</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Alternatives Modal -->
    <div class="modal fade" id="alternativeModal" tabindex="-1" 
         aria-labelledby="alternativeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-fullscreen-md-down">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="alternativeModalLabel">
              <i class="bx bx-list-ul me-2"></i>
              {{ localize('global.alternatives_for') }}: {{ currentItem?.medicine?.name }}
            </h5>
            <button type="button" class="btn-close" @click="closeModal" :aria-label="localize('global.close')"></button>
          </div>
          <div class="modal-body p-0">
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs nav-fill" id="alternativeTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link" 
                        :class="{ 'active': activeTab === 'add' }"
                        id="add-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#add-pane" 
                        type="button" 
                        role="tab"
                        @click="setActiveTab('add')">
                  <i class="bx bx-plus me-1"></i>
                  {{ localize('global.add_alternative') }}
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" 
                        :class="{ 'active': activeTab === 'existing' }"
                        id="existing-tab" 
                        data-bs-toggle="tab" 
                        data-bs-target="#existing-pane" 
                        type="button" 
                        role="tab"
                        @click="setActiveTab('existing')">
                  <i class="bx bx-list-ul me-1"></i>
                  {{ localize('global.existing_alternatives') }}
                  <span v-if="currentItem?.alternative_items?.length > 0" 
                        class="badge bg-primary ms-1">{{ currentItem.alternative_items.length }}</span>
                </button>
              </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="alternativeTabContent">
              <!-- Add Alternative Tab -->
              <div class="tab-pane fade" 
                   :class="{ 'show active': activeTab === 'add' }" 
                   id="add-pane" 
                   role="tabpanel">
                <div class="p-4">
                  <form @submit.prevent="addAlternative">
                    <div class="row g-3">
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">
                          <i class="bx bx-pill me-1 text-primary"></i>
                          {{ localize('global.medicine') }}
                        </label>
                        <Multiselect
                          v-model="newAlternative.medicine"
                          :options="medicines"
                          :placeholder="localize('global.select_medicine')"
                          :searchable="true"
                          :allow-empty="false"
                          :show-labels="false"
                          label="name"
                          track-by="id"
                          :required="true"
                          :loading="loading"
                          @select="onMedicineSelect"
                        >
                          <template #noOptions>
                            {{ localize('global.no_medicines_found') }}
                          </template>
                          <template #noResult>
                            {{ localize('global.no_medicines_found') }}
                          </template>
                        </Multiselect>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">
                          <i class="bx bx-category me-1 text-info"></i>
                          {{ localize('global.medicine_type') }}
                        </label>
                        <input :value="newAlternative.medicine_type?.type || ''" 
                               type="text" 
                               class="form-control" 
                               readonly
                               :placeholder="localize('global.auto_filled')">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">
                          <i class="bx bx-edit me-1 text-warning"></i>
                          {{ localize('global.usage_type') }}
                        </label>
                        <input :value="newAlternative.usage_type?.name || ''" 
                               type="text" 
                               class="form-control" 
                               readonly
                               :placeholder="localize('global.auto_filled')">
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">
                          <i class="bx bx-droplet me-1 text-success"></i>
                          {{ localize('global.dosage') }}
                        </label>
                        <input v-model="newAlternative.dosage" type="text" class="form-control" 
                               readonly
                               :placeholder="localize('global.auto_filled')">
                        <small class="text-muted">{{ localize('global.original') }}: {{ currentItem?.dosage }}</small>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">
                          <i class="bx bx-time me-1 text-danger"></i>
                          {{ localize('global.frequency') }}
                        </label>
                        <input v-model="newAlternative.frequency" type="text" class="form-control" 
                               readonly
                               :placeholder="localize('global.auto_filled')">
                        <small class="text-muted">{{ localize('global.original') }}: {{ currentItem?.frequency }}</small>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-semibold">
                          <i class="bx bx-package me-1 text-secondary"></i>
                          {{ localize('global.amount') }}
                        </label>
                        <input v-model="newAlternative.amount" type="text" class="form-control" 
                               readonly
                               :placeholder="localize('global.auto_filled')">
                        <small class="text-muted">{{ localize('global.original') }}: {{ currentItem?.amount }}</small>
                      </div>
                      <div class="col-12">
                        <label class="form-label fw-semibold">
                          <i class="bx bx-note me-1 text-dark"></i>
                          {{ localize('global.notes') }}
                        </label>
                        <textarea v-model="newAlternative.notes" class="form-control" rows="3" 
                                  :placeholder="localize('global.notes')"></textarea>
                      </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                      <div class="alert alert-info mb-0 flex-grow-1 me-3">
                        <i class="bx bx-info-circle me-2"></i>
                        {{ localize('global.select_medicine_to_auto_fill') }}
                      </div>
                      <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary" @click="copyFromOriginal">
                          <i class="bx bx-copy me-1"></i>
                          {{ localize('global.copy_from_original') }}
                        </button>
                        <button type="button" class="btn btn-primary" @click="addAlternative" :disabled="loading">
                          <i class="bx bx-plus me-1" v-if="!loading"></i>
                          <span class="spinner-border spinner-border-sm me-1" v-if="loading"></span>
                          {{ localize('global.add_alternative') }}
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>

              <!-- Existing Alternatives Tab -->
              <div class="tab-pane fade" 
                   :class="{ 'show active': activeTab === 'existing' }" 
                   id="existing-pane" 
                   role="tabpanel">
                <div class="p-4">
                  <div v-if="currentItem?.alternative_items?.length > 0" class="table-responsive">
                    <table class="table bg-none">
                      <thead class="table-none">
                        <tr>
                          <th width="30%">{{ localize('global.medicine') }}</th>
                          <th width="15%">{{ localize('global.dosage') }}</th>
                          <th width="15%">{{ localize('global.frequency') }}</th>
                          <th width="15%">{{ localize('global.amount') }}</th>
                          <th width="10%">{{ localize('global.status') }}</th>
                          <th width="15%">{{ localize('global.actions') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="alternative in currentItem?.alternative_items" :key="alternative.id">
                          <td>
                            <div class="d-flex align-items-center">
                              <i class="bx bx-pill me-2 text-primary"></i>
                              <div>
                                <div class="fw-semibold">{{ alternative.medicine?.name }}</div>
                                <small v-if="alternative.notes" class="text-muted">
                                  <i class="bx bx-note me-1"></i>{{ alternative.notes }}
                                </small>
                              </div>
                            </div>
                            <span v-if="alternative.is_selected" class="badge bg-success mt-1">
                              <i class="bx bx-check me-1"></i>{{ localize('global.selected') }}
                            </span>
                          </td>
                          <td>{{ alternative.dosage }}</td>
                          <td>{{ alternative.frequency }}</td>
                          <td>{{ alternative.amount }}</td>
                          <td>
                            <span class="badge" :class="alternative.is_delivered ? 'bg-success' : 'bg-danger'">
                              <i :class="alternative.is_delivered ? 'bx bx-check' : 'bx bx-x'" class="me-1"></i>
                              {{ alternative.is_delivered ? localize('global.delivered') : localize('global.not_delivered') }}
                            </span>
                          </td>
                          <td>
                            <div class="btn-group btn-group-sm" role="group">
                              <button v-if="!alternative.is_selected" 
                                      @click="selectAlternative(alternative)" 
                                      class="btn btn-success" 
                                      :disabled="loading"
                                      :title="localize('global.select_alternative')">
                                <i class="bx bx-check"></i>
                                <span class="d-none d-sm-inline ms-1">{{ localize('global.select') }}</span>
                              </button>
                              <button v-else
                                      @click="deselectAlternative(alternative)" 
                                      class="btn btn-warning fw-bold" 
                                      :disabled="loading"
                                      :title="localize('global.deselect_alternative')">
                                <i class="bx bx-x"></i>
                                <span class="d-none d-sm-inline ms-1">{{ localize('global.deselect') }}</span>
                              </button>
                              <button @click="toggleAlternativeStatus(alternative)" 
                                      class="btn"
                                      :class="alternative.is_delivered ? 'btn-warning' : 'btn-info'"
                                      :disabled="loading"
                                      :title="alternative.is_delivered ? localize('global.mark_not_delivered') : localize('global.mark_delivered')">
                                <i :class="alternative.is_delivered ? 'bx bx-x' : 'bx bx-check'"></i>
                              </button>
                              <button @click="deleteAlternative(alternative)" 
                                      class="btn btn-danger" 
                                      :disabled="loading"
                                      :title="localize('global.delete_alternative')">
                                <i class="bx bx-trash"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div v-else class="text-center py-5">
                    <i class="bx bx-list-ul display-1 text-muted"></i>
                    <p class="text-muted mt-3">{{ localize('global.no_alternatives_found') }}</p>
                    <button class="btn btn-primary" @click="switchToAddTab">
                      <i class="bx bx-plus me-1"></i>
                      {{ localize('global.add_alternative') }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">
              <i class="bx bx-x me-1"></i>
              {{ localize('global.close') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted, onUnmounted, computed } from 'vue'
import axios from 'axios'
import Multiselect from 'vue-multiselect'

export default {
  name: 'PrescriptionShowApp',
  components: {
    Multiselect
  },
  props: {
    prescriptionId: {
      type: [String, Number],
      required: true
    }
  },
  setup(props) {
    // Reactive data
    const prescription = ref({})
    const loading = ref(false)
    const currentItem = ref(null)
    const medicines = ref([])
    const medicineTypes = ref([])
    const medicineUsageTypes = ref([])
    const toast = reactive({
      show: false,
      message: '',
      type: 'success'
    })

    // New alternative form data
    const newAlternative = reactive({
      medicine: null,
      medicine_type: null,
      usage_type: null,
      dosage: '',
      frequency: '',
      amount: '',
      notes: ''
    })

    // Bulk operations
    const selectedItems = ref(new Set())
    const selectAll = ref(false)
    
    // Tab navigation
    const activeTab = ref('add')


    // Localization function
    const localize = (key) => {
      if (typeof window.localize === 'function') {
        return window.localize(key)
      }
      return key
    }


    // Methods
    const showToast = (message, type = 'success') => {
      toast.message = message
      toast.type = type
      toast.show = true
      setTimeout(() => {
        toast.show = false
      }, 3000)
    }

    const loadPrescriptionDetails = async () => {
      loading.value = true
      try {
        console.log('Loading prescription details for ID:', props.prescriptionId)
        if (!props.prescriptionId) {
          throw new Error('Prescription ID is required')
        }
        const response = await axios.get(`/prescription-show-ajax/prescription-details/${props.prescriptionId}`)
        if (response.data.success) {
          prescription.value = response.data.data
        } else {
          showToast(response.data.message, 'error')
        }
      } catch (error) {
        showToast(localize('global.failed_to_load_prescription_details'), 'error')
        console.error('Error loading prescription details:', error)
      } finally {
        loading.value = false
      }
    }

    const loadDropdownData = async () => {
      try {
        const [medicinesRes, typesRes, usageTypesRes] = await Promise.all([
          axios.get('/prescription-show-ajax/all-medicines'),
          axios.get('/prescription-show-ajax/medicine-types'),
          axios.get('/prescription-show-ajax/medicine-usage-types')
        ])

        if (medicinesRes.data.success) medicines.value = medicinesRes.data.data
        if (typesRes.data.success) medicineTypes.value = typesRes.data.data
        if (usageTypesRes.data.success) medicineUsageTypes.value = usageTypesRes.data.data
      } catch (error) {
        console.error('Error loading dropdown data:', error)
      }
    }

    const completePrescription = async () => {
      loading.value = true
      try {
        // First, mark all prescription items as delivered
        const itemPromises = []
        
        // Mark all original prescription items as delivered
        if (prescription.value.prescription_items) {
          prescription.value.prescription_items.forEach(item => {
            if (item.is_delivered == '0') {
              itemPromises.push(
                axios.put(`/prescription-show-ajax/update-item-status/${item.id}`, {
                  is_delivered: '1'
                })
              )
            }
            
            // Mark selected alternative as delivered if it exists
            if (item.selected_alternative && item.selected_alternative.is_delivered != '1') {
              itemPromises.push(
                axios.put(`/prescription-show-ajax/update-alternative-status/${item.selected_alternative.id}`, {
                  is_delivered: '1'
                })
              )
            }
          })
        }
        
        // Execute all item status updates
        if (itemPromises.length > 0) {
          await Promise.all(itemPromises)
        }
        
        // Then complete the prescription
        const response = await axios.put(`/prescription-show-ajax/update-prescription-status/${props.prescriptionId}`, {
          is_completed: '1'
        })
        
        if (response.data.success) {
          // Update local state
          prescription.value.is_completed = '1'
          
          // Update all items to delivered status locally
          if (prescription.value.prescription_items) {
            prescription.value.prescription_items.forEach(item => {
              item.is_delivered = '1'
              if (item.selected_alternative) {
                item.selected_alternative.is_delivered = '1'
              }
            })
          }
          
          showToast(localize('global.prescription_completed_successfully'))
        } else {
          showToast(response.data.message, 'error')
        }
      } catch (error) {
        showToast(localize('global.failed_to_complete_prescription'), 'error')
        console.error('Error completing prescription:', error)
      } finally {
        loading.value = false
      }
    }

    const toggleItemStatus = async (item) => {
      loading.value = true
      try {
        const newStatus = item.is_delivered == '0' ? '1' : '0'
        const response = await axios.put(`/prescription-show-ajax/update-item-status/${item.id}`, {
          is_delivered: newStatus
        })
        if (response.data.success) {
          item.is_delivered = newStatus
          showToast(localize('global.item_status_updated_successfully'))
        } else {
          showToast(response.data.message, 'error')
        }
      } catch (error) {
        showToast(localize('global.failed_to_update_item_status'), 'error')
        console.error('Error updating item status:', error)
      } finally {
        loading.value = false
      }
    }

    const openAlternativesModal = (item) => {
      currentItem.value = item
      // Pre-fill form with main medicine data (excluding dosage, frequency, amount)
      Object.assign(newAlternative, {
        medicine: item.medicine || null,
        medicine_type: item.medicine_type || null,
        usage_type: item.usage_type || null,
        dosage: '',
        frequency: '',
        amount: '',
        notes: ''
      })
      
      // Show modal using Bootstrap 5 Modal API
      const modalElement = document.getElementById('alternativeModal')
      if (modalElement) {
        // Try Bootstrap 5 Modal API first
        if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Modal) {
          try {
            // Check if modal instance already exists
            let modalInstance = window.bootstrap.Modal.getInstance(modalElement)
            if (!modalInstance) {
              modalInstance = new window.bootstrap.Modal(modalElement, {
                backdrop: false,
                keyboard: true,
                focus: true
              })
            }
            modalInstance.show()
          } catch (error) {
            console.log('Bootstrap Modal error, using jQuery fallback:', error)
            // Fallback to jQuery Bootstrap
            if (typeof window.$ !== 'undefined' && window.$.fn.modal) {
              window.$(modalElement).modal({ backdrop: false }).modal('show')
            } else {
              // Final fallback: manual show
              modalElement.style.display = 'block'
              modalElement.classList.add('show')
              document.body.classList.add('modal-open')
            }
          }
        }
        // Try jQuery Bootstrap
        else if (typeof window.$ !== 'undefined' && window.$.fn.modal) {
          window.$(modalElement).modal({ backdrop: false }).modal('show')
        }
        // Fallback: show manually
        else {
          modalElement.style.display = 'block'
          modalElement.classList.add('show')
          document.body.classList.add('modal-open')
        }
      } else {
        console.error('Modal element not found: alternativeModal')
      }
    }

    const addAlternative = async () => {
      loading.value = true
      try {
        const requestData = {
          prescription_id: props.prescriptionId,
          prescription_item_id: currentItem.value.id,
          medicine_id: newAlternative.medicine?.id || '',
          medicine_type_id: newAlternative.medicine_type?.id || '',
          usage_type_id: newAlternative.usage_type?.id || '',
          dosage: newAlternative.dosage,
          frequency: newAlternative.frequency,
          amount: newAlternative.amount,
          notes: newAlternative.notes
        }
        
        console.log('Sending alternative data:', requestData)
        console.log('Selected medicine:', newAlternative.medicine)
        console.log('Selected medicine type:', newAlternative.medicine_type)
        console.log('Selected usage type:', newAlternative.usage_type)
        
        // Validate required fields
        if (!requestData.medicine_id) {
          showToast(localize('global.please_select_medicine'), 'error')
          return
        }
        if (!requestData.medicine_type_id) {
          showToast(localize('global.medicine_type_required'), 'error')
          return
        }
        if (!requestData.usage_type_id) {
          showToast(localize('global.usage_type_required'), 'error')
          return
        }
        if (!requestData.dosage) {
          showToast(localize('global.dosage_required'), 'error')
          return
        }
        if (!requestData.frequency) {
          showToast(localize('global.frequency_required'), 'error')
          return
        }
        if (!requestData.amount) {
          showToast(localize('global.amount_required'), 'error')
          return
        }
        
        const response = await axios.post('/prescription-show-ajax/add-alternative', requestData)
        if (response.data.success) {
          // Add to current item's alternatives
          if (!currentItem.value.alternative_items) {
            currentItem.value.alternative_items = []
          }
          currentItem.value.alternative_items.push(response.data.data)
          showToast(localize('global.alternative_added_successfully'))
          // Reset form
          Object.assign(newAlternative, {
            medicine: null,
            medicine_type: null,
            usage_type: null,
            dosage: '',
            frequency: '',
            amount: '',
            notes: ''
          })
        } else {
          showToast(response.data.message, 'error')
        }
      } catch (error) {
        console.error('Error adding alternative:', error)
        if (error.response && error.response.data) {
          console.error('Server response:', error.response.data)
          if (error.response.data.errors) {
            const errorMessages = Object.values(error.response.data.errors).flat().join(', ')
            showToast(`${localize('global.validation_errors')}: ${errorMessages}`, 'error')
          } else if (error.response.data.message) {
            showToast(error.response.data.message, 'error')
          } else {
            showToast(localize('global.failed_to_add_alternative'), 'error')
          }
        } else {
          showToast(localize('global.failed_to_add_alternative'), 'error')
        }
      } finally {
        loading.value = false
      }
    }

    const selectAlternative = async (alternative) => {
      // Show confirmation dialog
      const confirmMessage = `${localize('global.confirm_select_alternative')}: ${alternative.medicine?.name}?`
      if (!confirm(confirmMessage)) {
        return
      }
      
      loading.value = true
      try {
        const response = await axios.put(`/prescription-show-ajax/select-alternative/${alternative.id}`)
        if (response.data.success) {
          // Update the alternative selection
          alternative.is_selected = '1'
          
          // Find the prescription item that this alternative belongs to and update it
          if (prescription.value.prescription_items && currentItem.value) {
            const parentItem = prescription.value.prescription_items.find(item => 
              item.id === currentItem.value.id
            )
            if (parentItem) {
              parentItem.selected_alternative = alternative
            }
          }
          
          // Also update currentItem if it exists
          if (currentItem.value) {
            currentItem.value.selected_alternative = alternative
          }
          
          // Refresh prescription data to ensure UI is in sync
          await refreshPrescriptionData()
          
          showToast(localize('global.alternative_selected_successfully'))
        } else {
          showToast(response.data.message, 'error')
        }
      } catch (error) {
        showToast(localize('global.failed_to_select_alternative'), 'error')
        console.error('Error selecting alternative:', error)
      } finally {
        loading.value = false
      }
    }

    const deselectAlternative = async (alternative) => {
      loading.value = true
      try {
        const response = await axios.put(`/prescription-show-ajax/select-alternative/${alternative.id}`)
        if (response.data.success) {
          alternative.is_selected = '0'
          
          // Find the prescription item that has this alternative and update it
          if (prescription.value.prescription_items) {
            const parentItem = prescription.value.prescription_items.find(item => 
              item.selected_alternative && item.selected_alternative.id === alternative.id
            )
            if (parentItem) {
              parentItem.selected_alternative = null
            }
          }
          
          // Also update currentItem if it exists
          if (currentItem.value && currentItem.value.selected_alternative && 
              currentItem.value.selected_alternative.id === alternative.id) {
            currentItem.value.selected_alternative = null
          }
          
          // Refresh prescription data to ensure UI is in sync
          await refreshPrescriptionData()
          
          showToast(localize('global.alternative_deselected_successfully'))
        } else {
          showToast(response.data.message, 'error')
        }
      } catch (error) {
        showToast(localize('global.failed_to_deselect_alternative'), 'error')
        console.error('Error deselecting alternative:', error)
      } finally {
        loading.value = false
      }
    }

    const toggleAlternativeStatus = async (alternative) => {
      loading.value = true
      try {
        const newStatus = alternative.is_delivered ? '0' : '1'
        const response = await axios.put(`/prescription-show-ajax/update-alternative-status/${alternative.id}`, {
          is_delivered: newStatus
        })
        if (response.data.success) {
          alternative.is_delivered = newStatus
          showToast(localize('global.alternative_status_updated_successfully'))
        } else {
          showToast(response.data.message, 'error')
        }
      } catch (error) {
        showToast(localize('global.failed_to_update_alternative_status'), 'error')
        console.error('Error updating alternative status:', error)
      } finally {
        loading.value = false
      }
    }

    const deleteAlternative = async (alternative) => {
      if (!confirm('Are you sure you want to delete this alternative?')) return
      
      loading.value = true
      try {
        const response = await axios.delete(`/prescription-show-ajax/delete-alternative/${alternative.id}`)
        if (response.data.success) {
          // Remove from current item's alternatives
          const index = currentItem.value.alternative_items.findIndex(alt => alt.id === alternative.id)
          if (index > -1) {
            currentItem.value.alternative_items.splice(index, 1)
          }
          showToast(localize('global.alternative_deleted_successfully'))
        } else {
          showToast(response.data.message, 'error')
        }
      } catch (error) {
        showToast(localize('global.failed_to_delete_alternative'), 'error')
        console.error('Error deleting alternative:', error)
      } finally {
        loading.value = false
      }
    }

    const copyFromOriginal = () => {
      if (currentItem.value) {
        newAlternative.dosage = currentItem.value.dosage || ''
        newAlternative.frequency = currentItem.value.frequency || ''
        newAlternative.amount = currentItem.value.amount || ''
        showToast(localize('global.original_data_copied'))
      }
    }

    const onMedicineSelect = (selectedMedicine) => {
      // Auto-fill medicine type and usage type based on selected medicine
      if (selectedMedicine) {
        // Find matching medicine type - try different possible property names
        const medicineTypeId = selectedMedicine.medicine_type_id || selectedMedicine.medicine_type?.id
        const matchingType = medicineTypes.value.find(type => 
          type.id === medicineTypeId
        )
        if (matchingType) {
          newAlternative.medicine_type = matchingType
        } else {
          // If no matching type found, use the first available type as fallback
          newAlternative.medicine_type = medicineTypes.value[0] || null
        }

        // Find matching usage type - try different possible property names
        const usageTypeId = selectedMedicine.usage_type_id || selectedMedicine.usage_type?.id
        const matchingUsageType = medicineUsageTypes.value.find(usageType => 
          usageType.id === usageTypeId
        )
        if (matchingUsageType) {
          newAlternative.usage_type = matchingUsageType
        } else {
          // If no matching usage type found, use the first available type as fallback
          newAlternative.usage_type = medicineUsageTypes.value[0] || null
        }

        // Auto-fill dosage, frequency, and amount from original item
        if (currentItem.value) {
          newAlternative.dosage = currentItem.value.dosage || ''
          newAlternative.frequency = currentItem.value.frequency || ''
          newAlternative.amount = currentItem.value.amount || ''
        }

        // Don't automatically submit - let user click Add button
      }
    }

    const setActiveTab = (tabName) => {
      activeTab.value = tabName
    }

    const switchToAddTab = () => {
      activeTab.value = 'add'
      const addTab = document.getElementById('add-tab')
      if (addTab) {
        addTab.click()
      }
    }

    const quickAddAlternative = (item) => {
      currentItem.value = item
      // Pre-fill form with main medicine data
      Object.assign(newAlternative, {
        medicine: item.medicine || null,
        medicine_type: item.medicine_type || null,
        usage_type: item.usage_type || null,
        dosage: item.dosage || '',
        frequency: item.frequency || '',
        amount: item.amount || '',
        notes: ''
      })
      
      // Open modal and switch to add tab
      openAlternativesModal(item)
      setTimeout(() => {
        switchToAddTab()
      }, 100)
    }

    // Bulk operations methods
    const toggleItemSelection = (itemId) => {
      if (selectedItems.value.has(itemId)) {
        selectedItems.value.delete(itemId)
      } else {
        selectedItems.value.add(itemId)
      }
      updateSelectAllState()
    }

    const toggleSelectAll = () => {
      selectAll.value = !selectAll.value
      if (selectAll.value) {
        // Select all items that can be toggled (not original items with selected alternatives)
        prescription.value.prescription_items?.forEach(item => {
          if (!item.selected_alternative) {
            selectedItems.value.add(item.id)
          }
        })
      } else {
        selectedItems.value.clear()
      }
    }

    const updateSelectAllState = () => {
      const selectableItems = prescription.value.prescription_items?.filter(item => !item.selected_alternative) || []
      selectAll.value = selectableItems.length > 0 && selectedItems.value.size === selectableItems.length
    }

    const bulkMarkDelivered = async () => {
      if (selectedItems.value.size === 0) return
      
      loading.value = true
      try {
        const promises = Array.from(selectedItems.value).map(itemId => {
          const item = prescription.value.prescription_items?.find(i => i.id === itemId)
          if (item && item.is_delivered == '0') {
            return axios.put(`/prescription-show-ajax/update-item-status/${itemId}`, {
              is_delivered: '1'
            })
          }
          return Promise.resolve()
        })

        await Promise.all(promises)
        
        // Update local state
        prescription.value.prescription_items?.forEach(item => {
          if (selectedItems.value.has(item.id) && item.is_delivered == '0') {
            item.is_delivered = '1'
          }
        })

        selectedItems.value.clear()
        selectAll.value = false
        showToast(localize('global.items_marked_as_delivered', { count: selectedItems.value.size }))
      } catch (error) {
        showToast(localize('global.failed_to_update_items'), 'error')
        console.error('Error updating items:', error)
      } finally {
        loading.value = false
      }
    }

    const bulkMarkNotDelivered = async () => {
      if (selectedItems.value.size === 0) return
      
      loading.value = true
      try {
        const promises = Array.from(selectedItems.value).map(itemId => {
          const item = prescription.value.prescription_items?.find(i => i.id === itemId)
          if (item && item.is_delivered == '1') {
            return axios.put(`/prescription-show-ajax/update-item-status/${itemId}`, {
              is_delivered: '0'
            })
          }
          return Promise.resolve()
        })

        await Promise.all(promises)
        
        // Update local state
        prescription.value.prescription_items?.forEach(item => {
          if (selectedItems.value.has(item.id) && item.is_delivered == '1') {
            item.is_delivered = '0'
          }
        })

        selectedItems.value.clear()
        selectAll.value = false
        showToast(localize('global.items_marked_as_not_delivered', { count: selectedItems.value.size }))
      } catch (error) {
        showToast(localize('global.failed_to_update_items'), 'error')
        console.error('Error updating items:', error)
      } finally {
        loading.value = false
      }
    }

    const closeModal = () => {
      const modalElement = document.getElementById('alternativeModal')
      if (modalElement) {
        // Try Bootstrap 5 Modal API first
        if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Modal) {
          try {
            const modalInstance = window.bootstrap.Modal.getInstance(modalElement)
            if (modalInstance) {
              modalInstance.hide()
            } else {
              // Create new instance and hide
              const modal = new window.bootstrap.Modal(modalElement, { backdrop: false })
              modal.hide()
            }
          } catch (error) {
            console.log('Bootstrap Modal error, using jQuery fallback:', error)
            // Fallback to jQuery Bootstrap
            if (typeof window.$ !== 'undefined' && window.$.fn.modal) {
              window.$(modalElement).modal('hide')
            } else {
              // Final fallback: manual hide
              modalElement.style.display = 'none'
              modalElement.classList.remove('show')
              document.body.classList.remove('modal-open')
            }
          }
        }
        // Try jQuery Bootstrap
        else if (typeof window.$ !== 'undefined' && window.$.fn.modal) {
          window.$(modalElement).modal('hide')
        }
        // Fallback: hide manually
        else {
          modalElement.style.display = 'none'
          modalElement.classList.remove('show')
          document.body.classList.remove('modal-open')
        }
      }
    }

    

    const refreshPrescriptionData = async () => {
      try {
        const response = await axios.get(`/prescription-show-ajax/prescription-details/${props.prescriptionId}`)
        if (response.data.success) {
          prescription.value = response.data.data
        }
      } catch (error) {
        console.error('Error refreshing prescription data:', error)
      }
    }

    // Keyboard shortcuts
    const handleKeydown = (event) => {
      // ESC key to close modal
      if (event.key === 'Escape') {
        const modalElement = document.getElementById('alternativeModal')
        if (modalElement && modalElement.classList.contains('show')) {
          closeModal()
        }
      }
      
      // Ctrl+S to mark selected items as delivered
      if (event.ctrlKey && event.key === 's') {
        event.preventDefault()
        if (selectedItems.value.size > 0) {
          bulkMarkDelivered()
        }
      }
      
      // Ctrl+A to add alternative (when modal is open)
      if (event.ctrlKey && event.key === 'a') {
        const modalElement = document.getElementById('alternativeModal')
        if (modalElement && modalElement.classList.contains('show')) {
          event.preventDefault()
          switchToAddTab()
        }
      }
      
      // Ctrl+D to mark selected items as not delivered
      if (event.ctrlKey && event.key === 'd') {
        event.preventDefault()
        if (selectedItems.value.size > 0) {
          bulkMarkNotDelivered()
        }
      }
    }

    // Lifecycle
    onMounted(() => {
      loadPrescriptionDetails()
      loadDropdownData()
      
      // Add keyboard event listener
      document.addEventListener('keydown', handleKeydown)
    })

    // Cleanup on unmount
    onUnmounted(() => {
      document.removeEventListener('keydown', handleKeydown)
    })

    return {
      prescription,
      loading,
      currentItem,
      medicines,
      medicineTypes,
      medicineUsageTypes,
      toast,
      newAlternative,
      selectedItems,
      selectAll,
      activeTab,
      localize,
      showToast,
      loadPrescriptionDetails,
      completePrescription,
      toggleItemStatus,
      openAlternativesModal,
      addAlternative,
      selectAlternative,
      deselectAlternative,
      toggleAlternativeStatus,
      deleteAlternative,
      copyFromOriginal,
      onMedicineSelect,
      setActiveTab,
      switchToAddTab,
      quickAddAlternative,
      toggleItemSelection,
      toggleSelectAll,
      bulkMarkDelivered,
      bulkMarkNotDelivered,
      refreshPrescriptionData,
      closeModal
    }
  }
}
</script>

<style scoped>
@import 'vue-multiselect/dist/vue-multiselect.css';

.toast {
  z-index: 1055;
}

/* Modal styles */
.modal {
  z-index: 1050;
}

.modal.show {
  display: block !important;
}

/* Vue Multiselect styles */
.multiselect {
  min-height: 38px;
}

.multiselect__tags {
  min-height: 38px;
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  padding: 0.375rem 0.75rem;
}

.multiselect__tags:focus-within {
  border-color: #86b7fe;
  outline: 0;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.multiselect__placeholder {
  color: #6c757d;
  margin-bottom: 0;
  padding-top: 0;
  padding-bottom: 0;
}

.multiselect__single {
  margin-bottom: 0;
  padding-top: 0;
  padding-bottom: 0;
  line-height: 1.5;
}

.multiselect__input {
  margin-bottom: 0;
  padding-top: 0;
  padding-bottom: 0;
  line-height: 1.5;
}

.multiselect__option--highlight {
  background: #0d6efd;
}

.multiselect__option--highlight::after {
  background: #0d6efd;
}

.multiselect__option--selected {
  background: #e9ecef;
  color: #495057;
  font-weight: 600;
}

.multiselect__option--selected.multiselect__option--highlight {
  background: #0d6efd;
  color: white;
}

.multiselect__spinner {
  background: #0d6efd;
}

.multiselect__loading-arrow {
  border-color: #0d6efd transparent transparent;
}

/* Tab Navigation Styles */
.nav-tabs .nav-link {
  border: 1px solid transparent;
  border-top-left-radius: 0.375rem;
  border-top-right-radius: 0.375rem;
  color: #6c757d;
  background-color: transparent;
  transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
  border-color: #e9ecef #e9ecef #dee2e6;
  color: #495057;
  background-color: #f8f9fa;
}

.nav-tabs .nav-link.active {
  color: #fff;
  background-color: #6f42c1;
  border-color: #6f42c1 #6f42c1 #fff;
  font-weight: 600;
}

.nav-tabs .nav-link.active:hover {
  color: #fff;
  background-color: #7724dd;
}

.nav-tabs .nav-link.active i {
  color: #fff;
}

.nav-tabs .nav-link.active .badge {
  background-color: rgba(255, 255, 255, 0.2) !important;
  color: #fff !important;
}

/* Selected alternative button styles */
.btn-warning.fw-bold {
  font-weight: 700 !important;
  border-width: 2px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  font-size: 1.1rem !important;
  background-color:rgb(25, 210, 0) !important;
  color: white !important;
}

.btn-warning.fw-bold:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
  background-color: #1e3d1a !important;
  border-color: #1e3d1a !important;
  color: white !important;
}

/* Selected state indicator */
.btn-warning.fw-bold i {
  font-weight: 900;
  font-size: 1.2rem !important;
}

/* General button styling improvements */
.btn {
  font-size: 1rem !important;
}

.btn-sm {
  font-size: 0.95rem !important;
}

/* Success button styling with dark grass green */
.btn-success {
  background-color: rgb(25, 210, 0)!important;
  border-color: rgb(25, 210, 0) !important;
  font-size: 1.05rem !important;
}

.btn-success:hover {
  background-color: #0d6e00 !important;
}

/* Alternative select button styling */
.btn-success.fw-bold {
  font-weight: 700 !important;
  font-size: 1.1rem !important;
  background-color: rgb(25, 210, 0) !important;
}

.btn-success.fw-bold:hover {
  background-color: #168008 !important;
}

/* Table text improvements */
.table {
  font-size: 1.05rem !important;
}

.table th {
  font-size: 15px !important;
  font-weight: 600 !important;
  background-color: #f8f9fa !important;
  border-bottom: 2px solid #2d5a27 !important;
}

.table td {
  font-size: 15px !important;
  border-bottom: 1px solid #e9ecef !important;
}



/* Badge styling with dark grass green */
.badge {
  font-size: 0.9rem !important;
  font-weight: 600 !important;
}

.badge.bg-success {
  background-color: #2d5a27 !important;
}

.badge.bg-warning {
  background-color: #8b5a2b !important;
  color: white !important;
}

/* Modal improvements */
.modal-title {
  font-size: 1.3rem !important;
  font-weight: 700 !important;
}

.modal-body {
  font-size: 1.05rem !important;
}

/* Form label improvements */
.form-label {
  font-size: 1.1rem !important;
  font-weight: 600 !important;
}

/* Card header improvements */
.card-header h5 {
  font-size: 1.4rem !important;
  font-weight: 700 !important;
}
</style>
