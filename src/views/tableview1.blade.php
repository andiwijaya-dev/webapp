@if(isset($extends))
  @extends($extends)
@endif

@section('options-area')
  <button name="action" value="load" class="rounded-2">
    <span class="fa fa-sync cl-gray-500"></span>
    Muat Ulang
  </button>
@endsection

@section('content')

  <form method="post" class="async" action="">

    <div class="sticky bg-gray-100" data-event data-sticky-after=".header">
      <div class="mb-3 pt-3">
        <div class="flex valign-top">
          <div>
            <h1 class="font-size-6 font-weight-600">{{ $title ?? 'Untitled' }}</h1>
            <button name="action" value="load" class="hidden"></button>
          </div>
          <span>
          @yield('options-area')
        </span>
        </div>
      </div>
      <div class="my-2 h-scrollable nowrap">

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

      </div>
    </div>

    <div class="my-2">
      <div data-type="table" id="{{ $id }}" data-event data-init-click="[value=load]">
        <div class="table-head">
          <table>
            <thead>
            <tr>
              {!! $column_html !!}
              <th width="100%"></th>
            </tr>
            </thead>
          </table>
        </div>
        <div class="table-body h-scrollable nh-90h"></div>
        <div class="table-foot"></div>
        <input type="hidden" name="_tableview1_id" value="{{ $id }}" />
      </div>
    </div>

  </form>

@endsection