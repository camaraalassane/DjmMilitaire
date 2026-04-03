<template>
    <AuthenticatedLayout>
        <!-- Header avec fond coloré comme le dashboard -->
        <div class="bg-gradient-to-r from-sky-500 to-sky-700 py-4 px-6">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white">
                    Éligibilités
                </h2>
                <div class="flex gap-2">
                    <Button v-if="activeTab !== 'all' && currentListLength > 0"
                            label="Exporter la liste en Excel" 
                            icon="pi pi-file-excel"
                            class="p-button-sm bg-white text-emerald-600 hover:bg-emerald-50 border border-emerald-200"
                            @click="exportCurrentList" />
                    <Button label="Exporter tout en Excel" 
                            icon="pi pi-file-excel"
                            class="p-button-sm bg-white text-emerald-600 hover:bg-emerald-50 border border-emerald-200"
                            @click="exportAll" />
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Cartes de navigation -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- Carte Promotions -->
                    <div class="cursor-pointer transition-all hover:shadow-md rounded-lg overflow-hidden"
                          :class="activeTab === 'promotions' ? 'ring-2 ring-sky-500 shadow-md' : ''"
                          @click="setActiveTab('promotions')">
                        <div class="bg-gradient-to-r from-sky-500 to-sky-600 p-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                    <i class="pi pi-star text-lg text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-white">Promotions</h3>
                                    <p class="text-xs text-white/80">Propositions de grade</p>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-white">{{ promotionsCount }}</div>
                        </div>
                    </div>

                    <!-- Carte Formations -->
                    <div class="cursor-pointer transition-all hover:shadow-md rounded-lg overflow-hidden"
                          :class="activeTab === 'formations' ? 'ring-2 ring-sky-500 shadow-md' : ''"
                          @click="setActiveTab('formations')">
                        <div class="bg-gradient-to-r from-amber-500 to-amber-600 p-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                    <i class="pi pi-book text-lg text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-white">Formations</h3>
                                    <p class="text-xs text-white/80">Formations disponibles</p>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-white">{{ formationsCount }}</div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques globales -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <Card class="bg-gradient-to-r from-sky-500 to-sky-700 text-white">
                        <template #content>
                            <div class="text-center">
                                <i class="pi pi-bell text-2xl mb-1"></i>
                                <div class="text-xl font-bold">{{ totalPromotions + totalFormations }}</div>
                                <div class="text-xs opacity-90">Total éligibilités</div>
                            </div>
                        </template>
                    </Card>
                    
                    <Card class="bg-gradient-to-r from-sky-500 to-sky-700 text-white">
                        <template #content>
                            <div class="text-center">
                                <i class="pi pi-star text-2xl mb-1"></i>
                                <div class="text-xl font-bold">{{ totalPromotions }}</div>
                                <div class="text-xs opacity-90">Promotions</div>
                            </div>
                        </template>
                    </Card>
                    
                    <Card class="bg-gradient-to-r from-amber-500 to-amber-700 text-white">
                        <template #content>
                            <div class="text-center">
                                <i class="pi pi-book text-2xl mb-1"></i>
                                <div class="text-xl font-bold">{{ totalFormations }}</div>
                                <div class="text-xs opacity-90">Formations</div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Onglets rapides -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                    <div class="p-3">
                        <div class="flex flex-wrap gap-2">
                            <Button label="Toutes les éligibilités" 
                                    :class="activeTab === 'all' ? 'bg-sky-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                                    class="p-button-sm text-sm"
                                    @click="setActiveTab('all')" />
                            <Button label="Promotions" 
                                    :class="activeTab === 'promotions' ? 'bg-sky-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                                    class="p-button-sm text-sm"
                                    @click="setActiveTab('promotions')" />
                            <Button label="Formations" 
                                    :class="activeTab === 'formations' ? 'bg-sky-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                                    class="p-button-sm text-sm"
                                    @click="setActiveTab('formations')" />
                        </div>
                    </div>
                </div>

                <!-- Section Promotions -->
                <div v-if="activeTab === 'all' || activeTab === 'promotions'">
                    <div v-for="(promotionGroup, index) in groupedPromotions" :key="index" class="mb-6">
                        <Card style="border-top: 4px solid #38bdf8;">
                            <template #title>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-star text-sky-500"></i>
                                        <span class="text-gray-800">{{ promotionGroup.grade_cible }} - {{ promotionGroup.type }}</span>
                                        <Badge :value="promotionGroup.items.length" 
                                               style="background: #38bdf8; color: white;" 
                                               class="ml-2" />
                                    </div>
                                    <Button v-if="promotionGroup.items.length > 0"
                                            label="Exporter Excel"
                                            icon="pi pi-file-excel"
                                            class="p-button-sm bg-emerald-600 hover:bg-emerald-700 border-emerald-600 text-white text-xs"
                                            @click="exportGroup(promotionGroup, 'promotion')" />
                                </div>
                            </template>
                            
                            <template #content>
                                <DataTable :value="promotionGroup.items" 
                                           stripedRows 
                                           responsiveLayout="scroll"
                                           class="p-datatable-sm">
                                    <Column field="militaire.matricule" header="Matricule">
                                        <template #body="slotProps">
                                            <Tag :value="slotProps.data.militaire.matricule" 
                                                 style="background: #bae6fd; color: #0369a1;" />
                                        </template>
                                    </Column>
                                    <Column field="militaire.nom" header="Nom & Prénom">
                                        <template #body="slotProps">
                                            <Button :label="slotProps.data.militaire.nom + ' ' + slotProps.data.militaire.prenom"
                                                    class="p-button-link p-0 text-sky-500 hover:text-sky-600 text-sm"
                                                    @click="viewMilitaire(slotProps.data.militaire.id)" />
                                        </template>
                                    </Column>
                                    <Column field="militaire.grade_actuel" header="Grade actuel">
                                        <template #body="slotProps">
                                            <Tag :value="slotProps.data.militaire.grade_actuel" 
                                                 style="background: #e5e7eb; color: #374151;" />
                                        </template>
                                    </Column>
                                    <Column field="message" header="Condition">
                                        <template #body="slotProps">
                                            <span class="text-sm">{{ slotProps.data.message }}</span>
                                        </template>
                                    </Column>
                                    <Column field="date_estimation" header="Date estimation">
                                        <template #body="slotProps">
                                            {{ formatDate(slotProps.data.date_estimation) }}
                                        </template>
                                    </Column>
                                    <template #empty>
                                        <div class="text-center py-4 text-gray-500">
                                            Aucune promotion pour ce grade
                                        </div>
                                    </template>
                                </DataTable>
                            </template>
                        </Card>
                    </div>
                </div>

                <!-- Section Formations -->
                <div v-if="activeTab === 'all' || activeTab === 'formations'">
                    <div v-for="(formationGroup, index) in groupedFormations" :key="index" class="mb-6">
                        <Card style="border-top: 4px solid #f97316;">
                            <template #title>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-book text-amber-500"></i>
                                        <span class="text-gray-800">{{ formationGroup.nom_formation }}</span>
                                        <Badge :value="formationGroup.items.length" 
                                               style="background: #f97316; color: white;" 
                                               class="ml-2" />
                                    </div>
                                    <Button v-if="formationGroup.items.length > 0"
                                            label="Exporter Excel"
                                            icon="pi pi-file-excel"
                                            class="p-button-sm bg-emerald-600 hover:bg-emerald-700 border-emerald-600 text-white text-xs"
                                            @click="exportGroup(formationGroup, 'formation')" />
                                </div>
                            </template>
                            
                            <template #content>
                                <DataTable :value="formationGroup.items" 
                                           stripedRows 
                                           responsiveLayout="scroll"
                                           class="p-datatable-sm">
                                    <Column field="militaire.matricule" header="Matricule">
                                        <template #body="slotProps">
                                            <Tag :value="slotProps.data.militaire.matricule" 
                                                 style="background: #bae6fd; color: #0369a1;" />
                                        </template>
                                    </Column>
                                    <Column field="militaire.nom" header="Nom & Prénom">
                                        <template #body="slotProps">
                                            <Button :label="slotProps.data.militaire.nom + ' ' + slotProps.data.militaire.prenom"
                                                    class="p-button-link p-0 text-sky-500 hover:text-sky-600 text-sm"
                                                    @click="viewMilitaire(slotProps.data.militaire.id)" />
                                        </template>
                                    </Column>
                                    <Column field="militaire.grade_actuel" header="Grade actuel">
                                        <template #body="slotProps">
                                            <Tag :value="slotProps.data.militaire.grade_actuel" 
                                                 style="background: #e5e7eb; color: #374151;" />
                                        </template>
                                    </Column>
                                    <Column field="message" header="Condition">
                                        <template #body="slotProps">
                                            <span class="text-sm">{{ slotProps.data.message }}</span>
                                        </template>
                                    </Column>
                                    <Column field="date_estimation" header="Date estimation">
                                        <template #body="slotProps">
                                            {{ formatDate(slotProps.data.date_estimation) }}
                                        </template>
                                    </Column>
                                    <template #empty>
                                        <div class="text-center py-4 text-gray-500">
                                            Aucun militaire éligible pour cette formation
                                        </div>
                                    </template>
                                </DataTable>
                            </template>
                        </Card>
                    </div>
                </div>

                <!-- Retraites proches -->
                <Card style="border-top: 4px solid #f97316;">
                    <template #title>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-calendar-times text-orange-500"></i>
                                <span class="text-gray-800">Retraites proches (dans les 12 mois)</span>
                                <Badge :value="retraites.length" 
                                       style="background: #f97316; color: white;" 
                                       class="ml-2" />
                            </div>
                            <Button v-if="retraites.length > 0"
                                    label="Exporter Excel"
                                    icon="pi pi-file-excel"
                                    class="p-button-sm bg-emerald-600 hover:bg-emerald-700 border-emerald-600 text-white text-xs"
                                    @click="exportRetraites" />
                        </div>
                    </template>
                    
                    <template #content>
                        <DataTable v-if="retraites.length > 0" 
                                   :value="retraites" 
                                   stripedRows 
                                   responsiveLayout="scroll"
                                   class="p-datatable-sm">
                            <Column field="militaire.matricule" header="Matricule">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.militaire.matricule" 
                                         style="background: #bae6fd; color: #0369a1;" />
                                </template>
                            </Column>
                            <Column field="militaire.nom" header="Nom & Prénom">
                                <template #body="slotProps">
                                    <Button :label="slotProps.data.militaire.nom + ' ' + slotProps.data.militaire.prenom"
                                            class="p-button-link p-0 text-sky-500 hover:text-sky-600 text-sm"
                                            @click="viewMilitaire(slotProps.data.militaire.id)" />
                                </template>
                            </Column>
                            <Column field="militaire.grade_actuel" header="Grade actuel">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.militaire.grade_actuel" 
                                         style="background: #e5e7eb; color: #374151;" />
                                </template>
                            </Column>
                            <Column field="date_retraite_formatted" header="Date retraite">
                                <template #body="slotProps">
                                    {{ slotProps.data.date_retraite_formatted || '-' }}
                                </template>
                            </Column>
                            <Column header="Mois restants">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.mois_restants_formate || slotProps.data.mois_restants + ' mois'" 
                                         :style="getMoisRestantsStyle(slotProps.data.mois_restants)" />
                                </template>
                            </Column>
                            <template #empty>
                                <div class="text-center py-4 text-gray-500">
                                    Aucune retraite proche
                                </div>
                            </template>
                        </DataTable>
                        <div v-else class="text-center py-4 text-gray-500">
                            <i class="pi pi-calendar-times text-2xl mb-2 text-gray-400"></i>
                            <p>Aucune retraite proche</p>
                        </div>
                    </template>
                </Card>
            </div>
        </div>

        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
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
    eligibilites: {
        type: Object,
        required: true
    }
});

