<template>
    <AuthenticatedLayout>
        <div class="bg-gradient-to-r from-sky-500 to-sky-700 py-4 px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <h2 class="font-semibold text-xl text-white">
                    Liste des alertes
                </h2>
                <div class="flex flex-wrap gap-2">
                    <Button v-if="statistiques.non_vues > 0" label="Tout marquer comme vu" icon="pi pi-check-circle"
                        class="p-button-sm bg-white text-sky-600 hover:bg-sky-50 hover:text-sky-700"
                        @click="markAllAsRead" />
                    <Button label="Générer alertes contrats" icon="pi pi-refresh"
                        class="p-button-sm bg-amber-600 hover:bg-amber-700 text-white"
                        @click="generateContractAlerts" />
                </div>
            </div>
        </div>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Cartes de navigation -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="cursor-pointer transition-all hover:shadow-md rounded-lg overflow-hidden"
                        :class="activeTab === 'promotion' ? 'ring-2 ring-sky-500 shadow-md' : ''"
                        @click="setActiveTab('promotion')">
                        <div class="bg-gradient-to-r from-sky-500 to-sky-600 p-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center shrink-0">
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

                    <div class="cursor-pointer transition-all hover:shadow-md rounded-lg overflow-hidden"
                        :class="activeTab === 'formation' ? 'ring-2 ring-amber-500 shadow-md' : ''"
                        @click="setActiveTab('formation')">
                        <div class="bg-gradient-to-r from-amber-500 to-amber-600 p-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center shrink-0">
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

                    <div class="cursor-pointer transition-all hover:shadow-md rounded-lg overflow-hidden"
                        :class="activeTab === 'contrat' ? 'ring-2 ring-emerald-500 shadow-md' : ''"
                        @click="setActiveTab('contrat')">
                        <div
                            class="bg-gradient-to-r from-emerald-500 to-emerald-600 p-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center shrink-0">
                                    <i class="pi pi-file text-lg text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-white">Contrats</h3>
                                    <p class="text-xs text-white/80">Renouvellements</p>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-white">{{ statistiques.contrats || 0 }}</div>
                        </div>
                    </div>
                </div>

                <!-- Statistiques globales -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
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

                <!-- Barre "Toutes" + recherche -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-6">
                    <div class="p-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <Button label="Tous afficher"
                                :class="activeTab === 'all' ? 'bg-sky-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                                class="p-button-sm text-sm w-full sm:w-auto" @click="setActiveTab('all')" />
                            <div class="w-full sm:w-auto">
                                <label class="block text-sm font-medium text-gray-700 mb-1 sm:sr-only">Recherche</label>
                                <span class="p-input-icon-left w-full">
                                    <i class="pi pi-search" />
                                    <InputText v-model="filters.search" placeholder="Rechercher..."
                                        class="w-full sm:w-56 text-sm" @input="onSearchInput" />
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau des alertes -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-4">
                        <div class="mb-3">
                            <h3 class="text-md font-semibold text-gray-800">{{ getTabTitle() }}</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <DataTable :value="alertes.data" stripedRows responsiveLayout="scroll" :loading="loading"
                                paginator lazy :rows="alertes.per_page" :totalRecords="alertes.total"
                                @page="onPageChange" class="p-datatable-sm" :rowClass="rowClass">

                                <Column field="militaire.nom" header="Militaire">
                                    <template #body="slotProps">
                                        <div v-if="slotProps.data.militaire">
                                            <Button
                                                :label="slotProps.data.militaire.nom + ' ' + slotProps.data.militaire.prenom"
                                                class="p-button-link p-0 text-sky-600 hover:text-sky-800 text-sm"
                                                @click="viewMilitaire(slotProps.data.militaire.id)" />
                                            <div class="text-xs text-gray-500">{{ slotProps.data.militaire.matricule }}
                                            </div>
                                        </div>
                                        <span v-else class="text-gray-400 text-sm">Militaire supprimé</span>
                                    </template>
                                </Column>

                                <Column field="type_alerte" header="Type">
                                    <template #body="slotProps">
                                        <Tag :value="getTypeLabel(slotProps.data.type_alerte)"
                                            :style="getTypeStyle(slotProps.data.type_alerte)" />
                                    </template>
                                </Column>

                                <Column field="message" header="Message">
                                    <template #body="slotProps">
                                        <span class="text-sm">{{ slotProps.data.message }}</span>
                                    </template>
                                </Column>

                                <Column field="date_echeance_formatted" header="Échéance">
                                    <template #body="slotProps">
                                        <div :class="{ 'font-bold text-red-600 text-sm': isEcheanceProche(slotProps.data.date_echeance) }"
                                            class="text-sm whitespace-nowrap">
                                            {{ slotProps.data.date_echeance_formatted }}
                                        </div>
                                    </template>
                                </Column>

                                <Column field="created_at" header="Créée le">
                                    <template #body="slotProps">
                                        <span class="text-sm whitespace-nowrap">{{ slotProps.data.created_at }}</span>
                                    </template>
                                </Column>

                                <Column header="Statut">
                                    <template #body="slotProps">
                                        <Tag v-if="slotProps.data.est_vue" value="Vue"
                                            style="background: #10b981; color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem;" />
                                        <Tag v-else value="Non vue"
                                            style="background: #f97316; color: white; font-size: 0.7rem; padding: 0.2rem 0.5rem;" />
                                    </template>
                                </Column>

                                <Column header="Action" style="min-width: 9rem;">
                                    <template #body="slotProps">
                                        <div class="flex flex-col gap-1.5 items-stretch">
                                            <Button v-if="!slotProps.data.est_vue" label="Marquer vue"
                                                icon="pi pi-check"
                                                class="p-button-sm bg-emerald-600 hover:bg-emerald-700 border-emerald-600 text-white text-xs justify-center"
                                                :loading="loadingStates[slotProps.data.id]"
                                                @click="markAsRead(slotProps.data.id)" />
                                            <Button
                                                v-if="slotProps.data.type_alerte === 'contrat' && slotProps.data.militaire"
                                                label="Renouveler" icon="pi pi-refresh"
                                                class="p-button-sm bg-amber-600 hover:bg-amber-700 border-amber-600 text-white text-xs justify-center"
                                                @click="openRenewModal(slotProps.data.militaire)" />
                                        </div>
                                    </template>
                                </Column>

                                <template #empty>
                                    <div class="text-center py-6 text-gray-500">
                                        <i class="pi pi-bell-slash text-3xl mb-2"></i>
                                        <p class="text-sm">Aucune alerte trouvée</p>
                                    </div>
                                </template>
                            </DataTable>
                        </div>

                        <div class="text-center sm:text-left text-sm text-gray-600 mt-4">
                            Affichage de {{ alertes.from }} à {{ alertes.to }} sur {{ alertes.total }} alertes
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de renouvellement de contrat -->
        <Dialog v-model:visible="showRenewModal" header="Renouvellement de contrat" :style="{ width: '450px' }"
            :modal="true" class="p-fluid">
            <div class="space-y-4">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold">Militaire :</span>
                        {{ selectedMilitaire?.nom }} {{ selectedMilitaire?.prenom }}
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold">Matricule :</span>
                        {{ selectedMilitaire?.matricule }}
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold">Grade :</span>
                        {{ selectedMilitaire?.grade_actuel }}
                    </p>
                    <p class="text-sm text-gray-600" v-if="selectedMilitaire?.contrat_actif">
                        <span class="font-semibold">Contrat actuel :</span>
                        {{ selectedMilitaire.contrat_actif.date_debut }} → {{ selectedMilitaire.contrat_actif.date_fin
                        }}
                    </p>
                </div>

                <div class="field">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de début du nouveau contrat</label>
                    <!--
                        FIX : une seule source de vérité (selectedDate). On a supprimé
                        form.date_debut ainsi que les listeners @date-select /
                        @update:model-value redondants qui causaient la désynchronisation
                        (le formulaire soumettait parfois l'ancienne valeur "aujourd'hui"
                        même après avoir choisi une autre date).
                        :minDate est maintenant une valeur figée au montage du composant
                        (computed une seule fois) plutôt que "new Date()" recréé à chaque
                        re-render, ce qui pouvait perturber la validation interne du DatePicker.
                    -->
                    <DatePicker v-model="selectedDate" dateFormat="dd/mm/yy" class="w-full" :showIcon="true" />
                    <small class="text-gray-500 text-xs">Sélectionnez la date de début du nouveau contrat</small>
                </div>

                <div class="field">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Durée (en années)</label>
                    <InputNumber v-model="form.duree_annees" :min="1" :max="10" class="w-full" />
                    <small class="text-gray-500 text-xs">Durée recommandée: 5 ans</small>
                </div>

                <div class="field">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observations</label>
                    <Textarea v-model="form.observations" rows="3" placeholder="Ajouter des observations..." />
                </div>
            </div>

            <template #footer>
                <Button label="Annuler" icon="pi pi-times" @click="closeModal" class="p-button-text" />
                <Button label="Renouveler le contrat" icon="pi pi-check" @click="renewContract" :loading="submitting"
                    class="bg-emerald-600 hover:bg-emerald-700 border-emerald-600 text-white" />
            </template>
        </Dialog>

        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import Dialog from 'primevue/dialog';
