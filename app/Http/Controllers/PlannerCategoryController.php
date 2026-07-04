<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlannerCategoryResource;
use App\Models\Planner;
use App\Models\PlannerCategory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlannerCategoryController extends Controller
{
    public function index(Request $request, string $plannerId): JsonResponse
    {
        $planner = $this->findPlanner($plannerId);

        if (! $planner) {
            return response()->json(['message' => 'Planner not found.'], 404);
        }

        if (! $this->canView($request, $planner)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $categories = $planner->categories()
            ->with('items')
            ->get();

        return response()->json([
            'count' => $categories->count(),
            'categories' => PlannerCategoryResource::collection($categories),
        ]);
    }

    public function store(Request $request, string $plannerId): JsonResponse
    {
        $planner = $this->findPlanner($plannerId);

        if (! $planner) {
            return response()->json(['message' => 'Planner not found.'], 404);
        }

        if (! $this->canManage($request, $planner)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'planner_id' => ['prohibited'],
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:32'],
        ]);

        $validated['planner_id'] = $planner->id;

        $category = PlannerCategory::create($validated);

        return response()->json([
            'message' => 'Planner category created successfully.',
            'category' => new PlannerCategoryResource($category),
        ], 201);
    }

    public function show(Request $request, string $plannerId, string $categoryId): JsonResponse
    {
        $planner = $this->findPlanner($plannerId);

        if (! $planner) {
            return response()->json(['message' => 'Planner not found.'], 404);
        }

        if (! $this->canView($request, $planner)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $category = $this->findCategory($planner, $categoryId);

        if (! $category) {
            return response()->json(['message' => 'Planner category not found.'], 404);
        }

        $category->load('items');

        return response()->json([
            'category' => new PlannerCategoryResource($category),
        ]);
    }

    public function update(Request $request, string $plannerId, string $categoryId): JsonResponse
    {
        $planner = $this->findPlanner($plannerId);

        if (! $planner) {
            return response()->json(['message' => 'Planner not found.'], 404);
        }

        if (! $this->canManage($request, $planner)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $category = $this->findCategory($planner, $categoryId);

        if (! $category) {
            return response()->json(['message' => 'Planner category not found.'], 404);
        }

        $validated = $request->validate([
            'planner_id' => ['prohibited'],
            'name' => ['sometimes', 'string', 'max:255'],
            'color' => ['sometimes', 'string', 'max:32'],
        ]);

        if ($validated === []) {
            $category->load('items');

            return response()->json([
                'message' => 'Nothing to update.',
                'category' => new PlannerCategoryResource($category),
            ]);
        }

        $category->update($validated);
        $category->load('items');

        return response()->json([
            'message' => 'Planner category updated successfully.',
            'category' => new PlannerCategoryResource($category),
        ]);
    }

    public function destroy(Request $request, string $plannerId, string $categoryId): JsonResponse
    {
        $planner = $this->findPlanner($plannerId);

        if (! $planner) {
            return response()->json(['message' => 'Planner not found.'], 404);
        }

        if (! $this->canManage($request, $planner)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $category = $this->findCategory($planner, $categoryId);

        if (! $category) {
            return response()->json(['message' => 'Planner category not found.'], 404);
        }

        $category->delete();

        return response()->json([
            'message' => 'Planner category deleted successfully.',
        ]);
    }

    private function findPlanner(string $id): ?Planner
    {
        if (! ctype_digit($id)) {
            return null;
        }

        return Planner::find((int) $id);
    }

    private function findCategory(Planner $planner, string $id): ?PlannerCategory
    {
        if (! ctype_digit($id)) {
            return null;
        }

        return $planner->categories()->find((int) $id);
    }

    private function canView(Request $request, Planner $planner): bool
    {
        return $this->isAdmin($request) || $planner->user_id === $request->user()?->id;
    }

    private function canManage(Request $request, Planner $planner): bool
    {
        return ! $this->isAdmin($request) && $planner->user_id === $request->user()?->id;
    }

    private function isAdmin(Request $request): bool
    {
        return $request->user()?->role === User::ROLE_ADMIN;
    }
}
