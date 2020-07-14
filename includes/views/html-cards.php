<div id="success_msg" class="success_msg hide"></div>
<div id="error_msg" class="error_msg hide"></div>
<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table" id="cards">
    <thead>
        <tr style="text-align:center"; >
            <?php foreach ($columns as $column_id => $column_name) : ?>
                <th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr($column_id); ?>"><span class="nobr"><?php echo esc_html($column_name); ?></span></th>
            <?php endforeach; ?>
        </tr>
    </thead>

    <tbody style="text-align:center">
        <?php
        if ($cards) {
            foreach ($cards as $card) {
                ?>
                <tr class="woocommerce-orders-table__row order" id="<?php echo $card['id']; ?>">                    
                    <td class="woocommerce-orders-table__cell ">
                        <?php echo $card['last4'] ?>
                    </td>
                    <td class="woocommerce-orders-table__cell ">
                        <?php echo $card['brand'] ?>
                    </td>
                    <td class="woocommerce-orders-table__cell ">
                        <input type="button" class="woocommerce-Button button" name="remove_card" onclick="delcard('<?php echo $card['id'];?>')"
                               value=" <?php _e('Remove Card', 'woocommerce-paggi'); ?>">
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr class="woocommerce-orders-table__row order">                    
                <td class="woocommerce-orders-table__cell" style="text-align: center" colspan="100%">
                    <?php _e('No cards', 'woocommerce-paggi'); ?>
                </td>
            </tr>
        <?php }
        ?>
        <tr class="woocommerce-orders-table__row order hide" id="nocards">                    
            <td class="woocommerce-orders-table__cell" style="text-align: center" colspan="100%">
                <?php _e('No cards', 'woocommerce-paggi'); ?>
            </td>
        </tr>
    </tbody>
</table>
<input type="button" class="woocommerce-Button button" name="add_card" onclick="addcard()" value=" <?php _e('New Card', 'woocommerce-paggi'); ?>">
<br/>
<div class="ccdiv hide" id="ccdiv">
    <form id="card_register" method="POST">
        <div class="row">
            <div class="col-md-6">
                <div id="card-wrapper"></div><br/>
                <div class="row" >
                    <input placeholder="<?php _e('Card number', 'woocommerce-paggi'); ?> " type="tel" name = "cc_number"id="cc_number" class="cc required" size="20" >
                </div>
                <div class="row">
                    <input placeholder="<?php _e('Full name', 'woocommerce-paggi'); ?> " type="text" name = "cc_name" id="cc_name" class="cc required" size="20">
                </div>
                <div class="row">
                    <input placeholder="<?php _e('MM/YY', 'woocommerce-paggi'); ?> " type="tel" name = "cc_expiry" id="cc_expiry" class="cc required" size="10">
                </div>
                <div class="row">
                    <input placeholder="<?php _e('CVC', 'woocommerce-paggi'); ?> " type="tel" name = "cc_cvc" id="cc_cvc" class="cc required" size="10">
                </div>
                <div class="row">
                    <input placeholder="<?php _e('Documento', 'woocommerce-paggi'); ?> " type="tel" name = "cc_document" id="cc_document" class="cc required" size="10">
                </div>
                <div class="row">
                    <input id="card_type" name="card_type" type="hidden">
                </div>
            </div>
            <input type="submit" class="submit" value="<?php _e('Register Card', 'woocommerce-paggi'); ?>" />
        </div>
    </form>
</div>
<div class="progressbar">
</div>
<script>
    jQuery(document).ready(function ($) {
        var card = new Card({
            form: '#card_register',
            container: '#card-wrapper',
            formSelectors: {
                numberInput: '#cc_number',
                expiryInput: '#cc_expiry',
                cvcInput: '#cc_cvc',
                nameInput: '#cc_name',
                documentInput: '#cc_document'
            }
        });
        jQuery('#cc_number').validateCreditCard(function (result) {
            if (result && result.hasOwnProperty('card_type') && result.card_type && result.card_type.hasOwnProperty('name')) {
                $('#card_type').val(result.card_type.name);
            } else {
                $('#card_type').val('');
            }
        });
    });
    function addcard() {
        jQuery('#ccdiv').removeClass('hide');
    }
    function delcard($id) {
        jQuery(".progressbar").show();
        $id_class = '#' + $id;
        jQuery.ajax({
            url: wpurl.siteurl + '/wp-admin/admin-ajax.php',
            type: "POST",
            data: {'action': 'delcard', 'data': $id},
            dataType: "json",
            success: function (response) {
                jQuery(".progressbar").hide();
                if (response.code == '204') {
                    jQuery('#error_msg').addClass('hide');
                    jQuery('#success_msg').removeClass('hide').html(response.message);
                    jQuery($id_class).addClass('hide');
                    $tot = jQuery('#cards tbody tr').length;
                    $hid = jQuery('#cards tbody tr.hide').length;
                    if ($tot === $hid) {
                        jQuery('#nocards').removeClass('hide')
                    }
                } else {
                    jQuery('#success_msg').addClass('hide');
                    jQuery('#error_msg').removeClass('hide').html(response.message);
                }
            },
            error: function () {
                jQuery(".progressbar").hide();
                jQuery('#success_msg').addClass('hide');
                jQuery('#error_msg').removeClass('hide').html("<?php _e('An error has occurred in request. Try Again', 'woocommerce-paggi');?>");
            }
        });
    }
</script>