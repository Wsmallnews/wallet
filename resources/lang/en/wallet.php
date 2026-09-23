<?php

// translations for Wsmallnews/Wallet
return [

    'global_default' => [
        'navigation_group' => 'Wallet',
    ],

    'transaction_types' => [
        'recharge' => 'Recharge',
        'consume' => 'Consume',
        'refund' => 'Refund',
        'adjust' => 'Adjust',
        'freeze' => 'Freeze',
        'unfreeze' => 'Unfreeze',
        'freeze_consume' => 'Frozen Consume',
        'frozen_credit' => 'Frozen Credit',
        'transfer_in' => 'Transfer In',
        'transfer_out' => 'Transfer Out',
    ],

    'recharge_status' => [
        'unpaid' => 'Unpaid',
        'paid' => 'Paid',
        'closed' => 'Closed',
    ],

    'transactions' => [
        'consume_description' => 'Payment deduction :pay_sn',
        'refund_description' => 'Refund credit :refund_sn',
    ],

    'recharges' => [
        'credit_description' => 'Wallet recharge',
    ],

    'wallets' => [
        'wallet_resource' => [
            'model_label' => 'Wallet',
            'plural_model_label' => 'Wallets',
            'navigation_label' => 'Wallets',
        ],
        'wallet_table' => [
            'owner' => 'Owner',
            'wallet_type' => 'Wallet Type',
            'balance' => 'Available Balance',
            'frozen' => 'Frozen',
            'team' => 'Team',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'search_placeholder' => 'Search wallets...',
        ],
        'transactions_relation' => [
            'title' => 'Transactions',
            'uuid' => 'UUID',
            'type' => 'Type',
            'amount' => 'Amount',
            'balance_change' => 'Balance Change',
            'frozen_change' => 'Frozen Change',
            'balance_after' => 'Balance After',
            'frozen_after' => 'Frozen After',
            'description' => 'Description',
            'created_at' => 'Created At',
        ],
        'actions' => [
            'credit' => 'Credit',
            'debit' => 'Debit',
            'freeze' => 'Freeze',
            'unfreeze' => 'Unfreeze',
            'amount' => 'Amount',
            'description' => 'Description',
            'description_placeholder' => 'Reason for this adjustment',
            'credit_success' => 'Wallet credited.',
            'debit_success' => 'Wallet debited.',
            'freeze_success' => 'Amount frozen.',
            'unfreeze_success' => 'Amount unfrozen.',
        ],
    ],

    'wallet_types' => [
        'wallet_type_resource' => [
            'model_label' => 'Wallet Type',
            'plural_model_label' => 'Wallet Types',
            'navigation_label' => 'Wallet Types',
        ],
        'wallet_type_table' => [
            'code' => 'Code',
            'name' => 'Name',
            'currency_code' => 'Currency Code',
            'decimals' => 'Decimals',
            'anchor_rate' => 'Anchor Rate',
            'enabled' => 'Enabled',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'search_placeholder' => 'Search wallet types...',
        ],
        'rates_relation' => [
            'title' => 'Anchor Rates',
            'team_id' => 'Team (empty = global default)',
            'anchor_rate' => 'Anchor Rate',
            'anchor_currency' => 'Anchor Currency',
            'enabled' => 'Enabled',
        ],
    ],

    'market_rates' => [
        'market_rate_resource' => [
            'model_label' => 'Market Rate',
            'plural_model_label' => 'Market Rates',
            'navigation_label' => 'Market Rates',
        ],
        'market_rate_table' => [
            'source_currency' => 'Source Currency',
            'target_currency' => 'Target Currency',
            'rate' => 'Rate',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'search_placeholder' => 'Search market rates...',
        ],
    ],

    'recharges' => [
        'recharge_resource' => [
            'model_label' => 'Recharge',
            'plural_model_label' => 'Recharges',
            'navigation_label' => 'Recharges',
        ],
        'recharge_table' => [
            'owner' => 'Owner',
            'wallet_type' => 'Wallet Type',
            'wallet_amount' => 'Wallet Amount',
            'pay_fee' => 'Pay Fee',
            'status' => 'Status',
            'paid_at' => 'Paid At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'search_placeholder' => 'Search recharges...',
        ],
    ],

];
