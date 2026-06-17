<?php

namespace Modules\Document\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;

trait DocumentParser
{
    use ForwardsCalls, HasKeywords;

    public function parseHtml($html)
    {
        foreach ($this->getKeyWordList() as $word) {
            if (str_contains($html, $word)) {
                
                $replace = $this->getReplacementText($word);
                $html = str_replace($word, $replace, $html);
            }
        }

        return $html;
    }
}
