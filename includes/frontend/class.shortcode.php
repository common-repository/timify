<?php
/**
 * shortcode class for WP-Admin 
 *
 * @subpackage shortcode interface
 * @var version - plugin version
 * @since 1.0.0
 * @var settings - plugin options
 */

 use Timify\helpers\HelperFunctions;
 use Timify\includes\frontend\Timify_Frontend;

 defined( 'ABSPATH' ) || exit;
 
if( !class_exists('Timify_Shortcode') ):

	class Timify_Shortcode extends Timify_Frontend {
		use HelperFunctions;

		public function __construct() {
			parent::__construct();
			add_shortcode( 'timify-last-modified-date', [ $this, 'lm_render' ] );
			add_shortcode( 'timify-post-reading-time', [ $this, 'rt_render' ] );
			add_shortcode( 'timify-post-words-count', [ $this, 'wc_render' ] );
			add_shortcode( 'timify-post-view-count', [ $this, 'pvc_render' ] );
		}

		/**
		 * Callback to register shortcodes for .
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string     Shortcode output.
		 */
		public function lm_render( $atts ) {
			global $post;
			
			if ( $this->settings['lm_enable'] !== 'on' ) {
				return;
			}

			$lmposition = $this->settings['lm_display_method'];
			if ( $lmposition !== 'shortcode_content' ) {
				return;
			}

			$label   = $this->settings['lm_label'];
			$post_id = $post->ID;
			$lmatts  = shortcode_atts( [
				'id'           => $post_id,
				'label'		   => $label
			], $atts);

			$get_post = get_post( absint( $lmatts['id'] ) );
			if ( ! $get_post ) {
				return;
			}

			
			$modified_timestamp = get_post_modified_time( 'U' );
			$time 				= current_time( 'U' );
			$ago_label 			= $this->settings['ago_label'];
			$timestamp			= human_time_diff( $modified_timestamp, $time ).' '.$ago_label;
			
			//time filter hook
			$timestamp = apply_filters( 'timify_post_formatted_date', $timestamp, get_the_ID() );
			$lmdisable = $this->get_meta( get_the_ID(), '_lm_disable' );

			if ( empty( $lmdisable ) || ! empty( $lmdisable ) && $lmdisable == 'no' ) {
				$template ='<p class="timify-meta-last-modified-wrap"><span class="label">' . wp_kses( $lmatts['label'], $this->allwoed_html_kses ) . '</span> <span class="date">'.esc_html($timestamp).'</span></p>';
			}

			return $template;
		}

		/**
		 * Callback to register shortcodes for reading time.
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string     Shortcode output.
		 */

		public function rt_render($atts) {

			global $post;
			
			if ( $this->settings['rt_enable'] !== 'on' ) {
				return;
			}

			$position = $this->settings['rt_display_method'];;
			if ( $position !== 'shortcode_content' ) {
				return;
			}

			$label   = $this->settings['rt_label'];
			$post_id = $post->ID;
			$rtatts  = shortcode_atts( [
				'id'     => $post_id,
				'label'  => $label
			], $atts);

			$get_post = get_post( absint( $rtatts['id'] ) );
			if ( ! $get_post ) {
				return;
			}

			$this->rt_calculation( $post_id, $this->settings );
			$postfix          = $this->settings['rt_postfix'];
			$postfixs         = $this->settings['rt_postfixs'];
			$cal_postfix = $this->add_postfix_reading_time( $this->reading_time, $postfixs, $postfix );
			$rtdisable = $this->get_meta( get_the_ID(), '_rt_disable' );
			if ( empty( $rtdisable ) || ! empty( $rtdisable ) && $rtdisable == 'no' ) {
				$template ='<p class="timify-meta-reading-wrap"><span class="label">' . wp_kses( $rtatts['label'], $this->allwoed_html_kses ) . '</span> <span class="time">' . esc_html( $this->reading_time ) . '</span><span class="postfix">' . wp_kses( $cal_postfix, $this->allwoed_html_kses ) . '</span></p>';
			}

			return $template;
		}

		/**
		 * Callback to register shortcodes for word count.
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string     Shortcode output.
		 * @since 1.1.0
		 */

		public function wc_render($atts) {
			
			global $post;
			
			if ( $this->settings['wc_enable'] !== 'on' ) {
				return;
			}
		
			$position = $this->settings['wc_display_method'];
			if ( $position !== 'shortcode_content' ) {
				return;
			}

			$label   = $this->settings['wc_label'];
			$post_id = $post->ID;
			$wcatts  = shortcode_atts( [
				'id'     => $post_id,
				'label'  => $label
			], $atts);

			$get_post = get_post( absint( $wcatts['id'] ) );
			if ( ! $get_post ) {
				return;
			}

			$content_post     = get_post($post_id);
			$content_word 	  = $content_post->post_content;
			$post_words_count = '<span class="words">&nbsp;'.$this->wc_calculation($content_word).'</span>';
			$postfix          = !empty($this->settings['wc_postfix'])?'<span class="postfix">'.$this->settings['wc_postfix'].'</span>':'';
			$icon		  	  = !empty($this->settings['wc_icon_class'])?'<span class="icon dashicons '.$this->settings['wc_icon_class'].'"></span>':'';
			$wcdisable 		  = $this->get_meta( get_the_ID(), '_wc_disable' );
			if ( empty( $wcdisable ) || ! empty( $wcdisable ) && $wcdisable == 'no' ) {
				$template ='<p class="timify-meta-word-wrap">'.esc_html($wcatts['label']) . wp_kses($post_words_count,$this->allowed_html_field).'&nbsp;'.wp_kses($postfix,$this->allowed_html_field).'</p>';
			}
			

			return $template;
		}

		/**
		 * Callback to register shortcodes for post view.
		 *
		 * @param array $atts Shortcode attributes.
		 * @return string     Shortcode output.
		 * @since 1.1.0
		 */

		public function pvc_render($atts) {
			
			global $post;
			
			if ( $this->settings['pvc_enable'] !== 'on' ) {
				return;
			}
		
			$position = $this->settings['pvc_display_method'];
			if ( $position !== 'shortcode_content' ) {
				return;
			}

			$label   = $this->settings['pvc_label'];
			$post_id = $post->ID;
			$pvcatts  = shortcode_atts( [
				'id'     => $post_id,
				'label'  => $label
			], $atts);

			$get_post = get_post( absint( $pvcatts['id'] ) );
			if ( ! $get_post ) {
				return;
			}

			$post_view_count  = '<span class="views">&nbsp;'.timify_get_post_view_count().'</span>';
			$postfix          = !empty($this->settings['pvc_postfix'])?'<span class="postfix">'.$this->settings['pvc_postfix'].'</span>':'';
			$icon		  	  = !empty($this->settings['pvc_icon_class'])?'<span class="icon dashicons '.$this->settings['pvc_icon_class'].'"></span>':'';
			$pvcdisable 	  = $this->get_meta( get_the_ID(), '_pvc_disable' );
			if ( empty( $pvcdisable ) || ! empty( $pvcdisable ) && $pvcdisable == 'no' ) {
				$template 	  = '<p class="timify-meta-view-wrap">'. esc_html($pvcatts['label']). wp_kses($post_view_count,$this->allowed_html_field).'&nbsp;'.wp_kses($postfix,$this->allowed_html_field).'</p>';
			}
			
			return $template;
		}


	}

endif;

new Timify_Shortcode();