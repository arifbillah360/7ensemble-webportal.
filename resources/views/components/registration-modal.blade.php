{{-- Registration Modal Component --}}
<div
    id="{{ $modalId }}"
    class="modal"
    x-data="{
        open: false,
        formData: {
            fullName: '',
            email: '',
            country: '',
            paymentMethod: '',
            option: {{ $option }},
            terms: false
        },
        errors: {},
        loading: false
    }"
    x-show="open"
    x-cloak
    @keydown.escape.window="open = false"
    @open-modal.window="if($event.detail === '{{ $modalId }}') { open = true }"
    style="display: none;"
>
    <div class="modal-backdrop" @click="open = false"></div>

    <div class="modal-content" @click.stop>
        <button class="modal-close" @click="open = false" aria-label="Fermer">
            <span>&times;</span>
        </button>

        <div class="modal-header">
            <h2>🌟 {{ $title }} 🌟</h2>
            <p class="modal-subtitle">
                Transformez 21€ en
                @if($option === 7)
                    <strong class="highlight">1'575'747€</strong>
                @else
                    <strong class="highlight">7'789€</strong>
                @endif
            </p>
        </div>

        <form
            @submit.prevent="submitRegistration"
            class="registration-form"
            method="POST"
            action="{{ route('register') }}"
        >
            @csrf
            <input type="hidden" name="constellation_type" :value="formData.option === 7 ? 'pleiades' : 'triangulum'">

            {{-- Full Name --}}
            <div class="form-group">
                <label for="{{ $modalId }}_fullName" class="form-label">
                    <span class="label-icon">👤</span>
                    Nom complet
                </label>
                <input
                    type="text"
                    id="{{ $modalId }}_fullName"
                    name="name"
                    x-model="formData.fullName"
                    required
                    placeholder="Votre nom et prénom"
                    class="form-input"
                    :class="{ 'error': errors.fullName }"
                >
                <span x-show="errors.fullName" class="error-message" x-text="errors.fullName"></span>
            </div>

            {{-- Email --}}
            <div class="form-group">
                <label for="{{ $modalId }}_email" class="form-label">
                    <span class="label-icon">📧</span>
                    Email
                </label>
                <input
                    type="email"
                    id="{{ $modalId }}_email"
                    name="email"
                    x-model="formData.email"
                    required
                    placeholder="votre@email.com"
                    class="form-input"
                    :class="{ 'error': errors.email }"
                >
                <span x-show="errors.email" class="error-message" x-text="errors.email"></span>
            </div>

            {{-- Country --}}
            <div class="form-group">
                <label for="{{ $modalId }}_country" class="form-label">
                    <span class="label-icon">🌍</span>
                    Pays
                </label>
                <select
                    id="{{ $modalId }}_country"
                    name="country"
                    x-model="formData.country"
                    required
                    class="form-select"
                    :class="{ 'error': errors.country }"
                >
                    <option value="">Choisir votre pays</option>
                    @if(!empty($countries))
                        @foreach($countries as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    @else
                        <option value="FR">🇫🇷 France</option>
                        <option value="CH">🇨🇭 Suisse</option>
                        <option value="BE">🇧🇪 Belgique</option>
                        <option value="CA">🇨🇦 Canada</option>
                        <option value="MA">🇲🇦 Maroc</option>
                        <option value="TN">🇹🇳 Tunisie</option>
                        <option value="SN">🇸🇳 Sénégal</option>
                        <option value="CI">🇨🇮 Côte d'Ivoire</option>
                        <option value="OTHER">🌍 Autre</option>
                    @endif
                </select>
                <span x-show="errors.country" class="error-message" x-text="errors.country"></span>
            </div>

            {{-- Payment Method --}}
            <div class="form-group">
                <label for="{{ $modalId }}_paymentMethod" class="form-label">
                    <span class="label-icon">💳</span>
                    Mode de paiement préféré
                </label>
                <select
                    id="{{ $modalId }}_paymentMethod"
                    name="payment_method"
                    x-model="formData.paymentMethod"
                    required
                    class="form-select"
                    :class="{ 'error': errors.paymentMethod }"
                >
                    <option value="">Choisir votre méthode</option>
                    @if(!empty($paymentMethods))
                        @foreach($paymentMethods as $key => $method)
                            @if($method['enabled'])
                                <option value="{{ $key }}">{{ $method['icon'] }} {{ $method['name'] }}</option>
                            @endif
                        @endforeach
                    @else
                        <option value="card">💳 Carte bancaire</option>
                        <option value="paypal">🔵 PayPal</option>
                        <option value="bank_transfer">🏦 Virement bancaire</option>
                        <option value="mobile_money">📱 Mobile Money (Afrique)</option>
                        <option value="crypto">₿ Bitcoin/Crypto</option>
                        <option value="other">🌍 Autre (nous nous adapterons)</option>
                    @endif
                </select>
                <span x-show="errors.paymentMethod" class="error-message" x-text="errors.paymentMethod"></span>
            </div>

            {{-- Referral Code (Optional) --}}
            <div class="form-group">
                <label for="{{ $modalId }}_referral" class="form-label">
                    <span class="label-icon">🎁</span>
                    Code de parrainage (optionnel)
                </label>
                <input
                    type="text"
                    id="{{ $modalId }}_referral"
                    name="referral_code"
                    placeholder="Ex: 7E-ABCD1234"
                    class="form-input"
                >
                <p class="form-help">Si quelqu'un vous a invité, entrez son code ici</p>
            </div>

            {{-- Option Selection --}}
            <div class="option-selection">
                <h3 class="option-title">✨ Votre Choix ✨</h3>
                <div class="option-cards">
                    <label class="option-card" :class="{ 'selected': formData.option === 3 }">
                        <input
                            type="radio"
                            name="option"
                            value="3"
                            x-model.number="formData.option"
                        >
                        <div class="option-content">
                            <div class="option-header">
                                <span class="option-icon">🔺</span>
                                <span class="option-name">Option 3 Personnes</span>
                            </div>
                            <div class="option-amount">7'789€</div>
                            <div class="option-desc">Parfait pour commencer</div>
                        </div>
                    </label>

                    <label class="option-card popular" :class="{ 'selected': formData.option === 7 }">
                        <input
                            type="radio"
                            name="option"
                            value="7"
                            x-model.number="formData.option"
                        >
                        <div class="option-content">
                            <div class="option-header">
                                <span class="option-icon">⭐</span>
                                <span class="option-name">Option 7 Personnes</span>
                            </div>
                            <div class="option-amount">1'575'747€</div>
                            <div class="option-desc">Recommandé ✨</div>
                        </div>
                        <div class="popular-badge">Populaire</div>
                    </label>
                </div>
            </div>

            {{-- Terms Checkbox --}}
            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input
                        type="checkbox"
                        name="terms"
                        x-model="formData.terms"
                        required
                        class="form-checkbox"
                    >
                    <span class="checkbox-text">
                        J'accepte le système d'entraide 7 Ensemble et comprends le principe de solidarité mutuelle.
                        <a href="{{ route('legal') }}" target="_blank" class="link">Conditions légales</a>
                    </span>
                </label>
                <span x-show="errors.terms" class="error-message" x-text="errors.terms"></span>
            </div>

            {{-- Submit Button --}}
            <button
                type="submit"
                class="btn btn-primary btn-submit"
                :disabled="loading"
                :class="{ 'loading': loading }"
            >
                <span x-show="!loading">
                    🚀 Créer Ma Constellation !
                </span>
                <span x-show="loading" class="loading-spinner">
                    <span class="spinner"></span> Traitement en cours...
                </span>
            </button>

            <p class="form-footer">
                Bravo, votre aventure commence ici 💖
            </p>
        </form>
    </div>
</div>

{{-- Alpine.js Logic --}}
<script>
document.addEventListener('alpine:init', () => {
    // Global function to open modal
    window.openModal = function(modalId) {
        window.dispatchEvent(new CustomEvent('open-modal', { detail: modalId }));
    };
});

// Form submission handler
function submitRegistration() {
    this.loading = true;
    this.errors = {};

    // Basic validation
    if (!this.formData.fullName) {
        this.errors.fullName = 'Le nom est requis';
    }
    if (!this.formData.email || !this.formData.email.includes('@')) {
        this.errors.email = 'Email valide requis';
    }
    if (!this.formData.country) {
        this.errors.country = 'Veuillez sélectionner un pays';
    }
    if (!this.formData.paymentMethod) {
        this.errors.paymentMethod = 'Veuillez choisir un mode de paiement';
    }
    if (!this.formData.terms) {
        this.errors.terms = 'Vous devez accepter les conditions';
    }

    if (Object.keys(this.errors).length > 0) {
        this.loading = false;
        return;
    }

    // Submit form
    this.$el.querySelector('form').submit();
}
</script>

<style>
/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(5px);
}

