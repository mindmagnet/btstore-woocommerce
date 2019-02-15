jQuery(window).load(function() {

    // Countries
    jQuery('select#woocommerce_allowed_countries, select#woocommerce_ship_to_countries').change(function() {
        if (jQuery(this).val() == "specific") {
            jQuery(this).parent().parent().next('tr').show();
        } else {
            jQuery(this).parent().parent().next('tr').hide();
        }
    }).change();

    jQuery('select#payment_btpay_standard_allowspecific, select#payment_btpay_standard_specificcountry').change(function() {
        if (jQuery(this).val() == "1") {
            jQuery(this).parent().parent().next('tr').show();
        } else {
            jQuery(this).parent().parent().next('tr').hide();
        }
    }).change();

});
