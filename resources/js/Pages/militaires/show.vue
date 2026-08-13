<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <h2 class="font-semibold text-xl text-white">
                    {{ militaire.nom }} {{ militaire.prenom }}
                </h2>
                <div class="flex flex-wrap gap-2">
                    <Button label="Modifier" icon="pi pi-pencil" class="p-button-sm"
                        style="background-color: #f59e0b; border-color: #f59e0b; color: white;"
                        @click="editMilitaire" />
                    <Button label="Supprimer" icon="pi pi-trash" class="p-button-sm"
                        style="background-color: #ef4444; border-color: #ef4444; color: white;"
                        @click="confirmDelete" />
                    <Button label="Retour" icon="pi pi-arrow-left" class="p-button-sm"
                        style="background-color: #6b7280; border-color: #6b7280; color: white;" @click="goBack" />
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
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                    <!-- Matricule -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-id-card text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Matricule</span>
                                        </div>
                                        <Tag :value="militaire.matricule" style="background: #bae6fd; color: #0369a1;"
                                            class="text-base" />
                                    </div>

                                    <!-- Date de naissance -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-calendar text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Date de naissance</span>
                                        </div>
                                        <div class="text-gray-800">{{ militaire.date_naissance ?? '-' }}</div>
                                        <small class="text-gray-500">{{ militaire.age }} ans</small>
                                    </div>

                                    <!-- Grade actuel -->
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

                                    <!-- Statut -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-tag text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Statut</span>
                                        </div>
                                        <Tag :value="militaire.statut" :style="getStatutStyle(militaire.statut)" />
                                    </div>

                                    <!-- Téléphone -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-phone text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Téléphone</span>
                                        </div>
                                        <div class="text-gray-800">{{ militaire.telephone || '-' }}</div>
                                    </div>

                                    <!-- Sexe -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-user text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Sexe</span>
                                        </div>
                                        <Tag :value="militaire.sexe || 'Non renseigné'"
                                            :style="militaire.sexe === 'M' ? { background: '#7dd3fc', color: '#0369a1' } : militaire.sexe === 'F' ? { background: '#f9a8d4', color: '#9d174d' } : { background: '#e5e7eb', color: '#6b7280' }" />
                                    </div>

                                    <!-- Groupe sanguin -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-heart text-red-500"></i>
                                            <span class="font-medium text-gray-700">Groupe sanguin</span>
                                        </div>
                                        <Tag :value="militaire.groupe_sanguin || 'Non renseigné'"
                                            style="background: #bae6fd; color: #0369a1;" />
                                    </div>

                                    <!-- Permis de conduire -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-car text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Permis de conduire</span>
                                        </div>
                                        <Tag :value="militaire.a_permis_conduire ? 'Oui' : 'Non'"
                                            :style="militaire.a_permis_conduire ? { background: '#7dd3fc', color: '#0369a1' } : { background: '#e5e7eb', color: '#6b7280' }" />
                                    </div>

                                    <!-- Date d'entrée en service -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-calendar-plus text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Date d'entrée en service</span>
                                        </div>
                                        <div class="text-gray-800">{{ militaire.date_entree_service ?? '-' }}</div>
                                        <small class="text-gray-500">{{ formatAnciennete(militaire.anciennete) }} de
                                            service</small>
                                    </div>

                                    <!-- Ancienneté dans le grade -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-chart-line text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Ancienneté dans le grade</span>
                                        </div>
                                        <Tag :value="formatAnciennete(militaire.anciennete_grade)"
                                            style="background: #bae6fd; color: #0369a1;" />
                                    </div>

                                    <!-- Date de retraite -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-calendar-minus text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Date de retraite</span>
                                        </div>
                                        <div v-if="militaire.date_retraite">
                                            <span class="text-gray-800">{{ militaire.date_retraite }}</span>
                                            <Tag v-if="militaire.est_eligible_retraite" value="Bientôt"
                                                style="background: #f97316; color: white;" class="ml-2" />
                                        </div>
                                        <span v-else class="text-gray-400">Non calculée</span>
                                    </div>

                                    <!-- Spécialité -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-book text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Spécialité</span>
                                        </div>
                                        <div class="text-gray-800">{{ militaire.specialite || '-' }}</div>
                                    </div>

                                    <!-- Position actuelle -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-map-marker text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Position actuelle</span>
                                        </div>
                                        <div class="text-gray-800">{{ militaire.position_actuelle || '-' }}</div>
                                    </div>

                                    <!-- Fonction passée -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-history text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Fonction passée</span>
                                        </div>
                                        <div class="text-gray-800">{{ militaire.fonction_passee || '-' }}</div>
                                    </div>

                                    <!-- Fonction actuelle -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-briefcase text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Fonction actuelle</span>
                                        </div>
                                        <div class="text-gray-800">{{ militaire.fonction_actuelle || '-' }}</div>
                                    </div>

                                    <!-- Problème judiciaire -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-gavel text-amber-500"></i>
                                            <span class="font-medium text-gray-700">Problème judiciaire</span>
                                        </div>
                                        <Tag :value="militaire.a_fait_justice ? 'Oui' : 'Non'"
                                            :style="militaire.a_fait_justice ? { background: '#fecaca', color: '#991b1b' } : { background: '#7dd3fc', color: '#0369a1' }" />
                                    </div>

                                    <!-- Problème disciplinaire -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-exclamation-triangle text-amber-500"></i>
                                            <span class="font-medium text-gray-700">Problème disciplinaire</span>
                                        </div>
                                        <Tag :value="militaire.a_fait_discipline ? 'Oui' : 'Non'"
                                            :style="militaire.a_fait_discipline ? { background: '#fecaca', color: '#991b1b' } : { background: '#7dd3fc', color: '#0369a1' }" />
                                    </div>

                                    <!-- Personne à contacter -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-users text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Personne à contacter</span>
                                        </div>
                                        <div class="text-gray-800">{{ militaire.personne_a_contacter || '-' }}</div>
                                    </div>

                                    <!-- Téléphone de la personne à contacter -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-all">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-phone text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Tél. de la personne</span>
                                        </div>
                                        <div class="text-gray-800">{{ militaire.telephone_personne_contacter || '-' }}
                                        </div>
                                    </div>

                                </div>

                                <!-- Certificats obtenus -->
                                <Divider>
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-verified text-sky-500"></i>
                                        <span class="font-medium text-gray-700">Certificats obtenus</span>
                                        <span class="text-sm text-gray-400">({{ certificats.length }})</span>
                                    </div>
                                </Divider>

                                <div v-if="certificats.length === 0" class="text-center py-4 text-gray-500">
                                    <i class="pi pi-info-circle text-2xl mb-2"></i>
                                    <p>Aucun certificat obtenu</p>
                                </div>

                                <DataTable v-else :value="certificats" stripedRows responsiveLayout="scroll"
                                    class="p-datatable-sm mb-6">
                                    <Column field="nom_certificat" header="Certificat" style="min-width: 150px;">
                                        <template #body="slotProps">
                                            <div class="font-medium">{{ slotProps.data.nom_certificat }}</div>
                                        </template>
                                    </Column>
                                    <Column field="niveau_certificat" header="Niveau" style="min-width: 100px;">
                                        <template #body="slotProps">
                                            <Tag :value="slotProps.data.niveau_certificat || '-'"
                                                style="background: #bae6fd; color: #0369a1;" />
                                        </template>
                                    </Column>
                                    <Column field="date_obtention" header="Date d'obtention" style="min-width: 120px;">
                                        <template #body="slotProps">
                                            {{ slotProps.data.date_obtention ?? '-' }}
                                        </template>
                                    </Column>
                                    <Column header="Document" style="min-width: 140px;">
                                        <template #body="slotProps">
                                            <div v-if="slotProps.data.document_id">
                                                <a :href="route('certificats.document.download', slotProps.data.document_id)"
                                                    target="_blank"
                                                    class="text-sky-600 hover:text-sky-800 flex items-center gap-2 p-2 bg-sky-50 rounded-lg hover:bg-sky-100 transition-all">
                                                    <i class="pi pi-file-pdf text-lg"></i>
                                                    <span class="text-sm truncate max-w-[100px]">{{
                                                        slotProps.data.document_nom || 'Document' }}</span>
                                                    <i class="pi pi-download ml-auto text-xs"></i>
                                                </a>
                                            </div>
                                            <span v-else class="text-gray-400 text-sm">Aucun document</span>
                                        </template>
                                    </Column>
                                </DataTable>

                                <!-- Alertes -->
                                <Divider>
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-bell text-amber-500"></i>
                                        <span class="font-medium text-gray-700">Alertes associées</span>
                                        <span class="text-sm text-gray-400">({{ alertes.length }})</span>
                                    </div>
                                </Divider>

                                <div v-if="alertes.length === 0" class="text-center py-4 text-gray-500">
                                    <i class="pi pi-info-circle text-2xl mb-2"></i>
                                    <p>Aucune alerte pour ce militaire</p>
                                </div>

                                <DataTable v-else :value="alertes" stripedRows responsiveLayout="scroll"
                                    class="p-datatable-sm">
                                    <Column field="type_alerte" header="Type" style="min-width: 100px;">
                                        <template #body="slotProps">
                                            <Tag :value="getTypeLabel(slotProps.data.type_alerte)"
                                                :style="getAlerteStyle(slotProps.data.type_alerte)" />
                                        </template>
                                    </Column>
                                    <Column field="message" header="Message" style="min-width: 200px;">
                                        <template #body="slotProps">
                                            <span class="text-sm">{{ slotProps.data.message }}</span>
                                        </template>
                                    </Column>
                                    <Column field="date_echeance" header="Échéance" style="min-width: 120px;">
                                        <template #body="slotProps">
                                            {{ slotProps.data.date_echeance ?? '-' }}
                                        </template>
                                    </Column>
                                    <Column field="est_vue" header="Statut" style="min-width: 100px;">
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

        <!-- Dialog suppression -->
        <Dialog v-model:visible="deleteDialogVisible" header="Confirmation" :modal="true"
            :style="{ width: '90%', maxWidth: '400px' }" class="p-fluid">
            <div class="flex items-center gap-3 mb-4">
                <i class="pi pi-exclamation-triangle text-3xl text-amber-500"></i>
                <p class="text-gray-700 text-sm">Êtes-vous sûr de vouloir supprimer le militaire <strong>{{
                    militaire.nom }} {{
                            militaire.prenom }}</strong> ?</p>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button label="Non" icon="pi pi-times" class="p-button-text text-gray-500 hover:text-gray-700"
                        @click="deleteDialogVisible = false" />
                    <Button label="Oui" icon="pi pi-check"
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
    militaire: { type: Object, required: true },
    certificats: { type: Array, default: () => [] },
    alertes: { type: Array, default: () => [] },
    contratActif: { type: Object, default: null }
});

