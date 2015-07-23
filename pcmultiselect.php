<?php

/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 42):
 * <jevin9@gmail.com> wrote this module. As long as you retain this notice you
 * can do whatever you want with this stuff. If we meet some day, and you think
 * this stuff is worth it, you can buy me a beer in return. Jevin O. Sewaruth.
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

		parent::__construct();

		$this->displayName = $this->l('Advanced multi selection Module');
		$this->description = $this->l('This is a advanced ui selector');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

		$this->_checkContent();

		$this->context->smarty->assign('module_name', $this->name);
	}

	public function install()
	{
		if (!parent::install() ||
			!$this->registerHook('actionAdminControllerSetMedia') ||
			!$this->registerHook('actionProductUpdate') ||
			!$this->registerHook('displayAdminProductsExtra') ||
			!$this->_createContent())
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall() ||
			!$this->_deleteContent())
			return false;
		return true;
	}
	
	public function hookDisplayHeader()
	{
		$this->context->controller->addCSS($this->_path.'css/style.css', 'all');
		$this->context->controller->addJS($this->_path.'js/script.js', 'all');
	}
	
	public function hookDisplayLeftColumn()
	{
		$this->context->smarty->assign(array(
			'placement' => 'left',
		));

		return $this->display(__FILE__, 'left.tpl');
	}
	
	public function hookDisplayRightColumn()
	{
		$this->context->smarty->assign(array(
			'placement' => 'right',
		));

		return $this->display(__FILE__, 'right.tpl');
	}
	
	public function hookDisplayFooter()
	{
		$this->context->smarty->assign(array(
			'module_link' => $this->context->link->getModuleLink('skeleton', 'details'),
		));

		return $this->display(__FILE__, 'footer.tpl');
	}

	public function getContent()
	{
		$message = '';

		if (Tools::isSubmit('submit_'.$this->name))
			$message = $this->_saveContent();

		$this->_displayContent($message);

		return $this->display(__FILE__, 'settings.tpl');
	}

	private function _saveContent()
	{
		$message = '';

		if (Configuration::updateValue('MOD_SKELETON_NAME', Tools::getValue('MOD_SKELETON_NAME')) &&
			Configuration::updateValue('MOD_SKELETON_COLOR', Tools::getValue('MOD_SKELETON_COLOR')))
			$message = $this->displayConfirmation($this->l('Your settings have been saved'));
		else
			$message = $this->displayError($this->l('There was an error while saving your settings'));

		return $message;
	}

	private function _displayContent($message)
	{
		$this->context->smarty->assign(array(
			'message' => $message,
			'MOD_SKELETON_NAME' => Configuration::get('MOD_SKELETON_NAME'),
			'MOD_SKELETON_COLOR' => Configuration::get('MOD_SKELETON_COLOR'),
		));
	}

	private function _checkContent()
	{
		if (!Configuration::get('MOD_SKELETON_NAME') &&
			!Configuration::get('MOD_SKELETON_COLOR'))
			$this->warning = $this->l('You need to configure this module.');
	}

	private function _createContent()
	{
		if (!Configuration::updateValue('MOD_SKELETON_NAME', '') ||
			!Configuration::updateValue('MOD_SKELETON_COLOR', ''))
			return false;
		return true;
	}

	private function _deleteContent()
	{
		if (!Configuration::deleteByName('MOD_SKELETON_NAME') ||
			!Configuration::deleteByName('MOD_SKELETON_COLOR'))
			return false;
		return true;

	}

	public function alterTable($method)
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
}

?>
