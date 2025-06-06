<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link, usePage } from '@inertiajs/vue3';
import { ref, watchEffect } from 'vue';

const isDark = ref(localStorage.theme === 'dark' || window.matchMedia('(prefers-color-scheme: dark)').matches);

// Unifica el acceso a props
const page = usePage<{
    auth: { user: { name: string; email: string } };
    unreadNotifications: number;
}>();

const user = page.props.auth?.user;
const unread = page.props.unreadNotifications || 0;

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
        <header class="flex items-center justify-between px-6 py-4 border-b shadow-sm bg-card border-border">
            <h1 class="text-2xl font-bold tracking-tight">🏋️ GymSupps</h1>
            <div class="flex items-center gap-4">
                <Button variant="ghost" @click="isDark = !isDark">
                    {{ isDark ? '☀️' : '🌙' }}
                </Button>
                <Link href="/logout" method="post" as="button" class="text-sm underline transition hover:text-primary"> Cerrar sesión </Link>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden">
            <!-- Sidebar -->
            <aside class="hidden w-64 p-4 space-y-3 text-sm border-r bg-muted/20 border-border md:block">
                <nav class="flex flex-col gap-2">
                    <Link href="/" class="hover:text-primary">🏠 Inicio</Link>
                    <Link href="/cart" class="hover:text-primary">🛒 Carrito</Link>
                    <Link href="/my-products" class="hover:text-primary">📦 Mis productos</Link>
                    <Link href="/orders" class="hover:text-primary">📃 Mis pedidos</Link>
                    <Link href="/notifications" class="relative">
                        🔔 Notificaciones
                        <span v-if="unread > 0" class="ml-1 text-sm font-bold text-red-500"> ({{ unread }}) </span>
                    </Link>
                    <Link href="/messages" class="hover:text-primary">💬 Chat</Link>
                    <Link href="/products/create" class="hover:text-primary">➕ Crear producto</Link>
                </nav>
            </aside>

            <!-- Main content -->
            <main class="flex-1 px-6 py-8 overflow-y-auto bg-background">
                <slot />
            </main>
        </div>
    </div>
</template>
