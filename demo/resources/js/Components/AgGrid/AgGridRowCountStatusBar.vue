<template>
  <div class="flex gap-2 py-2">
    <div>
      Total: <span class="font-semibold">{{ totalRowCount ?? 'n/a' }}</span>
    </div>
    <div>
      Selected: <span class="font-semibold">{{ selectedRowCount ?? 'n/a' }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'

import type { IStatusPanelParams } from 'ag-grid-community'
import type { AgGridEvent } from 'ag-grid-community/dist/lib/events'
import type { IServerSideSelectionState } from 'ag-grid-enterprise'

const props = defineProps<{ params: IStatusPanelParams }>()

const totalRowCount = ref<number>()
const selectedRowCount = ref<number>(0)

function handleModelUpdated(event: AgGridEvent) {
  if (event.api.getModel().isLastRowIndexKnown()) {
    totalRowCount.value = event.api.getModel().getRowCount()
    // update the selected row count as well
    handleSelectionChanged(event)
  } else {
    totalRowCount.value = undefined
  }
}

function handleSelectionChanged(event: AgGridEvent) {
  if (event.api.getModel().getType() === 'clientSide') {
    selectedRowCount.value = event.api.getSelectedRows().length
  } else {
    const selectionState = event.api.getServerSideSelectionState() as IServerSideSelectionState
    selectedRowCount.value = selectionState.selectAll
      ? event.api.getModel().getRowCount() - selectionState.toggledNodes.length
      : selectionState.toggledNodes.length
  }
}

onMounted(() => {
  props.params.api.addEventListener('modelUpdated', handleModelUpdated)
  props.params.api.addEventListener('selectionChanged', handleSelectionChanged)
})

onBeforeUnmount(() => {
  props.params.api.removeEventListener('modelUpdated', handleModelUpdated)
  props.params.api.removeEventListener('selectionChanged', handleSelectionChanged)
})
</script>
