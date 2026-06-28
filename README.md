# QuizApp

Interaktivna platforma za kvizove s podrškom za multiplayer, kreiranje kvizova, bodovanje i administraciju korisnika.

## Tehnologije

### Backend
- **Laravel 12** (PHP 8.2+)
- **Laravel Sanctum** — autentifikacija putem tokena
- **SQLite** (zadano) / MySQL
- **Eloquent ORM**

### Frontend
- **Vue.js 3** (Composition API, `<script setup>`)
- **Vite** — build alat
- **Axios** — HTTP klijent
- **TinyMCE** — rich text editor za opise kvizova
- **SortableJS** — drag-and-drop za promjenu redoslijeda pitanja
- **Custom CSS dizajn sustav** — Space Grotesk + Lato fontovi, mint/pink paleta boja

---

## Pokretanje projekta

### Preduvjeti
- PHP 8.2+
- Composer
- Node.js 18+
- npm

### 1. Kloniranje repozitorija
```bash
git clone https://github.com/Martinaa0/QuizApp.git
cd QuizApp
```

### 2. Backend postavljanje
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```
Backend se pokreće na `http://localhost:8000`.

### 3. Frontend postavljanje
```bash
cd frontend
npm install
npm run dev
```
Frontend se pokreće na `http://localhost:5173`.

### 4. Produkcijski build
```bash
cd frontend
npm run build
```

---

## Struktura projekta

```
QuizApp/
├── backend/                    # Laravel 12 API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/    # API kontroleri
│   │   │   ├── Middleware/     # AdminMiddleware, CorsMiddleware
│   │   │   └── Requests/      # Validacijski zahtjevi
│   │   └── Models/            # Eloquent modeli
│   ├── database/
│   │   └── migrations/        # Migracijske datoteke
│   └── routes/
│       └── api.php            # Definicije API ruta
│
├── frontend/                   # Vue 3 + Vite aplikacija
│   ├── src/
│   │   ├── assets/styles/     # CSS dizajn sustav
│   │   ├── components/        # Vue komponente
│   │   ├── router/            # Vue Router konfiguracija
│   │   ├── services/          # Axios API servis
│   │   ├── views/             # Stranice/pogledi
│   │   ├── App.vue            # Glavna komponenta s app barom
│   │   └── main.js            # Ulazna točka
│   └── index.html
└── README.md
```

---

## Korisničke uloge

| Uloga | Dopuštenja |
|-------|-----------|
| **Student** | Pregledavanje kvizova, rješavanje kvizova, pregled rezultata |
| **Profesor** | Sve od studenta + kreiranje/uređivanje kvizova, upravljanje pitanjima i opcijama |
| **Admin** | Sve od profesora + upravljanje korisnicima (CRUD), pristup admin nadzornoj ploči |

---

## Modeli podataka

### User (Korisnik)
- `id`, `name`, `username`, `email`, `password`, `user_type` (student/teacher/admin)
- Relacije: `hasMany(Quiz)`, `hasMany(QuizAttempt)`

### Quiz (Kviz)
- `id`, `title`, `description`, `duration` (minute), `image`, `category`, `difficulty` (easy/medium/hard), `is_active`, `created_by`
- Relacije: `belongsTo(User)`, `hasMany(Question)`, `hasMany(QuizAttempt)`

### Question (Pitanje)
- `id`, `quiz_id`, `text`, `type` (multiple_choice/true_false/short_answer), `points`, `order`
- Relacije: `belongsTo(Quiz)`, `hasMany(Option)`, `hasMany(UserAnswer)`

### Option (Opcija odgovora)
- `id`, `question_id`, `text`, `is_correct`, `order`
- Relacije: `belongsTo(Question)`

### QuizAttempt (Pokušaj kviza)
- `id`, `user_id`, `quiz_id`, `score`, `total_points`, `percentage`, `started_at`, `completed_at`, `status` (in_progress/completed)
- Relacije: `belongsTo(User)`, `belongsTo(Quiz)`, `hasMany(UserAnswer)`

### UserAnswer (Odgovor korisnika)
- `id`, `attempt_id`, `question_id`, `option_id`, `answer_text`, `is_correct`, `points_earned`
- Relacije: `belongsTo(QuizAttempt)`, `belongsTo(Question)`, `belongsTo(Option)`

### Lobby (Predvorje)
- `id`, `quiz_id`, `host_id`, `code` (6 znakova), `status` (waiting/starting/in_progress/completed/cancelled), `max_players`, `current_players`, `settings`
- Relacije: `belongsTo(Quiz)`, `belongsTo(User)`, `hasMany(GameSession)`

### GameSession (Sesija igre)
- `id`, `lobby_id`, `user_id`, `quiz_id`, `score`, `total_points`, `percentage`, `answers` (JSON), `current_question_index`, `status`
- Relacije: `belongsTo(Lobby)`, `belongsTo(User)`, `belongsTo(Quiz)`

