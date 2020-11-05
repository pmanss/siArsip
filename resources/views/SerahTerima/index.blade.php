@extends('layouts/app2')
@section('title','Data Serah Terima')
@section('content')
<div class="card">
    <div class="card-header">
      <h4>Data Serah Terima</h4>
    </div>
    <div class="card-body">
      <table class="table table-striped data" id="row">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Batch</th>
            <th scope="col">Nama Perusahaan</th>
            <th scope="col">Nomor Dokumen</th>
            <th scope="col">Tanggal Dokumen</th>
            <th scope="col">Jenis Dokumen</th>
            <th scope="col">Status Dokumen</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1; ?>
          @forelse ($serahTerima as $sr)
          @php
              dd($sr['no_dok'])
          @endphp
          <tr>
            <th scope="row"><?= $no++ ?></th>
            <td>{{$sr->batch}}</td>
            <td>{{$sr->nama_perusahaan}}</td>
            <td>{{$sr->no_dok}}</td>
            <td>{{$sr->tanggal_dokumen}}</td>
            <td>{{$sr->jenis_dokumen}}</td>
            <td>
              @php
                  if($sr->status == 0){
                    echo "<span class='badge badge-danger'>Belum dikonfirmasi</span>";
                  } else echo "<span class='badge badge-success'>Terkonfirmasi</span>";
              @endphp
            </td>
            <td>
              <button class="btn btn-warning" data-toggle="modal" data-target="#editSR{{$sr->no_dok}}">Edit</button>
              <button class="btn btn-danger">Hapus</button>
            </td>
          </tr>
          @empty
          <div class="alert alert-danger">
            Data Batch belum Tersedia.
        </div>
          @endforelse
        </tbody>
      </table>
  </div>
@endsection

<!-- Modal Edit -->
@foreach ($serahTerima as $sr)
<div class="modal fade" id="editSR{{$sr->no_dok}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Edit Data Nomor Dokumen : <span class="badge badge-primary"><?= $sr->no_dok ?></span></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ route('serahTerima.update', $sr->no_dok) }}" method="post">
              @csrf
              @method('PUT')
              <div class="form-group">
                <label for="recipient-name" class="col-form-label">Batch:</label>
                <input type="text" class="form-control" value="<?= $sr->batch ?>" name="batch">
              </div>
              <div class="form-group">
                <label for="message-text" class="col-form-label">Nama Perusahaan:</label>
                <input type="text" name="namaPT" value="<?= $sr->nama_perusahaan ?>" class="form-control">
              </div>
              <div class="form-group">
                <label for="message-text" class="col-form-label">Nomor Dokumen:</label>
                <input type="text" name="noDok" value="<?= $sr->no_dok ?>" class="form-control">
              </div>
              <div class="form-group">
                <label for="message-text" class="col-form-label">Tanggal Dokumen:</label>
                <input type="date" name="tanggalDok" value="<?= $sr->tanggal_dokumen ?>" class="form-control">
              </div>
              <div class="form-group">
                <label for="message-text" class="col-form-label">Jenis Dokumen:</label>
                <input type="text" name="jenisDok" value="<?= $sr->jenis_dokumen ?>" class="form-control">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary">Edit Data</button>
            </div>
        </form>
    </div>
  </div>
</div>
@endforeach