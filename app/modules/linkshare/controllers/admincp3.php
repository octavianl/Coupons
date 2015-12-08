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

class Admincp3 extends Admincp_Controller {

    private $specialChars = "(&=?{}\()[]-;~|$!><*%).";

    public function __construct() {
        parent::__construct();

        $this->admin_navigation->parent_active('linkshare');

        $siteID = $this->input->cookie('siteID');
        // change if value already in cookie
        if ($siteID) {
            $this->siteID = $siteID;
        }
    }

    public function index() {
        redirect('admincp3/linkshare/listProducts');
    }

    public function listProducts($mid, $cat_id) {
        $this->admin_navigation->module_link('Back to advertiser', site_url('admincp2/linkshare/listCreativeCategory/'));

        $this->load->model(array('site_model', 'product_model'));
        $siteRow = $this->site_model->getSiteBySID($this->siteID);

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
                'width' => '1%',),
            array(
                'name' => 'Magazin',
                'width' => '5%'),
            array(
                'name' => 'Creat_cat_ID',
                'width' => '2%'),
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
                'width' => '5%',
                'type' => 'text',
                'filter' => 'linkid'),
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

        $filters['id_site'] = $siteRow['id'];
        $filters['cat_creative_id'] = $cat_id;
        $filters['mid'] = $mid;

        $this->dataset->columns($columns);
        $this->dataset->datasource('product_model', 'getProductsByMid', $filters);
        $this->dataset->base_url(site_url('admincp3/linkshare/listProducts/' . $mid . '/' . $cat_id));
        $this->dataset->rows_per_page(20);

        $this->load->model('advertiser_model');

        // total rows
        $total_rows = $this->product_model->getCountProductsByMID($mid, $siteRow['id'], $filters);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp3/linkshare/deleteProduct');

        $magazin = '';

        $this->load->model('advertiser_model');
        $aux = $this->advertiser_model->getAdvertiserByMID($mid, $id_site);
        if ($aux)
            $magazin = $aux['name'];


        $data = array(
            'mid' => $mid,
            'magazin' => $magazin
        );

        $this->load->view('listProducts', $data);
    }

    public function listMergedProducts($mid, $cat_id) {
        $this->admin_navigation->module_link('Back to advertiser', site_url('admincp2/linkshare/listMergedCategories/'));

        $this->load->model(array('site_model', 'product_model'));
        $siteRow = $this->site_model->getSiteBySID($this->siteID);

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
                'width' => '1%',),
            array(
                'name' => 'Magazin',
                'width' => '5%'),
            array(
                'name' => 'Creat_cat_ID',
                'width' => '2%'),
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
                'width' => '5%',
                'type' => 'text',
                'filter' => 'linkid'),
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

        $filters['id_site'] = $siteRow['id'];
        $filters['cat_creative_id'] = $cat_id;
        $filters['mid'] = $mid;

        $this->dataset->columns($columns);
        $this->dataset->datasource('product_model', 'getProductsByMid', $filters);
        $this->dataset->base_url(site_url('admincp3/linkshare/listProducts/' . $mid . '/' . $cat_id));
        $this->dataset->rows_per_page(20);

        $this->load->model('advertiser_model');

        // total rows
        $total_rows = $this->product_model->getCountProductsByMID($mid, $siteRow['id'], $filters);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp3/linkshare/deleteProduct');

        $magazin = '';

        $this->load->model('advertiser_model');
        $aux = $this->advertiser_model->getAdvertiserByMID($mid, $id_site);
        if ($aux)
            $magazin = $aux['name'];


        $data = array(
            'mid' => $mid,
            'magazin' => $magazin
        );

        $this->load->view('listMergedProducts', $data);
    }

    public function listTempProducts($mid, $cat_id) {

        $this->admin_navigation->module_link('Clear', site_url('admincp3/linkshare/clearTempProducts/' . $mid . '/' . $cat_id));
        $this->admin_navigation->module_link('Back to CC', site_url('admincp2/linkshare/listCreativeCategory/'));
        $this->admin_navigation->module_link('Move to CURRENT', site_url('admincp3/linkshare/moveProducts/' . $mid . '/' . $cat_id));
        $this->admin_navigation->module_link('Parse FULL Products', site_url('admincp3/linkshare/parseProductSearch/' . $mid . '/' . $cat_id));

        $this->load->model(array('site_model', 'product_model'));
        $siteRow = $this->site_model->getSiteBySID($this->siteID);

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
                'width' => '2%'),
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
                'width' => '5%',
                'type' => 'text',
                'filter' => 'linkid'),
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

        $filters['id_site'] = $siteRow['id'];
        $filters['cat_creative_id'] = $cat_id;
        $filters['mid'] = $mid;

        $this->dataset->columns($columns);
        $this->dataset->datasource('product_model', 'getTempProductsByMid', $filters);
        $this->dataset->base_url(site_url('admincp3/linkshare/listTempProducts/' . $mid . '/' . $cat_id));
        $this->dataset->rows_per_page(20);

        $this->load->model('advertiser_model');

        // total rows
        $total_rows = $this->product_model->getCountTempProductsByMID($mid, $siteRow['id'], $filters);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp3/linkshare/deleteProduct');

        $magazin = '';

        $this->load->model('advertiser_model');
        $aux = $this->advertiser_model->getAdvertiserByMID($mid, $siteRow['id']);
        if ($aux)
            $magazin = $aux['name'];


        $data = array(
            'mid' => $mid,
            'magazin' => $magazin,
            'category' => $cat_id
        );

        $this->load->view('listTempProducts', $data);
    }

    public function moveProducts($mid, $cat_id, $offset = 0) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $batch_current = 100;

        $this->load->model(array('site_model', 'product_model', 'category_creative_model'));
        $siteID = $this->site_model->getSiteBySID($this->siteID);

        $currentProducts = $this->product_model->getProductsByMid(array('id_site' => $siteID['id'], 'mid' => $mid, 'cat_creative_id' => $cat_id, 'offset' => $offset, 'limit' => $batch_current));

        //echo"<pre>"; print_r($currentProducts); die();

        foreach ($currentProducts as $val) {
            $filters = array('id_site' => $siteID['id'], 'mid' => $val['mid'], 'cat_creative_id' => $val['cat_creative_id'], 'linkid' => $val['linkid']);
            $existsTempProducts = $this->product_model->existsTempProduct($siteID['id'], $val['cat_creative_id'], $val['mid'], $val['linkid']);

            //echo"<pre>"; print_r($filters); die();

            if (!empty($existsTempProducts)) {
                // Update Products from linkshare_products_temp by (site_id,cat_id,mid,linkid)
                $tempProductRow = $this->product_model->getTempProduct($filters);

                $this->product_model->updateProductCurrent($val, $tempProductRow);

                $this->product_model->deleteTempProduct($filters);
            } else {
                if ($val['live'] == 1) {
                    // Mark live items to be deleted by a later cron
                    $this->product_model->updateProduct(array('deleted' => 1), $filters);
                } else {
                    // Delete advertiser from linkshare_products_temp by (site_id,cat_id,mid,linkid)   
                    $this->product_model->deleteProduct($filters);
                }
                //echo"<pre> ZZ "; print_r($filters); die();
                $this->product_model->deleteTempProduct($filters);
            }
        }

        if (!empty($currentProducts)) {
            $offset += $batch_current;
            echo "$offset" . "Current";
            echo '<META http-equiv="refresh" content="1; URL=/admincp3/linkshare/moveProducts/' . $mid . '/' . $cat_id . '/' . $offset . '">';
            return;
        }

        redirect('admincp3/linkshare/moveProductsTemp/' . $mid . '/' . $cat_id);
    }

    public function moveProductsTemp($mid, $cat_id, $offset = 0) {
        $batch_temp = 1000;

        $this->load->model(array('site_model', 'product_model'));
        $siteID = $this->site_model->getSiteBySID($this->siteID);

        $tempProducts = $this->product_model->getTempProductsByMid(array('id_site' => $siteID['id'], 'mid' => $mid, 'cat_creative_id' => $cat_id, 'parsed' => 1, 'offset' => $offset, 'limit' => $batch_temp));

//            echo "<pre>";
//            print_r($tempProducts);
//            die();

        if (!empty($tempProducts)) {
            foreach ($tempProducts as $val) {
                $temp = $val;
                unset($temp['id']);
                $this->product_model->newProduct($temp);
                $this->product_model->deleteTempProduct(array('id' => $val['id']));
            }

            //$offset += $batch_temp;
            echo "$offset" . "Temp";
            echo '<META http-equiv="refresh" content="1; URL=/admincp3/linkshare/moveProductsTemp/' . $mid . '/' . $cat_id . '/' . $offset . '">';
            return;
        }

        //$this->product_model->deleteTempProducts();

        redirect('admincp3/linkshare/listProducts/' . $mid . '/' . $cat_id);
    }

    public function changeTempProductStatus($id_product, $sid, $cat_id, $mid, $linkid, $ret_url) {
        $ret_url = base64_decode($ret_url);
        $this->load->model('product_model');
        $this->product_model->changeTempProductStatus($id_product, $sid, $cat_id, $mid, $linkid);
        echo '<META http-equiv="refresh" content="0;URL=' . $ret_url . '">';
    }

    public function clearTempProducts($mid, $cat_id) {
        $this->load->model('product_model');
        $filters = array('mid' => $mid, 'cat_creative_id' => $cat_id);
        $this->product_model->deleteTempProduct($filters);
        redirect('admincp2/linkshare/listCreativeCategory');
    }

    private function cleanLink($link) {
        for ($i = 0; $i < strlen($link); $i++) {
            if (strpos($this->specialChars, $link[$i]) !== false) {
                $link = substr($link, 0, $i);
                break;
            }
        }

        return trim($link);
    }

    public function parseShortProduct($mid, $cat_id = 0) {
        error_reporting(E_ERROR);
        include "app/third_party/LOG/Log.php";
        $CI = & get_instance();

        $this->load->model(array('site_model', 'product_model', 'category_creative_model'));
        $siteRow = $this->site_model->getSiteBySID($this->siteID);

        $config = new LinkshareConfig();
        $accessToken = $config->setSiteCookieAndGetAccessToken($CI, $this->siteID);

        $campaignID = -1;
        $page = 1;

        $categoriesRequestUrl = "https://api.rakutenmarketing.com/linklocator/1.0/getProductLinks/$mid/$cat_id/$campaignID/$page";

        $request = new CurlApi($categoriesRequestUrl);
        $request->setHeaders($config->getMinimalHeaders($accessToken));
        $request->setGetData();
        $request->send();

        $responseObj = $request->getFormattedResponse();

        $aux = $responseObj['body'];
        $categories = simplexml_load_string($aux, "SimpleXMLElement", LIBXML_NOCDATA);

        //if (is_object($categories) && isset($categories)) {
        $kids = $categories->children('ns1', true);

        foreach ($kids as $child) {

            $value = $this->product_model->existsTempProduct($siteRow['id'], $cat_id, $mid, $child->linkID);

            if (!empty($value)) {
                $temp_shortProd = array(
                    'id_site' => $siteRow['id'],
                    'cat_creative_id' => addslashes($child->categoryID),
                    'cat_creative_name' => addslashes($child->categoryName),
                    'linkid' => addslashes($child->linkID),
                    'linkname' => addslashes($child->linkName),
                    'mid' => $mid,
                    'nid' => addslashes($child->nid),
                    'click_url' => addslashes($child->clickURL),
                    'icon_url' => addslashes($child->iconURL),
                    'show_url' => addslashes($child->showURL),
                    'available' => 'yes',
                    'insert_date' => date("Y-m-d H:i:s"),
                    'last_update_date' => date("Y-m-d H:i:s"),
                    'parsed' => 0
                );
                //UPDATE Short Product
                $this->product_model->updateTempProduct($temp_shortProd, $siteRow['id'], $mid, $cat_id, $child->linkID);
                //$shortProd[] = $temp_shortProd;                
            } else {
                $temp_shortProd = array(
                    'id_site' => $siteRow['id'],
                    'cat_creative_id' => addslashes($child->categoryID),
                    'cat_creative_name' => addslashes($child->categoryName),
                    'linkid' => addslashes($child->linkID),
                    'linkname' => addslashes($child->linkName),
                    'mid' => $mid,
                    'nid' => addslashes($child->nid),
                    'click_url' => addslashes($child->clickURL),
                    'icon_url' => addslashes($child->iconURL),
                    'show_url' => addslashes($child->showURL),
                    'available' => 'yes',
                    'insert_date' => date("Y-m-d H:i:s"),
                    'last_update_date' => date("Y-m-d H:i:s"),
                    'parsed' => 0
                );
                //ADD New Short Products
                $this->product_model->newTempProduct($temp_shortProd);
                //$shortProd[] = $temp_shortProd;
            }
        }

        redirect("admincp3/linkshare/listTempProducts/" . $mid . "/" . $cat_id . "");
        //echo"<pre>";print_r($shortProd);die();
    }

    public function parseProductSearch($mid, $cat_id = 0) {
        error_reporting(E_ERROR);
        include "app/third_party/LOG/Log.php";
        $CI = & get_instance();

        $this->load->model(array('site_model', 'product_model', 'category_creative_model'));
        $siteRow = $this->site_model->getSiteBySID($this->siteID);

        $config = new LinkshareConfig();
        $accessToken = $config->setSiteCookieAndGetAccessToken($CI, $this->siteID);

        $flag = 0;
        $limit = 1;
        $shortProd = $this->product_model->getTempProducts($mid, $cat_id, $limit, $flag);

        if (empty($shortProd)) {
            redirect("admincp3/linkshare/listTempProducts/" . $mid . "/" . $cat_id . "");
        }

        $tempLink = $this->cleanLink($shortProd['linkname']);
        $keyword = urlencode($tempLink);

        echo "keyword = $keyword" . PHP_EOL;

//      // LEGACY
//      $produs = simplexml_load_file("http://productsearch.linksynergy.com/productsearch?token=ff8c950f7a1c7f4f7b3db7e9407bbd89822f611a6c44c441bc9043c5edaa4746&keyword=\"{$keyword}\"&MaxResults=1&pagenumber=1&mid=$mid", "SimpleXMLElement", LIBXML_NOCDATA);
//      //  http://productsearch.linksynergy.com/productsearch?token=c9b4a2805e6d69846a3b7c9f0c23c26249cb86bc50fe864ff13746a8ab7dc92f&keyword=%22Heart%20Brooch%20in%2014%20Karat%20Gold%22&MaxResults=1&pagenumber=1&mid=37557       
//      echo "<pre>"; print_r($produs);die();
//      // END LEGACY

        $requestLink = "https://api.rakutenmarketing.com/productsearch/1.0?keyword={$keyword}&max=100&mid=$mid";

        $request = new CurlApi($requestLink);
        $request->setHeaders($config->getMinimalHeaders($accessToken));
        $request->setGetData();
        $request->send();

        $responseObj = $request->getFormattedResponse();

        $auxProd = $responseObj['body'];

        $prod = simplexml_load_string($auxProd, "SimpleXMLElement", LIBXML_NOCDATA);

        $found = false;
        foreach ($prod->item as $valProd) {
            $keyProd = $valProd->linkid;

            if ($shortProd['linkid'] == $valProd->linkid) {
                $found = true;

                $tempProd = array(
                    'mid' => addslashes($valProd->mid),
                    'merchantname' => addslashes($valProd->merchantname),
                    'linkid' => addslashes($valProd->linkid),
                    'createdon' => addslashes($valProd->createdon),
                    'sku' => addslashes($valProd->sku),
                    'productname' => addslashes($valProd->productname),
                    'categ_primary' => addslashes($valProd->category->primary),
                    'categ_secondary' => addslashes($valProd->category->secondary),
                    'price' => addslashes($valProd->price),
                    'currency' => addslashes($valProd->price["currency"]),
                    'upccode' => addslashes($valProd->upccode),
                    'description_short' => addslashes($valProd->description->short),
                    'description_long' => addslashes($valProd->description->long),
                    'saleprice' => addslashes($valProd->saleprice),
                    'keywords' => addslashes($valProd->keywords),
                    'linkurl' => addslashes($valProd->linkurl),
                    'imageurl' => addslashes($valProd->imageurl),
                    'price_list' => addslashes($valProd->saleprice),
                    'price_save' => 0,
                    'price_final' => 0,
                    'parsed' => 1
                );

                $longProd["$keyProd"] = $tempProd;

                break;
            }
        }

//        echo "<pre>";
//        print_r($longProd);
//        die();

        if (!$found) {
            echo "Delete shot prod id : " . $shortProd['id'];
            // Delete short prod
            $filters = array('id' => $shortProd['id']);
            $this->product_model->deleteTempProduct($filters);
            $logMessage = "Product from Category $cat_id and Advertiser mid $mid with linkid : {$shortProd['linkid']}. | NOT found on search product!";
            Log::warn($logMessage);
        } else {
            echo "Update shot prod id : " . $shortProd['linkid'] . PHP_EOL;

            // Update short product to full details
            $keyProdtoUpdate = $shortProd['linkid'];
            $update_fields = $longProd["$keyProdtoUpdate"];

            echo "<pre>";
            print_r($keyProdtoUpdate);

            $this->product_model->updateTempProductByLinkID($update_fields, $shortProd['linkid']);
        }

        echo '<META http-equiv="refresh" content="3;URL=/admincp3/linkshare/parseProductSearch/' . $mid . '/' . $cat_id . '">';
    }

    function exportJoinsCategoriesCSV($mergedCategoryId) {

        $this->load->model(array('site_model', 'category_creative_model', 'category_joins'));

        $siteRow = $this->site_model->getSiteBySID($this->siteID);
        
        $categoryMerged = $this->category_creative_model->getMergedCategoryByID ($mergedCategoryId);
        $query = $this->category_joins->getJoinsCategory($mergedCategoryId, $siteRow['id']);

        $filename = FCPATH . APPPATH . 'logs/' . 'exports/' . date("Y") . '/' . date('m') . '/' . 'MergedCategory-' . $mergedCategoryId . '/' . 'merged_category' . '-'.  $this->sanitize($categoryMerged['name']) . '-' . date("Y-m-d") . '.csv';
        $filepath = FCPATH . APPPATH . 'logs/' . 'exports/' . date("Y") . '/' . date('m') . '/' . 'MergedCategory-' . $mergedCategoryId;

        $this->load->helper('file');
        $this->load->library('array_to_csv');
        $need_header = true;
        
        if (!file_exists($filename) && $this->checkFolder($filepath)) {
            $csv_file = fopen($filename, 'w');
        } else {
            $csv_file = fopen($filename, 'a');
        }
        
        foreach ($query as $row) {
           
            // ID(category id);Active (0/1);Name *;Parent category;Root category (0/1);Description;Meta title;Meta keywords;Meta description;URL rewritten;Image URL;ID / Name of shop(MID)
            $main_array[] = array(
                'id' => $row['cat_id'],
                'active' => 1,
                'name' => $row['name'],
                'parent' => 0,
                'root' => 1,
                'description' => $row['name'],
                'meta_title' => $row['name'],
                'meta_keywords' => $row['name'],
                'meta_description' => $row['name'],
                'url_rewritten' => '',
                'image_url' => '',
                'shop_name' => $row['mid'],
                'products_number' => $row['nr_products']
            );
        }
        $this->array_to_csv->input($main_array);
        $data = $this->array_to_csv->output($need_header, true);
        fwrite($csv_file, $data);
        fclose($csv_file);
    }

    function exportJoinsProductsCSV($mergedCategoryId) {

        $this->load->model(array('site_model', 'category_creative_model', 'category_joins', 'product_model'));

        $siteRow = $this->site_model->getSiteBySID($this->siteID);
        
        $categoryMerged = $this->category_creative_model->getMergedCategoryByID ($mergedCategoryId);
        $joinsCategories = $this->category_joins->getJoinsCategory($mergedCategoryId, $siteRow['id']);
        
        foreach ($joinsCategories as $joins){     
            $filters['id_site'] = $siteRow['id'];
            $filters['cat_creative_id'] = $joins['cat_id'];
            $filters['mid'] = $joins['mid'];
            
            $categoryProducts = $this->product_model->getProductsByMid($filters);

            // Since we might be dealing with very large data
            // Ensure we have time to process it
            set_time_limit(0);

            $filepath = FCPATH . 'writeable/' . 'merged_category' . '-'.  urlencode($categoryMerged['name']) . '-' . date("Y-m-d") . '.csv';

            $this->load->helper('file');
            $this->load->library('array_to_csv');

            // If the file already exists, we need
            // to get rid of it since this is a new download.
            $f = @fopen($filepath, 'r+');
            if ($f !== false) {
                ftruncate($f, 0);
                fclose($f);
            }

            $csv_file = fopen($filepath, 'a');
            $need_header = true;    // Do we need the CSV header?

            foreach ($categoryProducts as $row) {  
                // ID(category id);Active (0/1);Name *;Parent category;Root category (0/1);Description;Meta title;Meta keywords;Meta description;URL rewritten;Image URL;ID / Name of shop(MID)
                $main_array[] = array(
                    'ID' => '',
                    'Active (0/1)' => 1, // (0/1)
                    'Name' => $row['productname'],
                    'Categories (x,y,z...)' => $row['cat_id'], // (x,y,z...)
                    'Price tax excluded or Price tax included' => '',
                    'Tax rules ID' => '',
                    'Wholesale price' => '',
                    'On sale (0/1)' => 0, // (0/1)
                    'Discount amount' => '',
                    'Discount percent' => '',
                    'Discount from (yyyy-mm-dd)' => '',
                    'Discount to (yyyy-mm-dd)' => '',
                    'Reference #' => '', // #
                    'Supplier reference #' => '', //#
                    'Supplier' => '',
                    'Manufacturer' => '',
                    'EAN13' => '',
                    'UPC' => '',
                    'Ecotax' => '',
                    'Width' => '',
                    'Height' => '',
                    'Depth' => '',
                    'Weight' => '',
                    'Quantity' => '',
                    'Minimal quantity' => '',
                    'Visibility' => '',
                    'Additional shipping cost' => '',
                    'Unit for the unit price' => '',
                    'Unit price' => '',
                    'Short description' => '',
                    'Description' => '',
                    'Tags (x,y,z...)' => '', // (x,y,z...)
                    'Meta title' => '',
                    'Meta keywords' => '',
                    'Meta description' => '',
                    'URL rewritten' => '',
                    'Text when in stock' => '',
                    'Text when backorder allowed' => '',
                    'Available for order (0 = No, 1 = Yes)' => '', // (0 = No, 1 = Yes)
                    'Product availability date' => '',
                    'Product creation date' => '',
                    'Show price (0 = No, 1 = Yes)' => '', // (0 = No, 1 = Yes)
                    'Image URLs (x,y,z...)' => '', // (x,y,z...)
                    'Delete existing images (0 = No, 1 = Yes)' => '', // (0 = No, 1 = Yes)
                    'Feature (Name:Value:Position:Customized)' => '', // (Name:Value:Position:Customized)
                    'Available online only (0 = No, 1 = Yes)' => '', // (0 = No, 1 = Yes)
                    'Condition' => '',
                    'Customizable (0 = No, 1 = Yes)' => '', // (0 = No, 1 = Yes)
                    'Uploadable files (0 = No, 1 = Yes)' => '', // (0 = No, 1 = Yes)
                    'Text fields (0 = No, 1 = Yes)' => '', // (0 = No, 1 = Yes)
                    'Action when out of stock' => '',
                    'ID / Name of shop' => '',
                    'Advanced Stock Management' => '', 
                    'Depends on stock' => '',
                    'Warehouse' => ''
                );
            }
            $this->array_to_csv->input($main_array);
            $data = $this->array_to_csv->output($need_header, true);
            fwrite($csv_file, $data);

            // Make sure we're not output buffering anymore so we don't
            // run out of memory due to buffering.
            if (ob_get_level() > 0) {
                ob_end_flush();
            }
            //ob_implicit_flush(true);

            header("Content-type: application/vnd.ms-excel");
            header("Content-disposition: attachment; filename=merged-category-" . $this->sanitize($categoryMerged['name']) . '-' . date("Y-m-d") . ".csv");
            readfile($filepath);
        }
        die();
    }
    
    private function checkFolder($filepath) {
//        chdir($filepath);      
        if (!is_dir($filepath)) {
            if (!mkdir($filepath, 0777, true)){
                $this->notices->SetError("Failed to create folder" . $filepath);
                return false;
            }
        } else {
            return true;
        }
    }
    
    /**
     * Convert a string to the file/URL safe "slug" form
     *
     * @param string $string the string to clean
     * @param bool $is_filename TRUE will allow additional filename characters
     * @return string
     */
    public function sanitize($string = '', $is_filename = TRUE)
    {
     // Replace all weird characters with dashes
     $string = preg_replace('/[^\w\-'. ($is_filename ? '~_\.' : ''). ']+/u', '-', $string);

     // Only allow one dash separator at a time (and make string lowercase)
     return mb_strtolower(preg_replace('/--+/u', '-', $string), 'UTF-8');
    }
    
    public function getXmlProducts() {
        $CI = & get_instance();
        $this->load->library('admin_form');
        $form = new Admin_form;

        $this->load->model(array('site_model', 'advertiser_model', 'advertiser_temp_model'));
        $this->admin_navigation->module_link('Back', site_url('admincp/linkshare/getXML/'));
        $this->admin_navigation->module_link('Parse ALL Creative Categories', site_url('admincp2/linkshare/parseCreativeCategories'));

        $siteRow = $this->site_model->getSiteBySID($this->siteID);

        $temp = $this->advertiser_temp_model->getTempAdvertisers($siteID['id']);
        $current = array_merge($this->advertiser_model->getAdvertisers(array('id_status' => 1, 'id_site' => $siteRow['id'])));

        foreach ($current as $val) {
            $allMids[] = array('mid' => $val['mid'], 'name' => $val['name']);
        }

        $responseObj = array();

        if (isset($_GET['mid'])) {

            $categoriesRequestUrl = 'https://api.rakutenmarketing.com/linklocator/1.0/getCreativeCategories/' . $_GET['mid'];

            $config = new LinkshareConfig();

            $accessToken = $config->setSiteCookieAndGetAccessToken($CI, $this->siteID);

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

        $form->fieldset('Xml');
        $form->textarea('Header', 'header', $responseObj['header'], 'header', true, 'e.g., header', true);
        $form->textarea('Body', 'body', $responseObj['body'], 'body', true, 'e.g., body', true);

        $data = array(
            'form' => $form->display(),
            'form_title' => 'Products XML',
            'form_scope' => 'products',
            'site_name' => $siteRow['name'],
            'allMids' => $allMids,
            'form_action' => site_url('admincp/linkshare/')
        );

        $this->load->view('xml', $data);
    }

}
