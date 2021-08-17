<form method="post" class="async" action="{{ $path ?? '' }}">
  <div class="modal-head p-3">
    <h1 class="font-size-4">Mapping Kolom</h1>
    <div class="dock-top-right">
      <button class="p-0 button-0" type="button" data-event data-click-close="parent(.modal)">
        <span class="fa fa-times cl-gray p-3"></span>
      </button>
    </div>
  </div>
  <div class="modal-body p-3">

    <div data-type="table">
      <div class="table-head">
        <table>
          <thead>
            <tr>
              <th width="200px">Column<div class="table-resize"></div></th>
              <th width="100px">Required<div class="table-resize"></div></th>
              <th width="200px">Map To<div class="table-resize"></div></th>
              <th width="100%"></th>
            </tr>
          </thead>
        </table>
      </div>
      <div class="table-body">
        <table>
          <tbody>
            @foreach($columns as $key=>$column)
            <tr>
              <td>
                <label class="ellipsis">
                  {{ $column['text'] ?? $column['name'] }}
                  <p class="mt-1 font-size-1 cl-gray-500 wrap-pre">{{ $column['description'] ?? '' }}</p>
                </label>
              </td>
              <td><label class="ellipsis">{{ ($column['required'] ?? false) ? 'Required' : 'Optional' }}</label></td>
              <td>
                <div data-type="dropdown">
                  <select name="{{ $key }}">
                    <option value="" selected disabled></option>
                    @if(!($column['required'] ?? false))
                      <option value="" selected>Tidak Digunakan</option>
                    @endif
                    @foreach($data_columns as $data_column)
                      <option value="{{ $data_column }}"
                        {{ strtolower($data_column) == strtolower(($column['text'] ?? $key)) || in_array($data_column, $column['mappings'] ?? []) ? 'selected' : '' }}>
                        {{ $data_column }}
                      </option>
                    @endforeach
                  </select>
                  <span class="dropdown-icon fa fa-caret-down cl-gray-500"></span>
                </div>
              </td>
              <td></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="modal-foot p-3 align-right">
    <button class="primary px-3 font-weight-600" name="action" value="proceed">Import</button>
  </div>
</form>