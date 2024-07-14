<?php
/*
Plugin Name: Toffu AI WP Plugin
Description: Plugin to enhance WordPress with AI capabilities
Version: 0.0.1
Author: Toffu AI, Inc.
*/

// Function to insert GTM code in the header
function gtm_install_header() {
    $gtm_id = get_option('gtm_id');
    if ($gtm_id) {
        echo "<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{$gtm_id}');</script>
<!-- End Google Tag Manager -->";
    }
}

// Function to insert GTM noscript code in the body
function gtm_install_body() {
    $gtm_id = get_option('gtm_id');
    if ($gtm_id) {
        echo "<!-- Google Tag Manager (noscript) -->
<noscript><iframe src='https://www.googletagmanager.com/ns.html?id={$gtm_id}'
height='0' width='0' style='display:none;visibility:hidden'></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->";
    }
}

// Register REST API endpoint
function gtm_register_rest_route() {
    register_rest_route('gtm/v1', '/install', array(
        'methods' => 'POST',
        'callback' => 'gtm_rest_install',
        'permission_callback' => function () {
            return current_user_can('manage_options');
        }
    ));
}

// Callback function for REST API endpoint
function gtm_rest_install($request) {
    $gtm_id = sanitize_text_field($request->get_param('gtm_id'));
    if ($gtm_id) {
        update_option('gtm_id', $gtm_id);
        return new WP_REST_Response('GTM ID updated successfully.', 200);
    } else {
        return new WP_REST_Response('Invalid GTM ID.', 400);
    }
}

// Hook to add GTM code to the header
add_action('wp_head', 'gtm_install_header');

// Hook to add GTM noscript code to the body (after opening body tag)
add_action('wp_body_open', 'gtm_install_body');

// Hook to register REST API endpoint
add_action('rest_api_init', 'gtm_register_rest_route');


// Register custom REST API endpoint
add_action('rest_api_init', function () {
    register_rest_route('yoast/v1', '/update-meta-description', array(
        'methods' => 'POST',
        'callback' => 'update_yoast_meta_description',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));
});

function update_yoast_meta_description(WP_REST_Request $request) {
    // Get parameters from request
    $post_id = $request->get_param('post_id');
    $meta_description = $request->get_param('meta_description');

    // Check if post exists
    $post = get_post($post_id);
    if (!$post) {
        return new WP_Error('invalid_post', 'Invalid post ID', array('status' => 404));
    }

    // Update Yoast meta description
    update_post_meta($post_id, '_yoast_wpseo_metadesc', sanitize_text_field($meta_description));

    return new WP_REST_Response('Meta description updated successfully', 200);
}


// Add admin menu and settings page
function toffu_ai_add_admin_menu() {
    add_options_page('Toffu AI', 'Toffu AI', 'manage_options', 'toffu-ai', 'toffu_ai_settings_page');
}
add_action('admin_menu', 'toffu_ai_add_admin_menu');

// Settings page content
function toffu_ai_settings_page() {
    ?>
    <div class="wrap">
        <h1>Toffu AI Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('toffu_ai_settings_group');
            do_settings_sections('toffu-ai');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
function toffu_ai_register_settings() {
    register_setting('toffu_ai_settings_group', 'gtm_id');
    add_settings_section('toffu_ai_settings_section', 'Settings', null, 'toffu-ai');
    add_settings_field('gtm_id', 'GTM ID', 'gtm_id_callback', 'toffu-ai', 'toffu_ai_settings_section');
}
add_action('admin_init', 'toffu_ai_register_settings');

// Callback function for GTM ID field
function gtm_id_callback() {
    $gtm_id = get_option('gtm_id');
    echo '<input type="text" name="gtm_id" value="' . esc_attr($gtm_id) . '" placeholder="GTM-XXXXXX" />';
}
?>