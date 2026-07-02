<template>
  <div class="denied-page">
    <div class="qa-card elevated denied-card">
      <div style="font-size:52px; margin-bottom:14px;">🚫</div>
      <h1 style="font-size:26px; margin-bottom:8px;">Nemate pristup</h1>
      <p style="color:var(--faint); font-size:14px; margin-bottom:24px;">
        Ova stranica je dostupna samo administratorima.
        Prijavljeni ste s računom koji nema administratorske ovlasti.
      </p>
      <router-link to="/quizzes" class="btn-mint" style="padding:12px 22px; font-size:14px;">
        ← Natrag na kvizove
      </router-link>
    </div>
  </div>
</template>

<style scoped>
.denied-page {
  min-height: 70vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px 32px;
}
.denied-card {
  width: 100%;
  max-width: 460px;
  padding: 40px;
  border-radius: 20px;
  text-align: center;
}
</style>
