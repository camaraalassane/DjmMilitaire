<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white">
                Ajouter un militaire
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submitForm" enctype="multipart/form-data">
                            <Card>
                                <template #title>
                                    <div class="flex items-center gap-2">
                                        <i class="pi pi-user-plus text-sky-500"></i>
                                        <span class="text-sky-600">Nouveau militaire</span>
                                    </div>
                                </template>

                                <template #content>
                                    <!-- Bannière de résumé d'erreurs de validation -->
                                    <div v-if="errors && Object.keys(errors).length > 0" class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                                        <div class="flex items-center gap-2 mb-2 text-red-700 font-semibold">
                                            <i class="pi pi-exclamation-triangle text-lg"></i>
                                            <span>Erreurs détectées lors de la validation du formulaire :</span>
                                        </div>
                                        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                                            <li v-for="(err, field) in errors" :key="field">
                                                <strong class="capitalize">{{ field }}</strong> : {{ Array.isArray(err) ? err.join(', ') : err }}
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Informations générales -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <!-- Matricule -->
                                        <div class="field">
                                            <label for="matricule" class="block text-sm font-medium text-gray-700 mb-2">
                                                Matricule <span class="text-red-500">*</span>
                                            </label>
                                            <InputText id="matricule" v-model="form.matricule" class="w-full"
                                                :class="{ 'p-invalid': errors.matricule }" />
                                            <small v-if="errors.matricule" class="text-red-500">{{ errors.matricule
                                            }}</small>
                                        </div>

                                        <!-- Grade actuel -->
                                        <div class="field">
                                            <label for="grade_actuel"
                                                class="block text-sm font-medium text-gray-700 mb-2">
                                                Grade actuel <span class="text-red-500">*</span>
                                            </label>
                                            <Select v-model="form.grade_actuel" :options="grades"
                                                optionLabel="nom_grade" optionValue="nom_grade"
                                                placeholder="Sélectionner un grade" class="w-full"
                                                :class="{ 'p-invalid': errors.grade_actuel }" />
                                            <small v-if="errors.grade_actuel" class="text-red-500">{{
                                                errors.grade_actuel }}</small>
                                        </div>
                                    </div>

                                    <!-- Nom et Prénom -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <div class="field">
                                            <label for="nom" class="block text-sm font-medium text-gray-700 mb-2">
                                                Nom <span class="text-red-500">*</span>
                                            </label>
                                            <InputText id="nom" v-model="form.nom" class="w-full"
                                                :class="{ 'p-invalid': errors.nom }" />
                                            <small v-if="errors.nom" class="text-red-500">{{ errors.nom }}</small>
                                        </div>

                                        <div class="field">
                                            <label for="prenom" class="block text-sm font-medium text-gray-700 mb-2">
                                                Prénom <span class="text-red-500">*</span>
                                            </label>
                                            <InputText id="prenom" v-model="form.prenom" class="w-full"
                                                :class="{ 'p-invalid': errors.prenom }" />
                                            <small v-if="errors.prenom" class="text-red-500">{{ errors.prenom }}</small>
                                        </div>
                                    </div>

                                    <!-- Dates -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <div class="field">
                                            <label for="date_naissance"
                                                class="block text-sm font-medium text-gray-700 mb-2">
                                                Date de naissance <span class="text-red-500">*</span>
                                            </label>
                                            <DatePicker id="date_naissance" v-model="form.date_naissance"
                                                dateFormat="dd/mm/yy" showIcon class="w-full"
                                                :class="{ 'p-invalid': errors.date_naissance }" />
                                            <small v-if="errors.date_naissance" class="text-red-500">{{
                                                errors.date_naissance }}</small>
                                        </div>

                                        <div class="field">
                                            <label for="date_entree_service"
                                                class="block text-sm font-medium text-gray-700 mb-2">
                                                Date d'entrée en service <span class="text-red-500">*</span>
                                            </label>
                                            <DatePicker id="date_entree_service" v-model="form.date_entree_service"
                                                dateFormat="dd/mm/yy" showIcon class="w-full"
                                                :class="{ 'p-invalid': errors.date_entree_service }" />
                                            <small v-if="errors.date_entree_service" class="text-red-500">{{
                                                errors.date_entree_service }}</small>
                                        </div>
                                    </div>

                                    <!-- Autres informations -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <div class="field">
                                            <label for="date_derniere_promotion"
                                                class="block text-sm font-medium text-gray-700 mb-2">
                                                Date dernière promotion
                                            </label>
                                            <DatePicker id="date_derniere_promotion"
                                                v-model="form.date_derniere_promotion" dateFormat="dd/mm/yy" showIcon
                                                class="w-full" />
                                        </div>

                                        <div class="field">
                                            <label for="specialite"
                                                class="block text-sm font-medium text-gray-700 mb-2">
                                                Spécialité
                                            </label>
                                            <InputText id="specialite" v-model="form.specialite" class="w-full" />
                                        </div>
                                    </div>

                                    <!-- Position, Fonction passée, Fonction actuelle -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                        <div class="field">
                                            <label for="position_actuelle"
                                                class="block text-sm font-medium text-gray-700 mb-2">
                                                <i class="pi pi-map-marker text-sky-500 mr-1"></i> Position actuelle
                                            </label>
                                            <InputText id="position_actuelle" v-model="form.position_actuelle"
                                                class="w-full" />
                                        </div>

                                        <div class="field">
                                            <label for="fonction_passee"
                                                class="block text-sm font-medium text-gray-700 mb-2">
                                                <i class="pi pi-history text-sky-500 mr-1"></i> Fonction passée
                                            </label>
                                            <InputText id="fonction_passee" v-model="form.fonction_passee"
                                                class="w-full" />
                                        </div>

                                        <div class="field">
                                            <label for="fonction_actuelle"
                                                class="block text-sm font-medium text-gray-700 mb-2">
                                                <i class="pi pi-briefcase text-sky-500 mr-1"></i> Fonction actuelle
                                            </label>
                                            <InputText id="fonction_actuelle" v-model="form.fonction_actuelle"
                                                class="w-full" />
                                        </div>
                                    </div>

                                    <!-- STATUT ET TÉLÉPHONE sur la même ligne -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <div class="field">
                                            <label for="statut" class="block text-sm font-medium text-gray-700 mb-2">
                                                Statut <span class="text-red-500">*</span>
                                            </label>
                                            <Select v-model="form.statut" :options="statutOptions" optionLabel="label"
                                                optionValue="value" placeholder="Sélectionner un statut" class="w-full"
                                                :class="{ 'p-invalid': errors.statut }" />
                                            <small v-if="errors.statut" class="text-red-500">{{ errors.statut }}</small>
                                        </div>

                                        <div class="field">
                                            <label for="telephone" class="block text-sm font-medium text-gray-700 mb-2">
                                                <i class="pi pi-phone text-sky-500 mr-1"></i> Téléphone
                                            </label>
                                            <InputText id="telephone" v-model="form.telephone" class="w-full"
                                                placeholder="Ex: +225 05 08 XX XX XX"
                                                :class="{ 'p-invalid': errors.telephone }" />
                                            <small v-if="errors.telephone" class="text-red-500">{{ errors.telephone
                                            }}</small>
                                        </div>
                                    </div>

                                    <!-- SEXE & GROUPE SANGUIN sur la même ligne -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <div class="field">
                                            <label for="sexe" class="block text-sm font-medium text-gray-700 mb-2">
                                                <i class="pi pi-user text-sky-500 mr-1"></i> Sexe
                                            </label>
                                            <Select v-model="form.sexe" :options="sexeOptions" optionLabel="label"
                                                optionValue="value" placeholder="Sélectionner" class="w-full"
                                                showClear />
                                        </div>

                                        <div class="field">
                                            <label for="groupe_sanguin"
                                                class="block text-sm font-medium text-gray-700 mb-2">
                                                <i class="pi pi-heart text-red-500 mr-1"></i> Groupe sanguin
                                            </label>
                                            <Select v-model="form.groupe_sanguin" :options="groupeSanguinOptions"
                                                optionLabel="label" optionValue="value" placeholder="Sélectionner"
                                                class="w-full" showClear />
                                        </div>
                                    </div>

                                    <!-- PERSONNE À CONTACTER & TÉLÉPHONE DE LA PERSONNE sur la même ligne -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <div class="field">
                                            <label for="personne_a_contacter"
                                                class="block text-sm font-medium text-gray-700 mb-2">
                                                <i class="pi pi-users text-sky-500 mr-1"></i> Personne à contacter
                                            </label>
                                            <InputText id="personne_a_contacter" v-model="form.personne_a_contacter"
                                                class="w-full" placeholder="Nom et prénom" />
                                        </div>

                                        <div class="field">
                                            <label for="telephone_personne_contacter"
                                                class="block text-sm font-medium text-gray-700 mb-2">
                                                <i class="pi pi-phone text-sky-500 mr-1"></i> Téléphone de la personne
                                            </label>
                                            <InputText id="telephone_personne_contacter"
                                                v-model="form.telephone_personne_contacter" class="w-full"
                                                placeholder="Ex: +225 05 08 XX XX XX" />
                                        </div>
                                    </div>

                                    <!-- PERMIS, JUSTICE, DISCIPLINE sur la même ligne -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                        <div class="flex items-center gap-2 p-2 border rounded-lg bg-gray-50">
                                            <Checkbox id="a_permis_conduire" v-model="form.a_permis_conduire"
                                                :binary="true" />
                                            <label for="a_permis_conduire" class="text-sm font-medium text-gray-700">
                                                <i class="pi pi-car text-sky-500 mr-1"></i> Permis de conduire
                                            </label>
                                        </div>

                                        <div class="flex items-center gap-2 p-2 border rounded-lg bg-gray-50">
                                            <Checkbox id="a_fait_justice" v-model="form.a_fait_justice"
                                                :binary="true" />
                                            <label for="a_fait_justice" class="text-sm font-medium text-gray-700">
                                                <i class="pi pi-gavel text-amber-500 mr-1"></i> A fait justice
                                            </label>
                                        </div>

                                        <div class="flex items-center gap-2 p-2 border rounded-lg bg-gray-50">
                                            <Checkbox id="a_fait_discipline" v-model="form.a_fait_discipline"
                                                :binary="true" />
                                            <label for="a_fait_discipline" class="text-sm font-medium text-gray-700">
                                                <i class="pi pi-exclamation-triangle text-red-500 mr-1"></i> A fait
                                                discipline
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
                                            <div v-if="filteredCertificats.length === 0"
                                                class="text-center py-4 text-gray-500">
                                                <i class="pi pi-info-circle text-2xl mb-2"></i>
                                                <p>Aucune formation disponible pour ce grade.</p>
                                            </div>

                                            <div v-else>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div v-for="certificat in filteredCertificats" :key="certificat.id"
                                                        class="border rounded-lg p-3 hover:border-sky-300 transition-all">

                                                        <div class="flex items-start gap-2">
                                                            <Checkbox :id="'certif_' + certificat.id"
                                                                v-model="form.certificats[certificat.id].obtenu"
                                                                :binary="true"
                                                                @change="onCertificatChange(certificat.id)" />
                                                            <label :for="'certif_' + certificat.id"
                                                                class="font-medium text-gray-700 text-sm">
                                                                {{ certificat.nom_certificat }}
                                                                <Tag :value="certificat.niveau_certificat"
                                                                    :style="getNiveauStyle(certificat.niveau_certificat)"
                                                                    class="ml-1 text-xs" />
                                                            </label>
                                                        </div>

                                                        <div class="mt-2">
                                                            <label :for="'date_certif_' + certificat.id"
                                                                class="block text-xs text-gray-600 mb-1">
                                                                Date d'obtention <span
                                                                    v-if="form.certificats[certificat.id].obtenu"
                                                                    class="text-red-500">*</span>
                                                            </label>
                                                            <DatePicker :id="'date_certif_' + certificat.id"
                                                                v-model="form.certificats[certificat.id].date_obtention"
                                                                dateFormat="dd/mm/yy" showIcon class="w-full"
                                                                size="small"
                                                                :disabled="!form.certificats[certificat.id].obtenu"
                                                                :class="{ 'p-invalid': dateErrors[certificat.id] && form.certificats[certificat.id].obtenu && !form.certificats[certificat.id].date_obtention }" />
                                                            <small
                                                                v-if="dateErrors[certificat.id] && form.certificats[certificat.id].obtenu && !form.certificats[certificat.id].date_obtention"
                                                                class="text-red-500 text-xs">
                                                                Date obligatoire
                                                            </small>
                                                        </div>

                                                        <div class="mt-2">
                                                            <label :for="'doc_' + certificat.id"
                                                                class="block text-xs text-gray-600 mb-1">
                                                                Document (optionnel)
                                                            </label>
                                                            <div class="flex items-center gap-2">
                                                                <FileUpload :id="'doc_' + certificat.id" mode="basic"
                                                                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                                                    chooseLabel="📎 Choisir" customUpload
                                                                    @select="(event) => onFileSelect(certificat.id, event)"
                                                                    :disabled="!form.certificats[certificat.id].obtenu"
                                                                    class="w-full" />
                                                                <Button v-if="form.certificats[certificat.id].document"
                                                                    icon="pi pi-times"
                                                                    class="p-button-rounded p-button-danger p-button-text"
                                                                    size="small" @click="removeDocument(certificat.id)"
                                                                    title="Supprimer le document" />
                                                            </div>
                                                            <div v-if="form.certificats[certificat.id].document"
                                                                class="text-xs text-green-600 flex items-center gap-1 mt-1">
                                                                <i class="pi pi-check-circle"></i>
                                                                <span class="truncate">{{
                                                                    getFileName(form.certificats[certificat.id].document)
                                                                }}</span>
                                                                <span class="text-gray-400">({{
                                                                    formatFileSize(form.certificats[certificat.id].document.size)
                                                                }})</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </Card>

                                    <div v-else class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="flex items-center gap-2">
                                            <i class="pi pi-info-circle text-yellow-600"></i>
                                            <p class="text-sm text-yellow-700">Veuillez sélectionner un grade pour voir
                                                les formations
                                                disponibles.</p>
                                        </div>
                                    </div>

                                    <!-- Boutons d'action -->
                                    <div class="flex justify-end gap-2 mt-6">
                                        <Button label="Annuler" icon="pi pi-times"
                                            class="p-button-outlined border-gray-300 text-gray-700 hover:bg-gray-100"
                                            @click="cancel" />
                                        <Button label="Enregistrer" icon="pi pi-save" type="submit"
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
import { ref, reactive, computed, watch } from 'vue';
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
import FileUpload from 'primevue/fileupload';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    grades: {
        type: Array,
        required: true
    },
    certificats: {
        type: Array,
        default: () => []
    }
});

