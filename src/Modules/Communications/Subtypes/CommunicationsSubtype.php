<?php

namespace atc\Bkkp\Modules\Communications\Subtypes;

use atc\WHx4\Core\Contracts\SubtypeInterface;

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
