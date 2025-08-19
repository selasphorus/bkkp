<?php

namespace atc\Bkkp\Modules\Accounting\Fields;

use atc\WHx4\Core\Contracts\FieldGroupInterface;

// TODO: make this final class?
class TransactionFields implements FieldGroupInterface
{
    public static function register(): void
    {
        //error_log( '=== EmployerFields: register()) ===' );
        if ( !function_exists('acf_add_local_field_group') ) return;

        acf_add_local_field_group([
            'key' => 'group_transaction_details',
            'title' => 'Transaction Details',
            'fields' => [
                [
                    'key'           => 'field_bkkp_employment_employer',
                    'name'          => 'bkkp_employment_employer',
                    'label'         => 'Employer',
                    'type'          => 'post_object',
                    'post_type'     => ['employer'],
                    'return_format' => 'id',
                ],
                [
                    'key' => 'field_bkkp_employment_employer_name',
                    'name' => 'bkkp_employment_employer_name',
                    'label' => 'Employer Name',
                    'type' => 'text',
                ],
                [
                    'key'   => 'field_bkkp_employment_employer_ein',
                    'name'  => 'bkkp_employment_employer_ein',
                    'label' => 'EIN',
                    'type'  => 'text',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'employer',
                    ],
                ],
            ],
        ]);
    }
}
