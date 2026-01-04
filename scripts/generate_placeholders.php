<?php
$imagesDir = __DIR__ . '/../public/assets/images';
if (!is_dir($imagesDir)) {
    mkdir($imagesDir, 0777, true);
}

function createPlaceholder($filename, $width, $height, $r, $g, $b, $text) {
    global $imagesDir;
    $im = imagecreatetruecolor($width, $height);
    
    // Transparent background
    imagealphablending($im, false);
    $col = imagecolorallocatealpha($im, 255, 255, 255, 127);
    imagefilledrectangle($im, 0, 0, $width, $height, $col);
    imagealphablending($im, true);
    
    // Draw shape
    $color = imagecolorallocate($im, $r, $g, $b);
    $textColor = imagecolorallocate($im, 255, 255, 255);
    
    // Fill slightly smaller to show transparency
    imagefilledellipse($im, $width/2, $height/2, $width-4, $height-4, $color);
    
    // Add text if small enough
    if ($width > 50) {
        imagestring($im, 5, 10, $height/2 - 10, $text, $textColor);
    }
    
    imagesavealpha($im, true);
    imagepng($im, $imagesDir . '/' . $filename);
    imagedestroy($im);
    echo "Created $filename\n";
}

// 1. Sidebar Decor (Special shape)
$filename = 'sidebar_decor_poseidon.png';
$width = 250;
$height = 300;
$im = imagecreatetruecolor($width, $height);
imagealphablending($im, false);
$col = imagecolorallocatealpha($im, 255, 255, 255, 127);
imagefilledrectangle($im, 0, 0, $width, $height, $col);
imagealphablending($im, true);
imagesavealpha($im, true);

// Draw "Poseidon" (Abstract)
$gold = imagecolorallocate($im, 218, 165, 32);
$coral = imagecolorallocate($im, 255, 127, 80);
$blue = imagecolorallocate($im, 0, 100, 200);

// Coral at bottom
imagefilledrectangle($im, 0, 250, 250, 300, $coral);
// Trident line
imagefilledrectangle($im, 120, 50, 130, 280, $gold);
// Text
$textColor = imagecolorallocate($im, 255, 255, 255);
imagestring($im, 5, 80, 150, "Poseidon", $textColor);

imagepng($im, $imagesDir . '/' . $filename);
imagedestroy($im);
echo "Created $filename\n";

// 2. Sealife
createPlaceholder('sealife_turtle.png', 50, 50, 34, 139, 34, 'T');
createPlaceholder('sealife_shark.png', 60, 40, 128, 128, 128, 'S');
createPlaceholder('sealife_ray.png', 60, 50, 70, 130, 180, 'R');
createPlaceholder('sealife_jellyfish.png', 40, 60, 255, 105, 180, 'J');
createPlaceholder('sealife_fish1.png', 40, 40, 255, 165, 0, 'F1');

?>
