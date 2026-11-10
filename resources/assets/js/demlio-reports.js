/**
 * Raporlar ekranı grafikleri (ApexCharts).
 * Hepsi tek serili: renk, tema değişkeni --demlio-chart'tan gelir (açık/koyu için ayrı doğrulanmış tonlar).
 * Tema değişince grafikler yeni renklerle yeniden çizilir. Her grafiğin tablo görünümü vardır.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const data = window.reportData;
  if (!data || !window.ApexCharts) return;

  const money = value =>
    new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) + ' ₺';
  const moneyShort = value =>
    new Intl.NumberFormat('tr-TR', { notation: 'compact', maximumFractionDigits: 1 }).format(value) + ' ₺';
  const css = name => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  // İpucu başlığı HTML olarak basılır; ürün ve kategori adları düz metin kalsın
  const escapeHtml = value =>
    String(value).replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);

  /** Tek renkli, ince işaretli, ızgarası silik ortak ayarlar */
  const base = () => {
    const ink = css('--bs-secondary-color') || '#6d6b77';
    return {
      chart: {
        type: 'bar',
        fontFamily: css('--bs-font-sans-serif') || 'inherit',
        toolbar: { show: false },
        zoom: { enabled: false },
        animations: { speed: 300 },
        foreColor: ink
      },
      colors: [css('--demlio-chart') || '#b2622a'],
      grid: { borderColor: css('--bs-border-color') || '#e6e6e8', strokeDashArray: 0, padding: { left: 4, right: 8 } },
      dataLabels: { enabled: false },
      legend: { show: false },
      states: { hover: { filter: { type: 'darken', value: 0.88 } } },
      tooltip: {
        theme: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light',
        x: { formatter: escapeHtml }
      },
      xaxis: { axisBorder: { show: false }, axisTicks: { show: false }, labels: { style: { colors: ink } } },
      yaxis: { labels: { style: { colors: ink } } }
    };
  };

  /** Yalnızca en yüksek değerin etiketini göster (her çubuğa sayı yazma) */
  const peakLabel = (series, format) => ({
    enabled: true,
    formatter: (value, { dataPointIndex }) => {
      const max = Math.max(...series.map(point => point.y));
      return series[dataPointIndex]?.y === max && max > 0 ? format(value) : '';
    },
    offsetY: -18,
    style: { fontSize: '12px', fontWeight: 500, colors: [css('--bs-heading-color') || '#444050'] }
  });

  const definitions = {
    revenue: () => {
      const options = base();
      return {
        ...options,
        chart: { ...options.chart, height: 300 },
        series: [{ name: 'Ciro', data: data.revenue }],
        plotOptions: { bar: { columnWidth: data.revenue.length > 20 ? '70%' : '24px', borderRadius: 4, borderRadiusApplication: 'end', dataLabels: { position: 'top' } } },
        dataLabels: peakLabel(data.revenue, moneyShort),
        yaxis: { ...options.yaxis, labels: { ...options.yaxis.labels, formatter: moneyShort } },
        xaxis: { ...options.xaxis, labels: { ...options.xaxis.labels, rotate: -45, hideOverlappingLabels: true } },
        tooltip: { ...options.tooltip, y: { formatter: money } }
      };
    },
    products: () => {
      const options = base();
      return {
        ...options,
        chart: { ...options.chart, height: Math.max(220, data.products.length * 36 + 40) },
        series: [{ name: 'Satış adedi', data: data.products }],
        plotOptions: { bar: { horizontal: true, barHeight: '20px', borderRadius: 4, borderRadiusApplication: 'end' } },
        xaxis: { ...options.xaxis, labels: { ...options.xaxis.labels, formatter: v => Math.round(v) } },
        tooltip: {
          ...options.tooltip,
          y: {
            formatter: (value, { dataPointIndex }) => `${value} adet · ${money(data.products[dataPointIndex].revenue)}`
          }
        }
      };
    },
    categories: () => {
      const options = base();
      return {
        ...options,
        chart: { ...options.chart, height: Math.max(220, data.categories.length * 44 + 40) },
        series: [{ name: 'Satış tutarı', data: data.categories }],
        plotOptions: { bar: { horizontal: true, barHeight: '20px', borderRadius: 4, borderRadiusApplication: 'end' } },
        xaxis: { ...options.xaxis, labels: { ...options.xaxis.labels, formatter: moneyShort } },
        tooltip: {
          ...options.tooltip,
          y: { formatter: (value, { dataPointIndex }) => `${money(value)} · ${data.categories[dataPointIndex].quantity} adet` }
        }
      };
    },
    hourly: () => {
      const options = base();
      return {
        ...options,
        chart: { ...options.chart, height: 260 },
        series: [{ name: 'Sipariş', data: data.hourly }],
        plotOptions: { bar: { columnWidth: '24px', borderRadius: 4, borderRadiusApplication: 'end', dataLabels: { position: 'top' } } },
        dataLabels: peakLabel(data.hourly, v => `${v} sipariş`),
        yaxis: { ...options.yaxis, forceNiceScale: true, labels: { ...options.yaxis.labels, formatter: v => Math.round(v) } },
        tooltip: { ...options.tooltip, y: { formatter: v => `${v} sipariş` } }
      };
    }
  };

  const charts = {};

  const render = () => {
    Object.entries(definitions).forEach(([id, build]) => {
      const element = document.getElementById(`chart-${id}`);
      if (!element) return;
      charts[id]?.destroy();
      charts[id] = new window.ApexCharts(element, build());
      charts[id].render();
    });
  };

  render();

  // Açık/koyu tema değişince renkler yeni temadan okunarak yeniden çizilir
  new MutationObserver(render).observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });

  // Grafik / tablo görünümü geçişi
  document.querySelectorAll('[data-view-toggle]').forEach(input => {
    input.addEventListener('change', () => {
      const id = input.dataset.viewToggle;
      const table = input.value === 'table';
      document.querySelector(`[data-chart-panel="${id}"]`).classList.toggle('d-none', table);
      document.querySelector(`[data-table-panel="${id}"]`).classList.toggle('d-none', !table);
    });
  });
});
