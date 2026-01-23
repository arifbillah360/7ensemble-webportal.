@extends('layouts.app')

@section('title', '7 Ensemble - Votre Nouvelle Aventure Financière')
@section('description', 'Une plateforme conçue pour que tout le monde puisse vivre et profiter des bons moments de la vie en famille, sans avoir à se soucier si demain, ils auront de quoi payer leurs factures.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/cosmic-theme.css') }}">
<link rel="stylesheet" href="{{ asset('css/animations.css') }}">
<link rel="stylesheet" href="{{ asset('css/components.css') }}">
<link rel="stylesheet" href="https://unpkg.com/aos@3.0.0-beta.6/dist/aos.css">
@endpush

@section('content')

{{-- Hero Section --}}
<section class="hero">
    <div class="container">
        <h1 class="hero-title" data-aos="fade-down">7 Ensemble</h1>
        <p class="subtitle" data-aos="fade-up" data-aos-delay="100">Qui, avec 21€, a changé ma vie</p>
        <p class="tagline" data-aos="fade-up" data-aos-delay="200">
            Une plateforme conçue pour que tout le monde puisse vivre et profiter des bons moments de la vie en famille,<br>
            sans avoir à se soucier si demain, ils auront de quoi payer leurs factures
        </p>

        <div class="transformation-amount" data-aos="zoom-in" data-aos-delay="300">
            {{ number_format($constellations['pleiades']['total_earning'], 0, ',', '\'') }}€
        </div>
        <p style="font-size: 1.5rem; margin-bottom: 1rem;" data-aos="fade-up" data-aos-delay="400">
            Votre destination finale avec seulement 21€ de départ
        </p>
        <p style="font-size: 1.8rem; color: #ff6b6b; font-weight: bold; margin-bottom: 3rem;" data-aos="fade-up" data-aos-delay="500">
            À RISQUE ZÉRO POUR VOUS
        </p>

        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-bottom: 3rem;" data-aos="fade-up" data-aos-delay="600">
            <button class="btn-primary" onclick="openModal('threeModal')" style="background: linear-gradient(45deg, #f093fb, #f5576c);">
                Commencer avec 3 personnes
            </button>
            <button class="btn-primary" onclick="openModal('sevenModal')">
                Commencer avec 7 personnes
            </button>
        </div>

        <p style="font-size: 1.2rem; color: #4ecdc4;" data-aos="fade-up" data-aos-delay="700">
            En ligne, ça va très très vite ! Un message WhatsApp et c'est parti !
        </p>
    </div>
</section>

