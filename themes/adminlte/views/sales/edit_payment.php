<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
        </button>
        <h4 class="modal-title" id="myModalLabel"><?php echo lang('edit_payment'); ?></h4>
    </div>
    <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
    echo form_open_multipart("panel/sales/edit_payment/" . $payment->id, $attrib); ?>
    <div class="modal-body">
        <p><?= lang('enter_info'); ?></p>
      
        <input type="hidden" value="<?php echo $payment->sale_id; ?>" name="sale_id"/>
        <div class="clearfix"></div>
        <div id="payments">
            <div class="well well-sm well_1">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="payment">
                                <div class="form-group">
                                    <?= lang("amount", "amount_1"); ?>
                                    <input name="amount-paid"
                                           value="<?= $this->repairer->formatDecimal($payment->amount); ?>" type="text"
                                           id="amount_1" class="pa form-control kb-pad amount"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <?= lang("paying_by", "paid_by_1"); ?>
                                <select name="paid_by" id="paid_by_1" class="form-control paid_by">
                                    <?= $this->repairer->paid_opts($payment->paid_by); ?>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="clearfix"></div>
                    <div class="pcc_1" style="display:none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input name="pcc_no" value="<?= $payment->cc_no; ?>" type="text" id="pcc_no_1"
                                           class="form-control" placeholder="<?= lang('cc_no') ?>"/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">

                                    <input name="pcc_holder" value="<?= $payment->cc_holder; ?>" type="text"
                                           id="pcc_holder_1" class="form-control"
                                           placeholder="<?= lang('cc_holder') ?>"/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <select name="pcc_type" id="pcc_type_1" class="form-control pcc_type"
                                            placeholder="<?= lang('card_type') ?>">
                                        <option
                                            value="Visa"<?= $payment->cc_type == 'Visa' ? ' checked="checcked"' : '' ?>><?= lang("Visa"); ?></option>
                                        <option
                                            value="MasterCard"<?= $payment->cc_type == 'MasterCard' ? ' checked="checcked"' : '' ?>><?= lang("MasterCard"); ?></option>
                                        <option
                                            value="Amex"<?= $payment->cc_type == 'Amex' ? ' checked="checcked"' : '' ?>><?= lang("Amex"); ?></option>
                                        <option
                                            value="Discover"<?= $payment->cc_type == 'Discover' ? ' checked="checcked"' : '' ?>><?= lang("Discover"); ?></option>
                                    </select>
                                    <!-- <input type="text" id="pcc_type_1" class="form-control" placeholder="<?= lang('card_type') ?>" />-->
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input name="pcc_month" value="<?= $payment->cc_month; ?>" type="text"
                                           id="pcc_month_1" class="form-control"
                                           placeholder="<?= lang('month') ?>"/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">

                                    <input name="pcc_year" value="<?= $payment->cc_year; ?>" type="text"
                                           id="pcc_year_1" class="form-control" placeholder="<?= lang('year') ?>"/>
                                </div>
                            </div>
                            <!--<div class="col-md-3">
                                <div class="form-group">
                                    <input name="pcc_ccv" type="text" id="pcc_cvv2_1" class="form-control" placeholder="<?= lang('cvv2') ?>" />
                                </div>
                            </div>-->
                        </div>
                    </div>
                    <div class="pcheque_1" style="display:none;">
                        <div class="form-group"><?= lang("cheque_no", "cheque_no_1"); ?>
                            <input name="cheque_no" value="<?= $payment->cheque_no; ?>" type="text" id="cheque_no_1"
                                   class="form-control cheque_no"/>
                        </div>
                    </div>
                    <div class="form-group v_1" style="display: none;">
                            <?=lang("voucher_no", "voucher_no_1");?>
                            <input name="voucher_no" type="text" id="voucher_no_1"
                                   class="pa form-control voucher_no"/>
                            <div id="v_details_1"></div>
                        </div>
                </div>
                <div class="clearfix"></div>
            </div>

        </div>
        <div class="form-group">
            <?= lang("note", "note"); ?>
            <?php
            $options = array(
                'name' => 'note',
                'rows' => '1',
                'value'=> set_value('note'),
                'class' => 'form-control',
            );
            echo form_textarea($options);
            ?>
        </div>
       
    </div>
    <div class="modal-footer">

         <button class="btn-icon btn btn-primary">
                <i class="fa fa-reply img-circle text-muted"></i> 
                <?= lang('edit_payment');?>
            </button>
    </div>
</div>
<?php echo form_close(); ?>

<input type="hidden" id="payment_paid_by" value="<?=$payment->paid_by;?>"/>
<input type="hidden" id="invoice_customer_id" value="<?=$inv->customer_id;?>"/>
<script src="<?= base_url() ?>panel/misc/js/sale_add_payment"></script>
