<script setup lang="ts">
defineProps<{
    message: string;
    type: 'success' | 'error' | null;
    show: boolean;
}>();
</script>

<template>
    <div
        v-if="show"
        :class="[
            'fixed top-19 right-2 px-5 py-3 rounded shadow-lg text-white font-semibold select-none z-50',
            type === 'success' ? 'bg-green-500' :
            type === 'error' ? 'bg-red-500' : 'bg-gray-500'
        ]"
    >
        {{ message }}
    </div>
</template>
