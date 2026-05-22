<?php

namespace App\Support;

use App\Models\SiteSetting;

class MemberNavigation
{
    public const SETTING_KEY = 'member_navigation_visible_items';

    /**
     * @return array<string, array{label: string, route: string, active: string, requires_subscription_plans?: bool}>
     */
    public static function items(): array
    {
        return [
            'dashboard' => [
                'label' => 'nav.dashboard',
                'route' => 'dashboard',
                'active' => 'dashboard',
            ],
            'directory' => [
                'label' => 'nav.directory',
                'route' => 'directory.index',
                'active' => 'directory.*',
            ],
            'one_to_one' => [
                'label' => 'nav.one_to_one',
                'route' => 'one-to-ones.index',
                'active' => 'one-to-ones.*',
            ],
            'events' => [
                'label' => 'nav.events',
                'route' => 'events.index',
                'active' => 'events.*',
            ],
            // 'forum' => [
            //     'label' => 'nav.forum',
            //     'route' => 'forum.index',
            //     'active' => 'forum.*',
            // ],
            'messages' => [
                'label' => 'nav.messages',
                'route' => 'conversations.index',
                'active' => 'conversations.*',
            ],
            'referrals' => [
                'label' => 'nav.referrals',
                'route' => 'referrals.index',
                'active' => 'referrals.*',
            ],
            'faq' => [
                'label' => 'nav.faq',
                'route' => 'faq',
                'active' => 'faq',
            ],
            'subscription' => [
                'label' => 'nav.subscription',
                'route' => 'subscriptions.index',
                'active' => 'subscriptions.*',
                'requires_subscription_plans' => true,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::items())
            ->mapWithKeys(fn (array $item, string $key): array => [$key => __($item['label'])])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function enabledKeys(): array
    {
        $default = array_keys(self::items());
        $stored = SiteSetting::getCached(self::SETTING_KEY);

        if ($stored === null || $stored === '') {
            return $default;
        }

        $decoded = json_decode($stored, true);

        if (! is_array($decoded)) {
            return $default;
        }

        return array_values(array_intersect($default, $decoded));
    }

    /**
     * @return array<string, array{label: string, route: string, active: string, requires_subscription_plans?: bool}>
     */
    public static function visibleItems(bool $hasActiveSubscriptionPlans = true): array
    {
        $enabledKeys = self::enabledKeys();

        $items = collect(self::items())
            ->only($enabledKeys)
            ->reject(fn (array $item): bool => ($item['requires_subscription_plans'] ?? false) && ! $hasActiveSubscriptionPlans)
            ->all();

        if (! array_key_exists('faq', $items)) {
            $items['faq'] = self::items()['faq'];
        }

        return $items;
    }
}
