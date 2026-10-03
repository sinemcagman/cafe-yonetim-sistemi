# Kafe Yönetim Sistemi

PHP Laravel ve MySQL tabanlı kafe yönetim sistemi. Günlük iş akışında masaları, paket siparişlerini, menüyü, ürün stoklarını, ödemeleri ve kasayı tek yerden yönetmek için tasarlanmıştır.

## Bir siparişin yolculuğu

### Müşteri geldiğinde

Garson sisteme kendi hesabıyla girer. Müşteri içeride oturacaksa uygun masayı seçer; örneğin **Masa 4**. Sipariş paket veya gel-al ise masa seçmeden sipariş türünü belirtir. Böylece her siparişin nerede servis edileceği baştan bellidir.

### Sipariş alınırken

Garson menüden ürünleri seçer. Örneğin müşteri iki kahve ve bir sandviç istediğinde siparişe bu ürünleri adetleriyle ekler. Sistem her kalemde ürünün adını, adedini ve sipariş anındaki fiyatını saklar. Müşterinin özel isteği varsa sipariş notuna yazılır.

Garson siparişi kaydettikten sonra ekranda hangi masaya veya paket müşterisine ait olduğu, siparişi kimin aldığı, ürünler ve toplam tutar görünür. Sipariş hazırlık sürecinde durumuna göre takip edilir.

### Stok güncellenirken

Siparişteki ürün adetleri stoktan düşülür: örnekte iki kahve ve bir sandviç. Bir ürün iptal edilirse ilgili adet stoğa geri eklenir. Stok hareketi kaydedildiği için ürün adedinin neden değiştiği sonradan görülebilir.

### Ödeme alınırken

Müşteri hesabı istediğinde kasiyer siparişi açar ve ödenecek tutarı görür. Ödeme yöntemi ile tahsil edilen tutarı kaydeder. Ödeme tamamlandığında sipariş kapanır; kasadaki para hareketi de ilgili kasa oturumuna işlenir.

### Gün sonunda

Yetkili çalışan kasa oturumunu kapatır. Açılış tutarı, gün içindeki tahsilatlar ve diğer kasa hareketleri üzerinden kapanış tutarı kontrol edilir. Sipariş ve ödeme geçmişi, gerektiğinde ilgili işlemi bulmayı sağlar.

## Çalışanların görevleri

- **Garson:** Masa veya paket siparişi açar, ürünleri ekler ve sipariş durumunu takip eder.
- **Kasiyer:** Ödemeyi alır ve kasa işlemlerini yürütür.
- **Yönetici:** Menü, ürün stoku, çalışan yetkileri ve işlem geçmişini yönetir.

## Teknik altyapı

- **Backend:** PHP ve Laravel
- **Veritabanı:** MySQL
- **Bağımlılık yönetimi:** Composer
