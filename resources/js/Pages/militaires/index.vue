<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 px-4 md:px-0">
                <h2 class="font-semibold text-xl text-sky-600">Gestion des Militaires</h2>
                <div class="flex flex-wrap gap-3">
                    <button @click="createMilitaire"
                        class="px-4 py-2 bg-white text-sky-600 rounded-lg font-medium hover:bg-sky-50 transition-colors flex items-center gap-2 shadow-sm text-sm md:text-base">
                        <i class="pi pi-plus text-sm md:text-base"></i> Nouveau militaire
                    </button>
                    <button @click="importExcel"
                        class="px-4 py-2 bg-white text-emerald-600 rounded-lg font-medium hover:bg-emerald-50 transition-colors flex items-center gap-2 shadow-sm text-sm md:text-base">
                        <i class="pi pi-upload text-sm md:text-base"></i> Importer Excel
                    </button>
                    <button @click="openExportModal"
                        class="px-4 py-2 bg-white text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition-colors flex items-center gap-2 shadow-sm text-sm md:text-base">
                        <i class="pi pi-file-excel text-sm md:text-base"></i> Exporter
                    </button>
                </div>
            </div>

            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Statistiques -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-sky-500">
                        <div class="text-sm text-gray-500">Total militaires</div>
                        <div class="text-xl md:text-2xl font-bold text-sky-600">{{ statistiques.total }}</div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-emerald-500">
                        <div class="text-sm text-gray-500">Militaires actifs</div>
                        <div class="text-xl md:text-2xl font-bold text-emerald-600">{{ statistiques.actifs }}</div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-amber-500">
                        <div class="text-sm text-gray-500">Retraités</div>
                        <div class="text-xl md:text-2xl font-bold text-amber-600">{{ statistiques.retraites }}</div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-red-500">
                        <div class="text-sm text-gray-500">Alertes non vues</div>
                        <div class="text-xl md:text-2xl font-bold text-red-600">{{ statistiques.alertes }}</div>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
                                <span class="p-input-icon-left w-full">
                                    <i class="pi pi-search" />
                                    <InputText v-model="filters.search" placeholder="Rechercher..." class="w-full"
                                        @input="onSearchInput" />
                                </span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Grade</label>
                                <Select v-model="filters.grade" :options="gradeOptions" optionLabel="label"
                                    optionValue="value" placeholder="Tous les grades" class="w-full" showClear
                                    @change="onFilterChange" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                                <Select v-model="filters.statut" :options="statutOptions" optionLabel="label"
                                    optionValue="value" placeholder="Tous les statuts" class="w-full" showClear
                                    @change="onFilterChange" />
                            </div>
                            <div>
                                <div class="h-7 mb-1"></div>
                                <Button label="Réinitialiser" icon="pi pi-times"
                                    class="p-button-sm bg-gray-500 hover:bg-gray-600 border-gray-500 text-white w-full md:w-auto"
                                    @click="resetFilters" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DataTable -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-4 md:p-6">
                        <DataTable :value="militaires.data" stripedRows responsiveLayout="scroll" :loading="loading"
                            paginator lazy :rows="militaires.per_page" :totalRecords="militaires.total"
                            @page="onPageChange" class="p-datatable-sm">

                            <!-- Colonne Matricule -->
                            <Column field="matricule" header="Matricule" style="min-width: 100px;">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.matricule"
                                        style="background: #bae6fd; color: #0369a1;" />
                                </template>
                            </Column>

                            <!-- Colonne Nom & Prénom -->
                            <Column field="nom" header="Nom & Prénom" style="min-width: 160px;">
                                <template #body="slotProps">
                                    <div class="flex items-center gap-2">
                                        <Button :label="slotProps.data.nom + ' ' + slotProps.data.prenom"
                                            class="p-button-link p-0 text-sky-600 hover:text-sky-700 font-medium text-left"
                                            @click="viewMilitaire(slotProps.data.id)" />
                                        <Badge v-if="slotProps.data.alertes_count > 0" value="!"
                                            style="background: #f97316; color: white;" class="ml-2" />
                                    </div>
                                </template>
                            </Column>

                            <!-- Colonne Grade -->
                            <Column field="grade_actuel" header="Grade" style="min-width: 110px;">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.grade_actuel"
                                        style="background: #7dd3fc; color: #0369a1;" />
                                </template>
                            </Column>

                            <!-- Colonne Téléphone ✅ NOUVEAU -->
                            <Column field="telephone" header="Téléphone" style="min-width: 120px;">
                                <template #body="slotProps">
                                    <span class="text-sm">{{ slotProps.data.telephone || '-' }}</span>
                                </template>
                            </Column>

                            <!-- Colonne Entrée service -->
                            <Column field="date_entree_service" header="Entrée service" style="min-width: 110px;">
                                <template #body="slotProps">
                                    <span class="text-sm">{{ slotProps.data.date_entree_service || '-' }}</span>
                                </template>
                            </Column>

                            <!-- Colonne Âge -->
                            <Column header="Âge" style="min-width: 70px;">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.age + ' ans'"
                                        style="background: #7dd3fc; color: #0369a1;" />
                                </template>
                            </Column>

                            <!-- Colonne Ancienneté -->
                            <Column header="Ancienneté" style="min-width: 90px;">
                                <template #body="slotProps">
                                    <span class="text-sm">{{ formatAnciennete(slotProps.data.anciennete) }}</span>
                                </template>
                            </Column>

                            <!-- Colonne Statut -->
                            <Column header="Statut" style="min-width: 90px;">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.statut" :style="getStatutStyle(slotProps.data.statut)"
                                        class="text-xs" />
                                </template>
                            </Column>

                            <!-- Colonne Position -->
                            <Column field="position_actuelle" header="Position" style="min-width: 120px;">
                                <template #body="slotProps">
                                    <span class="text-sm truncate block max-w-[100px]">{{
                                        slotProps.data.position_actuelle || '-' }}</span>
                                </template>
                            </Column>

                            <!-- Colonne Fonction passée -->
                            <Column field="fonction_passee" header="Fonction passée" style="min-width: 120px;">
                                <template #body="slotProps">
                                    <span class="text-sm truncate block max-w-[100px]">{{ slotProps.data.fonction_passee
                                        || '-' }}</span>
                                </template>
                            </Column>

                            <!-- Colonne Fonction actuelle -->
                            <Column field="fonction_actuelle" header="Fonction actuelle" style="min-width: 120px;">
                                <template #body="slotProps">
                                    <span class="text-sm truncate block max-w-[100px]">{{
                                        slotProps.data.fonction_actuelle || '-' }}</span>
                                </template>
                            </Column>

                            <!-- Colonne Actions -->
                            <Column header="Actions" style="min-width: 110px;">
                                <template #body="slotProps">
                                    <div class="flex gap-1">
                                        <Button icon="pi pi-eye"
                                            class="p-button-rounded p-button-text p-button-sm text-sky-500 hover:text-sky-600"
                                            @click="viewMilitaire(slotProps.data.id)" v-tooltip.top="'Voir'" />
                                        <Button icon="pi pi-pencil"
                                            class="p-button-rounded p-button-text p-button-sm text-amber-500 hover:text-amber-600"
                                            @click="editMilitaire(slotProps.data.id)" v-tooltip.top="'Modifier'" />
                                        <Button icon="pi pi-trash"
                                            class="p-button-rounded p-button-text p-button-sm text-red-500 hover:text-red-600"
                                            @click="confirmDelete(slotProps.data)" v-tooltip.top="'Supprimer'" />
                                    </div>
                                </template>
                            </Column>

                            <template #empty>
                                <div class="text-center py-8 text-gray-500">
                                    <i class="pi pi-users text-4xl mb-2"></i>
                                    <p>Aucun militaire trouvé</p>
                                </div>
                            </template>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dialog suppression -->
        <Dialog v-model:visible="deleteDialogVisible" header="Confirmation" :modal="true"
            :style="{ width: '90%', maxWidth: '400px' }" class="p-fluid">
            <div class="flex items-center gap-3 mb-4">
                <i class="pi pi-exclamation-triangle text-3xl text-amber-500"></i>
                <p class="text-gray-700 text-sm">Êtes-vous sûr de vouloir supprimer le militaire <strong>{{
                    militaireToDelete?.nom }} {{ militaireToDelete?.prenom }}</strong> ?</p>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button label="Non" icon="pi pi-times" class="p-button-text text-gray-500 hover:text-gray-700"
                        @click="deleteDialogVisible = false" />
                    <Button label="Oui" icon="pi pi-check" class="bg-red-500 hover:bg-red-600 border-red-500 text-white"
                        @click="deleteMilitaire" />
                </div>
            </template>
        </Dialog>

        <!-- Modal d'export -->
        <Dialog v-model:visible="exportDialogVisible" header="Exporter la liste" :modal="true"
            :style="{ width: '90%', maxWidth: '500px' }" class="p-fluid">
            <div class="space-y-4">
                <p class="text-sm text-gray-600">Choisissez les options d'export :</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grade</label>
                    <Select v-model="exportFilters.grade" :options="gradeOptions" optionLabel="label"
                        optionValue="value" placeholder="Tous les grades" class="w-full" showClear />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <Select v-model="exportFilters.statut" :options="statutOptions" optionLabel="label"
                        optionValue="value" placeholder="Tous les statuts" class="w-full" showClear />
                </div>
                <div class="flex items-center gap-2">
                    <Checkbox v-model="exportFilters.useCurrentFilters" binary />
                    <label class="text-sm text-gray-700">Utiliser les filtres actuels</label>
                </div>
                <div class="flex items-center gap-2">
                    <Checkbox v-model="exportFilters.includeSearch" binary />
                    <label class="text-sm text-gray-700">Appliquer la recherche en cours</label>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button label="Annuler" icon="pi pi-times" class="p-button-text text-gray-500 hover:text-gray-700"
                        @click="exportDialogVisible = false" />
                    <Button label="Exporter" icon="pi pi-file-excel"
                        class="bg-green-500 hover:bg-green-600 border-green-500 text-white" :loading="exporting"
                        @click="exportMilitaires" />
                </div>
            </template>
        </Dialog>

        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Badge from 'primevue/badge';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Checkbox from 'primevue/checkbox';
