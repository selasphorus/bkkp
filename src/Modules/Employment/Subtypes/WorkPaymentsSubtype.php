<?php

namespace atc\Bkkp\Modules\Employment\Subtypes;

use atc\WHx4\Core\Contracts\SubtypeInterface;

final class WorkpaymentsSubtype implements SubtypeInterface
{
    public function getPostType(): string
    {
        return 'document';
    }

    public function getSlug(): string
    {
        return 'workpayments';
    }

    public function getLabel(): string
    {
        return 'Workpayments';
    }

    public function getTermArgs(): array
    {
        return []; // e.g. ['description' => 'Organizations that employ people']
    }
}
