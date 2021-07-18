<div class="my-2 filter-item-datatype">
  <input type="hidden" name="params[][operand]" value="" />
  <input type="hidden" name="params[][operator]" value="in" />
  @foreach(($filter['enums'] ?? []) as $idx=>$text)
  <div data-type="checkbox">
    <input type="checkbox" id="enum-{{ $filter['name'] }}-{{ $idx }}" name="params[][value]" value="{{ $text }}"
      {{ in_array($text, $value['value'] ?? []) ? 'checked' : '' }}/>
    <label for="enum-{{ $filter['name'] }}-{{ $idx }}" class="ellipsis p-2 nowrap">
      <span class="checkbox-icon mr-1">
        <span class="fa fa-check"></span>
      </span>
      {{ $text }}
    </label>
  </div>
  @endforeach
</div>