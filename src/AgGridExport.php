<?php

namespace Clickbar\AgGrid;

use Clickbar\AgGrid\Contracts\AgGridExportable;
use Clickbar\AgGrid\Contracts\AgGridExportTimezoneProvider;
use Clickbar\AgGrid\Contracts\AgGridHasVirtualColumns;
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

    /** @var array<string, AgGridVirtualColumn> */
    private array $virtualColumns = [];

    public function __construct(
        private readonly Builder|Relation $queryBuilder,
        array $columnsToExport = null
    ) {
        $model = $this->queryBuilder->getModel();

        if (! ($model instanceof AgGridExportable)) {
            throw new \InvalidArgumentException('The model must implement the AgGridExportable interface.');
        }

        if ($model instanceof AgGridHasVirtualColumns) {
            $this->virtualColumns = $model->getVirtualColumns();
        }

        $this->columnDefinitions = collect($model->getAgGridColumnDefinitions())->keyBy('id');
        $this->columnsToExport = $columnsToExport ?? $this->columnDefinitions->keys()->all();

        $timezone = null;
        $timezoneProviderClass = config('ag-grid.export_timezone_provider');
        if ($timezoneProviderClass) {
            /** @var AgGridExportTimezoneProvider $timezoneProvider */
            $timezoneProvider = app($timezoneProviderClass);
            $timezone = $timezoneProvider->getAgGridExportTimezone();
        }

        $this->context = new AgGridFormatterContext(App::currentLocale(), $timezone);

        // account for columns without a definition
        $columnsWithoutDefinition = array_diff($this->columnsToExport, $this->columnDefinitions->keys()->all());
        foreach ($columnsWithoutDefinition as $columnWithoutDefinition) {
            // create temporary column definition that just returns the value as is
            $this->columnDefinitions->put(
                $columnWithoutDefinition,
                new AgGridColumnDefinition($columnWithoutDefinition, $columnWithoutDefinition)
            );
        }
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

            if (array_key_exists($column, $this->virtualColumns)) {
                $row[$column] = $this->virtualColumns[$column]->getValue($row);
            }

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
