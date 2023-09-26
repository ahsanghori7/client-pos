<!-- <script src="<?=base_url();?>panel/misc/js/close_register"></script> -->
    <div class="modal-content">
        <div class="modal-header">
            
            <h5 class="modal-title"
                id="myModalLabel"><font color="black">
                  <h4><?=lang('register_closing_report');?></h4>
                  <?= sprintf(lang('register_closing_report_span'), (date('m-d-Y' ,strtotime($this->session->userdata('register_open_time')))), (date('H:i:s' ,strtotime($this->session->userdata('register_open_time')))), (date('m-d-Y')), (date('H:i:s'))); ?>
                </font></h5>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
            </button>
        </div>
        <?php $attrib = array('data-toggle' => 'validator', 'role' => 'form', 'id'=> 'close_register_form');
        echo form_open_multipart("panel/pos/close_register/" . $user_id, $attrib);
        ?>
        <div class="modal-body" style="padding: 15px;">
            <!-- <div id="alerts"></div> -->
            <table width="100%" class="table">
                <tr>
					<td style="border-bottom: 1px solid #EEE;"><h6><?= lang('opening_Cash'); ?>:</h6></td>
                    <td style="text-align:right; border-bottom: 1px solid #EEE;"><h6>
                            <span><?= $this->repairer->formatMoney($cash_in_hand ? $cash_in_hand : $this->session->userdata('cash_in_hand')); ?></span>
                        </h6></td>
                </tr>
                  <tr>
                      <td style="border-bottom: 1px solid #EEE;"><h6><?= lang('cash_sales'); ?>:</h6></td>
                      <td style="text-align:right; border-bottom: 1px solid #EEE;"><h6>
                              <span><?= $this->repairer->formatMoney($cashsales->paid ? $cashsales->paid : '0.00'); ?></span>
                          </h6></td>
                  </tr>
                <tr>
                    <td style="border-bottom: 1px solid #EEE;"><h6><?= lang('check_sales'); ?>:</h6></td>
                    <td style="text-align:right;border-bottom: 1px solid #EEE;"><h6>
                            <span><?= $this->repairer->formatMoney($chsales->paid ? $chsales->paid : '0.00'); ?></span>
                        </h6></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h6><?= lang('cc_sales'); ?>:</h6></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h6>
                            <span><?= $this->repairer->formatMoney($ccsales->paid ? $ccsales->paid : '0.00'); ?></span>
                        </h6></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h6><?= lang('pppsales'); ?>:</h6></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h6>
                            <span><?= $this->repairer->formatMoney($pppsales->paid ? $pppsales->paid : '0.00'); ?></span>
                        </h6></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #DDD;"><h6><?= lang('othersales'); ?>:</h6></td>
                    <td style="text-align:right;border-bottom: 1px solid #DDD;"><h6>
                            <span><?= $this->repairer->formatMoney($othersales->paid ? $othersales->paid : '0.00'); ?></span>
                        </h6></td>
                </tr>
            
                <tr>
                    <td width="300px;" style="font-weight:bold;"><h6><?= lang('totalsales'); ?>:</h6></td>
                    <td width="200px;" style="font-weight:bold;text-align:right;"><h6>
                            <span><?= $this->repairer->formatMoney($totalsales->paid ? $totalsales->paid : '0.00'); ?></span>
                        </h6></td>
                </tr>
                <tr>
                    <td width="300px;" style="font-weight:bold;"><h4><strong><?= lang('total_cash'); ?></strong>:</h4>
                    </td>
                    <td style="text-align:right;"><h4>
                            <span><strong><?= $cashsales->paid ? $this->repairer->formatMoney($cashsales->paid + ($this->session->userdata('cash_in_hand'))) : $this->repairer->formatMoney($this->session->userdata('cash_in_hand')); ?></strong></span>
                        </h4></td>
                </tr>
            </table>

            
            <hr>
            
            <div class="row">
				<center><font size="5" color="white"><?= lang('cash_count_sheet'); ?></font></center>
			</div>
                   <br>
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
                                  <input type="number" min="0" placeholder="Quantity of <?= $this->mSettings->currency; ?><?=$name;?> bills here." class="form-control <?=$input;?>" name="n<?=$name;?>"  onchange="countCash('<?=$input;?>',<?=$name;?>)" value="0">
                                  </div>
                                  <div class="col-lg-7">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                          <span class="input-group-text">
                                            <?= $this->mSettings->currency; ?>
                                          </span>
                                        </div>

                                         <input type="text" class="form-control cash v<?=$input;?>" name="v<?=$name;?>"  onchange="countTotal('<?=$input;?>',<?=$name;?>)" value="0" readonly>
                                    </div>
                                  </div>
                             </div>
                           </div>
                        </div>
                      <?php endforeach; ?>


                    </div>
                        <hr>
                <div class="row"> 
                  <div class="col-sm-4">
                    <div class="form-group">
                        <label for="total_cash_submitted"><?= lang('total_value_cash'); ?></label>
                         <?php $total_cash = $cashsales->paid ? ($cashsales->paid + ($this->session->userdata('cash_in_hand'))) : ($this->session->userdata('cash_in_hand')); 

                         ?>
                        <?= form_hidden('total_cash', $total_cash); ?>
                        <?= form_input('total_cash_submitted', (isset($_POST['total_cash_submitted']) ? $_POST['total_cash_submitted'] : $total_cash), 'class="form-control input-tip" id="total_cash_submitted" required="required"  readonly tabindex=1'); ?>
                    </div>
                    </div>
                    <div class="col-sm-4">
                    <div class="form-group">
                        <label for="total_cheques_submitted"><?= lang('total_value_check'); ?></label>
                        <?= form_hidden('total_cheques', $chsales->total_cheques); ?>
                        <?= form_input('total_cheques_submitted', (isset($_POST['total_cheques_submitted']) ? $_POST['total_cheques_submitted'] : $chsales->total_cheques), 'class="form-control input-tip" id="total_cheques_submitted" required="required" tabindex=3'); ?>
                    </div>
                  </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="total_cc_slips_submitted"><?= lang('total_value_ccc'); ?></label>
                        <?= form_hidden('total_cc_slips', $ccsales->total_cc_slips); ?>
                        <?= form_input('total_cc_slips_submitted', (isset($_POST['total_cc_slips_submitted']) ? $_POST['total_cc_slips_submitted'] : $ccsales->total_cc_slips), 'class="form-control input-tip" id="total_cc_slips_submitted" required="required" tabindex=2'); ?>
                    </div>
                </div>
                  
                
            </div>

        </div>
          <?= form_hidden('total_cc', $ccsales->paid); ?>
          <?= form_hidden('total_ppp', $pppsales->paid); ?>
          <?= form_hidden('total_others', $othersales->paid); ?>

        <div class="modal-footer no-print">
            <?= form_submit('close_register', "Close Register", 'id="close_register_button" class="btn btn-primary"'); ?>
        </div>
    </div>
    <?= form_close(); ?>
</div>

<script>
$(document).ready(function() {
            $('#total_cash_submitted').change(function(e) {
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
            $("#total_cash_submitted").val(total.toFixed(2));
            return total;
        }
</script>