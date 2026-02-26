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
    <div class="flex gap-6 p-8">

      <!-- ─── Left: reviews list ─── -->
      <div class="flex-1 min-w-0">

        <!-- Flash success -->
        <div
          v-if="flash?.success"
          class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm"
        >
          ✅ {{ flash.success }}
        </div>

        <!-- Source badge -->
        <div class="mb-5">
          <span class="inline-flex items-center gap-1.5 bg-white border border-gray-200 rounded-full px-4 py-1.5 text-sm font-medium text-gray-700 shadow-sm">
            <!-- Yandex pin icon -->
            <svg width="14" height="14" viewBox="0 0 24 24" fill="#fc3f1d">
              <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
            </svg>
            Яндекс Карты
          </span>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="space-y-4">
          <div v-for="i in 3" :key="i"
            class="bg-white rounded-2xl p-5 shadow-sm animate-pulse h-36"
          />
        </div>

        <!-- Error -->
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

        <!-- Reviews list -->
        <div v-else class="space-y-4">
          <div
            v-for="review in reviews"
            :key="review.id"
            class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100"
          >
            <!-- Top row: date | branch | pin | stars -->
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-3 text-sm text-gray-500">
                <span>{{ formatDate(review.date) }}</span>
                <span class="text-gray-300">·</span>
                <span class="font-medium text-gray-700">{{ review.branch ?? 'Филиал 1' }}</span>
                <!-- pin -->
                <svg width="14" height="14" viewBox="0 0 24 24" fill="#fc3f1d">
                  <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                </svg>
              </div>

              <!-- Stars (right-aligned) -->
              <div class="flex gap-0.5">
                <template v-for="s in 5" :key="s">
                  <svg width="18" height="18" viewBox="0 0 24 24"
                    :fill="s <= review.rating ? '#fbbf24' : '#e5e7eb'">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                  </svg>
                </template>
              </div>
            </div>

            <!-- Author + phone -->
            <div class="flex items-center gap-3 mb-2">
              <span class="font-semibold text-gray-900 text-sm">{{ review.author }}</span>
              <span v-if="review.phone" class="text-sm text-gray-400">{{ review.phone }}</span>
            </div>

            <!-- Review text -->
            <p class="text-sm text-gray-600 leading-relaxed">{{ review.text }}</p>
          </div>

          <!-- Empty state -->
          <div v-if="reviews.length === 0 && !loading"
            class="bg-white rounded-2xl p-12 text-center text-gray-400 shadow-sm"
          >
            Отзывов пока нет.
          </div>
        </div>

        <!-- ─── Pagination ─── -->
        <div v-if="lastPage > 1" class="flex justify-center items-center gap-2 mt-6">
          <button
            @click="page--" :disabled="page === 1"
            class="w-9 h-9 flex items-center justify-center rounded-lg bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2.5">
              <polyline points="15 18 9 12 15 6"/>
            </svg>
          </button>

          <span class="text-sm text-gray-500 px-2">
            {{ page }} / {{ lastPage }}
          </span>

          <button
            @click="page++" :disabled="page >= lastPage"
            class="w-9 h-9 flex items-center justify-center rounded-lg bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2.5">
              <polyline points="9 18 15 12 9 6"/>
            </svg>
          </button>
        </div>

      </div>

      <!-- ─── Right: Rating summary ─── -->
      <div class="w-56 shrink-0">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 sticky top-8">
          <!-- Big rating number -->
          <div class="flex items-center gap-2 mb-2">
            <span class="text-5xl font-bold text-gray-900">{{ rating }}</span>
          </div>

          <!-- Stars row -->
          <div class="flex gap-0.5 mb-3">
            <template v-for="s in 5" :key="s">
              <svg width="22" height="22" viewBox="0 0 24 24"
                :fill="s <= Math.round(rating) ? '#fbbf24' : '#e5e7eb'">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
            </template>
          </div>

          <!-- Divider -->
          <div class="border-t border-gray-100 my-3"/>

          <!-- Total reviews -->
          <p class="text-sm text-gray-500">
            Всего отзывов:
            <span class="font-semibold text-gray-800">{{ totalRevs.toLocaleString('ru-RU') }}</span>
          </p>
        </div>
      </div>

    </div>
  </AppLayout>
</template>