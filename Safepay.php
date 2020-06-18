<?php 
namespace Rizwan\Safepay;

use Rizwan\Safepay\SafepayHandler;


class Safepay
{
    const SANDBOX                     = "sandbox";
    const PRODUCTION                  = "production";

    const PRODUCTION_CHECKOUT_URL     = "https://www.getsafepay.com/components";
    const SANDBOX_CHECKOUT_URL        = "https://sandbox.api.getsafepay.com/components";

    public function process_payment( $order_id, $total)
    {   

        try {
            $env = $this->get_environment();

            $result = SafepayHandler::create_charge(
                $total, config('safepay.currency'), $env
            );

            if (!$result[0]) {
                return array('result' => $result[1]);
            }

            $charge = $result[1]['data'];

            $hosted_url = $this->construct_url($order_id, $charge['token'], $env);
            return array(
                'result'   => 'success',
                'redirect' => $hosted_url,
            );
        //code...
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    protected function get_environment()
    {
        return config('safepay.environment');
    }

    protected function construct_url($order_id, $tracker="", $environment)
    {
        $baseURL = $environment == "sandbox" ? self::SANDBOX_CHECKOUT_URL : self::PRODUCTION_CHECKOUT_URL;
        $params = array(
            "env"            => $environment == "sandbox" ? SafepayHandler::$SANDBOX : SafepayHandler::$PRODUCTION,
            "beacon"         => $tracker,
            "source"         => 'custom',
            "order_id"       => $order_id,
            "redirect_url"   => config('safepay.redirect_url'),
            "cancel_url"     => config('safepay.cancel_url')
        );

        $baseURL = $baseURL. "?env=".$params['env']."&beacon=".$params['beacon']."&source=".$params['source']."&order_id=".$params['order_id']."&redirect_url=".$params['redirect_url']."&cancel_url=".$params['cancel_url'];

        return $baseURL;
    }

    /**
     * Check Safepay webhook request is valid.
     * @param  string $tracker
     */
    public function validate_signature($tracker, $signature)
    {
        $secret = config('safepay.webhook_secret_key');
        $signature_2 = hash_hmac('sha256', $tracker, $secret);

        if ($signature_2 === $signature) {
            return true;
        }

        return false;
    }
}