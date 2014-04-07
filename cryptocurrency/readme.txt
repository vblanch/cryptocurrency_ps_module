*********************************************
Cryptocurrency Module for Prestashop v. 1.0.0
by Prestashop & Victor Blanch
*********************************************

Version history:
*********************************************

February 2014: 1.0.0: Initial release of the module.

Intro:
*********************************************

What it does: Adds a new payment method for your shop based on cryptocurrencies (like Bitcoin, Litecoin, 
Dogecoin or any other you like). You will be able to sell your products in fiat also just as usual. Prestashop 
lets you configure the currencies that will be used on the "Cryptocurrency" payment method.

Please note that payments will have to be processed manually. This module works like the Bank Wire module
developed by Prestashop (in fact it's based on it), but it lets you put any number of cryptocurrencies to let 
the customer choose.

Please also note that you will have to change manually (or with another module) the "conversion rate" field
on the currencies list (Back Office->Localization->Currencies). Cryptocurrencies can have big fluctuations in
conversion rates so it's recommended to update this information very often.

Instructions:
*********************************************

In order to activate this module to allow the payment with cryptocurrencies you must follow these steps:

1. Go to your prestashop backend.

2. On the upper menu, click on Localization > Currencies

3. Add as many cryptocurrencies as you want for your shop. Recommended ISO code for cryptocurrencies: 999.
Also beware that some symbols for currencies won't display properly on the invoices. If you want to auto-update the 
value of the currencies (Bitcoin and Dogecoin) you must set their ISO codes as BTC and DGE.

4. Once you have your currencies, go to Modules > Payment.

5. On "Payment module restrictions" section, the cryptocurrency module is represented by the "blue coins" icon.
Mark the checkboxes on the "blue coins" column that match the cryptocurrencies, and uncheck the others 
(for example: if you have Euro, Litecoin and Bitcoin, uncheck Euro and check Litecoin and Bitcoin under the column, so
Euro won't show up while paying with the cryptocurrency payment method).

6. Go to Modules and search for the Cryptocurrency module. Click on Configure and fill the fields there. A wallet
field will be displayed for every cryptocurrency created in step 3. Put there your wallet address, so it will be
displayed on the payment process and inside the mail templates.

6.1 Remember to check the checkboxes in the Configure screen if you want to auto-update your bitcoin and dogecoin
values, and remember their ISO codes must be BTC and DGE respectively.

7. (optional) If you are using any other language different from English (code: 'en'), you must translate and copy the files
"cryptocurrency.txt" and "cryptocurrency.html" into your /mails/{code of your language} folder in order to be
able to send the "Awaiting cryptocurrency transaction" email in that language. You can find these files in the 
/mails/en folder inside this module.

8. You are done, your customers will see the cryptocurrency payment method if they choose a cryptocurrency
while inside your shop website. Remember to have the "Currency block" module activated and show in your page
so they can switch currencies.


Final notes:
*********************************************

This module should work for any 1.5.x Prestashop system.
This module copies several files in your Prestashop filesystem while the installation process is made, but 
overwrites none.
This module also inserts and updates data on your database. However no data or files are destroyed by this module.
All the changes made by this module can be reversed, and they represents no damage for your Prestashop system.
However I take no resposability for any damage made on your Prestashop shop or any money lost in any transaction
of any cryptocurrency. Please remember that this module doesn't interact with transactions, just creates an order
and gives the customer the wallet address to pay, so everything else must be manually processed.
Files copied:
-"14.gif" ("blue coins") into the /img/os folder.
-"cryptocurrency.html" & "cryptocurrency.txt" mail templates into the /mails/en folder.
-Changes on the database: Added the cryptocurrency method and order state mocking the Bank Wire method. Please 
see source code for details.
If you want to help with the development of this module, you can fork it in GitHub or send me a message with
the attached translation file for your language, so it will be added to the repository. Thanks if you do!

Credits:
*********************************************

This module is based on:
Prestashop "Bank Wire" module by Prestashop (released with PS version 1.5.6.2).
@ 2007-2014 PrestaShop SA
Adapted for cryptocurrencies by Victor Blanch.
http://vblanch.com
This module is subject to the Academic Free License (AFL 3.0)
that is bundled with this package in the file LICENSE.txt.

Larger image of the payment method obtained here and under CC0 license:
http://pixabay.com/en/background-bits-bit-network-blue-213649/

Donations:
*********************************************

If you like this module, please make a donation to support the author using 
Paypal, Bitcoin, Litecoin or Dogecoin:

Paypal: victor at vblanch (dot) com
Bitcoin : 12NZncFaCSv5xE8GCVDFBMaCAoLDDmqhL4
Litecoin: LYqdGQ9Eu2XCva6kSHWJ3uSTxBivFyfDNM
Dogecoin: DShCGaE7c9Ur9N29kd7Wfs7xqTCQTKoLAg
