<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SentimenAI — Analisis Sentimen Berita</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#08090c;--surface:#111318;--surface2:#181c24;
  --border:rgba(255,255,255,0.07);--border-hi:rgba(255,255,255,0.14);
  --text:#e8eaf0;--muted:#6b7385;
  --accent:#4f8eff;--accent2:#6ee7b7;
  --danger:#f87171;--warn:#fbbf24;
  --pos:#34d399;--neg:#f87171;--neu:#94a3b8;
  --radius:14px;--radius-sm:8px;
}

/* ── Light Mode Override ── */
body.light-mode {
  --bg: #f8fafc; --surface: #ffffff; --surface2: #f1f5f9;
  --border: rgba(0,0,0,0.1); --border-hi: rgba(0,0,0,0.2);
  --text: #0f172a; --muted: #64748b;
  --accent: #2563eb;
}
body.light-mode .nav-item:hover { background: rgba(0,0,0,.04); }
body.light-mode .nav-item.active { background: rgba(37,99,235,.08); }
body.light-mode .quest-icon.todo, body.light-mode .quest-badge.todo { background: rgba(0,0,0,.06); }
body.light-mode .bar-track, body.light-mode .score-bar-track, body.light-mode .progress-bar-wrap { background: rgba(0,0,0,.06); }
body.light-mode .saran-item { background: rgba(0,0,0,.04); }
body.light-mode .tag { background: rgba(0,0,0,.06); }
body.light-mode .news-card:hover { background: var(--surface2); }
body.light-mode .mobile-header { background: var(--surface); border-bottom-color: var(--border); }

html{scroll-behavior:smooth; overflow-x: hidden;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;line-height:1.6;transition:background 0.3s, color 0.3s; overflow-x: hidden; width: 100%;}
::-webkit-scrollbar{width:5px; height:5px;}
::-webkit-scrollbar-track{background:var(--surface)}
::-webkit-scrollbar-thumb{background:var(--border-hi);border-radius:3px}
h1,h2,h3,h4{font-family:'Syne',sans-serif}

/* ── Layout ── */
.app{display:flex;min-height:100vh; flex-direction: row; width: 100%; overflow-x: hidden;}

/* --- SIDEBAR DESKTOP --- */
.sidebar{width:260px;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;flex-shrink:0;position:sticky;top:0;height:100vh;transition: margin-left 0.35s cubic-bezier(0.4, 0, 0.2, 1), transform 0.35s cubic-bezier(0.4, 0, 0.2, 1), background 0.3s, border-color 0.3s; z-index: 100;}
.sidebar.collapsed {margin-left: -260px;} 

/* --- SIDEBAR TOP (Menu Dalam) --- */
.sidebar-top { display: flex; align-items: center; padding: 16px 20px; height: 76px; gap: 8px; margin-bottom: 8px; }
.logo{font-size:19px;font-weight:800;color:var(--text);letter-spacing:-0.5px; display:flex; align-items:center; gap:8px;}
.logo span{color:var(--accent)}
.nav-item{display:flex;align-items:center;gap:12px;padding:14px 24px;font-size:14.5px;color:var(--muted);cursor:pointer;transition:.15s;border-left:3px solid transparent; white-space: nowrap;}
.nav-item:hover{color:var(--text);background:rgba(255,255,255,.04)}
.nav-item.active{color:var(--text);background:rgba(79,142,255,.08);border-left-color:var(--accent)}
.nav-item i{font-size:20px;width:22px}
.sidebar-footer{margin-top:auto;padding:16px 24px;border-top:1px solid var(--border);font-size:12px;color:var(--muted)}
.main{flex:1;background:var(--bg);transition:background 0.3s;position:relative; display: flex; flex-direction: column; max-width: 100%; overflow-x: hidden;}

/* ── UI Tombol Hamburger ── */
.desktop-nav-toggle { 
  background: transparent; border: none; color: var(--text); font-size: 24px; 
  cursor: pointer; display: flex; align-items: center; justify-content: center; 
  width: 44px; height: 44px; border-radius: 50%; transition: background 0.2s, color 0.2s; 
  flex-shrink: 0;
}
.desktop-nav-toggle:hover { background: var(--surface2); color: var(--accent); }

