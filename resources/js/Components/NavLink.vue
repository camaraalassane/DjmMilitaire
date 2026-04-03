<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
        default: false,
    },
});

const classes = computed(() =>
    props.active
        ? 'flex items-center px-3 py-2 text-sm font-medium rounded-lg bg-sky-700 text-white transition-colors duration-150 ease-in-out'
        : 'flex items-center px-3 py-2 text-sm font-medium text-white hover:text-white hover:bg-sky-700 rounded-lg transition-colors duration-150 ease-in-out',
);
</script>