const toast = useToast();
const saving = ref(false);
const errors = ref({});
const dateErrors = ref({});
const docErrors = ref({});

const statutOptions = [
    { label: 'Actif', value: 'actif' },
    { label: 'Retraité', value: 'retraité' },
    { label: 'Déserteur', value: 'déserteur' },
    { label: 'Décédé', value: 'décédé' },
    { label: 'Formation', value: 'formation' },
    { label: 'Stage', value: 'stage' }
];

const sexeOptions = [
    { label: 'M', value: 'M' },
    { label: 'F', value: 'F' }
];

const groupeSanguinOptions = [
    { label: 'A+', value: 'A+' },
    { label: 'A-', value: 'A-' },
    { label: 'B+', value: 'B+' },
    { label: 'B-', value: 'B-' },
    { label: 'AB+', value: 'AB+' },
    { label: 'AB-', value: 'AB-' },
    { label: 'O+', value: 'O+' },
    { label: 'O-', value: 'O-' }
];

const formationsOfficiers = [
    'APLI', 'CFCU', 'Certificat État-major',
    'Certificat d\'état-major', 'École d\'État-Major', 'EEM',
    'Cours supérieur d\'état-major', 'École de guerre',
    'Brevet Supérieur de Second Degré',
    'École de guerre / Brevet Supérieur de Second Degré',
    'Cour d\'Application', 'Cour des Futurs Commandants d\'Unité'
];

