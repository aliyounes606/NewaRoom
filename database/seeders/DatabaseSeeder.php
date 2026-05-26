<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Profile;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed role-based users
        $this->call(RoleSeeder::class);

        // 2. Create profiles for all users
        User::all()->each(function (User $user) {
            Profile::factory()->create(['user_id' => $user->id]);
        });

        // 3. Create tags
        $tags = Tag::factory()->count(10)->create();

        // 4. Get writer users to author articles
        $writers = User::where('role', 'writer')->get();

        // 5. Create published articles for writers
        $writers->each(function (User $writer) use ($tags) {
            // Each writer gets 5-8 published articles
            $publishedArticles = Article::factory()
                ->published()
                ->count(fake()->numberBetween(5, 8))
                ->create(['user_id' => $writer->id]);

            // Attach random tags to each article
            $publishedArticles->each(function (Article $article) use ($tags) {
                $article->tags()->attach(
                    $tags->random(fake()->numberBetween(1, 4))->pluck('id')
                );
            });

            // Each writer also gets 2-3 draft articles
            Article::factory()
                ->draft()
                ->count(fake()->numberBetween(2, 3))
                ->create(['user_id' => $writer->id]);
        });

        // 6. Create comments on published articles
        $publishedArticles = Article::published()->get();
        $allUsers = User::all();

        $publishedArticles->each(function (Article $article) use ($allUsers) {
            Comment::factory()
                ->count(fake()->numberBetween(2, 6))
                ->create([
                    'commentable_type' => Article::class,
                    'commentable_id' => $article->id,
                    'user_id' => $allUsers->random()->id,
                ]);
        });

        // 7. Create some attachments on articles
        $publishedArticles->random(min(5, $publishedArticles->count()))->each(function (Article $article) {
            Attachment::factory()
                ->count(fake()->numberBetween(1, 3))
                ->create([
                    'attachable_type' => Article::class,
                    'attachable_id' => $article->id,
                ]);
        });

        // 8. Create some attachments on profiles
        Profile::all()->random(min(3, Profile::count()))->each(function (Profile $profile) {
            Attachment::factory()
                ->count(fake()->numberBetween(1, 2))
                ->create([
                    'attachable_type' => Profile::class,
                    'attachable_id' => $profile->id,
                ]);
        });
    }
}
