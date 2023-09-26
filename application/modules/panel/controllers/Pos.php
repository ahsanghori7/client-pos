<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
class Pos extends Auth_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pos_model');
        $this->pos_settings = $this->pos_model->getSetting();
        $this->data['pos_settings'] = $this->pos_settings;
    }

    public function index()
    {
        $this->mPageTitle = lang('pos/index');
        $this->repairer->checkPermissions();
        if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))) {
            $register_data = array('register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date);
            $this->session->set_userdata($register_data);
        } else {
            $this->session->set_flashdata('error', lang('register_not_open'));
            redirect('panel/pos/open_register');
        }
        //validate form input
        $this->form_validation->set_rules('customer', $this->lang->line("customer"), 'trim|required');
        if ($this->form_validation->run() == TRUE) {
            $date = date('Y-m-d H:i:s');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $sale_status = 'completed';
            $payment_status = 'due';
            $payment_term = 0;
            $due_date = date('Y-m-d', strtotime('+' . $payment_term . ' days'));

            $customer_details = $this->pos_model->getCustomerByID($customer_id);
            $customer = $customer_details ?$customer_details->name : lang('walk_in');

            $biller_details = $this->pos_model->getBillerByID($biller_id);
            $biller = $biller_details->first_name.' '.$biller_details->last_name;
            $note = $this->repairer->clear_tags($this->input->post('pos_note'));
                
            $products = array(); 
            $total_items = 0;
            $subtotal = 0;
            $total_tax = 0;
            $total_discount = 0;
            $total = 0;
            $gtotal = 0;
            if (isset($_POST['item_id']) && $_POST['item_id'] !== null) {
                $i = sizeof($_POST['item_id']);
                for ($r = 0; $r < $i; $r++) {
                    $item_id = $_POST['item_id'][$r];
                    $item_name = $_POST['item_name'][$r];
                    $item_code = $_POST['item_code'][$r];
                    $item_price = $_POST['item_price'][$r];
                    $unit_price = $_POST['unit_price'][$r];
                    $item_cost = $_POST['item_cost'][$r];
                    $item_tax = $_POST['item_tax'][$r];
                    $item_tax_id = $_POST['item_tax_id'][$r];
                    $item_discount = $_POST['item_discount'][$r];
                    $item_type = $_POST['item_type'][$r];
                    $item_qty = $_POST['item_qty'][$r];
                    $p_subtotal = $_POST['subtotal'][$r];
                    $products[] = array(
                        'product_id'        => $item_id,
                        'product_name'      => $item_name,
                        'product_code'      => $item_code,
                        'product_type'      => $item_type,
                        'quantity'          => $item_qty,
                        'unit_cost'         => $item_cost,
                        'unit_price'        => $item_price,
                        'real_unit_price'   => $unit_price,
                        'item_tax'          => $item_tax,
                        'tax_rate_id'       => $item_tax_id,
                        'subtotal'          => $p_subtotal,
                        'item_discount'     => $item_discount,
                    );
                    $subtotal += (float) $item_qty * $item_price;
                    $total_tax += (float) $item_tax;
                    $total_discount += (float)$item_discount;
                    $total += $this->repairer->formatDecimal(($item_price * $item_qty), 4);
                    $total_items += 1;
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang("order_items"), 'required');
            }
            $grand_total = $subtotal+$total_tax;
            $reference = $this->pos_model->getReference();
            $data = array(
                'date'  => $date,
                'reference_no'      => $reference,
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'note'              => $note,
                'total'             => $total,
                'total_discount'    => $total_discount,
                'total_tax'         => $total_tax,
                'grand_total'       => $grand_total,
                'total_items'       => $total_items,
                'sale_status'       => $sale_status,
                'payment_status'    => $payment_status,
                'created_by'        => $this->session->userdata('user_id'),
            );

            $p = isset($_POST['amount']) ? sizeof($_POST['amount']) : 0;
            $paid = 0;
            for ($r = 0; $r < $p; $r++) {
                if (isset($_POST['amount'][$r]) && !empty($_POST['amount'][$r]) && isset($_POST['paid_by'][$r]) && !empty($_POST['paid_by'][$r])) {
                    $amount = $this->repairer->formatDecimal($_POST['balance_amount'][$r] > 0 ? $_POST['amount'][$r] - $_POST['balance_amount'][$r] : $_POST['amount'][$r]);
                    $payment[] = array(
                        'date'         => $date,
                        'amount'       => $amount,
                        'paid_by'      => $_POST['paid_by'][$r],
                        'cheque_no'    => $_POST['cheque_no'][$r],
                        'cc_no'        => $_POST['cc_no'][$r],
                        'cc_holder'    => $_POST['cc_holder'][$r],
                        'cc_month'     => $_POST['cc_month'][$r],
                        'cc_year'      => $_POST['cc_year'][$r],
                        'cc_type'      => $_POST['cc_type'][$r],
                        'cc_cvv2'      => $_POST['cc_cvv2'][$r],
                        'created_by'   => $this->session->userdata('user_id'),
                        'type'         => 'received',
                        'note'         => $_POST['payment_note'][$r],
                        'pos_paid'     => $_POST['amount'][$r],
                        'pos_balance'  => $_POST['balance_amount'][$r],
                    );
                }
            }
            if (!isset($payment) || empty($payment)) {
                $payment = array();
            }

        }
        if ($this->form_validation->run() == TRUE && !empty($products) && !empty($data)) {
            if ($sale = $this->pos_model->addSale($data, $products, $payment)) {
                $this->session->set_userdata('remove_posls', 1);
                $msg = $this->lang->line("sale_added");
                if (!empty($sale['message']) && is_array($sale['message'])) {
                    foreach ($sale['message'] as $m) {
                        $msg .= '<br>' . $m;
                    }
                }
                $this->session->set_flashdata('message', $msg);
                redirect("panel/pos/view/" . $sale['sale_id']);
            }
        }else{
            $this->data['customers'] = $this->settings_model->getAllClients();
            $this->data['categories'] = $this->settings_model->getParentCategories();
            $this->data["tcp"] = $this->pos_model->products_count();
            $this->data['products'] = $this->ajaxproducts();
            $this->render('pos/add');
        }
       
    }



    public function open_register()
    {
        $this->repairer->checkPermissions('index');
        $q = $this->db->get_where('pos_register', array('user_id'=>$this->session->userdata('user_id'), 'status'=>'open'));
        if ($q->num_rows() > 0) {
            $this->session->set_flashdata('error', lang('register_open'));
            redirect('panel/pos');
        }

        $this->form_validation->set_rules('cash_in_hand', lang('cash_in_hand'), 'trim|required|numeric');
        if ($this->form_validation->run() == TRUE) {
            
            $currency_sets = $this->repairer->returnOpenRegisterSets();

            foreach($currency_sets as $input => $name){
                $cash_data[$input] = $this->input->post($input);
            }

            $cash_data = json_encode($cash_data);

            $data = array(
                'date'          => date('Y-m-d H:i:s'),
                'cash_in_hand'  => $this->input->post('cash_in_hand'),
                'user_id'       => $this->session->userdata('user_id'),
                'status'        => 'open',
                'cash_data'     => $cash_data,
            );
        }
        if ($this->form_validation->run() == TRUE) {
            $this->db->insert('pos_register', $data);
            $this->session->set_flashdata('message', lang('register_opened'));
            redirect("panel/pos");
        } else {
            $this->render('pos/open_register');
        }
    }

    public function register_details()
    {
        $this->repairer->checkPermissions('index');
        $this->load->library('repairer');
        $register_open_time = $this->session->userdata('register_open_time');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales'] = $this->pos_model->getRegisterCCSales($register_open_time);
        $this->data['cashsales'] = $this->pos_model->getRegisterCashSales($register_open_time);
        $this->data['chsales'] = $this->pos_model->getRegisterChSales($register_open_time);
        $this->data['pppsales'] = $this->pos_model->getRegisterPPPSales($register_open_time);
        $this->data['othersales'] = $this->pos_model->getRegisterOtherSales($register_open_time);
        $this->data['totalsales'] = $this->pos_model->getRegisterSales($register_open_time);
        $this->load->view($this->theme.'pos/register_details', $this->data);
    }


    public function close_register($user_id = NULL)
    {
        $this->repairer->checkPermissions('index');

        $this->load->library('repairer');
        $user_id = $this->session->userdata('user_id');
        $this->form_validation->set_rules('total_cash', lang('total_cash'), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cheques', lang('cheques'), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cc_slips', lang('cc_slips'), 'trim|required|numeric');

        if ($this->form_validation->run() == TRUE) {
            $rid = $this->session->userdata('register_id');
            $user_id = $this->session->userdata('user_id');


            $cash_data = array();
            $currency_sets = $this->repairer->returnOpenRegisterSets();

            foreach($currency_sets as $input => $name){
                $cash_data[$input] = $this->input->post($input);
            }
            $cash_data = json_encode($cash_data);

            if ($this->input->post('pin_close')) {
                $created_by = $this->db->get_where('users', array('pin_code'=>$this->input->post('pin_close')))->row()->id;
            }else{
                $created_by = $this->session->userdata('user_id');
            }
            $data = array(
                'closed_at'                => date('Y-m-d H:i:s'),
                'total_cash_submitted'     => $this->input->post('total_cash_submitted'),
                'total_cheques_submitted'  => $this->input->post('total_cheques_submitted'),
                'total_cc_submitted'       => $this->input->post('total_cc_slips_submitted'),
                'total_cash'               => $this->input->post('total_cash'),
                'total_cheques'            => $this->input->post('total_cheques'),
                'total_cc'                 => $this->input->post('total_cc'),
                'total_others'             => $this->input->post('total_others'),
                'total_ppp'                => $this->input->post('total_ppp'),
                'total_cash_submitted_data'=> $cash_data,
                'note'                     => $this->input->post('note'),
                'status'                   => 'close',
                'closed_by'                => $created_by,
            );
        } elseif ($this->input->post('close_register')) {
            $this->session->set_flashdata('error', (validation_errors() ? validation_errors() : $this->session->flashdata('error')));
            redirect("pos");
        }

        if ($this->form_validation->run() == TRUE && $this->pos_model->closeRegister($rid, $user_id, $data)) {
            $this->session->set_flashdata('message', lang('register_closed'));
            redirect("panel");
        } else {
            $register_open_time = $this->session->userdata('register_open_time');
            $this->data['cash_in_hand'] = NULL;
            $this->data['register_open_time'] = NULL;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['ccsales'] = $this->pos_model->getRegisterCCSales($register_open_time, $user_id);
            $this->data['cashsales'] = $this->pos_model->getRegisterCashSales($register_open_time, $user_id);
            $this->data['chsales'] = $this->pos_model->getRegisterChSales($register_open_time, $user_id);
            $this->data['pppsales'] = $this->pos_model->getRegisterPPPSales($register_open_time, $user_id);
            $this->data['othersales'] = $this->pos_model->getRegisterOtherSales($register_open_time);
            $this->data['totalsales'] = $this->pos_model->getRegisterSales($register_open_time, $user_id);
            $this->data['user_id'] = $user_id;
            $this->load->view($this->theme.'pos/close_register', $this->data);
        }
    }

    public function view($sale_id = NULL, $modal = NULL)
    {
        
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->load->helper('pos');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv = $this->pos_model->getInvoiceByID($sale_id);
        $this->data['rows'] = $this->pos_model->getAllInvoiceItems($sale_id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;
        $this->data['biller'] = $this->pos_model->getBillerByID($biller_id);
        $this->data['customer'] = $this->pos_model->getCustomerByID($customer_id);
        $this->data['payments'] = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['inv'] = $inv;
        $this->data['sid'] = $sale_id;
        $this->data['modal'] = $modal;
        $this->data['logo'] = $this->mSettings->logo;
        $this->data['created_by'] = $this->pos_model->getBillerByID($inv->created_by);
        $this->load->view($this->theme.'pos/view', $this->data);
    }


    function suggestions() {
        $term = $this->input->get('term', TRUE);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . anchor('panel/welcome') . "'; }, 10);</script>");
        }
        $rows = $this->pos_model->getProductNames($term);
        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $pr[] = array(
                    'id' => ($c + $r),
                    'item_id' => $row->id, 
                    'label' => $row->name . " (" . $row->code . ")", 
                    'code' => $row->code, 
                    'name' => $row->name, 
                    'price' => $row->price, 
                    'type' => $row->type, 
                    'quantity' => (int)$row->quantity, 
                    'qty' => 1, 
                    'cost' => (int)$row->cost,
                    'tax_rate' => $this->settings_model->getTaxRateByID($row->tax_rate),
                    'tax_method' => $row->tax_method,
                );
                $r++;
            }
            $this->repairer->send_json($pr);
        } else {
            $this->repairer->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
    function getProductByID($id) {
        if ((strlen($id) < 1 || !$id) && is_numeric($id) ) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . anchor('panel/welcome') . "'; }, 10);</script>");
        }
        $row = $this->pos_model->getProductByID($id);
        if ($row) {
            $c = str_replace(".", "", microtime(true));
            $r = 1;
            $pr[] = array(
                'id' => ($c + $r),
                'item_id' => $row->id, 
                'label' => $row->name . " (" . $row->code . ")", 
                'code' => $row->code, 
                'name' => $row->name, 
                'price' => $row->price, 
                'type' => $row->type, 
                'quantity' => (int)$row->quantity, 
                'qty' => 1, 
                'cost' => (int)$row->cost,
                'tax_rate' => $this->settings_model->getTaxRateByID($row->tax_rate),
                'tax_method' => $row->tax_method,
            );
            $this->repairer->send_json($pr);
        } else {
            $this->repairer->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }
    
    public function getSubCategories($id) {
        $this->data['rows'] = $this->settings_model->getSubCategories($id);
        $this->data['products'] = $this->ajaxproducts($id);
        $tcp = $this->pos_model->products_count($id);
        $html = $this->load->view($this->theme.'pos/subcategory_rows', $this->data, TRUE);
        $this->repairer->send_json(array('products' => $html, 'tcp' => $tcp, 'cat_id' => $id, 'sub_cat_id' => 0));
    }
    public function getCategories() {
        $this->data['rows'] = $this->settings_model->getParentCategories();
        $tcp = $this->pos_model->products_count();
        $this->data['products'] = $this->ajaxproducts();
        $html = $this->load->view($this->theme.'pos/subcategory_rows', $this->data, TRUE);
        $this->repairer->send_json(array('products' => $html, 'tcp' => $tcp, 'cat_id' => 0, 'sub_cat_id' => 0));
    }
    public function getProductsByCategory($id) {
        $this->data['rows'] = $this->pos_model->getProductsBySubCategory(NULL, $id);
        $this->data['products'] = $this->ajaxproducts(NULL, $id);
        $tcp = $this->pos_model->products_count(NULL, $id);
        $html = $this->load->view($this->theme.'pos/product_rows', $this->data, TRUE);
        $this->repairer->send_json(array('products' => $html, 'tcp' => $tcp, 'cat_id' => 0, 'sub_cat_id' => $id));
    }

    public function ajaxproducts($category_id = NULL, $subcategory_id = NULL)
    {
        return $this->pos_model->ajaxproducts($category_id, $subcategory_id);
    }


    public function modal_view($id = null) {
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->pos_model->getInvoiceByID($id);
        
        $this->data['settings'] = ($this->mSettings);
        $this->data['customer'] = ($inv->customer);
        $this->data['biller'] = ($inv->biller);
        $this->data['created_by'] = ($inv->created_by);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->pos_model->getAllInvoiceItems($id);
        $this->load->view($this->theme.'pos/modal_view', $this->data);
    }

    // SHOW THE settings PAGE //
    public function settings()
    {
        $this->mPageTitle = lang('pos_settings');

        $this->form_validation->set_rules('products_per_page', $this->lang->line("products_per_page"), 'trim|required');
        if ($this->form_validation->run() == TRUE) {
            $data = array(
                'products_per_page' => $this->input->post('products_per_page'),
                'product_button_color' => $this->input->post('product_button_color'),

            );
            $this->pos_model->updateSetting($data);
            $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
            redirect("panel/pos/settings");
        }else{
            $this->render('pos/settings');
        }

    }


    public function email_receipt($sale_id = NULL, $view = null)
    {
        if ($this->input->post('id')) {
            $sale_id = $this->input->post('id');
        }
        if ( ! $sale_id) {
            die('No sale selected.');
        }
        $to = '';
        if ($this->input->post('email')) {
            $to = $this->input->post('email');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');

        $this->data['rows'] = $this->pos_model->getAllInvoiceItems($sale_id);

        $inv = $this->pos_model->getInvoiceByID($sale_id);
        $biller_id = $inv->biller_id;
        $customer_id = $inv->customer_id;

        $this->data['biller'] = $this->pos_model->getBillerByID($biller_id);
        $customer = $this->pos_model->getCustomerByID($customer_id);
        $this->data['customer']  = $customer;
        $this->data['payments'] = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['inv'] = $inv;
        $this->data['sid'] = $sale_id;
        $this->data['logo'] = $this->mSettings->logo;
        $this->data['created_by'] = $this->pos_model->getBillerByID($inv->created_by);
        $this->data['pdf'] = true;
        $this->data['pos'] = $this->pos_model->getSetting();
        $this->data['page_title'] = $this->lang->line("invoice");
        $html = $this->load->view($this->theme.'pos/view', $this->data, TRUE);
        $pdf = $this->repairer->generate_pdf($html, lang('invoice').'.pdf', 'S');


        $this->load->library('parser');
        $message = $this->mSettings->sale_email_text;

        $parse_data = array(
            'stylesheet' => '<link rel="stylesheet" href="'.$this->assets.'assets/vendor/bootstrap/css/bootstrap.css" />',
            'name' => $customer ? ($customer->company && $customer->company != '-' ? $customer->company :  $customer->name) : lang('walk_in'),
            'email' => $to,
            'heading' => lang('invoice').'<hr>',
            'msg' => $message,
            'site_link' => base_url(),
            'site_name' => $this->mSettings->title,
            'logo' => '<img src="' . base_url('assets/uploads/logos/' . $this->mSettings->logo) . '" alt="' . $this->mSettings->title . '"/>',
            'email_footer' => '<body bgcolor="#f7f9fa">
                    <table class="body-wrap" bgcolor="#f7f9fa">
                        <tr>
                            <td></td>
                            <td class="container" bgcolor="#FFFFFF">
                                <div class="content">' . $this->mSettings->email_footer . '</div>

                            </td>
                            <td></td>
                        </tr>
                    </table>',
        );

        $msg = file_get_contents(FCPATH.'themes/'.$this->theme.'email_templates/email_con.html');
        $receipt = $this->parser->parse_string($msg, $parse_data, TRUE);

        if ($view) {
            echo $receipt;
            die();
        }

        if (!$to) {
            $this->repairer->send_json(array('msg' => $this->lang->line("no_meil_provided")));
        }

        try {
            if ($this->repairer->send_email($to, lang('sale_reciept'), $receipt, null, null, $pdf)) {
                $this->repairer->send_json(array('msg' => lang('email_sent')));
            } else {
                $this->repairer->send_json(array('msg' => $this->lang->line("email_failed")));
            }
        } catch (Exception $e) {
            $this->repairer->send_json(array('msg' => $e->getMessage()));
        }

    }
    
}