.modal-content {
    position: relative;
    background: linear-gradient(180deg, #1a237e 0%, #3949ab 100%);
    border-radius: 20px;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    padding: 2.5rem;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.2);
    animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.modal-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    font-size: 2rem;
    color: white;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: rotate(90deg);
}

.modal-header {
    text-align: center;
    margin-bottom: 2rem;
}

.modal-header h2 {
    font-size: 2rem;
    color: #4ecdc4;
    margin-bottom: 0.5rem;
}

.modal-subtitle {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.9);
}

.modal-subtitle .highlight {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 1.3rem;
}

/* Form Styles */
.registration-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: rgba(255, 255, 255, 0.9);
    font-weight: 600;
    font-size: 1rem;
}

.label-icon {
    font-size: 1.2rem;
}

.form-input,
.form-select {
    padding: 0.875rem 1rem;
    border-radius: 10px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    background: rgba(255, 255, 255, 0.1);
    color: white;
    font-size: 1rem;
    transition: all 0.3s;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: #4ecdc4;
    background: rgba(255, 255, 255, 0.15);
}

.form-input.error,
.form-select.error {
    border-color: #ff6b6b;
}

.form-input::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.error-message {
    color: #ff6b6b;
    font-size: 0.875rem;
    margin-top: -0.25rem;
}

