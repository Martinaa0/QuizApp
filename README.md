# Handoff: QuizApp — Full UI (11 screens)

## Overview
QuizApp is a quiz platform for **students, teachers, and admins**. Users browse and take quizzes (with a live timer, scoring, and results), teachers build quizzes, people play together in real-time multiplayer lobbies, and admins manage users. This package documents the **complete front-end design** for 11 screens, plus the interactions and state that drive them.

The visual direction is **"Bootswatch Minty, elevated"** — it keeps the Minty mint/pink palette but uses a refined type system (Space Grotesk + Lato), softer surfaces, a dark-teal app bar, and a consistent card/badge/shadow system instead of stock Bootstrap components.

## About the Design Files
The file in this bundle — `QuizApp.dc.html` — is a **design reference created in HTML**. It is an interactive prototype showing the intended look, layout, and behavior. **It is not production code to copy directly.**

Your task is to **recreate these designs in the target codebase** using its established patterns. Per the project brief the stack is:
- **Frontend:** Vue 3 + Vite, Bootstrap 5 (Bootswatch Minty), Axios, TinyMCE (rich text), SortableJS (drag-and-drop)
- **Backend:** Laravel 12 (PHP 8.2+), Sanctum token auth, SQLite/MySQL

Build each screen as Vue 3 components (SFCs). You may keep Bootstrap's grid/utilities, but the **elevated visual treatment in this prototype should win** wherever it differs from stock Bootstrap (custom cards, badges, spacing, the dark app bar, the Space Grotesk headings). Treat the exact hex/spacing/type values below as the source of truth.

> Note: the prototype's left sidebar ("DESIGN PREVIEW" rail) is **scaffolding to navigate between screens in the mockup** — it is NOT part of the product. Ignore it when building. The real app chrome is the **dark top app bar** documented under "Shared chrome".

### How to run the prototype
`QuizApp.dc.html` needs `support.js` (included in this folder) sitting next to it. Open `QuizApp.dc.html` in a browser. Use the left rail to jump between screens. The timer, quiz flow, results scoring, and Browse filters are all live.

## Fidelity
**High-fidelity (hifi).** Final colors, typography, spacing, radii, shadows, and interactions are all specified. Recreate the UI faithfully using the codebase's Vue + Bootstrap setup; the values in this README are exact.

---

## Design Tokens

### Typography
- **Display / headings:** `'Space Grotesk'` (Google Fonts), weights 400/500/600/700. Used for h1/h2, numbers/stats, brand wordmark, score values.
- **Body / UI:** `'Lato'` (Google Fonts), weights 400/700/900. Used for paragraphs, labels, buttons, table text.
- Google Fonts import:
  `https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Lato:wght@400;700;900&display=swap`

**Type scale (px / weight):**
- Hero H1: 54 / 700, line-height 1.05, letter-spacing −0.02em (Space Grotesk)
- Page H1: 28–32 / 700 (Space Grotesk)
- Question H2: 25 / 700, line-height 1.3 (Space Grotesk)
- Section H2: 19 / 700 (Space Grotesk)
- Card title: 17–18 / 700 (Space Grotesk)
- Stat numbers: 20–32 / 700 (Space Grotesk)
- Lead paragraph: 16–17.5 / 400, line-height 1.6 (Lato)
- Body: 14–15 / 400 (Lato)
- Button / nav label: 13.5–15 / 700 (Lato)
- Meta / small: 12.5–13.5 / 700 (Lato)
- Micro badge: 11.5–12 / 700 (Lato)
- Uppercase eyebrow label: 10.5–11.5 / 700, letter-spacing 0.12–0.14em, `text-transform:uppercase`

### Colors
**Brand / accent (themeable):**
- Primary mint `--accent`: **#78c2ad** (alt swatches: #56cc9d, #5b9fd6, #b07cc6, #e0a458)
- Secondary pink `--accent2`: **#f3969a** (alt swatches: #ff7851, #f5b740, #86d0b8)

