<?php 
defined( 'ABSPATH' ) || exit;

/**
 * Add admin scripts
 *
 * @return void
 */
if ( !function_exists( 'timify_enqueue_admin_scripts' ) ) :
    function timify_enqueue_admin_scripts() {
        wp_enqueue_script( 'timify-adminjs', TIMIFY_ASSETS_URL.'/js/admin.js', array('jquery'), TIMIFY_VERSION );
        wp_localize_script( 'timify-adminjs', 'admin_js', array( 
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        ) );
    }
endif;
add_action( 'admin_enqueue_scripts', 'timify_enqueue_admin_scripts' );
?>