import DatePicker from 'primevue/datepicker';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';
import debounce from 'lodash/debounce';

const props = defineProps({
    alertes: { type: Object, required: true },
    statistiques: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) }
});

const toast = useToast();
const page = usePage();
const loading = ref(false);
const loadingStates = ref({});
const activeTab = ref(props.filters?.type || 'all');

const showRenewModal = ref(false);
const submitting = ref(false);
const selectedMilitaire = ref(null);

// FIX : source de vérité unique pour la date du nouveau contrat.
// form.date_debut a été supprimé — on lit directement selectedDate au submit.
const selectedDate = ref(null);

const form = reactive({
    militaire_id: null,
    duree_annees: 5,
    observations: ''
});

const filters = reactive({
    search: props.filters?.search || ''
});

// Watcher pour les messages flash
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        toast.add({ severity: 'success', summary: 'Succès', detail: flash.success, life: 5000 });
    }
    if (flash?.error) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: flash.error, life: 5000 });
    }
    if (flash?.info) {
        toast.add({ severity: 'info', summary: 'Information', detail: flash.info, life: 5000 });
    }
}, { immediate: true });

const getTabTitle = () => {
    const titles = {
        'promotion': 'Alertes de promotion',
        'formation': 'Alertes de formation',
        'contrat': 'Alertes de contrat',
        'all': 'Toutes les alertes'
    };
    return titles[activeTab.value] || 'Toutes les alertes';
};

