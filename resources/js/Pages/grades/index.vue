<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white">
                    Liste des grades
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Statistiques -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <Card class="bg-white shadow-sm">
                        <template #content>
                            <div class="text-center">
                                <i class="pi pi-tag text-4xl mb-3 text-sky-500"></i>
                                <div class="text-3xl font-bold text-sky-600">{{ statistiques.total_grades }}</div>
                                <div class="text-sm text-gray-500 mt-1">Total grades</div>
                            </div>
                        </template>
                    </Card>
                    
                    <Card class="bg-white shadow-sm">
                        <template #content>
                            <div class="text-center">
                                <i class="pi pi-list text-4xl mb-3 text-sky-500"></i>
                                <div class="text-3xl font-bold text-sky-600">{{ statistiques.types_grades }}</div>
                                <div class="text-sm text-gray-500 mt-1">Types de grades</div>
                            </div>
                        </template>
                    </Card>
                    
                    <Card class="bg-white shadow-sm">
                        <template #content>
                            <div class="text-center">
                                <i class="pi pi-users text-4xl mb-3 text-sky-500"></i>
                                <div class="text-3xl font-bold text-sky-600">{{ statistiques.total_militaires }}</div>
                                <div class="text-sm text-gray-500 mt-1">Militaires actifs</div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Filtres avec recherche automatique - version alignée -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <!-- Champ recherche -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                                <span class="p-input-icon-left w-full">
                                    <i class="pi pi-search" />
                                    <InputText v-model="filters.search" 
                                              placeholder="Rechercher un grade..." 
                                              class="w-full"
                                              @input="onSearchInput" />
                                </span>
                            </div>
                            <!-- Filtre type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type de grade</label>
                                <Select v-model="filters.type" 
                                        :options="typesGrades" 
                                        optionLabel="label" 
                                        optionValue="value"
                                        placeholder="Tous les types"
                                        class="w-full"
                                        showClear
                                        @change="onFilterChange" />
                            </div>
                            <!-- Boutons : Réinitialiser + Nouveau grade, alignés à droite -->
                            <div class="md:col-span-2">
                                <div class="h-7 mb-1 md:invisible"></div> <!-- espace réservé pour l'alignement vertical -->
                                <div class="flex justify-end gap-2">
                                    <Button label="Réinitialiser" 
                                            icon="pi pi-times"
                                            class="p-button-sm bg-gray-500 hover:bg-gray-600 border-gray-500 text-white"
                                            @click="resetFilters" />
                                    <Button label="Nouveau grade" 
                                            icon="pi pi-plus"
                                            class="p-button-sm bg-sky-400 hover:bg-sky-500 border-sky-400 text-white"
                                            @click="createGrade" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau des grades -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <DataTable :value="grades.data" 
                                   stripedRows 
                                   responsiveLayout="scroll"
                                   :loading="loading"
                                   paginator
                                   lazy
                                   :rows="grades.per_page"
                                   :totalRecords="grades.total"
                                   @page="onPageChange"
                                   class="p-datatable-sm">
                            
                            <Column field="code_grade" header="Code" style="width: 100px">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.code_grade" 
                                         style="background: #bae6fd; color: #0369a1;" />
                                </template>
                            </Column>

                            <Column field="nom_grade" header="Grade">
                                <template #body="slotProps">
                                    <Button :label="slotProps.data.nom_grade"
                                            class="p-button-link p-0 text-sky-500 hover:text-sky-600 font-medium"
                                            @click="viewGrade(slotProps.data.id)" />
                                </template>
                            </Column>

                            <Column field="type_grade" header="Type" style="width: 150px">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.type_grade" 
                                         :style="getTypeStyle(slotProps.data.type_grade)" />
                                </template>
                            </Column>

                            <Column field="ordre" header="Ordre" style="width: 80px">
                                <template #body="slotProps">
                                    <Badge :value="slotProps.data.ordre" 
                                           style="background: #bae6fd; color: #0369a1;" />
                                </template>
                            </Column>

                            <Column header="Effectif actif" style="width: 120px">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.effectif_actif + ' militaires'" 
                                         :style="slotProps.data.effectif_actif > 0 ? { background: '#7dd3fc', color: '#0369a1' } : { background: '#e5e7eb', color: '#6b7280' }" />
                                </template>
                            </Column>

                            <Column header="Actions" style="width: 100px">
                                <template #body="slotProps">
                                    <Button icon="pi pi-eye" 
                                            class="p-button-rounded p-button-text p-button-sm text-sky-500 hover:text-sky-600"
                                            v-tooltip.top="'Voir les détails'"
                                            @click="viewGrade(slotProps.data.id)" />
                                </template>
                            </Column>

                            <template #empty>
                                <div class="text-center py-8 text-gray-500">
                                    <i class="pi pi-tag text-4xl mb-2"></i>
                                    <p>Aucun grade trouvé</p>
                                </div>
                            </template>
                        </DataTable>

                        <div class="text-center sm:text-left text-sm text-gray-600 mt-4">
                            Affichage de {{ grades.from }} à {{ grades.to }} sur {{ grades.total }} grades
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dialogue de création -->
        <Dialog v-model:visible="showCreateDialog" 
                header="Nouveau grade" 
                :modal="true"
                :style="{ width: '550px' }"
                class="p-fluid">
            <div class="space-y-4">
                <div class="field">
                    <label for="code_grade" class="block text-sm font-medium text-gray-700 mb-1">
                        Code grade * <span class="text-xs text-gray-500">(unique, max 20)</span>
                    </label>
                    <InputText id="code_grade" v-model="form.code_grade" 
                               :class="{ 'p-invalid': form.errors.code_grade }"
                               class="w-full" />
                    <small v-if="form.errors.code_grade" class="text-red-600">{{ form.errors.code_grade }}</small>
                </div>

                <div class="field">
                    <label for="nom_grade" class="block text-sm font-medium text-gray-700 mb-1">Nom du grade *</label>
                    <InputText id="nom_grade" v-model="form.nom_grade" 
                               :class="{ 'p-invalid': form.errors.nom_grade }"
                               class="w-full" />
                    <small v-if="form.errors.nom_grade" class="text-red-600">{{ form.errors.nom_grade }}</small>
                </div>

                <div class="field">
                    <label for="type_grade" class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                    <Select id="type_grade" v-model="form.type_grade" 
                            :options="typesGrades" 
                            optionLabel="label" 
                            optionValue="value"
                            placeholder="Choisir un type"
                            :class="{ 'p-invalid': form.errors.type_grade }"
                            class="w-full" />
                    <small v-if="form.errors.type_grade" class="text-red-600">{{ form.errors.type_grade }}</small>
                </div>

                <div class="field">
                    <label for="ordre" class="block text-sm font-medium text-gray-700 mb-1">
                        Ordre * <span class="text-xs text-gray-500">(entier unique, plus petit = plus haut grade)</span>
                    </label>
                    <InputNumber id="ordre" v-model="form.ordre" 
                                 :min="0" :step="1"
                                 :class="{ 'p-invalid': form.errors.ordre }"
                                 class="w-full" />
                    <small v-if="form.errors.ordre" class="text-red-600">{{ form.errors.ordre }}</small>
                </div>

                <div class="field">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <Textarea id="description" v-model="form.description" 
                              rows="3" 
                              class="w-full" />
                </div>
            </div>

            <template #footer>
                <Button label="Annuler" icon="pi pi-times" 
                        class="p-button-text p-button-secondary"
                        @click="closeCreateDialog" />
                <Button label="Créer" icon="pi pi-check" 
                        :loading="form.processing"
                        @click="submitCreate" />
            </template>
        </Dialog>

        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Badge from 'primevue/badge';
