<template>
    <AuthenticatedLayout>
        <div class="py-12">
            <!-- Header avec titre et boutons -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 px-4 md:px-0">
                <h2 class="font-semibold text-xl text-sky-600">
                    Gestion des Militaires
                </h2>
                <div class="flex flex-wrap gap-3">
                    <button 
                        @click="createMilitaire"
                        class="px-4 py-2 bg-white text-sky-600 rounded-lg font-medium hover:bg-sky-50 transition-colors flex items-center gap-2 shadow-sm text-sm md:text-base">
                        <i class="pi pi-plus text-sm md:text-base"></i>
                        Nouveau militaire
                    </button>
                    <button 
                        @click="importExcel"
                        class="px-4 py-2 bg-white text-emerald-600 rounded-lg font-medium hover:bg-emerald-50 transition-colors flex items-center gap-2 shadow-sm text-sm md:text-base">
                        <i class="pi pi-upload text-sm md:text-base"></i>
                        Importer Excel
                    </button>
                </div>
            </div>
       
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Statistiques - Responsive grid -->
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

                <!-- Filtres - Responsive -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                    <div class="p-4">
                        <form @submit.prevent="applyFilters" class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <span class="p-input-icon-left w-full">
                                    
                                    <InputText v-model="filters.search" 
                                              placeholder="Rechercher par nom, prénom ou matricule..." 
                                              class="w-full" />
                                </span>
                            </div>
                            <div class="w-full md:w-48">
                                <Select v-model="filters.grade" 
                                        :options="gradeOptions" 
                                        optionLabel="label" 
                                        optionValue="value"
                                        placeholder="Tous les grades"
                                        class="w-full"
                                        showClear />
                            </div>
                            <div class="w-full md:w-48">
                                <Select v-model="filters.statut" 
                                        :options="statutOptions" 
                                        optionLabel="label" 
                                        optionValue="value"
                                        placeholder="Tous les statuts"
                                        class="w-full"
                                        showClear />
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Button type="submit" 
                                        label="Filtrer" 
                                        icon="pi pi-filter"
                                        class="p-button-sm bg-sky-500 hover:bg-sky-600 border-sky-500 text-white flex-1 md:flex-none" />
                                <Button label="Réinitialiser" 
                                        icon="pi pi-times"
                                        class="p-button-sm bg-gray-500 hover:bg-gray-600 border-gray-500 text-white flex-1 md:flex-none"
                                        @click="resetFilters" />
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tableau des militaires -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-4 md:p-6">
                        <DataTable :value="militaires.data" 
                                   stripedRows 
                                   responsiveLayout="scroll"
                                   :loading="loading"
                                   paginator
                                   :rows="militaires.per_page"
                                   :totalRecords="militaires.total"
                                   :first="(militaires.current_page - 1) * militaires.per_page"
                                   @page="onPageChange"
                                   class="p-datatable-sm">
                            
                            <!-- Matricule -->
                            <Column field="matricule" header="Matricule">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.matricule" 
                                         style="background: #bae6fd; color: #0369a1;" />
                                </template>
                            </Column>

                            <!-- Nom & Prénom -->
                            <Column field="nom" header="Nom & Prénom">
                                <template #body="slotProps">
                                    <div class="flex items-center gap-2">
                                        <Button :label="slotProps.data.nom + ' ' + slotProps.data.prenom"
                                                class="p-button-link p-0 text-sky-600 hover:text-sky-700 font-medium text-left"
                                                @click="viewMilitaire(slotProps.data.id)" />
                                        <Badge v-if="slotProps.data.alertes_count > 0" 
                                               value="!" 
                                               style="background: #f97316; color: white;" 
                                               class="ml-2" />
                                    </div>
                                </template>
                            </Column>

                            <!-- Grade -->
                            <Column field="grade_actuel" header="Grade">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.grade_actuel" 
                                         style="background: #7dd3fc; color: #0369a1;" />
                                </template>
                            </Column>

                            <!-- Date entrée service -->
                            <Column field="date_entree_service" header="Entrée service">
                                <template #body="slotProps">
                                    <span class="text-sm">{{ slotProps.data.date_entree_service || '-' }}</span>
                                </template>
                            </Column>

                            <!-- Âge -->
                            <Column header="Âge">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.age + ' ans'" 
                                         style="background: #7dd3fc; color: #0369a1;" />
                                </template>
                            </Column>

                            <!-- Ancienneté service - CORRIGÉ -->
                            <Column header="Ancienneté">
                                <template #body="slotProps">
                                    <span class="text-sm">{{ formatAnciennete(slotProps.data.anciennete) }}</span>
                                </template>
                            </Column>

                            <!-- Statut -->
                            <Column header="Statut">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.statut" 
                                         :style="getStatutStyle(slotProps.data.statut)" 
                                         class="text-xs" />
                                </template>
                            </Column>

                            <!-- Actions -->
                            <Column header="Actions">
                                <template #body="slotProps">
                                    <div class="flex gap-1">
                                        <Button icon="pi pi-eye" 
                                                class="p-button-rounded p-button-text p-button-sm text-sky-500 hover:text-sky-600"
                                                @click="viewMilitaire(slotProps.data.id)"
                                                v-tooltip.top="'Voir'" />
                                        <Button icon="pi pi-pencil" 
                                                class="p-button-rounded p-button-text p-button-sm text-amber-500 hover:text-amber-600"
                                                @click="editMilitaire(slotProps.data.id)"
                                                v-tooltip.top="'Modifier'" />
                                        <Button icon="pi pi-trash" 
                                                class="p-button-rounded p-button-text p-button-sm text-red-500 hover:text-red-600"
                                                @click="confirmDelete(slotProps.data)"
                                                v-tooltip.top="'Supprimer'" />
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

                        <!-- Informations de pagination -->
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-3 mt-4 text-sm text-gray-600">
                            <div class="text-center sm:text-left">
                                Affichage de {{ militaires.from }} à {{ militaires.to }} sur {{ militaires.total }} militaires
                            </div>
                            <div class="flex gap-1">
                                <Button icon="pi pi-chevron-left" 
                                        class="p-button-rounded p-button-text p-button-sm text-gray-600 hover:text-sky-500"
                                        :disabled="militaires.current_page === 1"
                                        @click="changePage(militaires.current_page - 1)" />
                                <span class="px-3 py-1">
                                    Page {{ militaires.current_page }} / {{ militaires.last_page }}
                                </span>
                                <Button icon="pi pi-chevron-right" 
                                        class="p-button-rounded p-button-text p-button-sm text-gray-600 hover:text-sky-500"
                                        :disabled="militaires.current_page === militaires.last_page"
                                        @click="changePage(militaires.current_page + 1)" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dialog de confirmation de suppression -->
        <Dialog v-model:visible="deleteDialogVisible" 
                header="Confirmation" 
                :modal="true"
                :style="{ width: '90%', maxWidth: '400px' }"
                class="p-fluid">
            <div class="flex items-center gap-3 mb-4">
                <i class="pi pi-exclamation-triangle text-3xl text-amber-500"></i>
                <p class="text-gray-700 text-sm">Êtes-vous sûr de vouloir supprimer le militaire <strong>{{ militaireToDelete?.nom }} {{ militaireToDelete?.prenom }}</strong> ?</p>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button label="Non" 
                            icon="pi pi-times" 
                            class="p-button-text text-gray-500 hover:text-gray-700"
                            @click="deleteDialogVisible = false" />
                    <Button label="Oui" 
                            icon="pi pi-check" 
                            class="bg-red-500 hover:bg-red-600 border-red-500 text-white"
                            @click="deleteMilitaire" />
                </div>
            </template>
        </Dialog>

        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
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
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const loading = ref(false);
const deleteDialogVisible = ref(false);
const militaireToDelete = ref(null);

