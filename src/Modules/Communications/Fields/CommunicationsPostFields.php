<?php

namespace atc\Bkkp\Modules\Communications\Fields;

use atc\WHx4\Core\Contracts\FieldGroupInterface;
use atc\WHx4\Core\Contracts\SubtypeFieldGroupInterface;
use atc\WHx4\Core\SubtypeRegistrar;

// TODO: rename all fields (keys/names) according to whx4 naming conventions
final class CommunicationsPostFields implements FieldGroupInterface, SubtypeFieldGroupInterface
{
    public function getPostType(): string
    {
        return 'post';
    }

    public function getSubtypeSlug(): string
    {
        return 'communications';
    }

    public static function register(): void
    {
        error_log( '=== CommunicationsPostFields: register()) ===' );
        if ( !function_exists('acf_add_local_field_group') ) return;

        $taxonomy = "category";

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

        acf_add_local_field_group( array(
            'key' => 'group_64947709b3af9',
            'title' => 'Call Info',
            'fields' => array(
                array(
                    'key' => 'field_64947729a1ac4',
                    'label' => 'Call Type',
                    'name' => 'call_type',
                    'aria-label' => '',
                    'type' => 'radio',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'formula' => '',
                    'calculated_format' => '',
                    'blank_if_zero' => 0,
                    'readonly' => 0,
                    'choices' => array(
                        'outgoing' => 'Outgoing',
                        'incoming' => 'Incoming (call received)',
                        'reported' => 'Reported by third party',
                    ),
                    'default_value' => 'outgoing',
                    'return_format' => 'value',
                    'allow_null' => 0,
                    'other_choice' => 0,
                    'layout' => 'horizontal',
                    'save_other_choice' => 0,
                ),
                array(
                    'key' => 'field_64947709b8464',
                    'label' => 'Participants',
                    'name' => 'participants',
                    'aria-label' => '',
                    'type' => 'post_object',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_64947709b84a6',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '15',
                        'class' => '',
                        'id' => '',
                    ),
                    'formula' => '',
                    'calculated_format' => '',
                    'blank_if_zero' => 0,
                    'readonly' => 0,
                    'post_type' => array(
                        0 => 'person',
                    ),
                    'post_status' => '',
                    'taxonomy' => '',
                    'return_format' => 'object',
                    'multiple' => 1,
                    'allow_null' => 0,
                    'ui' => 1,
                    'bidirectional_target' => array(
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post',
                    ),
                    array(
                        'param' => 'post_taxonomy',
                        'operator' => '==',
                        'value' => 'category:phone-calls',
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

        acf_add_local_field_group( array(
            'key' => 'group_64947552a64e6',
            'title' => 'Correspondence Info',
            'fields' => array(
                array(
                    'key' => 'field_64947552aac3a',
                    'label' => 'Correspondent(s) (other than me...)',
                    'name' => 'correspondents',
                    'aria-label' => '',
                    'type' => 'post_object',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_64947552aac73',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '30',
                        'class' => '',
                        'id' => '',
                    ),
                    'formula' => '',
                    'calculated_format' => '',
                    'blank_if_zero' => 0,
                    'readonly' => 0,
                    'post_type' => array(
                        0 => 'person',
                    ),
                    'post_status' => '',
                    'taxonomy' => '',
                    'return_format' => 'object',
                    'multiple' => 1,
                    'allow_null' => 0,
                    'ui' => 1,
                    'bidirectional_target' => array(
                    ),
                ),
                array(
                    'key' => 'field_64947552aac73',
                    'label' => 'Message From',
                    'name' => 'msg_from',
                    'aria-label' => '',
                    'type' => 'post_object',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_6414d8cddad43',
                                'operator' => '==',
                                'value' => 'correspondence',
                            ),
                            array(
                                'field' => 'field_64947552aac3a',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '15',
                        'class' => '',
                        'id' => '',
                    ),
                    'formula' => '',
                    'calculated_format' => '',
                    'blank_if_zero' => 0,
                    'readonly' => 0,
                    'post_type' => array(
                        0 => 'person',
                    ),
                    'taxonomy' => '',
                    'return_format' => 'object',
                    'multiple' => 0,
                    'allow_null' => 0,
                    'ui' => 1,
                    'bidirectional_target' => array(
                    ),
                ),
                array(
                    'key' => 'field_64947552aacac',
                    'label' => 'Message To',
                    'name' => 'msg_to',
                    'aria-label' => '',
                    'type' => 'post_object',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_6414d8cddad43',
                                'operator' => '==',
                                'value' => 'correspondence',
                            ),
                            array(
                                'field' => 'field_64947552aac3a',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '15',
                        'class' => '',
                        'id' => '',
                    ),
                    'formula' => '',
                    'calculated_format' => '',
                    'blank_if_zero' => 0,
                    'readonly' => 0,
                    'post_type' => array(
                        0 => 'person',
                    ),
                    'taxonomy' => '',
                    'return_format' => 'object',
                    'multiple' => 1,
                    'allow_null' => 0,
                    'ui' => 1,
                    'bidirectional_target' => array(
                    ),
                ),
                array(
                    'key' => 'field_64948bab6d00e',
                    'label' => 'Subject Line',
                    'name' => 'subject_line',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'formula' => '',
                    'calculated_format' => '',
                    'blank_if_zero' => 0,
                    'readonly' => 0,
                    'default_value' => '',
                    'maxlength' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ),
                array(
                    'key' => 'field_64947552aace5',
                    'label' => 'Message ID(s)',
                    'name' => 'msg_ids',
                    'aria-label' => '',
                    'type' => 'post_object',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_6414d8cddad43',
                                'operator' => '==',
                                'value' => 'correspondence',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '15',
                        'class' => '',
                        'id' => '',
                    ),
                    'formula' => '',
                    'calculated_format' => '',
                    'blank_if_zero' => 0,
                    'readonly' => 0,
                    'post_type' => array(
                        0 => 'person',
                    ),
                    'taxonomy' => '',
                    'return_format' => 'object',
                    'multiple' => 1,
                    'allow_null' => 0,
                    'ui' => 1,
                    'bidirectional_target' => array(
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post',
                    ),
                    array(
                        'param' => 'post_taxonomy',
                        'operator' => '==',
                        'value' => 'category:correspondence',
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
