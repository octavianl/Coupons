<?php


Class Product extends ProductCore
{
	/** @var string Extern Link */
	public $extern_link;

	public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, Context $context = null)
	{
		self::$definition['fields']['extern_link'] = array('type' => self::TYPE_STRING,  'required' => true, 'size' => 1024);
		parent::__construct($id_product, $full, $id_lang, $id_shop, $context);
		
	}
}