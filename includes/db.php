<?php 

/**
 * Creating timify_post_state table when plugin active.  
 * 
 * This table contain ips which visit the post
 * @version 1.1.0
 */
if( !function_exists('timify_create_table') ):
    function timify_create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'timify_post_state';	
        $charset_collate = $wpdb->get_charset_collate();

        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                post_id mediumint(9) NOT NULL,
                ip text NOT NULL,
                time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

        }
    }
endif;


/**
 * Insert data(post_id, ip) in timify_post_state table.  
 * 
 * @param array $args
 * 		$args[table_name] = table name;
 * 		$args[post_id] = post id;
 * 		$args[ip] = current ip;
 * 
 * @version 1.1.0
 */
if( !function_exists('timify_insert_ip') ):
    function timify_insert_ip( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'table_name' => $wpdb->prefix . 'timify_post_state',
            'post_id'    => get_the_ID(),
            'ip'         => timify_get_ip(),
        );
        $args = wp_parse_args( $args, $defaults );

        if ( !timify_is_ip_exist() ) {
            $wpdb->insert( 
                $args['table_name'], 
                array( 
                    'post_id' => $args['post_id'], 
                    'ip'      => $args['ip'], 
                    'time'    => current_time('mysql'), 
                ) 
            );
        }
    }
endif;


/**
 * Checking the current ip is exists in timify_post_state table.  
 * 
 * @param int $post_id
 * @param string $ip
 *
 * @version 1.1.0
 */
if(!function_exists('timify_is_ip_exist')):
    function timify_is_ip_exist( $post_id = 0, $ip = 0 ){
        global $wpdb;
        if ( !$post_id ) {
            $post_id = get_the_ID();
        }
        if ( !$ip ) {
            $ip = (string)timify_get_ip();
        }

        $result = $wpdb->get_var( $wpdb->prepare( 
            "
            SELECT id FROM {$wpdb->prefix}timify_post_state
            WHERE post_id = %d
            AND ip = %s
            ",
            $post_id,
            $ip
        ));

        return isset($result) ? $result : 0;
    }
endif;



/**
 * Checking the current ip is exists in timify_post_state table.  
 * 
 * @param int $post_id
 * @param string $ip
 *
 * @version 1.1.0
 */
if(!function_exists('timify_get_post_view_count')):
    function timify_get_post_view_count( $post_id = 0 ) {
        global $wpdb;
        if ( !$post_id ) {
            $post_id = get_the_ID();
        }

        $result = $wpdb->get_var( $wpdb->prepare( 
            "
            SELECT COUNT(*) FROM {$wpdb->prefix}timify_post_state
            WHERE post_id = %d
            ",
            $post_id
        ));
        return $result;
    }
endif;

/**
 * Utility to retrieve IP address
 * @since  1.1.0
 */

if(!function_exists('timify_get_ip')):
    function timify_get_ip() {
        if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = ( isset( $_SERVER['REMOTE_ADDR'] ) ) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
        }
        $ip = filter_var( $ip, FILTER_VALIDATE_IP );
        $ip = ( $ip === false ) ? '0.0.0.0' : $ip;
        return $ip;
    }
endif;
