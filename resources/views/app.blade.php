<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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

html{scroll-behavior:smooth}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;line-height:1.6;transition:background 0.3s, color 0.3s;}
::-webkit-scrollbar{width:5px}
::-webkit-scrollbar-track{background:var(--surface)}
::-webkit-scrollbar-thumb{background:var(--border-hi);border-radius:3px}
h1,h2,h3,h4{font-family:'Syne',sans-serif}

/* ── Layout ── */
.app{display:flex;min-height:100vh}
.sidebar{width:220px;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;padding:20px 0;flex-shrink:0;position:sticky;top:0;height:100vh;transition:background 0.3s, border-color 0.3s;}
.logo{padding:0 20px 24px;font-size:18px;font-weight:800;color:var(--text);letter-spacing:-0.5px}
.logo span{color:var(--accent)}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 20px;font-size:13.5px;color:var(--muted);cursor:pointer;transition:.15s;border-left:2px solid transparent}
.nav-item:hover{color:var(--text);background:rgba(255,255,255,.04)}
.nav-item.active{color:var(--text);background:rgba(79,142,255,.08);border-left-color:var(--accent)}
.nav-item i{font-size:16px;width:18px}
.sidebar-footer{margin-top:auto;padding:16px 20px;border-top:1px solid var(--border);font-size:12px;color:var(--muted)}
.main{flex:1;overflow-y:auto;background:var(--bg);transition:background 0.3s;position:relative;}

/* ── Theme Toggle Button ── */
.theme-btn {
  position: fixed;
  top: 24px;
  right: 32px;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: var(--surface);
  border: 1px solid var(--border);
  color: var(--text);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  cursor: pointer;
  z-index: 100;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.theme-btn:hover {
  background: var(--surface2);
  border-color: var(--accent);
  color: var(--accent);
}

/* ── Pages ── */
.page{display:none;padding:28px 32px;animation:fade-up .35s ease both}
.page.active{display:block}
@keyframes fade-up{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.page-header{margin-bottom:24px; padding-right: 60px; /* space for theme btn */}
.page-header h2{font-size:22px;font-weight:700;color:var(--text);margin-bottom:4px}
.page-header p{font-size:13.5px;color:var(--muted)}

/* ── Cards ── */
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px;transition:background 0.3s, border-color 0.3s;}
.card-title{font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);margin-bottom:16px;font-weight:500}

/* ── Stat Grid ── */
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px 20px;transition:background 0.3s, border-color 0.3s;}
.stat-label{font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);margin-bottom:8px;font-weight:500}
.stat-value{font-size:26px;font-family:'Syne',sans-serif;font-weight:700;line-height:1}
.stat-sub{font-size:12px;color:var(--muted);margin-top:6px}
.stat-card.pos .stat-value{color:var(--pos)}
.stat-card.neg .stat-value{color:var(--neg)}
.stat-card.neu .stat-value{color:var(--neu)}
.stat-card.all .stat-value{color:var(--accent)}

/* ── Chart Row ── */
.chart-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px}
.donut-wrap{display:flex;align-items:center;gap:20px}
.donut-legend{display:flex;flex-direction:column;gap:8px}
.legend-item{display:flex;align-items:center;gap:8px;font-size:13px}
.legend-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}

/* ── Bar Chart ── */
.bar-wrap{display:flex;flex-direction:column;gap:10px}
.bar-row{display:flex;align-items:center;gap:10px;font-size:12.5px}
.bar-label{width:70px;color:var(--muted);text-align:right;flex-shrink:0}
.bar-track{flex:1;background:rgba(255,255,255,.06);border-radius:4px;height:8px;overflow:hidden}
.bar-fill{height:100%;border-radius:4px;animation:bar-grow .7s ease both}
@keyframes bar-grow{from{width:0}}
.bar-pct{width:32px;text-align:right;color:var(--text);font-size:12px}

/* ── Quest ── */
.quest-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.quest-card{background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:14px 16px;display:flex;align-items:flex-start;gap:12px;transition:background 0.3s, border-color 0.3s;}
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

