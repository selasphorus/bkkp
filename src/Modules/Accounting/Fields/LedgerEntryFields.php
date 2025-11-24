<?php

namespace atc\Bkkp\Modules\XXX\Fields;

use atc\WXC\Contracts\FieldGroupInterface;

// TODO: rename all fields (keys/names) according to whx4 naming conventions
final class XXXFields implements FieldGroupInterface
{
    public static function register(): void
    {
        //error_log( '=== XXXFields: register()) ===' );
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
    }
}
