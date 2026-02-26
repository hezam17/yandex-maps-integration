<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  settings: Object,
})

const form = useForm({
  yandex_url: props.settings?.yandex_url ?? '',
})

const submit = () => {
  form.post('/settings')
}
</script>

<template>
  <AppLayout>
    <div class="p-8 max-w-xl">

      <h1 class="text-xl font-semibold text-gray-900 mb-6">Подключить Яндекс</h1>

      <form @submit.prevent="submit">

        <!-- Label + example link -->
        <div class="mb-2 flex items-center gap-2 text-sm text-gray-600">
          <span>Укажите ссылку на Яндекс, пример</span>
          <a
            href="https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/"
            target="_blank"
            class="text-[#1a56db] hover:underline truncate max-w-xs text-xs"
          >
            https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/
          </a>
        </div>

        <!-- Input -->
        <input
          v-model="form.yandex_url"
          type="url"
          class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#1a56db]/30 focus:border-[#1a56db] bg-white mb-1 transition"
          placeholder="https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/"
        />

        <!-- Validation error -->
        <p v-if="form.errors.yandex_url" class="text-red-500 text-xs mt-1 mb-3">
          {{ form.errors.yandex_url }}
        </p>

        <!-- Submit button -->
        <button
          type="submit"
          :disabled="form.processing"
          class="mt-4 bg-[#1a56db] hover:bg-[#1648c4] text-white text-sm font-medium px-6 py-2.5 rounded-lg disabled:opacity-60 disabled:cursor-not-allowed transition-colors"
        >
          {{ form.processing ? 'Сохранение…' : 'Сохранить' }}
        </button>

      </form>
    </div>
  </AppLayout>
</template>