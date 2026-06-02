<template>
    <div class="h-screen flex items-center justify-center bg-gradient-to-br from-sky-500 via-sky-600 to-sky-700 relative overflow-hidden">
        <!-- Formes décoratives animées -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white/10 rounded-full blur-3xl animate-pulse animation-delay-2000"></div>
        </div>
        
        <div class="relative z-10 w-full max-w-md mx-4">
            <!-- Logo et titre -->
            <div class="text-center mb-4 animate-fade-in-down">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl shadow-lg mb-2 transition-all duration-300 hover:scale-105">
                    <i class="pi pi-shield text-2xl text-white"></i>
                </div>
                <h1 class="text-white text-xl font-bold tracking-tight">Suivi personnel</h1>
                <p class="text-white/70 text-[11px] mt-0.5">Créer un compte</p>
            </div>
            
            <!-- Carte d'inscription -->
            <Card class="shadow-2xl border-0 transition-all duration-300">
                <template #header>
                    <div class="text-center py-3 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-sky-500 to-sky-600 rounded-lg shadow-md mb-1">
                            <i class="pi pi-user-plus text-base text-white"></i>
                        </div>
                        <h2 class="text-lg font-bold text-gray-800">Inscription</h2>
                        <p class="text-gray-500 text-[11px] mt-0.5">Créez votre compte</p>
                    </div>
                </template>

                <template #content>
                    <form @submit.prevent="submit" class="space-y-3">
                        <!-- Nom -->
                        <div class="field">
                            <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center gap-1">
                                <i class="pi pi-user text-sky-500 text-[11px]"></i>
                                Nom complet
                            </label>
                            <div class="relative">
                                <InputText 
                                    id="name"
                                    v-model="form.name" 
                                    type="text" 
                                    class="w-full pl-8 pr-3 py-2 text-sm border-gray-200 focus:border-sky-400 focus:ring-sky-400"
                                    :class="{ 'p-invalid': form.errors.name }"
                                    placeholder="Votre nom complet"
                                    required
                                    autofocus />
                            </div>
                            <small v-if="form.errors.name" class="text-red-500 text-[11px] mt-1 flex items-center gap-1">
                                <i class="pi pi-exclamation-circle text-[10px]"></i>
                                {{ form.errors.name }}
                            </small>
                        </div>

                        <!-- Email -->
                        <div class="field">
                            <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center gap-1">
                                <i class="pi pi-envelope text-sky-500 text-[11px]"></i>
                                Email
                            </label>
                            <div class="relative">
                                <InputText 
                                    id="email"
                                    v-model="form.email" 
                                    type="email" 
                                    class="w-full pl-8 pr-3 py-2 text-sm border-gray-200 focus:border-sky-400 focus:ring-sky-400"
                                    :class="{ 'p-invalid': form.errors.email }"
                                    placeholder="exemple@domaine.com"
                                    required />
                            </div>
                            <small v-if="form.errors.email" class="text-red-500 text-[11px] mt-1 flex items-center gap-1">
                                <i class="pi pi-exclamation-circle text-[10px]"></i>
                                {{ form.errors.email }}
                            </small>
                        </div>

                        <!-- Mot de passe -->
                        <div class="field">
                            <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center gap-1">
                                <i class="pi pi-lock text-sky-500 text-[11px]"></i>
                                Mot de passe
                            </label>
                            <div class="relative">
                                <InputText 
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="form.password" 
                                    class="w-full pl-8 pr-8 py-2 text-sm border-gray-200 focus:border-sky-400 focus:ring-sky-400"
                                    :class="{ 'p-invalid': form.errors.password }"
                                    placeholder="••••••••"
                                    required />
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-2.5 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-sky-500">
                                    <i :class="showPassword ? 'pi pi-eye-slash text-xs' : 'pi pi-eye text-xs'"></i>
                                </button>
                            </div>
                            <small v-if="form.errors.password" class="text-red-500 text-[11px] mt-1 flex items-center gap-1">
                                <i class="pi pi-exclamation-circle text-[10px]"></i>
                                {{ form.errors.password }}
                            </small>
                        </div>

                        <!-- Confirmation mot de passe -->
                        <div class="field">
                            <label class="text-xs font-semibold text-gray-700 mb-1 flex items-center gap-1">
                                <i class="pi pi-lock text-sky-500 text-[11px]"></i>
                                Confirmer le mot de passe
                            </label>
                            <div class="relative">
                                <InputText 
                                    id="password_confirmation"
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    v-model="form.password_confirmation" 
                                    class="w-full pl-8 pr-8 py-2 text-sm border-gray-200 focus:border-sky-400 focus:ring-sky-400"
                                    :class="{ 'p-invalid': form.errors.password_confirmation }"
                                    placeholder="••••••••"
                                    required />
                                <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-2.5 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-sky-500">
                                    <i :class="showConfirmPassword ? 'pi pi-eye-slash text-xs' : 'pi pi-eye text-xs'"></i>
                                </button>
                            </div>
                            <small v-if="form.errors.password_confirmation" class="text-red-500 text-[11px] mt-1 flex items-center gap-1">
                                <i class="pi pi-exclamation-circle text-[10px]"></i>
                                {{ form.errors.password_confirmation }}
                            </small>
                        </div>

                        <!-- Boutons -->
                        <div class="flex items-center justify-between gap-3 pt-2">
                            <Link 
                                :href="route('login')" 
                                class="text-[11px] text-sky-600 hover:text-sky-700 hover:underline">
                                Déjà inscrit ? Se connecter
                            </Link>

                            <Button 
                                type="submit" 
                                :label="form.processing ? 'Inscription...' : 'S\'inscrire'"
                                :icon="form.processing ? 'pi pi-spinner pi-spin' : 'pi pi-user-plus'"
                                :loading="form.processing"
                                :disabled="form.processing"
                                class="bg-gradient-to-r from-sky-500 to-sky-600 border-0 text-white py-2 px-4 text-sm font-semibold" />
                        </div>
                        
                        <!-- Footer -->
                        <div class="text-center pt-3 border-t border-gray-100">
                            <div class="flex justify-center gap-2 text-[10px] text-gray-400">
                                <span>Suivi personnel</span>
                                <span>•</span>
                                <span>Gestion militaire</span>
                                <span>•</span>
                                <span>v1.0</span>
                            </div>
                        </div>
                    </form>
                </template>
            </Card>
            
            <!-- Footer -->
            <div class="text-center mt-3 animate-fade-in-up">
                <p class="text-white/40 text-[10px]">© {{ new Date().getFullYear() }} - Suivi personnel - Application de gestion</p>
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

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
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

:deep(.p-button) {
    border-radius: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 0.8125rem;
}

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