/* ── Recent / History ── */
.recent-list{display:flex;flex-direction:column;gap:8px}
.recent-item{background:var(--surface2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px 16px;display:flex;align-items:center;gap:14px;transition:background 0.3s, border-color 0.3s;}
.recent-ticker{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;color:var(--text);width:60px}
.recent-name{font-size:13px;color:var(--muted);flex:1}
.recent-score{font-size:13px;font-weight:500;margin-left:auto;margin-right:12px}

/* ── Badges ── */
.badge{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;padding:3px 9px;border-radius:20px;font-weight:500}
.badge.pos{background:rgba(52,211,153,.15);color:var(--pos)}
.badge.neg{background:rgba(248,113,113,.15);color:var(--neg)}
.badge.neu{background:rgba(148,163,184,.1);color:var(--neu)}
.badge-dot{width:6px;height:6px;border-radius:50%;background:currentColor}

/* ── Search Bar ── */
.search-bar{display:flex;gap:10px;margin-bottom:20px}
.search-input{flex:1;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);padding:10px 14px;color:var(--text);font-size:14px;font-family:'DM Sans',sans-serif;outline:none;transition:.2s}
.search-input:focus{border-color:var(--accent);background:var(--surface2)}
.search-input::placeholder{color:var(--muted)}
.search-btn{background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);padding:10px 20px;font-size:14px;font-family:'Syne',sans-serif;font-weight:600;cursor:pointer;transition:.15s;display:flex;align-items:center;gap:7px;white-space:nowrap}
.search-btn:hover{background:#3a7ae8}
.search-btn:disabled{opacity:.7;cursor:not-allowed}

/* ── Filter Tabs ── */
.filter-tabs{display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap}
.filter-tab{padding:6px 14px;border-radius:20px;font-size:12.5px;cursor:pointer;border:1px solid var(--border);color:var(--muted);background:transparent;font-family:'DM Sans',sans-serif;transition:.15s}
.filter-tab:hover{border-color:var(--border-hi);color:var(--text)}
.filter-tab.active{background:var(--accent);color:#fff;border-color:var(--accent)}

/* ── News Cards ── */
.news-list{display:flex;flex-direction:column;gap:10px}
.news-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:16px 20px;transition:.15s}
.news-card:hover{border-color:var(--border-hi);transform:translateY(-1px)}
.news-card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:8px}
.news-card-title{font-size:14px;font-weight:500;color:var(--text);line-height:1.5;flex:1}
.news-card-meta{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.news-source{font-size:11.5px;color:var(--muted)}
.news-time{font-size:11.5px;color:var(--muted)}
.news-tags{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}
.tag{font-size:11px;padding:2px 8px;border-radius:4px;background:rgba(255,255,255,.06);color:var(--muted)}

/* ── Alert / Saran ── */
.alert-box{border-radius:var(--radius);padding:16px 20px;margin-top:16px;display:flex;gap:14px}
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

/* ── Result Panel ── */
.result-panel{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px;margin-top:14px;transition:background 0.3s, border-color 0.3s;}
.result-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.result-ticker{font-family:'Syne',sans-serif;font-size:20px;font-weight:800}
.result-score-row{display:flex;align-items:center;gap:10px;margin-bottom:14px}
.score-bar-track{flex:1;height:10px;background:rgba(255,255,255,.06);border-radius:5px;overflow:hidden}
.score-bar-fill{height:100%;border-radius:5px;transition:width .8s ease}
.news-snippet{border-top:1px solid var(--border);padding-top:12px;margin-top:4px}
.news-snippet-item{padding:10px 0;border-bottom:1px solid var(--border);font-size:13px;line-height:1.5}
.news-snippet-item:last-child{border-bottom:none}
.news-snippet-item strong{color:var(--text);display:block;margin-bottom:3px;font-size:13.5px;font-weight:500}
.news-snippet-item span{color:var(--muted)}

/* ── Spinner ── */
.spinner{width:15px;height:15px;border:2px solid var(--border-hi);border-top:2px solid var(--accent);border-radius:50%;animation:spin .7s linear infinite;display:inline-block}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── Empty State ── */
.empty-state{text-align:center;padding:48px 20px;color:var(--muted)}
.empty-state i{font-size:36px;display:block;margin-bottom:12px;opacity:.4}
.empty-state p{font-size:14px}
</style>
</head>
<body>

<div class="app">
  <aside class="sidebar">
    <div class="logo">Sentimen<span>AI</span></div>
    <nav>
      <div class="nav-item active" data-page="analisis" onclick="goPage('analisis')">
        <i class="ti ti-home"></i> Beranda
      </div>
      <div class="nav-item" data-page="trend" onclick="goPage('trend')">
        <i class="ti ti-trending-up"></i> Tren Pasar
      </div>
      <div class="nav-item" data-page="dashboard" onclick="goPage('dashboard')">
        <i class="ti ti-layout-dashboard"></i> Dashboard
      </div>
      <div class="nav-item" data-page="history" onclick="goPage('history')">
        <i class="ti ti-clock-history"></i> Riwayat
      </div>
    </nav>
    <div class="sidebar-footer">
      <div style="font-size:13px;font-weight:500;color:var(--text);margin-bottom:2px">v2.1.0</div>
      <div>Model: FinBERT-ID</div>
    </div>
  </aside>

  <main class="main">

    <button class="theme-btn" onclick="toggleTheme()" title="Ganti Tema">
      <i id="theme-icon" class="ti ti-sun"></i>
    </button>

    <div id="page-analisis" class="page active">
      <div class="page-header">
        <h2>Analisis Sentimen Berita</h2>
        <p>Tempelkan teks berita secara langsung untuk mendeteksi sentimen dengan FinBERT AI</p>
      </div>

      <div class="card">
        <div style="margin-bottom:16px">
          <label style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:1px;display:block;margin-bottom:8px">Konten Berita</label>
          <textarea id="text-analyze-input" class="search-input" rows="7" style="width:100%;resize:vertical;padding:16px;line-height:1.6" placeholder="Masukkan atau tempel paragraf berita ekonomi di sini..."></textarea>
        </div>
        <button class="search-btn" id="btn-analyze-text" onclick="analyzeFreeText()">
          <i class="ti ti-scan"></i> Analisis Sentimen Konten
        </button>
      </div>

      <div id="text-result-panel" style="display:none;margin-top:20px"></div>
    </div>

    <div id="page-trend" class="page">
      <div class="page-header">
        <h2>Tren Pasar</h2>
        <p>Berita dan sentimen pasar terkini dari berbagai sumber</p>
      </div>

      <div class="search-bar">
        <input class="search-input" id="trend-search" type="text"
          placeholder="Cari berita tren pasar..."
          oninput="filterTrend(this.value)">
        <button class="search-btn" onclick="filterTrend(document.getElementById('trend-search').value)">
          <i class="ti ti-filter"></i> Filter
        </button>
      </div>

      <div class="filter-tabs">
        <button class="filter-tab active" onclick="setTrendFilter(this,'all')">Semua</button>
        <button class="filter-tab" onclick="setTrendFilter(this,'pos')">Positif</button>
        <button class="filter-tab" onclick="setTrendFilter(this,'neg')">Negatif</button>
        <button class="filter-tab" onclick="setTrendFilter(this,'neu')">Netral</button>
        <button class="filter-tab" onclick="setTrendFilter(this,'saham')">Saham</button>
        <button class="filter-tab" onclick="setTrendFilter(this,'makro')">Makroekonomi</button>
        <button class="filter-tab" onclick="setTrendFilter(this,'komoditas')">Komoditas</button>
      </div>

      <div id="trend-list" class="news-list"></div>
    </div>

    <div id="page-dashboard" class="page">
      <div class="page-header">
        <h2>Dashboard</h2>
        <p>Ringkasan aktivitas analisis dan pencapaian Anda</p>
      </div>

      <div class="stat-grid">
        <div class="stat-card all">
          <div class="stat-label">Total Prediksi</div>
          <div class="stat-value" id="d-total">0</div>
          <div class="stat-sub">sejak pertama digunakan</div>
        </div>
        <div class="stat-card pos">
          <div class="stat-label">Sentimen Positif</div>
          <div class="stat-value" id="d-pos">0</div>
          <div class="stat-sub" id="d-pos-pct">0% dari total</div>
        </div>
        <div class="stat-card neg">
          <div class="stat-label">Sentimen Negatif</div>
          <div class="stat-value" id="d-neg">0</div>
          <div class="stat-sub" id="d-neg-pct">0% dari total</div>
        </div>
        <div class="stat-card neu">
          <div class="stat-label">Sentimen Netral</div>
          <div class="stat-value" id="d-neu">0</div>
          <div class="stat-sub" id="d-neu-pct">0% dari total</div>
        </div>
      </div>

      <div class="chart-row">
        <div class="card">
          <div class="card-title">Distribusi Sentimen</div>
          <div class="donut-wrap">
            <div style="position:relative;width:130px;height:130px;flex-shrink:0">
              <canvas id="donutChart" role="img" aria-label="Donut chart distribusi sentimen positif negatif netral"></canvas>
            </div>
            <div class="donut-legend" id="donut-legend"></div>
          </div>
        </div>
        <div class="card">
          <div class="card-title">Top Emiten Dianalisis</div>
          <div class="bar-wrap" id="top-emiten">
            <div class="empty-state" style="padding:20px">
              <p style="font-size:13px">Belum ada data analisis</p>
            </div>
          </div>
        </div>
      </div>

      <div style="margin-bottom:14px">
        <div class="card">
          <div class="card-title">Quest &amp; Pencapaian</div>
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
            <span style="font-size:13px;color:var(--muted)">Progress keseluruhan</span>
            <span style="font-size:13px;font-weight:500;color:var(--accent)" id="quest-pct">0%</span>
          </div>
          <div class="progress-bar-wrap">
            <div class="progress-bar" id="quest-progress" style="width:0%"></div>
          </div>
          <div class="quest-grid" id="quest-grid"></div>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Riwayat Analisis Terbaru</div>
        <div class="recent-list" id="dash-recent"></div>
      </div>
    </div>

    <div id="page-history" class="page">
      <div class="page-header">
        <h2>Riwayat</h2>
        <p>Semua analisis yang pernah dilakukan</p>
      </div>
      <div class="search-bar">
        <input class="search-input" type="text"
          placeholder="Cari riwayat..."
          oninput="filterHistory(this.value)">
      </div>
      <div id="history-list" class="recent-list"></div>
    </div>

  </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
/* ═══════════════════════════════════════
   THEME MANAGER (DARK/LIGHT MODE)
═══════════════════════════════════════ */
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

// Jalankan saat pertama kali dimuat
initTheme();


/* ═══════════════════════════════════════
   DATA
═══════════════════════════════════════ */
const trendData = [
  {id:1,title:"Bank Indonesia pertahankan suku bunga acuan 6% di tengah ketidakpastian global",source:"Bisnis.com",time:"2 jam lalu",sentiment:"neu",score:0.51,tags:["makro","BI","suku bunga"],full:"Bank Indonesia dalam Rapat Dewan Gubernur memutuskan mempertahankan BI-Rate sebesar 6,00% dengan mempertimbangkan perlunya menjaga stabilitas nilai tukar Rupiah di tengah volatilitas pasar keuangan global."},
  {id:2,title:"IHSG ditutup menguat 1,2% didorong aksi beli investor asing pada sektor perbankan",source:"CNBC Indonesia",time:"3 jam lalu",sentiment:"pos",score:0.84,tags:["saham","IHSG","perbankan"],full:"Indeks Harga Saham Gabungan (IHSG) ditutup menguat 1,2% ke level 7.320, didorong oleh aksi beli bersih investor asing senilai Rp 1,2 triliun yang terpusat di sektor perbankan dan konsumer."},
  {id:3,title:"Rupiah melemah ke Rp15.800 per dolar AS imbas penguatan indeks dolar global",source:"Kontan",time:"4 jam lalu",sentiment:"neg",score:0.22,tags:["makro","rupiah","forex"],full:"Nilai tukar Rupiah melemah 0,4% ke level Rp15.800 per dolar AS pada perdagangan sore ini, tertekan oleh penguatan indeks dolar AS (DXY) yang naik 0,3% ke level 104,5."},
  {id:4,title:"Harga batu bara ICE Newcastle turun 3% ke USD 135 per ton, sentuh level terendah 6 bulan",source:"Reuters",time:"5 jam lalu",sentiment:"neg",score:0.18,tags:["komoditas","batu bara"],full:"Harga batu bara kontrak ICE Newcastle turun 3,1% ke level USD 135,40 per ton, menyentuh level terendah dalam enam bulan terakhir akibat meningkatnya pasokan dari Australia dan lesunya permintaan dari China."},
  {id:5,title:"Emiten sawit catat laba bersih naik 45% di kuartal pertama seiring kenaikan harga CPO",source:"Investor.id",time:"6 jam lalu",sentiment:"pos",score:0.91,tags:["saham","sawit","komoditas"],full:"Sejumlah emiten perkebunan sawit mencatat pertumbuhan laba bersih rata-rata 45% pada Q1 2025, ditopang oleh kenaikan harga minyak kelapa sawit (CPO) ke level USD 920 per ton di bursa Malaysia."},
  {id:6,title:"Pemerintah naikkan target pertumbuhan ekonomi 2025 menjadi 5,3%, pasar merespons positif",source:"Tempo.co",time:"7 jam lalu",sentiment:"pos",score:0.78,tags:["makro","ekonomi"],full:"Pemerintah merevisi ke atas target pertumbuhan ekonomi 2025 dari 5,0% menjadi 5,3%, didukung oleh proyeksi konsumsi rumah tangga yang kuat dan peningkatan belanja infrastruktur pemerintah."},
  {id:7,title:"Utang luar negeri Indonesia naik jadi USD 413 miliar, rasio terhadap PDB masih aman",source:"Detik Finance",time:"8 jam lalu",sentiment:"neu",score:0.49,tags:["makro","utang"],full:"Bank Indonesia melaporkan posisi utang luar negeri Indonesia sebesar USD 413,6 miliar, tumbuh 2,5% secara tahunan. Rasio ULN terhadap PDB terjaga di level 29,9%, masih dalam batas aman."},
  {id:8,title:"Saham teknologi dalam negeri kompak melemah menyusul koreksi Nasdaq semalam",source:"IDX Channel",time:"9 jam lalu",sentiment:"neg",score:0.21,tags:["saham","teknologi"],full:"Saham-saham emiten teknologi di BEI kompak melemah, mengikuti sentimen negatif dari koreksi Indeks Nasdaq sebesar 1,8% di Wall Street setelah data inflasi AS lebih tinggi dari ekspektasi."},
  {id:9,title:"Volume transaksi BEI tembus Rp 18 triliun, tertinggi dalam dua bulan terakhir",source:"Bloomberg Technoz",time:"10 jam lalu",sentiment:"pos",score:0.76,tags:["saham","BEI"],full:"Nilai transaksi di BEI menembus Rp 18,2 triliun, menjadi yang tertinggi sejak awal Januari 2025, dipicu antusiasme investor pada IPO sektor energi dan masuknya dana asing."},
  {id:10,title:"Inflasi April 2025 tercatat 2,8% yoy, masih dalam rentang target BI",source:"BPS",time:"11 jam lalu",sentiment:"neu",score:0.55,tags:["makro","inflasi"],full:"BPS mencatat inflasi April 2025 sebesar 2,8% secara tahunan, berada dalam rentang target Bank Indonesia 1,5–3,5%, didorong kenaikan harga pangan bergejolak dan tarif transportasi."},
  {id:11,title:"Harga emas Antam naik Rp 15.000 jadi Rp 1.385.000 per gram",source:"Kontan",time:"12 jam lalu",sentiment:"pos",score:0.69,tags:["komoditas","emas"],full:"Harga emas batangan Antam naik Rp 15.000 menjadi Rp 1.385.000 per gram pada Jumat pagi, mengikuti tren kenaikan harga emas spot global yang dipicu ketidakpastian geopolitik."},
  {id:12,title:"Defisit neraca perdagangan melebar ke USD 2,1 miliar di bulan April",source:"BPS",time:"13 jam lalu",sentiment:"neg",score:0.31,tags:["makro","perdagangan"],full:"BPS melaporkan defisit neraca perdagangan Indonesia melebar ke USD 2,1 miliar pada April 2025, lebih besar dari ekspektasi pasar USD 1,5 miliar, dipicu lonjakan impor barang modal."},
];

const questData = [
  {icon:'ti-scan',name:'Analisis Pertama',desc:'Lakukan analisis sentimen teks pertamamu',done:false,xp:10},
  {icon:'ti-repeat',name:'Analis Aktif',desc:'Lakukan 5 analisis teks berbeda',done:false,xp:25},
  {icon:'ti-chart-bar',name:'Pemburu Data',desc:'Lakukan 10 analisis teks berbeda',done:false,xp:50},
  {icon:'ti-trending-down',name:'Detektif Risiko',desc:'Temukan 3 sentimen negatif',done:false,xp:30},
  {icon:'ti-star',name:'Pakar Pasar',desc:'Lakukan 20 kali analisis',done:false,xp:100},
  {icon:'ti-calendar',name:'Konsisten 7 Hari',desc:'Gunakan aplikasi 7 hari berturut-turut',done:false,xp:40},
];

const saranNegatif = [
  {icon:'ti-shield-half',text:'Pertimbangkan untuk wait & see sementara sentimen negatif berlanjut'},
  {icon:'ti-cut',text:'Pasang stop-loss ketat jika ini terkait saham di portofolio Anda'},
  {icon:'ti-clock-pause',text:'Tunggu konfirmasi pembalikan arah pasar sebelum mengambil posisi'},
  {icon:'ti-transfer',text:'Lakukan diversifikasi ke sektor defensif untuk meminimalisir risiko'},
];

/* ═══════════════════════════════════════
   STATE
═══════════════════════════════════════ */
let trendFilter = 'all';
let trendSearch = '';
let history = [];
let analyzedTickers = new Set();
let negCount = 0;
let donutChart = null;

/* ═══════════════════════════════════════
   NAVIGATION
═══════════════════════════════════════ */
function goPage(p) {
  document.querySelectorAll('.page').forEach(x => x.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(x => x.classList.remove('active'));
  document.getElementById('page-' + p).classList.add('active');
  document.querySelector('[data-page=' + p + ']').classList.add('active');
  if (p === 'trend') renderTrend();
  if (p === 'dashboard') renderDashboard();
  if (p === 'history') renderHistoryPage();
}

/* ═══════════════════════════════════════
   HELPERS
═══════════════════════════════════════ */
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

/* ═══════════════════════════════════════
   ANALISIS KONTEN BERITA (TERHUBUNG KE API LARAVEL/PYTHON)
═══════════════════════════════════════ */
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

const response = await fetch('/api/v1/analyze', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json', 
        'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify({ 
        judul: 'Analisis Teks Bebas', 
        konten_berita: txt 
    }),
});

    const json = await response.json();
    if (!response.ok) throw new Error(json.message || 'Terjadi kesalahan pada server');

    const { sentimen, akurasi } = json.data;
    
    let s = 'neu';
    const lowerSentimen = sentimen.toLowerCase();
    if (lowerSentimen === 'positif') s = 'pos';
    else if (lowerSentimen === 'negatif') s = 'neg';

    const score = Number(akurasi) / 100;

    const snippet = txt.length > 50 ? txt.substring(0, 50) + '...' : txt;
    history.unshift({ticker: 'TEKS', name: snippet, sentiment: s, score: score, time: 'Baru saja'});
    analyzedTickers.add('TEKS_ANALYSIS_' + Date.now()); 
    if (s === 'neg') negCount++;

    let saranHtml = '';
    if (s === 'neg') {
      saranHtml = `
        <div class="alert-box neg" style="margin-top:20px">
          <div class="alert-icon">⚠️</div>
          <div style="flex:1">
            <div class="alert-title-neg">Sentimen Negatif Terdeteksi — Saran untuk Anda</div>
            <div class="alert-body">Berdasarkan deteksi AI, berita di atas mengandung unsur pesimis atau berisiko tinggi.</div>
            <div class="saran-grid">
              ${saranNegatif.map(sItem => `<div class="saran-item"><i class="ti ${sItem.icon}"></i><span>${sItem.text}</span></div>`).join('')}
            </div>
          </div>
        </div>
      `;
    }

    res.innerHTML = `
      <div class="result-panel">
        <div class="result-header">
          <div>
            <div class="result-ticker" style="font-size:16px">Hasil Analisis FinBERT AI</div>
          </div>
          ${badgeHTML(s)}
        </div>
        <div class="result-score-row" style="margin-top:16px">
          <span style="font-size:13px;color:var(--muted);width:120px;flex-shrink:0">Confidence Score</span>
          ${scoreBarHTML(score, s)}
          <span style="font-size:14px;font-weight:500;color:${sentColor(s)};width:40px;text-align:right">${akurasi}%</span>
        </div>
        <div style="background:var(--surface2);border:1px solid var(--border);padding:14px 18px;border-radius:8px;margin-top:16px;font-size:13px;color:var(--muted);line-height:1.6;font-style:italic">
          "${txt}"
        </div>
        ${saranHtml}
      </div>
    `;
    res.style.display = 'block';
    res.scrollIntoView({behavior:'smooth', block:'nearest'});

  } catch (err) {
    res.innerHTML = `
      <div class="alert-box neg" style="margin-top:16px">
        <div class="alert-icon">⚠️</div>
        <div style="flex:1">
          <div class="alert-title-neg">Gagal Menghubungi Server AI</div>
          <div class="alert-body">${err.message}. Pastikan server Laravel dan Python Anda berjalan.</div>
        </div>
      </div>
    `;
    res.style.display = 'block';
  } finally {
    btn.innerHTML = `<i class="ti ti-scan"></i> Analisis Sentimen Konten`;
    btn.disabled = false;
  }
}

