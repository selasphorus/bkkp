<?php

namespace atc\Bkkp\Modules\Employment\Fields;

use atc\WXC\Contracts\FieldGroupInterface;
use atc\WXC\Contracts\SubtypeFieldGroupInterface;
use atc\WXC\PostTypes\SubtypeRegistrar;

final class GigEventFields implements FieldGroupInterface, SubtypeFieldGroupInterface
{
    public function getPostType(): string
    {
        return 'event';
    }

    public function getSubtypeSlug(): string
    {
        return 'gigs';
    }

    public static function register(): void
    {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return;
        }

        //$tax = SubtypeRegistrar::getTaxonomyForPostType( 'event' ); // whx4_event_type
        $taxonomy = "event_category";

        // Event: Gig Details
        acf_add_local_field_group( array(
            'key' => 'group_5e76a88789388',
            'title' => 'Event: Gig Details',
            'fields' => array(
                array(
                    'key' => 'field_5e76aac8b7827',
                    'label' => 'Employer',
                    'name' => 'employer',
                    'aria-label' => '',
                    'type' => 'post_object',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '20',
                        'class' => '',
                        'id' => '',
                    ),
                    'post_type' => array(
                        0 => 'group',
                        1 => 'person',
                    ),
                    'post_status' => '',
                    'taxonomy' => '',
                    'return_format' => 'object',
                    'multiple' => 0,
                    'allow_null' => 0,
                    'bidirectional' => 0,
                    'ui' => 1,
                    'bidirectional_target' => array(
                    ),
                ),
                array(
                    'key' => 'field_5e76a8fe54a46',
                    'label' => 'Num. Hours',
                    'name' => 'num_hours',
                    'aria-label' => '',
                    'type' => 'number',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '8',
                        'class' => '',
                        'id' => '',
                    ),
                    'formula' => '',
                    'calculated_format' => '',
                    'blank_if_zero' => 0,
                    'readonly' => 0,
                    'default_value' => 0,
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'min' => '',
                    'max' => '',
                    'step' => '',
                ),
                array(
                    'key' => 'field_5e76a8a154a44',
                    'label' => 'Pay Rate Type',
                    'name' => 'rate_type',
                    'aria-label' => '',
                    'type' => 'radio',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '10',
                        'class' => '',
                        'id' => '',
                    ),
                    'formula' => '',
                    'calculated_format' => '',
                    'blank_if_zero' => 0,
                    'readonly' => 0,
                    'choices' => array(
                        'per_service' => 'Per Service',
                        'per_project' => 'Per Project',
                        'hourly' => 'Hourly',
                    ),
                    'allow_null' => 0,
                    'other_choice' => 0,
                    'default_value' => '',
                    'layout' => 'vertical',
                    'return_format' => 'value',
                    'save_other_choice' => 0,
                ),
                array(
                    'key' => 'field_5e76a91954a47',
                    'label' => 'Per Service/Project Rate (Gross)',
                    'name' => 'service_rate',
                    'aria-label' => '',
                    'type' => 'number',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '10',
                        'class' => '',
                        'id' => '',
                    ),
                    'formula' => '',
                    'calculated_format' => '',
                    'blank_if_zero' => 0,
                    'readonly' => 0,
                    'default_value' => 0,
                    'placeholder' => '',
                    'prepend' => '$',
                    'append' => '',
                    'min' => '',
                    'max' => '',
                    'step' => '',
                ),
                array(
                    'key' => 'field_5e76a8df54a45',
                    'label' => 'Hourly Rate',
                    'name' => 'hourly_rate',
                    'aria-label' => '',
                    'type' => 'number',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '10',
                        'class' => '',
                        'id' => '',
                    ),
                    'formula' => '',
                    'readonly' => 0,
                    'default_value' => 0,
                    'placeholder' => '',
                    'prepend' => '$',
                    'append' => '',
                    'min' => '',
                    'max' => '',
                    'step' => '',
                ),
                array(
                    'key' => 'field_5e76a9a5e2665',
                    'label' => 'Amount Due',
                    'name' => 'amount_due',
                    'aria-label' => '',
                    'type' => 'number',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '10',
                        'class' => '',
                        'id' => '',
                    ),
                    'formula' => '(hourly_rate * num_hours) + service_rate',
                    'calculated_format' => '',
                    'blank_if_zero' => 0,
                    'readonly' => 1,
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '$',
                    'append' => '',
                    'min' => '',
                    'max' => '',
                    'step' => '',
                ),
                array(
                    'key' => 'field_60269f6413500',
                    'label' => 'Amount Received',
                    'name' => 'amount_received',
                    'aria-label' => '',
                    'type' => 'number',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '10',
                        'class' => '',
                        'id' => '',
                    ),
                    'formula' => '',
                    'calculated_format' => '',
                    'blank_if_zero' => 0,
                    'readonly' => 0,
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'min' => '',
                    'max' => '',
                    'step' => '',
                ),
                array(
                    'key' => 'field_6273d8b676a94',
                    'label' => 'Related Documents',
                    'name' => 'documents',
                    'aria-label' => '',
                    'type' => 'post_object',
                    'instructions' => 'e.g. Paycheck/Earnings Statement',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '20',
                        'class' => '',
                        'id' => '',
                    ),
                    'post_type' => array(
                        0 => 'document',
                    ),
                    'post_status' => '',
                    'taxonomy' => '',
                    'return_format' => 'object',
                    'multiple' => 0,
                    'allow_null' => 0,
                    'bidirectional' => 0,
                    'ui' => 1,
                    'bidirectional_target' => array(
                    ),
                ),
                array(
                    'key' => 'field_66281fd113d70',
                    'label' => 'Transaction',
                    'name' => 'transaction',
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
                    'return_format' => 'object',
                    'multiple' => 0,
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
                        'value' => 'event',
                    ),
                    array(
                        'param' => 'post_taxonomy',
                        'operator' => '==',
                        'value' => 'event_category:gigs',
                    ),
                ),
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'event',
                    ),
                    array(
                        'param' => 'post_taxonomy',
                        'operator' => '==',
                        'value' => 'event_category:employment',
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




