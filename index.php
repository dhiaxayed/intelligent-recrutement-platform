<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'candidate') {
        header('Location: candidate/dashboard.php');
        exit;
    }
    if (($_SESSION['role'] ?? '') === 'recruiter') {
        header('Location: recruiter/dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RecruitPro — Le talent rencontre l'opportunité</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* ── LANDING OVERRIDES ── */
    body { background: var(--white); overflow-x: hidden; }

    /* ── NAVBAR ── */
    .land-nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 200;
      background: rgba(255,255,255,0.95);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid var(--gray-200);
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 5vw; height: 64px;
      box-shadow: 0 1px 4px rgba(13,31,60,0.07);
    }
    .land-brand {
      display: flex; align-items: center; gap: 10px;
      font-size: 17px; font-weight: 700; color: var(--navy);
      text-decoration: none;
    }
    .land-brand-icon {
      width: 36px; height: 36px; background: var(--navy);
      border-radius: 10px; display: flex; align-items: center; justify-content: center;
    }
    .land-brand-icon svg { width: 20px; height: 20px; fill: white; }
    .land-nav-center { display: flex; gap: 4px; }
    .land-nav-center a {
      color: var(--gray-600); font-size: 14px; font-weight: 500;
      padding: 8px 16px; border-radius: var(--radius-md);
      text-decoration: none; transition: background 0.15s, color 0.15s;
    }
    .land-nav-center a:hover { background: var(--gray-100); color: var(--navy); text-decoration: none; }
    .land-nav-right { display: flex; align-items: center; gap: 10px; }
    .land-nav-right .link-btn {
      color: var(--gray-600); font-size: 14px; font-weight: 500;
      padding: 8px 16px; border-radius: var(--radius-md);
      text-decoration: none; transition: background 0.15s, color 0.15s;
    }
    .land-nav-right .link-btn:hover { background: var(--gray-100); color: var(--navy); text-decoration: none; }
    .land-nav-right .cta-btn {
      background: var(--navy); color: white; font-size: 14px; font-weight: 600;
      padding: 10px 20px; border-radius: var(--radius-pill);
      text-decoration: none; transition: background 0.18s;
    }
    .land-nav-right .cta-btn:hover { background: var(--navy-mid); text-decoration: none; color: white; }

    /* ── HERO ── */
    .hero {
      min-height: 100vh; display: flex; align-items: center; justify-content: center;
      text-align: center;
      background: linear-gradient(160deg, #0d1f3c 0%, #1a3a6b 45%, #2563eb 100%);
      padding: 120px 24px 80px;
      position: relative; overflow: hidden;
    }
    .hero::before {
      content: '';
      position: absolute; inset: 0;
      background: radial-gradient(ellipse at 70% 50%, rgba(59,130,246,0.25) 0%, transparent 60%),
                  radial-gradient(ellipse at 20% 80%, rgba(37,99,235,0.20) 0%, transparent 50%);
    }
    .hero-inner { position: relative; max-width: 740px; margin: 0 auto; }
    .hero-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,0.10); border: 1px solid rgba(255,255,255,0.18);
      color: rgba(255,255,255,0.90); font-size: 13px; font-weight: 500;
      padding: 8px 18px; border-radius: var(--radius-pill);
      margin-bottom: 32px; backdrop-filter: blur(4px);
    }
    .hero-badge svg { width: 14px; height: 14px; fill: currentColor; }
    .hero h1 {
      color: white; font-size: clamp(40px, 6vw, 68px);
      font-weight: 800; line-height: 1.1; margin-bottom: 24px;
      letter-spacing: -1.5px;
    }
    .hero-sub {
      color: rgba(255,255,255,0.75); font-size: 17px; line-height: 1.7;
      margin-bottom: 44px; max-width: 540px; margin-left: auto; margin-right: auto;
    }
    .hero-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
    .hero-btn-primary {
      background: white; color: var(--navy); font-size: 15px; font-weight: 700;
      padding: 16px 32px; border-radius: var(--radius-pill);
      text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
      transition: transform 0.18s, box-shadow 0.18s;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    .hero-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,0.2); text-decoration: none; color: var(--navy); }
    .hero-btn-secondary {
      background: rgba(255,255,255,0.10); color: white; font-size: 15px; font-weight: 600;
      padding: 16px 32px; border-radius: var(--radius-pill);
      text-decoration: none; border: 1.5px solid rgba(255,255,255,0.30);
      transition: background 0.18s; backdrop-filter: blur(4px);
    }
    .hero-btn-secondary:hover { background: rgba(255,255,255,0.18); text-decoration: none; color: white; }

    /* ── STATS STRIP ── */
    .stats-section {
      background: white; border-bottom: 1px solid var(--gray-200);
      padding: 52px 5vw;
    }
    .stats-row {
      max-width: 960px; margin: 0 auto;
      display: grid; grid-template-columns: repeat(4, 1fr); gap: 0;
    }
    .stat-item {
      text-align: center; padding: 0 20px;
      border-right: 1px solid var(--gray-200);
    }
    .stat-item:last-child { border-right: none; }
    .stat-item .stat-num {
      font-size: 40px; font-weight: 800; color: var(--navy);
      line-height: 1; margin-bottom: 8px; letter-spacing: -1px;
    }
    .stat-item .stat-lbl {
      font-size: 14px; color: var(--gray-400); font-weight: 500;
    }

    /* ── FEATURES SECTION ── */
    .features-section {
      background: var(--gray-50); padding: 96px 5vw;
    }
    .section-header {
      text-align: center; max-width: 600px; margin: 0 auto 56px;
    }
    .section-header h2 {
      font-size: clamp(28px, 4vw, 40px); font-weight: 800;
      color: var(--navy); letter-spacing: -0.8px; margin-bottom: 12px;
    }
    .section-header p { font-size: 16px; color: var(--gray-400); margin: 0; }

    .features-grid {
      max-width: 1080px; margin: 0 auto;
      display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px;
    }
    .feature-card {
      background: white; border: 1px solid var(--gray-200);
      border-radius: var(--radius-lg); padding: 32px 28px;
      box-shadow: var(--shadow-sm);
      transition: transform 0.18s, box-shadow 0.18s;
    }
    .feature-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
    .feature-icon {
      width: 52px; height: 52px; background: var(--gray-100);
      border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center;
      margin-bottom: 20px;
    }
    .feature-icon svg { width: 24px; height: 24px; stroke: var(--navy); fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .feature-card h3 { font-size: 17px; font-weight: 700; color: var(--navy); margin-bottom: 10px; }
    .feature-card p { font-size: 14px; color: var(--gray-400); line-height: 1.7; margin: 0; }

    /* ── ROLES SECTION ── */
    .roles-section { background: white; padding: 96px 5vw; }
    .roles-grid {
      max-width: 1080px; margin: 0 auto;
      display: grid; grid-template-columns: 1fr 1fr; gap: 24px;
    }
    .role-card {
      background: var(--gray-50); border: 1px solid var(--gray-200);
      border-radius: var(--radius-xl); padding: 40px 36px;
      transition: transform 0.18s, box-shadow 0.18s;
    }
    .role-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .role-icon-wrap {
      width: 60px; height: 60px; background: var(--navy);
      border-radius: 16px; display: flex; align-items: center; justify-content: center;
      margin-bottom: 20px;
    }
    .role-icon-wrap svg { width: 28px; height: 28px; stroke: white; fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .role-label {
      font-size: 11px; font-weight: 700; letter-spacing: 1.4px;
      text-transform: uppercase; color: var(--gray-400); margin-bottom: 8px;
    }
    .role-card h3 { font-size: 22px; font-weight: 800; color: var(--navy); margin-bottom: 20px; letter-spacing: -0.3px; }
    .role-features { list-style: none; padding: 0; margin: 0 0 28px; display: flex; flex-direction: column; gap: 10px; }
    .role-features li {
      display: flex; align-items: center; gap: 10px;
      font-size: 14px; color: var(--gray-600);
    }
    .role-features li svg { width: 18px; height: 18px; stroke: var(--success); fill: none; flex-shrink: 0; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .role-cta {
      display: block; text-align: center;
      background: var(--navy); color: white; font-size: 15px; font-weight: 600;
      padding: 14px 28px; border-radius: var(--radius-pill);
      text-decoration: none; transition: background 0.18s;
    }
    .role-cta:hover { background: var(--navy-mid); text-decoration: none; color: white; }

    /* ── CTA BANNER ── */
    .cta-section { background: var(--gray-50); padding: 80px 5vw; }
    .cta-banner {
      max-width: 1080px; margin: 0 auto;
      background: linear-gradient(135deg, var(--navy) 0%, #1a3a6b 60%, #2563eb 100%);
      border-radius: var(--radius-xl); padding: 72px 64px;
      text-align: center;
    }
    .cta-banner h2 {
      color: white; font-size: clamp(28px, 4vw, 42px);
      font-weight: 800; margin-bottom: 14px; letter-spacing: -0.8px;
    }
    .cta-banner p { color: rgba(255,255,255,0.70); font-size: 16px; margin-bottom: 36px; }
    .cta-white-btn {
      display: inline-flex; align-items: center; gap: 8px;
      background: white; color: var(--navy); font-size: 15px; font-weight: 700;
      padding: 16px 36px; border-radius: var(--radius-pill);
      text-decoration: none; transition: transform 0.18s, box-shadow 0.18s;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    .cta-white-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,0.2); text-decoration: none; color: var(--navy); }

    /* ── FOOTER ── */
    .land-footer {
      background: white; border-top: 1px solid var(--gray-200);
      padding: 28px 5vw;
      display: flex; align-items: center; justify-content: space-between;
    }
    .land-footer-brand {
      display: flex; align-items: center; gap: 10px;
      font-size: 16px; font-weight: 700; color: var(--navy);
      text-decoration: none;
    }
    .land-footer-brand-icon {
      width: 32px; height: 32px; background: var(--navy);
      border-radius: 9px; display: flex; align-items: center; justify-content: center;
    }
    .land-footer-brand-icon svg { width: 17px; height: 17px; fill: white; }
    .land-footer p { color: var(--gray-400); font-size: 13px; margin: 0; }

    @media (max-width: 768px) {
      .features-grid { grid-template-columns: 1fr; }
      .roles-grid { grid-template-columns: 1fr; }
      .stats-row { grid-template-columns: 1fr 1fr; }
      .stat-item { border-right: none; border-bottom: 1px solid var(--gray-200); padding: 16px 0; }
      .stat-item:nth-child(even) { border-right: none; }
      .cta-banner { padding: 48px 28px; }
      .land-nav-center { display: none; }
      .land-footer { flex-direction: column; gap: 12px; text-align: center; }
      .hero h1 { font-size: 36px; }
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="land-nav">
  <a href="index.php" class="land-brand">
    <div class="land-brand-icon">
      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
        <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
      </svg>
    </div>
    RecruitPro
  </a>
  <div class="land-nav-center">
    <a href="candidate/jobs.php">Offres</a>
  </div>
  <div class="land-nav-right">
    <a href="signin.php" class="link-btn">Connexion</a>
    <a href="signup.php" class="cta-btn">S'inscrire</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-inner">
    <div class="hero-badge">
      <svg viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
      Recrutement nouvelle génération
    </div>
    <h1>Le talent rencontre<br>l'opportunité.</h1>
    <p class="hero-sub">Une plateforme unique qui connecte candidats et recruteurs.<br>
    Publiez, postulez et planifiez vos entretiens — automatiquement.</p>
    <div class="hero-actions">
      <a href="signup.php" class="hero-btn-primary">Créer mon compte →</a>
    <a href="candidate/jobs.php" class="hero-btn-secondary">Voir les offres</a>
    </div>
  </div>
</section>

<!-- STATS -->
<section class="stats-section">
  <div class="stats-row">
    <div class="stat-item">
      <div class="stat-num">10k+</div>
      <div class="stat-lbl">Candidats actifs</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">2.5k</div>
      <div class="stat-lbl">Offres publiées</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">85%</div>
      <div class="stat-lbl">Taux de réponse</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">48h</div>
      <div class="stat-lbl">Délai moyen</div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features-section">
  <div class="section-header">
    <h2>Tout ce qu'il faut pour recruter</h2>
    <p>De la publication à l'entretien en quelques clics.</p>
  </div>
  <div class="features-grid">

    <div class="feature-card">
      <div class="feature-icon">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      </div>
      <h3>Recherche intelligente</h3>
      <p>Les candidats trouvent les offres pertinentes en un coup d'œil. Filtres par lieu, type de contrat et plus.</p>
    </div>

    <div class="feature-card">
      <div class="feature-icon">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      </div>
      <h3>Candidature en 1 clic</h3>
      <p>CV uploadé une seule fois, postulez à toutes les offres sans ressaisie. Lettre de motivation optionnelle.</p>
    </div>

    <div class="feature-card">
      <div class="feature-icon">
        <svg viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
      </div>
      <h3>Entretien Google Meet</h3>
      <p>Quand un recruteur accepte, le candidat reçoit automatiquement un email avec son lien d'entretien.</p>
    </div>

  </div>
</section>

<!-- ROLES -->
<section class="roles-section">
  <div class="section-header">
    <h2>Une plateforme, deux profils</h2>
    <p>Que vous cherchiez un emploi ou le bon candidat, RecruitPro est fait pour vous.</p>
  </div>
  <div class="roles-grid">

    <div class="role-card">
      <div class="role-icon-wrap">
        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="role-label">Candidats</div>
      <h3>Trouvez le poste qui vous ressemble</h3>
      <ul class="role-features">
        <li>
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          Profil &amp; CV en quelques minutes
        </li>
        <li>
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          Postulez en 1 clic
        </li>
        <li>
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          Suivez vos candidatures en temps réel
        </li>
      </ul>
      <a href="signup.php?role=candidate" class="role-cta">Je suis candidat</a>
    </div>

    <div class="role-card">
      <div class="role-icon-wrap">
        <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
      </div>
      <div class="role-label">Recruteurs</div>
      <h3>Recrutez plus vite, mieux</h3>
      <ul class="role-features">
        <li>
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          Publiez vos offres gratuitement
        </li>
        <li>
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          Centralisez les candidatures reçues
        </li>
        <li>
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          Acceptez &amp; invitez en entretien automatiquement
        </li>
      </ul>
      <a href="signup.php?role=recruiter" class="role-cta">Je recrute</a>
    </div>

  </div>
</section>

<!-- CTA BANNER -->
<section class="cta-section">
  <div class="cta-banner">
    <h2>Prêt à commencer ?</h2>
    <p>Rejoignez la plateforme en 30 secondes. C'est gratuit.</p>
    <a href="signup.php" class="cta-white-btn">Créer mon compte →</a>
  </div>
</section>

<!-- FOOTER -->
<footer class="land-footer">
  <a href="index.php" class="land-footer-brand">
    <div class="land-footer-brand-icon">
      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/>
        <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
      </svg>
    </div>
    RecruitPro
  </a>
  <p>© 2026 RecruitPro — Plateforme de recrutement intelligente</p>
</footer>

</body>
</html>