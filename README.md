# Opencart Mesafeli Satış Sözleşmesi

Opencart V2.x.x, V3.x.x, Journal 2, Foster teması ve Quick Checkout ile uyumlu mesafeli satış sözleşmesi.

Destek için lütfen issues oluşturun.

Tüketici kanunu gereği "Mesafeli Satış Sözleşmesinin" online satış yapılan sitelerde bulunması yasal zorunluluktur.

## Özelikleri:
- [x] Opencart V2.x.x, V3.x.x sürümleri ile uyumludur.
- [x] Journal 2 ve Foster teması ile uyumludur.
- [x] Quick Checkout ile uyumludur.
- [x] Lexus Ceramic, Lexus Nomi ve Raven temaları ile uyumludur.
- [x] Her siparişe dinamik olarak oluşturulur. Sözleşmede alıcının adı soyadı, adresi, teslimat adresi, sipariş ettiği ürünler ve satıcının bilgileri yer alır.
- [x] Kupon indirimlerini ve kargo ücretlerini sözleşmeler içinde gösterir.
- [x] Diğer modüller tarafından toplamlara eklenen tüm ücretler aynı şekilde gösterilir.
- [ ] Sözleşmeler yönetici panelinden değiştirilebilir. (Değiştirildiğinde eski siparişlere ait sözleşmeler değişmez)
- [ ] Sipariş tamamlandıktan sonra sözleşmeler PDF olarak alıcıya e-posta'da ek olarak yollanır. (İsteğe bağlı olarak panelden değiştirilebilir)
- [ ] Sözleşme ve ön bilgilendirme formu veritabanına kaydedilmektedir. Alıcı istediği zaman o siparişe ait sözleşmeleri görüntüleyebilir ya da PDF olarak indirebilir.
- [ ] Yönetici panelinde o siparişe ait sözleşme ve ön bilgilendirme formu görüntülenebilir ya da PDF olarak indirilebilir.
- [ ] Modal (Pop-up) ya da sayfa içinde gösterim opsiyonu vardır.

## Kurulumu
- Repository indirdikten sonra **catalog/view/theme/default** klasörünü içindeki **default** klasörün adını kendi tema adınız ile değiştirin.
- Daha sonra **catalog** klasörünü sitenizin kök dizinine yükleyiniz.
- Web sayfanızda *catalog/controller/extension/quickcheckout/terms.php* dosyasını açın. Aşağıdaki kodu bulun.

```
	if ($information_info) {
		$data['text_agree'] = sprintf($this->language >get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_checkout_id'), true), $information_info['title'], $information_info['title']);
	} else {
				$data['text_agree'] = '';
			}
	} else {
			$data['text_agree'] = '';
	}
```

- Aşağıdaki gibi değiştirin.

```
	if ($information_info) {
		$data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('checkout/sozlesme', 'information_id=' . $this->config->get('config_checkout_id'), true), 'Mesafeli Satış Sözleşmesi', 'Mesafeli Satış Sözleşmesi');
	} else {
				$data['text_agree'] = '';
			}
	} else {
			$data['text_agree'] = '';
	}
```

## Eklentiyi Kullanan E-ticaret Siteleri
İkinci el kitap satışı yapan [Sosyal Shaf](https://www.sosyalsahaf.com/)

## Hakkımda
[Twitter](https://twitter.com/kamilklkn) | [Instagram](http://instagram.com/kamilklkn) | [Linkedin](http://tr.linkedin.com/in/kamilklkn/) | [500px](https://500px.com/kamilklkn) | [Vsco](https://vsco.co/kamilklkn/) | [Web Page](http://www.kamilklkn.com/)
