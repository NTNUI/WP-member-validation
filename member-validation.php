<?php
/**
 * Plugin Name: NTNUI Member Integration
 * Description: Log users in to a given account if they are registerd members of given group and NTNUI (Together with e.g. WPFront User Role Editor users can get permission to view restricted pages etc.)
 * Author: Eivind Dalholt
 * Version: 2.0.1
 */

require_once dirname( __FILE__ ) .'/NTNUI-login.php';

//From: https://travis.media/where-do-i-store-an-api-key-in-wordpress/

add_action('admin_menu', 'NTNUI_register_config_data_page');
function NTNUI_register_config_data_page() {
    //Docs: https://developer.wordpress.org/reference/functions/add_menu_page/
    add_menu_page(
        'NTNUI members',
        'NTNUI members',
        'manage_options',
        'NTNUI-config',
        'add_config_data_callback',
        plugin_dir_url( __FILE__ ).'/img/logo.png'
    );
}
 
// The admin page containing the form
function add_config_data_callback() { ?>
    <div class="wrap"><div id="icon-tools" class="icon32"></div>
        <h2>NTNUI login settings</h2>
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
            <h3>Group Slug (Remember uppercase on first letter)</h3>
            <input type="text" name="group_slug" placeholder="Enter group slug" <?php if(get_option('group_slug') != ""){echo 'value="'. get_option('group_slug'). '"';} ?>>
            <h3>Role for new users (Permission control)</h3>
            <input type="text" name="access_type" placeholder="Enter role for new users" <?php echo 'value="'. get_option('access_type', 'subscriber'). '"'; ?>>
            <input type="hidden" name="action" value="process_form">			 
            <br><br>
            <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update"  />
        </form> 
    </div>
    <?php
}

// Submit functionality
function submit_data() {
    if (isset($_POST['group_slug']) && isset($_POST['access_type'])) {
        $group_slug = sanitize_text_field( $_POST['group_slug'] );
        $access_type = strtolower(sanitize_text_field( $_POST['access_type'] ));

        $group_exists = get_option('group_slug');
        if (!empty($group_slug) && !empty($group_exists)) {
            update_option('group_slug', $group_slug);
        } else {
            add_option('group_slug', $group_slug);
        }

        $access_type_exists = get_option('access_type');
        if (!empty($access_type) && !empty($access_type_exists)) {
            update_option('access_type', $access_type);
        } else {
            add_option('access_type', $access_type);
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}
add_action( 'admin_post_nopriv_process_form', 'submit_data' );
add_action( 'admin_post_process_form', 'submit_data' );
?>