# OANDER FoxPost Shipment for Magento 2

> This module provides two shipping methods to Magento 2.
> To use this module configuration is needed. Look the config.xml and the admin pages.

## 1. Requirements

      PHP >= 7.4

## 2. Compatibility

      Magento >= 2.4

## 3. Incompatibility

      Oander_GLS Module

## 4. How to install

Run the following command in Magento 2 root folder:

```
composer require oander/modul-foxpost-shipping-method
php bin/magento module:enable Oander_FoxPostShippingMethod
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

## 5. Settings

#### 5.0 Setup Shipping method

> You can setup the standard Magento shipping settings here.

* Stores
    * Configuration
        * Sales
            * Shipping Methods / Delivery Methods
                * FoxPost ShippingMethod
                    * Shipping Settings

#### 5.1 Setup Pickup Address

> You can setup the threshold settings here.

* Stores
    * Configuration
        * Sales
            * Shipping Methods / Delivery Methods
                * FoxPost ShippingMethod
                    * Enable Free Shipping Threshold

## 6. Configuration

| Setting                        | Description                                                                                                                                 |
|--------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------|
| Enabled for Checkout           | Here you can enable / disable this shipping method                                                                                          |
| Title                          | This will be shown for the customer                                                                                                         |
| Method name                    | This will be shown for the customer                                                                                                         |
| Shipping Fee                   | Price of this shipping method, default empty                                                                                                |
| Enable Free Shipping Threshold | When this option is disabled this shipping method will be free in every case, or the price is as much as we set in the "Shipping Fee" field |
| Displayed Error Message        | This message will be shown when something gone wrong with this shipping method                                                              |
| Ship to Applicable Countries   | The shipping method can be allowed in all country or specified country                                                                      |
| Ship to Specific Countries     | When the applicable countries are set to specified, the admin can set the specified country in this list                                    |
| Show Method if Not Applicable  | The shipping method can be hidden if this not applicable in the customer's country                                                          |
| Sort Order                     | Position of this shipping method                                                                                                            |

## 7. Foxpost API Configuration

| Setting    | Description                                                                   |
|------------|-------------------------------------------------------------------------------|
| Enabled    | Enable / Disable the module                                                   |
| Username   | The Foxpost API Username                                                      |
| Password   | The Foxpost API Password                                                      |
| API Key    | The Foxpost API Key                                                           |
| Label size | The Foxpost API Label Sizes (generated wherever a shipment is sent to Foxpost |

## 8. Developer info

```
PHPUnit Coverage: 0%
```

Email template handling has been overwritten in this xml:
etc/email_templates.xml
