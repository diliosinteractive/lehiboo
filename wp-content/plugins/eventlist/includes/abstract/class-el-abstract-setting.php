<?php

if ( !defined( 'ABSPATH' ) ) {
	exit();
}

abstract class EL_Abstract_Setting extends EL_Setting {

	/**
     * $_id tab id
     * @var null
     */
	public $_id = null;

	/**
     * $_title tab display
     * @var null
     */
	protected $_title = null;

	/**
     * $_fields
     * @var array
     */
	protected $_fields = array();

	/**
     * tab in tab setting
     * @var boolean
     */
	public $_tab = false;

	/**
     * options group
     * @var null
     */
	public $_options = null;

	/**
     * $_position
     * @var integer
     */
	protected $_position = 1;

	public function __construct() {
		if ( is_admin() ) {
			add_filter( 'el_admin_settings', array( $this, 'add_tab' ), $this->_position, 1 );
			add_action( 'el_admin_setting_' . $this->_id . '_content', array( $this, 'layout' ), $this->_position, 1 );
		}

		$this->options();

		add_filter( 'el_settings_field', array( $this, 'settings' ) );
	}

	public function settings( $settings ) {
		$settings[$this->_id] = $this;
		return $settings;
	}

	/**
     * add_tab setting
     * @param array
     */
	public function add_tab( $tabs ) {
		if ( $this->_id && $this->_title ) {
			$tabs[$this->_id] = $this->_title;
			return $tabs;
		}
	}

	/**
     * generate layout
     * @return html layout
     */
	public function layout() { 
        // before tab content
		do_action( 'el_admin_setting_before_setting_tab', $this->_id );
		
		$this->_fields = apply_filters( 'el_admin_setting_fields', $this->load_field(), $this->_id );

		if ( $this->_fields ) {
	

			// Get tab
            $tab_group = isset( $_GET['group'] ) && $_GET['group'] ? $_GET['group'] : '';

            if ( ! $tab_group ) {
            	$tab_group = current( array_keys( $this->_fields ) );
            }

			if ( $this->_tab ) {
				?>
				<h3>
				<?php
				foreach ( $this->_fields as $id => $groups ) {
					$class = 'el_tab_group';
					if ( $tab_group === $id ) $class = 'el_tab_group active';
					?>
					<a href="#<?php echo esc_attr( $id ); ?>" id="<?php echo esc_attr( $id ); ?>" class="<?php echo $class; ?>">
						<?php echo $groups['title']; ?>
					</a>
					<?php
				}
				?>
				</h3>
				<?php
			}

			if ( $this->_tab ) {
				foreach ( $this->_fields as $id => $groups ) {
					$class = 'el_tab_group_content';
					if ( $tab_group === $id ) $class = 'el_tab_group_content active';
					?>
					<div data-tab-id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?>">
						<?php if ( isset($groups['desc']) ): ?>
							<div class="desc_tab">
								<?php echo $groups['desc']; ?>
							</div>
						<?php endif; ?>
					
					<?php $this->generate_fields( $groups ); ?>
					<?php echo apply_filters( 'el_tab_group_after_content_'.$id, '' ); ?>
					</div>
					<?php
				}
			} else {
				$this->generate_fields( $this->_fields );
			}

		}
        // after tab content
		do_action( 'el_admin_setting_after_setting_tab' . $this->_id, $this->_id );
	}

	protected function load_field() {
		return array();
	}

