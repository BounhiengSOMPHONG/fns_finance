<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSeederCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeders_run_on_test_database(): void
    {
        $this->seed();

        $this->assertGreaterThan(0, DB::table('degree_programs')->count());
        $this->assertGreaterThan(0, DB::table('course_credit_settings')->count());
        $this->assertDatabaseHas('users', ['username' => 'admin']);
    }
}
