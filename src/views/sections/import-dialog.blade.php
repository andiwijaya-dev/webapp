<form method="post" class="async" action="{{ $path ?? '' }}">
  <div class="modal-head p-3">
    <h1 class="font-size-5 font-weight-700">{{ $title ?? 'Import' }}</h1>
    <p class="mt-1">{!! nl2br($description ?? '') !!}</p>
    <div class="dock-top-right">
      <button class="p-0 button-0" type="button" data-event data-click-close="parent(.modal)">
        <span class="fa fa-times cl-gray p-3"></span>
      </button>
    </div>
  </div>
  <div class="modal-body p-3">
    <div class="my-3">
      <label>Masukkan file</label>
      <div class="bg-primary-100 b-3 p-3 mt-1">
        <input type="file" name="file" />
      </div>
    </div>
    <div class="mt-5">
      <button class="button-0 cl-primary" name="action" value="download">Download template</button>
    </div>
  </div>
  <div class="modal-foot p-3 align-right">
    <button class="primary px-3 font-weight-600" name="action" value="analyse">Lanjut</button>
  </div>
</form>