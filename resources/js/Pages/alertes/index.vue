<template>
    <AuthenticatedLayout>
        <div class="bg-gradient-to-r from-sky-500 to-sky-700 py-4 px-6">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white">
                    Liste des alertes
                </h2>
                <div class="flex gap-2">
                    <Button v-if="statistiques.non_vues > 0" 
                            label="Tout marquer comme vu" 
                            icon="pi pi-check-circle"
                            class="p-button-sm bg-white text-sky-600 hover:bg-sky-50 hover:text-sky-700"
                            @click="markAllAsRead" />
                </div>
            </div>
        </div> 

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Cartes de navigation -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- Carte Promotions -->
                    <div class="cursor-pointer transition-all hover:shadow-md rounded-lg overflow-hidden"
                          :class="activeTab === 'promotion' ? 'ring-2 ring-sky-500 shadow-md' : ''"
                          @click="setActiveTab('promotion')">
                        <div class="bg-gradient-to-r from-sky-500 to-sky-600 p-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                    <i class="pi pi-star text-lg text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-white">Promotions</h3>
                                    <p class="text-xs text-white/80">Alertes de promotion</p>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-white">{{ statistiques.promotions || 0 }}</div>
                        </div>
                    </div>

                    <!-- Carte Formations -->
                    <div class="cursor-pointer transition-all hover:shadow-md rounded-lg overflow-hidden"
                          :class="activeTab === 'formation' ? 'ring-2 ring-sky-500 shadow-md' : ''"
                          @click="setActiveTab('formation')">
                        <div class="bg-gradient-to-r from-amber-500 to-amber-600 p-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                    <i class="pi pi-book text-lg text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-white">Formations</h3>
                                    <p class="text-xs text-white/80">Alertes de formation</p>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-white">{{ statistiques.formations || 0 }}</div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques globales -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <Card class="bg-gradient-to-r from-sky-500 to-sky-700 text-white">
                        <template #content>
                            <div class="text-center">
                                <i class="pi pi-bell text-2xl mb-1"></i>
                                <div class="text-xl font-bold">{{ statistiques.total }}</div>
                                <div class="text-xs opacity-90">Total alertes</div>
                            </div>
                        </template>
                    </Card>
                    
                    <Card class="bg-gradient-to-r from-orange-500 to-orange-700 text-white">
                        <template #content>
                            <div class="text-center">
                                <i class="pi pi-exclamation-triangle text-2xl mb-1"></i>
                                <div class="text-xl font-bold">{{ statistiques.non_vues }}</div>
                                <div class="text-xs opacity-90">Non vues</div>
                            </div>
                        </template>
                    </Card>
                    
                    <Card class="bg-gradient-to-r from-emerald-500 to-emerald-700 text-white">
                        <template #content>
                            <div class="text-center">
                                <i class="pi pi-check-circle text-2xl mb-1"></i>
                                <div class="text-xl font-bold">{{ statistiques.vues }}</div>
                                <div class="text-xs opacity-90">Vues</div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Filtres -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                    <div class="p-3">
                        <div class="flex flex-wrap gap-3 justify-between items-center">
                            <div class="flex gap-2">
                                <Button label="Toutes" 
                                        :class="activeTab === 'all' ? 'bg-sky-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                                        class="p-button-sm text-sm"
                                        @click="setActiveTab('all')" />
                                <Button label="Promotions" 
                                        :class="activeTab === 'promotion' ? 'bg-sky-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                                        class="p-button-sm text-sm"
                                        @click="setActiveTab('promotion')" />
                                <Button label="Formations" 
                                        :class="activeTab === 'formation' ? 'bg-sky-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                                        class="p-button-sm text-sm"
                                        @click="setActiveTab('formation')" />
                            </div>
                            <div class="flex gap-2">
                                <span class="p-input-icon-left">
                                    <i class="pi pi-search" />
                                    <InputText v-model="filters.search" 
                                              placeholder="Rechercher..."  
                                              class="w-56 text-sm"
                                              @keyup.enter="applyFilters" />
                                </span>
                                <Button label="Rechercher" 
                                        icon="pi pi-search"
                                        class="p-button-sm bg-sky-600 hover:bg-sky-700 text-white text-sm"
                                        @click="applyFilters" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau des alertes -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-4">
                        <div class="mb-3">
                            <h3 class="text-md font-semibold text-gray-800">
                                {{ activeTab === 'promotion' ? 'Alertes de promotion' : activeTab === 'formation' ? 'Alertes de formation' : 'Toutes les alertes' }}
                            </h3>
                        </div>

                        <DataTable :value="alertes.data" 
                                   stripedRows 
                                   responsiveLayout="scroll"
                                   :loading="loading"
                                   paginator
                                   lazy
                                   :rows="alertes.per_page"
                                   :totalRecords="alertes.total"
                                   @page="onPageChange"
                                   class="p-datatable-sm"
                                   :rowClass="rowClass">
                            
                            <Column field="militaire.nom" header="Militaire">
                                <template #body="slotProps">
                                    <div v-if="slotProps.data.militaire">
                                        <Button :label="slotProps.data.militaire.nom + ' ' + slotProps.data.militaire.prenom"
                                                class="p-button-link p-0 text-sky-600 hover:text-sky-800 text-sm"
                                                @click="viewMilitaire(slotProps.data.militaire.id)" />
                                        <div class="text-xs text-gray-500">{{ slotProps.data.militaire.matricule }}</div>
                                    </div>
                                    <span v-else class="text-gray-400 text-sm">Militaire supprimé</span>
                                </template>
                            </Column>

                            <Column field="type_alerte" header="Type">
                                <template #body="slotProps">
                                    <Tag :value="getTypeLabel(slotProps.data.type_alerte)" 
                                         :style="slotProps.data.type_alerte === 'formation' ? { background: '#f97316', color: 'white', fontSize: '0.7rem', padding: '0.2rem 0.5rem' } : { background: '#0284c7', color: 'white', fontSize: '0.7rem', padding: '0.2rem 0.5rem' }" />
                                </template>
                            </Column>

                            <Column field="message" header="Message">
                                <template #body="slotProps">
                                    <span class="text-sm">{{ slotProps.data.message }}</span>
                                </template>
                            </Column>

                            <Column field="date_echeance_formatted" header="Échéance">
                                <template #body="slotProps">
                                    <div :class="{'font-bold text-red-600 text-sm': isEcheanceProche(slotProps.data.date_echeance)}" class="text-sm">
                                        {{ slotProps.data.date_echeance_formatted }}
                                    </div>
                                </template>
                            </Column>

                            <Column field="created_at" header="Créée le">
                                <template #body="slotProps">
                                    <span class="text-sm">{{ slotProps.data.created_at }}</span>
                                </template>
                            </Column>

                            <Column header="Statut">
                                <template #body="slotProps">
                                    <Tag v-if="slotProps.data.est_vue" 
                                         value="Vue" 
                                         style="background: #10b981; color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem;" />
                                    <Tag v-else 
                                         value="Non vue" 
                                         style="background: #f97316; color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem;" />
                                </template>
                            </Column>

                            <Column header="Action">
                                <template #body="slotProps">
                                    <Button v-if="!slotProps.data.est_vue"
                                            label="Marquer vue"
                                            icon="pi pi-check"
                                            class="p-button-sm bg-emerald-600 hover:bg-emerald-700 border-emerald-600 text-white text-xs"
                                            :loading="loadingStates[slotProps.data.id]"
                                            @click="markAsRead(slotProps.data.id)" />
                                </template>
                            </Column>

                            <template #empty>
                                <div class="text-center py-6 text-gray-500">
                                    <i class="pi pi-bell-slash text-3xl mb-2"></i>
                                    <p class="text-sm">Aucune alerte trouvée</p>
                                </div>
                            </template>
                        </DataTable>

                        <div class="text-center sm:text-left text-sm text-gray-600 mt-4">
                            Affichage de {{ alertes.from }} à {{ alertes.to }} sur {{ alertes.total }} alertes
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    alertes: {
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
    }
});

