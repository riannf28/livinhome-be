<?php

namespace App\Http\Controllers\API\Profile;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Utils\StoragePath;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileOwnerController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $property = Property::where('user_id', $user->id)->first();
            if (!empty($property)) {
                $user->bank = $property->bank;
                $user->rekening = $property->rekening;
            } else {
                $user->bank = null;
                $user->rekening = null;
            }

            $user->id_card = Storage::url($user->id_card);
            $user->photo_profile = Storage::url($user->photo_profile);

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
            'email' => 'required|email:rfc,dns',
            'phone_number' => 'required',
            'bank' => 'required|in:bca,bni,bri,bsi,mandiri',
            'rekening' => 'required',
            'date_of_birth' => 'required',
            'job' => 'nullable',
            'school_name' => 'nullable',
            'city' => 'nullable',
            'status' => 'nullable',
            'last_education' => 'nullable',
            'emergency_contact' => 'nullable',
        ], [
            'fullname.required' => 'Nama lengkap harus diisi.',
            'fullname.exists' => 'Nama lengkap yang dipilih tidak ada dalam catatan kami.',
            'gender.required' => 'Jenis kelamin harus diisi.',
            'gender.in' => 'Jenis kelamin harus salah satu dari: laki-laki atau perempuan.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Silakan masukkan alamat email yang valid.',
            'email.email:rfc,dns' => 'Alamat email harus valid sesuai standar RFC dan DNS.',
            'phone_number.required' => 'Nomor telepon harus diisi.',
            'bank.required' => 'Pemilihan bank harus diisi.',
            'bank.in' => 'Bank harus salah satu dari: bca, bni, bri, bsi, mandiri.',
            'rekening.required' => 'Nomor rekening bank harus diisi.',
            'school_name.required' => 'Nama sekolah wajib diisi.',
            'city.required' => 'Kota wajib diisi.',
            'status.required' => 'Status wajib diisi.',
            'last_education.required' => 'Pendidikan terakhir wajib diisi.',
            'emergency_contact.required' => 'Kontak darurat wajib diisi.',
            'date_of_birth.required' => 'Tanggal lahir wajib diisi.',
            'job.required' => 'Pekerjaan wajib diisi.',
        ]);
        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        try {
            $user = Auth::user();
            $user->fullname = $request->fullname;
            $user->gender = $request->gender;
            $user->email = $request->email;
            $user->phone_number = ResponseFormatter::convertPhoneNumber($request->phone_number);
            $user->date_of_birth = ResponseFormatter::timestampToDate($request->date_of_birth);
            $user->school_name = $request->school_name;
            $user->job = $request->job;
            $user->city = $request->city;
            $user->status = $request->status;
            $user->last_education = $request->last_education;
            $user->emergency_contact = $request->emergency_contact;
            $user->save();

            Property::where('user_id', $user->id)->update([
                'bank' => $request->bank,
                'rekening' => $request->rekening
            ]);

            return ResponseFormatter::success();
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function update_image(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => ['file', 'required', 'mimes:jpeg,jpg,png']
        ], [
            'image.required' => 'Foto profil harus diunggah',
            'image.mimes' => 'Foto profil hanya menerima gambar dengan format .jpeg, .jpg atau .png'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        $user = Auth::user();

        $profile_img = $request->file('image');
        $profile_img_extension = $profile_img->extension();

        $profile_path = StoragePath::userProfilePath($user->id);

        $profile_img_path = $profile_img->storeAs($profile_path, "photo-profile.$profile_img_extension", 'public');

        $user->photo_profile = $profile_img_path;
        $user->save();

        $photo_profile_url = Storage::url($user->photo_profile);

        return ResponseFormatter::success($photo_profile_url);
    }

    public function update_id_card(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id_card' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        $this->validateImage($attribute, $value, $fail);
                    },
                ],
            ],
            [
                'id_card.required' => 'Foto harus diunggah.',
                'id_card.string' => 'Format foto tidak valid.',
            ]
        );

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        $user = Auth::user();

        $ktp_img = $request->file('id_card');
        $ktp_img_extension = $ktp_img->extension();

        $profile_path = StoragePath::userProfilePath($user->id);

        $ktp_img_path = $ktp_img->storeAs($profile_path, "ktp.$ktp_img_extension", 'public');

        $user->id_card = $ktp_img_path;
        $user->save();

        $ktp_url = Storage::url($ktp_img_path);

        return ResponseFormatter::success($ktp_url);
    }

    public function validateImage($attribute, $value, $fail, $status = null)
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
        } else {
            $fail("The {$attribute} must be a valid base64 image, URL, or uploaded image file.");
        }
    }

    public function check_image($image, $path)
    {
        if (filter_var($image, FILTER_VALIDATE_URL)) {
            $url_parts = parse_url($image);
            $extension = pathinfo($url_parts['path'], PATHINFO_EXTENSION);
            $image_name_with_extension = $image . '.' . $extension;

            return $this->base64ToImage($this->convertImageUrlToBase64($image), $path);
        } else {
            return $this->base64ToImage($image, $path);
        }

        return $extension;
    }

    public function base64ToImage($base64, $path)
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
        $uploads_path = storage_path('app/public/uploads');
        $this->setPermissions($uploads_path);

        return $extension;
    }

    function convertImageUrlToBase64($imageUrl)
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
