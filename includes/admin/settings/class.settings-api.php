<?php
use Timify\helpers\HelperFunctions;
if ( ! class_exists( 'Timify_Settings_API' ) ):
	class Timify_Settings_API {
		use HelperFunctions;
		/**
		 * settings sections array
		 *
		 * @var array
		 */
		protected $settings_sections = array();
		/**
		 * Settings fields array
		 *
		 * @var array
		 */
		protected $settings_fields = array();

		
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}

		/**
		 * Enqueue scripts and styles
		 */
		function admin_enqueue_scripts() {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_media();
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'jquery' );
		}

		/**
		 * Set settings sections
		 *
		 * @since 1.0.0
		 *
		 * @param $sections
		 *
		 * @return $this
		 *
		 */
		function set_sections( $sections ) {
			$this->settings_sections = $sections;

			return $this;
		}

		/**
		 * Add a single section
		 *
		 * @since 1.0.0
		 *
		 * @param $section
		 *
		 * @return $this
		 *
		 */
		function add_section( $section ) {
			$this->settings_sections[] = $section;

			return $this;
		}

		/**
		 * Set settings fields
		 *
		 * @since 1.0.0
		 *
		 * @param $fields
		 *
		 * @return $this
		 *
		 */
		function set_fields( $fields ) {
			$this->settings_fields = $fields;

			return $this;
		}

		/**
		 * Set fields
		 *
		 * @since 1.0.0
		 *
		 * @param $section
		 * @param $field
		 *
		 * @return $this
		 *
		 */
		function add_field( $section, $field ) {
			$defaults                            = array(
				'name'  => '',
				'label' => '',
				'desc'  => '',
				'type'  => 'text'
			);
			$arg                                 = wp_parse_args( $field, $defaults );
			$this->settings_fields[ $section ][] = $arg;

			return $this;
		}

		/**
		 * Initialize and registers the settings sections and fileds to WordPress
		 *
		 * Usually this should be called at `admin_init` hook.
		 *
		 * This function gets the initiated settings sections and fields. Then
		 * registers them to WordPress and ready for use.
		 */
		function admin_init() {
			//register settings sections
			foreach ( $this->settings_sections as $section ) {
				if ( false == get_option( $section['id'] ) ) {
					add_option( $section['id'] );
				}
				if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {
					$callback = function() use ($section) {
						echo  '<div class="inside">' . esc_html($section['desc']) . '</div>';;
					};
				} else if ( isset( $section['callback'] ) ) {
					$callback = $section['callback'];
				} else {
					$callback = null;
				}
				add_settings_section( $section['id'], $section['title'], $callback, $section['id'] );
			}
			//register settings fields
			foreach ( $this->settings_fields as $section => $field ) {
				foreach ( $field as $option ) {
					$name     = $option['name'];
					$type     = isset( $option['type'] ) ? $option['type'] : 'text';
					$label    = isset( $option['label'] ) ? $option['label'] : '';
					$callback = isset( $option['callback'] ) ? $option['callback'] : array(
						$this,
						'callback_' . $type
					);
					$args     = array(
						'id'                => $name,
						'class'             => isset( $option['class'] ) ? $option['class'] : $name,
						'label_for'         => "{$section}[{$name}]",
						'desc'              => isset( $option['desc'] ) ? $option['desc'] : '',
						'name'              => $label,
						'section'           => $section,
						'size'              => isset( $option['size'] ) ? $option['size'] : null,
						'options'           => isset( $option['options'] ) ? $option['options'] : '',
						'std'               => isset( $option['default'] ) ? $option['default'] : '',
						'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
						'type'              => $type,
						'placeholder'       => isset( $option['placeholder'] ) ? $option['placeholder'] : '',
						'min'               => isset( $option['min'] ) ? $option['min'] : '',
						'max'               => isset( $option['max'] ) ? $option['max'] : '',
						'step'              => isset( $option['step'] ) ? $option['step'] : '',
                        'group_fields'       => isset( $option['group_fields'] ) ? $option['group_fields'] : '',
					);
					add_settings_field( "{$section}[{$name}]", $label, $callback, $section, $section, $args );
				}
			}
			// creates our settings in the options table
			foreach ( $this->settings_sections as $section ) {
				register_setting( $section['id'], $section['id'], array( $this, 'sanitize_options' ) );
			}
		}

		/**
		 * Get field description for display
		 *
		 * @since 1.0.0
		 *
		 * @param $args
		 *
		 * @return string
		 *
		 */
		public function get_field_description( $args ) {
			if ( ! empty( $args['desc'] ) ) {
				$desc = sprintf( '<p class="description">%s</p>', $args['desc'] );
			} else {
				$desc = '';
			}

			return $desc;
		}

		/**
		 * Displays a text field for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_text( $args ) {
			$value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$type        = isset( $args['type'] ) ? $args['type'] : 'text';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			$html        = sprintf( '<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder );
			$html        .= $this->get_field_description( $args );
			echo wp_kses( $html, $this->allowed_html_field );
		}

		/**
		 * Displays a url field for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_url( $args ) {
			$this->callback_text( $args );
		}

		/**
		 * Displays a number field for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_number( $args ) {
			$value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$type        = isset( $args['type'] ) ? $args['type'] : 'number';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			$min         = ( $args['min'] == '' ) ? '' : ' min="' . $args['min'] . '"';
			$max         = ( $args['max'] == '' ) ? '' : ' max="' . $args['max'] . '"';
			$step        = ( $args['step'] == '' ) ? '' : ' step="' . $args['step'] . '"';
			$html        = sprintf( '<input type="%1$s" class="timify-%2$s-number" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s%8$s%9$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder, $min, $max, $step );
			$html        .= $this->get_field_description( $args );
			echo wp_kses( $html, $this->allowed_html_field );
		}

		/**
		 * Displays a checkbox for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_checkbox( $args ) {
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$html  = '<fieldset>';
			$html  .= sprintf( '<label for="timify-%1$s[%2$s]">', $args['section'], $args['id'] );
			$html  .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id'] );
			$html  .= sprintf( '<input type="checkbox" class="checkbox" id="timify-%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s />', $args['section'], $args['id'], checked( $value, 'on', false ) );
			$html  .= sprintf( '%1$s</label>', $args['desc'] );
			$html  .= '</fieldset>';
			echo wp_kses($html,$this->allowed_html_field);
		}

		/**
		 * Displays a multicheckbox for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_multicheck( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$html  = '<fieldset>';
			$html  .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id'] );
			foreach ( $args['options'] as $key => $label ) {
				$checked = isset( $value[ $key ] ) ? $value[ $key ] : '0';
				$html    .= sprintf( '<label for="timify-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key );
				$html    .= sprintf( '<input type="checkbox" class="checkbox" id="timify-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $checked, $key, false ) );
				$html    .= sprintf( '%1$s</label><br>', $label );
			}
			$html .= $this->get_field_description( $args );
			$html .= '</fieldset>';
			echo wp_kses($html,$this->allowed_html_field);
		}

        /**
		 * Displays a groupTextSelect for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_groupTextSelect( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$html  = '<fieldset>';
			$html  .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id'] );
            $sn=1;
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			foreach ( $args['group_fields'] as $key ) {
                $checked = isset( $value[ $key ] ) ? $value[ $key ] : '0';
                if($sn==1){
                    $html    .= sprintf( '<label for="timify-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key );
                    $html    .= sprintf( '<input type="number" class="timify-%5$s-number" id="timify-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%4$s" />', $args['section'], $args['id'], $key,$checked,$size );
                }elseif($sn==2){
                    $html  .= sprintf( '<select class="time-type" name="%1$s[%2$s][%3$s]" id="%1$s[%2$s][%3$s]">',$args['section'], $args['id'], $key );
                    foreach ( $args['options'] as $key => $label ) {
                        $html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $checked, $key, false ), $label );
                    }
                    $html .= sprintf( '</select>' );
                }
                $sn++;
            }

			$html .= $this->get_field_description( $args );
			$html .= '</fieldset>';
			echo wp_kses($html,$this->allowed_html_field);
		}

		  /**
		 * Displays a groupNumberSelect for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_groupNumberSelect( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$html  = '<fieldset>';
			$html  .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id'] );
            $sn=1;
			foreach ( $args['group_fields'] as $key ) {
                $checked = isset( $value[ $key ] ) ? $value[ $key ] : '0';
				$size    = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
				$step    = ( $args['step'] == '' ) ? '' : ' step="' . $args['step'] . '"';
                if($sn<=4){
                    $html    .= sprintf( '<input type="number" class="timify-%5$s-number" id="timify-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%4$s" %6$s />', $args['section'], $args['id'], $key,$checked,$size, $step);
                }elseif($sn==5){
                    $html  .= sprintf( '<select class="%4$s-type" name="%1$s[%2$s][%3$s]" id="%1$s[%2$s][%3$s]">',$args['section'], $args['id'], $key,$size );
                    foreach ( $args['options'] as $key => $label ) {
                        $html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $checked, $key, false ), $label );
                    }
                    $html .= sprintf( '</select>' );
                }
                $sn++;
            }


			$html .= $this->get_field_description( $args );
			$html .= '</fieldset>';
			echo wp_kses($html,$this->allowed_html_field);
		}


		/**
		 * Displays a radio button for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_radio( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$html  = '<fieldset>';
			foreach ( $args['options'] as $key => $label ) {
				$html .= sprintf( '<label for="timify-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key );
				$html .= sprintf( '<input type="radio" class="radio" id="timify-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $value, $key, false ) );
				$html .= sprintf( '%1$s</label><br>', $label );
			}
			$html .= $this->get_field_description( $args );
			$html .= '</fieldset>';
			echo wp_kses($html,$this->allowed_html_field);
		}

		/**
		 * Displays a selectbox for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_select( $args ) {
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$html  = sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );
			foreach ( $args['options'] as $key => $label ) {
				$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
			}
			$html .= sprintf( '</select>' );

			$html .= $this->get_field_description( $args );
			echo wp_kses($html,$this->allowed_html_field);
		}

		/**
		 * Displays post types in selectbox for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_postTypes( $args ) {
			$post_types = $this->get_post_types();
			$value =  $this->get_option( $args['id'], $args['section'], $args['std'] );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$html  = sprintf( '<select  multiple="multiple" class="%1$s" name="%2$s[%3$s][]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );
			foreach ( $post_types as $key => $label ) {
				$value=!empty($value)?$value:array();
				$selected = in_array( $key, $value ) ? ' selected="selected"' : '';
				$html .= sprintf( '<option value="%s"%s>%s</option>', $key, $selected, $label );
			}
			$html .= sprintf( '</select>' );
			$html .= $this->get_field_description( $args );
			echo wp_kses($html,$this->allowed_html_field);
		}

		/**
		 * Displays a textarea for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_textarea( $args ) {
			$value       = esc_textarea( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			$html        = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%4$s>%5$s</textarea>', $size, $args['section'], $args['id'], $placeholder, $value );
			$html        .= $this->get_field_description( $args );
			echo wp_kses($html,$this->allowed_html_field);
		}

		/**
		 * Displays the html for a settings field
		 *
		 * @param array $args settings field args
		 *
		 * @return string
		 */
		function callback_html( $args ) {
			echo wp_kses($this->get_field_description( $args ),$this->allowed_html_field);
		}

		/**
		 * Displays a rich text textarea for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_wysiwyg( $args ) {
			$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : '500px';
			echo '<div style="max-width: ' . esc_attr($size) . ';">';
			$editor_settings = array(
				'teeny'         => true,
				'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
				'textarea_rows' => 10
			);
			if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
				$editor_settings = array_merge( $editor_settings, $args['options'] );
			}
			wp_editor( $value, $args['section'] . '-' . $args['id'], $editor_settings );
			echo '</div>';
			echo wp_kses($this->get_field_description( $args ),$this->allowed_html_field);
		}

		/**
		 * Displays a file upload field for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_file( $args ) {
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$id    = $args['section'] . '[' . $args['id'] . ']';
			$label = isset( $args['options']['button_label'] ) ? $args['options']['button_label'] : __( 'Choose File','timify' );
			$html  = sprintf( '<input type="text" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
			$html  .= '<input type="button" class="button wpsa-browse" value="' . $label . '" />';
			$html  .= $this->get_field_description( $args );
			echo wp_kses($html,$this->allowed_html_field);
		}

		/**
		 * Displays a password field for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_password( $args ) {
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$html  = sprintf( '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
			$html  .= $this->get_field_description( $args );
			echo wp_kses($html,$this->allowed_html_field);
		}

		/**
		 * Displays a color picker field for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_color( $args ) {
			$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
			$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
			$html  = sprintf( '<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />', $size, $args['section'], $args['id'], $value, $args['std'] );
			$html  .= $this->get_field_description( $args );
			echo wp_kses($html,$this->allowed_html_field);
		}

		/**
		 * Displays a select box for creating the pages select box
		 *
		 * @param array $args settings field args
		 */
		function callback_pages( $args ) {
			$dropdown_args = array(
				'selected' => esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) ),
				'name'     => $args['section'] . '[' . $args['id'] . ']',
				'id'       => $args['section'] . '[' . $args['id'] . ']',
				'echo'     => 0
			);
			$html          = wp_dropdown_pages( $dropdown_args );
			echo wp_kses($html,$this->allowed_html_field);
		}

		/**
		 * Sanitize callback for Settings API
		 *
		 * @return mixed
		 */
		function sanitize_options( $options ) {
			if ( ! $options ) {
				return $options;
			}
			foreach ( $options as $option_slug => $option_value ) {
				$sanitize_callback = $this->get_sanitize_callback( $option_slug );
				// If callback is set, call it
				if ( $sanitize_callback ) {
					$options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
					continue;
				}
			}

			return $options;
		}

		/**
		 * Get sanitization callback for given option slug
		 *
		 * @param string $slug option slug
		 *
		 * @return mixed string or bool false
		 */
		function get_sanitize_callback( $slug = '' ) {
			if ( empty( $slug ) ) {
				return false;
			}
			// Iterate over registered fields and see if we can find proper callback
			foreach ( $this->settings_fields as $section => $options ) {
				foreach ( $options as $option ) {
					if ( $option['name'] != $slug ) {
						continue;
					}

					// Return the callback name
					return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
				}
			}

			return false;
		}

		/**
		 * Get the value of a settings field
		 *
		 * @param string $option settings field name
		 * @param string $section the section name this field belongs to
		 * @param string $default default text if it's not found
		 *
		 * @return string
		 */
		function get_option( $option, $section, $default = '' ) {
			$options = get_option( $section );
			if ( isset( $options[ $option ] ) ) {
				return $options[ $option ];
			}

			return $default;
		}

		/**
		 * Show navigations as tab
		 *
		 * Shows all the settings section labels as tab
		 */
		function show_navigation() {
			echo '<div class="timify-settings-sidebar"><ul>';
			$count = count( $this->settings_sections );
			// don't show the navigation if only one section exists
			if ( $count === 1 ) {
				return;
			}
			foreach ( $this->settings_sections as $tab ) {
				echo '<li><a href="#'.esc_attr($tab['id']).'" id="'.esc_attr($tab['id']).'-tab">'. esc_html($tab['title']).'</a></li>';
			}
			echo '</ul></div>';
		}

		/**
		 * Show the section settings forms
		 *
		 * This function displays timify sections in a different form
		 */
		function show_forms() {
			$this->_style_fix();
			?>
            <div class="timify-settings-content">
				<?php foreach ( $this->settings_sections as $form ) { ?>
                    <div id="<?php echo esc_attr($form['id']); ?>" class="group" style="display: none;">
                        <form method="post" action="options.php">
							<?php
							do_action( 'wsa_form_top_' . $form['id'], $form );
							settings_fields( $form['id'] );
							do_settings_sections( $form['id'] );
							do_action( 'wsa_form_bottom_' . $form['id'], $form );
							if ( isset( $this->settings_fields[ $form['id'] ] ) ):
								?>
                                <div style="padding-left: 10px">
									<?php submit_button(); ?>
                                </div>
							<?php endif; ?>
                        </form>
                    </div>
				<?php } ?>
            </div>
			<?php
			$this->script();
		}

		function show_settings() {
			echo '<div class="timify-settings d-flex">';
			$this->show_navigation();
			$this->show_forms();
			echo '</div>';
		}

		/**
		 * Tabbable JavaScript codes & Initiate Color Picker
		 *
		 * This code uses localstorage for displaying active tabs
		 */
		function script() {
			?>
            <script>
                jQuery(document).ready(function ($) {
                    //Initiate Color Picker
                    $('.wp-color-picker-field').wpColorPicker();
                    // Switches option sections
                    $('.group').hide();
                    var activetab = '';
                    if (typeof(localStorage) != 'undefined') {
                        activetab = localStorage.getItem("activetab");
                    }
                    //if url has section id as hash then set it as active or override the current local storage value
                    if (window.location.hash) {
                        activetab = window.location.hash;
                        if (typeof(localStorage) != 'undefined') {
                            localStorage.setItem("activetab", activetab);
                        }
                    }
                    if (activetab != '' && $(activetab).length) {
                        $(activetab).fadeIn();
                    } else {
                        $('.group:first').fadeIn();
                    }
                    $('.group .collapsed').each(function () {
                        $(this).find('input:checked').parent().parent().parent().nextAll().each(
                            function () {
                                if ($(this).hasClass('last')) {
                                    $(this).removeClass('hidden');
                                    return false;
                                }
                                $(this).filter('.hidden').removeClass('hidden');
                            });
                    });

                    if (activetab != '' && $(activetab + '-tab').length) {
                        $(activetab + '-tab').closest('li').addClass('active');
                    }
                    else {
                        $('.timify-settings-sidebar  li:first').addClass('active');
                    }

                    $('.timify-settings-sidebar li a').click(function (evt) {
                        $('.timify-settings-sidebar li').removeClass('active');
                        $(this).closest('li').addClass('active').blur();

                        var clicked_group = $(this).attr('href');
                        if (typeof(localStorage) != 'undefined') {
                            localStorage.setItem("activetab", $(this).attr('href'));
                        }
                        $('.group').hide();
                        $(clicked_group).fadeIn();
                        evt.preventDefault();
                    });

                    $('.wpsa-browse').on('click', function (event) {
                        event.preventDefault();
                        var self = $(this);
                        // Create the media frame.
                        var file_frame = wp.media.frames.file_frame = wp.media({
                            title: self.data('uploader_title'),
                            button: {
                                text: self.data('uploader_button_text'),
                            },
                            multiple: false
                        });
                        file_frame.on('select', function () {
                            attachment = file_frame.state().get('selection').first().toJSON();
                            self.prev('.wpsa-url').val(attachment.url).change();
                        });
                        // Finally, open the modal
                        file_frame.open();
                    });
                });
            </script>
			<?php
		}

		function _style_fix() {
			global $wp_version;
			?>
            <style type="text/css">
                <?php  if (version_compare($wp_version, '3.8', '<=')):?>
                /** WordPress 3.8 Fix **/
                .form-table th {
                    padding: 20px 10px;
                }

                <?php endif; ?>
                .timify-settings *, .timify-settings *::before, .timify-settings *::after {
                    box-sizing: border-box;
                }
				.timify-settings .bg_color {
					box-sizing: inherit;
				}

                .timify-settings {
                    margin: 16px 0;
                }

                .timify-settings.d-flex {
                    display: -ms-flexbox !important;
                    display: flex !important;
                }

                .timify-settings-sidebar {
                    position: relative;
                    z-index: 1;
                    min-width: 185px;
                    background-color: #eaeaea;
                    border-bottom: 1px solid #cccccc;
                    border-left: 1px solid #cccccc;
                }

                .timify-settings-sidebar > ul {
                    margin: 0;
                    /*overflow: hidden;*/
                }

                .timify-settings-sidebar > ul > li {
                    margin: 0;
                    /*overflow: hidden;*/
                }

                .timify-settings-sidebar > ul > li:first-child a {
                    border-top-color: #cccccc;
                }

                .timify-settings-sidebar > ul > li a {
                    display: block;
                    padding: 0 20px;
                    margin: 0 -1px 0 0;
                    overflow: hidden;
                    font-size: 13px;
                    font-weight: 700;
                    line-height: 3;
                    color: #777;
                    text-decoration: none;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    border-top: 1px solid #f7f5f5;
                    border-bottom: 1px solid #cccccc;
                    width: 100%;
                    border-right: 0;
                    border-left: 0;
                    box-shadow: none !important;
                }

                .timify-settings-sidebar > ul > li.active a {
                    color: #23282d;
                    background-color: #fff;
                    border-right: 1px solid #fff !important;
                }

                .timify-settings-content {
                    position: relative;
                    width: 100%;
                    padding: 10px 20px;
                    background-color: #fff;
                    border: 1px solid #cccccc;
                    min-height: 500px;
                }

                .timify-settings-content h2 {
                    padding-bottom: 16px;
                    margin: 8px 0 16px;
                    font-size: 18px;
                    font-weight: 300;
                    border-bottom: 1px solid #cccccc;

                }
                .timify-settings-content form h2 {
                    font-weight: 500;
                }
				select#timify_settings\[time\]\[type\] {
   				 margin-top: -3px;
				}
				.timify-100-number{
					width: 100px;
				}
				.wp-picker-container .iris-picker {
					box-sizing: content-box;
				}

            </style>
			<?php
		}
	}
endif;
