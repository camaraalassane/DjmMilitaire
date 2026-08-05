<template>
    <AuthenticatedLayout>
        <div class="bg-gradient-to-r from-emerald-500 to-emerald-700 py-4 px-6">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white">
                    Gestion des contrats
                </h2>
                <div class="flex gap-2">
                    <span class="bg-white/20 text-white px-3 py-1 rounded-full text-sm">
                        {{ militairesRenouveles.length }} militaire(s) renouvelé(s)
                    </span>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Message d'information -->
                <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="pi pi-refresh text-amber-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-amber-700">
                                Liste des militaires qui ont déjà renouvelé leur contrat.
                                Un contrat <span class="font-bold text-green-600">actif</span> et un contrat
                                <span class="font-bold text-amber-600">renouvelé</span> sont affichés.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tableau des militaires renouvelés -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-4">
                        <DataTable :value="militairesRenouveles" stripedRows responsiveLayout="scroll"
                            class="p-datatable-sm" rowKey="id">

                            <Column field="matricule" header="Matricule">
                                <template #body="slotProps">
                                    <span class="text-sm font-medium">{{ slotProps.data.matricule }}</span>
                                </template>
                            </Column>

                            <Column header="Nom & Prénom">
                                <template #body="slotProps">
                                    <div>
                                        <div class="font-medium">{{ slotProps.data.nom }} {{ slotProps.data.prenom }}
                                        </div>
                                        <div class="text-xs text-gray-500">Grade: {{ slotProps.data.grade?.nom || 'N/A'
                                        }}</div>
                                    </div>
                                </template>
                            </Column>

                            <Column header="Date d'entrée">
                                <template #body="slotProps">
                                    <span class="text-sm">{{ slotProps.data.date_entree_service }}</span>
                                </template>
                            </Column>

                            <Column header="Ancien contrat (renouvelé)">
                                <template #body="slotProps">
                                    <div class="text-sm">
                                        <div><span class="font-semibold">Début:</span>
                                            {{ slotProps.data.contrat_renouvele?.date_debut }}
                                        </div>
                                        <div><span class="font-semibold">Fin:</span>
                                            {{ slotProps.data.contrat_renouvele?.date_fin }}
                                        </div>
                                        <Tag value="RENOUVELÉ"
                                            style="background: #f59e0b; color: white; font-size: 0.6rem; padding: 0.15rem 0.4rem; margin-top: 2px;" />
                                    </div>
                                </template>
                            </Column>

                            <Column header="Nouveau contrat (actif)">
                                <template #body="slotProps">
                                    <div class="text-sm">
                                        <div><span class="font-semibold">Début:</span>
                                            {{ slotProps.data.contrat_actif?.date_debut }}
                                        </div>
                                        <div><span class="font-semibold">Fin:</span>
                                            {{ slotProps.data.contrat_actif?.date_fin }}
                                        </div>
                                        <Tag value="ACTIF"
                                            style="background: #10b981; color: white; font-size: 0.6rem; padding: 0.15rem 0.4rem; margin-top: 2px;" />
                                    </div>
                                </template>
                            </Column>

                            <Column header="Date renouvellement">
                                <template #body="slotProps">
                                    <span class="text-sm">{{ slotProps.data.date_renouvellement }}</span>
                                </template>
                            </Column>

                            <Column header="Années service">
                                <template #body="slotProps">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-emerald-600">
                                            {{ Math.floor(slotProps.data.annees_service) || 0 }}
                                        </span>
                                        <span class="text-xs text-gray-500">ans</span>
                                    </div>
                                </template>
                            </Column>

                            <Column header="Actions">
                                <template #body="slotProps">
                                    <div class="flex gap-2 flex-wrap">
                                        <Button label="Renouveler" icon="pi pi-refresh"
                                            class="p-button-sm bg-emerald-600 hover:bg-emerald-700 border-emerald-600 text-white text-xs"
                                            @click="openRenewModal(slotProps.data)" />

                                        <Button label="Annuler" icon="pi pi-undo"
                                            class="p-button-sm bg-red-600 hover:bg-red-700 border-red-600 text-white text-xs"
                                            :loading="annulationLoading[slotProps.data.id]"
                                            @click="annulerRenouvellement(slotProps.data.id)" />
                                    </div>
                                </template>
                            </Column>

                            <template #empty>
                                <div class="text-center py-8 text-gray-500">
                                    <i class="pi pi-refresh text-4xl mb-3 text-gray-300"></i>
                                    <p class="text-sm">Aucun militaire n'a encore renouvelé son contrat</p>
                                    <p class="text-xs text-gray-400 mt-1">Les renouvellements apparaîtront ici une fois
                                        effectués</p>
                                </div>
                            </template>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de renouvellement -->
        <Dialog v-model:visible="showModal" header="Renouvellement de contrat" :style="{ width: '450px' }" :modal="true"
            class="p-fluid">
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
                        <span class="font-semibold">Années de service :</span>
                        {{ Math.floor(selectedMilitaire?.annees_service) || 0 }} ans
                    </p>
                </div>

                <div class="field">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de début du nouveau contrat</label>
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

        <!-- Dialog de confirmation pour annulation -->
        <Dialog v-model:visible="showAnnulationDialog" header="Confirmation" :modal="true" :style="{ width: '400px' }"
            class="p-fluid">
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <i class="pi pi-exclamation-triangle text-3xl text-amber-500 mt-1"></i>
                    <div>
                        <p class="text-gray-700 font-medium">
                            Êtes-vous sûr de vouloir annuler ce renouvellement ?
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            L'ancien contrat sera restauré.
                        </p>
                    </div>
                </div>
            </div>

            <template #footer>
                <Button label="Non" icon="pi pi-times" @click="showAnnulationDialog = false" class="p-button-text" />
                <Button label="Oui, annuler" icon="pi pi-check" @click="confirmAnnulation"
                    class="bg-red-600 hover:bg-red-700 border-red-600 text-white" />
            </template>
        </Dialog>

        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import DatePicker from 'primevue/datepicker';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    militairesRenouveles: {
        type: Array,
        default: () => []
    },
    militaireId: {
        type: Number,
        default: null
    },
    openModal: {
        type: Boolean,
        default: false
    },
    militaireCible: {
        type: Object,
        default: null
    }
});

