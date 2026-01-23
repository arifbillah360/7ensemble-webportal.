{{-- Stats Cards Component --}}
<div class="stats-grid" data-aos="fade-up" data-aos-delay="200">
    <div class="stat-card" data-aos="flip-left" data-aos-delay="0">
        <div class="stat-icon">💰</div>
        <div class="stat-number animate-number" data-target="{{ $stats['total_distributed'] ?? 0 }}">
            0
        </div>
        <div class="stat-label">Euros Distribués</div>
        <div class="stat-description">Versés aux membres</div>
    </div>

    <div class="stat-card" data-aos="flip-left" data-aos-delay="100">
        <div class="stat-icon">👥</div>
        <div class="stat-number">
            <span class="animate-number" data-target="{{ $stats['active_users'] ?? 0 }}">0</span>+
        </div>
        <div class="stat-label">Membres Actifs</div>
        <div class="stat-description">Dans le monde entier</div>
    </div>

    <div class="stat-card" data-aos="flip-left" data-aos-delay="200">
        <div class="stat-icon">🌟</div>
        <div class="stat-number">
            <span class="animate-number" data-target="{{ $stats['active_constellations'] ?? 0 }}">0</span>+
        </div>
        <div class="stat-label">Constellations Actives</div>
        <div class="stat-description">En ce moment</div>
    </div>

    <div class="stat-card" data-aos="flip-left" data-aos-delay="300">
        <div class="stat-icon">✅</div>
        <div class="stat-number">
            <span class="animate-number" data-target="{{ $stats['total_transactions'] ?? 0 }}">0</span>+
        </div>
        <div class="stat-label">Transactions Réussies</div>
        <div class="stat-description">100% sécurisées</div>
    </div>
</div>

<style>
/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin: 3rem 0;
}

.stat-card {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    padding: 2.5rem 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    opacity: 0;
    transition: opacity 0.4s;
}

.stat-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
    border-color: rgba(78, 205, 196, 0.5);
}

.stat-card:hover::before {
    opacity: 1;
}

.stat-icon {
    font-size: 3.5rem;
    margin-bottom: 1rem;
    animation: float 3s ease-in-out infinite;
    position: relative;
    z-index: 1;
}

.stat-number {
    font-size: 3rem;
    font-weight: 800;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 1;
    font-family: 'Inter', sans-serif;
}

.stat-label {
    font-size: 1.2rem;
    font-weight: 700;
    color: white;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 1;
}

.stat-description {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.7);
    position: relative;
    z-index: 1;
}

/* Floating animation for icons */
@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

/* Pulse animation for hover */
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

.stat-card:hover .stat-icon {
    animation: pulse 1s ease-in-out infinite;
}

/* Number animation class (will be handled by JavaScript) */
.animate-number {
    display: inline-block;
}

/* Responsive Design */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .stat-card {
        padding: 2rem 1.5rem;
    }

    .stat-icon {
        font-size: 2.5rem;
    }

    .stat-number {
        font-size: 2.5rem;
    }

    .stat-label {
        font-size: 1.1rem;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .stat-card {
        padding: 1.5rem;
    }
}
</style>
