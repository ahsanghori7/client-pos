<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Technician_model extends CI_Model
{
	
    public function getTechnician()
    {
        $data = array();
        $this->db->order_by('id', 'desc');
        $query = $this->db->get('technician');
        if ($query->num_rows() > 0) {
            $data = $query->result_array();
        }

        return $data;
    }
    public function delete_clients($id)
    {
        $this->db->delete('technician', array('id' => $id));
    }


    public function insert_client($data)
    {
        $this->db->insert('technician', $data);
        $id = $this->db->insert_id();
        
        $this->settings_model->addLog('add', 'technician', $id, json_encode(array(
            'data'=>$data,
        )));
        return $id;
    }

   
    public function edit_client($id, $data)
    {
        $this->db->where('id', $id);
        if ($this->db->update('technician', $data)) {
             $this->settings_model->addLog('update', 'technician', $id, json_encode(array(
                'data'=>$data,
            )));
            return TRUE;
        }else{
            return FALSE;
        }
    }
 
    public function find_customer($id)
    {
        $data = array();
        $query = $this->db->get_where('technician', array('id' => $id));
        if ($query->num_rows() > 0) {
            $data = $query->row_array();
        }

        return $data;
    }


    public function getCustomerByEmail($id)
    {
        $query = $this->db->get_where('technician', array('email' => $id));
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }


	
     public function addCustomers($data) {
        $this->db->insert_batch('technician', $data);
        $this->settings_model->addLog('add-batch', 'technician', $id, json_encode(array(
            'data'=>$data,
        )));
        return TRUE;
    }
}
