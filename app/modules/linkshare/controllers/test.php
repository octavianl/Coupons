<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function list_status()
{
    $this->admin_navigation->module_link('Adauga status', site_url('admincp/linkshare/add_status/'));

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
    $this->dataset->base_url(site_url('admincp/linkshare/list_status'));
    $this->dataset->rows_per_page(10);

    // total rows
    $total_rows = $this->db->get('linkshare_status')->num_rows();
    $this->dataset->total_rows($total_rows);

    $this->dataset->initialize();

    // add actions
    $this->dataset->action('Delete', 'admincp/linkshare/delete_status');

    $this->load->view('list_status');
}

function add_status() {
    $this->load->library('admin_form');
    $form = new Admin_form;

    $form->fieldset('status noua');
    $form->text('Nume', 'name', '', 'Introduceti nume status', TRUE, 'e.g., U.S. status', TRUE);
    $form->text('SID', 'id_status', '', 'Introduceti id status', TRUE, 'e.g., 1', TRUE);

    $data = array(
        'form' => $form->display(),
        'form_title' => 'Adauga status',
        'form_action' => site_url('admincp/linkshare/add_status_validate'),
        'action' => 'new'
    );

    $this->load->view('add_status', $data);
}

function add_status_validate($action = 'new', $id = false) {
    $this->load->library('form_validation');
    $this->form_validation->set_rules('name', 'Nume', 'required|trim');
    $this->form_validation->set_rules('sid', 'id status', 'required|trim');

    if ($this->form_validation->run() === FALSE) {
        $this->notices->SetError('Campuri obligatorii.');
        $error = TRUE;
    }

    if (isset($error)) {
        if ($action == 'new') {
            redirect('admincp/linkshare/add_status');
            return FALSE;
        } else {
            redirect('admincp/linkshare/edit_status/' . $id);
            return FALSE;
        }
    }

    $this->load->model('status_model');

    $fields['name'] = $this->input->post('name');
    $fields['id_status'] = $this->input->post('id_status');

    if ($action == 'new') {
        $type_id = $this->status_model->new_status($fields);

        $this->notices->SetNotice('status adaugat cu succes.');

        redirect('admincp/linkshare/list_status/');
    } else {
        $this->status_model->update_status($fields, $id);
        $this->notices->SetNotice('status actualizat cu succes.');

        redirect('admincp/linkshare/list_status/');
    }

    return TRUE;
}

function edit_status($id) {
    $this->load->model('status_model');
    $status = $this->status_model->get_status($id);

    if (empty($status)) {
        die(show_error('Nu exista nici un status cu acest ID.'));
    }

    $this->load->library('admin_form');
    $form = new Admin_form;

    $form->fieldset('status');
    $form->text('Nume', 'name', $status['name'], 'Introduceti nume status.', TRUE, 'e.g., U.S. status', TRUE);
    $form->text('SID', 'id_status', $status['id_status'], 'Introduceti status ID.', TRUE, 'e.g., approved', TRUE);

    $data = array(
        'form' => $form->display(),
        'form_title' => 'Editare status',
        'form_action' => site_url('admincp/linkshare/add_status_validate/edit/' . $status['id_status']),
        'action' => 'edit',
    );

    $this->load->view('add_status', $data);
}

function delete_status($contents, $return_url) {

    $this->load->library('asciihex');
    $this->load->model('status_model');

    $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
    $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

    foreach ($contents as $content) {
        $this->status_model->delete_status($content);
    }

    $this->notices->SetNotice('status sters cu succes.');

    redirect($return_url);

    return TRUE;
}

?>
