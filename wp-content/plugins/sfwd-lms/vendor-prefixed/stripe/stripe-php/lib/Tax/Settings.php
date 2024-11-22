<?php

// File generated from our OpenAPI spec

namespace StellarWP\Learndash\Stripe\Tax;

/**
 * You can use Tax <code>Settings</code> to manage configurations used by Stripe Tax calculations.
 *
 * Related guide: <a href="https://stripe.com/docs/tax/settings-api">Using the Settings API</a>
 *
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property \StellarWP\Learndash\Stripe\StripeObject $defaults
 * @property null|\StellarWP\Learndash\Stripe\StripeObject $head_office The place where your business is located.
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property string $status The <code>active</code> status indicates you have all required settings to calculate tax. A status can transition out of <code>active</code> when new required settings are introduced.
 * @property \StellarWP\Learndash\Stripe\StripeObject $status_details
 *
 * @license MIT
 * Modified by learndash on 18-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class Settings extends \StellarWP\Learndash\Stripe\SingletonApiResource
{
    const OBJECT_NAME = 'tax.settings';

    use \StellarWP\Learndash\Stripe\ApiOperations\SingletonRetrieve;

    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';

    /**
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \StellarWP\Learndash\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return static the updated resource
     */
    public static function update($params = null, $opts = null)
    {
        self::_validateParams($params);
        $url = '/v1/tax/settings';

        list($response, $opts) = static::_staticRequest('post', $url, $params, $opts);
        $obj = \StellarWP\Learndash\Stripe\Util\Util::convertToStripeObject($response->json, $opts);
        $obj->setLastResponse($response);

        return $obj;
    }
}
