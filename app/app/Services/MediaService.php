<?php

namespace App\Services;

use App\Models\Media;
use App\Support\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * MediaService — pipeline Media Library.
 *
 * Gambar: convert ke webp + generate varian thumbnail/medium/large (target < 100KB),
 *         lalu file asli dibuang.
 * Dokumen: disimpan asli.
 * Storage: disk public, path per tenant: media/{tenantId}/...
 */
class MediaService
{
    public const MAX_UPLOAD_BYTES = 10 * 1024 * 1024; // 10 MB

    public const IMAGE_MIME = ['image/jpeg', 'image/png', 'image/webp'];
    public const DOCUMENT_MIME = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    /** Target varian: nama => lebar maksimal. */
    public const VARIANTS = [
        'thumbnail' => 300,
        'medium' => 800,
        'large' => 1600,
    ];

    public const TARGET_BYTES = 100 * 1024; // < 100 KB
    public const MIN_QUALITY = 50;

    protected string $disk = 'public';

    public function __construct(protected TenantContext $tenantContext)
    {
    }

    public function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk($this->disk);
    }

    /**
     * Simpan file ke media library.
     *
     * @throws \InvalidArgumentException jika file tidak dikenali / melebihi limit
     */
    public function store(UploadedFile $file, array $options = []): Media
    {
        $this->guardFile($file);

        $tenantId = $this->tenantContext->requireId();
        $mime = $file->getMimeType() ?? $file->getClientMimeType();

        if (in_array($mime, self::IMAGE_MIME, true)) {
            return $this->storeImage($file, $tenantId, $options);
        }

        if (in_array($mime, self::DOCUMENT_MIME, true)) {
            return $this->storeDocument($file, $tenantId, $options);
        }

        throw new \InvalidArgumentException("Tipe file tidak didukung: {$mime}");
    }

    /**
     * Hapus media beserta file fisiknya.
     */
    public function delete(Media $media): void
    {
        foreach (['path_thumbnail', 'path_medium', 'path_large', 'path'] as $column) {
            $path = $media->{$column};
            if ($path && $this->disk()->exists($path)) {
                $this->disk()->delete($path);
            }
        }

        $media->delete();
    }

    protected function guardFile(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new \InvalidArgumentException('Upload gagal.');
        }

        if ($file->getSize() > self::MAX_UPLOAD_BYTES) {
            throw new \InvalidArgumentException('Ukuran file melebihi batas 10 MB.');
        }
    }

    protected function storeImage(UploadedFile $file, string $tenantId, array $options): Media
    {
        $source = imagecreatefromstring($file->get());
        if ($source === false) {
            throw new \InvalidArgumentException('File gambar tidak valid.');
        }

        $srcW = imagesx($source);
        $srcH = imagesy($source);

        $baseName = Str::uuid()->toString();
        $dir = "media/{$tenantId}";

        $paths = [];
        foreach (self::VARIANTS as $name => $maxWidth) {
            $resized = $this->resize($source, $srcW, $srcH, $maxWidth);
            $relative = "{$dir}/{$baseName}-{$name}.webp";
            $this->writeWebp($resized, $this->disk(), $relative);
            imagedestroy($resized);
            $paths["path_{$name}"] = $relative;
        }

        imagedestroy($source);

        return Media::create([
            'type' => Media::TYPE_IMAGE,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => 'image/webp',
            'file_size' => $file->getSize(),
            'width' => $srcW,
            'height' => $srcH,
            'title' => $options['title'] ?? null,
            'alt_text' => $options['alt_text'] ?? null,
            'category' => $options['category'] ?? null,
            'created_by' => $options['created_by'] ?? null,
            'tenant_id' => $tenantId,
            ...$paths,
        ]);
    }

    protected function storeDocument(UploadedFile $file, string $tenantId, array $options): Media
    {
        $dir = "media/{$tenantId}";
        $relative = "{$dir}/" . Str::uuid()->toString() . '-' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

        $this->disk()->put($relative, $file->get());

        return Media::create([
            'type' => Media::TYPE_DOCUMENT,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'path' => $relative,
            'title' => $options['title'] ?? null,
            'category' => $options['category'] ?? null,
            'created_by' => $options['created_by'] ?? null,
            'tenant_id' => $tenantId,
        ]);
    }

    protected function resize($src, int $srcW, int $srcH, int $maxWidth): \GdImage
    {
        if ($srcW <= $maxWidth) {
            $dstW = $srcW;
            $dstH = $srcH;
        } else {
            $dstW = $maxWidth;
            $dstH = (int) round($srcH * ($maxWidth / $srcW));
        }

        $dst = imagecreatetruecolor($dstW, $dstH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

        return $dst;
    }

    /**
     * Tulis gambar ke webp dengan quality adaptif hingga < target bytes.
     */
    protected function writeWebp(\GdImage $image, \Illuminate\Contracts\Filesystem\Filesystem $disk, string $path): void
    {
        $quality = 85;

        while (true) {
            ob_start();
            imagewebp($image, null, $quality);
            $bytes = ob_get_clean();

            if (strlen($bytes) <= self::TARGET_BYTES || $quality <= self::MIN_QUALITY) {
                $disk->put($path, $bytes);
                break;
            }

            $quality -= 5;
        }
    }
}
