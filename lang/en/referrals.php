<?php

return [
    'status' => [
        'sent'             => 'Sent',
        'in_progress'      => 'In progress',
        'completed'        => 'Value declared (client to confirm)',
        'client_confirmed' => 'Client confirmed (to validate)',
        'confirmed'        => 'Confirmed',
        'cancelled'        => 'Cancelled',
        'rejected'         => 'Value rejected',
    ],

    'tabs' => [
        'received'    => 'Received (I am the professional)',
        'sent'        => 'Sent (I referred)',
        'client'      => 'I was referred',
        'archive'     => 'Archive',
        'leaderboard' => 'Leaderboard',
        'moderation'  => 'Moderation',
    ],

    'roles' => [
        'sender'       => 'Referrer',
        'professional' => 'Professional',
        'client'       => 'Referred client',
    ],

    'form' => [
        'title'             => 'New referral',
        'intro'             => 'Connect a client who needs a service with a professional in the Kommunity.',
        'professional'      => 'Professional (who provides the service)',
        'professional_ph'   => 'Select professional',
        'professional_help' => 'Only members you have a completed one-to-one with.',
        'client'            => 'Client (who needs the service)',
        'client_ph'         => 'Select client',
        'client_help'       => 'The member you are referring (e.g. your friend).',
        'submit'            => 'Send referral',
    ],

    'actions' => [
        'view'                 => 'View referral',
        'acknowledge'          => 'Take charge',
        'declare_value'        => 'Declare consulting value',
        'declare_submit'       => 'Declare consulting completed',
        'client_confirm'       => 'Confirm service received',
        'client_confirm_help'  => 'Confirm you received the consulting: the referral moves to admin validation.',
        'approve'              => 'Approve value',
        'reject'               => 'Reject',
        'cancel'               => 'Cancel referral',
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
        'declared'         => 'Value declared. The referred client now needs to confirm the service.',
        'client_confirmed' => 'Thank you! You confirmed the service. The referral is awaiting admin validation.',
        'confirmed'        => 'Value confirmed: it now counts towards the leaderboard and rewards.',
        'rejected'         => 'Value not approved.',
    ],
];
