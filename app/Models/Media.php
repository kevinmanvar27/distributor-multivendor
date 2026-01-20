<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'file_name',
        'mime_type',
        'path',
        'size',
        'vendor_id',
    ];

    /**
     * Get the URL for the media file.
     */
    public function getUrlAttribute()
    {
        return Storage::disk('public')->url($this->path);
    }

    /**
     * Get the products that use this media as their main photo.
     */
    public function productsAsMainPhoto()
    {
        return $this->hasMany(Product::class, 'main_photo_id');
    }

    /**
     * Get the products that use this media in their gallery.
     */
    public function productsInGallery()
    {
        return $this->belongsToMany(Product::class, 'product_gallery');
    }

    /**
     * Get the vendor that owns this media.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}