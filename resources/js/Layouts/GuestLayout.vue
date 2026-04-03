<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-sky-500 via-sky-600 to-sky-700">
        <!-- Overlay décoratif -->
        <div class="absolute inset-0 bg-black/20"></div>
        
        <!-- Forme décorative en arrière-plan -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
        </div>
        
        <div class="relative z-10 w-full max-w-md mx-4">
            <!-- Logo et titre -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl shadow-lg mb-4">
                    <ApplicationLogo class="w-12 h-12 text-white" />
                </div>
                <h2 class="text-white text-xl font-semibold">Gestion Militaire</h2>
                <p class="text-white/70 text-sm mt-1">DTTIA</p>
            </div>
            
            <!-- Contenu du formulaire -->
            <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl overflow-hidden">
                <div class="p-6 sm:p-8">
                    <slot />
                </div>
            </div>
            
            <!-- Footer -->
            <div class="text-center mt-6 text-white/60 text-xs">
                Direction des Transmissions, des Telecommunications et de l'Informatique
            </div>
        </div>
    </div>
</template>

<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>