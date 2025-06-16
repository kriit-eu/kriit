-- Add image uploads table and modify messages table to support image attachments
-- Image uploads table for storing uploaded images in AVIF format with deduplication

-- Create images table to store uploaded images in AVIF format
CREATE TABLE IF NOT EXISTS `images` (
  `imageId` int unsigned NOT NULL AUTO_INCREMENT,
  `imageHash` varchar(64) NOT NULL COMMENT 'SHA256 hash of the original image for deduplication',
  `originalFilename` varchar(255) NOT NULL COMMENT 'Original filename when uploaded',
  `originalMimeType` varchar(100) NOT NULL COMMENT 'Original MIME type of the uploaded image',
  `originalSize` int unsigned NOT NULL COMMENT 'Original file size in bytes',
  `processedMimeType` varchar(100) NOT NULL COMMENT 'MIME type after processing (usually image/avif)',
  `processedSize` int unsigned NOT NULL COMMENT 'Processed file size in bytes',
  `imageData` longblob NOT NULL COMMENT 'Processed image data in AVIF format',
  `width` int unsigned NOT NULL COMMENT 'Image width in pixels',
  `height` int unsigned NOT NULL COMMENT 'Image height in pixels',
  `uploadedBy` int unsigned NOT NULL COMMENT 'User ID who uploaded the image',
  `uploadedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`imageId`),
  UNIQUE KEY `idx_image_hash` (`imageHash`),
  KEY `idx_uploaded_by` (`uploadedBy`),
  KEY `idx_uploaded_at` (`uploadedAt`),
  CONSTRAINT `fk_images_user` FOREIGN KEY (`uploadedBy`) REFERENCES `users` (`userId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Stores uploaded images in AVIF format with deduplication';

-- Images are stored as BLOB data in the database, no file system directories needed