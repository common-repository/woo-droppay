=== WooCommerce DropPay ===
Contributors: A-Tono, Zero11
Tags: woocommerce, droppay, payment method
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires at least: 4.9
Tested up to: 4.9
Requires PHP: 5.6
Stable tag: 1.0.0

== Description ==

DropPay is the free mobile payment system that allows users to send and receive money in real time and to make purchases in participating shops scanning QR Codes.

- The registration for the user is free of charge
- No opening or management expense.
- No payment or money exchange commission.
- Plugin integration is free.

Merchant can sign up to https://secure.drop-pay.com for the service with the extraordinary benefit of not having to pay any commission on payments received ever, without any transaction limit.

Each contracted Exhibitors will pay a small weekly fee of €2.50 if there are movements and only from the second year (the first one is free).

The DropPay revolution is also in the elimination of retail sales commissions, a clear trend reversal compared to the cost of the transaction imposed by the competition.

=Features=

- System that is licensed by Banca d’Italia, independent of traditional circuits and open, since it allows you to do transactions with those who don’t have a DropPay account.
- DropPay always asks permission on the operation that the app is about to make.
- Each charge is expressly authorized by the customer and is only made through DropPay.
- The customer is identified with a Public Key Encryption system.

Business account holders can sell with DropPay in physical stores (via QR Code or integration with the POS and cash registers) and online (via API integration or e-commerce plug-ins).

== Frequently Asked Questions ==

= Which version of WooCommerce is compatible? =

Form 3.2+

== Configuration ==

Get into Developer Secure Area of [DropPay website](https://secure.drop-pay.com/).

By hitting the + button you can add a POS.
When you focus on Brand input field, the list of your available brands is shown up. Choose the one you want the new POS is collecting payment for. Now choose the Store from available ones in the list.

Then make sure the Checkout radio button is set and define:
- the name
- a short description (optional)
- the domain (i.e. mycommerce.server.com) where you installed the DropPay Extension

Click the button to complete the creation of the new POS.

In the newly created POS you can find the Checkout Public Key and Checkout Private Key to be used for the configuration of the plugin.

== Screenshots ==

1. No costs for the users. No commissions for the operators.
2. Each charge is authorized by the customer.
3. To complete the purchase scan the QR Code
4. Create a new POS.
5. Set Info.
6. Configure Plugin.

== Changelog ==

= 1.0.0 =
* Stable version of plugin
