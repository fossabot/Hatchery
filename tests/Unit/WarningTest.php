<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\User;
use App\Models\Warning;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WarningTest extends TestCase
{
    use DatabaseTransactions, DatabaseMigrations;

    /**
     * Assert the Warning is cast by a User.
     */
    public function testWarningUserRelationship()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $warning = factory(Warning::class)->create();
        $this->assertInstanceOf(User::class, $warning->user);
        $this->assertEquals($user->id, $warning->user->id);
    }

    /**
     * Assert the Warning is cast on a Project.
     */
    public function testWarningProjectRelationship()
    {
        $user = factory(User::class)->create();
        $this->be($user);
        $project = factory(Project::class)->create();
        $warning = factory(Warning::class)->create(['project_id' => $project->id]);
        $this->assertInstanceOf(Project::class, $warning->project);
        $this->assertEquals($project->id, $warning->project->id);
    }
}
