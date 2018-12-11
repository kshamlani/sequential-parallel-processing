<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: amitahire
 * Date: 18/5/17
 * Time: 10:20 AM
 */
require_once APPPATH.'third_party/stripe/vendor/autoload.php';


Class Stripe
{
    /**
     * @var object Stripe
     */
    private $stripe;

    /**
     * @var TOKEN
     */
    public $token;

    public $amount;

    public $currency;

    /**
     * Stripe constructor
     */
    public function __construct()
    {
        // Load config
        $this->load->config('stripe');

        \Stripe\Stripe::setApiKey($this->config->item('stripe_secret_key'));

    }

    public function charge(){
        $charge = \Stripe\Charge::create(
            array(
                "amount"=> $this->amount,
                "currency" => $this->currency,
                "description" => "Testing demo",
                "source" => $this->token
            )
        );

        return $charge;
    }

    /**
     * Enables the use of CI super-global without having to define an extra variable.
     * I can't remember where I first saw this, so thank you if you are the original author.
     *
     * Borrowed from the Ion Auth library (http://benedmunds.com/ion_auth/)
     *
     * @param $var
     *
     * @return mixed
     */
    public function __get($var)
    {
        return get_instance()->$var;
    }


}
