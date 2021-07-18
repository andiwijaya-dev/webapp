<div class="relative{{ ($hidden ?? false) ? ' hidden' : '' }} {{ ($hidden ?? false) ? 'should-be-hidden' : 'should-show' }}">
  <form method="post" class="async" action="">
    <div>

      <div class="px-3 py-2">
        <h1 class="font-size-4">{{ collect($columns)->where('name', $filter['name'])->first()['text'] ?? ($filter['text'] ?? $filter['name']) }}</h1>
      </div>

      <div class="h-40h v-scrollable p-3">

        <div class="filter-section-items-{{ $filter['name'] }} filter-section-items">
          @if(isset($value['filters']))
            @foreach($value['filters'] as $item)
              @component('andiwijaya::components.tableview1-filter-item-' . ($filter['type'] ?? 'string'), [ 'filter'=>$filter, 'primary'=>true, 'value'=>$item ])@endcomponent
            @endforeach
          @else
            @component('andiwijaya::components.tableview1-filter-item-' . ($filter['type'] ?? 'string'), [ 'filter'=>$filter, 'primary'=>true ])@endcomponent
          @endif
        </div>

        @if(!in_array(($filter['type'] ?? ''), [ 'enum', 'bool', 'boolean' ]))
          <div class="my-2 mt-4 align-center">
            <button type="button" class="py-1 rounded-3 cl-gray-700" data-event data-click-append-template=".filter-section-items-{{ $filter['name'] }}|.template-{{ $filter['name'] }}">Tambah</button>
          </div>
        @endif
      </div>

      <template class="template-{{ $filter['name'] }}">
        @component('andiwijaya::components.tableview1-filter-item-' . ($filter['type'] ?? 'string'), compact('filter'))@endcomponent
      </template>

      <br />
      <br />
      <br />
      <br />

      <div class="dock-bottom-right p-3">
        <div class="align-right">
          <input type="hidden" name="name" value="{{ $filter['name'] }}" />
          <input type="hidden" name="id" value="{{ $value['id'] ?? '' }}" />
          <button class="primary" name="action" value="add-filter">
            <span class="fa fa-plus-circle cl-primary-500"></span>
            Tambah Filter
          </button>
        </div>
      </div>
    </div>
  </form>
</div>