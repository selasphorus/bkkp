<?php

namespace atc\Bkkp\Modules\Employment\Fields;

use atc\WHx4\Core\Contracts\FieldGroupInterface;
use atc\WHx4\Core\Contracts\SubtypeFieldGroupInterface;
use atc\WHx4\Core\SubtypeRegistrar;

final class EmployersGroupFields implements FieldGroupInterface, SubtypeFieldGroupInterface
{
    public function getPostType(): string
    {
        return 'group';
    }

    public function getSubtypeSlug(): string
    {
        return 'employers';
    }

    public function register(): void
    {
        //error_log( '=== EmployersGroupFields: register()) ===' );
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        $tax = SubtypeRegistrar::getTaxonomyForPostType( 'group' ); // rex_group_type

        acf_add_local_field_group( [
            'key'    => 'field_rex_employment_group_fields',
            'title'  => 'Employment (Employer Details)',
            'fields' => [
                [
                    'key'   => 'field_rex_employment_industry',
                    'name'  => 'rex_employment_industry',
                    'label' => 'Industry',
                    'type'  => 'text',
                ],
                // …
            ],
        ] );
    }
}
