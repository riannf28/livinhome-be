<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\BedroomFacilityProperty;
use App\Models\FacilityProperty;
use App\Models\ImageBathroomProperty;
use App\Models\ImageBuildProperty;
use App\Models\ListFacility;
use App\Models\ListRules;
use App\Models\Property;
use App\Models\RatingProperty;
use App\Models\RuleProperty;
use App\Models\Transaction\Transaction;
use App\Utils\StoragePath;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    public function get_rules()
    {
        $data = ListRules::all();

        return ResponseFormatter::success($data, 'success');
    }

    public function get_facilites()
    {
        $data = ListFacility::all();

        return ResponseFormatter::success($data, 'success');
    }

    public function register_property(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
            'kamar_mandi' => 'required',
            'daya_listrik' => 'required',
            'sumber_air' => 'required|in:Sumur,PDAM',
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
            'nama.required' => 'Nama harus diisi.',
            'deskripsi.required' => 'Deskripsi harus diisi.',
            'kategori.in' => 'Kategori hanya bisa terisi kost, kontrakan, apartment.',
            'tanggal_dibuat.required' => 'Tanggal dibuat harus diisi.',
            // 'tanggal_dibuat.date' => 'Tanggal dibuat harus dalam format yang valid.',
            'tanggal_mulai_sewa.required' => 'Tanggal mulai sewa harus diisi.',
            // 'tanggal_mulai_sewa.date' => 'Tanggal mulai sewa harus dalam format yang valid.',
            'sewa_untuk.required' => 'Sewa untuk harus diisi.',
            'sewa_untuk.in' => 'Sewa untuk harus salah satu dari: Pria, Wanita, atau Keduanya.',
            'latitude.required' => 'Silakan posisikan pin di peta pada halaman pertama terlebih dahulu.',
            'longitude.required' => 'Silakan posisikan pin di peta pada halaman pertama terlebih dahulu.',
            'provinsi.required' => 'Provinsi harus diisi.',
            'kecamatan.required' => 'Kecamatan harus diisi.',
            'alamat.required' => 'Alamat harus diisi.',
            'fasilitas.required' => 'Fasilitas harus diisi.',
            'fasilitas.in' => 'Fasilitas harus salah satu dari: kosongan, furnished, atau semi-furnished.',
            'fasilitas_lain.*.exists' => 'Fasilitas lain yang dipilih tidak valid.',
            'lebar_tanah.required' => 'Lebar tanah harus diisi.',
            'kamar_mandi.required' => 'Kamar Mandi harus diisi.',
            'daya_listrik.required' => 'Daya listrik harus diisi.',
            'sumber_air.required' => 'Sumber air harus diisi.',
            'sumber_air.in' => 'Sumber air hanya bisa Sumur atau PDAM.',
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

        // return response()->json($request->all());

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        try {

            // $request->meja == 'true' ? true : false;
            // $request->kasur == 'true' ? true : false;

            // return ResponseFormatter::success($request->all());
            $property = new Property();
            $property->fill($request->all());
            $property->tanggal_dibuat = ResponseFormatter::timestampToDate($request->tanggal_dibuat);
            $property->tanggal_mulai_sewa = ResponseFormatter::timestampToDate($request->tanggal_mulai_sewa);
            $property->user_id = Auth::user()->id;
            $property->save();

            // $bedroom_facility = new BedroomFacilityProperty();

            $image_property = new ImageBuildProperty();
            $image_property->property_id = $property->id;

            if ($request->has('foto_rumah_depan')) {
                $image_rumah_depan = $request->input('foto_rumah_depan');
                $image_rumah_depan_name = time() . '-' . $request->name . '-rumah-depan';
                $path = StoragePath::propertyPath($property->id) . "/$image_rumah_depan_name";
                $extension = $this->check_image($image_rumah_depan, $path);
                $image_property->bangunan_depan = $image_rumah_depan_name . '.' . $extension;
            }

            if ($request->has('foto_rumah_jalan')) {
                $image_rumah_jalan = $request->input('foto_rumah_jalan');
                $image_rumah_jalan_name = time() . '-' . $request->name . '-rumah-jalan';
//                $path = "public/uploads/properties/{$property->user[0]->fullname}/{$property->nama}/" . $image_rumah_jalan_name;
                $path = StoragePath::propertyPath($property->id) . "/$image_rumah_jalan_name";
                $extension = $this->check_image($image_rumah_jalan, $path);
                $image_property->depan = $image_rumah_jalan_name . '.' . $extension;
            }

            if ($request->has('foto_rumah_dalam')) {
                $image_rumah_dalam = $request->input('foto_rumah_dalam');
                $image_rumah_dalam_name = time() . '-' . $request->name . '-rumah-dalam';
//                $path = "public/uploads/properties/{$property->user[0]->fullname}/{$property->nama}/" . $image_rumah_dalam_name;
                $path = StoragePath::propertyPath($property->id) . "/$image_rumah_dalam_name";
                $extension = $this->check_image($image_rumah_dalam, $path);
                $image_property->dalam = $image_rumah_dalam_name . '.' . $extension;
            }

            $image_property->save();

            if ($request->has('foto_kamar_tidur')) {
                $image_bedroom_count = count($request->input('foto_kamar_tidur'));
                for ($i = 0; $i < $image_bedroom_count; $i++) {
                    $image = $request->input('foto_kamar_tidur')[$i];
                    $image_name = time() . '-' . $request->name . '-kamar-tidur';
//                    $path = "public/uploads/properties/{$property->user[0]->fullname}/{$property->nama}/kamar-tidur/" . $image_name;
                    $path = StoragePath::propertyPath($property->id) . "/kamar-tidur/$image_name";
                    $extension = $this->check_image($image, $path);
                    $image_bedroom = new BedroomFacilityProperty();
                    $image_bedroom->property_id = $property->id;
                    $image_bedroom->image = $image_name . '.' . $extension;
                    $image_bedroom->save();
                }
            }

            if ($request->has('foto_kamar_mandi')) {
                $image = $request->input('foto_kamar_mandi');
                $image_name = time() . '-' . $request->name . '-kamar-mandi';
//                $path = "public/uploads/properties/{$property->user[0]->fullname}/{$property->nama}/kamar-mandi/" . $image_name;
                $path = StoragePath::propertyPath($property->id) . "/kamar-mandi/$image_name";
                $extension = $this->check_image($image, $path);
                $image_bathroom = new ImageBathroomProperty();
                $image_bathroom->property_id = $property->id;
                $image_bathroom->image = $image_name . '.' . $extension;
                $image_bathroom->save();
            }

            if ($request->has('fasilitas_lain')) {
                $other_facility_count = count($request->fasilitas_lain);
                for ($i = 0; $i < $other_facility_count; $i++) {
                    $other_facility = new FacilityProperty();
                    $other_facility->property_id = $property->id;
                    $other_facility->facility_id = $request->fasilitas_lain[$i];
                    $other_facility->save();
                }
            }
            // $data = $other_facility_count;

            if ($request->has('rules')) {
                $rules_property = count($request->rules);
                for ($i = 0; $i < $rules_property; $i++) {
                    $rule_property = new RuleProperty();
                    $rule_property->property_id = $property->id;
                    $rule_property->rule_id = $request->rules[$i];
                    $rule_property->save();
                }
            }

            return ResponseFormatter::success($property);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function data_detail_property($id)
    {
        try {
            $property = Property::where('id', $id)->with('user')->first();
            $images = array();

            $image_property = ImageBuildProperty::where('property_id', $id)->get();
            foreach ($image_property as $item) {
//                $path_bangunan_depan = asset("uploads/properties/{$data->user[0]->fullname}/{$data->nama}/{$item->bangunan_depan}");
//                $path_depan = asset("uploads/properties/{$data->user[0]->fullname}/{$data->nama}/{$item->depan}");
//                $path_dalam = asset("uploads/properties/{$data->user[0]->fullname}/{$data->nama}/{$item->dalam}");

                $path_bangunan_depan = Storage::url(StoragePath::propertyPath($property->id) . "/$item->bangunan_depan");
                $path_depan = Storage::url(StoragePath::propertyPath($property->id) . "/$item->depan");
                $path_dalam = Storage::url(StoragePath::propertyPath($property->id) . "/$item->dalam");

                array_push($images, $path_bangunan_depan);
                array_push($images, $path_depan);
                array_push($images, $path_dalam);
            }

            $image_bedroom = BedroomFacilityProperty::where('id', $id)->get();
            foreach ($image_bedroom as $item) {
//                $path = asset("uploads/properties/{$property->user[0]->fullname}/{$property->nama}/kamar-mandi/{$item->image}");
                $path = Storage::url(StoragePath::propertyPath($property->id) . "/kamar-tidur/$item->image");
                array_push($images, $path);
            }

            $image_bathroom = ImageBathroomProperty::where('id', $id)->get();
            foreach ($image_bedroom as $item) {
//                $path = asset("uploads/properties/{$property->user[0]->fullname}/{$property->nama}/kamar-mandi/{$item->image}");
                $path = Storage::url(StoragePath::propertyPath($property->id) . "/kamar-mandi/$item->image");
                array_push($images, $path);
            }

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

                array_push($rules, $list_rule_array);
            }
            $property->rules = $rules;


            return $property;
        } catch (Exception $error) {
            return $error->getMessage();
        }
    }

    public function detail_property($property_id)
    {
        try {
            $data = $this->data_detail_property($property_id);

            return ResponseFormatter::success($data);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function livin_match($property_id)
    {

        try {
            $livin_match = Transaction::where('property_id', $property_id)->get();
            $result = array();
            foreach ($livin_match as $item) {
                $result_array = [
                    'fullname' => $item->fullname,
                    'phone_number' => $item->phone_number,
                    'gender' => $item->gender,
                    'job' => $item->job,
                    'school' => $item->school_name,
                ];
                array_push($result, $result_array);
            }

            $data = $this->data_detail_property($property_id);

            $results = [
                'data' => $data,
                'livin_match' => $result
            ];

            return ResponseFormatter::success($results);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function list_property()
    {
        try {
            $user = Auth::user();

            $count_transaction = Transaction::whereHas('property', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count();

            $user->total_property = Property::where('user_id', $user->id)->count();
            $user->total_transaction = $count_transaction;

            $properties = Property::where('user_id', $user->id)->get();

            foreach ($properties as $property) {
                $property->rating = RatingProperty::where('user_id', $user->id)->where('property_id', $property->id)->avg('rating');

                $image_build = ImageBuildProperty::where('property_id', $property->id)->pluck('bangunan_depan');

//                $property->image = !empty($image_build[0]) ? asset("uploads/properties/{$property->user[0]->fullname}/{$property->nama}/{$image_build[0]}") : null;
                $property_file_path = StoragePath::propertyPath($property->id);
                $property->image = !empty($image_build[0]) ? Storage::url("$property_file_path/$image_build[0]") : null;
            }

            $result_array = [
                'user' => $user,
                'properties' => $properties
            ];

            return ResponseFormatter::success($result_array);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function get_property($id)
    {
        try {
            $property = Property::where('id', $id)->with('user')->first();
            $property_image = ImageBuildProperty::where('property_id', $property->id)->first();

            $images = [];
            $images['property'] = [
//                'bagian_depan' => isset($property_image->bangunan_depan) ? Storage::url(StoragePath::propertyPath($property->id) . "/$property_image->bangunan_depan") : null,
//                'bagian_jalan' => isset($property_image->depan) ? Storage::url(StoragePath::propertyPath($property->id) . "/$property_image->depan") : null,
//                'bagian_dalam' => isset($property_image->dalam) ? Storage::url(StoragePath::propertyPath($property->id) . "/$property_image->dalam") : null,
                'bagian_depan' => $property_image->image_bangunan_depan_url(),
                'bagian_jalan' => $property_image->image_depan_url(),
                'bagian_dalam' => $property_image->image_dalam_url(),
            ];

            $property_bedroom = BedroomFacilityProperty::where('property_id', $property->id)->get();
            $images['bedroom'] = [];
            foreach ($property_bedroom as $item) {
//                $images['bedroom'][] = isset($item->image) ? (Storage::url(StoragePath::propertyPath($property->id) . "/kamar-tidur/$item->image")) : null;
                $images['bedroom'][] = $item->image_url();
            }

            $property_bathroom = ImageBathroomProperty::where('property_id', $property->id)->first();
//            $images['bathroom'] = isset($property_bathroom->image) ? Storage::url(StoragePath::propertyPath($property->id) . "/kamar-mandi/$property_bathroom->image") : null;
            $images['bathroom'] = $property_bathroom->image_url();

            $property_facility = FacilityProperty::where('property_id', $property->id)->get();
            $facility = [];
            foreach ($property_facility as $item) {
                $facility[] = ListFacility::where('id', $item->facility_id)->first()['id'];
            }

            $property_rules = RuleProperty::where('property_id', $property->id)->get();
            $rules = [];
            foreach ($property_rules as $item) {
                $rules[] = ListRules::where('id', $item->rule_id)->first()['id'];
            }

            $property->rules = $rules;
            $property->facility = $facility;
            $property->images = $images;

            return ResponseFormatter::success($property);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    // Testing
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
            'minimum_sewa' => 'in:1,3,12',
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
            $property_id = $request->get('property_id');
            $property = Property::findOrFail($property_id);
            $property->fill($request->all());
            $property->tanggal_dibuat = ResponseFormatter::timestampToDate($request->tanggal_dibuat);
            $property->tanggal_mulai_sewa = ResponseFormatter::timestampToDate($request->tanggal_mulai_sewa);
            $property->user_id = Auth::user()->id;
            $property->save();

            $image_property = ImageBuildProperty::where('property_id', $property->id)->first();
            if (isset($image_property)) {
//                $path_image_bangunan_depan = "/public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/{$image_property->bangunan_depan}";
//                $path_image_depan = "/public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/{$image_property->depan}";
//                $path_image_dalam = "/public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/{$image_property->dalam}";

//                $path_image_bangunan_depan = StoragePath::propertyPath($property_id) . "/$image_property->bangunan_depan";
//                $path_image_depan = StoragePath::propertyPath($property_id) . "/$image_property->depan";
//                $path_image_dalam = StoragePath::propertyPath($property_id) . "/$image_property->dalam";

                $path_image_bangunan_depan = $image_property->image_bangunan_depan_url();
                $path_image_depan = $image_property->image_depan_url();
                $path_image_dalam = $image_property->image_dalam_url();
            } else {
                $image_property = new ImageBuildProperty();
                $image_property->property_id = $property->id;
                $path_image_bangunan_depan = null;
                $path_image_depan = null;
                $path_image_dalam = null;
            }
            // if (!empty($image_property->bangunan_depan)) {
            //     Storage::delete("/public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/{$image_property->bangunan_depan}");
            // }

            // if (!empty($image_property->depan)) {
            //     Storage::delete("/public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/{$image_property->depan}");
            // }

            // if (!empty($image_property->dalam)) {
            //     Storage::delete("/public/uploads/properties/{$data->user[0]->fullname}/{$data->nama}/{$image_property->dalam}");
            // }

            if ($request->has('foto_rumah_depan')) {
                $image_rumah_depan = $request->input('foto_rumah_depan');
                $image_rumah_depan_name = time() . '-' . $request->name . '-rumah-depan';
//                $path = "public/uploads/properties/{$property->user[0]->fullname}/{$property->nama}/" . $image_rumah_depan_name;
                $path = StoragePath::propertyPath($property->id) . "/$image_rumah_depan_name";
                $extension = $this->check_image($image_rumah_depan, $path);
                $image_property->bangunan_depan = $image_rumah_depan_name . '.' . $extension;
            }


            if ($request->has('foto_rumah_jalan')) {
                $image_rumah_jalan = $request->input('foto_rumah_jalan');
                $image_rumah_jalan_name = time() . '-' . $request->name . '-rumah-jalan';
//                $path = "public/uploads/properties/{$property->user[0]->fullname}/{$property->nama}/" . $image_rumah_jalan_name;
                $path = StoragePath::propertyPath($property->id) . "/$image_rumah_jalan_name";
                $extension = $this->check_image($image_rumah_jalan, $path);
                $image_property->depan = $image_rumah_jalan_name . '.' . $extension;
            }

            if ($request->has('foto_rumah_dalam')) {
                $image_rumah_dalam = $request->input('foto_rumah_dalam');
                $image_rumah_dalam_name = time() . '-' . $request->name . '-rumah-dalam';
//                $path = "public/uploads/properties/{$property->user[0]->fullname}/{$property->nama}/" . $image_rumah_dalam_name;
                $path = StoragePath::propertyPath($property->id) . "/$image_rumah_dalam_name";
                $extension = $this->check_image($image_rumah_dalam, $path);
                $image_property->dalam = $image_rumah_dalam_name . '.' . $extension;
            }

            $image_property->save();

            if ($request->has('foto_kamar_tidur')) {
                $image_bedroom_count = count($request->input('foto_kamar_tidur'));
                for ($i = 0; $i < $image_bedroom_count; $i++) {
                    $image = $request->input('foto_kamar_tidur')[$i];
                    $image_name = time() . '-' . $request->name . '-kamar-tidur';
//                    $path = "public/uploads/properties/{$property->user[0]->fullname}/{$property->nama}/kamar-tidur/" . $image_name;
                    $path = StoragePath::propertyPath($property->id) . "/kamar-tidur/$image_name";
                    $extension = $this->check_image($image, $path);
                    $image_bedroom = new BedroomFacilityProperty();
                    $image_bedroom->property_id = $property->id;
                    $image_bedroom->image = $image_name . '.' . $extension;
                    $image_bedroom->save();
                }
            }

            if ($request->has('foto_kamar_mandi')) {
                $image = $request->input('foto_kamar_mandi');
                $image_name = time() . '-' . $request->name . '-kamar-mandi';
//                $path = "public/uploads/properties/{$property->user[0]->fullname}/{$property->nama}/kamar-mandi/" . $image_name;
                $path = StoragePath::propertyPath($property->id) . "/kamar-mandi/$image_name";
                $extension = $this->check_image($image, $path);
                $image_bathroom = new ImageBathroomProperty();
                $image_bathroom->property_id = $property->id;
                $image_bathroom->image = $image_name . '.' . $extension;
                $image_bathroom->save();
            }

            FacilityProperty::where('property_id', $property->id)->delete();

            $other_facility_count = count($request->fasilitas_lain);

            for ($i = 0; $i < $other_facility_count; $i++) {
                $other_facility = new FacilityProperty();
                $other_facility->property_id = $property->id;
                $other_facility->facility_id = $request->fasilitas_lain[$i];
                $other_facility->save();
            }

            RuleProperty::where('property_id', $property->id)->delete();

            $rules_property_count = count($request->rules);

            for ($i = 0; $i < $rules_property_count; $i++) {
                $rule_property = new RuleProperty();
                $rule_property->property_id = $property->id;
                $rule_property->rule_id = $request->rules[$i];
                $rule_property->save();
            }

            if ($path_image_bangunan_depan != null && $path_image_dalam != null && $path_image_depan != null) {
                Storage::delete($path_image_bangunan_depan);
                Storage::delete($path_image_dalam);
                Storage::delete($path_image_depan);
            }
            return ResponseFormatter::success($property);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
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

    public function search($city)
    {
        try {
            $query = Property::query();
            if ($city != 'all') {
                $query->where('kota', $city);
            }

            $request = $_REQUEST;
            if (isset($request['priceStart']) && isset($request['priceEnd']) && isset($request['priceStart']) && !empty($request['priceEnd'])) {
                $query->whereBetween('harga_sewa_1_bulan', [$request['priceStart'], $request['priceEnd']]);
            }

            if (isset($request['bedroomCount']) && !empty($request['bedroomCount'])) {
                if ($request['bedroomCount'] == 5) {
                    $query->where('kamar_mandi', '>=', 5);
                } else {
                    $query->where('total_kamar', $request['bedroomCount']);
                }
            }

            if (isset($request['bathroomCount']) && !empty($request['bathroomCount'])) {
                $query->where('kamar_mandi', $request['bathroomCount']);
            }

            if (isset($request['type']) && !empty($request['type'])) {
                $query->where('kategori', $request['type']);
            }

            if (isset($request['facility']) && !empty($request['facility'])) {
                $query->where('fasilitas', $request['facility']);
            }

            $data = $query->with('user')->paginate(10);

            foreach ($data as $item) {
                $images = array();
                $image_property = ImageBuildProperty::where('property_id', $item->id)->get();
                foreach ($image_property as $row) {
                    $path_bangunan_depan = !empty($row->bangunan_depan) ? $row->image_bangunan_depan_url() : [];
                    $path_depan = !empty($row->depan) ? $row->image_depan_url() : [];
                    $path_dalam = !empty($row->dalam) ? $row->image_dalam_url() : [];

                    array_push($images, $path_bangunan_depan);
                    array_push($images, $path_depan);
                    array_push($images, $path_dalam);
                }

                $image_bedroom = BedroomFacilityProperty::where('id', $item->id)->get();
                foreach ($image_bedroom as $row) {
                    $path = $row->image_url();
                    array_push($images, $path);
                }

                $image_bathroom = ImageBathroomProperty::where('id', $item->id)->get();
                foreach ($image_bathroom as $row) {
                    $path = $row->image_url();
                    array_push($images, $path);
                }

                $item->image = $images;
            }

            return ResponseFormatter::success($data);
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
