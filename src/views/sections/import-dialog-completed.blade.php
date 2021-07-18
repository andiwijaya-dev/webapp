<div class="modal-head p-3">
  <h1 class="font-size-4">Hasil Import</h1>
  <div class="dock-top-right">
    <button class="p-0 button-0" type="button" data-event data-click-close="parent(.modal)">
      <span class="fa fa-times cl-gray p-3"></span>
    </button>
  </div>
</div>
<div class="modal-body p-3">
  <div class="grid grid-3 gap-2">
    <div>
      <div class="bg-primary-100 b-2 rounded-2 p-3">
        <label>Baru</label>
        <h1 class="font-weight-700">{{ $result_new ?? 0 }}</h1>
      </div>
    </div>
    <div>
      <div class="bg-primary-100 b-2 rounded-2 p-3">
        <label>Update</label>
        <h1 class="font-weight-700">{{ $result_updates ?? 0 }}</h1>
      </div>
    </div>
    <div>
      <div class="bg-primary-100 b-2 rounded-2 p-3">
        <label>Dihapus</label>
        <h1 class="font-weight-700">{{ $result_removed ?? 0 }}</h1>
      </div>
    </div>
    @if(isset($result_logs) && is_array($result_logs) && count($result_logs) > 0)
    <div class="grid-span-3">
      <div data-type="table">
        <div class="table-head">
          <table>
            <thead>
              <tr>
                <th width="400px">Error<div class="table-resize"></div></th>
                <th width="100%"></th>
              </tr>
            </thead>
          </table>
        </div>
        <div class="table-body">
          <table>
            <tbody>
              @foreach($result_logs as $result_log)
              <tr>
                <td><label class="ellipsis">{{ $result_log }}</label></td>
                <td></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @endif
  </div>
</div>
<div class="modal-foot p-3 align-right">
  <button class="primary px-3 font-weight-600" type="button" data-event data-click-close="parent(.modal)">Tutup</button>
</div>