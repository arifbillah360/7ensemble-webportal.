/**
 * 7 Ensemble - Constellation Animation
 * Handles rotating constellation orbits with configurable speed
 */

class ConstellationAnimation {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error(`Container with id "${containerId}" not found`);
            return;
        }

        // Configuration options
        this.options = {
            orbitSpeed: options.orbitSpeed || 30, // seconds for one full rotation
            reverseDirection: options.reverseDirection || false,
            pauseOnHover: options.pauseOnHover || true,
            autoStart: options.autoStart !== false,
            glowEffect: options.glowEffect !== false,
            ...options
        };

        this.isAnimating = false;
        this.isPaused = false;
        this.orbits = [];

        this.init();
    }

    /**
     * Initialize the constellation animation
     */
    init() {
        this.findOrbits();
        this.setupEventListeners();

        if (this.options.autoStart) {
            this.start();
        }

        if (this.options.glowEffect) {
            this.addGlowEffect();
        }
    }

    /**
     * Find all orbit elements in the container
     */
    findOrbits() {
        const orbitElements = this.container.querySelectorAll('.orbit');
        this.orbits = Array.from(orbitElements).map((orbit, index) => {
            return {
                element: orbit,
                speed: this.calculateSpeed(index),
                direction: this.calculateDirection(index),
                members: this.findOrbitMembers(orbit)
            };
        });
    }

    /**
     * Calculate speed for each orbit (inner orbits faster, outer orbits slower)
     */
    calculateSpeed(index) {
        const baseSpeed = this.options.orbitSpeed;
        // Inner orbits are faster (multiply by 0.7), outer orbits slower (multiply by 1.3)
        const speedModifier = 1 + (index * 0.2);
        return baseSpeed * speedModifier;
    }

    /**
     * Calculate direction for each orbit (alternating directions)
     */
    calculateDirection(index) {
        if (this.options.reverseDirection) {
            return index % 2 === 0 ? 'reverse' : 'normal';
        }
        return 'normal';
    }

    /**
     * Find all member elements within an orbit
     */
    findOrbitMembers(orbit) {
        return Array.from(orbit.querySelectorAll('.orbit-member, .member-dot'));
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        if (this.options.pauseOnHover) {
            this.container.addEventListener('mouseenter', () => this.pause());
            this.container.addEventListener('mouseleave', () => this.resume());
        }

        // Pause on window blur (user switched tab)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pause();
            } else {
                this.resume();
            }
        });
    }

    /**
     * Start the animation
     */
    start() {
        if (this.isAnimating) return;

        this.isAnimating = true;
        this.isPaused = false;

        this.orbits.forEach(orbit => {
            // Apply rotation animation to orbit
            orbit.element.style.animation = `rotate-orbit ${orbit.speed}s linear infinite ${orbit.direction}`;

            // Apply counter-rotation to members to keep them upright
            orbit.members.forEach(member => {
                member.style.animation = `counter-rotate ${orbit.speed}s linear infinite ${orbit.direction === 'reverse' ? 'normal' : 'reverse'}`;
            });
        });

        console.log(`Constellation animation started for ${this.orbits.length} orbits`);
    }

    /**
     * Pause the animation
     */
    pause() {
        if (!this.isAnimating || this.isPaused) return;

        this.isPaused = true;

        this.orbits.forEach(orbit => {
            orbit.element.style.animationPlayState = 'paused';
            orbit.members.forEach(member => {
                member.style.animationPlayState = 'paused';
            });
        });
    }

    /**
     * Resume the animation
     */
    resume() {
        if (!this.isAnimating || !this.isPaused) return;

        this.isPaused = false;

        this.orbits.forEach(orbit => {
            orbit.element.style.animationPlayState = 'running';
            orbit.members.forEach(member => {
                member.style.animationPlayState = 'running';
            });
        });
    }

    /**
     * Stop the animation
     */
    stop() {
        if (!this.isAnimating) return;

        this.isAnimating = false;
        this.isPaused = false;

        this.orbits.forEach(orbit => {
            orbit.element.style.animation = 'none';
            orbit.members.forEach(member => {
                member.style.animation = 'none';
            });
        });

        console.log('Constellation animation stopped');
    }

    /**
     * Change animation speed
     */
    setSpeed(newSpeed) {
        this.options.orbitSpeed = newSpeed;

        if (this.isAnimating) {
            this.stop();
            this.findOrbits(); // Recalculate speeds
            this.start();
        }
    }

    /**
     * Reverse animation direction
     */
    reverseDirection() {
        this.options.reverseDirection = !this.options.reverseDirection;

        if (this.isAnimating) {
            this.stop();
            this.findOrbits(); // Recalculate directions
            this.start();
        }
    }

    /**
     * Add glow effect to Alcyone (center)
     */
    addGlowEffect() {
        const alcyone = this.container.querySelector('.alcyone, .constellation-center');
        if (alcyone) {
            alcyone.classList.add('alcyone-glow');
        }
    }

    /**
     * Remove glow effect
     */
    removeGlowEffect() {
        const alcyone = this.container.querySelector('.alcyone, .constellation-center');
        if (alcyone) {
            alcyone.classList.remove('alcyone-glow');
        }
    }

    /**
     * Destroy the animation and clean up
     */
    destroy() {
        this.stop();

        if (this.options.pauseOnHover) {
            this.container.removeEventListener('mouseenter', () => this.pause());
            this.container.removeEventListener('mouseleave', () => this.resume());
        }

        this.orbits = [];
        this.container = null;

        console.log('Constellation animation destroyed');
    }

    /**
     * Get current animation state
     */
    getState() {
        return {
            isAnimating: this.isAnimating,
            isPaused: this.isPaused,
            orbitCount: this.orbits.length,
            speed: this.options.orbitSpeed,
            reverseDirection: this.options.reverseDirection
        };
    }
}

/**
 * Initialize all constellations on the page
 */
function initConstellations() {
    const constellations = document.querySelectorAll('.constellation-container');
    const animations = [];

    constellations.forEach((constellation, index) => {
        const id = constellation.id || `constellation-${index}`;
        constellation.id = id;

        // Get configuration from data attributes
        const options = {
            orbitSpeed: parseFloat(constellation.dataset.speed) || 30,
            reverseDirection: constellation.dataset.reverse === 'true',
            pauseOnHover: constellation.dataset.pauseOnHover !== 'false',
            glowEffect: constellation.dataset.glow !== 'false'
        };

        const animation = new ConstellationAnimation(id, options);
        animations.push(animation);
    });

    console.log(`Initialized ${animations.length} constellation animations`);
    return animations;
}

/**
 * Auto-initialize when DOM is ready
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initConstellations);
} else {
    // DOM already loaded
    initConstellations();
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ConstellationAnimation, initConstellations };
}
