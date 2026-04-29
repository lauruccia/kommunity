<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kommunity - Relazioni giuste. Opportunità reali.</title>
    <meta name="description" content="Kommunity è l'ecosistema dove professionisti e aziende di Roma e del Lazio si connettono, collaborano e crescono insieme.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root{
            --bg:#07111a;
            --bg2:#0d1e2b;
            --bg3:#031018;
            --brand:#55794F;
            --brand2:#426240;
            --brand3:#6fa367;
            --brand4:#8fcf7d;
            --teal:#465D70;
            --teal2:#6e90a8;
            --text:#F0F6FA;
            --muted:#8FAAB8;
            --line:rgba(255,255,255,.08);
            --soft:rgba(255,255,255,.055);
        }
        *{box-sizing:border-box}
        html{scroll-behavior:smooth;overflow-x:hidden}
        body{margin:0;overflow-x:hidden;background:var(--bg);color:var(--text);font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui,sans-serif;-webkit-font-smoothing:antialiased}
        a{text-decoration:none;color:inherit}
        button,input{font:inherit}
        .km-page{min-height:100vh;background:
            radial-gradient(ellipse 70% 48% at 78% 0%,rgba(70,93,112,.20),transparent 62%),
            radial-gradient(ellipse 42% 32% at 12% 10%,rgba(85,121,79,.13),transparent 66%),
            linear-gradient(180deg,#06111a 0%,#0d1e2b 49%,#06111a 100%)}
        .km-wrap{width:min(100% - 5.5rem,1420px);margin-inline:auto}
        .km-section{padding:5.25rem 0}
        .glass{background:linear-gradient(145deg,rgba(255,255,255,.085),rgba(255,255,255,.028));border:1px solid var(--line);backdrop-filter:blur(20px) saturate(140%);box-shadow:0 28px 80px rgba(0,0,0,.32),inset 0 1px 0 rgba(255,255,255,.07)}
        .accent{color:var(--brand4);text-shadow:0 0 28px rgba(111,163,103,.22)}
        .muted{color:var(--muted)}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:.65rem;border-radius:.46rem;padding:.82rem 1.32rem;font-size:.82rem;font-weight:800;white-space:nowrap;border:1px solid transparent;cursor:pointer;transition:.22s ease}
        .btn:hover{transform:translateY(-2px)}
        .btn-primary{color:#07111a;background:linear-gradient(135deg,var(--brand4),var(--brand3) 54%,var(--brand));box-shadow:0 16px 36px rgba(111,163,103,.25),inset 0 1px 0 rgba(255,255,255,.30)}
        .btn-primary:hover{box-shadow:0 22px 54px rgba(111,163,103,.38)}
        .btn-ghost{color:var(--text);background:rgba(255,255,255,.045);border-color:rgba(255,255,255,.14)}
        .btn-ghost:hover{border-color:rgba(111,163,103,.45);background:rgba(111,163,103,.08)}
        .btn-sm{padding:.58rem .95rem;font-size:.76rem}.btn-lg{padding:1rem 1.62rem;font-size:.9rem}
        .brand-lockup{display:inline-flex;align-items:center;gap:.55rem;font-weight:800;font-size:1.14rem;letter-spacing:-.04em;color:var(--text)}
        .brand-mark{width:2.2rem;height:2.2rem;display:flex;align-items:center;justify-content:center;filter:drop-shadow(0 0 12px rgba(111,163,103,.30))}.brand-mark img{width:1.35rem;height:2.05rem;object-fit:contain;display:block}
        .badge{display:inline-flex;align-items:center;gap:.45rem;padding:.34rem .82rem;border-radius:999px;font-size:.70rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase}.badge-dot{width:.42rem;height:.42rem;border-radius:999px;background:currentColor;box-shadow:0 0 9px currentColor}.badge-green{background:rgba(85,121,79,.13);border:1px solid rgba(85,121,79,.34);color:var(--brand4)}.badge-teal{background:rgba(70,93,112,.15);border:1px solid rgba(70,93,112,.38);color:var(--teal2)}

        /* HEADER */
        .site-header{position:sticky;top:0;z-index:80;background:rgba(6,17,26,.76);border-bottom:1px solid rgba(255,255,255,.06);backdrop-filter:blur(24px) saturate(150%)}
        .header-inner{display:flex;align-items:center;justify-content:space-between;gap:2rem;padding:1rem 0}.site-nav{display:flex;align-items:center;gap:2.1rem}.nav-link{font-size:.78rem;font-weight:700;color:rgba(240,246,250,.73);transition:color .2s}.nav-link:hover{color:var(--brand4)}.header-cta{display:flex;align-items:center;gap:.75rem}.hamburger{display:none;flex-direction:column;gap:.32rem;cursor:pointer;padding:.3rem;background:transparent;border:0}.hamburger span{display:block;width:1.35rem;height:2px;border-radius:2px;background:var(--text)}

        /* HERO IDENTICA AL MOCKUP */
        .hero{position:relative;overflow:hidden;min-height:780px;border-bottom:1px solid rgba(111,163,103,.36);background:linear-gradient(90deg,rgba(7,17,26,.96) 0%,rgba(7,17,26,.92) 43%,rgba(7,17,26,.68) 100%)}
        .hero::before{content:"";position:absolute;inset:0;background:radial-gradient(ellipse 42% 70% at 92% 46%,rgba(70,93,112,.30),transparent 67%),radial-gradient(ellipse 30% 30% at 73% 42%,rgba(85,121,79,.18),transparent 62%);pointer-events:none}
        .hero::after{content:"K";position:absolute;right:28%;top:1.8rem;font-size:clamp(28rem,49vw,52rem);line-height:.8;font-weight:800;color:rgba(70,93,112,.20);text-shadow:0 0 80px rgba(85,121,79,.12);pointer-events:none;user-select:none;transform:skewX(-5deg)}
        .hero-line-bottom{position:absolute;left:0;right:0;bottom:0;height:2px;background:linear-gradient(90deg,var(--teal),var(--brand4));opacity:.9}
        .hero-grid{position:relative;z-index:2;display:grid;grid-template-columns:minmax(0,680px) minmax(540px,1fr);gap:4rem;align-items:center;padding:6.25rem 0 6rem}
        .hero-kicker{display:none}.hero h1{font-size:clamp(3.25rem,4.65vw,5.2rem);line-height:1.02;letter-spacing:-.07em;font-weight:800;margin:0;max-width:720px}.hero h1 .title-line{display:block;white-space:nowrap}.hero-body{margin-top:2.15rem;max-width:590px;font-size:1.08rem;line-height:1.86;color:rgba(230,239,245,.94);font-weight:600}.hero-actions{display:flex;flex-wrap:wrap;gap:1rem;margin-top:2.65rem}.social-proof{margin-top:2.65rem}.social-label{font-size:.92rem;font-weight:700;color:rgba(214,228,236,.76);margin-bottom:1rem}.avatar-stack{display:flex;align-items:center}.av{width:2.58rem;height:2.58rem;border-radius:999px;border:2px solid rgba(255,255,255,.25);margin-left:-.52rem;box-shadow:0 8px 20px rgba(0,0,0,.30);background-size:cover;background-position:center}.av:first-child{margin-left:0}.av:nth-child(1){background:linear-gradient(135deg,#d8f3dc,#7dd3fc)}.av:nth-child(2){background:linear-gradient(135deg,#fde68a,#f97316)}.av:nth-child(3){background:linear-gradient(135deg,#e0e7ff,#6366f1)}.av:nth-child(4){background:linear-gradient(135deg,#d1fae5,#10b981)}.av:nth-child(5){background:linear-gradient(135deg,#fce7f3,#ec4899)}.av:nth-child(6){background:linear-gradient(135deg,#ffe4e6,#f43f5e)}.av:nth-child(7){background:linear-gradient(135deg,#f0f9ff,#0ea5e9)}.av-more{display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:900;background:rgba(7,17,26,.78)!important;color:var(--text)}
        .network-hero{position:relative;min-height:540px;transform:translateX(1rem)}.network-hero::before{content:"";position:absolute;inset:5% -7% 0 -4%;background:radial-gradient(circle at 55% 43%,rgba(111,163,103,.16),transparent 34%),radial-gradient(circle at 78% 35%,rgba(110,144,168,.18),transparent 30%);filter:blur(2px);pointer-events:none}.network-canvas{position:absolute;inset:-1rem -2.5rem -1rem -1rem;opacity:.98}.network-canvas svg{width:100%;height:100%;overflow:visible}.net-line{stroke:rgba(110,144,168,.42);stroke-width:1.1}.net-line-strong{stroke:rgba(111,163,103,.46)}.net-node{fill:var(--brand4);filter:drop-shadow(0 0 9px rgba(111,163,103,.95))}.net-node-blue{fill:#8fb4c9;filter:drop-shadow(0 0 10px rgba(110,144,168,.8))}.net-orb{fill:rgba(12,25,38,.86);stroke:rgba(255,255,255,.16);stroke-width:1.3;filter:drop-shadow(0 22px 34px rgba(0,0,0,.38))}.hero-member-card{position:absolute;z-index:4;display:grid;grid-template-columns:3rem 1fr;gap:.85rem;align-items:center;width:16.5rem;padding:1.05rem;border-radius:1rem;background:rgba(7,20,31,.84);border:1px solid rgba(255,255,255,.12);box-shadow:0 28px 60px rgba(0,0,0,.38),inset 0 1px 0 rgba(255,255,255,.08);backdrop-filter:blur(18px)}.hero-member-card.card-marco{right:2%;top:10%}.hero-member-card.card-sara{left:7%;top:42%}.hero-member-card.card-luca{right:1%;bottom:13%}.member-face{width:3rem;height:3rem;border-radius:999px;border:2px solid rgba(255,255,255,.24);background:linear-gradient(135deg,#f8fafc,#6e90a8 56%,#2e5970)}.face-sara{background:linear-gradient(135deg,#fde68a,#f97316 56%,#b45309)}.face-luca{background:linear-gradient(135deg,#e0e7ff,#6366f1 56%,#1d4ed8)}.member-name{font-size:1rem;font-weight:900;line-height:1.05}.member-role{font-size:.70rem;color:var(--muted);margin-top:.18rem}.member-verified{grid-column:1/-1;display:flex;align-items:center;gap:.38rem;color:var(--brand4);font-size:.72rem;font-weight:800;margin-top:.15rem}.member-verified span{width:.62rem;height:.62rem;border-radius:999px;background:var(--brand4);box-shadow:0 0 10px rgba(111,163,103,.9)}.connect-pill{grid-column:1/-1;display:flex;align-items:center;justify-content:center;gap:.35rem;height:1.9rem;border:1px solid rgba(111,163,103,.34);border-radius:.48rem;color:#eaf7e5;background:rgba(85,121,79,.10);font-size:.76rem;font-weight:900}.hero-orb-icon{position:absolute;z-index:3;width:4.1rem;height:4.1rem;border-radius:999px;display:flex;align-items:center;justify-content:center;background:rgba(12,25,38,.84);border:1px solid rgba(255,255,255,.15);box-shadow:0 20px 40px rgba(0,0,0,.33),0 0 34px rgba(110,144,168,.20);backdrop-filter:blur(12px)}.hero-orb-icon svg{width:1.85rem;height:1.85rem;color:#e7f0f6}.orb-a{left:34%;top:30%}.orb-b{right:10%;top:36%}.orb-c{left:52%;bottom:10%}.orb-d{right:-2%;top:18%}

        /* STEPS */
        .steps-section{background:linear-gradient(180deg,rgba(13,30,43,.78),rgba(7,17,26,.70));border-top:1px solid rgba(70,93,112,.28);border-bottom:1px solid rgba(85,121,79,.28)}.steps-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.8rem}.step-card{position:relative;border-radius:.85rem;padding:1.45rem 1.2rem;background:linear-gradient(145deg,rgba(255,255,255,.062),rgba(255,255,255,.020));border:1px solid var(--line);transition:.22s}.step-card:hover{transform:translateY(-4px);border-color:rgba(111,163,103,.38);box-shadow:0 22px 55px rgba(111,163,103,.09)}.step-card:not(:last-child)::after{content:"›";position:absolute;right:-.82rem;top:50%;transform:translateY(-50%);font-size:2.3rem;color:var(--brand4);opacity:.75;z-index:2}.step-icon-wrap{width:3.1rem;height:3.1rem;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;color:var(--teal2);border:1px solid rgba(70,93,112,.44);background:rgba(70,93,112,.10)}.step-num-badge{display:inline-flex;align-items:center;justify-content:center;width:1.85rem;height:1.85rem;border-radius:999px;font-size:.72rem;font-weight:900;color:var(--brand4);background:rgba(85,121,79,.18);margin-left:.6rem}.step-card h3{font-size:1.05rem;font-weight:900;margin:1.08rem 0 0}.step-card p{font-size:.80rem;color:var(--muted);margin:.52rem 0 0;line-height:1.65}

        /* ECOSISTEMA */
        .eco-grid{display:grid;grid-template-columns:.86fr 1.14fr;gap:4.5rem;align-items:center}.section-title{font-size:clamp(2.25rem,4.2vw,4.25rem);font-weight:800;line-height:1.04;letter-spacing:-.055em;margin:0}.section-copy{margin-top:1.4rem;max-width:460px;font-size:.95rem;line-height:1.78;color:var(--muted)}.feature-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.82rem}.feat-card{border-radius:.78rem;padding:1.32rem;background:linear-gradient(145deg,rgba(255,255,255,.068),rgba(255,255,255,.022));border:1px solid var(--line);transition:.22s}.feat-card:hover{transform:translateY(-5px);border-color:rgba(111,163,103,.36);background:linear-gradient(145deg,rgba(85,121,79,.10),rgba(255,255,255,.026));box-shadow:0 24px 60px rgba(111,163,103,.08)}.feat-icon{width:2.75rem;height:2.75rem;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;color:var(--brand4);border:1px solid rgba(85,121,79,.38);background:rgba(85,121,79,.09);margin-bottom:.9rem}.feat-card h3{font-size:.9rem;font-weight:900;margin:0}.feat-card p{font-size:.77rem;color:var(--muted);margin:.45rem 0 0;line-height:1.62}
        .stats-band{padding:1.8rem 0;background:radial-gradient(ellipse 30% 80% at 10% 50%,rgba(70,93,112,.14),transparent),linear-gradient(90deg,rgba(255,255,255,.025),rgba(255,255,255,.055),rgba(255,255,255,.025))}.stats-card{display:grid;grid-template-columns:repeat(4,1fr);border-radius:1.1rem;overflow:hidden}.stat-cell{padding:1.7rem 1rem;text-align:center;border-right:1px solid rgba(255,255,255,.10)}.stat-cell:last-child{border-right:0}.stat-cell strong{display:block;font-size:clamp(2.35rem,4vw,3.8rem);font-weight:900;line-height:1;color:var(--brand4);letter-spacing:-.06em;text-shadow:0 0 42px rgba(111,163,103,.35)}.stat-cell p{font-size:.82rem;font-weight:700;color:rgba(214,228,236,.78);margin:.82rem 0 0}
        .split-grid{display:grid;grid-template-columns:.72fr 1.28fr;gap:4rem;align-items:start}.testi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.85rem}.testi-card{border-radius:.78rem;padding:1.45rem;background:linear-gradient(145deg,rgba(255,255,255,.068),rgba(255,255,255,.022));border:1px solid var(--line);transition:.22s}.testi-card:hover{transform:translateY(-4px);border-color:rgba(111,163,103,.36);box-shadow:0 22px 56px rgba(111,163,103,.08)}.stars{font-size:.88rem;color:var(--brand4);letter-spacing:.12em}.testi-quote{margin-top:1rem;font-size:.82rem;line-height:1.72;color:rgba(214,228,236,.90)}.testi-person{display:flex;align-items:center;gap:.7rem;margin-top:1.35rem}.tp-av{width:2.2rem;height:2.2rem;border-radius:999px;border:2px solid rgba(255,255,255,.20);flex-shrink:0}.tp-av-a{background:linear-gradient(135deg,#d8f3dc,#6e90a8)}.tp-av-b{background:linear-gradient(135deg,#fde68a,#f97316)}.tp-av-c{background:linear-gradient(135deg,#e0e7ff,#6366f1)}.testi-person strong{display:block;font-size:.82rem;font-weight:900}.testi-person span{display:block;font-size:.73rem;color:var(--muted)}

        /* MAPPA ITALIA COME MOCKUP */
        .local-section{background:linear-gradient(145deg,rgba(13,30,43,.94),rgba(7,17,26,.90));border-top:1px solid var(--line);border-bottom:1px solid var(--line)}.local-grid{display:grid;grid-template-columns:.86fr 1.14fr;gap:4.5rem;align-items:center}.search-bar{display:flex;align-items:center;justify-content:space-between;margin-top:1.8rem;padding:.86rem 1.1rem;border-radius:.54rem;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.048);color:#607888;font-size:.84rem}.city-chips{display:flex;flex-wrap:wrap;gap:.55rem;margin-top:1rem}.city-chip{padding:.52rem .88rem;border-radius:.45rem;font-size:.76rem;font-weight:800;color:rgba(220,232,240,.88);border:1px solid rgba(85,121,79,.32);background:rgba(255,255,255,.03);transition:.2s}.city-chip:hover{border-color:var(--brand4);background:rgba(111,163,103,.08);color:var(--brand4)}.map-wrap{position:relative;min-height:430px;display:flex;align-items:center;justify-content:center}.italy-map-art{position:relative;width:min(720px,100%);min-height:430px;display:flex;align-items:center;justify-content:center}.italy-map-art::before{content:"";position:absolute;inset:-12% -10%;background:radial-gradient(ellipse 56% 70% at 54% 54%,rgba(70,93,112,.22),transparent 64%),radial-gradient(ellipse 70% 48% at 65% 50%,rgba(111,163,103,.13),transparent 68%);border-radius:50%;opacity:.95}.italy-map-img{position:relative;z-index:1;width:min(610px,100%);height:auto;display:block;filter:drop-shadow(0 0 34px rgba(111,163,103,.22)) drop-shadow(0 34px 80px rgba(0,0,0,.34))}.map-city-card{position:absolute;right:0;bottom:3.2rem;z-index:3;width:15.5rem;border-radius:.86rem;padding:1rem}.city-thumb{width:4.2rem;height:4.2rem;border-radius:.55rem;background:linear-gradient(135deg,#6e90a8,#223a4c 55%,#55794F);float:left;margin-right:.85rem}.map-note{clear:both;padding-top:.2rem}.video-grid{display:grid;grid-template-columns:1fr 1fr;gap:3.6rem;align-items:center}.video-player{position:relative;min-height:310px;border-radius:.85rem;overflow:hidden;background:linear-gradient(135deg,rgba(255,255,255,.12),rgba(7,17,26,.9)),radial-gradient(circle at 32% 34%,rgba(70,93,112,.22),transparent 14rem)}.video-player::before{content:"";position:absolute;inset:0;background:linear-gradient(130deg,rgba(255,255,255,.07),transparent 44%),repeating-linear-gradient(90deg,transparent 0 34px,rgba(255,255,255,.022) 34px 35px)}.play-btn{position:absolute;inset:0;margin:auto;width:5.2rem;height:5.2rem;border-radius:999px;display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,.82);background:rgba(255,255,255,.09);color:var(--text);cursor:pointer;transition:.22s}.play-btn:hover{background:rgba(85,121,79,.22);transform:scale(1.06)}.video-brand{position:absolute;bottom:1.5rem;left:1.5rem}.cta-section{position:relative;overflow:hidden;border-top:1px solid rgba(70,93,112,.24);border-bottom:1px solid rgba(85,121,79,.30);background:radial-gradient(ellipse 55% 60% at 78% 100%,rgba(85,121,79,.28),transparent),linear-gradient(135deg,rgba(13,30,43,.95),rgba(7,17,26,.97))}.cta-section::before{content:"K";position:absolute;left:2%;bottom:-9rem;font-size:clamp(16rem,26vw,32rem);line-height:.8;font-weight:800;color:rgba(70,93,112,.12);text-shadow:0 0 60px rgba(85,121,79,.18)}.cta-inner{position:relative;z-index:1;display:grid;grid-template-columns:1fr auto;gap:2.5rem;align-items:center;padding:4rem 0}.cta-title{font-size:clamp(2rem,4.5vw,4rem);font-weight:800;line-height:1.05;letter-spacing:-.05em;margin:0}.cta-body{margin-top:1.2rem;font-size:.95rem;color:rgba(214,228,236,.80);line-height:1.75;max-width:520px}.site-footer{background:#03111a;border-top:1px solid rgba(255,255,255,.07)}.footer-grid{display:grid;grid-template-columns:1.5fr .72fr .85fr .95fr 1fr;gap:2rem;padding:2.5rem 0}.footer-col-title{font-size:.76rem;font-weight:900;color:#dce8f2;margin:0 0 .9rem}.footer-link{display:block;font-size:.76rem;color:var(--muted);margin-top:.5rem;transition:.2s}.footer-link:hover{color:var(--brand4)}.newsletter-form{display:flex;gap:.4rem;border:1px solid rgba(85,121,79,.38);border-radius:.52rem;padding:.32rem;background:rgba(255,255,255,.032)}.newsletter-form input{flex:1;min-width:0;background:transparent;border:0;outline:0;color:var(--text);font-size:.77rem;padding:.52rem .65rem}.newsletter-form button{width:2.15rem;border-radius:.38rem;background:var(--brand);color:#edf5ea;font-weight:900;font-size:1rem;cursor:pointer;border:0}.social-icons{display:flex;gap:.7rem;margin-top:1rem}.soc-icon{width:2.15rem;height:2.15rem;border-radius:999px;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04);color:var(--muted);font-size:.78rem;font-weight:900;transition:.2s}.soc-icon:hover{border-color:var(--brand4);color:var(--brand4);background:rgba(111,163,103,.08)}.footer-bottom{border-top:1px solid rgba(255,255,255,.07);padding:1.2rem 0}.footer-bottom-inner{display:flex;align-items:center;justify-content:space-between;gap:1rem;font-size:.74rem;color:#3d5060}.footer-bottom-links{display:flex;gap:1.5rem}.footer-bottom-links a{color:#3d5060}.footer-bottom-links a:hover{color:var(--muted)}

        @media(max-width:1200px){.km-wrap{width:min(100% - 2.5rem,1120px)}.hero-grid,.eco-grid,.local-grid,.video-grid{grid-template-columns:1fr}.hero::after{right:-10%;opacity:.65}.network-hero{min-height:520px}.feature-grid,.testi-grid{grid-template-columns:repeat(2,1fr)}.footer-grid{grid-template-columns:repeat(3,1fr)}}
        @media(max-width:860px){.site-nav{display:none}.header-cta .btn-ghost{display:none}.hamburger{display:flex}.hero{min-height:auto}.hero-grid{padding:4rem 0;gap:2.6rem}.hero h1{font-size:clamp(3rem,12vw,4.6rem)}.network-hero{min-height:430px}.hero-member-card{transform:scale(.86)}.hero-member-card.card-marco{right:2%;top:7%}.hero-member-card.card-sara{left:0;top:39%}.hero-member-card.card-luca{right:0;bottom:9%}.orb-d{display:none}.km-section{padding:3.8rem 0}.steps-grid{grid-template-columns:1fr}.step-card:not(:last-child)::after{display:none}.stats-card{grid-template-columns:repeat(2,1fr)}.stat-cell{border-right:0;border-bottom:1px solid rgba(255,255,255,.10)}.split-grid,.cta-inner{grid-template-columns:1fr}.testi-grid,.feature-grid,.footer-grid{grid-template-columns:1fr}.cta-inner .btn{width:100%}.map-city-card{position:relative;right:auto;bottom:auto;width:100%;margin-top:1rem}.map-wrap{min-height:auto}.italy-map-art{min-height:auto}.italy-map-img{width:100%}.footer-bottom-inner{flex-direction:column;align-items:flex-start}}
        @media(max-width:520px){.km-wrap{width:min(100% - 1.6rem,100%)}.hero-actions .btn{width:100%}.network-hero{min-height:410px}.hero-member-card{width:14rem}.hero-member-card.card-marco{right:-.8rem}.hero-member-card.card-luca{right:-.5rem}.hero-orb-icon{width:3.3rem;height:3.3rem}.stats-card{grid-template-columns:1fr}.italy-map-art{min-height:auto}.footer-bottom-links{flex-wrap:wrap}}
    </style>
</head>
<body>
@php
    $navPages = $navPages ?? collect();
    $footerPages = $footerPages ?? collect();
    $chapters = $chapters ?? collect();

    $steps = [
        ['01','Crea il tuo profilo','Mostra chi sei, cosa fai e quali sono i tuoi obiettivi.','<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>'],
        ['02','Entra nei Pianeti','Unisciti alla community più vicina a te.','<circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="1.8"/><path d="M12 22s7-5.5 7-12a7 7 0 1 0-14 0c0 6.5 7 12 7 12Z" stroke="currentColor" stroke-width="1.8"/>'],
        ['03','Connettiti','Incontra professionisti e crea relazioni di valore.','<path d="M18 8a3 3 0 1 0-3-3M6 19a3 3 0 1 0 0-6m12 6a3 3 0 1 0 0-6M8.5 14.5l7-6m-7 0l7 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>'],
        ['04','Genera opportunità','Collabora, condividi, fai crescere il tuo business.','<path d="M13 10V3L4 14h7v7l9-11h-7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>'],
    ];
    $features = [
        ['Directory','Trova e fatti trovare dai professionisti giusti.','<path d="M21 21l-4.35-4.35M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z" stroke="currentColor" stroke-width="1.8"/>'],
        ['Mini sito personale','Presentati al meglio con il tuo spazio dedicato.','<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z" stroke="currentColor" stroke-width="1.8"/><path d="M9 22V12h6v10" stroke="currentColor" stroke-width="1.8"/>'],
        ['Eventi e incontri','Partecipa a eventi esclusivi e networking dal vivo.','<path d="M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8"/>'],
        ['Referral e opportunità','Scambia, segnala, collabora.','<path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8M16 6l-4-4-4 4m4-4v13" stroke="currentColor" stroke-width="1.8"/>'],
        ['Chat e connessioni','Comunica in modo diretto e senza filtri.','<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10Z" stroke="currentColor" stroke-width="1.8"/>'],
        ['News e contenuti','Resta aggiornato su trend, novità e best practice.','<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Z" stroke="currentColor" stroke-width="1.8"/><path d="M14 2v6h6M16 13H8m8 4H8m2-8H8" stroke="currentColor" stroke-width="1.8"/>'],
    ];
    $stats = [['500+','Professionisti attivi'],['120+','Collaborazioni generate'],['20+','Pianeti attivi'],['15+','Eventi ogni mese']];
    $testimonials = [
        ['Kommunity mi ha permesso di entrare in contatto con clienti e partner in modo autentico e produttivo.','Francesca R.','Consulente HR','a'],
        ['Grazie ai Pianeti ho trovato persone straordinarie con cui collaboro ogni giorno.','Alessandro T.','Imprenditore','b'],
        ['Un ambiente positivo, professionale e dove le relazioni contano davvero.','Giulia M.','Marketing Manager','c'],
    ];
@endphp

<div class="km-page">
<header class="site-header">
    <div class="km-wrap header-inner">
        <a href="{{ route('home') }}" class="brand-lockup" aria-label="Kommunity home">
            <span class="brand-mark"><x-application-logo /></span><span>Kommunity</span>
        </a>
        <nav class="site-nav" aria-label="Navigazione principale">
            <a href="#come-funziona" class="nav-link">Come funziona</a>
            <a href="#community" class="nav-link">Community</a>

            <a href="#contatti" class="nav-link">Contatti</a>
            @foreach($navPages as $np)<a href="{{ route('page.show', $np->slug) }}" class="nav-link">{{ $np->title }}</a>@endforeach
        </nav>
        <div class="header-cta">
            <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Accedi</a>
            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Entra nella community</a>
        </div>
        <button class="hamburger" aria-label="Apri menu" aria-expanded="false"><span></span><span></span><span></span></button>
    </div>
</header>

<main>
<section class="hero" aria-labelledby="hero-heading">
    <div class="km-wrap hero-grid">
        <div>
            <h1 id="hero-heading"><span class="title-line">Relazioni giuste.</span><span class="title-line accent">Opportunità reali.</span></h1>
            <p class="hero-body">Kommunity è l'ecosistema dove professionisti e aziende si connettono, collaborano e crescono insieme.</p>
            <div class="hero-actions">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Entra nella community <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                <a href="#come-funziona" class="btn btn-ghost btn-lg">Scopri come funziona <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.14v13.72c0 .8.9 1.27 1.56.82l10.04-6.86a1 1 0 0 0 0-1.64L9.56 4.32C8.9 3.87 8 4.34 8 5.14Z"/></svg></a>
            </div>
            <div class="social-proof">
                <p class="social-label">Già oltre 500 professionisti connessi</p>
                <div class="avatar-stack" aria-hidden="true">
                    @for($i=1;$i<=7;$i++)<span class="av"></span>@endfor
                    <span class="av av-more">+500</span>
                </div>
            </div>
        </div>

        <div class="network-hero" aria-label="Rete professionale Kommunity">
            <div class="network-canvas" aria-hidden="true">
                <svg viewBox="0 0 720 560" xmlns="http://www.w3.org/2000/svg">
                    <defs><radialGradient id="g" cx="50%" cy="50%" r="50%"><stop offset="0" stop-color="#6fa367" stop-opacity=".65"/><stop offset="1" stop-color="#6fa367" stop-opacity="0"/></radialGradient></defs>
                    <line class="net-line" x1="140" y1="220" x2="280" y2="130"/><line class="net-line" x1="280" y1="130" x2="470" y2="185"/><line class="net-line net-line-strong" x1="470" y1="185" x2="600" y2="250"/><line class="net-line" x1="140" y1="220" x2="350" y2="340"/><line class="net-line" x1="350" y1="340" x2="600" y2="250"/><line class="net-line" x1="250" y1="410" x2="350" y2="340"/><line class="net-line net-line-strong" x1="470" y1="185" x2="520" y2="78"/><line class="net-line" x1="600" y1="250" x2="665" y2="120"/><line class="net-line" x1="600" y1="250" x2="640" y2="410"/><line class="net-line" x1="350" y1="340" x2="500" y2="455"/><line class="net-line" x1="120" y1="420" x2="250" y2="410"/><line class="net-line" x1="280" y1="130" x2="300" y2="280"/><line class="net-line" x1="300" y1="280" x2="470" y2="185"/>
                    @foreach([[140,220],[280,130],[470,185],[600,250],[350,340],[250,410],[520,78],[665,120],[640,410],[500,455],[120,420],[300,280]] as $p)
                        <circle class="{{ $loop->even ? 'net-node-blue' : 'net-node' }}" cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="5"/>
                    @endforeach
                    <circle cx="470" cy="185" r="46" fill="url(#g)" opacity=".28"/><circle cx="600" cy="250" r="54" fill="url(#g)" opacity=".20"/>
                </svg>
            </div>
            <div class="hero-orb-icon orb-a"><svg viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm14 14v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></div>
            <div class="hero-orb-icon orb-b"><svg viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm14 14v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></div>
            <div class="hero-orb-icon orb-c"><svg viewBox="0 0 24 24" fill="none"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm14 14v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></div>
            <div class="hero-orb-icon orb-d"><svg viewBox="0 0 24 24" fill="none"><path d="M18 8a3 3 0 1 0-3-3M6 19a3 3 0 1 0 0-6m12 6a3 3 0 1 0 0-6M8.5 14.5l7-6m-7 0l7 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></div>
            <article class="hero-member-card card-marco"><span class="member-face"></span><div><p class="member-name">Marco<br>B.</p><p class="member-role">Consulente Marketing</p></div><p class="member-verified"><span></span>Professionista verificato</p><a href="{{ route('register') }}" class="connect-pill">Connettiti</a></article>
            <article class="hero-member-card card-sara"><span class="member-face face-sara"></span><div><p class="member-name">Sara<br>V.</p><p class="member-role">Graphic Designer</p></div><p class="member-verified"><span></span>Professionista verificato</p><a href="{{ route('register') }}" class="connect-pill">Connettiti</a></article>
            <article class="hero-member-card card-luca"><span class="member-face face-luca"></span><div><p class="member-name">Luca<br>R.</p><p class="member-role">Finanziario</p></div><p class="member-verified"><span></span>Professionista verificato</p><a href="{{ route('register') }}" class="connect-pill">Connettiti</a></article>
        </div>
    </div>
    <span class="hero-line-bottom" aria-hidden="true"></span>
</section>

<section id="come-funziona" class="steps-section km-section">
    <div class="km-wrap steps-grid">
        @foreach($steps as [$num,$title,$copy,$icon])
            <article class="step-card"><div><span class="step-icon-wrap"><svg width="22" height="22" viewBox="0 0 24 24" fill="none">{!! $icon !!}</svg></span><span class="step-num-badge">{{ $num }}</span></div><h3>{{ $title }}</h3><p>{{ $copy }}</p></article>
        @endforeach
    </div>
</section>

<section id="community" class="km-section">
    <div class="km-wrap eco-grid">
        <div><span class="badge badge-green" style="margin-bottom:1.2rem"><span class="badge-dot"></span>L'ecosistema Kommunity</span><h2 class="section-title">Non è un social.<br>È un sistema progettato<br>per <span class="accent">far crescere il tuo business.</span></h2><p class="section-copy">Kommunity unisce persone, competenze e opportunità in un ambiente sicuro, selezionato e orientato ai risultati.</p><a href="{{ route('register') }}" class="btn btn-ghost" style="margin-top:2rem">Scopri l'ecosistema →</a></div>
        <div class="feature-grid">@foreach($features as [$title,$copy,$icon])<article class="feat-card"><span class="feat-icon"><svg width="21" height="21" viewBox="0 0 24 24" fill="none">{!! $icon !!}</svg></span><h3>{{ $title }}</h3><p>{{ $copy }}</p></article>@endforeach</div>
    </div>
</section>

<div class="stats-band"><div class="km-wrap"><div class="stats-card glass">@foreach($stats as [$value,$label])<div class="stat-cell"><strong>{{ $value }}</strong><p>{{ $label }}</p></div>@endforeach</div></div></div>

<section class="km-section"><div class="km-wrap split-grid"><div><h2 class="section-title" style="font-size:clamp(2rem,4vw,3.2rem)">Cosa dicono<br>i nostri membri</h2><p class="section-copy">Professionisti e aziende che hanno già scelto di far parte di Kommunity.</p><a href="{{ route('register') }}" class="btn btn-ghost" style="margin-top:1.75rem">Leggi tutte le testimonianze →</a></div><div class="testi-grid">@foreach($testimonials as [$quote,$name,$role,$avClass])<article class="testi-card"><div class="stars">★★★★★</div><p class="testi-quote">“{{ $quote }}”</p><div class="testi-person"><span class="tp-av tp-av-{{ $avClass }}"></span><span><strong>{{ $name }}</strong><span>{{ $role }}</span></span></div></article>@endforeach</div></div></section>

<section id="community-locale" class="local-section km-section">
    <div class="km-wrap local-grid">
        <div>
            <h2 class="section-title" style="font-size:clamp(2rem,4vw,3.2rem)">Trova il tuo Pianeta</h2>
            <p class="section-copy">Partiamo da Roma e dal Lazio con pianeti professionali attivi. Trova il Pianeta più vicino a te e inizia a connetterti.</p>
            <div class="search-bar"><span>Cerca il tuo Pianeta...</span><span>⌕</span></div>
            <p style="margin-top:1.35rem;font-size:.8rem;font-weight:900;color:rgba(214,228,236,.78)">Aree attive nel Lazio</p>
            <div class="city-chips">
                @foreach(['Roma','Latina','Frosinone','Viterbo','Rieti','Castelli Romani','Ostia','Tivoli'] as $city)<span class="city-chip">{{ $city }}</span>@endforeach
                <a href="{{ route('register') }}" class="city-chip" style="border-color:rgba(111,163,103,.55);color:var(--brand4)">Vedi tutti i Pianeti</a>
            </div>
        </div>
        <div class="map-wrap">
            <div class="italy-map-art" aria-hidden="true">
                <img class="italy-map-img" src="{{ asset('images/italianetwork.png') }}" alt="Network professionale Roma e Lazio">
                <div class="map-city-card glass">
                    <span class="city-thumb"></span>
                    <p style="font-size:1.15rem;font-weight:900;margin:0">Roma</p>
                    <p style="font-size:.76rem;color:var(--brand4);margin:.25rem 0">Pianeta attivo</p>
                    <p style="font-size:.76rem;color:var(--muted);margin:.15rem 0">48 membri nel Lazio</p>
                    <a href="{{ route('register') }}" style="display:inline-flex;margin-top:.65rem;font-size:.78rem;font-weight:900;color:var(--brand4)">Scopri il Pianeta →</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="eventi" class="km-section"><div class="km-wrap video-grid"><div class="video-player glass"><button type="button" class="play-btn" aria-label="Guarda il video"><svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.14v13.72c0 .8.9 1.27 1.56.82l10.04-6.86a1 1 0 0 0 0-1.64L9.56 4.32C8.9 3.87 8 4.34 8 5.14Z"/></svg></button><div class="video-brand brand-lockup" style="font-size:.95rem"><span class="brand-mark"><x-application-logo /></span><span>Kommunity</span></div></div><div><span class="badge badge-green" style="margin-bottom:1.2rem"><span class="badge-dot"></span>Scopri Kommunity</span><h2 class="section-title" style="font-size:clamp(2rem,4vw,3.2rem)">In 60 secondi</h2><p class="section-copy">Guarda il video e scopri come Kommunity può trasformare le tue relazioni in risultati concreti.</p><a href="{{ route('register') }}" class="btn btn-ghost" style="margin-top:2rem">Guarda il video ▶</a></div></div></section>

<section class="cta-section"><div class="km-wrap cta-inner"><div><h2 class="cta-title">Pronto a trasformare<br>le relazioni in risultati?</h2><p class="cta-body">Entra oggi in Kommunity e inizia a costruire il tuo futuro professionale.</p></div><a href="{{ route('register') }}" class="btn btn-primary btn-lg">Entra nella community <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></a></div></section>
</main>

<footer id="contatti" class="site-footer">
    <div class="km-wrap footer-grid">
        <div><a href="{{ route('home') }}" class="brand-lockup"><span class="brand-mark"><x-application-logo /></span><span>Kommunity</span></a><p style="margin-top:1rem;max-width:280px;font-size:.78rem;line-height:1.72;color:var(--muted)">Kommunity è l'ecosistema che connette professionisti e aziende per generare valore, collaborare e crescere insieme.</p><div class="social-icons"><a href="#" class="soc-icon">in</a><a href="#" class="soc-icon">f</a><a href="#" class="soc-icon">IG</a><a href="#" class="soc-icon">X</a></div></div>
        <nav><p class="footer-col-title">Esplora</p><a href="#come-funziona" class="footer-link">Come funziona</a><a href="#community" class="footer-link">Community</a>@foreach($footerPages as $fp)<a href="{{ route('page.show', $fp->slug) }}" class="footer-link">{{ $fp->title }}</a>@endforeach</nav>
        <nav><p class="footer-col-title">Risorse</p><a href="#" class="footer-link">FAQ</a><a href="#" class="footer-link">Storie di successo</a></nav>
        <address style="font-style:normal"><p class="footer-col-title">Contatti</p><p class="footer-link" style="cursor:default;line-height:1.7">KNM Srl<br>Via Eurialo, 56<br>00181 Roma (IT)</p> <a href="mailto:info@kommunity.it" class="footer-link">info@kommunity.it</a><a href="tel:+390678216530" class="footer-link">+39 06.7821653</a></address>
        <!-- <div><p class="footer-col-title">Resta aggiornato</p><p style="font-size:.78rem;color:var(--muted);margin-bottom:.75rem">Iscriviti alla newsletter</p>@if(session('newsletter_success'))<p style="font-size:.8rem;color:var(--brand4);font-weight:700;padding:.6rem .8rem;border-radius:.4rem;background:rgba(85,121,79,.12);border:1px solid rgba(85,121,79,.3);margin-bottom:.75rem">✓ Iscritto con successo!</p>@endif<form class="newsletter-form" method="POST" action="{{ route('newsletter.subscribe') }}">@csrf<input type="email" name="email" placeholder="La tua email" required><button type="submit">→</button></form></div> -->
    </div>
    <div class="footer-bottom"><div class="km-wrap footer-bottom-inner"><p>© {{ date('Y') }} KNM Srl · P.IVA 13273091002 · Tutti i diritti riservati</p><nav class="footer-bottom-links"><a href="https://kommunity.it/pagina/privacy-policy">Privacy Policy</a><a href="https://kommunity.it/pagina/termini-e-condizioni">Termini e condizioni</a><a href="https://kommunity.it/pagina/cookie-policy">Cookie Policy</a></nav></div></div>
</footer>
</div>

<script>
    const hdr = document.querySelector('.site-header');
    window.addEventListener('scroll', () => { hdr.style.boxShadow = window.scrollY > 40 ? '0 8px 40px rgba(0,0,0,.35)' : 'none'; }, { passive:true });
    const hamburger = document.querySelector('.hamburger');
    const siteNav = document.querySelector('.site-nav');
    hamburger?.addEventListener('click', () => {
        const open = hamburger.getAttribute('aria-expanded') === 'true';
        hamburger.setAttribute('aria-expanded', String(!open));
        if(!open){siteNav.style.cssText='display:flex;flex-direction:column;gap:1rem;position:absolute;top:100%;left:0;right:0;background:rgba(7,17,26,.97);padding:1.5rem 1.25rem;border-bottom:1px solid rgba(255,255,255,.08);z-index:90;backdrop-filter:blur(20px)'}
        else{siteNav.removeAttribute('style')}
    });
    document.querySelectorAll('a[href^="#"]').forEach(a=>a.addEventListener('click',e=>{const t=document.querySelector(a.getAttribute('href'));if(t){e.preventDefault();t.scrollIntoView({behavior:'smooth',block:'start'});if(hamburger?.getAttribute('aria-expanded')==='true')hamburger.click();}}));
</script>
</body>
</html>
