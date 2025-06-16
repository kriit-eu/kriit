<?php namespace App\api;

use App\Controller;
use App\Db;

class imagedisplay extends Controller
{
    public function index()
    {
        // Get image ID from URL parameter
        $imageId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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
}
