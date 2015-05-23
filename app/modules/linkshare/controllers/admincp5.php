<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Content Control Panel
 *
 * @category Admin Controller
 * @package  Linkshare
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */

use app\third_party\LOG\Log;

require_once APPPATH . 'third_party/OAUTH2/LinkshareConfig.php';
require_once APPPATH . 'third_party/OAUTH2/CurlApi.php';

class Admincp1 extends Admincp_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $this->admin_navigation->parent_active('linkshare');

        error_reporting(E_ALL^E_NOTICE);
        error_reporting(E_WARNING);
    }

    public function index()
    {
        redirect('admincp/linkshare/siteAdvertisers/1');
    }

    public function listSites()
    {
        $this->admin_navigation->module_link('Add channel', site_url('admincp/linkshare/addSite/'));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '10%'),
            array(
                'name' => 'Site Name',
                'width' => '20%'),
            array(
                'name' => 'Token',
                'width' => '70%'),
            array(
                'name' => 'Actions',
                'width' => '30%'
            ),
            array(
                'name' => 'Info',
                'width' => '30%'
            )
        );

        $this->dataset->columns($columns);
        $this->dataset->datasource('site_model', 'getSites');
        $this->dataset->base_url(site_url('admincp/linkshare/listSites'));
        $this->dataset->rows_per_page(10);

        // total rows
        $total_rows = $this->db->get('linkshare_site')->num_rows();
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        //$this->dataset->action('Delete','admincp/linkshare/deleteSite');

        $this->load->view('listSites');
    }

    public function addSite()
    {
        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('New channel');
        $form->text('Site name', 'name', '', 'Insert site name.', true, 'e.g., couponland.com', true);
        $form->text('Site token', 'token', '', 'Insert site token.', true, '', true);


        $data = array(
            'form' => $form->display(),
            'form_title' => 'Add channel',
            'form_action' => site_url('admincp/linkshare/addSiteValidate'),
            'action' => 'new'
        );

        $this->load->view('addSite', $data);
    }

    public function addSiteValidate($action = 'new', $id = false)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Site name', 'required|trim');
        $this->form_validation->set_rules('token', 'Site token', 'required|trim');

        if ($this->form_validation->run() === false) {
            $this->notices->SetError('You have errors in your form.');
            $error = true;
        }

        if (isset($error)) {
            if ($action == 'new') {
                redirect('admincp/linkshare/addSite');
                return false;
            } else {
                redirect('admincp/linkshare/editSite/' . $id);
                return false;
            }
        }

        $fields['name'] = $this->input->post('name');
        $fields['token'] = $this->input->post('token');

        $this->load->model('site_model');

        if ($action == 'new') {
            $type_id = $this->site_model->newSite($fields);

            $this->notices->SetNotice('Channel added successfuly.');

            redirect('admincp/linkshare/listSites/');
        } else {
            $this->site_model->updateSite($fields, $id);
            $this->notices->SetNotice('Site updated successfully.');

            redirect('admincp/linkshare/listSites/');
        }

        return true;
    }

    public function editSite($id)
    {
        $this->load->model('site_model');
        $site = $this->site_model->getSite($id);

        if (empty($site)) {
            die(show_error('No site with this ID.'));
        }

        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('Site');
        $form->text('Site name', 'name', $site['name'], 'Insert site name.', true, 'e.g., couponland', true);
        $form->text('Site token', 'token', $site['token'], 'Insert site token.', true, '', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Edit channel',
            'form_action' => site_url('admincp/linkshare/addSiteValidate/edit/' . $site['id']),
            'action' => 'edit',
        );

        $this->load->view('addSite', $data);
    }

    public function infoSite($id)
    {
        $this->admin_navigation->module_link('See creative categories linkshare', site_url('admincp/linkshare/listCreativeCategory/' . $id));
        $this->admin_navigation->module_link('See advertisers linkshare', site_url('admincp/linkshare/siteAdvertisers/' . $id));
        $this->admin_navigation->module_link('See products linkshare', site_url('admincp/linkshare/site_produse/' . $id));

        $this->load->model('site_model');
        $site = $this->site_model->getSite($id);

        if (empty($site)) {
            die(show_error('No channel with this ID.'));
        }

        $data = array();
        $data['name'] = $site['name'];

        $this->load->view('infoSite', $data);
    }

     public function deleteSite($contents, $return_url)
    {

        $this->load->library('asciihex');
        $this->load->model('site_model');

        $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
        $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

        foreach ($contents as $content) {
            $this->site_model->deleteSite($content);
        }

        $this->notices->SetNotice('Sites successfully removed.');

        redirect($return_url);

        return true;
    }

    /* networks */

    public function listNetworks()
    {
        $this->admin_navigation->module_link('Add network', site_url('admincp/linkshare/addNetwork/'));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '10%'),
            array(
                'name' => 'Network ID',
                'width' => '20%'),
            array(
                'name' => 'Name',
                'width' => '40%'),
            array(
                'name' => 'Actions',
                'width' => '30%'
            )
        );

        $this->dataset->columns($columns);
        $this->dataset->datasource('network_model', 'getNetworks');
        $this->dataset->base_url(site_url('admincp/linkshare/listNetworks'));
        $this->dataset->rows_per_page(10);

        // total rows
        $total_rows = $this->db->get('linkshare_network')->num_rows();
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp/linkshare/deleteNetwork');

        $this->load->view('listNetworks');
    }

    public function addNetwork()
    {
        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('New network');
        $form->text('Name', 'name', '', 'Add network name', true, 'e.g., U.S. Network', true);
        $form->text('NID', 'nid', '', 'Add network id', true, 'e.g., 1', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Add network',
            'form_action' => site_url('admincp/linkshare/addNetworkValidate'),
            'action' => 'new'
        );

        $this->load->view('addNetwork', $data);
    }

    public function addNetworkValidate($action = 'new', $id = false)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('nid', 'Network id', 'required|trim');

        if ($this->form_validation->run() === false) {
            $this->notices->SetError('Mandatory fields.');
            $error = true;
        }

        if (isset($error)) {
            if ($action == 'new') {
                redirect('admincp/linkshare/addNetwork');
                return false;
            } else {
                redirect('admincp/linkshare/editNetwork/' . $id);
                return false;
            }
        }

        $this->load->model('network_model');

        $fields['name'] = $this->input->post('name');
        $fields['nid'] = $this->input->post('nid');

        if ($action == 'new') {
            $type_id = $this->network_model->newNetwork($fields);

            $this->notices->SetNotice('Successfully added network.');

            redirect('admincp/linkshare/listNetworks/');
        } else {
            $this->network_model->updateNetwork($fields, $id);
            $this->notices->SetNotice('Successfully updated network.');

            redirect('admincp/linkshare/listNetworks/');
        }

        return true;
    }

    public function editNetwork($id)
    {
        $this->load->model('network_model');
        $network = $this->network_model->getNetwork($id);

        if (empty($network)) {
            die(show_error('No network with this ID.'));
        }

        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('network');
        $form->text('Name', 'name', $network['name'], 'Insert network name.', true, 'e.g., U.S. Network', true);
        $form->text('Nid', 'nid', $network['nid'], 'Insert network ID.', true, 'e.g., 1', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Editare network',
            'form_action' => site_url('admincp/linkshare/addNetworkValidate/edit/' . $network['id']),
            'action' => 'edit',
        );

        $this->load->view('addNetwork', $data);
    }

    public function deleteNetwork($contents, $return_url)
    {

        $this->load->library('asciihex');
        $this->load->model('network_model');

        $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
        $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

        foreach ($contents as $content) {
            $this->network_model->deleteNetwork($content);
        }

        $this->notices->SetNotice('Network successfully removed.');

        redirect($return_url);

        return true;
    }

    /* status */

    public function listStatus()
    {
        $this->admin_navigation->module_link('Add status', site_url('admincp/linkshare/addStatus/'));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '10%'),
            array(
                'name' => 'Status ID',
                'width' => '20%'),
            array(
                'name' => 'Name',
                'width' => '20%'),
            array(
                'name' => 'Description',
                'width' => '40%'),
            array(
                'name' => 'Actions',
                'width' => '10%'
            )
        );

        $this->dataset->columns($columns);
        $this->dataset->datasource('status_model', 'getStatuses');
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

    public function addStatus()
    {
        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('New status');
        $form->text('SID', 'id_status', '', 'Add status id', true, 'e.g., approved', true);
        $form->text('Name', 'name', '', 'Add status name', true, 'e.g., Approved', true);
        $form->textarea('Description', 'description', '', 'Add status description', false, '', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Add status',
            'form_action' => site_url('admincp/linkshare/addStatusValidate'),
            'action' => 'new'
        );

        $this->load->view('addStatus', $data);
    }

    public function addStatusValidate($action = 'new', $id = false)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('id_status', 'SID', 'required|trim');
        $this->form_validation->set_rules('name', 'Name', 'required|trim');


        if ($this->form_validation->run() === false) {
            $this->notices->SetError('Mandatory fields.');
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
        $fields = array();

        $fields['id_status'] = $this->input->post('id_status');
        $fields['name'] = $this->input->post('name');
        $fields['description'] = $this->input->post('description');

        if ($action == 'new') {
            $type_id = $this->status_model->newStatus($fields);

            $this->notices->SetNotice('Status successfully added.');

            redirect('admincp/linkshare/listStatus/');
        } else {
            $this->status_model->updateStatus($fields, $id);
            $this->notices->SetNotice('Status successfully updated.');

            redirect('admincp/linkshare/listStatus/');
        }

        return true;
    }

    public function editStatus($id)
    {
        $this->load->model('status_model');
        $status = $this->status_model->getStatus($id);

        if (empty($status)) {
            die(show_error('No status with this ID.'));
        }

        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('status');
        $form->text('SID', 'id_status', $status['id_status'], 'Add status ID.', true, 'e.g., approved', true);
        $form->text('Name', 'name', $status['name'], 'Add status name.', true, 'e.g., U.S. status', true);
        $form->textarea('Description', 'description', $status['description'], 'Add description status.', false, '', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Edit status',
            'form_action' => site_url('admincp/linkshare/addStatusValidate/edit/' . $status['id']),
            'action' => 'edit',
        );

        $this->load->view('addStatus', $data);
    }

    public function deleteStatus($contents, $return_url)
    {

        $this->load->library('asciihex');
        $this->load->model('status_model');

        $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
        $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

        foreach ($contents as $content) {
            $this->status_model->deleteStatus($content);
        }

        $this->notices->SetNotice('Status successfully removed.');

        redirect($return_url);

        return true;
    }

    public function siteAdvertisers($id = 1)
    {
        $this->load->model('site_model');
        $this->load->model('advertiser_model');
        $site = $this->site_model->getSite($id);

        $this->admin_navigation->module_link('Add advertiser', site_url('admincp/linkshare/addAdvertiser/'));
        $this->admin_navigation->module_link('Parse APPROVED advertisers', site_url('admincp/linkshare/parseAdvertisers/' . $site['token'] . '/approved'));
        $this->admin_navigation->module_link('Parse ALL advertisers', site_url('admincp/linkshare/parseAdvertisers/' . $site['token'] . '/all'));
        $this->admin_navigation->module_link('Refresh ALL advertisers', site_url('admincp/linkshare/refreshAdvertisers/' . $site['token'] . '/all'));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '1%'),
            array(
                'name' => 'Site',
                'width' => '10%'),
            array(
                'name' => 'Id Status',
                'width' => '4%'),
            array(
                'name' => 'Status',
                'width' => '8%',
                'type' => 'text',
                'filter' => 'name_status',
                'sort_column' => 'status'),
            array(
                'name' => 'Categories',
                'width' => '10%',
                'type' => 'text',
                'filter' => 'id_categories'),
            array(
                'name' => 'Mid',
                'width' => '10%',
                'type' => 'text',
                'filter' => 'mid',
                'sort_column' => 'mid'),
            array(
                'name' => 'Name',
                'width' => '10%',
                'type' => 'text',
                'filter' => 'name'),
            array(
                'name' => 'Commission',
                'width' => '10%'),
            array(
                'name' => 'Offer ID',
                'width' => '5%'),
            array(
                'name' => 'Offer name',
                'width' => '10%'),
            array(
                'name' => 'Actions',
                'width' => '10%'),
            array(
                'name' => 'Parse products',
                'width' => '5%'),
            array('name' => 'Products',
                'width' => '5%'),
            array('name' => 'No',
                'width' => '1%'),
        );

        $filters = array();
        $filters['limit'] = 10;
        $filters['id_site'] = $_GET['sites'];

        $this->dataset->columns($columns);
        $this->dataset->datasource('advertiser_model', 'getAdvertisers', $filters);
        $this->dataset->base_url(site_url('admincp/linkshare/siteAdvertisers/' . $id));
        $this->dataset->rows_per_page(10);

        // total rows
        if (isset($_GET['offset']))
            $filters['offset'] = $_GET['offset'];
        if (isset($_GET['name']))
            $filters['name'] = $_GET['name'];
        if (isset($_GET['mid']))
            $filters['mid'] = $_GET['mid'];
        if (isset($_GET['id_categories']))
            $filters['id_categories'] = $_GET['id_categories'];

        $total_rows = $this->advertiser_model->get_num_rows($filters);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp/linkshare/deleteAdvertiser');


        $allSites = $this->site_model->getSites($filters);
        $data = array(
            'allSites' => $allSites,
            'form_action' => site_url('admincp/linkshare/siteAdvertisers/'),
        );

        $this->load->view('listAdvertisers',$data);
    }

    public function addAdvertiser()
    {
        $this->load->library('admin_form');
        $form = new Admin_form;

        $status = array('aprobat' => 'aprobat', 'in curs' => 'in curs', 'terminat' => 'terminat', 'respins' => 'respins');
        $this->load->model('category_model');
        $categorii = $this->category_model->getCategoryStatus();

        $form->fieldset('New Advertiser');
        $form->text('Site ID', 'id_site', '', 'Insert site ID.', true, 'e.g., 1', true);
        $form->text('Status ID', 'id_status', '', 'Insert status ID.', true, 'e.g., 1', true);
        $form->text('Status', 'status', '', 'Insert status.', true, 'e.g., Temp Rejected', true);
        $form->text('Categories', 'id_categories', '', 'Insert categories', true, 'e.g., 103 155 154 125 18 132', true);
        $form->text('Advertiser ID', 'mid', '', 'Insert advertiser ID.', true, 'e.g., 35171', true);
        $form->text('Advertiser name', 'name', '', 'Insert advertiser name.', true, 'e.g., MrWatch', true);
        $form->text('Offer also', 'offer_also', '', 'Insert offer_also.', true, 'e.g., 19.2', true);
        $form->text('Commission', 'commission', '', 'Insert commission.', true, 'e.g., sale : 0-2000 8%', true);
        $form->text('Offer id', 'offer_id', '', 'Insert offer_id.', true, 'e.g., 228980', true);
        $form->text('Offer name', 'offer_name', '', 'Insert offer_name.', true, 'e.g., Baseline Offer', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Add advertiser',
            'form_action' => site_url('admincp/linkshare/addAdvertiserValidate'),
            'action' => 'new'
        );

        $this->load->view('addAdvertiser', $data);
    }

    public function addAdvertiserValidate($action = 'new', $id = false)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Advertiser name', 'required|trim');
        $this->form_validation->set_rules('mid', 'Advertiser ID', 'required|trim');

        if ($this->form_validation->run() === false) {
            $this->notices->SetError('You have errors in your form.');
            $error = true;
        }

        if (isset($error)) {
            if ($action == 'new') {
                redirect('admincp/linkshare/addAdvertiser');
                return false;
            } else {
                redirect('admincp/linkshare/editAdvertiser/' . $id);
                return false;
            }
        }

        $fields['id_site'] = $this->input->post('id_site');
        $fields['id_status'] = $this->input->post('id_status');
        $fields['status'] = $this->input->post('status');
        $fields['id_categories'] = $this->input->post('id_categories');
        $fields['mid'] = $this->input->post('mid');
        $fields['name'] = $this->input->post('name');
        $fields['offer_also'] = $this->input->post('offer_also');
        $fields['commission'] = $this->input->post('commission');
        $fields['offer_id'] = $this->input->post('offer_id');
        $fields['offer_name'] = $this->input->post('offer_name');

        $this->load->model('advertiser_model');

        if ($action == 'new') {
            $type_id = $this->advertiser_model->newAdvertiser($fields);

            $this->notices->SetNotice('Advertiser added successfully.');

            redirect('admincp/linkshare/siteAdvertisers/1');
        } else {
            $this->advertiser_model->updateAdvertiser($fields, $id);
            $this->notices->SetNotice('Advertiser updated successfully.');

            redirect('admincp/linkshare/siteAdvertisers/1');
        }

        return true;
    }

    public function editAdvertiser($id)
    {
        $this->load->model('advertiser_model');
        $magazin = $this->advertiser_model->getAdvertiser($id);

        if (empty($magazin)) {
            die(show_error('No advertiser with this ID.'));
        }

        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('Magazin');
        $form->text('Site ID', 'id_site', $magazin['id_site'], 'Insert site ID.', true, 'e.g., 1', true);
        $form->text('Status ID', 'id_status', $magazin['id_status'], 'Insert status ID.', true, 'e.g., 1', true);
        $form->text('Status', 'status', $magazin['status'], 'Insert status.', true, 'e.g., Temp Rejected', true);
        $form->text('Categories', 'id_categories', $magazin['id_categories'], 'Insert categories', true, 'e.g., 103 155 154 125 18 132', true);
        $form->text('Advertiser ID', 'mid', $magazin['mid'], 'Insert advertiser ID.', true, 'e.g., 35171', true);
        $form->text('Advertiser name', 'name', $magazin['name'], 'Insert advertiser name.', true, 'e.g., MrWatch', true);
        $form->text('Offer also', 'offer_also', $magazin['offer_also'], 'Insert offer_also.', true, 'e.g., 19.2', true);
        $form->text('Commission', 'commission', $magazin['commission'], 'Insert commission.', true, 'e.g., sale : 0-2000 8%', true);
        $form->text('Offer id', 'offer_id', $magazin['offer_id'], 'Insert offer_id.', true, 'e.g., 228980', true);
        $form->text('Offer name', 'offer_name', $magazin['offer_name'], 'Insert offer_name.', true, 'e.g., Baseline Offer', true);
        
        $data = array(
            'form' => $form->display(),
            'form_title' => 'Edit advertiser',
            'form_action' => site_url('admincp/linkshare/addAdvertiserValidate/edit/' . $magazin['id']),
            'action' => 'edit',
        );

        $this->load->view('addAdvertiser', $data);
    }

    public function deleteAdvertiser($contents, $return_url)
    {

        $this->load->library('asciihex');
        $this->load->model('advertiser_model');

        $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
        $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

        foreach ($contents as $content) {
            $this->advertiser_model->deleteAdvertiser($content);
        }

        $this->notices->SetNotice('Advertisers successfully removed.');

        redirect($return_url);

        return true;
    }

    public function refreshAdvertisers($token, $status)
    {
        $this->db->query("TRUNCATE TABLE linkshare_advertisers");
        $this->load->model('site_model');
        $aux = $this->site_model->getSiteByToken($token);
        $id_site = $aux['id'];
        $aux = '';
        $i = 0;
        $cate = 0;

        $j = 1;
        $statuses = array();
        if ($status == 'all') {
            $this->load->model('status_model');
            $aux = $this->status_model->getStatuses();
            foreach ($aux as $val) {
                $statuses[] = str_replace(' ', '%20', $val['id_status']);
            }
            $j = count($statuses);
        } else {
            $statuses[] = $status;
        }

        while ($j > 0) {
            $mids = array();
            $aux = file_get_contents('http://lld2.linksynergy.com/services/restLinks/getMerchByAppStatus/' . $token . '/' . $statuses[$j - 1]);

            $categories = simplexml_load_string($aux, "SimpleXMLElement", LIBXML_NOCDATA);
            //echo $categories->getName().'<br/>';
            if (isset($categories)) {
                $kids = $categories->children('ns1', true);
                //var_dump(count($kids));

                $this->load->model('status_model');

                foreach ($kids as $child) {
                    $mids[$i]['id_site'] = $id_site;
                    $mids[$i]['id_status'] = mysql_real_escape_string($this->status_model->getStatusByApplicationStatus($child->applicationStatus));
                    $mids[$i]['status'] = mysql_real_escape_string($child->applicationStatus);
                    $mids[$i]['id_categories'] = mysql_real_escape_string($child->categories);
                    $mids[$i]['mid'] = mysql_real_escape_string($child->mid);
                    $mids[$i]['name'] = mysql_real_escape_string($child->name);
                    $mids[$i]['offer_also'] = mysql_real_escape_string($child->offer->alsoName);
                    $mids[$i]['commission'] = mysql_real_escape_string($child->offer->commissionTerms);
                    $mids[$i]['offer_id'] = mysql_real_escape_string($child->offer->offerId);
                    $mids[$i]['offer_name'] = mysql_real_escape_string($child->offer->offerName);
                    $i++;
                }
            }

            $statuses[$j - 1] = str_replace('%20', ' ', $statuses[$j - 1]);
            $this->load->model('status_model');
            $id_status = $this->status_model->getStatusByName($statuses[$j - 1]);

            $this->load->model('advertiser_model');
            $this->advertiser_model->deleteAdvertiserByStatus($id_site, $id_status);
            $cate += $this->advertiser_model->newAdvertisers($mids);

            $i = 0;
            unset($mids);
            $j--;
        }


        $this->notices->SetNotice($cate . ' advertisers updated successfully.');
        redirect('admincp/linkshare/siteAdvertisers/' . $id_site);
    }

    public function parseAdvertisers($token, $status)
    {
        //error_reporting(E_ALL);
        //ini_set('display_errors',1);
        $mids = array();
        $aux = '';
        $this->load->model('site_model');
        $aux = $this->site_model->getSiteByToken($token);
        $i = 0;
        $site = $aux['name'];
        $offset = 0;
        if (isset($_GET['offset']) && $_GET['offset'])
            $offset = $_GET['offset'];

        $j = 1;
        $statuses = array();
        if ($status == 'all') {
            $this->load->model('status_model');
            $aux = $this->status_model->getStatuses();
            foreach ($aux as $val) {
                $statuses[] = str_replace(' ', '%20', $val['id_status']);
            }
            $j = count($statuses);
        } else {
            $statuses[] = $status;
        }
        
//         print '<pre>';
//          print_r($statuses);
//          echo "j=$j<br/>";
//          die; 

        while ($j > 0) {
            $aux = file_get_contents('http://lld2.linksynergy.com/services/restLinks/getMerchByAppStatus/' . $token . '/' . $statuses[$j - 1]);
            $categories = simplexml_load_string($aux, "SimpleXMLElement", LIBXML_NOCDATA);
            //echo $categories->getName().'<br/>';die;
            if (isset($categories)) {
                $kids = $categories->children('ns1', true);
                //var_dump(count($kids));die;

                foreach ($kids as $child) {
                    $mids[$i]['id'] = $i + 1;
                    $mids[$i]['site'] = $site;
                    $mids[$i]['id_status'] = $child->applicationStatus;
                    $mids[$i]['status'] = $child->applicationStatus;
                    $mids[$i]['id_categories'] = $child->categories;
                    $mids[$i]['mid'] = $child->mid;
                    $mids[$i]['name'] = $child->name;
                    $mids[$i]['offer_also'] = $child->offer->alsoName;
                    $mids[$i]['commission'] = $child->offer->commissionTerms;
                    $mids[$i]['offer_id'] = $child->offer->offerId;
                    $mids[$i]['offer_name'] = $child->offer->offerName;
                    $mids[$i]['limit'] = 10;
                    $mids[$i]['offset'] = $offset;
                    $i++;
                }
            }

            $j--;
            //print '<pre>';print_r($mids);die;
        }

        $this->admin_navigation->module_link('Add parsed advertisers', site_url('admincp/linkshare/parseAdvertisersAdd/' . $token . '/' . $status));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '5%'),
            array(
                'name' => 'Site',
                'width' => '10%'),
            array(
                'name' => 'Status ID',
                'width' => '5%'),
            array(
                'name' => 'Status',
                'width' => '5%'),
            array(
                'name' => 'Categories',
                'width' => '15%'),
            array(
                'name' => 'Mid',
                'width' => '10%'),
            array(
                'name' => 'Name',
                'width' => '10%'),
            array(
                'name' => 'Offer alias',
                'width' => '10%'),
            array(
                'name' => 'Commission',
                'width' => '10%'),
            array(
                'name' => 'Offer ID',
                'width' => '10%'
            ),
            array('name' => 'Offer name',
                'width' => '10%'
            ),
        );

        $this->dataset->columns($columns);
        $this->dataset->datasource('advertiser_model', 'parseAdvertiser', $mids);
        $this->dataset->base_url(site_url('admincp/linkshare/parseAdvertisers/' . $token) . '/' . $status);
        $this->dataset->rows_per_page(10);

        // total rows
        $total_rows = count($mids);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        $this->load->view('listAdvertisersParsed');
    }


    public function parseAdvertisersAdd($token, $status)
    {
        //error_reporting(E_ALL);
        //ini_set('display_errors',1);            
        $this->load->model('site_model');
        $aux = $this->site_model->getSiteByToken($token);
        $id_site = $aux['id'];
        $aux = '';
        $i = 0;
        $cate = 0;

        $j = 1;
        $statuses = array();
        if ($status == 'all') {
            $this->load->model('status_model');
            $aux = $this->status_model->getStatuses();
            foreach ($aux as $val) {
                $statuses[] = str_replace(' ', '%20', $val['id_status']);
            }
            $j = count($statuses);
        } else {
            $statuses[] = $status;
        }

        while ($j > 0) {
            $mids = array();
            $aux = file_get_contents('http://lld2.linksynergy.com/services/restLinks/getMerchByAppStatus/' . $token . '/' . $statuses[$j - 1]);

            $categories = simplexml_load_string($aux, "SimpleXMLElement", LIBXML_NOCDATA);
            //echo $categories->getName().'<br/>';
            if (isset($categories)) {
                $kids = $categories->children('ns1', true);
                //var_dump(count($kids));

                $this->load->model('status_model');

                foreach ($kids as $child) {
                    $mids[$i]['id_site'] = $id_site;
                    $mids[$i]['id_status'] = mysql_real_escape_string($this->status_model->getStatusByApplicationStatus($child->applicationStatus));
                    $mids[$i]['status'] = mysql_real_escape_string($child->applicationStatus);
                    $mids[$i]['id_categories'] = mysql_real_escape_string($child->categories);
                    $mids[$i]['mid'] = mysql_real_escape_string($child->mid);
                    $mids[$i]['name'] = mysql_real_escape_string($child->name);
                    $mids[$i]['offer_also'] = mysql_real_escape_string($child->offer->alsoName);
                    $mids[$i]['commission'] = mysql_real_escape_string($child->offer->commissionTerms);
                    $mids[$i]['offer_id'] = mysql_real_escape_string($child->offer->offerId);
                    $mids[$i]['offer_name'] = mysql_real_escape_string($child->offer->offerName);
                    $i++;
                }
            }

            $statuses[$j - 1] = str_replace('%20', ' ', $statuses[$j - 1]);
            $this->load->model('status_model');
            $id_status = $this->status_model->getStatusByName($statuses[$j - 1]);

            $this->load->model('advertiser_model');
            $this->advertiser_model->deleteAdvertiserByStatus($id_site, $id_status);
            $cate += $this->advertiser_model->newAdvertisers($mids);

            $i = 0;
            unset($mids);
            $j--;
        }


        $this->notices->SetNotice($cate . ' parsed advertisers added successfully.');
        redirect('admincp/linkshare/siteAdvertisers/' . $id_site);
    }

    public function getXmlCookie($scope = 2531438)
    {
        $CI =& get_instance();
        $this->load->library('admin_form');        
        
        $form = new Admin_form;
        
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        $config = new LinkshareConfig();
        
        $accessToken = $config->setSiteCookieAndGetAccessToken($CI, $scope);
        
        //echo "accessToken = $accessToken" . PHP_EOL;
                                                  
        $request = new CurlApi(LinkshareConfig::URL_ADVERTISERS_APPROVED);
        $request->setHeaders($config->getMinimalHeaders($accessToken));
        $request->setGetData();
        $request->send();
              
        $responseObj = $request->getFormattedResponse();
        
        if (!$responseObj) {
            $this->notices->SetError('Refresh token');
            $responseObj['header'] = $responseObj['body'] = 'ERROR';
        } else {
            //print '<pre>';
            //print_r($responseObj['header']);
        }
   
//        die();
        
        $form->fieldset('Xml');
        $form->textarea('Header', 'header', $responseObj['header'], 'header', true, 'e.g., header', false);
        $form->textarea('Body', 'body', $responseObj['body'], 'body', true, 'e.g., body', true);
        
        $data = array(
            'form' => $form->display(),
            'form_title' => 'XML',
            'form_action' => site_url('admincp/linkshare/')
        );

        $this->load->view('xml', $data);
    }      
    
    public function parseAdvertisersTEST($scope = 2531438)
    {
        error_reporting(E_ALL);
        ini_set('display_errors',1);
        
        $mids = array();
        
        $linkshareConstants = array(LinkshareConfig::URL_ADVERTISERS_APPROVED,
                                    "URL_ADVERTISERS_PERM_REJECTED",
                                    "URL_ADVERTISERS_PERM_REMOVED",
                                    "URL_ADVERTISERS_SELF_REMOVED",
                                    "URL_ADVERTISERS_TEMP_REMOVED",
                                    "URL_ADVERTISERS_TEMP_REJECTED",
                                    "URL_ADVERTISERS_WAIT");
        
        /* Get XML with OAUTH2 */
        
        
        
        $CI =& get_instance();
        $this->load->library('admin_form');                        
        
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        $config = new LinkshareConfig();
        
        $accessToken = $config->setSiteCookieAndGetAccessToken($CI, $scope);
        
        //echo "accessToken = $accessToken" . PHP_EOL;
                                                  
        $request = new CurlApi(LinkshareConfig::URL_ADVERTISERS_APPROVED);
        $request->setHeaders($config->getMinimalHeaders($accessToken));
        $request->setGetData();
        $request->send();                      
              
        $responseObj = $request->getFormattedResponse();
        
//        print '<pre>';
//        print_r($responseObj['body']);
//        die; 

        $aux = $responseObj['body'];
        $categories = simplexml_load_string($aux, "SimpleXMLElement", LIBXML_NOCDATA);
        //echo $categories->getName().'<br/>';die;
        if (isset($categories)) {
            $kids = $categories->children('ns1', true);
            //var_dump(count($kids));die;

            foreach ($kids as $child) {
//                $mids[$i]['id'] = $i + 1;
//                $mids[$i]['site'] = $site;
                $mids[$i]['id_status'] = $child->applicationStatus;
                $mids[$i]['status'] = $child->applicationStatus;
                $mids[$i]['id_categories'] = $child->categories;
                $mids[$i]['mid'] = $child->mid;
                $mids[$i]['name'] = $child->name;
                $mids[$i]['offer_also'] = $child->offer->alsoName;
                $mids[$i]['commission'] = $child->offer->commissionTerms;
                $mids[$i]['offer_id'] = $child->offer->offerId;
                $mids[$i]['offer_name'] = $child->offer->offerName;
                $mids[$i]['limit'] = 10;
//                $mids[$i]['offset'] = $offset;
                $i++;
            }
        }

        $this->admin_navigation->module_link('Add parsed advertisers', site_url('admincp/linkshare/parseAdvertisersAdd/'));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '5%'),
