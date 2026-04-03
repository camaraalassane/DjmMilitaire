<template>
    <aside :class="['bg-sky-600 text-white h-screen fixed left-0 top-0 z-40 transition-all duration-300', 
                   isOpen ? 'w-64' : 'w-20']">
        <div class="flex flex-col h-full">
            <!-- Logo et titre -->
            <div class="flex items-center justify-center p-4 border-b border-sky-500 relative">
                <img v-if="logoExists" 
                     :src="logoUrl" 
                     alt="DTTIA" 
                     class="h-12 w-auto transition-all duration-300"
                     :class="isOpen ? 'mr-2' : ''" />
                <h3 v-if="isOpen" class="text-white font-bold text-lg ml-2">DTTIA</h3>
                <button @click="toggleSidebar" 
                        class="absolute -right-3 top-6 bg-sky-700 rounded-full p-1 text-white hover:bg-sky-800 transition-colors">
                    <i :class="isOpen ? 'pi pi-chevron-left' : 'pi pi-chevron-right'" 
                       class="text-sm"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1">
                    <li v-for="item in menuItems" :key="item.name">
                        <NavLink :href="item.href" :active="isActive(item.href)">
                            <template #default>
                                <div class="flex items-center justify-between w-full">
                                    <div class="flex items-center">
                                        <i :class="item.icon + ' text-xl w-5'"></i>
                                        <span v-if="isOpen" class="ml-3">{{ item.name }}</span>
                                    </div>
                                    <span v-if="item.name === 'Alertes' && alertesCount > 0" 
                                          class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full ml-2">
                                        {{ alertesCount > 99 ? '99+' : alertesCount }}
                                    </span>
                                </div>
                            </template>
                        </NavLink>
                    </li>
                </ul>
            </nav>

            <!-- Déconnexion -->
            <div class="border-t border-sky-500 p-4">
                <button @click="logout" 
                        class="w-full flex items-center text-white hover:bg-sky-700 rounded-lg px-3 py-2 transition-colors">
                    <i class="pi pi-sign-out text-xl"></i>
                    <span v-if="isOpen" class="ml-3">Déconnexion</span>
                </button>
            </div>
        </div>
    </aside>

    <!-- Overlay pour mobile -->
    <div v-if="isOpen && isMobile" 
         @click="toggleSidebar"
         class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"></div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import NavLink from './NavLink.vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['update:isOpen']);

const page = usePage();
const isMobile = ref(false);
const alertesCount = computed(() => page.props.alertesCount || 0);

// Vérifier si le logo existe
const logoExists = ref(false);
const logoUrl = ref('/images/logo-dttia.png');

const checkScreenSize = () => {
    isMobile.value = window.innerWidth < 1024;
    if (isMobile.value) {
        emit('update:isOpen', false);
    } else {
        emit('update:isOpen', true);
    }
};

const toggleSidebar = () => {
    emit('update:isOpen', !props.isOpen);
};

// Menu items avec les bonnes icônes PrimeIcons
const menuItems = [
    { name: 'Tableau de bord', href: '/dashboard', icon: 'pi pi-chart-line' },        // Changé: speedometer -> chart-line
    { name: 'Militaires', href: '/militaires', icon: 'pi pi-users' },
    { name: 'Alertes', href: '/alertes', icon: 'pi pi-bell' },
    { name: 'Éligibilités', href: '/eligibilites', icon: 'pi pi-check-circle' },
    { name: 'Grades', href: '/grades', icon: 'pi pi-star' },
    { name: 'Certificats', href: '/certificats', icon: 'pi pi-file-pdf' }              // Changé: award -> file-pdf
];

const isActive = (href) => {
    return window.location.pathname === href || window.location.pathname.startsWith(href + '/');
};

const logout = () => {
    router.post(route('logout'));
};

onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
    
    fetch(logoUrl.value, { method: 'HEAD' })
        .then(res => {
            logoExists.value = res.ok;
        })
        .catch(() => {
            logoExists.value = false;
        });
});

onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize);
});
</script>

<style scoped>
/* Scrollbar personnalisée pour le thème sky blue */
::-webkit-scrollbar {
    width: 5px;
}

::-webkit-scrollbar-track {
    background: #0284c7;
}

::-webkit-scrollbar-thumb {
    background: #0e7490;
    border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
    background: #155e75;
}

/* Style pour les icônes */
.pi {
    font-size: 1.25rem;
}
</style>