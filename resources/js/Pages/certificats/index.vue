<template>
    <AuthenticatedLayout>
        
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-sky-600">
                Liste des certificats
            </h2>
            <div class="flex items-center gap-3">
                <Badge :value="`${certificats.total} certificats`" 
                       style="background: #0284c7; color: white;" 
                       size="large" />
                <Button label="Nouveau certificat" 
                        icon="pi pi-plus"
                        class="p-button-sm bg-sky-600 hover:bg-sky-700 border-sky-600 text-white"
                        @click="createCertificat" />
            </div>
        </div>
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <!-- Message si aucun certificat -->
                        <div v-if="certificats.data.length === 0" class="text-center py-8 text-gray-500">
                            <i class="pi pi-file-pdf text-5xl mb-3 text-gray-400"></i>
                            <p class="text-lg">Aucun certificat trouvé.</p>
                            <Button label="Créer le premier certificat" 
                                    icon="pi pi-plus"
                                    class="p-button-outlined mt-4 border-sky-600 text-sky-600 hover:bg-sky-50"
                                    @click="createCertificat" />
                        </div>

                        <!-- Tableau des certificats -->
                        <template v-else>
                            <div class="mb-4 flex flex-col md:flex-row gap-4">
                                <div class="flex-1">
                                    <span class="p-input-icon-left w-full">
                                        <InputText v-model="filters.search" 
                                                  placeholder="Rechercher un certificat..." 
                                                  class="w-full"
                                                  @input="debouncedSearch" />
                                    </span>
                                </div>
                                <div class="md:w-48">
                                    <Select v-model="filters.niveau" 
                                            :options="niveauxOptions" 
                                            optionLabel="label" 
                                            optionValue="value"
                                            placeholder="Filtrer par niveau"
                                            class="w-full"
                                            @change="loadCertificats" />
                                </div>
                                <div>
                                    <Button label="Filtrer" 
                                            icon="pi pi-filter"
                                            class="p-button-sm bg-sky-600 hover:bg-sky-700 border-sky-600 text-white"
                                            @click="loadCertificats" />
                                </div>
                            </div>

                            <DataTable :value="certificats.data" 
                                       stripedRows 
                                       responsiveLayout="scroll"
                                       :loading="loading"
                                       paginator
                                       lazy
                                       :rows="certificats.per_page"
                                       :totalRecords="certificats.total"
                                       @page="onPageChange"
                                       class="p-datatable-sm">
                                
                                <!-- Colonne Niveau avec badge sky blue -->
                                <Column field="niveau_certificat" header="Niveau" style="width: 100px">
                                    <template #body="slotProps">
                                        <Tag :value="slotProps.data.niveau_certificat" 
                                             :style="{ background: getNiveauColor(slotProps.data.niveau_certificat), color: 'white' }" />
                                    </template>
                                </Column>

                                <!-- Colonne Nom du certificat -->
                                <Column field="nom_certificat" header="Nom du certificat">
                                    <template #body="slotProps">
                                        <Button :label="slotProps.data.nom_certificat"
                                                class="p-button-link p-0 text-sky-600 hover:text-sky-800"
                                                @click="viewCertificat(slotProps.data.id)" />
                                    </template>
                                </Column>

                                <!-- Colonne Grade associé -->
                                <Column field="grade_associe" header="Grade associé">
                                    <template #body="slotProps">
                                        <Tag :value="slotProps.data.grade_associe" 
                                             :style="{ background: '#0284c7', color: 'white' }" />
                                    </template>
                                </Column>

                                <!-- Colonne Ancienneté requise -->
                                <Column field="anciennete_requise" header="Ancienneté" style="width: 120px">
                                    <template #body="slotProps">
                                        <template v-if="slotProps.data.anciennete_requise">
                                            <Tag :value="slotProps.data.anciennete_requise + ' ans'" 
                                                 :style="{ background: '#f97316', color: 'white' }" />
                                        </template>
                                        <span v-else class="text-gray-400">—</span>
                                    </template>
                                </Column>

                                <!-- Colonne Certificat prérequis -->
                                <Column field="certificat_precedent" header="Prérequis">
                                    <template #body="slotProps">
                                        <div v-if="slotProps.data.certificat_precedent">
                                            <Tag :value="slotProps.data.certificat_precedent" 
                                                 :style="{ background: '#0284c7', color: 'white' }" />
                                            <small v-if="slotProps.data.duree_certificat_precedent" 
                                                   class="text-gray-500 block mt-1">
                                                ≥ {{ slotProps.data.duree_certificat_precedent }} ans
                                            </small>
                                        </div>
                                        <span v-else class="text-gray-400">Aucun</span>
                                    </template>
                                </Column>

                                <!-- Colonne Actions -->
                                <Column header="Actions" style="width: 100px">
                                    <template #body="slotProps">
                                        <Button icon="pi pi-eye" 
                                                class="p-button-rounded p-button-text p-button-sm text-sky-600 hover:text-sky-800"
                                                @click="viewCertificat(slotProps.data.id)" />
                                    </template>
                                </Column>

                                <template #empty>
                                    <div class="text-center py-8 text-gray-500">
                                        <i class="pi pi-file-pdf text-4xl mb-2"></i>
                                        <p>Aucun certificat trouvé</p>
                                    </div>
                                </template>
                            </DataTable>

                            <!-- Simple information de pagination (sans boutons) -->
                            <div class="text-center sm:text-left text-sm text-gray-600 mt-4">
                                Affichage de {{ certificats.from }} à {{ certificats.to }} sur {{ certificats.total }} certificats
                            </div>
                        </template>
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
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Badge from 'primevue/badge';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import debounce from 'lodash/debounce';

