<?php

/**
 * Class PayProValidationModuleFrontController
 *
 * https://github.com/PrestaShop/paymentexample/blob/master/controllers/front/validation.php
 */
class PayProValidationModuleFrontController extends ModuleFrontController
{
	public function postProcess() {
        $cart = $this->context->cart;
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
			Tools::redirect('index.php?controller=order&step=1');
			return;
		}

		// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		$authorized = false;
		foreach (Module::getPaymentModules() as $module) {
			if ($module['name'] === 'paypro') {
				$authorized = true;
				break;
			}
		}

		if (!$authorized) {
			die($this->module->l('This payment method is not available.'));
		}

		$sql = 'SELECT payment_hash FROM `' . _DB_PREFIX_ . 'paypro` WHERE cart_id = ' . $cart->id;
		$result = Db::getInstance()->ExecuteS($sql);

		if (!isset($result[0]['payment_hash'])) {
			Tools::redirect('index.php?controller=order&step=1');
			return;
		}

		$payPro = new PayPro();
		$payPro->processPayment($cart->id, $result[0]['payment_hash']);

		Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$this->context->customer->secure_key);
	}
}