const toast = useToast();
const loading = ref(false);
const loadingStates = ref({});
const activeTab = ref(props.filters?.type || 'all');

// État des filtres
const filters = reactive({
    search: props.filters?.search || ''
});

// Obtenir le libellé du type d'alerte
const getTypeLabel = (type) => {
    const labels = {
        'promotion': 'Promotion',
        'formation': 'Formation',
        'retraite': 'Retraite',
        'certificat': 'Certificat'
    };
    return labels[type] || type;
};

// Vérifier si l'échéance est proche (moins de 30 jours)
const isEcheanceProche = (date) => {
    if (!date) return false;
    const today = new Date();
    const echeance = new Date(date);
    const diffTime = echeance - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays <= 30 && diffDays >= 0;
};

// Classe CSS pour les lignes non vues
const rowClass = (data) => {
    return data.est_vue ? '' : 'bg-amber-50';
};

// Charger les alertes
const loadAlertes = (page = 1) => {
    loading.value = true;
    
    const params = {
        page,
        search: filters.search
    };
    
    if (activeTab.value !== 'all') {
        params.type = activeTab.value;
    }
    
    router.get(route('alertes.index'), params, {
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
                detail: 'Impossible de charger les alertes',
                life: 3000
            });
        }
    });
};

// Changer l'onglet actif
const setActiveTab = (tab) => {
    if (activeTab.value === tab) return;
    activeTab.value = tab;
    loadAlertes(1);
};

