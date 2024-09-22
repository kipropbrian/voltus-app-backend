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
     * Show details of a specific faceset
     *
     * @param string $outer_id
     * @return JsonResponse
     */
    public function show(string $outer_id): JsonResponse
    {
        $options = ['outer_id' => $outer_id];
        try {
            $response = $this->facePlusClient->getDetailFaceset($options);
            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            Log::error("Error showing faceset: " . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve faceset details'], 500);
        }
    }

    /**
     * Update a faceset
     *
     * @param Request $request
     * @param string $outer_id
     * @return JsonResponse
     */
    public function update(Request $request, string $outer_id): JsonResponse
    {
        $validated = $request->validate([
            'display_name' => 'nullable|string',
            'tags' => 'nullable|string',
            'display_name' => 'nullable|string',
            'user_data' => 'nullable|string'
        ]);

        // Sanitize the tags field
        if (!empty($validated['tags'])) {
            $validated['tags'] = $this->sanitizeTags($validated['tags']);
        }

        $validated['outer_id'] = $outer_id;

        try {
            $response = $this->facePlusClient->updateFaceset($validated);
            return response()->json($response->json(), $response->status());
        } catch (Exception $e) {
            Log::error("Error updating faceset: " . $e->getMessage());
            return response()->json(['error' => 'Failed to update faceset'], 500);
        }
    }

    /**
     * Sanitize tags to remove unwanted characters and empty values.
     *
     * @param string $tags
     * @return string
     */
    protected function sanitizeTags(string $tags): string
    {
        // Split tags by comma, trim each tag, remove empty and forbidden characters
        $forbiddenCharacters = '/[\\^@,&=*\'"]/';

        $sanitizedTags = array_filter(
            array_map('trim', explode(',', $tags)), // Trim and split tags
            function ($tag) use ($forbiddenCharacters) {
                // Remove empty tags and tags with forbidden characters
                return !empty($tag) && !preg_match($forbiddenCharacters, $tag);
            }
        );

        // Join back the sanitized tags into a single string
        return implode(',', $sanitizedTags);
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
