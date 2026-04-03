<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white">
                    Importation depuis Excel
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <Card>
                            <template #title>
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-file-excel text-sky-500"></i>
                                    <span class="text-sky-600">Importation depuis Excel</span>
                                </div>
                            </template>
                            
                            <template #content>
                                <!-- Message d'information -->
                                <Message severity="info" :closable="false" class="mb-6" style="background: #f0f9ff; border-color: #7dd3fc;">
                                    <div class="font-medium mb-2 text-sky-700">Format du fichier Excel requis :</div>
                                    <p class="mb-3 text-gray-600">Le fichier doit contenir une première ligne d'en-tête avec les noms de colonnes ci-dessous. Les colonnes obligatoires sont marquées d'un astérisque (*). Les autres sont optionnelles.</p>
                                    
                                    <Accordion :multiple="true" :activeIndex="[0, 1, 2]" class="mb-3">
                                        <!-- Informations générales -->
                                        <AccordionTab>
                                            <template #header>
                                                <span class="font-medium text-sky-600">Informations générales</span>
                                            </template>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <ul class="list-none p-0 m-0 space-y-2">
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Obligatoire" style="background: #ef4444; color: white;" />
                                                            <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">matricule</code>
                                                            <span class="text-sm text-gray-600">(texte)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Obligatoire" style="background: #ef4444; color: white;" />
                                                            <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">nom</code>
                                                            <span class="text-sm text-gray-600">(texte)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Obligatoire" style="background: #ef4444; color: white;" />
                                                            <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">prenom</code>
                                                            <span class="text-sm text-gray-600">(texte)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Obligatoire" style="background: #ef4444; color: white;" />
                                                            <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_naissance</code>
                                                            <span class="text-sm text-gray-600">(AAAA-MM-JJ)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Obligatoire" style="background: #ef4444; color: white;" />
                                                            <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_entree_service</code>
                                                            <span class="text-sm text-gray-600">(AAAA-MM-JJ)</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div>
                                                    <ul class="list-none p-0 m-0 space-y-2">
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Obligatoire" style="background: #ef4444; color: white;" />
                                                            <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">grade_actuel</code>
                                                            <span class="text-sm text-gray-600">(doit exister dans la base)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Optionnel" style="background: #6b7280; color: white;" />
                                                            <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_derniere_promotion</code>
                                                            <span class="text-sm text-gray-600">(AAAA-MM-JJ)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Optionnel" style="background: #6b7280; color: white;" />
                                                            <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">specialite</code>
                                                            <span class="text-sm text-gray-600">(texte)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Optionnel" style="background: #6b7280; color: white;" />
                                                            <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">statut</code>
                                                            <span class="text-sm text-gray-600">(actif, retraité, etc.)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Optionnel" style="background: #6b7280; color: white;" />
                                                            <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_permis_conduire</code>
                                                            <span class="text-sm text-gray-600">(0/1)</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </AccordionTab>

                                        <!-- Certificats sous-officiers -->
                                        <AccordionTab>
                                            <template #header>
                                                <span class="font-medium text-sky-600">Certificats sous-officiers</span>
                                            </template>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <ul class="list-none p-0 m-0 space-y-2">
                                                        <li><code class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_cat1</code> + <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_cat1</code></li>
                                                        <li><code class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_cat2</code> + <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_cat2</code></li>
                                                        <li><code class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_cia</code> + <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_cia</code></li>
                                                        <li><code class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_ba1</code> + <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_ba1</code></li>
                                                        <li><code class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_ba2</code> + <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_ba2</code></li>
                                                    </ul>
                                                </div>
                                                <div>
                                                    <ul class="list-none p-0 m-0 space-y-2">
                                                        <li><code class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_bmp1</code> + <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_bmp1</code></li>
                                                        <li><code class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_bmp2</code> + <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_bmp2</code></li>
                                                        <li><code class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_bs</code> + <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_bs</code></li>
                                                        <li><code class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_ct2</code> + <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_ct2</code></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </AccordionTab>

                                        <!-- Formations officiers -->
                                        <AccordionTab>
                                            <template #header>
                                                <span class="font-medium text-sky-600">Formations officiers</span>
                                            </template>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <ul class="list-none p-0 m-0 space-y-2">
                                                    <li><code class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_apli</code> + <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_apli</code></li>
                                                    <li><code class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_cfcu</code> + <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_cfcu</code></li>
                                                    <li><code class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_cem</code> + <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_cem</code></li>
                                                    <li><code class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_certificat_etat_major</code> + <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_certificat_etat_major</code></li>
                                                    <li><code class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_ecole_guerre</code> + <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_ecole_guerre</code></li>
                                                </ul>
                                            </div>
                                        </AccordionTab>
                                    </Accordion>

                                    <div class="mt-4 p-3 bg-sky-50 border-l-4 border-sky-400 rounded">
                                        <p class="font-medium mb-2 text-sky-700">📝 Remarques importantes :</p>
                                        <ul class="list-disc pl-5 space-y-1 text-sm text-gray-600">
                                            <li>Les dates peuvent être au format texte (AAAA-MM-JJ) ou au format nombre Excel (automatiquement converti).</li>
                                            <li>Pour chaque certificat, si la colonne <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_xxx</code> est à 1, vous pouvez renseigner la date d'obtention correspondante. Si elle est omise ou à 0, la date est ignorée.</li>
                                            <li>Les colonnes de certificats ne sont pas obligatoires ; les valeurs par défaut sont 0 (non obtenu) et date nulle.</li>
                                        </ul>
                                    </div>
                                </Message>

                                <!-- Formulaire d'import -->
                                <form @submit.prevent="submitForm" enctype="multipart/form-data">
                                    <div class="field mb-4">
                                        <label for="fichier" class="block text-sm font-medium text-gray-700 mb-2">
                                            Fichier Excel (.xlsx, .xls, .csv) <span class="text-red-500">*</span>
                                        </label>
                                        
                                        <div class="flex flex-col gap-2">
                                            <FileUpload 
                                                id="fichier"
                                                name="fichier"
                                                :multiple="false"
                                                accept=".xlsx,.xls,.csv"
                                                :maxFileSize="2097152"
                                                :fileLimit="1"
                                                @select="onFileSelect"
                                                @remove="onFileRemove"
                                                :chooseLabel="file ? 'Changer de fichier' : 'Choisir un fichier'"
                                                :class="{ 'p-invalid': errors.fichier }"
                                                mode="advanced"
                                                class="w-full"
                                                style="border-color: #bae6fd;">
                                                <template #empty>
                                                    <div class="text-center p-8 text-gray-500">
                                                        <i class="pi pi-cloud-upload text-4xl mb-2 text-sky-400"></i>
                                                        <p class="text-gray-600">Glissez-déposez votre fichier ici ou cliquez pour parcourir</p>
                                                        <small class="text-gray-400">Taille maximale : 2 Mo</small>
                                                    </div>
                                                </template>
                                            </FileUpload>
                                            
                                            <small v-if="errors.fichier" class="text-red-500">{{ errors.fichier }}</small>
                                            <small class="text-gray-500">Formats acceptés : .xlsx, .xls, .csv</small>
                                        </div>
                                    </div>

                                    <!-- Boutons d'action -->
                                    <div class="flex gap-2 mt-6">
                                        <Button type="submit" 
                                                label="Importer" 
                                                icon="pi pi-upload"
                                                :loading="uploading"
                                                :disabled="!file"
                                                class="bg-sky-400 hover:bg-sky-500 border-sky-400 text-white" />
                                        
                                        <Button label="Retour" 
                                                icon="pi pi-arrow-left"
                                                class="p-button-outlined border-gray-300 text-gray-700 hover:bg-gray-100"
                                                @click="cancel" />
                                    </div>
                                </form>

                                <!-- Téléchargement du modèle -->
                                <Divider />
                                
                                <div class="mt-4">
                                    <h6 class="font-medium text-gray-700 mb-3">Télécharger le modèle :</h6>
                                    <div class="flex items-center gap-3">
                                        <Button label="Modèle Excel" 
                                                icon="pi pi-download"
                                                class="p-button-outlined border-sky-400 text-sky-500 hover:bg-sky-50"
                                                @click="downloadTemplate" />
                                        <small class="text-gray-500">
                                            (Placez votre fichier modèle dans <code class="bg-gray-100 px-2 py-1 rounded text-sky-600">public/modele/</code>)
                                        </small>
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>
                </div>
            </div>
        </div>

        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from 'primevue/card';
