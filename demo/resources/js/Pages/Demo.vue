<template>
    <Head title="Demo" />

    <AppLayout>

        <AgGrid :column-defs="columnDefs" :data-source-url="dataSourceUrl" :get-row-id="getRowId" class="h-full"/>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import AgGrid from "@/Components/AgGrid/AgGrid.vue";
import { pickColumns } from "@/types/ag-grid";
import { flamingoColumns } from "@/Models/Flamingo";
import type { GetRowIdParams } from "ag-grid-community";

const dataSourceUrl = route('api.flamingos.rows')

const columnDefs = pickColumns(flamingoColumns, [
    'id',
    'name',
    'weight',
    'preferred_food_types',
    'custom_properties',
    'is_hungry',
    'last_vaccinated_on',
    'keeper',
    'updated_at',
    'created_at',
])

function getRowId(parameters: GetRowIdParams) {
    return parameters.data.id
}

console.log(columnDefs)

</script>

