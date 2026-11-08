import './bootstrap';
import { bindAutoSubmit, initDatepickers, initSelect2 } from './form-plugins';
import { initIconPickers } from './icon-picker';

import.meta.glob(['../assets/img/**', '../assets/vendor/fonts/**']);

/**
 * Sunucudan gelen oturum mesajlarını (kaydedildi, silindi, hata...)
 * sağ üstte kısa süreli bildirim olarak gösterir.
 */
let notyf = null;

/**
 * Sağ üstte kısa süreli bildirim gösterir. Sayfa script'leri de kullanır: window.notify('success', 'Kaydedildi')
 */
function notify(type, message) {
  if (!window.Notyf || !message) return;

  notyf ??= new window.Notyf({
    duration: 4000,
    dismissible: true,
    ripple: false,
    position: { x: 'right', y: 'top' }
  });

  if (type === 'error') {
    notyf.error({ message, duration: 6000 });
  } else {
    notyf.success(message);
  }
}

window.notify = notify;

function showFlashMessages() {
  const messages = window.flashMessages || {};

  notify('success', messages.success);
  notify('error', messages.error);
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
 * data-prompt-reason özniteliği taşıyan formlar gönderilmeden önce neden sorar (ör. kalem iptali).
 * Girilen metin formdaki name="reason" alanına yazılır.
 * Örnek: <form data-prompt-reason="Kalem iptal edilsin mi?" data-prompt-placeholder="Örn. Yanlış girildi">
 */
function bindReasonPrompts() {
  document.addEventListener('submit', event => {
    const form = event.target;

    if (!form.matches('form[data-prompt-reason]') || form.dataset.confirmed === 'true') return;

    event.preventDefault();

    const reasonInput = form.querySelector('[name=reason]');

    if (!window.Swal) {
      const reason = window.prompt(form.dataset.promptReason);
      if (reason) {
        reasonInput.value = reason;
        form.dataset.confirmed = 'true';
        form.submit();
      }
      return;
    }

    window.Swal.fire({
      title: form.dataset.promptReason,
      text: form.dataset.promptText || 'İptal nedeni işlem geçmişine kaydedilir.',
      icon: 'warning',
      input: 'text',
      inputPlaceholder: form.dataset.promptPlaceholder || 'İptal nedeni',
      inputAttributes: { maxlength: 255, 'aria-label': 'İptal nedeni' },
      inputValidator: value => (!value || !value.trim() ? 'Lütfen bir neden yazın.' : undefined),
      showCancelButton: true,
      confirmButtonText: form.dataset.promptButton || 'İptal et',
      cancelButtonText: 'Vazgeç',
      reverseButtons: true,
      customClass: {
        confirmButton: 'btn btn-danger ms-2',
        cancelButton: 'btn btn-label-secondary'
      }
    }).then(result => {
      if (result.isConfirmed) {
        reasonInput.value = result.value.trim();
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
bindReasonPrompts();
initTooltips();
initSelect2();
initDatepickers();
bindAutoSubmit();
initIconPickers();
