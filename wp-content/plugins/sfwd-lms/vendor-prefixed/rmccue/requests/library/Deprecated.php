<?php
/**
 * Backwards compatibility layer for Learndash_Core_Requests.
 *
 * Allows for Composer to autoload the old PSR-0 classes via the custom autoloader.
 * This prevents issues with _extending final classes_ (which was the previous solution).
 *
 * Please see the Changelog for the 2.0.0 release for upgrade notes.
 *
 * @package Learndash_Core_Requests
 *
 * @deprecated 2.0.0 Use the PSR-4 class names instead.
 *
 * @license ISC
 * Modified by learndash on 18-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

if (class_exists('StellarWP\Learndash\WpOrg\Requests\Autoload') === false) {
	require_once dirname(__DIR__) . '/src/Autoload.php';
}

StellarWP\Learndash\WpOrg\Requests\Autoload::register();
