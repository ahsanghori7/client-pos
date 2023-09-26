<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pos_model extends CI_Model
{
    public function getProductNames($term, $limit = 5)
    {
        $this->db->select('inventory.id, inventory.code, inventory.name as name, inventory.price as price, quantity, cost, type, tax_method, tax_rate')
            ->where("(inventory.name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(inventory.name, ' (', inventory.code, ')') LIKE '%" . $term . "%')")
            ->group_by('inventory.id')->limit($limit);
        $q = $this->db->where('isDeleted', 0)->get('inventory');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getProductByID($id)
    {
        $this->db->select('inventory.id, inventory.code, inventory.name as name, inventory.price as price, quantity, cost, type, tax_method, tax_rate')
            ->where("inventory.id", $id)
            ->group_by('inventory.id');
        $q = $this->db->where('isDeleted', 0)->get('inventory');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSalesCount()
    {
        return $this->db->count_all_results()+1;
    }
    public function getReference($which = NULL) {
        $prefix = 'SALE';
        $ref_no = (!empty($prefix)) ? $prefix . '/' : '';
        $seq_number = $this->getSalesCount();
        
        $ref_no .= date('Y') . "/" . sprintf("%04s", $seq_number);
        return $ref_no;
    }
    public function getCustomerByID($id) {
        $q = $this->db->get_where('clients', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getBillerByID($id) {
        $q = $this->db->get_where('users', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }


    public function addSale($data = array(), $items = array(), $payments = array())
    {
        $this->load->model('reparation_model');
        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();
            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                $this->db->insert('sale_items', $item);
                if ($this->reparation_model->isNotService($item['product_id'])) {
                    $num = $this->reparation_model->getProdQty($item['product_id']) - $item['quantity'];
                    $this->reparation_model->syncProductQty($item['product_id'], $num);
                }
            }
            
            foreach ($payments as $payment) {
                $payment['sale_id'] = $sale_id;
                $this->db->insert('payments', $payment);
            }

            $this->syncSalePayments($sale_id);
            
            $this->settings_model->addLog('add', 'pos-sale', $sale_id, json_encode(array(
                'data'=>$data,
                'items'=>$items,
                'payments'=>$payments,
            )));

            return array('sale_id' => $sale_id, 'message' => 'Added!');
        }
        return false;
    }

    public function getInvoiceByID($id)
    {

        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return FALSE;
    }

    public function deleteSale($id)
    {
        $this->db->trans_start();

        $sale_items = $this->getAllInvoiceItems($id);
        foreach ($sale_items as $item) {
            if ($this->reparation_model->isNotService($item->product_id)) {
                $qty = $item->quantity;
                $num = $this->reparation_model->getProdQty($item->product_id) + $qty;
                $this->reparation_model->syncProductQty($item->product_id, $num);
            }
        }

        if ($this->db->delete('sale_items', ['sale_id' => $id]) && $this->db->delete('sales', ['id' => $id])) {
            $this->db->delete('payments', ['sale_id' => $id]);
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Delete:Sales_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function getAllInvoiceItems($sale_id)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, inventory.details as details')
        ->join('inventory', 'inventory.id=sale_items.product_id', 'left')
        ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
        ->group_by('sale_items.id')
        ->order_by('id', 'asc');

        $q = $this->db->get_where('sale_items', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }


    public function getInvoicePayments($sale_id)
    {
        $q = $this->db->get_where("payments", array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return FALSE;
    }
    public function getProductsBySubCategory($sub_id) {
        $q = $this->db->where('isDeleted != ', 1)->where('subcategory_id', $sub_id)->get("inventory");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
     public function registerData($user_id)
    {
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $q = $this->db->get_where('pos_register', array('user_id' => $user_id, 'status' => 'open'), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getRegisterCCSales($date, $user_id = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cc_slips, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'CC');
        $this->db->where('payments.created_by', $user_id);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
     public function getRegisterCashSales($date, $user_id = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cash_qty, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'cash');
        $this->db->where('payments.created_by', $user_id);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
     public function getRegisterChSales($date, $user_id = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'Cheque');
        $this->db->where('payments.created_by', $user_id);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getRegisterOtherSales($date, $user_id = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_others, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'other');
        $this->db->where('payments.created_by', $user_id);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getRegisterPPPSales($date, $user_id = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('COUNT(' . $this->db->dbprefix('payments') . '.id) as total_cheques, SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date)->where('paid_by', 'ppp');
        $this->db->where('payments.created_by', $user_id);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }
    public function getRegisterSales($date, $user_id = NULL)
    {
        if (!$date) {
            $date = $this->session->userdata('register_open_time');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->db->select('SUM( COALESCE( grand_total, 0 ) ) AS total, SUM( COALESCE( amount, 0 ) ) AS paid', FALSE)
            ->join('sales', 'sales.id=payments.sale_id', 'left')
            ->where('type', 'received')->where('payments.date >', $date);
        $this->db->where('payments.created_by', $user_id);
        $q = $this->db->get('payments');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function closeRegister($rid, $user_id, $data)
    {
        if (!$rid) {
            $rid = $this->session->userdata('register_id');
        }
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }

        if ($this->db->update('pos_register', $data, array('id' => $rid, 'user_id' => $user_id))) {
            return true;
        }
        return FALSE;
    }


    public function products_count($category_id = NULL , $subcategory_id = NULL)
    {
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }

        $this->db->where('isDeleted != ', 1)->from('inventory');
        return $this->db->count_all_results();
    }

    public function fetch_products($category_id, $limit, $start, $subcategory_id = NULL)
    {
        $this->db->limit($limit, $start);
        if ($category_id) {
            $this->db->where('category_id', $category_id);
        }
        if ($subcategory_id) {
            $this->db->where('subcategory_id', $subcategory_id);
        }
        $this->db->where('isDeleted != ', 1)->order_by("name", "asc");
        $query = $this->db->get("inventory");

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    function getSetting()
    {
        $q = $this->db->get('pos_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    function updateSetting($data)
    {
        $q = $this->db->update('pos_settings', $data);
        return TRUE;
    }


    

    public function getPaymentByID($id)
    {
        $q = $this->db->get_where('payments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function deletePayment($id)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->delete('payments', array('id' => $id))) {
            $this->syncSalePayments($opay->sale_id);


            $this->settings_model->addLog('delete-payment', 'pos-sale', $opay->sale_id, json_encode(array(
                'sale'=>$sale,
            )));

            return true;
        }
        return FALSE;
    }

    public function addPayment($data = array())
    {
        $settings = $this->settings_model->getSettings();
        unset($data['cc_cvv2']);
        if ($this->db->insert('payments', $data)) {
            $payment_id = $this->db->insert_id();
            $sale = $this->getSaleByID($data['sale_id']);
            if ($this->repairer->getReference('pay') == $data['reference_no']) {
                $this->repairer->updateReference('pay');
            }
            $this->syncSalePayments($data['sale_id']);

            $this->settings_model->addLog('add-payment', 'pos-sale', $data['sale_id'], json_encode(array(
                'sale'=>$sale,
            )));
            return true;
        }

        return false;
    }

    public function updatePayment($id, $data = array())
    {
        $settings = $this->settings_model->getSettings();
        $opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $data, array('id' => $id))) {
            $sale = $this->getSaleByID($data['sale_id']);
            
            $this->syncSalePayments($data['sale_id']);

            $this->settings_model->addLog('update-payment', 'pos-sale', $data['sale_id'], json_encode(array(
                'sale'=>$sale,
            )));
            return true;
        }
        return false;
    }


    public function getSaleByID($id) {
        $q = $this->db->get_where('sales', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }



    public function getSalePayments($sale_id) {
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

     public function syncSalePayments($id) {
        $sale = $this->getSaleByID($id);
        if ($payments = $this->getSalePayments($id)) {
            $paid = 0;
            $grand_total = $sale->grand_total;
            foreach ($payments as $payment) {
                $paid += $payment->amount;
            }
            $payment_status = $paid == 0 ? 'pending' : $sale->payment_status;
            if ($this->repairer->formatDecimal($grand_total) == $this->repairer->formatDecimal($paid)) {
                $payment_status = 'paid';
            } elseif ($paid != 0) {
                $payment_status = 'partial';
            }

            if ($this->db->update('sales', array('paid' => $paid, 'payment_status' => $payment_status), array('id' => $id))) {
                return true;
            }
        } else {
            $payment_status = ($sale->due_date && $sale->due_date <= date('Y-m-d')) ? 'due' : 'pending';
            if ($this->db->update('sales', array('paid' => 0, 'payment_status' => $payment_status), array('id' => $id))) {
                return true;
            }
        }

        return FALSE;
    }



    public function ajaxproducts($category_id = NULL, $subcategory_id = NULL)
    {
        $this->pos_settings = $this->pos_model->getSetting();
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } 

        if ($this->input->get('subcategory_id')) {
            $subcategory_id = $this->input->get('subcategory_id');
        }

        if ($this->input->get('per_page') == 'n') {
            $page = 0;
        } else {
            $page = $this->input->get('per_page');
        }

        $this->load->library("pagination");
        $config = array();
        $config["base_url"] = base_url() . "panel/pos/ajaxproducts";
        $config["total_rows"] = $this->pos_model->products_count($category_id, $subcategory_id);
        $config["per_page"] = $this->pos_settings->products_per_page;
        $config['prev_link'] = FALSE;
        $config['next_link'] = FALSE;
        $config['display_pages'] = FALSE;
        $config['first_link'] = FALSE;
        $config['last_link'] = FALSE;

        $this->pagination->initialize($config);

        $products = $this->pos_model->fetch_products($category_id, $config["per_page"], $page, $subcategory_id);
        $pro = 1;
        $prods = '<div>';
        if (!empty($products)) {
            foreach ($products as $product) {
                $count = $product->id;
                if ($count < 10) {
                    $count = "0" . ($count / 100) * 100;
                }
                if ($category_id < 10) {
                    $category_id = "0" . ($category_id / 100) * 100;
                }

                $prods .= "<button onclick='addProduct($product->id)' id=\"product-" . $category_id . $count . "\" type=\"button\" value='" . $product->code . "' title=\"" . $product->name . "\" class=\" btn-" . $this->pos_settings->product_button_color . " product pos-tip\" data-container=\"body\"><img src=\"" . base_url() . "assets/uploads/thumbs/".$product->image. "\"" . " alt=\"" . $product->name . "\" class='img-rounded' /><span>" . ($product->name) . "</span></button>";

                $pro++;
            }
        }
        $prods .= "</div>";

        if ($this->input->get('per_page')) {
            echo $prods;
        } else {
            return $prods;
        }
    }

    
}