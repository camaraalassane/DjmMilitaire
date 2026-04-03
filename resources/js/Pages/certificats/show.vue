<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white">
                    {{ certificat.nom_certificat }}
                </h2>
                <Button label="Retour à la liste" 
                        icon="pi pi-arrow-left"
                        class="p-button-sm bg-sky-400 hover:bg-sky-500 border-sky-400 text-white"
                        @click="goBack" />
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <Card>
                            <template #title>
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-file-pdf text-sky-500"></i>
                                    <span class="text-sky-600">Détails du certificat</span>
                                </div>
                            </template>
                            
                            <template #content>
                                <!-- Informations principales -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    <div class="border rounded-lg p-4">
                                        <div class="text-sm text-gray-600 mb-1">Niveau</div>
                                        <Tag :value="certificat.niveau_certificat" 
                                             :style="getNiveauStyle(certificat.niveau_certificat)" 
                                             class="text-base" />
                                    </div>
                                    
                                    <div class="border rounded-lg p-4">
                                        <div class="text-sm text-gray-600 mb-1">Grade associé</div>
                                        <Tag :value="certificat.grade_associe" 
                                             style="background: #bae6fd; color: #0369a1;" />
                                    </div>
                                </div>

                                <!-- Informations détaillées -->
                                <div class="space-y-4">
                                    <!-- Ancienneté requise -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-colors">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-clock text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Ancienneté requise</span>
                                        </div>
                                        <div>
                                            <Tag :value="ancienneteFormatee" 
                                                 :style="ancienneteRequise ? { background: '#7dd3fc', color: '#0369a1' } : { background: '#e5e7eb', color: '#6b7280' }" />
                                        </div>
                                    </div>

                                    <!-- Certificat prérequis -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-colors">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="pi pi-sitemap text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Certificat prérequis</span>
                                        </div>
                                        <div>
                                            <template v-if="certificat.certificat_precedent">
                                                <Tag :value="certificat.certificat_precedent" 
                                                     style="background: #bae6fd; color: #0369a1;" 
                                                     class="mb-2" />
                                                <div v-if="certificat.duree_certificat_precedent" class="text-sm text-gray-600 mt-1">
                                                    <i class="pi pi-calendar mr-1"></i>
                                                    Depuis au moins {{ certificat.duree_certificat_precedent }} ans
                                                </div>
                                            </template>
                                            <Tag v-else value="Aucun prérequis" 
                                                 style="background: #e5e7eb; color: #6b7280;" />
                                        </div>
                                    </div>

                                    <!-- Conditions détaillées -->
                                    <div class="border rounded-lg p-4 hover:border-sky-300 transition-colors">
                                        <div class="flex items-center gap-2 mb-4">
                                            <i class="pi pi-list-check text-sky-500"></i>
                                            <span class="font-medium text-gray-700">Conditions détaillées</span>
                                        </div>
                                        
                                        <div v-if="conditions && conditions.length > 0" class="space-y-2">
                                            <div v-for="(condition, index) in conditions" :key="index" 
                                                 class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-sky-50 transition-colors">
                                                <span class="text-gray-700">{{ formatConditionLabel(condition.key) }}</span>
                                                <Tag :value="formatConditionValue(condition.value)" 
                                                     :style="getConditionStyle(condition.value, condition.severity)" />
                                            </div>
                                        </div>
                                        
                                        <div v-else class="text-center py-4 text-gray-500">
                                            <i class="pi pi-info-circle text-2xl mb-2"></i>
                                            <p>Aucune condition spécifique enregistrée</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bouton retour -->
                                <div class="mt-6 flex justify-end">
                                    <Button label="Retour à la liste" 
                                            icon="pi pi-arrow-left"
                                            class="p-button-outlined border-sky-400 text-sky-500 hover:bg-sky-50"
                                            @click="goBack" />
                                </div>
                            </template>
                        </Card>
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
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import Button from 'primevue/button';

const props = defineProps({
    certificat: {
        type: Object,
        required: true
    }
});

// Vérifier si l'ancienneté est requise
const ancienneteRequise = computed(() => {
    return props.certificat.anciennete_requise && props.certificat.anciennete_requise > 0;
});

// Formater l'ancienneté requise
const ancienneteFormatee = computed(() => {
    return props.certificat.anciennete_requise 
        ? `${props.certificat.anciennete_requise} ans`
        : 'Aucune ancienneté requise';
});

// Style pour le niveau selon la couleur
const getNiveauStyle = (niveau) => {
    const styles = {
        'CAT1': { background: '#7dd3fc', color: '#0369a1' },
        'CAT2': { background: '#38bdf8', color: '#075985' },
        'CIA': { background: '#22d3ee', color: '#0e7490' },
        'BSP': { background: '#fdba74', color: '#c2410c' },
        'BSG': { background: '#fca5a5', color: '#b91c1c' },
        'BSC': { background: '#9ca3af', color: '#374151' },
        'CSG': { background: '#c4b5fd', color: '#5b21b6' }
    };
    return styles[niveau] || { background: '#7dd3fc', color: '#0369a1' };
};

// Style pour les conditions
const getConditionStyle = (value, severity) => {
    if (value === 'Oui') {
        return { background: '#bae6fd', color: '#0369a1' };
    }
    if (value === 'Non') {
        return { background: '#fee2e2', color: '#991b1b' };
    }
    return { background: '#e5e7eb', color: '#6b7280' };
};

// Traiter les conditions
const conditions = computed(() => {
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
    
    // Convertir l'objet en tableau avec formatage
    if (typeof conditionsData === 'object' && conditionsData !== null) {
        return Object.entries(conditionsData).map(([key, value]) => {
            let formattedValue = value;
            
            if (typeof value === 'boolean') {
                formattedValue = value ? 'Oui' : 'Non';
            } else if (typeof value === 'number') {
                formattedValue = value.toString();
            }
            
            return {
                key,
                value: formattedValue,
                severity: typeof value === 'boolean' ? (value ? 'success' : 'danger') : 'info'
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

// Formater la valeur de la condition
const formatConditionValue = (value) => {
    return value;
};

// Navigation retour
const goBack = () => {
    router.visit(route('certificats.index'));
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
    font-size: 0.875rem;
    padding: 0.35rem 0.85rem;
    border-radius: 0.5rem;
    font-weight: 500;
}

/* Styles pour les bordures */
.border {
    transition: all 0.2s ease;
    border-color: #e5e7eb;
}

.border:hover {
    border-color: #7dd3fc;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

/* Styles personnalisés */
.bg-sky-400 {
    background-color: #38bdf8;
}

.hover\:bg-sky-500:hover {
    background-color: #0ea5e9;
}

.border-sky-400 {
    border-color: #38bdf8;
}

.text-sky-500 {
    color: #0ea5e9;
}

.text-sky-600 {
    color: #0284c7;
}

.hover\:bg-sky-50:hover {
    background-color: #f0f9ff;
}

.transition-colors {
    transition-property: background-color, border-color, color, box-shadow;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>