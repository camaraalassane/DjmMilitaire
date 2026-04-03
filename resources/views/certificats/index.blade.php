<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Liste des certificats
                </h2>
                <Button label="Nouveau certificat" 
                        icon="pi pi-plus"
                        class="p-button-sm"
                        @click="createCertificat" />
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Filtres -->
                        <div class="mb-6 flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <span class="p-input-icon-left w-full">
                                    <i class="pi pi-search" />
                                    <InputText v-model="search" 
                                              placeholder="Rechercher un certificat..." 
                                              class="w-full"
                                              @input="onSearchInput" />
                                </span>
                            </div>
                        </div>

                        <!-- Tableau des certificats -->
                        <DataTable :value="certificatsData" 
                                   stripedRows 
                                   responsiveLayout="scroll"
                                   :loading="loading"
                                   paginator
                                   :rows="perPage"
                                   :totalRecords="totalRecords"
                                   :first="first"
                                   @page="onPageChange"
                                   class="p-datatable-sm">
                            
                            <Column field="niveau_certificat" header="Niveau" style="width: 100px">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.niveau_certificat" 
                                         :severity="getNiveauSeverity(slotProps.data.niveau_certificat)" />
                                </template>
                            </Column>
                            
                            <Column field="nom_certificat" header="Nom du certificat">
                                <template #body="slotProps">
                                    <div class="font-medium">{{ slotProps.data.nom_certificat }}</div>
                                    <small class="text-gray-500">{{ slotProps.data.grade_associe }}</small>
                                </template>
                            </Column>
                            
                            <Column field="anciennete_requise" header="Ancienneté" style="width: 120px">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.anciennete_requise ? slotProps.data.anciennete_requise + ' ans' : '-'" 
                                         :severity="slotProps.data.anciennete_requise ? 'info' : 'secondary'" />
                                </template>
                            </Column>
                            
                            <Column header="Actions" style="width: 100px">
                                <template #body="slotProps">
                                    <Button icon="pi pi-eye" 
                                            class="p-button-rounded p-button-text p-button-sm"
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
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    certificats: {
        type: Object,
        required: true
    }
});

const toast = useToast();
const loading = ref(false);
const search = ref('');
const perPage = ref(20);
const currentPage = ref(1);
const first = ref(0);

// Utiliser computed pour les données
const certificatsData = computed(() => {
    return props.certificats?.data || [];
});

const totalRecords = computed(() => {
    return props.certificats?.total || 0;
});

// Couleurs par niveau
const getNiveauSeverity = (niveau) => {
    const severities = {
        'CAT1': 'info',
        'CAT2': 'primary',
        'CIA': 'success',
        'BSP': 'warning',
        'BSG': 'danger',
        'BSC': 'secondary',
        'CSG': 'contrast'
    };
    return severities[niveau] || 'info';
};

// Debounce pour la recherche
let searchTimeout;
const onSearchInput = (event) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadCertificats();
    }, 500);
};

// Charger les certificats
const loadCertificats = () => {
    loading.value = true;
    
    router.get(route('certificats.index'), {
        page: currentPage.value,
        search: search.value
    }, {
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
                detail: 'Impossible de charger les certificats',
                life: 3000
            });
        }
    });
};

// Changement de page
const onPageChange = (event) => {
    currentPage.value = event.page + 1;
    first.value = event.first;
    loadCertificats();
};

// Actions
const viewCertificat = (id) => {
    router.visit(route('certificats.show', id));
};

const createCertificat = () => {
    toast.add({
        severity: 'info',
        summary: 'Information',
        detail: 'Fonctionnalité à venir',
        life: 3000
    });
};

// Watch pour les changements de props
watch(() => props.certificats, () => {
    // Mettre à jour currentPage si nécessaire
    if (props.certificats?.current_page) {
        currentPage.value = props.certificats.current_page;
        first.value = (props.certificats.current_page - 1) * perPage.value;
    }
}, { immediate: true });
</script>

<style scoped>
:deep(.p-datatable) {
    font-size: 0.95rem;
}

:deep(.p-tag) {
    font-size: 0.85rem;
    padding: 0.25rem 0.5rem;
}
</style>