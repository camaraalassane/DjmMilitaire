<template>
    <AuthenticatedLayout>
        <!-- Header -->
        <div class="bg-gradient-to-r from-sky-500 to-sky-700 py-4 px-6">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white">
                    Éligibilités
                </h2>
                <Button v-if="hasData && currentList.length > 0" 
                        label="Exporter la liste en Excel" 
                        icon="pi pi-file-excel"
                        class="p-button-sm bg-white text-emerald-600 hover:bg-emerald-50 border border-emerald-200"
                        @click="exportData" />
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Cartes de navigation avec les VRAIS totaux -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- Carte Promotions -->
                    <div class="cursor-pointer transition-all hover:shadow-md rounded-lg overflow-hidden"
                          :class="selectedType === 'promotions' ? 'ring-2 ring-sky-500 shadow-md' : ''"
                          @click="selectType('promotions')">
                        <div class="bg-gradient-to-r from-sky-500 to-sky-600 p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                    <i class="pi pi-star text-xl text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-white">Promotions</h3>
                                    <p class="text-xs text-white/80">Propositions de grade</p>
                                </div>
                            </div>
                            <div class="text-3xl font-bold text-white">{{ totalPromotions }}</div>
                        </div>
                    </div>

                    <!-- Carte Formations -->
                    <div class="cursor-pointer transition-all hover:shadow-md rounded-lg overflow-hidden"
                          :class="selectedType === 'formations' ? 'ring-2 ring-amber-500 shadow-md' : ''"
                          @click="selectType('formations')">
                        <div class="bg-gradient-to-r from-amber-500 to-amber-600 p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                    <i class="pi pi-book text-xl text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-white">Formations</h3>
                                    <p class="text-xs text-white/80">Formations disponibles</p>
                                </div>
                            </div>
                            <div class="text-3xl font-bold text-white">{{ totalFormations }}</div>
                        </div>
                    </div>
                </div>

                <!-- Filtres Formation (badges cliquables) -->
                <div v-if="selectedType === 'formations' && formationsListe.length > 0" class="bg-white rounded-lg shadow-sm p-4 mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">📚 Filtrer par formation :</label>
                    <div class="flex flex-wrap gap-2">
                        <Badge v-for="form in formationsListe" 
                               :key="form.id"
                               :value="`${form.nom} (${getFormationCount(form.id)})`"
                               :class="[
                                   'cursor-pointer px-3 py-2 text-sm font-medium rounded-full transition-all',
                                   selectedFormation === form.id 
                                       ? 'bg-amber-600 text-white' 
                                       : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                               ]"
                               @click="toggleFormation(form.id)" />
                    </div>
                    <div v-if="selectedFormation" class="mt-3 text-right">
                        <Button label="Effacer le filtre formation" 
                                icon="pi pi-filter-slash"
                                class="p-button-text p-button-sm text-gray-500"
                                @click="clearFormationFilter" />
                    </div>
                </div>

                <!-- Filtres Grade (badges cliquables avec compteurs) -->
                <div v-if="gradesListe.length > 0 && (selectedType === 'promotions' || selectedType === 'formations')" class="bg-white rounded-lg shadow-sm p-4 mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">⭐ Filtrer par grade :</label>
                    <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto">
                        <Badge v-for="grade in gradesListe" 
                               :key="grade.id"
                               :value="`${grade.nom} (${getGradeCount(grade.id)})`"
                               :class="[
                                   'cursor-pointer px-3 py-2 text-sm font-medium rounded-full transition-all',
                                   selectedGrade === grade.id 
                                       ? 'bg-sky-600 text-white' 
                                       : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                               ]"
                               @click="toggleGrade(grade.id)" />
                    </div>
                    <div v-if="selectedGrade" class="mt-3 text-right">
                        <Button label="Effacer le filtre grade" 
                                icon="pi pi-filter-slash"
                                class="p-button-text p-button-sm text-gray-500"
                                @click="clearGradeFilter" />
                    </div>
                </div>

                <!-- Loading -->
                <div v-if="loading" class="flex justify-center py-12">
                    <i class="pi pi-spin pi-spinner text-3xl text-sky-500"></i>
                </div>

                <!-- Résultats - TABLEAU POUR PROMOTIONS -->
                <div v-else-if="selectedType === 'promotions' && promotions.length > 0" class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <DataTable :value="promotions" stripedRows responsiveLayout="scroll" class="p-datatable-sm">
                        <Column field="militaire.matricule" header="Matricule" sortable>
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.militaire.matricule" 
                                     style="background: #bae6fd; color: #0369a1;" />
                            </template>
                        </Column>
                        <Column field="militaire.nom" header="Nom" sortable>
                            <template #body="slotProps">
                                <Button :label="slotProps.data.militaire.nom + ' ' + slotProps.data.militaire.prenom"
                                        class="p-button-link p-0 text-sky-600 hover:text-sky-800"
                                        @click="viewMilitaire(slotProps.data.militaire.id)" />
                            </template>
                        </Column>
                        <Column field="militaire.grade_actuel" header="Grade actuel" sortable>
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.militaire.grade_actuel" 
                                     style="background: #e5e7eb; color: #374151;" />
                            </template>
                        </Column>
                        <Column field="grade_cible" header="Grade cible" sortable>
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.grade_cible" 
                                     style="background: #dbeafe; color: #1e40af;" />
                            </template>
                        </Column>
                        <Column field="message" header="Condition" />
                        <Column field="date_estimation" header="Date estimation" sortable>
                            <template #body="slotProps">
                                {{ formatDate(slotProps.data.date_estimation) }}
                            </template>
                        </Column>
                    </DataTable>
                </div>

                <!-- Résultats - TABLEAU POUR FORMATIONS -->
                <div v-else-if="selectedType === 'formations' && formations.length > 0" class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <DataTable :value="formations" stripedRows responsiveLayout="scroll" class="p-datatable-sm">
                        <Column field="militaire.matricule" header="Matricule" sortable>
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.militaire.matricule" 
                                     style="background: #bae6fd; color: #0369a1;" />
                            </template>
                        </Column>
                        <Column field="militaire.nom" header="Nom" sortable>
                            <template #body="slotProps">
                                <Button :label="slotProps.data.militaire.nom + ' ' + slotProps.data.militaire.prenom"
                                        class="p-button-link p-0 text-sky-600 hover:text-sky-800"
                                        @click="viewMilitaire(slotProps.data.militaire.id)" />
                            </template>
                        </Column>
                        <Column field="militaire.grade_actuel" header="Grade actuel" sortable>
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.militaire.grade_actuel" 
                                     style="background: #e5e7eb; color: #374151;" />
                            </template>
                        </Column>
                        <Column field="nom_formation" header="Formation" sortable>
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.nom_formation" 
                                     style="background: #fef3c7; color: #92400e;" />
                            </template>
                        </Column>
                        <Column field="message" header="Condition" />
                        <Column field="date_estimation" header="Date estimation" sortable>
                            <template #body="slotProps">
                                {{ formatDate(slotProps.data.date_estimation) }}
                            </template>
                        </Column>
                    </DataTable>
                </div>

                <!-- Résultats - TABLEAU POUR RETRAITES -->
                <div v-else-if="selectedType === 'retraites' && retraites.length > 0" class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <DataTable :value="retraites" stripedRows responsiveLayout="scroll" class="p-datatable-sm">
                        <Column field="militaire.matricule" header="Matricule" sortable>
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.militaire.matricule" 
                                     style="background: #bae6fd; color: #0369a1;" />
                            </template>
                        </Column>
                        <Column field="militaire.nom" header="Nom" sortable>
                            <template #body="slotProps">
                                <Button :label="slotProps.data.militaire.nom + ' ' + slotProps.data.militaire.prenom"
                                        class="p-button-link p-0 text-sky-600 hover:text-sky-800"
                                        @click="viewMilitaire(slotProps.data.militaire.id)" />
                            </template>
                        </Column>
                        <Column field="militaire.grade_actuel" header="Grade actuel" sortable>
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.militaire.grade_actuel" 
                                     style="background: #e5e7eb; color: #374151;" />
                            </template>
                        </Column>
                        <Column field="date_retraite_formatted" header="Date retraite" sortable />
                        <Column header="Mois restants" sortable>
                            <template #body="slotProps">
                                <Tag :value="formatMoisRestants(slotProps.data.mois_restants)" 
                                     :style="getMoisRestantsStyle(slotProps.data.mois_restants)" />
                            </template>
                        </Column>
                    </DataTable>
                </div>

                <!-- Aucun résultat -->
                <div v-else-if="selectedType && !loading && currentList.length === 0" 
                     class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <i class="pi pi-inbox text-5xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">Aucune éligibilité trouvée</p>
                    <p class="text-sm text-gray-400 mt-1">Essayez un autre filtre</p>
                </div>

                <!-- Message initial -->
                <div v-if="!selectedType" class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <i class="pi pi-hand-point-up text-5xl text-sky-300 mb-3"></i>
                    <p class="text-gray-500">Cliquez sur une carte pour voir les éligibilités</p>
                </div>
            </div>
        </div>

        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Badge from 'primevue/badge';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    formationsListe: { type: Array, default: () => [] },
    gradesListe: { type: Array, default: () => [] }
});

