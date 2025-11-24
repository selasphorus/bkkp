<?php

namespace atc\Bkkp\Modules\TaxPrep\Subtypes;

use atc\WXC\Contracts\SubtypeInterface;
use atc\WXC\Traits\SubtypeDefaults;
use atc\WXC\Traits\SubtypeQueryHelpers;

final class TaxDocumentsSubtype implements SubtypeInterface
{
    use SubtypeDefaults, SubtypeQueryHelpers;
    
    public const POST_TYPE = 'document';
    public const TAXONOMY = 'document_category';
    public const TERM = 'taxes'; // ???

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