import Toast from 'primevue/toast';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';
import debounce from 'lodash/debounce';

const props = defineProps({
    grades: {
        type: Object,
        required: true
    },
    statistiques: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    typesGrades: {
        type: Array,
        default: () => []
    }
});

const toast = useToast();
const loading = ref(false);
const showCreateDialog = ref(false);

const form = useForm({
    code_grade: '',
    nom_grade: '',
    type_grade: null,
    ordre: null,
    description: ''
});

const filters = reactive({
    search: props.filters?.search || '',
    type: props.filters?.type || null
});

const debouncedSearch = debounce(() => {
    loadGrades(1);
}, 500);

const onSearchInput = () => {
    debouncedSearch();
};

const onFilterChange = () => {
    loadGrades(1);
};

watch(() => filters.search, () => {
    debouncedSearch();
});

watch(() => filters.type, () => {
    loadGrades(1);
});

const getTypeStyle = (type) => {
    const styles = {
        'Officier général': { background: '#fecaca', color: '#991b1b' },
        'Officier supérieur': { background: '#fed7aa', color: '#c2410c' },
        'Officier subalterne': { background: '#bae6fd', color: '#0369a1' },
        'Sous-officier supérieur': { background: '#7dd3fc', color: '#0369a1' },
        'Sous-officier subalterne': { background: '#7dd3fc', color: '#0369a1' },
        'Militaires du rang': { background: '#e5e7eb', color: '#374151' }
    };
    return styles[type] || { background: '#bae6fd', color: '#0369a1' };
};

