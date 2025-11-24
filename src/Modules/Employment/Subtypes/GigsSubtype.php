<?php

namespace atc\Bkkp\Modules\Employment\Subtypes;

use atc\WXC\Contracts\SubtypeInterface;
use atc\WXC\Traits\SubtypeDefaults;
use atc\WXC\Traits\SubtypeQueryHelpers;

final class GigsSubtype implements SubtypeInterface
{
    use SubtypeDefaults, SubtypeQueryHelpers;
    
    public const POST_TYPE = 'event';
    public const TAXONOMY = 'event_category';
    public const TERM = 'gig'; // ???

    public function getSlug(): string
    {
        return 'gigs';
    }

    public function getLabel(): string
    {
        return 'Gigs';
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
