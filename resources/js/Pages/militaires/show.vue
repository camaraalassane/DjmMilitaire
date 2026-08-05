<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white">
                    {{ militaire.nom }} {{ militaire.prenom }}
                </h2>
                <div class="flex gap-2">
                    <Button label="Modifier" 
                            icon="pi pi-pencil"
                            class="p-button-sm"
                            style="background-color: #f59e0b; border-color: #f59e0b; color: white;"
                            @click="editMilitaire" />
                    <Button label="Supprimer" 
                            icon="pi pi-trash"
                            class="p-button-sm"
                            style="background-color: #ef4444; border-color: #ef4444; color: white;"
                            @click="confirmDelete" />
                    <Button label="Retour" 
                            icon="pi pi-arrow-left"
                            class="p-button-sm"
                            style="background-color: #6b7280; border-color: #6b7280; color: white;"
                            @click="goBack" />
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <Card>
                            <template #title>
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-user text-sky-500"></i>
                                    <span class="text-sky-600">Informations générales</span>
                                </div>
                            </template>
                            
                            <template #content>
                                <!-- Informations principales en grille 2 colonnes -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    
                                    <!-- LIGNE 1 : Matricule & Date de naissance -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-id-card text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Matricule</span>
                                        </div>
                                        <Tag :value="militaire.matricule" 
                                             style="background: #bae6fd; color: #0369a1;" 
                                             class="text-base" />
                                    </div>

                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-calendar text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Date de naissance</span>
                                        </div>
                                        <div class="text-gray-800">{{ militaire.date_naissance ?? '-' }}</div>
                                        <small class="text-gray-500">{{ militaire.age }} ans</small>
                                    </div>

                                    <!-- LIGNE 2 : Grade actuel & Statut -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-briefcase text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Grade actuel</span>
                                        </div>
                                        <Tag :value="militaire.grade_actuel" 
                                             style="background: #7dd3fc; color: #0369a1;" />
                                        <div v-if="militaire.date_derniere_promotion" class="mt-2 text-sm">
                                            <span class="text-gray-600">Dernière promotion :</span>
                                            {{ militaire.date_derniere_promotion }}
                                        </div>
                                    </div>

                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-tag text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Statut</span>
                                        </div>
                                        <Tag :value="militaire.statut" 
                                             :style="getStatutStyle(militaire.statut)" />
                                    </div>

                                    <!-- LIGNE 3 : Permis de conduire & Date d'entrée en service -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-car text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Permis de conduire</span>
                                        </div>
                                        <Tag :value="militaire.a_permis_conduire ? 'Oui' : 'Non'" 
                                             :style="militaire.a_permis_conduire ? { background: '#7dd3fc', color: '#0369a1' } : { background: '#e5e7eb', color: '#6b7280' }" />
                                    </div>

                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-calendar-plus text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Date d'entrée en service</span>
                                        </div>
                                        <div class="text-gray-800">{{ militaire.date_entree_service ?? '-' }}</div>
                                        <small class="text-gray-500">{{ formatAnciennete(militaire.anciennete) }} de service</small>
                                    </div>

                                    <!-- LIGNE 4 : Ancienneté dans le grade & Date de retraite -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-chart-line text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Ancienneté dans le grade</span>
                                        </div>
                                        <Tag :value="formatAnciennete(militaire.anciennete_grade)" 
                                             style="background: #bae6fd; color: #0369a1;" />
                                    </div>

                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-calendar-minus text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Date de retraite</span>
                                        </div>
                                        <div v-if="militaire.date_retraite">
                                            <span class="text-gray-800">{{ militaire.date_retraite }}</span>
                                            <Tag v-if="militaire.est_eligible_retraite" 
                                                 value="Bientôt" 
                                                 style="background: #f97316; color: white;"
                                                 class="ml-2" />
                                        </div>
                                        <span v-else class="text-gray-400">Non calculée</span>
                                    </div>

                                    <!-- LIGNE 5 : Spécialité & Position actuelle -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-book text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Spécialité</span>
                                        </div>
                                        <div class="text-gray-800">{{ militaire.specialite || '-' }}</div>
                                    </div>

                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-map-marker text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Position actuelle</span>
                                        </div>
                                        <div class="text-gray-800">{{ militaire.position_actuelle || '-' }}</div>
                                    </div>

                                    <!-- LIGNE 6 : Fonction passée & Fonction actuelle -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-history text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Fonction passée</span>
                                        </div>
                                        <div class="text-gray-800">{{ militaire.fonction_passee || '-' }}</div>
                                    </div>

                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-briefcase text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Fonction actuelle</span>
                                        </div>
                                        <div class="text-gray-800">{{ militaire.fonction_actuelle || '-' }}</div>
                                    </div>

                                    <!-- LIGNE 7 : Problème judiciaire & Problème disciplinaire (sur une ligne) -->
                                    <div class="md:col-span-2">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="border rounded-lg p-3 hover:border-sky-300 transition-all">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <i class="pi pi-gavel text-amber-500"></i>
                                                    <span class="text-sm font-medium text-gray-700">Problème judiciaire</span>
                                                </div>
                                                <Tag :value="militaire.a_fait_justice ? 'Oui' : 'Non'" 
                                                     :style="militaire.a_fait_justice ? { background: '#fecaca', color: '#991b1b' } : { background: '#7dd3fc', color: '#0369a1' }" />
                                            </div>
                                            <div class="border rounded-lg p-3 hover:border-sky-300 transition-all">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <i class="pi pi-exclamation-triangle text-amber-500"></i>
                                                    <span class="text-sm font-medium text-gray-700">Problème disciplinaire</span>
                                                </div>
                                                <Tag :value="militaire.a_fait_discipline ? 'Oui' : 'Non'" 
                                                     :style="militaire.a_fait_discipline ? { background: '#fecaca', color: '#991b1b' } : { background: '#7dd3fc', color: '#0369a1' }" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Certificats obtenus -->
                                <Divider>
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-verified text-sky-500"></i>
                                        <span class="font-medium text-gray-700">Certificats obtenus</span>
                                    </div>
                                </Divider>

                                <div v-if="certificats.length === 0" class="text-center py-4 text-gray-500">
                                    <i class="pi pi-info-circle text-2xl mb-2"></i>
                                    <p>Aucun certificat obtenu</p>
                                </div>

                                <DataTable v-else :value="certificats" 
                                           stripedRows 
                                           responsiveLayout="scroll"
                                           class="p-datatable-sm mb-6">
                                    <Column field="nom_certificat" header="Certificat"></Column>
                                    <Column field="niveau_certificat" header="Niveau">
                                        <template #body="slotProps">
                                            <Tag :value="slotProps.data.niveau_certificat || '-'" 
                                                 style="background: #bae6fd; color: #0369a1;" />
                                        </template>
                                    </Column>
                                    <Column field="date_obtention" header="Date d'obtention">
                                        <template #body="slotProps">
                                            {{ slotProps.data.date_obtention ?? '-' }}
                                        </template>
                                    </Column>
                                </DataTable>

                                <!-- Alertes associées -->
                                <Divider>
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-bell text-amber-500"></i>
                                        <span class="font-medium text-gray-700">Alertes associées</span>
                                    </div>
                                </Divider>

                                <div v-if="alertes.length === 0" class="text-center py-4 text-gray-500">
                                    <i class="pi pi-info-circle text-2xl mb-2"></i>
                                    <p>Aucune alerte pour ce militaire</p>
                                </div>

                                <DataTable v-else :value="alertes" 
                                           stripedRows 
                                           responsiveLayout="scroll"
                                           class="p-datatable-sm">
                                    <Column field="type_alerte" header="Type">
                                        <template #body="slotProps">
                                            <Tag :value="getTypeLabel(slotProps.data.type_alerte)" 
                                                 :style="getAlerteStyle(slotProps.data.type_alerte)" />
                                        </template>
                                    </Column>
                                    <Column field="message" header="Message"></Column>
                                    <Column field="date_echeance" header="Échéance">
                                        <template #body="slotProps">
                                            {{ slotProps.data.date_echeance ?? '-' }}
                                        </template>
                                    </Column>
                                    <Column field="est_vue" header="Statut">
                                        <template #body="slotProps">
                                            <Tag :value="slotProps.data.est_vue ? 'Vue' : 'Non vue'" 
                                                 :style="slotProps.data.est_vue ? { background: '#7dd3fc', color: '#0369a1' } : { background: '#fed7aa', color: '#c2410c' }" />
                                        </template>
                                    </Column>
                                </DataTable>
                            </template>
                        </Card>
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
                <p class="text-gray-700 text-sm">Êtes-vous sûr de vouloir supprimer le militaire <strong>{{ militaire.nom }} {{ militaire.prenom }}</strong> ?</p>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button label="Non" 
                            icon="pi pi-times" 
                            class="p-button-text text-gray-500 hover:text-gray-700"
                            @click="deleteDialogVisible = false" />
                    <Button label="Oui" 
                            icon="pi pi-check" 
                            style="background-color: #ef4444; border-color: #ef4444; color: white;"
                            @click="deleteMilitaire" />
                </div>
            </template>
        </Dialog>

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
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Divider from 'primevue/divider';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    militaire: {
        type: Object,
        required: true
    },
    certificats: {
        type: Array,
        default: () => []
    },
    alertes: {
        type: Array,
        default: () => []
    }
});