**Semantic:**
- Success (text/green): #3aa17e, #2f8a64, #56cc9d
- Danger / orange-red: #ff7851 (solid), #c5512a (text)
- Warning / amber: #cf9526 (text), #9c7212 (deep text)
- Info / blue (CS category, Admin role): #5b7fd6

**Neutrals (warm, mint-tinted):**
- Ink / primary text: #1d2b28
- Body text: #3a4b46
- Muted text: #5b6b66, #6c7d77
- Faint text / placeholder: #859590, #9aaaa4, #a3b2ac
- Page background: #f4f8f6 (app), #fbfdfc (home/register)
- Card / surface: #ffffff
- Soft mint surfaces: #e8f4ef, #eef8f4, #e7f5ee
- Hairline borders: #e7efeb (default), #eef4f1, #f0f5f3, #e2ece8 (control borders), #dde7e3 (input borders)
- Dark surface (app bar, hero card chrome, lobby/results panels): #1f2d2b; gradient pair #2c423d; inner ring bg #243532
- Dark-surface text: #eaf3f0 (primary), #9fbab3 / #bcd2cc (muted)

**Badge / pill tints (bg / text / border):**
- Easy: #e7f5ee / #2f8a64 / #cfeede
- Medium: #fbf2dd / #9c7212 / #f1e2b8
- Hard: #ffe9e2 / #c5512a / #ffd6c7
- Role · Student: #e8f4ef / #2f6b5c
- Role · Teacher: #fdeef0 / #c5566a
- Role · Admin: #eef2fd / #5b7fd6

### Spacing
Common paddings: control 10–13px, card 16–30px, section gutters 26–32px. Grid gaps: cards 20px, form fields 14–18px, chips 6–8px. Page content max-widths: 1160px (browse/admin), 1120px (home), 1080px (create), 1020px (detail), 900px (results), 760–980px (take/lobby/game).

### Radius (themeable via `--radius`, default 16px, range 4–24)
- Cards / large surfaces: `var(--radius)` = 16px (panels often 18–20px)
- Buttons: 10–13px
- Inputs / dropdown controls: 10–12px
- Option key squares / nav items: 8–10px
- Pills / chips / avatars: 999px / 50%

### Shadows
- Card resting: `0 1px 2px rgba(31,45,43,.04)`
- Elevated card: `0 14px 36px -28px rgba(31,45,43,.35)`
- Hero device card: `0 26px 60px -28px rgba(31,45,43,.4)`
- Dropdown menu: `0 14px 32px -14px rgba(31,45,43,.35)`
- Primary button glow: `0 8px 20px rgba(120,194,173,.35)`

### Theme implementation
The prototype sets three CSS custom properties on the root (`--accent`, `--accent2`, `--radius`) and every accent-colored element reads them with a literal fallback, e.g. `color: var(--accent, #78c2ad)`, `border-radius: var(--radius, 16px)`. Recreate as Bootstrap/SCSS variables or CSS custom properties so the mint primary and corner radius are swappable in one place. Default values: `--accent:#78c2ad; --accent2:#f3969a; --radius:16px;`

---

## Shared chrome

