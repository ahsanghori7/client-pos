<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sales_model extends CI_Model
{

    function __construct() {
        parent::__construct();
    }


    public function getAllCompanies($group_name) {
        $q = $this->db->get('suppliers');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getCompanyByID($id) {
        $q = $this->db->get_where('suppliers', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTaxRateByID($id) {
        $q = $this->db->get_where('tax_rates', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getUser($id = NULL) {
        if (!$id) {
            $id = $this->session->userdata('user_id');
        }
        $q = $this->db->get_where('users', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getPurchaseCount()
    {
        $this->db->from('purchases');
        return $this->db->count_all_results()+1;
    }
    
     public function getReference() {
        
        $prefix = $this->controller->mSettings->purchase_prefix;
        $ref_no = (!empty($prefix)) ? $prefix . '/' : '';
        $seq_number = $this->getPurchaseCount();

        if ($this->controller->mSettings->reference_format == 1) {
            $ref_no .= date('Y') . "/" . sprintf("%04s", $seq_number);
        } elseif ($this->controller->mSettings->reference_format == 2) {
            $ref_no .= date('Y') . "/" . date('m') . "/" . sprintf("%04s", $seq_number);
        } elseif ($this->controller->mSettings->reference_format == 3) {
            $ref_no .= sprintf("%04s", $seq_number);
        } else {
            $ref_no .= sprintf("%04s", $seq_number);
        }

        return $ref_no;
    }

    public function getSupplierSuggestions($term, $limit = 10)
    {
        $this->db->select("id, (CASE WHEN company = '-' THEN name ELSE CONCAT(company, ' (', name, ')') END) as text", FALSE);
        $this->db->where(" (id LIKE '%" . $term . "%' OR name LIKE '%" . $term . "%' OR company LIKE '%" . $term . "%' OR email LIKE '%" . $term . "%' OR phone LIKE '%" . $term . "%') ");
        $q = $this->db->get('suppliers', $limit);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getAllTaxRates() {
        $q = $this->db->get('tax_rates');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductNames($term, $limit = 5)
    {
        $this->db->where("type = 'standard' AND (name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR  concat(name, ' (', code, ')') LIKE '%" . $term . "%')");
        $this->db->limit($limit);
        $q = $this->db->where('isDeleted != ', 1)->get('inventory');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllProducts()
    {
        $q = $this->db->where('isDeleted != ', 1)->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

   public function getProductByID($id) {
        $q = $this->db->get_where('inventory', array('id' => $id, 'isDeleted' => 0), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getRemainingQty($product_id, $sales_id) {
        $this->db->where('sale_id', $sales_id);
        $this->db->join('return_items', 'sales_return.return_id = return_items.return_id', 'inner');
        $this->db->where('return_items.product_id', $product_id);
        $q = $this->db->get('sales_return');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
 
    public function getProductByCode($code)
    {
        $q = $this->db->get_where('inventory', array('code' => $code, 'isDeleted' => 0), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getActutalQty($code, $sales_id)
    {
        $q = $this->db->get_where('sale_items', array('product_code' => $code, "sale_id" => $sales_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    

    public function getAllPurchases()
    {
        $q = $this->db->get('purchases');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getAllSalesItems($purchase_id)
    {
        $this->db->select('*');
        $q = $this->db->get_where('sale_items', array('sale_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTaxRateByName($name)
    {
        $q = $this->db->get_where('tax_rates', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    
    public function getPurchaseByID($id)
    {
        $q = $this->db->get_where('sales', array('id' => $id));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function getProductQtyByID($id)
    {
        $q = $this->db->get_where('inventory', array('id' => $id, 'isDeleted' => 0), 1);
        if ($q->num_rows() > 0) {
            return $q->row()->quantity;
        }
        return FALSE;
    }
    public function addReturn($data, $items)
    {
        $q = $this->db->get_where('sales_return', array('sale_id' => $data['sale_id']), 1);

        if ($q->num_rows() > 0) {
            $id = $q->row()->return_id;

            foreach ($items as $item) {
                $p = $this->db->get_where('return_items', array('return_id' => $id, 'product_id' => $item['product_id']), 1);
                if ($p->num_rows() > 0) {
                    $qty = ($p->row()->quantity + $item['quantity']);
                    $remain = ($p->row()->quantity_balance - $item['quantity']);
                    $this->db->update('return_items', array('quantity' => ($qty), 'quantity_balance' => $remain), array('return_id' => $id, 'product_id' => $item['product_id']));
                    $inventory_old = $this->db->get_where('inventory', array('id' => $item['product_id']), 1);
                    $quantity = $inventory_old->row()->quantity;
                    $this->db->update('inventory', array('quantity' => ($quantity+$item['quantity'])), array('id' => $item['product_id']));             
                }else{
                    $item['return_id'] = $id;
                    $this->db->insert('return_items', $item);
                }
            }
            return true;
        }else{

            if ($this->db->insert('sales_return', $data)) {
                $id = $this->db->insert_id();
                foreach ($items as $item) {
                    $inventory_old = $this->db->get_where('inventory', array('id' => $item['product_id']), 1);
                    $quantity = $inventory_old->row()->quantity;
                    $item['return_id'] = $id;
                    $this->db->insert('return_items', $item);
                    $this->db->update('inventory', array('quantity' => ($quantity+$item['quantity'])), array('id' => $item['product_id']));
                }
    
               return true;
            }
        }
 
        return false;
    }

    private function getBalanceQuantity($product_id) {
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', False);
        $this->db->where('product_id', $product_id)->where('quantity_balance !=', 0);
        $this->db->group_start()->where('status', 'received')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }
    public function syncProductQty($product_id) {
        $balance_qty = $this->getBalanceQuantity($product_id);

        if ($this->db->update('inventory', array('quantity' => ($balance_qty) ), array('id' => $product_id))) {
            return TRUE;
        }
        return FALSE;
    }

    public function updateAVCO($data)
    {
        $this->syncProductQty($data['product_id']);
    }

}
