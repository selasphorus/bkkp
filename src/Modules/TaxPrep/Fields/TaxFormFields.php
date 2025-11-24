<?php

namespace atc\Bkkp\Modules\TaxPrep\Fields;

use atc\WXC\Core\Contracts\FieldGroupInterface;

// TODO: rename all fields (keys/names) according to whx4 naming conventions
final class TaxFormFields implements FieldGroupInterface
{
    public static function register(): void
    {
        //error_log( '=== TaxFormFields: register()) ===' );
        if ( !function_exists('acf_add_local_field_group') ) return;

        //use atc\WHx4\Migrations\FieldKeyMigrator;
        /*
        // Migrate
        FieldKeyMigrator::migrate([
            'field_624775f4b6221' => [
                'new_field_key' => 'field_whx4_modulename_first_name',
                'old_meta_key'  => 'first_name',
                'new_meta_key'  => 'whx4_modulename_first_name',
            ],
            'field_abc123xyz456' => [
                'new_field_key' => 'field_whx4_notes',
                // no meta_key rename
            ],
        ]);

        acf_add_local_field_group([
            'key' => 'group_xxx_details',
            'title' => 'XXX Details',
            'fields' => [],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'xxx',
                    ],
                ],
            ],
        ]);
        */

        acf_add_local_field_group( array(
            'key' => 'group_642c39307f4cf',
            'title' => 'Tax Form Info',
            'fields' => array(
                array(
                    'key' => 'field_642c40d0cc22f',
                    'label' => 'Jurisdiction',
                    'name' => 'jurisdiction',
                    'aria-label' => '',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '10',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => array(
                        'federal' => 'Federal',
                        'nys' => 'NY State',
                        'nyc' => 'NYC',
                        'nj' => 'NJ',
                        'state' => 'State',
                        'local' => 'Local',
                    ),
                    'default_value' => false,
                    'return_format' => 'value',
                    'multiple' => 0,
                    'allow_null' => 0,
                    'ui' => 0,
                    'ajax' => 0,
                    'placeholder' => '',
                    'create_options' => 0,
                    'save_options' => 0,
                ),
                array(
                    'key' => 'field_642c393000a3c',
                    'label' => 'Form ID',
                    'name' => 'form_id',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '10',
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
                    'key' => 'field_642c395300a3d',
                    'label' => 'Form Name',
                    'name' => 'form_name',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '30',
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
                    'key' => 'field_642c396300a3e',
                    'label' => 'Form URL',
                    'name' => 'form_url',
                    'aria-label' => '',
                    'type' => 'url',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '20',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_642c39b7b188d',
                    'label' => 'Form Info URL',
                    'name' => 'form_info_url',
                    'aria-label' => '',
                    'type' => 'url',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '20',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_642c397100a3f',
                    'label' => 'Form PDF',
                    'name' => 'form_pdf',
                    'aria-label' => '',
                    'type' => 'file',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'return_format' => 'array',
                    'library' => 'all',
                    'min_size' => '',
                    'max_size' => '',
                    'mime_types' => 'pdf',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'tax_form',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'acf_after_title',
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