/* --- HEADER DESKTOP (Menu Luar) --- */
.desktop-header { display: flex; align-items: center; padding: 16px 20px; height: 76px; position: relative; z-index: 10; }
.desktop-header .desktop-nav-toggle { opacity: 0; pointer-events: none; transition: opacity 0.3s; margin-left: -6px;}
/* Ketika sidebar collpase, munculkan tombol luar */
.sidebar.collapsed ~ .main .desktop-header .desktop-nav-toggle { opacity: 1; pointer-events: auto; }

.mobile-header { display: none; }
.sidebar-overlay { display: none; }

/* ── Theme Toggle Button ── */
.theme-btn {
  position: fixed;
  top: 16px;
  right: 24px;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: var(--surface);
  border: 1px solid var(--border);
  color: var(--text);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  cursor: pointer;
  z-index: 90;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.theme-btn:hover { background: var(--surface2); border-color: var(--accent); color: var(--accent); }

/* ── Pages ── */
.page{display:none;padding:16px 32px 32px;animation:fade-up .35s ease both; flex: 1; width: 100%;}
.page.active{display:block}
@keyframes fade-up{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.page-header{margin-bottom:24px; padding-right: 60px;}
.page-header h2{font-size:22px;font-weight:700;color:var(--text);margin-bottom:4px}
.page-header p{font-size:13.5px;color:var(--muted)}

/* ── Landing Page Styles ── */
.landing-container { text-align: center; padding: 20px 20px; max-width: 800px; margin: 0 auto; }
.landing-emoji { font-size: 56px; margin-bottom: 24px; }
.landing-title { font-size: 36px; font-weight: 800; color: var(--text); margin-bottom: 16px; line-height: 1.3; }
.landing-title span { color: var(--accent); }
.landing-desc { font-size: 15.5px; color: var(--muted); line-height: 1.7; margin-bottom: 36px; }
.landing-features { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 48px; text-align: left; }
.feature-card { margin-bottom: 0 !important; }
.feature-icon { font-size: 26px; color: var(--accent); margin-bottom: 14px; display: block; }
.feature-title { font-size: 15px; margin-bottom: 8px; font-weight: 700; color: var(--text); }
.feature-desc { font-size: 13px; color: var(--muted); line-height: 1.6; }
.btn-start { padding: 16px 36px; font-size: 15px; border-radius: 30px; margin: 0 auto; display: inline-flex; align-items: center; justify-content: center; }

/* ── Cards & UI Elements ── */
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px;transition:background 0.3s, border-color 0.3s; margin-bottom: 14px; width: 100%; overflow: hidden;}
.card-title{font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);margin-bottom:16px;font-weight:500}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px 20px;transition:background 0.3s, border-color 0.3s;}
.stat-label{font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);margin-bottom:8px;font-weight:500}
.stat-value{font-size:26px;font-family:'Syne',sans-serif;font-weight:700;line-height:1}
.stat-sub{font-size:12px;color:var(--muted);margin-top:6px}
.stat-card.pos .stat-value{color:var(--pos)}
.stat-card.neg .stat-value{color:var(--neg)}
.stat-card.neu .stat-value{color:var(--neu)}
.stat-card.all .stat-value{color:var(--accent)}
.chart-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px}
.donut-wrap{display:flex;align-items:center;gap:20px; flex-wrap: wrap; justify-content: center;}
.donut-legend{display:flex;flex-direction:column;gap:8px}
.legend-item{display:flex;align-items:center;gap:8px;font-size:13px}
.legend-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
.bar-wrap{display:flex;flex-direction:column;gap:10px; width: 100%;}
.bar-row{display:flex;align-items:center;gap:10px;font-size:12.5px; width: 100%;}
.bar-label{width:70px;color:var(--muted);text-align:right;flex-shrink:0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;}
.bar-track{flex:1;background:rgba(255,255,255,.06);border-radius:4px;height:8px;overflow:hidden; min-width: 50px;}
.bar-fill{height:100%;border-radius:4px;animation:bar-grow .7s ease both}
@keyframes bar-grow{from{width:0}}
.bar-pct{width:32px;text-align:right;color:var(--text);font-size:12px; flex-shrink: 0;}
.quest-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.quest-card{background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:14px 16px;display:flex;align-items:flex-start;gap:12px;}
.quest-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:17px}
.quest-icon.done{background:rgba(52,211,153,.15);color:var(--pos)}
.quest-icon.todo{background:rgba(255,255,255,.06);color:var(--muted)}
.quest-name{font-size:13px;font-weight:500;color:var(--text);margin-bottom:3px}
.quest-desc{font-size:12px;color:var(--muted)}
.quest-badge{display:inline-block;font-size:11px;padding:2px 8px;border-radius:20px;margin-top:6px;font-weight:500}
.quest-badge.done{background:rgba(52,211,153,.15);color:var(--pos)}
.quest-badge.todo{background:rgba(255,255,255,.06);color:var(--muted)}
.progress-bar-wrap{background:rgba(255,255,255,.06);border-radius:4px;height:6px;margin:8px 0 16px;overflow:hidden}
.progress-bar{height:100%;border-radius:4px;background:linear-gradient(90deg,var(--accent),var(--accent2));transition:width .8s ease}
.badge{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;padding:3px 9px;border-radius:20px;font-weight:500; white-space: nowrap;}
.badge.pos{background:rgba(52,211,153,.15);color:var(--pos)}
.badge.neg{background:rgba(248,113,113,.15);color:var(--neg)}
.badge.neu{background:rgba(148,163,184,.1);color:var(--neu)}
.badge-dot{width:6px;height:6px;border-radius:50%;background:currentColor}
.recent-list{display:flex;flex-direction:column;gap:8px}
.recent-item{background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px 16px;display:flex;align-items:center;gap:14px; flex-wrap: wrap;}
.recent-ticker{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;color:var(--text);width:60px}
.recent-name{font-size:13px;color:var(--muted);flex:1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;}
.recent-score{font-size:13px;font-weight:500;}
.search-bar{display:flex;gap:10px;margin-bottom:20px; width: 100%;}
.search-input{flex:1; width: 100%; max-width: 100%; background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px 14px;color:var(--text);font-size:14px;font-family:'DM Sans',sans-serif;outline:none;transition:.2s}
.search-input:focus{border-color:var(--accent);background:var(--surface2)}
.search-input::placeholder{color:var(--muted)}
.search-btn{background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);padding:12px 20px;font-size:14px;font-family:'Syne',sans-serif;font-weight:600;cursor:pointer;transition:.15s;display:flex;align-items:center;justify-content:center;gap:7px;white-space:nowrap; flex-shrink: 0;}
.search-btn:hover{background:#3a7ae8}
.search-btn:disabled{opacity:.7;cursor:not-allowed}
.filter-tabs{display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap}
.filter-tab{padding:6px 14px;border-radius:20px;font-size:12.5px;cursor:pointer;border:1px solid var(--border);color:var(--muted);background:transparent;font-family:'DM Sans',sans-serif;transition:.15s}
.filter-tab:hover{border-color:var(--border-hi);color:var(--text)}
.filter-tab.active{background:var(--accent);color:#fff;border-color:var(--accent)}
.news-list{display:flex;flex-direction:column;gap:10px}
.news-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:16px 20px;transition:.15s; width: 100%;}
.news-card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:8px; flex-wrap: wrap;}
.news-card-title{font-size:14px;font-weight:500;color:var(--text);line-height:1.5;flex:1;}
.news-card-meta{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.news-source{font-size:11.5px;color:var(--muted)}
.news-time{font-size:11.5px;color:var(--muted)}
.news-tags{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}
.tag{font-size:11px;padding:2px 8px;border-radius:4px;background:rgba(255,255,255,.06);color:var(--muted)}
.alert-box{border-radius:var(--radius);padding:16px 20px;margin-top:16px;display:flex;gap:14px; width: 100%;}
.alert-box.neg{background:rgba(248,113,113,.07);border:1px solid rgba(248,113,113,.2)}
.alert-box.pos{background:rgba(52,211,153,.07);border:1px solid rgba(52,211,153,.2)}
.alert-box.neu{background:rgba(148,163,184,.07);border:1px solid rgba(148,163,184,.15)}
.alert-icon{font-size:22px;flex-shrink:0;margin-top:1px}
.alert-title-neg{font-size:13.5px;font-weight:500;margin-bottom:6px;color:var(--neg)}
.alert-title-pos{font-size:13.5px;font-weight:500;margin-bottom:6px;color:var(--pos)}
.alert-body{font-size:13px;color:var(--muted);line-height:1.6}
.saran-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px}
.saran-item{background:rgba(255,255,255,.04);border-radius:var(--radius-sm);padding:12px 14px;font-size:12.5px;color:var(--text);line-height:1.5;display:flex;align-items:flex-start;gap:8px}
.saran-item i{color:var(--accent);font-size:15px;margin-top:1px;flex-shrink:0}
.result-panel{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px;margin-top:14px; width: 100%;}
.result-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px; flex-wrap: wrap; gap: 10px;}
.result-ticker{font-family:'Syne',sans-serif;font-size:20px;font-weight:800}
.result-score-row{display:flex;align-items:center;gap:10px;margin-bottom:14px; flex-wrap: wrap;}
.score-bar-track{flex:1; min-width: 100px; height:10px;background:rgba(255,255,255,.06);border-radius:5px;overflow:hidden}
.score-bar-fill{height:100%;border-radius:5px;transition:width .8s ease}
.spinner{width:15px;height:15px;border:2px solid var(--border-hi);border-top:2px solid var(--accent);border-radius:50%;animation:spin .7s linear infinite;display:inline-block}
@keyframes spin{to{transform:rotate(360deg)}}
.empty-state{text-align:center;padding:48px 20px;color:var(--muted)}
.empty-state i{font-size:36px;display:block;margin-bottom:12px;opacity:.4}
.empty-state p{font-size:14px}

