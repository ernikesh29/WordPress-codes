<?php


// Register Custom Post Types
function am_register_custom_post_types() {
   register_post_type('appointment', [
        'public' => true,
        'label'  => 'Tidsbestillinger' ,
        'supports' => ['title', 'custom-fields'],
    ]);

    register_post_type('available_slot', [
        'public' => false,
        'show_ui' => true,
        'label' => 'Opret tid',
        'supports' => ['title', 'custom-fields'],
    ]);
}
add_action('init', 'am_register_custom_post_types');
