<div class="my-2 filter-item-datatype">
  <div class="grid grid-4 gap-2">

    <div class="relative">
      @if(($primary ?? false))
        <div data-type="dropdown" class="ml-4">
          <select name="params[][operand]">
            <option value="or"{{ ($value['operand'] ?? 'or') == 'or' ? ' selected' : '' }}>OR</option>
            <option value="and"{{ ($value['operand'] ?? 'or') == 'and' ? ' selected' : '' }}>AND</option>
          </select>
          <span class="dropdown-icon cl-gray-500 fa fa-caret-down"></span>
        </div>
      @else
        <div class="dock-left">
          <span class="fa fa-times cl-gray-300 p-1" data-event data-click-remove="parent(.filter-item-datatype)"></span>
        </div>
        <input type="hidden" name="params[][operand]" value="" />
      @endif
    </div>
    <div>
      <div data-type="dropdown">
        <select name="params[][operator]">
          <option value="="{{ ($value['operator'] ?? 'contains') == '=' ? ' selected' : '' }}>=</option>
          <option value="contains"{{ ($value['operator'] ?? 'contains') == 'contains' ? ' selected' : '' }}>Berisi</option>
          <option value="starts-with"{{ ($value['operator'] ?? 'contains') == 'starts-with' ? ' selected' : '' }}>Dimulai dari</option>
          <option value="ends-with"{{ ($value['operator'] ?? 'contains') == 'ends-with' ? ' selected' : '' }}>Berakhir dengan</option>
        </select>
        <span class="dropdown-icon cl-gray-500 fa fa-caret-down"></span>
      </div>
    </div>
    <div class="grid-span-2">
      <div data-type="textbox">
        <input type="text" name="params[][value]" value="{{ $value['value'] ?? '' }}" />
      </div>
    </div>

  </div>
</div>