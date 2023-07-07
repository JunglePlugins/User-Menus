<?php
/**
 * Option functions.
 *
 * @package UserMenus
 */

namespace UserMenus;

/**
 * Get all options
 *
 * @return array
 */
function get_all_plugin_options() {
	return plugin( 'options' )->get_all();
}

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @param string     $key Option key.
 * @param mixed|bool $default_value Default value.
 *
 * @return mixed|void
 */
function get_option( $key, $default_value = false ) {
	return plugin( 'options' )->get( $key, $default_value );
}

/**
 * Update an option
 *
 * Updates an setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *          the key from the _options array.
 *
 * @param string          $key The Key to update.
 * @param string|bool|int $value The value to set the key to.
 *
 * @return boolean True if updated, false if not.
 */
function update_option( $key = '', $value = false ) {
	return plugin( 'options' )->update( $key, $value );
}

/**
 * Update many values at once.
 *
 * @param array $new_options Array of new replacement options.
 *
 * @return bool
 */
function update_options( $new_options = [] ) {
	return plugin( 'options' )->update_many( $new_options );
}

/**
 * Remove an option
 *
 * @param string|string[] $keys Can be a single string  or array of option keys.
 *
 * @return boolean True if updated, false if not.
 */
function delete_options( $keys = '' ) {
	return plugin( 'options' )->delete( $keys );
}

/**
 * Get index of blockTypes.
 *
 * @return array
 */
function get_block_types() {
	return \get_option( 'user_menus_known_blockTypes', [] );
}

/**
 * Sanitize expeced block type data.
 *
 * @param array $type Block type definition.
 * @return array Sanitized definition.
 */
function sanitize_block_type( $type = [] ) {
	foreach ( $type as $key => $value ) {
		$type[ $key ] = is_array( $value )
			? sanitize_block_type( $value )
			: \sanitize_text_field( $value );
	}

	return $type;
}

/**
 * Update block type list.
 *
 * @param array $incoming_block_types Array of updated block type declarations.
 */
function update_block_types( $incoming_block_types = [] ) {
	$block_types = [];

	// Convert to a named index for deduplication.
	foreach ( get_block_types() as $type ) {
		$block_types[ $type['name'] ] = $type;
	}

	// Add or update incoming block types into the array.
	foreach ( $incoming_block_types as $type ) {
		// Sanitize each new block type.
		$block_types[ $type['name'] ] = sanitize_block_type( $type );
	}

	// Flatten values to a simple array for storage.
	$block_types = array_values( $block_types );

	\update_option( 'user_menus_known_blockTypes', $block_types );
}

/**
 * Get default denial message.
 *
 * @return string
 */
function get_default_denial_message() {
	if ( \UserMenus\get_data_version( 'settings' ) === 1 ) {
		$settings = get_option( 'ca_um_settings', [] );

		return isset( $settings['default_denial_message'] ) ? $settings['default_denial_message'] : '';
	}

	return get_option( 'defaultDenialMessage', '' );
}