import { useToast } from 'primevue/usetoast';
import debounce from 'lodash/debounce';

const toast = useToast();
const loading = ref(false);
const deleteDialogVisible = ref(false);
const militaireToDelete = ref(null);
const exportDialogVisible = ref(false);
const exporting = ref(false);

const gradeOptions = ref([]);
const statutOptions = [
    { label: 'Actif', value: 'actif' },
    { label: 'Retraité', value: 'retraité' },
    { label: 'Déserteur', value: 'déserteur' },
    { label: 'Décédé', value: 'décédé' },
    { label: 'Formation', value: 'formation' },
    { label: 'Stage', value: 'stage' }
];

const props = defineProps({
    militaires: { type: Object, required: true },
    statistiques: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    grades: { type: Array, default: () => [] }
});

const formatAnciennete = (annees) => {
    if (!annees && annees !== 0) return '0 ans';
    return `${Math.floor(annees)} ans`;
};

onMounted(() => {
    gradeOptions.value = [
        { label: 'Tous les grades', value: null },
        ...props.grades.map(g => ({ label: g.nom_grade, value: g.nom_grade }))
    ];
});

const filters = reactive({
    search: props.filters?.search || '',
    grade: props.filters?.grade || null,
    statut: props.filters?.statut || null
});

const exportFilters = reactive({
    grade: null,
    statut: null,
    useCurrentFilters: true,
    includeSearch: true
});