{{-- Principle Section --}}
<section id="principe" class="principe-section">
    <div class="container">
        <h2 style="text-align: center; font-size: 3rem; margin-bottom: 2rem;" data-aos="fade-up">
            Le Principe Simple : La Force du 7 et du 21
        </h2>

        <div style="text-align: center; margin: 3rem 0; padding: 2rem; background: rgba(255,107,107,0.1); border-radius: 15px;" data-aos="fade-up" data-aos-delay="100">
            <h3 style="font-size: 2rem; color: #f093fb;">Pourquoi 21€ et pourquoi 7 ?</h3>
            <p style="font-size: 1.3rem; line-height: 1.8; margin-top: 1rem;">
                <strong style="color: #4ecdc4;">Le chiffre 7 = puissance spirituelle et une symbolique énorme</strong><br>
                <strong style="color: #ff6b6b;">Le 3 = force universelle (3 × 7 = 21, et 2+1 = 3)</strong><br>
                Ce ne sont pas des montants choisis au hasard, c'est de la pure énergie mathématique !
            </p>
        </div>

        {{-- Constellation Visual --}}
        <div class="constellation-visual" data-aos="fade-up" data-aos-delay="200">
            <h3 style="font-size: 2rem; margin-bottom: 2rem; position: relative; z-index: 1;">
                Votre Constellation : Le Réseau Qui Vous Suivra À Vie
            </h3>
            @include('components.constellation-visual', [
                'type' => 'pleiades',
                'members' => 7
            ])
            <p style="font-size: 1.3rem; color: #4ecdc4; font-weight: bold; position: relative; z-index: 1;">
                Une fois votre Constellation créée, chaque tour peut durer UNE SEMAINE !
            </p>
            <p style="font-size: 1.2rem; margin-top: 1rem; position: relative; z-index: 1;">
                Le plus long, c'est le premier tour. Après, ça va très très vite !
            </p>
        </div>

        {{-- Principle Cards --}}
        <div class="principe-grid">
            <div class="principe-card" data-aos="fade-up" data-aos-delay="0">
                <div class="principe-number">💝</div>
                <h3>Vous Aidez</h3>
                <p style="font-size: 1.5rem; color: #4ecdc4; margin: 1rem 0;"><strong>21€</strong></p>
                <p>à une personne dans le besoin</p>
            </div>
            <div class="principe-card" data-aos="fade-up" data-aos-delay="100">
                <div class="principe-number">🎁</div>
                <h3>Vous Recevez</h3>
                <p style="font-size: 1.5rem; color: #4ecdc4; margin: 1rem 0;"><strong>147€</strong></p>
                <p>de 7 personnes (21€ chacune)</p>
            </div>
            <div class="principe-card" data-aos="fade-up" data-aos-delay="200">
                <div class="principe-number">✨</div>
                <h3>Vous Gardez</h3>
                <p style="font-size: 1.5rem; color: #4ecdc4; margin: 1rem 0;"><strong>126€ nets</strong></p>
                <p>Vous avez déjà multiplié par 6 votre mise !</p>
            </div>
        </div>

        {{-- Two Options Box --}}
        <div style="text-align: center; margin: 4rem 0; padding: 3rem; background: linear-gradient(45deg, rgba(78,205,196,0.2), rgba(255,107,107,0.2)); border-radius: 20px; border: 2px solid #4ecdc4;" data-aos="fade-up">
            <h2 style="font-size: 2.5rem; color: #ff6b6b; margin-bottom: 2rem;">
                🚨 DEUX POSSIBILITÉS POUR COMMENCER 🚨
            </h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin: 2rem 0;">
                <div style="background: rgba(255,255,255,0.1); padding: 2rem; border-radius: 15px; backdrop-filter: blur(10px);">
                    <h3 style="color: #f093fb; font-size: 1.8rem;">Option 3 Personnes</h3>
                    <p style="font-size: 1.2rem; margin: 1rem 0;">Pour ceux qui trouvent 7 "beaucoup"</p>
                    <h3 style="color: #4ecdc4; font-size: 1.8rem;">Gain total : {{ number_format($constellations['triangulum']['total_earning'], 0, ',', '\'') }}€</h3>
                    <p style="margin-top: 1rem;">Une fois que vous voyez que ça marche, vous passez au 7 !</p>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 2rem; border-radius: 15px; backdrop-filter: blur(10px);">
                    <h3 style="color: #4ecdc4; font-size: 1.8rem;">Option 7 Personnes</h3>
                    <p style="font-size: 1.2rem; margin: 1rem 0;">Pour les audacieux qui veulent tout</p>
                    <h3 style="color: #f093fb; font-size: 1.8rem;">Gain total : {{ number_format($constellations['pleiades']['total_earning'], 0, ',', '\'') }}€</h3>
                    <p style="margin-top: 1rem;">La voie royale vers la liberté financière !</p>
                </div>
            </div>

            <p style="font-size: 1.4rem; font-weight: bold; color: #4ecdc4;">
                En ligne, c'est ULTRA RAPIDE : un message WhatsApp, un lien de paiement, et c'est parti ! 🚀
            </p>
        </div>

        <div style="text-align: center; margin-top: 3rem; padding: 2rem; background: rgba(255,107,107,0.2); border-radius: 15px;" data-aos="fade-up">
            <h3 style="font-size: 2rem; color: #ff6b6b;">⚠️ CE N'EST QUE LE DÉBUT ! ⚠️</h3>
            <p style="font-size: 1.3rem; margin-top: 1rem;">
                147€, c'est juste pour prouver que ça marche. Les VRAIS gains viennent avec les tours suivants !
            </p>
        </div>
    </div>
</section>

