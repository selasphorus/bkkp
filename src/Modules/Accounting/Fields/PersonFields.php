<?php

namespace atc\Bkkp\Modules\Accounting\Fields;

use WXC\Core\Contracts\FieldGroupInterface;
use WXC\Core\Contracts\PostTypeFieldGroupInterface;

// Possibly can phase this out -- only one field left
final class PersonFields implements FieldGroupInterface, PostTypeFieldGroupInterface
{
    public function getPostType(): string
    {
        return 'person';
    }

    public static function register(): void
    {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        acf_add_local_field_group( array(
            'key' => 'group_624775b57f8df',
            'title' => 'BKKP Person Fields',
            'fields' => array(
                array(
                    'key' => 'field_65e73415b2b38',
                    'label' => 'Related Content',
                    'name' => '',
                    'aria-label' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'placement' => 'top',
                    'endpoint' => 0,
                    'selected' => 0,
                ),
                array(
                    'key' => 'field_65e73424b2b39',
                    'label' => 'Related Transactions',
                    'name' => 'transactions_people',
                    'aria-label' => '',
                    'type' => 'post_object',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'post_type' => array(
                        0 => 'transaction',
                    ),
                    'post_status' => '',
                    'taxonomy' => '',
                    'return_format' => 'id',
                    'multiple' => 1,
                    'allow_null' => 0,
                    'bidirectional' => 0,
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
                        'value' => 'person',
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
