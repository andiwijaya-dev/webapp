<div class="my-2 filter-item-datatype">
  <input type="hidden" name="params[][operand]" value="" />
  <input type="hidden" name="params[][operator]" value="=" />
  <div data-type="switch" class="mt-1">
    <input type="checkbox" id="{{ $tid = 't' . uniqid() }}" name="params[][value]" value="1" />
    <label for="{{ $tid }}"><span></span></label>
  </div>
</div>