// Options pour les filtres
const gradeOptions = ref([]);
const statutOptions = [
    { label: 'Actif', value: 'actif' },
    { label: 'Retraité', value: 'retraité' },
    { label: 'Déserteur', value: 'déserteur' },
    { label: 'Décédé', value: 'décédé' },
    { label: 'Formation', value: 'formation' },
    { label: 'Stage', value: 'stage' }
];

// Props reçus du contrôleur
const props = defineProps({
    militaires: {
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
    grades: {
        type: Array,
        default: () => []
    }
});

// Formater l'ancienneté (arrondir à l'entier)
const formatAnciennete = (annees) => {
    if (!annees && annees !== 0) return '0 ans';
    return `${Math.floor(annees)} ans`;
};

// Initialiser les options de grade
onMounted(() => {
    gradeOptions.value = [
        { label: 'Tous les grades', value: null },
        ...props.grades.map(g => ({ label: g.nom_grade, value: g.nom_grade }))
    ];
});

// État des filtres
const filters = reactive({
    search: props.filters?.search || '',
    grade: props.filters?.grade || null,
    statut: props.filters?.statut || null
});

// Style pour les badges selon le statut
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

// Appliquer les filtres
const applyFilters = () => {
    loading.value = true;
    
    router.get(route('militaires.index'), {
        search: filters.search,
        grade: filters.grade,
        statut: filters.statut
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
                detail: 'Impossible de charger les militaires',
                life: 3000
            });
        }
    });
};

