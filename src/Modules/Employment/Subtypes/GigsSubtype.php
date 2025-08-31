<?php

namespace atc\Bkkp\Modules\Employment\Subtypes;

use atc\WHx4\Core\Contracts\SubtypeInterface;

final class WorkPaymentsSubtype implements SubtypeInterface
{
    public function getPostType(): string
    {
        return 'event';
    }

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
}