//            array(
//                'name' => 'Site',
//                'width' => '10%'),
            array(
                'name' => 'Status ID',
                'width' => '5%'),
            array(
                'name' => 'Status',
                'width' => '5%'),
            array(
                'name' => 'Categories',
                'width' => '15%'),
            array(
                'name' => 'Mid',
                'width' => '10%'),
            array(
                'name' => 'Name',
                'width' => '10%'),
            array(
                'name' => 'Offer alias',
                'width' => '10%'),
            array(
                'name' => 'Commission',
                'width' => '10%'),
            array(
                'name' => 'Offer ID',
                'width' => '10%'
            ),
            array('name' => 'Offer name',
                'width' => '10%'
            ),
        );

        $this->dataset->columns($columns);
        $this->dataset->datasource('advertiser_model', 'parseAdvertiser', $mids);
        $this->dataset->base_url(site_url('admincp/linkshare/parseAdvertisersTEST/'));
        $this->dataset->rows_per_page(30);

        // total rows
        $total_rows = count($mids);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        $this->load->view('listAdvertisersParsed');
    }

}

class Admincp2 extends Admincp_Controller
{
    /**
     * Default coupon-land
     * 
     * @var int 
     */
    private $siteID = 2531438;
    
    public function __construct()
    {
        parent::__construct();

        $this->admin_navigation->parent_active('linkshare');
        
        $siteID = $this->input->cookie('siteID');
        // change if value already in cookie
        if ($siteID) {
            $this->siteID = $siteID;
        }                

    }

