type ThemeMode = 'auto' | 'light' | 'dark';

type GoalgusBoot = {
  locale: 'en' | 'bg';
  contactUrl: string;
  strings: Record<string, string>;
};

declare global {
  interface Window {
    __GOALGUS__?: GoalgusBoot;
  }
}

const THEME_KEY = 'goalgus-theme';

function hourTheme(): 'light' | 'dark' {
  const hour = new Date().getHours();
  return hour < 6 || hour >= 18 ? 'dark' : 'light';
}

function resolveTheme(mode: ThemeMode): 'light' | 'dark' {
  return mode === 'auto' ? hourTheme() : mode;
}

function applyTheme(mode: ThemeMode): void {
  const theme = resolveTheme(mode);
  document.documentElement.setAttribute('data-theme', theme);
  document.documentElement.setAttribute('data-theme-mode', mode);
  document.querySelectorAll<HTMLButtonElement>('[data-theme-set]').forEach((btn) => {
    btn.setAttribute('aria-pressed', btn.dataset.themeSet === mode ? 'true' : 'false');
  });
}

function initTheme(): void {
  const stored = (localStorage.getItem(THEME_KEY) as ThemeMode | null) ?? 'auto';
  const mode: ThemeMode = stored === 'light' || stored === 'dark' || stored === 'auto' ? stored : 'auto';
  applyTheme(mode);

  document.querySelectorAll<HTMLButtonElement>('[data-theme-set]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const next = (btn.dataset.themeSet || 'auto') as ThemeMode;
      localStorage.setItem(THEME_KEY, next);
      applyTheme(next);
    });
  });

  // Re-evaluate auto theme every 15 minutes in case the hour boundary passes.
  window.setInterval(() => {
    const current = (localStorage.getItem(THEME_KEY) as ThemeMode | null) ?? 'auto';
    if (current === 'auto') {
      applyTheme('auto');
    }
  }, 15 * 60 * 1000);
}

function initNav(): void {
  const toggle = document.querySelector<HTMLButtonElement>('[data-nav-toggle]');
  const panel = document.getElementById('mobile-nav');
  if (!toggle || !panel) return;

  const setOpen = (open: boolean): void => {
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    panel.hidden = !open;
    panel.classList.toggle('is-open', open);
  };

  setOpen(false);

  toggle.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') === 'true';
    setOpen(!open);
  });
}

function initReveal(): void {
  const nodes = document.querySelectorAll<HTMLElement>('.reveal');
  if (!('IntersectionObserver' in window) || nodes.length === 0) {
    nodes.forEach((n) => n.classList.add('is-in'));
    return;
  }

  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-in');
          io.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.16 }
  );

  nodes.forEach((n) => io.observe(n));
}

function initContactForm(): void {
  const form = document.querySelector<HTMLFormElement>('[data-contact-form]');
  const boot = window.__GOALGUS__;
  if (!form || !boot?.contactUrl) return;

  const status = form.querySelector<HTMLElement>('[data-form-status]');
  const submit = form.querySelector<HTMLButtonElement>('[type="submit"]');

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!submit || !status) return;

    status.dataset.state = '';
    status.textContent = '';
    submit.disabled = true;
    const previous = submit.textContent;
    submit.textContent = boot.strings.sending || 'Sending…';

    const data = new FormData(form);
    const payload = {
      name: String(data.get('name') || ''),
      email: String(data.get('email') || ''),
      company: String(data.get('company') || '') || null,
      message: String(data.get('message') || ''),
      website: String(data.get('website') || ''),
      locale: boot.locale,
    };

    try {
      const res = await fetch(boot.contactUrl, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
      });
      const json = (await res.json().catch(() => ({}))) as { ok?: boolean; message?: string };
      if (!res.ok || !json.ok) {
        throw new Error(json.message || boot.strings.error || 'Error');
      }
      status.dataset.state = 'ok';
      status.textContent = boot.strings.success || 'Sent.';
      form.reset();
    } catch {
      status.dataset.state = 'err';
      status.textContent = boot.strings.error || 'Error';
    } finally {
      submit.disabled = false;
      submit.textContent = previous;
    }
  });
}

function initCopyFacts(): void {
  document.querySelectorAll<HTMLButtonElement>('[data-copy]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const text = btn.dataset.copy || '';
      if (!text) return;

      try {
        await navigator.clipboard.writeText(text);
      } catch {
        const area = document.createElement('textarea');
        area.value = text;
        area.setAttribute('readonly', '');
        area.style.position = 'fixed';
        area.style.opacity = '0';
        document.body.appendChild(area);
        area.select();
        document.execCommand('copy');
        area.remove();
      }

      const previous = btn.textContent;
      btn.textContent = btn.dataset.labelCopied || 'Copied';
      btn.classList.add('is-copied');
      window.setTimeout(() => {
        btn.textContent = btn.dataset.labelCopy || previous || 'Copy';
        btn.classList.remove('is-copied');
      }, 1400);
    });
  });
}

