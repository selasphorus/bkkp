<?php

namespace atc\Bkkp\Modules\Accounting\Subtypes;

use atc\BhWP\Core\Contracts\SubtypeInterface;
use atc\BhWP\Core\Traits\SubtypeDefaults;
use atc\BhWP\Core\Traits\SubtypeQueryHelpers;

final class AccountantsSubtype implements SubtypeInterface
{
    use SubtypeDefaults, SubtypeQueryHelpers;
    
    public const POST_TYPE = 'person';
    public const TAXONOMY = 'people_category';
    public const TERM = 'accountant';

    public function getSlug(): string
    {
        return 'accountants';
    }

    public function getLabel(): string
    {
        return 'Accountants';
    }

    public function getTermArgs(): array
    {
        return []; // e.g. ['description' => 'Organizations that employ people']
    }
    
    // Recommended to keep UI label ("Employers") separate from the actual term slug ("employer")
	public function getTermSlug(): string
	{
		//return 'employer';
		return self::TERM; // singular term slug (internal taxonomy term)
	}
}
