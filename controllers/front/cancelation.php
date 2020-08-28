<?php

class PayProCancelationModuleFrontController extends ModuleFrontController
{
	public function postProcess() {
		Tools::redirect('index.php?controller=cart&id_cart=' . $this->context->cart->id);
	}
}
