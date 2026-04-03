<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tableau de bord</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Statistiques Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                            <Card v-for="stat in statistiquesCards" :key="stat.label" :class="stat.colorClass" class="text-white">
                                <template #title>
                                    <div class="flex items-center gap-2">
                                        <i :class="stat.icon"></i>
                                        <span class="text-sm font-medium">{{ stat.label }}</span>
                                    </div>
                                </template>
                                <template #content>
                                    <div class="text-3xl font-bold">{{ stat.value }}</div>
                                </template>
                            </Card>
                        </div>

                        <!-- Alertes et Retraites -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                            <!-- Alertes récentes -->
                            <Card>
                                <template #title>
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-bell" :class="alertesNonVues.length > 0 ? 'text-yellow-500' : ''"></i>
                                        <span>Alertes récentes ({{ alertesNonVues.length }})</span>
                                    </div>
                                </template>
                                <template #content>
                                    <div v-if="alertesNonVues.length > 0" class="space-y-3">
                                        <div v-for="alerte in alertesNonVues" :key="alerte.id" 
                                             class="p-3 border rounded-lg transition-all hover:shadow-md"
                                             :class="getAlerteClass(alerte.type_alerte)">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <i class="pi pi-user"></i>
                                                        <strong>{{ alerte.militaire.nom }} {{ alerte.militaire.prenom }}</strong>
                                                        <Tag :value="alerte.militaire.matricule" severity="info" />
                                                    </div>
                                                    <p class="text-sm">{{ alerte.message }}</p>
                                                    <div class="flex items-center gap-2 mt-2 text-xs text-gray-600">
                                                        <i class="pi pi-calendar"></i>
                                                        <span>Échéance: {{ formatDate(alerte.date_echeance) }}</span>
                                                        <Tag :value="joursRestants(alerte.date_echeance)" 
                                                             :severity="getJoursRestantsSeverity(joursRestants(alerte.date_echeance))" 
                                                             class="ml-2" />
                                                    </div>
                                                </div>
                                                <Button icon="pi pi-check" 
                                                        class="p-button-rounded p-button-text p-button-sm"
                                                        :loading="loadingStates[alerte.id]"
                                                        @click="marquerAlerteVue(alerte.id)" />
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-8 text-gray-500">
                                        <i class="pi pi-check-circle text-4xl mb-2 text-green-500"></i>
                                        <p>Aucune alerte pour le moment</p>
                                    </div>
                                </template>
                            </Card>

                            <!-- Prochaines retraites -->
                            <Card>
                                <template #title>
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-calendar"></i>
                                        <span>Prochaines retraites ({{ militairesProchesRetraite.length }})</span>
                                    </div>
                                </template>
                                <template #content>
                                    <DataTable :value="militairesProchesRetraite" 
                                               stripedRows 
                                               responsiveLayout="scroll"
                                               class="p-datatable-sm">
                                        <Column field="matricule" header="Matricule">
                                            <template #body="slotProps">
                                                <Tag :value="slotProps.data.matricule" severity="info" />
                                            </template>
                                        </Column>
                                        <Column field="nom" header="Nom">
                                            <template #body="slotProps">
                                                {{ slotProps.data.nom }} {{ slotProps.data.prenom }}
                                            </template>
                                        </Column>
                                        <Column field="grade_actuel" header="Grade"></Column>
                                        <Column header="Jours restants">
                                            <template #body="slotProps">
                                                <Tag :value="joursRestants(slotProps.data.date_retraite) + ' jours'" 
                                                     :severity="getJoursRestantsSeverity(joursRestants(slotProps.data.date_retraite))" />
                                            </template>
                                        </Column>
                                        <template #empty>
                                            <div class="text-center p-4 text-gray-500">
                                                <i class="pi pi-info-circle mr-2"></i>
                                                Aucune retraite proche
                                            </div>
                                        </template>
                                    </DataTable>
                                </template>
                            </Card>
                        </div>

                        <!-- Statistiques par grade -->
                        <Card>
                            <template #title>
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-chart-bar"></i>
                                    <span>Répartition par grade</span>
                                </div>
                            </template>
                            <template #content>
                                <DataTable :value="gradesFiltres" 
                                           stripedRows 
                                           responsiveLayout="scroll"
                                           class="p-datatable-sm">
                                    <Column field="nom_grade" header="Grade">
                                        <template #body="slotProps">
                                            <div class="font-medium">{{ slotProps.data.nom_grade }}</div>
                                            <small class="text-gray-500">{{ slotProps.data.type_grade }}</small>
                                        </template>
                                    </Column>
                                    <Column field="militaires_count" header="Nombre">
                                        <template #body="slotProps">
                                            <Tag :value="slotProps.data.militaires_count" severity="info" />
                                        </template>
                                    </Column>
                                    <Column header="Pourcentage">
                                        <template #body="slotProps">
                                            <div class="flex items-center gap-2">
                                                <ProgressBar :value="calculerPourcentage(slotProps.data.militaires_count)" 
                                                            class="w-48 h-2" />
                                                <span class="text-sm font-medium">
                                                    {{ calculerPourcentage(slotProps.data.militaires_count).toFixed(1) }}%
                                                </span>
                                            </div>
                                        </template>
                                    </Column>
                                    <template #empty>
                                        <div class="text-center p-4 text-gray-500">
                                            Aucun grade avec des militaires actifs
                                        </div>
                                    </template>
                                </DataTable>
                            </template>
                        </Card>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast pour les notifications -->
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
import ProgressBar from 'primevue/progressbar';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    alertesNonVues: Array,
    militairesProchesRetraite: Array,
    statistiques: Object,
    grades: Array
});

