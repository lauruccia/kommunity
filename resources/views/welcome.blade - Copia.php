<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kommunity — Le relazioni giuste generano risultati reali</title>
    <meta name="description" content="Kommunity è l'ecosistema dove professionisti e aziende si connettono, collaborano e crescono insieme.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Colori derivati dal logo SVG: #465D70 blu-ardesia + #55794F verde salvia */
        :root {
            --bg:     #07111a; --bg2: #0d1e2b;
            --brand:  #55794F; --brand2: #426240; --brand3: #6fa367;
            --teal:   #465D70; --teal2: #6e90a8;
            --text:   #F0F6FA; --muted: #8FAAB8; --line: rgba(255,255,255,.08);
        }
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; overflow-x: hidden; }
        body { margin: 0; overflow-x: hidden; background: var(--bg); color: var(--text);
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased; }
        a { text-decoration: none; color: inherit; }
        .km-wrap { width: min(100% - 2.5rem, 1380px); margin-inline: auto; }
        .km-section { padding: 5.5rem 0; }
        .km-page { background:
            radial-gradient(ellipse 60% 40% at 80% 0%, rgba(70,93,112,.18), transparent),
            radial-gradient(ellipse 55% 38% at 12% 12%, rgba(85,121,79,.14), transparent),
            linear-gradient(180deg, #07111a 0%, #0d1e2b 50%, #07111a 100%); }
        .glass { background: linear-gradient(145deg,rgba(255,255,255,.085),rgba(255,255,255,.030));
            border: 1px solid var(--line); backdrop-filter: blur(20px) saturate(140%);
            box-shadow: 0 28px 80px rgba(0,0,0,.32), inset 0 1px 0 rgba(255,255,255,.08); }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:.6rem;
            border-radius:.5rem; padding:.82rem 1.4rem; font-size:.85rem; font-weight:800;
            white-space:nowrap; cursor:pointer; border:none;
            transition:transform .2s ease, box-shadow .2s ease, background .2s ease, border-color .2s ease; }
        .btn:hover { transform: translateY(-2px); }
        .btn:active { transform: translateY(0); }
        .btn-primary { color:#edf5ea;
            background: linear-gradient(135deg, var(--brand3) 0%, var(--brand) 100%);
            box-shadow: 0 16px 36px rgba(85,121,79,.30), inset 0 1px 0 rgba(255,255,255,.20); }
        .btn-primary:hover { box-shadow: 0 22px 50px rgba(85,121,79,.42); }
        .btn-ghost { color:var(--text); background:rgba(255,255,255,.045); border:1px solid rgba(255,255,255,.14); }
        .btn-ghost:hover { border-color:rgba(70,93,112,.65); background:rgba(70,93,112,.12); }
        .btn-sm { padding:.56rem .95rem; font-size:.78rem; }
        .btn-lg { padding:.98rem 1.8rem; font-size:.94rem; }
        .brand-lockup { display:inline-flex; align-items:center; gap:.6rem;
            font-weight:800; font-size:1.22rem; letter-spacing:-.03em; color:var(--text); }
        .brand-mark { width:2.2rem; height:2.2rem; display:flex; align-items:center; justify-content:center;
            filter: drop-shadow(0 0 12px rgba(85,121,79,.35)); }
        .brand-mark svg { width:1.3rem; height:2.05rem; }
        .accent { color:var(--brand3); }
        .muted  { color:var(--muted); }
        .badge { display:inline-flex; align-items:center; gap:.45rem; padding:.34rem .85rem;
            border-radius:999px; font-size:.72rem; font-weight:800; letter-spacing:.06em; text-transform:uppercase; }
        .badge-teal  { background:rgba(70,93,112,.15); border:1px solid rgba(70,93,112,.38); color:var(--teal2); }
        .badge-green { background:rgba(85,121,79,.14);  border:1px solid rgba(85,121,79,.35);  color:var(--brand3); }
        .badge-dot   { width:.45rem; height:.45rem; border-radius:999px; background:currentColor; box-shadow:0 0 8px currentColor; }

        /* HEADER */
        .site-header { position:sticky; top:0; z-index:80; background:rgba(7,17,26,.82);
            border-bottom:1px solid rgba(255,255,255,.07); backdrop-filter:blur(24px) saturate(150%); }
        .header-inner { display:flex; align-items:center; justify-content:space-between; gap:2rem; padding:1rem 0; }
        .site-nav { display:flex; align-items:center; gap:2.2rem; }
        .nav-link { font-size:.8rem; font-weight:700; color:rgba(240,246,250,.72); transition:color .2s ease; }
        .nav-link:hover { color:var(--brand3); }
        .header-cta { display:flex; align-items:center; gap:.75rem; }
        .hamburger { display:none; flex-direction:column; gap:.32rem; cursor:pointer; padding:.3rem; }
        .hamburger span { display:block; width:1.35rem; height:2px; border-radius:2px; background:var(--text); }

        /* HERO */
        .hero { position:relative; overflow:hidden; min-height:780px; border-bottom:1px solid rgba(70,93,112,.22); }
        .hero::after { content:"K"; position:absolute; right:5%; top:-10rem;
            font-size:clamp(30rem,50vw,55rem); line-height:1; font-weight:800;
            color:rgba(70,93,112,.07); text-shadow:0 0 80px rgba(85,121,79,.12);
            pointer-events:none; user-select:none; }
        .hero-net { position:absolute; inset:0; pointer-events:none; opacity:.55;
            background-image:
                radial-gradient(circle at 70% 22%, rgba(110,144,168,.80) 0 2.5px, transparent 3px),
                radial-gradient(circle at 82% 40%, rgba(111,163,103,.85) 0 2.5px, transparent 3px),
                radial-gradient(circle at 61% 55%, rgba(70,93,112,.70) 0 2px,   transparent 3px),
                radial-gradient(circle at 90% 15%, rgba(111,163,103,.75) 0 2px,  transparent 3px),
                linear-gradient(115deg, transparent 54%, rgba(70,93,112,.14) 54.3%, transparent 54.7%),
                linear-gradient(35deg,  transparent 62%, rgba(85,121,79,.12)  62.3%, transparent 62.7%); }
        .hero-grid { position:relative; z-index:2; display:grid;
            grid-template-columns:minmax(0,.95fr) minmax(430px,1fr);
            gap:4rem; align-items:center; padding:5.5rem 0 5rem; }
        .hero h1 { font-size:clamp(3rem,6.5vw,6rem); line-height:.95; letter-spacing:-.06em; font-weight:800; }
        .hero-body { margin-top:1.8rem; max-width:520px; font-size:1.08rem; line-height:1.78; color:rgba(214,228,236,.92); }
        .hero-actions { display:flex; flex-wrap:wrap; gap:.9rem; margin-top:2.4rem; }
        .social-proof { display:flex; align-items:center; gap:1rem; margin-top:2.2rem; }
        .avatar-stack { display:flex; }
        .av { width:2.4rem; height:2.4rem; border-radius:999px; border:2px solid rgba(255,255,255,.26); margin-left:-.55rem; box-shadow:0 8px 20px rgba(0,0,0,.22); }
        .av:first-child { margin-left:0; }
        .av:nth-child(1){background:linear-gradient(135deg,#d8f3dc,#7dd3fc);}
        .av:nth-child(2){background:linear-gradient(135deg,#fde68a,#f97316);}
        .av:nth-child(3){background:linear-gradient(135deg,#e0e7ff,#6366f1);}
        .av:nth-child(4){background:linear-gradient(135deg,#d1fae5,#10b981);}
        .av:nth-child(5){background:linear-gradient(135deg,#fce7f3,#ec4899);}
        .av:nth-child(6){background:linear-gradient(135deg,#ffe4e6,#f43f5e);}
        .av:nth-child(7){background:linear-gradient(135deg,#f0f9ff,#0ea5e9);}
        .av:nth-child(8){background:linear-gradient(135deg,#fef3c7,#d97706);}
        .av-more{font-size:.66rem;font-weight:800;background:rgba(255,255,255,.10)!important;color:var(--text);display:flex;align-items:center;justify-content:center;}
        .social-text strong{display:block;font-size:.88rem;font-weight:800;}
        .social-text span{display:block;font-size:.74rem;color:var(--muted);margin-top:.1rem;}

        /* DASHBOARD MOCKUP */
        .dashboard-wrap{position:relative;}
        .floating-card{position:absolute;z-index:3;display:flex;align-items:center;gap:.72rem;
            padding:.75rem 1rem;border-radius:.85rem;min-width:13rem;
            background:rgba(7,20,30,.82);border:1px solid rgba(255,255,255,.12);
            box-shadow:0 24px 60px rgba(0,0,0,.36);backdrop-filter:blur(16px);}
        .floating-card.card-a{top:-1rem;right:-1rem;}
        .floating-card.card-b{bottom:1.5rem;left:-1.5rem;}
        .fc-face{width:2.4rem;height:2.4rem;border-radius:999px;border:2px solid rgba(255,255,255,.22);flex-shrink:0;}
        .fc-face-a{background:linear-gradient(135deg,#f8fafc,#6e90a8 55%,#2e5970);}
        .fc-face-b{background:linear-gradient(135deg,#fde68a,#f97316 55%,#b45309);}
        .fc-text strong{display:block;font-size:.82rem;font-weight:800;}
        .fc-text small{font-size:.72rem;color:var(--muted);}
        .verified-dot{width:.7rem;height:.7rem;border-radius:999px;background:var(--brand3);box-shadow:0 0 10px rgba(111,163,103,.8);flex-shrink:0;}
        .dash-card{position:relative;z-index:2;border-radius:1.2rem;padding:1.4rem;}
        .dash-chrome{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem;}
        .dash-dots{display:flex;gap:.36rem;}
        .dash-dots span{width:.52rem;height:.52rem;border-radius:999px;background:rgba(255,255,255,.22);}
        .dash-sub{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.17em;color:rgba(110,144,168,.80);}
        .dash-name{font-size:1.5rem;font-weight:800;margin-top:.35rem;}
        .metric-row{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;}
        .metric-cell{border-radius:.7rem;padding:.9rem;background:rgba(2,11,18,.42);border:1px solid rgba(255,255,255,.07);}
        .metric-cell strong{display:block;font-size:1.65rem;font-weight:800;color:var(--brand3);line-height:1;}
        .metric-cell span{display:block;font-size:.72rem;color:var(--muted);margin-top:.4rem;}
        .activity-list{display:grid;gap:.6rem;margin-top:.9rem;}
        .act-item{display:flex;align-items:center;gap:.65rem;border:1px solid rgba(255,255,255,.07);
            border-radius:.7rem;padding:.65rem .85rem;background:rgba(255,255,255,.03);}
        .act-dot{width:.5rem;height:.5rem;border-radius:999px;background:var(--brand3);box-shadow:0 0 14px rgba(111,163,103,.7);flex-shrink:0;}
        .act-item span{font-size:.78rem;color:rgba(214,228,236,.88);}
        .event-strip{display:flex;align-items:center;justify-content:space-between;gap:.75rem;
            margin-top:.9rem;border-radius:.9rem;padding:.95rem 1.1rem;
            background:linear-gradient(135deg,rgba(85,121,79,.18),rgba(70,93,112,.14));
            border:1px solid rgba(85,121,79,.28);}
        .event-label{font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.14em;color:rgba(110,144,168,.82);}
        .event-name{font-size:1.1rem;font-weight:800;margin-top:.25rem;}
        .event-meta{font-size:.76rem;color:var(--muted);margin-top:.2rem;}

        /* STEPS */
        .steps-section{background:linear-gradient(180deg,rgba(13,30,43,.75),rgba(7,17,26,.70));
            border-top:1px solid rgba(70,93,112,.30);border-bottom:1px solid rgba(85,121,79,.28);}
        .steps-header{text-align:center;margin-bottom:3.5rem;}
        .steps-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;}
        .step-card{position:relative;border-radius:.85rem;padding:1.4rem 1.2rem;
            background:linear-gradient(145deg,rgba(255,255,255,.062),rgba(255,255,255,.020));
            border:1px solid var(--line);
            transition:transform .22s ease,border-color .22s ease,box-shadow .22s ease;}
        .step-card:hover{transform:translateY(-4px);border-color:rgba(70,93,112,.48);box-shadow:0 22px 55px rgba(70,93,112,.12);}
        .step-card:not(:last-child)::after{content:"›";position:absolute;right:-.75rem;top:50%;
            transform:translateY(-50%);font-size:2.2rem;font-weight:300;color:var(--brand3);opacity:.7;z-index:2;line-height:1;}
        .step-icon-wrap{width:3rem;height:3rem;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;
            color:var(--teal2);border:1px solid rgba(70,93,112,.40);background:rgba(70,93,112,.10);box-shadow:0 0 28px rgba(70,93,112,.15);}
        .step-num-badge{display:inline-flex;align-items:center;justify-content:center;
            width:1.85rem;height:1.85rem;border-radius:999px;font-size:.72rem;font-weight:800;
            color:var(--brand3);background:rgba(85,121,79,.18);margin-left:.6rem;}
        .step-card h3{font-size:1rem;font-weight:800;margin-top:1.1rem;}
        .step-card p{font-size:.8rem;color:var(--muted);margin-top:.5rem;line-height:1.65;}

        /* ECOSISTEMA */
        .eco-grid{display:grid;grid-template-columns:.88fr 1.12fr;gap:4.5rem;align-items:center;}
        .eco-title{font-size:clamp(2.2rem,4.2vw,4.2rem);font-weight:800;line-height:1.02;letter-spacing:-.05em;}
        .feature-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.8rem;}
        .feat-card{border-radius:.75rem;padding:1.3rem;
            background:linear-gradient(145deg,rgba(255,255,255,.068),rgba(255,255,255,.022));
            border:1px solid var(--line);
            transition:transform .22s ease,border-color .22s ease,box-shadow .22s ease,background .22s ease;}
        .feat-card:hover{transform:translateY(-5px);border-color:rgba(70,93,112,.45);
            background:linear-gradient(145deg,rgba(70,93,112,.10),rgba(255,255,255,.03));box-shadow:0 24px 60px rgba(70,93,112,.12);}
        .feat-icon{width:2.75rem;height:2.75rem;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;
            color:var(--teal2);border:1px solid rgba(70,93,112,.38);background:rgba(70,93,112,.09);
            box-shadow:0 0 22px rgba(70,93,112,.13);margin-bottom:.9rem;}
        .feat-card h3{font-size:.9rem;font-weight:800;}
        .feat-card p{font-size:.78rem;color:var(--muted);margin-top:.45rem;line-height:1.62;}

        /* STATS */
        .stats-band{padding:1.8rem 0;
            background:radial-gradient(ellipse 30% 80% at 10% 50%, rgba(70,93,112,.14), transparent),
            linear-gradient(90deg, rgba(255,255,255,.025), rgba(255,255,255,.055), rgba(255,255,255,.025));}
        .stats-card{display:grid;grid-template-columns:repeat(4,1fr);border-radius:1.1rem;overflow:hidden;}
        .stat-cell{padding:1.75rem 1rem;text-align:center;border-right:1px solid rgba(255,255,255,.10);}
        .stat-cell:last-child{border-right:none;}
        .stat-cell strong{display:block;font-size:clamp(2.4rem,4vw,3.9rem);font-weight:800;line-height:1;
            letter-spacing:-.05em;color:var(--brand3);text-shadow:0 0 42px rgba(111,163,103,.35);}
        .stat-cell p{font-size:.82rem;font-weight:600;color:rgba(214,228,236,.78);margin-top:.85rem;}

        /* TESTIMONIANZE */
        .split-grid{display:grid;grid-template-columns:.7fr 1.3fr;gap:4rem;align-items:start;}
        .testi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.85rem;}
        .testi-card{border-radius:.75rem;padding:1.4rem;
            background:linear-gradient(145deg,rgba(255,255,255,.068),rgba(255,255,255,.022));
            border:1px solid var(--line);
            transition:transform .22s ease,border-color .22s ease,box-shadow .22s ease;}
        .testi-card:hover{transform:translateY(-4px);border-color:rgba(85,121,79,.40);box-shadow:0 22px 56px rgba(85,121,79,.10);}
        .stars{font-size:.88rem;color:var(--brand3);letter-spacing:.12em;}
        .testi-quote{margin-top:1rem;font-size:.82rem;line-height:1.72;color:rgba(214,228,236,.90);}
        .testi-person{display:flex;align-items:center;gap:.7rem;margin-top:1.35rem;}
        .tp-av{width:2.2rem;height:2.2rem;border-radius:999px;border:2px solid rgba(255,255,255,.20);flex-shrink:0;}
        .tp-av-a{background:linear-gradient(135deg,#d8f3dc,#6e90a8);}
        .tp-av-b{background:linear-gradient(135deg,#fde68a,#f97316);}
        .tp-av-c{background:linear-gradient(135deg,#e0e7ff,#6366f1);}
        .testi-person strong{display:block;font-size:.82rem;font-weight:800;}
        .testi-person span{display:block;font-size:.74rem;color:var(--muted);}

        /* COMMUNITY LOCALE */
        .local-section{background:linear-gradient(145deg,rgba(13,30,43,.90),rgba(7,17,26,.88));
            border-top:1px solid var(--line);border-bottom:1px solid var(--line);}
        .local-grid{display:grid;grid-template-columns:.88fr 1.12fr;gap:4.5rem;align-items:center;}
        .search-bar{display:flex;align-items:center;justify-content:space-between;margin-top:1.8rem;
            padding:.85rem 1.1rem;border-radius:.55rem;border:1px solid rgba(255,255,255,.12);
            background:rgba(255,255,255,.048);color:#607888;font-size:.84rem;cursor:text;}
        .city-chips{display:flex;flex-wrap:wrap;gap:.55rem;margin-top:1rem;}
        .city-chip{padding:.52rem .88rem;border-radius:.45rem;font-size:.76rem;font-weight:700;
            color:rgba(220,232,240,.88);border:1px solid rgba(85,121,79,.32);background:rgba(255,255,255,.03);cursor:pointer;
            transition:border-color .2s,background .2s,color .2s;}
        .city-chip:hover{border-color:var(--brand3);background:rgba(111,163,103,.08);color:var(--brand3);}
        .map-wrap{position:relative;min-height:420px;display:flex;align-items:center;justify-content:center;}
        .italy-map-container{position:relative;width:min(280px,75vw);height:440px;}
        .italy-svg{position:relative;width:min(420px,88vw);height:360px;}
        .italy-body{position:absolute;top:1rem;left:5.5rem;right:5.5rem;bottom:2.5rem;
            border-radius:44% 56% 56% 44% / 18% 20% 80% 82%;transform:rotate(22deg);
            background:linear-gradient(145deg,rgba(70,93,112,.28),rgba(85,121,79,.18));
            border:1px solid rgba(70,93,112,.32);}
        .italy-sardinia{position:absolute;left:4rem;bottom:4rem;width:3.2rem;height:5rem;
            border-radius:50% 50% 60% 40%;transform:rotate(12deg);
            background:rgba(70,93,112,.20);border:1px solid rgba(70,93,112,.26);}
        .italy-sicily{position:absolute;right:4rem;bottom:2rem;width:5rem;height:2.2rem;
            border-radius:55% 45% 55% 45%;transform:rotate(8deg);
            background:rgba(70,93,112,.20);border:1px solid rgba(70,93,112,.26);}
        .map-dot{position:absolute;width:.75rem;height:.75rem;border-radius:999px;background:var(--brand3);
            box-shadow:0 0 0 .4rem rgba(111,163,103,.14),0 0 18px rgba(111,163,103,.85);}
        .map-dot-pulse::before{content:"";position:absolute;inset:-.45rem;border-radius:999px;
            border:1.5px solid rgba(111,163,103,.42);animation:pulse 2.2s ease-out infinite;}
        @keyframes pulse{0%{opacity:1;transform:scale(.8)}100%{opacity:0;transform:scale(2.2)}}
        @keyframes pulse-dot{0%,100%{opacity:1;r:4}50%{opacity:.6;r:5.5}}
        .city-card-abs{position:absolute;right:0;bottom:2rem;width:15rem;border-radius:.85rem;padding:1rem;}

        /* VIDEO */
        .video-grid{display:grid;grid-template-columns:1fr 1fr;gap:3.5rem;align-items:center;}
        .video-player{position:relative;min-height:310px;border-radius:.85rem;overflow:hidden;
            background:linear-gradient(135deg,rgba(255,255,255,.12),rgba(7,17,26,.9)),
            radial-gradient(circle at 32% 34%, rgba(70,93,112,.22), transparent 14rem);}
        .video-player::before{content:"";position:absolute;inset:0;
            background:linear-gradient(130deg,rgba(255,255,255,.07),transparent 44%),
            repeating-linear-gradient(90deg,transparent 0 34px,rgba(255,255,255,.022) 34px 35px);}
        .video-brand{position:absolute;bottom:1.5rem;left:1.5rem;}
        .play-btn{position:absolute;inset:0;margin:auto;width:5.2rem;height:5.2rem;
            border-radius:999px;display:flex;align-items:center;justify-content:center;
            border:2px solid rgba(255,255,255,.82);background:rgba(255,255,255,.09);
            color:var(--text);cursor:pointer;transition:background .2s ease,transform .2s ease;}
        .play-btn:hover{background:rgba(85,121,79,.22);transform:scale(1.07);}
        .play-btn svg{margin-left:.28rem;}

        /* CTA */
        .cta-section{position:relative;overflow:hidden;
            border-top:1px solid rgba(70,93,112,.24);border-bottom:1px solid rgba(85,121,79,.30);
            background:radial-gradient(ellipse 55% 60% at 78% 100%, rgba(85,121,79,.26), transparent),
            linear-gradient(135deg,rgba(13,30,43,.95),rgba(7,17,26,.97));}
        .cta-section::before{content:"K";position:absolute;left:2%;bottom:-9rem;
            font-size:clamp(16rem,26vw,32rem);line-height:.8;font-weight:800;
            color:rgba(70,93,112,.12);text-shadow:0 0 60px rgba(85,121,79,.18);
            pointer-events:none;user-select:none;}
        .cta-inner{position:relative;z-index:1;display:grid;grid-template-columns:1fr auto;
            gap:2.5rem;align-items:center;padding:4rem 0;}
        .cta-title{font-size:clamp(2rem,4.5vw,4rem);font-weight:800;line-height:1.05;letter-spacing:-.05em;}
        .cta-body{margin-top:1.2rem;font-size:.95rem;color:rgba(214,228,236,.80);line-height:1.75;max-width:520px;}

        /* FOOTER */
        .site-footer{background:#03111a;border-top:1px solid rgba(255,255,255,.07);}
        .footer-grid{display:grid;grid-template-columns:1.5fr .72fr .85fr .95fr 1fr;gap:2rem;padding:2.5rem 0;}
        .footer-col-title{font-size:.76rem;font-weight:800;color:#dce8f2;margin-bottom:.9rem;}
        .footer-link{display:block;font-size:.76rem;color:var(--muted);margin-top:.5rem;transition:color .2s ease;}
        .footer-link:hover{color:var(--brand3);}
        .newsletter-form{display:flex;gap:.4rem;border:1px solid rgba(85,121,79,.38);
            border-radius:.52rem;padding:.32rem;background:rgba(255,255,255,.032);}
        .newsletter-form input{flex:1;min-width:0;background:transparent;border:none;outline:none;
            color:var(--text);font-size:.77rem;padding:.52rem .65rem;font-family:inherit;}
        .newsletter-form input::placeholder{color:#506070;}
        .newsletter-form button{width:2.15rem;border-radius:.38rem;background:var(--brand);
            color:#edf5ea;font-weight:900;font-size:1rem;cursor:pointer;border:none;transition:background .2s;}
        .newsletter-form button:hover{background:var(--brand3);}
        .social-icons{display:flex;gap:.7rem;margin-top:1rem;}
        .soc-icon{width:2.15rem;height:2.15rem;border-radius:999px;display:flex;align-items:center;justify-content:center;
            border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04);
            color:var(--muted);font-size:.8rem;font-weight:800;transition:border-color .2s,color .2s,background .2s;}
        .soc-icon:hover{border-color:var(--brand3);color:var(--brand3);background:rgba(111,163,103,.08);}
        .footer-bottom{border-top:1px solid rgba(255,255,255,.07);padding:1.2rem 0;}
        .footer-bottom-inner{display:flex;align-items:center;justify-content:space-between;gap:1rem;font-size:.74rem;color:#3d5060;}
        .footer-bottom-links{display:flex;gap:1.5rem;}
        .footer-bottom-links a{color:#3d5060;transition:color .2s;}
        .footer-bottom-links a:hover{color:var(--muted);}

        /* RESPONSIVE */
        @media(max-width:1200px){
            .hero-grid,.eco-grid,.local-grid,.video-grid{grid-template-columns:1fr;}
            .hero::after,.floating-card{display:none;}
            .feature-grid,.testi-grid{grid-template-columns:repeat(2,1fr);}
            .footer-grid{grid-template-columns:repeat(3,1fr);}
        }
        @media(max-width:860px){
            .site-nav{display:none;} .header-cta .btn-ghost{display:none;} .hamburger{display:flex;}
            .hero-grid{padding:3.5rem 0;gap:2.5rem;} .km-section{padding:3.8rem 0;}
            .steps-grid{grid-template-columns:1fr;} .step-card:not(:last-child)::after{display:none;}
            .stats-card{grid-template-columns:repeat(2,1fr);}
            .stat-cell{border-right:none;border-bottom:1px solid rgba(255,255,255,.10);}
            .stat-cell:last-child{border-bottom:none;}
            .split-grid,.cta-inner{grid-template-columns:1fr;}
            .testi-grid,.feature-grid,.footer-grid{grid-template-columns:1fr;}
            .cta-inner .btn{width:100%;}
            .city-card-abs{position:relative;right:auto;bottom:auto;width:100%;margin-top:1rem;}
            .map-wrap{min-height:340px;}
        }
        @media(max-width:520px){
            .hero h1{font-size:2.85rem;} .footer-grid{grid-template-columns:1fr;}
            .hero-actions .btn{width:100%;} .metric-row{grid-template-columns:1fr;}
        }
    </style>
</head>
<body>
@php
    $navItems = [
        ['Chi siamo','#chi-siamo'],['Come funziona','#come-funziona'],
        ['Community','#community'],['Contatti','#contatti'],
    ];
    $steps = [
        ['01','Crea il tuo profilo','Mostra chi sei, cosa fai e quali sono i tuoi obiettivi professionali.',
            '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>'],
        ['02','Entra nei pianeti','Unisciti alla community più vicina a te e ai tuoi interessi.',
            '<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>'],
        ['03','Connettiti','Incontra professionisti selezionati e crea relazioni di valore.',
            '<path d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0M15 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>'],
        ['04','Genera opportunità','Collabora, condividi referral e fai crescere il tuo business.',
            '<path d="M13 10V3L4 14h7v7l9-11h-7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>'],
    ];
    $features = [
        ['Directory professionisti','Trova e fatti trovare dai professionisti giusti nella tua area.',
            '<path d="M21 21l-4.35-4.35M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>'],
        ['Mini sito personale','Presentati al meglio con il tuo spazio personale dedicato.',
            '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M9 22V12h6v10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>'],
        ['Eventi e incontri','Partecipa a eventi esclusivi e networking dal vivo in tutta Italia.',
            '<path d="M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>'],
        ['Referral e opportunità','Scambia segnalazioni, proponi collaborazioni, genera business.',
            '<path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8M16 6l-4-4-4 4m4-4v13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>'],
        ['Chat e connessioni','Comunica in modo diretto e senza filtri con i tuoi contatti.',
            '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>'],
        ['News e contenuti','Resta aggiornato su trend, novità e best practice del settore.',
            '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M14 2v6h6M16 13H8m8 4H8m2-8H8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>'],
    ];
    $stats = [['500+','Professionisti attivi'],['120+','Collaborazioni generate'],['20+','Pianeti attivi'],['15+','Eventi ogni mese']];
    $testimonials = [
        ['Kommunity mi ha permesso di entrare in contatto con clienti e partner in modo autentico e produttivo.','Francesca R.','Consulente HR','a'],
        ['Grazie ai pianeti ho trovato persone straordinarie con cui collaboro ogni giorno.','Alessandro T.','Imprenditore','b'],
        ['Un ambiente positivo, professionale e dove le relazioni contano davvero.','Giulia M.','Marketing Manager','c'],
    ];
    $footerExplore   = ['Chi siamo','Come funziona','Community'];
    $footerResources = ['Guide','FAQ','Storie di successo','Lavora con noi'];
@endphp

<div class="km-page">

{{-- HEADER --}}
<header class="site-header" role="banner">
    <div class="km-wrap header-inner">
        <a href="{{ route('home') }}" class="brand-lockup" aria-label="Kommunity home">
            <span class="brand-mark"><x-application-logo /></span>
            <span>Kommunity</span>
        </a>
        <nav class="site-nav" aria-label="Navigazione principale">
            @foreach($navItems as [$label,$href])
                <a href="{{ $href }}" class="nav-link">{{ $label }}</a>
            @endforeach
            @foreach($navPages as $np)
                <a href="{{ route('page.show', $np->slug) }}" class="nav-link">{{ $np->title }}</a>
            @endforeach
        </nav>
        <div class="header-cta">
            <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Accedi</a>
            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Entra nella community</a>
        </div>
        <button class="hamburger" aria-label="Apri menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<main>

{{-- HERO --}}
<section class="hero" aria-labelledby="hero-heading">
    <div class="hero-net" aria-hidden="true"></div>
    <div class="km-wrap hero-grid">
        <div>
            <div style="margin-bottom:1.5rem">
                <span class="badge badge-teal"><span class="badge-dot"></span>Piattaforma attiva · 500+ professionisti</span>
            </div>
            <h1 id="hero-heading">Le relazioni giuste<br><span class="accent">generano risultati reali.</span></h1>
            <p class="hero-body">Kommunity è l'ecosistema dove professionisti e aziende si connettono, collaborano e crescono insieme.</p>
            <div class="hero-actions">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                    Entra nella community
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
                <a href="#come-funziona" class="btn btn-ghost btn-lg">
                    Scopri come funziona
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.14v13.72c0 .8.9 1.27 1.56.82l10.04-6.86a1 1 0 0 0 0-1.64L9.56 4.32C8.9 3.87 8 4.34 8 5.14Z"/></svg>
                </a>
            </div>
            <div class="social-proof">
                <div class="avatar-stack" aria-hidden="true">
                    @for($i=1;$i<=8;$i++)<span class="av"></span>@endfor
                    <span class="av av-more">+</span>
                </div>
                <div class="social-text">
                    <strong>Già oltre 500 professionisti</strong>
                    <span>hanno scelto Kommunity per crescere</span>
                </div>
            </div>
        </div>

        <div class="dashboard-wrap">
            <div class="floating-card card-a" aria-hidden="true">
                <span class="fc-face fc-face-a"></span>
                <div class="fc-text"><strong>Marco Bianchi</strong><small>Digital Marketing</small></div>
                <span class="verified-dot"></span>
            </div>
            <div class="floating-card card-b" aria-hidden="true">
                <span class="fc-face fc-face-b"></span>
                <div class="fc-text"><strong>Sara Verdi</strong><small>Growth Designer</small></div>
                <span class="verified-dot"></span>
            </div>
            <div class="dash-card glass" role="img" aria-label="Anteprima dashboard Kommunity">
                <div class="dash-chrome">
                    <div><div class="dash-sub">Dashboard</div><div class="dash-name">Benvenuto, Marco! 👋</div></div>
                    <div class="dash-dots" aria-hidden="true"><span></span><span></span><span></span></div>
                </div>
                <div class="metric-row">
                    <div class="metric-cell"><strong>128</strong><span>Connessioni</span></div>
                    <div class="metric-cell"><strong>23</strong><span>Opportunità attive</span></div>
                    <div class="metric-cell"><strong>356</strong><span>Visite profilo</span></div>
                </div>
                <div class="activity-list">
                    @foreach(['Francesca ha richiesto una connessione','Nuova opportunità nel pianeta Roma','Alessandro ti ha inviato un referral'] as $act)
                        <div class="act-item"><span class="act-dot" aria-hidden="true"></span><span>{{ $act }}</span></div>
                    @endforeach
                </div>
                <div class="event-strip">
                    <div>
                        <p class="event-label">Prossimo evento</p>
                        <p class="event-name">Networking Day Roma</p>
                        <p class="event-meta">22 Mag · 18:30 · 62 iscritti</p>
                    </div>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Partecipa</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- COME FUNZIONA --}}
<section id="come-funziona" class="steps-section km-section" aria-labelledby="steps-heading">
    <div class="km-wrap">
        <div class="steps-header">
            <span class="badge badge-teal" style="margin-bottom:1rem"><span class="badge-dot"></span>Metodo Kommunity</span>
            <h2 id="steps-heading" style="font-size:clamp(2rem,4vw,3.2rem);font-weight:800;letter-spacing:-.045em;margin-top:.75rem">Come funziona Kommunity</h2>
            <p style="margin-top:.85rem;color:var(--muted);font-size:.95rem;max-width:520px;margin-inline:auto;line-height:1.72">Quattro passi per entrare in un ecosistema professionale che genera risultati concreti.</p>
        </div>
        <div class="steps-grid">
            @foreach($steps as [$num,$title,$copy,$icon])
                <article class="step-card">
                    <div style="display:flex;align-items:center;gap:.5rem">
                        <span class="step-icon-wrap"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">{!! $icon !!}</svg></span>
                        <span class="step-num-badge">{{ $num }}</span>
                    </div>
                    <h3>{{ $title }}</h3>
                    <p>{{ $copy }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ECOSISTEMA --}}
<section id="community" class="km-section" aria-labelledby="eco-heading">
    <div class="km-wrap eco-grid">
        <div>
            <span class="badge badge-green" style="margin-bottom:1.2rem"><span class="badge-dot"></span>L'ecosistema</span>
            <h2 id="eco-heading" class="eco-title">Non è un social.<br>È un sistema progettato<br>per <span class="accent">far crescere<br>il tuo business.</span></h2>
            <p style="margin-top:1.5rem;max-width:440px;font-size:.95rem;line-height:1.78;color:var(--muted)">Kommunity unisce persone, competenze e opportunità in un ambiente sicuro, selezionato e orientato ai risultati.</p>
            <a href="{{ route('register') }}" class="btn btn-ghost" style="margin-top:2rem">Scopri l'ecosistema <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        </div>
        <div class="feature-grid">
            @foreach($features as [$title,$copy,$icon])
                <article class="feat-card">
                    <span class="feat-icon"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" aria-hidden="true">{!! $icon !!}</svg></span>
                    <h3>{{ $title }}</h3>
                    <p>{{ $copy }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- STATISTICHE --}}
<div class="stats-band" role="region" aria-label="Statistiche Kommunity">
    <div class="km-wrap">
        <div class="stats-card glass">
            @foreach($stats as [$value,$label])
                <div class="stat-cell"><strong>{{ $value }}</strong><p>{{ $label }}</p></div>
            @endforeach
        </div>
    </div>
</div>

{{-- TESTIMONIANZE --}}
<section class="km-section" aria-labelledby="testi-heading">
    <div class="km-wrap split-grid">
        <div>
            <span class="badge badge-teal" style="margin-bottom:1.2rem"><span class="badge-dot"></span>Testimonianze</span>
            <h2 id="testi-heading" style="font-size:clamp(2rem,4vw,3.2rem);font-weight:800;letter-spacing:-.045em;line-height:1.08">Cosa dicono<br>i nostri membri</h2>
            <p style="margin-top:1.1rem;color:var(--muted);font-size:.9rem;line-height:1.72">Professionisti e aziende che hanno già scelto di far parte di Kommunity.</p>
            <a href="{{ route('register') }}" class="btn btn-ghost" style="margin-top:1.75rem">Leggi tutte le testimonianze →</a>
        </div>
        <div class="testi-grid">
            @foreach($testimonials as [$quote,$name,$role,$avClass])
                <article class="testi-card">
                    <div class="stars" aria-label="5 stelle">★★★★★</div>
                    <p class="testi-quote">"{{ $quote }}"</p>
                    <div class="testi-person">
                        <span class="tp-av tp-av-{{ $avClass }}" aria-hidden="true"></span>
                        <span><strong>{{ $name }}</strong><span>{{ $role }}</span></span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- COMMUNITY LOCALE --}}
<section id="community-locale" class="local-section km-section" aria-labelledby="local-heading">
    <div class="km-wrap local-grid">
        <div>
            <span class="badge badge-green" style="margin-bottom:1.2rem"><span class="badge-dot"></span>Pianeti in tutta Italia</span>
            <h2 id="local-heading" style="font-size:clamp(2rem,4vw,3.2rem);font-weight:800;letter-spacing:-.045em;line-height:1.08">Trova la tua community locale</h2>
            <p style="margin-top:1.1rem;max-width:440px;color:var(--muted);font-size:.9rem;line-height:1.72">Siamo presenti in tutta Italia con pianeti attivi. Trova quello più vicino a te e inizia a connetterti.</p>
            <p style="margin-top:1.35rem;font-size:.8rem;font-weight:800;color:rgba(214,228,236,.78)">Pianeti attivi</p>
            <div class="city-chips">
                @forelse($chapters as $chapter)
                    <span class="city-chip">{{ $chapter->city->name ?? $chapter->name }}</span>
                @empty
                    <span class="city-chip">Roma</span>
                    <span class="city-chip">Milano</span>
                    <span class="city-chip">Torino</span>
                    <span class="city-chip">Napoli</span>
                @endforelse
                <a href="{{ route('register') }}" class="city-chip" style="border-color:rgba(70,93,112,.52);color:var(--teal2)">Vedi tutte →</a>
            </div>
            <a href="{{ route('register') }}" class="btn btn-ghost" style="margin-top:1.75rem">Esplora tutti i pianeti →</a>
        </div>
        <div class="map-wrap">
            {{-- Mappa Italia con SVG reale e connessioni --}}
            <div class="italy-map-container" aria-hidden="true">
                <svg viewBox="0 0 300 440" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;overflow:visible">
                    <defs>
                        <filter id="glow-dot">
                            <feGaussianBlur stdDeviation="3" result="blur"/>
                            <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                        </filter>
                        <filter id="glow-land">
                            <feGaussianBlur stdDeviation="4" result="blur"/>
                            <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                        </filter>
                    </defs>

                    {{-- === PENISOLA ITALIANA — path geografico preciso === --}}
                    <path d="M182,14 L188,16 L194,20 L198,26 L196,32 L190,36 L186,40 L188,44
                             L194,46 L200,50 L204,56 L202,63 L196,67 L192,72 L196,77 L202,81
                             L208,86 L210,93 L206,99 L200,103 L196,109 L198,116 L204,121
                             L210,127 L212,135 L208,142 L202,147 L198,153 L200,160 L206,166
                             L210,174 L208,181 L202,186 L196,190 L198,197 L204,203 L208,211
                             L206,219 L198,225 L192,230 L190,237 L194,244 L198,252 L196,260
                             L188,266 L180,270 L176,277 L178,285 L184,292 L188,300 L184,308
                             L176,314 L168,318 L162,324 L158,332 L154,340 L148,346 L140,350
                             L132,352 L124,350 L118,344 L116,336 L120,328 L126,321 L128,313
                             L122,306 L114,300 L108,294 L106,286 L110,278 L116,271 L118,263
                             L112,256 L104,250 L98,243 L96,235 L100,227 L106,220 L108,212
                             L102,205 L94,198 L88,190 L86,182 L90,174 L96,167 L98,158
                             L92,150 L84,143 L78,135 L76,127 L80,119 L86,112 L90,104
                             L86,96 L80,88 L76,80 L78,72 L84,65 L90,58 L92,50
                             L88,42 L84,34 L86,26 L92,20 L100,15 L110,12 L122,11
                             L134,12 L146,14 L158,15 L170,14 L182,14 Z"
                          fill="rgba(55,85,105,.55)" stroke="rgba(110,160,190,.60)" stroke-width="1.5"
                          filter="url(#glow-land)"/>

                    {{-- === SARDEGNA === --}}
                    <path d="M50,222 L56,216 L64,214 L72,216 L78,222 L80,230 L78,238
                             L72,246 L66,252 L60,258 L56,265 L54,272 L56,278 L52,282
                             L46,280 L40,274 L38,266 L40,258 L46,252 L48,244 L44,236
                             L44,228 L50,222 Z"
                          fill="rgba(55,85,105,.50)" stroke="rgba(110,160,190,.55)" stroke-width="1.2"/>

                    {{-- === SICILIA === --}}
                    <path d="M96,388 L106,382 L118,379 L130,380 L142,384 L152,390
                             L158,398 L156,407 L148,413 L136,416 L124,414 L112,408
                             L104,400 L96,388 Z"
                          fill="rgba(55,85,105,.50)" stroke="rgba(110,160,190,.55)" stroke-width="1.2"/>

                    {{-- Linee di connessione tra pianeti (raggi dal centro) --}}
                    @php
                        // Coordinate calibrate sul path geografico aggiornato
                        $cityCoords = [
                            'Roma'     => [140, 258],
                            'Milano'   => [110, 62],
                            'Torino'   => [92,  68],
                            'Bologna'  => [138, 126],
                            'Firenze'  => [130, 162],
                            'Napoli'   => [162, 302],
                            'Verona'   => [146, 100],
                            'Padova'   => [158, 93],
                            'Venezia'  => [170, 85],
                            'Genova'   => [102, 108],
                            'Bari'     => [192, 284],
                            'Palermo'  => [110, 402],
                            'Catania'  => [138, 408],
                            'Cagliari' => [55,  258],
                            'Lazio'    => [140, 258],
                        ];
                        $centerX = 140; $centerY = 258; // Roma come hub
                        $chapterDots = [];
                        foreach ($chapters as $ch) {
                            $cityName = $ch->city->name ?? $ch->name;
                            foreach ($cityCoords as $k => $v) {
                                if (stripos($cityName, $k) !== false || stripos($k, $cityName) !== false) {
                                    $chapterDots[$cityName] = $v; break;
                                }
                            }
                        }
                        if (empty($chapterDots)) {
                            // Fallback: solo Roma (unico pianeta attivo al momento)
                            $chapterDots = ['Roma' => [148, 265]];
                        }
                    @endphp

                    {{-- Ellissi orbitali concentriche (stile immagine riferimento) --}}
                    <ellipse cx="{{ $centerX }}" cy="{{ $centerY }}" rx="60"  ry="30"  fill="none" stroke="rgba(111,163,103,.12)" stroke-width=".8" transform="rotate(-30,{{ $centerX }},{{ $centerY }})"/>
                    <ellipse cx="{{ $centerX }}" cy="{{ $centerY }}" rx="100" ry="55"  fill="none" stroke="rgba(111,163,103,.09)" stroke-width=".8" transform="rotate(-30,{{ $centerX }},{{ $centerY }})"/>
                    <ellipse cx="{{ $centerX }}" cy="{{ $centerY }}" rx="145" ry="80"  fill="none" stroke="rgba(111,163,103,.07)" stroke-width=".8" transform="rotate(-30,{{ $centerX }},{{ $centerY }})"/>
                    <ellipse cx="{{ $centerX }}" cy="{{ $centerY }}" rx="190" ry="108" fill="none" stroke="rgba(111,163,103,.05)" stroke-width=".8" transform="rotate(-30,{{ $centerX }},{{ $centerY }})"/>

                    {{-- Linee di connessione verso Roma --}}
                    @foreach($chapterDots as $cityName => [$cx,$cy])
                        @if(!in_array($cityName, ['Roma','Lazio']))
                        <line x1="{{ $centerX }}" y1="{{ $centerY }}" x2="{{ $cx }}" y2="{{ $cy }}"
                              stroke="rgba(111,163,103,.25)" stroke-width=".8"/>
                        @endif
                    @endforeach

                    {{-- Dots pianeti --}}
                    @foreach($chapterDots as $cityName => [$cx,$cy])
                        @php $isHub = in_array($cityName, ['Roma','Lazio']); @endphp
                        {{-- alone esterno --}}
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $isHub ? 14 : 9 }}"
                                fill="{{ $isHub ? 'rgba(111,163,103,.15)' : 'rgba(111,163,103,.10)' }}"/>
                        {{-- cerchio medio --}}
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $isHub ? 8 : 5 }}"
                                fill="{{ $isHub ? 'rgba(111,163,103,.30)' : 'rgba(111,163,103,.22)' }}"
                                filter="url(#glow-dot)"/>
                        {{-- dot centrale --}}
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $isHub ? 4 : 3 }}"
                                fill="#b8f5a0"
                                filter="url(#glow-dot)"
                                style="{{ $isHub ? 'animation:pulse-dot 2s ease-in-out infinite' : '' }}"/>
                        {{-- etichetta città --}}
                        @php $label = in_array($cityName, ['Roma','Lazio']) ? 'Roma' : $cityName; @endphp
                        <text x="{{ $cx + ($isHub ? 10 : 8) }}" y="{{ $cy + 4 }}"
                              font-size="{{ $isHub ? 9 : 7.5 }}" fill="rgba(214,228,236,.85)"
                              font-family="Plus Jakarta Sans,sans-serif" font-weight="700">{{ $label }}</text>
                    @endforeach
                </svg>
            </div>
            @php $firstChapter = $chapters->first(); $cardCity = $firstChapter?->city?->name ?? 'Roma'; @endphp
            <div class="city-card-abs glass">
                <p style="font-size:1.1rem;font-weight:800">{{ $cardCity }}</p>
                <p style="font-size:.78rem;color:var(--teal2);margin-top:.25rem">Pianeta attivo</p>
                <a href="{{ route('register') }}" style="display:inline-flex;margin-top:.9rem;font-size:.8rem;font-weight:800;color:var(--brand3)">Scopri il pianeta →</a>
            </div>
        </div>
    </div>
</section>

{{-- VIDEO --}}
<section class="km-section" aria-labelledby="video-heading">
    <div class="km-wrap video-grid">
        <div class="video-player glass" role="img" aria-label="Anteprima video Kommunity">
            <button type="button" class="play-btn" aria-label="Guarda il video di presentazione">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.14v13.72c0 .8.9 1.27 1.56.82l10.04-6.86a1 1 0 0 0 0-1.64L9.56 4.32C8.9 3.87 8 4.34 8 5.14Z"/></svg>
            </button>
            <div class="video-brand brand-lockup" style="font-size:.95rem;color:rgba(240,246,250,.82)">
                <span class="brand-mark"><x-application-logo /></span><span>Kommunity</span>
            </div>
        </div>
        <div>
            <span class="badge badge-green" style="margin-bottom:1.2rem"><span class="badge-dot"></span>Video · 60 secondi</span>
            <h2 id="video-heading" style="font-size:clamp(2rem,4vw,3.2rem);font-weight:800;letter-spacing:-.045em;line-height:1.08">Scopri Kommunity<br><span class="accent">in 60 secondi</span></h2>
            <p style="margin-top:1.2rem;max-width:440px;font-size:.95rem;line-height:1.78;color:var(--muted)">Guarda il video e scopri come Kommunity può trasformare le tue relazioni in risultati concreti per il tuo business.</p>
            <a href="{{ route('register') }}" class="btn btn-ghost" style="margin-top:2rem">Guarda il video <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.14v13.72c0 .8.9 1.27 1.56.82l10.04-6.86a1 1 0 0 0 0-1.64L9.56 4.32C8.9 3.87 8 4.34 8 5.14Z"/></svg></a>
        </div>
    </div>
</section>

{{-- CTA FINALE --}}
<section id="cta-finale" class="cta-section" aria-labelledby="cta-heading">
    <div class="km-wrap cta-inner">
        <div>
            <h2 id="cta-heading" class="cta-title">Pronto a trasformare<br>le relazioni in risultati?</h2>
            <p class="cta-body">Entra oggi in Kommunity e inizia a costruire il tuo futuro professionale tra persone che fanno la differenza.</p>
        </div>
        <a href="{{ route('register') }}" class="btn btn-primary btn-lg" style="flex-shrink:0">
            Entra nella community
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>
</section>

</main>

{{-- FOOTER --}}
<footer class="site-footer" role="contentinfo">
    <div class="km-wrap footer-grid">
        <div>
            <a href="{{ route('home') }}" class="brand-lockup" style="font-size:1.1rem">
                <span class="brand-mark"><x-application-logo /></span><span>Kommunity</span>
            </a>
            <p style="margin-top:1rem;max-width:270px;font-size:.78rem;line-height:1.72;color:var(--muted)">Kommunity è l'ecosistema che connette professionisti e aziende per generare valore, collaborare e crescere insieme.</p>
            <div class="social-icons" aria-label="Social media">
                <a href="#" class="soc-icon" aria-label="LinkedIn">in</a>
                <a href="#" class="soc-icon" aria-label="Facebook">f</a>
                <a href="#" class="soc-icon" aria-label="Instagram">IG</a>
                <a href="#" class="soc-icon" aria-label="X">X</a>
            </div>
        </div>
        <nav aria-label="Esplora">
            <p class="footer-col-title">Esplora</p>
            @foreach($footerExplore as $link)
                <a href="#{{ \Illuminate\Support\Str::slug($link) }}" class="footer-link">{{ $link }}</a>
            @endforeach
            @foreach($footerPages as $fp)
                <a href="{{ route('page.show', $fp->slug) }}" class="footer-link">{{ $fp->title }}</a>
            @endforeach
        </nav>
        <nav aria-label="Risorse">
            <p class="footer-col-title">Risorse</p>
            @foreach($footerResources as $link)
                <a href="#" class="footer-link">{{ $link }}</a>
            @endforeach
        </nav>
        <address style="font-style:normal">
            <p class="footer-col-title">Contatti</p>
            <a href="mailto:info@kommunity.it" class="footer-link">info@kommunity.it</a>
            <a href="tel:+390678216530" class="footer-link">+39 06.7821653</a>
            <p class="footer-link" style="cursor:default;line-height:1.7">KNM Srl<br>Via Eurialo, 56<br>00181 Roma (IT)</p>
        </address>
        <div>
            <p class="footer-col-title">Resta aggiornato</p>
            <p style="font-size:.78rem;color:var(--muted);margin-bottom:.75rem">Iscriviti alla newsletter</p>
            @if(session('newsletter_success'))
                <p style="font-size:.8rem;color:var(--brand3);font-weight:700;padding:.6rem .8rem;border-radius:.4rem;background:rgba(85,121,79,.12);border:1px solid rgba(85,121,79,.3);margin-bottom:.75rem">✓ Iscritto con successo!</p>
            @endif
            <form class="newsletter-form" method="POST" action="{{ route('newsletter.subscribe') }}" aria-label="Iscrizione newsletter">
                @csrf
                <input type="email" name="email" placeholder="La tua email" autocomplete="email" aria-label="Indirizzo email" required>
                <button type="submit" aria-label="Iscriviti">→</button>
            </form>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="km-wrap footer-bottom-inner">
            <p>© {{ date('Y') }} KNM Srl · P.IVA 13273091002 · Tutti i diritti riservati</p>
            <nav class="footer-bottom-links" aria-label="Link legali">
                <a href="#">Privacy Policy</a>
                <a href="#">Termini e condizioni</a>
                <a href="#">Cookie Policy</a>
            </nav>
        </div>
    </div>
</footer>

</div>

<script>
    const hdr = document.querySelector('.site-header');
    window.addEventListener('scroll', () => {
        hdr.style.boxShadow = window.scrollY > 40 ? '0 8px 40px rgba(0,0,0,.35)' : 'none';
    }, { passive: true });

    const hamburger = document.querySelector('.hamburger');
    const siteNav   = document.querySelector('.site-nav');
    hamburger?.addEventListener('click', () => {
        const open = hamburger.getAttribute('aria-expanded') === 'true';
        hamburger.setAttribute('aria-expanded', String(!open));
        if (!open) {
            siteNav.style.cssText = 'display:flex;flex-direction:column;gap:1rem;position:absolute;top:100%;left:0;right:0;background:rgba(7,17,26,.97);padding:1.5rem 1.25rem;border-bottom:1px solid rgba(255,255,255,.08);z-index:90;backdrop-filter:blur(20px)';
        } else { siteNav.removeAttribute('style'); }
    });

    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const target = document.querySelector(a.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                if (hamburger?.getAttribute('aria-expanded') === 'true') hamburger.click();
            }
        });
    });

    const iObs = new IntersectionObserver((entries) => {
        entries.forEach(({ target, isIntersecting }) => {
            if (isIntersecting) { target.style.opacity='1'; target.style.transform='translateY(0)'; iObs.unobserve(target); }
        });
    }, { threshold: 0.12 });
    document.querySelectorAll('.step-card,.feat-card,.testi-card,.metric-cell,.stat-cell,.act-item').forEach(el => {
        'opacity:0;transform:translateY(18px);transition:opacity .55s ease,transform .55s ease';
        iObs.observe(el);
    });
</script>
</body>
</html>