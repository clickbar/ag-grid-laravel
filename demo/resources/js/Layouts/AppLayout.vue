<script setup lang="ts">
import NavLink from "@/Components/NavLink.vue";
import { computed } from "vue";

const currentRoute = computed(() =>route().current() )

</script>


<template>
    <div class="h-screen bg-gray-100 flex flex-col">
      <nav class="p-4 flex gap-4 text-sm text-gray-800 font-medium">
          <NavLink :href="route('demo')" :active="currentRoute === 'demo'" >Basic Demo</NavLink>
          <NavLink :href="route('view-demo')" :active="currentRoute === 'view-demo'">View Demo</NavLink>
      </nav>
      <div class="flex-1">
        <slot></slot>
      </div>
    </div>
</template>
