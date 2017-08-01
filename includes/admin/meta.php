<div id="success_msg" class="success_msg hide"></div>
<div id="error_msg" class="error_msg hide"></div>
<?php if (isset($id)) { ?>
<input type="button" class="woocommerce-Button button" name="cancel_payment" id="cancel_payment"onclick="deltransaction('<?php echo $id . '\' ,\' ' . $order->id; ?>')" 
           value=" <?php _e('Cancel payment', 'woocommerce-paggi'); ?>">
       <?php } ?>
<div class="progressbar">
</div>

<script>
    function deltransaction($id, $order_id) {
        jQuery(".progressbar").show();
        $id_class = '#' + $id;
        jQuery.ajax({
            url: wpurl.siteurl + '/wp-admin/admin-ajax.php',
            type: "POST",
            data: {'action': 'cancelregularpayment', 'data': $id, 'order_id': $order_id},
            dataType: "json",
            success: function (response) {
                jQuery(".progressbar").hide();
                if (response.code == '200') {
                    jQuery('#error_msg').addClass('hide');
                    jQuery('#success_msg').removeClass('hide').html(response.message);
                    jQuery('#cancel_payment').addClass('hide');
                } else {
                    jQuery('#success_msg').addClass('hide');
                    jQuery('#error_msg').removeClass('hide').html(response.message);
                }
            },
            error: function () {
                jQuery(".progressbar").hide();
                jQuery('#success_msg').addClass('hide');
                jQuery('#error_msg').removeClass('hide').html("<?php _e('An error has occurred in request. Try Again', 'woocommerce-paggi'); ?>");
            }
        });
    }
</script>

