<template>
  <AgGridVue
    class="ag-theme-alpine"
    :row-model-type="rowModelType"
    :default-excel-export-params="defaultExportParameters"
    :default-csv-export-params="defaultExportParameters"
    :excel-styles="excelStyles"
    :enable-range-selection="enableRangeSelection"
    :suppress-multi-range-selection="true"
    :suppress-row-click-selection="!enableRowClickSelection"
    :row-multi-select-with-click="rowMultiSelectWithClick"
    :row-selection="rowSelection"
    :default-col-def="defaultColDefinition"
    :column-defs="columnDefs"
    :row-buffer="20"
    :get-row-id="getRowId"
    :get-row-class="getRowClass"
    :context="context"
    :tooltip-show-delay="200"
    :cache-block-size="50"
    :max-blocks-in-cache="4"
    :get-context-menu-items="getContextMenuItems"
    :status-bar="statusBar"
    :side-bar="true"
    group-display-type="singleColumn"
    un-sort-icon
    @grid-ready="onGridReady"
    @selection-changed="onSelectionChanged"
  />
</template>

<script lang="ts" setup>
import { AgGridVue } from 'ag-grid-vue3'
import axios from 'axios'
import 'ag-grid-community/styles/ag-grid.css'
import 'ag-grid-community/styles/ag-theme-alpine.css'

import type { ColDef, ColGroupDef, GridApi, GridReadyEvent, RowEvent } from 'ag-grid-community'
import type {
  ColumnApi,
  CsvExportParams,
  ExcelExportParams,
  ExcelStyle,
  IServerSideSelectionState,
  MenuItemDef,
  RowClassParams,
} from 'ag-grid-enterprise'
import { computed, ref, toRef, watch } from 'vue'
import AgGridRowCountStatusBar from '@/Components/AgGrid/AgGridRowCountStatusBar.vue'
import type {
  AgGridClientSideSelection,
  AgGridSelection,
  AgGridServerSideSelection,
} from '@/types/ag-grid'
import { makeDataSource } from '@/types/ag-grid'

const props = withDefaults(
  defineProps<{
    columnDefs: (ColDef | ColGroupDef)[]
    rowData?: object[]
    dataSourceUrl?: string
    enableRangeSelection?: boolean
    enableRowClickSelection?: boolean
    hideToolPanels?: boolean
    rowSelection?: 'single' | 'multiple' | null
    context?: object
    getRowId?: (event: RowEvent) => void
    getRowClass?: (event: RowClassParams) => void
    autoSizeColumns?: boolean
    showRowCount?: boolean
    customFilters?: Record<string, any>
  }>(),
  {
    rowData: undefined,
    dataSourceUrl: undefined,
    hideToolPanels: false,
    enableRangeSelection: false,
    enableRowClickSelection: false,
    showRowCount: false,
    rowSelection: 'multiple',
    context: undefined,
    autoSizeColumns: false,
    getRowId: (parameters: RowEvent) => {
      return parameters.data.id
    },
    getRowClass: (parameters: RowClassParams) => {
      return parameters.node.rowPinned ? 'font-semibold' : undefined
    },
    customFilters: () => ({}),
  },
)

const emit = defineEmits<{
  (event: 'update:selection', value: AgGridSelection): void
  (event: 'gridReady', value: GridReadyEvent): void
}>()

const statusBar = {
  statusPanels: [
    {
      statusPanel: AgGridRowCountStatusBar,
      align: 'left',
    },
  ],
}

const defaultColDefinition = {
  resizable: true,
  menuTabs: ['filterMenuTab'],
}

let api: GridApi | null = null
let columnApi: ColumnApi | null = null

watch(
  () => props.rowData,
  (rowData) => {
    api?.setRowData(rowData ?? [])
  },
)

watch(
  () => props.dataSourceUrl,
  (dataSourceUrl) => {
    api?.setServerSideDatasource(makeDataSource(dataSourceUrl!, toRef(props, 'customFilters')))
  },
)

const rowMultiSelectWithClick = computed(() => props.rowSelection === 'multiple')
const rowModelType = computed(() => (props.dataSourceUrl ? 'serverSide' : 'clientSide'))

watch(
  () => props.customFilters,
  (value) => {
    api?.refreshServerSide()
    const selectionState = {
      ...currentSelection.value,
      customFilters: value,
    } as AgGridServerSideSelection

    currentSelection.value = selectionState
    emit('update:selection', selectionState)
  },
)

const defaultExportParameters: ExcelExportParams & CsvExportParams = {
  skipPinnedBottom: true,
}

const excelStyles: ExcelStyle[] = [
  {
    id: 'string',
    dataType: 'String',
  },
]

function getExportFilename(format: 'excel' | 'csv') {
  const timestamp = Math.floor(Date.now() / 1000)
  const extension = format === 'excel' ? 'xlsx' : 'csv'
  return `${timestamp}.${extension}`
}

function exportSelectedToCsv() {
  const model = api!.getModel()
  if (model.getType() === 'clientSide') {
    exportClientSide('csv', true)
  } else {
    exportServerSide('csv', true)
  }
}

function exportSelectedToExcel() {
  const model = api!.getModel()
  if (model.getType() === 'clientSide') {
    exportClientSide('excel', true)
  } else {
    exportServerSide('excel', true)
  }
}

