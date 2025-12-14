<template>
    <div class="image-gallery">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">{{ localize('global.images') }}</h6>
            <button class="btn btn-sm btn-primary" @click="showUploadModal = true">
                <i class="bx bx-plus"></i> {{ localize('global.upload_image') }}
            </button>
        </div>

        <!-- Image Grid -->
        <div v-if="images.length > 0" class="row g-2">
            <div v-for="image in images" :key="image.id" class="col-md-3 col-sm-4 col-6">
                <div class="image-item card">
                    <img :src="image.image_url" 
                         :alt="image.description || 'Dental chart image'"
                         class="card-img-top"
                         @click="openLightbox(image)"
                         style="cursor: pointer; height: 150px; object-fit: cover;">
                    <div class="card-body p-2">
                        <small class="text-muted d-block">{{ image.image_type }}</small>
                        <button class="btn btn-sm btn-danger mt-1 w-100" @click="deleteImage(image)">
                            <i class="bx bx-trash"></i> {{ localize('global.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-center text-muted py-4">
            <p>{{ localize('global.no_images_uploaded') }}</p>
        </div>

        <!-- Upload Modal -->
        <div v-if="showUploadModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" @click.self="showUploadModal = false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ localize('global.upload_image') }}</h5>
                        <button type="button" class="btn-close" @click="showUploadModal = false"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="uploadImage">
                            <div class="mb-3">
                                <label class="form-label">{{ localize('global.image_type') }}</label>
                                <select v-model="uploadForm.image_type" class="form-select" required>
                                    <option value="xray">{{ localize('global.xray') }}</option>
                                    <option value="photo">{{ localize('global.photo') }}</option>
                                    <option value="diagram">{{ localize('global.diagram') }}</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ localize('global.image_file') }}</label>
                                <input type="file" 
                                       @change="handleFileSelect"
                                       accept="image/jpeg,image/jpg,image/png"
                                       class="form-control"
                                       required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ localize('global.description') }}</label>
                                <textarea v-model="uploadForm.description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" @click="showUploadModal = false">
                                    {{ localize('global.cancel') }}
                                </button>
                                <button type="submit" class="btn btn-primary" :disabled="uploading">
                                    <span v-if="uploading" class="spinner-border spinner-border-sm me-1"></span>
                                    {{ localize('global.upload') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lightbox -->
        <div v-if="lightboxImage" class="modal fade show d-block" style="background: rgba(0,0,0,0.9);" @click.self="lightboxImage = null">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content bg-transparent border-0">
                    <div class="modal-header border-0">
                        <button type="button" class="btn-close btn-close-white" @click="lightboxImage = null"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img :src="lightboxImage.image_url" 
                             :alt="lightboxImage.description"
                             class="img-fluid"
                             style="max-height: 80vh;">
                        <p v-if="lightboxImage.description" class="text-white mt-3">{{ lightboxImage.description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ImageGallery',
    props: {
        dentalChartId: {
            type: Number,
            required: true
        },
        initialImages: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            images: [...this.initialImages],
            showUploadModal: false,
            lightboxImage: null,
            uploading: false,
            uploadForm: {
                image_type: 'xray',
                description: '',
                file: null
            }
        }
    },
    methods: {
        handleFileSelect(event) {
            this.uploadForm.file = event.target.files[0];
        },
        async uploadImage() {
            if (!this.uploadForm.file) {
                alert(this.localize('global.please_select_file'));
                return;
            }

            this.uploading = true;
            const formData = new FormData();
            formData.append('image', this.uploadForm.file);
            formData.append('image_type', this.uploadForm.image_type);
            formData.append('description', this.uploadForm.description);

            try {
                const response = await fetch(`/dental-chart-images/store/${this.dentalChartId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    this.images.push(result.data);
                    this.showUploadModal = false;
                    this.uploadForm = {
                        image_type: 'xray',
                        description: '',
                        file: null
                    };
                    this.$emit('image-uploaded', result.data);
                } else {
                    alert(result.message || this.localize('global.upload_failed'));
                }
            } catch (error) {
                console.error('Upload error:', error);
                alert(this.localize('global.upload_failed'));
            } finally {
                this.uploading = false;
            }
        },
        async deleteImage(image) {
            if (!confirm(this.localize('global.are_you_sure_delete'))) {
                return;
            }

            try {
                const response = await fetch(`/dental-chart-images/destroy/${image.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (result.success) {
                    this.images = this.images.filter(img => img.id !== image.id);
                    this.$emit('image-deleted', image.id);
                } else {
                    alert(result.message || this.localize('global.delete_failed'));
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert(this.localize('global.delete_failed'));
            }
        },
        openLightbox(image) {
            this.lightboxImage = image;
        },
        localize(key) {
            return window.localize ? window.localize(key) : key;
        }
    }
}
</script>

<style scoped>
.image-item {
    transition: transform 0.2s;
}

.image-item:hover {
    transform: scale(1.05);
}

.modal.show {
    display: block;
}
</style>
