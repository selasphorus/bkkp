<?php

namespace atc\Bkkp\Modules\Documents;

use atc\WHx4\Core\Module as BaseModule;

// Post Types
use atc\Bkkp\Modules\Documents\PostTypes\Document;

// Taxonomies
use atc\Bkkp\Modules\Documents\Taxonomies\DocumentCategory;

// Define the module class
final class DocumentsModule extends BaseModule
{
    public function boot(): void
    {
        $this->registerDefaultViewRoot();
        parent::boot();

        // TODO: change this so that taxonomies are auto-detected, like field groups
        add_filter('whx4_register_taxonomy_handlers', function (array $handlers): array {
            $handlers['document_category'] = DocumentCategory::class;
            return $handlers;
        });
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
