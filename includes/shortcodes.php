<?php

// Booking Form Shortcode
function am_booking_form_shortcode() {
    ob_start();
    include AM_PLUGIN_DIR . 'includes/booking-form.php';
    return ob_get_clean();
}
add_shortcode('apple_mashing_booking', 'am_booking_form_shortcode');

// Calendar Shortcode
function am_calendar_shortcode() {
    ob_start();
    include AM_PLUGIN_DIR . 'includes/calendar.php';
    return ob_get_clean();
}
add_shortcode('apple_mashing_calendar', 'am_calendar_shortcode');