const toast = useToast();
const activeTab = ref('all');

const promotions = computed(() => props.eligibilites.promotions || []);
const formations = computed(() => props.eligibilites.formations || []);
const retraites = computed(() => props.eligibilites.retraites || []);

const totalPromotions = computed(() => promotions.value.length);
const totalFormations = computed(() => formations.value.length);
const promotionsCount = computed(() => promotions.value.length);
const formationsCount = computed(() => formations.value.length);

const currentListLength = computed(() => {
    if (activeTab.value === 'promotions') return promotions.value.length;
    if (activeTab.value === 'formations') return formations.value.length;
    return 0;
});

// Grouper les promotions par grade cible
const groupedPromotions = computed(() => {
    const groups = {};
    promotions.value.forEach(promo => {
        const key = `${promo.grade_cible}_${promo.type}`;
        if (!groups[key]) {
            groups[key] = {
                grade_cible: promo.grade_cible,
                type: promo.type,
                items: []
            };
        }
        groups[key].items.push(promo);
    });
    return Object.values(groups);
});

// Grouper les formations par nom
const groupedFormations = computed(() => {
    const groups = {};
    formations.value.forEach(formation => {
        const key = formation.nom_formation;
        if (!groups[key]) {
            groups[key] = {
                nom_formation: formation.nom_formation,
                items: []
            };
        }
        groups[key].items.push(formation);
    });
    return Object.values(groups);
});

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const getMoisRestantsStyle = (mois) => {
    if (mois <= 0) {
        return { background: '#fecaca', color: '#991b1b' };
    }
    if (mois <= 1) {
        return { background: '#fed7aa', color: '#c2410c' };
    }
    if (mois <= 3) {
        return { background: '#fef3c7', color: '#92400e' };
    }
    return { background: '#bae6fd', color: '#0369a1' };
};

