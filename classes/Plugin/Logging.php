<?php
/**
 * Logging class.
 *
 * @package UserMenus\Plugin
 */

namespace UserMenus\Plugin;

/**
 * Logging class.
 */
class Logging {

	/**
	 * Log file prefix.
	 */
	const LOG_FILE_PREFIX = 'user-menus-';

	/**
	 * Container.
	 *
	 * @var \UserMenus\Base\Container
	 */
	private $c;

	/**
	 * Whether the log file is writable.
	 *
	 * @var bool
	 */
	private $is_writable;

	/**
	 * Log file name.
	 *
	 * @var string
	 */
	private $filename = '';

	/**
	 * Log file path.
	 *
	 * @var string
	 */
	private $file = '';

	/**
	 * File system API.
	 *
	 * @var WP_Filesystem_Base
	 */
	private $fs;

	/**
	 * Log file content.
	 *
	 * @var string
	 */
	private $content;

	/**
	 * Initialize logging.
	 *
	 * @param \UserMenus\Base\Container $c Container.
	 */
	public function __construct( $c ) {
		$this->c = $c;

		$this->init();

		$this->register_hooks();
	}

	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		// On shutdown, save the log file.
		add_action( 'shutdown', [ $this, 'save_logs' ] );
	}

	/**
	 * Gets the Uploads directory
	 *
	 * @return bool|array An associated array with baseurl and basedir or false on failure
	 */
	public function get_upload_dir() {
		if ( defined( '\IS_WPCOM' ) && \IS_WPCOM ) {
			$wp_upload_dir = wp_get_upload_dir();
		} else {
			$wp_upload_dir = wp_upload_dir();
		}

		if ( isset( $wp_upload_dir['error'] ) && false !== $wp_upload_dir['error'] ) {
			return false;
		} else {
			return $wp_upload_dir;
		}
	}

	/**
	 * Gets the uploads directory URL
	 *
	 * @param string $path A path to append to end of upload directory URL.
	 * @return bool|string The uploads directory URL or false on failure
	 */
	public function get_upload_dir_url( $path = '' ) {
		$upload_dir = $this->get_upload_dir();
		if ( false !== $upload_dir && isset( $upload_dir['baseurl'] ) ) {
			$url = preg_replace( '/^https?:/', '', $upload_dir['baseurl'] );
			if ( null === $url ) {
				return false;
			}
			if ( ! empty( $path ) ) {
				$url = trailingslashit( $url ) . $path;
			}
			return $url;
		} else {
			return false;
		}
	}

	/**
	 * Chek if logging is enabled.
	 *
	 * @return bool
	 */
	public function enabled() {
		return defined( 'USER_MENUS_LOGGING' ) && USER_MENUS_LOGGING && $this->is_writable();
	}

	/**
	 * Get working WP Filesystem instance
	 *
	 * @return WP_Filesystem_Base|false
	 */
	public function fs() {
		if ( isset( $this->fs ) ) {
			return $this->fs;
		}

		global $wp_filesystem;

		require_once ABSPATH . 'wp-admin/includes/file.php';

		// If for some reason the include doesn't work as expected just return false.
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			return false;
		}

		$writable = WP_Filesystem( false, '', true );

		// We consider the directory as writable if it uses the direct transport,
		// otherwise credentials would be needed.
		$this->fs = ( $writable && 'direct' === $wp_filesystem->method ) ? $wp_filesystem : false;

		return $this->fs;
	}

	/**
	 * Check if the log file is writable.
	 *
	 * @return boolean
	 */
	public function is_writable() {
		if ( isset( $this->is_writable ) ) {
			return $this->is_writable;
		}

		$this->is_writable = false !== $this->fs() && 'direct' === $this->fs()->method;

		$upload_dir = $this->get_upload_dir();

		if ( ! $this->fs()->is_writable( $upload_dir['basedir'] ) ) {
			$this->is_writable = false;
		}

		return $this->is_writable;
	}

	/**
	 * Get things started
	 */
	public function init() {
		$upload_dir = $this->get_upload_dir();

		$file_token = get_option( 'user_menus_debug_log_token' );
		if ( false === $file_token ) {
			$file_token = uniqid( wp_rand(), true );
			update_option( 'user_menus_debug_log_token', $file_token );
		}

		$this->filename = self::LOG_FILE_PREFIX . "debug-{$file_token}.log"; // ex. user-menus-debug-5c2f6a9b9b5a3.log.
		$this->file     = trailingslashit( $upload_dir['basedir'] ) . $this->filename;

		if ( ! $this->fs()->exists( $this->file ) ) {
			$this->setup_new_log();
		} else {
			$this->content = $this->get_file( $this->file );
		}

		// Truncate long log files.
		if ( $this->fs()->exists( $this->file ) && $this->fs()->size( $this->file ) >= 1048576 ) {
			$this->truncate_log();
		}
	}

	/**
	 * Retrieves the url to the file
	 *
	 * @returns string
	 */
	public function get_file_url() {
		return $this->get_upload_dir_url( $this->filename );
	}

	/**
	 * Retrieve the log data
	 *
	 * @return string
	 */
	public function get_log() {
		return $this->get_log_content();
	}

	/**
	 * Log message to file
	 *
	 * @param string $message The message to log.
	 */
	public function log( $message = '' ) {
		$this->write_to_log( wp_date( 'Y-n-d H:i:s' ) . ' - ' . $message );
	}

	/**
	 * Log unique message to file.
	 *
	 * @param string $message The unique message to log.
	 */
	public function log_unique( $message = '' ) {
		$contents = $this->get_log_content();

		if ( strpos( $contents, $message ) !== false ) {
			return;
		}

		$this->log( $message );
	}

	/**
	 * Get the log file contents.
	 *
	 * @return string
	 */
	public function get_log_content() {
		if ( ! isset( $this->content ) ) {
			$this->content = $this->get_file();
		}

		return $this->content;
	}

	/**
	 * Set the log file contents in memory.
	 *
	 * @param mixed $content The content to set.
	 * @param bool  $save    Whether to save the content to the file immediately.
	 * @return void
	 */
	private function set_log_content( $content, $save = false ) {
		$this->content = $content;

		if ( $save ) {
			$this->save_logs();
		}
	}

	/**
	 * Retrieve the contents of a file.
	 *
	 * @param string|boolean $file File to get contents of.
	 *
	 * @return string
	 */
	protected function get_file( $file = false ) {
		$file = $file ? $file : $this->file;

		if ( ! $this->enabled() ) {
			return '';
		}

		$content = '';

		if ( $this->fs()->exists( $file ) ) {
			$content = $this->fs()->get_contents( $file );
		}

		return $content;
	}

	/**
	 * Write the log message
	 *
	 * @param string $message The message to write.
	 */
	protected function write_to_log( $message = '' ) {
		if ( ! $this->enabled() ) {
			return;
		}

		$contents = $this->get_log_content();

		// If it doesn't end with a new line, add one. \r\n length is 2.
		if ( substr( $contents, -2 ) !== "\r\n" ) {
			$contents .= "\r\n";
		}

		$this->set_log_content( $contents . $message );
	}

	/**
	 * Save the current contents to file.
	 */
	public function save_logs() {
		if ( ! $this->enabled() ) {
			return;
		}

		$this->fs()->put_contents( $this->file, $this->content, FS_CHMOD_FILE );
	}

	/**
	 * Get a line count.
	 *
	 * @return int
	 */
	public function count_lines() {
		$file  = $this->get_log_content();
		$lines = explode( "\r\n", $file );

		return count( $lines );
	}

	/**
	 * Truncates a log file to maximum of 250 lines.
	 */
	public function truncate_log() {
		$content           = $this->get_log_content();
		$lines             = explode( "\r\n", $content );
		$lines             = array_slice( $lines, 0, 250 ); // 50 is how many lines you want to keep
		$truncated_content = implode( "\r\n", $lines );
		$this->set_log_content( $truncated_content, true );
	}

	/**
	 * Set up a new log file.
	 *
	 * @return void
	 */
	public function setup_new_log() {
		$this->set_log_content( "User Menus Debug Logs:\r\n" . wp_date( 'Y-n-d H:i:s' ) . " - Log file initialized\r\n", true );
	}

	/**
	 * Delete the log file.
	 */
	public function clear_log() {
		// Delete the file.
		@$this->fs()->delete( $this->file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		if ( $this->enabled() ) {
			$this->setup_new_log();
		}
	}

	/**
	 * Log a deprecated notice.
	 *
	 * @param string $func_name Function name.
	 * @param string $version Versoin deprecated.
	 * @param string $replacement Replacement function (optional).
	 */
	public function log_deprecated_notice( $func_name, $version, $replacement = null ) {
		if ( ! is_null( $replacement ) ) {
			$notice = sprintf( '%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.', $func_name, $version, $replacement );
		} else {
			$notice = sprintf( '%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.', $func_name, $version );
		}

		$this->log_unique( $notice );
	}
}
