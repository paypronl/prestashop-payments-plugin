<section>
    <p>
        <select class="form-control form-control-select" id="paypro-ideal-issuer-select">
            <option value="">{l s='Select your bank' mod='paypro'}</option>
            {foreach $issuers as $key => $value}
                <option value="{$key}">
                    {$value}
                </option>
            {/foreach}
        </select>
    </p>

    <div id="paypro-ideal-issuer-error" class="alert alert-danger" role="alert" data-alert="danger" style="display: none;">
        {l s='You have to select a bank' mod='paypro' }
    </div>
</section>

<script type="text/javascript">
    (function() {
        function setupIssuers() {
            if (document.getElementById('payment-confirmation') === null) {
                setTimeout(setupIssuers, 100);
                return;
            }

            const submitButton = document.getElementById('payment-confirmation').querySelector('button');
            const issuerSelect = document.getElementById('paypro-ideal-issuer-select');
            const errorContainer = document.getElementById('paypro-ideal-issuer-error');
            const issuerInput = document.querySelector('[name="paypro_ideal_issuer"]');

            submitButton.addEventListener('click', function(event) {
                hideElement(errorContainer);

                if (idealSelected() && issuerSelect.value === '') {
                    event.stopPropagation();
                    showElement(errorContainer);
                } else {
                    issuerInput.value = issuerSelect.value;
                }
            });

            issuerSelect.addEventListener('change', function(event) {
                hideElement(errorContainer);
            });
        }

        function idealSelected() {
            const radioInput = document.querySelector('input[name="payment-option"]:checked');

            if (radioInput === null) { return false; }

            const container = document.getElementById('pay-with-' + radioInput.id + '-form');
            const input = container.querySelector('input[name="method"]');

            return input.value === 'ideal';
        }

        function hideElement(element) {
            element.style.display = 'none';
        }

        function showElement(element) {
            element.style.display = 'block';
        }

        setupIssuers();
    }());
</script>
