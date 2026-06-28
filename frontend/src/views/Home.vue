<template>
  <div class="home-page">
    <!-- Light header -->
    <header class="home-header">
      <router-link to="/" class="home-brand">
        <div class="brand-icon-sm">Q</div>
        <span class="brand-name">QuizApp</span>
      </router-link>
      <nav class="home-nav">
        <router-link to="/quizzes" class="home-link">Pregled</router-link>
        <router-link to="/lobby" class="home-link">Multiplayer</router-link>
      </nav>
      <div class="home-header-spacer"></div>
      <router-link v-if="!isAuthenticated" to="/login" class="btn-outline-mint" style="padding:9px 16px; font-size:13.5px;">Prijava</router-link>
      <router-link v-if="!isAuthenticated" to="/register" class="btn-mint" style="padding:9px 17px; font-size:13.5px;">Započni</router-link>
      <router-link v-if="isAuthenticated" to="/quizzes" class="btn-mint" style="padding:9px 17px; font-size:13.5px;">Nadzorna ploča</router-link>
    </header>

    <!-- Hero -->
    <section class="hero-section">
      <div class="hero-grid two-col-layout">
        <div class="hero-left">
          <div class="hero-pill pill pill-md pill-student">
            <span style="color:var(--accent);">●</span> Multiplayer uživo + 4.000 kviz pitanja
          </div>
          <h1 class="hero-h1">
            Kvizovi koji zaista djeluju <span class="mint">živahno.</span>
          </h1>
          <p class="hero-lead">
            Kreirajte bogate kvizove, izazovite sobu u stvarnom vremenu i pratite svaki rezultat. Za studente, profesore i sve koji vole dobro pitanje.
          </p>
          <div class="hero-ctas">
            <router-link to="/register" class="btn-mint glow" style="padding:14px 26px; font-size:15px;">Započni besplatno</router-link>
            <router-link to="/quizzes" class="btn-outline-mint" style="padding:14px 24px; font-size:15px;">Pregledaj kvizove →</router-link>
          </div>
          <div class="hero-stats">
            <div class="hero-stat">
              <div class="stat-num">12k+</div>
              <div class="stat-label">Kreiranih kvizova</div>
            </div>
            <div class="hero-stat">
              <div class="stat-num">98%</div>
              <div class="stat-label">Završi svoj kviz</div>
            </div>
            <div class="hero-stat">
              <div class="stat-num">3 uloge</div>
              <div class="stat-label">Student · Profesor · Admin</div>
            </div>
          </div>
        </div>

        <div class="hero-right">
          <div class="hero-glow"></div>
          <div class="hero-device qa-card" style="box-shadow: var(--shadow-hero); border-radius: 20px; overflow: hidden; position: relative;">
            <div class="device-bar">
              <div class="traffic-dots">
                <span style="background:#ff7851;"></span>
                <span style="background:#f9c74f;"></span>
                <span style="background:#56cc9d;"></span>
              </div>
              <span class="device-title">Glavni gradovi · Pitanje 3 / 10</span>
            </div>
            <div style="padding:24px;">
              <div class="qa-progress" style="margin-bottom:20px;">
                <div class="qa-progress-fill" style="width:30%;"></div>
              </div>
              <div style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:21px; margin-bottom:18px; line-height:1.3;">Koji je glavni grad Australije?</div>
              <div style="display:grid; gap:10px;">
                <div class="device-option">Sydney</div>
                <div class="device-option selected">Canberra <span>✓</span></div>
                <div class="device-option">Melbourne</div>
              </div>
              <div style="display:flex; justify-content:space-between; margin-top:20px; align-items:center;">
                <span style="font-size:13px; color:var(--faint); font-weight:700;">+10 bodova</span>
                <span style="background:var(--accent); color:#fff; font-weight:700; font-size:13.5px; padding:9px 18px; border-radius:10px;">Dalje →</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Feature Cards -->
    <section class="features-section">
      <div class="features-grid three-col-grid">
        <div v-for="f in features" :key="f.title" class="qa-card" style="padding:26px;">
          <div class="feature-icon" :style="{ background: f.tint, color: f.ink }">{{ f.icon }}</div>
          <div style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:18px; margin-bottom:8px;">{{ f.title }}</div>
          <div style="font-size:14.5px; line-height:1.6; color:var(--muted);">{{ f.body }}</div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const isAuthenticated = computed(() => !!localStorage.getItem('auth_token'))

