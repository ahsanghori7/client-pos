<div class="row">
  <div class="col-12">
    <div class="card">

      <!-- /.card-header -->
      <div class="card-body">

                <?php
                $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id' => 'pos_setting');
                echo form_open("panel/pos/settings", $attrib);
                ?>
                <fieldset class="scheduler-border">
                    <legend class="scheduler-border"><?= lang('pos_config') ?></legend>
                    <div class="row">
                        <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('products_per_page', 'limit'); ?>
                            <?= form_input('products_per_page', $pos_settings->products_per_page, 'class="form-control" id="limit" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <div class="form-group">
                            <?= lang('product_button_color', 'product_button_color'); ?>
                            <?php $col = array('default' => lang('default'), 'primary' => lang('primary'), 'info' => lang('info'), 'warning' => lang('warning'), 'danger' => lang('danger'));
                            echo form_dropdown('product_button_color', $col, $pos_settings->product_button_color, 'class="form-control" id="product_button_color" required="required"');
                            ?>
                        </div>
                    </div>
                    </div>
                </fieldset>
                <?= form_submit('update_settings', lang('update_settings'), 'class="btn btn-primary"'); ?>
                <?= form_close(); ?>
                
      </div>
  </div>
</div>