const debouncedSearch = debounce(() => { loadAlertes(1); }, 500);
const onSearchInput = () => { debouncedSearch(); };
watch(() => filters.search, () => { debouncedSearch(); });

const getTypeLabel = (type) => {
    const labels = {
        'promotion': 'Promotion',
        'formation': 'Formation',
        'retraite': 'Retraite',
        'certificat': 'Certificat',
        'contrat': 'Contrat'
    };
    return labels[type] || type;
};

const getTypeStyle = (type) => {
    const styles = {
        'promotion': { background: '#0284c7', color: 'white', fontSize: '0.7rem', padding: '0.2rem 0.5rem' },
        'formation': { background: '#f97316', color: 'white', fontSize: '0.7rem', padding: '0.2rem 0.5rem' },
        'retraite': { background: '#dc2626', color: 'white', fontSize: '0.7rem', padding: '0.2rem 0.5rem' },
        'certificat': { background: '#8b5cf6', color: 'white', fontSize: '0.7rem', padding: '0.2rem 0.5rem' },
        'contrat': { background: '#059669', color: 'white', fontSize: '0.7rem', padding: '0.2rem 0.5rem' }
    };
    return styles[type] || { background: '#6b7280', color: 'white', fontSize: '0.7rem', padding: '0.2rem 0.5rem' };
};

