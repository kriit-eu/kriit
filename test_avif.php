<?php
// Test AVIF support
echo "<h1>AVIF Support Test</h1>";

echo "<h2>PHP Version</h2>";
echo "<p>" . PHP_VERSION . "</p>";

echo "<h2>GD Information</h2>";
echo "<pre>";
print_r(gd_info());
echo "</pre>";

echo "<h2>Image Type Support</h2>";
$types = imagetypes();
echo "<ul>";
echo "<li>JPEG: " . (($types & IMG_JPG) ? "✅ Supported" : "❌ Not supported") . "</li>";
echo "<li>PNG: " . (($types & IMG_PNG) ? "✅ Supported" : "❌ Not supported") . "</li>";
echo "<li>GIF: " . (($types & IMG_GIF) ? "✅ Supported" : "❌ Not supported") . "</li>";
echo "<li>WebP: " . (($types & IMG_WEBP) ? "✅ Supported" : "❌ Not supported") . "</li>";
echo "<li>AVIF: " . (($types & (IMG_AVIF ?? 0)) ? "✅ Supported" : "❌ Not supported") . "</li>";
echo "</ul>";

echo "<h2>AVIF Function Availability</h2>";
echo "<ul>";
echo "<li>imageavif(): " . (function_exists('imageavif') ? "✅ Available" : "❌ Not available") . "</li>";
echo "<li>imagecreatefromavif(): " . (function_exists('imagecreatefromavif') ? "✅ Available" : "❌ Not available") . "</li>";
echo "</ul>";

echo "<h2>Test AVIF Creation</h2>";
if (function_exists('imageavif')) {
    // Create a simple test image
    $image = imagecreate(100, 100);
    $background = imagecolorallocate($image, 255, 255, 255);
    $text_color = imagecolorallocate($image, 0, 0, 0);
    imagestring($image, 5, 10, 40, "AVIF", $text_color);
    
    ob_start();
    $success = imageavif($image, null, 80);
    $avif_data = ob_get_contents();
    ob_end_clean();
    
    if ($success && !empty($avif_data)) {
        echo "<p>✅ AVIF creation test: SUCCESS</p>";
        echo "<p>Generated AVIF size: " . strlen($avif_data) . " bytes</p>";
        
        // Save test image
        file_put_contents('test_avif_image.avif', $avif_data);
        echo "<p>Test image saved as: test_avif_image.avif</p>";
    } else {
        echo "<p>❌ AVIF creation test: FAILED</p>";
    }
    
    imagedestroy($image);
} else {
    echo "<p>❌ AVIF functions not available</p>";
}

echo "<h2>Next Steps</h2>";
if (function_exists('imageavif')) {
    echo "<p>🎉 AVIF support is working! You can now use the image upload feature.</p>";
} else {
    echo "<p>⚠️ AVIF support is not working. Please rebuild your Docker container.</p>";
}
?>
