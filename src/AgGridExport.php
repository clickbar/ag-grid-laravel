<?php

namespace Clickbar\AgGrid;

use Clickbar\AgGrid\Contracts\AgGridExportable;
use Clickbar\AgGrid\Contracts\AgGridExportTimezoneProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class AgGridExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping
{
    /**
     * @var Collection<string, AgGridColumnDefinition>
     */
    private readonly Collection $columnDefinitions;

    private readonly AgGridFormatterContext $context;

    private readonly array $columnsToExport;

    public function __construct(
        private readonly Builder|Relation $queryBuilder,
        ?array $columnsToExport = null
    ) {
        $model = $this->queryBuilder->getModel();

        if (! ($model instanceof AgGridExportable)) {
            throw new \InvalidArgumentException('The model must implement the AgGridExportable interface.');
        }

        $this->columnDefinitions = collect($model->getAgGridColumnDefinitions())->keyBy('id');

        // Export columns come from the client, so only columns the model defines for export are read;
        // anything else could reach hidden attributes or relations through data_get().
        $this->columnsToExport = $columnsToExport === null
            ? $this->columnDefinitions->keys()->all()
            : array_values(array_filter($columnsToExport, fn ($column) => $this->columnDefinitions->has($column)));

        $timezone = null;
        $timezoneProviderClass = config('ag-grid.export_timezone_provider');
        if ($timezoneProviderClass) {
            /** @var AgGridExportTimezoneProvider $timezoneProvider */
            $timezoneProvider = app($timezoneProviderClass);
            $timezone = $timezoneProvider->getAgGridExportTimezone();
        }

        $this->context = new AgGridFormatterContext(App::currentLocale(), $timezone);
    }

    public function query(): Relation|Builder
    {
        return $this->queryBuilder;
    }

    public function map($row): array
    {
        return array_map(function (string $column) use ($row) {
            /** @var AgGridColumnDefinition $columDefinition */
            $columDefinition = $this->columnDefinitions[$column];

            if ($columDefinition->valueGetter !== null) {
                $value = $columDefinition->valueGetter->call($row, $row);
            } else {
                $value = data_get($row, $column);
            }

            if ($columDefinition->valueFormatter !== null) {
                return $columDefinition->valueFormatter->format($this->context, $value);
            }

            return $value;
        }, $this->columnsToExport);
    }

    public function headings(): array
    {
        return collect($this->columnsToExport)->map(fn ($column) => $this->columnDefinitions[$column]->name)->all();
    }

    public function columnFormats(): array
    {
        return collect($this->columnsToExport)
            ->map(fn ($column) => $this->columnDefinitions[$column])
            ->map(function (AgGridColumnDefinition $columDefinition) {
                $columDefinition->excelFormat ??= $columDefinition->valueFormatter !== null ? $columDefinition->valueFormatter::EXCEL_FORMAT : null;

                return $columDefinition;
            })
            ->filter(fn (AgGridColumnDefinition $columnDefinition) => $columnDefinition->excelFormat !== null)
            ->mapWithKeys(fn (AgGridColumnDefinition $columnDefinition, $index) => [
                Coordinate::stringFromColumnIndex($index + 1) => $columnDefinition->excelFormat,
            ])
            ->all();
    }
}
