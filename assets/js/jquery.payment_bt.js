jQuery( function( $ ) {
    'use strict';

    /**
     * Object to handle Stripe payment forms.
     */
    var wc_btstandard_form = {

        /**
         * Initialize e handlers and UI state.
         */
        init: function( form ) {
            this.form          = form;
            this.stripe_submit = false;

            $( this.form )
            // We need to bind directly to the click (and not checkout_place_order_stripe) to avoid popup blockers
            // especially on mobile devices (like on Chrome for iOS) from blocking StripeCheckout.open from opening a tab
                .on( 'click', '#place_order', this.onSubmit );

                // WooCommerce lets us return a false on checkout_place_order_{gateway} to keep the form from submitting
                // .on( 'submit checkout_place_order_stripe' );

            $( document.body ).on( 'checkout_error', this.resetModal );
        },

        isBtStandardChosen: function() {
            return $( '#payment_method_mindmagnet_btpay_standard' ).is( ':checked' );
        },

        isStripeModalNeeded: function( e ) {


            // Don't affect submission if modal is not needed.
            if ( ! wc_btstandard_form.isBtStandardChosen() ) {
                return false;
            }

            // Don't open modal if required fields are not complete
            if ( $( 'input#terms' ).length === 1 && $( 'input#terms:checked' ).length === 0 ) {
                return false;
            }

            if ( $( '#createaccount' ).is( ':checked' ) && $( '#account_password' ).length && $( '#account_password' ).val() === '' ) {
                return false;
            }
            var $required_inputs;
            // check to see if we need to validate shipping address
            if ( $( '#ship-to-different-address-checkbox' ).is( ':checked' ) ) {
                $required_inputs = $( '.woocommerce-billing-fields .validate-required, .woocommerce-shipping-fields .validate-required' );
            } else {
                $required_inputs = $( '.woocommerce-billing-fields .validate-required' );
            }

            if ( $required_inputs.length ) {
                var required_error = false;

                $required_inputs.each( function() {
                    if ( $( this ).find( 'input.input-text, select' ).not( $( '#account_password, #account_username' ) ).val() === '' ) {
                        required_error = true;
                    }

                    var emailField = $( this ).find( '#billing_email' );

                    if ( emailField.length ) {
                        var re = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

                        if ( ! re.test( emailField.val() ) ) {
                            required_error = true;
                        }
                    }
                });

                if ( required_error ) {
                    return false;
                }
            }

            return true;
        },

        block: function() {
            wc_btstandard_form.form.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        },

        unblock: function() {
            wc_btstandard_form.form.unblock();
        },

        onClose: function() {
            wc_btstandard_form.unblock();
        },

        onSubmit: function( e ) {
            // var string = '<div style="margin: 0 auto;  min-height: 450px; text-align: center;"><p>Tranzactie in curs ...</p><div id="payment_form_block" style="display:block !important; border:0 !important; margin:0 !important; padding:0 !important; font-size:0 !important; line-height:0 !important; width:0 !important; height:0 !important; overflow:hidden !important;"><form id="btpay" action="https://www.activare3dsecure.ro/teste3d/cgi-bin/" method="post"><input type="hidden" name="AMOUNT" value="120.00" /><input type="hidden" name="CURRENCY" value="EUR" /><input type="hidden" name="ORDER" value="1000518" /><input type="hidden" name="DESC" value="518" /><input type="hidden" name="MERCH_NAME" value="AS. TRANS. SCHOOL" /><input type="hidden" name="MERCH_URL" value="http://www.nibs2018.com" /><input type="hidden" name="MERCHANT" value="000000060001276" /><input type="hidden" name="TERMINAL" value="60001276" /><input type="hidden" name="EMAIL" value="andra@citytoursevents.com" /><input type="hidden" name="TRTYPE" value="0" /><input type="hidden" name="COUNTRY" value="" /><input type="hidden" name="MERCH_GMT" value="" /><input type="hidden" name="TIMESTAMP" value="20180108131308" /><input type="hidden" name="NONCE" value="15mzW0OZ558UvHT1oz0j2gp4dCq24xOR" /><input type="hidden" name="BACKREF" value="http://www.nibs2018.com/checkout/order-received/518/wp-content/plugins/woocommerce-mindmagnet-ebtpay..." /><input type="hidden" name="P_SIGN" value="5D6239591DB939F92A9ECF986AA9F9CA167009A8" /><input type="submit" value="Executa plata" /></form></div><script>//document.getElementById("btpay").submit();</script></div>';
            // $('#page').append(string);
            // var frm = wc_btstandard_form.form;
            // frm.submit(function(e){
            //     e.preventDefault();
            //     $.ajax({
            //         type: frm.attr('method'),
            //         url: frm.attr('action'),
            //         data: frm.serialize(),
            //         success: function (data) {
            //             console.log('Submission was successful.');
            //             console.log(data);
            //
            //         },
            //         error: function (data) {
            //             console.log('An error occurred.');
            //             console.log(data);
            //         }
            //     });
            // });
            // if ( wc_btstandard_form.isStripeModalNeeded() ) {
            //     e.preventDefault();
            //
            //     // Capture submittal and open stripecheckout
            //     var $form = wc_btstandard_form.form,
            //         $data = $( '#stripe-payment-data' ),
            //         token = $form.find( 'input.stripe_token' );
            //
            //     token.val( '' );
            //
            //     var token_action = function( res ) {
            //         $form.find( 'input.stripe_token' ).remove();
            //         $form.append( '<input type="hidden" class="stripe_token" name="stripe_token" value="' + res.id + '"/>' );
            //         wc_btstandard_form.stripe_submit = true;
            //         $form.submit();
            //     };
            //
            //     var string = '<div style="margin: 0 auto;  min-height: 450px; text-align: center;"><p>Tranzactie in curs ...</p><div id="payment_form_block" style="display:block !important; border:0 !important; margin:0 !important; padding:0 !important; font-size:0 !important; line-height:0 !important; width:0 !important; height:0 !important; overflow:hidden !important;"><form id="btpay" action="https://www.activare3dsecure.ro/teste3d/cgi-bin/" method="post"><input type="hidden" name="AMOUNT" value="120.00" /><input type="hidden" name="CURRENCY" value="EUR" /><input type="hidden" name="ORDER" value="1000518" /><input type="hidden" name="DESC" value="518" /><input type="hidden" name="MERCH_NAME" value="AS. TRANS. SCHOOL" /><input type="hidden" name="MERCH_URL" value="http://www.nibs2018.com" /><input type="hidden" name="MERCHANT" value="000000060001276" /><input type="hidden" name="TERMINAL" value="60001276" /><input type="hidden" name="EMAIL" value="andra@citytoursevents.com" /><input type="hidden" name="TRTYPE" value="0" /><input type="hidden" name="COUNTRY" value="" /><input type="hidden" name="MERCH_GMT" value="" /><input type="hidden" name="TIMESTAMP" value="20180108131308" /><input type="hidden" name="NONCE" value="15mzW0OZ558UvHT1oz0j2gp4dCq24xOR" /><input type="hidden" name="BACKREF" value="http://www.nibs2018.com/checkout/order-received/518/wp-content/plugins/woocommerce-mindmagnet-ebtpay..." /><input type="hidden" name="P_SIGN" value="5D6239591DB939F92A9ECF986AA9F9CA167009A8" /><input type="submit" value="Executa plata" /></form></div><script>//document.getElementById("btpay").submit();</script></div>';
            //     $('body').append(string);
            //     // document.getElementById("btpay").submit();
            //     // // window.open(string);
            //     // StripeCheckout.open({
            //     //     key               : wc_stripe_params.key,
            //     //     billingAddress    : 'yes' === wc_stripe_params.stripe_checkout_require_billing_address,
            //     //     amount            : $data.data( 'amount' ),
            //     //     name              : $data.data( 'name' ),
            //     //     description       : $data.data( 'description' ),
            //     //     currency          : $data.data( 'currency' ),
            //     //     image             : $data.data( 'image' ),
            //     //     bitcoin           : $data.data( 'bitcoin' ),
            //     //     locale            : $data.data( 'locale' ),
            //     //     email             : $( '#billing_email' ).val() || $data.data( 'email' ),
            //     //     panelLabel        : $data.data( 'panel-label' ),
            //     //     allowRememberMe   : $data.data( 'allow-remember-me' ),
            //     //     token             : token_action,
            //     //     closed            : wc_btstandard_form.onClose()
            //     // });
            //
            //     return false;
            // }

            // return true;
        },

        resetModal: function() {
            wc_btstandard_form.form.find( 'input.stripe_token' ).remove();
            wc_btstandard_form.stripe_submit = false;
        }
    };

    wc_btstandard_form.init( $( "form.checkout, form#order_review, form#add_payment_method" ) );
} );
