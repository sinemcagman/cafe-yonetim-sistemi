# Demlio — Kafe Yönetim Sistemi

Demlio, PHP Laravel ve MySQL tabanlı bir kafe yönetim sistemidir. Masa ve paket siparişleri, menü, ürün adedi, ödeme ve kasa işlemleri aynı akış içinde yönetilir.

## Kafe İş Akışı

### Menü ve masalar hazırlanır

Yönetici, ürünleri sisteme eklerken her ürünün adını, kategorisini, satış fiyatını ve eldeki adedini girer. Örneğin **İçecekler** kategorisine çay ve kahve, **Yiyecekler** kategorisine tost ekler. Satışı durdurulan bir ürün menüde seçilemez. Kafedeki masalar da numaralarıyla tanımlanır; çalışan hangi masanın boş, hangisinin kullanımda olduğunu görür.

### Müşteri için sipariş açılır

Örneğin iki müşteri **Masa 4**'e oturur. Garson kendi hesabıyla sisteme girer, masayı seçer ve yeni bir masa siparişi açar. Siparişe masa numarası ve siparişi açan çalışan bağlanır; açılış zamanı sistem tarafından kaydedilir. Böylece aynı masadaki ürünler ve hesap bir arada tutulur.

Müşteri **iki çay ve bir tost** istediğinde garson menüden bu ürünleri seçip adetlerini girer. Ürünlerin güncel fiyatları siparişe aktarılır ve siparişin toplamı hesaplanır. Müşterinin isteği varsa, örneğin “tost soğansız olsun”, bunu sipariş notuna yazar. Garson kaydetmeden önce ürünleri, adetleri, notu ve toplam tutarı kontrol eder.

### Sipariş hazırlanır ve gerektiğinde değiştirilir

Kaydedilen sipariş hazırlık için görünür olur. Çalışanlar siparişin yeni, hazırlanıyor veya hazır olduğunu takip eder. Müşteri sonradan bir çay daha isterse garson mevcut siparişi açıp yeni ürünü ekler; ayrı bir hesap oluşturmaz. Yanlış girilen ürün iptal edildiğinde sipariş toplamı yeniden hesaplanır ve iptal bilgisi işlem geçmişinde kalır.

### Ürün adedi takip edilir

Sistem stok miktarını ürün adedi üzerinden izler. Örnekte siparişe iki çay ve bir tost eklendiğinde ilgili ürünlerin mevcut adedi azalır. Stokta yeterli adet yoksa çalışan uyarılır ve eksik ürün siparişe eklenmez. Bir sipariş kalemi iptal edilirse düşülen adet geri eklenir. Yönetici yeni ürün geldiğinde ürünün adedini artırır ve bu değişikliğin nedenini stok hareketine kaydeder.

### Hesap ödenir

Müşteri hesabı istediğinde kasiyer **Masa 4**'ün açık siparişini seçer. Ekranda sipariş kalemleri, her kalemin tutarı ve ödenecek toplam görünür. Kasiyer tahsil edilen tutarı ve ödeme yöntemini girer; ödeme bilgisi siparişle ilişkilendirilir. Tutarın tamamı ödendiğinde sipariş kapatılır, masa yeniden boş duruma geçer ve kasa hareketi kaydedilir.

### Paket veya gel-al siparişinde

Garson masa seçmek yerine sipariş türünü **paket** veya **gel-al** olarak işaretler. Ürün, adet, not ve ödeme bilgileri aynı şekilde kaydedilir; sipariş masa ile ilişkilendirilmez. Çalışan sipariş durumunu hazırlık ve teslim aşamalarında takip eder.

### Kasa kapanır

Kasiyer mesai başında kasa oturumu açıp başlangıçtaki nakit tutarını girer. Gün içindeki tahsilatlar ve diğer kasa giriş/çıkışları bu oturumda tutulur. Mesai sonunda kasadaki nakit sayılır, kapanış tutarı girilir ve kayıtlı hareketlerle karşılaştırılır. Yetkili çalışan geçmişteki bir siparişi, ödemeyi veya stok değişikliğini gerektiğinde inceleyebilir.

## Çalışanların görevleri

- **Garson:** Masa veya paket siparişi açar, ürünleri ekler ve sipariş durumunu takip eder.
- **Kasiyer:** Ödemeleri kaydeder ve kasa oturumunu yönetir.
- **Yönetici:** Menü, ürün adetleri, masalar, çalışan yetkileri ve işlem geçmişini yönetir.

## Teknik altyapı

