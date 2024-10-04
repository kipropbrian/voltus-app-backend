<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Face;
use App\Models\Person;
use App\FacePlusClient;
use App\Models\Faceset;
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
            // Fetch details of the faceset from FacePlus API
            $response = $this->facePlusClient->getDetailFaceset($options);

            // Get facetokens from the response
            $facetokens = $response->json()['face_tokens'] ?? [];

            if (empty($facetokens)) {
                return response()->json([
                    'faceset' => $response->json(),
                    'faces' => [],
                ], 200);
            }

            // Fetch faces with related person and image from the database based on the face tokens
            $faces = Face::with(['person', 'image'])
                ->whereIn('face_token', $facetokens)
                ->get()
                ->keyBy('face_token'); // Key the collection by face_token for easier lookup

            // Transform data to include person and image information
            $facesData = collect($facetokens)->map(function ($token) use ($faces) {
                // Check if the face_token exists in the $faces collection
                $face = $faces->get($token);

                return [
                    'face_token' => $token, // Always include the face token
                    'person' => $face && $face->person ? [
                        'id' => $face->person->id,
                        'name' => $face->person->name,
                    ] : null,
                    'image' => $face && $face->image ? [
                        'id' => $face->image->id,
                        'url' => $face->image->transformed_url,
                    ] : null,
                ];
            });


            return response()->json([
                'faceset' => $response->json(),
                'faces' => $facesData,
            ], 200);
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
     * @param String $faceset_token
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(String $faceset_token)
    {
        try {
            //remove all faces from faceset first
            $options = [
                'faceset_token' => $faceset_token,
                'face_tokens' => 'RemoveAllFaceTokens'
            ];
            $response = $this->facePlusClient->removeFaceset($options);

            if (isset($response['error_message'])) {
                return response()->json([
                    'message' => 'Failed to delete faceset',
                    'error' => $response['error_message'],
                ], 400);
            }


            // Prepare the options array for the API call
            $options = ['faceset_token' => $faceset_token];

            // Make the API call to delete the FaceSet using the client
            $response = $this->facePlusClient->deleteFaceset($options);

            // Handle the Face++ API response
            if (isset($response['error_message'])) {
                return response()->json([
                    'message' => 'Failed to delete faceset',
                    'error' => $response['error_message'],
                ], 400);
            }

            // If deletion is successful, return a success response
            return response()->json([
                'message' => 'Faceset deleted successfully',
                'faceset_token' => $faceset_token, // Optionally return the token of the deleted FaceSet
            ], 200);
        } catch (Exception $e) {
            // Log the exception message for debugging purposes (optional)
            Log::error('Faceset deletion error: ' . $e->getMessage());

            // Return a 500 response for any unexpected server-side error
            return response()->json([
                'mesage' => 'An internal error occurred while attempting to delete the faceset.',
                'error' => $e->getMessage(), // Optionally include the exception message
            ], 500);
        }
    }

    /**
     * Add faces to a FaceSet.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncFace(Request $request)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'personId' => 'integer',
            'face_token' => 'string|required',
        ]);

        // Retrieve the person using the personId
        $person = Person::find($validated['personId']);

        if (!$person) {
            return response()->json([
                'message' => 'Person not found',
            ], 404);
        }

        // Call the FacePlus API to associate the face with the person (set user_id)
        $response = $this->facePlusClient->setUserIdFace([
            'face_token' => $validated['face_token'],
            'user_id' => $person->uuid,
        ]); // Use the person's UUID as the user_id

        $data = $response->object();

        // Handle errors from the setUserIdFace API call
        if (isset($data->error_message)) {
            Log::channel('stderr')->info($data->error_message);
            return response()->json([
                'message' => 'There was an issue with the set user ID request',
                'error' => $data->error_message,
            ], 500);
        }

        // Retrieve the active FaceSet
        $faceSet = Faceset::where('status', 'active')->first();

        if (!$faceSet) {
            return response()->json([
                'message' => 'No active FaceSet found',
            ], 404);
        }

        // Add the face to the FaceSet using the FacePlus API
        $response = $this->facePlusClient->addFaceset([
            'faceset_token' => $faceSet->faceset_token,
            'face_tokens' => $validated['face_token'],
        ]);

        $addFace = $response->object();

        // Handle errors from the addFaceset API call
        if (isset($addFace->error_message)) {
            return response()->json([
                'message' => 'There was an issue with the add face request',
                'error' => $addFace->error_message,
            ], 500);
        }

        // Return a successful JSON response
        return response()->json([
            'message' => 'Face successfully added to FaceSet',
            'data' => $addFace,
        ]);
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
    public function removeFace(Request $request, string $outer_id)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'face_token' => 'string|required',
        ]);

        $options = [
            'outer_id' => $outer_id,
            'face_tokens' => $validated['face_token']
        ];

        // Call FacePlus API to remove the face token from the FaceSet
        $response = $this->facePlusClient->removeFaceset($options);

        if (isset($response['error_message'])) {
            return response()->json([
                'message' => 'Failed to remove face from faceset',
                'error' => $response['error_message'],
            ], 400);
        }

        // 4. Find and delete the face from the `faces` table
        $face = Face::where('face_token', $validated['face_token'])->first();

        if ($face) {
            $face->delete(); // Delete the face from the database
        }

        return response()->json(['message' => 'Face removed successfully.'], 200);
    }

    /**
     * Remove all faces from FaceSet.
     *
     * @param String faceset_token
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeAllFaces(String $faceset_token)
    {
        try {
            //remove all faces from faceset first
            $options = [
                'faceset_token' => $faceset_token,
                'face_tokens' => 'RemoveAllFaceTokens'
            ];
            $response = $this->facePlusClient->removeFaceset($options);

            if (isset($response['error_message'])) {
                return response()->json([
                    'message' => 'Failed to delete faceset',
                    'error' => $response['error_message'],
                ], 400);
            }

            // If deletion is successful, return a success response
            return response()->json([
                'message' => 'All Faces deleted successfully',
                'faceset_token' => $faceset_token, // Optionally return the token of the deleted FaceSet
            ], 200);
        } catch (Exception $e) {
            // Log the exception message for debugging purposes (optional)
            Log::error('Faceset deletion error: ' . $e->getMessage());

            // Return a 500 response for any unexpected server-side error
            return response()->json([
                'mesage' => 'An internal error occurred while attempting to delete the faceset.',
                'error' => $e->getMessage(), // Optionally include the exception message
            ], 500);
        }
    }
}