const officierTypes = ['officier général', 'officier supérieur', 'officier subalterne'];

const form = reactive({
    matricule: '',
    grade_actuel: null,
    nom: '',
    prenom: '',
    date_naissance: null,
    date_entree_service: null,
    date_derniere_promotion: null,
    specialite: '',
    position_actuelle: '',
    fonction_passee: '',
    fonction_actuelle: '',
    telephone: '',
    statut: 'actif',
    sexe: null,
    groupe_sanguin: null,
    personne_a_contacter: '',
    telephone_personne_contacter: '',
    a_permis_conduire: false,
    a_fait_justice: false,
    a_fait_discipline: false,
    certificats: {}
});

if (props.certificats && Array.isArray(props.certificats)) {
    props.certificats.forEach(certificat => {
        form.certificats[certificat.id] = {
            obtenu: false,
            date_obtention: null,
            document: null
        };
    });
} else {
    form.certificats = {};
}

const estFormationOfficier = (certificat) => {
    const nom = certificat.nom_certificat;
    const niveau = certificat.niveau_certificat;
    if (formationsOfficiers.includes(nom) || formationsOfficiers.includes(niveau)) return true;
    for (const f of formationsOfficiers) {
        if (nom && (nom.includes(f) || f.includes(nom))) return true;
        if (niveau && (niveau.includes(f) || f.includes(niveau))) return true;
    }
    return false;
};

