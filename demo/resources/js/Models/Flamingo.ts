import type {KeeperResource} from "@/Models/Keeper";
import type {ColDef, KeyCreatorParams, ValueFormatterParams, ValueGetterParams} from "ag-grid-community";
import {getSetFilterParametersFor} from "@/Utils/ag-grid";

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
        sortable: true,
        filter: true,
        ...getSetFilterParametersFor(
            'name',
            route('api.flamingos.set-values'
            )
        )
    },
    species: {
        headerValueGetter: () => 'Species',
        field: 'species',
        sortable: true,
        filter: true,
        ...getSetFilterParametersFor(
            'species',
            route('api.flamingos.set-values'
            )
        )
    },
    weight: {
        headerValueGetter: () => 'Weight',
        field: 'weight',
        filter: 'agNumberColumnFilter',
        sortable: true,
    },
    preferred_food_types: {
        headerValueGetter: () => 'Preferred food types',
        field: 'preferred_food_types',
        filter: true,
        ...getSetFilterParametersFor(
            'preferred_food_types',
            route('api.flamingos.set-values'
            )
        )
    },
    custom_properties: {
        headerValueGetter: () => 'Custom properties',
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
        headerValueGetter: () => 'Keeper',
        field: 'keeper.name',
        sortable: false,
        valueGetter(parameters: ValueGetterParams) {
            return parameters.data.keeper
        },
        valueFormatter(parameters: ValueFormatterParams) {
            return parameters.value.name
        },
        filter: true,
        ...getSetFilterParametersFor(
            'keeper.name',
            route('api.flamingos.set-values'
            )
        )
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

export const flamingoViewColumnDefinition = [

    {
        headerName: 'Flamingo',
        children: [
            {
                headerValueGetter: () => 'ID',
                field: 'id',
                filter: true,
                sortable: true,
                checkboxSelection: true,
                headerCheckboxSelection: true,
            },
            {
                headerValueGetter: () => 'Name',
                field: 'flamingo_name',
                sortable: true,
                filter: true,
                ...getSetFilterParametersFor(
                    'flamingo_name',
                    route('api.view.flamingos.set-values'
                    )
                )
            },
            {
                headerValueGetter: () => 'Species',
                field: 'flamingo_species',
                sortable: true,
                filter: true,
                ...getSetFilterParametersFor(
                    'flamingo_species',
                    route('api.view.flamingos.set-values'
                    )
                )
            },
            {
                headerValueGetter: () => 'Weight',
                field: 'flamingo_weight',
                filter: 'agNumberColumnFilter',
                sortable: true,
            },
            {
                headerValueGetter: () => 'Preferred food types',
                field: 'flamingo_preferred_food_types',
                filter: true,
                ...getSetFilterParametersFor(
                    'flamingo_preferred_food_types',
                    route('api.view.flamingos.set-values'
                    )
                )
            },
            {
                headerValueGetter: () => 'Custom properties',
                field: 'flamingo_custom_properties',
                filter: false,
                sortable: false,
                suppressMenu: true
            },
            {
                headerValueGetter: () => 'Is hungry',
                field: 'flamingo_is_hungry',
                filter: true,
                sortable: true,
            },
            {
                headerValueGetter: () => 'Last vaccinated on',
                field: 'flamingo_last_vaccinated_on',
                sortable: true,
                filter: 'agDateColumnFilter',
                valueFormatter(parameters) {
                    return (parameters.value as Date).toLocaleDateString()
                },
                valueGetter(parameters) {
                    return new Date(parameters.data.last_vaccinated_on)
                },
            },

        ],
    },
    {
        headerName: 'Keeper',
        children: [
            {
                headerValueGetter: () => 'Name',
                field: 'keeper_name',
                sortable: true,
                filter: true,
                ...getSetFilterParametersFor(
                    'keeper_name',
                    route('api.view.flamingos.set-values'
                    )
                )
            },
        ],
    },
    {
        headerName: 'Zoo',
        children: [
            {
                headerValueGetter: () => 'Name',
                field: 'zoo_name',
                sortable: true,
                filter: true,
                ...getSetFilterParametersFor(
                    'zoo_name',
                    route('api.view.flamingos.set-values'
                    )
                )
            },
            {
                headerValueGetter: () => 'Street',
                field: 'zoo_address.street',
                sortable: true,
                filter: true,
                ...getSetFilterParametersFor(
                    'zoo_address.street',
                    route('api.view.flamingos.set-values'
                    )
                )
            },
            {
                headerValueGetter: () => 'City',
                field: 'zoo_address.city',
                sortable: true,
                filter: true,
                ...getSetFilterParametersFor(
                    'zoo_address.city',
                    route('api.view.flamingos.set-values'
                    )
                )
            },
            {
                headerValueGetter: () => 'Mail',
                field: 'zoo_address.contact.email',
                sortable: true,
                filter: true,
                ...getSetFilterParametersFor(
                    'zoo_address.contact.email',
                    route('api.view.flamingos.set-values'
                    )
                )
            },
            {
                headerValueGetter: () => 'Phone',
                field: 'zoo_address.contact.phone',
                sortable: true,
                filter: true,
                ...getSetFilterParametersFor(
                    'zoo_address.contact.phone',
                    route('api.view.flamingos.set-values'
                    )
                )
            },
        ],
    },

];
