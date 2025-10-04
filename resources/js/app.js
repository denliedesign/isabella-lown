import { gsap } from "gsap";
import { ScrollToPlugin } from "gsap/ScrollToPlugin";
gsap.registerPlugin(ScrollToPlugin);

function getScroller(el) {
    // Find the nearest scrollable ancestor; fall back to document/window
    let n = el.parentElement;
    while (n && n !== document.body) {
        const cs = getComputedStyle(n);
        const canScroll = /(auto|scroll|overlay)/.test(cs.overflowY) && n.scrollHeight > n.clientHeight;
        if (canScroll) return n;
        n = n.parentElement;
    }
    // Standard root scrolling element across browsers
    return document.scrollingElement || document.documentElement || window;
}

function initBackToTop() {
    const btn  = document.getElementById("toTopBtn");
    const wrap = document.getElementById("toTopWrap");
    if (!btn || !wrap) return;

    const show = () => {
        if (btn.dataset.shown) return;
        btn.dataset.shown = "1";
        btn.classList.remove("hidden");
        gsap.fromTo(wrap, { y: 16, opacity: 0 }, { y: 0, opacity: 1, duration: 0.2, ease: "power2.out" });
    };
    const hide = () => {
        if (!btn.dataset.shown) return;
        delete btn.dataset.shown;
        gsap.to(wrap, { y: 16, opacity: 0, duration: 0.15, onComplete: () => btn.classList.add("hidden") });
    };

    const onScroll = () => {
        // Always check the current scroller—Flux may change it per page
        const scroller = getScroller(btn);
        const y = scroller === window ? window.scrollY : scroller.scrollTop;
        (y > 300) ? show() : hide();
    };

    // Rebind click each init (remove old just in case)
    btn.replaceWith(btn.cloneNode(true));
    const freshBtn = document.getElementById("toTopBtn");

    freshBtn.addEventListener("click", () => {
        const scroller = getScroller(freshBtn);
        // If GSAP is unavailable or plugin not loaded, graceful native fallback
        if (!gsap || !gsap.plugins || !gsap.plugins.scrollTo) {
            // Native smooth scroll
            if (scroller === document.scrollingElement || scroller === document.documentElement) {
                window.scrollTo({ top: 0, behavior: "smooth" });
            } else {
                scroller.scrollTo({ top: 0, behavior: "smooth" });
            }
            return;
        }

        // GSAP scroll (works for either window root or element scroller)
        gsap.to(scroller === window ? window : scroller, {
            duration: 0.6,
            scrollTo: { y: 0, autoKill: true },
            ease: "power2.out",
        });
    });

    // Observe scroll on both window and possible element scroller
    // 1) Window:
    onScroll();
    window.removeEventListener("scroll", onScroll);
    window.addEventListener("scroll", onScroll, { passive: true });

    // 2) Element scroller (if different from window):
    const scroller = getScroller(freshBtn);
    if (scroller !== window && scroller !== document.scrollingElement && scroller !== document.documentElement) {
        scroller.removeEventListener?.("scroll", onScroll);
        scroller.addEventListener?.("scroll", onScroll, { passive: true });
    }
}

// Initial load
document.addEventListener("DOMContentLoaded", initBackToTop);

// Re-init after Flux/Livewire navigations (covering common events)
document.addEventListener("livewire:navigated", initBackToTop);
document.addEventListener("flux:navigated", initBackToTop);      // if Flux emits this
document.addEventListener("turbo:load", initBackToTop);          // if using Turbo
window.addEventListener("pageshow", initBackToTop);              // bfcache restores