const showFormationsSection = computed(() => form.grade_actuel !== null && form.grade_actuel !== '');

const filteredCertificats = computed(() => {
    if (!form.grade_actuel) return [];
    const gradeInfo = props.grades.find(g => g.nom_grade === form.grade_actuel);
    if (!gradeInfo) return [];
    if (officierTypes.includes(gradeInfo.type_grade?.toLowerCase())) return props.certificats;
    return props.certificats.filter(cert => !estFormationOfficier(cert));
});

watch(() => form.grade_actuel, (newGrade, oldGrade) => {
    if (newGrade && newGrade !== oldGrade) {
        const oldInfo = props.grades.find(g => g.nom_grade === oldGrade);
        const newInfo = props.grades.find(g => g.nom_grade === newGrade);
        if (oldInfo && newInfo) {
            const wasOfficier = officierTypes.includes(oldInfo.type_grade?.toLowerCase());
            const isNowOfficier = officierTypes.includes(newInfo.type_grade?.toLowerCase());
            if (wasOfficier && !isNowOfficier) {
                Object.keys(form.certificats).forEach(certifId => {
                    const cert = props.certificats.find(c => c.id == certifId);
                    if (cert && estFormationOfficier(cert)) {
                        if (form.certificats[certifId].obtenu) {
                            form.certificats[certifId].obtenu = false;
                            form.certificats[certifId].date_obtention = null;
                            form.certificats[certifId].document = null;
                            dateErrors.value[certifId] = false;
                            docErrors.value[certifId] = false;
                        }
                    }
                });
                toast.add({
                    severity: 'info',
                    summary: 'Information',
                    detail: 'Les formations d\'officiers ont été désélectionnées.',
                    life: 3000
                });
            }
        }
    }
}, { immediate: true });

