<?php

namespace atc\Bkkp\Modules\TaxPrep\Subtypes;

use atc\WHx4\Core\Contracts\SubtypeInterface;

final class TaxDocumentsSubtype implements SubtypeInterface
{
    public function getPostType(): string
    {
        return 'document';
    }

    public function getSlug(): string
    {
        return 'taxdocs';
    }

    public function getLabel(): string
    {
        return 'Tax Documents';
    }

    public function getTermArgs(): array
    {
        return []; // e.g. ['description' => 'Organizations that employ people']
    }
    
	public function getTermSlug(): string
	{
		return self::TERM; // singular term slug (internal taxonomy term)
	}
}
