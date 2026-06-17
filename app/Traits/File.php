<?php

namespace App\Traits;

use Illuminate\Support\Facades\File as FacadesFile;
use Illuminate\Support\Str;

trait File
{

    /**
     * Upload give file to particular path
     * @param $file
     * @param $uploadPath
     * @param $oldFilePath
     * @param $fileName
     *
     * @return string $path
     */
    public function upload($file, $uploadPath, $oldFilePath = null, $fileName = null)
    {
        $path = rtrim($uploadPath, '/');
        $this->checkIfPathExists($uploadPath);
        if (!$fileName) {
            $fileName = $file->hashName();
        }
        if ($oldFilePath) {
            @unlink(public_path($oldFilePath));
        }
        $file->move(public_path($uploadPath), $fileName);

        return Str::finish($uploadPath,  '/')  . $fileName;
    }

    /**
     * Upload Base64 File  to particular path
     * @param $file
     * @param $uploadPath
     *
     * @return string $path
     */
    public function uploadBase64($base64Image, $path)
    {
        $path = rtrim($path, '/');
        $this->checkIfPathExists($path);
        $image_parts = explode(";base64,", $base64Image);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $imageName = Str::random(32) . '.' . $image_type;
        $file = public_path($path . '/' . $imageName);
        @file_put_contents($file, $image_base64);

        return $path . '/' . $imageName;
    }

    /**
     * Check if folder exists or not
     * @param  string $path
     * @return string $path
     */
    public function checkIfPathExists($path)
    {
        $absolutePath = public_path($path);
        if (!FacadesFile::isDirectory($absolutePath)) {
            FacadesFile::makeDirectory($absolutePath, 0777);
        }

        return $absolutePath;
    }
}
