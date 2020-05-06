<?php

require_once __DIR__ . '/PayProApi.php';

class PayProHelper
{
	const CONFIG_PREFIX = 'paypro_';
	const AFTERPAY = 'paypro_afterpay';
	const BANCONTACT = 'paypro_bancontact';
	const IDEAL = 'paypro_ideal';
	const MASTERCARD = 'paypro_mastercard';
	const PAYPAL = 'paypro_paypal';
	const SEPA = 'paypro_sepa';
	const SEPA_ONCE = 'paypro_sepa_once';
	const SOFORT_DIGITAL = 'paypro_sofort_digital';
	const SOFORT_PHYSICAL = 'paypro_sofort_physical';
	const VISA = 'paypro_visa';

	public static function getConfigFields() {
		$fields = array_map(function($field) {
			return self::CONFIG_PREFIX . $field;
		}, [ 'test_mode', 'api_key', 'product_id']);

		return array_merge([
			self::AFTERPAY,
			self::BANCONTACT,
			self::IDEAL,
			self::MASTERCARD,
			self::PAYPAL,
			self::SEPA,
			self::SEPA_ONCE,
			self::SOFORT_DIGITAL,
			self::SOFORT_PHYSICAL,
			self::VISA,
		], $fields);
	}

	public static function getConfigValue($field, $prefix = false) {
		$key = ($prefix ? self::CONFIG_PREFIX : '') . $field;

		return trim(Tools::getValue($key, Configuration::get($key)));
	}

	public static function createApi() {
		return new PayProApi(
			self::getConfigValue('api_key', true),
			self::getConfigValue('product_id', true),
			self::getConfigValue('test_mode', true) === '1'
		);
	}
}