    public function index()
    {
        redirect('admincp2/linkshare/listCategories');
    }

    public function listCategories()
    {
        $this->admin_navigation->module_link('Add category', site_url('admincp2/linkshare/addCategory'));
        $this->admin_navigation->module_link('Parse linkshare category', site_url('admincp2/linkshare/parseCategories'));
        $this->admin_navigation->module_link('Back', site_url('admincp/linkshare/info'));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '15%'),
            array(
                'name' => 'Categories ID #',
                'width' => '15%'),
            array(
                'name' => 'Name',
                'width' => '40%'),
            array(
                'name' => 'Actions',
                'width' => '30%'
            )
        );

        $this->dataset->columns($columns);
        $this->dataset->datasource('category_model', 'getCategories');
        $this->dataset->base_url(site_url('admincp2/linkshare/listCategories'));
        $this->dataset->rows_per_page(1000);

        // total rows
        $total_rows = $this->db->get('linkshare_categories')->num_rows();
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp2/linkshare/deleteCategory');

        $this->load->view('listCategories');
    }

    public function addCategory()
    {
        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('New Categories');
        $form->text('Category ID linkshare', 'id_category', '', 'Insert category name according to linkshare', true, 'e.g. 17', true);
        $form->text('Name', 'name', '', 'Insert category name', true, 'e.g. Business & Career', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Add category',
            'form_action' => site_url('admincp2/linkshare/addCategoryValidate'),
            'action' => 'new'
        );

        $this->load->view('addCategory', $data);
    }

    public function addCategoryValidate($action = 'new', $id = false)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('id_category', 'Id categorie linkshare', 'required|trim');

        if ($this->form_validation->run() === false) {
            $this->notices->SetError('Required fields.');
            $error = true;
        }

        if (isset($error)) {
            if ($action == 'new') {
                redirect('admincp2/linkshare/addCategory');
                return false;
            } else {
                redirect('admincp2/linkshare/editCategory/' . $id);
                return false;
            }
        }

        $this->load->model('category_model');

        $fields['name'] = $this->input->post('name');
        $fields['id_category'] = $this->input->post('id_category');

        if ($action == 'new') {
            $this->category_model->newCategory($fields);

            $this->notices->SetNotice('Category added successfully.');

            redirect('admincp2/linkshare/listCategories/');
        } else {
            $this->category_model->updateCategory($fields, $id);
            $this->notices->SetNotice('Category updated successfully.');

            redirect('admincp2/linkshare/listCategories/');
        }

        return true;
    }

    public function editCategory($id)
    {
        $this->load->model('category_model');
        $categorie = $this->category_model->getCategory($id);

        if (empty($categorie)) {
            die(show_error('No category with this ID.'));
        }

        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('Category');
        $form->text('Id category linkshare', 'id_category', $categorie['id_category'], 'Insert category id according to linkshare.', true, 'e.g., 17', true);
        $form->text('Name', 'name', $categorie['name'], 'Insert category name.', true, 'e.g., Business & Career', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Edit Category',
            'form_action' => site_url('admincp2/linkshare/addCategoryValidate/edit/' . $categorie['id']),
            'action' => 'edit',
        );

        $this->load->view('addCategory', $data);
    }

    public function deleteCategory($contents, $return_url)
    {

        $this->load->library('asciihex');
        $this->load->model('category_model');

        $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
        $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

        foreach ($contents as $content) {
            $this->category_model->deleteCategory($content);
        }

        $this->notices->SetNotice('Category successfully removed.');

        redirect($return_url);

        return true;
    }

    public function parseCategoryUrl()
    {
        $url = "http://helpcenter.linkshare.com/publisher/questions.php?questionid=709";

        $cUrl = curl_init();
        curl_setopt($cUrl, CURLOPT_URL, $url);
        curl_setopt($cUrl, CURLOPT_HTTPGET, 1);
        //curl_setopt($cUrl, CURLOPT_USERAGENT,'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.2; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0)');
        //curl_setopt($cUrl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
        curl_setopt($cUrl, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($cUrl, CURLOPT_TIMEOUT, '3');
        //$pageContent = trim(curl_exec($cUrl));
        $pageContent = curl_exec($cUrl);
        curl_close($cUrl);

        //preg_match_all('|<a href="(.*)" title="View all (.*)">|',$pageContent,$out);
        preg_match_all('|<span>(.*)</span>|', $pageContent, $out);
        $categories = array();
        //$data['content'] = print_r($out,1);

        foreach ($out[1] as $k => $v) {
            if ($k > 2) {
                if ($k % 2)
                    $ids[] = $v;
                else
                    $cat[] = $v;
            }
        }

        $categories = array();
        foreach ($ids as $k => $v) {
            $categories[] = array('id_category' => $v,
                'name' => $cat[$k]
            );
        }

        //print '<pre>';print_r($categories);die;

        return $categories;
    }

    public function parseCategories()
    {
        $this->admin_navigation->module_link('Update linkshare categories', site_url('admincp2/linkshare/refreshCategories/'));

        $categories = $this->parseCategoryUrl();

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'Category ID #',
                'width' => '35%'),
            array(
                'name' => 'Name',
                'width' => '65%'),
        );

        $filters['categories'] = $categories;
        $filters['limit'] = 50;
        if (isset($_GET['offset']))
            $filters['offset'] = $_GET['offset'];
        $this->dataset->rows_per_page(50);

        $this->dataset->columns($columns);
        $this->dataset->datasource('category_model', 'getCategoriesParse', $filters);
        $this->dataset->base_url(site_url('admincp2/linkshare/parseCategories'));


        // total rows
        $total_rows = count($categories);
        $this->dataset->total_rows($total_rows);

        $data['cate'] = $total_rows;
        $this->dataset->initialize();

        $this->load->view('parseCategories', $data);
    }

    public function refreshCategories() {
        $this->db->query("TRUNCATE TABLE linkshare_categories");
        $categories = $this->parseCategoryUrl();

        foreach ($categories as $cat) {
            $this->db->query("INSERT INTO  linkshare_categories (id_category,name) VALUES ('{$cat['id_category']}','{$cat['name']}')");
        }

        $data['cate'] = count($categories);

        $this->load->view('refreshCategories', $data);
    }

    public function parseCreativeCategorySite($id)
    {
        $this->admin_navigation->module_link('Update linkshare categories', site_url('admincp2/linkshare/refresh_categorii_site/' . $id));

        $aux = '';
        $aux = file_get_contents('http://lld2.linksynergy.com/services/restLinks/getCreativeCategories/c9b4a2805e6d69846a3b7c9f0c23c26249cb86bc50fe864ff13746a8ab7dc92f/216');

        $categories = simplexml_load_string($aux, "SimpleXMLElement", LIBXML_NOCDATA);
        //echo $categories->getName().'<br/>';
        $kids = $categories->children('ns1', true);
        //var_dump(count($kids));
        foreach ($kids as $child) {
            echo $child->catId . '<br/>';
            echo $child->catName . '<br/>';
            echo $child->mid . '<br/>';
            echo $child->nid . '<br/>----<br/>';
        }

        die;

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'Category ID #',
                'width' => '35%'),
            array(
                'name' => 'Name',
                'width' => '65%'),
        );

        $filters['categories'] = $categories;
        $filters['limit'] = 50;
        if (isset($_GET['offset']))
            $filters['offset'] = $_GET['offset'];
        $this->dataset->rows_per_page(50);

        $this->dataset->columns($columns);
        $this->dataset->datasource('category_model', 'getCategoriesParse', $filters);
        $this->dataset->base_url(site_url('admincp2/linkshare/parseCategories'));


        // total rows
        $total_rows = count($categories);
        $this->dataset->total_rows($total_rows);

        $data['cate'] = $total_rows;
        $this->dataset->initialize();

        $this->load->view('parseCategories', $data);
    }
    public function listCreativeCategory($id = 1)
    {
        $this->admin_navigation->module_link('Add creative category', site_url('admincp2/linkshare/addCreativeCategory'));
        $this->admin_navigation->module_link('Parse creative categories add them into DB', site_url('admincp2/linkshare/parseCreativeCategories/' . $id));
        $this->admin_navigation->module_link('Export CSV', site_url('admincp2/linkshare/export_csv/category_creative'));
        
        $this->load->library('dataset');
        $this->load->model('category_creative_model');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '15%'),
            array(
                'name' => 'Site',
                'width' => '15%'),
            array(
                'name' => 'Category ID #',
                'width' => '10%'),
            array(
                'name' => 'Name',
                'width' => '20%',
                'type' => 'text',
                'filter' => 'nume'),
            array(
                'name' => 'Mid',
                'width' => '15%',
                'type' => 'text',
                'filter' => 'mid'),
            array(
                'name' => 'Nid',
                'width' => '5%'),
            array(
                'name' => 'Actions',
                'width' => '20%'
            )
        );

        $filters = array();
        $filters['limit'] = 50;
        $filters['id_site'] = $id;
        $filters['name'] = true;

        $this->dataset->columns($columns);
        $this->dataset->datasource('category_creative_model', 'getCategories', $filters);
        $this->dataset->base_url(site_url('admincp2/linkshare/listCreativeCategory/' . $id));
        $this->dataset->rows_per_page(50);

        if (isset($_GET['offset']))
            $filters['offset'] = $_GET['offset'];

        if (isset($_GET['nume']))
            $filters['nume'] = $_GET['nume'];
        if (isset($_GET['mid']))
            $filters['mid'] = $_GET['mid'];

        $this->load->library('asciihex');
        $this->load->model('forms/form_model');

        if (isset($_GET['filters'])) {
            $aux = unserialize(base64_decode($this->asciihex->HexToAscii($_GET['filters'])));
            if (isset($aux['nume']))
                $filters['nume'] = $aux['nume'];
        }

        // total rows
        $this->db->where('id_site', $id);
        $total_rows = $this->category_creative_model->getCategoriesLines($filters);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp2/linkshare/deleteCreativeCategory');

        $this->load->view('listCreativeCategory');
    }

    public function addCreativeCategory()
    {
        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('New creative category');
        $form->text('Site ID linkshare', 'id_site', '', 'Insert site ID', true, 'e.g., 1', true);
        $form->text('Creative category ID', 'cat_id', '', 'Insert creative category ID according to linkshare', true, 'e.g., 200229205', true);
        $form->text('Name', 'name', '', 'Insert category name', true, 'e.g., MAT (Mission Against Terror)', true);
        $form->text('Mid linkshare', 'mid', '', 'Insert mid', true, 'e.g., 37517', true);
        $form->text('Nid linkshare', 'nid', '', 'Insert nid', true, 'e.g., 1', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Add Category',
            'form_action' => site_url('admincp2/linkshare/addCreativeCategoryValidate'),
            'action' => 'new'
        );

        $this->load->view('addCreativeCategory', $data);
    }

    public function addCreativeCategoryValidate($action = 'new', $id = false)
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('cat_id', 'Creative category ID linkshare', 'required|trim');

        if ($this->form_validation->run() === false) {
            $this->notices->SetError('Required fields.');
            $error = true;
        }

        if (isset($error)) {
            if ($action == 'new') {
                redirect('admincp2/linkshare/addCreativeCategory');
                return false;
            } else {
                redirect('admincp2/linkshare/editCreativeCategory/' . $id);
                return false;
            }
        }

        $this->load->model('category_creative_model');

        $fields['id_site'] = $this->input->post('id_site');
        $fields['cat_id'] = $this->input->post('cat_id');
        $fields['name'] = $this->input->post('name');
        $fields['mid'] = $this->input->post('mid');
        $fields['nid'] = $this->input->post('nid');

        if ($action == 'new') {
            $type_id = $this->category_creative_model->newCategory($fields);

            $this->notices->SetNotice('Creative category added successfully.');

            redirect('admincp2/linkshare/listCreativeCategory/');
        } else {
            $this->category_creative_model->updateCategory($fields, $id);
            $this->notices->SetNotice('Creative category updated successfully.');

            redirect('admincp2/linkshare/listCreativeCategory/');
        }

        return true;
    }

    public function editCreativeCategory($id)
    {
        $this->load->model('category_creative_model');
        $categorie = $this->category_creative_model->getCategory($id);

        if (empty($categorie)) {
            die(show_error('No category with this ID.'));
        }

        $this->load->library('admin_form');
        $form = new Admin_form;

        $form->fieldset('Creative category');
        $form->text('Site ID linkshare', 'id_site', $categorie['id_site'], 'Insert site ID', true, 'e.g., 1', true);
        $form->text('Creative category ID', 'cat_id', $categorie['cat_id'], 'Insert category ID according to linkshare', true, 'e.g., 200229205', true);
        $form->text('Name', 'name', $categorie['name'], 'Insert category name', true, 'e.g., MAT (Mission Against Terror)', true);
        $form->text('Mid linkshare', 'mid', $categorie['mid'], 'Insert mid', true, 'e.g., 37517', true);
        $form->text('Nid linkshare', 'nid', $categorie['nid'], 'Insert nid', true, 'e.g., 1', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Edit creative category',
            'form_action' => site_url('admincp2/linkshare/addCreativeCategoryValidate/edit/' . $categorie['id']),
            'action' => 'edit',
        );

        $this->load->view('addCreativeCategory', $data);
    }

    public function deleteCreativeCategory($contents, $return_url)
    {

        $this->load->library('asciihex');
        $this->load->model('category_creative_model');

        $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
        $return_url = base64_decode($this->asciihex->HexToAscii($return_url));

        foreach ($contents as $content) {
            $this->category_creative_model->deleteCategory($content);
        }

        $this->notices->SetNotice('Creative category removed successfully.');

        redirect($return_url);

        return true;
    }

    public function parseCreativeCategories($id)
    {
        //error_reporting(E_ALL);
        //ini_set('display_errors',1);
        include "app/third_party/LOG/Log.php";
        
        $mids = array();
        $aux = '';
        $this->load->model('site_model');
        $aux = $this->site_model->getSite($id);
        $token = $aux['token'];
        $i = 0;
        $site = $aux['name'];
        $offset = 0;
        if (isset($_GET['offset']) && $_GET['offset'])
            $offset = $_GET['offset'];

        $this->load->model('advertiser_model');
        $filters['id_site'] = $id;
        $this->load->model('status_model');
        $filters['id_status'] = $this->status_model->getStatusByName('approved');
        $mag = array();
        $mag = $this->advertiser_model->getAdvertisers($filters);
        foreach ($mag as $val) {
            $mids[] = $val['mid'];
        }

        $j = count($mids);

        $cate = 0;

        while ($j > 0) {
            $cats = array();
            $aux = @file_get_contents('http://lld2.linksynergy.com/services/restLinks/getCreativeCategories/' . $token . '/' . $mids[$j - 1]);
            $categories = simplexml_load_string($aux, "SimpleXMLElement", LIBXML_NOCDATA);
            //echo $categories->getName().'<br/>';die;
            if (isset($categories) && is_object($categories)) {
                $kids = $categories->children('ns1', true);
                //var_dump(count($kids));die;

                foreach ($kids as $child) {
                    $cats[$i]['id'] = $i + 1;
                    $cats[$i]['id_site'] = $id;
                    $cats[$i]['cat_id'] = (string) $child->catId;
                    $cats[$i]['name'] = (string) $child->catName;
                    $cats[$i]['mid'] = (string) $child->mid;
                    $cats[$i]['nid'] = (string) $child->nid;
                    //$cats[$i]['limit'] = 10;
                    //$cats[$i]['offset'] = $offset;
                    $i++;
                }

                $cate += count($cats);

                $this->load->model('category_creative_model');
                //delete old categories for this mid and this site id
                $this->category_creative_model->deleteCategoryByMid($id, $mids[$j - 1]);

                foreach ($cats as $cat) {
                    $cat['id'] = '';
                    $this->category_creative_model->newCategory($cat);
                }

                //print '<pre>';print_r($cats);die;
            } else {
                $message = 'http://lld2.linksynergy.com/services/restLinks/getCreativeCategories/' . $token . '/' . $mids[$j - 1] . ' xml eroare ';
                Log::error($message);
            }
            $j--;
        }

        $this->admin_navigation->module_link('See parsed creative category', site_url('admincp2/linkshare/listCreativeCategory/' . $id));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'Parsed creative categories',
                'width' => '50%'),
            array(
                'name' => 'Site ID',
                'width' => '50%'),
        );

        $catz = array();
        $catz[0]['site'] = $site;
        $catz[0]['cate'] = $cate;

        $this->dataset->columns($columns);
        $this->dataset->datasource('category_creative_model', 'parseCategories', $catz);
        $this->dataset->base_url(site_url('admincp2/linkshare/parseCreativeCategories/' . $id));
        $this->dataset->rows_per_page(10);

        // total rows
        $total_rows = 1;
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        $this->load->view('listCreativeCategoryParsed');
    }
    
     function updateFilters() {
        $this->load->library('asciihex');
        
        // old filters
        $filters_decode = unserialize(base64_decode($this->asciihex->HexToAscii($_POST['filterz'])));
        
        $filters = array();
        
        foreach ($_POST as $key => $val) {
            if (in_array($val, array('filter results'))) {
                unset($_POST[$key]);
            }
        }
        if (!empty($_POST['merged_category'])) {
            $filters['merged_category'] = $_POST['merged_category'];
        }
        
        if (!empty($_POST['all_page_category'])) {
            $filters['all_page_category'] = $_POST['all_page_category'];
        }
        $all_page_category_ok = array();
        $all_page_category = explode(',', $filters['all_page_category']);
        // remove 0
        foreach ($all_page_category as $cat) {
            if ($cat) {
                // current page checkboxes
                $all_page_category_ok[] = $cat;
            }
        }                
        
        if (!empty($_POST['check_category'])) {
            $filters['check_category'] = $_POST['check_category'];
        }
        
        $check_category_ok = array();
        $check_category = explode(',', $filters['check_category']);
        
        // remove 0
        foreach ($check_category as $cat) {
            if ($cat) {
                //current checkboxes
                $check_category_ok[] = $cat;
            }
        }
        
        $filters_decode_check_category_ok = array();
        $filters_decode_check_category = explode(',', $filters_decode['check_category']);
        
        // remove 0
        foreach ($filters_decode_check_category as $cat) {
            if ($cat) {
                //past checkboxes
                $filters_decode_check_category_ok[] = $cat;
            }
        }

        // add new checkboxes
        foreach ($check_category_ok as $cat) {
            if (!in_array($cat, $filters_decode_check_category_ok)) {
                $filters_decode_check_category_ok[] = $cat;
            }
        }
        
        $checkboxes_to_remove = array();
        $checkboxes_to_remove = array_diff($all_page_category_ok, $check_category_ok);
        // remove old checkboxes from this page
        $filters_decode_check_category_ok = array_diff($filters_decode_check_category_ok, $checkboxes_to_remove);                
        
        $filters_decode_check_category_ok = implode(',', $filters_decode_check_category_ok);
        
        $filters['check_category'] = $filters_decode_check_category_ok;
        
        if (!empty($_POST['nume'])) {
            $filters['nume'] = $_POST['nume'];
        }
        if (!empty($_POST['mid'])) {
            $filters['mid'] = $_POST['mid'];
        }
        if (!empty($_POST['offset'])) {
            $filters['offset'] = $_POST['offset'];
        }
        
        if (!empty($_POST['limit'])) {
            $filters['limit'] = $_POST['limit'];
        }
        
        //print_r($filters);
        
        $filters = $this->CI->asciihex->AsciiToHex(base64_encode(serialize($filters)));
        echo $filters;
    }

    public function joinCreativeCategory($id = 1)
    {
        $this->admin_navigation->module_link('View Merged Categories', site_url('admincp2/linkshare/listMergedCategories/'));

        $this->load->library('dataset');
        $this->load->model('category_creative_model');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '5%'),
            array(
                'name' => 'SELECT CATEG TO MERGE',
                'width' => '10%'),
            array(
                'name' => 'SITE',
                'width' => '15%'),
            array(
                'name' => 'CATEGORY ID #',
                'width' => '30%'),
            array(
                'name' => 'Name',
                'width' => '20%',
                'type' => 'text',
                'filter' => 'nume'),
            array(
                'name' => 'Mid',
                'width' => '10%',
                'type' => 'text',
                'filter' => 'mid'),
            array(
                'name' => 'Nid',
                'width' => '5%'),
            array(
                'name' => 'Actions',
                'width' => '5%'
            )
        );

        $filters = $filters_decode = array();
        //$filters['limit'] = 50;
        $filters['id_site'] = $id;
        $filters['name'] = true; 
        
        if (isset($_GET['offset'])){
        $filters['offset'] = $_GET['offset'];}
       
        if (isset($_GET['filterz'])) {
            $filters_decode = unserialize(base64_decode($this->asciihex->HexToAscii($_GET['filterz'])));            
        } elseif (isset($_POST['filterz'])) {
            $filters_decode = unserialize(base64_decode($this->asciihex->HexToAscii($_POST['filterz'])));            
        }
        