- **Backend:** PHP 8.4 ve Laravel 13
- **Veritabanı:** MySQL 8 (geliştirme, test ve canlı ortamda yalnızca MySQL kullanılır)
- **Bağımlılık yönetimi:** Composer ve Yarn
- **Arayüz:** Vuexy Bootstrap 5 admin teması (Laravel sürümü, Vite ile derlenir)

## Kurulum

Gereksinimler: PHP 8.4+, Composer, MySQL 8, Node.js 20+ ve Yarn.

```bash
git clone https://github.com/sinemcagman/cafe-yonetim-sistemi.git
cd cafe-yonetim-sistemi
composer install
yarn install
cp .env.example .env
php artisan key:generate
```

MySQL'de `kafe_yonetim` adında bir veritabanı oluşturun (`utf8mb4_unicode_ci`) ve `.env` içindeki `DB_*` bilgilerini düzenleyin. Ardından arayüz dosyalarını derleyin, tabloları ve örnek verileri yükleyin:

```bash
yarn build
php artisan migrate --seed
php artisan serve
```

Arayüz üzerinde çalışırken `yarn build` yerine `yarn dev` kullanılabilir.

Uygulama `http://127.0.0.1:8000` adresinde açılır. Örnek hesapların şifresi `password`'dür:

| Rol | Ad | E-posta |
| --- | --- | --- |
| Yönetici | Selin Aydın | yonetici@kafe.test |
| Garson | Ayşe Demir | garson@kafe.test |
| Kasiyer | Mehmet Kaya | kasiyer@kafe.test |

### Testler

Testler MySQL üzerinde, ayrı bir test veritabanında çalışır. Önce `kafe_yonetim_test` veritabanını oluşturun, sonra:

```bash
php artisan test
```

## Veritabanı nesneleri

Tablolara ek olarak migration'larla şu MySQL nesneleri oluşturulur:

| Tür | Ad | Görevi |
| --- | --- | --- |
| Function | `fn_stok_durumu(adet, kritik_esik)` | Ürünün stok durumunu `tukendi`, `kritik` veya `yeterli` olarak döner |
| View | `v_urun_stok_durumu` | Ürün, kategori, adet, stok değeri ve stok durumunu tek tabloda listeler |
| Trigger | `trg_order_items_after_insert / update / delete` | Sipariş kalemi eklenince, iptal edilince ya da silinince sipariş toplamını yeniden hesaplar |
| Stored procedure | `sp_gunluk_kasa_raporu(tarih)` | Verilen günün tahsilatlarını ödeme yöntemine göre özetler |

```sql
SELECT * FROM v_urun_stok_durumu WHERE stok_durumu <> 'yeterli';
CALL sp_gunluk_kasa_raporu('2026-10-28');
```

## Arayüz kuralları

Tüm ekranlar Vuexy bileşenleriyle hazırlanır: seçim kutuları Select2, tarih alanları flatpickr, listeler sayfalı. Ayrıntılar için [docs/arayuz-kurallari.md](docs/arayuz-kurallari.md).

## Geliştirme durumu

**Tamamlananlar**

- Veritabanı şeması: kategori, ürün, masa, sipariş, sipariş kalemi, stok hareketi, kasa oturumu, ödeme, kasa hareketi ve işlem geçmişi tabloları
- Stok durumu fonksiyonu ve görünümü, sipariş toplamı trigger'ları, günlük kasa raporu prosedürü
- Çalışan girişi, pasif hesap kontrolü ve rol bazlı yetkilendirme (Yönetici / Garson / Kasiyer)
- Vuexy arayüzü: kafe renk paleti, açık/koyu tema, role göre menü, kritik stok bildirimleri
- Menü yönetimi: kategoriler (tüm Tabler ikonlarından aranabilir ikon seçimi) ve ürünler (fiyat, adet, satış durumu)
- Stok girişi ve düzeltmesi; her değişiklik nedeniyle birlikte stok hareketine kaydedilir, tarih aralığıyla filtrelenebilir
- Masa tanımlama ve çalışanlar için boş/dolu masa ekranı
- Panel: masa durumu, açık sipariş, günlük tahsilat, kritik stok ve son işlemler

**Sıradakiler**

- Masa, paket ve gel-al siparişi açma; ürün ekleme ve kalem iptali
- Sipariş hazırlık durumlarının takibi
- Ödeme alma ve hesabı kapatma
- Kasa oturumu açma/kapama ve kasa hareketleri
- Çalışan yönetimi ve işlem geçmişi ekranı