/* ═══════════════════════════════════════
   RESPONSIVE MOBILE 100% FLUID FIX
═══════════════════════════════════════ */
@media (max-width: 992px) {
  .stat-grid { grid-template-columns: repeat(2, 1fr); }
  .chart-row { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
  .app { flex-direction: column; }
  .desktop-header { display: none; } 
  
  .mobile-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 16px; background: var(--surface); border-bottom: 1px solid var(--border);
    position: sticky; top: 0; z-index: 80; width: 100%;
  }
  .mobile-header .logo { padding: 0; margin: 0; font-size: 17px; }
  .hamburger-btn { 
    background: transparent; border: none; color: var(--text); font-size: 24px; 
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    width: 40px; height: 40px; border-radius: 50%; transition: 0.2s;
  }
  .hamburger-btn:hover { background: var(--surface2); }
  
  /* Reset Sidebar untuk Mobile */
  .sidebar { position: fixed; top: 0; left: 0; width: 260px; height: 100vh; transform: translateX(-100%); margin-left: 0; z-index: 1000; }
  .sidebar.open { transform: translateX(0); box-shadow: 4px 0 24px rgba(0,0,0,0.5); }
  .sidebar-top { padding: 24px; height: auto; }
  .sidebar-top .inside-sidebar { display: none; } /* Sembunyikan toggle dalam karena mobile pake header luar */
  
  .sidebar-overlay { display: block; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.6); opacity: 0; visibility: hidden; transition: 0.3s; z-index: 999; }
  .sidebar-overlay.open { opacity: 1; visibility: visible; }
  
  .page { padding: 16px 16px 100px 16px; }
  .page-header { padding-right: 0; margin-bottom: 16px; }
  .page-header h2 { font-size: 20px; line-height: 1.3;}
  .page-header p { font-size: 13px; }
  
  .landing-container { padding: 10px 10px; }
  .landing-emoji { font-size: 40px; margin-bottom: 16px; }
  .landing-title { font-size: 26px; margin-bottom: 12px; }
  .landing-desc { font-size: 14px; margin-bottom: 30px; }
  .landing-features { grid-template-columns: 1fr; gap: 12px; margin-bottom: 30px; }
  .btn-start { font-size: 14px; padding: 14px 20px; width: 100%; }

  .card { padding: 16px; margin-bottom: 12px; border-radius: 12px;}
  .stat-grid { grid-template-columns: 1fr; gap: 10px; margin-bottom: 16px;}
  .stat-card { padding: 14px 16px; }
  .chart-row { grid-template-columns: 1fr; gap: 12px; margin-bottom: 16px;}
  .search-bar { flex-direction: column; width: 100%; }
  .search-input { width: 100%; font-size: 14px; }
  .search-btn { width: 100%; margin-top: 4px; }
  .saran-grid { grid-template-columns: 1fr; }
  .quest-grid { grid-template-columns: 1fr; }
  .recent-item { padding: 10px 12px; flex-direction: row; flex-wrap: wrap; }
  .recent-ticker { width: 100%; margin-bottom: 4px;}
  .recent-name { white-space: normal; line-height: 1.4; width: 100%; margin-bottom: 8px;}
  
  .theme-btn { top: auto; bottom: 24px; right: 16px; width: 48px; height: 48px; box-shadow: 0 4px 16px rgba(0,0,0,0.4); }
}
</style>
</head>
<body>

