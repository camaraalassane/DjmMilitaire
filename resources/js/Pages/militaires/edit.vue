<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white">
                Modifier le militaire : {{ form.nom }} {{ form.prenom }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submitForm">
                            <Card>
                                <template #title>
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-user-edit text-sky-500"></i>
                                        <span class="text-sky-600">Modifier le militaire</span>
                                    </div>
                                </template>
                                
                                <template #content>
                                    <!-- Informations générales -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <!-- Matricule -->
                                        <div class="field">
                                            <label for="matricule" class="block text-sm font-medium text-gray-700 mb-2">
                                                Matricule <span class="text-red-500">*</span>
                                            </label>
                                            <InputText id="matricule" 
                                                      v-model="form.matricule" 
                                                      class="w-full"
                                                      :class="{ 'p-invalid': errors.matricule }" />
                                            <small v-if="errors.matricule" class="text-red-500">{{ errors.matricule }}</small>
                                        </div>

                                        <!-- Grade actuel -->
                                        <div class="field">
                                            <label for="grade_actuel" class="block text-sm font-medium text-gray-700 mb-2">
                                                Grade actuel <span class="text-red-500">*</span>
                                            </label>
                                            <Select v-model="form.grade_actuel" 
                                                    :options="grades" 
                                                    optionLabel="nom_grade" 
                                                    optionValue="nom_grade"
                                                    placeholder="Sélectionner un grade"
                                                    class="w-full"
                                                    :class="{ 'p-invalid': errors.grade_actuel }" />
                                            <small v-if="errors.grade_actuel" class="text-red-500">{{ errors.grade_actuel }}</small>
                                        </div>
                                    </div>

                                    <!-- Nom et Prénom -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <div class="field">
                                            <label for="nom" class="block text-sm font-medium text-gray-700 mb-2">
                                                Nom <span class="text-red-500">*</span>
                                            </label>
                                            <InputText id="nom" 
                                                      v-model="form.nom" 
                                                      class="w-full"
                                                      :class="{ 'p-invalid': errors.nom }" />
                                            <small v-if="errors.nom" class="text-red-500">{{ errors.nom }}</small>
                                        </div>

                                        <div class="field">
                                            <label for="prenom" class="block text-sm font-medium text-gray-700 mb-2">
                                                Prénom <span class="text-red-500">*</span>
                                            </label>
                                            <InputText id="prenom" 
                                                      v-model="form.prenom" 
                                                      class="w-full"
                                                      :class="{ 'p-invalid': errors.prenom }" />
                                            <small v-if="errors.prenom" class="text-red-500">{{ errors.prenom }}</small>
                                        </div>
                                    </div>

                                    <!-- Dates -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <div class="field">
                                            <label for="date_naissance" class="block text-sm font-medium text-gray-700 mb-2">
                                                Date de naissance <span class="text-red-500">*</span>
                                            </label>
                                            <DatePicker id="date_naissance" 
                                                        v-model="form.date_naissance" 
                                                        dateFormat="dd/mm/yy"
                                                        showIcon
                                                        class="w-full"
                                                        :class="{ 'p-invalid': errors.date_naissance }" />
                                            <small v-if="errors.date_naissance" class="text-red-500">{{ errors.date_naissance }}</small>
                                        </div>

                                        <div class="field">
                                            <label for="date_entree_service" class="block text-sm font-medium text-gray-700 mb-2">
                                                Date d'entrée en service <span class="text-red-500">*</span>
                                            </label>
                                            <DatePicker id="date_entree_service" 
                                                        v-model="form.date_entree_service" 
                                                        dateFormat="dd/mm/yy"
                                                        showIcon
                                                        class="w-full"
                                                        :class="{ 'p-invalid': errors.date_entree_service }" />
                                            <small v-if="errors.date_entree_service" class="text-red-500">{{ errors.date_entree_service }}</small>
                                        </div>
                                    </div>

                                    <!-- Autres informations -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <div class="field">
                                            <label for="date_derniere_promotion" class="block text-sm font-medium text-gray-700 mb-2">
                                                Date dernière promotion
                                            </label>
                                            <DatePicker id="date_derniere_promotion" 
                                                        v-model="form.date_derniere_promotion" 
                                                        dateFormat="dd/mm/yy"
                                                        showIcon
                                                        class="w-full" />
                                        </div>

                                        <div class="field">
                                            <label for="specialite" class="block text-sm font-medium text-gray-700 mb-2">
                                                Spécialité
                                            </label>
                                            <InputText id="specialite" 
                                                      v-model="form.specialite" 
                                                      class="w-full" />
                                        </div>
                                    </div>

                                    <!-- NOUVEAUX CHAMPS : Position, Fonction passée, Fonction actuelle -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                        <div class="field">
                                            <label for="position_actuelle" class="block text-sm font-medium text-gray-700 mb-2">
                                                <i class="pi pi-map-marker text-sky-500 mr-1"></i> Position actuelle
                                            </label>
                                            <InputText id="position_actuelle" 
                                                      v-model="form.position_actuelle" 
                                                      class="w-full" />
                                        </div>

                                        <div class="field">
                                            <label for="fonction_passee" class="block text-sm font-medium text-gray-700 mb-2">
                                                <i class="pi pi-history text-sky-500 mr-1"></i> Fonction passée
                                            </label>
                                            <InputText id="fonction_passee" 
                                                      v-model="form.fonction_passee" 
                                                      class="w-full" />
                                        </div>

                                        <div class="field">
                                            <label for="fonction_actuelle" class="block text-sm font-medium text-gray-700 mb-2">
                                                <i class="pi pi-briefcase text-sky-500 mr-1"></i> Fonction actuelle
                                            </label>
                                            <InputText id="fonction_actuelle" 
                                                      v-model="form.fonction_actuelle" 
                                                      class="w-full" />
                                        </div>
                                    </div>

                                    <!-- Statut et Permis -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <div class="field">
                                            <label for="statut" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                                            <Select v-model="form.statut" 
                                                    :options="statutOptions" 
                                                    optionLabel="label" 
                                                    optionValue="value"
                                                    placeholder="Sélectionner un statut"
                                                    class="w-full"
                                                    :class="{ 'p-invalid': errors.statut }" />
                                            <small v-if="errors.statut" class="text-red-500">{{ errors.statut }}</small>
                                        </div>

                                        <div class="flex items-center mt-6">
                                            <Checkbox id="a_permis_conduire" 
                                                     v-model="form.a_permis_conduire" 
                                                     :binary="true" />
                                            <label for="a_permis_conduire" class="ml-2 text-sm font-medium text-gray-700">
                                                Permis de conduire obtenu
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Certificats et formations -->
                                    <Card v-if="showFormationsSection" class="mt-6">
                                        <template #title>
                                            <div class="flex items-center gap-2">
                                                <i class="pi pi-book text-sky-500"></i>
                                                <span class="text-sky-600">Certificats et formations obtenus</span>
                                            </div>
                                        </template>
                                        
                                        <template #content>
                                            <div v-if="filteredCertificats.length === 0" class="text-center py-4 text-gray-500">
                                                <i class="pi pi-info-circle text-2xl mb-2"></i>
                                                <p>Aucune formation disponible pour ce grade.</p>
                                            </div>

                                            <div v-else>
                                                <div class="mb-4">
                                                    <h4 class="text-sm font-medium text-gray-700 mb-2">
                                                        Formations disponibles pour ce grade :
                                                    </h4>
                                                </div>
                                                
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div v-for="certificat in filteredCertificats" 
                                                         :key="certificat.id" 
                                                         class="border rounded-lg p-3 hover:border-sky-300 transition-all">
                                                        <div class="flex items-start justify-between">
                                                            <div class="flex items-start gap-2">
                                                                <Checkbox :id="'certif_' + certificat.id"
                                                                          v-model="form.certificats[certificat.id].obtenu"
                                                                          :binary="true"
                                                                          @change="onCertificatChange(certificat.id)" />
                                                                <label :for="'certif_' + certificat.id" class="font-medium text-gray-700">
                                                                    {{ certificat.nom_certificat }} 
                                                                    <Tag :value="certificat.niveau_certificat" 
                                                                         :style="getNiveauStyle(certificat.niveau_certificat)"
                                                                         class="ml-2" />
                                                                </label>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mt-2 ml-6">
                                                            <label :for="'date_certif_' + certificat.id" class="block text-xs text-gray-600 mb-1">
                                                                Date d'obtention <span class="text-red-500">*</span>
                                                            </label>
                                                            <DatePicker :id="'date_certif_' + certificat.id"
                                                                        v-model="form.certificats[certificat.id].date_obtention"
                                                                        dateFormat="dd/mm/yy"
                                                                        showIcon
                                                                        class="w-full"
                                                                        :disabled="!form.certificats[certificat.id].obtenu"
                                                                        :class="{ 'p-invalid': dateErrors[certificat.id] && form.certificats[certificat.id].obtenu && !form.certificats[certificat.id].date_obtention }" />
                                                            <small v-if="dateErrors[certificat.id] && form.certificats[certificat.id].obtenu && !form.certificats[certificat.id].date_obtention" 
                                                                   class="text-red-500 text-xs">
                                                                La date d'obtention est obligatoire
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </Card>

                                    <div v-else class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="flex items-center gap-2">
                                            <i class="pi pi-info-circle text-yellow-600"></i>
                                            <p class="text-sm text-yellow-700">Veuillez sélectionner un grade pour voir les formations disponibles.</p>
                                        </div>
                                    </div>

                                    <!-- Boutons d'action -->
                                    <div class="flex justify-end gap-2 mt-6">
                                        <Button label="Annuler" 
                                                icon="pi pi-times"
                                                class="p-button-outlined border-gray-300 text-gray-700 hover:bg-gray-100"
                                                @click="cancel" />
                                        <Button label="Mettre à jour" 
                                                icon="pi pi-save"
                                                type="submit"
                                                class="bg-sky-400 hover:bg-sky-500 border-sky-400 text-white"
                                                :loading="saving" />
                                    </div>
                                </template>
                            </Card>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    militaire: {
        type: Object,
        required: true
    },
    grades: {
        type: Array,
        required: true
    },
    certificats: {
        type: Array,
        required: true
    },
    certificats_du_militaire: {
        type: Object,
        default: () => ({})
    }
});