const props = defineProps({
    certificats: {
        type: Object,
        required: true
    }
});

const toast = useToast();
const loading = ref(false);

// Options de filtres par niveau
const niveauxOptions = [
    { label: 'Tous les niveaux', value: null },
    { label: 'CAT1', value: 'CAT1' },
    { label: 'CAT2', value: 'CAT2' },
    { label: 'CIA', value: 'CIA' },
    { label: 'BSP', value: 'BSP' },
    { label: 'BSG', value: 'BSG' },
    { label: 'BSC', value: 'BSC' },
    { label: 'CSG', value: 'CSG' }
];

// État des filtres
const filters = reactive({
    search: '',
    niveau: null
});

// Déterminer la couleur du badge selon le niveau
const getNiveauColor = (niveau) => {
    const colors = {
        'CAT1': '#0284c7',   // sky-600
        'CAT2': '#0ea5e9',   // sky-500
        'CIA': '#06b6d4',    // cyan-500
        'BSP': '#f97316',    // orange-500
        'BSG': '#ef4444',    // red-500
        'BSC': '#6b7280',    // gray-500
        'CSG': '#8b5cf6'     // violet-500
    };
    return colors[niveau] || '#0284c7';
};

// Recherche avec debounce
const debouncedSearch = debounce(() => {
    loadCertificats();
}, 500);

// Charger les certificats avec les filtres (par défaut page 1)
const loadCertificats = (page = 1) => {
    loading.value = true;
    
    router.get(route('certificats.index'), {
        page,
        search: filters.search,
        niveau: filters.niveau
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
        },
        onError: (errors) => {
            loading.value = false;
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Impossible de charger les certificats',
                life: 3000
            });
        }
    });
};

// Changement de page via la pagination DataTable (lazy)
const onPageChange = (event) => {
    loadCertificats(event.page + 1);
};

// Voir les détails d'un certificat
const viewCertificat = (id) => {
    router.visit(route('certificats.show', id));
};

// Créer un nouveau certificat
const createCertificat = () => {
    toast.add({
        severity: 'info',
        summary: 'Information',
        detail: 'Fonctionnalité de création à venir',
        life: 3000
    });
};
</script>

<style scoped>
:deep(.p-datatable) {
    font-size: 0.95rem;
}

:deep(.p-datatable .p-datatable-tbody > tr:hover) {
    background-color: #f8f9fa;
} 

:deep(.p-paginator) {
    padding: 0.5rem 0;
}

:deep(.p-input-icon-left) {
    width: 100%;
}

:deep(.p-inputtext) {
    width: 100%;
    padding-left: 2.5rem;
}

:deep(.p-tag) {
    font-size: 0.85rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
}

:deep(.p-button.p-button-sm) {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}

/* Style pour le badge de total */
:deep(.p-badge.p-badge-lg) {
    font-size: 1rem;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
}

/* Styles personnalisés */
.bg-sky-600 {
    background-color: #0284c7;
}

.hover\:bg-sky-700:hover {
    background-color: #0369a1;
}

.border-sky-600 {
    border-color: #0284c7;
}

.text-sky-600 {
    color: #0284c7;
}

.hover\:text-sky-800:hover {
    color: #075985;
}

.hover\:bg-sky-50:hover {
    background-color: #f0f9ff;
}

/* Style pour les liens */
:deep(.p-button-link) {
    text-decoration: none;
    font-weight: 500;
}

:deep(.p-button-link:hover) {
    text-decoration: underline;
}

/* Style pour les badges */
:deep(.p-tag) {
    font-weight: 500;
}
</style>