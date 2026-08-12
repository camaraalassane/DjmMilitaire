<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Informations du profil</h2>
            <p class="mt-1 text-sm text-gray-600">
                Mettez à jour les informations de votre compte et votre adresse email.
            </p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-6 space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                <InputText
                    id="name"
                    type="text"
                    class="w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    :class="{ 'p-invalid': form.errors.name }"
                />
                <small v-if="form.errors.name" class="text-red-500 block mt-1">{{ form.errors.name }}</small>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse Email</label>
                <InputText
                    id="email"
                    type="email"
                    class="w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    :class="{ 'p-invalid': form.errors.email }"
                />
                <small v-if="form.errors.email" class="text-red-500 block mt-1">{{ form.errors.email }}</small>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-gray-800">
                    Votre adresse email n'est pas vérifiée.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Cliquez ici pour renvoyer l'email de vérification.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600"
                >
                    Un nouveau lien de vérification a été envoyé à votre adresse email.
                </div>
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
import { useForm, usePage, Link } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>
