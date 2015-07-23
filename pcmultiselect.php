<?php

/*
 * ----------------------------------------------------------------------------
 * This is a Bambara Module
 * ----------------------------------------------------------------------------
 */

if (!defined('_PS_VERSION_'))
	exit;

class pcmultiselect extends Module
{
	public function __construct()
	{
		$this->name = 'pcmultiselect';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'Purecobalt';
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Advanced multi selection Module');
		$this->description = $this->l('This is a advanced ui selector');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

		$this->context->smarty->assign('module_name', $this->name);
	}

	public function install()
	{
		if (!parent::install() ||
			!$this->registerHook('actionAdminControllerSetMedia') ||
			!$this->registerHook('actionProductUpdate') ||
			!$this->registerHook('displayAdminProductsExtra') ||
			!$this->alterTable('add') ||
			!Configuration::updateValue('MULTISELECT_NAME', ''))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall() ||
			!$this->alterTable('remove') ||
			!Configuration::deleteByName('MULTISELECT_NAME'))
			return false;
		return true;
	}


	public function getContent()
	{
		$output = null;
	 
		if (Tools::isSubmit('submit'.$this->name))
		{
			$my_module_name = strval(Tools::getValue('MULTISELECT_NAME'));
			if (!$my_module_name
			  || empty($my_module_name)
			  || !Validate::isGenericName($my_module_name))
				$output .= $this->displayError($this->l('Invalid Configuration value'));
			else
			{
				Configuration::updateValue('MULTISELECT_NAME', $my_module_name);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}
		return $output.$this->renderForm();
	}

	private function _postProcess()
	{
		
	}

	public function hookDisplayAdminProductsExtra($params)
	{
		if (Validate::isLoadedObject($product = new Product((int)Tools::getValue('id_product'))))
		{
			return $this->display(__FILE__, 'product-tab.tpl');
		}
	}


	private function alterTable($method)
	{
		switch ($method) {
			case 'add':
				$sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'product_lang ADD `wine_attribute` TEXT NOT NULL';
				break;
			 
			case 'remove':
				$sql = 'ALTER TABLE ' . _DB_PREFIX_ . 'product_lang DROP COLUMN `wine_attribute`';
				break;
		}
		 
		if(!Db::getInstance()->Execute($sql))
			return false;
		return true;
	}

	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
							'type' => 'text',
							'label' => $this->l('Food Pairing'),
							'name' => 'pms_food_pairing',
							'class' => 'fixed-width-md',
							'required' => true
						),
					)
				),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'btn btn-default pull-right')
		);

		$helper = new HelperForm();
		$helper->module = $this;
	    $helper->name_controller = $this->name;
	    $helper->token = Tools::getAdminTokenLite('AdminModules');
	    $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

	    $helper->default_form_language = $default_lang;
    	$helper->allow_employee_form_lang = $default_lang;

    	$helper->title = $this->displayName;
	    $helper->show_toolbar = true;        // false -> remove toolbar
	    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
	    $helper->submit_action = 'submit'.$this->name;
	    $helper->toolbar_btn = array(
	        'save' =>
	        array(
	            'desc' => $this->l('Save'),
	            'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
	            '&token='.Tools::getAdminTokenLite('AdminModules'),
	        ),
	        'back' => array(
	            'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
	            'desc' => $this->l('Back to list')
	        )
	    );
		
		$helper->fields_value['MULTISELECT_NAME'] = Configuration::get('MULTISELECT_NAME');
		return $helper->generateForm(array($fields_form));
	}

}

?>