---

## API rute

### Javne rute (bez autentifikacije)

| Metoda | Ruta | Kontroler | Opis |
|--------|------|-----------|------|
| POST | `/api/register` | AuthController@register | Registracija novog korisnika |
| POST | `/api/login` | AuthController@login | Prijava korisnika (blokira admin) |
| POST | `/api/admin/login` | AuthController@adminLogin | Prijava administratora |
| GET | `/api/quizzes` | QuizController@index | Popis kvizova (s filterima: category, difficulty, search, is_active) |
| GET | `/api/quizzes/{id}` | QuizController@show | Detalji kviza s pitanjima |
| GET | `/api/questions` | QuestionController@index | Popis pitanja (filter: quiz_id, type) |
| GET | `/api/options` | OptionController@index | Popis opcija (filter: question_id) |
| GET | `/api/external/premade-quizzes` | ApiController@premadeQuizzes | Gotovi kvizovi iz Open Trivia Database |
| GET | `/api/external/premade-quizzes/{quizId}` | ApiController@getPremadeQuizQuestions | Pitanja za gotovi kviz |

### Zaštićene rute (potrebna autentifikacija)

| Metoda | Ruta | Kontroler | Opis |
|--------|------|-----------|------|
| POST | `/api/logout` | AuthController@logout | Odjava i brisanje tokena |
| GET | `/api/user` | AuthController@user | Dohvat podataka prijavljenog korisnika |
| POST | `/api/quizzes` | QuizController@store | Kreiranje kviza (multipart/form-data za sliku) |
| PUT | `/api/quizzes/{id}` | QuizController@update | Ažuriranje kviza (kreator ili admin) |
| DELETE | `/api/quizzes/{id}` | QuizController@destroy | Brisanje kviza (kreator ili admin) |
| POST | `/api/questions` | QuestionController@store | Kreiranje pitanja |
| PUT | `/api/questions/{id}` | QuestionController@update | Ažuriranje pitanja |
| DELETE | `/api/questions/{id}` | QuestionController@destroy | Brisanje pitanja |
| POST | `/api/quizzes/{id}/questions/reorder` | QuestionController@reorder | Promjena redoslijeda pitanja |
| POST | `/api/options` | OptionController@store | Kreiranje opcije odgovora |
| PUT | `/api/options/{id}` | OptionController@update | Ažuriranje opcije |
| DELETE | `/api/options/{id}` | OptionController@destroy | Brisanje opcije |
| POST | `/api/quizzes/{id}/start` | AttemptController@start | Pokretanje pokušaja kviza |
| POST | `/api/attempts/answer` | AttemptController@submitAnswer | Slanje odgovora na pitanje |
| POST | `/api/attempts/{id}/submit` | AttemptController@submit | Završetak i slanje kviza |
| GET | `/api/attempts/{id}/results` | AttemptController@results | Detaljni rezultati pokušaja |
| GET | `/api/quizzes/{id}/attempts` | AttemptController@userAttempts | Prethodni pokušaji korisnika za kviz |
| POST | `/api/upload/image` | FileUploadController@uploadImage | Upload slike (maks. 2MB) |
| POST | `/api/upload/pdf` | FileUploadController@uploadPdf | Upload PDF-a (maks. 5MB) |

### Multiplayer rute (potrebna autentifikacija)

| Metoda | Ruta | Kontroler | Opis |
|--------|------|-----------|------|
| GET | `/api/lobbies` | LobbyController@index | Popis aktivnih predvorja |
| POST | `/api/lobbies` | LobbyController@store | Kreiranje predvorja |
| GET | `/api/lobbies/{id}` | LobbyController@show | Detalji predvorja |
| POST | `/api/lobbies/join/{code}` | LobbyController@join | Pridruživanje predvorju putem koda |
| POST | `/api/lobbies/{id}/start` | LobbyController@start | Pokretanje igre (samo domaćin) |
| POST | `/api/lobbies/{id}/leave` | LobbyController@leave | Napuštanje predvorja |
| GET | `/api/lobbies/{lobbyId}/game-state` | MultiplayerGameController@getGameState | Trenutno stanje igre i ljestvica |
| POST | `/api/lobbies/{lobbyId}/submit-answer` | MultiplayerGameController@submitAnswer | Slanje odgovora u multiplayer modu |
| POST | `/api/lobbies/{lobbyId}/complete` | MultiplayerGameController@completeGame | Završetak igre za igrača |

### Admin rute (potrebna admin uloga)

| Metoda | Ruta | Kontroler | Opis |
|--------|------|-----------|------|
| GET | `/api/admin/users` | Admin\UserController@index | Popis korisnika (pretraga, filter po tipu, paginacija) |
| POST | `/api/admin/users` | Admin\UserController@store | Kreiranje novog korisnika |
| GET | `/api/admin/users/{id}` | Admin\UserController@show | Detalji korisnika sa statistikom |
| PUT | `/api/admin/users/{id}` | Admin\UserController@update | Ažuriranje korisnika |
| DELETE | `/api/admin/users/{id}` | Admin\UserController@destroy | Brisanje korisnika (ne može obrisati sebe) |