//        if (isset($filters_decode['nume']))
//                $filters['nume'] = $filters_decode['nume'];
//        
//        $filters['offset'] = $filters_decode['offset'];
       
        if (isset($filters_decode) && !empty($filters_decode)) {
            foreach ($filters_decode as $key => $val) {
                $filters[$key] = $val;
            }
        }
       
        foreach ($_POST as $key => $val) {
            if (in_array($val, array('filter results'))) {
                unset($_POST[$key]);
            }
        }
        
        print '<pre>FILTERS DECODE';
        print_r($filters_decode);
        print '</pre>';      
 
        if ($_POST['saving'] == 'ok'){
            if (isset($_POST['merged_category']) && !empty($filters_decode['check_category'])){
                $checked_values = array();
                $checked_values = explode(',', $filters_decode['check_category']);
                if(!empty($_POST['check_category'])){
                    foreach ($_POST['check_category'] as $cat) {
                        if ($cat) {
                            //curent checkboxes
                            $checked_values[] = $cat;
                        }
                    }
                }  
                $id_merged_category = $this->category_creative_model->newMergedCategory($_POST['merged_category']);            
                $id_join_category = $this->category_creative_model->newJoinCategory($id_merged_category,$checked_values);
                
                $message = "New Merged Category (".$_POST['merged_category'].") added successfully.";
                $this->notices->SetNotice($message);
                redirect(site_url('admincp2/linkshare/joinCreativeCategory/1'));
                
            }elseif(isset($_POST['merged_category']) && !empty($_POST['check_category'])){
                $id_merged_category = $this->category_creative_model->newMergedCategory($_POST['merged_category']);            
                $this->category_creative_model->newJoinCategory($id_merged_category,$_POST['check_category']);

                $message = "New Merged Category (".$_POST['merged_category'].") added successfully.";
                $this->notices->SetNotice($message);
                redirect(site_url('admincp2/linkshare/joinCreativeCategory/1'));

            }  else {
                $this->notices->SetNotice('Check if any checkboxes are selected!');
                redirect(site_url('admincp2/linkshare/joinCreativeCategory/1'));
            }
        }

