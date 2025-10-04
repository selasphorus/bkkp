<?php
declare(strict_types=1);

namespace atc\Bkkp\Modules\Employment\Subtypes;

use atc\WHx4\Core\Contracts\SubtypeInterface;
use atc\WHx4\Core\Traits\SubtypeDefaults;
use atc\WHx4\Core\Traits\SubtypeQueryHelpers;

final class EmployersSubtype implements SubtypeInterface
{
    use SubtypeDefaults, SubtypeQueryHelpers;
    
    public const POST_TYPE = 'group';
    public const TAXONOMY = 'group_category';
    public const TERM = 'employer';

    public function getSlug(): string
    {
        return 'employers';
    }

    public function getLabel(): string
    {
        return 'Employers';
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
