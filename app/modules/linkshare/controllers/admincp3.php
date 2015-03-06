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
        $this->dataset->datasource('produs_model', 'get_produse_by_mid', $filters);
        $this->dataset->base_url(site_url('admincp3/linkshare/listProducts/' . $id_site . '/' . $mid));
        $this->dataset->rows_per_page(20);

        $this->load->model('advertiser_model');

        // total rows
        $total_rows = $this->advertiser_model->getCountProductsByMID($mid, $id_site, $filters);
        $this->dataset->total_rows($total_rows);

        $this->dataset->initialize();

        // add actions
        $this->dataset->action('Delete', 'admincp3/linkshare/delete_produs');

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
    
    public function change_produs_status($id_product, $ret_url)
    {
        $ret_url = base64_decode($ret_url);
        $this->load->model('produs_model');
        $this->produs_model->change_produs_status($id_product);
        echo '<META http-equiv="refresh" content="0;URL=' . $ret_url . '">';
    }

    public function parse_product_search($id, $mid, $creative_cat_id = 0)
    {
        error_reporting(E_ERROR);
        include "app/third_party/LOG/Log.php";
        
        $aux = '';
        $this->load->model('site_model');
        $aux = $this->site_model->getSite($id);
        $token = $aux['token'];

        $this->load->model('produs_model');
        $this->load->model('produs_new_model');

        $creative_categories = array();
        $this->load->model('category_creative_model');
        $filters['id_site'] = $id;
        $filters['mid'] = $mid;
        $creative_categories = $this->category_creative_model->get_categorii($filters);

        $data = array();

        // print '<pre>';
        // print_r($creative_categories);
        // print "count = ".count($creative_categories);
        // die;
        //the creative category is not present it means we have finished adding/updating new products...now we must set the old ones to available='no' and return from method

        if (!array_key_exists($creative_cat_id, $creative_categories)) {
            $old = $this->produs_new_model->mark_old($mid);
            $data['message'] = "$old products marked as not available";
            $this->load->view('parse_product_search', $data);
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

                        $this->produs_new_model->new_produs($product[$i]);

                        $exista = $this->produs_model->exists_produs($product[$i]['linkid']);
                        if (!$exista) {
                            $this->produs_model->new_produs($product[$i]);
                            $j++;
                        } else {
                            $this->produs_model->update_produs_by_linkid($product[$i], $product[$i]['linkid']);
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

        $this->load->view('parse_product_search', $data);

        $creative_cat_id++;
        echo '<META http-equiv="refresh" content="1;URL=/admincp3/linkshare/parse_product_search/' . $id . '/' . $mid . '/' . $creative_cat_id . '">';
        die;
    }

    public function parse_product_xml_reader_search($id, $mid, $creative_cat_id = 0, $partial = 0, $page = 1, $id_produs_partial = 1, $j = 0, $k = 0)
    {
        error_reporting(E_ERROR);
        $aux = '';
        $this->load->model('site_model');
        $aux = $this->site_model->getSite($id);
        $token = $aux['token'];

        $this->load->model('produs_model');
        $this->load->model('produs_partial_model');
        $this->load->model('produs_new_model');

        $creative_categories = array();
        $this->load->model('category_creative_model');
        $filters['id_site'] = $id;
        $filters['mid'] = $mid;
        $creative_categories = $this->category_creative_model->get_categorii($filters);

        $data = array();

        // print '<pre>';
        // print_r($creative_categories);
        // print "count = ".count($creative_categories);
        // die;

        if (!$partial) {

            //the creative category is not present it means we have finished adding/updating new products...now we must set the old ones to available='no' and return from method
            if (!array_key_exists($creative_cat_id, $creative_categories)) {
                $old = $this->produs_new_model->mark_old($mid);
                $data['message'] = "$old products marked as not available";
                $this->load->view('parse_product_search', $data);
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

                $this->load->view('parse_product_search', $data);

                $creative_cat_id++;
                echo '<META http-equiv="refresh" content="1;URL=/admincp3/linkshare/parse_product_xml_reader_search/' . $id . '/' . $mid . '/' . $creative_cat_id . '">';
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

                        $this->produs_partial_model->new_produs($product[$i]);

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

                    $this->load->view('parse_product_search', $data);

                    $creative_cat_id++;
                    echo '<META http-equiv="refresh" content="1;URL=/admincp3/linkshare/parse_product_xml_reader_search/' . $id . '/' . $mid . '/' . $creative_cat_id . '">';
                    die;
                }

                $partial = 1;
                $id_produs_partial = 1;
                $j = $k = 0;

                $data['message'] = $message;

                $this->load->view('parse_product_search', $data);

                echo '<META http-equiv="refresh" content="1;URL=/admincp3/linkshare/parse_product_xml_reader_search/' . $id . '/' . $mid . '/' . $creative_cat_id . '/' . $partial . '/' . $page . '/' . $id_produs_partial . '/' . $j . '/' . $k . '/' . '">';
                die;
            }
        } else {
            $produs_partial = array();
            $produs_partial = $this->produs_partial_model->get_produs($id_produs_partial);
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
                $this->produs_new_model->new_produs($produs_partial);

                $exista = $this->produs_model->exists_produs($produs_partial['linkid']);
                if (!$exista) {
                    $this->produs_model->new_produs($produs_partial);
                    $j++;
                } else {
                    $this->produs_model->update_produs_by_linkid($produs_partial, $produs_partial['linkid']);
                    $k++;
                }

                $partial = 1;
                $id_produs_partial++;

                $data['message'] = "$id_produs_partial products parsed";

                $this->load->view('parse_product_search', $data);

                echo '<META http-equiv="refresh" content="0;URL=/admincp3/linkshare/parse_product_xml_reader_search/' . $id . '/' . $mid . '/' . $creative_cat_id . '/' . $partial . '/' . $page . '/' . $id_produs_partial . '/' . $j . '/' . $k . '/' . '">';
                die;
            } else {

                $i = $id_produs_partial - 1;
                $fp = fopen("loguri.txt", "a");
                fwrite($fp, "http://lld2.linksynergy.com/services/restLinks/getProductLinks/$token/$mid/$creative_category/$campaignID/$page $i total products $j inserted $k updated  " . date("Y-m-d H:i:s") . PHP_EOL);
                fclose($fp);

                //i finished all the partial products,truncate the linkshare_produs_partial,increment the page and refresh
                $this->produs_partial_model->sterge();
                $partial = 0;
                $page++;

                $data['message'] = "cat_id $creative_cat_id creative_category $creative_category page $page $i products parsed<br/>";
                $this->load->view('parse_product_search', $data);
                echo '<META http-equiv="refresh" content="1;URL=/admincp3/linkshare/parse_product_xml_reader_search/' . $id . '/' . $mid . '/' . $creative_cat_id . '/' . $partial . '/' . $page . '">';
                die;
            }
        }

        $data['message'] = 'FINISHED';

        $this->load->view('parse_product_search', $data);
    }
    

    public function test_mark_old()
    {
        $this->load->model('produs_model');
        $this->load->model('produs_new_model');
                
        $id = 2; $mid = 37920; $creative_cat_id = 200260395;
        $linkid = 78900666;
        
        // Delete product from linkshare_produs
        $this->produs_new_model->delete_old_from_current($linkid,$mid);
        
        //$produs = array();
        
        //$produs = $this->produs_new_model->get_produse_by_linkid($linkid,$mid);
       

        
//        echo "<pre>";
//        print_r ($produs);
//        echo "</pre>";
//        die();
       
        //$this->produs_new_model->copy_to_old($produs);
       
    }
    
    
}
