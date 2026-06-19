<?php

return [
    /*
    |----------------------------------------------------------------------
    | Push Notification — Italiano
    |----------------------------------------------------------------------
    | Usato da toWebPush() nelle Notification e nel banner consenso.
    */

    // Banner consenso
    'banner_title'    => '🔔 Notifiche immediate',
    'banner_body'     => 'Vuoi essere avvisata subito di nuove richieste 1:1, messaggi e referral? Puoi disattivarle quando vuoi dal tuo profilo.',
    'banner_enable'   => 'Attiva',
    'banner_later'    => 'Più tardi',
    'banner_never'    => 'Mai',
    'banner_aria'     => 'Attiva notifiche push',
    'banner_enabling' => 'Attivazione in corso…',
    'banner_enabled'  => '✓ Notifiche attivate.',
    'banner_failed'   => 'Non è stato possibile attivare',

    // OneToOneReceived
    'one_to_one_received_title' => '🤝 Nuova richiesta 1:1',
    'one_to_one_received_body'  => 'Da :name — :goal',

    // OneToOneReminder
    'one_to_one_reminder_1h_title'  => '⏰ 1:1 fra un\'ora',
    'one_to_one_reminder_24h_title' => '📅 1:1 domani',
    'one_to_one_reminder_body'      => 'Con :name:when',
    'one_to_one_reminder_at'        => ' alle :time',

    // ReferralReceived
    'referral_received_title' => '🔗 Nuovo referral da :name',

    // SubscriptionApproved
    'subscription_approved_title' => '🎉 Abbonamento approvato',
    'subscription_approved_body'  => 'Il tuo abbonamento :plan è stato approvato.',

    // EventReminder
    'event_reminder_title' => '📅 Domani: :title',

    // NewMemberConciergeAlert
    'new_member_concierge_title' => '🟢 Nuovo utente Kommunity',
    'new_member_concierge_body'  => 'Concierge entro 24h: :name',
];
