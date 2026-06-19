<?php

return [
    'status' => [
        'sent'        => 'Sent',
        'in_progress' => 'In progress',
        'completed'   => 'Completed (to validate)',
        'confirmed'   => 'Confirmed',
        'cancelled'   => 'Cancelled',
        'rejected'    => 'Value rejected',
    ],

    'tabs' => [
        'received'    => 'Received',
        'sent'        => 'Sent',
        'archive'     => 'Archive',
        'leaderboard' => 'Leaderboard',
        'moderation'  => 'Moderation',
    ],

    'actions' => [
        'view'           => 'View referral',
        'acknowledge'    => 'Take charge',
        'declare_value'  => 'Declare consulting value',
        'declare_submit' => 'Declare consulting completed',
        'approve'        => 'Approve value',
        'reject'         => 'Reject',
        'cancel'         => 'Cancel referral',
    ],

    'value' => [
        'estimated'      => 'Estimated value',
        'declared'       => 'Declared value',
        'approved'       => 'Confirmed value',
        'amount_label'   => 'Consulting value (€)',
        'amount_help'    => 'Enter the value of the consulting work delivered thanks to this referral.',
        'pending'        => 'Awaiting validation',
    ],

    'leaderboard' => [
        'title'         => 'Value generators leaderboard',
        'subtitle'      => 'Who brings the most value to the Kommunity through referrals.',
        'rank'          => '#',
        'member'        => 'Member',
        'referrals'     => 'Confirmed consultings',
        'value'         => 'Value generated',
        'points'        => 'Points',
        'you'           => 'You',
        'empty'         => 'No confirmed referrals yet. Validated consultings will appear here.',
        'my_points'     => 'Your points',
        'my_value'      => 'Value you generated',
        'how_title'     => 'How points are calculated',
        'how_body'      => '50 points for each confirmed consulting + 1 point for every €10 of admin-validated value.',
    ],

    'flash' => [
        'declared'  => 'Value declared. The referral is awaiting validation by an admin.',
        'confirmed' => 'Value confirmed: it now counts towards the leaderboard and rewards.',
        'rejected'  => 'Value not approved.',
    ],
];
