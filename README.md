# Innomis - Inovasyon ve Yönetim Bilişim Sistemleri Topluluğu Web Sitesi



## Hakkında

Bu proje, **İnovasyon ve Yönetim Bilişim Sistemleri Topluluğu** için geliştirilmiş bir web sitesidir. Topluluk hakkında bilgilendirici içerikler sunmanın yanı sıra etkinlik takibi, üye kaydı, galeri yönetimi ve iletişim gibi işlevleri barındırmaktadır.

## Kullanılan Teknolojiler

- **Frontend:** HTML, CSS, Bootstrap
- **Backend:** PHP
- **Veritabanı:** MySQL

## Özellikler

### Genel Kullanıcı Özellikleri
- Topluluk hakkında bilgilendirici sayfalar
- Resimler ve açıklamalar içeren galeri
- Yaklaşan ve geçmiş etkinlikleri görüntüleyebilme
- Site üzerinden topluluğa üye olabilme
- Site üzerinden iletişim formu ile mesaj gönderme (Mesajlar otomatik olarak e-posta adresine iletilir.)
- Galeriye fotoğraf ekleme

### Admin Paneli Özellikleri
- **Üyelik Yönetimi:**
  - Toplam üye sayısını görüntüleme
  - Topluluğa kaç kişinin üye olduğunu görüntüleme
  - Topluluk kurul üyelerinin bilgi yönetimi
- **Etkinlik Yönetimi:**
  - Etkinlik bilgilerini görüntüleme
  - Yeni etkinlik ekleme / düzenleme / silme
- **Galeri Yönetimi:**
  - Fotoğraf ekleme / silme
- **Mesaj Yönetimi:**
  - Gelen mesajları okuma ve yanıt verme
- **Yetkili Yönetimi:**
  - Site yetkililerini ekleme / silme / güncelleme
  - Toplam yetkili sayısını görüntüleme
- **Ziyaretçi & Analitik:**
  - Siteye kaç kişinin giriş yaptığını görüntüleme
  - Hangi cihazdan ne kadar giriş yapıldığını görme
  - Ziyaretçilerin sitede geçirdiği süreyi takip etme
- **Finans Yönetimi:**
  - Toplam gelir ve giderleri görüntüleme
  - Toplam mevcut bakiyeyi kontrol etme

## Kurulum

1. **Projeyi Kopyalayın**
   ```bash
   git clone https://github.com/mekroket/innomis-php-website.git
   ```
2. **Bağımlılıkları Yükleyin**
   - PHP'nin ve MySQL'in kurulu olduğundan emin olun.
3. **Veritabanını Kurun**
   - `database.sql` dosyasını MySQL veritabanına içe aktarın.
4. **Konfigürasyonu Yapın**
   - `config.php` dosyasındaki veritabanı bilgilerini güncelleyin.
5. **Sunucuyu Başlatın**
   - Bir **Apache** veya **Nginx** sunucusunda çalıştırabilirsiniz.
   - `localhost` üzerinde test etmek için `XAMPP` veya `WAMP` gibi araçları kullanabilirsiniz.

## Kullanım
- Web sitesine giriş yaparak topluluk hakkında bilgi alabilir, etkinlikleri takip edebilir ve iletişime geçebilirsiniz.
- Admin panelinden yönetici yetkileriyle siteyi yönetebilirsiniz.

## Katkıda Bulunma
Projeye katkıda bulunmak isterseniz:
1. **Fork** yapın.
2. Yeni bir **branch** oluşturun (`feature-ekleme` gibi).
3. Değişikliklerinizi yapın ve **commit** atın.
4. **Pull Request** gönderin.

## Lisans
Bu proje **MIT Lisansı** ile lisanslanmıştır. Daha fazla bilgi için `LICENSE` dosyasına bakabilirsiniz.

## İletişim
- **Topluluk:** Innomis - İnovasyon ve Yönetim Bilişim Sistemleri
- **Geliştirici:** [GitHub Profiliniz]

