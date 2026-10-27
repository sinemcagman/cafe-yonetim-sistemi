import './bootstrap';
import { bindAutoSubmit, initDatepickers, initSelect2 } from './form-plugins';
import { initIconPickers } from './icon-picker';

import.meta.glob(['../assets/img/**', '../assets/vendor/fonts/**']);

/**
 * Sunucudan gelen oturum mesajlarını (kaydedildi, silindi, hata...)
 * sağ üstte kısa süreli bildirim olarak gösterir.
 */
function showFlashMessages() {
  const messages = window.flashMessages || {};

  if (!window.Notyf || (!messages.success && !messages.error)) return;

  const notyf = new window.Notyf({
    duration: 4000,
    dismissible: true,
    ripple: false,
    position: { x: 'right', y: 'top' }
  });

  if (messages.success) notyf.success(messages.success);
  if (messages.error) notyf.error({ message: messages.error, duration: 6000 });
}

/**
 * data-confirm özniteliği taşıyan formlar gönderilmeden önce onay ister.
 * Örnek: <form data-confirm="Ürün silinsin mi?" data-confirm-text="...">
 */
function bindConfirmForms() {
  document.addEventListener('submit', event => {
    const form = event.target;

    if (!form.matches('form[data-confirm]') || form.dataset.confirmed === 'true') return;

    event.preventDefault();

    if (!window.Swal) {
      if (window.confirm(form.dataset.confirm)) {
        form.dataset.confirmed = 'true';
        form.submit();
      }
      return;
    }

    window.Swal.fire({
      title: form.dataset.confirm,
      text: form.dataset.confirmText || 'Bu işlem geri alınamaz.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: form.dataset.confirmButton || 'Evet, sil',
      cancelButtonText: 'Vazgeç',
      reverseButtons: true,
      customClass: {
        confirmButton: 'btn btn-danger ms-2',
        cancelButton: 'btn btn-label-secondary'
      }
    }).then(result => {
      if (result.isConfirmed) {
        form.dataset.confirmed = 'true';
        form.submit();
      }
    });
  });
}

/**
 * Sayfalardaki tooltip'leri etkinleştirir.
 */
function initTooltips() {
  if (!window.bootstrap) return;

  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(element => {
    new window.bootstrap.Tooltip(element);
  });
}

showFlashMessages();
bindConfirmForms();
initTooltips();
initSelect2();
initDatepickers();
bindAutoSubmit();
initIconPickers();
