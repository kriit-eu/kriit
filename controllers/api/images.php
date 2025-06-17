<?php namespace App\api;

use App\Controller;
use App\Db;
use App\User;

class images extends Controller
{
    public function index()
    {
        $this->upload();
    }

    public function upload()
    {
        // Set JSON header
        header('Content-Type: application/json');

        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            stop(405, 'Method not allowed');
        }

        // Check authentication
        if (!$this->auth->userIsTeacher && !$this->auth->userIsAdmin && !$this->auth->userIsStudent) {
            stop(403, 'Authentication required');
        }

        try {
            // Check if file was uploaded
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                stop(400, 'No image uploaded or upload error');
            }

            $uploadedFile = $_FILES['image'];
            
            // Validate file type
            $allowedTypes = [
                'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 
                'image/webp', 'image/avif', 'image/bmp', 'image/tiff'
            ];
            if (!in_array($uploadedFile['type'], $allowedTypes)) {
                stop(400, 'Invalid image type. Supported formats: JPEG, PNG, GIF, WebP, AVIF, BMP, TIFF');
            }

            // Validate file size (max 10MB)
            if ($uploadedFile['size'] > 10 * 1024 * 1024) {
                stop(400, 'Image too large. Maximum size is 10MB.');
            }

            // Read and validate image
            $originalImageData = file_get_contents($uploadedFile['tmp_name']);
            $imageInfo = \getimagesizefromstring($originalImageData);
            
            if (!$imageInfo) {
                stop(400, 'Invalid image file');
            }

            // Calculate hash for deduplication
            $imageHash = hash('sha256', $originalImageData);
            
            // Check if image already exists
            $existingImage = Db::getFirst("SELECT imageId FROM uploaded_images WHERE imageHash = ?", [$imageHash]);
            
            if ($existingImage) {
                // Return existing image ID
                stop(200, [
                    'imageId' => $existingImage['imageId'],
                    'message' => 'Image already exists (deduplicated)'
                ]);
            }

