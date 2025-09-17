<?php

declare(strict_types=1);

namespace App\Contracts;



interface ParserContract 
{
    /**
     * Summary of parse
     * @return \App\OV\ProductOV[]
     */
    public function parse(): iterable;
}
