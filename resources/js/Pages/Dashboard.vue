<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tableau de bord</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Cartes statistiques -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-7 gap-4 mb-8">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-xl shadow-md p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="pi pi-users text-xl"></i>
                                    <span class="text-sm font-medium">Total Militaires</span>
                                </div>
                                <div class="text-3xl font-bold">{{ statistiques.total_militaires }}</div>
                            </div>
                            <div class="bg-gradient-to-r from-green-500 to-green-700 text-white rounded-xl shadow-md p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="pi pi-check-circle text-xl"></i>
                                    <span class="text-sm font-medium">Militaires Actifs</span>
                                </div>
                                <div class="text-3xl font-bold">{{ statistiques.militaires_actifs }}</div>
                            </div>
                            <div class="bg-gradient-to-r from-yellow-500 to-yellow-700 text-white rounded-xl shadow-md p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="pi pi-bell text-xl"></i>
                                    <span class="text-sm font-medium">Alertes Non Vues</span>
                                </div>
                                <div class="text-3xl font-bold">{{ statistiques.alertes_non_vues }}</div>
                            </div>
                            <div class="bg-gradient-to-r from-red-500 to-red-700 text-white rounded-xl shadow-md p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="pi pi-calendar text-xl"></i>
                                    <span class="text-sm font-medium">Retraites N</span>
                                </div>
                                <div class="text-3xl font-bold">{{ retraitesNTotal }}</div>
                            </div>
                            <div class="bg-gradient-to-r from-orange-500 to-orange-700 text-white rounded-xl shadow-md p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="pi pi-calendar text-xl"></i>
                                    <span class="text-sm font-medium">Retraites N+1</span>
                                </div>
                                <div class="text-3xl font-bold">{{ retraitesN1Total }}</div>
                            </div>
                            <div class="bg-gradient-to-r from-purple-500 to-purple-700 text-white rounded-xl shadow-md p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="pi pi-star text-xl"></i>
                                    <span class="text-sm font-medium">Proposables N</span>
                                </div>
                                <div class="text-3xl font-bold">{{ proposablesNTotal }}</div>
                            </div>
                            <div class="bg-gradient-to-r from-indigo-500 to-indigo-700 text-white rounded-xl shadow-md p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="pi pi-star text-xl"></i>
                                    <span class="text-sm font-medium">Proposables N+1</span>
                                </div>
                                <div class="text-3xl font-bold">{{ proposablesN1Total }}</div>
                            </div>
                        </div>

                        <!-- Boutons de sélection -->
                        <div class="flex flex-wrap gap-3 mb-6 border-b pb-4">
                            <Button label="Retraites N" 
                                    :class="activeSection === 'retraitesN' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700'"
                                    @click="loadSection('retraitesN')" />
                            <Button label="Retraites N+1" 
                                    :class="activeSection === 'retraitesN1' ? 'bg-orange-600 text-white' : 'bg-gray-200 text-gray-700'"
                                    @click="loadSection('retraitesN1')" />
                            <Button label="Proposables N" 
                                    :class="activeSection === 'proposablesN' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700'"
                                    @click="loadSection('proposablesN')" />
                            <Button label="Proposables N+1" 
                                    :class="activeSection === 'proposablesN1' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
                                    @click="loadSection('proposablesN1')" />
                        </div>

                        <!-- Section chargée dynamiquement -->
                        <div v-if="loading" class="text-center py-12">
                            <i class="pi pi-spin pi-spinner text-3xl text-sky-600"></i>
                            <p class="mt-2 text-gray-500">Chargement...</p>
                        </div>
                        <div v-else-if="sectionData">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">{{ sectionTitle }}</h3>
                                <Button label="Exporter en Excel" 
                                        icon="pi pi-file-excel" 
                                        class="p-button-success p-button-sm"
                                        @click="exportCurrentSection" />
                            </div>
                            <Card>
                                <template #title>
                                    <div class="flex items-center gap-2">
                                        <i :class="sectionIcon"></i>
                                        <span>{{ sectionTitle }}</span>
                                        <Tag :value="sectionTotal" severity="warning" />
                                    </div>
                                </template>
                                <template #content>
                                    <DataTable :value="sectionData" 
                                               stripedRows 
                                               responsiveLayout="scroll"
                                               class="p-datatable-sm"
                                               :rows="10"
                                               paginator
                                               :rowsPerPageOptions="[10, 20, 50, 100]"
                                               :sortField="sortField"
                                               :sortOrder="1">
                                        <Column v-for="col in columns" :key="col.field" :field="col.field" :header="col.header" :sortable="col.sortable">
                                            <template #body="slotProps">
                                                <span v-if="col.field === 'matricule'">
                                                    <Tag :value="slotProps.data.matricule" severity="info" />
                                                </span>
                                                <span v-else-if="col.field === 'grade_cible'">
                                                    <Tag :value="slotProps.data.grade_cible" severity="success" />
                                                </span>
                                                <span v-else-if="col.field === 'grade_actuel'">
                                                    <Tag :value="slotProps.data.grade_actuel" severity="secondary" />
                                                </span>
                                                <div v-else-if="col.field === 'date_proposition_formatted'" class="flex items-center gap-2 whitespace-nowrap">
                                                    <i class="pi pi-calendar"></i>
                                                    <span>{{ slotProps.data.date_proposition_formatted }}</span>
                                                </div>
                                                <div v-else-if="col.field === 'date_retraite_formatted'" class="flex items-center gap-2 whitespace-nowrap">
                                                    <i class="pi pi-calendar"></i>
                                                    <span>{{ slotProps.data.date_retraite_formatted }}</span>
                                                </div>
                                                <div v-else-if="col.field === 'date_anciennete_formatted'" class="flex items-center gap-2 whitespace-nowrap">
                                                    <i class="pi pi-clock"></i>
                                                    <span>{{ slotProps.data.date_anciennete_formatted }}</span>
                                                </div>
                                                <span v-else>{{ slotProps.data[col.field] }}</span>
                                            </template>
                                        </Column>
                                        <template #empty>
                                            <div class="text-center p-8 text-gray-500">
                                                <i class="pi pi-info-circle text-4xl mb-2"></i>
                                                <p>Aucune donnée pour cette section</p>
                                            </div>
                                        </template>
                                    </DataTable>
                                </template>
                            </Card>
                        </div>
                        <div v-else class="text-center py-12 text-gray-500">
                            <i class="pi pi-info-circle text-4xl mb-2"></i>
                            <p>Sélectionnez une section ci-dessus</p>
                        </div>

                        <!-- Alertes récentes -->
                        <div class="mt-8">
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
                                                        <strong>{{ alerte.militaire?.nom || 'Inconnu' }} {{ alerte.militaire?.prenom || '' }}</strong>
                                                        <Tag v-if="alerte.militaire?.matricule" :value="alerte.militaire.matricule" severity="info" />
                                                    </div>
                                                    <p class="text-sm">{{ alerte.message }}</p>
                                                    <div class="flex items-center gap-2 mt-2 text-xs text-gray-600">
                                                        <i class="pi pi-calendar"></i>
                                                        <span>Échéance: {{ formatDate(alerte.date_echeance) }}</span>
                                                        <Tag :value="joursRestants(alerte.date_echeance)" 
                                                             :severity="getJoursRestantsSeverity(joursRestants(alerte.date_echeance))" />
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
                        </div>

                        <!-- Répartition par grade -->
                        <Card class="mt-8">
                            <template #title>
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-chart-bar"></i>
                                    <span>Répartition par grade</span>
                                </div>
                            </template>
                            <template #content>
                                <DataTable :value="gradesFiltres" stripedRows responsiveLayout="scroll" class="p-datatable-sm">
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
                                                <ProgressBar :value="calculerPourcentage(slotProps.data.militaires_count)" class="w-48 h-2" />
                                                <span class="text-sm font-medium">{{ calculerPourcentage(slotProps.data.militaires_count).toFixed(1) }}%</span>
                                            </div>
                                        </template>
                                    </Column>
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
import axios from 'axios';
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
    statistiques: Object,
    grades: Array,
});

