define(
    [
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/modal/modal',
    'mage/storage',
    'jquery/jquery-storageapi',
    ], function ($, Component, ko, quote, modal) {
        'use strict';


        var cacheKey = 'foxpostdata',
        storage = $.initNamespaceStorage('mage-cache-storage').localStorage;

        var psModal = Component.extend(
            {
                defaults: {
                    template: 'Oander_FoxPostShippingMethod/additional-shipping-option',
                    parcelData: {}
                },
                parcelshopMethodSelected: ko.observable(),
                pclshopData: ko.observable(),
                modalSelector: '#parcelshop-modal',
                modalElem: null,
                close: false,
                modalOptions: {
                    type: 'popup',
                    responsive: true,
                    innerScroll: false,
                    clickableOverlay: true,
                    modalClass: 'foxpost-detail-modal',
                    modalCloseBtn: false,
                    buttons: [{
                        text: $.mage.__('Continue'),
                        click: function (e) {
                            e.preventDefault();
                            var phoneNumber = $("[name='modal-phonenumber']").val();

                            if (phoneNumber == '') {
                                $('#modal-error-foxpost').html($.mage.__('Please enter your phone number'));
                                return false;
                            }

                            const regex = /^(\+36|36)(20|30|31|70|50)\d{7}$/gm;

                            if (regex.exec(phoneNumber) !== null) {
                                $('#modal-error-foxpost').html('');
                                this.closeModal();
                            } else {
                                $('#modal-error-foxpost').html($.mage.__('Not valid, for example: +36301234567'));
                                return false;
                            }

                            var data = JSON.parse(storage.get(cacheKey));
                            data['phonenumber'] = $("[name='modal-phonenumber']").val();
                            var newMethodLabel = quote.shippingMethod()['carrier_title'];
                            if (data.name && data.address) {
                                newMethodLabel = newMethodLabel + '<br>' + data.name + '<br>' + data.address
                            }
                            storage.set(cacheKey, JSON.stringify(data));
                            $("#label_method_foxpost_foxpost").html(newMethodLabel);
                        }
                    },
                    {
                        text: $.mage.__('Cancel'),
                        class: 'cancel-foxpost',
                        click: function (e) {
                            this.closeModal();
                        }
                    }]
                },
                initialize: function () {
                    var self = this._super();
                    storage.set(cacheKey, null);
                    var sendData;

                    this.selectedMethod = ko.computed(
                        function () {
                            var method = quote.shippingMethod();
                            var selectedMethod = method != null ? method.carrier_code + '_' + method.method_code : null;
                            return selectedMethod;
                        }, this
                    );
                    this.iframeurl = ko.computed(
                        function () {
                            return window.checkoutConfig.foxpost_data.iframeurl;
                        }
                    )

                    function receiveMessage(event)
                    {
                        var data = JSON.parse(event.data);
                        var sendData = {
                            name: data.name,
                            code: data.place_id,
                            address_zip: data.zip,
                            address: data.address
                        };
                        storage.set(cacheKey, JSON.stringify(sendData));
                    }

                    window.addEventListener("message", receiveMessage, false);
                    return this;
                },
                showModal: function () {
                    var method = quote.shippingMethod();
                    var selectedMethod = method != null ? method.carrier_code + '_' + method.method_code : null;

                    if (selectedMethod.indexOf('foxpost_foxpost') !== -1) {
                        $(".action-close").hide();
                        this.parcelshopMethodSelected(true);
                        this.modalElem = $(this.modalSelector);

                        if (this.modalElem.length) {
                            modal(this.modalOptions, this.modalElem);
                            this.modalElem.modal("openModal");
                        }
                    }
                }
            }
        );

        window.$ = $;

        $(document).on(
            "click", "#checkout-shipping-method-load table.table-checkout-shipping-method tr.row", function () {
                psModal.prototype.showModal();
            }
        );

        return psModal;
    }
);
