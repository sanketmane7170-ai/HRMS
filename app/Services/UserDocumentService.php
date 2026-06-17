<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDocument;
use App\Traits\File;
use Exception;
use Illuminate\Http\Request;

class UserDocumentService
{
    use File;

    /**
     * Added a user document record in the storage
     */
    public function addDocument(Request $request, User $user): UserDocument|Exception
    {
        try {
            if(isset($request->file)){
                $folderPath = "uploads/temp";
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0777, true);
                }
            }
            $document =   $user->documents()->create([
                'type' => $request->type,
                'expiry_date' => $request->expiry_date,
                'serial_number' => $request->serial_number,
                'original_name' => isset($request->file) ? $request->file->getClientOriginalName() : __trans($request->type),
                // 'path' => $this->upload($request->file, "uploads/users/$user->id/documents") // Wrong path generate error in upload document | Gagan 02-08-2023
                'path' => isset($request->file) ? $this->upload($request->file, "uploads/temp") : '/uploads/default-document.jpeg',
                'issue_date' => $request->issue_date,
                'place_of_issue' => $request->place_of_issue,
                'country_name' => $request->country_name,
                'ministry_of_labor_personal_no' => $request->ministry_of_labor_personal_no,
                'note' => $request->note,

            ]);
           

            return $document;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Delete the user document and unlink the file
     */
    public function destroy(UserDocument $userDocument): bool
    {
        if ($userDocument->path != '/uploads/default-document.jpeg' || $userDocument->path != 'uploads/default-document.jpeg') {
            @unlink(public_path($userDocument->path));
            $userDocument->delete();
        }

        return true;
    }

    /**
     * Update a user document record in the storage
     */
    public function updateDocument(Request $request, UserDocument $userDocument): bool
    {
        try {

            $data = [
                'type' => $request->type,
                'expiry_date' => $request->expiry_date,
                'serial_number' => $request->serial_number,
                // 'path' => $this->upload($request->file, "uploads/users/$user->id/documents") // Wrong path generate error in upload document | Gagan 02-08-2023
                //'path' => isset($request->file) ? $this->upload($request->file, "uploads/temp") : '/uploads/default-document.jpeg',
                'issue_date' => $request->issue_date,
                'place_of_issue' => $request->place_of_issue,
                'country_name' => $request->country_name,
                'ministry_of_labor_personal_no' => $request->ministry_of_labor_personal_no,
                'note' => $request->note,
            ];
            if ($request->file) {
                if(isset($request->file)){
                    $folderPath = "uploads/temp";
                    if (!file_exists($folderPath)) {
                        mkdir($folderPath, 0777, true);
                    }
                }
                $data['path'] = isset($request->file) ? $this->upload($request->file, "uploads/temp") : '/uploads/default-document.jpeg';
                $data['original_name'] = isset($request->file) ? $request->file->getClientOriginalName() : __trans($request->type);
            }

            $document = $userDocument->update($data);
            return $document;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
