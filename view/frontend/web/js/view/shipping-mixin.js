/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

define(
    [
        'jquery',
        'underscore',
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'uiRegistry',
        'mage/translate',
        'Magento_Checkout/js/model/shipping-rate-service',
        'jquery/jquery-storageapi',
        'mage/storage',
    ], function (
        $,
        _,
        Component,
        ko,
        customer,
        addressList,
        addressConverter,
        quote,
        createShippingAddress,
        selectShippingAddress,
        shippingRatesValidator,
        formPopUpState,
        shippingService,
        selectShippingMethodAction,
        rateRegistry,
        setShippingInformationAction,
        stepNavigator,
        modal,
        checkoutDataResolver,
        checkoutData,
        registry,
        $t
    ) {
        'use strict';
        var cacheKey = 'foxpostdata',
            storage = $.initNamespaceStorage('mage-cache-storage').localStorage;

        return function (Component) {
            return Component.extend(
                {
                    validateShippingInformation: function () {
                        var result = this._super();
                        var foxPostData = storage.get(cacheKey) ? storage.get(cacheKey) : false;
                        if (quote.shippingMethod()['carrier_code'] == "foxpost") {
                            if (!foxPostData) {
                                this.errorValidationMessage(
                                    $t('Select a parcel point')
                                );
                                return false;
                            }

                            if ($("input[name='modal-phonenumber']").val().length === 0) {
                                this.errorValidationMessage(
                                    $t('Enter your mobile phone')
                                );
                                return false;
                            }

                        }
                        return result;
                    }
                }
            );
        }
    }
);
