<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DocumentExpiringList extends Component
{
    public $documentExpiringList;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        // Fetch 5 upcoming expiring documents
        $this->documentExpiringList = getUpcomingDocumentExpiringQuery()->take(5)->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.document-expiring-list');
    }
}
