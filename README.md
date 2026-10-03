# Kafe Yönetim Sistemi

PHP Laravel ve MySQL tabanlı kafe yönetim sistemi. Masa ve paket siparişleri, menü, ürün adedi, ödeme ve kasa işlemleri aynı akış içinde yönetilir.

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

- **Backend:** PHP ve Laravel
- **Veritabanı:** MySQL
- **Bağımlılık yönetimi:** Composer
