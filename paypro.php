<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;


if (!defined('_PS_VERSION_')) {
	exit;
}

require_once __DIR__ . '/paypro/PayProHelper.php';

class PayPro extends PaymentModule {

	private $paymentMethods;

	public function __construct() {
		$this->name = 'paypro';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.1';
		$this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
		$this->author = 'PayPro';
		$this->controllers = ['payment', 'validation', 'callback'];

		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('PayPro');
		$this->description = $this->l('PayPro payments');

		if (!PayProHelper::getConfigValue('api_key', true)) {
			$this->warning = $this->l('API key must be configured.');
		}

		if (!count(Currency::checkPaymentCurrencies($this->id))) {
			$this->warning = $this->l('No currency has been set for this module.');
		}

		$this->paymentMethods = [
			PayProHelper::AFTERPAY => ['label' => $this->l('Afterpay'), 'code' => 'afterpay/giro'],
			PayProHelper::BANCONTACT => ['label' => $this->l('Bancontact'), 'code' => 'bancontact/mrcash'],
			PayProHelper::IDEAL => ['label' => $this->l('iDEAL'), 'code' => 'ideal'],
			PayProHelper::MASTERCARD => ['label' => $this->l('Mastercard'), 'code' => 'creditcard/mastercard'],
			PayProHelper::PAYPAL => ['label' => $this->l('Paypal'), 'code' => 'paypal/direct'],
			PayProHelper::SEPA => ['label' => $this->l('Banktransfer'), 'code' => 'banktransfer/sepa'],
			PayProHelper::SEPA_ONCE => ['label' => $this->l('Direct Debit'), 'code' => 'directdebit/sepa-once'],
			PayProHelper::SOFORT_DIGITAL => ['label' => $this->l('Sofort Digital'), 'code' => 'sofort/digital'],
			PayProHelper::SOFORT_PHYSICAL => ['label' => $this->l('Sofort Physical'), 'code' => 'sofort/physical'],
			PayProHelper::VISA => ['label' => $this->l('Visa'), 'code' => 'creditcard/visa'],
		];
	}

	public function install() {
		return parent::install() && $this->registerHook('paymentOptions') && Db::getInstance()->Execute(
			'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'paypro` (`cart_id` int(11), `payment_hash` varchar(255)) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;');
	}

	public function uninstall() {
		foreach (PayProHelper::getConfigFields() as $field) {
			Configuration::deleteByName($field);
		}

		return Db::getInstance()->Execute( 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'paypro`') && parent::uninstall();
	}

	public function getContent() {
		// Check post
		$postOutput = $this->handleSubmit();

		$params = ['paymentMethods' => $this->paymentMethods];
		foreach (PayProHelper::getConfigFields() as $field) {
			$params[$field] = PayProHelper::getConfigValue($field);
		}

		$this->smarty->assign($params);
		return $postOutput . $this->display(__FILE__, 'admin.tpl');
	}

	public function hookPaymentOptions($params) {
		if (!$this->active) {
			return;
		}

		// Only allow EUR
		$currency = new Currency($params['cart']->id_currency);
		if ($currency->iso_code !== 'EUR') {
			return;
		}

		$paymentOptions = [];
		foreach ($this->paymentMethods as $key => $paymentMethod) {
			if (PayProHelper::getConfigValue($key) === '1') {
				$paymentOptions[] = $this->createPaymentMethod($key, $paymentMethod['label'], $paymentMethod['code']);
			}
		}

		return $paymentOptions;
	}

	private function createPaymentMethod($id, $label, $code) {
		$paymentOption = new PaymentOption();
		$inputs = [
			'method' => [
				'name' => 'method',
				'type' => 'hidden',
				'value' => $code,
			]
		];

		if ($id === PayProHelper::IDEAL) {
			$inputs['issuer'] = [
				'name' => 'paypro_ideal_issuer',
				'type' => 'hidden',
				'value' => '',
			];

			$issuers = PayProHelper::createApi()->getIdealIssuers();
			$this->smarty->assign(['issuers' => $issuers]);
			$paymentOption->setAdditionalInformation($this->fetch('module:paypro/views/templates/hook/issuers.tpl'));
		}

		$paymentOption
			->setCallToActionText($label)
			->setInputs($inputs)
			->setAction($this->context->link->getModuleLink($this->name, 'payment', [], true));

		return $paymentOption;
	}

	private function handleSubmit() {
		$apiKey = PayProHelper::getConfigValue('api_key', true);

		if ($apiKey) {
			foreach(PayProHelper::getConfigFields() as $configField) {
				Configuration::updateValue($configField, PayProHelper::getConfigValue($configField));
			}

			return $this->displayConfirmation($this->l('Settings updated'));
		}

		return '';
	}

	public function processPayment($cartID, $paymentHash) {
		$api = PayProHelper::createApi();
		$payment = $api->getPayment($paymentHash);

		if ($payment) {
			switch ($payment['current_status']) {
				case 'completed':
					$status = Configuration::get('PS_OS_PAYMENT');
					break;
				case 'cancelled':
					$status = Configuration::get('PS_OS_ERROR');
			}

			// Order update
			if (empty(Context::getContext()->link)) {
				Context::getContext()->link = new Link(); // workaround a prestashop bug so email is sent
			}

			$orderID = Order::getIdByCartId($cartID);
			if (!$orderID) {
				// Create new order
				$this->validateOrder($cartID, $status, $payment['amount_total'] / 100, $payment['pay_method'], null, [], null, false, $this->context->customer->secure_key);
			} else {
				$order = new Order($orderID);

				if ($order->current_state !== $status) {
					$orderHistory = new OrderHistory();
					$orderHistory->id_order = $orderID;
					$orderHistory->changeIdOrderState($status, $order, true);
					$orderHistory->addWithemail(true);
				}
			}
		}
	}
}