const toast = useToast();
const deleteDialogVisible = ref(false);

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

// Style pour les alertes
const getAlerteStyle = (type) => {
    const styles = {
        'promotion': { background: '#bae6fd', color: '#0369a1' },
        'formation': { background: '#fed7aa', color: '#c2410c' },
        'retraite': { background: '#fecaca', color: '#991b1b' }
    };
    return styles[type] || { background: '#e5e7eb', color: '#374151' };
};

// Obtenir le libellé du type d'alerte
const getTypeLabel = (type) => {
    const labels = {
        'promotion': 'Promotion',
        'formation': 'Formation',
        'retraite': 'Retraite'
    };
    return labels[type] || type;
};

// Formater l'ancienneté (arrondir à l'entier)
const formatAnciennete = (annees) => {
    if (!annees && annees !== 0) return '0 ans';
    return `${Math.floor(annees)} ans`;
};

// Navigation
const editMilitaire = () => {
    router.visit(route('militaires.edit', props.militaire.id));
};

const goBack = () => {
    router.visit(route('militaires.index'));
};

// Confirmation de suppression
const confirmDelete = () => {
    deleteDialogVisible.value = true;
};

// Supprimer un militaire
const deleteMilitaire = () => {
    router.delete(route('militaires.destroy', props.militaire.id), {
        onSuccess: () => {
            deleteDialogVisible.value = false;
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Militaire supprimé avec succès',
                life: 3000
            });
            router.visit(route('militaires.index'));
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
    font-size: 0.85rem;
    padding: 0.35rem 0.75rem;
    border-radius: 0.5rem;
    font-weight: 500;
}

:deep(.p-divider) {
    margin: 2rem 0;
}

:deep(.p-divider .p-divider-content) {
    background: white;
    padding: 0 1rem;
}

:deep(.p-datatable) {
    font-size: 0.9rem;
}

:deep(.p-datatable .p-datatable-tbody > tr:hover) {
    background-color: #f0f9ff;
}

.border {
    transition: all 0.2s ease;
    border-color: #e5e7eb;
}

.border:hover {
    border-color: #7dd3fc !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transform: translateY(-2px);
}

.text-sky-500 {
    color: #0ea5e9;
}

.text-sky-600 {
    color: #0284c7;
}

.text-white {
    color: white;
}
</style>