---

## Frontend rute

| Putanja | Ime | Komponenta | Opis |
|---------|-----|-----------|------|
| `/` | Home | Home.vue | Početna stranica s hero sekcijom i karticama značajki |
| `/login` | Login | Login.vue | Prijava — split layout s formom i testimonial panelom |
| `/register` | Register | Register.vue | Registracija — centrirana kartica s izborom uloge |
| `/admin/login` | AdminLogin | AdminLogin.vue | Admin prijava portal |
| `/quizzes` | QuizList | QuizList.vue | Pregled svih kvizova s filterima (pretraga, kategorija, težina) |
| `/quizzes/create` | QuizCreate | QuizCreate.vue | Kreiranje kviza — 2 stupca: sadržaj + postavke |
| `/quizzes/:id` | QuizDetail | QuizDetail.vue | Detalji kviza prije rješavanja — hero blok + bočna traka |
| `/quizzes/:id/take` | QuizTaking | QuizTaking.vue | Rješavanje kviza — timer, navigacija, bodovanje, rezultati |
| `/admin/users` | UserManagement | UserManagement.vue | Admin nadzorna ploča — upravljanje korisnicima |
| `/lobby` | Lobby | Lobby.vue | Multiplayer predvorje — kreiranje/pridruživanje |
| `/lobby/:lobbyId/game` | MultiplayerGame | MultiplayerGame.vue | Multiplayer igra — ljestvica uživo |

### Zaštita ruta
- **requiresAuth** — preusmjerava na `/login` ako korisnik nije prijavljen
- **requiresGuest** — preusmjerava na `/` ako je korisnik već prijavljen
- **requiresAdmin** — provjerava admin ulogu, preusmjerava na `/quizzes` ako nije admin

---

## Ključne značajke

### Sustav kvizova
- Kreiranje kvizova s naslovom, opisom (rich text), slikom, kategorijom, težinom i trajanjem
- 3 tipa pitanja: **višestruki izbor**, **točno/netočno**, **kratki odgovor**
- Konfigurirani bodovi po pitanju
- Drag-and-drop promjena redoslijeda pitanja (SortableJS)
- Klijentsko filtriranje po kategoriji, težini i pretrazi

### Rješavanje kvizova
- Odbrojavanje s vizualnim upozorenjima (zeleno ≥5min, žuto 1-5min, crveno <1min)
- Navigacija pitanjima (prethodno/dalje/preskoči)
- Vizualni progress bar
- Automatsko slanje po isteku vremena
- Povratna informacija nakon svakog odgovora
- Rezultati s postotkom, pregledom odgovora i opcijom ispisa

### Multiplayer
- Domaćin kreira predvorje i odabire kviz
- Igrači se pridružuju putem 6-znakovnog koda
- Ljestvica uživo s bodovima i napretkom
- Podržava do 20 igrača po predvorju
- Polling svake 2 sekunde za ažuriranje stanja

### Eksterni kvizovi
- Integracija s Open Trivia Database API-jem
- 8 kategorija gotovih kvizova (Opće znanje, Znanost, Povijest, Geografija, Sport, Računarstvo)

### Admin nadzorna ploča
- CRUD operacije nad korisnicima
- Pretraga po imenu, korisničkom imenu, emailu
- Filtriranje po ulozi
- Statistika (broj kvizova, pokušaja)
- Paginacija

---

## Dizajn sustav

### Fontovi
- **Space Grotesk** — naslovi, statistike, brojevi
- **Lato** — tekst, gumbi, oznake

### Boje
- Primarna (mint): `#78c2ad`
- Sekundarna (roza): `#f3969a`
- Pozadina: `#f4f8f6`
- Tamna (app bar): `#1f2d2b`
- Tekst: `#1d2b28`

### Komponente
- `.qa-card` — kartica s sjenom i zaobljenim rubovima
- `.btn-mint` / `.btn-outline-mint` — gumbi
- `.qa-input` / `.qa-select` — polja za unos
- `.pill-easy` / `.pill-medium` / `.pill-hard` — oznake težine
- `.pill-student` / `.pill-teacher` / `.pill-admin` — oznake uloga

---

## Autentifikacija

Sustav koristi **Laravel Sanctum** za token-baziranu autentifikaciju:
1. Korisnik se registrira ili prijavljuje
2. Server vraća Bearer token
3. Frontend sprema token u `localStorage`
4. Svaki zahtjev šalje token u `Authorization` zaglavlju putem Axios interceptora
5. Pri odjavi, token se briše lokalno i na serveru

---

## Autorica

**Martina Crnogorac** — [martina.crnogorac@fpmoz.sum.ba](mailto:martina.crnogorac@fpmoz.sum.ba)
