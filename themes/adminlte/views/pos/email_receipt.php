<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?><!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?= $page_title . " " . lang("no") . " " . $inv->id; ?></title>
    <style>
        * { font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.6em; margin: 0; padding: 0; } img { max-width: 100%; } body { -webkit-font-smoothing: antialiased; height: 100%; -webkit-text-size-adjust: none; width: 100% !important; } a { color: #348eda; } .btn-primary { Margin-bottom: 10px; width: auto !important; } .btn-primary td { background-color: #62cb31; border-radius: 3px; font-family: "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif; font-size: 14px; text-align: center; vertical-align: top; } .btn-primary td a { background-color: #62cb31; border: solid 1px #62cb31; border-radius: 3px; border-width: 4px 20px; display: inline-block; color: #ffffff; cursor: pointer; font-weight: bold; line-height: 2; text-decoration: none; } .last { margin-bottom: 0; } .first { margin-top: 0; } .padding { padding: 10px 0; } table.body-wrap { padding: 20px; width: 100%; } table.body-wrap .container { border: 1px solid #e4e5e7; } table.footer-wrap { clear: both !important; width: 100%; } .footer-wrap .container p { color: #666666; font-size: 12px; } table.footer-wrap a { color: #999999; } h1, h2, h3 { color: #111111; font-family: "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif; font-weight: 200; line-height: 1.2em; margin: 10px 0 10px; } h1 { font-size: 36px; } h2 { font-size: 28px; } h3 { font-size: 22px; } p, ul, ol {font-weight: normal;margin-bottom: 10px;} ul li, ol li {margin-left: 5px;list-style-position: inside;} .container { clear: both !important; display: block !important; Margin: 0 auto !important; max-width: 600px !important; } .body-wrap .container { padding: 20px; } .content { display: block; margin: 0 auto; max-width: 600px; } .content table { width: 100%; }
    </style>
</head>

<body bgcolor="#f7f9fa">
<table class="body-wrap" bgcolor="#f7f9fa">
    <tr>
        <td></td>
        <td class="container" bgcolor="#FFFFFF">
            <div class="content">
                <table>
                    <tr>
                        <td style="text-align:center;">
                            <h2>
                                <img src="<?= base_url() . 'assets/uploads/logos/' . $settings->logo; ?>" alt="<?= $settings->logo; ?>">
                            </h2>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="clear:both;height:15px;"></div>
                            <div style="text-align:center;">
                                <img src="<?= base_url('panel/misc/barcode/'.$this->repairer->base64url_encode($inv->reference_no).'/code128/74/0/1'); ?>" alt="<?= $inv->reference_no; ?>" class="bcimg" />
                                <?= $this->repairer->qrcode('link', urlencode(base_url('panel/pos/view/' . $inv->id)), 2, 'H', null, TRUE); ?>
                            </div>
                            <div style="clear:both;height:15px;"></div>
                                <div id="receiptData" style="border:1px solid #DDD; padding:10px; text-align:center;">

                                        <div class="text-center">
                                            <h3 style="text-transform:uppercase;"><?=$settings->title;?></h3>
                                            <?php
                                            echo $settings->address;
                                            echo "<p>";
                                            echo lang("tel") . ": " . $settings->phone . "<br>" . lang("email") . ": " . $settings->invoice_mail;
                                            echo '</p>';
                                            ?>
                                        </div>
                                        <?php
                                        
                                        echo '<div style="text-align:left;padding-bottom:10px;">';
                                        echo "<p>" .lang("date") . ": " . $this->repairer->hrld($inv->date) . "<br>";
                                        echo lang("sale_no_ref") . ": " . $inv->reference_no . "<br>";
                                        if (!empty($inv->return_sale_ref)) {
                                            echo '<p>'.lang("return_ref").': '.$inv->return_sale_ref;
                                            if ($inv->return_id) {
                                                echo ' <a data-target="#myModal2" data-toggle="modal" href="'.base_url('panel/sales/modal_view/'.$inv->return_id).'"><i class="fa fa-external-link no-print"></i></a><br>';
                                            } else {
                                                echo '</p>';
                                            }
                                        }
                                        echo lang("sales_person") . ": " . $created_by->first_name." ".$created_by->last_name . "</p>";
                                        if ($customer) {
                                            echo "<p>";
                                            echo lang("customer") . ": " . ($customer->company && $customer->company != '-' ? $customer->company : $customer->name) . "<br>";
                                            echo '</div>';
                                                echo lang("tel") . ": " . $customer->telephone . "<br>";
                                                echo lang("address") . ": " . $customer->address . ", ";
                                                echo $customer->city ." ".$customer->postal_code."<br>";
                                            echo "</p>";
                                        }else{
                                            echo "<p>";
                                            echo lang("customer") . ": " . ($inv->customer) . "<br>";
                                            echo '</div>';
                                            echo "</p>";

                                        }
                                        
                                        ?>

                                        <div style="clear:both;"></div>
                                        <table width="100%" style="margin:15px 0;">
                                            <tbody>
                                                <?php
                                                $r = 1; $category = 0;
                                                $tax_summary = array();
                                                foreach ($rows as $row) {
                                                    
                                                    echo '<tr><td colspan="2" style="text-align:left;border:0;">#' . $r . ': &nbsp;&nbsp;' . $row->product_name. '<span class="pull-right">' . ($row->tax_code ? '*'.$row->tax_code : '') . '</span></td></tr>';

                                                    echo '<tr><td style="text-align:left;border-bottom:1px solid #DDD;">' . $this->repairer->formatQuantity($row->quantity) . ' x '.$this->repairer->formatMoney($row->unit_price).($row->item_tax != 0 ? ' - '.lang('tax').' <small>('.($row->tax_code).')</small> '.$this->repairer->formatMoney($row->item_tax): '').'</td><td style="text-align:right;border-bottom:1px solid #DDD;">' . $this->repairer->formatMoney($row->subtotal) . '</td></tr>';

                                                    $r++;
                                                }
                                                

                                                ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th style="text-align:left;border-bottom:1px solid #DDD;"><?=lang("total");?></th>
                                                    <th style="text-align:right;border-bottom:1px solid #DDD;"><?=$this->repairer->formatMoney($inv->total + $inv->total_tax);?></th>
                                                </tr>
                                                <?php
                                                
                                                if (true){
                                                    ?>
                                                    <tr>
                                                        <th style="text-align:left;border-bottom:1px solid #DDD;"><?=lang("grand_total");?></th>
                                                        <th style="text-align:right;border-bottom:1px solid #DDD;"><?=$this->repairer->formatMoney($inv->grand_total);?></th>
                                                    </tr>
                                                    <?php
                                                }
                                                if ($inv->paid < ($inv->grand_total)) {
                                                    ?>
                                                    <tr>
                                                        <th style="text-align:left;border-bottom:1px solid #DDD;"><?=lang("paid_amount");?></th>
                                                        <th style="text-align:right;border-bottom:1px solid #DDD;"><?=$this->repairer->formatMoney($inv->paid);?></th>
                                                    </tr>
                                                    <tr>
                                                        <th style="text-align:left;border-bottom:1px solid #DDD;"><?=lang("due_amount");?></th>
                                                        <th style="text-align:right;border-bottom:1px solid #DDD;"><?=$this->repairer->formatMoney(($inv->paid));?></th>
                                                    </tr>
                                                    <?php
                                                } ?>
                                            </tfoot>
                                        </table>
                                        <?php
                                        if ($payments) {
                                            echo '<table class="table table-striped table-condensed" style="margin-bottom: 0"><tbody>';
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
                                                } elseif ($payment->paid_by == 'Cheque' && $payment->cheque_no) {
                                                    echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                                                    echo '<td>' . lang("amount") . ': ' . $this->repairer->formatMoney($payment->pos_paid) . '</td>';
                                                    echo '<td>' . lang("cheque_no") . ': ' . $payment->cheque_no . '</td>';
                                                } elseif ($payment->paid_by == 'other' && $payment->amount) {
                                                    echo '<td>' . lang("paid_by") . ': ' . lang($payment->paid_by) . '</td>';
                                                    echo '<td>' . lang("amount") . ': ' . $this->repairer->formatMoney($payment->pos_paid == 0 ? $payment->amount : $payment->pos_paid)  . '</td>';
                                                    echo $payment->note ? '</tr><td colspan="2">' . lang("payment_note") . ': ' . $payment->note . '</td>' : '';
                                                }
                                                echo '</tr>';
                                            }
                                        }
                                        ?>
                                        <div style="padding-top:10px;">
                                        <?= $inv->note ? '<p class="text-center">' . $this->repairer->decode_html($inv->note) . '</p>' : ''; ?>
                                        </div>
                                    </div>
                                    <div style="clear:both;"></div>
                                </div>
                            </div>
                            <div style="clear:both;height:25px;"></div>
                            <strong><?= $settings->title; ?></strong>
                            <!-- <p><?= base_url(); ?></p> -->
                            <div style="clear:both;height:15px;"></div>
                            <?php if($customer): ?>
                            <p style="border-top:1px solid #CCC;margin-bottom:0;">This email is sent to <?= $customer->company; ?> (<?= $customer->email; ?>).</p>
                            <?php else: ?>
                            <p style="border-top:1px solid #CCC;margin-bottom:0;">This email is sent to (<?= $inv->customer; ?>).</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

               
            </div>

        </td>
        <td></td>
    </tr>
</table>

</body>
</html>