{{-- Tours Section --}}
<section id="tours" class="tours-section">
    <div class="container">
        <h2 style="font-size: 3rem; margin-bottom: 2rem;" data-aos="fade-up">
            Les 7 Tours Magiques : Votre Ascension Vers la Prospérité
        </h2>
        <p style="font-size: 1.5rem; margin-bottom: 3rem; color: #4ecdc4;" data-aos="fade-up" data-aos-delay="100">
            Préparez-vous à découvrir comment <strong>21€ se transforment en {{ number_format($constellations['pleiades']['total_earning'], 0, ',', '\'') }}€</strong>
            grâce à la puissance exponentielle de notre système !
        </p>
        <p style="font-size: 1.2rem; text-align: center; margin-bottom: 2rem;" data-aos="fade-up" data-aos-delay="200">
            <a href="{{ route('tours.index') }}" class="btn-primary">📊 Voir le détail des 7 Tours</a>
        </p>

        <div class="tours-timeline">
            @foreach([
                ['number' => 1, 'title' => 'L\'Éveil', 'amount' => '147€', 'desc' => 'Votre première victoire qui confirme que le système fonctionne parfaitement'],
                ['number' => 2, 'title' => 'L\'Élan', 'amount' => '1\'029€', 'desc' => 'L\'accélération commence, vos premières vraies économies'],
                ['number' => 3, 'title' => 'La Percée', 'amount' => '7\'203€', 'desc' => 'Le tournant décisif qui change votre rapport à l\'argent'],
                ['number' => 4, 'title' => 'L\'Envol', 'amount' => '50\'421€', 'desc' => 'La liberté financière commence à prendre forme'],
                ['number' => 5, 'title' => 'La Puissance', 'amount' => '352\'947€', 'desc' => 'Vos projets les plus fous deviennent réalisables'],
                ['number' => 6, 'title' => 'L\'Excellence', 'amount' => '2\'470\'629€', 'desc' => 'L\'indépendance financière totale à portée de main'],
                ['number' => 7, 'title' => 'L\'Apothéose', 'amount' => '17\'294\'403€', 'desc' => 'Le sommet de votre transformation financière']
            ] as $tour)
                <div class="tour-item" data-aos="fade-right" data-aos-delay="{{ ($tour['number'] - 1) * 100 }}">
                    <div class="tour-number">{{ $tour['number'] }}</div>
                    <div class="tour-content">
                        <div class="tour-title">Tour {{ $tour['number'] }} : {{ $tour['title'] }}</div>
                        <div class="tour-amount">{{ $tour['amount'] }}</div>
                        <p>{{ $tour['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="text-align: center; margin-top: 4rem;" data-aos="fade-up">
            <h3 style="font-size: 2.5rem; color: #f093fb;">Vitesse de Croisière :<br> En Moins d'Une Année, Tout Bascule</h3>
            <p style="font-size: 1.3rem; margin: 2rem 0;">
                La beauté du système 7 Ensemble réside dans son <strong style="color: #4ecdc4;">accélération naturelle</strong>.<br>
                Une fois votre Constellation créée, chaque tour se déroule à une vitesse époustouflante !<br>
                En moins d'une semaine, chaque membre de votre réseau constitue sa propre Constellation,<br>
                créant un effet domino extraordinaire.
            </p>
        </div>
    </div>
</section>

{{-- Urgence Section --}}
<section id="urgence" class="urgence-section">
    <div class="container">
        <h2 style="font-size: 3rem; margin-bottom: 2rem;" data-aos="fade-up">
            ⚡ URGENCE : Chaque Jour de Retard Est un Jour de Liberté Financière en Moins ! ⚡
        </h2>

        <p style="font-size: 1.5rem; margin: 2rem 0;" data-aos="fade-up" data-aos-delay="100">
            <strong style="color: #ff6b6b;">L'heure est venue de transformer votre vie !</strong><br>
            Pendant que vous hésitez, d'autres construisent déjà leur Constellation et récoltent leurs premiers gains.<br>
            <strong style="color: #4ecdc4;">Chaque jour de retard est un jour de liberté financière en moins.</strong>
        </p>

        {{-- Stats Cards --}}
        @include('components.stats-cards', ['stats' => $stats])

        <p style="font-size: 1.5rem; text-align: center; font-style: italic; color: #4ecdc4; margin-top: 3rem;" data-aos="fade-up">
            "Entraidons-nous car ensemble, nous changerons le monde et éradiquerons la pauvreté."
        </p>

        <div style="text-align: center; margin-top: 2rem;" data-aos="fade-up" data-aos-delay="100">
            <button class="btn-primary" onclick="openModal('sevenModal')" style="font-size: 1.3rem; padding: 20px 40px;">
                🚀 Je Rejoins la Révolution Maintenant !
            </button>
        </div>
    </div>
</section>

{{-- Mission Section --}}
<section id="mission" style="background: rgba(255,255,255,0.05); padding: 4rem 0; border-radius: 25px; margin: 4rem 0;">
    <div class="container">
        <h2 style="text-align: center; font-size: 3rem; margin-bottom: 2rem;" data-aos="fade-up">
            <span class="coeur-magique">🫶</span> Mon Petit Coup de Gueule : Pourquoi 7 Ensemble ? <span class="coeur-magique">🫶</span>
        </h2>

        <div style="text-align: center; margin: 3rem 0; padding: 3rem; background: rgba(255,107,107,0.15); border-radius: 20px; border-left: 5px solid #ff6b6b;" data-aos="fade-up" data-aos-delay="100">
            <p style="font-size: 1.8rem; font-style: italic; color: #ff6b6b; font-weight: bold;">
                "Ne supportant plus ce qui se passe autour de moi, j'ai voulu créé cette plateforme d'entraide révolutionnaire"
            </p>
        </div>

        <div style="background: rgba(78,205,196,0.1); padding: 3rem; border-radius: 20px; margin: 3rem 0;" data-aos="fade-up" data-aos-delay="200">
            <h3 style="font-size: 2.2rem; color: #4ecdc4; text-align: center; margin-bottom: 2rem;">
                🎯 Aller À LA SOURCE Du Problème
            </h3>
            <div style="text-align: center; font-size: 1.3rem; line-height: 1.7;">
                <strong>Aujourd'hui, tout le monde cherche plus d'argent mais peu vont à la source du vrai problème.</strong><br>
                Ce qu'il faut, ce n'est pas seulement plus d'aides ou moins d'impôts.<br>
                <strong style="color: #4ecdc4;"><br> Ce qu'il faut, c'est d'améliorer le pouvoir d'achat des gens, pour de bon.</strong>
            </div>
        </div>

        {{-- Risk Zero Section --}}
        <section style="background: radial-gradient(circle at center, #d9052c, #02f0fcd4); padding: 2rem; border-radius: 2rem; margin: 3rem 0; position: relative; overflow: hidden; box-shadow: inset 0 0 7px rgba(0, 0, 0, 0.6);" data-aos="fade-up">
            <h2 style="text-align: center; color: #4ecdc4; font-size: 2.8rem; font-weight: bold; z-index: 2; position: relative;">
                <span class="pulse-heart">❤️</span> Risque Zéro pour Vous <span class="pulse-heart">❤️</span>
            </h2>
            <p style="font-size: 1.3rem; line-height: 2; color: #fff; text-align: center; max-width: 900px; margin: 2rem auto 0; font-weight: bold; z-index: 2; position: relative;">
                Je suis le seul à investir dans cette plateforme. Pas de frais cachés. Pas de commissions. Pas de système obscur.
                Pour lancer un mouvement d'entraide et de dignité. Juste un don volontaire de <strong style="color: #4ecdc4; font-size: 3rem;"> 21€</strong><br><br>
                <strong style="color: #4ecdc4;">Mon seul intérêt ici, c'est VOUS. Je Vous AIME</strong><br><br>
                <em style="font-size: 1.1rem; font-weight: normal;">
                    Chaque matin je me lève, pour vous Voler des Rires, des Sourires, des Câlins et Sèmer l'Amour.
                </em>
            </p>
        </section>

        {{-- Legal Section --}}
        <section style="background: linear-gradient(135deg, #220f1f, #4e2a33); padding: 2rem; border-radius: 1.5rem; margin: 3rem 0; box-shadow: inset 0 0 7px rgba(0,0,0,0.4); color: white;" data-aos="fade-up">
            <h2 style="text-align: center; font-size: 2.5rem; color: #4ecdc4;">
                ⚖️ 100% Légal… mais pas toujours accepté par l'État
            </h2>
            <p style="font-size: 1.2rem; line-height: 1.8; max-width: 950px; margin: 2rem auto;">
                Ce que je vous propose ici n'est pas nouveau. Dans toutes les cultures du monde, des systèmes d'entraide existent depuis des siècles :
            </p>
            <ul style="font-size: 1.1rem; line-height: 1.8; max-width: 850px; margin: 0 auto 2rem; list-style: none; padding: 0;">
                <li>• La Tontine, en Afrique de l'Ouest</li>
                <li>• Les ROSCA, en Asie et en Amérique latine</li>
                <li>• Les cercles de dons, aux États-Unis</li>
                <li>• Les Hui, en Chine</li>
                <li>• Les Chit Funds, en Inde</li>
            </ul>
            <p style="text-align:center; color:#fff; font-size:1.8rem; font-weight:bold; margin-top:2rem;">
                Rome ne s'est pas faite en un jour, ni toute seule.<br>
                C'est avec <strong style="color:#4ecdc4;">7 Ensemble</strong> qu'on Rallumera la Terre et le <span class="glow-heart">❤️</span> des gens
            </p>
        </section>
    </div>
</section>

{{-- Final CTA --}}
<section style="background: linear-gradient(135deg, #667eea, #764ba2); padding: 6rem 0; text-align: center; border-radius: 25px; margin: 4rem 0;" data-aos="fade-up">
    <div class="container">
        <h2 style="font-size: 4rem; margin-bottom: 2rem;">
            Rejoignez la Révolution 7 Ensemble Dès Maintenant !
        </h2>

        <div class="transformation-amount" style="margin: 2rem 0;">
            {{ number_format($constellations['pleiades']['total_earning'], 0, ',', '\'') }}€
        </div>
        <p style="font-size: 1.8rem; margin-bottom: 1rem;">Votre destination finale avec seulement 21€ de départ</p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 3rem 0; max-width: 800px; margin-left: auto; margin-right: auto;">
            <div style="background: rgba(0,0,0,0.2); padding: 2rem; border-radius: 15px; border: 2px solid rgba(255,255,255,0.3);">
                <h4 style="color: #4ecdc4; font-size: 1.2rem;">Investissement Initial</h4>
                <p style="font-size: 2rem; font-weight: bold;">Seulement 21€</p>
                <p>pour changer votre vie</p>
            </div>
            <div style="background: rgba(0,0,0,0.2); padding: 2rem; border-radius: 15px; border: 2px solid rgba(255,255,255,0.3);">
                <h4 style="color: #4ecdc4; font-size: 1.2rem;">Premier Résultat</h4>
                <p style="font-size: 2rem; font-weight: bold;">126€ nets</p>
                <p>dès la première semaine</p>
            </div>
            <div style="background: rgba(0,0,0,0.2); padding: 2rem; border-radius: 15px; border: 2px solid rgba(255,255,255,0.3);">
                <h4 style="color: #4ecdc4; font-size: 1.2rem;">Potentiel Final</h4>
                <p style="font-size: 2rem; font-weight: bold;">Plus de<br>1,5 million d'euros</p>
                <p>à terme</p>
            </div>
        </div>

        <button class="btn-primary" onclick="openModal('sevenModal')" style="font-size: 1.5rem; padding: 25px 50px; margin-top: 2rem; box-shadow: 0 10px 30px rgba(255,255,255,0.3);">
            <span class="coeur-magique">🫶</span>
            Ma Nouvelle VIE Commence Maintenant !
            <span class="coeur-magique">🫶</span>
        </button>
    </div>
</section>

@endsection

@push('modals')
{{-- Registration Modals --}}
@include('components.registration-modal', [
    'modalId' => 'sevenModal',
    'title' => 'Rejoindre 7 Ensemble - Option 7 Personnes',
    'option' => 7,
    'countries' => $countries ?? [],
    'paymentMethods' => $paymentMethods ?? []
])

@include('components.registration-modal', [
    'modalId' => 'threeModal',
    'title' => 'Rejoindre 7 Ensemble - Option 3 Personnes',
    'option' => 3,
    'countries' => $countries ?? [],
    'paymentMethods' => $paymentMethods ?? []
])
@endpush

@push('scripts')
<script src="{{ asset('js/constellation-animation.js') }}"></script>
<script src="{{ asset('js/modal-handler.js') }}"></script>
<parameter name="file_path">/home/user/7ensemble-webportal./resources/views/pages/index.blade.php