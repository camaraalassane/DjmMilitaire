<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestion des Utilisateurs</h2>
                <Link
                    :href="route('users.create')"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    Nouvel Utilisateur
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ $page.props.flash.success }}
                </div>
                <div v-if="$page.props.flash.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ $page.props.flash.error }}
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr>
                                    <th class="border-b px-4 py-2">Nom</th>
                                    <th class="border-b px-4 py-2">Email</th>
                                    <th class="border-b px-4 py-2">Rôle</th>
                                    <th class="border-b px-4 py-2 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                                    <td class="border-b px-4 py-2">{{ user.name }}</td>
                                    <td class="border-b px-4 py-2">{{ user.email }}</td>
                                    <td class="border-b px-4 py-2">
                                        <span v-if="user.role === 'super_admin'" class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded">Super Administrateur</span>
                                        <span v-else class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">Administrateur</span>
                                    </td>
                                    <td class="border-b px-4 py-2 text-right flex justify-end gap-2">
                                        <Link
                                            :href="route('users.edit', user.id)"
                                            class="text-blue-600 hover:text-blue-900 font-medium"
                                        >
                                            Modifier
                                        </Link>
                                        <button
                                            v-if="user.id !== $page.props.auth.user.id"
                                            @click="confirmDelete(user)"
                                            class="text-red-600 hover:text-red-900 font-medium ml-4"
                                        >
                                            Supprimer
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="users.length === 0">
                                    <td colspan="4" class="text-center py-4 text-gray-500">Aucun utilisateur trouvé.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="userToDelete !== null" @close="userToDelete = null">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Êtes-vous sûr de vouloir supprimer {{ userToDelete?.name }} ?
                </h2>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="userToDelete = null">Annuler</SecondaryButton>
                    <DangerButton class="ml-3" @click="deleteUser" :class="{ 'opacity-25': processing }" :disabled="processing">
                        Supprimer
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

defineProps({
    users: Array,
});

const userToDelete = ref(null);
const processing = ref(false);
const form = useForm({});

const confirmDelete = (user) => {
    userToDelete.value = user;
};

const deleteUser = () => {
    processing.value = true;
    form.delete(route('users.destroy', userToDelete.value.id), {
        onSuccess: () => {
            userToDelete.value = null;
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        }
    });
};
</script>
