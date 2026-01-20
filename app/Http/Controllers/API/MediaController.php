<?php

namespace App\Http\Controllers\API;

use App\Models\Media;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Media",
 *     description="API Endpoints for Media Management"
 * )
 */
class MediaController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/api/v1/media",
     *      operationId="getMediaList",
     *      tags={"Media"},
     *      summary="Get list of media items",
     *      description="Returns list of media items with pagination",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="page",
     *          description="Page number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function index()
    {
        $media = Media::paginate(15);
        return $this->sendResponse($media, 'Media retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/api/v1/media",
     *      operationId="storeMedia",
     *      tags={"Media"},
     *      summary="Store new media item",
     *      description="Returns media data",
     *      security={{"sanctum": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"file_name","file_path","file_type","file_size"},
     *              @OA\Property(property="file_name", type="string", example="image.jpg"),
     *              @OA\Property(property="file_path", type="string", example="/storage/media/image.jpg"),
     *              @OA\Property(property="file_type", type="string", example="image/jpeg"),
     *              @OA\Property(property="file_size", type="integer", example=1024),
     *              @OA\Property(property="alt_text", type="string", example="Product image"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'file_name' => 'required|string|max:255',
            'file_path' => 'required|string|max:500',
            'file_type' => 'required|string|max:50',
            'file_size' => 'required|integer',
            'alt_text' => 'nullable|string|max:255',
        ]);

        $media = Media::create($request->all());

        return $this->sendResponse($media, 'Media created successfully.', 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/api/v1/media/{id}",
     *      operationId="getMediaById",
     *      tags={"Media"},
     *      summary="Get media information",
     *      description="Returns media data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Media id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function show($id)
    {
        $media = Media::find($id);

        if (is_null($media)) {
            return $this->sendError('Media not found.');
        }

        return $this->sendResponse($media, 'Media retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *      path="/api/v1/media/{id}",
     *      operationId="updateMedia",
     *      tags={"Media"},
     *      summary="Update existing media item",
     *      description="Returns updated media data",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Media id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"file_name","file_path","file_type","file_size"},
     *              @OA\Property(property="file_name", type="string", example="image.jpg"),
     *              @OA\Property(property="file_path", type="string", example="/storage/media/image.jpg"),
     *              @OA\Property(property="file_type", type="string", example="image/jpeg"),
     *              @OA\Property(property="file_size", type="integer", example=1024),
     *              @OA\Property(property="alt_text", type="string", example="Product image"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        $media = Media::find($id);

        if (is_null($media)) {
            return $this->sendError('Media not found.');
        }

        $request->validate([
            'file_name' => 'required|string|max:255',
            'file_path' => 'required|string|max:500',
            'file_type' => 'required|string|max:50',
            'file_size' => 'required|integer',
            'alt_text' => 'nullable|string|max:255',
        ]);

        $media->update($request->all());

        return $this->sendResponse($media, 'Media updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *      path="/api/v1/media/{id}",
     *      operationId="deleteMedia",
     *      tags={"Media"},
     *      summary="Delete media item",
     *      description="Deletes a media item",
     *      security={{"sanctum": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Media id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function destroy($id)
    {
        $media = Media::find($id);

        if (is_null($media)) {
            return $this->sendError('Media not found.');
        }

        $media->delete();

        return $this->sendResponse(null, 'Media deleted successfully.');
    }
}