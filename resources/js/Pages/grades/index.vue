<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white">
                    Liste des grades
                </h2>
                <div class="flex gap-2">
                    <Button label="Nouveau grade" 
                            icon="pi pi-plus"
                            class="p-button-sm bg-sky-400 hover:bg-sky-500 border-sky-400 text-white"
                            @click="createGrade" />
                </div>
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

                <!-- Filtres avec recherche automatique -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
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
                            <div>
                                <div class="h-7 mb-1"></div>
                                <Button label="Réinitialiser" 
                                        icon="pi pi-times"
                                        class="p-button-sm bg-gray-500 hover:bg-gray-600 border-gray-500 text-white w-full"
                                        @click="resetFilters" />
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
                            
                            <!-- Code -->
                            <Column field="code_grade" header="Code" style="width: 100px">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.code_grade" 
                                         style="background: #bae6fd; color: #0369a1;" />
                                </template>
                            </Column>

                            <!-- Grade (cliquable) -->
                            <Column field="nom_grade" header="Grade">
                                <template #body="slotProps">
                                    <Button :label="slotProps.data.nom_grade"
                                            class="p-button-link p-0 text-sky-500 hover:text-sky-600 font-medium"
                                            @click="viewGrade(slotProps.data.id)" />
                                </template>
                            </Column>

                            <!-- Type -->
                            <Column field="type_grade" header="Type" style="width: 150px">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.type_grade" 
                                         :style="getTypeStyle(slotProps.data.type_grade)" />
                                </template>
                            </Column>

                            <!-- Ordre -->
                            <Column field="ordre" header="Ordre" style="width: 80px">
                                <template #body="slotProps">
                                    <Badge :value="slotProps.data.ordre" 
                                           style="background: #bae6fd; color: #0369a1;" />
                                </template>
                            </Column>

                            <!-- Effectif actif -->
                            <Column header="Effectif actif" style="width: 120px">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.effectif_actif + ' militaires'" 
                                         :style="slotProps.data.effectif_actif > 0 ? { background: '#7dd3fc', color: '#0369a1' } : { background: '#e5e7eb', color: '#6b7280' }" />
                                </template>
                            </Column>

                            <!-- Actions -->
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

                        <!-- Simple information de pagination -->
                        <div class="text-center sm:text-left text-sm text-gray-600 mt-4">
                            Affichage de {{ grades.from }} à {{ grades.to }} sur {{ grades.total }} grades
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
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

// État des filtres
const filters = reactive({
    search: props.filters?.search || '',
    type: props.filters?.type || null
});

// Recherche automatique avec debounce
const debouncedSearch = debounce(() => {
    loadGrades(1);
}, 500);

const onSearchInput = () => {
    debouncedSearch();
};

const onFilterChange = () => {
    loadGrades(1);
};

// Watcher pour surveiller les changements de recherche
watch(() => filters.search, () => {
    debouncedSearch();
});

watch(() => filters.type, () => {
    loadGrades(1);
});

// Style pour les badges selon le type de grade
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

// Charger les grades
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

// Réinitialiser les filtres
const resetFilters = () => {
    filters.search = '';
    filters.type = null;
};

// Changement de page
const onPageChange = (event) => {
    changePage(event.page + 1);
};

const changePage = (page) => {
    if (page >= 1 && page <= props.grades.last_page) {
        loadGrades(page);
    }
};

// Actions
const viewGrade = (id) => {
    router.visit(route('grades.show', id));
};

const createGrade = () => {
    toast.add({
        severity: 'info',
        summary: 'Information',
        detail: 'Fonctionnalité de création à venir',
        life: 3000
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

/* Styles personnalisés */
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
</style>