// Réinitialiser les filtres
const resetFilters = () => {
    filters.search = '';
    filters.grade = null;
    filters.statut = null;
    applyFilters();
};

// Changement de page
const onPageChange = (event) => {
    changePage(event.page + 1);
};

const changePage = (page) => {
    if (page >= 1 && page <= props.militaires.last_page) {
        loading.value = true;
        
        router.get(route('militaires.index'), {
            ...filters,
            page
        }, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                loading.value = false;
            }
        });
    }
};

// Actions sur les militaires
const viewMilitaire = (id) => {
    router.visit(route('militaires.show', id));
};

const editMilitaire = (id) => {
    router.visit(route('militaires.edit', id));
};

const createMilitaire = () => {
    router.visit(route('militaires.create'));
};

const importExcel = () => {
    router.visit(route('militaires.import'));
};

// Confirmation de suppression
const confirmDelete = (militaire) => {
    militaireToDelete.value = militaire;
    deleteDialogVisible.value = true;
};

// Supprimer un militaire
const deleteMilitaire = () => {
    if (!militaireToDelete.value) return;
    
    router.delete(route('militaires.destroy', militaireToDelete.value.id), {
        onSuccess: () => {
            deleteDialogVisible.value = false;
            militaireToDelete.value = null;
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Militaire supprimé avec succès',
                life: 3000
            });
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Impossible de supprimer le militaire',
                life: 3000
            });
        }
    });
};
</script>

<style scoped>
:deep(.p-datatable) {
    font-size: 0.875rem;
}

:deep(.p-datatable .p-datatable-tbody > tr:hover) {
    background-color: #f0f9ff;
}

:deep(.p-tag) {
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-weight: 500;
}

:deep(.p-button.p-button-sm) {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

:deep(.p-button.p-button-rounded.p-button-text) {
    width: 1.75rem;
    height: 1.75rem;
}

/* Responsive pour le tableau sur mobile */
@media (max-width: 768px) {
    :deep(.p-datatable .p-datatable-thead > tr > th) {
        font-size: 0.75rem;
        padding: 0.5rem;
    }
    
    :deep(.p-datatable .p-datatable-tbody > tr > td) {
        font-size: 0.75rem;
        padding: 0.5rem;
    }
    
    :deep(.p-tag) {
        font-size: 0.65rem;
        padding: 0.2rem 0.4rem;
    }
}

/* Styles personnalisés */
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

.text-white {
    color: white;
}
</style>