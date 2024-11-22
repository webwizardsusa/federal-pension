<?php

// File generated from our OpenAPI spec

namespace StellarWP\Learndash\Stripe\Service\Treasury;

/**
 * Service factory class for API resources in the Treasury namespace.
 *
 * @property CreditReversalService $creditReversals
 * @property DebitReversalService $debitReversals
 * @property FinancialAccountService $financialAccounts
 * @property InboundTransferService $inboundTransfers
 * @property OutboundPaymentService $outboundPayments
 * @property OutboundTransferService $outboundTransfers
 * @property ReceivedCreditService $receivedCredits
 * @property ReceivedDebitService $receivedDebits
 * @property TransactionEntryService $transactionEntries
 * @property TransactionService $transactions
 *
 * @license MIT
 * Modified by learndash on 18-November-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
class TreasuryServiceFactory extends \StellarWP\Learndash\Stripe\Service\AbstractServiceFactory
{
    /**
     * @var array<string, string>
     */
    private static $classMap = [
        'creditReversals' => CreditReversalService::class,
        'debitReversals' => DebitReversalService::class,
        'financialAccounts' => FinancialAccountService::class,
        'inboundTransfers' => InboundTransferService::class,
        'outboundPayments' => OutboundPaymentService::class,
        'outboundTransfers' => OutboundTransferService::class,
        'receivedCredits' => ReceivedCreditService::class,
        'receivedDebits' => ReceivedDebitService::class,
        'transactionEntries' => TransactionEntryService::class,
        'transactions' => TransactionService::class,
    ];

    protected function getServiceClass($name)
    {
        return \array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
    }
}
