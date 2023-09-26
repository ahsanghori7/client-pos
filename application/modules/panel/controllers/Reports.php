<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends Auth_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('repairer');
		$this->load->model('reports_model');
	}

	function stock()
    {
        $this->mPageTitle = lang('stock');
        $this->repairer->checkPermissions();
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['totals'] = $this->reports_model->getStockTotals();
        $this->render('reports/stock_chart');

    }

    public function finance()
    {
        $start = date("Y-m-d",strtotime("-1 month"));
        if ($this->input->get('start')) {
            $start = $this->repairer->fsd($this->input->get('start'));
        }

        
        $end = date('Y-m-d 23:59:59');
        if ($this->input->get('end')) {
            $end = $this->repairer->fsd($this->input->get('end'));
        }

        $created_by = null;
        if ($this->input->get('created_by')) {
            $created_by = $this->input->get('created_by');
        }

        $sales_count = $this->reports_model->getTotalSales($start,$end, $created_by);
        $repairs_sum = $this->reports_model->getRepairSales($start,$end, $created_by);
       
        $this->data['reports_data'] = [
            'order_counts' => $sales_count[0],
            'order_counts_total' => $sales_count[1],
            'order_repairs' => $repairs_sum[0],
            'order_repairs_total' => $repairs_sum[1],
        ];
        $this->data['users'] = $this->db->where('active', 1)->get('users')->result();
        
        $this->render('reports/finance_new');
    }

    function quantity_alerts($warehouse_id = NULL)
    {
        $this->repairer->checkPermissions();
        $this->mPageTitle = lang('quantity_alerts');

        $this->render('reports/quantity_alerts');
    }
    function getQuantityAlerts($warehouse_id = NULL, $pdf = NULL, $xls = NULL)
    {
        $this->repairer->checkPermissions('quantity_alerts');

        $this->load->library('datatables');
        
        $this->datatables
            ->select('code, name, quantity, alert_quantity')
            ->where('isDeleted != ', 1)
            ->from('inventory')
            ->where('alert_quantity > quantity', NULL)
            ->where('alert_quantity >', 0);
        echo $this->datatables->generate();
    }





    function sales()
    {
        $this->repairer->checkPermissions();
        $this->render('reports/sales');
    }
    
   
    function getAllSales($pdf = NULL, $xls = NULL)
    {
        if ($this->input->get('start_date')) {
            $start_date = date('Y-m-d', strtotime($this->input->get('start_date'))) . " 00:00:00";
        } else {
            $start_date = date('Y-m-d 00:00:00');
        }
        if ($this->input->get('end_date')) {
            $end_date = date('Y-m-d', strtotime($this->input->get('end_date'))) . " 23:59:59";
        } else {
            $end_date = date('Y-m-d 23:59:59');
        }

        if ($pdf || $xls) {
             $this->db->select("sales.id as id,LPAD(sales.id, 4, '0') as sale_id, DATE_FORMAT(date, '%m-%d-%Y %T') as date, customer, (SELECT GROUP_CONCAT(product_name) FROM sale_items WHERE sale_items.sale_id = sales.id) as name, TRUNCATE(grand_total-total_tax, 2) as total, TRUNCATE(total_tax, 2) as total_tax, TRUNCATE(grand_total, 2) as grand_total, (grand_total - paid) as balance")
                ->from('sales')
                ->where('sale_status', 'completed')
                ->group_by('sales.id');
                
            if ($start_date) {
                $this->db->where('sales.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Sales Report');

                $sheet->SetCellValue('A1', sprintf(lang('sales_report_to_from'), date('m-d-Y H:i:s', strtotime($start_date)), date('m-d-Y H:i:s', strtotime($end_date))));
                $sheet->mergeCells('A1:I1');
                $sheet->SetCellValue('A2', lang('sale_id'));
                $sheet->SetCellValue('B2', lang('date'));
                $sheet->SetCellValue('C2', lang('customer'));
                $sheet->SetCellValue('D2', lang('product_name'));
                $sheet->SetCellValue('E2', lang('total'));
                $sheet->SetCellValue('F2', lang('tax'));
                $sheet->SetCellValue('G2', lang('grand_total'));
                $sheet->SetCellValue('H2', lang('payments'));
                $sheet->SetCellValue('I2', lang('balance'));

                $row = 3;
                $ttotal = 0;
                $ttotal_tax = 0;
                $tgrand_total = 0;
                foreach ($data as $data_row) {
                    
                    $ir = $row + 1;
                    if ($ir % 2 == 0) {
                        $style_header = array(                  
                            'fill' => array(
                                'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'color' => array('rgb'=>'CCCCCC'),
                            ),
                        );
                        $sheet->getStyle("A$row:I$row")->applyFromArray( $style_header );
                    }
                    $total = number_format($data_row->total, 2);
                    $total_tax = number_format($data_row->total_tax, 2);
                    $grand_total = number_format($data_row->grand_total, 2);
                    $ttotal += $total;
                    $ttotal_tax += $total_tax;
                    $tgrand_total += $grand_total;

                    $payments = $this->reports_model->getSalePayments($data_row->id);
                    $paid = '';

                    if($payments) {
                        foreach ($payments as $payment) {
                            $paid .= lang($payment->paid_by) . ': ' . $this->repairer->formatMoney($payment->amount) . "\n";
                        }
                    }

                    $sheet->SetCellValue('A' . $row, ($data_row->sale_id));
                    $sheet->SetCellValue('B' . $row, $data_row->date);
                    $sheet->SetCellValue('C' . $row, $data_row->customer);
                    $sheet->SetCellValue('D' . $row, $data_row->name);
                    $sheet->SetCellValue('E' . $row, $total);
                    $sheet->SetCellValue('F' . $row, $total_tax);
                    $sheet->SetCellValue('G' . $row, $grand_total);
                    $sheet->SetCellValue('H' . $row, $paid);
                    $sheet->SetCellValue('I' . $row, $data_row->balance);
                    $row++;
                }
                $style_header = array(      
                    'fill' => array(
                        'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'color' => array('rgb'=>'fdbf2d'),
                    ),
                );
                $sheet->getStyle("A$row:G$row")->applyFromArray( $style_header );
                $sheet->SetCellValue('E' . $row, $ttotal);
                $sheet->SetCellValue('F' . $row, $ttotal_tax);
                $sheet->SetCellValue('G' . $row, $tgrand_total);

                $sheet->getColumnDimension('A')->setWidth(10);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(25);
                $sheet->getColumnDimension('I')->setWidth(10);
               
                $filename = 'sales_report';
                $sheet->getParent()->getDefaultStyle()->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle('E2:E' . ($row))->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle('F2:F' . ($row))->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle('G2:G' . ($row))->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);


                $header = 'A1:I1';
                $sheet->getStyle($header)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('94ce58');
                $style = array(
                    'font' => array('bold' => true,),
                    'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,),
                );
                $sheet->getStyle($header)->applyFromArray($style);
                

                $header = 'A2:I2';
                $sheet->getStyle($header)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('fdbf2d');
                $style = array(
                    'font' => array('bold' => true,),
                    'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_GENERAL,),
                );
                $sheet->getStyle($header)->applyFromArray($style);


                $header = 'A'.$row.':I'.$row;
                $sheet->getStyle($header)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('fdbf2d');
                $style = array(
                    'font' => array('bold' => true,),
                    'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_GENERAL,),
                );
                $sheet->getStyle($header)->applyFromArray($style);

                if ($pdf) {
                    $styleArray = [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                                'color' => ['argb' => 'FFFF0000'],
                            ],
                        ],
                    ];
                    
                    $sheet->getStyle('A0:I'.($row))->applyFromArray($styleArray);
                    $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');
                    $writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Mpdf');
                    $writer->save('php://output');
                }
                if ($xls) {
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
                    header('Cache-Control: max-age=0');

                    $writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save('php://output');
                    exit();
                }
            }
        } else {
            $this->load->library('datatables');

            if ($start_date ) {
                $this->datatables->where('sales.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            $this->datatables
                ->select("sales.id as id,LPAD(sales.id, 4, '0') as sale_id, DATE_FORMAT(date, '%m-%d-%Y %T') as date, customer, (SELECT GROUP_CONCAT(product_name) FROM sale_items WHERE sale_items.sale_id = sales.id) as name, (grand_total-total_tax) as total, total_tax, (grand_total), paid, (grand_total - paid),payment_status,  sales.id as actions,  (SELECT clients.email FROM clients WHERE clients.id = sales.customer_id) as email")
                ->from('sales')
                ->where('sale_status', 'completed')
                ->group_by('sales.id');

            $payments_link = anchor('panel/sales/payments/$1', '<i class="fas fa-money-bill-alt"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal" class="dropdown-item"');
            $add_payment_link = anchor('panel/sales/add_payment/$1', '<i class="fas fa-money-bill-alt"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal" class="dropdown-item"');
            
            $delete_link       = "<a href='#' class='po dropdown-item' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . base_url('panel/sales/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fas fa-trash\"></i> "
            . lang('delete') . '</a>';
            
            $email_invoice = '<a class="dropdown-item" id="email_invoice" data-num="$1"  data-email="$2"><i class="fas fa-envelope"></i> '.lang('email_invoice').'</a>';

            $detail_link = anchor('panel/pos/modal_view/$1', '<i class="fas fa-file"></i> ' . lang('sale_details'), 'data-toggle="modal" data-target="#myModal" class="dropdown-item"');

            $bill_link = '<a href="'.base_url('panel/pos/view/$1').'" class="dropdown-item">'.lang('view_sale').'</a>';

            $action = '<div class="text-center"><div class="btn-group dropleft">'
                . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                . lang('actions') . ' <span class="caret"></span></button>
                <ul class="dropdown-menu pull-right" role="menu">
                    ' . $detail_link . '
                    ' . $bill_link . ''.
                    '' . $email_invoice . ''.
                   '' . $payments_link . '
                    ' . $add_payment_link . '
                    ' . $delete_link . '
                </ul>
            </div></div>';
            $this->datatables->edit_column('actions', $action, 'id, email');
            $this->datatables->unset_column('id');
            $this->datatables->unset_column('email');
            echo $this->datatables->generate();
        }



    }


    public function drawer()
    {
        $this->repairer->checkPermissions();
        $this->mPageTitle = lang('drawer_report');
        $this->render('reports/drawer');
    }

    function getDrawerReport($pdf = NULL, $xls = NULL)
    {
        if ($this->input->get('start_date')) {
            $start_date = date('Y-m-d', strtotime($this->input->get('start_date'))) . " 00:00:00";
        } else {
            $start_date = date('Y-m-d 00:00:00');
        }
        if ($this->input->get('end_date')) {
            $end_date = date('Y-m-d', strtotime($this->input->get('end_date'))) . " 23:59:59";
        } else {
            $end_date = date('Y-m-d 23:59:59');
        }


        if ($pdf || $xls) {
             $this->db
                ->select("date, closed_at, (SELECT CONCAT(users.first_name, ' ', users.last_name) FROM users where users.id=pos_register.user_id) as opened_by,(SELECT CONCAT(users.first_name, ' ', users.last_name) FROM users where users.id=pos_register.closed_by) as closed_by, cash_in_hand, total_cc, total_cheques, total_cash, total_cc_submitted, total_cheques_submitted,total_cash_submitted", FALSE)
                ->from("pos_register")
                ->order_by('date desc');

            if ($start_date) {
                $this->db->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = NULL;
            }

            if (!empty($data)) {

                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
          
                $sheet->setTitle(lang('drawer_report'));
                $sheet->SetCellValue('A2', lang('open_time'));
                $sheet->SetCellValue('B2', lang('close_time'));
                $sheet->SetCellValue('C2', lang('opened_by'));
                $sheet->SetCellValue('D2', lang('closed_by'));
                $sheet->SetCellValue('E2', lang('cash_in_hand'));
                $sheet->SetCellValue('F2', lang('cc_slips'));
                $sheet->SetCellValue('G2', lang('cheques'));
                $sheet->SetCellValue('H2', lang('total_cash'));
                $sheet->SetCellValue('I2', lang('cc_slips_submitted'));
                $sheet->SetCellValue('J2', lang('cheques_submitted'));
                $sheet->SetCellValue('K2', lang('total_cash_submitted'));
               

                $sheet->SetCellValue('A1', sprintf(lang('drawer_report_Date'), date('m-d-Y H:i:s', strtotime($start_date)), date('m-d-Y H:i:s', strtotime($end_date))));
                $sheet->mergeCells('A1:K1');

                
                $row = 3;
                foreach ($data as $data_row) {
                    $ir = $row + 1;
                    if ($ir % 2 == 0) {
                        $style_header = array(                  
                            'fill' => array(
                                'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'color' => array('rgb'=>'CCCCCC'),
                            ),
                        );
                        $sheet->getStyle("A$row:K$row")->applyFromArray( $style_header );
                    }

                    $sheet->SetCellValue('A' . $row, ($data_row->date));
                    $sheet->SetCellValue('B' . $row, $data_row->closed_at);
                    $sheet->SetCellValue('C' . $row, $data_row->opened_by);
                    $sheet->SetCellValue('D' . $row, $data_row->closed_by);
                    $sheet->SetCellValue('E' . $row, $data_row->cash_in_hand);
                    $sheet->SetCellValue('F' . $row, $data_row->total_cc);
                    $sheet->SetCellValue('G' . $row, $data_row->total_cheques);
                    $sheet->SetCellValue('H' . $row, $data_row->total_cash);
                    $sheet->SetCellValue('I' . $row, $data_row->total_cc_submitted);
                    $sheet->SetCellValue('J' . $row, $data_row->total_cheques_submitted);
                    $sheet->SetCellValue('K' . $row, $data_row->total_cash_submitted);
                    if($data_row->total_cash_submitted < $data_row->total_cash || $data_row->total_cheques_submitted < $data_row->total_cheques || $data_row->total_cc_submitted < $data_row->total_cc) {
                        $sheet->getStyle('A'.$row.':K'.$row)->applyFromArray(

                                array( 'fill' => array('type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => array('rgb' => 'F2DEDE')) )
                                );
                    }
                    $row++;
                }



                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(15);
                $sheet->getColumnDimension('J')->setWidth(15);
                $sheet->getColumnDimension('K')->setWidth(15);
                $filename = 'register_report';

                $sheet->getParent()->getDefaultStyle()->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $sheet->getStyle('E2:K' . ($row))->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                $header = 'A1:K1';
                $sheet->getStyle($header)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('94ce58');
                $style = array(
                    'font' => array('bold' => true,),
                    'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,),
                );
                $sheet->getStyle($header)->applyFromArray($style);
               

                $header = 'A2:K2';
                $sheet->getStyle($header)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('fdbf2d');
                $style = array(
                    'font' => array('bold' => true,),
                    'alignment' => array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,),
                );

                $sheet->getStyle($header)->applyFromArray($style);


                $sheet->getParent()->getDefaultStyle()->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                if ($pdf) {
                    $styleArray = [
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                                'color' => ['argb' => 'FFFF0000'],
                            ],
                        ],
                    ];
                    $sheet->getStyle('A0:K'.($row-1))->applyFromArray($styleArray);
                    $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
                    header('Cache-Control: max-age=0');
                    $writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Mpdf');
                    $writer->save('php://output');
                }
                if ($xls) {
                    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
                    header('Cache-Control: max-age=0');

                    $writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save('php://output');
                    exit();
                }
            }else{
                $this->session->set_flashdata('warning', lang('no_record_found'));
                redirect('panel/reports/drawer');
            }
        } else {
            $this->load->library('datatables');

            $this->datatables

                ->select("(SELECT CONCAT(users.first_name, ' ', users.last_name) FROM users where users.id=pos_register.user_id) as opener,DATE_FORMAT(date, '%m-%d-%Y %T') as op_date, cash_in_hand, (SELECT CONCAT(users.first_name, ' ', users.last_name) FROM users where users.id=pos_register.closed_by) as closer, DATE_FORMAT(closed_at, '%m-%d-%Y %T') as cl_date,total_cash, pos_register.id as id, pos_register.status as status")
                ->from('pos_register');
            if ($start_date) {
                $this->datatables->where('pos_register.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            echo $this->datatables->generate();
        }

    }

}
