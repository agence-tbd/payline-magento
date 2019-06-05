function paylineTrySkipPaymentMethod() {
    if (!paylinePaymentSavedTransport) {
        return;
    }

    var paylinePaymentInputNames = [];

    var paylineIsUniquePaymentChoice = true;

    // Retrive all radio groups
    $$('#opc-payment input[type=radio]').each(function (radio) {
        if (paylinePaymentInputNames.indexOf(radio.name)==-1)
            paylinePaymentInputNames.push(radio.name);
        else
            paylineIsUniquePaymentChoice = false;
    });

    // If only one input by group
    if (paylineIsUniquePaymentChoice) {
        // Check all boxes
        paylinePaymentInputNames.each(function (name) {
            $$('#opc-payment input[name="' + name + '"]').first().checked = true;
        });
        // Go to review step
        checkout.setStepResponse(paylinePaymentSavedTransport);
    }
}

var PaylineWidgetWrapper = Class.create();
PaylineWidgetWrapper.prototype = {
    initialize: function(data_token, data_template, save_url, custom_methods, init_quote_total, currency_code, payment_template, data_events) {
        this.dataToken = data_token;
        this.dataTemplate = data_template;
        this.saveUrl = save_url;
        this.paymentTemplate = payment_template;
        this.dataEvents = data_events;

        this.rootTabSelector = 'pmLayout-.*-mycustompm';

        this.initQuoteGrandTotal = init_quote_total;
        this.currencyCode = currency_code;
        this.finalQuoteGrandTotal = 0;
        this.finalPaymentMethods = {};
        this.customPmMethods = [];
        this.allPaymentMethods = {};

        this.widgetInitialized = false;

        this.widgetReady = false;

        this.preparePmMethods(custom_methods);
    },



    setFinalQuoteGrandTotal: function(grantotal)
    {
        this.finalQuoteGrandTotal = grantotal;
    },

    setFinalPaymentMethods: function(methods)
    {
        this.finalPaymentMethods = methods;
    },

    insertWidget: function($widgetAnchor){

        if($widgetAnchor && !$("PaylineWidget")) {

            var widgetAttributes = {"id":"PaylineWidget", "data-auto-init":"false", "class":"pl-custom"};
            $H(this.dataEvents).each(function(pair){
                widgetAttributes[pair.key] = pair.value;
            });

            var $divPaylineWidget = new Element('div', widgetAttributes);

            $widgetAnchor.insert({after: $divPaylineWidget});

            Payline.Api.init(this.dataToken, this.dataTemplate);

            this.widgetInitialized = true;

            this.insertBeforeHtml();
        }
    },

    initPayline: function(noAgreement, displayReview){

        var $checkoutReview = $('checkout-review-load');

        if(!$checkoutReview) {
            console.log('Cannot found DOM anchor for payline.');
            return '';
        }

        if(typeof Payline != 'undefined' && typeof Payline.Api != 'undefined') {

            var elemProgress =  $('payment-progress-opcheckout');
            if (elemProgress) {
                var elemDd = elemProgress.select('dd').first();
                elemProgress.select('dt').each(function (item) {
                    item.addClassName('complete');

                    if(typeof elemDd != 'undefined') {
                        elemDd.remove();
                    }
                    item.insert({after:'<dd class="complete"><table class="checkout-payline-order-review-table data-table">'
                        + $('checkout-review-table').select('tfoot').first().innerHTML
                        + '</table></dd>'}
                    );
                });
                if (!displayReview) {
                    $('checkout-review-table-wrapper').hide();
                } else {
                    $('checkout-review-table-wrapper').show();
                }
            }

            this.insertWidget($checkoutReview);
            
            if(!this.widgetReady) {
                Payline.Api.hide();
            }
        } else {
            console.log('Payline.Api is undefined.');
        }
    },

    insertBeforeHtml: function() {

    },

    insertAfterHtml: function() {

    },

    showWidget: function(){
        if(this.widgetInitialized) {

            $reviewContainer = $('review-buttons-container');
            if($reviewContainer) {
                $('review-buttons-container').hide();
            }

            $$('.magento-payline-custom-html [id*=payment_form]').each(function(elem){
                elem.style.display = '';
            });

            Payline.Api.show();

            if(!this.widgetReady) {
                this.insertAfterHtml();
            }

            this.widgetReady = true;
        } else {
            alert('Payline is not initialized!');
        }
    },

    setPmMethodTabs: function()
    {
        if($$('div[id*="pmLayout-"][class*="pl-card"]').length > 0) {
            $$('div[id*="pmLayout-"][class*="pl-card"]').each(function(divTab){
                var re = new RegExp(this.rootTabSelector+'(\\w+)',"ig");
                var result = re.exec(divTab.id);
                var tabSpan = divTab.select('span.pl-card-logo').first();
                if (tabSpan && result) {
                    tabSpan.setAttribute('style', 'background:none; width:auto !important');

                    if(this.allPaymentMethods[result[1]]) {
                        tabSpan.update(this.allPaymentMethods[result[1]].title);
                    } else {
                        tabSpan.update(result[1]);
                    }

                    var divPayment = divTab.up('div').up('div');
                    if(this.finalPaymentMethods && this.finalPaymentMethods[result[1]]) {
                        divPayment.show();
                    } else {
                        divPayment.hide();
                    }
                }
            }.bind(this));
        } else {
            setTimeout(this.setPmMethodTabs.bind(this), 500);
        }

        $$('[id^="payment_form_"]').invoke('show');
    },

    preparePmMethods: function(custom_methods)
    {
        custom_methods.each(function(payment){
            this.allPaymentMethods[payment.code] = {title:payment.title, checked:false, code:payment.code};
            this.customPmMethods.push({"paymentMethodId":("myCustomPm"+payment.code),
                "html": this.paymentTemplate.evaluate({paymentHtml: payment.html, paymentCode: payment.code})
            });
        }.bind(this));
    },

    getPmMethods: function()
    {
        return this.customPmMethods;
    },

    saveCustomPayment: function(method)
    {
        if (checkout.loadWaiting!=false) return;

        var params = 'paymentmethod='+method;
        if (review.agreementsForm) {
            params += '&'+Form.serialize(review.agreementsForm);
        }
        params.save = true;

        var request = new Ajax.Request(
            this.saveUrl,
            {
                method:'post',
                parameters:params,
                onComplete: review.onComplete,
                onSuccess: review.onSave,
                onFailure: checkout.ajaxFailure.bind(checkout)
            }
        );
    }

}


