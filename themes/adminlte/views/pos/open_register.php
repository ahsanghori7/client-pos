<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- Main content -->
<div class="row">
  <div class="col-12">
    <div class="card">

      <!-- /.card-header -->
      <div class="card-body">
         <?= validation_errors('<div class="alert alert-info">', '</div>'); ?>
                    <?php 
                    $attribs = array('id'=>'open_register_form');
                    echo form_open("panel/pos/open_register", $attribs); ?>
                    <div class="form-group">
                        <label><?= lang('cash_in_hand'); ?></label>
                        <input type="number" name="cash_in_hand" value="" id="cash_in_hand" class="form-control" readonly>
                    </div>
                    <div class="row">
                        <?php $currency_sets = $this->repairer->returnOpenRegisterSets(); ?>
                        <?php foreach($currency_sets as $input => $name): ?>
                           <div class="col-md-6">
                             <div class="form-group">
                               <div class="row">
                                    <div class="col-lg-2">
                                    <span><?= $this->mSettings->currency; ?><?=$name;?></span>
                                    </div>
                                    <div class="col-lg-3">
                                    <input type="number" min="0" class="form-control <?=$input;?>" name="n<?=$name;?>"  onchange="countCash('<?=$input;?>',<?=$name;?>)" value="0">
                                    </div>
                                    <div class="col-lg-7">
                                      <div class="input-group">
                                         <span class="input-group-addon"><?= $this->mSettings->currency; ?></span>
                                         <input type="text" class="form-control cash v<?=$input;?>" name="v<?=$name;?>"  onchange="countTotal('<?=$input;?>',<?=$name;?>)" value="0" readonly>
                                      </div>
                                    </div>
                               </div>
                             </div>
                          </div>
                        <?php endforeach; ?>
                        <?= form_submit('open_register', utf8ize(lang('open_register')), 'style="width:100%;" class="btn btn-primary btn-lg" id="open_register"'); ?>
                  </div>
                <?php echo form_close(); ?>
      </div>
  </div>
</div>
<!-- <script src="<?=base_url();?>panel/misc/js/open_register.php"></script> -->
<script>
  $(document).ready(function() {
      $('#cash_in_hand').change(function(e) {
        if ($(this).val() && !is_numeric($(this).val())) {
          bootbox.alert("Unexpected Value");
          $(this).val('');
        }
      })
    });
    function countCash(class_cur, amount) {
        var total = amount * $("."+class_cur).val();
        $(".v" + class_cur).val(total.toFixed(2));
        getTotal();
    }

    function countTotal(class_cur, amount) {
        var round_amount = Math.round($(".v" + class_cur).val() / amount) * amount;
        $(".v" + class_cur).val(round_amount.toFixed(2));
        $("."+class_cur).val((round_amount.toFixed(2) / amount));
        getTotal();
    }


    function getTotal() {
        var total = 0;
        $('.cash').each(function(){
            total += parseFloat($(this).val());
        });
        $("#cash_in_hand").val(total.toFixed(2));
        return total;

    }

    jQuery(document).on("submit", "#open_register_form", function (e) {
      e.preventDefault();
      $('#open_register_form')[0].submit();
    })


    jQuery(document).ready( function($) {

        // Disable scroll when focused on a number input.

        $('form').on('focus', 'input[type=number]', function(e) {

            $(this).on('wheel', function(e) {

                e.preventDefault();

            });

        });



        // Restore scroll on number inputs.

        $('form').on('blur', 'input[type=number]', function(e) {

            $(this).off('wheel');

        });



        // Disable up and down keys.

        $('form').on('keydown', 'input[type=number]', function(e) {

            if ( e.which == 38 || e.which == 40 )

                e.preventDefault();

        });
    });
</script>