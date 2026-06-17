<?php

namespace App\View\Components;
use App\Models\Feature;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FeatureList extends Component
{
    public $features;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $query = getFeatureQuery();

        $this->features = $query->take(5)->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
       
        return view('components.feature-list');
    }
}
