<?php 


// Fetch Booked Dates
function am_get_booked_dates() {
    $appointments = get_posts([
        'post_type' => 'appointment',
        'posts_per_page' => -1,
        'fields' => 'ids',
        
       
    ]);
		
    $booked_dates = [];
    foreach ($appointments as $appointment_id) {
        $date = get_post_meta($appointment_id, 'slot_date', true);
        if ($date) {
            $booked_dates[] = $date;
        }
    }
    return $booked_dates;
}

// Appointment Calendar Shortcode
function am_calendar_shortcode() {
    ob_start();
    ?>
    <div id="appointment-calendar"></div>
    <div id="appointment-details"></div>
    <script>
        jQuery(document).ready(function($) {
            $('#appointment-calendar').datepicker({
                dateFormat: 'yy-mm-dd',
                beforeShowDay: function(date) {
                    var dateString = $.datepicker.formatDate('yy-mm-dd', date);
                    return [true, amGetDateClass(dateString), null];
                },
                onSelect: function(dateText) {
                    amLoadAppointmentsForDate(dateText);
                }
            });

            function amGetDateClass(date) {
                var bookedDates = <?php echo json_encode(am_get_booked_dates()); ?>;
                return bookedDates.includes(date) ? 'booked-date' : '';
            }

            function amLoadAppointmentsForDate(date) {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'am_get_appointments_for_date',
                        date: date
                    },
                    success: function(response) {
                        $('#appointment-details').html(response);
                    }
                });
            }
        });
    </script>
    <style>
        .ui-datepicker .booked-date a {
            background-color: #ff6666 !important;
            color: white !important;
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('apple_mashing_calendar', 'am_calendar_shortcode');

// Get Appointments for Selected Date
function am_get_appointments_for_date() {
    if (!isset($_POST['date'])) {
        wp_die();
    }

    $date = sanitize_text_field($_POST['date']);
    $appointments = get_posts([
        'post_type' => 'appointment',
        'posts_per_page' => -1,
        'meta_query' => [
            ['key' => 'slot_date', 'value' => $date, 'compare' => '=']
        ],
        'meta_key' => 'start_time', // Sort by start_time meta key
        'orderby' => 'meta_value', // Order by the value of the start_time
        'order' => 'ASC'   ,
    ]);

    if ($appointments) {
        echo "<ul>";
        foreach ($appointments as $appointment) {
            $name = get_post_meta($appointment->ID, 'name', true);
            $quantity = get_post_meta($appointment->ID, 'quantity', true);
            $location = get_post_meta($appointment->ID, 'location', true);
			$start_time = get_post_meta($appointment->ID, 'start_time', true);
			

            echo "<li><strong>$start_time</strong> - $quantity kg  -  $name  </li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Ingen aftaler for denne dato.</p>";
    }

    wp_die();
}
add_action('wp_ajax_am_get_appointments_for_date', 'am_get_appointments_for_date');
add_action('wp_ajax_nopriv_am_get_appointments_for_date', 'am_get_appointments_for_date');