/* ═══════════════════════════════════════
   TREND PAGE
═══════════════════════════════════════ */
function renderTrend() {
  const q = trendSearch.toLowerCase();
  const items = trendData.filter(n => {
    const matchSentiment = trendFilter === 'all' || n.sentiment === trendFilter || n.tags.includes(trendFilter);
    const matchSearch = !q || n.title.toLowerCase().includes(q) || n.tags.some(t => t.includes(q)) || n.source.toLowerCase().includes(q);
    return matchSentiment && matchSearch;
  });

  const el = document.getElementById('trend-list');
  if (!items.length) {
    el.innerHTML = `<div class="empty-state"><i class="ti ti-file-off"></i><p>Tidak ada berita yang cocok dengan filter ini</p></div>`;
    return;
  }
  el.innerHTML = items.map(n => `
    <div class="news-card">
      <div class="news-card-top">
        <div class="news-card-title">${n.title}</div>
        ${badgeHTML(n.sentiment, n.score)}
      </div>
      <div style="font-size:13px;color:var(--muted);line-height:1.6;margin-bottom:8px">${n.full}</div>
      <div class="news-card-meta">
        <span class="news-source"><i class="ti ti-news" style="font-size:13px;vertical-align:-2px;margin-right:4px"></i>${n.source}</span>
        <span class="news-time"><i class="ti ti-clock" style="font-size:13px;vertical-align:-2px;margin-right:4px"></i>${n.time}</span>
      </div>
      <div class="news-tags">${n.tags.map(t => `<span class="tag">${t}</span>`).join('')}</div>
    </div>
  `).join('');
}

