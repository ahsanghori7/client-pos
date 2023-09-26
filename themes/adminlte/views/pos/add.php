<link href="<?= $assets ?>dist/css/custom/pos.css" rel="stylesheet">

<div>
<form id="pos-sale-form" name="pos-sale-form" action="" method="post">
    <div class="row">
        <div class="col-md-7">
            <div id="sticker">
                <?php echo form_input('add_pos_item', '', 'class="form-control pos-tip" id="add_pos_item" data-placement="top" data-trigger="focus" placeholder="' . $this->lang->line("search_product_by_name_code") . '" title="' . $this->lang->line("au_pr_name_tip") . '"'); ?>
                    
            </div>
        </div>

        <div class="col-md-5 no-padding">
            <div id="left-top">
                <div class="form-group">
                  <div class="input-group mb-3">
                      <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa  fa-user"></i></span>
                      </div>
                        <select id="poscustomer" name="customer" class="client_name form-control" required>
                            <option selected disabled><?= lang('select_pos_Client'); ?></option>
                            <option value="-1" <?= $this->input->get('customer') && $this->input->get('customer') == -1 ? 'selected' : ''; ?>><?= lang('walk_in'); ?></option>
                            <?php 
                                foreach ($customers as $client) :
                                echo '<option value="'.$client->id.'">'.$client->name.'</option>';
                                endforeach; 
                            ?>
                        </select>
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <a id="add_client" class="add_c"><i class="fa fa-user-plus"></i></a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row">
<div class="col-md-7 no-padding">
      <div class="card box-primary">
        <div class="card-header">
            <h5 style="font-size:16px;" class="card-title" id="rhead">
                <a onclick="getCategories()"> Repairer <?= lang('pos'); ?></a>
            </h5>
        </div>
        <div class="card-body pad">
            <div id="gridbox">
                <?php foreach($categories as $category): ?>
                <a id="category_btn" class="btn btn-app table_cat" onclick="selectCategory(<?= $category->id; ?>, true)">
                    <div style="background-image:url(<?= base_url() ?>assets/uploads/<?= $category->image ? $category->image : 'no_image.png'; ?>); background-size: 100px 100px;  height: 100px;width: 100px;">
                    </div>
                    <span id="cat_id_<?= $category->id; ?>" style="font-size: 12px" class="label label-warning label-btnpr"><?= $category->name;?></span>    
                </a>
                <?php endforeach; ?>
                <hr>
                <div class="quick-menu">
                    <div id="proContainer">
                        <div id="ajaxproducts">
                            <div id="item-list">
                                <?php echo $products; ?>
                            </div>
                            
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                </div>
            </div>
            <div class="btn-group btn-group-justified pos-grid-nav">
                <div class="btn-group">
                    <button class="btn btn-primary pos-tip" title="<?=lang('previous')?>" type="button" id="previous">
                        <i class="fa fa-chevron-left"></i>
                    </button>
                </div>
                <div class="btn-group">
                    <button class="btn btn-primary pos-tip" title="<?=lang('next')?>" type="button" id="next">
                        <i class="fa fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
      </div>