function exportClientSide(format: 'excel' | 'csv', onlySelected: boolean) {
  api?.exportDataAsExcel({
    fileName: getExportFilename(format),
    onlySelected,
    skipPinnedBottom: true,
    skipPinnedTop: true,
    skipColumnGroupHeaders: true,
  })
}

async function exportServerSide(format: 'excel' | 'csv', onlySelected: boolean) {
  // @ts-expect-error using a private api here
  const parameters = api!.getModel().getRootStore().getSsrmParams()

  // only request the visible columns
  const cols = columnApi?.getAllDisplayedColumns().map((column) => column.getColId())

  const response = await axios.post(
    props.dataSourceUrl!,
    {
      ...parameters,
      ...(onlySelected ? api?.getServerSideSelectionState() : {}),
      exportFormat: format,
      exportColumns: cols,
      customFilters: props.customFilters,
    },
    {
      responseType: 'blob',
    },
  )

  const url = URL.createObjectURL(response.data)

  // Create a link to download it
  const a = document.createElement('a')
  a.href = url
  a.setAttribute('download', getExportFilename(format))
  a.click()
}

function exportAllToExcel() {
  const model = api!.getModel()
  if (model.getType() === 'clientSide') {
    exportClientSide('excel', false)
  } else {
    exportServerSide('excel', false)
  }
}

function exportAllToCsv() {
  const model = api!.getModel()
  if (model.getType() === 'clientSide') {
    exportClientSide('csv', false)
  } else {
    exportServerSide('csv', false)
  }
}

function onGridReady(event: GridReadyEvent) {
  api = event.api
  columnApi = event.columnApi

  if (props.dataSourceUrl) {
    event.api.setServerSideDatasource(
      makeDataSource(props.dataSourceUrl, toRef(props, 'customFilters')),
    )
  } else {
    event.api.setRowData(props.rowData ?? [])
  }

  if (props.autoSizeColumns) {
    columnApi.autoSizeAllColumns()
  }

  emit('gridReady', event)
}

const currentSelection = ref<AgGridSelection>({
  rowModel: 'clientSide',
  toggledNodes: [],
  data: [],
  count: 0,
  isEmpty: true,
})

function onSelectionChanged() {
  const gridApi = api!
  if (gridApi.getModel().getType() === 'serverSide') {
    const selectionState = gridApi.getServerSideSelectionState() as IServerSideSelectionState
    // aggregate the detail grid selection state (they always have the client side row model)
    gridApi.forEachDetailGridInfo((gridInfo) => {
      const detailSelectionState = getClientSideSelectionState(gridInfo.api!)
      selection.toggledNodes = [...selection.toggledNodes, ...detailSelectionState.toggledNodes]
    })

    const selection: AgGridServerSideSelection = {
      rowModel: 'serverSide',
      selectAll: selectionState.selectAll,
      toggledNodes: selectionState.toggledNodes,
      isEmpty: !selectionState.selectAll && selectionState.toggledNodes.length === 0,
      filterModel: gridApi.getFilterModel(),
      customFilters: props.customFilters,
    }

    currentSelection.value = selection
    emit('update:selection', selection)
    return
  }

  const selection = getClientSideSelectionState(gridApi)
  // aggregate the detail grid selection state
  gridApi.forEachDetailGridInfo((gridInfo) => {
    const detailSelectionState = getClientSideSelectionState(gridInfo.api!)
    selection.toggledNodes = [...selection.toggledNodes, ...detailSelectionState.toggledNodes]
    selection.data = [...selection.data, ...detailSelectionState.data]
    selection.count += detailSelectionState.count
    selection.isEmpty = selection.isEmpty && detailSelectionState.isEmpty
  })

  currentSelection.value = selection
  emit('update:selection', selection)
}

function getClientSideSelectionState(grid: GridApi): AgGridClientSideSelection {
  const sortedNodes = [...grid.getSelectedNodes()].sort((a, b) => a.rowIndex! - b.rowIndex!)
  return {
    rowModel: 'clientSide',
    toggledNodes: sortedNodes.map((node) => node.id!),
    data: sortedNodes.map((node) => node.data),
    count: sortedNodes.length,
    isEmpty: sortedNodes.length === 0,
  }
}

function getContextMenuItems() {
  return [
    'copy',
    'copyWithHeaders',
    'copyWithGroupHeaders',
    'separator',
    {
      name: `Export (selected)`,
      icon: '<span class="ag-icon ag-icon-save" role="presentation"></span>',
      disabled: currentSelection.value.toggledNodes.length === 0,
      subMenu: [
        {
          name: 'CSV Export',
          icon: '<span class="ag-icon ag-icon-csv" role="presentation"></span>',
          action: exportSelectedToCsv,
        },
        {
          name: 'Excel Export',
          icon: '<span class="ag-icon ag-icon-excel" role="presentation"></span>',
          action: exportSelectedToExcel,
        },
      ],
    } as MenuItemDef,
    {
      name: 'Export (alle)',
      icon: '<span class="ag-icon ag-icon-save" role="presentation"></span>',
      subMenu: [
        {
          name: 'CSV Export',
          icon: '<span class="ag-icon ag-icon-csv" role="presentation"></span>',
          action: exportAllToCsv,
        },
        {
          name: 'Excel Export',
          icon: '<span class="ag-icon ag-icon-excel" role="presentation"></span>',
          action: exportAllToExcel,
        },
      ],
    } as MenuItemDef,
  ]
}
</script>
