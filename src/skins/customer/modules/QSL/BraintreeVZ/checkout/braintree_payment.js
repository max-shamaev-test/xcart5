/* vim: set ts=4 sw=4 sts=4 et: */

/**
 * Braintree widget 
 *
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */


var braintreePayment = {

    /**
     * Client authorization from the server
     */
    token: '',

    /**
     * Merchant ID
     */
    merchantId: '',

    /**
     * Test mode flag
     */
    isTestMode: false,

    /**
     * 3-D Secure flag
     */
    is3dSecure: false,

    /**
     * 3-D Secure for not supported cards flag
     */
    isAcceptNo3dSecure: false,

    /**
     * Error message for the failed 3-D Secure
     */
    acceptNo3dSecureError: '3-D Secure failed',

    /**
     * 3-D Secure for the vaulted cards
     */
    is3dSecureForVault: false,

    /**
     * PayPal flag
     */
    isPayPal: false,

    /**
     * Flag indicating is it a PayPal express checkout button or complete braintree component
     */
    isButton: false,

    /**
     * Kount merchant ID
     */
    kountMerchantId: '',

    /**
     * Payment method nonce
     */
    _nonce: '',
    get nonce() {
        return this._nonce;
    },
    set nonce(value) {
        this._nonce = value;
        this.nonceElement.value = value;
    },

    /**
     * Payment method nonce element
     */
    nonceElement: null,

    /**
     * Form element
     */
    formElement: null,

    /**
     * Braintree client instance
     */
    client: null,

    /**
     * Braintree hosted fields instance
     */
    hostedFields: null,

    /**
     * Braintree PayPal instance
     */
    paypal: null,

    /**
     * Braintree PayPal Checkout instance
     */
    paypalCheckout: null,

    /**
     * Braintree 3-D Secure instance
     */
    secure3d: null,

    /**
     * Credit card number selector
     */
    numberSelector: '',

    /**
     * Credit card number placeholder
     */
    numberPlaceholder: '4111 1111 1111 1111',

    /**
     * CVV selector
     */
    cvvSelector: '',

    /**
     * CVV placeholder
     */
    cvvPlaceholder: '123',

    /**
     * Expiration date selector
     */
    dateSelector: '',

    /**
     * Expiration date placeholder
     */
    datePlaceholder: '10/2025',

    /**
     * PayPal buttons DOM id's
     */
    paypalButtons: {},

    /**
     * PayPal button style
     */
    paypalButtonStyle: {},

    /**
     * Flag indicating braintree is initialized
     */
    isInitialized: false,

    /**
     * Flag indicating something is in progress 
     */
    _isInProgress: false,
    get isInProgress() {
        return this._isInProgress;
    },
    set isInProgress(value) {
        this._isInProgress = value;
        this.checkout.processInProgress();
    },

    /**
     * Credit card form fields
     */
    fields: {},

    /**
     * Credit card form CSS styles
     */
    styles: {},    

    /**
     * Trigger error
     */
    triggerError: function(e)
    {
        console.error(e);

        if (typeof e === 'string' || e instanceof String) {
            var message = e;
        } else if ('undefined' != typeof e.message) {
            var message = e.message
        } else {
            var message = 'Unknown Braintree error';
        }

        this.nonce = '';

        this.checkout.triggerError(message);

        this.isInProgress = false;
    },

    /**
     * Check if Braintree is the current method
     */
    isCurrent: function (includeSavedCards)
    {
        return this.checkout.isCurrent(includeSavedCards);
    },

    /**
     * Create Braintree 3-D Secure instance callback
     */
    create3DSecureCallback: function(error, instance)
    {
        if (error) {
            return this.triggerError(error);
        }

        this.secure3d = instance;

        this.checkout.create3DSecureCallback(instance);

        this.isInProgress = false;
        this.isInitialized = true;
    },

    /**
     * Add 3-D secure iframe callback
     */
    addFrameCallback: function(error, iframe)
    {
        if (error) {
            return this.triggerError(error);
        }

        this.checkout.addFrameCallback(iframe);

        this.isInProgress = false;
    },

    /**
     * Remove 3-D secure iframe callback
     */
    removeFrameCallback: function()
    {
        if ('function' == typeof this.checkout.removeFrameCallback) {
            this.checkout.removeFrameCallback();
        }
    },

    /**
     * Verify card via 3-D Secure
     */
    verifyCard: function(options)
    {
        if ('undefined' != options.nonce && options.nonce) {
            this.nonce = options.nonce;
        }

        var options = {
            amount:      options.total,
            nonce:       this.nonce,
            addFrame:    this.addFrameCallback.bind(this),
            removeFrame: this.removeFrameCallback.bind(this),
        };

        this.secure3d.verifyCard(options, this.verifyCardCallback.bind(this));
    },

    /**
     * 3-D Secure verify card callback
     */
    verifyCardCallback: function(error, response)
    {
        if (error) {
            return this.triggerError(error);
        }

        if (
            'undefined' != typeof response.verificationDetails
            && response.verificationDetails.liabilityShiftPossible == false
            && response.verificationDetails.liabilityShifted == false
            && this.isAcceptNo3dSecure == false
        ) {
            return this.triggerError(this.acceptNo3dSecureError);
        }

        this.checkout.verifyCardCallback(response);

        this.nonce = response.nonce; 

        this.isInProgress = false;
        jQuery(this.formElement).submit();
    },

    /**
     * Create Braintree hosted fields instance callback
     */
    createHostedFieldsCallback: function(error, instance)
    {
        if (error) {
            return this.triggerError(error);
        }

        // Clear nonce
        this.nonce = '';

        // Form submitter
        this.formElement.addEventListener('submit', this.submitForm.bind(this));
        jQuery(this.formElement).bind('submit', this.submitForm.bind(this));

        this.hostedFields = instance;

        this.checkout.createHostedFieldsCallback(instance);

        if (this.is3dSecure) {
            braintree.threeDSecure.create({client: this.client}, this.create3DSecureCallback.bind(this));
        } else {
            this.isInProgress = false;
            this.isInitialized = true;
        }
    },

    /**
     * Get options for the Hosted Fields initialization 
     */
    getHostedFieldsOptions: function()
    {
        return {
            client: this.client,
            styles: this.styles,
            fields: this.fields,
        };
    },

    /**
     * PayPal button payment
     */
    paypalButtonPayment: function()
    {
        return new Promise(
            (resolve, reject) => {
                this.checkout.getPayPalData(
                    (options) => { resolve(this.paypalCheckout.createPayment(options)); }
                );
            }
        );
    },

    /**
     * Callback for get PayPal data
     */
    getPayPalDataCallback: function(options)
    {
        return this.paypalCheckout.createPayment(options);
    },

    /**
     * PayPal button Authorize action
     */
    paypalButtonOnAuthorize: function(data, actions)
    {
        return this.paypalCheckout.tokenizePayment(data)
            .then(this.paypalButtonOnAuthorizeThen.bind(this));
    },

    /**
     * Then for PayPal button Authorize action
     */
    paypalButtonOnAuthorizeThen: function(payload)
    {
        this.nonce = payload.nonce;

        this.isInProgress = false;

        if (!this.isButton) {

            // Submit order
            jQuery(this.formElement).submit();

        } else {

            // Proceed to checkout
            this.checkout.continuePayPal(payload.details);
        }
    },

    /**
     * PayPal button Cancel action
     */
    paypalButtonOnCancel: function(data)
    {
        if (core.isDeveloperMode) {
            console.log(data);
        }
    },

    /**
     * Create PayPal Checkout calback
     */
    createPayPalCheckoutCallback: function (error, instance)
    {
        if (!this.isButton) {
            // Regardles of the error create hosted fields
            braintree.hostedFields.create(this.getHostedFieldsOptions(), this.createHostedFieldsCallback.bind(this));
        }

        if (error) {
            return this.triggerError(error);
        }

        this.paypalCheckout = instance;

        var options = {
            env:         this.isTestMode ? 'sandbox' : 'production',
            commit:      (!this.isButton),
            payment:     this.paypalButtonPayment.bind(this),
            onAuthorize: this.paypalButtonOnAuthorize.bind(this),
            onCancel:    this.paypalButtonOnCancel.bind(this),
            onError:     this.triggerError.bind(this),
            style:       this.paypalButtonStyle,
        };

        for (var i in this.paypalButtons) {

            paypal.Button.render(options, this.paypalButtons[i]).then(function () {
               // The PayPal button will be rendered in an html element with the id
               // `paypal-button`. This function will be called when the PayPal button
               // is set up and ready to be used.
            });

            if (this.isButton) {
                this.isInProgress = false;
            }
        }
    },

    /**
     * Create PayPal calback
     */
    createPayPalCallback: function (error, instance)
    {
        if (error) {

            if (!this.isButton) {
                // Regardles of the error create hosted fields
                braintree.hostedFields.create(this.getHostedFieldsOptions(), this.createHostedFieldsCallback.bind(this));
            }

            return this.triggerError(error);
        }

        this.paypal = instance;

        var options = {
            client: this.client,
        };

        // Create a PayPal Checkout component
        braintree.paypalCheckout.create(options, this.createPayPalCheckoutCallback.bind(this));
    },

    /**
     * Create Braintree client instance callback
     */
    createClientCallback: function (error, instance)
    {
        if (error) {
            return this.triggerError(error);
        }

        this.client = instance;

        this.checkout.createClientCallback();

        if (this.isPayPal || this.isButton) {

            var options = {
                client: this.client,
            }; 

            // Create PayPal. Hosted fields are created in callback if necessary 
            braintree.paypal.create(options, this.createPayPalCallback.bind(this));

        } else {

            braintree.hostedFields.create(this.getHostedFieldsOptions(), this.createHostedFieldsCallback.bind(this));
        }
    },

    /**
     * Tokenize card callback
     */
    tokenizeCallback: function(error, payload)
    {
        if (error) {
            return this.triggerError(error);
        }

        this.nonce = payload.nonce;

        this.checkout.tokenizeCallback(payload);

        if (this.is3dSecure && this.secure3d) {

            this.checkout.getCartTotal(this.verifyCard.bind(this));

        } else {

            this.isInProgress = false;
            jQuery(this.formElement).submit();
        }
    },

    /**
     * Form submitter
     */
    submitForm: function(event)
    {
        var allow = true;

        if (this.isInitialized && this.isCurrent(true) && 'undefined' != typeof braintree) {

            // This is one of the Braintree payment methods
        
            if (this.isInProgress) {

                // Form is being submitted, do nothing
                allow = false;
                event.preventDefault();

            } else if (this.nonce == '') {

                if (this.isCurrent(false)) {

                    // This is credit card payment
                    this.isInProgress = true;
                    allow = false;
                    event.preventDefault();
                    this.hostedFields.tokenize(this.tokenizeCallback.bind(this));

                } else {

                    // This is saved card payment
                    if (this.is3dSecureForVault && this.secure3d) {

                        // Process 3-D secure for the vault
                        this.isInProgress = true;
                        allow = false;
                        event.preventDefault();
                        this.checkout.getSavedCardNonce(this.verifyCard.bind(this));
                    }
                }
            }
        }

        return allow;
    },

    /**
     * List of required properties
     */
    required: ['token', 'merchantId', 'nonceSelector', 'formSelector', 'numberSelector', 'cvvSelector', 'dateSelector'],

    /**
     * Init callback
     */
    initCallback: function(options)
    {
        // Copy options
        for (var i in options) {
            this[i] = options[i];
        }

        // Check options
        for (var i in this.required) {
            if (!this[this.required[i]]) {
                return this.triggerError('Empty field: ' + this.required[i]);
            }
        }

        this.nonceElement = document.querySelector(this.nonceSelector);
        this.formElement = document.querySelector(this.formSelector)

        if (!this.isButton) {

            // Details for the hosted fields
            this.fields = {
              'number':         {selector: this.numberSelector, placeholder: this.numberPlaceholder},
              'cvv':            {selector: this.cvvSelector, placeholder: this.cvvPlaceholder},
              'expirationDate': {selector: this.dateSelector, placeholder: this.datePlaceholder},
            };
        }

        var clientOptions = {
            authorization: this.token
        };

        braintree.client.create(clientOptions, this.createClientCallback.bind(this));
    },

    /**
     * Constructor/initializator
     */
    init: function(forceCurrent) 
    {
        isCurrent = this.isCurrent()
            || (forceCurrent == true);

        if (this.isInitialized || this.isInProgress || !isCurrent || 'undefined' == typeof braintree) {
            return;
        }

        this.isInProgress = true;

        this.checkout.init(this.initCallback.bind(this));
    },

    /**
     * Some external methods which shoudld be processed by the store checkout page
     */
    checkout: {
        init:                       function (callback) { callback(); }, // @param callback Callback function 
        triggerError:               function (message) {}, // @param message Error message
        create3DSecureCallback:     function () {}, // @param instance 3-D Secure object instance
        addFrameCallback:           function (iframe) {}, // @param iframe Iframe element
        verifyCardCallback:         function (response) {}, // @param response Braintree response of card verification
        createHostedFieldsCallback: function () {}, 
        createClientCallback:       function () {},
        tokenizeCallback:           function (payload) {}, // @param payload Braintree payload with card tokenization nonce
        getCartTotal:               function (callback) { callback(); }, // @param callback Callback function
        getSavedCardNonce:          function (callback) { callback(); }, // @param callback Callback function
        getPayPalData:              function (callback) { callback(); }, // @param callback Callback function
        continuePayPal:             function (details) {}, // @param details Some details from PayPal
        processInProgress:          function () {},
        isCurrent:                  function () { return false; },
    },
};
