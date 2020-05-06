<div class="alert alert-info">
    <p><strong>{l s="This module allows you to accept payment with PayPro" mod='paypro'}</strong></p>
    <p>{l s="You need to configure it first with your PayPro API key." mod='paypro'}</p>
    <p>{l s="Also a Product ID is required if you want to enable Mastercard, Visa or Sofort payment methods." mod='paypro'}</p>
</div>

<form class="defaultForm form-horizontal" method="post">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-cogs"></i> {l s="Settings" mod='paypro'}
        </div>
        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s="Testmode" mod='paypro'}
                </label>
                <div class="col-lg-9">
                    <select name="paypro_test_mode">
                        <option value="0" {($paypro_test_mode === '0') ? 'selected="selected"' : ''}>{l s="Disabled" mod='paypro'}</option>
                        <option value="1" {($paypro_test_mode === '1') ? 'selected="selected"' : ''}>{l s="Enabled" mod='paypro'}</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3 required">
                    {l s="API Key" mod='paypro'}
                </label>
                <div class="col-lg-9">
                    <input type="text" name="paypro_api_key" value="{$paypro_api_key}" required="required">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s="Product ID" mod='paypro'}
                </label>
                <div class="col-lg-9">
                    <input type="text" name="paypro_product_id" value="{$paypro_product_id}">
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s="Save" mod='paypro'}
            </button>
        </div>
    </div>

    <div class="panel">
        <div class="panel-heading">
            {l s="Payment methods" mod='paypro'}
        </div>
        <div class="form-wrapper">
            {foreach $paymentMethods as $key => $paymentMethod}
                <div class="form-group">
                    <label class="control-label col-lg-3">
                        {$paymentMethod['label']}
                    </label>
                    <div class="col-lg-9">
                        <select name="{$key}">
                            <option value="0" {(${$key} === '0') ? 'selected="selected"' : ''}>{l s="Disabled" mod='paypro'}</option>
                            <option value="1" {(${$key} === '1') ? 'selected="selected"' : ''}>{l s="Enabled" mod='paypro'}</option>
                        </select>
                    </div>
                </div>
            {/foreach}
        </div>
        <div class="panel-footer">
            <button type="submit" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s="Save" mod='paypro'}
            </button>
        </div>
    </div>
</form>