//        $filters['nume'] = $filters_decode['nume'];
//        
//        $filters['mid'] = $filters_decode['mid'];
        
        $this->dataset->columns($columns);
        $this->dataset->datasource('category_creative_model', 'getCreativeForMerge', $filters);
        $this->dataset->base_url(site_url('admincp2/linkshare/joinCreativeCategory/' . $id));


        if (isset($_GET['nume']))
            $filters['nume'] = $_GET['nume'];

        if (isset($_GET['mid']))
            $filters['mid'] = $_GET['mid'];
        
        // &limit=10&offset=20
        if (isset($_POST['offset'])) {
            $_GET['offset'] = $_POST['offset'];
        } else {
            $_GET['offset'] = $filters_decode['offset'];
        }
        
        if (isset($_POST['limit'])) {
            $_GET['limit'] = $_POST['limit'];
        }
        
        print '<pre>POST';
        print_r($_POST);        
        print '</pre>';
        
        print '<pre>GET';        
        print_r($_GET);
        print '</pre>';

        $this->load->library('asciihex');
        $this->load->model('forms/form_model');

        // total rows
        $this->db->where('id_site', $id);
        $total_rows = $this->category_creative_model->getCategoriesLines($filters);

        $this->dataset->total_rows($total_rows); 
        
        $this->dataset->initialize();

        // add actions
        
        $data = array(
          'filterz' => isset($_GET['filterz']) ? $_GET['filterz'] : $_POST['filterz'],
          'name_search' => $filters['nume'],
          'mid_search' => $filters['mid'],
          'name_merged' => $filters['merged_category']
        );

        $this->load->view('joinCreativeCategory', $data);
    }
    
    public function listMergedCategories()
    {
        $this->admin_navigation->module_link('ADD ANOTHER Merged Categories', site_url('admincp2/linkshare/joinCreativeCategory'));

        $this->load->library('dataset');
        $this->load->model('category_creative_model');

        $columns = array(
            array(
                'name' => 'Merged Category Name',
                'width' => '20%'),
            array(
                'name' => 'ID merged categories',
                'width' => '20%'),
            array(
                'name' => 'Actions',
                'width' => '15%'
            )
        );

//        echo "<pre>";
//        print_r($result);
//        echo "</pre>";
//        die;
        
        $filters = array();
        $this->dataset->columns($columns);
        $this->dataset->datasource('category_creative_model','listMergedCategory', $filters);
        $this->dataset->base_url(site_url('admincp2/linkshare/listMergedCategories'));
        $this->dataset->rows_per_page(10);

        // total rows
        //$total_rows = $this->db->get('linkshare_categories')->num_rows();
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp2/linkshare/deleteMergedCatory');

        $this->load->view('listMergedCategory');
    }
    
    public function editMergedCategory($id)
    {
        $this->load->model('category_creative_model');
        $fields['name'] = $this->input->post('category_name');
        
        $category_name = $this->category_creative_model->getMergedCategoryByID($id);
                
//        echo "<pre>";
//        print_r($category_name);
//        echo "</pre>";
//        die;

        if (!empty($fields['name']) && isset($fields['name'])) {
            
            $this->category_creative_model->updateMergedCategory($fields, $id);
            $this->notices->SetNotice('Category updated successfully.');

            redirect('admincp2/linkshare/listMergedCategories/');
            return true;
        }
        
        $data = array(
               	'merged_category_name' => $category_name[0]['name'],
                'form_action' => site_url('admincp2/linkshare/editMergedCategory/'.$id),
	);
        
    	$this->load->view('editMergedCategory',$data);

    }
    
    function deleteMergedCatory($contents, $return_url) {
        $this->load->library('asciihex');
        $this->load->model('category_creative_model');
        
        $contents = unserialize(base64_decode($this->asciihex->HexToAscii($contents)));
        $return_url = base64_decode($this->asciihex->HexToAscii($return_url));
        
        foreach ($contents as $content) {
            $this->category_creative_model->deleteMergedCategory($content);
        }
        
        $this->notices->SetNotice('Merged Category deleted successfully.');
        redirect($return_url);
        return TRUE;
    }
    
    function ajaxDeleteCategory($MergedCategory_id, $JoinsCategory_id){
        $this->load->model('category_creative_model');
        $this->category_creative_model->deleteJoinCategory($MergedCategory_id, $JoinsCategory_id);
        
        $message = "Joins Category id:".$JoinsCategory_id." from Merged Category id:".$MergedCategory_id."deleted successfully.";
        $this->notices->SetNotice($message);
        
        $return_url = site_url('admincp2/linkshare/listMergedCategories');
        redirect($return_url);
        return TRUE;
    }
    
    function export_csv($table) {
        // Create the real model name based on the $table variable
        $model = $table.'_model';
        
        // Since we might be dealing with very large data
        // Ensure we have time to process it
        set_time_limit(0);
        
        $temp_file = FCPATH . 'writeable/' . $table . '-' . date("Y-m-d") . '.csv';

        $this->load->helper('file');
        $this->load->library('array_to_csv');
        $this->load->model($model);

        // If the file already exists, we need
        // to get rid of it since this is a new download.
        $f = @fopen($temp_file, 'r+');
        if ($f !== false)
        {
            ftruncate($f, 0);
            fclose($f);
        }

        $csv_file = fopen($temp_file, 'a');
        $need_header = true;    // Do we need the CSV header?

        $query = $this->$model->get_all_categories();
        
        foreach($query as $row)
        {
            // ID(category id);Active (0/1);Name *;Parent category;Root category (0/1);Description;Meta title;Meta keywords;Meta description;URL rewritten;Image URL;ID / Name of shop(MID)
            $main_array[] = array(
                'id' => $row->cat_id,
                'active' => 1,
                'name' => $row->name,
                'parent' => 0,
                'root' => 1,
                'description' => $row->name,
                'meta_title' => $row->name,
                'meta_keywords' => $row->name,
                'meta_description' => $row->name,
                'url_rewritten' => '',
                'image_url' => '',
                'shop_name' => $row->mid
            );
        }
        $this->array_to_csv->input($main_array);
        $data = $this->array_to_csv->output($need_header, true);
        fwrite($csv_file, $data);
        
        // Make sure we're not output buffering anymore so we don't
        // run out of memory due to buffering.
        if (ob_get_level() > 0)
        {
            ob_end_flush();
        }
        //ob_implicit_flush(true);

        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=export-" . $table . '-' . date("Y-m-d") . ".csv");
        readfile($temp_file);
        die();
    }
  
    public function getXmlCreativeCategories()
    {            
        $CI =& get_instance();
        $this->load->library('admin_form'); 
        $form = new Admin_form;

        $this->load->model(array('site_model','advertiser_model'));
        
        $siteRow = $this->site_model->getSiteBySID($this->siteID);

        $temp = $this->advertiser_model->getTempAdvertisers($siteID['id']);
        $current = array_merge($this->advertiser_model->getAdvertisers(array('id_status' => 1,'id_site'=>$siteRow['id'])));

        foreach ($current as $val){
            $allMids[] = array( 'mid' => $val['mid'],'name' => $val['name']);
        }
     
//        echo "<pre>";
//        print_r($_GET['mid']);
//        die();
        $this->admin_navigation->module_link('Back', site_url('admincp/linkshare/getXML/'));

        $responseObj = array();

        if(isset($_GET['mid'])){
            
            $categoriesRequestUrl = 'https://api.rakutenmarketing.com/linklocator/1.0/getCreativeCategories/'.$_GET['mid'];
            
            $config = new LinkshareConfig();

            $accessToken = $config->setSiteCookieAndGetAccessToken($CI, $this->siteID);

            //echo "accessToken = $accessToken" . PHP_EOL;

            $request = new CurlApi($categoriesRequestUrl);
            $request->setHeaders($config->getMinimalHeaders($accessToken));
            $request->setGetData();
            $request->send();

            $responseObj = $request->getFormattedResponse();

            if (!$responseObj) {
                $this->notices->SetError('Refresh token');
                $responseObj['header'] = $responseObj['body'] = 'ERROR';
            } else {
                //print '<pre>';
                //print_r($responseObj['header']);
            }
        }
//        print_r($_GET['status']);
//        die();                  
        
        $form->fieldset('Xml');
        $form->textarea('Header', 'header', $responseObj['header'], 'header', true, 'e.g., header', true);
        $form->textarea('Body', 'body', $responseObj['body'], 'body', true, 'e.g., body', true);
        
        $data = array(
            'form' => $form->display(),
            'form_title' => 'Categories XML',
            'form_scope' => 'category',
            'site_name' => $siteRow['name'],
            'allMids' => $allMids,
            'form_action' => site_url('admincp/linkshare/')
        );

        $this->load->view('xml', $data);
    }  
}