const toast = useToast();
const loading = ref(false);
const promotions = ref([]);
const formations = ref([]);
const retraites = ref([]);

// Totaux globaux (calculés une fois au chargement)
const totalPromotions = ref(0);
const totalFormations = ref(0);

// Cache pour les compteurs par formation et grade
const formationCounts = ref({});
const gradeCounts = ref({});

// Filtres
const selectedType = ref('');
const selectedFormation = ref('');
const selectedGrade = ref('');

const currentList = computed(() => {
    if (selectedType.value === 'promotions') return promotions.value;
    if (selectedType.value === 'formations') return formations.value;
    if (selectedType.value === 'retraites') return retraites.value;
    return [];
});

const hasData = computed(() => currentList.value.length > 0);

const getFormationCount = (formationId) => {
    return formationCounts.value[formationId] || 0;
};

const getGradeCount = (gradeId) => {
    return gradeCounts.value[gradeId] || 0;
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR');
};

const formatMoisRestants = (mois) => {
    if (mois === 0) return "Aujourd'hui";
    if (mois === 1) return "Dans 1 mois";
    return `Dans ${mois} mois`;
};

const getMoisRestantsStyle = (mois) => {
    if (mois <= 0) return { background: '#fecaca', color: '#991b1b' };
    if (mois <= 1) return { background: '#fed7aa', color: '#c2410c' };
    if (mois <= 3) return { background: '#fef3c7', color: '#92400e' };
    return { background: '#dbeafe', color: '#1e40af' };
};