const toast = useToast();
const loadingStates = ref({});

// Configuration des cartes de statistiques
const statistiquesCards = computed(() => [
    { 
        label: 'Total Militaires', 
        value: props.statistiques.total_militaires, 
        icon: 'pi pi-users',
        colorClass: 'bg-gradient-to-r from-blue-500 to-blue-700'
    },
    { 
        label: 'Militaires Actifs', 
        value: props.statistiques.militaires_actifs, 
        icon: 'pi pi-check-circle',
        colorClass: 'bg-gradient-to-r from-green-500 to-green-700'
    },
    { 
        label: 'Alertes Non Vues', 
        value: props.statistiques.alertes_non_vues, 
        icon: 'pi pi-bell',
        colorClass: 'bg-gradient-to-r from-yellow-500 to-yellow-700'
    },
    { 
        label: 'Retraites Proches', 
        value: props.statistiques.prochaines_retraites, 
        icon: 'pi pi-calendar',
        colorClass: 'bg-gradient-to-r from-red-500 to-red-700'
    }
]);

// Filtrer les grades avec des militaires
const gradesFiltres = computed(() => {
    return props.grades.filter(grade => grade.militaires_count > 0);
});

// Méthodes utilitaires
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const joursRestants = (date) => {
    const aujourdhui = new Date();
    const dateTarget = new Date(date);
    const diffTime = dateTarget - aujourdhui;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : 0;
};

const getJoursRestantsSeverity = (jours) => {
    if (jours <= 30) return 'danger';
    if (jours <= 90) return 'warning';
    return 'info';
};

const getAlerteClass = (type) => {
    const classes = {
        'info': 'border-blue-200 bg-blue-50',
        'warning': 'border-yellow-200 bg-yellow-50',
        'danger': 'border-red-200 bg-red-50',
        'success': 'border-green-200 bg-green-50'
    };
    return classes[type] || 'border-gray-200 bg-gray-50';
};

const calculerPourcentage = (count) => {
    if (props.statistiques.militaires_actifs === 0) return 0;
    return (count / props.statistiques.militaires_actifs) * 100;
};

const marquerAlerteVue = (alerteId) => {
    loadingStates.value[alerteId] = true;
    
    router.post(route('alertes.marquer-vue', alerteId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Alerte marquée comme vue',
                life: 3000
            });
            loadingStates.value[alerteId] = false;
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Impossible de marquer l\'alerte',
                life: 3000
            });
            loadingStates.value[alerteId] = false;
        }
    });
};
</script>

<style scoped>
:deep(.p-card) {
    border-radius: 12px;
    overflow: hidden;
}

:deep(.p-card .p-card-title) {
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

:deep(.p-card .p-card-content) {
    padding: 0;
}

:deep(.p-datatable .p-datatable-tbody > tr) {
    transition: background-color 0.2s;
}

:deep(.p-datatable .p-datatable-tbody > tr:hover) {
    background-color: #f3f4f6 !important;
}
</style>