**Dark top app bar** (logged-in screens: browse, detail, take, results, create, lobby, game, admin)
- Height 60px, sticky top, background #1f2d2b, text #eaf3f0, horizontal padding 26px, items gap 20px.
- Left: 26px rounded-8px mint tile with "Q" (Space Grotesk 700) + "QuizApp" wordmark (Space Grotesk 600, 16px).
- Nav links: Browse (active — bg rgba(255,255,255,.12), white), Create, Multiplayer (inactive — #a9c4bd). 13.5px/700, padding 7×12, radius 8.
- Right: search field (230px, bg rgba(255,255,255,.09), 1px rgba(255,255,255,.12) border, radius 10, placeholder "Search quizzes…") + 34px circular avatar (bg #f3969a, initials "AM", #5a1f24).

---

## Screens / Views

### 1. Home / Landing  (`screen: 'home'`)
- **Purpose:** Market the product; route to sign-up / browse.
- **Layout:** Own light header (white, bottom hairline) with brand left, "Browse"/"Multiplayer" links, outline "Sign in" + solid "Get started" buttons. Then a 2-column hero (1.05fr / 0.95fr, gap 48px, max-width 1120px, padding 74px top).
- **Left column:** pill badge ("● Live multiplayer + 4,000 trivia questions", bg #e8f4ef, #2f6b5c); H1 54px with "alive." in mint; lead paragraph (#5b6b66, max 480px); two CTAs (solid mint "Start for free" with glow shadow, outline "Browse quizzes →"); a row of 3 stats (12k+ / 98% / 3 roles).
- **Right column:** a faux quiz-card preview inside a device-style frame — dark 46px title bar with 3 traffic-light dots + "World Capitals · Question 3 / 10", a 30%-filled progress bar, a question, 3 options (the correct one outlined in mint with a ✓), "+10 points" and a "Next →" pill. Behind it a soft radial mint glow.
- **Below hero:** 3 feature cards (grid 3×, gap 20). Each: 42px rounded-12 tinted icon tile, Space Grotesk 18 title, #5b6b66 body. Content:
  - "Build it your way" — Multiple choice, true/false, short answer with rich text, images, points.
  - "Play live, together" — lobby + 6-char code + real-time leaderboard.
  - "Beat the clock" — countdown timers, auto-submit, instant feedback, printable results.

### 2. Sign in / Login  (`screen: 'login'`)
- **Purpose:** Authenticate existing users.
- **Layout:** 2-column split (1fr / 1fr), full height.
- **Left (form, max 380px centered):** brand; H1 "Welcome back"; "Email or username" field (with "@" affix); "Password" field (• affix, dots); row "Remember me" / "Forgot password?" (mint); full-width solid mint "Sign in"; "or" divider; outline "Continue with Google"; footer "New here? Create an account" (link → register); tiny "Admin? Admin portal →" (link → admin).
- **Right (brand panel):** background #1f2d2b with two soft translucent circles (mint top-right, pink bottom-left); a pull-quote (Space Grotesk 30/700), supporting copy (#9fbab3), and an attribution row (42px pink avatar "RT" + "Rosa Tan · Teacher · Lincoln High").
- Inputs here are display-only placeholders in the mock; implement as real fields.

### 3. Create account / Register  (`screen: 'register'`)
- **Purpose:** New-user sign-up.
- **Layout:** Centered card (max 460px) on #fbfdfc; card padding 38px, radius 20, elevated shadow.
- **Fields:** Full name + Username (2-col), Email, **"I am a…" role picker** (2 segmented options Student[selected, mint outline + #eef8f4]/Teacher), Password. Full-width solid mint "Create account". Footer "Already have one? Sign in" (→ login).

### 4. Browse quizzes  (`screen: 'browse'`)  — *fully interactive in prototype*
- **Purpose:** Discover quizzes; filter/search.
- **Header row:** H1 "Browse quizzes" + subcount "{N} quizzes · find one to take or remix"; right: solid mint "+ New quiz" (→ create).
- **Filter bar** (white card, radius 14, padding 14, flex-wrap, gap 12):
  - **Search input** (flex:1, min 200px, bg #f4f8f6, radius 10, ⌕ icon) — filters by title OR category substring (case-insensitive).
  - **Category dropdown** — button shows current label ("Category" when none) + ▾; opens a menu (absolute, white, radius 12, dropdown shadow, min 200px) listing **All, Geography, Computer Science, Science, History, Sports, General Knowledge**. Selected row highlighted #e8f4ef/#2f6b5c.
  - **Status dropdown** — same pattern (right-aligned menu, min 170px): **All, New, Popular, Completed**.
  - **Difficulty chips** — Easy / Medium / Hard pills (radius 999). Click toggles; active chip becomes solid (its text color fills the bg, white label).
  - **Clear ✕** (text button, #c5512a) — visible only when any filter active; resets all filters + search.
- **Grid:** 3-column cards (gap 20). **Card:**
  - 118px cover band with a per-category gradient + a large emoji glyph in a translucent rounded tile (bottom-left) + a difficulty badge (top-right, white 92% bg, difficulty text color).
  - Body: uppercase category eyebrow (category color); Space Grotesk 17 title; footer row (top hairline) "◷ {duration}", "❓ {n} Q", author (right).
  - Whole card is clickable → quiz detail.
- **Seed quizzes (title · category · difficulty · #Q · duration · author · status):**
  1. World Capitals Challenge · Geography · Medium · 15 · 10 min · R. Tan · Popular
  2. JavaScript Fundamentals · Computer Science · Hard · 25 · 20 min · D. Cole · Popular
  3. The Human Body · Science · Easy · 12 · 8 min · M. Ito · New
  4. Ancient Civilizations · History · Medium · 18 · 15 min · L. Park · New
  5. World Cup Trivia · Sports · Easy · 10 · 6 min · J. Diaz · Completed
  6. Famous Paintings · General Knowledge · Hard · 20 · 12 min · S. Vogel · Popular
- Category gradient covers: Geography #9fe0cc→#78c2ad, CS #a9c2f2→#7f9fe0, Science #f7b6b9→#f3969a, History #e0c3a9→#cda37f, Sports #ffd0a9→#ffae7f, General #c3b6f7→#a596e0.

### 5. Quiz detail  (`screen: 'detail'`)
- **Purpose:** Preview a quiz before taking / hosting it.
- **Layout:** Breadcrumb ("Browse / Geography / World Capitals"); 2-col (1.4fr / 1fr, gap 28, max 1020).
- **Left:** 220px gradient hero block with big emoji; badge row (Geography / Medium / ◷ 10 min); H1 32px; description paragraph; a row of 3 stat tiles (15 Questions / 150 Total points / 1.2k Plays).
- **Right (sticky card, top 80px):** author row (40px avatar + "Rosa Tan · Teacher · 24 quizzes"); a #f4f8f6 facts box (Questions 15, Time limit 10 min, Total points 150, Attempts 1,204); solid mint **"Start quiz →"** (→ take) with glow; outline **"Play with friends"** (→ lobby).

### 6. Take quiz  (`screen: 'take'`)  — *fully interactive: live timer + scoring*
- **Purpose:** Answer questions under a timer.
- **Layout:** Single column, max 760, padding-top 26.
- **Top row:** "Question {n} of {total}" (current/total) + **timer pill** (◷ m:ss) whose colors change by time remaining (see Interactions).
- **Progress bar:** 8px track #eef4f1, fill mint width = (qIndex+1)/total, `transition: width .3s`.
- **Question card** (white, radius 18, padding 30, elevated shadow): top row = question-type badge (#e8f4ef/#2f6b5c) + "+{points} points"; H2 25 question text; **options grid** (gap 12). Each option is a button: 28px rounded key square (A/B/C/D) + label + (when selected) a ● on the right; selected state = #eef8f4 bg, 2px mint border, #2f6b5c text, mint key tile.
- **Footer:** "← Previous" (outline, disabled/no-op on Q1), then "Skip" (outline) + primary button labeled **"Next →"** on Qs 1..n-1 and **"Submit ✓"** on the last question (→ results).
- **Question dots:** row of numbered 30px square buttons under the card. Current = solid mint/white; answered = #d6efe5/#2f8a64; unanswered = #f1f5f3/#aab8b3. Click a dot to jump to that question.
- **Seed questions (all multiple-choice, 10 pts; correct in bold):**
  1. Which river flows through the Egyptian capital, Cairo? — The Tigris / The Euphrates / **The Nile** / The Congo
  2. What is the capital of Australia? — Sydney / **Canberra** / Melbourne / Perth
  3. Mount Everest sits on the border of Nepal and which country? — India / **China** / Bhutan / Pakistan
  4. By total area, what is the largest desert on Earth? — The Sahara / The Gobi / **The Antarctic** / The Arabian
  5. The Amazon River carries more water than any other. Most of it lies in which country? — **Brazil** / Peru / Colombia / Venezuela

### 7. Results  (`screen: 'results'`)  — *computed live from answers*
- **Purpose:** Show score + per-question review.
- **Hero panel** (dark gradient #1f2d2b→#2c423d, radius 20, padding 34, text #eaf3f0): left a **128px score ring** — `conic-gradient(var(--accent) 0% {scorePct}%, rgba(255,255,255,.14) {scorePct}% 100%)` with an inner #243532 disc showing "{scorePct}%" + "SCORE"; right column: "QUIZ COMPLETE" eyebrow, H1 "Nicely done, Alex!", "You scored {correct} of {total} on World Capitals Challenge.", and 4 stats (Correct {correct} #56cc9d / Missed {missed} #ff9a7f / Points {earned} / Time 6:42).
- **Answer review:** header "Answer review" + "⎙ Print" (outline) + "More quizzes" (mint → browse). Then one row per question: 4px left bar (green if correct / orange if wrong), 30px mark tile (✓ green tint / ✕ orange tint), question text + "Your answer: {chosen or 'No answer'}", and right "+{pts}" / "+0".
- All numbers derive from the Take-quiz answers (unanswered counts as wrong, "No answer").

### 8. Create quiz (teacher)  (`screen: 'create'`)
- **Purpose:** Author a quiz.
- **Header:** H1 "Create a quiz" + "Draft · saved 2 min ago"; right: outline "Save draft" + solid mint "Publish".
- **Layout:** 2-col (1fr / 320px, gap 24, align start).
- **Left column:**
  - **Title + description card:** large editable title (Space Grotesk 22, bottom border); **rich-text editor** mock — a toolbar strip (B / I / U / "" / • / 🔗 buttons on #f8fbfa) over an editable body. *Implement with TinyMCE per stack.*
  - **Questions card:** header "Questions · {n}" + "⤢ Drag to reorder"; list of question rows — each: ⠿ drag handle (grab cursor), 26px number tile, question text + meta ("4 options · 10 pts" etc.), and a type pill (Multiple choice #e8f4ef/#2f6b5c, True/False #fbf2dd/#9c7212, Short answer #fdeef0/#c5566a). Then a dashed **"+ Add question"** button. *Implement reordering with SortableJS.*
  - Seed: Q1 "Who painted the Mona Lisa?" (Multiple choice), Q2 "The Sistine Chapel ceiling was painted by Raphael." (True/False), Q3 "Name the city where the Renaissance began." (Short answer).
- **Right column:**
  - **Cover image card:** dashed drop zone with diagonal-stripe bg, ↑ icon, monospace "drop image · max 2MB".
  - **Settings card:** Category select ("General Knowledge" ▾); Difficulty 3-segment (Easy / **Medium**[selected] / Hard); Time-limit field ("15").

### 9. Multiplayer lobby  (`screen: 'lobby'`)
- **Purpose:** Host gathers players before starting.
- **Layout:** 2-col (1fr / 1.2fr, gap 24, max 980, align start).
- **Left (dark gradient card):** "JOIN CODE" eyebrow; **6 code tiles** (46×58, each one char — seed "7 K 9 M 2 X", Space Grotesk 28); full-width "⧉ Copy invite link" ghost button; divider; quiz summary ("World Capitals Challenge · 15 questions · 10 min · Hosted by you"); two stats (Players 5 / 12, Status "Waiting" #56cc9d).
- **Right:** header "Players in lobby · 5 joined"; player rows (white card, 38px colored avatar with initials, name + @handle, status pill — Host #e8f4ef/#2f6b5c, Ready #e7f5ee/#2f8a64, Joining… #fbf2dd/#9c7212); a dashed "Waiting for more players…" row; full-width solid mint **"Start game · 5 players"** (→ game) with glow.
- Seed players: You/@alexm (Host), Priya Nair/@priya (Ready), Diego Cole/@dcole (Ready), Mei Ito/@meiito (Ready), Sam Vogel/@svogel (Joining…).

### 10. Live game (leaderboard)  (`screen: 'game'`)
- **Purpose:** Real-time standings during multiplayer play.
- **Top row:** live dot (#ff7851 with halo) + "Live · Question 7 / 15"; red timer pill "◷ 0:18".
- **Progress bar** at 46%. H2 "Live leaderboard".
- **Leaderboard rows** (gap 11): rank number (medal colors gold #f5b740 / silver #b8c2c9 / bronze #cd8b53 for top 3, else #aab8b3), 40px colored avatar, name (+ a "You" pill on the current player's row), a thin progress bar (per-player %), and right-aligned score + "{answered} / 7". The current user's row is highlighted (#eef8f4 bg, mint border).
- Seed (rank order): Priya Nair 680 (92%, 7/7), **You** 640 (86%, 7/7), Diego Cole 590 (79%, 6/7), Mei Ito 520 (70%, 6/7), Sam Vogel 410 (55%, 5/7).

### 11. Admin · User management  (`screen: 'admin'`)
- **Purpose:** Admin CRUD over users.
- **Header:** H1 "User management" + "Admin dashboard · manage accounts, roles, and activity".
- **Stat cards** (4-col, gap 16): Total users 412, Teachers 38, Quizzes 126, Attempts (7d) 2.1k — each a small tinted icon tile + label + Space Grotesk 26 value.
- **Table card:** toolbar = search field ("Search by name, username or email…") + "All roles ▾" filter + solid mint "+ Add user". Column header row (#fafcfb): User / Role / Quizzes / Attempts / (actions). Grid columns `2.4fr 1fr 1fr 1fr 80px`.
  - **Row:** 38px colored avatar + name + email; role pill (Student/Teacher/Admin tints above); quizzes count; attempts count; ⋯ actions.
  - Seed: Alex Morgan (Admin, 12, 48), Rosa Tan (Teacher, 24, 6), Diego Cole (Teacher, 9, 14), Mei Ito (Student, 0, 132), Sam Vogel (Student, 0, 87), Priya Nair (Student, 0, 201).
- **Footer:** "Showing 1–6 of 412 users" + pagination ( ‹ 1 2 3 › ; current page solid mint). *Wire to the paginated admin API.*

---

## Interactions & Behavior

- **Screen navigation:** single `screen` value selects the active view. In production this maps to the routes below; in the mock it's the left rail.
- **Take quiz — answer selection:** clicking an option stores `answers[currentIndex] = optionIndex` and re-renders that option as selected. Re-clickable/changeable until submit.
- **Take quiz — navigation:** "Next" increments index (or, on last question, navigates to Results); "Previous" decrements (no-op at 0); "Skip" advances without recording; the numbered dots jump to any index. Progress bar = `(index+1)/total`, animated `width .3s ease`.
- **Take quiz — timer:** a 1-second `setInterval` decrements remaining seconds while on the take screen; display formatted `m:ss` (tabular numerals). **Color thresholds:** ≥ 300s → green (text #3aa17e on #e7f5ee); 60–299s → amber (#cf9526 on #fbf2dd); < 60s → red (#ff7851 on #ffe9e2). On reaching 0, auto-submit → Results.
- **Results — scoring:** `correct = count(answers[i] === question[i].correct)`; `scorePct = round(correct/total*100)`; `earned = sum(points of correct)`; `missed = total − correct`. The ring uses `scorePct` in its conic-gradient; review marks each question correct/incorrect and echoes the chosen option (or "No answer").
- **Browse — filtering:** results = quizzes where (no difficulty filter OR matches) AND (no category OR matches) AND (no status OR matches) AND (no search OR title/category contains search). Count label shows filtered length when any filter is active, else the full catalog count (126). Dropdowns are mutually exclusive (one `openMenu` at a time); selecting an option closes the menu. "Clear" resets all four filters + search.
- **Hover/active states:** buttons darken ~8% on hover; cards lift slightly (use the resting→elevated shadow). Nav items show a soft mint bg when active.
- **Responsive:** target both desktop and mobile. Collapse 2-col layouts (home hero, login split, detail, create, lobby) to single column under ~880px; quiz card grid 3→2→1; admin/leaderboard tables become stacked rows; the dark app bar collapses its center nav into a menu. Min touch target 44px.

## State Management
Local UI state used by the prototype (map to Vue `ref`/`reactive` + Pinia/Vuex where it should persist or come from the API):
- `screen` — active view (replace with Vue Router routes).
- `timeLeft` (seconds), decremented by interval on the take screen.
- `qIndex` — current question index.
- `answers` — map `{ [questionIndex]: optionIndex }`.
- `diff`, `category`, `status`, `search` — Browse filter state.
- `openMenu` — which Browse dropdown is open (`'category' | 'status' | null`).
- Themeable: `accent`, `accent2`, `cardRadius` (CSS custom properties).
- Data fetching: quizzes list, single quiz + questions, submit attempt + answers, lobby + live sessions (poll or websockets), admin users (paginated). See API below.

---

## Backend / Data Models (from project brief — for wiring the UI)

**Models:** User → has many Quizzes, QuizAttempts · Quiz → belongs to User, has many Questions, QuizAttempts · Question (types: multiple_choice, true_false, short_answer) → belongs to Quiz, has many Options, UserAnswers · Option → belongs to Question (`is_correct`) · QuizAttempt (status in_progress/completed) → belongs to User & Quiz, has many UserAnswers · Lobby (states: waiting → starting → in_progress → completed) → belongs to Quiz & Host User, has many GameSessions · GameSession → belongs to Lobby & User, stores answers as JSON.

**Roles:** Student (browse/take/results), Teacher (create/edit quizzes + questions/options), Admin (everything + user management).

**Routes (Vue Router targets for the screens):**
`/` Home · `/login` · `/register` · `/admin/login` · `/quizzes` (Browse) · `/quizzes/create` · `/quizzes/:id` (Detail) · `/quizzes/:id/take` · `/admin/users` · `/lobby` · `/lobby/:lobbyId/game`.

**API:** RESTful, Bearer-token (Sanctum). Public endpoints for browsing; protected for managing/taking quizzes & multiplayer; admin-only for user management. File uploads: images ≤ 2MB, PDFs ≤ 5MB.

## Design Tokens — quick reference
- Fonts: Space Grotesk (display), Lato (body)
- `--accent` #78c2ad · `--accent2` #f3969a · `--radius` 16px
- Ink #1d2b28 · muted #5b6b66/#859590 · borders #e7efeb/#dde7e3 · page #f4f8f6 · dark #1f2d2b
- Difficulty Easy/Medium/Hard, Role Student/Teacher/Admin tints (see Badge tints)
- Shadows: resting `0 1px 2px rgba(31,45,43,.04)`, elevated `0 14px 36px -28px rgba(31,45,43,.35)`

## Assets
- **No raster images** — quiz "covers" are CSS gradients + emoji glyphs; replace with real uploaded cover images in production (the design reserves a 118px band on cards and a 220px hero on detail).
- **Icons** are Unicode/emoji glyphs in the mock (◷ ❓ ⌕ ⧉ ⠿ ✓ ✕ ●). Swap for the codebase's icon set (e.g. Bootstrap Icons) at the same sizes.
- **Fonts:** Google Fonts (Space Grotesk, Lato) — self-host or link.
- **Avatars** are initials on colored discs — keep as a fallback for users without photos.

## Files
- `QuizApp.dc.html` — the interactive design reference (all 11 screens; open in a browser, navigate with the left rail).
- `support.js` — runtime required to open the prototype locally (keep it beside the HTML). Not part of the product.
