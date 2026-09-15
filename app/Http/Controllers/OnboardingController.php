<?php

namespace App\Http\Controllers;

use App\Actions\Weddings\GenerateWeddingRoadmap;
use App\Http\Requests\StoreWeddingProjectRequest;
use App\Models\Team;
use App\Models\WeddingProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function show(Team $current_team): Response|RedirectResponse
    {
        if ($current_team->weddingProject()->exists()) {
            return to_route('dashboard', $current_team);
        }

        return Inertia::render('Onboarding');
    }

    public function store(StoreWeddingProjectRequest $request, Team $current_team, GenerateWeddingRoadmap $generateRoadmap): RedirectResponse
    {
        DB::transaction(function () use ($request, $current_team, $generateRoadmap): void {
            $validated = $request->validated();

            $current_team->update([
                'name' => 'Pernikahan '.$validated['partner_one_name'].($validated['partner_two_name'] ? ' & '.$validated['partner_two_name'] : ''),
            ]);

            $weddingProject = WeddingProject::create([
                ...$validated,
                'team_id' => $current_team->id,
            ]);

            $generateRoadmap->handle($weddingProject);
        });

        return to_route('dashboard', $current_team->fresh());
    }
}
