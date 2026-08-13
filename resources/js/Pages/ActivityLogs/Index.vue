<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Journal d'activités</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr>
                                    <th class="border-b px-4 py-2">Date & Heure</th>
                                    <th class="border-b px-4 py-2">Utilisateur</th>
                                    <th class="border-b px-4 py-2">Action</th>
                                    <th class="border-b px-4 py-2">Description</th>
                                    <th class="border-b px-4 py-2">Adresse IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50">
                                    <td class="border-b px-4 py-2 whitespace-nowrap text-sm text-gray-600">
                                        {{ new Date(log.created_at).toLocaleString('fr-FR') }}
                                    </td>
                                    <td class="border-b px-4 py-2 font-medium">
                                        {{ log.user ? log.user.name : 'Utilisateur supprimé' }}
                                    </td>
                                    <td class="border-b px-4 py-2">
                                        <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2 py-1 rounded">
                                            {{ log.action }}
                                        </span>
                                    </td>
                                    <td class="border-b px-4 py-2 text-sm">
                                        {{ log.description }}
                                    </td>
                                    <td class="border-b px-4 py-2 text-sm text-gray-500">
                                        {{ log.ip_address }}
                                    </td>
                                </tr>
                                <tr v-if="logs.data.length === 0">
                                    <td colspan="5" class="text-center py-4 text-gray-500">Aucune activité enregistrée.</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <!-- Simple Pagination Navigation -->
                        <div v-if="logs.links && logs.links.length > 3" class="mt-4 flex flex-wrap gap-1">
                            <template v-for="(link, k) in logs.links" :key="k">
                                <div
                                    v-if="link.url === null"
                                    class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
                                    v-html="link.label"
                                />
                                <Link
                                    v-else
                                    class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500"
                                    :class="{ 'bg-indigo-500 text-white hover:bg-indigo-600': link.active }"
                                    :href="link.url"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    logs: Object,
});
</script>
