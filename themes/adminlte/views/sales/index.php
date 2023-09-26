<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>


<?php 
    echo form_open('panel/purchases/purchase_actions', 'id="action-form"');
?>



<div class="row">
  <div class="col-12">
    <div class="card">

    <div class="card-header d-flex p-0">
        <h3 class="card-title p-3"><?= lang('sales'); ?></h3>
    </div>
      <div class="card-body">

         <table id="SRData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-hover table-striped" width="100%">
                <thead>
                <tr class="default">
                    <th><?= lang('sale_id'); ?></th>
                    <th><?= lang('date'); ?></th>
                    <th><?= lang('customer'); ?></th>
                    <th><?= lang('items'); ?></th>
                    <th><?= lang('paid'); ?></th>
                    <th><?= lang('payment_status'); ?></th>
                    <th style="width:100px;"><?= lang('actions'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="11" class="dataTables_empty"><?=lang('loading_data_from_server');?></td>
                </tr>
                </tbody>
                <tfoot class="dtFilter">
                <tr>
                    <th><?= lang('sale_id'); ?></th>
                    <th><?= lang('date'); ?></th>
                    <th><?= lang('customer'); ?></th>
                    <th><?= lang('items'); ?></th>
                    <th><?= lang('paid'); ?></th>
                    <th><?= lang('payment_status'); ?></th>
                    <th style="width:100px;"><?= lang('actions'); ?></th>
                </tr>
                </tfoot>
            </table>
            
      </div>
  </div>
</div>

<div style="display: none;">
    <input type="hidden" name="form_action" value="" id="form_action"/>
    <?=form_submit('performAction', 'performAction', 'id="action-form-submit"')?>
</div>
<?=form_close()?>

<script type="text/javascript" src="<?=base_url();?>panel/misc/js/sales-return"></script>
