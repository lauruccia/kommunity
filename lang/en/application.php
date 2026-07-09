<?php

return [
    /*
    |----------------------------------------------------------------------
    | Membership applications — English
    |----------------------------------------------------------------------
    | "Apply" section on the homepage, public form messages
    | and applicant emails (received / approved / rejected).
    */

    // ── Homepage section ──────────────────────────────────────────────────
    'home_badge'      => 'Members by selection',
    'home_title_1'    => 'We don\'t collect sign-ups.',
    'home_title_2'    => 'We build relationships that matter.',
    'home_text'       => 'Kommunity is a closed-number community: professionals and companies selected one by one, to guarantee high-quality relationships and concrete opportunities. Every application is reviewed personally.',
    'home_point_1'    => 'Verified profiles, no random contacts',
    'home_point_2'    => 'Local Planets with limited seats per profession',
    'home_point_3'    => 'Referrals and collaborations among selected members',
    'home_cta'        => 'Submit your application',
    'home_nav'        => 'Apply',

    // ── Form ──────────────────────────────────────────────────────────────
    'form_title'          => 'Membership application',
    'form_subtitle'       => 'Fill in the fields: our team will review your profile and you will receive an answer by email.',
    'form_name'           => 'Full name',
    'form_email'          => 'Email',
    'form_phone'          => 'Phone',
    'form_type'           => 'You are applying as',
    'form_type_private'   => 'Individual / Professional',
    'form_type_company'   => 'Company',
    'form_vat'            => 'VAT number',
    'form_vat_hint'       => 'Required for companies, optional for individuals',
    'form_profession'     => 'Profession / business',
    'form_profession_ph'  => 'E.g. Accountant, communication agency…',
    'form_referrer'       => 'How did you hear about Kommunity?',
    'form_referrer_hint'  => 'If a member introduced you, name them: introduced applications get priority.',
    'form_referrer_ph'    => 'Full name, or "online search", an event…',
    'form_submit'         => 'Submit application',
    'form_privacy'        => 'By submitting you consent to the processing of your data for the sole purpose of reviewing this application.',

    // ── Outcomes ──────────────────────────────────────────────────────────
    'success_title' => 'Application received',
    'success_text'  => 'Thank you: your profile is now under review. We will get back to you by email as soon as possible.',

    'error_already_member'  => 'This email is already registered on Kommunity. If you forgot your password, use "Forgot password" on the login page.',
    'error_already_pending' => 'We have already received an application with this email: it is under review, we will get back to you shortly.',

    // ── Validation ────────────────────────────────────────────────────────
    'v_name_required'       => 'Please enter your full name.',
    'v_name_full'           => 'Please enter both first and last name.',
    'v_email_required'      => 'Please enter your email.',
    'v_email_valid'         => 'Please enter a valid email address.',
    'v_phone_required'      => 'Please enter your phone number.',
    'v_phone_valid'         => 'Please enter a valid phone number.',
    'v_type_required'       => 'Please tell us whether you are applying as an individual or a company.',
    'v_vat_required'        => 'The VAT number is required for companies.',
    'v_profession_required' => 'Please tell us your profession or business.',

    // ── Email: application received ───────────────────────────────────────
    'mail_received_subject'  => 'Your Kommunity application is under review',
    'mail_received_title'    => 'Application received',
    'mail_received_greeting' => 'Hi :name,',
    'mail_received_line1'    => 'thank you for applying to Kommunity, the closed-number community where selected professionals and companies build valuable relationships.',
    'mail_received_line2'    => 'Our team is reviewing your profile. Access is by selection: you will receive an answer by email as soon as possible.',
    'mail_received_presenter'=> 'Introduced by: :name',
    'mail_received_footer'   => 'You received this email because a Kommunity application was submitted with this address. If it wasn\'t you, please ignore this email.',

    // ── Email: application approved ───────────────────────────────────────
    'mail_approved_subject'  => 'Welcome to Kommunity — your application has been approved',
    'mail_approved_title'    => 'You\'re in.',
    'mail_approved_greeting' => 'Hi :name,',
    'mail_approved_line1'    => 'we are pleased to let you know that your application has been approved: as of today you are part of Kommunity.',
    'mail_approved_planet'   => 'You have been admitted to the :planet Planet.',
    'mail_approved_line2'    => 'To activate your account, set your personal password now:',
    'mail_approved_button'   => 'Set your password and enter',
    'mail_approved_expiry'   => 'For security reasons the link expires after :minutes minutes. If it has expired, go to ":forgot" on the login page and enter this email to receive a new one.',
    'mail_approved_forgot'   => 'Forgot password',
    'mail_approved_line3'    => 'Once inside, complete your profile: it is your business card towards the other members.',

    // ── Email: application not accepted ───────────────────────────────────
    'mail_rejected_subject'  => 'Your Kommunity application',
    'mail_rejected_title'    => 'Thank you for applying',
    'mail_rejected_greeting' => 'Hi :name,',
    'mail_rejected_line1'    => 'thank you for your interest in Kommunity. After careful consideration, we are unable to accept your application at this time.',
    'mail_rejected_reason'   => 'Note from the team: :reason',
    'mail_rejected_line2'    => 'Kommunity grows by selection and seats in each Planet are limited: you are welcome to apply again in the future, ideally introduced by a member.',
];