</div>


 <div class="col-md-5">
        <div id="left-middle">
            <div id="product-list">
                <table class="table pitems table-striped table-bordered table-condensed table-hover"
                       id="posTable" style="margin-bottom: 0;">
                    <thead>
                    <tr>
                        <th width="40%"><?= lang('product');?></th>
                        <th width="10%"><?= lang('qty');?></th>
                        <th width="15%"><?= lang('price');?></th>
                        <th width="10%"><?= lang('tax');?></th>
                        <th width="10%"><?= lang('discount');?></th>
                        <th width="20%"><?= lang('subtotal');?></th>
                        <th style="width: 5%; text-align: center;">
                            <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                        </th>
                    </tr>
                    </thead>
                    <tbody id="Pro-table" bgcolor="white">
                    </tbody>
                </table>
                <div style="clear:both;"></div>
            </div>
        </div>
        <div style="clear:both;"></div>
        <div id="left-bottom">
            <table id="totalTable"
                   style="padding: 10px; width:100%; float:right; color:#000; background: #FFF;">
                <tr>
                    <td style="padding: 5px 10px; width: 24%; border-top: 1px solid #DDD;"><?=lang('pitems');?></td>
                    <td class="text-right" style="padding: 5px 10px; width: 24%;font-size: 14px; font-weight:bold;border-top: 1px solid #DDD;padding: 5px 10px;">
                        <span id="tpitems">0</span>
                    </td>
                    <td style="padding: 5px 10px; width: 24%;border-top: 1px solid #DDD;"><?=lang('subtotal');?></td>
                    <td class="text-right" style="padding: 5px 10px; width: 24%;font-size: 14px; font-weight:bold;border-top: 1px solid #DDD;">
                        <span id="subtotal">0.00</span>
                    </td>
                </tr>
                <tr>
                    <td style="width: 24%; padding: 5px 10px;"><?=lang('order_tax');?>
                    </td>
                    <td class="text-right" style="width: 24%; padding: 5px 10px;font-size: 14px; font-weight:bold;">
                        <span id="ttax2">0.00</span>
                    </td>
                    <td style="width: 24%; padding: 5px 10px;"><?=lang('discount');?>
                    </td>
                    <td class="text-right" style="width: 24%; padding: 5px 10px;font-weight:bold;">
                        <span id="tds">0.00</span>
                    </td>
                </tr>
                <tr>
                    <td class="gtotals_tds surcharge_td text-right" style="display:none; padding:5px 10px 5px 10px; font-size: 14px;border-top: 1px solid #666; border-bottom: 1px solid #333; font-weight:bold; background:#333; color:#FFF;" colspan="2">
                        <span id="surcharge_span">0.00</span>
                    </td>
                    <td class="gtotals_tds" style="padding: 5px 10px; border-top: 1px solid #666; border-bottom: 1px solid #333; font-weight:bold; background:#333; color:#FFF;" colspan="2">
                        <?= lang('grand_total');?>
                    </td>
                    <td class="gtotals_tds text-right" style="padding:5px 10px 5px 10px; font-size: 14px;border-top: 1px solid #666; border-bottom: 1px solid #333; font-weight:bold; background:#333; color:#FFF;" colspan="2">
                        <span id="gtotal">0.00</span>
                    </td>
                </tr>
            </table>
                <input type="hidden" name="biller" id="biller" value="<?= $this->ion_auth->user()->row()->id;?>"/>

            <div class="clearfix"></div>
            <div id="botbuttons" class="col-md-12">
                <div class="row">
                    <div class="col-md-6" style="padding: 0;">
                        <button type="button" class="btn btn-danger btn-block " style="height:67px;" 
                        id="reset">
                            <?=lang('clear_sale');?>
                        </button>
                    </div>
                    <div class="col-md-6" style="padding: 0;">
                        <button type="button" class="btn btn-success btn-block" id="payment" style="height:67px;">
                            <i class="fa fa-money" style="margin-right: 5px;"></i>
                            <?=lang('payment');?>
                        </button>
                    </div>
                </div>
            </div>
            <div id="payment-con">
                <?php for ($i = 1; $i <= 5; $i++) {?>
                    <input type="hidden" name="amount[]" id="amount_val_<?=$i?>" value=""/>
                    <input type="hidden" name="balance_amount[]" id="balance_amount_<?=$i?>" value=""/>
                    <input type="hidden" name="paid_by[]" id="paid_by_val_<?=$i?>" value="cash"/>
                    <input type="hidden" name="cc_no[]" id="cc_no_val_<?=$i?>" value=""/>
                    <input type="hidden" name="cc_holder[]" id="cc_holder_val_<?=$i?>" value=""/>
                    <input type="hidden" name="cheque_no[]" id="cheque_no_val_<?=$i?>" value=""/>
                    <input type="hidden" name="cc_month[]" id="cc_month_val_<?=$i?>" value=""/>
                    <input type="hidden" name="cc_year[]" id="cc_year_val_<?=$i?>" value=""/>
                    <input type="hidden" name="cc_type[]" id="cc_type_val_<?=$i?>" value=""/>
                    <input type="hidden" name="cc_cvv2[]" id="cc_cvv2_val_<?=$i?>" value=""/>
                    <input type="hidden" name="payment_note[]" id="payment_note_val_<?=$i?>" value=""/>
                <?php }
                ?>
                <input type="hidden" name="pos_note" value="" id="pos_note">
                

            </div>
            <div style="clear:both; height:5px;"></div>
        </div>
    </div>
   

</div>
   
    <?= form_close();?>
