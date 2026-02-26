<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
        <Head title="Log in" />

        <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-950 px-4">
            <div class="w-full max-w-md bg-white dark:bg-gray-900 p-8 rounded-3xl shadow-2xl">

                <div v-if="status" class="mb-6 text-sm font-medium text-green-600 text-center">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Email -->
                    <div>
                        <InputLabel for="email" value="Email Address"  class="text-gray-600 dark:text-gray-200"/>
                        <TextInput
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            class="mt-2 w-full  rounded-xl border-gray-300 
                                   focus:ring-2 focus:ring-black dark:focus:ring-white"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Password -->
                    <div>
                        <InputLabel for="password" value="Password" class="text-gray-600 dark:text-gray-200"/>
                        <TextInput
                            id="password"
                            type="password"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            class="mt-2 w-full rounded-xl border-gray-300 
                                   focus:ring-2 focus:ring-black dark:focus:ring-white"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Remember + Forgot -->
                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-gray-600 dark:text-gray-200">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            Remember me
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="underline text-gray-600 dark:text-gray-400 hover:text-black dark:hover:text-white transition"
                        >
                            Forgot password?
                        </Link>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 text-lg font-semibold rounded-2xl 
                               bg-black text-white 
                               hover:bg-gray-800 
                               dark:bg-white dark:text-black 
                               dark:hover:bg-gray-200
                               transition-all duration-300 
                               shadow-lg hover:shadow-xl active:scale-95
                               disabled:opacity-50"
                    >
                        Log in
                    </button>

                </form>

            </div>
        </div>
</template>