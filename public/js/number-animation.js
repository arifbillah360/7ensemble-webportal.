/**
 * 7 Ensemble - Number Animation
 * Animated counting up effect for statistics and numbers
 */

/**
 * Number Counter Class
 */
class NumberCounter {
    constructor(element, options = {}) {
        this.element = element;
        this.target = parseFloat(element.dataset.target || element.textContent);
        this.start = parseFloat(options.start || 0);
        this.duration = parseFloat(options.duration || 2000); // milliseconds
        this.decimals = parseInt(options.decimals || 0);
        this.separator = options.separator || ' '; // Thousands separator
        this.prefix = options.prefix || '';
        this.suffix = options.suffix || '';
        this.easing = options.easing || 'easeOutQuad';
        this.onComplete = options.onComplete || null;

        this.currentValue = this.start;
        this.animationFrame = null;
        this.startTime = null;
        this.hasAnimated = false;
    }

    /**
     * Start the animation
     */
    animate() {
        if (this.hasAnimated) return;

        this.hasAnimated = true;
        this.startTime = Date.now();
        this.step();
    }

    /**
     * Animation step
     */
    step() {
        const elapsed = Date.now() - this.startTime;
        const progress = Math.min(elapsed / this.duration, 1);

        // Apply easing function
        const easedProgress = this.applyEasing(progress);

        // Calculate current value
        this.currentValue = this.start + (this.target - this.start) * easedProgress;

        // Update display
        this.updateDisplay();

        // Continue or complete
        if (progress < 1) {
            this.animationFrame = requestAnimationFrame(() => this.step());
        } else {
            this.complete();
        }
    }

    /**
     * Update the display
     */
    updateDisplay() {
        const formattedValue = this.formatNumber(this.currentValue);
        this.element.textContent = this.prefix + formattedValue + this.suffix;
    }

    /**
     * Format number with separators and decimals
     */
    formatNumber(value) {
        const fixed = value.toFixed(this.decimals);
        const parts = fixed.split('.');
        const integerPart = parts[0];
        const decimalPart = parts[1];

        // Add thousands separator
        const formatted = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, this.separator);

        return decimalPart ? `${formatted},${decimalPart}` : formatted;
    }

    /**
     * Apply easing function
     */
    applyEasing(t) {
        switch (this.easing) {
            case 'linear':
                return t;
            case 'easeInQuad':
                return t * t;
            case 'easeOutQuad':
                return t * (2 - t);
            case 'easeInOutQuad':
                return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
            case 'easeInCubic':
                return t * t * t;
            case 'easeOutCubic':
                return (--t) * t * t + 1;
            case 'easeInOutCubic':
                return t < 0.5 ? 4 * t * t * t : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1;
            case 'easeInQuart':
                return t * t * t * t;
            case 'easeOutQuart':
                return 1 - (--t) * t * t * t;
            case 'easeInOutQuart':
                return t < 0.5 ? 8 * t * t * t * t : 1 - 8 * (--t) * t * t * t;
            case 'easeOutElastic':
                const p = 0.3;
                return Math.pow(2, -10 * t) * Math.sin((t - p / 4) * (2 * Math.PI) / p) + 1;
            case 'easeOutBounce':
                if (t < (1 / 2.75)) {
                    return 7.5625 * t * t;
                } else if (t < (2 / 2.75)) {
                    return 7.5625 * (t -= (1.5 / 2.75)) * t + 0.75;
                } else if (t < (2.5 / 2.75)) {
                    return 7.5625 * (t -= (2.25 / 2.75)) * t + 0.9375;
                } else {
                    return 7.5625 * (t -= (2.625 / 2.75)) * t + 0.984375;
                }
            default:
                return t;
        }
    }

    /**
     * Complete the animation
     */
    complete() {
        this.currentValue = this.target;
        this.updateDisplay();

        if (this.onComplete && typeof this.onComplete === 'function') {
            this.onComplete();
        }
    }

    /**
     * Reset the animation
     */
    reset() {
        if (this.animationFrame) {
            cancelAnimationFrame(this.animationFrame);
        }
        this.currentValue = this.start;
        this.hasAnimated = false;
        this.updateDisplay();
    }

    /**
     * Destroy the counter
     */
    destroy() {
        if (this.animationFrame) {
            cancelAnimationFrame(this.animationFrame);
        }
    }
}

/**
 * Intersection Observer for triggering animations on scroll
 */