watch(() => form.certificats, (newVal) => {
    Object.keys(newVal).forEach(certifId => {
        if (!newVal[certifId].obtenu) {
            dateErrors.value[certifId] = false;
            docErrors.value[certifId] = false;
        }
    });
}, { deep: true });

const getNiveauStyle = (niveau) => {
    const styles = {
        'BE':   { background: '#e0f2fe', color: '#0369a1' },
        'CAT1': { background: '#7dd3fc', color: '#0369a1' },
        'CAT2': { background: '#38bdf8', color: '#075985' },
        'CIA':  { background: '#22d3ee', color: '#0e7490' },
        'BA1':  { background: '#a7f3d0', color: '#047857' },
        'BA2':  { background: '#6ee7b7', color: '#065f46' },
        'BMP1': { background: '#fde68a', color: '#b45309' },
        'BMP2': { background: '#fcd34d', color: '#92400e' },
        'BS':   { background: '#fed7aa', color: '#c2410c' },
        'CT1':  { background: '#ddd6fe', color: '#6d28d9' },
        'CT2':  { background: '#c4b5fd', color: '#5b21b6' },
    };
    return styles[niveau] || { background: '#bae6fd', color: '#0369a1' };
};

const onCertificatChange = (certifId) => {
    if (!form.certificats[certifId].obtenu) {
        form.certificats[certifId].date_obtention = null;
        form.certificats[certifId].document = null;
        dateErrors.value[certifId] = false;
        docErrors.value[certifId] = false;
    } else {
        if (!form.certificats[certifId].date_obtention) {
            dateErrors.value[certifId] = true;
        }
    }
};

