/**
 * Copyright ©2023 Oander Media Kft. (https://www.oander.hu). All rights reserved.
 * See COPYING.txt for license details.
 */

define(
    [
    'jquery',
    'mage/utils/wrapper',
    'underscore',
    'Magento_Checkout/js/model/quote',
    'jquery/jquery-storageapi',
    'mage/storage',
    ], function ($, wrapper, _, quote) {
        'use strict';

        var cacheKey = 'foxpostdata',
        storage = $.initNamespaceStorage('mage-cache-storage').localStorage;


        return function (payloadExtender) {
            return wrapper.wrap(
                payloadExtender, function (originalFunction, payload) {
                    var foxPostData = storage.get(cacheKey) ? storage.get(cacheKey) : '';

                    payload = originalFunction(payload);
                    quote["foxpost_data"] = foxPostData;

                    _.extend(
                        payload.addressInformation, {
                            extension_attributes: {
                                'foxpost_data': foxPostData
                            }
                        }
                    );
                    storage.set(cacheKey, null);
                    return payload;
                }
            );
        };
    }
);