<div class="app">
  <div class="sidebar-overlay" onclick="toggleMobileMenu()"></div>

  <aside class="sidebar" id="main-sidebar">
    <div class="sidebar-top">
      <button class="desktop-nav-toggle inside-sidebar" onclick="toggleDesktopSidebar()" title="Tutup Menu">
        <i class="ti ti-menu-2"></i>
      </button>
      <div class="logo">
        <i class="ti ti-robot" style="font-size: 24px; color: var(--accent);"></i> Sentimen<span>AI</span>
      </div>
    </div>
    
    <nav>
      <div class="nav-item active" data-page="landing" onclick="goPage('landing')">
        <i class="ti ti-info-square-rounded"></i> <span>Pengenalan</span>
      </div>
      <div class="nav-item" data-page="analisis" onclick="goPage('analisis')">
        <i class="ti ti-scan"></i> <span>Analisis AI</span>
      </div>
      <div class="nav-item" data-page="trend" onclick="goPage('trend')">
        <i class="ti ti-trending-up"></i> <span>Tren Pasar</span>
      </div>
      <div class="nav-item" data-page="dashboard" onclick="goPage('dashboard')">
        <i class="ti ti-layout-dashboard"></i> <span>Dashboard</span>
      </div>
      <div class="nav-item" data-page="history" onclick="goPage('history')">
        <i class="ti ti-clock-history"></i> <span>Riwayat</span>
      </div>
    </nav>
    <div class="sidebar-footer">
      <div style="font-size:13px;font-weight:500;color:var(--text);margin-bottom:2px">v2.5.0</div>
      <div>Model: FinBERT-ID</div>
    </div>
  </aside>

  <main class="main">
    <header class="desktop-header">
      <button class="desktop-nav-toggle" onclick="toggleDesktopSidebar()" title="Buka Menu">
        <i class="ti ti-menu-2"></i>
      </button>
    </header>

    <div class="mobile-header">
      <button class="hamburger-btn" onclick="toggleMobileMenu()">
        <i class="ti ti-menu-2"></i>
      </button>
      <div class="logo" style="display:flex; align-items:center; gap:6px;">
         <i class="ti ti-robot" style="font-size: 20px; color: var(--accent);"></i> Sentimen<span>AI</span>
      </div>
      <div style="width: 40px;"></div>
    </div>

    <button class="theme-btn" onclick="toggleTheme()" title="Ganti Tema">
      <i id="theme-icon" class="ti ti-sun"></i>
    </button>

    <div id="page-landing" class="page active">
      <div class="landing-container">
        <div class="landing-emoji">🤖</div>
        <h1 class="landing-title">
          Selamat Datang di <span>SentimenAI</span>
        </h1>
        <p class="landing-desc">
          Platform Analisis Sentimen Berita Ekonomi untuk Pengambilan Keputusan Investasi Retail. Kami membantu Anda membaca arah pasar dan mendeteksi sentimen berita secara instan menggunakan teknologi kecerdasan buatan, sehingga Anda dapat mengambil keputusan investasi yang lebih cerdas, objektif, dan minim risiko.
        </p>
        
        <div class="landing-features">
          <div class="card feature-card">
            <i class="ti ti-bolt feature-icon"></i>
            <h3 class="feature-title">Real-time AI</h3>
            <p class="feature-desc">Analisis teks dan berita makroekonomi hanya dalam hitungan detik menggunakan FinBERT.</p>
          </div>
          <div class="card feature-card">
            <i class="ti ti-chart-pie feature-icon"></i>
            <h3 class="feature-title">Market Insight</h3>
            <p class="feature-desc">Kategorisasi tren pasar secara otomatis menjadi Positif, Negatif, atau Netral.</p>
          </div>
          <div class="card feature-card">
            <i class="ti ti-shield-check feature-icon"></i>
            <h3 class="feature-title">Fokus Retail</h3>
            <p class="feature-desc">Dirancang secara khusus untuk meminimalisir risiko bagi para investor ritel.</p>
          </div>
        </div>

        <button class="search-btn btn-start" onclick="goPage('analisis')">
          Mulai Analisis Sekarang <i class="ti ti-arrow-right" style="margin-left: 8px; font-size: 18px;"></i>
        </button>
      </div>
    </div>

    <div id="page-analisis" class="page">
      <div class="page-header">
        <h2>Analisis Sentimen Berita</h2>
        <p>Tempelkan teks berita secara langsung untuk mendeteksi sentimen dengan FinBERT AI</p>
      </div>
      <div class="card">
        <div style="margin-bottom:12px">
          <label style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:1px;display:block;margin-bottom:8px">Konten Berita</label>
          <textarea id="text-analyze-input" class="search-input" rows="7" style="resize:vertical;line-height:1.6" placeholder="Masukkan atau tempel paragraf berita ekonomi di sini..."></textarea>
        </div>
        <button class="search-btn" id="btn-analyze-text" onclick="analyzeFreeText()">
          <i class="ti ti-scan"></i> Analisis Sentimen Konten
        </button>
      </div>
      <div id="text-result-panel" style="display:none;margin-top:16px"></div>
    </div>

    <div id="page-trend" class="page">
      <div class="page-header">
        <h2>Tren Pasar</h2>
        <p>Berita dan sentimen pasar terkini dari berbagai sumber</p>
      </div>
      <div class="search-bar">
        <input class="search-input" id="trend-search" type="text" placeholder="Cari berita tren pasar..." oninput="filterTrend(this.value)">
        <button class="search-btn" onclick="filterTrend(document.getElementById('trend-search').value)"><i class="ti ti-filter"></i> Filter</button>
      </div>
      <div class="filter-tabs">
        <button class="filter-tab active" onclick="setTrendFilter(this,'all')">Semua</button>
        <button class="filter-tab" onclick="setTrendFilter(this,'pos')">Positif</button>
        <button class="filter-tab" onclick="setTrendFilter(this,'neg')">Negatif</button>
        <button class="filter-tab" onclick="setTrendFilter(this,'neu')">Netral</button>
      </div>
      <div id="trend-list" class="news-list"></div>
    </div>

    <div id="page-dashboard" class="page">
      <div class="page-header">
        <h2>Dashboard</h2>
        <p>Ringkasan aktivitas analisis dan pencapaian Anda</p>
      </div>
      <div class="stat-grid">
        <div class="stat-card all"><div class="stat-label">Total Prediksi</div><div class="stat-value" id="d-total">0</div><div class="stat-sub">sejak pertama</div></div>
        <div class="stat-card pos"><div class="stat-label">Positif</div><div class="stat-value" id="d-pos">0</div><div class="stat-sub" id="d-pos-pct">0%</div></div>
        <div class="stat-card neg"><div class="stat-label">Negatif</div><div class="stat-value" id="d-neg">0</div><div class="stat-sub" id="d-neg-pct">0%</div></div>
        <div class="stat-card neu"><div class="stat-label">Netral</div><div class="stat-value" id="d-neu">0</div><div class="stat-sub" id="d-neu-pct">0%</div></div>
      </div>
      <div class="chart-row">
        <div class="card">
          <div class="card-title">Distribusi Sentimen</div>
          <div class="donut-wrap">
            <div style="position:relative;width:130px;height:130px;flex-shrink:0;margin:0 auto;"><canvas id="donutChart"></canvas></div>
            <div class="donut-legend" id="donut-legend"></div>
          </div>
        </div>
        <div class="card">
          <div class="card-title">Quest &amp; Pencapaian</div>
          <div class="progress-bar-wrap"><div class="progress-bar" id="quest-progress" style="width:0%"></div></div>
          <div class="quest-grid" id="quest-grid"></div>
        </div>
      </div>
    </div>

    <div id="page-history" class="page">
      <div class="page-header">
        <h2>Riwayat</h2>
        <p>Semua analisis yang pernah dilakukan</p>
      </div>
      <div class="search-bar">
        <input class="search-input" type="text" placeholder="Cari riwayat..." oninput="filterHistory(this.value)">
      </div>
      <div id="history-list" class="recent-list"></div>
    </div>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
