<?php
class ApiClient {
    private $apiToken;
    private $city;

    public function __construct() {
        $this->apiToken = getenv('API_TOKEN'); 
        $this->city = 'geo:-6.322369;107.337691'; 
    }

    public function getLatestAQI() {
        if (!$this->apiToken) {
            return ['status' => 'error', 'message' => 'API Token belum disetting di .env'];
        }

        $url = "https://api.waqi.info/feed/" . $this->city . "/?token=" . $this->apiToken;
        
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