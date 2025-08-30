<?php

namespace atc\Bkkp\Modules\Accounting\Subtypes;

use atc\WHx4\Core\Contracts\SubtypeInterface;

final class AccountantsSubtype implements SubtypeInterface
{
    public function getPostType(): string
    {
        return 'person';
    }

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
}