const debouncedSearch = debounce(() => {
    loadMilitaires(1);
}, 500);

const onSearchInput = () => {
    debouncedSearch();
};

const onFilterChange = () => {
    loadMilitaires(1);
};

watch(() => filters.search, () => debouncedSearch());
watch([() => filters.grade, () => filters.statut], () => loadMilitaires(1));

const getStatutStyle = (statut) => {
    const styles = {
        'actif': { background: '#7dd3fc', color: '#0369a1' },
        'retraité': { background: '#e5e7eb', color: '#374151' },
        'déserteur': { background: '#fecaca', color: '#991b1b' },
        'décédé': { background: '#fecaca', color: '#991b1b' },
        'formation': { background: '#bae6fd', color: '#0369a1' },
        'stage': { background: '#fed7aa', color: '#c2410c' }
    };
    return styles[statut] || { background: '#e5e7eb', color: '#374151' };
};

const loadMilitaires = (page = 1) => {
    loading.value = true;
    router.get(route('militaires.index'), {
        page,
        search: filters.search,
        grade: filters.grade,
        statut: filters.statut
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { loading.value = false; },
        onError: () => {
            loading.value = false;
            toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les militaires', life: 3000 });
        }
    });
};

const resetFilters = () => {
    filters.search = '';
    filters.grade = null;
    filters.statut = null;
};

