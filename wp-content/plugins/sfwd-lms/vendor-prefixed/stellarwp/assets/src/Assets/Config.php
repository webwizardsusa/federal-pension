<?php
/**
 * @license GPL-2.0
 *
 * Modified by learndash on 18-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
namespace StellarWP\Learndash\StellarWP\Assets;

use RuntimeException;

class Config {
	/**
	 * @var string
	 */
	protected static string $hook_prefix = '';

	/**
	 * @var string
	 */
	protected static string $relative_asset_path = 'src/assets/';

	/**
	 * @var string
	 */
	protected static string $root_path = '';

	/**
	 * @var array<string, array<string, string>>
	 */
	protected static array $group_paths = [];

	/**
	 * @var string
	 */
	protected static string $version = '';

	/**
	 * @var array<string, string>
	 */
	protected static array $path_urls = [];

	/**
	 * Gets the hook prefix.
	 *
	 * @return string
	 */
	public static function get_hook_prefix(): string {
		if ( static::$hook_prefix === '' ) {
			$class = __CLASS__;
			throw new RuntimeException( "You must specify a hook prefix for your project with {$class}::set_hook_prefix()" );
		}
		return static::$hook_prefix;
	}

	/**
	 * Gets the root path of a group.
	 *
	 * @since 1.4.0
	 *
	 * @return string
	 */
	public static function get_path_of_group_path( string $group ): string {
		return ( static::$group_paths[ $group ] ?? [] )['root'] ?? '';
	}

	/**
	 * Gets the relative path of a group.
	 *
	 * @since 1.4.0
	 *
	 * @return string
	 */
	public static function get_relative_path_of_group_path( string $group ): string {
		return ( static::$group_paths[ $group ] ?? [] )['relative'] ?? '';
	}

	/**
	 * Adds a group path.
	 *
	 * @since 1.4.0
	 *
	 * @throws RuntimeException If the root or relative path is not specified.
	 *
	 * @param string $group_path_slug The slug of the group path.
	 * @param string $root            The root path of the group.
	 * @param string $relative        The relative path of the group.
	 *
	 * @return void
	 */
	public static function add_group_path( string $group_path_slug, string $root, string $relative ): void {
		static::$group_paths[ $group_path_slug ] = [
			'root'     => self::normalize_path( $root ),
			'relative' => trailingslashit( $relative ),
		];
	}

	/**
	 * Gets the root path of the project.
	 *
	 * @return string
	 */
	public static function get_path(): string {
		if ( static::$root_path === '' ) {
			$class = __CLASS__;
			throw new RuntimeException( "You must specify a path to the root of you project with {$class}::set_path()" );
		}
		return static::$root_path;
	}

	/**
	 * Gets the relative asset path of the project.
	 *
	 * @return string
	 */
	public static function get_relative_asset_path(): string {
		return static::$relative_asset_path;
	}

	/**
	 * Gets the root path of the project.
	 *
	 * @return string
	 */
	public static function get_url( $path ): string {
		$path = wp_normalize_path( $path );
		$key  = Utils::get_runtime_cache_key( [ $path ] );

		if ( empty( static::$path_urls[ $key ] ) ) {
			$bases = Utils::get_bases();
			static::$path_urls[ $key ] = trailingslashit( str_replace( wp_list_pluck( $bases, 'base_dir' ), wp_list_pluck( $bases, 'base_url' ), $path ) );
		}

		return static::$path_urls[ $key ];
	}

	/**
	 * Gets the version of the project.
	 *
	 * @return string
	 */
	public static function get_version(): string {
		return static::$version;
	}

	/**
	 * Resets this class back to the defaults.
	 */
	public static function reset() {
		static::$hook_prefix         = '';
		static::$relative_asset_path = 'src/assets/';
		static::$root_path           = '';
		static::$path_urls           = [];
		static::$version             = '';
	}

	/**
	 * Sets the hook prefix.
	 *
	 * @param string $prefix The prefix to add to hooks.
	 *
	 * @return void
	 */
	public static function set_hook_prefix( string $prefix ) {
		static::$hook_prefix = $prefix;
	}

	/**
	 * Sets the relative asset path of the project.
	 *
	 * @param string $path The root path of the project.
	 *
	 * @return void
	 */
	public static function set_relative_asset_path( string $path ) {
		static::$relative_asset_path = trailingslashit( $path );
	}

	/**
	 * Sets the root path of the project.
	 *
	 * @param string $path The root path of the project.
	 *
	 * @return void
	 */
	public static function set_path( string $path ) {
		static::$root_path = self::normalize_path( $path );
	}

	/**
	 * Sets the version of the project.
	 *
	 * @param string $version The version of the project.
	 *
	 * @return void
	 */
	public static function set_version( string $version ) {
		static::$version = $version;
	}

	/**
	 * Normalizes a path.
	 *
	 * @since 1.4.0
	 *
	 * @param string $path The path to normalize.
	 *
	 * @return string
	 */
	protected static function normalize_path( string $path ): string {
		$plugin_dir = wp_normalize_path( WP_PLUGIN_DIR );
		$path       = wp_normalize_path( $path );

		$plugins_content_dir_position = $plugin_dir ? strpos( $path, $plugin_dir ) : false;
		$themes_content_dir_position  = strpos( $path, wp_normalize_path( get_theme_root() ) );

		if (
			$plugins_content_dir_position === false
			&& $themes_content_dir_position === false
		) {
			// Default to plugins.
			$path = $plugin_dir . $path;
		} elseif ( $plugins_content_dir_position !== false ) {
			$path = substr( $path, $plugins_content_dir_position );
		} elseif ( $themes_content_dir_position !== false ) {
			$path = substr( $path, $themes_content_dir_position );
		}

		return trailingslashit( $path );
	}
}
