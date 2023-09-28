<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
class Sales extends Auth_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pos_model');
        $this->load->model('sales_model');
        $this->pos_settings = $this->pos_model->getSetting();
        $this->data['pos_settings'] = $this->pos_settings;
    }

    public function index()
    {
        $this->mPageTitle = lang('sales');

        $this->repairer->checkPermissions();
        $this->render('sales/index');
    }

    public function payments($id = null) {
        $this->data['payments'] = $this->pos_model->getInvoicePayments($id);
        $this->data['inv'] = $this->pos_model->getInvoiceByID($id);
        $this->load->view($this->theme.'/sales/payments', $this->data);
    }

    public function delete_payment($id = null) {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->pos_model->deletePayment($id)) {
            $this->session->set_flashdata('message', lang("payment_deleted"));
            redirect($_SERVER["HTTP_REFERER"]);
        }
    }

    public function add_payment($id = NULL) {
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->pos_model->getInvoiceByID($id);
        if ($sale->payment_status == 'paid' && $sale->grand_total == $sale->paid) {
            $this->session->set_flashdata('error', lang("sale_already_paid"));
            $this->repairer->md();
        }

        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == TRUE) {
            $date = date('Y-m-d H:i:s');
            $payment = array(
                'date'         => $date,
                'sale_id'      => $this->input->post('sale_id'),
                'reference_no' => $this->repairer->getReference('pay'),
                'amount'       => $this->input->post('amount-paid'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'cc_cvv2'      => $this->input->post('pcc_ccv'),
                'note'         => $this->input->post('note'),
                'created_by'   => $this->session->userdata('user_id'),
                'type'         => 'received',
            );

        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == TRUE && $msg = $this->pos_model->addPayment($payment)) {
            if ($msg) {
                $this->session->set_flashdata('message', lang("payment_added"));
            } else {
                $this->session->set_flashdata('error', lang("payment_failed"));
            }
            redirect("panel/reports/sales");
        } else {
            if ($sale->sale_status == 'returned' && $sale->paid == $sale->grand_total) {
                $this->session->set_flashdata('warning', lang('payment_was_returned'));
                $this->repairer->md();
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $sale = $this->pos_model->getInvoiceByID($id);
            $this->data['inv'] = $sale;
            $this->data['payment_ref'] = $this->repairer->getReference('pay');
            $this->load->view($this->theme.'/sales/add_payment', $this->data);
        }
    }

    public function refund_payment($id = NULL) {
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->pos_model->getInvoiceByID($id);
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');
        if ($this->form_validation->run() == TRUE) {
            $date = date('Y-m-d H:i:s');
            $payment = array(
                'date'         => $date,
                'sale_id'      => $this->input->post('sale_id'),
                'reference_no' => $this->repairer->getReference('pay'),
                'amount'       => 0-$this->input->post('amount-paid'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'cc_cvv2'      => $this->input->post('pcc_ccv'),
                'note'         => $this->input->post('note'),
                'created_by'   => $this->session->userdata('user_id'),
                'type'         => 'refund',
            );

        } elseif ($this->input->post('refund_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == TRUE && $msg = $this->pos_model->addPayment($payment)) {
            if ($msg) {
                $this->session->set_flashdata('message', lang("payment_added"));
            } else {
                $this->session->set_flashdata('error', lang("payment_failed"));
            }
            redirect("panel/reports/sales");
        } else {
            if ($sale->sale_status == 'returned' && $sale->paid == $sale->grand_total) {
                $this->session->set_flashdata('warning', lang('payment_was_returned'));
                $this->repairer->md();
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $sale = $this->pos_model->getInvoiceByID($id);
            $this->data['inv'] = $sale;
            $this->data['payment_ref'] = $this->repairer->getReference('pay');
            $this->load->view($this->theme.'/sales/refund_payment', $this->data);
        }
    }

    public function edit_payment($id = null, $sale_id = null)
    {
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $payment = $this->pos_model->getPaymentByID($id);

        
        $this->form_validation->set_rules('amount-paid', lang("amount"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        $this->form_validation->set_rules('userfile', lang("attachment"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $date = date('Y-m-d H:i:s');

            $payment = array(
                'date' => $date,
                'sale_id' => $this->input->post('sale_id'),
                'reference_no' => $this->repairer->getReference('pay'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note'         => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
            );


        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER["HTTP_REFERER"]);
        }

        if ($this->form_validation->run() == true && $this->pos_model->updatePayment($id, $payment)) {
            $this->session->set_flashdata('message', lang("payment_updated"));
            redirect("panel/reports/sales");
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment'] = $payment;
            $this->load->view($this->theme . '/sales/edit_payment', $this->data);
        }
    }


    public function payment_note($id = null)
    {
        $payment = $this->pos_model->getPaymentByID($id);
        $inv = $this->pos_model->getInvoiceByID($payment->sale_id);
        $this->data['customer'] = $this->pos_model->getCustomerByID($inv->customer_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['settings'] = $this->mSettings;

        $this->data['page_title'] = lang("payment_note");
        $this->load->view($this->theme . '/sales/payment_note', $this->data);
    }


    public function email_payment($id = null)
    {
        $payment = $this->pos_model->getPaymentByID($id);
        $inv = $this->pos_model->getInvoiceByID($payment->sale_id);
        $customer = $this->pos_model->getCustomerByID($inv->customer_id);
        if (!$customer) {
            $this->repairer->send_json(array('msg' => lang("customer_not_found")));die();
        }
        if (!$customer->email) {
            $this->repairer->send_json(array('msg' => lang("update_customer_email")));
        }
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['customer'] =$customer;
        $this->data['page_title'] = lang("payment_note");
        $this->data['settings'] = $this->mSettings;
        $html = $this->load->view($this->theme . '/sales/payment_note', $this->data, TRUE);

        $html = str_replace(array('<i class="fa fa-2x">&times;</i>', 'modal-', '<p>&nbsp;</p>', '<p style="border-bottom: 1px solid #666;">&nbsp;</p>', '<p>'.lang("stamp_sign").'</p>'), '', $html);
        $html = preg_replace("/<img[^>]+\>/i", '', $html);

        $this->load->library('parser');
        $parse_data = array(
            'stylesheet' => '<link rel="stylesheet" href="'.$this->assets.'assets/vendor/bootstrap/css/bootstrap.css" />',
            'name' => $customer->company && $customer->company != '-' ? $customer->company :  $customer->name,
            'email' => $customer->email,
            'heading' => lang('payment_note').'<hr>',
            'msg' => $html,
            'site_link' => base_url(),
            'site_name' => $this->mSettings->title,
            'logo' => '<img src="' . base_url('assets/uploads/logos/' . $this->mSettings->logo) . '" alt="' . $this->mSettings->title . '"/>'
        );
        $msg = file_get_contents(FCPATH. 'themes/' .$this->theme.'email_templates/email_con.html');
        $message = $this->parser->parse_string($msg, $parse_data);
        $subject = lang('payment_note') . ' - ' . $this->mSettings->title;


        if ($this->repairer->send_email($customer->email, $subject, $message)) {
            $this->repairer->send_json(array('msg' => lang("email_sent")));
        } else {
            $this->repairer->send_json(array('msg' => lang("email_failed")));
        }
    }

    public function delete($id = null)
    {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $inv = $this->pos_model->getInvoiceByID($id);

        if ($this->pos_model->deleteSale($id)) {
            if ($this->input->is_ajax_request()) {
                $this->repairer->send_json(['error' => 0, 'msg' => lang('sale_deleted')]);
            }
            $this->session->set_flashdata('message', lang('sale_deleted'));
            redirect('welcome');
        }
    }

    public function getSales()
    {
        $this->repairer->checkPermissions('index');

        $return_link = anchor('panel/sales/return_sales/$1', '<i class="fas fa-angle-double-left"></i>' . lang('return_sales'), 'class="dropdown-item"');

        $action = '<div class="text-center"><div class="btn-group dropleft">'
        . '<button type="button" class="btn  btn-primary dropdown-toggle" data-toggle="dropdown">'
        . "Actions" . ' <span class="caret"></span></button>
        <ul class="dropdown-menu " role="menu">
            ' . $return_link . '
        </ul>
    </div></div>';
        $this->load->library('datatables');
        $this->datatables->select("LPAD(sales.id, 4, '0') as sale_id, 
             DATE_FORMAT(date, '%m-%d-%Y %T') as date, 
             customer, 
             (SELECT GROUP_CONCAT(product_name) FROM sale_items WHERE sale_items.sale_id = sales.id) as name, 
              paid ,payment_status")
                ->from('sales')
                ->where('sale_status', 'completed');
        $this->datatables->add_column("Actions", $action, "sale_id");
        echo $this->datatables->generate();
    }

    public function return_sales($id = null)
    {
        $this->repairer->checkPermissions('return_sales');

        if ($this->input->get('id')) {
            $id = ltrim($this->input->get('id'));
        }

        $purchase = $this->sales_model->getPurchaseByID($id);
        if (!$purchase) {
            $this->session->set_flashdata('error', lang('purchase_already_returned'));
            redirect($_SERVER['HTTP_REFERER']);
        }
     
        if (isset($_POST['formPost']) && $_POST['formPost'] == true) {
            
            $i = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            $total_qty = 0;
            $actutal_total= 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_code          = $_POST['product'][$r];
                $item_quantity =    $_POST['quantity'][$r];

                if (isset($item_code) && isset($item_quantity) && $item_quantity > 0) {
                    $product_details = $this->sales_model->getProductByCode($item_code);
                    $actutal_qty = $this->sales_model->getActutalQty($item_code, $id);
                    $actutal_qty = $actutal_qty->quantity;
        
                    $item_name        = $product_details->name;
   
                    $product = [
                        'product_id'        => $item_id,
                        'quantity'     => $item_quantity,
                        'quantity_balance'  => ($actutal_qty-$item_quantity)
                    ];

                    $products[] = ($product);
                    $total_qty = $total_qty+$item_quantity;
                    $actutal_total = ($actutal_total+$actutal_qty);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            if($actutal_total > $total_qty){
                $return_status = 'partial';
            }else{
                $return_status = 'completed';
            }
            $data  = [
                'return_status' => $return_status,
                'comment' => $_POST['note'],
                'sale_id' => $id
            ];
        }

        if (isset($_POST['formPost']) && $_POST['formPost'] == true && $this->sales_model->addReturn($data, $products)) {
            $this->session->set_flashdata('message', lang('return_purchase_added'));
            redirect('panel/sales');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $purchase;
            // if ($this->data['inv']->status != 'received' && $this->data['inv']->status != 'partial') {
            //     $this->session->set_flashdata('error', lang('purchase_status_x_received'));
            //     //redirect($_SERVER['HTTP_REFERER']);
            // }
            
            $inv_items = $this->sales_model->getAllSalesItems($id);
            $c = rand(100000, 9999999);

            foreach ($inv_items as $item) {
                $row                   = $this->sales_model->getProductByID($item->product_id);

                $row->base_quantity    = $item->quantity;
                $row->base_unit        = $row->unit;
                $row->base_unit_cost   = $row->cost ? $row->cost : $item->unit_cost;
                $row->qty              = $item->quantity;
                $row->oqty             = $item->quantity;

                $row->purchase_item_id = $item->id;
                $row->received         = $item->quantity;
                $row->discount         = $item->item_discount ? $item->item_discount : '0';
                
                $row->real_unit_price   = $item->real_unit_price;
                $row->tax_rate         = $item->tax_rate_id;
                $row->subtotal         = $item->subtotal;
                $row->remaining_qty    = $this->sales_model->getRemainingQty($item->product_id , $item->sale_id)->quantity_balance ?? null;
                
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $tax_rate = $this->settings_model->getTaxRateByID($row->tax_rate);
                $ri       = $row->id;

                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'row' => $row, 'tax_rate' => $tax_rate, 'options' => null];

                $c++;
            }

            $this->data['inv_items'] = json_encode($pr);
            $this->data['id']        = $id;
            $this->render('sales/return_sales');
        }
    }

}