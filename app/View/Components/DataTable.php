<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Str;

class DataTable extends Component
{
    public array $columns;
    public string $url;
    public $dataTableColumns;
    /**
     * Create a new component instance.
     */
    public function __construct($columns, $url = null)
    {
        $this->columns = $columns;
        $this->url = $url ? $url : url()->current();
        $this->dataTableColumns = $this->getDataTableColumns();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.data-table');
    }


    public function getDataTableColumns()
    {
        $dataTableColumns = [];

        foreach ($this->columns as $column) {
            $data['name'] = strtolower($column);
            $data['data'] = strtolower($column);

            if ($column == '#') {
                $data['name'] = 'id';
                $data['data'] = 'DT_RowIndex';
            }
            if ($column == 'action') {
                $data['orderable'] = false;
                $data['searchable'] = false;
            }
            array_push($dataTableColumns, $data);
        }

        return ($dataTableColumns);
    }

    public function getColumnName($column)
    {
        $string = $column;
        if (Str::contains($column, '.')) {
            $string = Str::replace('.', ' ', $string);
        }

        return Str::title($string);
    }
}
