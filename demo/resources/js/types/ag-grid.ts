import type {ColDef, ColGroupDef, IServerSideDatasource} from "ag-grid-community";
import axios from "axios";
import type { Ref } from "vue";
import { ref } from "vue";
import { deepmerge } from '@fastify/deepmerge'

export interface AgGridClientSideSelection<T = any> {
    rowModel: 'clientSide'
    toggledNodes: (string | number)[]
    data: T[]
    count: number
    isEmpty: boolean
}

export interface AgGridServerSideSelection {
    rowModel: 'serverSide'
    toggledNodes: (string | number)[]
    selectAll: boolean
    isEmpty: boolean
    filterModel: Record<string, unknown> | null
    customFilters: Record<string, unknown>
}

export type AgGridSelection = AgGridClientSideSelection | AgGridServerSideSelection

export function initClientSideSelection<T>(): Ref<AgGridClientSideSelection<T>> {
    return ref({
        rowModel: 'clientSide',
        toggledNodes: [],
        data: [],
        count: 0,
        isEmpty: true,
    })
}

export function initServerSideSelection(): Ref<AgGridServerSideSelection> {
    return ref({
        rowModel: 'serverSide',
        selectAll: false,
        toggledNodes: [],
        isEmpty: true,
        filterModel: null,
        customFilters: {},
    })
}

export function wrapInSelection<T extends { id: number | string }>(
    ...data: T[]
): AgGridClientSideSelection<T> {
    return {
        rowModel: 'clientSide',
        data,
        toggledNodes: data.map((entry) => entry.id),
        count: data.length,
        isEmpty: data.length === 0,
    }
}

export function selectionToRequestData(
    selection: AgGridSelection,
    customFilters?: Record<string, any>,
): any {
    if (selection.rowModel === 'serverSide') {
        return selection
    }
    return {
        rowModel: selection.rowModel,
        toggledNodes: selection.toggledNodes,
        customFilters,
    }
}

const merge = deepmerge()

export function pickColumns<D extends Record<string, ColDef | ColGroupDef>>(
    columnDefinitions: D,
    columns: (
        | keyof D
        | {
        name: keyof D
        params: ColDef
    }
        )[],
): (ColDef | ColGroupDef)[] {
    const selectedColumnDefinitions = [] as (ColDef | ColGroupDef)[]
    for (const column of columns) {
        if (typeof column === 'object') {
            selectedColumnDefinitions.push(merge(columnDefinitions[column.name], column.params))
        } else {
            selectedColumnDefinitions.push(columnDefinitions[column])
        }
    }
    return selectedColumnDefinitions
}

export interface AgGridGetRowsResponse<T> {
    total: number
    data: T[]
}

export function makeDataSource<T>(
    url: string,

    customFilters: Ref | undefined = undefined,
): IServerSideDatasource {
    return {
        // called by the grid when more rows are required
        async getRows(parameters) {
            const request = {
                ...parameters.request,
                customFilters: customFilters?.value,
            }

            // get data for request from server
            try {
                const response = await axios.post<AgGridGetRowsResponse<T>>(url, request)
                parameters.success({
                    rowData: response.data.data,
                    rowCount: response.data.total,
                })
            } catch {
                parameters.fail()
            }
        },
    }
}