// --- FITUR BARU: TOGGLE MENU DESKTOP ---
function toggleDesktopSidebar() {
  document.getElementById('main-sidebar').classList.toggle('collapsed');
}

// --- TOGGLE MENU MOBILE ---
function toggleMobileMenu() {
  document.getElementById('main-sidebar').classList.toggle('open');
  document.querySelector('.sidebar-overlay').classList.toggle('open');
}

function initTheme() {
  const savedTheme = localStorage.getItem('theme');
  const icon = document.getElementById('theme-icon');
  if (savedTheme === 'light') {
    document.body.classList.add('light-mode');
    icon.classList.replace('ti-sun', 'ti-moon');
  }
}

function toggleTheme() {
  const body = document.body;
  const icon = document.getElementById('theme-icon');
  body.classList.toggle('light-mode');
  if (body.classList.contains('light-mode')) {
    icon.classList.replace('ti-sun', 'ti-moon');
    localStorage.setItem('theme', 'light');
  } else {
    icon.classList.replace('ti-moon', 'ti-sun');
    localStorage.setItem('theme', 'dark');
  }
}

initTheme();

const trendData = [
  {id:1,title:"Bank Indonesia pertahankan suku bunga acuan 6% di tengah ketidakpastian global",source:"Bisnis.com",time:"2 jam lalu",sentiment:"neu",score:0.51,tags:["makro","BI"]},
  {id:2,title:"IHSG ditutup menguat 1,2% didorong aksi beli investor asing pada sektor perbankan",source:"CNBC Indonesia",time:"3 jam lalu",sentiment:"pos",score:0.84,tags:["saham","IHSG"]},
  {id:3,title:"Rupiah melemah ke Rp15.800 per dolar AS imbas penguatan indeks dolar global",source:"Kontan",time:"4 jam lalu",sentiment:"neg",score:0.22,tags:["makro","rupiah"]},
];