const features = [
  {
    icon: '✏️',
    tint: '#e8f4ef',
    ink: '#2f6b5c',
    title: 'Kreirajte na svoj način',
    body: 'Višestruki izbor, točno/netočno, kratki odgovor s bogatim tekstom, slikama, bodovima i povlačenjem za promjenu redoslijeda.',
  },
  {
    icon: '🎮',
    tint: '#fdeef0',
    ink: '#c5566a',
    title: 'Igrajte uživo, zajedno',
    body: 'Kreirajte predvorje, podijelite 6-znakovni kod i natječite se na ljestvici u stvarnom vremenu s do 20 igrača.',
  },
  {
    icon: '⏱️',
    tint: '#fbf2dd',
    ink: '#9c7212',
    title: 'Pobijedite vrijeme',
    body: 'Odbrojavanje, automatsko slanje, trenutna povratna informacija na svaki odgovor i rezultati za ispis kad završite.',
  },
]
</script>

<style scoped>
.home-page {
  min-height: 100vh;
  background: var(--page-bg-alt);
}

/* Header */
.home-header {
  padding: 18px 26px;
  display: flex;
  align-items: center;
  gap: 18px;
  border-bottom: 1px solid var(--line2);
  background: var(--surface);
}
.home-brand {
  display: flex;
  align-items: center;
  gap: 9px;
  margin-right: auto;
  text-decoration: none;
}
.brand-icon-sm {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  background: var(--accent);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
}
.brand-name {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 17px;
  color: var(--ink);
}
.home-nav { display: flex; gap: 6px; }
.home-link {
  font-size: 14px;
  font-weight: 700;
  color: var(--muted);
  text-decoration: none;
  padding: 6px 10px;
  border-radius: 8px;
  transition: color .15s;
}
.home-link:hover { color: var(--ink); }
.home-header-spacer { flex: 1; }

/* Hero */
.hero-section {
  max-width: 1120px;
  margin: 0 auto;
  padding: 74px 32px 56px;
}
.hero-grid {
  display: grid;
  grid-template-columns: 1.05fr .95fr;
  gap: 48px;
  align-items: center;
}
.hero-pill { margin-bottom: 22px; }
.hero-h1 {
  font-size: 54px;
  line-height: 1.05;
  letter-spacing: -.02em;
  margin-bottom: 20px;
  color: #16241f;
}
.hero-h1 .mint { color: var(--accent); }
.hero-lead {
  font-size: 17.5px;
  line-height: 1.6;
  color: var(--muted);
  margin: 0 0 30px;
  max-width: 480px;
}
.hero-ctas { display: flex; gap: 12px; align-items: center; }
.hero-stats {
  display: flex;
  gap: 34px;
  margin-top: 42px;
}
.stat-num {
  font-family: 'Space Grotesk', sans-serif;
  font-weight: 700;
  font-size: 26px;
}
.stat-label {
  font-size: 13px;
  color: var(--faint);
  font-weight: 700;
}

/* Hero device */
.hero-right { position: relative; }
.hero-glow {
  position: absolute;
  inset: -18px;
  background: radial-gradient(120% 120% at 70% 10%, #d7efe6 0%, rgba(215,239,230,0) 60%);
  border-radius: 30px;
}
.device-bar {
  height: 46px;
  background: var(--dark);
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 0 16px;
}
.traffic-dots {
  display: flex;
  gap: 6px;
}
.traffic-dots span {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}
.device-title {
  color: var(--dark-muted);
  font-size: 12px;
  font-weight: 700;
}
.device-option {
  padding: 14px 16px;
  border: 1px solid var(--line);
  border-radius: 12px;
  font-weight: 700;
  color: var(--body-text);
}
.device-option.selected {
  border: 2px solid var(--accent);
  background: var(--mint-soft2);
  color: #2f6b5c;
  display: flex;
  justify-content: space-between;
}

/* Features */
.features-section {
  max-width: 1120px;
  margin: 0 auto;
  padding: 20px 32px 80px;
}
.features-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}
.feature-icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 16px;
}

@media (max-width: 880px) {
  .hero-grid { grid-template-columns: 1fr; }
  .hero-h1 { font-size: 38px; }
  .hero-right { display: none; }
}
@media (max-width: 600px) {
  .home-nav { display: none; }
  .hero-section { padding: 40px 20px 40px; }
  .hero-h1 { font-size: 32px; }
  .hero-stats { flex-wrap: wrap; gap: 20px; }
  .hero-ctas { flex-wrap: wrap; }
}
</style>
