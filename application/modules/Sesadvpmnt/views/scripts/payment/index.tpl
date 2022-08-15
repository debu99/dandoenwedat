<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesadvpmnt
 * @package    Sesadvpmnt
 * @copyright  Copyright 2019-2020 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl  2019-04-25 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
 
 ?>
<script src="https://js.stripe.com/v3/"></script>

<div class="layout_middle">
	<div class="generic_layout_container layout_core_content sesbasic_bxs">

  	<div class="sesadvpmnt_stripe_payment_step">
      <div class="sesadvpmnt_stripe_pay_hint_txt"><?php echo $this->translate("Pay with card securely for completing the transaction with Stripe Payment Gateway. We will keep your details confidential.");?></div>
      <div class="sesadvpmnt_stripe_pay_button">
        <b><?php echo  $this->error; ?></b>
        <form action="<?php echo $this->returnUrl ? $this->returnUrl : '';?>" method="POST">
          <script
            src="https://checkout.stripe.com/checkout.js" class="stripe-button"
            data-key="<?php echo $this->publishKey; ?>"
            data-amount="<?php echo $this->amount*100; ?>"
            data-currency="<?php echo $this->currency; ?>"
            data-name="<?php echo $this->title; ?>"
            data-description="<?php echo $this->description; ?>"
            data-image="<?php echo Engine_Api::_()->sesadvpmnt()->getFileUrl($this->logo); ?>"
            data-locale="auto">
          </script>
        </form>
      </div>
		</div>
	</div>
</div>

<?php if($this->request_type === "sesevent_order" || ($this->request_type === "user" && !$this->isRecurring)) {?> 
    <div id="card-element">
      <form id="payment-form">
        <div class="form-row">
          <label for="ideal-bank-element">
            iDEAL Bank
          </label>
          <div id="ideal-bank-element">
          </div>
        </div>
        <button><?php echo $this->translate('Pay with Ideal'); ?></button>
        <div id="error-message" role="alert"></div>
      </form>
    </div>
<?php } ?>

<style>
  #card-element {
    background-color: white;
    padding: 15px;
  }
</style>

<script>
  var stripe = Stripe('<?php echo $this->publishKey;?>');
  var elements = stripe.elements();
  var options = {
    style: {
      base: {
        padding: '10px 12px',
        width: '200px',
        color: '#32325d',
        fontSize: '16px',
        '::placeholder': {
          color: '#aab7c4'
        },
      },
    },
  };

  var idealBank = elements.create('idealBank', options);
  idealBank.mount('#ideal-bank-element');

  var form = document.getElementById('payment-form');
  form.style.width = "200px";
  
  var accountholderName = document.getElementById('accountholder-name');

  form.addEventListener('submit', function(event) {
    event.preventDefault();

    stripe.confirmIdealPayment(
      '<?php echo $this->intent->client_secret; ?>',
      {
        payment_method: {
          ideal: idealBank,
        },
        return_url: "<?php echo $this->intent->return_url; ?>"
      }
    );
  });
</script>