const toast = useToast();
const page = usePage();
const showModal = ref(false);
const showAnnulationDialog = ref(false);
const submitting = ref(false);
const selectedMilitaire = ref(null);
const annulationLoading = ref({});
const selectedDate = ref(null);
const militaireIdAAnnuler = ref(null);

const form = reactive({
    militaire_id: null,
    duree_annees: 5,
    observations: ''
});

// Watcher pour les messages flash
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        toast.add({
            severity: 'success',
            summary: 'Succès',
            detail: flash.success,
            life: 5000
        });
    }
    if (flash?.error) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: flash.error,
            life: 5000
        });
    }
}, { immediate: true });

// Formater une date locale
const formatDateLocal = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Ouvrir le modal de renouvellement
const openRenewModal = (militaire) => {
    selectedMilitaire.value = militaire;
    form.militaire_id = militaire.id;
    selectedDate.value = new Date();
    form.duree_annees = 5;
    form.observations = '';
    showModal.value = true;
};

// Fermer le modal
const closeModal = () => {
    showModal.value = false;
    form.militaire_id = null;
    selectedDate.value = null;
    form.duree_annees = 5;
    form.observations = '';
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
            showModal.value = false;
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Contrat renouvelé avec succès',
                life: 3000
            });
            router.reload();
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

// Annuler un renouvellement - OUVRE LA DIALOG
const annulerRenouvellement = (militaireId) => {
    militaireIdAAnnuler.value = militaireId;
    showAnnulationDialog.value = true;
};

// Confirmer l'annulation
const confirmAnnulation = () => {
    showAnnulationDialog.value = false;
    const militaireId = militaireIdAAnnuler.value;

    if (!militaireId) return;

    annulationLoading.value[militaireId] = true;

    router.post(route('contrats.annuler-renouvellement'), { militaire_id: militaireId }, {
        preserveScroll: true,
        onSuccess: () => {
            annulationLoading.value[militaireId] = false;
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Renouvellement annulé avec succès',
                life: 5000
            });
            router.reload();
        },
        onError: (errors) => {
            annulationLoading.value[militaireId] = false;
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: Object.values(errors).flat()[0] || 'Impossible d\'annuler le renouvellement',
                life: 5000
            });
        }
    });
};

// Ouvrir le modal depuis la redirection
const openRenewalFromRedirect = () => {
    if (!props.militaireId || !props.openModal) {
        return;
    }

    let militaire = props.militairesRenouveles.find(m => m.id === props.militaireId);

    if (!militaire && props.militaireCible) {
        militaire = props.militaireCible;
        toast.add({
            severity: 'info',
            summary: 'Information',
            detail: 'Ce militaire n\'est pas dans la liste des renouvelés mais vous pouvez toujours renouveler son contrat.',
            life: 5000
        });
    }

    if (militaire) {
        openRenewModal(militaire);
    } else {
        toast.add({
            severity: 'warn',
            summary: 'Militaire non trouvé',
            detail: 'Impossible de trouver ce militaire.',
            life: 5000
        });
    }
};

// Initialisation
onMounted(() => {
    if (props.openModal) {
        setTimeout(() => {
            openRenewalFromRedirect();
        }, 300);
    }
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
    background-color: #f0fdf4;
}

:deep(.p-dialog .p-dialog-content) {
    padding: 1.5rem;
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

.bg-red-600 {
    background-color: #dc2626;
}

.hover\:bg-red-700:hover {
    background-color: #b91c1c;
}
</style>