const onFileSelect = (certifId, event) => {
    const file = event.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Le fichier est trop volumineux. Maximum 5MB.',
                life: 3000
            });
            return;
        }
        form.certificats[certifId].document = file;
        docErrors.value[certifId] = false;
        toast.add({
            severity: 'success',
            summary: 'Fichier ajouté',
            detail: `"${file.name}" sélectionné`,
            life: 2000
        });
    }
};

const removeDocument = (certifId) => {
    form.certificats[certifId].document = null;
    docErrors.value[certifId] = false;
};

const getFileName = (file) => {
    if (!file) return '';
    if (typeof file === 'string') return file.split('/').pop();
    return file.name;
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
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

    const formData = new FormData();

    const textFields = [
        'matricule', 'nom', 'prenom', 'grade_actuel', 'specialite',
        'position_actuelle', 'fonction_passee', 'fonction_actuelle',
        'telephone', 'statut',
        'sexe', 'groupe_sanguin', 'personne_a_contacter', 'telephone_personne_contacter'
    ];

    textFields.forEach(field => {
        formData.append(field, (form[field] !== null && form[field] !== undefined) ? form[field] : '');
    });

    const dateFields = ['date_naissance', 'date_entree_service', 'date_derniere_promotion'];
    dateFields.forEach(field => {
        formData.append(field, form[field] ? formatDateForServer(form[field]) : '');
    });

    formData.append('a_permis_conduire', form.a_permis_conduire ? '1' : '0');
    formData.append('a_fait_justice', form.a_fait_justice ? '1' : '0');
    formData.append('a_fait_discipline', form.a_fait_discipline ? '1' : '0');

    Object.keys(form.certificats).forEach(certifId => {
        const certData = form.certificats[certifId];
        formData.append(`certificats[${certifId}][obtenu]`, certData.obtenu ? '1' : '0');

        if (certData.obtenu) {
            if (certData.date_obtention) {
                formData.append(`certificats[${certifId}][date_obtention]`,
                    formatDateForServer(certData.date_obtention));
            }
            if (certData.document) {
                formData.append(`certificats[${certifId}][document]`, certData.document);
            }
        }
    });

    router.post(route('militaires.store'), formData, {
        forceFormData: true,
        onSuccess: () => {
            saving.value = false;
            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: 'Militaire ajouté avec succès',
                life: 3000
            });
            router.visit(route('militaires.index'));
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
    if (!date) return '';
    if (typeof date === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(date)) {
        return date;
    }
    const d = new Date(date);
    if (isNaN(d.getTime())) return '';
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const cancel = () => {
    router.visit(route('militaires.index'));
};
</script>

<style scoped>
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

:deep(.p-fileupload) {
    width: 100%;
}

:deep(.p-fileupload .p-button) {
    width: 100%;
    justify-content: center;
    font-size: 0.8rem;
    padding: 0.3rem 0.5rem;
}

:deep(.p-datepicker .p-inputtext) {
    font-size: 0.85rem;
    padding: 0.3rem 0.5rem;
}

.text-sky-500 {
    color: #0ea5e9;
}

.text-sky-600 {
    color: #0284c7;
}

.text-amber-500 {
    color: #f59e0b;
}

.text-red-500 {
    color: #ef4444;
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

.truncate {
    max-width: 100px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

:deep(.p-button.p-button-text) {
    padding: 0.2rem;
}

.bg-gray-50 {
    background-color: #f9fafb;
}
</style>