.form-help {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.6);
    margin-top: -0.25rem;
}

/* Option Selection */
.option-selection {
    margin: 1.5rem 0;
}

.option-title {
    text-align: center;
    font-size: 1.5rem;
    color: #4ecdc4;
    margin-bottom: 1rem;
}

.option-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.option-card {
    position: relative;
    background: rgba(255, 255, 255, 0.05);
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 15px;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.3s;
}

.option-card input[type="radio"] {
    display: none;
}

.option-card:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-5px);
}

.option-card.selected {
    border-color: #4ecdc4;
    background: rgba(78, 205, 196, 0.15);
    box-shadow: 0 8px 20px rgba(78, 205, 196, 0.3);
}

.option-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.option-icon {
    font-size: 1.5rem;
}

.option-name {
    font-weight: 600;
    color: white;
    font-size: 1rem;
}

.option-amount {
    font-size: 1.8rem;
    font-weight: bold;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
}

.option-desc {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9rem;
}

.option-card.popular .popular-badge {
    position: absolute;
    top: -10px;
    right: -10px;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
    box-shadow: 0 4px 10px rgba(245, 87, 108, 0.4);
}

/* Checkbox */
.checkbox-group {
    margin: 1rem 0;
}

.checkbox-label {
    display: flex;
    gap: 0.75rem;
    cursor: pointer;
    align-items: flex-start;
}

.form-checkbox {
    width: 20px;
    height: 20px;
    margin-top: 0.25rem;
    cursor: pointer;
    flex-shrink: 0;
}

.checkbox-text {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.95rem;
    line-height: 1.5;
}

.checkbox-text .link {
    color: #4ecdc4;
    text-decoration: underline;
}

/* Submit Button */
.btn-submit {
    width: 100%;
    font-size: 1.2rem;
    padding: 1rem 2rem;
    margin-top: 1rem;
    position: relative;
}

.btn-submit.loading {
    cursor: not-allowed;
    opacity: 0.7;
}

.loading-spinner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.form-footer {
    text-align: center;
    color: rgba(255, 255, 255, 0.8);
    font-style: italic;
    font-size: 1.1rem;
    margin-top: 0.5rem;
}

/* Responsive */
@media (max-width: 640px) {
    .modal-content {
        padding: 1.5rem;
    }

    .modal-header h2 {
        font-size: 1.5rem;
    }

    .option-cards {
        grid-template-columns: 1fr;
    }
}
</style>
