<script setup>
import { ref, watch, onMounted } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'

// ─── State ───────────────────────────────────────────
const rating      = ref(0)
const totalRevs   = ref(0)
const reviews     = ref([])
const page        = ref(1)
const perPage     = 10
const lastPage    = ref(1)
const loading     = ref(false)
const error       = ref(null)

// Flash message از Inertia
const flash = usePage().props.flash

// ─── Fetch ───────────────────────────────────────────
const fetchReviews = async () => {
  loading.value = true
  error.value   = null
  try {
    const res = await axios.get('/reviews/data', {
      params: { page: page.value, per_page: perPage }
    })
    rating.value    = res.data.rating
    totalRevs.value = res.data.total_reviews
    reviews.value   = res.data.reviews
    lastPage.value  = res.data.pagination?.last_page ?? 1
  } catch (e) {
    error.value = e.response?.data?.error ?? 'Unable to load reviews.'
  } finally {
    loading.value = false
  }
}

watch(page, fetchReviews)
onMounted(fetchReviews)

// ─── Helpers ─────────────────────────────────────────
const formatDate = (dateStr) => {
  if (!dateStr) return '—'
  const d = new Date(dateStr)
  return d.toLocaleDateString('ru-RU', {
    day:    '2-digit',
    month:  '2-digit',
    year:   'numeric',
  }) + ' ' + d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <AppLayout>
    <div class="flex flex-col lg:flex-row gap-6 p-4 md:p-8 max-w-7xl mx-auto">

      <div class="flex-1 min-w-0 order-2 lg:order-1">

        <div
          v-if="flash?.success"
          class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm shadow-sm"
        >
          ✅ {{ flash.success }}
        </div>

        <div class="mb-5 flex justify-center lg:justify-start">
          <span class="inline-flex items-center gap-1.5 bg-white border border-gray-200 rounded-full px-4 py-2 text-sm font-medium text-gray-700 shadow-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="#fc3f1d">
              <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
            </svg>
            Яндекс Карты
          </span>
        </div>

        <div v-if="loading" class="space-y-4">
          <div v-for="i in 3" :key="i"
            class="bg-white rounded-2xl p-5 shadow-sm animate-pulse h-36"
          />
        </div>

        <div v-else-if="error"
          class="bg-red-50 border border-red-200 text-red-600 rounded-2xl p-6 text-sm"
        >
          ⚠️ {{ error }}
          <div class="mt-3">
            <Link href="/settings" class="text-red-700 underline font-medium">
              Перейти в настройки
            </Link>
          </div>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="review in reviews"
            :key="review.id"
            class="bg-white rounded-2xl p-4 md:p-5 shadow-sm border border-gray-100 hover:border-gray-200 transition-colors"
          >
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
              <div class="flex flex-wrap items-center gap-2 text-xs md:text-sm text-gray-500">
                <span class="bg-gray-50 px-2 py-1 rounded-md">{{ formatDate(review.date) }}</span>
                <span class="hidden sm:inline text-gray-300">·</span>
                <div class="flex items-center gap-1">
                   <svg width="12" height="12" viewBox="0 0 24 24" fill="#fc3f1d">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                  </svg>
                  <span class="font-medium text-gray-700">{{ review.branch ?? 'Филиал 1' }}</span>
                </div>
              </div>

              <div class="flex gap-0.5 bg-gray-50 sm:bg-transparent p-1 sm:p-0 rounded-lg w-fit">
                <template v-for="s in 5" :key="s">
                  <svg class="w-4 h-4 md:w-4.5 md:h-4.5" viewBox="0 0 24 24"
                    :fill="s <= review.rating ? '#fbbf24' : '#e5e7eb'">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                  </svg>
                </template>
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-3">
              <span class="font-bold text-gray-900 text-sm md:text-base">{{ review.author }}</span>
              <span v-if="review.phone" class="text-xs md:text-sm bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-medium">
                {{ review.phone }}
              </span>
            </div>

            <p class="text-sm text-gray-600 leading-relaxed break-words">
              {{ review.text }}
            </p>
          </div>

          <div v-if="reviews.length === 0 && !loading"
            class="bg-white rounded-2xl p-12 text-center text-gray-400 shadow-sm"
          >
            Отзывов пока нет.
          </div>
        </div>

        <div v-if="lastPage > 1" class="flex justify-center items-center gap-4 mt-8">
          <button
            @click="page--" :disabled="page === 1"
            class="w-11 h-11 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 shadow-sm hover:bg-gray-50 disabled:opacity-30 transition-all active:scale-95"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="15 18 9 12 15 6"/>
            </svg>
          </button>

          <span class="font-bold text-gray-700 bg-white px-4 py-2 rounded-lg border border-gray-100 shadow-sm">
            {{ page }} <span class="text-gray-400 font-normal mx-1">/</span> {{ lastPage }}
          </span>

          <button
            @click="page++" :disabled="page >= lastPage"
            class="w-11 h-11 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-600 shadow-sm hover:bg-gray-50 disabled:opacity-30 transition-all active:scale-95"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="9 18 15 12 9 6"/>
            </svg>
          </button>
        </div>

      </div>

      <div class="w-full lg:w-64 shrink-0 order-1 lg:order-2">
        <div class="bg-white rounded-2xl p-6 shadow-md lg:shadow-sm border border-gray-100 lg:sticky lg:top-8 flex lg:flex-col items-center lg:items-start justify-between lg:justify-start gap-4">
          
          <div class="flex flex-col lg:w-full">
            <div class="flex items-center gap-2 mb-1">
              <span class="text-4xl md:text-5xl font-black text-gray-900">{{ rating }}</span>
              <div class="lg:hidden flex gap-0.5">
                 <svg width="20" height="20" viewBox="0 0 24 24" fill="#fbbf24">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
              </div>
            </div>

            <div class="hidden lg:flex gap-0.5 mb-3">
              <template v-for="s in 5" :key="s">
                <svg width="20" height="20" viewBox="0 0 24 24"
                  :fill="s <= Math.round(rating) ? '#fbbf24' : '#e5e7eb'">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
              </template>
            </div>
            
             <p class="text-xs md:text-sm text-gray-500 whitespace-nowrap">
              Всего отзывов:
              <span class="font-bold text-gray-800">{{ totalRevs.toLocaleString('ru-RU') }}</span>
            </p>
          </div>

          <div class="hidden lg:block border-t border-gray-100 my-3 w-full"/>

          <div class="lg:w-full">
             <button class="bg-gray-900 text-white text-xs font-bold py-2 px-4 rounded-xl w-full lg:mt-2">
               Обновить
             </button>
          </div>
          
        </div>
      </div>

    </div>
  </AppLayout>
</template>