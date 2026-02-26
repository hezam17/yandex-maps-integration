<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const page  = usePage()
const user  = computed(() => page.props.auth?.user)

// حالة القائمة في الجوال
const isSidebarOpen = ref(false)

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}
</script>

<template>
  <div class="flex min-h-screen bg-[#f4f6f9]">
    
    <div 
      v-if="isSidebarOpen" 
      @click="isSidebarOpen = false"
      class="fixed inset-0 bg-black/40 z-30 lg:hidden transition-opacity"
    ></div>

    <aside 
      class="w-64 bg-white border-r border-gray-100 flex flex-col fixed inset-y-0 left-0 z-40 transform transition-transform duration-300 ease-in-out lg:translate-x-0"
      :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="h-16 flex items-center justify-between px-5 border-b border-gray-100">
        <span class="flex items-center gap-2 font-bold text-[#1a56db] text-xl tracking-tight">
          <svg width="24" height="24" viewBox="0 0 20 20" fill="none">
            <polygon points="10,2 18,18 2,18" fill="#1a56db"/>
          </svg>
          Daily Grow
        </span>
        <button @click="isSidebarOpen = false" class="lg:hidden p-1 text-gray-400">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <div class="px-5 py-4 text-xs text-gray-400 font-semibold border-b border-gray-50 uppercase tracking-wider">
        {{ user?.name ?? 'Название аккаунта' }}
      </div>

      <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
        <Link
          href="/reviews"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all"
          :class="$page.url.startsWith('/reviews')
            ? 'bg-[#eff4ff] text-[#1a56db]'
            : 'text-gray-600 hover:bg-gray-50'"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
            :stroke="$page.url.startsWith('/reviews') ? '#1a56db' : '#6b7280'"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          </svg>
          Отзывы
        </Link>

        <div class="mt-2 ml-4 border-l-2 border-gray-50 space-y-1">
          <Link
            href="/reviews"
            class="block px-4 py-2 text-sm transition-colors"
            :class="$page.url === '/reviews'
              ? 'text-[#1a56db] font-bold'
              : 'text-gray-500 hover:text-gray-800'"
          >
            Все отзывы
          </Link>
          <Link
            href="/settings"
            class="block px-4 py-2 text-sm transition-colors"
            :class="$page.url === '/settings'
              ? 'text-[#1a56db] font-bold'
              : 'text-gray-500 hover:text-gray-800'"
          >
            Настройка
          </Link>
        </div>
      </nav>

      <div class="p-4 border-t border-gray-100">
        <Link
          href="/logout" method="post" as="button"
          class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-red-500 hover:bg-red-50 font-medium transition-colors"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          <span>Выйти</span>
        </Link>
      </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 lg:ml-64">
      
      <header class="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-4 sticky top-0 z-20 lg:hidden">
        <button @click="toggleSidebar" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
        <span class="font-bold text-[#1a56db]">Daily Grow</span>
        <div class="w-10"></div> </header>

      <main class="flex-1 p-0">
        <slot />
      </main>
    </div>

  </div>
</template>