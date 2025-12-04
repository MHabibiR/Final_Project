<?php
class ApiClient {
    private $apiToken;

    public function __construct($token) {
        $this->apiToken = $token;
    }

    public function getAirQualityByGeo($lat, $lon) {
        $url = "https://api.waqi.info/feed/geo:" . $lat . ";" . $lon . "/?token=" . $this->apiToken;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        
        $output = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($output === false) {
            return ['status' => 'error', 'message' => $error];
        }

        return json_decode($output, true);
    }
}
?>