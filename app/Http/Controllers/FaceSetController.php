<?php

namespace App\Http\Controllers;

use Exception;
use App\FacePlusClient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FaceSetController extends Controller
{
    protected $facePlusClient;

    public function __construct(FacePlusClient $facePlusClient)
    {
        $this->facePlusClient = $facePlusClient;
    }

    /**
     * Create a new FaceSet.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'outer_id' => 'required|string',
            'display_name' => 'nullable|string',
            'tags' => 'nullable|string',
            'force_merge' => 'nullable|boolean',
            // Add other necessary validations based on Face++ API
        ]);

        try {
            $response = $this->facePlusClient->createFaceset($validated);
            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            Log::error("Error creating faceset: " . $e->getMessage());
            return response()->json(['error' => 'Failed to create faceset'], 500);
        }
    }

    /**
     * List all facesets
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $options = $request->only(['page', 'per_page']);
        try {
            $response = $this->facePlusClient->getFacesets($options);
            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            Log::error("Error listing facesets: " . $e->getMessage());
            return response()->json(['error' => 'Failed to list facesets'], 500);
        }
    }

    /**
     * Get details of a specific FaceSet.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $options = $request->all();
        $response = $this->facePlusClient->getDetailFaceset($options);
        return response()->json($response->json());
    }

    /**
     * Update a FaceSet.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $options = $request->all();
        $response = $this->facePlusClient->updateFaceset($options);
        return response()->json($response->json());
    }

    /**
     * Delete a FaceSet.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $options = $request->all();
        $response = $this->facePlusClient->deleteFaceset($options);
        return response()->json($response->json());
    }

    /**
     * Add faces to a FaceSet.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addFace(Request $request)
    {
        $options = $request->all();
        $response = $this->facePlusClient->addFaceset($options);
        return response()->json($response->json());
    }

    /**
     * Remove faces from a FaceSet.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFace(Request $request)
    {
        $options = $request->all();
        $response = $this->facePlusClient->removeFaceset($options);
        return response()->json($response->json());
    }
}
