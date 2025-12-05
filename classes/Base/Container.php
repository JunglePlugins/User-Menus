<?php
/**
 * Plugin container.
 *
 * @package UserMenus
 */

namespace UserMenus\Base;

defined( 'ABSPATH' ) || exit;

/**
 * Simple dependency injection container.
 */
class Container implements \ArrayAccess {

	/**
	 * Container values.
	 *
	 * @var array<string,mixed>
	 */
	protected $values = [];

	/**
	 * Frozen services (already resolved).
	 *
	 * @var array<string,bool>
	 */
	protected $frozen = [];

	/**
	 * Raw service definitions.
	 *
	 * @var array<string,callable>
	 */
	protected $raw = [];

	/**
	 * Constructor.
	 *
	 * @param array<string,mixed> $values Initial values.
	 */
	public function __construct( array $values = [] ) {
		foreach ( $values as $key => $value ) {
			$this->offsetSet( $key, $value );
		}
	}

	/**
	 * Check if offset exists.
	 *
	 * @param string $offset Offset key.
	 * @return bool
	 */
	public function offsetExists( $offset ): bool {
		return isset( $this->values[ $offset ] );
	}

	/**
	 * Get offset value.
	 *
	 * @param string $offset Offset key.
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		if ( ! isset( $this->values[ $offset ] ) ) {
			return null;
		}

		if ( isset( $this->raw[ $offset ] ) && ! isset( $this->frozen[ $offset ] ) ) {
			$this->frozen[ $offset ] = true;
			$this->values[ $offset ] = $this->values[ $offset ]( $this );
		}

		return $this->values[ $offset ];
	}

	/**
	 * Set offset value.
	 *
	 * @param string $offset Offset key.
	 * @param mixed  $value  Value to set.
	 */
	public function offsetSet( $offset, $value ): void {
		if ( is_callable( $value ) && ! is_string( $value ) ) {
			$this->raw[ $offset ] = true;
		}
		$this->values[ $offset ] = $value;
	}

	/**
	 * Unset offset.
	 *
	 * @param string $offset Offset key.
	 */
	public function offsetUnset( $offset ): void {
		unset( $this->values[ $offset ], $this->frozen[ $offset ], $this->raw[ $offset ] );
	}

	/**
	 * Get item from container.
	 *
	 * @param string $id Key for the item.
	 * @return mixed
	 */
	public function get( $id ) {
		return $this->offsetGet( $id );
	}

	/**
	 * Set item in container.
	 *
	 * @param string $id    Key for the item.
	 * @param mixed  $value Value to set.
	 */
	public function set( $id, $value ) {
		$this->offsetSet( $id, $value );
	}
}
