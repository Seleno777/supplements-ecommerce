<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link, usePage } from '@inertiajs/vue3';
import { ref, watchEffect } from 'vue';

import { User, ShoppingCart, Bell, LogOut, Home, Package, PlusCircle, Dumbbell, MessageSquare } from 'lucide-vue-next';

// Detectar tema oscuro
const isDark = ref(localStorage.theme === 'dark' || window.matchMedia('(prefers-color-scheme: dark)').matches);

// Obtener datos del usuario y notificaciones
const page = usePage<{
  auth: { user: { name: string; email: string } };
  unreadNotifications: number;
}>();

const user = page.props.auth?.user;
const unread = page.props.unreadNotifications || 0;

// Control del menú desplegable de usuario
const showUserMenu = ref(false);
function toggleUserMenu() {
  showUserMenu.value = !showUserMenu.value;
}

// Aplicar tema oscuro y guardar en localStorage
watchEffect(() => {
  if (isDark.value) {
    document.documentElement.classList.add('dark');
    localStorage.theme = 'dark';
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.theme = 'light';
  }
});
</script>

<template>
  <div class="flex flex-col min-h-screen bg-background text-foreground font-gym">
    <!-- Header -->
    <header class="flex items-center justify-between px-6 py-4 border-b shadow-sm bg-[#00569D] text-white">
      <div class="flex items-center gap-2 font-bold text-2xl tracking-tight select-none">
        <Dumbbell class="w-7 h-7" />
        NutriForge
      </div>

      <nav class="flex items-center gap-6">
        <Link href="/" class="flex items-center gap-1 hover:underline" title="Inicio">
          <Home class="w-5 h-5" />
          <span class="hidden md:inline">Inicio</span>
        </Link>
        <Link href="/my-products" class="flex items-center gap-1 hover:underline" title="Mis productos">
          <Package class="w-5 h-5" />
          <span class="hidden md:inline">Mis productos</span>
        </Link>
        <Link href="/products/create" class="flex items-center gap-1 hover:underline" title="Crear producto">
          <PlusCircle class="w-5 h-5" />
          <span class="hidden md:inline">Crear producto</span>
        </Link>

        <Link href="/cart" class="relative hover:text-gray-300" title="Carrito">
          <ShoppingCart class="w-6 h-6" />
        </Link>

        <Link href="/notifications" class="relative hover:text-gray-300" title="Notificaciones">
          <Bell class="w-6 h-6" />
          <span 
            v-if="unread > 0" 
            class="absolute -top-1 -right-2 bg-red-600 text-xs font-bold text-white rounded-full w-5 h-5 flex items-center justify-center"
          >
            {{ unread }}
          </span>
        </Link>

        <!-- Usuario y menú desplegable -->
        <div class="relative">
          <button
            @click="toggleUserMenu"
            class="flex items-center gap-2 focus:outline-none"
            aria-label="Menú de usuario"
          >
            <User class="w-6 h-6" />
            <span class="hidden md:inline">{{ user.name }}</span>
          </button>

          <div
            v-if="showUserMenu"
            class="absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded shadow-lg z-10"
          >
            <Link href="/orders" class="block px-4 py-2 hover:bg-gray-200">Mis pedidos</Link>
            <Link 
              href="/logout" 
              method="post" 
              as="button" 
              class="w-full text-left px-4 py-2 hover:bg-gray-200 flex items-center gap-2"
            >
              <LogOut class="w-5 h-5" />
              Cerrar sesión
            </Link>
          </div>
        </div>

        <!-- Toggle tema -->
        <Button variant="ghost" @click="isDark = !isDark" class="ml-4">
          {{ isDark ? '☀️' : '🌙' }}
        </Button>
      </nav>
    </header>

    <!-- Main content -->
    <main class="flex-1 px-6 py-8 overflow-y-auto bg-background">
      <slot />
    </main>

    <!-- Botón de chat fijo en esquina inferior derecha -->
    <Link
      href="/conversations"
      title="Ir al chat"
      class="fixed bottom-6 right-6 bg-blue-600 hover:bg-blue-700 text-white rounded-full p-4 shadow-lg flex items-center justify-center cursor-pointer"
    >
      <MessageSquare class="w-6 h-6" />
    </Link>
  </div>
</template>
