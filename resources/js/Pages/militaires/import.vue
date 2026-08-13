<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white">
                    Importation depuis Excel / CSV
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
                                    <span class="text-sky-600">Importation depuis Excel / CSV</span>
                                </div>
                            </template>

                            <template #content>
                                <!-- Affichage des doublons détectés -->
                                <div v-if="duplicates.length > 0" class="mb-6">
                                    <Message severity="warn" :closable="false">
                                        <div class="font-medium mb-2">⚠️ Doublons détectés</div>
                                        <p class="text-sm mb-3">Les matricules suivants existent déjà dans la base de
                                            données :</p>

                                        <div class="bg-white rounded-lg border border-amber-200 overflow-hidden">
                                            <DataTable :value="duplicates" class="p-datatable-sm">
                                                <Column field="matricule" header="Matricule" />
                                                <Column field="nom" header="Nom" />
                                                <Column field="prenom" header="Prénom" />
                                                <Column field="action" header="Action">
                                                    <template #body="slotProps">
                                                        <Tag :value="slotProps.data.action"
                                                            :severity="slotProps.data.action === 'Ignoré' ? 'warning' : 'success'" />
                                                    </template>
                                                </Column>
                                            </DataTable>
                                        </div>

                                        <div class="mt-3 flex gap-3">
                                            <Button label="Ignorer les doublons" icon="pi pi-ban" severity="warning"
                                                @click="setDuplicateAction('ignore')"
                                                :class="{ 'p-button-outlined': duplicateAction !== 'ignore' }" />
                                            <Button label="Mettre à jour les doublons" icon="pi pi-refresh"
                                                severity="info" @click="setDuplicateAction('update')"
                                                :class="{ 'p-button-outlined': duplicateAction !== 'update' }" />
                                        </div>
                                    </Message>
                                </div>

                                <!-- Message d'information -->
                                <Message severity="info" :closable="false" class="mb-6"
                                    style="background: #f0f9ff; border-color: #7dd3fc;">
                                    <div class="font-medium mb-2 text-sky-700">📋 Format du fichier requis :</div>
                                    <p class="mb-3 text-gray-600">Le fichier doit contenir une première ligne d'en-tête
                                        avec les noms de
                                        colonnes ci-dessous. Les colonnes obligatoires sont marquées d'un astérisque
                                        (*).</p>

                                    <Accordion :multiple="true" :activeIndex="[0, 1, 2, 3, 4]" class="mb-3">
                                        <!-- 1. Informations personnelles -->
                                        <AccordionTab>
                                            <template #header>
                                                <span class="font-medium text-sky-600">👤 Informations
                                                    personnelles</span>
                                            </template>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <ul class="list-none p-0 m-0 space-y-2">
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Obligatoire"
                                                                style="background: #ef4444; color: white;" />
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">matricule</code>
                                                            <span class="text-sm text-gray-600">(texte)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Obligatoire"
                                                                style="background: #ef4444; color: white;" />
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">nom</code>
                                                            <span class="text-sm text-gray-600">(texte)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Obligatoire"
                                                                style="background: #ef4444; color: white;" />
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">prenom</code>
                                                            <span class="text-sm text-gray-600">(texte)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Obligatoire"
                                                                style="background: #ef4444; color: white;" />
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_naissance</code>
                                                            <span class="text-sm text-gray-600">(JJ/MM/AAAA)</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div>
                                                    <ul class="list-none p-0 m-0 space-y-2">
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Optionnel"
                                                                style="background: #6b7280; color: white;" />
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">sexe</code>
                                                            <span
                                                                class="text-sm text-gray-600">(Masculin/Féminin ou M/F)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Optionnel"
                                                                style="background: #6b7280; color: white;" />
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">telephone</code>
                                                            <span class="text-sm text-gray-600">(numéro)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Optionnel"
                                                                style="background: #6b7280; color: white;" />
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">groupe_sanguin</code>
                                                            <span class="text-sm text-gray-600">(A+, A-, B+,
                                                                etc.)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Optionnel"
                                                                style="background: #6b7280; color: white;" />
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">personne_a_contacter</code>
                                                            <span class="text-sm text-gray-600">(nom complet)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Optionnel"
                                                                style="background: #6b7280; color: white;" />
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">telephone_personne_contacter</code>
                                                            <span class="text-sm text-gray-600">(numéro)</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </AccordionTab>

                                        <!-- 2. Informations professionnelles -->
                                        <AccordionTab>
                                            <template #header>
                                                <span class="font-medium text-sky-600">💼 Informations
                                                    professionnelles</span>
                                            </template>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <ul class="list-none p-0 m-0 space-y-2">
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Obligatoire"
                                                                style="background: #ef4444; color: white;" />
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_entree_service</code>
                                                            <span class="text-sm text-gray-600">(JJ/MM/AAAA)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Obligatoire"
                                                                style="background: #ef4444; color: white;" />
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">grade_actuel</code>
                                                            <span class="text-sm text-gray-600">(doit exister dans la
                                                                base)</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div>
                                                    <ul class="list-none p-0 m-0 space-y-2">
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Optionnel"
                                                                style="background: #6b7280; color: white;" />
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_derniere_promotion</code>
                                                            <span class="text-sm text-gray-600">(JJ/MM/AAAA)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Optionnel"
                                                                style="background: #6b7280; color: white;" />
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">specialite</code>
                                                            <span class="text-sm text-gray-600">(texte)</span>
                                                        </li>
                                                        <li class="flex items-center gap-2">
                                                            <Tag value="Optionnel"
                                                                style="background: #6b7280; color: white;" />
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">statut</code>
                                                            <span
                                                                class="text-sm text-gray-600">(actif/retraité/déserteur/décédé/formation/stage)</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </AccordionTab>

                                        <!-- 3. Fonctions et positions -->
                                        <AccordionTab>
                                            <template #header>
                                                <span class="font-medium text-sky-600">📌 Fonctions et positions</span>
                                            </template>
                                            <ul class="list-none p-0 m-0 space-y-2">
                                                <li class="flex items-center gap-2">
                                                    <Tag value="Optionnel"
                                                        style="background: #6b7280; color: white;" />
                                                    <code
                                                        class="bg-gray-100 px-2 py-1 rounded text-sky-600">position_actuelle</code>
                                                    <span class="text-sm text-gray-600">(texte)</span>
                                                </li>
                                                <li class="flex items-center gap-2">
                                                    <Tag value="Optionnel"
                                                        style="background: #6b7280; color: white;" />
                                                    <code
                                                        class="bg-gray-100 px-2 py-1 rounded text-sky-600">fonction_passee</code>
                                                    <span class="text-sm text-gray-600">(texte)</span>
                                                </li>
                                                <li class="flex items-center gap-2">
                                                    <Tag value="Optionnel"
                                                        style="background: #6b7280; color: white;" />
                                                    <code
                                                        class="bg-gray-100 px-2 py-1 rounded text-sky-600">fonction_actuelle</code>
                                                    <span class="text-sm text-gray-600">(texte)</span>
                                                </li>
                                            </ul>
                                        </AccordionTab>

                                        <!-- 4. Permis et justice -->
                                        <AccordionTab>
                                            <template #header>
                                                <span class="font-medium text-sky-600">📜 Permis et justice</span>
                                            </template>
                                            <ul class="list-none p-0 m-0 space-y-2">
                                                <li class="flex items-center gap-2">
                                                    <Tag value="Optionnel"
                                                        style="background: #6b7280; color: white;" />
                                                    <code
                                                        class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_permis_conduire</code>
                                                    <span class="text-sm text-gray-600">(0/1 ou Oui/Non)</span>
                                                </li>
                                                <li class="flex items-center gap-2">
                                                    <Tag value="Optionnel"
                                                        style="background: #6b7280; color: white;" />
                                                    <code
                                                        class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_justice</code>
                                                    <span class="text-sm text-gray-600">(0/1 ou Oui/Non)</span>
                                                </li>
                                                <li class="flex items-center gap-2">
                                                    <Tag value="Optionnel"
                                                        style="background: #6b7280; color: white;" />
                                                    <code
                                                        class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_discipline</code>
                                                    <span class="text-sm text-gray-600">(0/1 ou Oui/Non)</span>
                                                </li>
                                            </ul>
                                        </AccordionTab>

                                        <!-- 5. Certificats et formations -->
                                        <AccordionTab>
                                            <template #header>
                                                <span class="font-medium text-sky-600">🎓 Certificats et
                                                    formations</span>
                                            </template>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div>
                                                    <p class="font-medium text-gray-700 mb-2">Certificats sous-officiers
                                                    </p>
                                                    <ul class="list-none p-0 m-0 space-y-1 text-sm">
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_cat1</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_cat1</code>
                                                        </li>
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_cat2</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_cat2</code>
                                                        </li>
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_cia</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_cia</code>
                                                        </li>
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_be</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_be</code>
                                                        </li>
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_ba1</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_ba1</code>
                                                        </li>
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_ba2</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_ba2</code>
                                                        </li>
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_bmp1</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_bmp1</code>
                                                        </li>
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_bmp2</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_bmp2</code>
                                                        </li>
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_bs</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_bs</code>
                                                        </li>
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_ct1</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_ct1</code>
                                                        </li>
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_ct2</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_ct2</code>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-700 mb-2">Formations officiers</p>
                                                    <ul class="list-none p-0 m-0 space-y-1 text-sm">
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_apli</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_apli</code>
                                                        </li>
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_cfcu</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_cfcu</code>
                                                        </li>
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_cpo</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_cpo</code>
                                                        </li>
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_cem</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_cem</code>
                                                        </li>
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_certificat_etat_major</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_certificat_etat_major</code>
                                                        </li>
                                                        <li>
                                                            <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_ecole_guerre</code>
                                                            + <code
                                                                class="bg-gray-100 px-2 py-1 rounded text-sky-600">date_obtention_ecole_guerre</code>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-700 mb-2">📝 Format des dates</p>
                                                    <ul class="list-none p-0 m-0 space-y-1 text-sm">
                                                        <li class="text-gray-600">• Format français :
                                                            <strong>JJ/MM/AAAA</strong>
                                                        </li>
                                                        <li class="text-gray-600">• Exemple : <code
                                                                class="bg-gray-100 px-2 py-1 rounded">31/12/2024</code>
                                                        </li>
                                                        <li class="text-gray-600">• Laisser vide si non obtenu</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </AccordionTab>
                                    </Accordion>

                                    <div class="mt-4 p-3 bg-sky-50 border-l-4 border-sky-400 rounded">
                                        <p class="font-medium mb-2 text-sky-700">📝 Remarques importantes :</p>
                                        <ul class="list-disc pl-5 space-y-1 text-sm text-gray-600">
                                            <li>Les dates sont au format français : <strong>JJ/MM/AAAA</strong> (ex:
                                                31/12/2024) ou <strong>AAAA-MM-JJ</strong></li>
                                            <li>Les colonnes <strong>0/1</strong> : 0 = Non, 1 = Oui (ou Oui/Non)</li>
                                            <li>Le sexe peut être : <strong>Masculin/Féminin</strong> ou <strong>M/F</strong></li>
                                            <li>Pour les certificats, si <code
                                                    class="bg-gray-100 px-2 py-1 rounded text-sky-600">a_fait_xxx</code>
                                                = 1, renseignez la date
                                                d'obtention</li>
                                            <li>Les grades doivent exister dans la base de données</li>
                                            <li>Le statut doit être parmi : actif, retraité, déserteur, décédé,
                                                formation, stage</li>
                                            <li>Formats acceptés : <strong>.xlsx</strong>, <strong>.xls</strong>, <strong>.csv</strong> (séparateur point-virgule ;)</li>
                                            <li>Taille maximale du fichier : <strong>10 Mo</strong></li>
                                        </ul>
                                    </div>
                                </Message>

                                <!-- Formulaire d'import -->
                                <form @submit.prevent="submitForm" enctype="multipart/form-data">
                                    <div class="field mb-4">
                                        <label for="fichier" class="block text-sm font-medium text-gray-700 mb-2">
                                            Fichier Excel ou CSV (.xlsx, .xls, .csv) <span class="text-red-500">*</span>
                                        </label>

                                        <div class="flex flex-col gap-2">
                                            <FileUpload id="fichier" name="fichier" :multiple="false"
                                                accept=".xlsx,.xls,.csv" :maxFileSize="10485760" :fileLimit="1"
                                                @select="onFileSelect" @remove="onFileRemove"
                                                :chooseLabel="file ? 'Changer de fichier' : 'Choisir un fichier'"
                                                :class="{ 'p-invalid': errors.fichier }" mode="advanced" class="w-full"
                                                style="border-color: #bae6fd;">
                                                <template #empty>
                                                    <div class="text-center p-8 text-gray-500">
                                                        <i class="pi pi-cloud-upload text-4xl mb-2 text-sky-400"></i>
                                                        <p class="text-gray-600">Glissez-déposez votre fichier ici ou
                                                            cliquez pour parcourir</p>
                                                        <small class="text-gray-400">Taille maximale : 10 Mo — Formats : .xlsx, .xls, .csv</small>
                                                    </div>
                                                </template>
                                            </FileUpload>

                                            <small v-if="errors.fichier" class="text-red-500">{{ errors.fichier
                                            }}</small>
                                            <small class="text-gray-500">Formats acceptés : .xlsx, .xls, .csv</small>
                                        </div>
                                    </div>

                                    <!-- Option de gestion des doublons -->
                                    <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            🔄 Comportement en cas de doublon (même matricule) :
                                        </label>
                                        <div class="flex gap-2">
                                            <Button label="Ignorer les doublons" icon="pi pi-ban" severity="warning"
                                                @click="setDuplicateAction('ignore')"
                                                :class="{ 'p-button-outlined': duplicateAction !== 'ignore' }"
                                                size="small" />
                                            <Button label="Mettre à jour les doublons" icon="pi pi-refresh"
                                                severity="info" @click="setDuplicateAction('update')"
                                                :class="{ 'p-button-outlined': duplicateAction !== 'update' }"
                                                size="small" />
                                        </div>
                                        <small class="text-gray-500 mt-2 block">
                                            <span v-if="duplicateAction === 'ignore'">🔹 Les doublons seront
                                                <strong>ignorés</strong> (aucune
                                                modification)</span>
                                            <span v-else>🔹 Les doublons seront <strong>mis à jour</strong> avec les
                                                nouvelles données</span>
                                        </small>
                                    </div>

                                    <!-- Boutons d'action -->
                                    <div class="flex gap-2 mt-6">
                                        <Button type="submit" label="Importer" icon="pi pi-upload" :loading="uploading"
                                            :disabled="!file"
                                            class="bg-sky-400 hover:bg-sky-500 border-sky-400 text-white" />

                                        <Button label="Retour" icon="pi pi-arrow-left"
                                            class="p-button-outlined border-gray-300 text-gray-700 hover:bg-gray-100"
                                            @click="cancel" />
                                    </div>
                                </form>

                                <!-- Résumé de l'import -->
                                <div v-if="importSummary" class="mt-6">
                                    <Divider />
                                    <Message :severity="importSummary.hasErrors ? 'error' : 'success'"
                                        :closable="false">
                                        <div class="font-medium mb-2">
                                            {{ importSummary.hasErrors ? '⚠️ Import terminé avec des erreurs' : '✅ Import terminé avec succès' }}
                                        </div>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-600">📥 Créés :</span>
                                                <span class="font-bold text-green-600">{{ importSummary.created
                                                }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">🔄 Mis à jour :</span>
                                                <span class="font-bold text-blue-600">{{ importSummary.updated }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">⏭️ Ignorés :</span>
                                                <span class="font-bold text-orange-600">{{ importSummary.skipped
                                                }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">❌ Erreurs :</span>
                                                <span class="font-bold text-red-600">{{ importSummary.errors }}</span>
                                            </div>
                                        </div>
                                    </Message>
                                </div>

                                <!-- Téléchargement du modèle -->
                                <Divider />

                                <div class="mt-4">
                                    <h6 class="font-medium text-gray-700 mb-3">📄 Télécharger le modèle d'import :</h6>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <Button label="Modèle Excel (.xlsx)" icon="pi pi-download"
                                            class="p-button-outlined border-sky-400 text-sky-500 hover:bg-sky-50"
                                            @click="downloadTemplate" />
                                        <Button label="Modèle CSV (.csv)" icon="pi pi-download"
                                            class="p-button-outlined border-gray-400 text-gray-600 hover:bg-gray-50"
                                            @click="downloadTemplateCSV" />
                                        <small class="text-gray-500">
                                            Le modèle contient les en-têtes de colonnes et un exemple de données.
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
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const uploading = ref(false);
const file = ref(null);
const errors = ref({});
const duplicates = ref([]);
const duplicateAction = ref('ignore');
const importSummary = ref(null);

const onFileSelect = (event) => {
    file.value = event.files[0];
    errors.value.fichier = null;
    duplicates.value = [];
    importSummary.value = null;
};

const onFileRemove = () => {
    file.value = null;
    duplicates.value = [];
    importSummary.value = null;
};

const setDuplicateAction = (action) => {
    duplicateAction.value = action;
};

const submitForm = () => {
    if (!file.value) {
        errors.value.fichier = 'Veuillez sélectionner un fichier';
        return;
    }

    uploading.value = true;
    errors.value = {};

    const formData = new FormData();
    formData.append('fichier', file.value);
    formData.append('duplicate_action', duplicateAction.value);

    router.post(route('militaires.import.process'), formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        },
        onSuccess: (page) => {
            uploading.value = false;

            if (page.props.duplicates && page.props.duplicates.length > 0) {
                duplicates.value = page.props.duplicates;
                toast.add({
                    severity: 'warn',
                    summary: '⚠️ Doublons détectés',
                    detail: `${page.props.duplicates.length} matricule(s) existent déjà dans la base`,
                    life: 5000
                });
                file.value = null;
                return;
            }

            if (page.props.import_summary) {
                importSummary.value = page.props.import_summary;
                if (importSummary.value.hasErrors) {
                    toast.add({
                        severity: 'error',
                        summary: '❌ Import terminé avec erreurs',
                        detail: `${importSummary.value.errors} erreur(s) rencontrée(s)`,
                        life: 5000
                    });
                } else {
                    toast.add({
                        severity: 'success',
                        summary: '✅ Importation réussie',
                        detail: `${importSummary.value.created} créé(s), ${importSummary.value.updated} mis à jour, ${importSummary.value.skipped} ignoré(s)`,
                        life: 5000
                    });
                }
            }

            file.value = null;
            const fileUpload = document.querySelector('.p-fileupload');
            if (fileUpload) {
                fileUpload.__vueParentComponent?.reset();
            }

            if (!importSummary.value?.hasErrors) {
                setTimeout(() => {
                    router.visit(route('militaires.index'));
                }, 3000);
            }
        },
        onError: (err) => {
            uploading.value = false;
            errors.value = err;
            toast.add({
                severity: 'error',
                summary: '❌ Erreur',
                detail: err.fichier || 'Erreur lors de l\'importation',
                life: 5000
            });
        }
    });
};

const downloadTemplate = () => {
    window.location.href = route('militaires.import.template.xlsx');
};

const downloadTemplateCSV = () => {
    window.location.href = route('militaires.import.template.csv');
};

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
