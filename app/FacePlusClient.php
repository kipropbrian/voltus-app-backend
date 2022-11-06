<?php
/**
 * Sections are from https://github.dev/FacePlusPlus/facepp-php-sdk
 * 
 * */ 

namespace App;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class FacePlusClient 
{
     // Server URL in china is api-cn.faceplusplus.com, and in others is api-us.faceplusplus.com
    protected $host;

    // the request key
    protected $apiKey;

    // the request secret
    protected $apiSecret;

    /**
     * Constructor
     *
     * Usage: $client = new FppClient();
     *
     * @param string $apiKey The key you obtain from face++ web console
     * @param string $apiSecret The secret you obtain from face++ web console
     * @param string $hostname The domain name of the datacenter,For example: api-cn.faceplusplus.com
     * @throws Exception
     */
    public function __construct()
    {
        if (!env('FACEPLUS_API_KEY') || !env('FACEPLUS_API_SECRET') || !env('FACEPLUS_API_HOST') ) {
            throw new Exception("Please ensure your FPP credentials are set before continuing.");
        }

        $this->host = env('FACEPLUS_API_KEY' );
        $this->apiKey = env('FACEPLUS_API_SECRET');
        $this->apiSecret = env('FACEPLUS_API_HOST');
        Log::channel('stderr')->info("Config keys are set up!");
    }


    function request($path, $options) {
        $creds = [
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret
        ];

        $url = $this->generateUrl($path);

        $allOptions = array_merge($creds, $options);

        //Send post request to faceplus host
        $response = Http::dd()->asForm()->post($url, $allOptions);

        Log::channel('stderr')->info($response);

        return $response;
    }

    /**
     * @param string $path the request uri, starts with '/''
     * @return string the request url
     */
    public function generateUrl($path) {
        return trim($this->host, '/') . '/' . trim($path, '/');
    }

    /**
     * @param array $options The options for detect
     * @throws Exception \ RequestException
     */
    public function detectFace($options)
    {
        $path = '/facepp/v3/detect';
        return $this->request($path, $options);
    }

    public function compareFace($options)
    {
        $path = '/facepp/v3/compare';
        return $this->request($path, $options);
    }

    public function searchFace($options)
    {
        $path = '/facepp/v3/search';
        return $this->request($path, $options);
    }

    /**
     * create a faceset if outer_id not exists, else when
     * outer_id exists and force_merge is 0, return 400 error.
     */
    public function createFaceset($options)
    {
        $path = '/facepp/v3/faceset/create';
        return $this->request($path, $options);
    }

    /**
     * add one or more face to exists faceset
     */
    public function addFaceset($options) {
        $path = '/facepp/v3/faceset/addface';
        return $this->request($path, $options);
    }

    /**
     * remove one or more face of the faceset
     */
    public function removeFaceset($options) {
        $path = '/facepp/v3/faceset/removeface';
        return $this->request($path, $options);
    }

    /**
     * update faceset information
     */
    public function updateFaceset($options) {
        $path = '/facepp/v3/faceset/update';
        return $this->request($path, $options);
    }

    /**
     * get faceset information
     */
    public function getDetailFaceset($options) {
        $path = '/facepp/v3/faceset/getdetail';
        return $this->request($path, $options);
    }

    /**
     * delete faceset
     */
    public function deleteFaceset($options) {
        $path = '/facepp/v3/faceset/delete';
        return $this->request($path, $options);
    }

    /**
     * get faceset list
     */
    public function getFacesets($options) {
        $path = '/facepp/v3/faceset/getfacesets';
        return $this->request($path, $options);
    }

    /**
     * analyze face_token face
     */
    public function analyzeFace($options) {
        $path = '/facepp/v3/face/analyze';
        return $this->request($path, $options);
    }

    /**
     * get face token detail
     */
    public function getDetailFace($options) {
        $path = '/facepp/v3/face/getdetail';
        return $this->request($path, $options);
    }

    /**
     * set user id of the face token
     */
    public function setUserIdFace($options) {
        $path = '/facepp/v3/face/setuserid';
        return $this->request($path, $options);
    }

}