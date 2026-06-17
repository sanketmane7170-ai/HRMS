<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class DocumentExpiredList extends Component
{
    public  $documentExpiredCount;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $query = getUserDocumentExpiredQuery();
        $this->documentExpiredCount = getUserDocumentExpiredQuery()->count();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.document-expired-list');
    }
}
