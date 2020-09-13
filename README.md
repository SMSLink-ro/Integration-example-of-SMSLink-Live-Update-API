# Integration example of SMSLink Live Update API

SMSLink Live Update allows performing operations on a SMSLink account, operations such as: 

- Blacklist Phone Number Add/Remove/Verify
- Contact Create/Remove/Update. 

## Requirements & Usage

1. Create an account on [SMSLink.ro](https://www.smslink.ro/inregistrare/)
2. Create a Live Update connection at [SMSLink.ro / SMS Marketing / Live Update](https://www.smslink.ro/sms/marketing/liveupdate.php). Each Live Update connection is a pair of Connection ID and Password. 

## Featured Functions

- *$liveUpdate = new SMSLinkLiveUpdate("MyLiveUpdateConnectionID", "MyLiveUpdatePassword");* - Instantiates the SMSLinkLiveUpdate class for performing various operations
- *$liveUpdate->blacklistAdd()* - Adds a Phone Number to the Blacklist in your SMSLink account
- *$liveUpdate->blacklistRemove()* - Removes a Phone Number from the Blacklist in your SMSLink account     
- *$liveUpdate->isBlacklisted()* - Checks if Phone Number is in the Blacklist in your SMSLink account
- *$liveUpdate->createContact()* - Creates a Contact into a Specified Group in your SMSLink account
- *$liveUpdate->updateContact()* - Updates a Contact from a Specified Group in your SMSLink account
- *$liveUpdate->removeContact()* - Removes a Phone Number from a Specified Group or from All Groups in your 

## Features

- Supports HTTP and HTTPS protocols
- Supports PHP cURL GET, PHP cURL POST and file_get_contents()

## System Requirements 

PHP 5 with CURL enabled or file_get_contents with allow_url_fopen to be set to 1 in php.ini
    
## Documentation

The [complete documentation](https://smslink.ro/sms-marketing-documentatie-live-update.html) of the SMSLink - Live Update API can be found [here](https://smslink.ro/sms-marketing-documentatie-live-update.html), describing all available API functions.

## SMS Sending using SMSLink.ro

In order to send SMS using SMSLink.ro, please see SMSLink.ro API, called SMS Gateway. SMSLink.ro allows you to send SMS to all mobile networks in Romania and also to more than 168 countries and more than 1000 mobile operators worldwide. The [complete documentation](https://www.smslink.ro/sms-gateway-documentatie-sms-gateway.html) of the SMSLink - SMS Gateway API can be found [here](https://www.smslink.ro/sms-gateway-documentatie-sms-gateway.html), describing all available APIs (HTTP GET / POST, SOAP / WSDL, JSON and more).

Examples for SMS Gateway (HTTP), SMS Gateway (SOAP), SMS Gateway (JSON) or SMS Gateway (BULK) can be found here:

- SMS Gateway (HTTP): https://github.com/SMSLink-ro/Example-for-sending-SMS-using-PHP-cURL/blob/master/main.php
- SMS Gateway (SOAP): https://github.com/SMSLink-ro/Example-for-sending-SMS-using-PHP-SoapClient/blob/master/main.php
- SMS Gateway (JSON): https://github.com/SMSLink-ro/Example-for-sending-SMS-using-PHP-cURL-with-JSON/blob/master/main.php
- SMS Gateway (BULK) Endpoint Version 1: https://github.com/SMSLink-ro/Example-for-sending-SMS-using-PHP-with-BULK-Endpoint-ver.-1/blob/master/main.php
- SMS Gateway (BULK) Endpoint Version 3: https://github.com/SMSLink-ro/Example-for-sending-SMS-using-PHP-with-BULK-Endpoint-ver.-3/blob/master/main.php

SMSLink also provides modules for major eCommerce platforms (on-premise & on-demand), integrations using Microsoft Power Automate, Zapier or Integromat and many other useful features. Read more about all available features [here](https://www.smslink.ro/sms-gateway.html). 

## Support

For technical support inquiries contact us at contact@smslink.ro or by using any other available method described [here](https://www.smslink.ro/contact.php).