import Message from 'primevue/message';
import Accordion from 'primevue/accordion';
import AccordionTab from 'primevue/accordiontab';
import FileUpload from 'primevue/fileupload';
import Button from 'primevue/button';
import Divider from 'primevue/divider';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const uploading = ref(false);
const file = ref(null);
const errors = ref({});

// Gérer la sélection de fichier
const onFileSelect = (event) => {
    file.value = event.files[0];
    errors.value.fichier = null;
};

// Gérer la suppression de fichier
const onFileRemove = () => {
    file.value = null;
};

// Soumettre le formulaire
const submitForm = () => {
    if (!file.value) {
        errors.value.fichier = 'Veuillez sélectionner un fichier';
        return;
    }

    uploading.value = true;
    errors.value = {};

    const formData = new FormData();
    formData.append('fichier', file.value);

    router.post(route('militaires.import.process'), formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        },
        onSuccess: (page) => {
            uploading.value = false;
            file.value = null;
            
            // Réinitialiser le composant FileUpload
            const fileUpload = document.querySelector('.p-fileupload');
            if (fileUpload) {
                fileUpload.__vueParentComponent?.reset();
            }

            toast.add({
                severity: 'success',
                summary: 'Succès',
                detail: page.props.flash?.success || 'Importation réussie',
                life: 5000
            });

            // Rediriger vers la liste après un délai
            setTimeout(() => {
                router.visit(route('militaires.index'));
            }, 2000);
        },
        onError: (err) => {
            uploading.value = false;
            errors.value = err;
            
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: err.fichier || 'Erreur lors de l\'importation',
                life: 5000
            });
        }
    });
};

