<?php
//dodanie miejsca i grupy odbioru 
add_action( 'wpo_wcpdf_after_order_data', 'wpo_wcpdf_delivery_date', 10, 2 );
function wpo_wcpdf_delivery_date ($template_type, $order) {
    if ($template_type == 'packing-slip') {
        $document = wcpdf_get_document( $template_type, $order );
        ?>
        <tr class="delivery-date">
            <th>Data i miejsce odbioru:</th>
            <td><?php $document->custom_field('_data_i_grupa'); ?></td>
        </tr>
        <?php
    }
}
