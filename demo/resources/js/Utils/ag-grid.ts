import type {ColDef, SetFilterValuesFuncParams} from "ag-grid-community";
import axios from "axios";

export function getSetFilterParametersFor(
    column: string,
    url: string,
    refreshValuesOnOpen = false,
): ColDef {
    return {
        filterParams: {
            excelMode: 'windows',
            values: (parameters: SetFilterValuesFuncParams) => {
                const payload = { column, filterModel: parameters.api.getFilterModel() }

                axios
                    .post(url, payload)
                    .then((response) => {
                        parameters.success(response.data)
                    })
                    .catch(() => {
                        parameters.success([])
                    })
            },
            refreshValuesOnOpen,
        },
    }
}