// Télécharger le modèle Excel
const downloadTemplate = () => {
    window.location.href = '/modele/modele_militaires.xlsx';
};

// Annuler et retourner à la liste
const cancel = () => {
    router.visit(route('militaires.index'));
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

:deep(.p-message) {
    border-radius: 8px;
}

:deep(.p-accordion .p-accordion-header .p-accordion-header-link) {
    padding: 1rem;
    background: #f9fafb;
    transition: all 0.2s ease;
}

:deep(.p-accordion .p-accordion-header .p-accordion-header-link:hover) {
    background: #f0f9ff;
}

:deep(.p-accordion .p-accordion-content) {
    padding: 1.5rem;
}

:deep(.p-fileupload) {
    border: 2px dashed #bae6fd;
    border-radius: 8px;
    transition: all 0.2s;
    background: #fafafa;
}

:deep(.p-fileupload:hover) {
    border-color: #7dd3fc;
    background: #f0f9ff;
}

:deep(.p-invalid) {
    border-color: #f87171 !important;
}

code {
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}

:deep(.p-tag) {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
    border-radius: 0.375rem;
    font-weight: 500;
}

/* Styles personnalisés */
.text-sky-500 {
    color: #0ea5e9;
}

.text-sky-600 {
    color: #0284c7;
}

.text-sky-700 {
    color: #0369a1;
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

.hover\:bg-sky-50:hover {
    background-color: #f0f9ff;
}
</style>