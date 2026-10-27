# Arayüz Kuralları

Demlio'nun tüm ekranları **Vuexy (Bootstrap 5, Laravel sürümü)** ile yapılır. Yeni bir sayfa eklerken veya mevcut bir sayfayı değiştirirken aşağıdaki kurallara uyulur.

## Sayfa iskeleti

- Her sayfa `@extends('layouts/layoutMaster')` ile başlar; giriş gibi menüsüz sayfalar `layouts/blankLayout` kullanır.
- Sayfa başlığı `<x-page-header>` bileşeniyle verilir: başlık, kısa açıklama, ikon, breadcrumb ve sağda işlem butonları.
- Liste boşsa `<x-empty-state>` ile kullanıcı bir sonraki adıma yönlendirilir (ör. "İlk ürünü ekle").
- Yan menü öğeleri `resources/menu/verticalMenu.json` dosyasına eklenir; yalnızca belirli rollerin görmesi gereken öğelere `"roles": ["yonetici"]` yazılır.

## Form bileşenleri

| Alan türü | Kullanılacak bileşen | Nasıl |
| --- | --- | --- |
| Seçim kutusu (`<select>`) | **Select2** (Vuexy) | `class="select2 form-select"` |
| Tarih / tarih aralığı | **flatpickr** (Vuexy) | `data-datepicker="single"` veya `data-datepicker="range"` |
| İkon seçimi | Tabler ikon seçici | `<x-icon-picker name="icon" />` |
| Açma/kapama | Bootstrap switch | `form-check form-switch` |
| Silme | SweetAlert2 onayı | `<x-delete-button :action="..." />` |

- Düz `<select>` veya `<input type="date">` **kullanılmaz**; her seçim kutusu Select2, her tarih alanı flatpickr olur.
- Select2 ve flatpickr tüm sayfalara layout üzerinden yüklenir ve `resources/js/form-plugins.js` tarafından Türkçe ayarlarla otomatik başlatılır. Sayfada ek JavaScript yazmak gerekmez.
- Select2 seçenekleri:
  - `data-placeholder="..."`: yer tutucu metin (boş bir `<option value=""></option>` ile birlikte)
  - `data-allow-clear="true"`: seçimi temizleme butonu
  - `data-search="false"`: az seçenekli listelerde arama kutusunu gizler
  - `data-tags="true"`: listede olmayan değeri yazıp eklemeye izin verir
- Tarih alanları sunucuya `Y-m-d` biçiminde gider, kullanıcıya `gg.aa.yyyy` gösterilir. Aralıklar `2026-10-01 - 2026-10-28` biçimindedir.
- Filtre formlarında değişince sayfanın yenilenmesi için alana `data-auto-submit` eklenir.
- Doğrulama hataları alanın altında `invalid-feedback` ile, form başında da `<x-form-errors />` ile gösterilir.

## Listeler

- Listeler `paginate($this->perPage($request))` ile sayfalanır (sayfa başına 10 / 25 / 50, `?adet=` parametresi).
- Liste altında `<x-list-footer :paginator="$liste" unit="ürün" />` kullanılır: toplam kayıt, sayfa başına seçimi ve Vuexy sayfalama.
- Ekleme/düzenleme formları kısa ise sayfa değiştirmeden yan panelde (offcanvas) açılır; uzun formlar ayrı sayfadadır.

## Geri bildirim

- İşlem sonucu `->with('success', '...')` veya `->with('error', '...')` ile döndürülür; mesaj sağ üstte bildirim (Notyf) olarak görünür.
- Geri alınamayan işlemler SweetAlert2 ile onaylatılır (`data-confirm` özniteliği).

## Renk ve tema

- Kafe paleti `resources/assets/vendor/scss/_custom-variables/` altındadır (açık tema: kahve `#8b5e3c` ve krem zemin; koyu tema: espresso zemin ve karamel `#c58b5a`). Renkler sayfalarda elle yazılmaz; `primary`, `bg-label-*` gibi tema sınıfları kullanılır.
- İkonlar yalnızca Tabler setinden kullanılır: `<i class="icon-base ti tabler-coffee"></i>`.
- Görseller açık ve koyu tema için ayrı hazırlanır ve `data-app-light-img` / `data-app-dark-img` öznitelikleriyle bağlanır.
