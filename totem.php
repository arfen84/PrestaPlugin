<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}



class Totem extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'totem';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'ArFeN';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Totem');
        $this->description = $this->l('totem is module that allows to change some filtering option');

        $this->confirmUninstall = $this->l('');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('TOTEM_LIVE_MODE', false);

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
		  
		  $this->addTable()&&	
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
				$this->registerHook('displayAdminProductsExtra') &&
            $this->registerHook('actionProductUpdate');
    }

    public function uninstall()
    {
        Configuration::deleteByName('TOTEM_LIVE_MODE');

        include(dirname(__FILE__).'/sql/uninstall.php');
		  $this->deleteTable();

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitTotemModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitTotemModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'TOTEM_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'TOTEM_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'TOTEM_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'TOTEM_LIVE_MODE' => Configuration::get('TOTEM_LIVE_MODE', true),
            'TOTEM_ACCOUNT_EMAIL' => Configuration::get('TOTEM_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'TOTEM_ACCOUNT_PASSWORD' => Configuration::get('TOTEM_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

	 protected function renderFormTab()
    {
        $helper = new HelperForm();

       // $helper->show_toolbar = false;
        //$helper->table = $this->table;
       // $helper->module = $this;
        //$helper->default_form_language = $this->context->language->id;
        //$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

       // $helper->identifier = $this->identifier;
		 // $helper->id_product = 2;
        $helper->submit_action = 'updateproduct';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminProducts', false);
           /* .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;  */
        $helper->token = Tools::getAdminTokenLite('AdminProducts');
/*
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs *//*
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
 */
        return $helper->generateForm(array($this->getTabForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getTabForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Retail'),
                        'name' => 'TOTEM_Retail',
                        'is_bool' => true,
                        'desc' => $this->l('Retail product'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),  
						   array(	
                        'type' => 'switch',
                        'label' => $this->l('Dates'),
                        'name' => 'TOTEM_Dates',
                        'is_bool' => true,
                        'desc' => $this->l('Dates product'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),  
						   array(	
                        'type' => 'switch',
                        'label' => $this->l('Coordinates'),
                        'name' => 'TOTEM_Coordinates',
                        'is_bool' => true,
                        'desc' => $this->l('Coordinates product'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),  
							
							
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
					 
					 
					 'buttons' => array(
                                    'save-and-stay' => array(
                                    'title' => $this->l('Save and Stay'),
                                    'name' => 'submitAdd'.$this->table.'AndStay',
                                    'type' => 'submit',
                                    'class' => 'btn btn-default pull-right',
                                    'icon' => 'process-icon-save',
                      ),
                  ),
						
						
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
	  /*	
    protected function getTabFormValues()
    {
        return array(
            'TOTEM_LIVE_MODE' => Configuration::get('TOTEM_LIVE_MODE', true),
            'TOTEM_ACCOUNT_EMAIL' => Configuration::get('TOTEM_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'TOTEM_ACCOUNT_PASSWORD' => Configuration::get('TOTEM_ACCOUNT_PASSWORD', null),
        );
    } */
	 
	 	 public function addTable()
{
    /*
    $sql  = 'CREATE TABLE ps_totem_product 
    (id INT AUTO_INCREMENT,
    `retail` TINYINT NOT NULL,
    `dates`  TINYINT NOT NULL,
    `cords`  TINYINT NOT NULL);';
   
	 */
       
		  Db::getInstance()->execute(
                       'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'totem_product` (
                                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                `id_product` INT NULL,
                                `retail` TINYINT NOT NULL,
										  `dates` TINYINT NOT NULL,
										  `cords` TINYINT NOT NULL,		
                                PRIMARY KEY (`id`)
                             
                                )'
            ); 
	 
    return true;
}

 public function deleteTable()
{
    $sql  = 'DROP TABLE `'. _DB_PREFIX_.'totem_product`';
    Db::getInstance()->Execute($sql);

    return true;
}
	 /*
	 public function alterTable($method)
{
    switch ($method) {
        case 'add':
    $sql  = 'ALTER TABLE ' . _DB_PREFIX_ . 'product ADD `retail` TINYINT NOT NULL';
	 $sql2 = 'ALTER TABLE ' . _DB_PREFIX_ . 'product ADD `dates`  TINYINT NOT NULL';
	 $sql3 = 'ALTER TABLE ' . _DB_PREFIX_ . 'product ADD `cords`  TINYINT NOT NULL';
		  
       // return false;
            break;
         
        case 'remove':
        $sql  = 'ALTER TABLE ' . _DB_PREFIX_ . 'product DROP COLUMN `retail`';
		  $sql2 = 'ALTER TABLE ' . _DB_PREFIX_ . 'product DROP COLUMN `dates`';
		  $sql3 = 'ALTER TABLE ' . _DB_PREFIX_ . 'product DROP COLUMN `cords`';
            break;
    }
     
    if(!Db::getInstance()->Execute($sql))
       ;
	 if(!Db::getInstance()->Execute($sql2))		
       ;
	 if(!Db::getInstance()->Execute($sql3))	 ;
    return true;
} */
/*
public function prepareNewTab()
	{
	  $this->context->smarty->assign(array(
			'$retail' => '1' //$this->getCustomField(Tools::getValue('retail'))
		)); 
	} */

 	public function hookDisplayAdminProductsExtra()
	{
	//$this->prepareNewTab();
  //	$this->context->smarty->assign('$retail' , '1'); 
  $id_product = (int)Tools::getValue('id_product');	
  //$quer = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT retail FROM `'._DB_PREFIX_.'product` where `id_product` = 2');	
  //$que = 'SELECT retail FROM ' . _DB_PREFIX_ . 'product where id_product = '.$id_product;		
  //	$query2=Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT retail FROM `'._DB_PREFIX_.'product` where id_product=2`');
	
  //$ret =(int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($que);//(int)Db::getInstance()->Execute($que);
	
   $sqlret = 'SELECT retail FROM '._DB_PREFIX_.'totem_product where id_product='.$id_product;	
   $valueRet = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sqlret);
	
	$sqldat = 'SELECT dates FROM '._DB_PREFIX_.'totem_product where id_product='.$id_product;	
   $valueDat = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sqldat);
	
	$sqlcor = 'SELECT cords FROM '._DB_PREFIX_.'totem_product where id_product='.$id_product;	
   $valueCor = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sqlcor);
		  	
	 $this->context->smarty->assign(array(
        'retail' => $this->l((int)reset($valueRet)),
		  'dates' => $this->l((int)reset($valueDat))	,
		  'cords' => $this->l((int)reset($valueCor))	
    ));
       return 	$this->display(__FILE__, 'tab.tpl');
			//$output = $this->context->smarty->fetch($this->local_path.'views/templates/hook/tab.tpl');
			// return $helper->generateForm(array($this->getConfigForm()));
    		 //$output.$this->renderFormTab();
	}
	
  public function hookActionProductUpdate($params)
    {
        /* Place your code here. */
		//  $id_product = (int)Tools::getValue('id_product');	
 /*
    $languages = Language::getLanguages(true);
    foreach ($languages as $lang) { */
 $id_product = (int)Tools::getValue('id_product');
 $retail = Tools::getValue('retail');
 $dates = Tools::getValue('dates');
 $cords = Tools::getValue('cords');
 //()
 //if ($retail == 'ret') {$retail = 1;}  else {$retail = 0;} 
 //Db::getInstance()->update('product', array('retail'=> '20', 'id_product = 2' ));
 
 /*
 
 if(!Db::getInstance()->update('totem_product', array('retail'=> $retail),'id_product = '.$id_product  ))
            $this->context->controller->_errors[] = Tools::displayError('Error: ').mysql_error();
    } 
	  */
	   $sql11 = 'select 1 from   '._DB_PREFIX_.'totem_product';		
   $value = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql11);
		  	
	if ( (bool)reset($value) !== false ) 
	 {
			Db::getInstance()->update('totem_product', array('retail'=> $retail,'dates'=> $dates, 'cords' =>$cords),'id_product = '.$id_product );
			}
			else
			

			{
	  		DB::getInstance()->Execute('	
INSERT INTO `'._DB_PREFIX_.'totem_product` (`retail`, `dates`, `cords`,`id_product`) VALUES ('.$retail.', '.$dates.', '.$cords.','.$id_product.' )');
}
return true;
 
	 
 }
 
}
