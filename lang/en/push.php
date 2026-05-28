<?php

return [
    /*
    |----------------------------------------------------------------------
    | Push Notification — English
    |----------------------------------------------------------------------
    | Used by toWebPush() in Notifications and the consent banner.
    */

    // Consent banner
    'banner_title'    => '🔔 Instant notifications',
    'banner_body'     => 'Want to be notified right away about new 1:1 requests, messages and referrals? You can turn them off at any time from your profile.',
    'banner_enable'   => 'Enable',
    'banner_later'    => 'Later',
    'banner_never'    => 'Never',
    'banner_aria'     => 'Enable push notifications',
    'banner_enabling' => 'Enabling…',
    'banner_enabled'  => '✓ Notifications enabled.',
    'banner_failed'   => 'Could not enable notifications',

    // OneToOneReceived
    'one_to_one_received_title' => '🤝 New 1:1 request',
    'one_to_one_received_body'  => 'From :name — :goal',

    // OneToOneReminder
    'one_to_one_reminder_1h_title'  => '⏰ 1:1 in one hour',
    'one_to_one_reminder_24h_title' => '📅 1:1 tomorrow',
    'one_to_one_reminder_body'      => 'With :name:when',
    'one_to_one_reminder_at'        => ' at :time',

    // ReferralReceived
    'referral_received_title' => '🔗 New referral from :name',

    // SubscriptionApproved
    'subscription_approved_title' => '🎉 Subscription approved',
    'subscription_approved_body'  => 'Your :plan subscription has been approved.',

    // EventReminder
    'event_reminder_title' => '📅 Tomorrow: :title',

    // NewMemberConciergeAlert
    'new_member_concierge_title' => '🟢 New Kommunity member',
    'new_member_concierge_body'  => 'Concierge within 24h: :name',
];
