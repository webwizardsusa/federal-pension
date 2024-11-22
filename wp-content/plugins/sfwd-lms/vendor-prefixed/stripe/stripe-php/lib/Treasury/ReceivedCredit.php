<?php

// File generated from our OpenAPI spec

namespace StellarWP\Learndash\Stripe\Treasury;

/**
 * ReceivedCredits represent funds sent to a <a href="https://stripe.com/docs/api#financial_accounts">FinancialAccount</a> (for example, via ACH or wire). These money movements are not initiated from the FinancialAccount.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property int $amount Amount (in cents) transferred.
 * @property int $created Time at which the object was created. Measured in seconds since the Unix epoch.
 * @property string $currency Three-letter <a href="https://www.iso.org/iso-4217-currency-codes.html">ISO currency code</a>, in lowercase. Must be a <a href="https://stripe.com/docs/currencies">supported currency</a>.
 * @property string $description An arbitrary string attached to the object. Often useful for displaying to users.
 * @property null|string $failure_code Reason for the failure. A ReceivedCredit might fail because the receiving FinancialAccount is closed or frozen.
 * @property null|string $financial_account The FinancialAccount that received the funds.
 * @property null|string $hosted_regulatory_receipt_url A <a href="https://stripe.com/docs/treasury/moving-money/regulatory-receipts">hosted transaction receipt</a> URL that is provided when money movement is considered regulated under Stripe's money transmission licenses.
 * @property \StellarWP\Learndash\Stripe\StripeObject $initiating_payment_method_details
 * @property \StellarWP\Learndash\Stripe\StripeObject $linked_flows
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property string $network The rails used to send the funds.
 * @property null|\StellarWP\Learndash\Stripe\StripeObject $reversal_details Details describing when a ReceivedCredit may be reversed.
 * @property string $status Status of the ReceivedCredit. ReceivedCredits are created either <code>succeeded</code> (approved) or <code>failed</code> (declined). If a ReceivedCredit is declined, the failure reason can be found in the <code>failure_code</code> field.
 * @property null|string|\StellarWP\Learndash\Stripe\Treasury\Transaction $transaction The Transaction associated with this object.
 *
 * Modified by learndash on 18-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class ReceivedCredit extends \StellarWP\Learndash\Stripe\ApiResource
{
    const OBJECT_NAME = 'treasury.received_credit';

    use \StellarWP\Learndash\Stripe\ApiOperations\All;
    use \StellarWP\Learndash\Stripe\ApiOperations\Retrieve;

    const FAILURE_CODE_ACCOUNT_CLOSED = 'account_closed';
    const FAILURE_CODE_ACCOUNT_FROZEN = 'account_frozen';
    const FAILURE_CODE_OTHER = 'other';

    const NETWORK_ACH = 'ach';
    const NETWORK_CARD = 'card';
    const NETWORK_STRIPE = 'stripe';
    const NETWORK_US_DOMESTIC_WIRE = 'us_domestic_wire';

    const STATUS_FAILED = 'failed';
    const STATUS_SUCCEEDED = 'succeeded';
}