const questData = [
  {icon:'ti-scan',name:'Analisis Pertama',desc:'Lakukan analisis sentimen teks pertamamu',done:false},
  {icon:'ti-repeat',name:'Analis Aktif',desc:'Lakukan 5 analisis teks berbeda',done:false},
];

const saranNegatif = [
  {icon:'ti-shield-half',text:'Pertimbangkan untuk wait & see sementara sentimen negatif berlanjut'},
  {icon:'ti-cut',text:'Pasang stop-loss ketat jika ini terkait saham di portofolio Anda'},
];

let trendFilter = 'all';
let trendSearch = '';
let history = [];
let analyzedTickers = new Set();
let negCount = 0;
let donutChart = null;

function goPage(p) {
  document.querySelectorAll('.page').forEach(x => x.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(x => x.classList.remove('active'));
  document.getElementById('page-' + p).classList.add('active');
  document.querySelector('[data-page=' + p + ']').classList.add('active');
  if (p === 'trend') renderTrend();
  if (p === 'dashboard') renderDashboard();
  if (p === 'history') renderHistoryPage();
  if (window.innerWidth <= 768) {
    document.getElementById('main-sidebar').classList.remove('open');
    document.querySelector('.sidebar-overlay').classList.remove('open');
  }
}

function sentLabel(s) { return s==='pos'?'Positif':s==='neg'?'Negatif':'Netral'; }
function sentColor(s) { return s==='pos'?'#34d399':s==='neg'?'#f87171':'#94a3b8'; }
function badgeHTML(s, score) {
  const lbl = sentLabel(s);
  const sc = score !== undefined ? ' ' + Math.round(score*100) + '%' : '';
  return `<span class="badge ${s}"><span class="badge-dot"></span>${lbl}${sc}</span>`;
}
function scoreBarHTML(score, s) {
  return `<div class="score-bar-track"><div class="score-bar-fill" style="width:${Math.round(score*100)}%;background:${sentColor(s)}"></div></div>`;
}

// FUNGSI ANALISIS DENGAN CSRF TOKEN LARAVEL
async function analyzeFreeText() {
  const txt = document.getElementById('text-analyze-input').value.trim();
  if(!txt) return;
  const btn = document.getElementById('btn-analyze-text');
  const res = document.getElementById('text-result-panel');
  btn.innerHTML = `<span class="spinner" style="margin-right:6px;vertical-align:middle"></span> Memproses AI...`;
  btn.disabled = true;
  res.style.display = 'none';

  try {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    const response = await axios.post('/api/v1/analyze', { 
        judul: 'Analisis Teks Bebas', 
        konten_berita: txt 
    }, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    });

    const json = response.data;
    const { sentimen, akurasi } = json.data;
    let s = 'neu';
    if (sentimen.toLowerCase() === 'positif') s = 'pos';
    else if (sentimen.toLowerCase() === 'negatif') s = 'neg';
    const score = Number(akurasi) / 100;
    const snippet = txt.length > 40 ? txt.substring(0, 40) + '...' : txt;
    history.unshift({ticker: 'BERITA', name: snippet, sentiment: s, score: score, time: 'Baru saja'});
    if (s === 'neg') negCount++;

    let saranHtml = '';
    if (s === 'neg') {
      saranHtml = `<div class="alert-box neg" style="margin-top:20px"><div class="alert-icon">⚠️</div><div style="flex:1"><div class="alert-title-neg">Sentimen Negatif Terdeteksi — Rekomendasi Aksi:</div><div class="saran-grid">${saranNegatif.map(sItem => `<div class="saran-item"><i class="ti ${sItem.icon}"></i><span>${sItem.text}</span></div>`).join('')}</div></div></div>`;
    }

    res.innerHTML = `<div class="result-panel"><div class="result-header"><div class="result-ticker" style="font-size:15px">Hasil FinBERT AI</div>${badgeHTML(s)}</div><div class="result-score-row" style="margin-top:12px"><span style="font-size:12px;color:var(--muted);width:100px;flex-shrink:0">Confidence Score</span>${scoreBarHTML(score, s)}<span style="font-size:13px;font-weight:500;color:${sentColor(s)};width:40px;text-align:right">${akurasi}%</span></div>${saranHtml}</div>`;
    res.style.display = 'block';
  } catch (err) {
    res.innerHTML = `<div class="alert-box neg" style="margin-top:16px"><div class="alert-icon">⚠️</div><div style="flex:1"><div class="alert-title-neg">Gagal Menghubungi Server AI</div><div class="alert-body">Pastikan backend Anda menyala. ${err.message}</div></div></div>`;
    res.style.display = 'block';
  } finally {
    btn.innerHTML = `<i class="ti ti-scan"></i> Analisis Sentimen Konten`;
    btn.disabled = false;
  }
}

