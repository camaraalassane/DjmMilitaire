<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-red-600">Supprimer le compte</h2>
            <p class="mt-1 text-sm text-gray-600">
                Une fois votre compte supprimé, toutes ses ressources et données seront définitivement effacées.
            </p>
        </header>

        <Button
            label="Supprimer le compte"
            icon="pi pi-trash"
            class="p-button-danger"
            @click="confirmUserDeletion"
        />

        <Dialog
            v-model:visible="confirmingUserDeletion"
            modal
            header="Êtes-vous sûr de vouloir supprimer votre compte ?"
            :style="{ width: '30rem' }"
        >
            <p class="text-sm text-gray-600 mb-4">
                Veuillez saisir votre mot de passe pour confirmer la suppression définitive de votre compte.
            </p>

            <div class="mb-4">
                <label for="delete_password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                <Password
                    id="delete_password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="w-full"
                    :feedback="false"
                    placeholder="Votre mot de passe"
                    @keyup.enter="deleteUser"
                    :class="{ 'p-invalid': form.errors.password }"
                />
                <small v-if="form.errors.password" class="text-red-500 block mt-1">{{ form.errors.password }}</small>
            </div>

            <template #footer>
                <Button label="Annuler" icon="pi pi-times" class="p-button-text text-gray-600" @click="closeModal" />
                <Button
                    label="Supprimer définitivement"
                    icon="pi pi-trash"
                    class="p-button-danger"
                    :loading="form.processing"
                    @click="deleteUser"
                />
            </template>
        </Dialog>
    </section>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import Password from 'primevue/password';
import Button from 'primevue/button';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.$el?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
};
</script>
