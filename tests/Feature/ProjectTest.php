<?php

namespace Tests\Feature;

use App\Mail\ProjectNotificationMail;
use App\Models\Badge;
use App\Models\Category;
use App\Models\File;
use App\Models\Project;
use App\Models\User;
use App\Models\Version;
use App\Models\Vote;
use App\Models\Warning;
use Faker\Factory;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations, WithFaker;

    /**
     * Unit test setup use Mail faker.
     */
    public function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /**
     * Check the projects list.
     */
    public function testProjectsIndex()
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/projects');
        $response->assertStatus(200)
            ->assertViewHas('projects', Project::paginate());
    }

    /**
     * Check the projects creation page exists.
     */
    public function testProjectsCreate()
    {
        $user = factory(User::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/projects/create');
        $response->assertStatus(200);
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsStore()
    {
        $user = factory(User::class)->create();
        $faker = Factory::create();
        $this->assertEmpty(Project::all());
        $category = factory(Category::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('post', '/projects', ['name' => $faker->name, 'description' => $faker->paragraph, 'category_id' => $category->id, 'status' => 'unknown']);
        $this->assertNotNull(Project::get()->last());
        $response->assertRedirect('/projects/'.Project::get()->last()->slug.'/edit')->assertSessionHas('successes');
        $this->assertCount(1, Project::all());
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsStoreUnique()
    {
        $user = factory(User::class)->create();
        $faker = Factory::create();
        $this->assertEmpty(Project::all());
        $category = factory(Category::class)->create();
        $name = $faker->name;
        $response = $this
            ->actingAs($user)
            ->call('post', '/projects', ['name' => $name, 'description' => $faker->paragraph, 'category_id' => $category->id, 'status' => 'unknown']);
        $this->assertNotNull(Project::get()->last());
        $response->assertRedirect('/projects/'.Project::get()->last()->slug.'/edit')->assertSessionHas('successes');
        $this->assertCount(1, Project::all());
        $response = $this
            ->actingAs($user)
            ->call('post', '/projects', ['name' => $name, 'description' => $faker->paragraph, 'category_id' => $category->id]);
        $response->assertRedirect('')->assertSessionHas('errors');

        $name .= '+'; // issue found by fox name is unique, slug is identical
        $this->assertCount(1, Project::all());
        $response = $this
            ->actingAs($user)
            ->call('post', '/projects', ['name' => $name, 'description' => $faker->paragraph, 'category_id' => $category->id]);
        $response->assertRedirect('/projects/create')->assertSessionHas('errors');
    }

    /**
     * Check the projects edit page functions.
     */
    public function testProjectsEdit()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->get('/projects/'.$project->slug.'/edit');
        $response->assertStatus(200);
    }

    /**
     * Check the projects edit page functions for other users.
     */
    public function testProjectsEditOtherUser()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($otherUser)
            ->get('/projects/'.$project->slug.'/edit');
        $response->assertStatus(403);
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsUpdate()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $projectDep = factory(Project::class)->create();
        $projectDep->versions()->first()->zip = 'test';
        $projectDep->versions()->first()->save();
        $project = factory(Project::class)->create();
        $faker = Factory::create();
        $response = $this
            ->actingAs($user)
            ->call('put', '/projects/'.$project->slug, [
                'description'  => $faker->paragraph,
                'dependencies' => [$projectDep->id],
                'category_id'  => $project->category_id,
                'status'       => 'unknown',
            ]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
        // add deps
        $response = $this
            ->actingAs($user)
            ->call('put', '/projects/'.$project->slug, [
                'description' => $faker->paragraph,
                'category_id' => $project->category_id,
                'status'      => 'unknown',
            ]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
        // remove deps
    }

    /**
     * Check the projects can't be stored by other users.
     */
    public function testProjectsUpdateOtherUser()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $this->be($user);
        $projectDep = factory(Project::class)->create();
        $projectDep->versions()->first()->zip = 'test';
        $projectDep->versions()->first()->save();
        $project = factory(Project::class)->create();
        $faker = Factory::create();
        $response = $this
            ->actingAs($user)
            ->call('put', '/projects/'.$project->slug, [
                'description'  => $faker->paragraph,
                'dependencies' => [$projectDep->id],
                'category_id'  => $project->category_id,
                'status'       => 'unknown',
            ]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
        // add deps
        $response = $this
            ->actingAs($otherUser)
            ->call('put', '/projects/'.$project->slug, [
                'description' => $faker->paragraph,
                'category_id' => $project->category_id,
                'status'      => 'unknown',
            ]);
        $response->assertStatus(403);
        // remove deps
    }

    /**
     * Check the projects can be published.
     */
    public function testProjectsPublish()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $projectDep = factory(Project::class)->create();
        $projectDep->versions()->first()->zip = 'test';
        $projectDep->versions()->first()->save();
        $project = factory(Project::class)->create();
        $project->dependencies()->save($projectDep);
        $file = factory(File::class, ['version_id' => $project->versions()->unPublished()->first()->id])->create();
        $file->first()->version_id = $project->versions()->unPublished()->first()->id; // yah ugly
        $file->first()->save(); // wut?

        $response = $this
            ->actingAs($user)
            ->call('post', '/release/'.$project->slug);
        $response->assertRedirect('/projects/'.$project->slug.'/edit')->assertSessionHas('successes');

        $version = Version::published()->where('project_id', $project->id)->get()->last();

        $this->assertFileExists(public_path($version->zip));
        unlink(public_path($version->zip));
    }

    /**
     * Check the projects can be deleted.
     */
    public function testProjectsDestroy()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('delete', '/projects/'.$project->slug);
        $response->assertRedirect('/projects/')->assertSessionHas('successes');
    }

    /**
     * Check the projects can't be deleted by other users.
     */
    public function testProjectsDestroyOtherUser()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($otherUser)
            ->call('delete', '/projects/'.$project->slug);
        $response->assertStatus(403);
    }

    /**
     * Check the projects can be deleted by admin users.
     */
    public function testProjectsDestroyAdminUser()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create(['admin' => true]);
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($otherUser)
            ->call('delete', '/projects/'.$project->slug);
        $response->assertRedirect('/projects/')->assertSessionHas('successes');
    }

    /**
     * Check the projects can be viewed (publicly).
     */
    public function testProjectsView()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->call('get', '/projects/'.$project->slug);
        $response->assertStatus(200)->assertViewHas(['project']);
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsStoreBadge()
    {
        $user = factory(User::class)->create();
        $faker = Factory::create();
        $this->assertEmpty(Project::all());
        $category = factory(Category::class)->create();
        $badge = factory(Badge::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('post', '/projects', [
                'name'        => $faker->name,
                'description' => $faker->paragraph,
                'category_id' => $category->id,
                'badge_ids'   => [$badge->id],
                'status'      => 'unknown', ]);
        $this->assertNotNull(Project::get()->last());
        $response->assertRedirect('/projects/'.Project::get()->last()->slug.'/edit')->assertSessionHas('successes');
        $this->assertCount(1, Project::all());
    }

    /**
     * Check the projects can be stored.
     */
    public function testProjectsUpdateBadge()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $faker = Factory::create();
        $badge = factory(Badge::class)->create();
        $this->assertEquals('unknown', $project->status);
        $response = $this
            ->actingAs($user)
            ->call('put', '/projects/'.$project->slug, [
                'description'  => $faker->paragraph,
                'category_id'  => $project->category_id,
                'badge_ids'    => [$badge->id],
                'badge_status' => [$badge->id => 'working'],
            ]);
        $response->assertRedirect('/projects')->assertSessionHas('successes');
        $project = Project::find($project->id);
        $this->assertCount(1, $project->states);
        $this->assertEquals('working', $project->status);
    }

    /**
     * Check that badge.team can be notified of dangerous projects.
     */
    public function testProjectsNotify()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('post', '/notify/'.$project->slug, ['description' => 'het zuigt']);
        $response->assertRedirect('/projects/'.$project->slug)->assertSessionHas('successes');
        Mail::assertSent(ProjectNotificationMail::class, function (ProjectNotificationMail $mail) {
            Container::getInstance()->call([$mail, 'build']);

            return 'mails.projectNotify' === $mail->build()->textView;
        });
        $this->assertCount(1, Warning::all());
        $project = Project::find($project->id);
        $this->assertEquals('het zuigt', $project->warnings()->first()->description);
    }

    /**
     * Check that a User can Vote for a Project.
     */
    public function testProjectsVote()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('post', '/votes', ['project_id' => $project->id, 'type' => 'pig']);
        $response->assertRedirect('/projects/'.$project->slug)->assertSessionHas('successes');
        $this->assertCount(1, Vote::all());
        $project = Project::find($project->id);
        $this->assertEquals('pig', $project->votes()->first()->type);
    }

    /**
     * Check that a User can Vote for a Project only once.
     */
    public function testProjectsVoteOnce()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('post', '/votes', ['project_id' => $project->id, 'type' => 'pig']);
        $response->assertRedirect('/projects/'.$project->slug)->assertSessionHas('successes');
        $this->assertCount(1, Vote::all());
        $response = $this
            ->actingAs($user)
            ->call('post', '/votes', ['project_id' => $project->id, 'type' => 'pig']);
        $response->assertRedirect('/projects/'.$project->slug)->assertSessionHas('errors');
        $this->assertCount(1, Vote::all());
    }

    /**
     * Check that a Vote has existing type.
     */
    public function testProjectsVoteTypeExists()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $response = $this
            ->actingAs($user)
            ->call('post', '/votes', ['project_id' => $project->id, 'type' => 'awesome']);
        $response->assertRedirect('')->assertSessionHas('errors');
        $this->assertEmpty(Vote::all());
    }
}
