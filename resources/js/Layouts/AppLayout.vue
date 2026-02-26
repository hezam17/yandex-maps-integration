<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page  = usePage()
const user  = computed(() => page.props.auth?.user)
</script>

<template>
  <div class="flex min-h-screen bg-[#f4f6f9]">

    <!-- ─── Sidebar ─── -->
    <aside class="w-56 bg-white border-r border-gray-100 flex flex-col fixed inset-y-0 left-0 z-20">

      <!-- Logo -->
      <div class="h-14 flex items-center px-5 border-b border-gray-100">
        <span class="flex items-center gap-2 font-bold text-[#1a56db] text-lg tracking-tight">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <polygon points="10,2 18,18 2,18" fill="#1a56db"/>
          </svg>
          Daily Grow
        </span>
      </div>

      <!-- Account name -->
      <div class="px-5 py-3 text-xs text-gray-400 font-medium border-b border-gray-100">
        {{ user?.name ?? 'Название аккаунта' }}
      </div>

      <!-- Navigation -->
      <nav class="flex-1 p-3 space-y-0.5">

        <!-- Отзывы group -->
        <div class="mb-1">
          <Link
            href="/reviews"
            class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-semibold w-full"
            :class="$page.url.startsWith('/reviews')
              ? 'bg-[#eff4ff] text-[#1a56db]'
              : 'text-gray-700 hover:bg-gray-50'"
          >
            <!-- icon -->
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
              :stroke="$page.url.startsWith('/reviews') ? '#1a56db' : '#6b7280'"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            Отзывы
          </Link>
        </div>

        <!-- Sub-items -->
        <div class="ml-3 space-y-0.5">
          <Link
            href="/reviews"
            class="block px-3 py-1.5 rounded-md text-sm"
            :class="$page.url === '/reviews'
              ? 'text-[#1a56db] bg-[#eff4ff] font-medium'
              : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
          >
            Отзывы
          </Link>
          <Link
            href="/settings"
            class="block px-3 py-1.5 rounded-md text-sm"
            :class="$page.url === '/settings'
              ? 'text-[#1a56db] bg-[#eff4ff] font-medium'
              : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
          >
            Настройка
          </Link>
        </div>
      </nav>

      <!-- Logout at bottom -->
      <div class="p-3 border-t border-gray-100">
        <Link
          href="/logout" method="post" as="button"
          class="w-full flex items-center justify-end px-2 py-2 text-gray-400 hover:text-gray-600 transition-colors"
          title="Выйти"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </Link>
      </div>
    </aside>

    <!-- ─── Main content ─── -->
    <main class="flex-1 ml-56 min-h-screen">
      <slot />
    </main>

  </div>
</template>