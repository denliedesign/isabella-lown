<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media extends Model
{
    use HasFactory;

    /**
     * If you ever change disks, do it here.
     */
    public const DISK = 'spaces';

    /**
     * Types for `type` column.
     */
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_EMBED = 'embed';

    protected $fillable = [
        'type',          // image|video|embed
        'path',          // e.g. 'portfolio/uuid.mp4' (null for embeds)
        'poster_path',   // e.g. 'portfolio_posters/uuid.jpg'
        'embed_html',    // iframe markup when type = embed
        'title',
        'sort_order',
        'created_by',
        'tag',           // e.g. 'all', 'dancing', etc.
        'style',         // optional (hip-hop|contemporary|fusion)
        'mime',          // optional: 'video/mp4', 'image/jpeg', etc.
        'size',          // optional: bytes
        'duration',      // optional: seconds (if you store it)
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'created_by' => 'integer',
        'size'       => 'integer',
        'duration'   => 'integer',
    ];

    /* -----------------------------
     |          Accessors
     * ----------------------------*/

    /**
     * CDN-backed URL for the primary asset.
     * Falls back to local /storage for legacy items.
     */
    protected function cdnUrl(): Attribute
    {
        return Attribute::get(function () {
            return $this->path ? $this->assetUrl($this->path) : null;
        });
    }

    /**
     * CDN-backed URL for the poster image (if any).
     */
    protected function posterCdnUrl(): Attribute
    {
        return Attribute::get(function () {
            return $this->poster_path ? $this->assetUrl($this->poster_path) : null;
        });
    }

    /**
     * Convenience: returns a temporary signed URL (useful if you flip to private).
     */
    public function signedUrl(int $minutes = 10): ?string
    {
        if (!$this->path) return null;

        // Legacy local file:
        if (Str::startsWith($this->path, 'storage/')) {
            return asset($this->path);
        }

        return Storage::disk(self::DISK)->temporaryUrl($this->path, now()->addMinutes($minutes));
        // Note: DigitalOcean Spaces supports S3 v4 signing via the S3 driver.
    }

    /* -----------------------------
     |          Scopes
     * ----------------------------*/

    public function scopeOrdered($q)
    {
        return $q->orderBy('sort_order')->orderByDesc('id');
    }

    public function scopeTagged($q, string $tag)
    {
        return $tag === 'all' ? $q : $q->where('tag', $tag);
    }

    public function scopeOfType($q, string $type)
    {
        return $q->where('type', $type);
    }

    public function scopeSearch($q, ?string $term)
    {
        if (!$term) return $q;
        return $q->where(function ($qq) use ($term) {
            $qq->where('title', 'like', "%{$term}%")
                ->orWhere('tag', 'like', "%{$term}%")
                ->orWhere('style', 'like', "%{$term}%");
        });
    }

    /* -----------------------------
     |          Helpers
     * ----------------------------*/

    public function isImage(): bool { return $this->type === self::TYPE_IMAGE; }
    public function isVideo(): bool { return $this->type === self::TYPE_VIDEO; }
    public function isEmbed(): bool { return $this->type === self::TYPE_EMBED; }

    /**
     * Turn a stored key into a public URL (CDN for Spaces, asset() for legacy).
     */
    private function assetUrl(string $key): string
    {
        if (Str::startsWith($key, 'storage/')) {
            // Old local files kept under /public/storage/...
            return asset($key);
        }

        // Spaces → returns CDN URL if 'url' is set on the disk in config/filesystems.php
        return Storage::disk(self::DISK)->url($key);
    }

    /* -----------------------------
     |          Model Events
     * ----------------------------*/

    /**
     * On delete, remove files from disk(s).
     * (Enable only if your Spaces key has Delete permission.)
     */
    protected static function booted(): void
    {
        static::deleting(function (Media $media) {
            // Soft delete: don't remove files on soft delete
            if ($media->isForceDeleting()) {
                foreach (['path', 'poster_path'] as $col) {
                    $val = $media->{$col};
                    if (!$val) continue;

                    if (Str::startsWith($val, 'storage/')) {
                        // Legacy local public disk
                        $local = Str::after($val, 'storage/'); // strip prefix
                        Storage::disk('public')->delete($local);
                    } else {
                        // Spaces object key
                        Storage::disk(self::DISK)->delete($val);
                    }
                }
            }
        });
    }
}
