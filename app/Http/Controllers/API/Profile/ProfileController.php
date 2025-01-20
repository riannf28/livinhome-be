<?php

namespace App\Http\Controllers\API\Profile;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction\Transaction;
use App\Utils\StoragePath;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index()
    {
        try {

            $transaction_history = array();

            $user = Auth::user();
            $user->id_card = Storage::url($user->id_card);
            $user->photo_profile = Storage::url($user->photo_profile);

            $transaction = Transaction::where('fullname', $user->fullname)->get();

            if (empty($transaction[0])) {
                $transaction = Transaction::where('phone_number', $user->phone_number)->get();
            }
            if (empty($transaction[0])) {
                $transaction_history = null;
            } else {
                foreach ($transaction as $item) {
                    $transaction_history['property_name'] = $item->property[0]->nama;
                    $transaction_history['owner_property'] = $item->property[0]->user[0]->fullname;
                    $transaction_history['transaction_id'] = $item->id;
                    $transaction_history['property_id'] = $item->property_id;
                    $transaction_history['status'] = $item->status;
                }
            }
            $user['transaction_history'] = $transaction_history;

            return ResponseFormatter::success($user);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required',
            'gender' => 'required|in:1,0',
            'date_of_birth' => 'required',
            'phone_number' => 'required',
            'job' => 'nullable',
            'school_name' => 'nullable',
            'city' => 'nullable',
            'status' => 'nullable',
            'last_education' => 'nullable',
            'emergency_contact' => 'nullable',
            'photo_profile' => 'nullable|image|mimes:jpeg,png,jpg',
            'id_card' => 'nullable|image|mimes:jpeg,png,jpg',
        ], [
            'fullname.required' => 'Nama lengkap harus diisi.',
            'gender.required' => 'Jenis kelamin harus diisi.',
            'gender.in' => 'Jenis kelamin harus berupa 1 (Pria) atau 0 (Wanita).',
            'date_of_birth.required' => 'Tanggal lahir harus diisi.',
            // 'date_of_birth.date' => 'Tanggal lahir harus dalam format yang benar.',
            'phone_number.required' => 'Nomor telepon harus diisi.',
            'job.nullable' => 'Pekerjaan bersifat opsional.',
            'school_name.nullable' => 'Nama sekolah bersifat opsional.',
            'city.nullable' => 'Kota bersifat opsional.',
            'status.nullable' => 'Status bersifat opsional.',
            'last_education.nullable' => 'Pendidikan terakhir bersifat opsional.',
            'emergency_contact.nullable' => 'Kontak darurat bersifat opsional.',
            'photo_profile.nullable' => 'Foto profil bersifat opsional.',
            'photo_profile.image' => 'Foto profil yang diunggah harus berupa gambar.',
            'photo_profile.mimes' => 'Foto profil harus memiliki format: jpeg, png, atau jpg.',
            'id_card.nullable' => 'Kartu identitas bersifat opsional.',
            'id_card.image' => 'Kartu identitas yang diunggah harus berupa gambar.',
            'id_card.mimes' => 'Kartu identitas harus memiliki format: jpeg, png, atau jpg.',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        try {
            $user = Auth::user();
            $user->fill($request->all());
            $user->phone_number = ResponseFormatter::convertPhoneNumber($request->phone_number);
            $user->date_of_birth = ResponseFormatter::timestampToDate($request->date_of_birth);
            if ($request->hasFile('photo_profile')) {
                $photo_profile = $request->file('photo_profile');
                $profile_path = StoragePath::userProfilePath($user->id);
                $photo_profile_extension = $photo_profile->extension();

                if (Storage::exists($user->photo_profile)) {
                    Storage::delete($user->photo_profile);
                }

                $photo_profile_path = $photo_profile
                    ->storeAs($profile_path, "photo-profile.$photo_profile_extension", 'public');

                $user->photo_profile = $photo_profile_path;
            }

            if ($request->hasFile('id_card')) {
                $id_card = $request->file('id_card');
                $profile_path = StoragePath::userProfilePath($user->id);
                $id_card_extension = $id_card->extension();

                if (Storage::exists($user->id_card)) {
                    Storage::delete($user->id_card);
                }

                $id_card_path = $id_card->storeAs($profile_path, "ktp.$id_card_extension", 'public');

                $user->id_card = $id_card_path;
            }
            $user->save();

            if (!empty($photo_profile_path)) {
                $user->photo_profile_url = Storage::url($user->photo_profile);
            } else {
                $user->photo_profile_url = null;
            }

            if (!empty($id_card_path)) {
                $user->id_card_url = Storage::url($user->id_card);
            } else {
                $user->id_card_url = null;
            }

            $uploads_path = storage_path('app/public/uploads');
            $this->setPermissions($uploads_path);

            return ResponseFormatter::success($user);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    function setPermissions($dir, $folder_permission = 0755, $file_permission = 0644)
    {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            $path = $dir . '/' . $item;

            if (is_dir($path)) {
                chmod($path, $folder_permission);
                $this->setPermissions($path, $folder_permission, $file_permission);
            } else {
                chmod($path, $file_permission);
            }
        }
    }
}