const onPageChange = (event) => changePage(event.page + 1);

const changePage = (page) => {
    if (page >= 1 && page <= props.militaires.last_page) loadMilitaires(page);
};

const viewMilitaire = (id) => router.visit(route('militaires.show', id));
const editMilitaire = (id) => router.visit(route('militaires.edit', id));
const createMilitaire = () => router.visit(route('militaires.create'));
const importExcel = () => router.visit(route('militaires.import'));

const openExportModal = () => {
    exportFilters.grade = filters.grade;
    exportFilters.statut = filters.statut;
    exportFilters.useCurrentFilters = true;
    exportFilters.includeSearch = true;
    exportDialogVisible.value = true;
};

const exportMilitaires = () => {
    exporting.value = true;

    let params = {};

    if (exportFilters.useCurrentFilters) {
        if (filters.search) params.search = filters.search;
        if (filters.grade) params.grade = filters.grade;
        if (filters.statut) params.statut = filters.statut;
    } else {
        if (exportFilters.includeSearch && filters.search) params.search = filters.search;
        if (exportFilters.grade) params.grade = exportFilters.grade;
        if (exportFilters.statut) params.statut = exportFilters.statut;
    }

    // Fallback
    if (Object.keys(params).length === 0) {
        if (exportFilters.grade) params.grade = exportFilters.grade;
        if (exportFilters.statut) params.statut = exportFilters.statut;
        if (exportFilters.includeSearch && filters.search) params.search = filters.search;
    }

    // Nettoyer les valeurs null/undefined
    Object.keys(params).forEach(key => {
        if (params[key] === null || params[key] === undefined || params[key] === '') delete params[key];
    });

    const baseUrl = '/militaires/export';
    const queryString = new URLSearchParams(params).toString();
    const url = baseUrl + (queryString ? '?' + queryString : '');

    window.open(url, '_blank');

    exporting.value = false;
    exportDialogVisible.value = false;
};

const confirmDelete = (militaire) => {
    militaireToDelete.value = militaire;
    deleteDialogVisible.value = true;
};

const deleteMilitaire = () => {
    if (!militaireToDelete.value) return;
    router.delete(route('militaires.destroy', militaireToDelete.value.id), {
        onSuccess: () => {
            deleteDialogVisible.value = false;
            militaireToDelete.value = null;
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Militaire supprimé avec succès', life: 3000 });
            loadMilitaires(1);
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de supprimer le militaire', life: 3000 });
        }
    });
};
</script>

<style scoped>
/* ============================================================
   STYLES GÉNÉRAUX
   ============================================================ */
.text-sky-600 {
    color: #0284c7;
}

.text-sky-500 {
    color: #0ea5e9;
}

.text-emerald-600 {
    color: #059669;
}

.text-amber-600 {
    color: #d97706;
}

.text-red-600 {
    color: #dc2626;
}

.text-white {
    color: white;
}

.bg-sky-500 {
    background-color: #0ea5e9;
}

.hover\:bg-sky-600:hover {
    background-color: #0284c7;
}

