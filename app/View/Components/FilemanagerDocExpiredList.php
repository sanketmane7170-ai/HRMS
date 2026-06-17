<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class FilemanagerDocExpiredList extends Component
{
    public  $documentExpiredCount;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $query = getFilemanagerDocumentExpiredQuery();
        // dd($query);
        $this->documentExpiredCount = getFilemanagerDocumentExpiredQuery()->count();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.filemanager-doc-expired-list');
    }
}
