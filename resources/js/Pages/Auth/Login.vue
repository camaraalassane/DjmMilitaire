<template>
    <div class="h-screen flex items-center justify-center bg-gradient-to-br from-sky-500 via-sky-600 to-sky-700 relative overflow-hidden">
        <!-- Formes décoratives animées -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white/10 rounded-full blur-3xl animate-pulse animation-delay-2000"></div>
        </div>
        
        <div class="relative z-10 w-full max-w-md mx-4">
            <!-- Logo et titre - version ultra compacte -->
            <div class="text-center mb-4 animate-fade-in-down">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl shadow-lg mb-2 transition-all duration-300 hover:scale-105">
                    <i class="pi pi-shield text-2xl text-white"></i>
                </div>
                <h1 class="text-white text-xl font-bold tracking-tight">Gestion Militaire</h1>
                <p class="text-white/70 text-[11px] mt-0.5">DTTIA</p>
            </div>
            
            <!-- Carte de connexion - ultra compacte -->
            <Card class="shadow-2xl border-0 transition-all duration-300">
                <template #header>
                    <div class="text-center py-3 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-sky-500 to-sky-600 rounded-lg shadow-md mb-1">
                            <i class="pi pi-user text-base text-white"></i>
                        </div>
                        <h2 class="text-lg font-bold text-gray-800">Se connecter</h2>
                        <p class="text-gray-500 text-[11px] mt-0.5">Accédez à votre espace</p>
                    </div>
                </template>

                <template #content>
                    <form @submit.prevent="submit" class="space-y-3">
                        <!-- Session Status -->
                        <Message v-if="status" severity="success" :closable="false" class="mb-2 text-xs">
                            <div class="flex items-center gap-1">
                                <i class="pi pi-check-circle text-green-500 text-xs"></i>
                                <span>{{ status }}</span>
                            </div>
                        </Message>

                        <!-- Email -->
                        <div class="field">
                            <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center gap-1">
                                <i class="pi pi-envelope text-sky-500 text-[11px]"></i>
                                Email
                            </label>
                            <div class="relative group">
                                <InputText 
                                    v-model="form.email" 
                                    type="email" 
                                    class="w-full pl-8 pr-3 py-2 text-sm border-gray-200 focus:border-sky-400 focus:ring-sky-400"
                                    :class="{ 'p-invalid': form.errors.email }"
                                    placeholder="exemple@domaine.com"
                                    autofocus />
                                <i class="pi pi-envelope absolute left-2.5 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs group-focus-within:text-sky-500"></i>
                            </div>
                            <small v-if="form.errors.email" class="text-red-500 text-[11px] mt-1 flex items-center gap-1">
                                <i class="pi pi-exclamation-circle text-[10px]"></i>
                                {{ form.errors.email }}
                            </small>
                        </div>

                        <!-- Password -->
                        <div class="field">
                            <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center gap-1">
                                <i class="pi pi-lock text-sky-500 text-[11px]"></i>
                                Mot de passe
                            </label>
                            <div class="relative group">
                                <InputText 
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="form.password" 
                                    class="w-full pl-8 pr-8 py-2 text-sm border-gray-200 focus:border-sky-400 focus:ring-sky-400"
                                    :class="{ 'p-invalid': form.errors.password }"
                                    placeholder="••••••••" />
                                <i class="pi pi-lock absolute left-2.5 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs group-focus-within:text-sky-500"></i>
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-2.5 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-sky-500">
                                    <i :class="showPassword ? 'pi pi-eye-slash text-xs' : 'pi pi-eye text-xs'"></i>
                                </button>
                            </div>
                            <small v-if="form.errors.password" class="text-red-500 text-[11px] mt-1 flex items-center gap-1">
                                <i class="pi pi-exclamation-circle text-[10px]"></i>
                                {{ form.errors.password }}
                            </small>
                        </div>

                        <!-- Remember & Forgot -->
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-1 cursor-pointer group">
                                <Checkbox v-model="form.remember" :binary="true" class="scale-75" />
                                <span class="text-[11px] text-gray-600 group-hover:text-gray-800">Se souvenir</span>
                            </label>
                            <Link 
                                v-if="canResetPassword" 
                                :href="route('password.request')" 
                                class="text-[11px] text-sky-600 hover:text-sky-700 hover:underline">
                                Mot de passe oublié ?
                            </Link>
                        </div>

                        <!-- Submit Button -->
                        <Button 
                            type="submit" 
                            :label="form.processing ? 'Connexion...' : 'Se connecter'"
                            :icon="form.processing ? 'pi pi-spinner pi-spin' : 'pi pi-sign-in'"
                            :loading="form.processing"
                            class="w-full bg-gradient-to-r from-sky-500 to-sky-600 border-0 text-white py-2 text-sm font-semibold" />
                        
                        <!-- Footer compact -->
                        <div class="text-center pt-2 border-t border-gray-100">
                            <div class="flex justify-center gap-2 text-[10px] text-gray-400">
                                <span>DTTIA</span>
                                <span>•</span>
                                <span>Gestion Militaire</span>
                                <span>•</span>
                                <span>v1.0</span>
                            </div>
                        </div>
                    </form>
                </template>
            </Card>
            
            <!-- Footer ultra compact -->
            <div class="text-center mt-3 animate-fade-in-up">
                <p class="text-white/40 text-[10px]">© {{ new Date().getFullYear() }} - Direction des Transmissions, des Telecommunications et de l'Informatique</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';

defineProps({
    canResetPassword: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: null,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<style scoped>
@keyframes fade-in-down {
    from {
        opacity: 0;
        transform: translateY(-15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-down {
    animation: fade-in-down 0.5s ease-out;
}

.animate-fade-in-up {
    animation: fade-in-up 0.5s ease-out;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

:deep(.p-card) {
    border-radius: 0.875rem;
    overflow: hidden;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.98);
}

:deep(.p-card .p-card-header) {
    padding: 0;
}

:deep(.p-card .p-card-content) {
    padding: 1rem 1.25rem;
}

:deep(.p-inputtext) {
    border-radius: 0.5rem;
    font-size: 0.8125rem;
    padding: 0.5rem 0.75rem;
}

:deep(.p-inputtext:focus) {
    box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.1);
}

:deep(.p-checkbox .p-checkbox-box) {
    width: 0.875rem;
    height: 0.875rem;
    border-radius: 0.25rem;
}

:deep(.p-checkbox .p-checkbox-box.p-highlight) {
    background: #0284c7;
    border-color: #0284c7;
}

:deep(.p-button) {
    border-radius: 0.5rem;
    padding: 0.5rem;
    font-size: 0.8125rem;
}

:deep(.p-message) {
    border-radius: 0.5rem;
}

:deep(.p-message .p-message-wrapper) {
    padding: 0.375rem 0.625rem;
}

/* Ajustements pour les écrans de hauteur limitée */
@media (max-height: 680px) {
    :deep(.p-card .p-card-content) {
        padding: 0.75rem 1rem;
    }
    
    .mb-4 {
        margin-bottom: 0.5rem;
    }
    
    .mt-3 {
        margin-top: 0.5rem;
    }
    
    .space-y-3 > :not([hidden]) ~ :not([hidden]) {
        margin-top: 0.5rem;
    }
}
</style>