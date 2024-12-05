<?php
/*
Plugin Name: Apple Mashing Booking Enhanced
Description: 
Version: 2.6
Author: Nikesh
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define constants
define('AM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include files
require_once AM_PLUGIN_DIR . 'includes/post-types.php';
require_once AM_PLUGIN_DIR . 'includes/enqueue-scripts.php';
require_once AM_PLUGIN_DIR . 'includes/meta-boxes.php';
require_once AM_PLUGIN_DIR . 'includes/calendar.php';
require_once AM_PLUGIN_DIR . 'includes/helpers.php';

// Get future available slots for booking.
function sb_get_available_slots() {
    $slots = get_posts([
        'post_type' => 'available_slot',
        'posts_per_page' => -1,
    ]);

    $available_slots = [];
    foreach ($slots as $slot) {
        $slot_id = $slot->ID;
        $date = get_post_meta($slot_id, 'date', true);
        $start_time = get_post_meta($slot_id, 'start_time', true);
        $location = get_post_meta($slot_id, 'location', true);
        $available_slots[] = [
            'id' => $slot_id,
            'date' => $date,
            'start_time' => $start_time,
            'location' => $location,
        ];
    }

    return $available_slots;
}

// Get the next available time after the latest booked slot.
function sb_get_next_available_time($slot_id) {
    $appointments = get_posts([
        'post_type' => 'appointment',
        'meta_query' => [
            [
                'key' => 'slot_id',
                'value' => $slot_id,
                'compare' => '='
            ],
        ],
        'orderby' => 'meta_value',
        'order' => 'DESC',
        'meta_key' => 'end_time',
    ]);

    $latest_end_time = get_post_meta($appointments[0]->ID, 'end_time', true) ?: '09:00';
    $end_time_obj = new DateTime($latest_end_time);
    $end_time_obj->add(new DateInterval('PT30M'));  // Add 30 minutes interval.
    
    return $end_time_obj->format('H:i');
}

// Function to calculate the time based on weight
function sb_calculate_slot_time($weight) {
    if ($weight >= 50 && $weight <= 75) return 5;
    if ($weight > 75 && $weight <= 125) return 10;
    if ($weight > 125 && $weight <= 200) return 15;
    if ($weight > 200 && $weight <= 300) return 25;
    if ($weight > 300 && $weight <= 400) return 30;
    if ($weight > 400 && $weight <= 500) return 40;
    if ($weight > 500 && $weight <= 600) return 45;
    if ($weight > 600 && $weight <= 700) return 55;
    if ($weight > 700 && $weight <= 800) return 60;
    if ($weight > 800 && $weight <= 900) return 70;
    if ($weight > 900 && $weight <= 1000) return 75;
    if ($weight > 1000 && $weight <= 1100) return 85;
    if ($weight > 1100 && $weight <= 1200) return 90;
    return 0; // Default duration
}

// Handle booking form submission.
function sb_handle_booking_form() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sb_email'])) {
        $slot_id = intval($_POST['sb_slot']);
        $slot_time = sanitize_text_field($_POST['sb_next_time']);
        $slot_date = get_post_meta($slot_id, 'date', true);

        // Validate that the next time was calculated
        if (empty($slot_time)) {
            echo "<p>Fejl: Næste tid kunne ikke beregnes.</p>";
            return;
        }

        // Get the weight from the form
        $weight = intval($_POST['am_quantity']); // Assuming the weight is being passed as 'am_quantity'

        // Calculate the session duration based on the weight
        $duration_minutes = sb_calculate_slot_time($weight);
        
        // Set the end time based on the duration
        $start_time_obj = new DateTime($slot_time);
        $start_time_obj->add(new DateInterval("PT{$duration_minutes}M"));  // Add calculated duration

        $end_time = $start_time_obj->format('H:i');

        // Create a new appointment post.
        $appointment = [
            'post_title' => 'Appointment for ' . sanitize_text_field($_POST['sb_name']),
            'post_type' => 'appointment',
            'post_status' => 'publish',
        ];

        $appointment_id = wp_insert_post($appointment);

        // Save the relevant metadata for the appointment.
        update_post_meta($appointment_id, 'slot_id', $slot_id);
        update_post_meta($appointment_id, 'start_time', $slot_time);
        update_post_meta($appointment_id, 'end_time', $end_time);  // Use the calculated end time
        update_post_meta($appointment_id, 'slot_date', $slot_date);  // Save the selected date

        // Save other information like name, email, mobile, and quantity.
        update_post_meta($appointment_id, 'name', sanitize_text_field($_POST['sb_name']));
        update_post_meta($appointment_id, 'email', sanitize_email($_POST['sb_email']));
        update_post_meta($appointment_id, 'mobile', sanitize_text_field($_POST['sb_mobile']));
        update_post_meta($appointment_id, 'quantity', $weight);

        // Send email notification (optional).
        wp_mail(sanitize_email($_POST['sb_email']), 'Booking Confirmation', 'Your appointment is booked.');

        echo "<p>Din bestilling er bekræftet kl. $slot_time, den $slot_date.</p>";
    }
}

// Booking form shortcode.
function sb_booking_form_shortcode() {
    ob_start();
    sb_handle_booking_form();

    $available_slots = sb_get_available_slots();
    ?>
    <form id="booking-form" method="post" action="">
        <div class="form-group">
            <label for="sb_slot">Vælg dato og sted:</label>
            <select id="sb_slot" name="sb_slot" required>
                <option value="">Vælg dato og sted</option>
                <?php foreach ($available_slots as $slot): ?>
                    <option value="<?php echo $slot['id']; ?>" data-start-time="<?php echo $slot['location']; ?>">
                        <?php echo $slot['date'] . ' - ' . $slot['location']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- <div class="form-group col-6">
            <label for="sb_slot_time">Vælg tidspunkt:</label>
            <select id="sb_slot_time" name="sb_slot_time">
                <option value="">Vælg tidspunkt</option>
                 Time options will be populated here 
            </select>
        </div> -->
        <div class="form-group col-6">
        <label for="sb_next_time">Næste tid:</label>
    <input type="text" id="sb_next_time" name="sb_next_time" readonly placeholder="Vælg dato og sted først">

</div>

        <div class="form-group col-6">
        <label for="am_quantity">Vælg mængde (kg):</label>
        <select id="am_quantity" name="am_quantity" required>
            <?php for ($i = 50; $i <= 1200; $i += 50): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?> kg</option>
            <?php endfor; ?>
        </select>
            </div>
        <div class="form-group">
            <label for="sb_name">Navn:</label>
            <input type="text" id="sb_name" name="sb_name" required>
        </div>

        <div class="form-group">
            <label for="sb_email">Email:</label>
            <input type="email" id="sb_email" name="sb_email" required>
        </div>

        <div class="form-group">
            <label for="sb_mobile">Mobil:</label>
            <input type="tel" id="sb_mobile" name="sb_mobile" required>
        </div>

        <button type="submit">BESTIL TID</button>
    </form>

    <script>

jQuery(document).ready(function($) {
    function calculateEndTime() {
        var slotId = $('#sb_slot').val();
        var weight = $('#am_quantity').val();

        if (slotId && weight) {
            var selectedOption = $('#sb_slot option:selected');
            var startTime = selectedOption.data('start-time'); // Get the slot's start time

            if (startTime) {
                $.ajax({
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    method: 'POST',
                    data: {
                        action: 'sb_calculate_end_time',
                        start_time: startTime,
                        weight: weight
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#sb_next_time').val(startTime + ' - ' + response.data.end_time);
                        } else {
                            $('#sb_next_time').val('Ingen ledige tider');
                        }
                    },
                    error: function() {
                        $('#sb_next_time').val('Fejl ved beregning af tid');
                    }
                });
            }
        } else {
            $('#sb_next_time').val('Vælg dato først');
        }
    }

    // Trigger end time calculation when slot or weight is changed
    $('#sb_slot, #am_quantity').on('change', calculateEndTime);
});


jQuery(document).ready(function($) {
    function fetchNextAvailableTime() {
        var slotId = $('#sb_slot').val();
        var weight = $('#am_quantity').val();

        if (slotId && weight) {
            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                method: 'POST',
                data: {
                    action: 'sb_get_next_available_time',
                    slot_id: slotId,
                    weight: weight
                },
                success: function(response) {
                    if (response.success) {
                        $('#sb_next_time').val(response.data.next_time);
                    } else {
                        $('#sb_next_time').val('Ingen ledige tider');
                    }
                },
                error: function() {
                    $('#sb_next_time').val('Fejl ved indlæsning af tid');
                }
            });
        } else {
            $('#sb_next_time').val('Vælg dato først');
        }
    }

    // Trigger fetching next available time when slot or weight is changed
    $('#sb_slot, #am_quantity').on('change', fetchNextAvailableTime);
});


    jQuery(document).ready(function($) {
        $('#sb_slot').on('change', function() {
            var selectedOption = $(this).find(':selected');
            var slotId = $(this).val();
            var startTime = selectedOption.data('start-time');

            // Reset the time dropdown
            $('#sb_slot_time').empty().append('<option value="">Select Time</option>');

            if (slotId) {
                $.ajax({
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    method: 'POST',
                    data: {
                        action: 'sb_get_available_times',
                        slot_id: slotId,
                        start_time: startTime
                    },
                    success: function(response) {
                        if (response.success) {
                            var times = response.data;
                            times.forEach(function(time) {
                                $('#sb_slot_time').append('<option value="' + time + '">' + time + '</option>');
                            });
                        } else {
                            $('#sb_slot_time').append('<option value="">No available times</option>');
                        }
                    },
                    error: function() {
                        $('#sb_slot_time').append('<option value="">Error fetching times</option>');
                    }
                });
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('apple_mashing_booking', 'sb_booking_form_shortcode');

function sb_calculate_end_time_ajax() {
    if (!isset($_POST['start_time']) || !isset($_POST['weight'])) {
        wp_send_json_error(['message' => 'Missing required parameters']);
    }

    $start_time = sanitize_text_field($_POST['start_time']);
    $weight = intval($_POST['weight']);

    // Calculate the duration based on weight
    $duration_minutes = sb_calculate_slot_time($weight);

    // Calculate the end time
    try {
        $start_time_obj = new DateTime($start_time);
        $start_time_obj->add(new DateInterval("PT{$duration_minutes}M"));

        $end_time = $start_time_obj->format('H:i');
        wp_send_json_success(['end_time' => $end_time]);
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Invalid start time']);
    }
}
add_action('wp_ajax_sb_calculate_end_time', 'sb_calculate_end_time_ajax');
add_action('wp_ajax_nopriv_sb_calculate_end_time', 'sb_calculate_end_time_ajax');


function sb_get_next_available_time_ajax() {
    if (!isset($_POST['slot_id']) || !isset($_POST['weight'])) {
        wp_send_json_error(['message' => 'Missing required parameters']);
    }

    $slot_id = intval($_POST['slot_id']);
    $weight = intval($_POST['weight']);

    // Calculate the duration based on weight
    $duration_minutes = sb_calculate_slot_time($weight);

    // Get the latest booked time for the slot
    $appointments = get_posts([
        'post_type' => 'appointment',
        'meta_query' => [
            ['key' => 'slot_id', 'value' => $slot_id, 'compare' => '='],
        ],
        'orderby' => 'meta_value',
        'order' => 'DESC',
        'meta_key' => 'end_time',
    ]);

    $latest_end_time = get_post_meta($appointments[0]->ID, 'end_time', true) ?: '09:00';

    // Calculate the next available time
    $start_time_obj = new DateTime($latest_end_time);
    $start_time_obj->add(new DateInterval("PT{$duration_minutes}M"));

    $next_available_time = $start_time_obj->format('H:i');

    // Ensure the next time is within working hours
    $end_of_day = new DateTime('21:00');
    if ($start_time_obj > $end_of_day) {
        wp_send_json_error(['message' => 'No available times today']);
    }

    wp_send_json_success(['next_time' => $next_available_time]);
}
add_action('wp_ajax_sb_get_next_available_time', 'sb_get_next_available_time_ajax');
add_action('wp_ajax_nopriv_sb_get_next_available_time', 'sb_get_next_available_time_ajax');

// Fetch available times for the selected slot.
function sb_get_available_times() {
    if (!isset($_POST['slot_id']) || !isset($_POST['start_time'])) {
        wp_send_json_error(['message' => 'Missing required parameters']);
    }

    $slot_id = intval($_POST['slot_id']);
    $start_time = sanitize_text_field($_POST['start_time']);

    // Get the latest booked time for the slot
    $appointments = get_posts([
        'post_type' => 'appointment',
        'meta_query' => [
            ['key' => 'slot_id', 'value' => $slot_id, 'compare' => '='],
        ],
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'meta_key' => 'start_time',
    ]);

    $latest_end_time = $start_time;
    foreach ($appointments as $appointment) {
        $appointment_end_time = get_post_meta($appointment->ID, 'end_time', true);
        if ($appointment_end_time > $latest_end_time) {
            $latest_end_time = $appointment_end_time;
        }
    }

    // Generate available time slots after the latest end time
    $times = [];
    $interval = 30; // 30-minute interval
    $current_time = strtotime($latest_end_time);
    $end_of_day = strtotime('21:00');

    while ($current_time < $end_of_day) {
        $current_time += $interval * 60;
        $times[] = date('H:i', $current_time);
    }

    wp_send_json_success($times);
}
add_action('wp_ajax_sb_get_available_times', 'sb_get_available_times');
add_action('wp_ajax_nopriv_sb_get_available_times', 'sb_get_available_times');
?>