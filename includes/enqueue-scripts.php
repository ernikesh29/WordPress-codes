<?php
// Enqueue Scripts and Styles
function am_enqueue_scripts() {
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_script('am-scripts', AM_PLUGIN_URL . 'assets/scripts.js', ['jquery'], null, true);
    wp_localize_script('am-scripts', 'amAjax', ['ajaxurl' => admin_url('admin-ajax.php')]);
}
add_action('wp_enqueue_scripts', 'am_enqueue_scripts');