const toast = useToast();
const saving = ref(false);
const errors = ref({});
const dateErrors = ref({});
const forceRerender = ref(0);

const statutOptions = [
    { label: 'Actif', value: 'actif' },
    { label: 'Retraité', value: 'retraité' },
    { label: 'Déserteur', value: 'déserteur' },
    { label: 'Décédé', value: 'décédé' },
    { label: 'Formation', value: 'formation' },
    { label: 'Stage', value: 'stage' }
];

const formationsOfficiers = [
    'APLI',
    'CFCU', 
    'CEM',
    'Certificat État-major',
    'Certificat d\'état-major',
    'École d\'État-Major',
    'ESM',
    'Cours supérieur d\'état-major',
    'École de guerre',
    'Brevet Supérieur de Second Degré',
    'École de guerre / Brevet Supérieur de Second Degré',
    'Cour d\'Application',
    'Cour des Futurs Commandants d\'Unité',
    'Cour d\'état-major'
];

const officierTypes = ['officier général', 'officier supérieur', 'officier subalterne'];

// FORMULAIRE - AJOUT DES TROIS CHAMPS
const form = reactive({
    id: props.militaire.id,
    matricule: props.militaire.matricule || '',
    grade_actuel: props.militaire.grade_actuel || null,
    nom: props.militaire.nom || '',
    prenom: props.militaire.prenom || '',
    date_naissance: props.militaire.date_naissance ? new Date(props.militaire.date_naissance) : null,
    date_entree_service: props.militaire.date_entree_service ? new Date(props.militaire.date_entree_service) : null,
    date_derniere_promotion: props.militaire.date_derniere_promotion ? new Date(props.militaire.date_derniere_promotion) : null,
    specialite: props.militaire.specialite || '',
    // NOUVEAUX CHAMPS
    position_actuelle: props.militaire.position_actuelle || '',
    fonction_passee: props.militaire.fonction_passee || '',
    fonction_actuelle: props.militaire.fonction_actuelle || '',
    statut: props.militaire.statut || 'actif',
    a_permis_conduire: props.militaire.a_permis_conduire || false,
    a_fait_justice: props.militaire.a_fait_justice || false,
    a_fait_discipline: props.militaire.a_fait_discipline || false,
    certificats: {}
});

