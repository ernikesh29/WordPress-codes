<?php

function am_get_time_duration_for_quantity($quantity) {
    if ($quantity >= 50 && $quantity <= 75) {
        return 5;   // 5 minutes
    } elseif ($quantity > 75 && $quantity <= 125) {
        return 10;  // 10 minutes
    } elseif ($quantity > 125 && $quantity <= 200) {
        return 15;  // 15 minutes
    } elseif ($quantity > 200 && $quantity <= 300) {
        return 25;  // 25 minutes
    } elseif ($quantity > 300 && $quantity <= 400) {
        return 30;  // 30 minutes
    } elseif ($quantity > 400 && $quantity <= 500) {
        return 40;  // 40 minutes
    } elseif ($quantity > 500 && $quantity <= 600) {
        return 45;  // 45 minutes
    } elseif ($quantity > 600 && $quantity <= 700) {
        return 55;  // 55 minutes
    } elseif ($quantity > 700 && $quantity <= 800) {
        return 60;  // 60 minutes
    } elseif ($quantity > 800 && $quantity <= 900) {
        return 70;  // 70 minutes
    } elseif ($quantity > 900 && $quantity <= 1000) {
        return 75;  // 75 minutes
    } elseif ($quantity > 1000 && $quantity <= 1100) {
        return 85;  // 85 minutes
    } elseif ($quantity > 1100 && $quantity <= 1200) {
        return 90;  // 90 minutes
    }
    return 0; // Default 0 minutes if out of range
}


function am_update_email_from($email) {
    return 'info@mobilmost.dk';
}
add_filter('wp_mail_from', 'am_update_email_from');

function am_update_email_name($name) {
    return 'Mobilmost'; // Set a friendly name for the "From" field
}
add_filter('wp_mail_from_name', 'am_update_email_name');