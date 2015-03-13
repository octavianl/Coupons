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

class Admincp extends Admincp_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->admin_navigation->parent_active('annonces');

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
    
    protected function getToken($tokenType = LinkshareConfig::PASSWORD)
    {       
        $config = new LinkshareConfig();
        // build a new HTTP POST request
        $request = new CurlApi(LinkshareConfig::URL_TOKEN);
        $request->setHeaders($config->getTokenHeaders());
        $request->setPostData($config->getTokenParams());
        $request->send();
        
        // decode the incoming string as JSON
        $responseObj = $request->getDecodedResponse();
        if ($tokenType == LinkshareConfig::PASSWORD) {
            return $responseObj->access_token;
        } elseif ($tokenType == LinkshareConfig::REFRESH) {
            return $responseObj->refresh_token;
        } else {
            return $responseObj->access_token;
        }
        
        
        //print '<pre>';
        //print_r($responseObj);
                
        /*
            stdClass Object
            (
                [token_type] => bearer
                [expires_in] => 3042
                [refresh_token] => ec03243c5d33d193f1983f69f2ac
                [access_token] => e6f99a58f75753fa8bcbb81a6ff91
            )
         */                
    }
    
    public function getRefreshToken()
    {
        $config = new LinkshareConfig();
        // build a new HTTP POST request
        $request = new CurlApi(LinkshareConfig::URL_TOKEN);
        $request->setHeaders($config->getTokenHeaders());
        $request->setPostData($config->getTokenParams());
        $request->send();
        $request->setPostData($config->getRefreshTokenParams($this->getToken(LinkshareConfig::REFRESH)));
        $request->send();
        
        // decode the incoming string as JSON
        $responseObj = json_decode($request->getResponse());
        return $responseObj->access_token;
        
        //print '<pre>';
        //print_r($responseObj);
                
        /*
            stdClass Object
            (
                [token_type] => bearer
                [expires_in] => 3600
                [refresh_token] => b2f98de52cfb9f6925fe1dff3de494af
                [access_token] => b0d4d2a3c9665c3c706356da3a423d
            )
         */
    }
    
    public function getXml()
    {
        $this->load->library('admin_form');
        $form = new Admin_form;
        
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        $config = new LinkshareConfig();
        
        $accessToken = $this->getToken();

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
    
    public function parseAdvertisersTEST()
    {
        error_reporting(E_ALL);
        ini_set('display_errors',1);
        
        $mids = array();
        
        $linkshareConstants = array("URL_ADVERTISERS_APPROVED",
                                    "URL_ADVERTISERS_PERM_REJECTED",
                                    "URL_ADVERTISERS_PERM_REMOVED",
                                    "URL_ADVERTISERS_SELF_REMOVED",
                                    "URL_ADVERTISERS_TEMP_REMOVED",
                                    "URL_ADVERTISERS_TEMP_REJECTED",
                                    "URL_ADVERTISERS_WAIT");
        
        /* Get XML with OAUTH2 */
        
        $config = new LinkshareConfig();
        
        $accessToken = $this->getToken();

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
