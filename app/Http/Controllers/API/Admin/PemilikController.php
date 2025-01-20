<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\BedroomFacilityProperty;
use App\Models\FacilityProperty;
use App\Models\ImageBathroomProperty;
use App\Models\ImageBuildProperty;
use App\Models\ListRules;
use App\Models\Property;
use App\Models\RuleProperty;
use App\Models\Transaction\Transaction;
use App\Utils\StoragePath;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PemilikController extends Controller
{
    public function index($type)
    {
        try {
            $data = Property::with(['user', 'image_property', 'bedroom_facility', 'image_bathroom'])
                ->where('kategori', $type)
                ->get();
            foreach ($data as $item) {
                $check_status_transaction = Transaction::where('property_id', $item->id)->first();
                $check_status_transaction = $check_status_transaction == null ? 'Tersedia' : 'Tidak Tesedia';
                $item['status'] = $check_status_transaction;
            }
            return ResponseFormatter::success($data);
        } catch (Exception $error) {
            return ResponseFormatter::success($error->getMessage(), 'Error');
        }
    }

    public function detail($id)
    {
        try {
            $property = Property::where('id', $id)->first();

            if (!$property) {
                return ResponseFormatter::error(null, 'Data properti tidak ditemukan.', 404);
            }

            $images = array();

            $images[] = $property->image_property->image_bangunan_depan_url();
            $images[] = $property->image_property->image_depan_url();
            $images[] = $property->image_property->image_dalam_url();

            $images[] = $property->bedroom_facility->image_url();

            $images[] = $property->image_bathroom->image_url();

            $property->image = $images;

            $property->transaction_success = Transaction::where('property_id', $id)
                ->where('status', true)
                ->count();
            $property->user[0]->pemilik_property = Property::where('user_id', $property->user_id)->count();

            $rules_data = RuleProperty::where('property_id', $id)->get();
            $rules = array();

            foreach ($rules_data as $item) {
                $list_rule = ListRules::where('id', $item->rule_id)->first();

                $list_rule_array = $list_rule->toArray();
                unset($list_rule_array['updated_at']);
                unset($list_rule_array['deleted_at']);
                unset($list_rule_array['created_at']);

                $rules[] = $list_rule_array;
            }
            $property->rules = $rules;


            return ResponseFormatter::success($property);
        } catch (Exception $error) {
            return ResponseFormatter::success($error->getMessage(), 'Error');
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'nama' => 'required',
            'deskripsi' => 'required',
            'kategori' => 'required|in:kost,kontrakan,apartment',
            'tanggal_dibuat' => 'required',
            'tanggal_mulai_sewa' => 'required',
            'sewa_untuk' => 'required|in:Pria,Wanita,Keduanya',
            'latitude' => 'required',
            'longitude' => 'required',
            'provinsi' => 'required',
            'kecamatan' => 'required',
            'alamat' => 'required',
            'catatan_alamat' => 'nullable',
            'fasilitas' => 'required|in:kosongan,furnished,semi-furnished',
            'fasilitas_lain.*' => 'nullable|exists:list_facilities,id',
            'lebar_tanah' => 'required',
            'daya_listrik' => 'required',
            'kamar_mandi' => 'required',
            'sumber_air' => 'required',
            'total_kamar' => 'required',
            'total_lemari' => 'required',
            'minimum_sewa' => 'required|in:1,3,12',
            'meja' => 'required',
            'kasur' => 'required',
            'harga_sewa_tahun' => 'nullable',
            'harga_sewa_3_bulan' => 'nullable',
            'harga_sewa_1_bulan' => 'nullable',
            'bank' => 'required|in:bca,bni,bri,bsi,mandiri',
            'rekening' => 'required',
            'foto_rumah_depan' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $this->validateImage($attribute, $value, $fail);
                },
            ],
            'foto_rumah_jalan' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $this->validateImage($attribute, $value, $fail);
                },
            ],
            'foto_rumah_dalam' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $this->validateImage($attribute, $value, $fail);
                },
            ],
            'foto_kamar_tidur.*' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $this->validateImage($attribute, $value, $fail);
                },
            ],
            'foto_kamar_mandi' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $this->validateImage($attribute, $value, $fail);
                },
            ],
        ], [
            'property_id.required' => 'Property harus dipilih.',
            'property_id.exists' => 'Property tidak tersedia.',
            'nama.required' => 'Nama harus diisi.',
            'deskripsi.required' => 'Deskripsi harus diisi.',
            'kategori.in' => 'Kategori hanya bisa terisi kost, kontrakan, apartment.',
            'tanggal_dibuat.required' => 'Tanggal dibuat harus diisi.',
            'tanggal_mulai_sewa.required' => 'Tanggal mulai sewa harus diisi.',
            'sewa_untuk.required' => 'Sewa untuk harus diisi.',
            'sewa_untuk.in' => 'Sewa untuk harus salah satu dari: Pria, Wanita, atau Keduanya.',
            'latitude.required' => 'Latitude harus diisi.',
            'longitude.required' => 'Longitude harus diisi.',
            'provinsi.required' => 'Provinsi harus diisi.',
            'kecamatan.required' => 'Kecamatan harus diisi.',
            'alamat.required' => 'Alamat harus diisi.',
            'fasilitas.required' => 'Fasilitas harus diisi.',
            'fasilitas.in' => 'Fasilitas harus salah satu dari: kosongan, furnished, atau semi-furnished.',
            'fasilitas_lain.*.exists' => 'Fasilitas lain yang dipilih tidak valid.',
            'lebar_tanah.required' => 'Lebar tanah harus diisi.',
            'daya_listrik.required' => 'Daya listrik harus diisi.',
            'kamar_mandi.required' => 'Kamar Mandi harus diisi.',
            'sumber_air.required' => 'Sumber air harus diisi.',
            'total_kamar.required' => 'Total kamar harus diisi.',
            'total_lemari.required' => 'Total lemari harus diisi.',
            'minimum_sewa.required' => 'Minimum sewa harus diisi.',
            'minimum_sewa.in' => 'Minimum sewa harus salah satu dari 1 bulan, 3 bulan, atau 1 tahun.',
            'meja.required' => 'Meja harus diisi.',
            'kasur.required' => 'Kasur harus diisi.',
            'bank.required' => 'Bank harus diisi.',
            'bank.in' => 'Bank harus salah satu dari: bca, bni, bri, bsi, mandiri.',
            'rekening.required' => 'Rekening harus diisi.',
            'foto_rumah_depan.required' => 'Foto rumah bagian depan harus diunggah.',
            'foto_rumah_depan.string' => 'Format foto rumah depan tidak valid.',
            'foto_rumah_jalan.required' => 'Foto rumah bagian jalan harus diunggah.',
            'foto_rumah_jalan.string' => 'Format foto rumah depan tidak valid.',
            'foto_rumah_dalam.required' => 'Foto rumah bagian dalam harus diunggah.',
            'foto_rumah_dalam.string' => 'Format foto rumah depan tidak valid.',
            'foto_kamar_tidur.*.required' => 'Setiap foto kamar tidur harus diunggah.',
            'foto_kamar_tidur.*.string' => 'Format foto rumah depan tidak valid.',
            'foto_kamar_mandi.required' => 'Setiap foto kamar mandi harus diunggah.',
            'foto_kamar_mandi.string' => 'Format foto rumah depan tidak valid.',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }
        try {
            $data = Property::findOrFail($request->property_id);
            $data->fill($request->all());
            $data->tanggal_dibuat = ResponseFormatter::timestampToDate($request->tanggal_dibuat);
            $data->tanggal_mulai_sewa = ResponseFormatter::timestampToDate($request->tanggal_mulai_sewa);
            $data->user_id = Auth::user()->id;
            $data->save();

            $image_property = ImageBuildProperty::where('property_id', $data->id)->first();
            if (isset($image_property)) {
                $path_image_bangunan_depan = "/public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/{$image_property->bangunan_depan}";
                $path_image_depan = "/public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/{$image_property->depan}";
                $path_image_dalam = "/public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/{$image_property->dalam}";
            } else {
                $image_property = new ImageBuildProperty();
                $image_property->property_id = $data->id;
                $path_image_bangunan_depan = null;
                $path_image_depan = null;
                $path_image_dalam = null;
            }

            if ($request->has('foto_rumah_depan')) {
                $image_rumah_depan = $request->input('foto_rumah_depan');
                $image_rumah_depan_name = time() . '-' . $request->name . '-rumah-depan';
                $path = "public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/" . $image_rumah_depan_name;
                $extension = $this->check_image($image_rumah_depan, $path);
                $image_property->bangunan_depan = $image_rumah_depan_name . '.' . $extension;
            }


            if ($request->has('foto_rumah_jalan')) {
                $image_rumah_jalan = $request->input('foto_rumah_jalan');
                $image_rumah_jalan_name = time() . '-' . $request->name . '-rumah-jalan';
                $path = "public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/" . $image_rumah_jalan_name;
                $extension = $this->check_image($image_rumah_jalan, $path);
                $image_property->depan = $image_rumah_jalan_name . '.' . $extension;
            }

            if ($request->has('foto_rumah_dalam')) {
                $image_rumah_dalam = $request->input('foto_rumah_dalam');
                $image_rumah_dalam_name = time() . '-' . $request->name . '-rumah-dalam';
                $path = "public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/" . $image_rumah_dalam_name;
                $extension = $this->check_image($image_rumah_dalam, $path);
                $image_property->dalam = $image_rumah_dalam_name . '.' . $extension;
            }

            $image_property->save();

            if ($request->has('foto_kamar_tidur')) {
                $image_bedroom_count = count($request->input('foto_kamar_tidur'));
                for ($i = 0; $i < $image_bedroom_count; $i++) {
                    $image = $request->input('foto_kamar_tidur')[$i];
                    $image_name = time() . '-' . $request->name . '-kamar-tidur';
                    $path = "public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/kamar-tidur/" . $image_name;
                    $extension = $this->check_image($image, $path);
                    $image_bedroom = new BedroomFacilityProperty();
                    $image_bedroom->property_id = $data->id;
                    $image_bedroom->image = $image_name . '.' . $extension;
                    $image_bedroom->save();
                }
            }

            if ($request->has('foto_kamar_mandi')) {
                $image = $request->input('foto_kamar_mandi');
                $image_name = time() . '-' . $request->name . '-kamar-mandi';
                $path = "public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/kamar-mandi/" . $image_name;
                $extension = $this->check_image($image, $path);
                $image_bathroom = new ImageBathroomProperty();
                $image_bathroom->property_id = $data->id;
                $image_bathroom->image = $image_name . '.' . $extension;
                $image_bathroom->save();
            }

            FacilityProperty::where('property_id', $data->id)->delete();

            $other_facility_count = count($request->fasilitas_lain);

            for ($i = 0; $i < $other_facility_count; $i++) {
                $other_facility = new FacilityProperty();
                $other_facility->property_id = $data->id;
                $other_facility->facility_id = $request->fasilitas_lain[$i];
                $other_facility->save();
            }

            RuleProperty::where('property_id', $data->id)->delete();

            $rules_property_count = count($request->rules);

            for ($i = 0; $i < $rules_property_count; $i++) {
                $rule_property = new RuleProperty();
                $rule_property->property_id = $data->id;
                $rule_property->rule_id = $request->rules[$i];
                $rule_property->save();
            }

            if ($path_image_bangunan_depan != null && $path_image_dalam != null && $path_image_depan != null) {
                Storage::delete($path_image_bangunan_depan);
                Storage::delete($path_image_dalam);
                Storage::delete($path_image_depan);
            }
            return ResponseFormatter::success($data);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
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
        }
        // Jika bukan Base64, URL, maupun file
        else {
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

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id'
        ], [
            'property_id.required' => 'ID properti wajib diisi.',
            'property_id.exists' => 'ID properti tidak ditemukan di database.'
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->messages()->all());
        }

        try {
            $data = Property::where('id', $request->property_id)->first();

            $image_property = ImageBuildProperty::where('property_id', $data->id)->first();
            $image_bedroom = BedroomFacilityProperty::where('property_id', $data->id)->first();
            $image_bathroom = ImageBathroomProperty::where('property_id', $data->id)->first();
            if (isset($image_property)) {
                $path_image_bangunan_depan = "/public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/{$image_property->bangunan_depan}";
                $path_image_depan = "/public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/{$image_property->depan}";
                $path_image_dalam = "/public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/{$image_property->dalam}";
            }
            if (isset($image_bedroom)) {
                $path_kamar_tidur = "public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/kamar-tidur/{$image_bedroom->image}";
                Storage::delete($path_kamar_tidur);
            }

            if (isset($image_bathroom)) {
                $path_kamar_mandi = "public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/kamar-mandi/{$image_bathroom->image}";
                Storage::delete($path_kamar_mandi);
            }


            if ($path_image_bangunan_depan != null && $path_image_dalam != null && $path_image_depan != null) {
                Storage::delete($path_image_bangunan_depan);
                Storage::delete($path_image_dalam);
                Storage::delete($path_image_depan);
            }

            $data->delete();

            return ResponseFormatter::success();
        } catch (Exception $error) {
            return ResponseFormatter::success($error->getMessage(), 'Error');
        }
    }
}
