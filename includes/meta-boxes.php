<?php


// Add Meta Box for Available Slots
function am_add_available_slot_meta_box() {
    add_meta_box(
        'available_slot_details',
        'Slot Details',
        'am_render_available_slot_meta_box',
        'available_slot',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'am_add_available_slot_meta_box');

function am_render_available_slot_meta_box($post) {
    $date = get_post_meta($post->ID, 'date', true);
    $location = get_post_meta($post->ID, 'location', true);
    $start_time = get_post_meta($post->ID, 'start_time', true);
    ?>
    <p>
        <label for="slot_date">Date:</label>
        <input type="date" id="slot_date" name="slot_date" value="<?php echo esc_attr($date); ?>" required>
    </p>
    <p>
        <label for="location">Location:</label>
        <input type="text" id="location" name="location" value="<?php echo esc_attr($location); ?>" required>
    </p>
    <p>
        <label for="start_time">Start Time:</label>
        <input type="time" id="start_time" name="start_time" value="<?php echo esc_attr($start_time); ?>" required>
    </p>
    <?php
}


// Save Meta Fields for Available Slots
function am_save_available_slot_meta($post_id) {
    if (array_key_exists('slot_date', $_POST)) {
        update_post_meta($post_id, 'date', sanitize_text_field($_POST['slot_date']));
    }
    if (array_key_exists('location', $_POST)) {
        update_post_meta($post_id, 'location', sanitize_text_field($_POST['location']));
    }
    if (array_key_exists('start_time', $_POST)) {
        update_post_meta($post_id, 'start_time', sanitize_text_field($_POST['start_time']));
    }
}

add_action('save_post_available_slot', 'am_save_available_slot_meta');


// Add Meta Boxes for Appointments
function am_appointment_meta_boxes() {
    add_meta_box(
        'am_appointment_details',
        'Appointment Details',
        'am_render_appointment_meta_box',
        'appointment',
        'normal',
        'default'
    );
}
add_action('add_meta_boxes', 'am_appointment_meta_boxes');

// Render Meta Box
function am_render_appointment_meta_box($post) {
    $start_time = get_post_meta($post->ID, 'start_time', true);
    ?>
    <label for="am_start_time">Start Time:</label>
    <input type="time" id="am_start_time" name="am_start_time" value="<?php echo esc_attr($start_time); ?>" />
    <?php
}

