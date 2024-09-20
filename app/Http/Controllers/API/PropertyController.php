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
            'daya_listrik' => 'required',
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
                    if (!preg_match('/^data:image\/(jpeg|png|jpg);base64,/', $value)) {
                        $fail('The ' . $attribute . ' must be a valid image format (jpeg, png, jpg).');
                    }
                },
            ],
            'foto_rumah_jalan' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^data:image\/(jpeg|png|jpg);base64,/', $value)) {
                        $fail('The ' . $attribute . ' must be a valid image format (jpeg, png, jpg).');
                    }
                },
            ],
            'foto_rumah_dalam' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^data:image\/(jpeg|png|jpg);base64,/', $value)) {
                        $fail('The ' . $attribute . ' must be a valid image format (jpeg, png, jpg).');
                    }
                },
            ],
            'foto_kamar_tidur.*' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^data:image\/(jpeg|png|jpg);base64,/', $value)) {
                        $fail('The ' . $attribute . ' must be a valid image format (jpeg, png, jpg).');
                    }
                },
            ],
            'foto_kamar_mandi' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^data:image\/(jpeg|png|jpg);base64,/', $value)) {
                        $fail('The ' . $attribute . ' must be a valid image format (jpeg, png, jpg).');
                    }
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

        // return response()->json($request->all());

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->messages()->all(), 400);
        }

        try {

            // $request->meja == 'true' ? true : false;
            // $request->kasur == 'true' ? true : false;

            // return ResponseFormatter::success($request->all());
            $data = new Property();
            $data->fill($request->all());
            $data->tanggal_dibuat = ResponseFormatter::timestampToDate($request->tanggal_dibuat);
            $data->tanggal_mulai_sewa = ResponseFormatter::timestampToDate($request->tanggal_mulai_sewa);
            $data->user_id = Auth::user()->id;
            $data->save();

            // $bedroom_facility = new BedroomFacilityProperty();

            $image_property = new ImageBuildProperty();
            $image_property->property_id = $data->id;

            if ($request->has('foto_rumah_depan')) {
                $image_rumah_depan = $request->input('foto_rumah_depan');
                $image_rumah_depan_name = time() . '-' . $request->name . '-rumah-depan';
                $path = "public/uploads/properties/{$data->user[0]->name}/{$data->nama}/" . $image_rumah_depan_name;
                $extension = $this->base64ToImage($image_rumah_depan, $path);
                $image_property->bangunan_depan = $image_rumah_depan_name . '.' . $extension;
            }

            if ($request->has('foto_rumah_jalan')) {
                $image_rumah_jalan = $request->input('foto_rumah_jalan');
                $image_rumah_jalan_name = time() . '-' . $request->name . '-rumah-jalan';
                $path = "public/uploads/properties/{$data->user[0]->name}/{$data->nama}/" . $image_rumah_jalan_name;
                $extension = $this->base64ToImage($image_rumah_jalan, $path);
                $image_property->depan = $image_rumah_jalan_name . '.' . $extension;
            }

            if ($request->has('foto_rumah_dalam')) {
                $image_rumah_dalam = $request->input('foto_rumah_dalam');
                $image_rumah_dalam_name = time() . '-' . $request->name . '-rumah-dalam';
                $path = "public/uploads/properties/{$data->user[0]->name}/{$data->nama}/" . $image_rumah_dalam_name;
                $extension = $this->base64ToImage($image_rumah_dalam, $path);
                $image_property->dalam = $image_rumah_dalam_name . '.' . $extension;
            }

            $image_property->save();

            if ($request->has('foto_kamar_tidur')) {
                $image_bedroom_count = count($request->input('foto_kamar_tidur'));
                for ($i = 0; $i < $image_bedroom_count; $i++) {
                    $image = $request->input('foto_kamar_tidur')[$i];
                    $image_name = time() . '-' . $request->name . '-kamar-tidur';
                    $path = "public/uploads/properties/{$data->user[0]->name}/{$data->nama}/kamar-tidur/" . $image_name;
                    $extension = $this->base64ToImage($image, $path);
                    $image_bedroom = new BedroomFacilityProperty();
                    $image_bedroom->property_id = $data->id;
                    $image_bedroom->image = $image_name . '.' . $extension;
                    $image_bedroom->save();
                }
            }

            if ($request->has('foto_kamar_mandi')) {
                $image = $request->input('foto_kamar_mandi');
                $image_name = time() . '-' . $request->name . '-kamar-mandi';
                $path = "public/uploads/properties/{$data->user[0]->name}/{$data->nama}/kamar-mandi/" . $image_name;
                $extension = $this->base64ToImage($image, $path);
                $image_bathroom = new ImageBathroomProperty();
                $image_bathroom->property_id = $data->id;
                $image_bathroom->image = $image_name . '.' . $extension;
                $image_bathroom->save();
            }

            $other_facility_count = count($request->fasilitas_lain);
            for ($i = 0; $i < $other_facility_count; $i++) {
                $other_facility = new FacilityProperty();
                $other_facility->property_id = $data->id;
                $other_facility->facility_id = $request->fasilitas_lain[$i];
                $other_facility->save();
            }
            // $data = $other_facility_count;
            $rules_property = count($request->rules);
            for ($i = 0; $i < $rules_property; $i++) {
                $rule_property = new RuleProperty();
                $rule_property->property_id = $data->id;
                $rule_property->rule_id = $request->rules[$i];
                $rule_property->save();
            }

            return ResponseFormatter::success($data);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function detail_property($id)
    {
        try {
            $data = Property::where('id', $id)->with('user')->first();

            return ResponseFormatter::success($data);
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
            $data = Property::where('user_id', $user->id)->get();
            foreach ($data as $item) {
                $item->rating = RatingProperty::where('user_id', $user->id)->where('property_id', $item->id)->avg('rating');
                $image_build = ImageBuildProperty::where('property_id', $item->id)->pluck('bangunan_depan');
                $item->image = !empty($image_build[0]) ? asset("uploads/properties/{$item->user[0]->name}/{$item->nama}/{$image_build[0]}") : null;
            }

            $result_array = [
                'user' => $user,
                'properties' => $data
            ];

            return ResponseFormatter::success($result_array);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    public function get_property($id)
    {
        try {
            $data = Property::where('id', $id)->with('user')->first();
            $property_image = ImageBuildProperty::where('property_id', $data->id)->first();
            $images = [
                'property' => [
                    'bagian_depan' => isset($property_image->bangunan_depan) ? asset("uploads/properties/{$data->user[0]->name}/{$data->nama}/{$property_image->bangunan_depan}") : null,
                    'bagian_jalan' => isset($property_image->depan) ? asset("uploads/properties/{$data->user[0]->name}/{$data->nama}/{$property_image->depan}") : null,
                    'bagian_dalam' => isset($property_image->dalam) ? asset("uploads/properties/{$data->user[0]->name}/{$data->nama}/{$property_image->dalam}") : null,
                ],
                'bedroom' => []
            ];

            $property_bedroom = BedroomFacilityProperty::where('property_id', $data->id)->get();
            $images['bedroom'] = array();
            foreach ($property_bedroom as $item) {
                $images['bedroom'][] = isset($item->image) ? asset("uploads/properties/{$data->user[0]->name}/{$data->nama}/kamar-tidur/{$item->image}") : null;
            }

            $property_bathroom = ImageBathroomProperty::where('property_id', $data->id)->first();
            $images['bathroom'] = isset($property_bathroom->image) ? asset("uploads/properties/{$data->user[0]->name}/{$data->nama}/kamar-mandi/{$property_bathroom->image}") : null;

            $property_facility = FacilityProperty::where('property_id', $data->id)->get();
            $facility = [];
            foreach ($property_facility as $item) {
                $facility[] = ListFacility::where('id', $item->facility_id)->first()['id'];
            }

            $property_rules = RuleProperty::where('property_id', $data->id)->get();
            $rules = [];
            foreach ($property_rules as $item) {
                $rules[] = ListRules::where('id', $item->rule_id)->first()['id'];
            }

            $data->rules = $rules;
            $data->facility = $facility;
            $data->images = $images;

            return ResponseFormatter::success($data);
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
                    if (!preg_match('/^data:image\/(jpeg|png|jpg);base64,/', $value)) {
                        $fail('The ' . $attribute . ' must be a valid image format (jpeg, png, jpg).');
                    }
                },
            ],
            'foto_rumah_jalan' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^data:image\/(jpeg|png|jpg);base64,/', $value)) {
                        $fail('The ' . $attribute . ' must be a valid image format (jpeg, png, jpg).');
                    }
                },
            ],
            'foto_rumah_dalam' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^data:image\/(jpeg|png|jpg);base64,/', $value)) {
                        $fail('The ' . $attribute . ' must be a valid image format (jpeg, png, jpg).');
                    }
                },
            ],
            'foto_kamar_tidur.*' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^data:image\/(jpeg|png|jpg);base64,/', $value)) {
                        $fail('The ' . $attribute . ' must be a valid image format (jpeg, png, jpg).');
                    }
                },
            ],
            'foto_kamar_mandi' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^data:image\/(jpeg|png|jpg);base64,/', $value)) {
                        $fail('The ' . $attribute . ' must be a valid image format (jpeg, png, jpg).');
                    }
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
        // return response()->json($request->all());

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
            // dd($image_property);

            if (!empty($image_property->bangunan_depan)) {
                Storage::delete("/public/uploads/properties/{$data->user[0]->name}/{$data->nama}/{$image_property->bangunan_depan}");
            }

            if (!empty($image_property->depan)) {
                Storage::delete("/public/uploads/properties/{$data->user[0]->name}/{$data->nama}/{$image_property->depan}");
            }

            if (!empty($image_property->dalam)) {
                Storage::delete("/public/uploads/properties/{$data->user[0]->name}/{$data->nama}/{$image_property->dalam}");
            }

            if ($request->has('foto_rumah_depan')) {
                $image_rumah_depan = $request->input('foto_rumah_depan');
                $image_rumah_depan_name = time() . '-' . $request->name . '-rumah-depan';
                $path = "public/uploads/properties/{$data->user[0]->name}/{$data->nama}/" . $image_rumah_depan_name;
                $extension = $this->base64ToImage($image_rumah_depan, $path);
                $image_property->bangunan_depan = $image_rumah_depan_name . '.' . $extension;
            }

            if ($request->has('foto_rumah_jalan')) {
                $image_rumah_jalan = $request->input('foto_rumah_jalan');
                $image_rumah_jalan_name = time() . '-' . $request->name . '-rumah-jalan';
                $path = "public/uploads/properties/{$data->user[0]->name}/{$data->nama}/" . $image_rumah_jalan_name;
                $extension = $this->base64ToImage($image_rumah_jalan, $path);
                $image_property->depan = $image_rumah_jalan_name . '.' . $extension;
            }

            if ($request->has('foto_rumah_dalam')) {
                $image_rumah_dalam = $request->input('foto_rumah_dalam');
                $image_rumah_dalam_name = time() . '-' . $request->name . '-rumah-dalam';
                $path = "public/uploads/properties/{$data->user[0]->name}/{$data->nama}/" . $image_rumah_dalam_name;
                $extension = $this->base64ToImage($image_rumah_dalam, $path);
                $image_property->dalam = $image_rumah_dalam_name . '.' . $extension;
            }

            $image_property->save();
            if ($request->has('foto_kamar_tidur')) {
                $image_bedroom_count = count($request->input('foto_kamar_tidur'));
                for ($i = 0; $i < $image_bedroom_count; $i++) {
                    $image = $request->input('foto_kamar_tidur')[$i];
                    $image_name = time() . '-' . $request->name . '-kamar-tidur';
                    $path = "public/uploads/properties/{$data->user[0]->name}/{$data->nama}/kamar-tidur/" . $image_name;
                    $extension = $this->base64ToImage($image, $path);
                    $image_bedroom = new BedroomFacilityProperty();
                    $image_bedroom->property_id = $data->id;
                    $image_bedroom->image = $image_name . '.' . $extension;
                    $image_bedroom->save();
                }
            }

            if ($request->has('foto_kamar_mandi')) {
                $image = $request->input('foto_kamar_mandi');
                $image_name = time() . '-' . $request->name . '-kamar-mandi';
                $path = "public/uploads/properties/{$data->user[0]->name}/{$data->nama}/kamar-mandi/" . $image_name;
                $extension = $this->base64ToImage($image, $path);
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

            return ResponseFormatter::success($data);
        } catch (Exception $error) {
            return ResponseFormatter::exception_error($error->getMessage());
        }
    }

    function base64ToImage($base64, $path)
    {
        $mimeType = explode(';', explode(':', $base64)[1])[0];
        $validMimeTypes = ['image/jpeg', 'image/png'];
        $validExtensions = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

        // Validasi MIME type
        if (!in_array($mimeType, $validMimeTypes)) {
            throw new \Exception('Invalid image type');
        }

        // Mengonversi Base64 ke gambar
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $imageData = str_replace(' ', '+', $imageData);
        $image = base64_decode($imageData);

        // Menentukan ekstensi
        $extension = $validExtensions[$mimeType];
        $fileName = $path . '.' . $extension;

        // Menyimpan gambar ke Storage
        Storage::put($fileName, $image);

        // dd(Storage::put($fileName, $image));
        return $extension;
    }
}
