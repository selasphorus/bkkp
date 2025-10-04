<?php

namespace atc\Bkkp\Modules\Communications\Subtypes;

use atc\WHx4\Core\Contracts\SubtypeInterface;
use atc\WHx4\Core\Traits\SubtypeDefaults;
use atc\WHx4\Core\Traits\SubtypeQueryHelpers;

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
}
