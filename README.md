# Planner API

Laravel 12 API backend za personalni planner. Projekat pokriva registraciju i prijavu korisnika, role korisnika, planere, kategorije planera, stavke planera, javne eksterne API pozive, CSV eksport planera i Swagger dokumentaciju.

## Tehnologije

- PHP 8.2+
- Laravel 12
- Laravel Sanctum
- MySQL
- Pest/PHPUnit testovi
- darkaonline/l5-swagger

## Povlacenje projekta

Kloniraj repozitorijum i udji u folder projekta:

```bash
git clone <repository-url>
cd planner
```

Instaliraj PHP zavisnosti:

```bash
composer install
```

Ako zelis da koristis Vite/Laravel frontend alatke koje dolaze uz Laravel skeleton:

```bash
npm install
```

## Podesavanje lokalnog okruzenja

Kopiraj `.env.example` u `.env`:

```bash
cp .env.example .env
```

Na Windows PowerShell-u mozes koristiti:

```powershell
Copy-Item .env.example .env
```

Generisi aplikacioni kljuc:

```bash
php artisan key:generate
```

U `.env` podesi konekciju ka lokalnoj MySQL bazi. Podrazumevano je:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=planner
DB_USERNAME=root
DB_PASSWORD=
```

Pre migracija napravi bazu `planner` u MySQL-u.

## Migracije i seed podaci

Pokreni migracije:

```bash
php artisan migrate
```

Popuni bazu pocetnim podacima:

```bash
php artisan db:seed
```

Ili sve odjednom za svezu lokalnu bazu:

```bash
php artisan migrate:fresh --seed
```

Seeder kreira admin korisnika, nekoliko obicnih korisnika, realne primere planera, kategorija i stavki, a zatim dodaje jos podataka kroz factory-je.

Seed korisnici imaju lozinku:

```text
password
```

Primeri naloga:

```text
admin@planner.com
mila.novak@example.com
luka.petrovic@example.com
sara.jovanovic@example.com
```

## Pokretanje aplikacije

Pokreni Laravel server:

```bash
php artisan serve
```

Aplikacija ce biti dostupna na:

```text
http://127.0.0.1:8000
```

Ako je port 8000 zauzet drugim projektom, koristi drugi port:

```bash
php artisan serve --port=8001
```

API rute su pod `/api`, na primer:

```text
http://127.0.0.1:8000/api/planners
```

## Swagger dokumentacija

Projekat koristi `darkaonline/l5-swagger`.

Generisi OpenAPI dokumentaciju:

```bash
php artisan l5-swagger:generate
```

Swagger UI se otvara na:

```text
http://127.0.0.1:8000/api/documentation
```

Raw OpenAPI JSON je dostupan na:

```text
http://127.0.0.1:8000/docs
```

Za autorizovane rute prvo pozovi `/api/login` ili `/api/register`, kopiraj `access_token`, pa u Swagger UI klikni `Authorize` i unesi token u formatu:

```text
Bearer <token>
```

## Testovi

Pokretanje svih testova:

```bash
php artisan test
```

## Glavne funkcionalnosti

### Autentifikacija

- Registracija korisnika
- Login korisnika
- Logout korisnika
- Sanctum Bearer token autentifikacija
- Role korisnika: `admin`, `user`

Registracija moze dobiti role `admin` ili `user`. Ako role nije poslat, korisnik se kreira kao `user`.

### Planeri

Sve planner rute su zasticene preko Sanctum tokena.

- Admin moze da pregleda sve planere
- Obican korisnik moze da pregleda samo svoje planere
- Obican korisnik moze da kreira, azurira i brise samo svoje planere
- Admin ne moze da kreira, azurira ni brise planere
- Pregled liste podrzava pretragu, filtere, sortiranje i paginaciju

Tipovi planera:

```text
daily
weekly
monthly
yearly
custom
```

Podrzani filteri za listu planera:

```text
search
type
is_active
user_id
starts_from
starts_until
ends_from
ends_until
sort_by
sort_direction
per_page
page
```

### Kategorije planera

Kategorije se koriste samo na nivou konkretnog planera:

```text
/api/planners/{planner}/categories
```

- Admin moze da pregleda kategorije bilo kog planera
- Obican korisnik moze da pregleda i menja kategorije samo svojih planera
- Admin ne moze da kreira, azurira ni brise kategorije
- Nema globalnog pregleda svih kategorija
- Nema dodatnih filtera, pretrage, sortiranja ni paginacije

Kategorija ima naziv i boju.

### Stavke planera

Stavke se koriste samo na nivou konkretnog planera:

```text
/api/planners/{planner}/items
```

- Admin moze da pregleda iteme bilo kog planera
- Obican korisnik moze da pregleda i menja iteme samo svojih planera
- Admin ne moze da kreira, azurira ni brise iteme
- Kategorija itema, ako se salje, mora pripadati istom planeru
- Pregled liste podrzava pretragu, filtere, sortiranje i paginaciju

Tipovi itema:

```text
task
event
habit
note
```

Statusi itema:

```text
pending
in_progress
completed
cancelled
```

Prioriteti itema:

```text
low
medium
high
```

Podrzani filteri za listu itema:

```text
search
planner_category_id
item_type
status
priority
due_from
due_until
starts_from
starts_until
ends_from
ends_until
completed_from
completed_until
sort_by
sort_direction
per_page
page
```

### Javni eksterni API-jevi

Projekat ima javne rute koje ne zahtevaju autentifikaciju i pozivaju eksterne API servise bez API kljuca.

Praznici:

```text
GET /api/holidays
GET /api/public/holidays
```

Ruta poziva Nager.Date API. Podrzani query parametri:

```text
year
country
```

Primer:

```text
/api/holidays?year=2026&country=RS
```

Vremenska prognoza:

```text
GET /api/weather
GET /api/public/weather
```

Ruta poziva Open-Meteo API. Podrzani query parametri:

```text
latitude
longitude
forecast_days
timezone
```

Primer:

```text
/api/weather?latitude=44.8125&longitude=20.4612&forecast_days=7
```

### CSV eksport

Ruta:

```text
GET /api/planners/export
```

Preuzima CSV fajl sa podacima o planerima. CSV sadrzi vlasnika planera, naslov, opis, tip, datume, aktivnost, broj kategorija i broj itema.

Eksport postuje prava pristupa:

- Admin eksportuje sve planere
- Obican korisnik eksportuje samo svoje planere

Podrzani filteri:

```text
search
type
is_active
user_id
starts_from
starts_until
ends_from
ends_until
```

## Pregled glavnih ruta

```text
POST      /api/register
POST      /api/login
POST      /api/logout
GET       /api/user

GET       /api/holidays
GET       /api/weather

GET       /api/planners
POST      /api/planners
GET       /api/planners/export
GET       /api/planners/{planner}
PUT/PATCH /api/planners/{planner}
DELETE    /api/planners/{planner}

GET       /api/planners/{planner}/categories
POST      /api/planners/{planner}/categories
GET       /api/planners/{planner}/categories/{category}
PUT/PATCH /api/planners/{planner}/categories/{category}
DELETE    /api/planners/{planner}/categories/{category}

GET       /api/planners/{planner}/items
POST      /api/planners/{planner}/items
GET       /api/planners/{planner}/items/{item}
PUT/PATCH /api/planners/{planner}/items/{item}
DELETE    /api/planners/{planner}/items/{item}
```

## Korisne komande

```bash
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan l5-swagger:generate
php artisan serve
php artisan test
```