const toast = useToast();
const loadingStates = ref({});
const activeSection = ref(null);
const sectionData = ref(null);
const sectionTitle = ref('');
const sectionIcon = ref('');
const sectionTotal = ref(0);
const sortField = ref('');
const columns = ref([]);
const loading = ref(false);

const retraitesNTotal = ref(0);
const retraitesN1Total = ref(0);
const proposablesNTotal = ref(0);
const proposablesN1Total = ref(0);

const gradesFiltres = computed(() => props.grades.filter(g => g.militaires_count > 0));

const loadSection = (section) => {
    if (activeSection.value === section && sectionData.value) return;
    loading.value = true;
    activeSection.value = section;

    axios.get(route('dashboard.section'), { params: { section } })
        .then(response => {
            const data = response.data;
            sectionData.value = data.data;
            sectionTitle.value = data.title;
            sectionIcon.value = data.icon;
            sectionTotal.value = data.total;
            sortField.value = data.sortField;
            columns.value = data.columns;
            
            switch (section) {
                case 'retraitesN':
                    retraitesNTotal.value = data.total;
                    break;
                case 'retraitesN1':
                    retraitesN1Total.value = data.total;
                    break;
                case 'proposablesN':
                    proposablesNTotal.value = data.total;
                    break;
                case 'proposablesN1':
                    proposablesN1Total.value = data.total;
                    break;
            }
            loading.value = false;
        })
        .catch(error => {
            console.error(error);
            toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger la section', life: 3000 });
            loading.value = false;
        });
};

const exportCurrentSection = () => {
    let routeName = '';
    if (activeSection.value === 'retraitesN') routeName = 'dashboard.export-retraites-annee-n';
    else if (activeSection.value === 'retraitesN1') routeName = 'dashboard.export-retraites-annee-n1';
    else if (activeSection.value === 'proposablesN') routeName = 'dashboard.export-proposables-annee-n';
    else if (activeSection.value === 'proposablesN1') routeName = 'dashboard.export-proposables-annee-n1';
    if (routeName) window.open(route(routeName), '_blank');
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR');
};

const joursRestants = (date) => {
    if (!date) return 0;
    const diff = Math.ceil((new Date(date) - new Date()) / (1000 * 60 * 60 * 24));
    return diff > 0 ? diff : 0;
};

const getJoursRestantsSeverity = (jours) => {
    if (jours <= 7) return 'danger';
    if (jours <= 30) return 'warning';
    return 'info';
};

const getAlerteClass = (type) => {
    const classes = {
        'promotion': 'border-blue-200 bg-blue-50',
        'formation': 'border-yellow-200 bg-yellow-50',
        'retraite': 'border-red-200 bg-red-50',
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
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Alerte marquée comme vue', life: 3000 });
            loadingStates.value[alerteId] = false;
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de marquer l\'alerte', life: 3000 });
            loadingStates.value[alerteId] = false;
        }
    });
};
</script>

<style scoped>
:deep(.p-card) { border-radius: 12px; overflow: hidden; }
:deep(.p-datatable .p-datatable-tbody > tr:hover) { background-color: #f3f4f6 !important; }
</style>