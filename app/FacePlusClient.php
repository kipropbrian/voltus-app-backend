<?php

/**
 * Sections are from https://github.dev/FacePlusPlus/facepp-php-sdk
 *
 * */

namespace App;

use Exception;
use App\Models\FaceplusRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Traits\QueueableFacePlusRequest;
use Illuminate\Http\Client\RequestException;

class FacePlusClient
{
    use QueueableFacePlusRequest;

    // Server URL in china is api-cn.faceplusplus.com, and in others is api-us.faceplusplus.com
    protected $host;

    // the request key
    protected $apiKey;

    // the request secret
    protected $apiSecret;
    protected $maxRetries = 3;
    protected $retryDelay = 1000; // milliseconds

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
        if (!env('FACEPLUS_API_KEY') || !env('FACEPLUS_API_SECRET') || !env('FACEPLUS_API_HOST')) {
            throw new Exception("Please ensure your FPP credentials are set before continuing.");
        }

        $this->host = env('FACEPLUS_API_HOST');
        $this->apiKey = env('FACEPLUS_API_KEY');
        $this->apiSecret = env('FACEPLUS_API_SECRET');
    }

    /**
     * Makes an HTTP request to the FacePlus API with retry capability
     *
     * This method handles the actual HTTP request to the FacePlus API, including retry logic
     * with exponential backoff. It will attempt the request up to the specified maximum
     * number of retries before giving up.
     *
     * @param \Illuminate\Http\Client\PendingRequest $httpRequest The prepared HTTP request object
     * @param string $url The full URL to make the request to
     * @param array $allOptions Array of options to be sent with the request, including credentials
     * @param int $attempt Current attempt number, starting at 1
     * 
     * @throws \Illuminate\Http\Client\RequestException When the request fails and max retries are exceeded
     * @return \Illuminate\Http\Client\Response Returns the HTTP response object
     * 
     * 
     * The method implements exponential backoff for retries:
     * - 1st retry: waits retryDelay milliseconds
     * - 2nd retry: waits retryDelay * 2 milliseconds
     * - 3rd retry: waits retryDelay * 4 milliseconds
     * 
     * If all retries fail, returns a response with error details and 500 status code
     */
    protected function makeRequest($httpRequest, $url, $allOptions, $attempt = 1)
    {
        try {
            $response = $httpRequest->post($url, $allOptions);
            
            // Check if the response was successful
            if ($response->successful()) {
                return $response;
            }
            
            // If we get here, the request wasn't successful but didn't throw an exception
            throw new RequestException($response);
        } catch (Exception $e) {
            Log::warning("FacePlus API request failed (attempt {$attempt} of {$this->maxRetries})", [
                'url' => $url,
                'error' => $e->getMessage(),
                'attempt' => $attempt
            ]);

            // If we haven't exceeded max retries, try again
            if ($attempt < $this->maxRetries) {
                // Exponential backoff: wait longer between each retry
                $delay = $this->retryDelay * pow(2, $attempt - 1);
                usleep($delay * 1000); // Convert to microseconds
                
                return $this->makeRequest($httpRequest, $url, $allOptions, $attempt + 1);
            }

            // If we've exhausted all retries, return a failed response
            return Http::response([
                'error' => 'Max retries exceeded',
                'original_error' => $e->getMessage()
            ], 500);
        }
    }

    function request($path, $options)
    {
        $creds = [
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret
        ];

        $url = $this->generateUrl($path);
        $allOptions = array_merge($creds, $options);

        // Prepare the HTTP request
        if (isset($options['image_file']) && $options['image_file'] instanceof \Illuminate\Http\UploadedFile) {
            $imageFile = $options['image_file'];
            unset($allOptions['image_file']);

            $httpRequest = Http::acceptJson()->attach(
                'image_file',
                $imageFile->getContent(),
                $imageFile->getClientOriginalName()
            );
        } else {
            $httpRequest = Http::acceptJson()->asForm();
        }

        // Make the request with retry logic
        $response = $this->makeRequest($httpRequest, $url, $allOptions);

        // Create FaceplusRequest record regardless of success/failure
        $facePlusData = new FaceplusRequest;
        $facePlusData->endpoint = $path;
        $facePlusData->request_data = json_encode($options);
        $facePlusData->response_data = json_encode($response->json());
        $facePlusData->status_code = $response->status();

        // If the response was successful and contains a request_id, store it
        if ($response->successful() && isset($response->object()->request_id)) {
            $facePlusData->request_id = $response->object()->request_id;
        }

        $facePlusData->save();

        // Add the faceplusrequest_id to the response data
        $responseData = $response->object();
        if (is_object($responseData)) {
            $responseData->faceplusrequest_id = $facePlusData->id;
        }

        return $response;
    }

    /**
     * @param string $path the request uri, starts with '/''
     * @return string the request url
     */
    public function generateUrl($path)
    {
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
    public function addFaceset($options)
    {
        $path = '/facepp/v3/faceset/addface';
        return $this->request($path, $options);
    }

     /**
     * add one or more face to existing faceset async
     */
    public function asyncAddFaceToFaceset($options)
    {
        $path = '/facepp/v3/faceset/asaddface';
        return $this->request($path, $options);
    }

    /**
     * remove one or more face of the faceset
     */
    public function removeFaceset($options)
    {
        $path = '/facepp/v3/faceset/removeface';
        return $this->request($path, $options);
    }

    /**
     * update faceset information
     */
    public function updateFaceset($options)
    {
        $path = '/facepp/v3/faceset/update';
        return $this->request($path, $options);
    }

    /**
     * get faceset information
     */
    public function getDetailFaceset($options)
    {
        $path = '/facepp/v3/faceset/getdetail';
        return $this->request($path, $options);
    }

    /**
     * delete faceset
     */
    public function deleteFaceset($options)
    {
        $path = '/facepp/v3/faceset/delete';
        return $this->request($path, $options);
    }

    /**
     * get faceset list
     */
    public function getFacesets($options)
    {
        $path = '/facepp/v3/faceset/getfacesets';
        return $this->request($path, $options);
    }

    /**
     * analyze face_token face
     */
    public function analyzeFace($options)
    {
        $path = '/facepp/v3/face/analyze';
        return $this->request($path, $options);
    }

    /**
     * get face token detail
     */
    public function getDetailFace($options)
    {
        $path = '/facepp/v3/face/getdetail';
        return $this->request($path, $options);
    }

    /**
     * set user id of the face token
     */
    public function setUserIdFace($options)
    {
        $path = '/facepp/v3/face/setuserid';
        return $this->request($path, $options);
    }
}
