<?php
/**
 * Plugin Autoloader.
 *
 * @package UserMenus
 */

namespace UserMenus\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Autoloader
 */
class Autoloader {

	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	protected static $plugin_name;

	/**
	 * Plugin path.
	 *
	 * @var string
	 */
	protected static $plugin_path;

	/**
	 * Class map for autoloading.
	 *
	 * @var array<string,string>
	 */
	protected static $class_map = [];

	/**
	 * Initialize the autoloader.
	 *
	 * @param string $plugin_name Plugin name.
	 * @param string $plugin_path Plugin path.
	 *
	 * @return bool
	 */
	public static function init( $plugin_name, $plugin_path ) {
		self::$plugin_name = $plugin_name;
		self::$plugin_path = $plugin_path;

		spl_autoload_register( [ __CLASS__, 'autoload' ] );

		return true;
	}

	/**
	 * Autoload classes.
	 *
	 * @param string $class_name Class name.
	 */
	public static function autoload( $class_name ) {
		// Check if class is in our namespace.
		if ( 0 !== strpos( $class_name, 'UserMenus\\' ) ) {
			return;
		}

		// Remove namespace prefix.
		$relative_class = substr( $class_name, strlen( 'UserMenus\\' ) );

		// Convert namespace separators to directory separators.
		$file = str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class ) . '.php';

		// Build the full path.
		$path = self::$plugin_path . 'classes' . DIRECTORY_SEPARATOR . $file;

		// Include the file if it exists.
		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}
}
