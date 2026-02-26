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
    <div class="p-4 md:p-8 max-w-2xl mx-auto">

      <div class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-gray-100">

        <h1 class="text-xl md:text-2xl font-bold text-gray-900 mb-2">Подключить Яндекс</h1>
        <p class="text-sm text-gray-500 mb-8">Настройте интеграцию с Яндекс Картами для автоматического получения
          отзывов</p>

        <form @submit.prevent="submit" class="space-y-6">

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Ссылка на страницу отзывов
            </label>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-4">
              <div class="flex flex-col sm:flex-row sm:items-center gap-2 text-xs md:text-sm text-blue-700">
                <span class="font-medium shrink-0 italic">Пример:</span>
                <a href="https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/" target="_blank"
                  class="underline break-all opacity-80 hover:opacity-100 transition-opacity">
                  https://yandex.ru/maps/org/samoye_populyarnoye_kafe/1010501395/reviews/
                </a>
              </div>
            </div>

            <div class="relative">
              <input v-model="form.yandex_url" type="url"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-[#1a56db]/10 focus:border-[#1a56db] bg-gray-50/50 transition-all"
                placeholder="Вставьте ссылку здесь..." />

              <transition name="fade">
                <p v-if="form.errors.yandex_url" class="text-red-500 text-xs mt-2 flex items-center gap-1">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                  </svg>
                  {{ form.errors.yandex_url }}
                </p>
              </transition>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button type="submit" :disabled="form.processing"
              class="w-full sm:w-auto bg-[#1a56db] hover:bg-[#1648c4] text-white text-sm font-bold px-8 py-3.5 rounded-xl disabled:opacity-60 disabled:cursor-not-allowed transition-all shadow-md shadow-blue-200 active:scale-95 flex items-center justify-center gap-2">
              <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              {{ form.processing ? 'Сохранение…' : 'Сохранить изменения' }}
            </button>
          </div>

        </form>
      </div>

      <div class="mt-6 px-4 py-4 bg-gray-100/50 rounded-2xl border border-dashed border-gray-200">
        <p class="text-xs text-gray-500 leading-relaxed text-center">
          💡 <strong>Как найти ссылку?</strong> Откройте Яндекс Карты, найдите вашу организацию, перейдите в раздел
          «Отзывы» и скопируйте ссылку из адресной строки браузера.
        </p>
      </div>

    </div>
  </AppLayout>
</template>