<?php
/*
Plugin Name: CMB2 Field Type: Google Maps
Plugin URI: https://github.com/mustardBees/cmb_field_map
GitHub Plugin URI: https://github.com/mustardBees/cmb_field_map
Description: Google Maps field type for CMB2.
Version: 2.2.0
Author: Phil Wylie
Author URI: https://www.philwylie.co.uk/
License: GPLv2+
*/

/**
 * Class PW_CMB2_Field_Google_Maps.
 */
class PW_CMB2_Field_Google_Maps {

	/**
	 * Current version number.
	 */
	const VERSION = '2.2.0';

	/**
	 * Initialize the plugin by hooking into CMB2.
	 */
	public function __construct() {
		add_filter( 'cmb2_render_pw_map', array( $this, 'render_pw_map' ), 10, 5 );
		add_filter( 'cmb2_sanitize_pw_map', array( $this, 'sanitize_pw_map' ), 10, 4 );
		add_filter( 'pw_google_api_key', array( $this, 'google_api_key_constant' ) );
	}

	/**
	 * Render field.
	 */
	public function render_pw_map( $field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object ) {

		// Get the Google API key from the field's parameters.
		$api_key = $field->args( 'api_key' );

		// Allow a custom hook to specify the key.
		$api_key = apply_filters( 'pw_google_api_key', $api_key );

		$this->setup_admin_scripts( $api_key );

		echo $field_type_object->input( array(
			'type'  => 'text',
			'name'  => $field->args( '_name' ) . '[address]',
			'value' => isset( $field_escaped_value['address'] ) ? $field_escaped_value['address'] : '',
			'class' => 'large-text pw-map-search',
			'desc'  => '',
		) );

		echo '<div class="pw-map"></div>';

		$field_type_object->_desc( true, true );

		echo $field_type_object->input( array(
			'type'       => 'hidden',
			'name'       => $field->args('_name') . '[latitude]',
			'value'      => isset( $field_escaped_value['latitude'] ) ? $field_escaped_value['latitude'] : '',
			'class'      => 'pw-map-latitude',
			'desc'       => '',
		) );
		
		echo $field_type_object->input( array(
			'type'       => 'hidden',
			'name'       => $field->args('_name') . '[longitude]',
			'value'      => isset( $field_escaped_value['longitude'] ) ? $field_escaped_value['longitude'] : '',
			'class'      => 'pw-map-longitude',
			'desc'       => '',
		) );

		echo $field_type_object->input( array(
			'type'			=> 'hidden',
			'name'       => $field->args('_name') . '[street]',
			'value'      => isset( $field_escaped_value['street'] ) ? $field_escaped_value['street'] : '',
			'class'      => 'pw-map-street',
		) );

		echo $field_type_object->input( array(
			'type'			=> 'hidden',
			'name'       => $field->args('_name') . '[street_number]',
			'value'      => isset( $field_escaped_value['street_number'] ) ? $field_escaped_value['street_number'] : '',
			'class'      => 'pw-map-street_number',
		) );

		echo $field_type_object->input( array(
			'type'			=> 'hidden',
			'name'       => $field->args('_name') . '[locality]',
			'value'      => isset( $field_escaped_value['locality'] ) ? $field_escaped_value['locality'] : '',
			'class'      => 'pw-map-locality',
		) );

		echo $field_type_object->input( array(
			'type'			=> 'hidden',
			'name'       => $field->args('_name') . '[postal_code]',
			'value'      => isset( $field_escaped_value['postal_code'] ) ? $field_escaped_value['postal_code'] : '',
			'class'      => 'pw-map-postal_code',
		) );

	}

	/**
	 * Optionally save the latitude/longitude values into two custom fields.
	 */
	public function sanitize_pw_map( $override_value, $value, $object_id, $field_args ) {

		if ( ! empty( $value['address'] ) ) {
			update_post_meta( $object_id, $field_args['id'] . '_address', $value['address'] );
		}

		if ( ! empty( $value['street'] ) ) {
			update_post_meta( $object_id, $field_args['id'] . '_street', $value['street'] );
		}

		if ( ! empty( $value['street_number'] ) ) {
			update_post_meta( $object_id, $field_args['id'] . '_street_number', $value['street_number'] );
		}

		if ( ! empty( $value['locality'] ) ) {
			update_post_meta( $object_id, $field_args['id'] . '_locality', $value['locality'] );
		}

		if ( isset( $field_args['split_values'] ) && $field_args['split_values'] ) {
			if ( ! empty( $value['latitude'] ) ) {
				update_post_meta( $object_id, $field_args['id'] . '_latitude', $value['latitude'] );
			}

			if ( ! empty( $value['longitude'] ) ) {
				update_post_meta( $object_id, $field_args['id'] . '_longitude', $value['longitude'] );
			}
		}

		return $value;
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public function setup_admin_scripts($api_key) {
		wp_register_script( 'pw-google-maps-api', "https://maps.googleapis.com/maps/api/js?key={$api_key}&libraries=places", null, null );
		wp_enqueue_script( 'pw-google-maps', plugins_url( 'js/script.js', __FILE__ ), array( 'pw-google-maps-api', 'jquery' ), self::VERSION );
		wp_enqueue_style( 'pw-google-maps', plugins_url( 'css/style.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Default filter to return a Google API key constant if defined.
	 */
	public function google_api_key_constant( $google_api_key = null ) {

		// Allow the field's 'api_key' parameter or a custom hook to take precedence.
		if ( ! empty( $google_api_key ) ) {
			return $google_api_key;
		}

		if ( defined( 'PW_GOOGLE_API_KEY' ) ) {
			$google_api_key = PW_GOOGLE_API_KEY;
		}

		return $google_api_key;
	}
}
$pw_cmb2_field_google_maps = new PW_CMB2_Field_Google_Maps();
