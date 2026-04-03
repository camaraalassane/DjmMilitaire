<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white">
                    {{ grade.nom_grade }}
                </h2>
                <div class="flex gap-2">
                    <Button label="Retour à la liste" 
                            icon="pi pi-arrow-left"
                            class="p-button-sm bg-sky-400 hover:bg-sky-500 border-sky-400 text-white"
                            @click="goBack" />
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Détails du grade -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                    <div class="p-6">
                        <Card>
                            <template #title>
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-tag text-sky-500"></i>
                                    <span class="text-sky-600">Détails du grade</span>
                                </div>
                            </template>
                            
                            <template #content>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- Code -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-qrcode text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Code</span>
                                        </div>
                                        <Tag :value="grade.code_grade" 
                                             style="background: #bae6fd; color: #0369a1;" 
                                             class="text-base" />
                                    </div>

                                    <!-- Type -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-list text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Type</span>
                                        </div>
                                        <Tag :value="grade.type_grade" 
                                             :style="getTypeStyle(grade.type_grade)" />
                                    </div>

                                    <!-- Ordre hiérarchique -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-sort-numeric-up text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Ordre hiérarchique</span>
                                        </div>
                                        <Badge :value="grade.ordre" 
                                               style="background: #bae6fd; color: #0369a1;" 
                                               size="large" />
                                    </div>

                                    <!-- Effectif actif -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-users text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Effectif actif</span>
                                        </div>
                                        <div class="text-3xl font-bold text-sky-600">{{ statistiques.effectif_actif }}</div>
                                        <small class="text-gray-500">militaires actifs</small>
                                    </div>

                                    <!-- Total militaires -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-users text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Total</span>
                                        </div>
                                        <div class="text-3xl font-bold text-sky-600">{{ statistiques.effectif_total }}</div>
                                        <small class="text-gray-500">militaires (tous statuts)</small>
                                    </div>

                                    <!-- Retraités -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-user-minus text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Retraités</span>
                                        </div>
                                        <div class="text-3xl font-bold text-sky-600">{{ statistiques.effectif_retraite }}</div>
                                        <small class="text-gray-500">militaires retraités</small>
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>
                </div>

                <!-- Militaires ayant ce grade -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <Card>
                            <template #title>
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-users text-sky-500"></i>
                                    <span class="text-gray-800">Militaires ayant ce grade (actifs)</span>
                                </div>
                            </template>
                            
                            <template #content>
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
                                    <Column field="matricule" header="Matricule" style="width: 120px">
                                        <template #body="slotProps">
                                            <Tag :value="slotProps.data.matricule" 
                                                 style="background: #bae6fd; color: #0369a1;" />
                                        </template>
                                    </Column>
                                    
                                    <!-- Nom & Prénom -->
                                    <Column field="nom" header="Nom & Prénom">
                                        <template #body="slotProps">
                                            <Button :label="slotProps.data.nom + ' ' + slotProps.data.prenom"
                                                    class="p-button-link p-0 text-sky-500 hover:text-sky-600 font-medium"
                                                    @click="viewMilitaire(slotProps.data.id)" />
                                        </template>
                                    </Column>
                                    
                                    <!-- Âge -->
                                    <Column header="Âge" style="width: 100px">
                                        <template #body="slotProps">
                                            <Tag :value="slotProps.data.age + ' ans'" 
                                                 style="background: #bae6fd; color: #0369a1;" />
                                        </template>
                                    </Column>
                                    
                                    <!-- Ancienneté -->
                                    <Column header="Ancienneté" style="width: 120px">
                                        <template #body="slotProps">
                                            <Tag :value="slotProps.data.anciennete" 
                                                 style="background: #fed7aa; color: #c2410c;" />
                                        </template>
                                    </Column>
                                    
                                    <!-- Date retraite -->
                                    <Column header="Date retraite" style="width: 120px">
                                        <template #body="slotProps">
                                            <div v-if="slotProps.data.date_retraite" class="text-gray-700">
                                                {{ slotProps.data.date_retraite }}
                                            </div>
                                            <span v-else class="text-gray-400">-</span>
                                        </template>
                                    </Column>
                                    
                                    <!-- Actions -->
                                    <Column header="Actions" style="width: 100px">
                                        <template #body="slotProps">
                                            <Button icon="pi pi-eye" 
                                                    class="p-button-rounded p-button-text p-button-sm text-sky-500 hover:text-sky-600"
                                                    v-tooltip.top="'Voir le militaire'"
                                                    @click="viewMilitaire(slotProps.data.id)" />
                                        </template>
                                    </Column>

                                    <template #empty>
                                        <div class="text-center py-8 text-gray-500">
                                            <i class="pi pi-users text-4xl mb-2"></i>
                                            <p>Aucun militaire actif à ce grade</p>
                                        </div>
                                    </template>
                                </DataTable>

                                <!-- Informations de pagination -->
                                <div class="flex justify-between items-center mt-4 text-sm text-gray-600">
                                    <div>
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
                            </template>
                        </Card>
                    </div>
                </div>
            </div>
        </div>

        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Badge from 'primevue/badge';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import Tooltip from 'primevue/tooltip';

const props = defineProps({
    grade: {
        type: Object,
        required: true
    },
    militaires: {
        type: Object,
        required: true
    },
    statistiques: {
        type: Object,
        required: true
    }
});

const toast = useToast();
const loading = ref(false);

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

// Changement de page
const onPageChange = (event) => {
    changePage(event.page + 1);
};

const changePage = (page) => {
    if (page >= 1 && page <= props.militaires.last_page) {
        loading.value = true;
        
        router.get(route('grades.show', props.grade.id), {
            page
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
                    detail: 'Impossible de charger la page',
                    life: 3000
                });
            }
        });
    }
};

// Navigation
const viewMilitaire = (id) => {
    router.visit(route('militaires.show', id));
};

const goBack = () => {
    router.visit(route('grades.index'));
};
</script>

<style scoped>
:deep(.p-card) {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

:deep(.p-card .p-card-title) {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}

:deep(.p-tag) {
    font-size: 0.9rem;
    padding: 0.35rem 0.75rem;
    border-radius: 0.5rem;
    font-weight: 500;
}

:deep(.p-badge) {
    font-size: 1rem;
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
}

:deep(.p-datatable) {
    font-size: 0.95rem;
}

:deep(.p-datatable .p-datatable-tbody > tr:hover) {
    background-color: #f0f9ff;
}

:deep(.p-button-link) {
    text-decoration: none;
    font-weight: 500;
}

:deep(.p-button-link:hover) {
    text-decoration: underline;
}

/* Style pour les cartes d'information */
.border {
    transition: all 0.2s ease;
    background: white;
    border-color: #e5e7eb;
}

.border:hover {
    border-color: #7dd3fc !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transform: translateY(-2px);
}

/* Styles personnalisés */
.text-sky-500 {
    color: #0ea5e9;
}

.text-sky-600 {
    color: #0284c7;
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

.hover\:text-sky-600:hover {
    color: #0284c7;
}

.text-white {
    color: white;
}
</style>