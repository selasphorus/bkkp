<?php

namespace atc\Bkkp\Modules\Communications\Subtypes;

use WXC\Core\Contracts\SubtypeInterface;
use WXC\Core\Traits\SubtypeDefaults;
use WXC\Core\Traits\SubtypeQueryHelpers;

// TODO: rethink this implementation as a Subtype of Post
// -- may want to build logbook module in case on some installations Post type is being used another way

final class CommunicationsSubtype implements SubtypeInterface
{
    use SubtypeDefaults, SubtypeQueryHelpers;
    
    public const POST_TYPE = 'post';
    public const TAXONOMY = 'category';
    public const TERM = 'comms'; // ???

    public function getSlug(): string
    {
        return 'communications';
    }

    public function getLabel(): string
    {
        return 'Communications';
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
