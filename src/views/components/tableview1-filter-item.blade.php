<span class="cursor-pointer p-1 px-2 bg-gray-100 b-3 rounded-3 cl-gray-500 filter-item-{{ $filter['id'] }} valign-middle" id="filter-item-{{ $filter['id'] }}">
  <button name="action" value="open-filters|{{ $filter['id'] }}" class="button-0 p-0">
    <span class="fa fa-circle cl-primary font-size-1 mt--1 valign-middle"></span>
    {{ $filter['text'] ?? $filter['name'] }}
  </button>
  <span class="fa fa-times cl-gray-300 cursor-pointer valign-middle" data-event data-click-remove=".filter-item-{{ $filter['id'] }}" data-click-click="[value=load]"></span>
  <input type="hidden" name="filters[]" value="{{ json_encode($filter)  }}" />
</span>