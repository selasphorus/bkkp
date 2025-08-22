<?php

namespace atc\Bkkp\Modules\Employment\Fields;

use atc\WHx4\Core\Contracts\FieldGroupInterface;

final class WorkPaymentFields implements FieldGroupInterface
{
    public static function register(): void
    {
        //error_log( '=== EmployerFields: register()) ===' );
        if ( !function_exists('acf_add_local_field_group') ) return;

        acf_add_local_field_group([
            'key' => 'group_workpayment_details',
            'title' => 'Work Payment Details',
            'fields' => [
                /*[
                    'key'   => 'field_bkkp_employment_employer_ein',
                    'name'  => 'bkkp_employment_employer_ein',
                    'label' => 'EIN',
                    'type'  => 'text',
                ],*/
                [
                    'key'           => 'field_bkkp_employment_employer',
                    'name'          => 'bkkp_employment_employer',
                    'label'         => 'Employer',
                    'type'          => 'post_object',
                    'post_type'     => ['employer'],
                    'return_format' => 'id',
                ],
                [
                    'key'    => 'field_bkkp_employment_gross_pay',
                    'name'   => 'bkkp_employment_gross_pay',
                    'label'  => 'Gross Pay',
                    'type'   => 'number',
                    'prepend'=> '$',
                ],
                [
                    'key'   => 'field_bkkp_employment_pay_period_start',
                    'name'  => 'bkkp_employment_pay_period_start',
                    'label' => 'Pay Period Start',
                    'type'  => 'date_picker',
                ],
                [
                    'key'   => 'field_bkkp_employment_pay_period_end',
                    'name'  => 'bkkp_employment_pay_period_end',
                    'label' => 'Pay Period End',
                    'type'  => 'date_picker',
                ],
                [
                    'key'   => 'field_bkkp_employment_tax_year',
                    'name'  => 'bkkp_employment_tax_year',
                    'label' => 'Tax Year',
                    'type'  => 'text',
                ],
                [
                    'key'           => 'field_bkkp_employment_document',
                    'name'          => 'bkkp_employment_document',
                    'label'         => 'Attached Document',
                    'type'          => 'file',
                    'return_format' => 'url',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'workpayment',
                    ],
                ],
            ],
        ]);
    }
}