const loadGrades = (page = 1) => {
    loading.value = true;
    router.get(route('grades.index'), {
        page,
        search: filters.search,
        type: filters.type
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Impossible de charger les grades',
                life: 3000
            });
        }
    });
};

const resetFilters = () => {
    filters.search = '';
    filters.type = null;
};

const onPageChange = (event) => {
    changePage(event.page + 1);
};

const changePage = (page) => {
    if (page >= 1 && page <= props.grades.last_page) {
        loadGrades(page);
    }
};

const viewGrade = (id) => {
    router.visit(route('grades.show', id));
};

const createGrade = () => {
    form.reset();
    form.clearErrors();
    showCreateDialog.value = true;
};

const closeCreateDialog = () => {
    showCreateDialog.value = false;
    form.reset();
};

const submitCreate = () => {
    form.post(route('grades.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateDialog.value = false;
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Grade créé avec succès',
                life: 3000
            });
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Erreur de validation',
                detail: 'Veuillez corriger les champs en rouge',
                life: 5000
            });
        }
    });
};
</script>

<style scoped>
:deep(.p-card) {
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.2s ease;
    border: 1px solid #e5e7eb;
}

:deep(.p-card:hover) {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    border-color: #bae6fd;
}

:deep(.p-datatable) {
    font-size: 0.95rem;
}

:deep(.p-datatable .p-datatable-tbody > tr:hover) {
    background-color: #f0f9ff;
}

:deep(.p-tag) {
    font-size: 0.85rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-weight: 500;
}

:deep(.p-button-link) {
    text-decoration: none;
    font-weight: 500;
}

:deep(.p-button-link:hover) {
    text-decoration: underline;
}

:deep(.p-badge) {
    font-size: 0.85rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
}

.bg-sky-400 {
    background-color: #38bdf8;
}

.hover\:bg-sky-500:hover {
    background-color: #0ea5e9;
}

.border-sky-400 {
    border-color: #38bdf8;
}

.text-sky-500 {
    color: #0ea5e9;
}

.text-sky-600 {
    color: #0284c7;
}

.hover\:text-sky-600:hover {
    color: #0284c7;
}

.bg-gray-500 {
    background-color: #6b7280;
}

.hover\:bg-gray-600:hover {
    background-color: #4b5563;
}

.border-gray-500 {
    border-color: #6b7280;
}

.text-white {
    color: white;
}

.flex {
    display: flex;
}

.gap-2 {
    gap: 0.5rem;
}

.justify-end {
    justify-content: flex-end;
}

.md\:col-span-2 {
    grid-column: span 2;
}

.md\:invisible {
    visibility: hidden;
}

.h-7 {
    height: 1.75rem;
}

.mb-1 {
    margin-bottom: 0.25rem;
}
</style>