class NumberAnimationObserver {
    constructor(options = {}) {
        this.options = {
            threshold: options.threshold || 0.5,
            rootMargin: options.rootMargin || '0px',
            triggerOnce: options.triggerOnce !== false,
            ...options
        };

        this.counters = new Map();
        this.observer = null;
        this.init();
    }

    /**
     * Initialize the observer
     */
    init() {
        this.observer = new IntersectionObserver(
            (entries) => this.handleIntersection(entries),
            {
                threshold: this.options.threshold,
                rootMargin: this.options.rootMargin
            }
        );

        this.observeElements();
    }

    /**
     * Observe all elements with animate-number class
     */
    observeElements() {
        const elements = document.querySelectorAll('.animate-number');

        elements.forEach(element => {
            // Create counter instance
            const counter = new NumberCounter(element, {
                duration: parseFloat(element.dataset.duration) || 2000,
                decimals: parseInt(element.dataset.decimals) || 0,
                separator: element.dataset.separator || ' ',
                prefix: element.dataset.prefix || '',
                suffix: element.dataset.suffix || '',
                easing: element.dataset.easing || 'easeOutQuad'
            });

            this.counters.set(element, counter);
            this.observer.observe(element);
        });

        console.log(`Number Animation Observer initialized for ${elements.length} elements`);
    }

    /**
     * Handle intersection changes
     */
    handleIntersection(entries) {
        entries.forEach(entry => {
            const counter = this.counters.get(entry.target);
            if (!counter) return;

            if (entry.isIntersecting) {
                counter.animate();

                if (this.options.triggerOnce) {
                    this.observer.unobserve(entry.target);
                }
            }
        });
    }

    /**
     * Add new element to observe
     */
    observe(element, options = {}) {
        const counter = new NumberCounter(element, options);
        this.counters.set(element, counter);
        this.observer.observe(element);
    }

    /**
     * Remove element from observation
     */
    unobserve(element) {
        const counter = this.counters.get(element);
        if (counter) {
            counter.destroy();
            this.counters.delete(element);
        }
        this.observer.unobserve(element);
    }

    /**
     * Reset all counters
     */
    resetAll() {
        this.counters.forEach(counter => counter.reset());
    }

    /**
     * Destroy observer
     */
    destroy() {
        this.counters.forEach(counter => counter.destroy());
        this.counters.clear();
        this.observer.disconnect();
    }
}

/**
 * Simple API for manual counter creation
 */
function createCounter(selector, options = {}) {
    const element = typeof selector === 'string'
        ? document.querySelector(selector)
        : selector;

    if (!element) {
        console.error('Element not found for counter:', selector);
        return null;
    }

    const counter = new NumberCounter(element, options);
    counter.animate();
    return counter;
}

/**
 * Animate all numbers on page (without intersection observer)
 */
function animateAllNumbers(selector = '.animate-number', options = {}) {
    const elements = document.querySelectorAll(selector);
    const counters = [];

    elements.forEach((element, index) => {
        const delay = options.staggerDelay ? index * options.staggerDelay : 0;

        setTimeout(() => {
            const counter = new NumberCounter(element, {
                duration: parseFloat(element.dataset.duration) || options.duration || 2000,
                decimals: parseInt(element.dataset.decimals) || options.decimals || 0,
                separator: element.dataset.separator || options.separator || ' ',
                prefix: element.dataset.prefix || options.prefix || '',
                suffix: element.dataset.suffix || options.suffix || '',
                easing: element.dataset.easing || options.easing || 'easeOutQuad'
            });

            counter.animate();
            counters.push(counter);
        }, delay);
    });

    return counters;
}

/**
 * Initialize number animations with Intersection Observer
 */
let numberAnimationObserver = null;

function initNumberAnimations(options = {}) {
    if (numberAnimationObserver) {
        numberAnimationObserver.destroy();
    }

    numberAnimationObserver = new NumberAnimationObserver(options);
    return numberAnimationObserver;
}

/**
 * Auto-initialize when DOM is ready
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initNumberAnimations();
    });
} else {
    // DOM already loaded
    initNumberAnimations();
}

/**
 * Expose global functions
 */
window.createCounter = createCounter;
window.animateAllNumbers = animateAllNumbers;
window.initNumberAnimations = initNumberAnimations;

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        NumberCounter,
        NumberAnimationObserver,
        createCounter,
        animateAllNumbers,
        initNumberAnimations
    };
}
