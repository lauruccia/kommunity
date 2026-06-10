# Project Map — Kommunity

Mappa rapida delle aree del progetto per orientare le sessioni AI.

## Aree funzionali

| Area | Models | Controller | Viste |
|------|--------|-----------|-------|
| Auth | User | `Auth/` (Breeze) | `resources/views/auth/` |
| Capitoli (Pianeti) | Chapter, ChapterMember, ChapterRole, ChapterInvitation, ChapterJoinRequest, PlanetRole | ChapterInviteController | `resources/views/invito/` |
| Directory membri | MemberProfile, MemberOnepage, MemberGalleryImage | DirectoryController, MemberOnepageController, CardController | `resources/views/directory/`, `members/`, `card/` |
| One-to-One | OneToOneRequest, OneToOneNote, OneToOneFollowup, OneToOneReference | OneToOneController | `resources/views/one-to-ones/` |
| Forum | ForumCategory, ForumThread, ForumPost, ForumCategoryProposal | ForumController | `resources/views/forum/` |
| Eventi | Event, EventRegistration, EventInvitation | EventController | `resources/views/events/` |
| Messaggi | Conversation, Message | ConversationController | `resources/views/conversations/` |
| Referral | Referral | ReferralController | `resources/views/referrals/` |
| Abbonamenti | SubscriptionPlan, MemberSubscription | SubscriptionController | `resources/views/subscriptions/` |
| Banner advertising | BannerCampaign, BannerCreative, BannerPlacement, BannerClick, BannerImpression, Advertiser | BannerClickController, Admin/BannerReportController | — |
| Push notifications | PushSubscription | PushSubscriptionController | — |
| Notifiche | — | NotificationController | `resources/views/notifications/` |
| Profilo | MemberProfile, ProfileSuggestion, ProfileVideoAccessRequest | ProfileController, ProfileVideoAccessController | `resources/views/profile/` |
| Onboarding | User | OnboardingController | `resources/views/onboarding/` |
| Dashboard | — | DashboardController | `resources/views/dashboard.blade.php` |
| Pagine CMS | Page | PageController | `resources/views/page.blade.php` |
| Admin | tutti | `app/Filament/Resources/` (30 risorse) | `/admin` (Filament) |
| Feature flags | FeatureFlag | — | — |
| Geo | City, Province, Region | — | — |
| Tassonomie | Sector, Category, Profession, CompanyInterestType | — | — |
| Impostazioni sito | SiteSetting | Admin/CacheController | — |

## Servizi (`app/Services/`)
- `BannerService` — gestione campagne banner
- `Features` — feature flag runtime
- `MemberAnalyticsService` — analytics profilo membro
- `ProfileAiRewriteService` — rewrite AI profilo (GPT)
- `ProfileCompletionService` — calcolo completamento profilo
- `WebPush/` — notifiche push browser

## Enums (`app/Enums/`)
`ContactMethod`, `EventAttendanceStatus`, `EventType`, `MemberProfileStatus`, `OneToOneStatus`, `OnepageVisibility`, `PaymentMethod`, `ReferralStatus`, `SubscriptionPlanType`, `SubscriptionStatus`

## Policies (`app/Policies/`)
`ConversationPolicy`, `EventPolicy`, `MemberOnepagePolicy`, `OneToOnePolicy`, `ReferralPolicy`

## Observers (`app/Observers/`)
`UserObserver` — side-effect automatici su User

## i18n
- `lang/it/` e `lang/en/`: `auth.php`, `directory.php`, `nav.php`, `profile.php`, `push.php`, `subscription.php`, `validation.php`

## Asset pubblici
- `public/css/kommunity.css` — design system (.km-*)
- `public/js/` — JS custom
- `public/images/`, `public/fonts/` — asset statici
- `public/build/` — output Vite (gitignato)
- `public/sw.js` — service worker PWA/push
- `public/manifest.json` — PWA manifest
- `public/brand/` — asset brand

## Rotte pubbliche (senza auth)
- `/` — homepage
- `/member/{slug}` — onepage pubblica membro
- `/card/{slug}` — biglietto da visita digitale
- `/invita/{token}` — invito pianeta
- `/pagina/{slug}` — pagine CMS
- `/lingua/{locale}` — cambio lingua
