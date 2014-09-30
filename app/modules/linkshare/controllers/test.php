<?php

/**
* To change this template, choose Tools | Templates
* and open the template in the editor.
*
* @category Admin Controller
* @package  Linkshare
* @author   Weblight <office@weblight.ro>
* @license  License http://www.weblight.ro/
* @link     http://www.weblight.ro/
*
**/

function listStatus()
{
    $this->admin_navigation->module_link('Adauga status', site_url('admincp/linkshare/addStatus/'));

    $this->load->library('dataset');

    $columns = array(
        array(
            'name' => 'ID #',
            'width' => '10%'),
        array(
            'name' => 'status ID',
            'width' => '20%'),
        array(
            'name' => 'Nume',
            'width' => '70%'),
        array(
            'name' => 'Operatii',
            'width' => '30%'
        )
    );

    $this->dataset->columns($columns);
    $this->dataset->datasource('status_model', 'get_statuses');
    $this->dataset->base_url(site_url('admincp/linkshare/listStatus'));
    $this->dataset->rows_per_page(10);

    // total rows
    $total_rows = $this->db->get('linkshare_status')->num_rows();
    $this->dataset->total_rows($total_rows);

    $this->dataset->initialize();

    // add actions
    $this->dataset->action('Delete', 'admincp/linkshare/deleteStatus');

    $this->load->view('listStatus');
}

function addStatus()
{
    $this->load->library('admin_form');
    $form = new Admin_form;

    $form->fieldset('status noua');
    $form->text('Nume', 'name', '', 'Introduceti nume status', true, 'e.g., U.S. status', true);
    $form->text('SID', 'id_status', '', 'Introduceti id status', true, 'e.g., 1', true);

    $data = array(
        'form' => $form->display(),
        'form_title' => 'Adauga status',
        'form_action' => site_url('admincp/linkshare/addStatusValidate'),
        'action' => 'new'
    );

    $this->load->view('addStatus', $data);
}

function addStatusValidate($action = 'new', $id = false)
{
    $this->load->library('form_validation');
    $this->form_validation->set_rules('name', 'Nume', 'required|trim');
    $this->form_validation->set_rules('sid', 'id status', 'required|trim');

    if ($this->form_validation->run() === false) {
        $this->notices->SetError('Campuri obligatorii.');
        $error = true;
    }

    if (isset($error)) {
        if ($action == 'new') {
            redirect('admincp/linkshare/addStatus');
            return false;
        } else {
            redirect('admincp/linkshare/editStatus/' . $id);
            return false;
        }
    }

    $this->load->model('status_model');

    $fields['name'] = $this->input->post('name');
    $fields['id_status'] = $this->input->post('id_status');

    if ($action == 'new') {
        $type_id = $this->status_model->new_status($fields);

        $this->notices->SetNotice('status adaugat cu succes.');

        redirect('admincp/linkshare/listStatus/');
    } else {
        $this->status_model->update_status($fields, $id);
        $this->notices->SetNotice('status actualizat cu succes.');

        redirect('admincp/linkshare/listStatus/');
    }

    return true;
}

function editStatus($id)
{
    $this->load->model('status_model');
    $status = $this->status_model->get_status($id);

    if (empty($status)) {
        die(show_error('Nu exista nici un status cu acest ID.'));
    }

    $this->load->library('admin_form');
    $form = new Admin_form;

    $form->fieldset('status');
    $form->text('Nume', 'name', $status['name'], 'Introduceti nume status.', true, 'e.g., U.S. status', true);
    $form->text('SID', 'id_status', $status['id_status'], 'Introduceti status ID.', true, 'e.g., approved', true);

    $data = array(
        'form' => $form->display(),
        'form_title' => 'Editare status',
        'form_action' => site_url('admincp/linkshare/addStatusValidate/edit/' . $status['id_status']),
        'action' => 'edit',
    );

    $this->load->view('addStatus', $data);
}

function deleteStatus($contents, $return_url)
{

    $this->load->library('asciihex');
    $this->load->model('status_model');

    $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
    $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

    foreach ($contents as $content) {
        $this->status_model->deleteStatus($content);
    }

    $this->notices->SetNotice('status sters cu succes.');

    redirect($return_url);

    return true;
}

?>