// Initialisation des certificats
props.certificats.forEach(certificat => {
    const certifExistant = props.certificats_du_militaire[certificat.id];
    form.certificats[certificat.id] = {
        obtenu: !!certifExistant,
        date_obtention: certifExistant?.date_obtention ? new Date(certifExistant.date_obtention) : null
    };
});

// Fonctions de filtrage et watchers (inchangés)
const estFormationOfficier = (certificat) => {
    const nomCertificat = certificat.nom_certificat;
    const niveauCertificat = certificat.niveau_certificat;
    if (formationsOfficiers.includes(nomCertificat) || formationsOfficiers.includes(niveauCertificat)) return true;
    for (const formation of formationsOfficiers) {
        if (nomCertificat && (nomCertificat.includes(formation) || formation.includes(nomCertificat))) return true;
        if (niveauCertificat && (niveauCertificat.includes(formation) || formation.includes(niveauCertificat))) return true;
    }
    return false;
};

const showFormationsSection = computed(() => form.grade_actuel !== null && form.grade_actuel !== '');

const filteredCertificats = computed(() => {
    if (!form.grade_actuel) return [];
    const gradeInfo = props.grades.find(g => g.nom_grade === form.grade_actuel);
    if (!gradeInfo) return [];
    if (officierTypes.includes(gradeInfo.type_grade)) return props.certificats;
    return props.certificats.filter(cert => !estFormationOfficier(cert));
});