function setTrendFilter(btn, f) {
  document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  trendFilter = f;
  renderTrend();
}

function filterTrend(v) {
  trendSearch = v;
  renderTrend();
}

/* ═══════════════════════════════════════
   DASHBOARD
═══════════════════════════════════════ */
function renderDashboard() {
  const total = history.length;
  const pos = history.filter(h => h.sentiment === 'pos').length;
  const neg = history.filter(h => h.sentiment === 'neg').length;
  const neu = history.filter(h => h.sentiment === 'neu').length;

  document.getElementById('d-total').textContent = total;
  document.getElementById('d-pos').textContent = pos;
  document.getElementById('d-neg').textContent = neg;
  document.getElementById('d-neu').textContent = neu;
  document.getElementById('d-pos-pct').textContent = total ? Math.round(pos/total*100) + '% dari total' : '0% dari total';
  document.getElementById('d-neg-pct').textContent = total ? Math.round(neg/total*100) + '% dari total' : '0% dari total';
  document.getElementById('d-neu-pct').textContent = total ? Math.round(neu/total*100) + '% dari total' : '0% dari total';

  // Donut
  document.getElementById('donut-legend').innerHTML = [
    {label:'Positif',color:'#34d399',val:pos},
    {label:'Negatif',color:'#f87171',val:neg},
    {label:'Netral',color:'#94a3b8',val:neu},
  ].map(l => `<div class="legend-item"><span class="legend-dot" style="background:${l.color}"></span><span>${l.label}: <strong>${l.val}</strong></span></div>`).join('');

  if (donutChart) donutChart.destroy();
  const ctx = document.getElementById('donutChart').getContext('2d');
  donutChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Positif','Negatif','Netral'],
      datasets: [{
        data: [pos||1, neg||1, neu||1],
        backgroundColor: ['#34d399','#f87171','#94a3b8'],
        borderWidth: 0,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      cutout: '68%',
      plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ` ${c.label}: ${c.raw}` } } }
    }
  });

  // Top analysis
  const tickerMap = {};
  history.forEach(h => { tickerMap[h.ticker] = (tickerMap[h.ticker] || {count:0, sentiment:h.sentiment, name:h.name}); tickerMap[h.ticker].count++; });
  const topList = Object.entries(tickerMap).sort((a,b) => b[1].count - a[1].count).slice(0,6);
  const maxCount = topList.length ? Math.max(...topList.map(e => e[1].count)) : 1;
  document.getElementById('top-emiten').innerHTML = topList.length
    ? topList.map(([ticker, d]) => `
        <div class="bar-row">
          <div class="bar-label">${ticker}</div>
          <div class="bar-track"><div class="bar-fill" style="width:${Math.round(d.count/maxCount*100)}%;background:${sentColor(d.sentiment)}"></div></div>
          <div class="bar-pct">${d.count}x</div>
        </div>`)
      .join('')
    : `<div style="font-size:13px;color:var(--muted);text-align:center;padding:20px">Belum ada data</div>`;

  // Quests logic
  updateQuests();
  const done = questData.filter(q => q.done).length;
  const pct = Math.round(done / questData.length * 100);
  document.getElementById('quest-pct').textContent = pct + '%';
  document.getElementById('quest-progress').style.width = pct + '%';
  document.getElementById('quest-grid').innerHTML = questData.map(q => `
    <div class="quest-card">
      <div class="quest-icon ${q.done ? 'done' : 'todo'}"><i class="ti ${q.icon}"></i></div>
      <div>
        <div class="quest-name">${q.name}</div>
        <div class="quest-desc">${q.desc}</div>
        <span class="quest-badge ${q.done ? 'done' : 'todo'}">${q.done ? '✓ Selesai' : 'Belum selesai'} · +${q.xp} XP</span>
      </div>
    </div>
  `).join('');

  // Recent
  document.getElementById('dash-recent').innerHTML = history.length
    ? history.slice(0, 5).map(h => recentItemHTML(h)).join('')
    : `<div class="empty-state" style="padding:24px"><p>Belum ada analisis dilakukan</p></div>`;
}

