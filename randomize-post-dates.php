<?php
/*
Plugin Name: Randomize Post Dates with Progress Bar
Description: A plugin to randomize the published dates of all posts between the current date and 3 years ago with a progress bar.
Version: 1.2
Author: David Oak
*/

// Add admin page
add_action('admin_menu', 'rpd_add_admin_page');
function rpd_add_admin_page() {
    add_submenu_page('tools.php', 'Randomize Post Dates', 'Randomize Dates', 'manage_options', 'randomize-post-dates', 'rpd_admin_page_callback');
}

// Enqueue scripts
add_action('admin_enqueue_scripts', 'rpd_enqueue_scripts');
function rpd_enqueue_scripts($hook) {
    if ($hook != 'tools_page_randomize-post-dates') {
        return;
    }

    wp_enqueue_script('rpd-ajax-script', plugin_dir_url(__FILE__) . 'ajax.js', array('jquery'), '1.0', true);
    wp_localize_script('rpd-ajax-script', 'rpd_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}

// Admin page callback
function rpd_admin_page_callback() {
    global $wpdb;
    $totalPosts = $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' AND post_content NOT LIKE '%[randomized]%'");

    echo '<h2>Randomize Post Dates</h2>';
    echo '<p>Total Posts: ' . $totalPosts . '</p>';
    echo '<input type="hidden" id="total_posts" value="' . $totalPosts . '">';
    echo '<p>Posts Left to Randomize: <span id="posts-left">' . $totalPosts . '</span></p>';
    echo '<p>Posts Randomized: <span id="posts-done">0</span></p>';
    echo '<progress id="progress-bar" value="0" max="' . $totalPosts . '"></progress><br>';
    echo '<button id="rpd_submit">Start Randomizing</button>';
    echo '<p>This will change the publish post dates to a random date between now and 3 years ago. Dates are updated every 1 second</p>';
    echo '<p>The plugin will ignore posts that have already been randomised</p>';
	echo '<p>Built by David Oak</p>';
}

// Handle AJAX request
add_action('wp_ajax_randomize_post_date', 'rpd_randomize_single_post_date');
function rpd_randomize_single_post_date() {
    global $wpdb;

    // Introduce a 2-second delay
    sleep(1);

    $post = $wpdb->get_row("SELECT ID FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' AND post_content NOT LIKE '%[randomized]%' LIMIT 1");

    if ($post) {
        $end_date = strtotime('now');
        $start_date = strtotime('-3 years');
        $random_date = date("Y-m-d H:i:s", mt_rand($start_date, $end_date));

        $wpdb->update($wpdb->posts, array('post_date' => $random_date, 'post_date_gmt' => get_gmt_from_date($random_date), 'post_content' => $post->post_content . ' [randomized]'), array('ID' => $post->ID));
        echo 'success';
    } else {
        echo 'done';
    }

    wp_die();
}

