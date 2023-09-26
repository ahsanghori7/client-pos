<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title><?="Invoice " . lang("no") . ": " . $inv->id;?></title>
    <base href="<?=base_url()?>"/>
    <meta http-equiv="cache-control" content="max-age=0"/>
    <meta http-equiv="cache-control" content="no-cache"/>
    <meta http-equiv="expires" content="0"/>
    <meta http-equiv="pragma" content="no-cache"/>
    <link rel="stylesheet" href="<?= $assets ?>dist/css/custom/bootstrap.min.css">

    <script src="<?= $assets ?>plugins/jquery/jquery.min.js"></script>
    <script src="<?= $assets ?>plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
    <link rel="stylesheet" href="<?= $assets ?>plugins/toastr/toastr.min.css">
    <script src="<?= $assets ?>plugins/toastr/toastr.min.js"></script>

    <link rel="stylesheet" href="<?= $assets ?>dist/css/custom/pos.view.css">

    <!-- <link rel="stylesheet" href="<?=base_url();?>assets/dist/css/theme.css" type="text/css"/> -->

</head>
<body>
    <div id="wrapper">
        <div id="receiptData">
            <div class="no-print">
                <?php
                if ($message) {
                    ?>
                    <div class="alert alert-success">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <?=is_array($message) ? print_r($message, true) : $message;?>
                    </div>
                    <?php
                } ?>
            </div>
            <div id="receipt-data">
                <div class="text-center">
                    <?= '<img width="400px" src="'.base_url('assets/uploads/logos/'.$logo).'" alt="">' ?>
                    <h3 style="text-transform:uppercase;"><?=$biller->company != '-' ? $biller->company : $biller->name;?></h3>
                    <?php
                    echo "<p>" . $settings->address . "<br>" . $settings->invoice_mail . "<br>" . $settings->vat . "<br>" . lang("tel") . ": " . $settings->phone;
                    echo '</p>';
                    ?>
                </div>
               
                <?php
                
                echo "<p>" .lang("date") . ": " . date('d m Y', strtotime($inv->date)) . "<br>";
                echo lang("sale_no_ref") . ": " . $inv->reference_no . "<br>";
                
                echo lang("sales_person") . ": " . $created_by->first_name." ".$created_by->last_name . "</p>";
                echo "<p>";
                echo lang("customer") . ": " . ($customer ? ($customer->company && $customer->company != '-' ? $customer->company : $customer->name) : lang('walk_in')) . "<br>";
                echo "</p>";
                ?>
                <div style="clear:both;"></div>
                <table class="table table-striped table-condensed">
                    <thead>
                        <tr>
                            <th><?= lang('#'); ?></th>
                            <th><?= lang('product_name'); ?></th>
                            <th><?= lang('price'); ?> (<?= lang('quantity'); ?>)</th>
                            <th class="text-right"><?= lang('tax');?></th>
                            <th class="text-right"><?= lang('total');?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $r = 1;
                        foreach ($rows as $row) : ?>
                            <tr>
                                <td><?= $r; ?></td>
                                <td><?= product_name($row->product_name, null); ?></td>
                                <td><?= $this->repairer->formatMoney($row->unit_price); ?> (<?= $this->repairer->formatQuantity($row->quantity); ?>)</td>
                                <td class="text-right"><?= $this->repairer->formatMoney($row->item_tax); ?> (<?= ($row->tax_code ? '*'.$row->tax_code : ''); ?>)</td>
                                <td class="text-right"><?= $this->repairer->formatMoney($row->subtotal); ?></td>
                                
                            </tr>
                       <?php $r++; endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4"><?=lang("tax");?></th>
                            <th class="text-right"><?=$this->repairer->formatMoney(($inv->total_tax));?></th>
                        </tr>
                        <tr>
                            <th colspan="4"><?=lang("total");?></th>
                            <th class="text-right"><?=$this->repairer->formatMoney(($inv->total));?></th>
                        </tr>
                        <tr>
                            <th colspan="4"><?=lang("discount");?></th>
                            <th class="text-right"><?=$this->repairer->formatMoney($inv->total_discount);?></th>
                        </tr>
                        <tr>
                            <th colspan="4"><?=lang("grand_total");?></th>
                            <th class="text-right"><?=$this->repairer->formatMoney($inv->grand_total);?></th>
                        </tr>
                    </tfoot>
                </table>
                <?php
                if ($payments) {
                    echo '<table class="table table-striped table-condensed"><tbody>';
                    foreach ($payments as $payment) {
                        echo '<tr>';
                        if (($payment->paid_by == 'cash') && $payment->pos_paid) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td colspan="2">' . lang("amount") . ': ' . $this->repairer->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid). '</td>';
                            echo '<td>' . lang("change") . ': ' . ($payment->pos_balance > 0 ? $this->repairer->formatMoney($payment->pos_balance) : 0) . '</td>';
                        } elseif (($payment->paid_by == 'CC' || $payment->paid_by == 'ppp') && $payment->cc_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->repairer->formatMoney($payment->pos_paid) . '</td>';
                            echo '<td>' . lang("no") . ': ' . 'xxxx xxxx xxxx ' . substr($payment->cc_no, -4) . '</td>';
                            echo '<td>' . lang("name") . ': ' . $payment->cc_holder . '</td>';
                        } elseif (strtolower($payment->paid_by) == 'cheque' && $payment->cheque_no) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->repairer->formatMoney($payment->pos_paid) . '</td>';
                            echo '<td>' . lang("cheque_no") . ': ' . $payment->cheque_no . '</td>';
                        } elseif ($payment->paid_by == 'other' && $payment->amount) {
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->repairer->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid)  . '</td>';
                            echo $payment->note ? '</tr><td colspan="2">' . lang("payment_note") . ': ' . $payment->note . '</td>' : '';
                        }else{
                            echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                            echo '<td>' . lang("amount") . ': ' . $this->repairer->formatMoney($payment->pos_paid) . '</td>';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                }
                ?>
                <?= $inv->note ? '<p class="text-center">' . $this->repairer->decode_html($inv->note) . '</p>' : ''; ?>
                 <div class="well">
                    <div class="order_barcodes text-center">
                         <?= $this->repairer->barcode($inv->reference_no, 'code128', 74, false); ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div style="clear:both;"></div>
        </div>

        <div id="buttons" style="padding-top:10px; text-transform:uppercase;" class="no-print">
            <hr>
            <?php
            if ($message) {
                ?>
                <div class="alert alert-success">
                    <button data-dismiss="alert" class="close" type="button">×</button>
                    <?=is_array($message) ? print_r($message, true) : $message;?>
                </div>
                <?php
            } ?>
            <button id="print_button" onclick="window.print();" class="btn btn-block btn-primary"><?=lang("print")?></button>
            <a id="back_button" href="<?= base_url('panel/pos'); ?>" class="btn btn-block btn-primary"><?= lang('back_to_pos'); ?></a>
            <a id="email_button" class="btn btn-block btn-primary"><?= lang('email_invoice'); ?></a>
            <div style="clear:both;"></div>
        </div>
    </div>

    <input type="hidden" id="customer_email" value="<?= $customer ? $customer->email : ''; ?>">  
    <input type="hidden" id="invoice_id" value="<?= $inv->id; ?>">  

    <script type="text/javascript" src="<?=base_url();?>panel/misc/js/pos_view"></script>

</body>
</html>
