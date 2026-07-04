<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlannerItemResource;
use App\Models\Planner;
use App\Models\PlannerCategory;
use App\Models\PlannerItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class PlannerItemController extends Controller
{
    private const ITEM_TYPES = [
        PlannerItem::TYPE_TASK,
        PlannerItem::TYPE_EVENT,
        PlannerItem::TYPE_HABIT,
        PlannerItem::TYPE_NOTE,
    ];

    private const STATUSES = [
        PlannerItem::STATUS_PENDING,
        PlannerItem::STATUS_IN_PROGRESS,
        PlannerItem::STATUS_COMPLETED,
        PlannerItem::STATUS_CANCELLED,
    ];

    private const PRIORITIES = [
        PlannerItem::PRIORITY_LOW,
        PlannerItem::PRIORITY_MEDIUM,
        PlannerItem::PRIORITY_HIGH,
    ];

    private const SORTABLE_FIELDS = [
        'title',
        'item_type',
        'status',
        'priority',
        'due_date',
        'starts_at',
        'ends_at',
        'completed_at',
        'position',
        'created_at',
        'updated_at',
    ];

    public function index(Request $request, string $plannerId): JsonResponse
    {
        $planner = $this->findPlanner($plannerId);

        if (! $planner) {
            return response()->json(['message' => 'Planner not found.'], 404);
        }

        if (! $this->canView($request, $planner)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'search' => ['sometimes', 'string', 'max:255'],
            'planner_category_id' => ['sometimes', 'nullable', 'integer'],
            'item_type' => ['sometimes', Rule::in(self::ITEM_TYPES)],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'priority' => ['sometimes', Rule::in(self::PRIORITIES)],
            'due_from' => ['sometimes', 'date'],
            'due_until' => ['sometimes', 'date'],
            'starts_from' => ['sometimes', 'date'],
            'starts_until' => ['sometimes', 'date'],
            'ends_from' => ['sometimes', 'date'],
            'ends_until' => ['sometimes', 'date'],
            'completed_from' => ['sometimes', 'date'],
            'completed_until' => ['sometimes', 'date'],
            'sort_by' => ['sometimes', Rule::in(self::SORTABLE_FIELDS)],
            'sort_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        if (
            array_key_exists('planner_category_id', $validated)
            && $validated['planner_category_id'] !== null
            && ! $this->categoryBelongsToPlanner($planner, (int) $validated['planner_category_id'])
        ) {
            return response()->json([
                'message' => 'Planner category not found.',
            ], 404);
        }

        $sortBy = $validated['sort_by'] ?? 'position';
        $sortDirection = $validated['sort_direction'] ?? 'asc';
        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = $planner->items()->with('category');

        if (! empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (array_key_exists('planner_category_id', $validated)) {
            $query->where('planner_category_id', $validated['planner_category_id']);
        }

        if (isset($validated['item_type'])) {
            $query->where('item_type', $validated['item_type']);
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['priority'])) {
            $query->where('priority', $validated['priority']);
        }

        if (isset($validated['due_from'])) {
            $query->where('due_date', '>=', $validated['due_from']);
        }

        if (isset($validated['due_until'])) {
            $query->where('due_date', '<=', $validated['due_until']);
        }

        if (isset($validated['starts_from'])) {
            $query->where('starts_at', '>=', $validated['starts_from']);
        }

        if (isset($validated['starts_until'])) {
            $query->where('starts_at', '<=', $validated['starts_until']);
        }

        if (isset($validated['ends_from'])) {
            $query->where('ends_at', '>=', $validated['ends_from']);
        }

        if (isset($validated['ends_until'])) {
            $query->where('ends_at', '<=', $validated['ends_until']);
        }

        if (isset($validated['completed_from'])) {
            $query->where('completed_at', '>=', $validated['completed_from']);
        }

        if (isset($validated['completed_until'])) {
            $query->where('completed_at', '<=', $validated['completed_until']);
        }

        $items = $query
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'count' => $items->count(),
            'total' => $items->total(),
            'per_page' => $items->perPage(),
            'current_page' => $items->currentPage(),
            'last_page' => $items->lastPage(),
            'sort' => [
                'by' => $sortBy,
                'direction' => $sortDirection,
            ],
            'filters' => $request->only([
                'search',
                'planner_category_id',
                'item_type',
                'status',
                'priority',
                'due_from',
                'due_until',
                'starts_from',
                'starts_until',
                'ends_from',
                'ends_until',
                'completed_from',
                'completed_until',
            ]),
            'items' => PlannerItemResource::collection($items->getCollection()),
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
            'planner_category_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'item_type' => ['required', Rule::in(self::ITEM_TYPES)],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'priority' => ['sometimes', Rule::in(self::PRIORITIES)],
            'due_date' => ['nullable', 'date'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        if (! $this->hasValidCategory($planner, $validated['planner_category_id'] ?? null)) {
            return response()->json([
                'message' => 'Planner category not found.',
            ], 404);
        }

        if (! $this->hasValidDateTimeRange($validated)) {
            return response()->json([
                'message' => 'The ends at must be after or equal to the starts at.',
            ], 422);
        }

        $validated['planner_id'] = $planner->id;
        $validated['status'] ??= PlannerItem::STATUS_PENDING;
        $validated['priority'] ??= PlannerItem::PRIORITY_MEDIUM;

        $item = PlannerItem::create($validated)->load('category');

        return response()->json([
            'message' => 'Planner item created successfully.',
            'item' => new PlannerItemResource($item),
        ], 201);
    }

    public function show(Request $request, string $plannerId, string $itemId): JsonResponse
    {
        $planner = $this->findPlanner($plannerId);

        if (! $planner) {
            return response()->json(['message' => 'Planner not found.'], 404);
        }

        if (! $this->canView($request, $planner)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $item = $this->findItem($planner, $itemId);

        if (! $item) {
            return response()->json(['message' => 'Planner item not found.'], 404);
        }

        $item->load('category');

        return response()->json([
            'item' => new PlannerItemResource($item),
        ]);
    }

    public function update(Request $request, string $plannerId, string $itemId): JsonResponse
    {
        $planner = $this->findPlanner($plannerId);

        if (! $planner) {
            return response()->json(['message' => 'Planner not found.'], 404);
        }

        if (! $this->canManage($request, $planner)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $item = $this->findItem($planner, $itemId);

        if (! $item) {
            return response()->json(['message' => 'Planner item not found.'], 404);
        }

        $validated = $request->validate([
            'planner_id' => ['prohibited'],
            'planner_category_id' => ['sometimes', 'nullable', 'integer'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'item_type' => ['sometimes', Rule::in(self::ITEM_TYPES)],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'priority' => ['sometimes', Rule::in(self::PRIORITIES)],
            'due_date' => ['nullable', 'date'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        if (
            array_key_exists('planner_category_id', $validated)
            && ! $this->hasValidCategory($planner, $validated['planner_category_id'])
        ) {
            return response()->json([
                'message' => 'Planner category not found.',
            ], 404);
        }

        if ($validated === []) {
            $item->load('category');

            return response()->json([
                'message' => 'Nothing to update.',
                'item' => new PlannerItemResource($item),
            ]);
        }

        if (! $this->hasValidDateTimeRange($validated, $item)) {
            return response()->json([
                'message' => 'The ends at must be after or equal to the starts at.',
            ], 422);
        }

        $item->update($validated);
        $item->load('category');

        return response()->json([
            'message' => 'Planner item updated successfully.',
            'item' => new PlannerItemResource($item),
        ]);
    }

    public function destroy(Request $request, string $plannerId, string $itemId): JsonResponse
    {
        $planner = $this->findPlanner($plannerId);

        if (! $planner) {
            return response()->json(['message' => 'Planner not found.'], 404);
        }

        if (! $this->canManage($request, $planner)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $item = $this->findItem($planner, $itemId);

        if (! $item) {
            return response()->json(['message' => 'Planner item not found.'], 404);
        }

        $item->delete();

        return response()->json([
            'message' => 'Planner item deleted successfully.',
        ]);
    }

    private function findPlanner(string $id): ?Planner
    {
        if (! ctype_digit($id)) {
            return null;
        }

        return Planner::find((int) $id);
    }

    private function findItem(Planner $planner, string $id): ?PlannerItem
    {
        if (! ctype_digit($id)) {
            return null;
        }

        return $planner->items()->find((int) $id);
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

    private function categoryBelongsToPlanner(Planner $planner, int $categoryId): bool
    {
        return $planner->categories()
            ->whereKey($categoryId)
            ->exists();
    }

    private function hasValidCategory(Planner $planner, mixed $categoryId): bool
    {
        return $categoryId === null || $this->categoryBelongsToPlanner($planner, (int) $categoryId);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function hasValidDateTimeRange(array $validated, ?PlannerItem $item = null): bool
    {
        $startsAt = $validated['starts_at'] ?? $item?->starts_at;
        $endsAt = $validated['ends_at'] ?? $item?->ends_at;

        if ($startsAt === null || $endsAt === null) {
            return true;
        }

        return Carbon::parse($endsAt)->gte(Carbon::parse($startsAt));
    }
}
