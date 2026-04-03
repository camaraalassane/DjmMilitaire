<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Sidebar -->
        <Sidebar v-model:is-open="sidebarOpen" />

        <!-- Main content -->
        <div :class="['transition-all duration-300', sidebarOpen ? 'lg:ml-64' : 'lg:ml-20']">
            <!-- Header -->
            <header class="bg-gradient-to-r from-sky-500 to-sky-700 shadow-sm sticky top-0 z-10">
                <div class="flex justify-between items-center px-4 py-3">
                    <button @click="toggleSidebarMobile" class="lg:hidden text-white">
                        <i class="pi pi-bars text-xl"></i>
                    </button>
                    <h1 class="text-xl font-semibold text-white">{{ title }}</h1>
                    <div class="flex items-center gap-4">
                        <!-- Notifications -->
                        <button @click="showNotifications" class="relative text-white hover:text-sky-100">
                            <i class="pi pi-bell text-xl"></i>
                            <span v-if="alertesCount > 0" 
                                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
                                {{ alertesCount > 9 ? '9+' : alertesCount }}
                            </span>
                        </button>
                        <!-- User menu -->
                        <div class="relative" ref="userMenuRef">
                            <button @click="toggleUserMenu" class="flex items-center gap-2 text-white hover:text-sky-100">
                                <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                                    <i class="pi pi-user"></i>
                                </div>
                                <span class="text-sm hidden md:inline">{{ userName }}</span>
                                <i class="pi pi-chevron-down text-xs"></i>
                            </button>
                            <div v-if="userMenuOpen" 
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-20 border border-gray-200">
                                <Link :href="route('profile.edit')" 
                                      class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="pi pi-user mr-2"></i> Mon profil
                                </Link>
                                <button @click="logout" 
                                        class="flex items-center w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="pi pi-sign-out mr-2"></i> Déconnexion
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';

const page = usePage();
const sidebarOpen = ref(true);
const userMenuOpen = ref(false);
const userMenuRef = ref(null);

const user = computed(() => page.props.auth.user);
const userName = computed(() => user.value?.name || 'Utilisateur');
const alertesCount = computed(() => page.props.alertesCount || 0);
const title = computed(() => page.props.title || 'Gestion Militaire');

// Fermer le menu quand on clique en dehors
const handleClickOutside = (event) => {
    if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
        userMenuOpen.value = false;
    }
};

const toggleUserMenu = () => {
    userMenuOpen.value = !userMenuOpen.value;
};

const toggleSidebarMobile = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const showNotifications = () => {
    router.visit(route('alertes.index'));
};

const logout = () => {
    router.post(route('logout'));
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
}
</style>