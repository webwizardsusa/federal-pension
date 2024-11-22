<?php
/**
 * Learndash_Core_Requests for PHP
 *
 * Inspired by Learndash_Core_Requests for Python.
 *
 * Based on concepts from SimplePie_File, RequestCore and WP_Http.
 *
 * @package Learndash_Core_Requests
 *
 * @deprecated 2.0.0
 *
 * @license ISC
 * Modified by learndash on 18-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

/*
 * Integrators who cannot yet upgrade to the PSR-4 class names can silence deprecations
 * by defining a `REQUESTS_SILENCE_PSR0_DEPRECATIONS` constant and setting it to `true`.
 * The constant needs to be defined before this class is required.
 */
if (!defined('REQUESTS_SILENCE_PSR0_DEPRECATIONS') || REQUESTS_SILENCE_PSR0_DEPRECATIONS !== true) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
	trigger_error(
		'The PSR-0 `Requests_...` class names in the Learndash_Core_Requests library are deprecated.'
		. ' Switch to the PSR-4 `WpOrg\Requests\...` class names at your earliest convenience.',
		E_USER_DEPRECATED
	);

	// Prevent the deprecation notice from being thrown twice.
	if (!defined('REQUESTS_SILENCE_PSR0_DEPRECATIONS')) {
		define('REQUESTS_SILENCE_PSR0_DEPRECATIONS', true);
	}
}

require_once dirname(__DIR__) . '/src/Learndash_Core_Requests.php';

/**
 * Learndash_Core_Requests for PHP
 *
 * Inspired by Learndash_Core_Requests for Python.
 *
 * Based on concepts from SimplePie_File, RequestCore and WP_Http.
 *
 * @package Learndash_Core_Requests
 *
 * @deprecated 2.0.0 Use `WpOrg\Requests\Requests` instead for the actual functionality and
 *                   use `WpOrg\Requests\Autoload` for the autoloading.
 */
class Learndash_Core_Requests extends StellarWP\Learndash\WpOrg\Requests\Requests {

	/**
	 * Deprecated autoloader for Learndash_Core_Requests.
	 *
	 * @deprecated 2.0.0 Use the `WpOrg\Requests\Autoload::load()` method instead.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $class Class name to load
	 */
	public static function autoloader($class) {
		if (class_exists('StellarWP\Learndash\WpOrg\Requests\Autoload') === false) {
			require_once dirname(__DIR__) . '/src/Autoload.php';
		}

		return StellarWP\Learndash\WpOrg\Requests\Autoload::load($class);
	}

	/**
	 * Register the built-in autoloader
	 *
	 * @deprecated 2.0.0 Include the `WpOrg\Requests\Autoload` class and
	 *                   call `WpOrg\Requests\Autoload::register()` instead.
	 *
	 * @codeCoverageIgnore
	 */
	public static function register_autoloader() {
		require_once dirname(__DIR__) . '/src/Autoload.php';
		StellarWP\Learndash\WpOrg\Requests\Autoload::register();
	}
}
