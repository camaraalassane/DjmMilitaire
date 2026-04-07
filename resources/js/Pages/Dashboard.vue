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
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
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

                        <!-- Tableau Année N -->
                        <div class="mb-12">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">Militaires proposables pour l'année {{ anneeN }}</h3>
                                    <p class="text-sm text-gray-500">Périodes: Janvier, Avril et Octobre</p>
                                </div>
                                <Button label="Exporter en Excel" 
                                        icon="pi pi-file-excel" 
                                        class="p-button-success p-button-sm"
                                        @click="exportProposablesAnneeN" />
                            </div>
                            
                            <Card>
                                <template #title>
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-calendar text-blue-500"></i>
                                        <span>Liste des propositions - Année {{ anneeN }}</span>
                                        <Tag :value="totalProposablesN" severity="warning" />
                                    </div>
                                </template>
                                <template #content>
                                    <DataTable :value="allProposablesN" 
                                               stripedRows 
                                               responsiveLayout="scroll"
                                               class="p-datatable-sm"
                                               :rows="10"
                                               paginator
                                               :rowsPerPageOptions="[10, 20, 50, 100]"
                                               sortField="date_proposition"
                                               :sortOrder="1">
                                        <Column field="periode" header="Période" sortable>
                                            <template #body="slotProps">
                                                <span :class="getPeriodeColor(slotProps.data.periode_key)">
                                                    {{ slotProps.data.periode }}
                                                </span>
                                            </template>
                                        </Column>
                                        <Column field="date_proposition_formatted" header="Date proposition" sortable>
                                            <template #body="slotProps">
                                                <div class="flex items-center gap-2 whitespace-nowrap">
                                                    <i class="pi pi-calendar"></i>
                                                    <span>{{ slotProps.data.date_proposition_formatted }}</span>
                                                </div>
                                            </template>
                                        </Column>
                                        <Column field="matricule" header="Matricule" sortable>
                                            <template #body="slotProps">
                                                <Tag :value="slotProps.data.matricule" severity="info" />
                                            </template>
                                        </Column>
                                        <Column field="nom_complet" header="Nom" sortable>
                                            <template #body="slotProps">
                                                <div class="font-medium whitespace-nowrap">{{ slotProps.data.nom_complet }}</div>
                                            </template>
                                        </Column>
                                        <Column field="grade_actuel" header="Grade actuel" sortable>
                                            <template #body="slotProps">
                                                <span class="whitespace-nowrap">{{ slotProps.data.grade_actuel }}</span>
                                            </template>
                                        </Column>
                                        <Column field="grade_cible" header="Grade cible" sortable>
                                            <template #body="slotProps">
                                                <Tag :value="slotProps.data.grade_cible" severity="success" />
                                            </template>
                                        </Column>
                                        <template #empty>
                                            <div class="text-center p-8 text-gray-500">
                                                <i class="pi pi-info-circle text-4xl mb-2"></i>
                                                <p>Aucun militaire proposable pour l'année {{ anneeN }}</p>
                                            </div>
                                        </template>
                                    </DataTable>
                                </template>
                            </Card>
                        </div>

                        <!-- Tableau Année N+1 -->
                        <div class="mb-12">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800">Militaires proposables pour l'année {{ anneeN1 }}</h3>
                                    <p class="text-sm text-gray-500">Périodes: Janvier, Avril et Octobre</p>
                                </div>
                                <Button label="Exporter en Excel" 
                                        icon="pi pi-file-excel" 
                                        class="p-button-success p-button-sm"
                                        @click="exportProposablesAnneeN1" />
                            </div>
                            
                            <Card>
                                <template #title>
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-calendar-plus text-purple-500"></i>
                                        <span>Liste des propositions - Année {{ anneeN1 }}</span>
                                        <Tag :value="totalProposablesN1" severity="warning" />
                                    </div>
                                </template>
                                <template #content>
                                    <DataTable :value="allProposablesN1" 
                                               stripedRows 
                                               responsiveLayout="scroll"
                                               class="p-datatable-sm"
                                               :rows="10"
                                               paginator
                                               :rowsPerPageOptions="[10, 20, 50, 100]"
                                               sortField="date_proposition"
                                               :sortOrder="1">
                                        <Column field="periode" header="Période" sortable>
                                            <template #body="slotProps">
                                                <span :class="getPeriodeColor(slotProps.data.periode_key)">
                                                    {{ slotProps.data.periode }}
                                                </span>
                                            </template>
                                        </Column>
                                        <Column field="date_proposition_formatted" header="Date proposition" sortable>
                                            <template #body="slotProps">
                                                <div class="flex items-center gap-2 whitespace-nowrap">
                                                    <i class="pi pi-calendar"></i>
                                                    <span>{{ slotProps.data.date_proposition_formatted }}</span>
                                                </div>
                                            </template>
                                        </Column>
                                        <Column field="matricule" header="Matricule" sortable>
                                            <template #body="slotProps">
                                                <Tag :value="slotProps.data.matricule" severity="info" />
                                            </template>
                                        </Column>
                                        <Column field="nom_complet" header="Nom" sortable>
                                            <template #body="slotProps">
                                                <div class="font-medium whitespace-nowrap">{{ slotProps.data.nom_complet }}</div>
                                            </template>
                                        </Column>
                                        <Column field="grade_actuel" header="Grade actuel" sortable>
                                            <template #body="slotProps">
                                                <span class="whitespace-nowrap">{{ slotProps.data.grade_actuel }}</span>
                                            </template>
                                        </Column>
                                        <Column field="grade_cible" header="Grade cible" sortable>
                                            <template #body="slotProps">
                                                <Tag :value="slotProps.data.grade_cible" severity="success" />
                                            </template>
                                        </Column>
                                        <template #empty>
                                            <div class="text-center p-8 text-gray-500">
                                                <i class="pi pi-info-circle text-4xl mb-2"></i>
                                                <p>Aucun militaire proposable pour l'année {{ anneeN1 }}</p>
                                            </div>
                                        </template>
                                    </DataTable>
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
                                        <Column header="Mois restants">
                                            <template #body="slotProps">
                                                <Tag :value="slotProps.data.mois_restants_formate" 
                                                     :severity="getMoisRestantsSeverity(slotProps.data.mois_restants)" />
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
    proposablesAnneeN: Object,
    proposablesAnneeN1: Object,
    statistiques: Object,
    grades: Array
});

