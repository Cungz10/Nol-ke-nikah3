<?php

use App\Actions\Weddings\GenerateWeddingRoadmap;
use App\Models\BudgetAllocation;
use App\Models\Payment;
use App\Models\Task;
use App\Models\Team;
use App\Models\TimelinePhase;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorCategory;
use App\Models\WeddingDocument;
use App\Models\WeddingProject;
use Database\Seeders\VendorCategorySeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

/*
|--------------------------------------------------------------------------
| Helper: creates a user + team + wedding project with roadmap
|--------------------------------------------------------------------------
*/
function createWeddingSetup(array $projectOverrides = []): array
{
    $user = User::factory()->create();
    $team = $user->currentTeam;

    // Seed vendor categories once
    if (VendorCategory::count() === 0) {
        $seeder = new VendorCategorySeeder;
        $seeder->run();
    }

    $project = WeddingProject::create([
        'team_id' => $team->id,
        'partner_one_name' => 'Rendra',
        'partner_two_name' => 'Sari',
        'target_date' => now()->addMonths(8)->toDateString(),
        'budget_total' => 200_000_000,
        'guest_count' => 300,
        'city' => 'Jakarta',
        'religion' => 'islam',
        'tradition' => 'jawa',
        ...$projectOverrides,
    ]);

    (new GenerateWeddingRoadmap)->handle($project);

    return ['user' => $user, 'team' => $team, 'project' => $project];
}

// =====================================================================
// ONBOARDING & ROADMAP GENERATION
// =====================================================================

test('onboarding page redirects to dashboard if project already exists', function () {
    ['user' => $user, 'team' => $team] = createWeddingSetup();

    $this->actingAs($user)
        ->get(route('onboarding.show', $team))
        ->assertRedirect(route('dashboard', $team));
});

