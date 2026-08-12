<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Mettre à jour le mot de passe</h2>
            <p class="mt-1 text-sm text-gray-600">
                Assurez-vous que votre compte utilise un mot de passe long et aléatoire pour rester sécurisé.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe actuel</label>
                <Password
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="w-full"
                    :feedback="false"
                    toggleMask
                    autocomplete="current-password"
                    :class="{ 'p-invalid': form.errors.current_password }"
                />
                <small v-if="form.errors.current_password" class="text-red-500 block mt-1">{{ form.errors.current_password }}</small>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                <Password
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="w-full"
                    toggleMask
                    autocomplete="new-password"
                    :class="{ 'p-invalid': form.errors.password }"
                />
                <small v-if="form.errors.password" class="text-red-500 block mt-1">{{ form.errors.password }}</small>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                <Password
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="w-full"
                    :feedback="false"
                    toggleMask
                    autocomplete="new-password"
                    :class="{ 'p-invalid': form.errors.password_confirmation }"
                />
                <small v-if="form.errors.password_confirmation" class="text-red-500 block mt-1">{{ form.errors.password_confirmation }}</small>
            </div>

            <div class="flex items-center gap-4">
                <Button type="submit" label="Enregistrer" icon="pi pi-save" :loading="form.processing" class="bg-sky-500 hover:bg-sky-600 border-sky-500 text-white" />

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-green-600 flex items-center gap-1">
                        <i class="pi pi-check"></i> Enregistré.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Password from 'primevue/password';
import Button from 'primevue/button';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors) => {
            if (errors.password) {
                form.reset('password', 'password_confirmation');
            }
            if (errors.current_password) {
                form.reset('current_password');
            }
        },
    });
};
</script>
