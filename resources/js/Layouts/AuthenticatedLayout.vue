<template>
    <div class="min-h-screen bg-gray-100">
        <Sidebar v-model:is-open="sidebarOpen" :is-mobile="isMobile" @close-mobile="closeMobileMenu" />

        <div
            :class="['transition-all duration-300', !isMobile && sidebarOpen ? 'lg:ml-64' : (!isMobile ? 'lg:ml-20' : '')]">

            <header class="bg-gradient-to-r from-sky-500 to-sky-700 shadow-sm z-10">
                <div class="flex justify-between items-center px-4 py-3">
                    <button @click="toggleSidebarMobile" class="lg:hidden text-white">
                        <i class="pi pi-bars text-xl"></i>
                    </button>

                    <div class="lg:hidden flex-1 text-center">
                        <h1 class="text-lg font-semibold text-white">{{ title }}</h1>
                    </div>

                    <h1 class="text-xl font-semibold text-white hidden lg:block">{{ title }}</h1>

                    <div class="lg:hidden w-8"></div>

                    <div class="flex items-center gap-4">
                        <!-- Lien Contrats -->
                        <button v-if="$page.props.auth.user.role !== 1" @click="showContrats" class="relative text-white hover:text-sky-100" title="Contrats">
                            <i class="pi pi-file text-xl"></i>
                        </button>

                        <button v-if="$page.props.auth.user.role !== 1" @click="showNotifications" class="relative text-white hover:text-sky-100"
                            title="Alertes">
                            <i class="pi pi-bell text-xl"></i>
                            <span v-if="alertesCount > 0"
                                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
                                {{ alertesCount > 9 ? '9+' : alertesCount }}
                            </span>
                        </button>

                        <div class="relative" ref="userMenuRef">
                            <button @click="toggleUserMenu"
                                class="flex items-center gap-2 text-white hover:text-sky-100">
                                <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                                    <i class="pi pi-user"></i>
                                </div>
                                <span class="text-sm hidden md:inline">{{ userName }}</span>
                                <i class="pi pi-chevron-down text-xs hidden md:inline"></i>
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

// Détection immédiate de l'environnement et de la taille de l'écran pour éviter le saut d'affichage
const isServer = typeof window === 'undefined';
const isMobile = ref(isServer ? false : window.innerWidth < 1024);
const sidebarOpen = ref(isServer ? false : window.innerWidth >= 1024);

const userMenuOpen = ref(false);
const userMenuRef = ref(null);

const user = computed(() => page.props.auth.user);
const userName = computed(() => user.value?.name || 'Utilisateur');
const alertesCount = computed(() => page.props.alertesCount || 0);
const title = computed(() => page.props.title || 'Suivi personnel');

const checkScreenSize = () => {
    const wasMobile = isMobile.value;
    isMobile.value = window.innerWidth < 1024;

    if (wasMobile && !isMobile.value) {
        sidebarOpen.value = true;
    }
    if (!wasMobile && isMobile.value) {
        sidebarOpen.value = false;
    }
};

const closeMobileMenu = () => {
    if (isMobile.value) {
        sidebarOpen.value = false;
    }
};

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

// Aller à la page des contrats
const showContrats = () => {
    router.visit(route('contrats.index'));
};

const logout = () => {
    router.post(route('logout'));
};

onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize);
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
