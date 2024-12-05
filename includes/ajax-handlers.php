<?php

// AJAX: Update End Time
function am_get_updated_end_time() {
    if (isset($_POST['slot_id']) && isset($_POST['quantity']) && isset($_POST['direction'])) {
        $slot_id = intval($_POST['slot_id']);
        $quantity = intval($_POST['quantity']);
        $direction = sanitize_text_field($_POST['direction']); // 'forward' or 'backward'
        
        // Get the start time of the selected slot
        $start_time = get_post_meta($slot_id, 'start_time', true);
        $start_time_obj = new DateTime($start_time);
        
        // Calculate the duration for the given quantity
        $time_duration = am_get_time_duration_for_quantity($quantity);
        $start_time_obj->add(new DateInterval('PT' . $time_duration . 'M'));
        
        // Determine the new end time based on user direction (forward or backward)
        if ($direction === 'forward') {
            $new_end_time = $start_time_obj->format('H:i');
        } elseif ($direction === 'backward') {
            // For backward booking, subtract the time duration
            $start_time_obj->sub(new DateInterval('PT' . ($time_duration) . 'M'));
            $new_end_time = $start_time_obj->format('H:i');
        }

        // Check if the new slot overlaps with existing booked slots
        $available_slot = am_check_for_slot_availability($slot_id, $start_time_obj, $time_duration, $direction);
        if ($available_slot) {
            wp_send_json_success(['new_end_time' => $new_end_time, 'slot_available' => true]);
        } else {
            wp_send_json_error(['message' => 'No available slot in the selected direction.']);
        }
    }

    wp_die();
}
add_action('wp_ajax_am_get_updated_end_time', 'am_get_updated_end_time');
add_action('wp_ajax_nopriv_am_get_updated_end_time', 'am_get_updated_end_time');