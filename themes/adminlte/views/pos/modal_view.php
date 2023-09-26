<div class="modal-content">
    <div class="modal-body">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
            <i class="fa fa-2x">&times;</i>
        </button>
        
            
        <div class="well well-sm">
            <div class="row bold">
                <div class="col-xs-5">
                <p class="bold">
                    <?= lang("date"); ?>: <?= ($inv->date); ?><br>
                    <?= lang("ref"); ?>: <?= $inv->reference_no; ?><br>
                </p>
                </div>
                
                <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="row" style="margin-bottom:15px;">
            <div class="col-xs-6">
                <?php echo $this->lang->line("from"); ?>:
                <h2 style="margin-top:10px;"><?= $inv->biller; ?></h2>
            </div>
            <div class="col-xs-6">
                <?php echo $this->lang->line("to"); ?>:<br/>
                <h2 style="margin-top:10px;"><?= $inv->customer; ?></h2>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped print-table order-table" width="100%">

                <thead>

                <tr>
                    <th><?= lang("no"); ?></th>
                    <th><?= lang("description"); ?></th>
                    <th><?= lang("quantity"); ?></th>
                    <th><?= lang("unit_price"); ?></th>
                    <?php
                        echo '<th>' . lang("tax") . '</th>';
                        echo '<th>' . lang("discount") . '</th>';
                    ?>
                    <th><?= lang("subtotal"); ?></th>
                </tr>

                </thead>

                <tbody>

                <?php $r = 1;
                $tax_summary = array();
                foreach ($rows as $row):
                ?>
                    <tr>
                        <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                        <td style="vertical-align:middle;">
                            <?= $row->product_name . " (" . $row->product_code . ")"; ?>
                        </td>
                        <td style="width: 80px; text-align:center; vertical-align:middle;"><?= ($row->quantity);?></td>
                        <td style="text-align:right; width:100px;"><?=  $this->repairer->formatMoney($row->unit_price); ?></td>
                        <?php
                            echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' .  $this->repairer->formatMoney($row->item_tax) . '</td>';
                            echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' .  $this->repairer->formatMoney($row->item_discount) . '</td>';
                        ?>
                        <td style="text-align:right; width:120px;"><?=  $this->repairer->formatMoney($row->subtotal); ?></td>
                    </tr>
                    <?php
                    $r++;
                endforeach;
                ?>
                </tbody>
                <tfoot>
                
                
                
                <tr>
                    <td colspan="6"
                        style="text-align:right; font-weight:bold;">Total Amount
                        (site.settings.currency)
                    </td>
                    <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= ($inv->grand_total); ?></td>
                </tr>
                <tr>
                    <td colspan="6"
                        style="text-align:right; font-weight:bold;">Paid
                        (site.settings.currency)
                    </td>
                    <td style="text-align:right; font-weight:bold;"><?= $this->repairer->formatMoney($inv->paid); ?></td>
                </tr>
                <tr>
                    <td colspan="6"
                        style="text-align:right; font-weight:bold;">Balance
                        (site.settings.currency)
                    </td>
                    <td style="text-align:right; font-weight:bold;"><?= ($inv->grand_total - $inv->paid); ?></td>
                </tr>

                </tfoot>
            </table>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <?php if ($inv->note || $inv->note != "") { ?>
                        <div class="well well-sm">
                            <p class="bold"><?= lang("note"); ?>:</p>
                            <div><?= ($inv->note); ?></div>
                        </div>
                <?php
                    }
                ?>
            </div>
        </div>
        
    </div>
</div>