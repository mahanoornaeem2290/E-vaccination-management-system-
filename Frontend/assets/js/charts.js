/* Live dashboards: polls api/stats.php and animates Chart.js charts in place. Colours follow the day/night theme. */
const Live = (() => {
  const $ = id => document.getElementById(id);
  const css = n => getComputedStyle(document.documentElement).getPropertyValue(n).trim();
  const charts = {};
  let scope, api, timer, lastOk = 0, parentAxis = 'y';

  const pal = () => ({ teal: css('--teal'), mint: css('--mint'), sky: css('--sky'), sun: css('--sun'), coral: css('--coral'), ink: css('--ink'), muted: css('--muted'), grid: css('--grid') });
  const axes = p => ({ ticks: { color: p.muted, precision: 0 }, grid: { color: p.grid }, border: { display: false }, beginAtZero: true });
  const doughnutCenter = {
    id: 'vaxcareDoughnutCenter',
    afterDraw(chart) {
      if (chart.canvas.id !== 'cStatus') return;
      const meta = chart.getDatasetMeta(0);
      const arc = meta.data[0];
      if (!arc) return;
      const total = chart.data.datasets[0].data.reduce((sum, value, index) =>
        sum + (chart.getDataVisibility(index) ? Number(value) || 0 : 0), 0);
      const ctx = chart.ctx, p = pal();
      ctx.save();
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillStyle = p.muted;
      ctx.font = '600 10px Figtree, sans-serif';
      ctx.fillText('DOSES SHOWN', arc.x, arc.y - 13);
      ctx.fillStyle = p.ink;
      ctx.font = '800 27px "Bricolage Grotesque", sans-serif';
      ctx.fillText(String(total), arc.x, arc.y + 13);
      ctx.restore();
    }
  };

  function make(id, type, labels, sets, opts = {}) {
    const el = $(id); if (!el) return;
    const p = pal();
    charts[id] = new Chart(el, {
      type, data: { labels, datasets: sets(p) }, plugins: [doughnutCenter],
      options: Object.assign({
        responsive: true, maintainAspectRatio: false, animation: { duration: 900, easing: 'easeOutQuart' },
        plugins: { legend: { display: type === 'doughnut', position: 'bottom', labels: { color: p.ink, usePointStyle: true, boxWidth: 8 } } },
        scales: type === 'doughnut' ? {} : { x: { ticks: { color: p.muted }, grid: { display: false }, border: { display: false } }, y: axes(p) },
        cutout: type === 'doughnut' ? '64%' : undefined
      }, opts)
    });
  }
  function set(id, labels, ...series) {
    const c = charts[id]; if (!c) return;
    if (labels) c.data.labels = labels;
    series.forEach((d, i) => { c.data.datasets[i].data = d; });
    c.update();
  }
  const grad = (ctx, color) => { const g = ctx.createLinearGradient(0, 0, 0, 260); g.addColorStop(0, color + '88'); g.addColorStop(1, color + '00'); return g; };
  const parentScales = axis => {
    const p = pal();
    return axis === 'y'
      ? { x: { stacked: true, ...axes(p) }, y: { stacked: true, ticks: { color: p.ink }, grid: { display: false }, border: { display: false } } }
      : { x: { stacked: true, ticks: { color: p.ink }, grid: { display: false }, border: { display: false } }, y: { stacked: true, ...axes(p) } };
  };
  function setParentAxis(axis) {
    parentAxis = axis;
    const c = charts.cMembers;
    if (c) {
      c.options.indexAxis = axis;
      c.options.interaction.axis = axis;
      c.options.scales = parentScales(axis);
      c.update();
    }
    const button = $('chartOrientation');
    if (button) {
      button.setAttribute('aria-pressed', String(axis === 'x'));
      button.querySelector('span').textContent = axis === 'y' ? 'Vertical view' : 'Horizontal view';
      button.setAttribute('aria-label', `Switch to ${axis === 'y' ? 'vertical' : 'horizontal'} graph view`);
    }
  }

  const build = {
    admin(d) {
      const h = d.status;
      make('cStatus', 'doughnut', ['Vaccinated', 'Booked', 'Pending'], p => [{ data: [h.vaccinated, h.booked, h.pending], backgroundColor: [p.mint, p.sky, p.sun], borderWidth: 0, hoverOffset: 13, hoverBorderWidth: 3, hoverBorderColor: css('--glass-2'), spacing: 3, borderRadius: 6 }],
        { cutout: '76%', rotation: -90, plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: ctx => {
            const total = ctx.dataset.data.reduce((sum, value) => sum + (Number(value) || 0), 0);
            const value = Number(ctx.raw) || 0;
            const pct = total ? Math.round(value / total * 100) : 0;
            return `${ctx.label}: ${value.toLocaleString()} (${pct}%)`;
          } } }
        } });
      updateDoseStatus(h);
      make('cDaily', 'line', d.daily.labels, p => [{ label: 'Doses given', data: d.daily.values, borderColor: p.teal, borderWidth: 3, tension: .4, fill: true, pointRadius: 3, pointBackgroundColor: p.teal,
        backgroundColor: ctx => grad(ctx.chart.ctx, p.teal) }], { plugins: { legend: { display: false } } });
      make('cMonthly', 'bar', d.monthly.labels, p => [{ label: 'Appointments', data: d.monthly.values, backgroundColor: p.sky, borderRadius: 8 }], { plugins: { legend: { display: false } } });
      make('cAudience', 'doughnut', ['Children', 'Adults'], p => [{ data: [d.audience.child, d.audience.adult], backgroundColor: [p.mint, p.sky], borderWidth: 0, hoverOffset: 8 }]);
      make('cTravel', 'doughnut', ['Cleared to travel', 'Not cleared'], p => [{ data: [d.travel.cleared, d.travel.blocked], backgroundColor: [p.mint, p.coral], borderWidth: 0, hoverOffset: 8 }]);
    },
    parent(d) {
      make('cMembers', 'bar', d.members.labels, p => [
        { label: 'Vaccinated', data: d.members.done, backgroundColor: p.mint, borderRadius: 7, maxBarThickness: 54 },
        { label: 'Booked', data: d.members.booked, backgroundColor: p.sky, borderRadius: 7, maxBarThickness: 54 },
        { label: 'Pending', data: d.members.pending, backgroundColor: p.sun, borderRadius: 7, maxBarThickness: 54 }],
        { indexAxis: parentAxis, interaction: { mode: 'nearest', axis: parentAxis, intersect: false },
          plugins: {
            legend: { display: true, position: 'bottom', labels: { color: pal().ink, usePointStyle: true, pointStyle: 'circle', boxWidth: 9, padding: 20 } },
            tooltip: { callbacks: { label: ctx => `${ctx.dataset.label}: ${ctx.parsed[ctx.chart.options.indexAxis === 'y' ? 'x' : 'y']} dose(s)` } }
          },
          scales: parentScales(parentAxis), layout: { padding: { top: 8, right: 12, bottom: 4, left: 8 } } });
    },
    hospital(d) {
      make('cStock', 'bar', d.stock.labels, p => [{ label: 'Doses in stock', data: d.stock.values, backgroundColor: d.stock.values.map(v => v < 10 ? p.coral : p.teal), borderRadius: 6 }],
        { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: axes(pal()), y: { ticks: { color: pal().ink }, grid: { display: false }, border: { display: false } } } });
      make('cWeek', 'line', d.week.labels, p => [{ label: 'Appointments', data: d.week.values, borderColor: p.sky, borderWidth: 3, tension: .4, fill: true, pointRadius: 3, pointBackgroundColor: p.sky,
        backgroundColor: ctx => grad(ctx.chart.ctx, p.sky) }], { plugins: { legend: { display: false } } });
    }
  };
  const update = {
    admin(d) { set('cStatus', null, [d.status.vaccinated, d.status.booked, d.status.pending]); updateDoseStatus(d.status); set('cDaily', d.daily.labels, d.daily.values); set('cMonthly', d.monthly.labels, d.monthly.values);
      set('cAudience', null, [d.audience.child, d.audience.adult]); set('cTravel', null, [d.travel.cleared, d.travel.blocked]); },
    parent(d) { set('cMembers', d.members.labels, d.members.done, d.members.booked, d.members.pending); },
    hospital(d) { set('cStock', d.stock.labels, d.stock.values); const c = charts.cStock; if (c) c.data.datasets[0].backgroundColor = d.stock.values.map(v => v < 10 ? pal().coral : pal().teal);
      set('cWeek', d.week.labels, d.week.values); }
  };

  function paintKpis(k) { document.querySelectorAll('[data-kpi]').forEach(el => { const v = k[el.dataset.kpi]; if (v !== undefined) window.countTo?.(el, +v); }); }
  function pill(ok) {
    const p = $('livePill'); if (!p) return;
    p.classList.toggle('off', !ok); p.querySelector('span').textContent = ok ? 'Live' : 'Offline';
    $('liveTime').textContent = ok ? 'updated ' + new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }) : 'retrying…';
  }
  async function pull(first) {
    try {
      const r = await fetch(api + '?scope=' + scope, { credentials: 'same-origin', cache: 'no-store' });
      if (!r.ok) throw 0; const d = await r.json();
      if (first) build[scope](d); else update[scope](d);
      paintKpis(d.kpi || {}); lastOk = Date.now(); pill(true);
    } catch (e) { pill(false); }
  }
  function themeSync() {
    // rebuild colours when the user flips day/night
    const p = pal();
    Object.values(charts).forEach(c => {
      if (c.options.plugins.legend.labels) c.options.plugins.legend.labels.color = p.ink;
      Object.entries(c.options.scales || {}).forEach(([k, s]) => { if (s.ticks) s.ticks.color = (c.options.indexAxis === 'y' && k === 'y') ? p.ink : p.muted; if (s.grid && s.grid.display !== false) s.grid.color = p.grid; });
      c.update('none');
    });
    pull(false);
  }
  function bindParentControls() {
    $('chartOrientation')?.addEventListener('click', () => setParentAxis(parentAxis === 'y' ? 'x' : 'y'));
    $('chartReset')?.addEventListener('click', () => {
      setParentAxis('y');
      const c = charts.cMembers;
      if (c) {
        c.data.datasets.forEach((_, i) => c.setDatasetVisibility(i, true));
        c.reset();
        c.update();
      }
    });
    $('chartDownload')?.addEventListener('click', () => {
      const c = charts.cMembers;
      if (!c) return;
      const link = document.createElement('a');
      link.href = c.toBase64Image('image/png', 1);
      link.download = 'vaxcare-vaccination-progress.png';
      link.click();
    });
  }
  function updateDoseStatus(status) {
    const values = [status.vaccinated, status.booked, status.pending].map(value => Number(value) || 0);
    const total = values.reduce((sum, value) => sum + value, 0);
    document.querySelectorAll('[data-status-count]').forEach(el => {
      el.textContent = values[Number(el.dataset.statusCount)].toLocaleString();
    });
    document.querySelectorAll('[data-status-percent]').forEach(el => {
      const value = values[Number(el.dataset.statusPercent)];
      el.textContent = `${total ? Math.round(value / total * 100) : 0}% of doses`;
    });
    document.querySelectorAll('[data-status-toggle]').forEach(button => {
      const visible = charts.cStatus?.getDataVisibility(Number(button.dataset.statusToggle)) ?? true;
      button.setAttribute('aria-pressed', String(visible));
    });
  }
  function bindAdminControls() {
    document.querySelectorAll('[data-status-toggle]').forEach(button => {
      button.addEventListener('click', () => {
        const chart = charts.cStatus;
        if (!chart) return;
        chart.toggleDataVisibility(Number(button.dataset.statusToggle));
        chart.update();
        updateDoseStatus({
          vaccinated: chart.data.datasets[0].data[0],
          booked: chart.data.datasets[0].data[1],
          pending: chart.data.datasets[0].data[2]
        });
      });
    });
  }
  return {
    start(s, url) {
      scope = s; api = url; Chart.defaults.font.family = 'Figtree, system-ui, sans-serif';
      if (scope === 'parent') bindParentControls();
      if (scope === 'admin') bindAdminControls();
      pull(true); timer = setInterval(() => { if (!document.hidden) pull(false); }, 8000);
      document.addEventListener('visibilitychange', () => { if (!document.hidden && Date.now() - lastOk > 8000) pull(false); });
      document.addEventListener('themechange', () => { Object.values(charts).forEach(c => c.destroy()); for (const k in charts) delete charts[k]; pull(true); });
    }
  };
})();
