import type { KeeperResource } from "@/Models/Keeper";
import type { ColDef, KeyCreatorParams, ValueFormatterParams, ValueGetterParams } from "ag-grid-community";

export type FoodType = 'shrimp'
| 'algae'
| 'fish'
| 'insects'
| 'pellets'
| 'vegetables'

export type FlamingoSpecies = 'greater'
| 'lesser'
| 'chilean'
| 'james'
| 'andean'
| 'american'

export interface FlamingoResource {
    id: number
    name: string
    species: FlamingoSpecies
    weight: number
    preferred_food_types: FoodType[]
    custom_properties: Record<string, any> | null
    is_hungry: boolean
    last_vaccinated_on: string
    keeper_id: number
    keeper: KeeperResource
    updated_at: string
    created_at: string
}

type FlamingoColumnNames = 'id'
| 'name'
| 'species'
| 'weight'
| 'preferred_food_types'
| 'custom_properties'
| 'is_hungry'
| 'last_vaccinated_on'
| 'keeper'
| 'updated_at'
| 'created_at'

export const flamingoColumns: Record<FlamingoColumnNames, ColDef> = {
    id: {
        headerValueGetter: () => 'ID',
        field: 'id',
        filter: true,
        sortable: true,
        checkboxSelection: true,
        headerCheckboxSelection: true,
        pinned: 'left'
    },
    name: {
        headerValueGetter: () => 'Name',
        field: 'name',
        filter: 'agTextColumnFilter',
        sortable: true,
    },
    species: {
        headerValueGetter: () => 'Species',
        field: 'species',
        sortable: true,
    },
    weight: {
        headerValueGetter: () => 'Weight',
        field: 'weight',
        filter: 'agNumberColumnFilter',
        sortable: true,
    },
    preferred_food_types: {
        headerValueGetter: () =>'Preferred food types',
        field: 'preferred_food_types',
        filter: true,
    },
    custom_properties: {
        headerValueGetter: () =>'Custom properties',
        field: 'custom_properties',
        filter: false,
        sortable: false,
        suppressMenu: true
    },
    is_hungry: {
        headerValueGetter: () => 'Is hungry',
        field: 'is_hungry',
        filter: true,
        sortable: true,
    },
    last_vaccinated_on: {
        headerValueGetter: () => 'Last vaccinated on',
        field: 'last_vaccinated_on',
        sortable: true,
        filter: 'agDateColumnFilter',
        valueFormatter(parameters) {
            return (parameters.value as Date).toLocaleDateString()
        },
        valueGetter(parameters) {
            return new Date(parameters.data.last_vaccinated_on)
        },
    },
    keeper: {
        headerValueGetter: () =>'Keeper',
        field: 'keeper.name',
        filter: 'agTextColumnFilter',
        sortable: true,
        valueGetter(parameters: ValueGetterParams){
            return parameters.data.keeper
        },
        valueFormatter(parameters: ValueFormatterParams) {
            return parameters.value.name
        },
        filterParams: {
            keyCreator(parameters: KeyCreatorParams) {
                return parameters.value.id
            },
            valueFormatter(parameters: ValueFormatterParams) {
                return parameters.value.name
            },
        },
    },
    updated_at: {
        headerValueGetter: () => 'Updated at',
        field: 'updated_at',
        sortable: true,
        filter: 'agDateColumnFilter',
        valueFormatter(parameters) {
            return (parameters.value as Date).toLocaleString()
        },
        valueGetter(parameters) {
            return new Date(parameters.data.created_at)
        },
    },
    created_at: {
        headerValueGetter: () => 'Created at',
        field: 'created_at',
        sortable: true,
        filter: 'agDateColumnFilter',
        valueFormatter(parameters) {
            return (parameters.value as Date).toLocaleString()
        },
        valueGetter(parameters) {
            return new Date(parameters.data.created_at)
        },
    },
}
