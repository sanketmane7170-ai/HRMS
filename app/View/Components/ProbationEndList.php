<?php
namespace App\View\Components;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
class ProbationEndList extends Component
{
    public $probationEndList;
    public int $days = 40;
    public function __construct()
    {
        $query = getProbationEndQuery($this->days);
        $query->whereDoesntHave('probationLetters');
        $this->probationEndList = $query->take(5)->get();
    }
    public function render(): View|Closure|string
    {
        return view('components.probation-end-list');
    }
}
