<?php

namespace App\Http\Controllers;

use App\Exports\ArsipBulanan;
use App\Exports\ArsipExport;
use App\Exports\ArsipStatus;
use App\Imports\ArsipImport;
use Illuminate\Http\Request;
use App\Models\Rak;
use App\Models\Dokumen;
use App\Models\DataArsip;
use App\Models\ImportArsip;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
// use Yajra\DataTables\DataTables;
use Yajra\DataTables\Facades\DataTables;

class DataArsipController extends Controller
{

    function __construct()
    {
        $year = date('Y');
        DB::table('tb_arsip')
            ->join('dokumen', 'tb_arsip.no_pen', '=', 'dokumen.no_pen')
            ->select('tb_arsip.*', 'dokumen.tahun_resensi')
            ->where('dokumen.tahun_resensi', $year)
            ->update(['tb_arsip.status' => 0]);
    }

    public function index()
    {
        $arsip = DB::table('tb_arsip')
            ->join('dokumen', 'tb_arsip.no_pen', '=', 'dokumen.no_pen')
            ->select('tb_arsip.*', 'dokumen.nama_perusahaan', 'dokumen.tanggal_dokumen', 'dokumen.jenis_dokumen', 'dokumen.tahun_batch')
            ->get();

        $box = DataArsip::pluck('box')->toArray();
        $batch = DataArsip::pluck('batch')->toArray();
        $status = DataArsip::pluck('status')->toArray();
        $tahun = Dokumen::pluck('tahun_batch')->toArray();
        $rak = Rak::get();
        return view('Arsip/index', compact('arsip', 'rak', 'box', 'batch', 'status', 'tahun'));
    }

