<?php
/**
 * Credit Card - Checkout form.
 *
 */
if (!defined('ABSPATH')) {
    exit;
}
if ($cards) {
    ?>
    <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table" id="cards">
        <thead>
            <tr>
                <?php foreach ($columns as $column_id => $column_name) : ?>
                    <th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr($column_id); ?>"><span class="nobr"><?php echo esc_html($column_name); ?></span></th>
                <?php endforeach; ?>
            </tr>
        </thead>

        <tbody>
            <?php
            foreach ($cards as $card) {
                ?>
                <tr class="woocommerce-orders-table__row order" id="<?php echo $card['id']; ?>">                    
                    <td class="woocommerce-orders-table__cell ">
                        <?php echo $card['name'] ?>
                    </td>
                    <td class="woocommerce-orders-table__cell ">
                        <?php echo $card['expires'] ?>
                    </td>
                    <td class="woocommerce-orders-table__cell ">
                        <?php echo $card['brand'] ?>
                    </td>
                    <td class="woocommerce-orders-table__cell ">
                        <?php echo $card['last4'] ?>
                    </td>
                    <td class="woocommerce-orders-table__cell ">
                        <input name="card_id" type="radio" id="card_id_<?php echo $card['id'] ?>" value="<?php echo $card['id'] ?>">
                        <label for="card_id_<?php echo $card['id'] ?>"><?php _e('Use this Card', 'woocommerce-paggi'); ?></label>

                    </td>
                </tr>
            <?php } ?>
            <tr class="woocommerce-orders-table__row order" id="">                    
                <td class="woocommerce-orders-table__cell ">

                </td>
                <td class="woocommerce-orders-table__cell ">

                </td>
                <td class="woocommerce-orders-table__cell ">

                </td>
                <td class="woocommerce-orders-table__cell ">

                </td>
                <td class="woocommerce-orders-table__cell ">
                    <input name="card_id" type="radio" id="card_id" value="new">
                    <label for="card_id"><?php _e('New Card', 'woocommerce-paggi'); ?></label>

                </td>
            </tr>
        </tbody>
    </table>
<?php }
?>
<br/>
<div id="card-wrapper"></div><br/>
<p class="form-row form-row-wide">
    <label for="cc_number"><?php esc_html_e('Card number', 'woocommerce-paggi'); ?> <span class="required">*</span></label>
    <input placeholder="<?php _e('Card number', 'woocommerce-paggi'); ?> " type="tel" name = "cc_number"id="cc_number" class="cc required" size="20" >
</p>
<p class="form-row form-row-wide">
    <label for="cc_name"><?php esc_html_e('Full name', 'woocommerce-paggi'); ?> <span class="required">*</span></label>
    <input placeholder="<?php _e('Full name', 'woocommerce-paggi'); ?> " type="text" name = "cc_name" id="cc_name" class="cc required" size="20">
</p>
<p class="form-row form-row-wide">
    <label for="cc_expiry"><?php esc_html_e('Expires', 'woocommerce-paggi'); ?> <span class="required">*</span></label>

    <input placeholder="<?php _e('MM/YY', 'woocommerce-paggi'); ?> " type="tel" name = "cc_expiry" id="cc_expiry" class="cc required" size="10">

    <label for="cc_cvc"><?php esc_html_e('CVC', 'woocommerce-paggi'); ?> <span class="required">*</span></label>

    <input placeholder="<?php _e('CVC', 'woocommerce-paggi'); ?> " type="tel" name = "cc_cvc" id="cc_cvc" class="cc required" size="10">

    <input id="card_type" name="card_type" type="hidden">
    <input name="tot" type="hidden" value="<?php echo $cart_total; ?>">
</p>
<div class="clear"></div>
<p class="form-row form-row-wide">
    <label for="installments"><?php esc_html_e('Installments', 'woocommerce-paggi'); ?> <span class="required">*</span></label>
    <select name="installments" id="paggi-installments" class="cc required">
        <?php
        foreach ($installments as $number => $installment) :
            ?>
            <option value="<?php echo $number; ?>"><?php printf(esc_html__('%1$dx of %2$s', 'woocommerce-paggi'), absint($number), $installment); ?></option>
        <?php endforeach; ?>
    </select>
</p>
<script>
    jQuery(document).ready(function ($) {
        if (jQuery('form[name="checkout"]').length) {
            var card = new Card({
                form: 'form[name="checkout"]',
                container: '#card-wrapper',
                formSelectors: {
                    numberInput: '#cc_number',
                    expiryInput: '#cc_expiry',
                    cvcInput: '#cc_cvc',
                    nameInput: '#cc_name'
                }
            });
        } else {
            var card = new Card({
                form: 'form',
                container: '#card-wrapper',
                formSelectors: {
                    numberInput: '#cc_number',
                    expiryInput: '#cc_expiry',
                    cvcInput: '#cc_cvc',
                    nameInput: '#cc_name'
                }
            });
        }

        jQuery('#cc_number').validateCreditCard(function (result) {
            if (result && result.hasOwnProperty('card_type') && result.card_type && result.card_type.hasOwnProperty('name')) {
                $('#card_type').val(result.card_type.name);
            } else {
                $('#card_type').val('');
            }
        });
    });

</script>
