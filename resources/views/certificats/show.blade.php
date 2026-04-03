<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Détails du certificat
                </h2>
                <Button label="Retour" 
                        icon="pi pi-arrow-left"
                        class="p-button-sm p-button-outlined"
                        @click="goBack" />
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Informations principales -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="border rounded-lg p-4">
                                <div class="text-sm text-gray-600 mb-1">Niveau</div>
                                <Tag :value="certificat.niveau_certificat" severity="primary" />
                            </div>
                            
                            <div class="border rounded-lg p-4">
                                <div class="text-sm text-gray-600 mb-1">Nom complet</div>
                                <div class="font-medium">{{ certificat.nom_certificat }}</div>
                            </div>
                            
                            <div class="border rounded-lg p-4">
                                <div class="text-sm text-gray-600 mb-1">Grade associé</div>
                                <div class="font-medium">{{ certificat.grade_associe }}</div>
                            </div>
                            
                            <div class="border rounded-lg p-4">
                                <div class="text-sm text-gray-600 mb-1">Ancienneté requise</div>
                                <div>{{ certificat.anciennete_requise ? certificat.anciennete_requise + ' ans' : 'Aucune' }}</div>
                            </div>
                            
                            <div class="border rounded-lg p-4 md:col-span-2">
                                <div class="text-sm text-gray-600 mb-1">Certificat prérequis</div>
                                <div>
                                    <span v-if="certificat.certificat_precedent">
                                        {{ certificat.certificat_precedent }}
                                        <span v-if="certificat.duree_certificat_precedent" class="text-sm text-gray-500">
                                            (depuis au moins {{ certificat.duree_certificat_precedent }} ans)
                                        </span>
                                    </span>
                                    <span v-else>Aucun</span>
                                </div>
                            </div>
                        </div>

                        <Divider />

                        <!-- Conditions détaillées -->
                        <div class="mt-4">
                            <h6 class="font-medium mb-3">Conditions détaillées</h6>
                            <div v-if="conditionsList.length > 0" class="space-y-2">
                                <div v-for="(condition, index) in conditionsList" 
                                     :key="index"
                                     class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span>{{ formatConditionLabel(condition.key) }}</span>
                                    <Tag :value="condition.value" 
                                         :severity="condition.severity" />
                                </div>
                            </div>
                            <div v-else class="text-center py-4 text-gray-500">
                                <i class="pi pi-info-circle text-2xl mb-2"></i>
                                <p>Aucune condition spécifique enregistrée</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Divider from 'primevue/divider';

const props = defineProps({
    certificat: {
        type: Object,
        required: true
    }
});

// Traitement des conditions
const conditionsList = computed(() => {
    if (!props.certificat.conditions) return [];
    
    let conditionsData = props.certificat.conditions;
    
    // Si c'est une chaîne JSON, la décoder
    if (typeof conditionsData === 'string') {
        try {
            conditionsData = JSON.parse(conditionsData);
        } catch (e) {
            console.error('Erreur de parsing des conditions:', e);
            return [];
        }
    }
    
    // Convertir l'objet en tableau
    if (typeof conditionsData === 'object' && conditionsData !== null) {
        return Object.entries(conditionsData).map(([key, value]) => {
            let formattedValue = value;
            let severity = 'info';
            
            if (typeof value === 'boolean') {
                formattedValue = value ? 'Oui' : 'Non';
                severity = value ? 'success' : 'danger';
            } else if (typeof value === 'number') {
                formattedValue = value.toString();
            }
            
            return {
                key: key,
                value: formattedValue,
                severity: severity
            };
        });
    }
    
    return [];
});

// Formater le label de la condition
const formatConditionLabel = (key) => {
    return key
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

// Navigation retour
const goBack = () => {
    router.visit(route('certificats.index'));
};
</script>

<style scoped>
:deep(.p-tag) {
    font-size: 0.85rem;
    padding: 0.25rem 0.5rem;
}
</style>