function updateQuests() {
  const total = history.length;
  const unique = analyzedTickers.size;
  questData[0].done = total >= 1;
  questData[1].done = unique >= 5;
  questData[2].done = total >= 10;
  questData[3].done = negCount >= 3;
  questData[4].done = total >= 20;
}

function recentItemHTML(h) {
  return `<div class="recent-item">
    <div class="recent-ticker">${h.ticker}</div>
    <div class="recent-name">${h.name}</div>
    ${badgeHTML(h.sentiment)}
    <div class="recent-score" style="color:${sentColor(h.sentiment)};margin-left:auto;margin-right:12px">${Math.round(h.score*100)}%</div>
    <div style="font-size:12px;color:var(--muted)">${h.time}</div>
  </div>`;
}

/* ═══════════════════════════════════════
   HISTORY PAGE
═══════════════════════════════════════ */
function renderHistoryPage() {
  renderHistoryList(history);
}

function filterHistory(v) {
  const q = v.toLowerCase();
  const items = history.filter(h => !q || h.ticker.toLowerCase().includes(q) || h.name.toLowerCase().includes(q));
  renderHistoryList(items);
}

function renderHistoryList(items) {
  document.getElementById('history-list').innerHTML = items.length
    ? items.map(h => recentItemHTML(h)).join('')
    : `<div class="empty-state"><i class="ti ti-history-off"></i><p>Tidak ada riwayat ditemukan</p></div>`;
}
</script>
</body>
</html>