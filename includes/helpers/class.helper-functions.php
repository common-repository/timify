<?php
/**
 * Helper functions.
 *
 * @subpackage Helper interface
 * @since 1.0.0
 * @var settings - plugin options
 */

namespace Timify\helpers;

defined( 'ABSPATH' ) || exit;

trait HelperFunctions{
    
	//for seetings api html from input filed senitize
	protected $allowed_html_field = [
		'input'      => [
			'type'  => [],
			'name' => [],
			'value' => [],
			'class'  => [],
			'id' => [],
			'placeholder'=>[],
			'min'=>[],
			'max'=>[],
			'step'=>[],
			'checked'=>[],
		],
		'p'     => [
			'class'=>[],
			'id'=>[],
			'a'=>[],
			'span'=>[]
		],
		'label'  	=> [
			'for'=>[],
			'class'=>[],
			'id'=>[]
		],
		'select'=> [
			'name' => [],
			'value' => [],
			'class'  => [],
			'id' => [],
			'multiple'=>[],
		],
		'option'  => [
			'value' => [],
			'selected'=>[]
		],
		'textarea'=>[
			'rows'=>[],
			'cols'=>[],
			'name' => [],
			'value' => [],
			'class'  => [],
			'id' => [],
			'placeholder'=>[],
		],
		'a'=>[
			'class'  => [],
			'id' => [],
			'href'=>[],
			'target'=>[]
		],
		'span'=>[
			'class'  => [],
			'id' => [],
			'span'=>[]
		],
		'fieldset'=>[],
		'br'=>[],
		'strong'=>[],
		

	];

	/**
	 * Get all registered public post types.
	 *
	 * @param bool $public Public type True or False.
	 * @return array
	 */
	protected function get_post_types( $public = true ) {
		$post_types = get_post_types( [ 'public' => $public ], 'objects' );
		$data = [];
		foreach ( $post_types as $post_type ) {
			if ( ! is_object( $post_type ) )
			    continue;															
			
			if ( isset( $post_type->labels ) ) {
				$label = $post_type->labels->name ? $post_type->labels->name : $post_type->name;
			} else {
				$label = $post_type->name;
			}
			
			if ( $label == 'Media' || $label == 'media' || $post_type->name == 'elementor_library' )
				continue; // skip media
				
			$data[$post_type->name] = $label;
		}

		return $data;
	}

	protected function get_data( $key, $default = false ) {
		$settings = get_option( 'timify_settings' );
		return ( isset( $settings[$key] ) ) ? $settings[$key] : $default;
	}

    public function get_meta( $post_id, $key, $single = true ){
		return \get_post_meta( $post_id, $key, $single );
	}

    protected function update_meta( $post_id, $key, $value ){
		return \update_post_meta( $post_id, $key, $value );
	}

	/**
	 * Check plugin settings if enabled
	 * 
	 * @param  string  $name  Settings field name.
	 * @return bool
	 */
	protected function is_enabled( $name ) {
		$data = $this->get_data( $name );
		if ( $data == 'on' ) {
            return true;
		}

		return false;
	}
	
	
	public function add_postfix_reading_time( $time, $singular, $multiple ) {

		if ( $time > 1 ) {
			$postfix = $multiple;
		} else {
			$postfix = $singular;
		}
		$postfix = apply_filters( 'timify_edit_postfix', $postfix, $time, $singular, $multiple );

		return $postfix;
	}

	/**
	 * get current theme name.
	 * @return string
	 * @since 1.1.0
	 */
	public function get_current_theme() {
		$theme_name = '';
		$theme      = wp_get_theme();
		if ( isset( $theme->parent_theme ) && '' != $theme->parent_theme || null != $theme->parent_theme ) {
			$theme_name = $theme->parent_theme;
		} else {
			$theme_name = $theme->name;
		}
		return $theme_name;
	}



}