<?php

namespace atc\Bkkp\Modules\Communications\Subtypes;

use atc\WHx4\Core\Contracts\SubtypeInterface;

// TODO: rethink this implementation as a Subtype of Post
// -- may want to build logbook module in case on some installations Post type is being used another way

final class CommunicationsSubtype implements SubtypeInterface
{
    public function getPostType(): string
    {
        return 'post';
    }

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
