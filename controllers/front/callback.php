<?php

class PayProCallbackModuleFrontController extends ModuleFrontController {

	public function postProcess() {
		$paymentHash = Tools::getValue('payment_hash');
		$success = false;

		$sql = 'SELECT cart_id FROM `' . _DB_PREFIX_ . 'paypro` WHERE payment_hash = "' . Db::getInstance()->escape($paymentHash) . '"';
		$result = Db::getInstance()->ExecuteS($sql);

		if (!isset($result[0]['cart_id'])) {
			header('Content-Type: application/json');
		} else {
			$payPro = new PayPro();
			$payPro->processPayment($result[0]['cart_id'], $paymentHash);

			$success = true;
		}

		header('Content-Type: application/json');
		die(json_encode(['success' => $success]));
	}
}
