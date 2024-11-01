<?php
/**
 * Post Meta box.
 *
 * @subpackage Admin interface
 * @since 1.0.0
 * @var settings - plugin options
 */

use Timify\helpers\HelperFunctions;

defined( 'ABSPATH' ) || exit;

if( !class_exists('Timify_MetaBox') ):
	
	class Timify_MetaBox {
		use HelperFunctions;

		/**
		 * Register functions.
		 */
		public function __construct() {
			add_action( 'add_meta_boxes',  array($this,'metabox_render'), 10, 2 );
			add_action( 'save_post',  array($this,'save_metadata' ) );
		}

		/**
		 * Add Meta box.
		 * 
		 * @param string $post_type Post Type
		 * @param object $post      WP Post
		 */
		public function metabox_render( $post_type, $post ) {

			// If user can't publish posts, then get out
			if ( ! current_user_can( 'publish_posts' ) ) {
				return;
			}

			if ( in_array( $post->post_status, [ 'auto-draft', 'future' ] ) ) {
				return;
			}

			// $position = $this->get_data( 'lm_display_method', 'before_content' );
			// if ( ! in_array( $position, [ 'before_content', 'replace_original' ] ) ) {
			// 	return;
			// }
			
			$post_types = $this->get_data( 'lm_rt_post_types' );

			if ( ! empty( $post_types ) ) {
				add_meta_box( 'timify_meta_box', __( 'Last Modified and Reading Time Info', 'timify' ), array(&$this, 'metabox' ), $post_types, 'side', 'default' );
			}
		}

		/**
		 * Generate column data.
		 * 
		 * @param string   $column   Column name
		 * @param int      $post_id  Post ID
		 * 
		 * @return string  $time
		 */
		public function metabox( $post ) {
			// retrieve post meta
			$disabled = $this->get_meta( $post->ID, '_lm_disable' );
			$rtdisabled = $this->get_meta( $post->ID, '_rt_disable' );
			$wcdisabled = $this->get_meta( $post->ID, '_wc_disable' );
			$pvcdisabled = $this->get_meta( $post->ID, '_pvc_disable' );

			// buid nonce
			$this->nonce( 'disabled' ); ?>
				
			<div id="metabox-wrap" class="meta-options">
				<p>
					<label for="lm-disable" class="selectit" title="<?php _e( 'You can disable auto insertation of last modified date on this', 'timify' ); ?>">
						<input id="lm-disable" type="checkbox" name="lm_disable_insert" <?php if ( $disabled == 'yes' ) { echo esc_attr('checked'); } ?> /> <?php _e( 'Disable last modified date on this post', 'timify' ); ?>
					</label>
				</p>
				<p>
					<label for="rt-disable" class="selectit" title="<?php _e( 'You can disable auto insertation of reading time on this', 'timify' ); ?>">
						<input id="rt-disable" type="checkbox" name="rt_disable_insert" <?php if ( $rtdisabled == 'yes' ) { echo esc_attr('checked'); } ?> /> <?php _e( 'Disable reading time on this post', 'timify' ); ?>
					</label>
				</p>
				<p>
					<label for="wc-disable" class="selectit" title="<?php _e( 'You can disable auto insertation of word count on this', 'timify' ); ?>">
						<input id="wc-disable" type="checkbox" name="wc_disable_insert" <?php if ( $wcdisabled == 'yes' ) { echo esc_attr('checked'); } ?> /> <?php _e( 'Disable Word count on this post', 'timify' ); ?>
					</label>
				</p>
				<p>
					<label for="pvc-disable" class="selectit" title="<?php _e( 'You can disable auto insertation of Post View on this', 'timify' ); ?>">
						<input id="pvc-disable" type="checkbox" name="pvc_disable_insert" <?php if ( $pvcdisabled == 'yes' ) { echo esc_attr('checked'); } ?> /> <?php _e( 'Disable View Count on this post', 'timify' ); ?>
					</label>
				</p>
			</div>
			<?php 
		}

		/**
		 * Store custom field meta box data.
		 *
		 * @param int $post_id The post ID.
		 */
		public function save_metadata( $post_id ) {
			// return if autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}
			
			// Check the user's permissions.
			// if ( ! current_user_can( 'edit_post', $post_id ) ) {
			// 	return;
			// }

			if ( ! $this->verify( 'disabled' ) ) {
				return;
			}

			// disableautoinsert string
			if ( isset( $_POST[ 'lm_disable_insert' ] ) ) {
				$this->update_meta( $post_id, '_lm_disable', 'yes' );
			} else {
				$this->update_meta( $post_id, '_lm_disable', 'no' );
			}
			if ( isset( $_POST[ 'rt_disable_insert' ] ) ) {
				$this->update_meta( $post_id, '_rt_disable', 'yes' );
			} else {
				$this->update_meta( $post_id, '_rt_disable', 'no' );
			}
			if ( isset( $_POST[ 'wc_disable_insert' ] ) ) {
				$this->update_meta( $post_id, '_wc_disable', 'yes' );
			} else {
				$this->update_meta( $post_id, '_wc_disable', 'no' );
			}
			if ( isset( $_POST[ 'pvc_disable_insert' ] ) ) {
				$this->update_meta( $post_id, '_pvc_disable', 'yes' );
			} else {
				$this->update_meta( $post_id, '_pvc_disable', 'no' );
			}
		}

		/**
		 * Store custom field meta box data.
		 *
		 * @param int $post_id The post ID.
		 */
		private function nonce( $name, $referer = true, $echo = true ) {
			\wp_nonce_field( 'timify_nonce_'.$name, 'timify_metabox_'.$name.'_nonce', $referer, $echo );
		}

		/**
		 * Store custom field meta box data.
		 *
		 * @param int $post_id The post ID.
		 */
		private function verify( $name ) {
			if ( ! isset( $_REQUEST['timify_metabox_'.$name.'_nonce'] ) || ! \wp_verify_nonce( $_REQUEST['timify_metabox_'.$name.'_nonce'], 'timify_nonce_'.$name ) ) {
				return false;
			}

			return true;
		}
	}

endif;