	/**
     * genarate input atts
     * @param  $atts
     * @return string
     */
	public function render_atts( $atts = array() ) {
		if ( !is_array( $atts ) )
			return;

		$html = array();
		foreach ( $atts as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = implode( ' ', $value );
			}
			$html[] = $key . '="' . $value . '"';
		}
		return implode( ' ', $html );
	}

	/**
     * options load options
     * @return array || null
     */
	protected function options() {

		if ( $this->_options )
			return $this->_options;

		$options = parent::options();

		if ( !$options )
			$options = get_option( $this->_prefix, null );

		if ( isset( $options[$this->_id] ) )
			return $this->_options = $options[$this->_id];

		return null;
	}

	/**
     * get option value
     * @param  $name
     * @return option value. array, string, boolean
     */
	public function get( $name = null, $default = null ) {
		if ( !$this->_options )
			$this->_options = $this->options();

		if ( $name && isset( $this->_options[$name] ) && !is_array($this->_options[$name]) )
			return trim( $this->_options[$name] );

		if ( $name && isset( $this->_options[$name] ) && is_array($this->_options[$name]) )
			return $this->_options[$name];

		return $default;
	}


	/**
     * get_name_field
     * @param  $name of field option
     * @return string name field
     */
	public function get_field_id( $name = null, $group = null ) {
		if ( !$this->_prefix || !$name )
			return;

		if ( !$group )
			$group = $this->_id;

		if ( $group )
			return $this->_prefix . '_' . $group . '_' . $name;

		return $this->_prefix . '_' . $name;
	}

	/**
     * get_name_field
     * @param  $name of field option
     * @return string name field
     */
	public function get_field_name( $name = null, $group = null ) {
		if ( !$this->_prefix || !$name )
			return;
		
		if ( !$group )
			$group = $this->_id;

		if ( $group )
			return $this->_prefix . '[' . $group . '][' . $name . ']';

		return $this->_prefix . '[' . $name . ']';
	}

	/**
     * genterate fields settings
     * @param  array  $groups
     * @return html
     */
	function generate_fields( $groups = array() ) {
		$html = array();

		foreach ( $groups as $key => $group ) {

			// accordion
			$accordion = '';
			if ( isset( $group['accordion'] ) ) {
				$accordion = $group['accordion'];
			}

			if ( $accordion ) {
				?>
				<div class="ova_accordion">
					<?php
				foreach ( $accordion as $section_key => $section_name ) {
					?>
					<h3>
						<?php echo $section_name; ?>
					</h3>
					<div class="<?php echo esc_attr( $section_key ); ?>">

					<?php $this->generate_tables_accordion( $group, $section_key ); ?>

					</div>
					<?php } ?>
				</div>
				<?php
			} else {
				$this->generate_tables( $group );
			}
		}
	}

	public function generate_tables_accordion( $group = array(), $section_key = null ){
		

		if ( isset( $group['title'], $group['desc'] ) ) {
			?>
			<h3><?php echo esc_html( $group['title'] ); ?></h3>
			<p><?php echo esc_html( $group['desc'] ); ?></p>
			<?php
		}

		if ( isset( $group['fields'] ) ) {

			?>
			<table>
			<?php
			foreach ( $group['fields'] as $type => $field ) {

				$default = array(
					'belong_to' => '',
					'type' => '',
					'label' => '',
					'desc' => '',
					'atts' => array(
						'id' => '',
						'class' => ''
					),
					'name' => '',
					'group' => $this->_id ? $this->_id : null,
					'options' => array(
					),
					'default' => ''
				);

				if ( $section_key && $field['belong_to'] === $section_key ) {

					if ( isset( $field['filter'] ) && $field['filter'] ) {
					
						echo call_user_func_array( $field['filter'], array( $field ) );
				
					} else if ( isset( $field['name'], $field['type'] ) ) {
						?>
						<tr>

				
						<th><label for="<?php echo esc_attr( $this->get_field_id( $field['name'] ) ); ?>"><?php echo $field['label']; ?></label>
						<?php
						if ( isset( $field['desc'] ) ) {
							?>
							<p><small><?php echo esc_html( $field['desc'] ); ?></small></p>
							<?php
						} ?>
					
						</th>
						<td>
						<?php
						$field = wp_parse_args( $field, $default );

			
						include EL_PLUGIN_INC . 'admin/views/settings/fields/' . $field['type'] . '.php';
		
						?>
						</td>
            

						</tr>
						<?php
					}

				}
			}
			?>
			</table>
			<?php
		}
		
	}

	public function generate_tables( $group = array() ){
		$html = array();
		
		if ( isset( $group['title'], $group['desc'] ) ) { ?>
			<h3><?php echo $group['title']; ?></h3>
			<p><?php echo $group['desc']; ?></p>
		<?php
		}

		if ( isset( $group['fields'] ) ) {
			?>
			<table>
			<?php
			foreach ( $group['fields'] as $type => $field ) {

				$default = array(
					'belong_to' => '',
					'type' => '',
					'label' => '',
					'desc' => '',
					'atts' => array(
						'id' => '',
						'class' => ''
					),
					'name' => '',
					'group' => $this->_id ? $this->_id : null,
					'options' => array(
					),
					'default' => ''
				);

				if ( isset( $field['filter'] ) && $field['filter'] ) {
		
					echo call_user_func_array( $field['filter'], array( $field ) );
				
				} else if ( isset( $field['name'], $field['type'] ) ) {
					if ( $field['type'] == 'hidden' ) {
						?>
						<tr style="display: none;">
						<?php
					} else {
						?>
						<tr>
						<?php
					}
					?>
					<th><label for="<?php echo esc_attr( $this->get_field_id( $field['name'] ) ); ?>">
						<?php echo $field['label']; ?>
					</label>
					<?php
					if ( isset( $field['desc'] ) ) {
						?>
						<p><small>
							<?php echo $field['desc']; ?>
						</small></p>
						<?php
					}
					?>
					</th>
					<td>
					<?php
					$field = wp_parse_args( $field, $default );

					include EL_PLUGIN_INC . 'admin/views/settings/fields/' . $field['type'] . '.php';
			
					?>
					</td>

					</tr>
					<?php
				}
			}
			?>
			</table>
			<?php
		}
	}

}