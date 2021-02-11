# Opencart Distance Sales Contract

Distance sales contract compatible with Opencart V2.x.x, V3.x.x, Journal 2, Foster theme and Quick Checkout.

Please create issues for support.

According to the consumer law, it is a legal obligation to have the "Distance Sales Contract" on online sales sites.

## Features:
- [x] Opencart V2.x.x is compatible with V3.x.x versions.
- [x] Compatible with Journal 2 and Foster theme.
- [x] Compatible with Quick Checkout.
- [x] Compatible with Lexus Ceramic, Lexus Nomi and Raven themes.
- [x] Dynamically created for each order. The contract includes the name, surname, address, delivery address, the products ordered and the seller's information.
- [x] Displays coupon discounts and shipping costs in contracts.
- [x] All fees added to totals by other modules are shown in the same way.
- [] Agreements can be changed from admin panel. (When changed, contracts belonging to old orders do not change)
- [] After the order is completed, the contracts are sent to the buyer in PDF as an attachment to the e-mail. (Can be changed optionally from the panel)
- [] Contract and preliminary information form are saved in the database. The buyer can view the contracts for that order at any time or download them as PDF.
- [] Contract and preliminary information form for that order can be viewed in the admin panel or downloaded as PDF.
- [] Modal (Pop-up) or in-page display option.

## Installation
- After downloading the repository, replace the ** default ** folder name in the ** catalog / view / theme / default ** folder with your own theme name.
- Then upload the ** catalog ** folder to your site's root directory.
- Open * catalog / controller / extension / quickcheckout / terms.php * on your web page. Find the code below.

``
if ($ information_info) (
$ data ['text_agree'] = sprintf ($ this-> language> get ('text_agree'), $ this-> url-> link ('information / information / agree', 'information_id ='. $ this-> config -> get ('config_checkout_id'), true), $ information_info ['title'], $ information_info ('title']);
} else {
$ data ['text_agree'] = '';
}
} else {
$ data ['text_agree'] = '';
}
``

- Replace as below.

``
if ($ information_info) (
$ data ['text_agree'] = sprintf ($ this-> language-> get ('text_agree'), $ this-> url-> link ('checkout / contract', 'information_id ='. $ this-> config- > get ('config_checkout_id'), true), 'Distance Selling Agreement', 'Distance Selling Agreement');
} else {
$ data ['text_agree'] = '';
}
} else {
$ data ['text_agree'] = '';
}
``

## E-commerce Sites Using the Plugin
Second hand book seller [Sosyal Shaf] (https://www.sosyalsahaf.com/)

## About me
[Twitter] (https://twitter.com/kamilklkn) | [Instagram] (http://instagram.com/kamilklkn) | [Linkedin] (http://tr.linkedin.com/in/kamilklkn/) | [500px] (https://500px.com/kamilklkn) | [Vsco] (https://vsco.co/kamilklkn/) | [Web Page] (http://www.kamilklkn.com/) 
