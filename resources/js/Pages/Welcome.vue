<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>

    <Head title="Welcome" />

    <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-950">
        <div class="flex flex-col sm:flex-row gap-4">

            <Link v-if="canLogin" :href="route('login')" class="px-8 py-3 text-lg font-semibold rounded-2xl bg-black text-white 
                       hover:bg-gray-800 transition-all duration-300 
                       shadow-lg hover:shadow-xl active:scale-95 text-center">
                Log in </Link>

            <Link v-if="canRegister" :href="route('register')" class="px-8 py-3 text-lg font-semibold rounded-2xl border-2 border-black 
                       text-black dark:text-white dark:border-white
                       hover:bg-black hover:text-white 
                       dark:hover:bg-white dark:hover:text-black
                       transition-all duration-300 
                       shadow-lg hover:shadow-xl active:scale-95 text-center">
                Register
            </Link>

        </div>
    </div>
</template>