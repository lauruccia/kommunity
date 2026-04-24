<?php

namespace Database\Seeders;

use App\Enums\ContactMethod;
use App\Enums\EventType;
use App\Enums\MemberProfileStatus;
use App\Enums\ReferralStatus;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\City;
use App\Models\Conversation;
use App\Models\Event;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\Message;
use App\Models\OneToOneRequest;
use App\Models\Profession;
use App\Models\Referral;
use App\Models\Region;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            'gestire-utenti',
            'assegnare-ruoli',
            'assegnare-permessi',
            'gestire-eventi',
            'gestire-capitoli',
            'moderare-forum',
        ])->mapWithKeys(fn (string $permission) => [$permission => Permission::findOrCreate($permission)]);

        $roles = collect([
            'super-admin',
            'admin-community',
            'leader-capitolo',
            'moderatore',
            'membro',
            'visitor',
        ])->mapWithKeys(fn (string $role) => [$role => Role::findOrCreate($role)]);

        $roles['super-admin']->syncPermissions($permissions->values());
        $roles['admin-community']->syncPermissions([
            $permissions['gestire-utenti'],
            $permissions['assegnare-ruoli'],
            $permissions['assegnare-permessi'],
            $permissions['gestire-eventi'],
            $permissions['gestire-capitoli'],
            $permissions['moderare-forum'],
        ]);
        $roles['leader-capitolo']->syncPermissions([
            $permissions['gestire-eventi'],
            $permissions['gestire-capitoli'],
        ]);
        $roles['moderatore']->syncPermissions([
            $permissions['moderare-forum'],
        ]);

        $categories = collect([
            ['name' => 'Marketing e crescita'],
            ['name' => 'Consulenza business'],
            ['name' => 'Tecnologia e digitale'],
            ['name' => 'Formazione e coaching'],
        ])->map(fn (array $item) => Category::query()->create([
            'name' => $item['name'],
            'slug' => Str::slug($item['name']),
            'description' => 'Categoria professionale Kommunity',
            'is_active' => true,
        ]));

        $professions = collect([
            'Business strategist',
            'Consulente marketing',
            'Sviluppatore software',
            'Coach aziendale',
        ])->map(fn (string $name) => Profession::query()->create([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Professione disponibile in directory',
            'is_active' => true,
        ]));

        $sectors = collect([
            'Servizi professionali',
            'Digitale',
            'Formazione',
            'Network marketing',
        ])->map(fn (string $name) => Sector::query()->create([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Settore economico',
            'is_active' => true,
        ]));

        $regions = collect([
            ['name' => 'Lazio', 'code' => 'LAZ'],
            ['name' => 'Lombardia', 'code' => 'LOM'],
            ['name' => 'Emilia-Romagna', 'code' => 'EMR'],
        ])->map(fn (array $item) => Region::query()->create([
            'name' => $item['name'],
            'slug' => Str::slug($item['name']),
            'code' => $item['code'],
        ]));

        $cities = collect([
            ['name' => 'Roma', 'province' => 'RM', 'region' => 'Lazio'],
            ['name' => 'Milano', 'province' => 'MI', 'region' => 'Lombardia'],
            ['name' => 'Bologna', 'province' => 'BO', 'region' => 'Emilia-Romagna'],
        ])->map(function (array $item) use ($regions) {
            $region = $regions->firstWhere('name', $item['region']);

            return City::query()->create([
                'region_id' => $region?->id,
                'name' => $item['name'],
                'slug' => Str::slug($item['name']),
                'province' => $item['province'],
            ]);
        });

        $admin = User::query()->create([
            'name' => 'Kommunity Admin',
            'email' => 'admin@kommunity.test',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole($roles['super-admin']);
        $admin->memberProfile()->update([
            'company_name' => 'Kommunity HQ',
            'profession_id' => $professions[0]->id,
            'category_id' => $categories[1]->id,
            'sector_id' => $sectors[0]->id,
            'city_id' => $cities[0]->id,
            'region_id' => $regions[0]->id,
            'bio' => 'Coordino lo sviluppo della piattaforma e la crescita della community.',
            'short_bio' => 'Super admin e community architect.',
            'services' => 'Strategia community, partnership, sviluppo ecosistema.',
            'networking_goals' => 'Costruire la community professionale di riferimento in Italia.',
            'phone' => '+39 06 5555000',
            'whatsapp_number' => '+39 351 5555000',
            'show_phone' => true,
            'status' => MemberProfileStatus::Active,
            'is_active' => true,
            'onboarding_completed' => true,
            'preferred_contact_method' => ContactMethod::Email,
        ]);
        $admin->memberOnepage()->update([
            'hero_subtitle' => 'Community architect · Roma',
            'about_text' => 'Guido il progetto Kommunity e la costruzione della business community.',
            'services_text' => 'Visione di piattaforma, onboarding partner, sviluppo capitoli territoriali.',
        ]);

        $leader = User::query()->create([
            'name' => 'Giulia Bianchi',
            'email' => 'giulia@kommunity.test',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);
        $leader->assignRole($roles['leader-capitolo']);

        $chapter = Chapter::query()->create([
            'name' => 'Kapitolo Roma Centro',
            'slug' => 'kapitolo-roma-centro',
            'description' => 'Capitolo territoriale dedicato a professionisti, networker e imprenditori dell’area romana.',
            'city_id' => $cities[0]->id,
            'leader_id' => $leader->id,
            'is_active' => true,
        ]);

        $profiles = [
            [
                'user' => $leader,
                'role' => 'leader-capitolo',
                'company_name' => 'Studio Bianchi Growth',
                'profession_id' => $professions[1]->id,
                'category_id' => $categories[0]->id,
                'sector_id' => $sectors[1]->id,
                'city_id' => $cities[0]->id,
                'region_id' => $regions[0]->id,
                'chapter_id' => $chapter->id,
                'short_bio' => 'Aiuto imprenditori e professionisti a generare relazioni commerciali di valore.',
                'bio' => 'Consulente marketing e leader capitolo con focus su networking strutturato e referral.',
                'services' => 'Strategia commerciale, funnel, posizionamento, business networking.',
                'networking_goals' => 'Creare referral qualificati e partnership continuative.',
                'phone' => '+39 348 1002000',
                'whatsapp_number' => '+39 348 1002000',
            ],
            [
                'user' => User::query()->create([
                    'name' => 'Marco De Santis',
                    'email' => 'marco@kommunity.test',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]),
                'role' => 'membro',
                'company_name' => 'De Santis Advisory',
                'profession_id' => $professions[0]->id,
                'category_id' => $categories[1]->id,
                'sector_id' => $sectors[0]->id,
                'city_id' => $cities[1]->id,
                'region_id' => $regions[1]->id,
                'chapter_id' => null,
                'short_bio' => 'Business strategist per PMI e founder in fase di scale-up.',
                'bio' => 'Affianco aziende nella definizione di modelli di crescita, partnership e processi commerciali.',
                'services' => 'Advisory strategico, modelli di business, crescita commerciale.',
                'networking_goals' => 'Aprire collaborazioni inter-regionali e progetti B2B.',
                'phone' => '+39 347 2223334',
                'whatsapp_number' => '+39 347 2223334',
            ],
            [
                'user' => User::query()->create([
                    'name' => 'Sara Conti',
                    'email' => 'sara@kommunity.test',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]),
                'role' => 'membro',
                'company_name' => 'Conti Digital Lab',
                'profession_id' => $professions[2]->id,
                'category_id' => $categories[2]->id,
                'sector_id' => $sectors[1]->id,
                'city_id' => $cities[1]->id,
                'region_id' => $regions[1]->id,
                'chapter_id' => null,
                'short_bio' => 'Creo prodotti digitali e piattaforme per community e membership business.',
                'bio' => 'Sviluppatrice software specializzata in Laravel, piattaforme SaaS e automazioni di processo.',
                'services' => 'Sviluppo Laravel, prodotto digitale, integrazioni e ottimizzazione workflow.',
                'networking_goals' => 'Collaborare con consulenti e professionisti che vogliono scalare servizi digitali.',
                'phone' => '+39 349 5556677',
                'whatsapp_number' => '+39 349 5556677',
            ],
            [
                'user' => User::query()->create([
                    'name' => 'Elena Ferri',
                    'email' => 'elena@kommunity.test',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]),
                'role' => 'moderatore',
                'company_name' => 'Ferri Coaching',
                'profession_id' => $professions[3]->id,
                'category_id' => $categories[3]->id,
                'sector_id' => $sectors[2]->id,
                'city_id' => $cities[2]->id,
                'region_id' => $regions[2]->id,
                'chapter_id' => null,
                'short_bio' => 'Supporto professionisti e team nel trasformare relazioni in risultati.',
                'bio' => 'Coach aziendale con esperienza in leadership, public speaking e facilitazione di community.',
                'services' => 'Coaching, workshop, facilitazione eventi, mentoring.',
                'networking_goals' => 'Connettermi con organizzatori di eventi e leader di capitolo.',
                'phone' => '+39 340 8899001',
                'whatsapp_number' => '+39 340 8899001',
            ],
        ];

        collect($profiles)->each(function (array $data) use ($roles): void {
            $user = $data['user'];
            $user->assignRole($roles[$data['role']]);
            $user->memberProfile()->update([
                'company_name' => $data['company_name'],
                'profession_id' => $data['profession_id'],
                'category_id' => $data['category_id'],
                'sector_id' => $data['sector_id'],
                'city_id' => $data['city_id'],
                'region_id' => $data['region_id'],
                'chapter_id' => $data['chapter_id'],
                'bio' => $data['bio'],
                'short_bio' => $data['short_bio'],
                'services' => $data['services'],
                'networking_goals' => $data['networking_goals'],
                'phone' => $data['phone'],
                'whatsapp_number' => $data['whatsapp_number'],
                'show_phone' => true,
                'show_whatsapp' => true,
                'show_email' => true,
                'allow_whatsapp_contact' => true,
                'preferred_contact_method' => ContactMethod::Whatsapp,
                'status' => MemberProfileStatus::Active,
                'is_active' => true,
                'onboarding_completed' => true,
            ]);
            $user->memberOnepage()->update([
                'hero_subtitle' => trim(($user->memberProfile->profession?->name ?? 'Professionista').' · '.($user->memberProfile->city?->name ?? 'Italia')),
                'about_text' => $data['bio'],
                'services_text' => $data['services'],
            ]);
        });

        $chapter->members()->syncWithoutDetaching([
            $leader->id => ['status' => 'active', 'joined_at' => now()],
            $admin->id => ['status' => 'active', 'joined_at' => now()],
        ]);

        $event = Event::query()->create([
            'chapter_id' => $chapter->id,
            'organizer_id' => $leader->id,
            'title' => 'Business Matching Roma',
            'slug' => 'business-matching-roma',
            'description' => 'Evento networking serale con pitch rapidi, tavoli tematici e sessioni one-to-one.',
            'type' => EventType::Networking,
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(3),
            'location' => 'Roma, Eur',
            'capacity' => 80,
            'status' => 'published',
            'is_published' => true,
        ]);

        $event->attendees()->syncWithoutDetaching([
            $admin->id => ['status' => 'registered', 'registered_at' => now()],
            $leader->id => ['status' => 'registered', 'registered_at' => now()],
        ]);

        OneToOneRequest::query()->create([
            'requester_id' => $leader->id,
            'recipient_id' => $admin->id,
            'requested_at' => now()->addDays(3),
            'meeting_mode' => 'online',
            'meeting_link' => 'https://meet.google.com/kommunity-demo',
            'goal' => 'Valutare partnership per il lancio del capitolo Roma Centro.',
            'pre_notes' => 'Condividere obiettivi, referenze attese e calendario eventi.',
            'status' => 'pending',
        ]);

        $forumCategory = ForumCategory::query()->create([
            'name' => 'Collaborazioni e opportunita\'',
            'slug' => 'collaborazioni-opportunita',
            'description' => 'Spazio dedicato a richieste, partnership e opportunita\' business.',
            'is_active' => true,
        ]);

        $thread = ForumThread::query()->create([
            'forum_category_id' => $forumCategory->id,
            'user_id' => $leader->id,
            'title' => 'Come organizzare sessioni di business matching efficaci',
            'slug' => 'come-organizzare-sessioni-di-business-matching-efficaci',
            'excerpt' => 'Raccolta di idee pratiche per strutturare incontri produttivi tra membri.',
            'is_pinned' => true,
        ]);

        ForumPost::query()->create([
            'forum_thread_id' => $thread->id,
            'user_id' => $leader->id,
            'content' => 'Sto impostando il format del prossimo evento del capitolo. Mi interessa capire quali regole usate per rendere davvero utili le sessioni di matching tra membri.',
        ]);

        $conversation = Conversation::query()->create([
            'subject' => 'Allineamento partnership capitolo',
        ]);
        $conversation->participants()->sync([
            $leader->id => ['last_read_at' => now()],
            $admin->id => ['last_read_at' => now()],
        ]);
        Message::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $leader->id,
            'body' => 'Ciao, ho preparato una bozza del calendario capitolo. Possiamo rivederla insieme?',
        ]);
        Message::query()->create([
            'conversation_id' => $conversation->id,
            'user_id' => $admin->id,
            'body' => 'Perfetto. Portiamo anche una proposta per le referenze e per il primo evento di matching.',
        ]);

        Referral::query()->create([
            'sender_id' => $leader->id,
            'recipient_id' => $admin->id,
            'title' => 'Introduzione a partner formazione corporate',
            'description' => 'Opportunita\' per workshop su vendita consulenziale e networking strategico.',
            'company_name' => 'Crescita Group',
            'contact_name' => 'Luca Moretti',
            'estimated_value' => 4500,
            'priority' => 'high',
            'status' => ReferralStatus::Sent,
        ]);
    }
}