</div>

    <div class="modal fade in" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="payModalLabel"><?=lang('finalize_sale');?></h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i
                            class="fa ">&times;</i></span><span class="sr-only"><?=lang('close');?></span></button>
            </div>
            <br>
            <div class="modal-body row" id="payment_content">

                    <div class="col-md-9">
                        <div class="clearfir"></div>
                        <div id="payments">
                            <div class="card card-body bg-light well_1">
                                <div class="payment">
                                    <div class="row">
                                        <div class="col-sm-5">
                                            <div class="form-group">
                                                <label for="amount_1"><?=lang('amount');?></label>
                                                <input name="amount[]" type="text" id="amount_1"
                                                       class="pa form-control kb-pad1 amount"/>
                                            </div>
                                        </div>
                                        <div class="col-sm-5 col-sm-offset-1">
                                            <div class="form-group">
                                                <label for="paid_by_1"><?=lang('paying_by');?></label>
                                                <select style="width: 100%" name="paid_by[]" id="paid_by_1" class="form-control paid_by">
                                                    <option disabled><?=lang('select_payment_method'); ?></option>
                                                        <option value="cash"><?=lang('cash'); ?></option>
                                                        <option value="CC"><?=lang('CC'); ?></option>
                                                        <option value="Cheque"><?=lang('cheque'); ?></option>
                                                        <option value="ppp"><?=lang('ppp'); ?></option>
                                                    	<option value="other"><?=lang('other'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-11">
                                            <div class="pcc_1" style="display:none;">
                                                <!-- <div class="form-group">
                                                    <input type="text" id="swipe_1" class="form-control swipe"
                                                           placeholder="Swipe Your Card"/>
                                                </div> -->
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input name="cc_no[]" type="text" id="pcc_no_1"
                                                                   class="form-control"
                                                                   placeholder="<?= lang('cc_no');?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">

                                                            <input name="cc_holer[]" type="text" id="pcc_holder_1"
                                                                   class="form-control"
                                                                   placeholder="<?= lang('cc_holder');?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <select name="cc_type[]" id="pcc_type_1"
                                                                    class="form-control pcc_type"
                                                                    placeholder="<?=lang('card_type')?>">
                                                                <option value="Visa"><?= lang('Visa'); ?></option>
                                                                <option value="MasterCard"><?= lang('MasterCard'); ?></option>
                                                                <option value="Amex"><?= lang('Amex'); ?></option>
                                                                <option value="Discover"><?= lang('Discover'); ?></option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <input name="cc_month[]" type="text" id="pcc_month_1"
                                                                   class="form-control"
                                                                   placeholder="<?= lang(
                                                                    'month'); ?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">

                                                            <input name="cc_year" type="text" id="pcc_year_1"
                                                                   class="form-control"
                                                                   placeholder="<?= lang(
                                                                    'year'); ?>"/>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">

                                                            <input name="cc_cvv2" type="text" id="pcc_cvv2_1"
                                                                   class="form-control"
                                                                   placeholder="<?= lang(
                                                                    'cvv2'); ?>"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="pcheque_1" style="display:none;">
                                                <div class="form-group"><?=lang('check_number');?>
                                                    <input name="cheque_no[]" type="text" id="cheque_no_1" class="form-control cheque_no"/>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label><?=lang('payment_note');?></label>
                                                <textarea name="payment_note[]" id="payment_note_1"
                                                          class="pa form-control kb-text payment_note"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="multi-payment"></div>
                        <button type="button" class="btn btn-primary col-md-12 addButton"><i
                                class="fa fa-plus"></i> <?=lang('add_more_payments');?></button>
                        <div style="clear:both; height:15px;"></div>
                        
                    </div>
                    <div class="col-md-2">
                        <div class="font16">
                            <table class="table table-bordered table-condensed table-striped" style="margin-bottom: 0;">
                                <tbody>
                                <tr>
                                    <td style="width: 25%;"><?= lang('quantity'); ?></td>
                                    <td style="width: 25%;" class="text-right"><span id="item_count">0.00</span></td>
                                <tr>
                                </tr>
                                    <td style="width: 25%;"><?= lang('total'); ?></td>
                                    <td style="width: 25%;" class="text-right"><span id="twt">0.00</span></td>
                                </tr>
                                <tr>
                                    <td width="25%"><?= lang('tpayments'); ?></td>
                                    <td style="width: 25%;" class="text-right"><span id="total_paying">0.00</span></td>
                                <tr>
                                </tr>
                                    <td style="width: 25%;"><?= lang('change'); ?></td>
                                    <td style="width: 25%;" class="text-right"><span id="balance">0.00</span></td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="col-md-12">
                    	<div class="form-group">
		                    <label><?= lang('sale_note') ?></label>
		                    <textarea rows="4" name="sale_note" id="sale_note" class="form-control"></textarea>
		                </div>
                    </div>
                    
                    <!--  -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-block btn-lg btn-primary" id="submit-sale"><?=lang('submit');?></button>
            </div>
        </div>
    </div>
</div>
    
<div class="modal" id="prdModal" tabindex="-1" role="dialog" aria-labelledby="prdModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="prdModalLabel"></h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"><i class="fa fa-2x">&times;</i></span><span class="sr-only"><?= lang('close') ?></span></button>
            </div>
            <div class="modal-body">
                <form id="discount_form" class="form-horizontal" role="form">
                    <div class="default">
                        <div class="form-group">
                            <label for="pdiscount" class="col-sm-4 control-label"><?= lang('discount') ?></label>
                            <div class="col-sm-8">
                                <div id="did-div"></div>
                                <div id="pdiscount-div">
                                	<input type="text" class="form-control"  name="discount_input" id="discount_input">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button data-dismiss="modal" class="pull-left btn btn-danger"><?= lang('go_back') ?></button>
                <button type="submit" id="discount_form_btn" class="btn btn-primary" form="discount_form"><?= lang('submit') ?></button>
            </div>
        </div>
    </div>
</div>

<div style="clear: both;"></div>


<div class="modal modal-primary fade" id="myCashModal" tabindex="-1" role="dialog" aria-labelledby="myCashModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        </div><!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal modal-primary fade" id="myCDrawerModal" tabindex="-1" role="dialog" aria-labelledby="myCDrawerModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg ">
        <div class="modal-content">
        </div><!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<script src="<?=base_url();?>panel/misc/js/pos"></script>