const setActiveTab = (tab) => {
    activeTab.value = tab;
};

// Exporter un groupe spécifique (promotion ou formation) vers Excel
const exportGroup = (group, type) => {
    const exportType = type === 'promotion' ? 'promotions' : 'formations';
    const groupName = type === 'promotion' ? group.grade_cible : group.nom_formation;
    
    // Créer un paramètre pour identifier le groupe
    const params = new URLSearchParams({
        type: exportType,
        group: encodeURIComponent(groupName)
    });
    
    window.location.href = route('eligibilites.export') + '?' + params.toString();
    toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: `Export de la liste "${groupName}" en cours...`,
        life: 3000
    });
};

// Exporter la liste actuelle (promotions ou formations)
const exportCurrentList = () => {
    const type = activeTab.value === 'promotions' ? 'promotions' : 'formations';
    window.location.href = route('eligibilites.export', { type });
    toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: `Export de la liste des ${type === 'promotions' ? 'promotions' : 'formations'} en cours...`,
        life: 3000
    });
};

// Exporter les retraites
const exportRetraites = () => {
    window.location.href = route('eligibilites.export', { type: 'retraites' });
    toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Export de la liste des retraites en cours...',
        life: 3000
    });
};

// Exporter toutes les listes
const exportAll = () => {
    window.location.href = route('eligibilites.export', { type: 'all' });
    toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Export de toutes les listes en cours...',
        life: 3000
    });
};

