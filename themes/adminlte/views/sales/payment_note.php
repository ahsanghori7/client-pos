<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-content">
    <div class="modal-body print">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i></button>
         <?php if ($settings->logo) { ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $settings->logo; ?>"
                         alt="<?= $settings->logo; ?>">
                </div>
            <?php } ?>
            <div class="well well-sm">
                <div class=" bold">
                    <div class="row">
                         <div class="col-xs-5">
                            <p class="bold">
                                <?= lang("date"); ?>: <?= $this->repairer->hrld($inv->date); ?><br>
                                <?= lang("ref"); ?>: <?= $inv->reference_no; ?><br>
                                <?= lang("payment_status"); ?>: <?= lang($inv->payment_status); ?>
                            </p>
                            </div>
                            <div class="col-xs-7 text-right order_barcodes">
                                <img src="<?= base_url('panel/misc/barcode/'.$this->repairer->base64url_encode($inv->reference_no).'/code128/74/0/1'); ?>" alt="<?= $inv->reference_no; ?>" class="bcimg" />
                                <?= $this->repairer->qrcode('link', urlencode(base_url('panel/sales/view/' . $inv->id)), 2); ?>
                            </div>
                    </div>
                    
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>
        <hr>
        <div class="row">
            <div class="col-sm-12">
                <p style="font-weight:bold;"><?= lang("date"); ?>: <?= $this->repairer->hrsd($payment->date); ?></p>
                <p style="font-weight:bold;"><?= lang("sale").' '.lang('ref'); ?>: <?= $inv->reference_no; ?></p>
                <p style="font-weight:bold;"><?= lang("payment_reference"); ?>: <?= $payment->reference_no; ?></p>
            </div>
        </div>
        <div class="well">
            <table class="table table-borderless" style="margin-bottom:0;">
                <tbody>
                <tr>
                    <td>
                        <strong><?= $payment->type == 'returned' ? lang("payment_returned") : lang("payment_received"); ?></strong>
                    </td>
                    <td class="text-right">
                        <strong class="text-right"><?php echo $this->repairer->formatMoney($payment->amount); ?></strong>
                    </td>
                </tr>
                <tr>
                    <td><strong><?= lang("paid_by"); ?></strong></td>
                    <td class="text-right"><strong class="text-right"><?php echo lang($payment->paid_by);
                            if ( $payment->paid_by == 'CC') {
                                echo ' (' . substr($payment->cc_no, -4) . ')';
                            } elseif ($payment->paid_by == 'Cheque') {
                                echo ' (' . $payment->cheque_no . ')';
                            }
                            ?></strong></td>
                </tr>
                <?php if ($payment->paid_by == 'CC') { ?>
                <tr>
                    <td>
                        <strong><?= lang("name"); ?></strong>
                    </td>
                    <td class="text-right">
                        <strong class="text-right"><?= $payment->cc_holder; ?></strong>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td colspan="2"><?= html_entity_decode($payment->note); ?></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div style="clear: both;"></div>
        <div class="row">
            <div class="col-sm-4 pull-left">
                <p>&nbsp;</p>

                <p>&nbsp;</p>

                <p>&nbsp;</p>

                <p style="border-bottom: 1px solid #666;">&nbsp;</p>

                <p><?= lang("stamp_sign"); ?></p>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
