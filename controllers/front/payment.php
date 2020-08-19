<?php


class PayProPaymentModuleFrontController extends ModuleFrontController {

	public function postProcess() {
		$method = strtolower(Tools::getValue('method'));
		if ($method === 'ideal') {
			$method = Tools::getValue('paypro_ideal_issuer');
		}

		$this->paymentAction($method);
	}

	public function paymentAction($payMethod) {
		$cart = $this->context->cart;
		$address = new Address($cart->id_address_invoice);
		$customer = $this->context->customer;
		$language = new Language($customer->id_lang);

		$api = PayProHelper::createApi();

		// Create payment
		$redirectUrl = $this->context->link->getModuleLink('paypro', 'validation') . '?id_cart=' . $cart->id;
		$callbackUrl = $this->context->link->getModuleLink('paypro', 'callback');

		$data = [
			'amount' => (int) (round($cart->getOrderTotal(true, Cart::BOTH) * 100, 0)),
			'pay_method' => $payMethod,
			'return_url' => $redirectUrl,
			'cancel_url' => $redirectUrl,
			'postback_url' => $callbackUrl,
			'description' => Configuration::get('PS_SHOP_NAME'),
			'locale' => strtoupper($language->iso_code),
			'custom' => strval($cart->id),
			'consumer_email' => $customer->email,
			'consumer_firstname' => $address->firstname,
			'consumer_name' => $address->lastname,
			'consumer_phone' => $address->phone,
			'consumer_address' => trim($address->address1 . ' ' . $address->address2),
			'consumer_city' => $address->city,
			'consumer_companyname' => $address->company,
			'consumer_country' => $address->country,
			'consumer_postal' => $address->postcode,
		];

		$response = $api->createPayment($data);
		if ($response && isset($response['payment_url'])) {
			$sql = 'INSERT INTO`' . _DB_PREFIX_ . 'paypro` (cart_id, payment_hash) VALUES (' . $cart->id . ', "' . $response['payment_hash'] . '")';

			if (Db::getInstance()->Execute($sql)) {
				header("Location: " . $response['payment_url']);
				die();
			}
		}

		// Error or invalid response
		die(Tools::displayError("Error occurred!"));
	}
}