const viewMilitaire = (id) => {
    router.visit(route('militaires.show', id));
};
</script>

<style scoped>
:deep(.p-card) {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

:deep(.p-card:hover) {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

:deep(.p-card .p-card-title) {
    font-size: 1rem;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
}

:deep(.p-datatable) {
    font-size: 0.85rem;
}

:deep(.p-tag) {
    font-size: 0.75rem;
    padding: 0.2rem 0.5rem;
    border-radius: 0.375rem;
    font-weight: 500;
}

:deep(.p-button-link) {
    text-decoration: none;
    font-weight: 500;
    font-size: 0.85rem;
}

:deep(.p-button-link:hover) {
    text-decoration: underline;
}

:deep(.p-badge) {
    font-size: 0.75rem;
    padding: 0.2rem 0.5rem;
    min-width: 1.5rem;
    border-radius: 0.375rem;
}

/* Styles personnalisés */
.text-sky-500 {
    color: #0ea5e9;
}

.text-sky-600 {
    color: #0284c7;
}

.text-amber-500 {
    color: #f59e0b;
}

.text-orange-500 {
    color: #f97316;
}

.bg-emerald-600 {
    background-color: #059669;
}

.hover\:bg-emerald-700:hover {
    background-color: #047857;
}

.border-emerald-600 {
    border-color: #059669;
}
</style>