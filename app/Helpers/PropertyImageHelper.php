<?php

namespace App\Helpers;

use App\Models\Property;
use App\Utils\StoragePath;
use Illuminate\Support\Facades\Storage;

class PropertyImageHelper {

    public static function validateImage($attribute, $value, $fail, $status = null)
    {
        if (is_string($value) && preg_match('/^data:image\/(jpeg|png|jpg);base64,/', $value)) {
            $data = substr($value, strpos($value, ',') + 1);

            $decodedData = base64_decode($data);

            if ($decodedData === false) {
                $fail("The {$attribute} must be a valid base64 encoded image.");
            }

            $imageInfo = getimagesizefromstring($decodedData);
            if ($imageInfo === false) {
                $fail("The {$attribute} must be a valid image.");
            }
        } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
            $headers = @get_headers($value, 1);

            if ($headers && isset($headers['content-type']) && strpos($headers['content-type'], 'image/') !== false) {
            } else {
                $fail("The {$attribute} must be a valid image URL.");
            }
        } elseif ($value instanceof \Illuminate\Http\UploadedFile) {
            if (!$value->isValid() || !in_array($value->getClientOriginalExtension(), ['jpeg', 'jpg', 'png'])) {
                $fail("The {$attribute} must be a valid image file (jpeg, png, jpg).");
            }
        }
        // Jika bukan Base64, URL, maupun file
        else {
            $fail("The {$attribute} must be a valid base64 image, URL, or uploaded image file.");
        }
    }

    public static function convertImageUrlToBase64($imageUrl)
    {
        if (filter_var($imageUrl, FILTER_VALIDATE_URL) === FALSE) {
            throw new \Exception('Invalid URL');
        }

        $imageContent = file_get_contents($imageUrl);

        if ($imageContent === FALSE) {
            throw new \Exception('Unable to fetch image from URL');
        }

        $imageInfo = getimagesizefromstring($imageContent);
        if ($imageInfo === FALSE) {
            throw new \Exception('Unable to get image info');
        }

        $mimeType = $imageInfo['mime'];

        $base64 = base64_encode($imageContent);

        return "data:{$mimeType};base64,{$base64}";
    }

    public static function base64ToImage($base64, $path)
    {
        $mimeType = explode(';', explode(':', $base64)[1])[0];
        $validMimeTypes = ['image/jpeg', 'image/png'];
        $validExtensions = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

        if (!in_array($mimeType, $validMimeTypes)) {
            throw new \Exception('Invalid image type');
        }

        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $imageData = str_replace(' ', '+', $imageData);
        $image = base64_decode($imageData);

        $extension = $validExtensions[$mimeType];
        $fileName = $path . '.' . $extension;

        Storage::put($fileName, $image);
//        $uploads_path = storage_path('app/public/uploads');
//        $this->setPermissions($uploads_path);

        return $extension;
    }

    public static function getExtension($image, $path)
    {
        if (filter_var($image, FILTER_VALIDATE_URL)) {
            $url_parts = parse_url($image);
            $extension = pathinfo($url_parts['path'], PATHINFO_EXTENSION);
            $image_name_with_extension = $image . '.' . $extension;

            return self::base64ToImage(self::convertImageUrlToBase64($image), $path);
        } else {
            return self::base64ToImage($image, $path);
        }
    }

    public static function saveBase64Image(Property $property, $image_str, $file_name = null)
    {
        $property_path = StoragePath::propertyPath($property_id);

        $extension = self::getExtension($image_str, $property_path);

        $image_property->bangunan_depan = $image_rumah_depan_name . '.' . $extension;
    }

}