function renderTrend() {
  const q = trendSearch.toLowerCase();
  const items = trendData.filter(n => (trendFilter === 'all' || n.sentiment === trendFilter || n.tags.includes(trendFilter)) && (!q || n.title.toLowerCase().includes(q)));
  const el = document.getElementById('trend-list');
  if (!items.length) { el.innerHTML = `<div class="empty-state"><p>Tidak ada berita cocok</p></div>`; return; }
  el.innerHTML = items.map(n => `<div class="news-card"><div class="news-card-top"><div class="news-card-title">${n.title}</div>${badgeHTML(n.sentiment, n.score)}</div><div class="news-card-meta"><span class="news-source"><i class="ti ti-news"></i> ${n.source}</span><span class="news-time"><i class="ti ti-clock"></i> ${n.time}</span></div></div>`).join('');
}
function setTrendFilter(btn, f) { document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active')); btn.classList.add('active'); trendFilter = f; renderTrend(); }
function filterTrend(v) { trendSearch = v; renderTrend(); }

function renderDashboard() {
  const total = history.length;
  const pos = history.filter(h => h.sentiment === 'pos').length;
  const neg = history.filter(h => h.sentiment === 'neg').length;
  const neu = history.filter(h => h.sentiment === 'neu').length;

  document.getElementById('d-total').textContent = total;
  document.getElementById('d-pos').textContent = pos;
  document.getElementById('d-neg').textContent = neg;
  document.getElementById('d-neu').textContent = neu;
  
  if (donutChart) donutChart.destroy();
  const ctx = document.getElementById('donutChart').getContext('2d');
  donutChart = new Chart(ctx, {
    type: 'doughnut',
    data: { labels: ['Positif','Negatif','Netral'], datasets: [{ data: [pos||1, neg||1, neu||1], backgroundColor: ['#34d399','#f87171','#94a3b8'], borderWidth: 0 }] },
    options: { responsive: true, maintainAspectRatio: true, cutout: '70%', plugins: { legend: { display: false } } }
  });

  document.getElementById('quest-grid').innerHTML = questData.map(q => `<div class="quest-card"><div class="quest-icon todo"><i class="ti ${q.icon}"></i></div><div><div class="quest-name">${q.name}</div><div class="quest-desc">${q.desc}</div></div></div>`).join('');
}

function recentItemHTML(h) { return `<div class="recent-item"><div class="recent-ticker">${h.ticker}</div><div class="recent-name">${h.name}</div>${badgeHTML(h.sentiment)}<div class="recent-score">${Math.round(h.score*100)}%</div></div>`; }
function renderHistoryPage() { document.getElementById('history-list').innerHTML = history.length ? history.map(h => recentItemHTML(h)).join('') : `<div class="empty-state"><p>Belum ada riwayat</p></div>`; }
function filterHistory(v) { const q = v.toLowerCase(); const items = history.filter(h => h.name.toLowerCase().includes(q)); document.getElementById('history-list').innerHTML = items.length ? items.map(h => recentItemHTML(h)).join('') : `<div class="empty-state"><p>Tidak ditemukan</p></div>`; }
</script>
</body>
</html>
