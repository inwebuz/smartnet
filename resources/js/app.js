const hero = document.querySelector('[data-cursor-glow]');

if (hero) {
    const glow = hero.querySelector('.hero-cursor-glow');
    const precisePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let currentX = 0;
    let currentY = 0;
    let targetX = 0;
    let targetY = 0;
    let frame = null;

    const restingPosition = () => {
        targetX = hero.clientWidth * 0.78;
        targetY = hero.clientHeight * 0.26;
    };

    const draw = () => {
        currentX += (targetX - currentX) * 0.13;
        currentY += (targetY - currentY) * 0.13;
        glow.style.setProperty('--glow-x', `${currentX}px`);
        glow.style.setProperty('--glow-y', `${currentY}px`);

        if (Math.abs(targetX - currentX) + Math.abs(targetY - currentY) < 0.5) {
            frame = null;
            return;
        }

        frame = window.requestAnimationFrame(draw);
    };

    const animate = () => {
        if (frame === null) {
            frame = window.requestAnimationFrame(draw);
        }
    };

    const reset = () => {
        restingPosition();
        if (precisePointer.matches && !reducedMotion.matches) {
            animate();
        } else {
            if (frame !== null) {
                window.cancelAnimationFrame(frame);
                frame = null;
            }
            currentX = targetX;
            currentY = targetY;
            glow.style.setProperty('--glow-x', `${currentX}px`);
            glow.style.setProperty('--glow-y', `${currentY}px`);
        }
    };

    restingPosition();
    currentX = targetX;
    currentY = targetY;
    reset();

    hero.addEventListener('pointermove', (event) => {
        if (!precisePointer.matches || reducedMotion.matches) {
            return;
        }

        const bounds = hero.getBoundingClientRect();
        targetX = Math.max(0, Math.min(bounds.width, event.clientX - bounds.left));
        targetY = Math.max(0, Math.min(bounds.height, event.clientY - bounds.top));
        animate();
    });
    hero.addEventListener('pointerleave', reset);
    window.addEventListener('resize', reset);
    precisePointer.addEventListener('change', reset);
    reducedMotion.addEventListener('change', reset);
}

const revealElements = document.querySelectorAll('[data-reveal]');

if (revealElements.length && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    revealElements.forEach((element) => observer.observe(element));
    document.documentElement.classList.add('motion-ready');
}