const isEcheanceProche = (date) => {
    if (!date) return false;
    const today = new Date();
    const echeance = new Date(date);
    const diffDays = Math.ceil((echeance - today) / (1000 * 60 * 60 * 24));
    return diffDays <= 30 && diffDays >= 0;
};

const rowClass = (data) => data.est_vue ? '' : 'bg-amber-50';

const loadAlertes = (page = 1) => {
    loading.value = true;
    const params = { page, search: filters.search };
    if (activeTab.value !== 'all') params.type = activeTab.value;

    router.get(route('alertes.index'), params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => { loading.value = false; },
        onError: () => {
            loading.value = false;
            toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les alertes', life: 3000 });
        }
    });
};

const setActiveTab = (tab) => {
    if (activeTab.value === tab) return;
    activeTab.value = tab;
    loadAlertes(1);
};

const onPageChange = (event) => { loadAlertes(event.page + 1); };

const markAsRead = (alerteId) => {
    loadingStates.value[alerteId] = true;
    router.post(route('alertes.marquer-vue', alerteId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            loadingStates.value[alerteId] = false;
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Alerte marquée comme vue', life: 3000 });
            loadAlertes(props.alertes.current_page);
        },
        onError: () => {
            loadingStates.value[alerteId] = false;
            toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de marquer l\'alerte', life: 3000 });
        }
    });
};

const markAllAsRead = () => {
    router.post(route('alertes.marquer-tout-vue'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Toutes les alertes ont été marquées comme vues', life: 3000 });
            loadAlertes(props.alertes.current_page);
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de marquer toutes les alertes', life: 3000 });
        }
    });
};

const generateContractAlerts = () => {
    toast.add({ severity: 'info', summary: 'Chargement', detail: 'Génération des alertes en cours...', life: 2000 });
    router.post(route('alertes.check-renouvellements'), {}, {
        preserveScroll: true,
        onSuccess: (response) => {
            const flash = response.props?.flash;
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: flash?.success || 'Alertes de contrat générées avec succès',
                life: 5000
            });
            loadAlertes(props.alertes.current_page);
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de générer les alertes de contrat', life: 3000 });
        }
    });
};

// Ouvrir le modal de renouvellement
const openRenewModal = (militaire) => {
    selectedMilitaire.value = militaire;
    form.militaire_id = militaire.id;
    selectedDate.value = new Date();
    form.duree_annees = 5;
    form.observations = '';
    showRenewModal.value = true;
};

// Fermer le modal
const closeModal = () => {
    showRenewModal.value = false;
    form.militaire_id = null;
    selectedDate.value = null;
    form.duree_annees = 5;
    form.observations = '';
};

// Formater une date locale (année-mois-jour) sans décalage de fuseau horaire
const formatDateLocal = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Renouveler le contrat
const renewContract = () => {
    if (!selectedDate.value) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Veuillez sélectionner une date de début',
            life: 3000
        });
        return;
    }

    submitting.value = true;

    const data = {
        militaire_id: form.militaire_id,
        date_debut: formatDateLocal(selectedDate.value),
        duree_annees: form.duree_annees,
        observations: form.observations
    };

    router.post(route('contrats.store'), data, {
        preserveScroll: true,
        onSuccess: () => {
            submitting.value = false;
            showRenewModal.value = false;
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Contrat renouvelé avec succès',
                life: 3000
            });
            loadAlertes(props.alertes.current_page);
        },
        onError: (errors) => {
            submitting.value = false;
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: Object.values(errors).flat()[0] || 'Impossible de renouveler le contrat',
                life: 3000
            });
        }
    });
};

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

:deep(.p-dialog .p-dialog-content) {
    padding: 1.5rem;
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

.bg-emerald-600 {
    background-color: #059669;
}

.hover\:bg-emerald-700:hover {
    background-color: #047857;
}

.bg-amber-600 {
    background-color: #d97706;
}

.hover\:bg-amber-700:hover {
    background-color: #b45309;
}
</style>