var PaylineWidgetShortcutWrapper = Class.create();
PaylineWidgetShortcutWrapper.prototype = {
    initialize: function(data_token, address_url, shipping_url, place_url) {
        this.dataToken = data_token;

        this.addressUrl = address_url;
        this.shippingUrl = shipping_url;
        this.placeUrl = place_url;
        this.successUrl = false;
        this.currentBuyerAddress = false;

        this.finalBaseGrandTotal = 0;
        this.loadWaiting = false;

        this.selectorAgreementsForm = 'checkout-agreements';
        this.selectorShippingMethods = 'input[name="shipping_method"][id^="s_method_"]';

        this.overlay = $('shortcut-please-wait').clone(true);
        this.overlay.removeAttribute('id');

        this.buyerString = '';

        if(!$("PaylineWidget")) {
            $('payline-checkout-shortcut-load').insert({after: '<div id="PaylineWidget" data-token="' + this.dataToken + '" data-template="shortcut" data-event-didshowstate="showStateAddressPaymentFunction"></div>'});
        }

        this.onFailure = this.failureCallback.bindAsEventListener(this);
        this.onSaveAddress = this.successSaveAddress.bindAsEventListener(this);
        this.onSaveShippingMethod = this.successSaveShippingMethod.bindAsEventListener(this);
        this.onPlaceOrder = this.successPlaceOrder.bindAsEventListener(this);
    },

    failureCallback: function(transport) {
        console.log(transport.responseJSON, 'failure');
        this.setLoadWaiting(false);
    },

    shortcutSaveAddresses: function(event)
    {
        this.setLoadWaiting(true);

        var buyer = Payline.Api.getBuyerShortcut();
        if(this.buyerString ==  JSON.stringify(buyer)) {
           this.setLoadWaiting(false);
           return ;
        }
        this.buyerString =  JSON.stringify(buyer);

        new Ajax.Request(
            this.addressUrl,
            {
                method: 'post',
                parameters: buyer,
                onComplete: function(transport){
                    this.setLoadWaiting(false);
                }.bind(this),
                onSuccess: this.onSaveAddress,
                onFailure: this.onFailure
            }
        );
    },

    getShippingMethods: function()
    {
        return $$(this.selectorShippingMethods);
    },

    successSaveAddress: function(transport) {
        var response = transport.responseJSON || transport.responseText.evalJSON(true) || {};
        if (response.update_section) {
            $('payline-'+response.update_section.name+'-load').update(response.update_section.html);

            if(this.getShippingMethods().length==1) {
                this.shortcutSaveShippingMethod();
            } else {

                this.getShippingMethods().each(function(element){
                    Event.observe(element, 'click', this.shortcutSaveShippingMethod.bindAsEventListener(this));
                }.bind(this));

            }
        } else {
            this.logError(response);
        }
    },

    shortcutSaveShippingMethod: function(event)
    {
        var element = this.getShippingMethods().first()
        if ('undefined' != typeof event) {
            element = Event.element(event);
        }

        this.setLoadWaiting(true);

        new Ajax.Request(
            this.shippingUrl,
            {
                method: 'post',
                parameters: {'shipping_method':element.value},
                onComplete: function(transport){
                    this.setLoadWaiting(false);
                }.bind(this),
                onSuccess: this.onSaveShippingMethod,
                onFailure: this.onFailure
            }
        );
    },

    successSaveShippingMethod: function(transport) {
        var response = transport.responseJSON || transport.responseText.evalJSON(true) || {};

        if (response.update_section) {
            $('payline-'+response.update_section.name+'-load').update(response.update_section.html);

            this.finalBaseGrandTotal = response.base_grand_total;
        } else {
            this.logError(response);
        }

    },

    shortcutPlaceOrder: function(event)
    {
        this.setLoadWaiting(true);

        var params = '';
        if ($(this.selectorAgreementsForm)) {
            params = Form.serialize($(this.selectorAgreementsForm));
        }
        params.save = true;

        new Ajax.Request(
            this.placeUrl,
            {
                method: 'post',
                parameters: params,
                onComplete: function(transport){
                    //this.setLoadWaiting(false);
                }.bind(this),
                onSuccess: this.onPlaceOrder,
                onFailure: this.onFailure
            }
        );
    },


    successPlaceOrder: function(transport) {

        var response = transport.responseJSON || transport.responseText.evalJSON(true) || {};

        if (response.error) {
            alert(response.error_messages.stripTags().toString());
            this.setLoadWaiting(false);
        }

        if (response.success) {

            if(this.finalBaseGrandTotal) {
                var totalAmount= Math.round(String(this.finalBaseGrandTotal*100));
                var datasPayline = {"payment":{"amount":totalAmount},"order":{"amount":totalAmount}};

                Payline.Api.updateWebpaymentData(Payline.Api.getToken(), datasPayline,function () {
                    Payline.Api.finalizeShortcut();
                });
            } else {
                Payline.Api.finalizeShortcut();
            }

            if (response.redirect) {
                this.successUrl = response.redirect;
            }
        } else {
            this.logError(response);
        }
    },

    logError: function(response) {
        if (response.error !== "undefined" && response.message !== "undefined" && Object.isString(response.message)) {
            alert(response.message.stripTags().toString());
        }
    },

    setLoadWaiting: function(keepDisabled) {

        var containers = $$('.paylineContainer');
        containers.each(function(elem){
            if(!elem.select('.overlay').length) {
                elem.insert({top: this.overlay.show().clone(true)});
            }
        }.bind(this));

        this.loadWaiting = (keepDisabled ? true : false);
        if (this.loadWaiting) {
            containers.invoke('addClassName', 'disabled');
        } else {
            containers.invoke('removeClassName', 'disabled');
        }
    }
};