.border-sky-500 {
    border-color: #0ea5e9;
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

.bg-red-500 {
    background-color: #ef4444;
}

.hover\:bg-red-600:hover {
    background-color: #dc2626;
}

.border-red-500 {
    border-color: #ef4444;
}

.bg-green-500 {
    background-color: #22c55e;
}

.hover\:bg-green-600:hover {
    background-color: #16a34a;
}

.border-green-500 {
    border-color: #22c55e;
}

/* ============================================================
   DATATABLE
   ============================================================ */
:deep(.p-datatable) {
    font-size: 0.875rem;
}

:deep(.p-datatable .p-datatable-thead > tr > th) {
    background: #f8fafc;
    color: #1e293b;
    font-weight: 600;
    padding: 0.75rem 0.75rem;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}

:deep(.p-datatable .p-datatable-tbody > tr > td) {
    padding: 0.6rem 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

:deep(.p-datatable .p-datatable-tbody > tr:hover) {
    background-color: #f0f9ff;
}

:deep(.p-datatable .p-datatable-tbody > tr:nth-child(even)) {
    background-color: #fafbfc;
}

:deep(.p-datatable .p-datatable-tbody > tr:nth-child(even):hover) {
    background-color: #f0f9ff;
}

/* ============================================================
   TAGS ET BADGES
   ============================================================ */
:deep(.p-tag) {
    padding: 0.25rem 0.6rem;
    border-radius: 0.375rem;
    font-weight: 500;
    font-size: 0.75rem;
}

:deep(.p-tag .p-tag-value) {
    font-size: 0.75rem;
}

:deep(.p-badge) {
    font-size: 0.65rem;
    min-width: 1.2rem;
    height: 1.2rem;
    line-height: 1.2rem;
    border-radius: 50%;
}

/* ============================================================
   BOUTONS
   ============================================================ */
:deep(.p-button.p-button-sm) {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

:deep(.p-button.p-button-rounded.p-button-text) {
    width: 1.75rem;
    height: 1.75rem;
    min-width: 1.75rem;
}

:deep(.p-button.p-button-link) {
    font-size: 0.875rem;
}

/* ============================================================
   INPUTS ET SELECT
   ============================================================ */
:deep(.p-inputtext) {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
}

:deep(.p-select) {
    font-size: 0.875rem;
}

:deep(.p-select .p-select-label) {
    padding: 0.5rem 0.75rem;
}

:deep(.p-input-icon-left > i) {
    left: 0.75rem;
}

:deep(.p-input-icon-left > .p-inputtext) {
    padding-left: 2.5rem;
}

/* ============================================================
   DIALOG
   ============================================================ */
:deep(.p-dialog .p-dialog-header) {
    padding: 1.25rem 1.5rem 0.75rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

:deep(.p-dialog .p-dialog-content) {
    padding: 1.25rem 1.5rem;
}

:deep(.p-dialog .p-dialog-footer) {
    padding: 0.75rem 1.5rem 1.25rem 1.5rem;
    border-top: 1px solid #e2e8f0;
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 768px) {
    :deep(.p-datatable .p-datatable-thead > tr > th) {
        font-size: 0.7rem;
        padding: 0.4rem 0.4rem;
        white-space: nowrap;
    }

    :deep(.p-datatable .p-datatable-tbody > tr > td) {
        font-size: 0.7rem;
        padding: 0.4rem 0.4rem;
    }

    :deep(.p-tag) {
        font-size: 0.6rem;
        padding: 0.15rem 0.35rem;
    }

    :deep(.p-button.p-button-rounded.p-button-text) {
        width: 1.5rem;
        height: 1.5rem;
        min-width: 1.5rem;
    }

    :deep(.p-button.p-button-rounded.p-button-text .p-button-icon) {
        font-size: 0.7rem;
    }

    .text-xl {
        font-size: 1.1rem;
    }
}

@media (max-width: 480px) {
    :deep(.p-datatable .p-datatable-thead > tr > th) {
        font-size: 0.6rem;
        padding: 0.3rem 0.3rem;
    }

    :deep(.p-datatable .p-datatable-tbody > tr > td) {
        font-size: 0.6rem;
        padding: 0.3rem 0.3rem;
    }

    :deep(.p-tag) {
        font-size: 0.5rem;
        padding: 0.1rem 0.25rem;
    }
}

/* ============================================================
   UTILITAIRES
   ============================================================ */
.truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.max-w-100px {
    max-width: 100px;
}
</style>
