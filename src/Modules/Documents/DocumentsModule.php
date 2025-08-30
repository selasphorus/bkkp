<?php

namespace atc\Bkkp\Modules\Documents;

use atc\WHx4\Core\Module as BaseModule;
//
use atc\Bkkp\Modules\Documents\PostTypes\Document;

// Define the module class
final class DocumentsModule extends BaseModule
{
    public function boot(): void
    {
        $this->registerDefaultViewRoot();
        parent::boot();
    }

    public function getPostTypeHandlerClasses(): array
    {
        return [
            Document::class,
            //YYY::class,
            //ZZZ::class,
        ];
    }
}
