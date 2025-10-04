<?php

namespace atc\Bkkp\Modules\Employment\Subtypes;

use atc\WHx4\Core\Contracts\SubtypeInterface;
use atc\WHx4\Core\Traits\SubtypeDefaults;
use atc\WHx4\Core\Traits\SubtypeQueryHelpers;

final class WorkPaymentsSubtype implements SubtypeInterface
{
    use SubtypeDefaults, SubtypeQueryHelpers;
    
    public const POST_TYPE = 'document';
    public const TAXONOMY = 'document_category';
    public const TERM = 'employment'; // ???

    public function getSlug(): string
    {
        return 'workpayments';
    }

    public function getLabel(): string
    {
        return 'Work Payments';
    }

    public function getTermArgs(): array
    {
        return []; // e.g. ['description' => 'Organizations that employ people']
    }
    
    // Recommended to keep UI label ("Employers") separate from the actual term slug ("employer")
	public function getTermSlug(): string
	{
		return self::TERM; // singular term slug (internal taxonomy term)
	}
}
