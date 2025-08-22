<?php

namespace atc\Bkkp\Modules\Employment\Subtypes;

use atc\WHx4\Core\Contracts\SubtypeInterface;

final class EmployersSubtype implements SubtypeInterface
{
    public function getPostType(): string
    {
        return 'group';
    }

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
}
