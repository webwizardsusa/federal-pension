<?php
/**
 * Authentication provider interface
 *
 * @package Requests\Authentication
 *
 * @license ISC
 * Modified by learndash on 18-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace StellarWP\Learndash\WpOrg\Requests;

use StellarWP\Learndash\WpOrg\Requests\Hooks;

/**
 * Authentication provider interface
 *
 * Implement this interface to act as an authentication provider.
 *
 * Parameters should be passed via the constructor where possible, as this
 * makes it much easier for users to use your provider.
 *
 * @see \StellarWP\Learndash\WpOrg\Requests\Hooks
 *
 * @package Requests\Authentication
 */
interface Auth {
	/**
	 * Register hooks as needed
	 *
	 * This method is called in {@see \WpOrg\Requests\Requests::request()} when the user
	 * has set an instance as the 'auth' option. Use this callback to register all the
	 * hooks you'll need.
	 *
	 * @see \StellarWP\Learndash\WpOrg\Requests\Hooks::register()
	 * @param \StellarWP\Learndash\WpOrg\Requests\Hooks $hooks Hook system
	 */
	public function register(Hooks $hooks);
}
