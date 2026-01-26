<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MediaController extends Controller
{
    /**
     * Get the current vendor
     */
    private function getVendor()
    {
        return Auth::user()->vendor ?? Auth::user()->vendorStaff?->vendor;
    }

    /**
     * Display the media library.
     */
    public function index()
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.login')->with('error', 'Vendor profile not found.');
        }
        
        return view('vendor.media.index');
    }

    /**
     * Get media for the media library with filtering and pagination.
     */
    public function getMedia(Request $request)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 403);
        }
        
        $query = Media::where('vendor_id', $vendor->id);
        
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
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return response()->json(['success' => false, 'error' => 'Vendor not found'], 403);
        }
        
        // Log request details for debugging
        Log::info('Vendor Media upload request received:', [
            'vendor_id' => $vendor->id,
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
                Log::error('Vendor file upload error: ' . $errorMessage, ['error_code' => $errorCode, 'vendor_id' => $vendor->id]);
                return response()->json(['success' => false, 'error' => $errorMessage], 400);
            }
            
            Log::error('No file found in vendor media upload request', ['vendor_id' => $vendor->id]);
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
            Log::error('Invalid vendor file upload', ['error_code' => $errorCode, 'error_message' => $errorMessage, 'vendor_id' => $vendor->id]);
            return response()->json(['success' => false, 'error' => $errorMessage], 400);
        }
        
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,mp4,mov,avi|max:10240',
        ]);
        
        if ($validator->fails()) {
            Log::error('Vendor Media upload validation failed:', $validator->errors()->toArray());
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }
        
        try {
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            
            // Store in vendor-specific folder
            $path = $file->storeAs('vendor/' . $vendor->id, $fileName, 'public');
            
            $media = Media::create([
                'name' => pathinfo($originalName, PATHINFO_FILENAME),
                'file_name' => $fileName,
                'mime_type' => $file->getMimeType(),
                'path' => $path,
                'size' => $file->getSize(),
                'vendor_id' => $vendor->id,
            ]);
            
            // Append URL
            $media->append('url');
            
            Log::info('Vendor Media uploaded successfully:', ['media_id' => $media->id, 'vendor_id' => $vendor->id]);
            
            return response()->json([
                'success' => true,
                'media' => $media
            ]);
            
        } catch (\Exception $e) {
            Log::error('Vendor Media upload error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to upload file: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a media file.
     */
    public function destroy(Media $media)
    {
        $vendor = $this->getVendor();
        
        if (!$vendor) {
            return response()->json(['success' => false, 'error' => 'Vendor not found'], 403);
        }
        
        try {
            // Delete the file from storage
            if (Storage::disk('public')->exists($media->path)) {
                Storage::disk('public')->delete($media->path);
            }
            
            // Delete the database record
            $media->delete();
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Vendor Media delete error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to delete file'], 500);
        }
    }
}