// Charger les statistiques globales
const loadGlobalStats = async () => {
    try {
        // Charge toutes les promotions pour avoir les totaux
        const response = await axios.get(route('eligibilites.filtered'), {
            params: { type: 'promotions' }
        });
        totalPromotions.value = response.data.statistiques?.total_promotions || 0;
        
        // Calcule les compteurs par grade pour les promotions
        const promos = response.data.promotions || [];
        const tempGradeCounts = {};
        promos.forEach(promo => {
            const grade = promo.militaire.grade_actuel;
            tempGradeCounts[grade] = (tempGradeCounts[grade] || 0) + 1;
        });
        gradeCounts.value = tempGradeCounts;
        
        // Charge toutes les formations
        const responseForm = await axios.get(route('eligibilites.filtered'), {
            params: { type: 'formations' }
        });
        totalFormations.value = responseForm.data.statistiques?.total_formations || 0;
        
        // Calcule les compteurs par formation
        const forms = responseForm.data.formations || [];
        const tempFormCounts = {};
        forms.forEach(form => {
            const formation = form.formation;
            tempFormCounts[formation] = (tempFormCounts[formation] || 0) + 1;
        });
        formationCounts.value = tempFormCounts;
        
    } catch (error) {
        console.error('Erreur chargement stats:', error);
    }
};

const selectType = (type) => {
    selectedType.value = type;
    selectedFormation.value = '';
    selectedGrade.value = '';
    loadData();
};

const toggleFormation = (formationId) => {
    selectedFormation.value = selectedFormation.value === formationId ? '' : formationId;
    loadData();
};

const clearFormationFilter = () => {
    selectedFormation.value = '';
    loadData();
};

const toggleGrade = (gradeId) => {
    selectedGrade.value = selectedGrade.value === gradeId ? '' : gradeId;
    loadData();
};

const clearGradeFilter = () => {
    selectedGrade.value = '';
    loadData();
};

const loadData = async () => {
    if (!selectedType.value) return;
    
    loading.value = true;
    try {
        const params = new URLSearchParams();
        params.append('type', selectedType.value);
        if (selectedFormation.value) params.append('formation', selectedFormation.value);
        if (selectedGrade.value) params.append('grade', selectedGrade.value);
        
        const response = await axios.get(route('eligibilites.filtered') + '?' + params.toString());
        
        promotions.value = response.data.promotions || [];
        formations.value = response.data.formations || [];
        retraites.value = response.data.retraites || [];
        
    } catch (error) {
        console.error('Erreur:', error);
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les données', life: 3000 });
    } finally {
        loading.value = false;
    }
};

const exportData = () => {
    const params = new URLSearchParams();
    params.append('type', selectedType.value);
    if (selectedFormation.value) params.append('formation', selectedFormation.value);
    if (selectedGrade.value) params.append('grade', selectedGrade.value);
    
    window.location.href = route('eligibilites.export') + '?' + params.toString();
    toast.add({ severity: 'success', summary: 'Export', detail: 'Export en cours...', life: 3000 });
};

const viewMilitaire = (id) => {
    router.visit(route('militaires.show', id));
};

// Chargement des stats au montage
onMounted(() => {
    loadGlobalStats();
});
</script>

<style scoped>
:deep(.p-datatable) {
    font-size: 0.85rem;
}

:deep(.p-datatable .p-datatable-tbody > tr) {
    transition: background-color 0.2s;
}

:deep(.p-datatable .p-datatable-tbody > tr:hover) {
    background-color: #f1f5f9;
}

:deep(.p-tag) {
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 9999px;
}

:deep(.p-datatable .p-datatable-thead > tr > th) {
    background-color: #f8fafc;
    color: #1e293b;
    font-weight: 600;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e2e8f0;
}

:deep(.p-datatable .p-datatable-tbody > tr > td) {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f1f5f9;
}

:deep(.p-button-link) {
    text-decoration: none;
    font-weight: 500;
}
</style>