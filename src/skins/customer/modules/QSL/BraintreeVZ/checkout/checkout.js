/* vim: set ts=4 sw=4 sts=4 et: */

/**
 * Braintree widget for checkout (common for OPC and FLC) 
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

core.bind(
    'load',
    function() {

        /**
         * Accordion magic
         */
        $('.collapse').on('show.bs.collapse', function (e) {
            $('.collapse').not(e.target).removeClass('in');
        });
        $('.collapse').on('shown.bs.collapse', function (e) {
            braintreePayment.init();
        });        
        $('.collapse.in').prev('.panel-heading').addClass('active');
        $('#braintree-container, #bs-collapse').on('show.bs.collapse', function(a) {
            $(a.target).prev('.panel-heading').addClass('active');
        }).on('hide.bs.collapse', function(a) {
            $(a.target).prev('.panel-heading').removeClass('active');
        });

        // Hide card form when saved cards are shown
        $('#collapse-saved-cards').on('shown.bs.collapse', function () {
            $('#collapse-new-card').collapse('hide');
        });

        /**
         * Trigger error
         */
        braintreePayment.checkout.triggerError = function(message)
        {
            core.trigger('message', { 'message': message, 'type': MESSAGE_ERROR });
        };

        /**
         * Check if Braintree is the current payment method
         */
        braintreePayment.checkout.isCurrent = function(includeSavedCards)
        {
            let currentPaymentId = $('input[name=methodId][id^=pmethod]:checked:visible').val();
            let braintreePaymentId = $('#braintree-payment-id').val();

            let isCurrent = currentPaymentId == braintreePaymentId;

            if ('undefined' == typeof includeSavedCards || !includeSavedCards) {
                isCurrent = isCurrent && $('#collapse-new-card.in').length;
            }

            return isCurrent;
        }

        /**
         * List of PayPal buttons
         */
        braintreePayment.paypalButtons = ['#braintree-paypal-button'];

        /**
         * Constructor/initializator
         */
        braintreePayment.checkout.init = function(callback)
        {
            let url = URLHandler.buildURL({
                target: 'braintree',
                action: 'get_braintree_data'
            });

            core.get(url, (response) => { callback.bind(braintreePayment, response.responseJSON)(); });
        }

        /**
         * Get cart total
         */
        braintreePayment.checkout.getCartTotal = function(callback)
        {
            let url = URLHandler.buildURL({
                target: 'braintree',
                action: 'get_cart_total'
            });

            core.get(url, (response) => { callback.bind(braintreePayment, response.responseJSON)(); });
        };

        /**
         * Get saved card nonce
         */
        braintreePayment.checkout.getSavedCardNonce = function(callback)
        {
            let token = '';

            if ($('[name=saved_card_token]:checked').length) {
                token = $('[name=saved_card_token]:checked').val();
            }

            let url = URLHandler.buildURL({
                target: 'braintree',
                action: 'get_saved_card_nonce',
                token: token,
            });

            core.get(url, (response) => { callback.bind(braintreePayment, response.responseJSON)(); });
        };

        /**
         * Get data for the PayPal payment
         */
        braintreePayment.checkout.getPayPalData = function(callback)
        {
            let url = URLHandler.buildURL({
                target: 'braintree',
                action: 'get_paypal_data'
            });

            core.get(url, (response) => { callback.bind(braintreePayment, response.responseJSON)(); });
        };

        /**
         * Add 3-D secure iframe callback
         */
        braintreePayment.checkout.addFrameCallback = function(iframe)
        {
            let div = $('<div></div>').css('min-width', '400px').css('min-height', '400px').html(iframe);

            popup.open(div);
        };

        /**
         * Remove 3-D secure iframe callback
         */
        braintreePayment.checkout.removeFrameCallback = function(iframe)
        {
            popup.close();
        };

        /**
         * Process shadows
         */
        braintreePayment.checkout.processInProgress = function ()
        {
            let elm = $('#collapse-new-card');

            if (braintreePayment.isInProgress) {
                assignWaitOverlay(elm);
            } else {
                unassignWaitOverlay(elm);
                $('.wait-block-overlay', '.braintree-panel-content').remove(); // Otherwise doesn't work
                if (jQuery('.steps').get(0)) {
                    jQuery('.steps').get(0).loadable.unshade(); // Unshade checkout
                }
                jQuery('.place-order').removeClass('submitted'); // And re-activate place order button
            }
        }
    }
);