            // Create image resource from uploaded file
            switch ($uploadedFile['type']) {
                case 'image/jpeg':
                case 'image/jpg':
                    $image = \imagecreatefromjpeg($uploadedFile['tmp_name']);
                    break;
                case 'image/png':
                    $image = \imagecreatefrompng($uploadedFile['tmp_name']);
                    break;
                case 'image/gif':
                    $image = \imagecreatefromgif($uploadedFile['tmp_name']);
                    break;
                case 'image/webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $image = \imagecreatefromwebp($uploadedFile['tmp_name']);
                    } else {
                        stop(400, 'WebP support not available');
                    }
                    break;
                case 'image/avif':
                    if (function_exists('imagecreatefromavif')) {
                        $image = \imagecreatefromavif($uploadedFile['tmp_name']);
                    } else {
                        stop(400, 'AVIF support not available');
                    }
                    break;
                case 'image/bmp':
                    if (function_exists('imagecreatefrombmp')) {
                        $image = \imagecreatefrombmp($uploadedFile['tmp_name']);
                    } else {
                        stop(400, 'BMP support not available');
                    }
                    break;
                case 'image/tiff':
                    // TIFF support is limited in GD, might need ImageMagick
                    stop(400, 'TIFF format not fully supported. Please convert to JPEG, PNG, or WebP.');
                default:
                    stop(400, 'Unsupported image type');
            }

            if (!$image) {
                stop(500, 'Failed to process image');
            }

            $originalWidth = \imagesx($image);
            $originalHeight = \imagesy($image);
            
            // Resize if width exceeds 1920px
            if ($originalWidth > 1920) {
                $newWidth = 1920;
                $newHeight = intval(($originalHeight * $newWidth) / $originalWidth);
                
                // Create new resized image
                $resizedImage = \imagecreatetruecolor($newWidth, $newHeight);
                
                // Preserve transparency for PNG and GIF
                if ($uploadedFile['type'] === 'image/png' || $uploadedFile['type'] === 'image/gif') {
                    \imagealphablending($resizedImage, false);
                    \imagesavealpha($resizedImage, true);
                    $transparent = \imagecolorallocatealpha($resizedImage, 255, 255, 255, 127);
                    \imagefill($resizedImage, 0, 0, $transparent);
                }
                
                // Resize the image
                \imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
                
                // Clean up original image
                \imagedestroy($image);
                $image = $resizedImage;
                
                $width = $newWidth;
                $height = $newHeight;
                $wasResized = true;
            } else {
                $width = $originalWidth;
                $height = $originalHeight;
                $wasResized = false;
            }
            
            // AVIF
            if (!function_exists('imageavif')) {
                \imagedestroy($image);
                stop(500, [
                    'error' => 'AVIF support not available',
                    'details' => 'PHP GD extension does not have AVIF support compiled in',
                    'solution' => 'Enable AVIF support in PHP or compile GD with AVIF support',
                    'check_diagnostics' => 'Visit /api/images/diagnostics for detailed server information',
                    'required_action' => 'Server configuration must be updated to support AVIF'
                ]);
            }
            
            ob_start();
            $success = \imageavif($image, null, 80); // 80% quality
            if (!$success) {
                ob_end_clean();
                \imagedestroy($image);
                stop(500, [
                    'error' => 'Failed to convert image to AVIF format',
                    'details' => 'AVIF encoding failed during processing',
                    'note' => 'AVIF function exists but encoding failed'
                ]);
            }
            
            $processedImageData = ob_get_contents();
            $processedMimeType = 'image/avif';
            ob_end_clean();
            \imagedestroy($image);

            // Calculate compression savings
            $originalSize = $uploadedFile['size'];
            $compressionSavings = $originalSize > strlen($processedImageData) ? 
                round((1 - strlen($processedImageData) / $originalSize) * 100, 1) : 0;

            $imageId = Db::insert('uploaded_images', [
                'imageHash' => $imageHash,
                'originalFilename' => $uploadedFile['name'],
                'originalMimeType' => $uploadedFile['type'],
                'originalSize' => $uploadedFile['size'],
                'processedMimeType' => $processedMimeType,
                'processedSize' => strlen($processedImageData),
                'imageData' => $processedImageData,
                'width' => $width,
                'height' => $height,
                'uploadedBy' => $this->auth->userId
            ]);

            $responseData = [
                'imageId' => $imageId,
                'originalWidth' => $originalWidth,
                'originalHeight' => $originalHeight,
                'width' => $width,
                'height' => $height,
                'originalMimeType' => $uploadedFile['type'],
                'processedMimeType' => $processedMimeType,
                'originalSize' => $originalSize,
                'processedSize' => strlen($processedImageData),
                'message' => 'Image uploaded and converted to AVIF successfully'
            ];
            
            // Add additional info if image was modified
            if ($wasResized) {
                $responseData['wasResized'] = true;
                $responseData['resizeInfo'] = "Resized from {$originalWidth}x{$originalHeight} to {$width}x{$height}";
            }
            
            if ($compressionSavings > 0) {
                $responseData['compressionSavings'] = $compressionSavings;
                $responseData['compressionInfo'] = "Size reduced by {$compressionSavings}% through AVIF conversion";
            }
            
            // Always mention format conversion since we convert everything to AVIF
            $responseData['formatChanged'] = true;
            $responseData['formatInfo'] = "Converted from {$uploadedFile['type']} to AVIF format";

            stop(200, $responseData);

        } catch (Exception $e) {
            error_log("Image upload error: " . $e->getMessage());
            stop(500, 'Internal server error');
        }
    }

    public function diagnostics()
    {
        // Set JSON header
        header('Content-Type: application/json');
        
        // Check if user is admin
        if (!$this->auth->userIsAdmin) {
            stop(403, 'Admin access required');
        }
        
        $diagnostics = [
            'php_version' => PHP_VERSION,
            'gd_version' => gd_info()['GD Version'] ?? 'Not available',
            'supported_formats' => [],
            'avif_functions' => [
                'imageavif' => function_exists('imageavif'),
                'imagecreatefromavif' => function_exists('imagecreatefromavif')
            ],
            'gd_info' => gd_info()
        ];
        
        // Check supported image formats
        if (function_exists('imagetypes')) {
            $types = imagetypes();
            $diagnostics['supported_formats'] = [
                'JPEG' => (bool)($types & IMG_JPG),
                'PNG' => (bool)($types & IMG_PNG),
                'GIF' => (bool)($types & IMG_GIF),
                'WebP' => (bool)($types & IMG_WEBP),
                'AVIF' => (bool)($types & IMG_AVIF ?? 0)
            ];
        }
        
        stop(200, $diagnostics);
    }
}
