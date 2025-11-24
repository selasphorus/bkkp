<?php

namespace atc\Bkkp\Modules\Employment\Fields;

use WXC\Core\Contracts\FieldGroupInterface;
use WXC\Core\Contracts\SubtypeFieldGroupInterface;
use WXC\Core\SubtypeRegistrar;

final class AccountantsGroupFields implements FieldGroupInterface, SubtypeFieldGroupInterface
{
    public function getPostType(): string
    {
        return 'group';
    }

    public function getSubtypeSlug(): string
    {
        return 'accountants';
    }

    public static function register(): void
    {
        //error_log( '=== EmployersGroupFields: register()) ===' );
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        //$tax = SubtypeRegistrar::getTaxonomyForPostType( 'group' ); // rex_group_type
        $taxonomy = "group_category";

        /*acf_add_local_field_group( [
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
        ] );*/

        acf_add_local_field_group( array(
            'key' => 'group_642702dbd3df3',
            'title' => 'Accountant Info',
            'fields' => array(
                array(
                    'key' => 'field_642702ddad1bb',
                    'label' => 'Recommended by (TXT)',
                    'name' => 'recommended_by_txt',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '33',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ),
                array(
                    'key' => 'field_64270326ad1bd',
                    'label' => 'Tax Prep Fee',
                    'name' => 'tax_prep_fee',
                    'aria-label' => '',
                    'type' => 'number',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '33',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'min' => '',
                    'max' => '',
                    'placeholder' => '',
                    'step' => '',
                    'prepend' => '',
                    'append' => '',
                ),
                array(
                    'key' => 'field_64270353ad1be',
                    'label' => 'Years Prepped (TXT)',
                    'name' => 'years_prepped_txt',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '33',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'person',
                    ),
                    array(
                        'param' => 'post_taxonomy',
                        'operator' => '==',
                        'value' => 'person_category:cpas',
                    ),
                ),
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'person',
                    ),
                    array(
                        'param' => 'post_taxonomy',
                        'operator' => '==',
                        'value' => 'person_category:accountants',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
            'show_in_rest' => 0,
        ) );

    }
}
