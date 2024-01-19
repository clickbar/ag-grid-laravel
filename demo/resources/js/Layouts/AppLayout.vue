<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="h-screen bg-gray-100 flex flex-col">
      <nav class="p-4 flex gap-4 text-sm text-gray-800 font-medium">
          <Link class="hover:underline">Basic Demo</Link>
          <Link class="hover:underline" href="/view">View Demo</Link>
      </nav>
      <div class="flex-1">
        <slot></slot>
      </div>
    </div>
</template>