class Admincp3 extends Admincp_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->admin_navigation->parent_active('linkshare');

        //error_reporting(E_ALL^E_NOTICE);
        //error_reporting(E_WARNING);
    }

    public function index()
    {
        redirect('admincp3/linkshare/listProducts');
    }

    public function listProducts($id_site, $mid)
    {
        $this->admin_navigation->module_link('Inapoi la magazine', site_url('admincp3/linkshare/siteAdvertisers/' . $id_site));

        $this->load->library('dataset');

        $columns = array(
            array(
                'name' => 'ID #',
                'width' => '5%'),
            array(
                'name' => 'Site',
                'width' => '1%'),
            array(
                'name' => 'MID',
                'width' => '1%'),
            array(
                'name' => 'Magazin',
                'width' => '5%'),
            array(
                'name' => 'Creat_cat_ID',
                'width' => '2%',
                'type' => 'text',
                'filter' => 'cat_creative_id'),
            array(
                'name' => 'Cat Creativa',
                'width' => '9%',
                'type' => 'text',
                'filter' => 'cat_creative_name'),
            array(
                'name' => 'Primary',
                'width' => '4%'),
            array(
                'name' => 'Secondary',
                'width' => '4%'),
            array(
                'name' => 'Produs',
                'width' => '10%'),
            array(
                'name' => 'Image',
                'width' => '10%'),
            array(
                'name' => 'Description',
                'width' => '10%'),
            array(
                'name' => 'Price',
                'width' => '5%'),
            array(
                'name' => 'Pricelist',
                'width' => '5%'),
            array(
                'name' => 'Currency',
                'width' => '5%'),
            array(
                'name' => 'Linkid',
                'width' => '5%'),
            array(
                'name' => 'SKU',
                'width' => '5%'),
            array(
                'name' => 'Link',
                'width' => '5%'),
            array(
                'name' => 'Available',
                'width' => '5%'),
            array(
                'name' => 'Operatii',
                'width' => '5%'
            )
        );
        /*
          //click url
          http://click.linksynergy.com/fs-bin/click?id=eRClQ*mKkgw&offerid=270100.1772&type=2 BAZA
          //icon url
          http://www.glam-net.com/media/catalog/product/g/n/gn_ring.jpg BAZA
          //show url
          http://ad.linksynergy.com/fs-bin/show?id=eRClQ*mKkgw&bids=270100.1772&type=2 varza nu e bun de nimik
          //link url
          http://click.linksynergy.com/link?id=eRClQ*mKkgw&offerid=270100.1772&type=15&murl=http%3A%2F%2Fwww.glam-net.com%2Fniin-cosmo-agate-ring.html are in plus url de redirect
          //image url
          http://www.glam-net.com/media/catalog/product/g/n/gn_ring.jpg same shit
         *
         */

        $filters = array();
        $filters['limit'] = 20;

        if (isset($_GET['offset']))
            $filters['offset'] = $_GET['offset'];
        if (isset($_GET['cat_creative_id']))
            $filters['cat_creative_id'] = $_GET['cat_creative_id'];

        if (isset($_GET['filters'])) {
            $aux = unserialize(base64_decode($this->asciihex->HexToAscii($_GET['filters'])));
            if (isset($aux['cat_creative_id']))
                $filters['cat_creative_id'] = $aux['cat_creative_id'];
            if (isset($aux['cat_creative_name']))
                $filters['cat_creative_name'] = $aux['cat_creative_name'];
        }

        $filters['id_site'] = $id_site;
        $filters['mid'] = $mid;

        $this->dataset->columns($columns);
        $this->dataset->datasource('product_model', 'getProductsByMid', $filters);
        $this->dataset->base_url(site_url('admincp3/linkshare/listProducts/' . $id_site . '/' . $mid));
        $this->dataset->rows_per_page(20);

        $this->load->model('advertiser_model');

        // total rows
        $total_rows = $this->product_model->getCountProductsByMID($mid, $id_site, $filters);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp3/linkshare/deleteProduct');

        $magazin = '';

        $this->load->model('advertiser_model');
        $aux = $this->product_model->getAdvertiserByMID($mid, $id_site);
        if ($aux)
            $magazin = $aux['name'];


        $data = array(
            'mid' => $mid,
            'magazin' => $magazin
        );

        $this->load->view('listProducts', $data);
    }
    
    public function changeProductStatus($id_product, $ret_url)
    {
        $ret_url = base64_decode($ret_url);
        $this->load->model('product_model');
        $this->product_model->changeProductStatus($id_product);
        echo '<META http-equiv="refresh" content="0;URL=' . $ret_url . '">';
    }

    public function parseProductSearch($id, $mid, $creative_cat_id = 0)
    {
        error_reporting(E_ERROR);
        include "app/third_party/LOG/Log.php";
        
        $aux = '';
        $this->load->model('site_model');
        $aux = $this->site_model->getSite($id);
        $token = $aux['token'];

        $this->load->model('product_model');
        $this->load->model('product_new_model');

        $creative_categories = array();
        $this->load->model('category_creative_model');
        $filters['id_site'] = $id;
        $filters['mid'] = $mid;
        $creative_categories = $this->category_creative_model->getCategories($filters);

        $data = array();

        // print '<pre>';
        // print_r($creative_categories);
        // print "count = ".count($creative_categories);
        // die;
        //the creative category is not present it means we have finished adding/updating new products...now we must set the old ones to available='no' and return from method

        if (!array_key_exists($creative_cat_id, $creative_categories)) {
            $old = $this->product_new_model->markOld($mid);
            $data['message'] = "$old products marked as not available";
            $this->load->view('parseProductSearch', $data);
            return;
        }

        $creative_category = $creative_categories[$creative_cat_id]['cat_id'];
        $campaignID = -1;
        $page = 1;

        do {
            $kids = 0;
            $i = $j = $k = 0;
            if (isset($product))
                unset($product);
            $product = array();
            $message = '';

            $categories = simplexml_load_file("http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page", "SimpleXMLElement", LIBXML_NOCDATA);
            //  print '<pre>';
            //  print_r($categories);
            // eg. http://lld2.linksynergy.com/services/restLinks/getProductLinks/c9b4a2805e6d69846a3b7c9f0c23c26249cb86bc50fe864ff13746a8ab7dc92f/37557/200338012/-1/1
                        
            if (is_object($categories) && isset($categories)) {
                $copii = $categories->children('ns1', true);
                $kids = count($copii);
                if ($kids) {
                    //var_dump($kids);
                    foreach ($copii as $child) {
                        $product[$i]['id_site'] = $id;
                        $product[$i]['cat_creative_id'] = addslashes($child->categoryID);
                        $product[$i]['cat_creative_name'] = addslashes($child->categoryName);
                        $product[$i]['mid'] = $mid;
                        $product[$i]['linkid'] = addslashes($child->linkID);
                        $product[$i]['linkname'] = addslashes($child->linkName);
                        $product[$i]['nid'] = $child->nid;
                        $product[$i]['click_url'] = addslashes($child->clickURL);
                        $product[$i]['icon_url'] = addslashes($child->iconURL);
                        $product[$i]['show_url'] = addslashes($child->showURL);
                        $product[$i]['available'] = 'yes';
                        $product[$i]['insert_date'] = date("Y-m-d H:i:s");
                        $product[$i]['last_update_date'] = date("Y-m-d H:i:s");

                        $produs = simplexml_load_file("http://productsearch.linksynergy.com/productsearch?token=$token&keyword=\"{$child->linkName}\"&MaxResults=1&pagenumber=1&mid=$mid", "SimpleXMLElement", LIBXML_NOCDATA);
            //  http://productsearch.linksynergy.com/productsearch?token=c9b4a2805e6d69846a3b7c9f0c23c26249cb86bc50fe864ff13746a8ab7dc92f&keyword=%22Heart%20Brooch%20in%2014%20Karat%20Gold%22&MaxResults=1&pagenumber=1&mid=37557
 
                        print '<pre>';
                        print_r($product);
                       
                        if (is_object($produs) && isset($produs)) {
                            $x = $produs->item[0];
                            $product[$i]['merchantname'] = addslashes($x->merchantname);
                            $product[$i]['createdon'] = addslashes($x->createdon);
                            $product[$i]['sku'] = addslashes($x->sku);
                            $product[$i]['productname'] = addslashes($x->productname);
                            $product[$i]['categ_primary'] = addslashes($x->category->primary);
                            $product[$i]['categ_secondary'] = addslashes($x->category->secondary);
                            $product[$i]['price'] = $x->price;
                            $product[$i]['currency'] = addslashes($x->price["currency"]);
                            $product[$i]['upccode'] = addslashes($x->upccode);
                            $product[$i]['description_short'] = addslashes($x->description->short);
                            $product[$i]['description_long'] = addslashes($x->description->long);
                            $product[$i]['keywords'] = addslashes($x->keywords);
                            $product[$i]['linkurl'] = addslashes($x->linkurl);
                            $product[$i]['imageurl'] = addslashes($x->imageurl);
                            $product[$i]['price_list'] = $x->saleprice;
                            $product[$i]['price_save'] = 0;
                            $product[$i]['price_final'] = 0;
                        } else {
                            $product[$i]['merchantname'] = '';
                            $product[$i]['createdon'] = '';
                            $product[$i]['sku'] = $product[$i]['linkid'];
                            $product[$i]['productname'] = $product[$i]['linkname'];
                            $product[$i]['categ_primary'] = '';
                            $product[$i]['categ_secondary'] = '';
                            $product[$i]['price'] = 0;
                            $product[$i]['currency'] = "USD";
                            $product[$i]['upccode'] = '';
                            $product[$i]['description_short'] = '';
                            $product[$i]['description_long'] = '';
                            $product[$i]['keywords'] = '';
                            $product[$i]['linkurl'] = '';
                            $product[$i]['imageurl'] = '';
                            $product[$i]['price_list'] = 0;
                            $product[$i]['price_save'] = 0;
                            $product[$i]['price_final'] = 0;
                        }

                        $this->product_new_model->newProduct($product[$i]);

                        $exista = $this->product_model->existsProduct($product[$i]['linkid']);
                        if (!$exista) {
                            $this->product_model->newProduct($product[$i]);
                            $j++;
                        } else {
                            $this->product_model->updateProductByLinkID($product[$i], $product[$i]['linkid']);
                            $k++;
                        }
                        $i++;
                        //echo $i.'<br/>';
                    }
                }
            }
            
            $logMessage = "http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page $i total products $j inserted $k updated ";

            Log::warn($logMessage);
 
            $message .= "cat_id $creative_cat_id creative_category $creative_category page $page " . count($product) . " products parsed<br/>";
            $page++;
        } while ($kids > 0);

        $data['message'] = $message;

        $this->load->view('parseProductSearch', $data);

        $creative_cat_id++;
        echo '<META http-equiv="refresh" content="1;URL=/admincp3/linkshare/parseProductSearch/' . $id . '/' . $mid . '/' . $creative_cat_id . '">';
        die;
    }

    public function parseProductXmlReaderSearch($id, $mid, $creative_cat_id = 0, $partial = 0, $page = 1, $id_produs_partial = 1, $j = 0, $k = 0)
    {
        error_reporting(E_ERROR);
        $aux = '';
        $this->load->model('site_model');
        $aux = $this->site_model->getSite($id);
        $token = $aux['token'];

        $this->load->model('product_model');
        $this->load->model('product_partial_model');
        $this->load->model('product_new_model');

        $creative_categories = array();
        $this->load->model('category_creative_model');
        $filters['id_site'] = $id;
        $filters['mid'] = $mid;
        $creative_categories = $this->category_creative_model->getCategories($filters);

        $data = array();

        // print '<pre>';
        // print_r($creative_categories);
        // print "count = ".count($creative_categories);
        // die;

        if (!$partial) {

            //the creative category is not present it means we have finished adding/updating new products...now we must set the old ones to available='no' and return from method
            if (!array_key_exists($creative_cat_id, $creative_categories)) {
                $old = $this->product_new_model->markOld($mid);
                $data['message'] = "$old products marked as not available";
                $this->load->view('parseProductSearch', $data);
                return;
            }

            $creative_category = $creative_categories[$creative_cat_id]['cat_id'];
            $campaignID = -1;

            $xml = new XMLReader();

            $file = "http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page";

            if (!$xml->open($file)) {
                $fp = fopen("loguri.txt", "a");
                fwrite($fp, "Failed to open input file : http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page " . date("Y-m-d H:i:s") . PHP_EOL);
                fclose($fp);

                $data['message'] = "Failed to open input file : http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page " . date("Y-m-d H:i:s");

                $this->load->view('parseProductSearch', $data);

                $creative_cat_id++;
                echo '<META http-equiv="refresh" content="1;URL=/admincp3/linkshare/parseProductXmlReaderSearch/' . $id . '/' . $mid . '/' . $creative_cat_id . '">';
                die;
            } else {
                $i = 0;
                $product = array();
                $wrapperName = 'ns1:return';
                while ($xml->read()) {
                    $subarray = array();
                    //echo 'read<br/>';
                    if ($xml->nodeType == XMLReader::ELEMENT && $xml->name == $wrapperName) {
                        while ($xml->read() && $xml->name != $wrapperName) {
                            if ($xml->nodeType == XMLReader::ELEMENT) {
                                $name = $xml->name;
                                $xml->read();
                                $value = $xml->value;
                                $subarray[$name] = $value;
                            }
                        }

                        $product[$i]['id_site'] = $id;
                        $product[$i]['cat_creative_id'] = addslashes($subarray['ns1:categoryID']);
                        $product[$i]['cat_creative_name'] = addslashes($subarray['ns1:categoryName']);
                        $product[$i]['mid'] = $mid;
                        $product[$i]['linkid'] = addslashes($subarray['ns1:linkID']);
                        $product[$i]['linkname'] = addslashes($subarray['ns1:linkName']);
                        $product[$i]['nid'] = $subarray['ns1:nid'];
                        $product[$i]['click_url'] = addslashes($subarray['ns1:clickURL']);
                        $product[$i]['icon_url'] = addslashes($subarray['ns1:iconURL']);
                        $product[$i]['show_url'] = addslashes($subarray['ns1:showURL']);
                        $product[$i]['available'] = 'yes';
                        $product[$i]['insert_date'] = date("Y-m-d H:i:s");
                        $product[$i]['last_update_date'] = date("Y-m-d H:i:s");

                        $this->product_partial_model->newProduct($product[$i]);

                        unset($subarray);

                        $i++;
                        //echo $i.'<br/>';
                    }

                    //echo "slice = $slice total = $total<br/>";
                }
                $xml->close();

                /* print '<pre>';
                  print_r($product);
                  die; */

                //no product found the xml is empty : <ns1:getProductLinksResponse xmlns:ns1="http://endpoint.linkservice.linkshare.com/"/>
                if (!$i) {
                    $data['message'] = "Failed to open input file : http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page xml empty " . date("Y-m-d H:i:s");

                    $this->load->view('parseProductSearch', $data);

                    $creative_cat_id++;
                    echo '<META http-equiv="refresh" content="1;URL=/admincp3/linkshare/parseProductXmlReaderSearch/' . $id . '/' . $mid . '/' . $creative_cat_id . '">';
                    die;
                }

                $partial = 1;
                $id_produs_partial = 1;
                $j = $k = 0;

                $data['message'] = $message;

                $this->load->view('parseProductSearch', $data);

                echo '<META http-equiv="refresh" content="1;URL=/admincp3/linkshare/parseProductXmlReaderSearch/' . $id . '/' . $mid . '/' . $creative_cat_id . '/' . $partial . '/' . $page . '/' . $id_produs_partial . '/' . $j . '/' . $k . '/' . '">';
                die;
            }
        } else {
            $produs_partial = array();
            $produs_partial = $this->product_partial_model->getProduct($id_produs_partial);
            if (!empty($produs_partial)) {
                $produs = simplexml_load_file("http://productsearch.linksynergy.com/productsearch?token=$token&keyword=\"{$produs_partial['linkname']}\"&MaxResults=1&pagenumber=1&mid=$mid", "SimpleXMLElement", LIBXML_NOCDATA);

                if (is_object($produs) && isset($produs)) {
                    $x = $produs->item[0];
                    $produs_partial['merchantname'] = addslashes($x->merchantname);
                    $produs_partial['createdon'] = addslashes($x->createdon);
                    $produs_partial['sku'] = addslashes($x->sku);
                    $produs_partial['productname'] = addslashes($x->productname) ? addslashes($x->productname) : $produs_partial['linkname'];
                    $produs_partial['categ_primary'] = addslashes($x->category->primary);
                    $produs_partial['categ_secondary'] = addslashes($x->category->secondary);
                    $produs_partial['price'] = $x->price[0];
                    $produs_partial['currency'] = addslashes($x->price["currency"]);
                    $produs_partial['upccode'] = addslashes($x->upccode);
                    $produs_partial['description_short'] = addslashes($x->description->short);
                    $produs_partial['description_long'] = addslashes($x->description->long);
                    $produs_partial['keywords'] = addslashes($x->keywords);
                    $produs_partial['linkurl'] = addslashes($x->linkurl);
                    $produs_partial['imageurl'] = addslashes($x->imageurl);
                    $produs_partial['price_list'] = $x->saleprice[0];
                    $produs_partial['price_save'] = 0;
                    $produs_partial['price_final'] = 0;
                } else {
                    $produs_partial['merchantname'] = '';
                    $produs_partial['createdon'] = '';
                    $produs_partial['sku'] = $produs_partial['linkid'];
                    $produs_partial['productname'] = $produs_partial['linkname'];
                    $produs_partial['categ_primary'] = '';
                    $produs_partial['categ_secondary'] = '';
                    $produs_partial['price'] = 0;
                    $produs_partial['currency'] = "USD";
                    $produs_partial['upccode'] = '';
                    $produs_partial['description_short'] = '';
                    $produs_partial['description_long'] = '';
                    $produs_partial['keywords'] = '';
                    $produs_partial['linkurl'] = '';
                    $produs_partial['imageurl'] = '';
                    $produs_partial['price_list'] = 0;
                    $produs_partial['price_save'] = 0;
                    $produs_partial['price_final'] = 0;
                }

                unset($produs_partial['id']);
                $this->product_new_model->newProduct($produs_partial);

                $exista = $this->product_model->existsProduct($produs_partial['linkid']);
                if (!$exista) {
                    $this->product_model->newProduct($produs_partial);
                    $j++;
                } else {
                    $this->product_model->updateProductByLinkID($produs_partial, $produs_partial['linkid']);
                    $k++;
                }

                $partial = 1;
                $id_produs_partial++;

                $data['message'] = "$id_produs_partial products parsed";

                $this->load->view('parseProductSearch', $data);

                echo '<META http-equiv="refresh" content="0;URL=/admincp3/linkshare/parseProductXmlReaderSearch/' . $id . '/' . $mid . '/' . $creative_cat_id . '/' . $partial . '/' . $page . '/' . $id_produs_partial . '/' . $j . '/' . $k . '/' . '">';
                die;
            } else {

                $i = $id_produs_partial - 1;
                $fp = fopen("loguri.txt", "a");
                fwrite($fp, "http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page $i total products $j inserted $k updated  " . date("Y-m-d H:i:s") . PHP_EOL);
                fclose($fp);

                //i finished all the partial products,truncate the linkshare_produs_partial,increment the page and refresh
                $this->product_partial_model->deletePartialProduct();
                $partial = 0;
                $page++;

                $data['message'] = "cat_id $creative_cat_id creative_category $creative_category page $page $i products parsed<br/>";
                $this->load->view('parseProductSearch', $data);
                echo '<META http-equiv="refresh" content="1;URL=/admincp3/linkshare/parseProductXmlReaderSearch/' . $id . '/' . $mid . '/' . $creative_cat_id . '/' . $partial . '/' . $page . '">';
                die;
            }
        }

        $data['message'] = 'FINISHED';

        $this->load->view('parseProductSearch', $data);
    }
    

    public function testMarkOld()
    {
        $this->load->model('product_model');
        $this->load->model('product_new_model');
                
        $id = 2; $mid = 37920; $creative_cat_id = 200260395;
        $linkid = 78900666;
        
        // Delete product from linkshare_produs
        $this->product_new_model->deleteOldFromCurrent($linkid,$mid);
        
        //$produs = array();
        
        //$produs = $this->product_new_model->getProductsByLinkID($linkid,$mid);
       

        
//        echo "<pre>";
//        print_r ($produs);
//        echo "</pre>";
//        die();
       
        //$this->product_new_model->copyToProductsOld($produs);
       
    }
    
    
}


