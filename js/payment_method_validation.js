jQuery(document).ready(function($){
    jQuery('#post').submit(function(){
        // Validate Stuff
        var pm = document.getElementById('_payment_method').value;
        var status = document.getElementById('order_status').value;

        if (pm == "") {
            if (status == "wc-processing" || status == "wc-completed") {
                alert('ERROR: "Processing" and "Completed" orders must have a payment method set. \n\nPlease choose a payment method and try again. Manually entered orders will either be "Bank Transfer, Check, or Purchase Order" or "Request a Quote". For $0 warranty orders, choose "Bank Transfer, Check, or Purchase Order".');
                return false;
            }
        }
        return true;
    });
});

