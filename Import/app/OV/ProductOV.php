<?php

declare(strict_types=1);

namespace App\OV;



class ProductOV  
{
    public function __construct(
        public string $name, 
        public string $sku, 
        public float $price, 
        public string $category,
        public string $description,
        public array $images
        )
    {
        
    }
}