const toast = useToast();
const deleteDialogVisible = ref(false);

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

const getAlerteStyle = (type) => {
    const styles = {
        'promotion': { background: '#bae6fd', color: '#0369a1' },
        'formation': { background: '#fed7aa', color: '#c2410c' },
        'retraite': { background: '#fecaca', color: '#991b1b' },
        'contrat': { background: '#dbeafe', color: '#1e40af' }
    };
    return styles[type] || { background: '#e5e7eb', color: '#374151' };
};

const getTypeLabel = (type) => {
    const labels = {
        'promotion': 'Promotion',
        'formation': 'Formation',
        'retraite': 'Retraite',
        'contrat': 'Contrat'
    };
    return labels[type] || type;
};

const formatAnciennete = (annees) => {
    if (!annees && annees !== 0) return '0 ans';
    return `${Math.floor(annees)} ans`;
};

const editMilitaire = () => { router.visit(route('militaires.edit', props.militaire.id)); };
const goBack = () => { router.visit(route('militaires.index')); };
const confirmDelete = () => { deleteDialogVisible.value = true; };

const deleteMilitaire = () => {
    router.delete(route('militaires.destroy', props.militaire.id), {
        onSuccess: () => {
            deleteDialogVisible.value = false;
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Militaire supprimé avec succès', life: 3000 });
            router.visit(route('militaires.index'));
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de supprimer le militaire', life: 3000 });
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

:deep(.p-datatable .p-datatable-thead > tr > th) {
    background: #f8fafc;
    color: #1e293b;
    font-weight: 600;
    padding: 0.75rem 0.75rem;
    border-bottom: 2px solid #e2e8f0;
}

:deep(.p-datatable .p-datatable-tbody > tr > td) {
    padding: 0.6rem 0.75rem;
    vertical-align: middle;
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

a {
    text-decoration: none;
}

a:hover {
    text-decoration: none;
}

.truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.max-w-100px {
    max-width: 100px;
}
</style>
