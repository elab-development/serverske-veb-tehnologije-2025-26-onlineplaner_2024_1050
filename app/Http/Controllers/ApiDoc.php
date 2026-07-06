<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Planner API",
 *     version="1.0.0",
 *     description="REST API za personalne planere. API koristi JSON za standardne odgovore, Sanctum Bearer tokene za zasticene rute, javne eksterne API pozive za praznike i vremensku prognozu i CSV za eksport planera."
 * )
 *
 * @OA\Server(
 *     url="/api",
 *     description="API base path"
 * )
 *
 * @OA\Tag(name="Auth", description="Registracija, login i logout")
 * @OA\Tag(name="Users", description="Podaci o trenutno ulogovanom korisniku")
 * @OA\Tag(name="Public", description="Javne rute koje pozivaju eksterne API-jeve")
 * @OA\Tag(name="Planners", description="Planeri, pretraga, filteri i upravljanje")
 * @OA\Tag(name="Planner Categories", description="Kategorije na nivou jednog planera")
 * @OA\Tag(name="Planner Items", description="Stavke na nivou jednog planera")
 * @OA\Tag(name="Exports", description="Eksport podataka")
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Sanctum token",
 *     description="Uneti token dobijen kroz /register ili /login. Format u Authorization headeru: Bearer {token}"
 * )
 *
 * @OA\Schema(
 *     schema="ErrorMessage",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="Unauthorized")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     example={"message":"The given data was invalid.","errors":{"email":{"The email has already been taken."}}}
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Mila Novak"),
 *     @OA\Property(property="email", type="string", format="email", example="mila.novak@example.com"),
 *     @OA\Property(property="role", type="string", enum={"admin","user"}, example="user"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="Planner",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=2),
 *     @OA\Property(property="title", type="string", example="Weekly Study Planner"),
 *     @OA\Property(property="description", type="string", nullable=true, example="A weekly plan for classes, assignments, reading, and exam preparation."),
 *     @OA\Property(property="type", type="string", enum={"daily","weekly","monthly","yearly","custom"}, example="weekly"),
 *     @OA\Property(property="start_date", type="string", format="date", example="2026-06-22"),
 *     @OA\Property(property="end_date", type="string", format="date", nullable=true, example="2026-06-28"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="user", ref="#/components/schemas/User", nullable=true),
 *     @OA\Property(property="categories", type="array", @OA\Items(ref="#/components/schemas/PlannerCategory")),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/PlannerItem")),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="PlannerCategory",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="planner_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Work"),
 *     @OA\Property(property="color", type="string", example="#2563eb"),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/PlannerItem")),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="PlannerItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="planner_id", type="integer", example=1),
 *     @OA\Property(property="planner_category_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="title", type="string", example="Review project requirements"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Check API requirements and prepare the next implementation step."),
 *     @OA\Property(property="item_type", type="string", enum={"task","event","habit","note"}, example="task"),
 *     @OA\Property(property="status", type="string", enum={"pending","in_progress","completed","cancelled"}, example="pending"),
 *     @OA\Property(property="priority", type="string", enum={"low","medium","high"}, example="high"),
 *     @OA\Property(property="due_date", type="string", format="date", nullable=true, example="2026-06-23"),
 *     @OA\Property(property="starts_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="ends_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="completed_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="position", type="integer", nullable=true, example=1),
 *     @OA\Property(property="category", ref="#/components/schemas/PlannerCategory", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Post(
 *     path="/register",
 *     tags={"Auth"},
 *     summary="Registracija korisnika",
 *     description="Kreira user ili admin nalog i vraca Sanctum Bearer token. Ako role nije poslat, podrazumevano se koristi user.",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"name","email","password"},
 *         @OA\Property(property="name", type="string", maxLength=255, example="Petar Petrovic"),
 *         @OA\Property(property="email", type="string", format="email", example="petar@example.com"),
 *         @OA\Property(property="password", type="string", minLength=8, example="password123"),
 *         @OA\Property(property="role", type="string", enum={"admin","user"}, example="user")
 *     )),
 *     @OA\Response(response=201, description="User registered", @OA\JsonContent(
 *         @OA\Property(property="data", ref="#/components/schemas/User"),
 *         @OA\Property(property="access_token", type="string", example="1|plain-text-token"),
 *         @OA\Property(property="token_type", type="string", example="Bearer")
 *     )),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Post(
 *     path="/login",
 *     tags={"Auth"},
 *     summary="Login korisnika",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"email","password"},
 *         @OA\Property(property="email", type="string", format="email", example="petar@example.com"),
 *         @OA\Property(property="password", type="string", example="password123")
 *     )),
 *     @OA\Response(response=200, description="User logged in", @OA\JsonContent(
 *         @OA\Property(property="message", type="string", example="Petar Petrovic logged in"),
 *         @OA\Property(property="data", ref="#/components/schemas/User"),
 *         @OA\Property(property="access_token", type="string", example="1|plain-text-token"),
 *         @OA\Property(property="token_type", type="string", example="Bearer")
 *     )),
 *     @OA\Response(response=401, description="Wrong credentials", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Post(
 *     path="/logout",
 *     tags={"Auth"},
 *     summary="Logout korisnika",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(response=200, description="Logged out", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage"))
 * )
 *
 * @OA\Get(
 *     path="/user",
 *     tags={"Users"},
 *     summary="Trenutno ulogovan korisnik",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(response=200, description="Authenticated user", @OA\JsonContent(ref="#/components/schemas/User")),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage"))
 * )
 *
 * @OA\Get(
 *     path="/holidays",
 *     tags={"Public"},
 *     summary="Javni praznici",
 *     description="Poziva Nager.Date API. Ruta ne zahteva autentikaciju.",
 *     @OA\Parameter(name="year", in="query", required=false, @OA\Schema(type="integer", minimum=1970, maximum=2100, example=2026)),
 *     @OA\Parameter(name="country", in="query", required=false, @OA\Schema(type="string", minLength=2, maxLength=2, example="RS")),
 *     @OA\Response(response=200, description="Holiday data"),
 *     @OA\Response(response=502, description="External service error", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Get(
 *     path="/public/holidays",
 *     tags={"Public"},
 *     summary="Javni praznici - alias ruta",
 *     @OA\Parameter(name="year", in="query", required=false, @OA\Schema(type="integer", example=2026)),
 *     @OA\Parameter(name="country", in="query", required=false, @OA\Schema(type="string", example="RS")),
 *     @OA\Response(response=200, description="Holiday data")
 * )
 *
 * @OA\Get(
 *     path="/weather",
 *     tags={"Public"},
 *     summary="Vremenska prognoza",
 *     description="Poziva Open-Meteo API. Ruta ne zahteva autentikaciju.",
 *     @OA\Parameter(name="latitude", in="query", required=false, @OA\Schema(type="number", format="float", minimum=-90, maximum=90, example=44.8125)),
 *     @OA\Parameter(name="longitude", in="query", required=false, @OA\Schema(type="number", format="float", minimum=-180, maximum=180, example=20.4612)),
 *     @OA\Parameter(name="forecast_days", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=16, example=7)),
 *     @OA\Parameter(name="timezone", in="query", required=false, @OA\Schema(type="string", example="Europe/Belgrade")),
 *     @OA\Response(response=200, description="Weather data"),
 *     @OA\Response(response=502, description="External service error", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Get(
 *     path="/public/weather",
 *     tags={"Public"},
 *     summary="Vremenska prognoza - alias ruta",
 *     @OA\Parameter(name="latitude", in="query", required=false, @OA\Schema(type="number", format="float", example=44.8125)),
 *     @OA\Parameter(name="longitude", in="query", required=false, @OA\Schema(type="number", format="float", example=20.4612)),
 *     @OA\Parameter(name="forecast_days", in="query", required=false, @OA\Schema(type="integer", example=7)),
 *     @OA\Response(response=200, description="Weather data")
 * )
 *
 * @OA\Get(
 *     path="/planners",
 *     tags={"Planners"},
 *     summary="Lista planera",
 *     description="Admin vidi sve planere. Obican korisnik vidi samo svoje planere.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", example="study")),
 *     @OA\Parameter(name="type", in="query", required=false, @OA\Schema(type="string", enum={"daily","weekly","monthly","yearly","custom"})),
 *     @OA\Parameter(name="is_active", in="query", required=false, @OA\Schema(type="boolean", example=true)),
 *     @OA\Parameter(name="user_id", in="query", required=false, description="Admin filter po korisniku.", @OA\Schema(type="integer", example=2)),
 *     @OA\Parameter(name="starts_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="starts_until", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="ends_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="ends_until", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="sort_by", in="query", required=false, @OA\Schema(type="string", enum={"title","type","start_date","end_date","is_active","created_at","updated_at"})),
 *     @OA\Parameter(name="sort_direction", in="query", required=false, @OA\Schema(type="string", enum={"asc","desc"})),
 *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=50)),
 *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
 *     @OA\Response(response=200, description="Paginated planners"),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Post(
 *     path="/planners",
 *     tags={"Planners"},
 *     summary="Kreiranje planera",
 *     description="Planer kreira samo obican korisnik. Admin moze samo da pregleda planere.",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"title","type","start_date"},
 *         @OA\Property(property="title", type="string", example="Weekly Work Planner"),
 *         @OA\Property(property="description", type="string", nullable=true, example="Plan for meetings and tasks."),
 *         @OA\Property(property="type", type="string", enum={"daily","weekly","monthly","yearly","custom"}, example="weekly"),
 *         @OA\Property(property="start_date", type="string", format="date", example="2026-06-22"),
 *         @OA\Property(property="end_date", type="string", format="date", nullable=true, example="2026-06-28"),
 *         @OA\Property(property="is_active", type="boolean", example=true)
 *     )),
 *     @OA\Response(response=201, description="Planner created"),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Get(
 *     path="/planners/export",
 *     tags={"Exports","Planners"},
 *     summary="CSV eksport planera",
 *     description="Admin eksportuje sve planere, korisnik samo svoje. Podrzava iste osnovne filtere kao lista planera.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="type", in="query", required=false, @OA\Schema(type="string", enum={"daily","weekly","monthly","yearly","custom"})),
 *     @OA\Parameter(name="is_active", in="query", required=false, @OA\Schema(type="boolean")),
 *     @OA\Parameter(name="user_id", in="query", required=false, @OA\Schema(type="integer")),
 *     @OA\Response(
 *         response=200,
 *         description="CSV file",
 *         @OA\MediaType(mediaType="text/csv", @OA\Schema(type="string", example="id,user_id,user_name,user_email,title,description,type,start_date,end_date,is_active,categories_count,items_count,created_at,updated_at"))
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Get(
 *     path="/planners/{planner}",
 *     tags={"Planners"},
 *     summary="Pregled jednog planera",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="planner", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Planner details", @OA\JsonContent(@OA\Property(property="planner", ref="#/components/schemas/Planner"))),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=404, description="Planner not found", @OA\JsonContent(ref="#/components/schemas/ErrorMessage"))
 * )
 *
 * @OA\Put(
 *     path="/planners/{planner}",
 *     tags={"Planners"},
 *     summary="Azuriranje planera",
 *     description="Korisnik moze azurirati samo svoje planere. Admin ne moze da azurira planere.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="planner", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\RequestBody(required=false, @OA\JsonContent(
 *         @OA\Property(property="title", type="string", example="Updated Weekly Planner"),
 *         @OA\Property(property="description", type="string", nullable=true),
 *         @OA\Property(property="type", type="string", enum={"daily","weekly","monthly","yearly","custom"}),
 *         @OA\Property(property="start_date", type="string", format="date"),
 *         @OA\Property(property="end_date", type="string", format="date", nullable=true),
 *         @OA\Property(property="is_active", type="boolean")
 *     )),
 *     @OA\Response(response=200, description="Planner updated"),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=404, description="Planner not found", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Patch(
 *     path="/planners/{planner}",
 *     tags={"Planners"},
 *     summary="Delimicno azuriranje planera",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="planner", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\RequestBody(required=false, @OA\JsonContent(@OA\Property(property="is_active", type="boolean", example=false))),
 *     @OA\Response(response=200, description="Planner updated"),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=404, description="Planner not found", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Delete(
 *     path="/planners/{planner}",
 *     tags={"Planners"},
 *     summary="Brisanje planera",
 *     description="Korisnik moze obrisati samo svoje planere. Admin ne moze da brise planere.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="planner", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Planner deleted", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=404, description="Planner not found", @OA\JsonContent(ref="#/components/schemas/ErrorMessage"))
 * )
 *
 * @OA\Get(
 *     path="/planners/{planner}/categories",
 *     tags={"Planner Categories"},
 *     summary="Lista kategorija jednog planera",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="planner", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Categories list"),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=404, description="Planner not found", @OA\JsonContent(ref="#/components/schemas/ErrorMessage"))
 * )
 *
 * @OA\Post(
 *     path="/planners/{planner}/categories",
 *     tags={"Planner Categories"},
 *     summary="Kreiranje kategorije planera",
 *     description="Kategoriju moze kreirati samo vlasnik planera. Admin samo pregleda.",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="planner", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\RequestBody(required=true, @OA\JsonContent(required={"name","color"}, @OA\Property(property="name", type="string", example="Work"), @OA\Property(property="color", type="string", example="#2563eb"))),
 *     @OA\Response(response=201, description="Planner category created"),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=404, description="Planner not found", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Get(
 *     path="/planners/{planner}/categories/{category}",
 *     tags={"Planner Categories"},
 *     summary="Pregled jedne kategorije planera",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="planner", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Planner category details"),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=404, description="Planner or category not found", @OA\JsonContent(ref="#/components/schemas/ErrorMessage"))
 * )
 *
 * @OA\Put(
 *     path="/planners/{planner}/categories/{category}",
 *     tags={"Planner Categories"},
 *     summary="Azuriranje kategorije planera",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="planner", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\RequestBody(required=false, @OA\JsonContent(@OA\Property(property="name", type="string", example="Personal"), @OA\Property(property="color", type="string", example="#16a34a"))),
 *     @OA\Response(response=200, description="Planner category updated"),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=404, description="Planner or category not found", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Patch(
 *     path="/planners/{planner}/categories/{category}",
 *     tags={"Planner Categories"},
 *     summary="Delimicno azuriranje kategorije planera",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="planner", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\RequestBody(required=false, @OA\JsonContent(@OA\Property(property="color", type="string", example="#f97316"))),
 *     @OA\Response(response=200, description="Planner category updated"),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=404, description="Planner or category not found", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Delete(
 *     path="/planners/{planner}/categories/{category}",
 *     tags={"Planner Categories"},
 *     summary="Brisanje kategorije planera",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="planner", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Planner category deleted", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=404, description="Planner or category not found", @OA\JsonContent(ref="#/components/schemas/ErrorMessage"))
 * )
 *
 * @OA\Get(
 *     path="/planners/{planner}/items",
 *     tags={"Planner Items"},
 *     summary="Lista itema jednog planera",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="planner", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string", example="review")),
 *     @OA\Parameter(name="planner_category_id", in="query", required=false, @OA\Schema(type="integer", nullable=true)),
 *     @OA\Parameter(name="item_type", in="query", required=false, @OA\Schema(type="string", enum={"task","event","habit","note"})),
 *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"pending","in_progress","completed","cancelled"})),
 *     @OA\Parameter(name="priority", in="query", required=false, @OA\Schema(type="string", enum={"low","medium","high"})),
 *     @OA\Parameter(name="due_from", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="due_until", in="query", required=false, @OA\Schema(type="string", format="date")),
 *     @OA\Parameter(name="sort_by", in="query", required=false, @OA\Schema(type="string", enum={"title","item_type","status","priority","due_date","starts_at","ends_at","completed_at","position","created_at","updated_at"})),
 *     @OA\Parameter(name="sort_direction", in="query", required=false, @OA\Schema(type="string", enum={"asc","desc"})),
 *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=50)),
 *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
 *     @OA\Response(response=200, description="Paginated planner items"),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=404, description="Planner not found", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Post(
 *     path="/planners/{planner}/items",
 *     tags={"Planner Items"},
 *     summary="Kreiranje itema planera",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="planner", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"title","item_type"},
 *         @OA\Property(property="planner_category_id", type="integer", nullable=true, example=1),
 *         @OA\Property(property="title", type="string", example="Review project requirements"),
 *         @OA\Property(property="description", type="string", nullable=true),
 *         @OA\Property(property="item_type", type="string", enum={"task","event","habit","note"}, example="task"),
 *         @OA\Property(property="status", type="string", enum={"pending","in_progress","completed","cancelled"}, example="pending"),
 *         @OA\Property(property="priority", type="string", enum={"low","medium","high"}, example="high"),
 *         @OA\Property(property="due_date", type="string", format="date", nullable=true),
 *         @OA\Property(property="starts_at", type="string", format="date-time", nullable=true),
 *         @OA\Property(property="ends_at", type="string", format="date-time", nullable=true),
 *         @OA\Property(property="completed_at", type="string", format="date-time", nullable=true),
 *         @OA\Property(property="position", type="integer", nullable=true)
 *     )),
 *     @OA\Response(response=201, description="Planner item created"),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=404, description="Planner or category not found", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Get(
 *     path="/planners/{planner}/items/{item}",
 *     tags={"Planner Items"},
 *     summary="Pregled jednog itema planera",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="planner", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Parameter(name="item", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Planner item details"),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=404, description="Planner or item not found", @OA\JsonContent(ref="#/components/schemas/ErrorMessage"))
 * )
 *
 * @OA\Put(
 *     path="/planners/{planner}/items/{item}",
 *     tags={"Planner Items"},
 *     summary="Azuriranje itema planera",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="planner", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Parameter(name="item", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\RequestBody(required=false, @OA\JsonContent(
 *         @OA\Property(property="planner_category_id", type="integer", nullable=true),
 *         @OA\Property(property="title", type="string"),
 *         @OA\Property(property="description", type="string", nullable=true),
 *         @OA\Property(property="item_type", type="string", enum={"task","event","habit","note"}),
 *         @OA\Property(property="status", type="string", enum={"pending","in_progress","completed","cancelled"}),
 *         @OA\Property(property="priority", type="string", enum={"low","medium","high"}),
 *         @OA\Property(property="due_date", type="string", format="date", nullable=true),
 *         @OA\Property(property="starts_at", type="string", format="date-time", nullable=true),
 *         @OA\Property(property="ends_at", type="string", format="date-time", nullable=true),
 *         @OA\Property(property="completed_at", type="string", format="date-time", nullable=true),
 *         @OA\Property(property="position", type="integer", nullable=true)
 *     )),
 *     @OA\Response(response=200, description="Planner item updated"),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=404, description="Planner, item or category not found", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Patch(
 *     path="/planners/{planner}/items/{item}",
 *     tags={"Planner Items"},
 *     summary="Delimicno azuriranje itema planera",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="planner", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Parameter(name="item", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\RequestBody(required=false, @OA\JsonContent(@OA\Property(property="status", type="string", enum={"pending","in_progress","completed","cancelled"}, example="completed"))),
 *     @OA\Response(response=200, description="Planner item updated"),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=404, description="Planner or item not found", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Delete(
 *     path="/planners/{planner}/items/{item}",
 *     tags={"Planner Items"},
 *     summary="Brisanje itema planera",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="planner", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Parameter(name="item", in="path", required=true, @OA\Schema(type="integer", example=1)),
 *     @OA\Response(response=200, description="Planner item deleted", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=403, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorMessage")),
 *     @OA\Response(response=404, description="Planner or item not found", @OA\JsonContent(ref="#/components/schemas/ErrorMessage"))
 * )
 */
class ApiDoc extends Controller
{
}
