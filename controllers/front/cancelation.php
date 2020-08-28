<?php

class PayProCancelationModuleFrontController extends ModuleFrontController
{
	public function postProcess() {
        $cart = $this->context->cart;

		Tools::redirect('index.php?controller=cart&id_cart='.$cart->id);
	}
}
