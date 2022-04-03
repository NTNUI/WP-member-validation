<?php
/**
 * Plugin Name: NTNUI Member Integration
 * Description: Log users in to a given account if they are registerd members of given group and NTNUI (Together with e.g. WPFront User Role Editor users can get permission to view restricted pages etc.)
 * Author: Eivind Dalholt
 * Version: 1.0.0
 */

require_once dirname( __FILE__ ) .'/NTNUI-login.php';

//From: https://travis.media/where-do-i-store-an-api-key-in-wordpress/

add_action('admin_menu', 'wpdocs_register_my_api_keys_page');
function wpdocs_register_my_api_keys_page() {
    //Docs: https://developer.wordpress.org/reference/functions/add_menu_page/
    add_menu_page(
        'NTNUI members',
        'NTNUI members',
        'manage_options',
        'NTNUI-api-keys',
        'add_api_keys_callback',
        plugin_dir_url( __FILE__ ).'/logo.png'
    );
}
 
// The admin page containing the form
function add_api_keys_callback() { ?>
    <div class="wrap"><div id="icon-tools" class="icon32"></div>
        <h2>NTNUI member settings</h2>
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
            <h3>API Key</h3>
            <input type="text" name="api_key" placeholder="Enter API Key" <?php if(get_option('api_key') != ""){echo 'value="'. get_option('api_key'). '"';} ?>>
            <h3>Group Slug</h3>
            <input type="text" name="group_slug" placeholder="Enter group slug" <?php if(get_option('group_slug') != ""){echo 'value="'. get_option('group_slug'). '"';} ?>>
            <h3>WP username for logged in users (existing user)</h3>
            <input type="text" name="login_username" placeholder="Enter username (existing user)" <?php if(get_option('login_username') != ""){echo 'value="'. get_option('login_username'). '"';} ?>>
            <input type="hidden" name="action" value="process_form">			 
            <br><br>
            <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update API credentials"  />
        </form> 
    </div>
    <?php
}

// Submit functionality
function submit_api() {
    if (isset($_POST['api_key']) && isset($_POST['group_slug']) && isset($_POST['login_username'])) {
        $api_key = sanitize_text_field( $_POST['api_key'] );
        $group_slug = sanitize_text_field( $_POST['group_slug'] );
        $login_username = sanitize_text_field( $_POST['login_username'] );

        $api_exists = get_option('api_key');
        if (!empty($api_key) && !empty($api_exists)) {
            update_option('api_key', $api_key);
        } else {
            add_option('api_key', $api_key);
        }

        $group_exists = get_option('group_slug');
        if (!empty($group_slug) && !empty($group_exists)) {
            update_option('group_slug', $group_slug);
        } else {
            add_option('group_slug', $group_slug);
        }

        $username_exists = get_option('login_username');
        if (!empty($login_username) && !empty($username_exists)) {
            update_option('login_username', $login_username);
        } else {
            add_option('login_username', $login_username);
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}
add_action( 'admin_post_nopriv_process_form', 'submit_api' );
add_action( 'admin_post_process_form', 'submit_api' );
?>