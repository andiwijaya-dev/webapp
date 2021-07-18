<div class="modal-head p-3 bb-3">
  <h1 class="font-size-4">Filter</h1>
  <div class="dock-top-right">
    <span class="fa fa-times p-3 cl-gray-500" data-event data-click-close="#tableview1-filter"></span>
  </div>
</div>
<div class="modal-body relative">
  <div class="flex">
    <span class="h-50h w-30p v-scrollable">
      <div data-type="tabs" data-container="#tableview1-tabcont" class="tabs-vertical">
        @foreach($filters as $idx=>$filter)
          <div class="tab-item p-3 bb-3{{ (!isset($value['name']) && $idx == 0) || ($value['name'] ?? -1) == $filter['name'] ? ' active' : '' }}">{{ $filter['text'] ?? (collect($columns)->where('name', $filter['name'])->first()['text'] ?? $filter['text'] ?? $filter['name']) }}</div>
        @endforeach
      </div>
    </span>
    <div id="tableview1-tabcont" class="bl-3">

      @foreach($filters as $idx=>$filter)
        @component('andiwijaya::sections.tableview1-filter-section', [ 'filter'=>$filter, 'hidden'=>!((!isset($value['name']) && $idx == 0) || ($value['name'] ?? -1) == $filter['name']), 'value'=>($value['name'] ?? -1) == $filter['name'] ? $value : null ])@endcomponent
      @endforeach

    </div>
  </div>
</div>