// Appliquer les filtres
const applyFilters = () => {
    loadAlertes(1);
};

// Changement de page
const onPageChange = (event) => {
    loadAlertes(event.page + 1);
};

// Marquer une alerte comme vue
const markAsRead = (alerteId) => {
    loadingStates.value[alerteId] = true;
    
    router.post(route('alertes.marquer-vue', alerteId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            loadingStates.value[alerteId] = false;
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Alerte marquée comme vue',
                life: 3000
            });
            loadAlertes(props.alertes.current_page);
        },
        onError: () => {
            loadingStates.value[alerteId] = false;
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Impossible de marquer l\'alerte',
                life: 3000
            });
        }
    });
};

// Marquer toutes les alertes comme vues
const markAllAsRead = () => {
    router.post(route('alertes.marquer-tout-vue'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Toutes les alertes ont été marquées comme vues',
                life: 3000
            });
            loadAlertes(props.alertes.current_page);
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Impossible de marquer toutes les alertes',
                life: 3000
            });
        }
    });
};

// Voir les détails d'un militaire
const viewMilitaire = (id) => {
    router.visit(route('militaires.show', id));
};
</script>

<style scoped>
:deep(.p-card) {
    border-radius: 8px;
    overflow: hidden;
}

:deep(.p-datatable) {
    font-size: 0.85rem;
}

:deep(.p-datatable .p-datatable-tbody > tr.bg-amber-50) {
    background-color: #fffbeb;
}

:deep(.p-datatable .p-datatable-tbody > tr.bg-amber-50:hover) {
    background-color: #fef3c7 !important;
}

:deep(.p-tag) {
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 0.25rem;
}

:deep(.p-button-link) {
    text-decoration: none;
    font-size: 0.85rem;
}

:deep(.p-button-link:hover) {
    text-decoration: underline;
}

:deep(.p-button.p-button-sm) {
    font-size: 0.75rem;
    padding: 0.3rem 0.6rem;
}

.bg-sky-600 {
    background-color: #0284c7;
}

.hover\:bg-sky-700:hover {
    background-color: #0369a1;
}

.text-sky-600 {
    color: #0284c7;
}
</style>