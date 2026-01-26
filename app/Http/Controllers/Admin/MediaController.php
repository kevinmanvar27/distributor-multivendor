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
            'file_keys' => $request->files->keys(),
        ]);
        
        // Check if file exists in request
        if (!$request->hasFile('file')) {
            // Check if there was a file but it failed to upload
            $file = $request->file('file');
            if ($file) {
                $errorCode = $file->getError();
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'The file exceeds the maximum upload size (' . ini_get('upload_max_filesize') . '). Please upload a smaller file or contact administrator.',
                    UPLOAD_ERR_FORM_SIZE => 'The file exceeds the maximum form size.',
                    UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded. Please try again.',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Server error: Missing temporary folder.',
                    UPLOAD_ERR_CANT_WRITE => 'Server error: Failed to write file to disk.',
                    UPLOAD_ERR_EXTENSION => 'Server error: A PHP extension stopped the file upload.',
                ];
                $errorMessage = $errorMessages[$errorCode] ?? 'Unknown upload error (code: ' . $errorCode . ')';
                Log::error('File upload error: ' . $errorMessage, ['error_code' => $errorCode]);
                return response()->json(['success' => false, 'error' => $errorMessage], 400);
            }
            
            Log::error('No file found in request');
            return response()->json(['success' => false, 'error' => 'No file uploaded. Please select a file.'], 400);
        }
        
        $file = $request->file('file');
        
        // Check if file is valid
        if (!$file->isValid()) {
            $errorCode = $file->getError();
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'The file exceeds the maximum upload size (' . ini_get('upload_max_filesize') . '). Please upload a smaller file.',
                UPLOAD_ERR_FORM_SIZE => 'The file exceeds the maximum form size.',
                UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded. Please try again.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server error: Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Server error: Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'Server error: A PHP extension stopped the file upload.',
            ];
            $errorMessage = $errorMessages[$errorCode] ?? $file->getErrorMessage();
            Log::error('Invalid file upload', ['error_code' => $errorCode, 'error_message' => $errorMessage]);
            return response()->json(['success' => false, 'error' => $errorMessage], 400);
        }
        
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,wmv,mpg,ogg,webm,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv|max:20480', // 20MB max
            'name' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            Log::error('Media upload validation failed:', $validator->errors()->toArray());
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