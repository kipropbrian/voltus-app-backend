<?php

namespace App\Traits;

use App\Jobs\ProcessFacePlusRequest;

trait QueueableFacePlusRequest
{
    /**
     * Queue a request to the FacePlus API
     *
     * @param string $path
     * @param array $options
     * @param string|null $callback Event class to be dispatched after completion
     * @param string|null $queue Name of the queue to use
     * @return void
     */
    protected function queueRequest($path, $options, $callback = null, $queue = 'faceplus')
    {
        ProcessFacePlusRequest::dispatch($path, $options, $callback)
            ->onQueue($queue);
    }

    /**
     * Make all API methods queueable by prefixing with 'queue'
     */
    public function __call($method, $arguments)
    {
        if (strpos($method, 'queue') === 0) {
            $originalMethod = lcfirst(substr($method, 5));
            
            if (method_exists($this, $originalMethod)) {
                // Get the path that would be used by the original method
                $reflection = new \ReflectionMethod($this, $originalMethod);
                $path = $this->getPathForMethod($originalMethod);
                
                // Extract callback if provided
                $callback = isset($arguments[1]) ? $arguments[1] : null;
                $queue = isset($arguments[2]) ? $arguments[2] : 'faceplus';
                
                return $this->queueRequest($path, $arguments[0], $callback, $queue);
            }
        }
        
        throw new \BadMethodCallException("Method {$method} does not exist.");
    }

    /**
     * Get the API path for a given method
     */
    private function getPathForMethod($method)
    {
        $paths = [
            'detectFace' => '/facepp/v3/detect',
            'compareFace' => '/facepp/v3/compare',
            'searchFace' => '/facepp/v3/search',
            'createFaceset' => '/facepp/v3/faceset/create',
            'addFaceset' => '/facepp/v3/faceset/addface',
            'removeFaceset' => '/facepp/v3/faceset/removeface',
            'updateFaceset' => '/facepp/v3/faceset/update',
            'getDetailFaceset' => '/facepp/v3/faceset/getdetail',
            'deleteFaceset' => '/facepp/v3/faceset/delete',
            'getFacesets' => '/facepp/v3/faceset/getfacesets',
            'analyzeFace' => '/facepp/v3/face/analyze',
            'getDetailFace' => '/facepp/v3/face/getdetail',
            'setUserIdFace' => '/facepp/v3/face/setuserid'
        ];

        return $paths[$method] ?? null;
    }
}