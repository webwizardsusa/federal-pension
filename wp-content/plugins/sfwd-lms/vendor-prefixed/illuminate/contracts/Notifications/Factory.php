<?php
/**
 * @license MIT
 *
 * Modified by learndash on 18-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace StellarWP\Learndash\Illuminate\Contracts\Notifications;

interface Factory
{
    /**
     * Get a channel instance by name.
     *
     * @param  string|null  $name
     * @return mixed
     */
    public function channel($name = null);

    /**
     * Send the given notification to the given notifiable entities.
     *
     * @param  \StellarWP\Learndash\Illuminate\Support\Collection|array|mixed  $notifiables
     * @param  mixed  $notification
     * @return void
     */
    public function send($notifiables, $notification);

    /**
     * Send the given notification immediately.
     *
     * @param  \StellarWP\Learndash\Illuminate\Support\Collection|array|mixed  $notifiables
     * @param  mixed  $notification
     * @return void
     */
    public function sendNow($notifiables, $notification);
}
