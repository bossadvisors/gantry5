<?php
defined('ABSPATH') or die;

add_action( 'admin_enqueue_scripts', 'gantry_admin_scripts' );
add_action( 'admin_print_styles', 'gantry_admin_print_styles', 200 );
add_action( 'admin_print_scripts', 'gantry_admin_print_scripts', 200 );

// Adjust menu to contain Gantry stuff.
add_action(
    'admin_menu',
    function () {
        remove_submenu_page( 'themes.php', 'theme-editor.php' );
        add_theme_page( 'Layout Manager', 'Layout Manager', 'manage_options', 'layout-manager', 'gantry_layout_manager' );
    },
    102
);

function gantry_admin_scripts() {
    if( isset( $_GET['page'] ) && $_GET['page'] == 'layout-manager' ) {
        gantry_layout_manager();
    }
}
function gantry_admin_print_styles() {
    $styles = \Gantry\Framework\Gantry::instance()->styles();
    if ( $styles ) {
        echo implode( "\n", $styles ) . "\n";
    }
}
function gantry_admin_print_scripts() {
    $scripts = \Gantry\Framework\Gantry::instance()->scripts();
    if ( $scripts ) {
        echo implode( "\n", $scripts ) . "\n";
    }
}

function gantry_layout_manager() {
    static $output = null;

    if ( !current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    if ( $output ) {
        echo $output;
        return;
    }

    // Detect Gantry Framework or fail gracefully.
    if (!class_exists('Gantry5\Loader')) {
        wp_die( __( 'Gantry 5 Framework not found.' ) );
    }

    // Initialize administrator or fail gracefully.
    try {
        Gantry5\Loader::setup();

        $gantry = Gantry\Framework\Gantry::instance();
        $gantry['router'] = function ($c) {
            return new \Gantry\Admin\Router($c);
        };

        // Dispatch to the controller.
        $output = $gantry['router']->dispatch();

    } catch (Exception $e) {
        wp_die( $e->getMessage() );
    }

    echo $output;
}