watch(() => form.grade_actuel, (newGrade, oldGrade) => {
    if (newGrade && newGrade !== oldGrade) {
        const oldGradeInfo = props.grades.find(g => g.nom_grade === oldGrade);
        const newGradeInfo = props.grades.find(g => g.nom_grade === newGrade);
        if (oldGradeInfo && newGradeInfo) {
            const wasOfficier = officierTypes.includes(oldGradeInfo.type_grade);
            const isNowOfficier = officierTypes.includes(newGradeInfo.type_grade);
            if (wasOfficier && !isNowOfficier) {
                Object.keys(form.certificats).forEach(certifId => {
                    const cert = props.certificats.find(c => c.id == certifId);
                    if (cert && estFormationOfficier(cert)) {
                        if (form.certificats[certifId].obtenu) {
                            form.certificats[certifId].obtenu = false;
                            form.certificats[certifId].date_obtention = null;
                            dateErrors.value[certifId] = false;
                        }
                    }
                });
            }
        }
    }
}, { immediate: true });

watch(() => form.certificats, (newVal) => {
    Object.keys(newVal).forEach(certifId => {
        if (!newVal[certifId].obtenu) dateErrors.value[certifId] = false;
    });
}, { deep: true });

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
    return styles[niveau] || { background: '#bae6fd', color: '#0369a1' };
};

const onCertificatChange = (certificatId) => {
    if (!form.certificats[certificatId].obtenu) {
        form.certificats[certificatId].date_obtention = null;
        dateErrors.value[certificatId] = false;
    } else {
        if (!form.certificats[certificatId].date_obtention) dateErrors.value[certificatId] = true;
    }
};

const validateDates = () => {
    let isValid = true;
    dateErrors.value = {};
    Object.keys(form.certificats).forEach(certifId => {
        if (form.certificats[certifId].obtenu && !form.certificats[certifId].date_obtention) {
            dateErrors.value[certifId] = true;
            isValid = false;
        }
    });
    return isValid;
};

// SOUMISSION - AJOUT DES NOUVEAUX CHAMPS DANS LES DONNÉES ENVOYÉES
const submitForm = () => {
    if (!validateDates()) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'Veuillez renseigner les dates d\'obtention pour les certificats cochés',
            life: 5000
        });
        return;
    }
    
    saving.value = true;
    errors.value = {};

    const formData = {
        ...form,
        date_naissance: form.date_naissance ? formatDateForServer(form.date_naissance) : null,
        date_entree_service: form.date_entree_service ? formatDateForServer(form.date_entree_service) : null,
        date_derniere_promotion: form.date_derniere_promotion ? formatDateForServer(form.date_derniere_promotion) : null,
        // Inclure les trois champs (ils sont déjà dans form, on les passe)
        position_actuelle: form.position_actuelle,
        fonction_passee: form.fonction_passee,
        fonction_actuelle: form.fonction_actuelle,
        certificats: {}
    };

    Object.keys(form.certificats).forEach(certifId => {
        if (form.certificats[certifId].obtenu) {
            formData.certificats[certifId] = {
                obtenu: true,
                date_obtention: form.certificats[certifId].date_obtention 
                    ? formatDateForServer(form.certificats[certifId].date_obtention)
                    : null
            };
        }
    });

    router.put(route('militaires.update', form.id), formData, {
        onSuccess: () => {
            saving.value = false;
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Militaire mis à jour avec succès',
                life: 3000
            });
            router.visit(route('militaires.show', form.id));
        },
        onError: (err) => {
            saving.value = false;
            errors.value = err;
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Veuillez corriger les erreurs du formulaire',
                life: 5000
            });
            const firstError = document.querySelector('.p-invalid');
            if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
};

const formatDateForServer = (date) => {
    if (!date) return null;
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const cancel = () => {
    router.visit(route('militaires.index'));
};

onMounted(() => {
    Object.keys(form.certificats).forEach(certifId => {
        if (!form.certificats[certifId].obtenu) form.certificats[certifId].date_obtention = null;
        else if (form.certificats[certifId].obtenu && !form.certificats[certifId].date_obtention) dateErrors.value[certifId] = true;
    });
});
</script>

<style scoped>
/* Styles inchangés */
.field {
    margin-bottom: 1rem;
}
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
:deep(.p-inputtext), 
:deep(.p-select),
:deep(.p-datepicker) {
    width: 100%;
    border-radius: 0.5rem;
}
:deep(.p-invalid) {
    border-color: #f87171;
}
:deep(.p-tag) {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-weight: 500;
}
:deep(.p-checkbox) {
    margin-top: 0.25rem;
}
.text-sky-500 {
    color: #0ea5e9;
}
.text-sky-600 {
    color: #0284c7;
}
.bg-sky-400 {
    background-color: #38bdf8;
}
.hover\:bg-sky-500:hover {
    background-color: #0ea5e9;
}
.border-sky-400 {
    border-color: #38bdf8;
}
.text-white {
    color: white;
}
.border {
    transition: all 0.2s ease;
    border-color: #e5e7eb;
}
.border:hover {
    border-color: #7dd3fc;
}
</style>