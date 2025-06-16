<?php namespace App;

use App\Db;

/**
 * Class images
 * Controller for image management
 */
class images extends Controller
{
    public $template = 'admin';

    /**
     * View method for displaying images
     */
    public function view(): void
    {
        // Get image ID from URL parameter
        $imageId = isset($this->params[0]) ? (int)$this->params[0] : 0;

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
            exit;

        } catch (Exception $e) {
            error_log("Image display error: " . $e->getMessage());
            http_response_code(500);
            die('Internal server error');
        }
    }

    /**
     * Default index method
     */
    public function index(): void
    {
        // Redirect to admin or show image management interface
        $this->redirect('admin');
    }
}
