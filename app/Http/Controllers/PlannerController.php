<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlannerResource;
use App\Models\Planner;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class PlannerController extends Controller
{
    private const TYPES = [
        Planner::TYPE_DAILY,
        Planner::TYPE_WEEKLY,
        Planner::TYPE_MONTHLY,
        Planner::TYPE_YEARLY,
        Planner::TYPE_CUSTOM,
    ];

    private const SORTABLE_FIELDS = [
        'title',
        'type',
        'start_date',
        'end_date',
        'is_active',
        'created_at',
        'updated_at',
    ];

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::in(self::TYPES)],
            'user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'starts_from' => ['sometimes', 'date'],
            'starts_until' => ['sometimes', 'date'],
            'ends_from' => ['sometimes', 'date'],
            'ends_until' => ['sometimes', 'date'],
            'sort_by' => ['sometimes', Rule::in(self::SORTABLE_FIELDS)],
            'sort_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $user = $request->user();

        if (! $this->isAdmin($request) && isset($validated['user_id']) && (int) $validated['user_id'] !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortDirection = $validated['sort_direction'] ?? 'desc';
        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = Planner::query()->with(['user', 'categories']);

        if (! $this->isAdmin($request)) {
            $query->where('user_id', $user->id);
        }

        if (! empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($query) use ($request, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('categories', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });

                if ($this->isAdmin($request)) {
                    $query->orWhereHas('user', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                }
            });
        }

        if (isset($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if ($this->isAdmin($request) && isset($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }

        if (isset($validated['starts_from'])) {
            $query->where('start_date', '>=', $validated['starts_from']);
        }

        if (isset($validated['starts_until'])) {
            $query->where('start_date', '<=', $validated['starts_until']);
        }

        if (isset($validated['ends_from'])) {
            $query->where('end_date', '>=', $validated['ends_from']);
        }

        if (isset($validated['ends_until'])) {
            $query->where('end_date', '<=', $validated['ends_until']);
        }

        $planners = $query
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'count' => $planners->count(),
            'total' => $planners->total(),
            'per_page' => $planners->perPage(),
            'current_page' => $planners->currentPage(),
            'last_page' => $planners->lastPage(),
            'sort' => [
                'by' => $sortBy,
                'direction' => $sortDirection,
            ],
            'filters' => $request->only([
                'search',
                'type',
                'is_active',
                'user_id',
                'starts_from',
                'starts_until',
                'ends_from',
                'ends_until',
            ]),
            'planners' => PlannerResource::collection($planners->getCollection()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($this->isAdmin($request)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => ['prohibited'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(self::TYPES)],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['is_active'] ??= true;

        $planner = Planner::create($validated)->load(['user', 'categories', 'items.category']);

        return response()->json([
            'message' => 'Planner created successfully.',
            'planner' => new PlannerResource($planner),
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $planner = $this->findPlanner($id);

        if (! $planner) {
            return response()->json(['message' => 'Planner not found.'], 404);
        }

        if (! $this->canView($request, $planner)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $planner->load(['user', 'categories', 'items.category']);

        return response()->json([
            'planner' => new PlannerResource($planner),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $planner = $this->findPlanner($id);

        if (! $planner) {
            return response()->json(['message' => 'Planner not found.'], 404);
        }

        if (! $this->canManage($request, $planner)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => ['prohibited'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', Rule::in(self::TYPES)],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($validated === []) {
            $planner->load(['user', 'categories', 'items.category']);

            return response()->json([
                'message' => 'Nothing to update.',
                'planner' => new PlannerResource($planner),
            ]);
        }

        if (! $this->hasValidDateRange($validated, $planner)) {
            return response()->json([
                'message' => 'The end date must be after or equal to the start date.',
            ], 422);
        }

        $planner->update($validated);
        $planner->load(['user', 'categories', 'items.category']);

        return response()->json([
            'message' => 'Planner updated successfully.',
            'planner' => new PlannerResource($planner),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $planner = $this->findPlanner($id);

        if (! $planner) {
            return response()->json(['message' => 'Planner not found.'], 404);
        }

        if (! $this->canManage($request, $planner)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $planner->delete();

        return response()->json([
            'message' => 'Planner deleted successfully.',
        ]);
    }

    private function findPlanner(string $id): ?Planner
    {
        if (! ctype_digit($id)) {
            return null;
        }

        return Planner::find((int) $id);
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

    /**
     * @param  array<string, mixed>  $validated
     */
    private function hasValidDateRange(array $validated, Planner $planner): bool
    {
        $startDate = Carbon::parse($validated['start_date'] ?? $planner->start_date);
        $endDate = $validated['end_date'] ?? $planner->end_date;

        if ($endDate === null) {
            return true;
        }

        return Carbon::parse($endDate)->gte($startDate);
    }
}
