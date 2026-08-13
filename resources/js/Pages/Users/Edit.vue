<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Modifier Utilisateur : {{ user.name }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6 max-w-2xl">
                            <div>
                                <InputLabel for="name" value="Nom Complet" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.name"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    v-model="form.email"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div>
                                <InputLabel for="role" value="Rôle" />
                                <select
                                    id="role"
                                    v-model="form.role"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                    :disabled="user.id === $page.props.auth.user.id"
                                >
                                    <option value="" disabled>Sélectionner un rôle</option>
                                    <option :value="2">Administrateur (Gestionnaire)</option>
                                    <option :value="1">Super Administrateur (Gestion des accès)</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.role" />
                                <p v-if="user.id === $page.props.auth.user.id" class="mt-1 text-sm text-gray-500">
                                    Vous ne pouvez pas modifier votre propre rôle.
                                </p>
                            </div>

                            <div class="pt-4 border-t border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Modifier le mot de passe (Optionnel)</h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <InputLabel for="password" value="Nouveau mot de passe" />
                                        <TextInput
                                            id="password"
                                            type="password"
                                            class="mt-1 block w-full"
                                            v-model="form.password"
                                        />
                                        <InputError class="mt-2" :message="form.errors.password" />
                                    </div>

                                    <div>
                                        <InputLabel for="password_confirmation" value="Confirmer le mot de passe" />
                                        <TextInput
                                            id="password_confirmation"
                                            type="password"
                                            class="mt-1 block w-full"
                                            v-model="form.password_confirmation"
                                        />
                                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 pt-4">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Mettre à jour
                                </PrimaryButton>

                                <Link :href="route('users.index')" class="text-gray-600 hover:text-gray-900 underline text-sm">
                                    Annuler
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('users.update', props.user.id), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>
