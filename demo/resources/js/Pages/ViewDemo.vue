<template>
    <Head title="Demo" />
    <AppLayout>
        <AgGrid
            :column-defs="flamingoViewColumnDefinition"
            :data-source-url="dataSourceUrl"
            :get-row-id="getRowId" class="h-full"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import AgGrid from "@/Components/AgGrid/AgGrid.vue";
import {flamingoColumns, flamingoViewColumnDefinition} from "@/Models/Flamingo";
import type { GetRowIdParams } from "ag-grid-community";

const dataSourceUrl = route('api.view.flamingos')

function getRowId(parameters: GetRowIdParams) {
    return parameters.data.id
}

</script>

