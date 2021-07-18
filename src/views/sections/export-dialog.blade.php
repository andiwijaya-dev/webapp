<form method="post" class="async" action="">
  <div class="modal-head p-3">
    <h1 class="font-size-4">Export</h1>
    <div class="dock-top-right">
      <button class="button-0 p-0" type="button" data-event data-click-close="parent(.modal)">
        <span class="fa fa-times cl-gray-500 p-3"></span>
      </button>
    </div>
  </div>
  <div class="modal-body p-3">
    <div class="grid grid-2 gap-2">
      @if($date ?? true)
      <div>
        <label>Dari</label>
        <div class="mt-1" data-type="datepicker" data-empty-text="{{ \Carbon\Carbon::now()->startOfMonth()->format('j M Y') }}">
          <input type="text" name="export_from" />
          <span class="datepicker-open datepicker-icon fa fa-calendar cl-gray-500 p-2"></span>
        </div>
      </div>
      <div>
        <label>Sampai</label>
        <div class="mt-1" data-type="datepicker" data-empty-text="{{ \Carbon\Carbon::now()->endOfMonth()->format('j M Y') }}">
          <input type="text" name="export_to" />
          <span class="datepicker-open datepicker-icon fa fa-calendar cl-gray-500 p-2"></span>
        </div>
      </div>
      @endif
      <div class="grid-span-2">
        <label class="block mt-3">Format</label>
        <div class="grid grid-2 gap-2">
          <div>
            <div data-type="radio" class="radio-2">
              <input type="radio" id="{{ $tid = 't' . uniqid() }}" name="format" value="{{ \Maatwebsite\Excel\Excel::XLSX }}" checked/>
              <label for="{{ $tid }}" class="py-2 block">
                <span><span></span></span>
                Excel (.xlsx)
              </label>
            </div>
          </div>
          <div>
            <div data-type="radio" class="radio-2">
              <input type="radio" id="{{ $tid = 't' . uniqid() }}" name="format" value="{{ \Maatwebsite\Excel\Excel::CSV }}"/>
              <label for="{{ $tid }}" class="py-2 block">
                <span><span></span></span>
                CSV (.csv)
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-foot p-3">
    <div class="grid grid-3 gap-2">
      <div class="grid-span-2">
        <button class="block primary font-weight-600" name="action" value="export">Export</button>
      </div>
      <div>
        <button class="block" type="button" data-event data-click-close="parent(.modal)">Batal</button>
      </div>
    </div>
  </div>
</form>