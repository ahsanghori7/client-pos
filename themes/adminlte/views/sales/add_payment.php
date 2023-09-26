<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
            <h4 class="modal-title" id="myModalLabel"><?php echo lang('add_payment'); ?></h4>
            
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form');
        echo form_open_multipart("panel/sales/add_payment/" . $inv->id, $attrib); ?>
        <div class="modal-body">
            <p><?= lang('enter_info'); ?></p>
           
            <input type="hidden" value="<?php echo $inv->id; ?>" name="sale_id"/>
            <div class="clearfix"></div>
            <div id="payments">
                <div class="well well-sm well_1">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="payment">
                                    <div class="form-group">
                                        <?= lang("amount", "amount_1"); ?>
                                        <input name="amount-paid" type="text" id="amount_1" value="<?= $this->repairer->formatDecimal($inv->grand_total - $inv->paid); ?>" class="pa form-control kb-pad amount" required="required"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <?= lang("paying_by", "paid_by_1"); ?>
                                    <select name="paid_by" id="paid_by_1" class="form-control paid_by" required="required">
                                        <?= $this->repairer->paid_opts(); ?>
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="clearfix"></div>
                        <div class="pcc_1" style="display:none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input name="pcc_no" type="text" id="pcc_no_1" class="form-control" placeholder="<?= lang('cc_no') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input name="pcc_holder" type="text" id="pcc_holder_1" class="form-control" placeholder="<?= lang('cc_holder') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <select name="pcc_type" id="pcc_type_1" class="form-control pcc_type" placeholder="<?= lang('card_type') ?>">
                                            <option value="Visa"><?= lang("Visa"); ?></option>
                                            <option value="MasterCard"><?= lang("MasterCard"); ?></option>
                                            <option value="Amex"><?= lang("Amex"); ?></option>
                                            <option value="Discover"><?= lang("Discover"); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input name="pcc_month" type="text" id="pcc_month_1" class="form-control" placeholder="<?= lang('month') ?>"/>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input name="pcc_year" type="text" id="pcc_year_1" class="form-control" placeholder="<?= lang('year') ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="pcheque_1" style="display:none;">
                            <div class="form-group"><?= lang("cheque_no", "cheque_no_1"); ?>
                                <input name="cheque_no" type="text" id="cheque_no_1" class="form-control cheque_no"/>
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
                <?= lang('add_payment');?>
            </button>
        </div>
    </div>
<?php echo form_close(); ?>

<input type="hidden" id="invoice_customer_id" value="<?=$inv->customer_id;?>"/>
<script src="<?= base_url() ?>panel/misc/js/sale_add_payment"></script>
