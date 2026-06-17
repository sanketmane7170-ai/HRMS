<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Carbon\Carbon;
use App\Models\UserDocument;

class PicList extends Component
{
    public $picExpiryList;

    public function __construct()
    {
        $this->picExpiryList = UserDocument::with('user.department')
            ->where('type', 'pic_certification')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('components.pic-list');
    }
}
