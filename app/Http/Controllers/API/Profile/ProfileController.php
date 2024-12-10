<?php

namespace App\Http\Controllers\API\Profile;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction\Transaction;
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

            $data = Auth::user();
            $data->id_card = asset("uploads/ktp-person/{$data->fullname}/" . $data->id_card);
            $data->photo_profile = asset("uploads/photo-profile/{$data->fullname}/{$data->photo_profile}");

            $transaction = Transaction::where('fullname', $data->fullname)->get();

            if (empty($transaction[0])) {
                $transaction = Transaction::where('phone_number', $data->phone_number)->get();
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
            $data['transaction_history'] = $transaction_history;

            return ResponseFormatter::success($data);
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

        // return response()->json($request->all());

        try {
            $user = Auth::user();
            $user->fill($request->all());
            $user->phone_number = ResponseFormatter::convertPhoneNumber($request->phone_number);
            $user->date_of_birth = ResponseFormatter::timestampToDate($request->date_of_birth);
            if ($request->hasFile('photo_profile')) {
                $photo_profile = $request->file('photo_profile');
                $photo_profile_name = time() . '-' . $user->fullname . '.' . $photo_profile->getClientOriginalExtension();

                $existingPhotoPathPhotoProfile = "public/uploads/photo-profile/{$user->fullname}/{$user->photo_profile}";
                if (Storage::exists($existingPhotoPathPhotoProfile)) {
                    Storage::delete($existingPhotoPathPhotoProfile);
                }
                $user->photo_profile = $photo_profile_name;

                Storage::putFileAs("public/uploads/photo-profile/{$user->fullname}", $photo_profile, $photo_profile_name);
            }

            if ($request->hasFile('id_card')) {
                $id_card = $request->file('id_card');
                $id_card_name = time() . '-' . $user->fullname . '.' . $id_card->getClientOriginalExtension();

                $existingPhotoPathIdCard = "public/uploads/ktp-person/{$user->fullname}/{$user->id_card}";
                if (Storage::exists($existingPhotoPathIdCard)) {
                    Storage::delete($existingPhotoPathIdCard);
                }

                Storage::putFileAs("public/uploads/ktp-person/{$user->fullname}", $id_card, $id_card_name);
                $user->id_card = $id_card_name;
            }
            $user->save();

            if (!empty($photo_profile_name)) {
                $user->photo_profile_url = asset("uploads/photo-profile/{$user->fullname}/{$photo_profile_name}");
            } else {
                $user->photo_profile_url = null;
            }

            if (!empty($photo_profile_name)) {
                $user->id_card_url = asset("uploads/ktp-person/{$user->fullname}/{$id_card_name}");
            } else {
                $user->id_card_url = null;
            }

            $uploads_path = storage_path('app/public/uploads');
            $this->setPermissions($uploads_path);
            // return response()->json($user);

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