var PaylineDirectMethod = Class.create();
PaylineDirectMethod.prototype = {
    initialize: function(code, tokenUrl,cryptedKeys,accessKeyRef,tokenReturnURL){
        // Payment code
        this.code = code;
        // Token list by method
        this.tokenUrl=tokenUrl;
        // Internal crypted key
        this.cryptedKeys=cryptedKeys;
        // Payline user key
        this.accessKeyRef=accessKeyRef;
        // Return url for post redirection
        this.tokenReturnURL = tokenReturnURL;
        // Regex to find back real name for data
        this.regexPayment= /^payment\[(.+)\]/;
        // Keep all values
        this.paymentInputs= $H({});
        // Errors code and label
        this.errors = {};
        // Initialize inputs changes to fill paymentInputs
        this.onChangeInputs = this.checkInputChange.bind(this);
        // Check inputs changes
        this.followInputs();

        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },
    // Set user error by code
    setErrors: function(errors){
        this.errors = errors;
    },
    showError: function(errorCode){

        if (this.errors[errorCode]) {
            var errorMsg = 'Error ' + errorCode + ': "' + this.errors[errorCode] + '"';
        } else {
            var errorMsg = 'Unexpected error: ' + errorCode;
        }

        alert(errorMsg);
    },
    // Retrieve real name from input name
    getPaymentInputName: function(input)
    {
        matchPayment = this.regexPayment.exec(input.name);
        if (matchPayment) {
            return matchPayment[1];
        } else {
            return false;
        }
    },
    // Init observe on payments inputs
    followInputs: function (container) {

        $("payment_form_"+this.code).select('input', 'select').each(function(input){
            inputName= this.getPaymentInputName(input);
            if (inputName) {
                input.observe('change', this.onChangeInputs );
            }
        }.bind(this));
    },
    // Keep the paymentInputs up to date
    checkInputChange: function (evt) {
        var input = Event.element(evt);

        inputName= this.getPaymentInputName(input);
        if (inputName) {
            this.paymentInputs.set(inputName, input.value);
        }
    },
    // Prepare data to send to payline
    save: function(){
        var isPayline = false;
        $$('input[name=payment[method]]').each(function(elem){
            if(elem.checked && elem.getAttribute('value')==this.code) {
                isPayline = true;
            }
        }.bind(this));

        // Check if we have some work to do
        if (!isPayline) {
            payment.save();
        } else {
            if (checkout.loadWaiting!=false) return;
            var validator = new Validation(payment.form);
            if (validator.validate()) {
                checkout.setLoadWaiting('payment');
                var requestParameters= {
                    data: this.cryptedKeys[this.paymentInputs.get('cc_type')],
                    accessKeyRef: this.accessKeyRef,
                    cardNumber:         this.paymentInputs.get('cc_number'),
                    cardExpirationDate: this.paymentInputs.get('cc_exp_month') + this.paymentInputs.get('cc_exp_year'),
                    cardCvx:            this.paymentInputs.get('cc_cid')
                };

                if(this.canUseCors()) {
                    this.saveWithAjax(requestParameters);
                } else {
                    this.saveWithPost(requestParameters);
                }
            }
        }
    },

    resetLoadWaiting: function(){
        checkout.setLoadWaiting(false);
    },

    // Test browser capability to make cross domain calls
    canUseCors: function()
    {
        // Cors detection is dactivated by default
        return false;

        var cors = false;
        if ('withCredentials' in new XMLHttpRequest()) {
            // Supports cross-domain requests
            cors = true;
        }
        else if(typeof XDomainRequest !== "undefined"){
            // Use IE-specific "CORS" code with XDR but we know IE capabilities :-(
            if(Prototype.Browser.IE) {
                var ua = navigator.userAgent;
                var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
                if (re.exec(ua) != null) {
                    rv = parseFloat( RegExp.$1 );
                    // Problem detected on IE8
                    if(rv > 8.0 ) {
                        cors = true;
                    }
                }
            }
        }
        return cors;
    },
    // Call payline to get the token and then go to review
    saveWithAjax: function (requestParameters)
    {
        var paramArray = new Array();
        $H(requestParameters).each(function(pair){
            paramArray.push(pair.key + '=' + pair.value);
        });

        var request = new Ajax.Request(this.tokenUrl, {
            method: 'post',
            // Using postBody avoid to use encodeURIComponent with parameters
            postBody: paramArray.join('&'),
            contentType: 'application/x-www-form-urlencoded',
            onCreate: function(response) { // here comes the fix
                // request.transport.setRequestHeader = Prototype.emptyFunction;
                if (response.request.isSameOrigin()) {
                    return;
                }

                var t = response.transport;
                t.setRequestHeader = t.setRequestHeader.wrap(function(original, k, v) {
                    if (/^(accept|accept-language|content-language)$/i.test(k))
                        return original(k, v);
                    if (/^content-type$/i.test(k) && /^(application\/x-www-form-urlencoded|multipart\/form-data|text\/plain)(;.+)?$/i.test(v))
                        return original(k, v);
                    return;
                });
            },
            onComplete: this.onComplete,
            onSuccess: function(transport) {
                response={};
                if(transport.responseJSON) {
                    response = transport.responseJSON;
                } else if (transport.responseText)  {
                    result = transport.responseText.split('=');
                    if(result) {
                        response[result[0]] = result[1];
                    }
                } else {
                    response = {errorCode:1, message:'No result'};
                }

                if(typeof response.data!=='undefined') {
                    this.saveStep(response.data);
                } else if (response.errorCode) {
                    this.showError(response.errorCode)
                } else {
                    this.showError('ajax_call');
                }
            }.bind(this),
            onFailure: function() {
                alert('Failed to call secure server');
            }
        });
    },
    saveWithPost: function (requestParameters)
    {
        requestParameters.returnURL = this.tokenReturnURL;

        var frameId = 'output_token_response';
        var form = new Element('form', { id: 'form_post_token', action: this.tokenUrl, method: 'post',target:frameId });
        $H(requestParameters).each(function(input){
            var formInput = new Element('input', { 'type': 'hidden', 'name':input.key,  'value':input.value});
            form.insert({top:formInput});
        });

        var iframe = new Element('iframe', { id: frameId,name:frameId, style:'display:none', width:0, height:0 });

        // Old school event attachement to prevent IE8 bugs
        if(iframe.attachEvent) {
            iframe.attachEvent('onload', this.postIframeLoad.bind(this));
        } else {
            iframe.onload = this.postIframeLoad.bind(this);
        }

        document.body.appendChild(iframe);

        iframe.insert({after:form});
        form.submit();
    },

    postIframeLoad: function(event)
    {
        var iframe = Event.element(event);
        var idoc= iframe.contentDocument || iframe.contentWindow.document;
        var parWindow = idoc.defaultView || idoc.parentWindow;

        // Prevent check when onload is fired when iframe added to dom
        // have to wait for submit
        if(parWindow.document.body.innerHTML) {
            var regexToken= /^(.+)=(.+)$/;
            var matchResponse = regexToken.exec(parWindow.document.body.innerHTML);
            if(matchResponse && matchResponse[1]=='data') {
                this.saveStep(matchResponse[2]);
            } else if (matchResponse && matchResponse[1]=='errorCode' && this.errors[matchResponse[2]]) {
                this.showError(matchResponse[2]);
            } else {
                this.showError('frame_content');
            }
            iframe.remove();
            this.resetLoadWaiting();
        }
    },

    // Bypass the payment save method
    saveStep: function(token)
    {
        var saveParameters = {};
        saveParameters['payment[method]']=  this.code;
        saveParameters['payment[card_token_pan]']=  token;
        saveParameters['payment[subscribe_wallet]']=  this.paymentInputs.get('subscribe_wallet');
        saveParameters['payment[cc_type]']=  this.paymentInputs.get('cc_type');
        saveParameters['payment[cc_last4]']=  $(this.code + '_cc_number').value.substr(-4);
        saveParameters['payment[cc_exp_month]']=  this.paymentInputs.get('cc_exp_month');
        saveParameters['payment[cc_exp_year]']=  this.paymentInputs.get('cc_exp_year');
        saveParameters['payment[cc_cid]']=  this.paymentInputs.get('cc_cid');
        saveParameters['payment[assign_session]']=  1;
        // Manual post data to avoid posting extra data by payment class
        new Ajax.Request(payment.saveUrl, {
            method:'post',
            parameters: saveParameters,
            onComplete:  function(transport){
                this.resetForm(token);
                payment.nextStep(transport);
            }.bind(this),
            onSuccess:  function(){
                checkout.setLoadWaiting(false);
            },
            onFailure: checkout.ajaxFailure.bind(checkout)
        });
    },
    // Prevent review to post data
    resetForm: function(token)
    {
//        ['cc_number', 'expiration', 'expiration_yr', 'cc_cid', 'card_token_pan'].each(function(el) {
        ['card_token_pan', 'cc_last4', 'cc_number', 'expiration', 'expiration_yr'].each(function(el) {
            element = $(this.code + '_' + el);
            if (element) {
                switch(el)
                {
                    case 'card_token_pan':
                        element.setValue(token);
                        break;
                    default:
                        element.clear();
                }
            }
        }.bind(this));
        // Then unselect cards
        $$('input[name=payment[cc_type]]').invoke('setValue', false);
    }
}
