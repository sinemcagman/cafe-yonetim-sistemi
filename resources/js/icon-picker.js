/**
 * İkon seçici (resources/views/components/icon-picker.blade.php)
 * Tüm Tabler ikonlarını JSON'dan yükler; İngilizce ikon adıyla ya da Türkçe kelimeyle arama yapılabilir.
 * Dışarıdan değer atamak için: element.dispatchEvent(new CustomEvent('icon-picker:set', { detail: 'tabler-coffee' }))
 */

const PAGE_SIZE = 180;

// Türkçe arama kelimelerinin karşılık geldiği ikon adı parçaları
const TURKISH_KEYWORDS = {
  kahve: ['coffee', 'cup', 'mug'],
  çay: ['cup', 'mug', 'leaf', 'tea'],
  bardak: ['cup', 'glass', 'mug'],
  fincan: ['cup', 'coffee', 'mug'],
  içecek: ['coffee', 'cup', 'glass', 'bottle', 'beer', 'mug'],
  soğuk: ['glass', 'ice', 'snowflake'],
  sıcak: ['coffee', 'flame', 'mug'],
  tatlı: ['cake', 'ice-cream', 'cookie', 'candy', 'cupcake'],
  pasta: ['cake', 'cupcake'],
  dondurma: ['ice-cream'],
  kurabiye: ['cookie'],
  ekmek: ['bread', 'baguette'],
  tost: ['bread', 'baguette'],
  sandviç: ['bread', 'baguette'],
  yemek: ['tools-kitchen', 'bowl', 'salad', 'soup', 'pizza', 'burger', 'chef'],
  mutfak: ['tools-kitchen', 'chef', 'cooker'],
  kahvaltı: ['egg', 'bread', 'coffee', 'cheese'],
  yumurta: ['egg'],
  peynir: ['cheese'],
  salata: ['salad', 'leaf'],
  çorba: ['soup', 'bowl'],
  pizza: ['pizza'],
  hamburger: ['burger'],
  burger: ['burger'],
  et: ['meat'],
  balık: ['fish'],
  meyve: ['apple', 'lemon', 'cherry', 'pear', 'carrot', 'melon'],
  limon: ['lemon'],
  elma: ['apple'],
  su: ['droplet', 'bottle'],
  şişe: ['bottle'],
  bira: ['beer'],
  süt: ['milk'],
  vegan: ['leaf', 'plant', 'salad'],
  bitki: ['leaf', 'plant'],
  ateş: ['flame'],
  yıldız: ['star'],
  kalp: ['heart'],
  hediye: ['gift'],
  paket: ['package', 'box', 'paper-bag'],
  masa: ['armchair', 'table']
};

const normalize = text => text.toLocaleLowerCase('tr').trim();

function matchesQuery(name, query) {
  if (!query) return true;
  if (name.includes(query)) return true;

  return Object.entries(TURKISH_KEYWORDS).some(
    ([keyword, parts]) => keyword.startsWith(query) && parts.some(part => name.includes(part))
  );
}

let iconsPromise = null;
const loadIcons = url => (iconsPromise ??= fetch(url).then(response => response.json()));

function initIconPicker(root) {
  const input = root.querySelector('[data-icon-picker-input]');
  const preview = root.querySelector('[data-icon-picker-preview]');
  const nameEl = root.querySelector('[data-icon-picker-name]');
  const search = root.querySelector('[data-icon-picker-search]');
  const grid = root.querySelector('[data-icon-picker-grid]');
  const more = root.querySelector('[data-icon-picker-more]');
  const count = root.querySelector('[data-icon-picker-count]');
  const suggested = root.querySelector('[data-icon-picker-suggested]');
  const suggestedTitle = root.querySelector('[data-icon-picker-suggested-title]');

  let icons = [];
  let filtered = [];
  let shown = 0;

  const markSelected = () => {
    root.querySelectorAll('.icon-picker-item').forEach(button => {
      const selected = button.dataset.icon === input.value;
      button.classList.toggle('btn-primary', selected);
      button.classList.toggle('btn-outline-secondary', !selected);
      button.setAttribute('aria-pressed', selected ? 'true' : 'false');
    });
  };

  const setValue = icon => {
    preview.className = `icon-base ti ${icon} icon-28px`;
    input.value = icon;
    nameEl.textContent = icon;
    markSelected();
  };

  const renderMore = () => {
    const fragment = document.createDocumentFragment();
    filtered.slice(shown, shown + PAGE_SIZE).forEach(name => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'btn btn-icon btn-outline-secondary icon-picker-item';
      button.dataset.icon = `tabler-${name}`;
      button.title = name;
      button.setAttribute('aria-label', name);
      button.innerHTML = `<i class="icon-base ti tabler-${name} icon-22px"></i>`;
      fragment.appendChild(button);
    });
    grid.appendChild(fragment);
    shown = Math.min(shown + PAGE_SIZE, filtered.length);
    more.classList.toggle('d-none', shown >= filtered.length);
    markSelected();
  };

  const applyFilter = () => {
    const query = normalize(search.value);
    filtered = icons.filter(name => matchesQuery(name, query));
    shown = 0;
    grid.innerHTML = filtered.length ? '' : '<div class="text-body-secondary small px-1">Eşleşen ikon yok.</div>';
    count.textContent = `(${filtered.length})`;

    if (suggested) {
      let visible = 0;
      suggested.querySelectorAll('.icon-picker-item').forEach(button => {
        const show = matchesQuery(button.dataset.icon.replace('tabler-', ''), query) || normalize(button.title).includes(query);
        button.classList.toggle('d-none', !show);
        if (show) visible++;
      });
      suggested.classList.toggle('d-none', visible === 0);
      suggestedTitle?.classList.toggle('d-none', visible === 0);
    }

    renderMore();
  };

  root.addEventListener('click', event => {
    const button = event.target.closest('.icon-picker-item');
    if (button) setValue(button.dataset.icon);
  });

  let timer;
  search.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(applyFilter, 150);
  });
  // Arama kutusunda Enter formu göndermesin
  search.addEventListener('keydown', event => {
    if (event.key === 'Enter') event.preventDefault();
  });
  more.addEventListener('click', renderMore);
  root.addEventListener('icon-picker:set', event => setValue(event.detail));

  markSelected();
  loadIcons(root.dataset.source)
    .then(list => {
      icons = list;
      applyFilter();
    })
    .catch(() => {
      grid.innerHTML = '<div class="text-danger small px-1">İkon listesi yüklenemedi.</div>';
    });
}

export function initIconPickers() {
  document.querySelectorAll('[data-icon-picker]').forEach(initIconPicker);
}
