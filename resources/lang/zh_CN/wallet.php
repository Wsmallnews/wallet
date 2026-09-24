<?php

// translations for Wsmallnews/Wallet
return [

    'global_default' => [
        'navigation_group' => '钱包',
    ],

    'transaction_types' => [
        'recharge' => '充值',
        'consume' => '消费',
        'refund' => '退款回款',
        'adjust' => '管理员调整',
        'freeze' => '冻结',
        'unfreeze' => '解冻',
        'freeze_consume' => '冻结扣减',
        'frozen_credit' => '冻结入账',
        'transfer_in' => '转入',
        'transfer_out' => '转出',
    ],

    'recharge_status' => [
        'unpaid' => '待支付',
        'paid' => '已支付',
        'closed' => '已关闭',
    ],

    'errors' => [
        'insufficient_available' => ':type可用余额不足',
        'insufficient_frozen' => ':type冻结金额不足',
    ],

    'transactions' => [
        'consume_description' => '支付扣款 :pay_sn',
        'refund_description' => '退款回款 :refund_sn',
    ],

    'recharges' => [
        'credit_description' => '钱包充值入账',
    ],

    'wallets' => [
        'wallet_resource' => [
            'model_label' => '钱包',
            'plural_model_label' => '钱包',
            'navigation_label' => '钱包',
        ],
        'wallet_table' => [
            'owner' => '所有者',
            'wallet_type' => '钱包类型',
            'balance' => '可用余额',
            'frozen' => '冻结金额',
            'team' => '团队',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'search_placeholder' => '搜索钱包...',
        ],
        'transactions_relation' => [
            'title' => '钱包流水',
            'uuid' => '幂等键',
            'type' => '类型',
            'amount' => '变动量',
            'balance_change' => '余额变动',
            'frozen_change' => '冻结变动',
            'balance_after' => '变动后余额',
            'frozen_after' => '变动后冻结',
            'description' => '描述',
            'created_at' => '时间',
        ],
        'actions' => [
            'credit' => '增加余额',
            'debit' => '扣除余额',
            'freeze' => '冻结',
            'unfreeze' => '解冻',
            'amount' => '金额',
            'description' => '备注',
            'description_placeholder' => '请填写调整原因',
            'credit_success' => '余额已增加。',
            'debit_success' => '余额已扣除。',
            'freeze_success' => '金额已冻结。',
            'unfreeze_success' => '金额已解冻。',
        ],
    ],

    'wallet_types' => [
        'wallet_type_resource' => [
            'model_label' => '钱包类型',
            'plural_model_label' => '钱包类型',
            'navigation_label' => '钱包类型',
        ],
        'wallet_type_table' => [
            'code' => '类型标识',
            'name' => '名称',
            'currency_code' => '币种代码',
            'decimals' => '小数位',
            'anchor_rate' => '锚定率',
            'enabled' => '启用',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'search_placeholder' => '搜索钱包类型...',
        ],
        'rates_relation' => [
            'title' => '锚定率',
            'team_id' => '团队（空=全局默认）',
            'anchor_rate' => '锚定率',
            'anchor_currency' => '锚定币种',
            'enabled' => '启用',
        ],
    ],

    'market_rates' => [
        'market_rate_resource' => [
            'model_label' => '市场汇率',
            'plural_model_label' => '市场汇率',
            'navigation_label' => '市场汇率',
        ],
        'market_rate_table' => [
            'source_currency' => '源币种',
            'target_currency' => '目标币种',
            'rate' => '汇率',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'search_placeholder' => '搜索市场汇率...',
        ],
    ],

    'recharges' => [
        'recharge_resource' => [
            'model_label' => '充值单',
            'plural_model_label' => '充值单',
            'navigation_label' => '充值单',
        ],
        'recharge_table' => [
            'owner' => '受益人',
            'wallet_type' => '钱包类型',
            'wallet_amount' => '到账金额',
            'pay_fee' => '应付金额',
            'status' => '状态',
            'paid_at' => '支付时间',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'search_placeholder' => '搜索充值单...',
        ],
    ],

];
