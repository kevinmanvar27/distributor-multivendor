<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MediaController extends Controller
{
    /**
     * Display the media library.
     */
    public function index()
    {
        $this->authorize('viewAny', Media::class);
        
        return view('admin.media.index');
    }

    /**
     * Get media for the media library with filtering and pagination.
     */
    public function getMedia(Request $request)
    {
        $this->authorize('viewAny', Media::class);
        
        $query = Media::query();
        
        // Apply search filter
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('file_name', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Apply type filter
        if ($request->has('type') && $request->type && $request->type !== 'all') {
            switch ($request->type) {
                case 'images':
                    $query->where('mime_type', 'like', 'image/%');
                    break;
                case 'videos':
                    $query->where('mime_type', 'like', 'video/%');
                    break;
                case 'documents':
                    $query->where(function($q) {
                        $q->where('mime_type', 'like', 'application/%')
                          ->orWhere('mime_type', 'like', 'text/%');
                    });
                    break;
            }
        }
        
        $media = $query->latest()->paginate(24);
        
        // Ensure all necessary attributes are visible
        $media->getCollection()->each(function ($item) {
            $item->makeVisible(['mime_type', 'name', 'file_name', 'size']);
        });
        
        // Append URL to each media item
        $media->getCollection()->each->append('url');
        
        return response()->json($media);
    }

    /**
     * Store a newly uploaded media file.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Media::class);
        
        // Log request details for debugging
        Log::info('Media upload request received:', [
            'has_file' => $request->hasFile('file'),
            'all_request_data' => $request->all(),
            'file_keys' => $request->files->keys(),
            'content_type' => $request->header('Content-Type'),
            'request_method' => $request->method(),
            'request_uri' => $request->getRequestUri(),
            'request_headers' => $request->headers->all(),
        ]);
        
        // Check if file exists in request
        if (!$request->hasFile('file')) {
            Log::error('No file found in request', [
                'files' => $request->files->all(),
                'input' => $request->input(),
                'all' => $request->all(),
            ]);
            return response()->json(['success' => false, 'error' => 'No file uploaded'], 400);
        }
        
        $file = $request->file('file');
        
        // Log file details
        Log::info('File details:', [
            'is_valid' => $file->isValid(),
            'path' => $file->path(),
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'error' => $file->getError(),
        ]);
        
        // Check if file is valid
        if (!$file->isValid()) {
            Log::error('Invalid file upload', [
                'error_code' => $file->getError(),
                'error_message' => $file->getErrorMessage(),
            ]);
            return response()->json(['success' => false, 'error' => 'Invalid file: ' . $file->getErrorMessage()], 400);
        }
        
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,wmv,mpg,ogg,webm,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv|max:20480', // 20MB max
            'name' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            // Log validation errors for debugging
            Log::error('Media upload validation failed:', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all(),
                'file_info' => $request->file('file') ? [
                    'original_name' => $request->file('file')->getClientOriginalName(),
                    'mime_type' => $request->file('file')->getMimeType(),
                    'size' => $request->file('file')->getSize(),
                    'extension' => $request->file('file')->getClientOriginalExtension(),
                ] : null,
            ]);
            
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        $name = $request->name ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        
        // Store the file
        $path = $file->store('media', 'public');
        
        // Create media record
        $media = Media::create([
            'name' => $name,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'path' => $path,
            'size' => $file->getSize(),
        ]);
        
        // Append URL to the media item
        $media->append('url');
        
        return response()->json(['success' => true, 'media' => $media]);
    }

    /**
     * Delete the specified media file.
     */
    public function destroy(Media $media)
    {
        $this->authorize('delete', $media);
        
        // Delete the file from storage
        Storage::disk('public')->delete($media->path);
        
        // Delete the media record
        $media->delete();
        
        return response()->json(['success' => true]);
    }

    /**
     * Clean up database records for missing files.
     */
    public function cleanup()
    {
        $this->authorize('delete', Media::class);
        
        $deletedCount = 0;
        $mediaItems = Media::all();
        
        foreach ($mediaItems as $media) {
            if (!Storage::disk('public')->exists($media->path)) {
                $media->delete();
                $deletedCount++;
            }
        }
        
        return response()->json(['success' => true, 'deleted_count' => $deletedCount]);
    }

    /**
     * Check storage and symlink status.
     */
    public function checkStorage()
    {
        $this->authorize('viewAny', Media::class);
        
        $storageExists = Storage::disk('public')->exists('media');
        $symlinkExists = file_exists(public_path('storage'));
        
        return response()->json([
            'storage_exists' => $storageExists,
            'symlink_exists' => $symlinkExists,
            'media_directory' => public_path('storage/media'),
        ]);
    }
}