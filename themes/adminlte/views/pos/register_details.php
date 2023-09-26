<div class="modal-content">
    <div class="modal-header">
        <h4 class="modal-title"
            id="myCashModalLabel"><?= ('Sales') . ' (' .(date('m-d-Y H:i:s' ,strtotime($this->session->userdata('register_open_time')))) . ' - ' . (date('m-d-Y H:i:s')) . ')'; ?></h4>
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-2x">&times;</i>
        </button>
    </div>
    <div class="modal-body">
        <table class="table">
            <tr>
                <td><h4><?= lang('opening_Cash'); ?>:</h4></td>
                <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                        <span><?= $this->repairer->formatMoney($this->session->userdata('cash_in_hand')); ?></span></h4>
                </td>
            </tr>
            <tr>
                <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('cash_sales'); ?>:</h4></td>
                <td style="text-align:right; border-bottom: 1px solid #EEE;"><h4>
                        <span><?= $this->repairer->formatMoney($cashsales->paid ? $cashsales->paid : '0.00'); ?></span>
                    </h4></td>
            </tr>
            <tr>
                <td style="border-bottom: 1px solid #EEE;"><h4><?= lang('check_sales'); ?>:</h4></td>
                <td style="text-align:right;border-bottom: 1px solid #EEE;"><h4>
                        <span><?= $this->repairer->formatMoney($chsales->paid ? $chsales->paid : '0.00'); ?></span>
                    </h4></td>
            </tr>
            <tr>
                <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('cc_sales'); ?>:</h4></td>
                <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                        <span><?= $this->repairer->formatMoney($ccsales->paid ? $ccsales->paid : '0.00'); ?></span>
                    </h4></td>
            </tr>
            <tr>
                <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('pppsales'); ?>:</h4></td>
                <td style="text-align:right;border-bottom: 1px solid #DDD;"><h4>
                        <span><?= $this->repairer->formatMoney($pppsales->paid ? $pppsales->paid : '0.00'); ?></span>
                    </h4></td>
            </tr>
            <tr>
                <td style="border-bottom: 1px solid #DDD;"><h4><?= lang('othersales'); ?>:</h4></td>
                <td style="text-align:right;border-bottom: 1px solid #DDD;">
                    <h4>
                        <span><?= $this->repairer->formatMoney($othersales->paid ? $othersales->paid : '0.00'); ?></span>
                    </h4>
                </td>
            </tr>
           <tr>
                <td width="300px;" style="font-weight:bold;"><h4><?= lang('totalsales'); ?>:</h4></td>
                <td width="200px;" style="font-weight:bold;text-align:right;"><h4>
                        <span><?= $this->repairer->formatMoney($totalsales->paid ? $totalsales->paid : '0.00') . ' (' . $this->repairer->formatMoney($totalsales->total ? $totalsales->total : '0.00') . ')'; ?></span>
                    </h4></td>
            </tr>
            <tr>
                <td width="300px;" style="font-weight:bold;"><h4><strong><?= lang('cash_in_hand'); ?></strong>:</h4>
                </td>
                <td style="text-align:right;"><h4>
                        <span><strong><?= $cashsales->paid ? $this->repairer->formatMoney($cashsales->paid + ($this->session->userdata('cash_in_hand'))) : $this->repairer->formatMoney($this->session->userdata('cash_in_hand')); ?></strong></span>
                    </h4></td>
            </tr>
        </table>
    </div>
</div>
   