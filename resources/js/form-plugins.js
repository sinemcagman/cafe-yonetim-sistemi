/**
 * Uygulama geneli form bileşenleri (Vuexy Select2 + flatpickr), Türkçe ayarlarla.
 *
 * Kurallar (docs/arayuz-kurallari.md):
 *  - Her <select> "select2" sınıfıyla Select2 olarak kullanılır.
 *      data-placeholder="..."    yer tutucu metin
 *      data-allow-clear="true"   seçimi temizleme butonu
 *      data-search="false"       az seçenekli listelerde arama kutusunu gizler
 *      data-tags="true"          listede olmayan değeri yazıp eklemeye izin verir
 *  - Her tarih alanı data-datepicker özniteliğiyle flatpickr olarak kullanılır.
 *      data-datepicker="single|range"  tek tarih ya da tarih aralığı
 *  - data-auto-submit taşıyan alanlar değiştiğinde bağlı oldukları formu gönderir.
 */
import { Turkish } from 'flatpickr/dist/l10n/tr.js';

const select2Turkish = {
  errorLoading: () => 'Sonuç yüklenemedi',
  inputTooLong: args => `${args.input.length - args.maximum} karakter daha silmelisiniz`,
  inputTooShort: args => `En az ${args.minimum - args.input.length} karakter daha girmelisiniz`,
  loadingMore: () => 'Daha fazla…',
  maximumSelected: args => `Sadece ${args.maximum} seçim yapabilirsiniz`,
  noResults: () => 'Sonuç bulunamadı',
  searching: () => 'Aranıyor…',
  removeAllItems: () => 'Tüm öğeleri kaldır',
  removeItem: () => 'Bu öğeyi kaldır',
  search: () => 'Ara'
};

export function initSelect2(scope = document) {
  const $ = window.jQuery;
  if (!$ || !$.fn.select2) return;

  $(scope)
    .find('select.select2')
    .each(function () {
      const $select = $(this);
      if ($select.hasClass('select2-hidden-accessible')) return;

      if (!$select.parent().hasClass('position-relative')) {
        $select.wrap('<div class="position-relative"></div>');
      }

      $select.select2({
        language: select2Turkish,
        width: '100%',
        placeholder: $select.data('placeholder') || 'Seçiniz',
        allowClear: $select.data('allow-clear') === true,
        tags: $select.data('tags') === true,
        minimumResultsForSearch: $select.data('search') === false ? Infinity : 0,
        dropdownParent: $select.parent()
      });
    });
}

export function initDatepickers(scope = document) {
  if (!window.flatpickr) return;

  window.flatpickr.localize(Turkish);

  scope.querySelectorAll('[data-datepicker]').forEach(input => {
    if (input._flatpickr) return;

    window.flatpickr(input, {
      mode: input.dataset.datepicker === 'range' ? 'range' : 'single',
      dateFormat: 'Y-m-d',
      altInput: true,
      altFormat: 'd.m.Y',
      allowInput: false,
      maxDate: input.dataset.maxDate || null,
      locale: { ...Turkish, rangeSeparator: ' - ' },
      onClose: (selected, value, instance) => {
        // Aralık seçiminde iki tarih de seçildiğinde (veya temizlendiğinde) otomatik gönder
        if (input.hasAttribute('data-auto-submit') && (selected.length !== 1 || instance.config.mode !== 'range')) {
          input.form?.requestSubmit();
        }
      }
    });
  });
}

export function bindAutoSubmit() {
  const $ = window.jQuery;

  // Select2, değişikliği jQuery "change" olayıyla bildirir
  if ($) {
    $(document).on('change', 'select[data-auto-submit]', function () {
      this.form?.requestSubmit();
    });
  }

  document.addEventListener('change', event => {
    const field = event.target;
    if (field.matches('input[type=checkbox][data-auto-submit]')) {
      field.form?.requestSubmit();
    }
  });
}
