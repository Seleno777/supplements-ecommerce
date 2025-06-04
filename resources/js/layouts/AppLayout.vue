<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link, usePage } from '@inertiajs/vue3';
import { ref, watchEffect } from 'vue';

const isDark = ref(localStorage.theme === 'dark' || window.matchMedia('(prefers-color-scheme: dark)').matches);

watchEffect(() => {
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.theme = 'dark';
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.theme = 'light';
    }
});

const page = usePage<{ auth: { user: { name: string; email: string } } }>();
const user = page.props.auth?.user;
</script>

<template>
    <div class="bg-background text-foreground font-gym flex min-h-screen flex-col">
        <!-- Header -->
        <header class="bg-card border-border flex items-center justify-between border-b px-6 py-4 shadow-sm">
            <h1 class="text-2xl font-bold tracking-tight">🏋️ GymSupps</h1>
            <div class="flex items-center gap-4">
                <Button variant="ghost" @click="isDark = !isDark">
                    {{ isDark ? '☀️' : '🌙' }}
                </Button>
                <Link v-if="user" href="/logout" method="post" as="button" class="hover:text-primary text-sm underline transition"> Salir </Link>
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden">
            <!-- Sidebar -->
            <aside class="bg-muted/20 border-border hidden w-64 space-y-3 border-r p-4 text-sm md:block">
                <nav class="flex flex-col gap-2">
                    <Link href="/" class="hover:text-primary">🏠 Inicio</Link>
                    <Link href="/cart" class="hover:text-primary">🛒 Carrito</Link>
                    <Link href="/my-products" class="hover:text-primary">📦 Mis productos</Link>
                    <Link href="/orders" class="hover:text-primary">📃 Mis pedidos</Link>
                    <Link href="/notifications" class="hover:text-primary">🔔 Notificaciones</Link>
                    <Link href="/messages" class="hover:text-primary">💬 Chat</Link>
                </nav>
            </aside>

            <!-- Main content -->
            <main class="bg-background flex-1 overflow-y-auto px-6 py-8">
                <slot />
            </main>
        </div>
    </div>
</template>