test('onboarding stores project and generates roadmap in one transaction', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    if (VendorCategory::count() === 0) {
        (new VendorCategorySeeder)->run();
    }

    $this->actingAs($user)
        ->post(route('onboarding.store', $team), [
            'partner_one_name' => 'Andi',
            'partner_two_name' => 'Budi',
            'target_date' => now()->addYear()->toDateString(),
            'budget_total' => 150_000_000,
            'guest_count' => 200,
            'city' => 'Bandung',
            'religion' => 'kristen',
            'tradition' => 'sunda',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('wedding_projects', [
        'team_id' => $team->id,
        'partner_one_name' => 'Andi',
        'city' => 'Bandung',
    ]);

    $project = WeddingProject::where('team_id', $team->id)->first();
    expect($project->timelinePhases)->not->toBeEmpty();
    expect($project->timelinePhases->flatMap->tasks)->not->toBeEmpty();
});

test('onboarding rejects invalid data', function () {
    $user = User::factory()->create();
    $team = $user->currentTeam;

    $this->actingAs($user)
        ->post(route('onboarding.store', $team), [
            'partner_one_name' => '',
            'budget_total' => -100,
            'city' => '',
        ])
        ->assertSessionHasErrors(['partner_one_name', 'budget_total', 'city']);
});

test('roadmap generates phases without deadline when target_date is null', function () {
    ['project' => $project] = createWeddingSetup(['target_date' => null]);

    $phases = $project->timelinePhases()->with('tasks')->get();
    expect($phases)->not->toBeEmpty();

    $allTasks = $phases->flatMap->tasks;
    $allTasks->each(fn ($task) => expect($task->due_date)->toBeNull());
});

// =====================================================================
// TIMELINE / TASK CRUD
// =====================================================================

test('timeline page loads with phases and tasks', function () {
    ['user' => $user, 'team' => $team] = createWeddingSetup();

    $this->actingAs($user)
        ->get(route('timeline.index', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Timeline/Index')
            ->has('phases')
            ->has('weddingProject')
        );
});

test('user can create a custom task', function () {
    ['user' => $user, 'team' => $team, 'project' => $project] = createWeddingSetup();

    $phase = $project->timelinePhases()->first();

    $this->actingAs($user)
        ->post(route('timeline.tasks.store', $team), [
            'timeline_phase_id' => $phase->id,
            'title' => 'Pesan kue pengantin custom',
            'description' => 'Toko A di Cikini',
            'is_critical' => true,
            'due_date' => now()->addMonths(2)->toDateString(),
            'status' => 'todo',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('tasks', [
        'title' => 'Pesan kue pengantin custom',
        'timeline_phase_id' => $phase->id,
    ]);
});

test('user can update task status', function () {
    ['user' => $user, 'team' => $team, 'project' => $project] = createWeddingSetup();

    $task = $project->timelinePhases()->first()->tasks()->first();

    $this->actingAs($user)
        ->put(route('timeline.tasks.update', [$team, $task]), [
            'status' => 'completed',
        ])
        ->assertRedirect();

    expect($task->fresh()->status)->toBe('completed');
});

test('user can delete a task', function () {
    ['user' => $user, 'team' => $team, 'project' => $project] = createWeddingSetup();

    $task = $project->timelinePhases()->first()->tasks()->first();
    $taskId = $task->id;

    $this->actingAs($user)
        ->delete(route('timeline.tasks.destroy', [$team, $task]))
        ->assertRedirect();

    $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
});

test('overdue is computed visually from due_date and status', function () {
    ['project' => $project] = createWeddingSetup();

    $phase = $project->timelinePhases()->first();
    $task = $phase->tasks()->create([
        'title' => 'Tugas terlewat',
        'sort_order' => 99,
        'is_critical' => false,
        'due_date' => now()->subDays(3)->toDateString(),
        'status' => 'todo',
    ]);

    expect($task->status)->not->toBe('completed');
    expect($task->due_date->isPast())->toBeTrue();
});

// =====================================================================
// VENDOR CRUD
// =====================================================================

test('vendor page loads with categories and vendors', function () {
    ['user' => $user, 'team' => $team] = createWeddingSetup();

    $this->actingAs($user)
        ->get(route('vendors.index', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Vendors/Index')
            ->has('categories')
            ->has('vendors')
        );
});

test('user can create a vendor', function () {
    ['user' => $user, 'team' => $team] = createWeddingSetup();
    $catId = VendorCategory::first()->id;

    $this->actingAs($user)
        ->post(route('vendors.store', $team), [
            'vendor_category_id' => $catId,
            'name' => 'Catering Nusantara',
            'contact_person' => 'Pak Budi',
            'phone' => '08123456789',
            'status' => 'research',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('vendors', [
        'name' => 'Catering Nusantara',
        'contact_person' => 'Pak Budi',
    ]);
});

test('user can update a vendor', function () {
    ['user' => $user, 'team' => $team, 'project' => $project] = createWeddingSetup();

    $vendor = Vendor::factory()->create(['wedding_project_id' => $project->id]);

    $this->actingAs($user)
        ->put(route('vendors.update', [$team, $vendor]), [
            'name' => 'Vendor Updated',
            'status' => 'booked',
        ])
        ->assertRedirect();

    expect($vendor->fresh()->name)->toBe('Vendor Updated');
    expect($vendor->fresh()->status)->toBe('booked');
});

test('user can delete a vendor', function () {
    ['user' => $user, 'team' => $team, 'project' => $project] = createWeddingSetup();

    $vendor = Vendor::factory()->create(['wedding_project_id' => $project->id]);
    $vendorId = $vendor->id;

    $this->actingAs($user)
        ->delete(route('vendors.destroy', [$team, $vendor]))
        ->assertRedirect();

    $this->assertDatabaseMissing('vendors', ['id' => $vendorId]);
});

// =====================================================================
// BUDGET & PAYMENT
// =====================================================================

test('budget page loads allocations and payments', function () {
    ['user' => $user, 'team' => $team] = createWeddingSetup();

    $this->actingAs($user)
        ->get(route('budget.index', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Budget/Index')
            ->has('allocations')
            ->has('payments')
        );
});

test('budget allocations auto-create based on default percentages', function () {
    ['user' => $user, 'team' => $team, 'project' => $project] = createWeddingSetup();

    // Visit budget page to trigger auto-create
    $this->actingAs($user)->get(route('budget.index', $team));

    $count = BudgetAllocation::where('wedding_project_id', $project->id)->count();
    expect($count)->toBe(VendorCategory::count());
});

test('user can record a payment', function () {
    ['user' => $user, 'team' => $team] = createWeddingSetup();

    $this->actingAs($user)
        ->post(route('budget.payments.store', $team), [
            'title' => 'DP Gedung 30%',
            'amount_idr' => 15_000_000,
            'payment_type' => 'dp',
            'payment_date' => now()->toDateString(),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'title' => 'DP Gedung 30%',
        'amount_idr' => 15_000_000,
    ]);
});

test('user can delete a payment', function () {
    ['user' => $user, 'team' => $team, 'project' => $project] = createWeddingSetup();

    $payment = Payment::factory()->create(['wedding_project_id' => $project->id]);
    $paymentId = $payment->id;

    $this->actingAs($user)
        ->delete(route('budget.payments.destroy', [$team, $payment]))
        ->assertRedirect();

    $this->assertDatabaseMissing('payments', ['id' => $paymentId]);
});

test('total spent is aggregated from payments', function () {
    ['project' => $project] = createWeddingSetup();

    Payment::factory()->create(['wedding_project_id' => $project->id, 'amount_idr' => 10_000_000]);
    Payment::factory()->create(['wedding_project_id' => $project->id, 'amount_idr' => 5_000_000]);

    $totalSpent = (int) $project->payments()->sum('amount_idr');
    expect($totalSpent)->toBe(15_000_000);
});

// =====================================================================
// DOCUMENT UPLOAD & PRIVATE STORAGE
// =====================================================================

test('documents page loads', function () {
    ['user' => $user, 'team' => $team] = createWeddingSetup();

    $this->actingAs($user)
        ->get(route('documents.index', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Index')
            ->has('documents')
        );
});

test('user can upload a document to private storage', function () {
    Storage::fake('local');
    ['user' => $user, 'team' => $team, 'project' => $project] = createWeddingSetup();

    $file = UploadedFile::fake()->create('kontrak-catering.pdf', 1024, 'application/pdf');

    $this->actingAs($user)
        ->post(route('documents.store', $team), [
            'title' => 'Kontrak Catering 2026',
            'category' => 'vendor_contract',
            'file' => $file,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('wedding_documents', [
        'title' => 'Kontrak Catering 2026',
        'wedding_project_id' => $project->id,
    ]);

    $doc = WeddingDocument::where('wedding_project_id', $project->id)->first();
    Storage::disk('local')->assertExists($doc->file_path);
});

test('user can download a document', function () {
    Storage::fake('local');
    ['user' => $user, 'team' => $team, 'project' => $project] = createWeddingSetup();

    $fakeFile = UploadedFile::fake()->create('invoice.pdf', 512, 'application/pdf');
    $storedPath = $fakeFile->store('wedding_documents/'.$project->id, 'local');

    $doc = WeddingDocument::create([
        'wedding_project_id' => $project->id,
        'title' => 'Invoice Venue',
        'category' => 'invoice',
        'file_path' => $storedPath,
        'file_name' => 'invoice.pdf',
        'mime_type' => 'application/pdf',
        'file_size_bytes' => 512_000,
    ]);

    $this->actingAs($user)
        ->get(route('documents.download', [$team, $doc]))
        ->assertOk()
        ->assertDownload('invoice.pdf');
});

test('user can delete a document and file is removed', function () {
    Storage::fake('local');
    ['user' => $user, 'team' => $team, 'project' => $project] = createWeddingSetup();

    $fakeFile = UploadedFile::fake()->create('kontrak.pdf', 256, 'application/pdf');
    $storedPath = $fakeFile->store('wedding_documents/'.$project->id, 'local');

    $doc = WeddingDocument::create([
        'wedding_project_id' => $project->id,
        'title' => 'Kontrak WO',
        'category' => 'vendor_contract',
        'file_path' => $storedPath,
        'file_name' => 'kontrak.pdf',
        'mime_type' => 'application/pdf',
        'file_size_bytes' => 256_000,
    ]);

    $docId = $doc->id;

    $this->actingAs($user)
        ->delete(route('documents.destroy', [$team, $doc]))
        ->assertRedirect();

    $this->assertDatabaseMissing('wedding_documents', ['id' => $docId]);
    Storage::disk('local')->assertMissing($storedPath);
});

// =====================================================================
// WORKSPACE ISOLATION
// =====================================================================

test('user cannot access another teams vendor', function () {
    ['user' => $user, 'team' => $team, 'project' => $project] = createWeddingSetup();

    // Create second user with a separate workspace
    $otherUser = User::factory()->create();
    $otherTeam = $otherUser->currentTeam;
    $otherProject = WeddingProject::create([
        'team_id' => $otherTeam->id,
        'partner_one_name' => 'X',
        'budget_total' => 100_000_000,
        'city' => 'Surabaya',
    ]);
    $otherVendor = Vendor::factory()->create(['wedding_project_id' => $otherProject->id]);

    // Try to delete other team's vendor from my workspace
    $this->actingAs($user)
        ->delete(route('vendors.destroy', [$team, $otherVendor]))
        ->assertNotFound();
});

test('user cannot access another teams task', function () {
    ['user' => $user, 'team' => $team] = createWeddingSetup();

    $otherUser = User::factory()->create();
    $otherTeam = $otherUser->currentTeam;
    $otherProject = WeddingProject::create([
        'team_id' => $otherTeam->id,
        'partner_one_name' => 'Y',
        'budget_total' => 100_000_000,
        'city' => 'Medan',
    ]);
    $otherPhase = TimelinePhase::factory()->create(['wedding_project_id' => $otherProject->id]);
    $otherTask = Task::factory()->create(['timeline_phase_id' => $otherPhase->id]);

    $this->actingAs($user)
        ->put(route('timeline.tasks.update', [$team, $otherTask]), ['status' => 'completed'])
        ->assertNotFound();
});

test('user cannot access another teams payment', function () {
    ['user' => $user, 'team' => $team] = createWeddingSetup();

    $otherUser = User::factory()->create();
    $otherTeam = $otherUser->currentTeam;
    $otherProject = WeddingProject::create([
        'team_id' => $otherTeam->id,
        'partner_one_name' => 'Z',
        'budget_total' => 100_000_000,
        'city' => 'Bali',
    ]);
    $otherPayment = Payment::factory()->create(['wedding_project_id' => $otherProject->id]);

    $this->actingAs($user)
        ->delete(route('budget.payments.destroy', [$team, $otherPayment]))
        ->assertNotFound();
});

test('user cannot download another teams document', function () {
    Storage::fake('local');
    ['user' => $user, 'team' => $team] = createWeddingSetup();

    $otherUser = User::factory()->create();
    $otherTeam = $otherUser->currentTeam;
    $otherProject = WeddingProject::create([
        'team_id' => $otherTeam->id,
        'partner_one_name' => 'W',
        'budget_total' => 100_000_000,
        'city' => 'Makassar',
    ]);
    $otherDoc = WeddingDocument::factory()->create(['wedding_project_id' => $otherProject->id]);

    $this->actingAs($user)
        ->get(route('documents.download', [$team, $otherDoc]))
        ->assertNotFound();
});

// =====================================================================
// DASHBOARD PROPS
// =====================================================================

test('dashboard returns correct metrics props', function () {
    ['user' => $user, 'team' => $team, 'project' => $project] = createWeddingSetup();

    // Add some payments
    Payment::factory()->create(['wedding_project_id' => $project->id, 'amount_idr' => 20_000_000]);

    $this->actingAs($user)
        ->get(route('dashboard', $team))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('weddingProject')
            ->has('metrics')
            ->has('urgentTasks')
            ->has('vendorsToFollowUp')
            ->where('metrics.totalBudget', 200_000_000)
            ->where('metrics.totalSpent', 20_000_000)
            ->where('metrics.remainingBudget', 180_000_000)
        );
});
