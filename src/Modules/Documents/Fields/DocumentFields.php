<?php

namespace atc\Bkkp\Modules\Documents\Fields;

use atc\WXC\Contracts\FieldGroupInterface;

// TODO: rename all fields (keys/names) according to whx4 naming conventions
// TODO: update array definitions for consistency ( [] instead of array() )
final class DocumentFields implements FieldGroupInterface
{
    public static function register(): void
    {
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
        */

        /*acf_add_local_field_group([
            'key' => 'group_xxx_details',
            'title' => 'Document Details',
            'fields' => [],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'document',
                    ],
                ],
            ],
        ]);*/

        // Document Basics
        acf_add_local_field_group( array(
            'key' => 'group_65e8fdc57fb0f',
            'title' => 'Document Basics',
            'fields' => array(
                array(
                    'key' => 'field_65e8fdc5ef703',
                    'label' => 'PDF',
                    'name' => 'pdf',
                    'aria-label' => '',
                    'type' => 'file',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '25',
                        'class' => '',
                        'id' => '',
                    ),
                    'return_format' => 'array',
                    'library' => 'all',
                    'min_size' => '',
                    'max_size' => '',
                    'mime_types' => '',
                ),
                array(
                    'key' => 'field_6428cb67d7b30',
                    'label' => 'Hard copy on file?',
                    'name' => 'hard_copy',
                    'aria-label' => '',
                    'type' => 'true_false',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '25',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 0,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_6428cb7ad7b31',
                    'label' => 'PDF Filename',
                    'name' => 'filename',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '25',
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
                        'value' => 'document',
                    ),
                ),
                // TBD: keep the following? or re-org?
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'tax_payment',
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
