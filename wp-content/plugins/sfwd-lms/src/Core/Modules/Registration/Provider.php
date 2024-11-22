<?php
/**
 * LearnDash Registration Provider class.
 *
 * @since 4.16.0
 *
 * @package LearnDash\Core
 */

namespace LearnDash\Core\Modules\Registration;

use StellarWP\Learndash\lucatume\DI52\ContainerException;
use StellarWP\Learndash\lucatume\DI52\ServiceProvider;

/**
 * Service provider class for registration.
 *
 * @since 4.16.0
 */
class Provider extends ServiceProvider {
	/**
	 * Register service providers.
	 *
	 * @since 4.16.0
	 *
	 * @throws ContainerException If there's an issue while trying to bind the implementation.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->container->singleton( Assets::class );
		$this->container->singleton( Settings::class );
		$this->container->singleton( Form::class );

		$this->register_actions();
		$this->register_filters();
	}

	/**
	 * Register actions.
	 *
	 * @since 4.16.0
	 *
	 * @return void
	 */
	public function register_actions(): void {
		add_action( 'init', $this->container->callback( Assets::class, 'register_scripts' ) );
		add_action( 'learndash_initialization_new_install', $this->container->callback( Settings::class, 'action_set_new_install_appearance' ) );
		add_action(
			'login_form_register',
			$this->container->callback(
				Form::class,
				'set_confirm_password'
			)
		);
	}

	/**
	 * Register filters.
	 *
	 * @since 4.16.0
	 *
	 * @return void
	 */
	public function register_filters(): void {
		add_filter( 'learndash_registration_variation', static fn() => learndash_registration_variation() );
	}
}
