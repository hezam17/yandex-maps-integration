<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
        <Head title="Register" />

        <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-950 px-4">
            <div class="w-full max-w-md bg-white dark:bg-gray-900 p-8 rounded-3xl shadow-2xl">

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Name -->
                    <div>
                        <InputLabel for="name" value="Full Name" class="text-gray-600 dark:text-gray-200"/>
                        <TextInput
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                            class="mt-2 w-full rounded-xl border-gray-300 
                                   focus:ring-2 focus:ring-black dark:focus:ring-white"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <!-- Email -->
                    <div>
                        <InputLabel for="email" value="Email Address" class="text-gray-600 dark:text-gray-200"/>
                        <TextInput
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            class="mt-2 w-full rounded-xl border-gray-300 
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
                            autocomplete="new-password"
                            class="mt-2 w-full rounded-xl border-gray-300 
                                   focus:ring-2 focus:ring-black dark:focus:ring-white"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" class="text-gray-600 dark:text-gray-200"/>
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            class="mt-2 w-full rounded-xl border-gray-300 
                                   focus:ring-2 focus:ring-black dark:focus:ring-white"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password_confirmation"
                        />
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between text-sm pt-2">
                        <Link
                            :href="route('login')"
                            class="underline text-gray-600 dark:text-gray-400 hover:text-black dark:hover:text-white transition"
                        >
                            Already have an account?
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 font-semibold rounded-2xl 
                                   bg-black text-white 
                                   hover:bg-gray-800 
                                   dark:bg-white dark:text-black 
                                   dark:hover:bg-gray-200
                                   transition-all duration-300 
                                   shadow-lg hover:shadow-xl active:scale-95
                                   disabled:opacity-50"
                        >
                            Register
                        </button>
                    </div>

                </form>

            </div>
        </div>
</template>