    function getData(Request $request)
    {
        $data = DataArsip::select([
            'tb_arsip.*',
            'dokumen.nama_perusahaan', 'dokumen.tanggal_dokumen', 'dokumen.jenis_dokumen', 'dokumen.tahun_batch', 'dokumen.tahun_resensi'
        ])->join('dokumen', 'tb_arsip.no_pen', '=', 'dokumen.no_pen');

        if ($request->input('rak') != null) {
            $data = $data->where('rak', $request->rak);
        }

        if ($request->input('box') != null) {
            $data = $data->where('box', $request->box);
        }

        if ($request->input('batch') != null) {
            $data = $data->where('tb_arsip.batch', $request->batch);
        }

        if ($request->input('tahun') != null) {
            $data = $data->where('dokumen.tahun_batch', $request->tahun);
        }

        if ($request->input('bulan') != null) {
            $data = $data->whereMonth('tb_arsip.created_at', $request->bulan);
        }

        if ($request->input('tahunInput') != null) {
            $data = $data->whereYear('tb_arsip.created_at', $request->tahunInput);
        }

        if ($request->input('status') != null) {
            if ($request->input('status') == 1)
                $data = $data->where('status', $request->status);
            elseif ($request->input('status') == 2)
                $data = $data->where('status', $request->status);
            else $data = $data->where('status', $request->status);
        }
        return DataTables::of($data)->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $dataRak = Rak::get();
        $dokumen = Dokumen::get()->all();
        return view('Arsip/create', compact('dataRak', 'dokumen'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        foreach ($request->noDok as $key => $no_dok) {
            $data = new DataArsip;
            $data->no_pen = $no_dok;
            $data->rak = $request->rak[$key];
            $data->box = $request->box[$key];
            $data->batch = $request->batch[$key];
            $data->save();
        }
        alert()->success('Success!', 'Data Berhasil Ditambahkan!')->autoclose(3500);
        return redirect('dataArsip');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $arsip = DataArsip::findOrFail($id);
        $arsip->update([
            'rak' => $request->rak,
            'box' => $request->box,
            'batch' => $request->batch,
        ]);
        if ($arsip) {
            alert()->success('Success!', 'Data Berhasil Diubah!')->autoclose(3500);
            return redirect('dataArsip');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $arsip = DataArsip::findOrFail($id);
        $arsip->delete();
        if ($arsip) {
            alert()->success('Success!', 'Data Berhasil Dihapus!')->autoclose(3500);
            return redirect('dataArsip');
        }
    }

    public function getDataSerahTerima(Request $request)
    {
        $search = $request->cari;

        $dataSerahTerima = DB::table('dokumen')
            ->select('no_pen', 'nama_perusahaan', 'jenis_dokumen', 'tanggal_dokumen')
            ->limit(5);

        $search = !empty($request->cari) ? ($request->cari) : ('');

        if ($search) {
            $dataSerahTerima->where('no_pen', 'like', '%' . $search . '%');
        }

        $data = $dataSerahTerima->limit(5)->get();

        $response = array();
        foreach ($data as $arsip) {
            $response[] = array(
                "value" => $arsip->no_pen,
                "perusahaan" => $arsip->nama_perusahaan,
                "jenisDok" => $arsip->jenis_dokumen,
                "tanggalDok" => $arsip->tanggal_dokumen
            );
        }
        return response()->json($response);
    }

    public function getDataArsip(Request $request)
    {
        $search = $request->cari;

        $dataSerahTerima = DB::table('tb_arsip')
            ->join('dokumen', 'tb_arsip.no_pen', '=', 'dokumen.no_pen')
            ->select('tb_arsip.*', 'dokumen.nama_perusahaan', 'dokumen.tanggal_dokumen', 'dokumen.jenis_dokumen')
            ->where('tb_arsip.status', '=', 1)
            ->limit(5);

        $search = !empty($request->cari) ? ($request->cari) : ('');

        if ($search) {
            $dataSerahTerima->where('tb_arsip.no_pen', 'like', '%' . $search . '%');
        }

        $data = $dataSerahTerima->limit(5)->get();

        $response = array();
        foreach ($data as $arsip) {
            $response[] = array(
                "value" => $arsip->no_pen,
                "id_dok" => $arsip->id_dok,
                "perusahaan" => $arsip->nama_perusahaan,
                "jenisDok" => $arsip->jenis_dokumen,
                "tanggalDok" => $arsip->tanggal_dokumen
            );
        }
        return response()->json($response);
    }

    public function getArsipImport(Request $request)
    {
        $data = ImportArsip::select('*');

        if ($request->input('rak') != null) {
            $data = $data->where('rak', $request->rak);
        }

        if ($request->input('box') != null) {
            $data = $data->where('box', $request->box);
        }

        if ($request->input('batch') != null) {
            $data = $data->where('batch', $request->batch);
        }

        if ($request->input('tahun') != null) {
            $data = $data->whereYear('tanggal_dok', $request->tahun);
        }

        if ($request->input('bulan') != null) {
            $data = $data->whereMonth('tanggal_dok', $request->bulan);
        }
        return DataTables::of($data)->make(true);
    }


    function exportDataArsip(Request $request)
    {
        $rak = $request->rak;
        $box = $request->box;
        $batch = $request->batch;
        $tahun = $request->tahun;
        return (new ArsipExport)->kondisi($rak, $box, $batch, $tahun, $request->status)
            ->download('arsip-' . $rak . $batch . $tahun . '.xlsx');
    }

    function exportDataArsipStatus(Request $request)
    {
        if ($request->status == 1) $ket = 'Akitf';
        if ($request->status == 2) $ket = 'Dipinjam';
        if ($request->status == 0) $ket = 'NonAktif';
        return (new ArsipStatus)->kondisi($request->tahun, $request->status)
            ->download('arsipStatus-' . $ket . $request->tahun . '.xlsx');
    }

    function exportDataArsipBulanan(Request $request)
    {
        return (new ArsipBulanan)->kondisi($request->bulan, $request->tahun)
            ->download('arsipBulan-' . $request->bulan . $request->tahun . '.xlsx');
    }

    function listDataImport()
    {
        $box = ImportArsip::pluck('box')->toArray();
        $batch = ImportArsip::pluck('batch')->toArray();
        $rak = ImportArsip::pluck('rak')->toArray();
        return view('arsip/import/listData', compact('box', 'batch', 'rak'));
    }

    function importData(Request $request)
    {
        // validasi file
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        // Ambil file excel
        $file = $request->file('file');
        // rename file
        $nama_file = rand() . $file->getClientOriginalName();
        // upload ke file_pt
        $file->move('file_arsip', $nama_file);
        // Import data
        $data =  Excel::import(new ArsipImport, public_path('/file_arsip/' . $nama_file));
        // notif session
        // redirect
        return redirect('/dataArsipImport');
    }
}