const toast = useToast();
const loadingStates = ref({});

const anneeN = computed(() => {
    return props.proposablesAnneeN?.annee || new Date().getFullYear();
});

const anneeN1 = computed(() => {
    return props.proposablesAnneeN1?.annee || new Date().getFullYear() + 1;
});

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
    },
    { 
        label: `Proposables ${anneeN.value}`, 
        value: props.statistiques.total_proposables_n || 0, 
        icon: 'pi pi-star',
        colorClass: 'bg-gradient-to-r from-purple-500 to-purple-700'
    },
    { 
        label: `Proposables ${anneeN1.value}`, 
        value: props.statistiques.total_proposables_n1 || 0, 
        icon: 'pi pi-star',
        colorClass: 'bg-gradient-to-r from-indigo-500 to-indigo-700'
    }
]);

// Filtrer les grades avec des militaires
const gradesFiltres = computed(() => {
    return props.grades.filter(grade => grade.militaires_count > 0);
});

// Aplatir toutes les propositions pour l'année N
const allProposablesN = computed(() => {
    const result = [];
    const ordrePeriodes = ['janvier', 'avril', 'octobre'];
    
    if (props.proposablesAnneeN) {
        for (const periode of ordrePeriodes) {
            if (props.proposablesAnneeN[periode] && props.proposablesAnneeN[periode].proposables) {
                for (const proposable of props.proposablesAnneeN[periode].proposables) {
                    result.push({
                        ...proposable,
                        periode: props.proposablesAnneeN[periode].nom,
                        periode_key: periode,
                        date_proposition: props.proposablesAnneeN[periode].date,
                        date_proposition_formatted: props.proposablesAnneeN[periode].date_formatted,
                        nom_complet: `${proposable.nom} ${proposable.prenom}`,
                        est_passee: props.proposablesAnneeN[periode].est_passee || false
                    });
                }
            }
        }
    }
    
    return result.sort((a, b) => new Date(a.date_proposition) - new Date(b.date_proposition));
});

// Aplatir toutes les propositions pour l'année N+1
const allProposablesN1 = computed(() => {
    const result = [];
    const ordrePeriodes = ['janvier', 'avril', 'octobre'];
    
    if (props.proposablesAnneeN1) {
        for (const periode of ordrePeriodes) {
            if (props.proposablesAnneeN1[periode] && props.proposablesAnneeN1[periode].proposables) {
                for (const proposable of props.proposablesAnneeN1[periode].proposables) {
                    result.push({
                        ...proposable,
                        periode: props.proposablesAnneeN1[periode].nom,
                        periode_key: periode,
                        date_proposition: props.proposablesAnneeN1[periode].date,
                        date_proposition_formatted: props.proposablesAnneeN1[periode].date_formatted,
                        nom_complet: `${proposable.nom} ${proposable.prenom}`,
                        est_passee: props.proposablesAnneeN1[periode].est_passee || false
                    });
                }
            }
        }
    }
    
    return result.sort((a, b) => new Date(a.date_proposition) - new Date(b.date_proposition));
});

const totalProposablesN = computed(() => allProposablesN.value.length);
const totalProposablesN1 = computed(() => allProposablesN1.value.length);

// Couleurs des badges par période avec white-space nowrap
const getPeriodeColor = (periodeKey) => {
    const colors = {
        'janvier': 'bg-orange-500 text-white px-3 py-1 rounded-full text-sm font-medium whitespace-nowrap inline-block',
        'avril': 'bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium whitespace-nowrap inline-block',
        'octobre': 'bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-medium whitespace-nowrap inline-block'
    };
    return colors[periodeKey] || 'bg-gray-500 text-white px-3 py-1 rounded-full text-sm font-medium whitespace-nowrap inline-block';
};

// Méthodes utilitaires
const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const joursRestants = (date) => {
    if (!date) return 0;
    const aujourdhui = new Date();
    const echeance = new Date(date);
    const diffTime = echeance - aujourdhui;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : 0;
};

const getJoursRestantsSeverity = (jours) => {
    if (jours <= 7) return 'danger';
    if (jours <= 30) return 'warning';
    return 'info';
};

const getMoisRestantsSeverity = (mois) => {
    if (mois <= 0) return 'danger';
    if (mois <= 1) return 'warning';
    if (mois <= 3) return 'info';
    return 'secondary';
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

const exportProposablesAnneeN = () => {
    window.open(route('dashboard.export-proposables-annee-n'), '_blank');
};

const exportProposablesAnneeN1 = () => {
    window.open(route('dashboard.export-proposables-annee-n1'), '_blank');
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