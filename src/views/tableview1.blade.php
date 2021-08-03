@if(isset($extends))
  @extends($extends)
@endif

@section('options-area')
  <button name="action" value="load" class="rounded-2">
    <span class="fa fa-sync cl-gray-500"></span>
    Muat Ulang
  </button>
@endsection

@section('filterbar')
  @if($searchable)
    <span data-type="textbox" class="bg-white b-3 rounded-3">
        <span class="fa fa-search font-size-2 p-1 pl-2 cl-gray-400"></span>
        <input type="text" class="p-1" name="search" value="" placeholder="Cari..."/>
          <button name="action" value="load" class="hidden"></button>
        <span class="textbox-icon textbox-clear">
          <span class="fa fa-times font-size-2 cl-gray-300 p-2 py-1"></span>
        </span>
      </span>
  @endif

  @if(count($filters) > 0)
    <span class="filter-area"></span>

    <button class="p-1 px-2 bg-gray-100 b-3 rounded-3 cl-gray-500" name="action" value="open-filters">
      <span class="fa fa-plus cl-gray-400"></span>
      Tambah Filter
    </button>
  @endif
@endsection

@section('header-row-1')
  <div>
    <div class="flex valign-top">
      <div>
        <h1 class="font-size-6">{{ $title ?? 'Untitled' }}</h1>
      </div>
      <span class="options-area">
        @yield('options-area')
      </span>
    </div>
  </div>
@endsection

@section('content')

  <form method="post" class="async" action="">

    <div class="sticky bg-gray-100 py-3 pb-0" data-event data-sticky-after=".header">
      @yield('header-row-1')
      <div class="my-2 h-scrollable nowrap filterbar">
        @yield('filterbar')
      </div>
      <button name="action" value="load" class="hidden"></button>
      <div class="table-head rt-1">
        <table>
          <thead>
          <tr>
            {!! $column_html ?? '' !!}
          </tr>
          </thead>
        </table>
      </div>
    </div>

    <div>
      <div data-type="table" id="{{ $id }}" data-event data-init-click="[value=load]" data-table-head=".table-head">
        <div class="table-body h-scrollable nh-100h"></div>
        <input type="hidden" name="_tableview1_id" value="{{ $id }}" />
      </div>
    </div>

    <div class="sticky sticky-bottom bg-white">
      <div class="table-foot"></div>
    </div>

  </form>

@endsection