/**
 * Produs Partial Model - Manages partial produs
 *
 * @category Admin Controller
 * @package  Linkshare
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */

class Product_partial_model extends CI_Model
{

    private $CI;

    function __construct()
    {
        parent::__construct();

        $this->CI = & get_instance();
    }

    /**
     * Get Produse
     *
     *
     * @return array
     */
    function getProducts()
    {
        $row = array();
        $result = $this->db->get('linkshare_produs_partial');

        foreach ($result->result_array() as $linie) {
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Produse By Mid
     * @param array $params
     *
     * @return array
     */
    function getProductsByMid($params)
    {
        $row = array();
        if (isset($params['limit'])) {
            $offset = (isset($params['offset'])) ? $params['offset'] : 0;
            $this->db->limit($params['limit'], $offset);
        }
        if ($params['id_site'])
            $this->db->where('id_site', $params['id_site']);
        if ($params['mid'])
            $this->db->where('mid', $params['mid']);
        $result = $this->db->get('linkshare_produs_partial');

        foreach ($result->result_array() as $linie) {
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Product Status
     *
     *
     * @return array
     */
    function getProductStatus()
    {
        $row = array();
        $result = $this->db->get('linkshare_produs_partial');

        foreach ($result->result_array() as $value) {
            $row[$value['id']] = $value['name'];
        }

        return $row;
    }

    /**
     * Change Produs Status
     * 
     * @param int $id_product
     *
     * @return boolean
     */
    function changeProductStatus($id_product)
    {
        $row = array();
        $this->db->where('id', $id_product);
        $result = $this->db->get('linkshare_produs_partial');

        foreach ($result->result_array() as $value) {
            $available = $value['available'];
        }

        if ($available == 'yes')
            $available = 'no';
        elseif ($available == 'no')
            $available = 'yes';

        $update_fields['available'] = $available;
        $this->updateProduct($update_fields, $id_product);

        return true;
    }

    /**
     * Get Produs
     *
     * @param int $id	
     *
     * @return array
     */
    function getProduct($id)
    {
        $row = array();
        $this->db->where('id', $id);
        $result = $this->db->get('linkshare_produs_partial');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }

    /**
     * Get Produs By linkid
     *
     * @param int $linkid	
     *
     * @return array
     */
    function getProductByLinkID($linkid)
    {
        $row = array();
        $this->db->where('linkid', $linkid);
        $result = $this->db->get('linkshare_produs_partial');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }

    /**
     * Create New Produs
     *
     * Creates a new produs
     *
     * @param array $insert_fields	
     *
     * @return int $insert_id
     */
    function newProduct($insert_fields)
    {
        $this->db->insert('linkshare_produs_partial', $insert_fields);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }

    /**
     * Update Produs
     *
     * Updates produs
     * 
     * @param array $update_fields
     * @param int $id	
     *
     * @return boolean true
     */
    function updateProduct($update_fields, $id)
    {
        $this->db->update('linkshare_produs_partial', $update_fields, array('id' => $id));

        return true;
    }

    /**
     * Update Produs By Linkid
     *
     * Updates produs
     * 
     * @param array $update_fields
     * @param int $linkid	
     *
     * @return boolean true
     */
    function updateProductByLinkID($update_fields, $linkid)
    {
        $produs = $this->getProductByLinkID($linkid);
        //preserve insert date
        $update_fields['insert_date'] = $produs['insert_date'];
        $this->db->update('linkshare_produs_partial', $update_fields, array('linkid' => $linkid));

        return true;
    }

    /**
     * Delete Produs
     *
     * Deletes produs
     * 	
     * @param int $id	
     *
     * @return boolean true
     */
    function deleteProduct($id)
    {

        $this->db->delete('linkshare_produs_partial', array('id' => $id));

        return true;
    }

    /**
     * Checks if Produs Exists
     *
     * Exists produs
     * 	
     * @param int $linkid	
     *
     * @return boolean
     */
    function existsProduct($linkid)
    {
        $this->db->where('linkid', $linkid);
        $result = $this->db->get('linkshare_produs_partial');
        foreach ($result->result_array() as $row) {
            return true;
        }

        return false;
    }

    /**
     * Sterge Produs partial
     *
     * Truncate produs partial	
     *
     * @return boolean true
     */
    function deletePartialProduct()
    {
        $this->db->truncate('linkshare_produs_partial');

        return true;
    }

}

/**
 * Produs New Model
 *
 * Manages produs new
 *
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 */
class Product_new_model extends CI_Model {

    private $CI;

    function __construct()
    {
        parent::__construct();

        $this->CI = & get_instance();
    }
    
    /**
     * Get Produse
     *
     *
     * @return array
     */
    function getProducts() {
        $row = array();
        $result = $this->db->get('linkshare_produs_new');

        foreach ($result->result_array() as $linie) {
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Produse By Mid
     * @param array $params
     *
     * @return array
     */
    function getProductsByMid($params) {
        $row = array();
        if ($params['mid'])
            $this->db->where('mid', $params['mid']);
        $result = $this->db->get('linkshare_produs_new');

        foreach ($result->result_array() as $linie) {
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Get Produs Status
     *
     *
     * @return array
     */
    function getProductStatus() {
        $row = array();
        $result = $this->db->get('linkshare_produs_new');

        foreach ($result->result_array() as $value) {
            $row[$value['id']] = $value['name'];
        }

        return $row;
    }

    /**
     * Get Produs
     *
     * @param int $id	
     *
     * @return array
     */
    function getProduct($id)
    {
        $row = array();
        $this->db->where('id', $id);
        $result = $this->db->get('linkshare_produs_new');

        foreach ($result->result_array() as $row) {
            return $row;
        }

        return $row;
    }

    /**
     * Create New Produs
     *
     * Creates a new produs
     *
     * @param array $insert_fields	
     *
     * @return int $insert_id
     */
    function newProduct($insert_fields)
    {
        $this->db->insert('linkshare_produs_new', $insert_fields);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }

    /**
     * Update Produs
     *
     * Updates produs
     * 
     * @param array $update_fields
     * @param int $id	
     *
     * @return boolean true
     */
    function updateProduct($update_fields, $id)
    {
        $this->db->update('linkshare_produs_new', $update_fields, array('id' => $id));

        return true;
    }

    /**
     * Update Produs By Linkid
     *
     * Updates produs
     * 
     * @param array $update_fields
     * @param int $linkid	
     *
     * @return boolean true
     */
    function updateProductByLinkID($update_fields, $linkid)
    {
        $this->db->update('linkshare_produs_new', $update_fields, array('linkid' => $linkid));

        return true;
    }

    /**
     * Delete Produs
     *
     * Deletes produs
     * 	
     * @param int $id	
     *
     * @return boolean true
     */
    function deleteProduct($id)
    {

        $this->db->delete('linkshare_produs_new', array('id' => $id));

        return true;
    }

    /**
     * Checks if Produs Exists
     *
     * Exists produs
     * 	
     * @param int $linkid	
     *
     * @return boolean
     */
    function existsProduct($linkid)
    {
        $this->db->where('linkid', $linkid);
        $result = $this->db->get('linkshare_produs_new');
        foreach ($result->result_array() as $row) {
            return true;
        }

        return false;
    }
    
    /**
     * Get Produse By Linkid
     * @param array $params
     *
     * @return array
     */
    function getProductsByLinkID($linkid,$mid) {
        $row = array();

        $result = $this->db->get('linkshare_produs', array('linkid' => $linkid,'mid' => $mid));

        foreach ($result->result_array() as $linie) {
            $row[] = $linie;
        }

        return $row;
    }

    /**
     * Copy to Old Produs
     *
     * Creates a new produs
     *
     * @param array $insert_fields	
     *
     * @return int $insert_id
     */
    function copyToProductsOld($insert_fields)
    {
        $this->db->insert('linkshare_produs_old', $insert_fields['0']);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }
    
    /**
     * Delete Old Product from 
     *
     * Deletes produs
     * 	
     * @param int $id	
     *
     * @return boolean true
     */
    function deleteOldFromCurrent($linkid,$mid)
    {
        $this->db->delete('linkshare_produs', array('linkid' => $linkid,'mid' => $mid));

        return true;
    }
    
    /**
     * Mark Old Produs
     *
     * Marks old produs
     * 	
     * @param int $id	
     *
     * @return int $i
     */
    function markOld($mid)
    {
        $i = $j = 0;
        $update_fields = array();
        $this->db->where('mid', $mid);
        $result = $this->db->get('linkshare_produs');
        foreach ($result->result_array() as $row) {
            //if the old product is not present among the new = current products...we mark it as old move it to linkshare_produs_old
            $update_fields['available'] = 'no';
            $update_fields['last_update_date'] = date('Y-m-d H:i:s');
            if (!$this->existsProduct($row['linkid'])) {
                $this->db->update('linkshare_produs', $update_fields, array('linkid' => $row['linkid']));
                
                // Copy product to linkshare_produs_old
                $produs = $this->getProductsByLinkID($row['linkid'],$mid);    
                $this->copyToProductsOld($produs);
                
                // Delete product from linkshare_produs
                $this->deleteOldFromCurrent($row['linkid'],$mid);
                
                $i++;
            }
        }

        //delete all mid new = current products 
        $this->db->delete('linkshare_produs_new', array('mid' => $mid));
        $result = $this->db->get('linkshare_produs_new');
        foreach ($result->result_array() as $row) {
            $j++;
        }
        //if table empty reset auto_increment
        if (!$j)
            $this->db->query('ALTER TABLE linkshare_produs_new AUTO_INCREMENT = 0');
        return $i;
    }

}
