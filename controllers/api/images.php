<?php namespace App\api;

use App\Controller;
use App\Db;
use App\User;

class images extends Controller
{
    public function index()
    {
        // Check if this is an image display request (numeric ID in URL)
        if (isset($_GET['id']) || (isset($this->params[0]) && is_numeric($this->params[0]))) {
            $this->display();
        } else {
            $this->upload();
        }
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
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($uploadedFile['type'], $allowedTypes)) {
                stop(400, 'Invalid image type. Only JPEG, PNG, GIF, and WebP are allowed.');
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
                default:
                    stop(400, 'Unsupported image type');
            }

            if (!$image) {
                stop(500, 'Failed to process image');
            }

            $width = \imagesx($image);
            $height = \imagesy($image);

            // Convert to AVIF if supported, otherwise use WebP as fallback, or PNG as final fallback
            ob_start();
            $processedMimeType = 'image/png'; // Safe fallback
            
            if (function_exists('imageavif')) {
                $success = \imageavif($image, null, 80); // 80% quality
                $processedMimeType = 'image/avif';
            } elseif (function_exists('imagewebp')) {
                $success = \imagewebp($image, null, 80); // 80% quality
                $processedMimeType = 'image/webp';
            } else {
                $success = \imagepng($image, null, 8); // PNG compression level 8
                $processedMimeType = 'image/png';
            }
            
            if (!$success) {
                \imagedestroy($image);
                ob_end_clean();
                stop(500, 'Failed to convert image');
            }

            $processedImageData = ob_get_contents();
            ob_end_clean();
            \imagedestroy($image);

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

            stop(200, [
                'imageId' => $imageId,
                'width' => $width,
                'height' => $height,
                'processedMimeType' => $processedMimeType,
                'processedSize' => strlen($processedImageData),
                'message' => 'Image uploaded and processed successfully'
            ]);

        } catch (Exception $e) {
            error_log("Image upload error: " . $e->getMessage());
            stop(500, 'Internal server error');
        }
    }

    public function display()
    {
        // Get image ID from URL parameter or path
        $imageId = 0;
        
        if (isset($_GET['id'])) {
            $imageId = (int)$_GET['id'];
        } elseif (isset($this->params[0]) && is_numeric($this->params[0])) {
            $imageId = (int)$this->params[0];
        }

        if (!$imageId) {
            http_response_code(400);
            die('Image ID required');
        }

        try {
            // Fetch image from database
            $image = Db::getFirst("
                SELECT imageData, processedMimeType, originalFilename 
                FROM uploaded_images 
                WHERE imageId = ?
            ", [$imageId]);

            if (!$image) {
                http_response_code(404);
                die('Image not found');
            }

            // Set appropriate headers
            header('Content-Type: ' . $image['processedMimeType']);
            header('Content-Length: ' . strlen($image['imageData']));
            header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
            header('Content-Disposition: inline; filename="' . $image['originalFilename'] . '"');

            // Output image data
            echo $image['imageData'];

        } catch (Exception $e) {
            error_log("Image display error: " . $e->getMessage());
            http_response_code(500);
            die('Internal server error');
        }
    }

    /**
     * View method for displaying images (handles /api/images/ID routes)
     */
    public function view()
    {
        $this->display();
    }

    /**
     * Handle numeric method calls (image IDs)
     */
    public function __call($method, $args)
    {
        // If method name is numeric, treat it as an image ID
        if (is_numeric($method)) {
            // Set the image ID and call display
            $this->params = [$method];
            $this->display();
        } else {
            // Unknown method
            http_response_code(404);
            die('Method not found');
        }
    }
}
