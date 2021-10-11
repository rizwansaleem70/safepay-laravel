<?php 
namespace Priceoye\Safepay;

class SafepayHandler {


    /** @var string Safepay sandbox API url. */
    public static $sandbox_api_url = 'https://sandbox.api.getsafepay.com/';

    /** @var string Safepay production API url. */
    public static $production_api_url = 'https://api.getsafepay.com/';

    /** @var string Safepay init transaction endpoint. */
    public static $init_transaction_endpoint = "order/v1/init";

    /** @var string Safepay sandbox API key. */
    public static $sandbox_api_key;

    /** @var string Safepay production API key. */
    public static $production_api_key;

    public static $environment;

    public static $client;

    public static $SANDBOX = "sandbox";

    public static $PRODUCTION = "production";


    /**
     * Get the response from an API request.
     * @param  string $endpoint
     * @param  array  $params
     * @param  string $method
     * @return array
     */
    public static function send_request($environment = "sandbox", $endpoint = "", $params = array(), $method = 'GET')
    {
        // $args = array(
        //     'method'  => $method,
        //     'headers' => array(
        //         'Content-Type' => 'application/json'
        //     )
        // );

        $baseURL = $environment === self::$SANDBOX ? self::$sandbox_api_url : self::$production_api_url;
        $url = $baseURL . $endpoint;

        // $args['body'] = json_encode($params);

        // dd($args);

        // $fields = self::build_post_fields($args);

        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        
        $headers = [
            'Content-Type: application/json'
        ];

        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $server_output = curl_exec($ch);
        
        
        curl_close($ch);
        
        $response = json_decode($server_output, true);
        
        if($response['status']['message'] == 'success')
        {
            return array(true, $response);
        }else{
            return array( false, $response['status']['message']);
        }
    }

    /**
     * Create a new charge request.
     * @param  int    $amount
     * @param  string $currency
     * @param  array  $metadata
     * @param  string $redirect
     * @param  string $name
     * @param  string $desc
     * @param  string $cancel
     * @return array
     */
    public static function create_charge($amount = null, $currency = null, $environment = "sandbox")
    {
        $args = array(
            "environment" => $environment
        );

        if (is_null($amount)) {
            return array(false, "Missing amount");
        }
        $args["amount"] = floatval($amount);

        if (is_null($currency)) {
            return array(false, "Missing currency");
        }
        $args["currency"] = $currency;

        $client = "";
        
        if ($environment === self::$SANDBOX) {
            $client = config('safepay.api_key');
        }
        elseif($environment === self::$PRODUCTION) {
            $client = config('safepay.api_key');
        }else{
            return array(false, "Invalid environment");
        }

        
        if ($client === "") {
            return array(false, "Missing client");
        }
        
        self::$client = $client;

        $args["client"] = $client;

        $result = self::send_request($environment, self::$init_transaction_endpoint, $args, 'POST');
        
        return $result;
    }

    public static function build_post_fields( $data,$existingKeys='',&$returnArray=[]){

        if(($data instanceof CURLFile) or !(is_array($data) or is_object($data))){
            $returnArray[$existingKeys]=$data;
            return $returnArray;
        }
        else{
            foreach ($data as $key => $item) {
                self::build_post_fields($item,$existingKeys?$existingKeys."[$key]":$key,$returnArray);
            }
            return $returnArray;
        }
    }
}