type RainColumn = {
  x: number;
  y: number;
  speed: number;
  length: number;
  size: number;
  depth: number;
  glyphs: string[];
};

function isDarkTheme(): boolean {
  return document.documentElement.getAttribute('data-theme') === 'dark';
}

function initBinaryRain(): void {
  const canvas = document.querySelector<HTMLCanvasElement>('[data-binary-rain]');
  if (!canvas) return;

  const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
  const ctx = canvas.getContext('2d', { alpha: false });
  if (!ctx) return;

  let columns: RainColumn[] = [];
  let width = 0;
  let height = 0;
  let frame = 0;
  let raf = 0;
  let running = false;
  let dark = isDarkTheme();
  let reduced = motionQuery.matches;
  // Desktop tall heroes need a bit more speed so the fall still reads.
  let speedScale = 1;

  const bit = (): string => (Math.random() > 0.5 ? '1' : '0');

  const paintSky = (): void => {
    const g = ctx.createLinearGradient(0, 0, 0, height);
    if (dark) {
      g.addColorStop(0, '#020617');
      g.addColorStop(0.22, '#0a1f55');
      g.addColorStop(0.48, '#163a7a');
      g.addColorStop(0.72, '#0d1830');
      g.addColorStop(1, '#070b14');
    } else {
      g.addColorStop(0, '#020b29');
      g.addColorStop(0.18, '#0a2a6e');
      g.addColorStop(0.38, '#1f5fe0');
      g.addColorStop(0.55, '#7eb0ff');
      g.addColorStop(0.72, '#d7e6ff');
      g.addColorStop(0.88, '#f4f8fc');
      g.addColorStop(1, '#eef3f9');
    }
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, width, height);

    // Soft bloom near the wash-out band (matches fromto feel).
    const bloom = ctx.createRadialGradient(
      width * 0.5,
      height * (dark ? 0.42 : 0.58),
      0,
      width * 0.5,
      height * (dark ? 0.42 : 0.58),
      height * 0.55
    );
    if (dark) {
      bloom.addColorStop(0, 'rgba(91, 140, 255, 0.28)');
      bloom.addColorStop(1, 'rgba(91, 140, 255, 0)');
    } else {
      bloom.addColorStop(0, 'rgba(255, 255, 255, 0.55)');
      bloom.addColorStop(0.45, 'rgba(190, 220, 255, 0.22)');
      bloom.addColorStop(1, 'rgba(255, 255, 255, 0)');
    }
    ctx.fillStyle = bloom;
    ctx.fillRect(0, 0, width, height);
  };

  /** Digits stay strong in the upper band, then dissolve into the white/dark floor. */
  const bandAlpha = (y: number): number => {
    const t = y / Math.max(1, height);
    if (t < 0.08) return 0.35 + (t / 0.08) * 0.65;
    if (t < 0.42) return 1;
    if (t < 0.68) return 1 - ((t - 0.42) / 0.26) * 0.92;
    return Math.max(0, 0.08 - ((t - 0.68) / 0.32) * 0.08);
  };

  const makeColumn = (x: number): RainColumn => {
    const depth = Math.random();
    const size = Math.round((depth < 0.35 ? 12 : depth < 0.7 ? 16 : 20) * (width < 640 ? 0.9 : 1));
    const length = 14 + Math.floor(Math.random() * 28);
    return {
      x,
      y: Math.random() * height * 1.2 - height * 0.2,
      speed: (2.2 + Math.random() * 4.2) * (0.55 + depth) * speedScale,
      length,
      size,
      depth,
      glyphs: Array.from({ length }, bit),
    };
  };

  const resize = (): void => {
    const parent = canvas.parentElement;
    if (!parent) return;
    const rect = parent.getBoundingClientRect();
    const nextW = Math.max(1, Math.floor(rect.width));
    const nextH = Math.max(1, Math.floor(rect.height));
    // Ignore sub-pixel thrash from ResizeObserver / scrollbars.
    if (Math.abs(nextW - width) < 2 && Math.abs(nextH - height) < 2 && columns.length) {
      return;
    }

    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    width = nextW;
    height = nextH;
    speedScale = Math.max(1, height / 720);
    canvas.width = Math.floor(width * dpr);
    canvas.height = Math.floor(height * dpr);
    // Keep sizing in CSS (width/height: 100%) — inline styles can fight layout.
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    const step = width < 640 ? 12 : 14;
    const count = Math.ceil(width / step) + 2;
    columns = Array.from({ length: count }, (_, i) => makeColumn(i * step + (Math.random() * 4 - 2)));
    paintSky();
  };

  const drawFrame = (animate: boolean): void => {
    dark = isDarkTheme();
    paintSky();
    ctx.textBaseline = 'top';
    ctx.textAlign = 'center';

    if (animate) frame += 1;

    columns.forEach((col, index) => {
      if (animate) {
        col.y += col.speed;
        if (col.y - col.length * col.size > height * 0.85) {
          const next = makeColumn(col.x);
          next.y = -Math.random() * height * 0.45;
          columns[index] = next;
          col = next;
        }
        if (frame % 3 === index % 3) {
          col.glyphs[Math.floor(Math.random() * col.glyphs.length)] = bit();
        }
      }

      ctx.font = `600 ${col.size}px "JetBrains Mono", ui-monospace, monospace`;

      for (let i = 0; i < col.length; i += 1) {
        const gy = col.y - i * col.size * 0.92;
        if (gy < -col.size || gy > height) continue;

        const fade = bandAlpha(gy) * (0.35 + col.depth * 0.65) * (1 - i / col.length * 0.55);
        if (fade < 0.03) continue;

        if (i === 0) {
          ctx.shadowColor = dark ? 'rgba(160, 200, 255, 0.9)' : 'rgba(120, 180, 255, 0.95)';
          ctx.shadowBlur = 14 + col.depth * 10;
          ctx.fillStyle = dark ? '#e8f1ff' : '#ffffff';
          ctx.globalAlpha = Math.min(1, fade * 1.15);
        } else if (i < 3) {
          ctx.shadowColor = 'rgba(59, 124, 255, 0.65)';
          ctx.shadowBlur = 8;
          ctx.fillStyle = dark ? '#9ec0ff' : '#dcecff';
          ctx.globalAlpha = fade;
        } else {
          ctx.shadowBlur = 0;
          ctx.fillStyle = dark ? '#5b8cff' : '#3b7cff';
          ctx.globalAlpha = fade * (0.55 + (1 - i / col.length) * 0.45);
        }
        ctx.fillText(col.glyphs[i] || bit(), col.x, gy);
      }
    });

    ctx.shadowBlur = 0;
    ctx.globalAlpha = 1;
  };

  const tick = (): void => {
    if (!running) return;
    drawFrame(true);
    raf = window.requestAnimationFrame(tick);
  };

  const start = (): void => {
    if (running || reduced) return;
    running = true;
    raf = window.requestAnimationFrame(tick);
  };

  const stop = (): void => {
    running = false;
    window.cancelAnimationFrame(raf);
  };

  const syncMotionPreference = (): void => {
    reduced = motionQuery.matches;
    if (reduced) {
      stop();
      drawFrame(false);
    } else {
      start();
    }
  };

  resize();
  drawFrame(false);
  syncMotionPreference();

  let resizeRaf = 0;
  const ro = new ResizeObserver(() => {
    window.cancelAnimationFrame(resizeRaf);
    resizeRaf = window.requestAnimationFrame(() => {
      const prevW = width;
      const prevH = height;
      resize();
      // Only force a static redraw when the bitmap actually changed;
      // otherwise the animation loop keeps owning the frames.
      if (width !== prevW || height !== prevH) {
        drawFrame(running);
      }
    });
  });
  if (canvas.parentElement) ro.observe(canvas.parentElement);

  const mo = new MutationObserver(() => {
    dark = isDarkTheme();
    // Theme change paints sky colors; keep the loop if it is already running.
    drawFrame(running);
  });
  mo.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

  const onMotionChange = (): void => syncMotionPreference();
  if (typeof motionQuery.addEventListener === 'function') {
    motionQuery.addEventListener('change', onMotionChange);
  } else {
    motionQuery.addListener(onMotionChange);
  }

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) stop();
    else syncMotionPreference();
  });
}

function fitHeroBrand(): void {
  const el = document.querySelector<HTMLElement>('.hero__brand');
  if (!el) return;

  const fit = (): void => {
    el.style.fontSize = '';
    const parent = el.parentElement;
    if (!parent) return;
    const max = parent.clientWidth;
    if (max <= 0) return;

    // Binary search a size that keeps the full word on one line.
    let low = 18;
    let high = Math.min(96, Math.floor(max * 0.34));
    let best = low;
    while (low <= high) {
      const mid = Math.floor((low + high) / 2);
      el.style.fontSize = `${mid}px`;
      if (el.scrollWidth <= max) {
        best = mid;
        low = mid + 1;
      } else {
        high = mid - 1;
      }
    }
    el.style.fontSize = `${best}px`;
  };

  fit();
  window.addEventListener('resize', fit, { passive: true });
  if ('fonts' in document) {
    void document.fonts.ready.then(fit);
  }
}

function boot(): void {
  initTheme();
  initNav();
  initReveal();
  initContactForm();
  initCopyFacts();
  initBinaryRain();
  fitHeroBrand();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot);
} else {
  boot();
}
