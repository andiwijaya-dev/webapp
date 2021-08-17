<form method="post" class="async" action="">
  <div class="modal-head p-3">
    <h1 class="font-size-5">Scheduled Task</h1>
    <div class="dock-top-right">
      <span class="fa fa-times cl-gray-500 p-3" data-event data-click-close=":closest(.modal)"></span>
    </div>
    @if(isset($task->results))
    <div class="align-center mt-3">
      <span data-type="tabs" data-container=".{{ ($tab_id = 't' . uniqid()) }}">
        <div class="tab-item p-1 active font-size-2">Info</div>
        <div class="tab-item p-1 font-size-2">Last Result</div>
      </span>
    </div>
    @endif
  </div>
  <div class="modal-body p-3 {{ $tab_id ?? '' }}">
    <div>
      <div class="grid gap-2">
        <div>
          <small>Status</small>
          <div data-type="dropdown" class="mt-1">
            <select name="status">
              <option value="" disabled selected>- Status -</option>
              <option value="-2"{{ ($task->status ?? 1) == -2 ? ' selected' : '' }}>Disabled</option>
              <option value="-1"{{ ($task->status ?? 1) == -1 ? ' selected' : '' }} disabled>Error</option>
              <option value="1"{{ ($task->status ?? 1) == 1 ? ' selected' : '' }}>Active</option>
              <option value="2"{{ ($task->status ?? 1) == 2 ? ' selected' : '' }} disabled>Running</option>
              <option value="3"{{ ($task->status ?? 1) == 3 ? ' selected' : '' }} disabled>Completed</option>
            </select>
            <div class="dropdown-icon fa fa-caret-down cl-gray-500"></div>
          </div>
        </div>
        <div>
          <small>Description</small>
          <div data-type="textbox" class="mt-1">
            <input type="text" name="description" autocomplete="off" value="{{ $task->description ?? '' }}" />
          </div>
        </div>
        <div>
          <small>Command</small>
          <div data-type="textbox" class="mt-1">
            <input type="text" name="command" autocomplete="off" value="{{ $task->command ?? '' }}" />
          </div>
        </div>
        <div>
          <small>Repeat</small>
          <div data-type="dropdown" class="mt-1">
            <select name="repeat">
              <option value="0" disabled selected>- Repeat -</option>
              <option value="0"{{ ($task->repeat ?? 0) == 0 ? ' selected' : '' }}>No Repeat</option>
              <option value="1"{{ ($task->repeat ?? 0) == 1 ? ' selected' : '' }}>Every Minute</option>
              <option value="2"{{ ($task->repeat ?? 0) == 2 ? ' selected' : '' }}>Every 5 Minute</option>
              <option value="3"{{ ($task->repeat ?? 0) == 3 ? ' selected' : '' }}>Every 10 Minute</option>
              <option value="4"{{ ($task->repeat ?? 0) == 4 ? ' selected' : '' }}>Every Hour</option>
              <option value="5"{{ ($task->repeat ?? 0) == 5 ? ' selected' : '' }}>Every Day</option>
            </select>
            <div class="dropdown-icon fa fa-caret-down cl-gray-500"></div>
          </div>
        </div>
        <div >
          <small>Start</small>
          <div class="mt-1" data-type="datepicker" data-mode="datetime" data-empty-text="{{ $task->start ?? \Carbon\Carbon::now()->addHours(1)->format('j M Y H:00:00') }}">
            <input type="text" name="start" />
            <span class="datepicker-icon datepicker-open fa fa-calendar cl-gray-500 p-2"></span>
          </div>
        </div>
      </div>
    </div>
    @if(isset($task->results) && is_array($task->results) && count($task->results) > 0)
    <div class="hidden">
      <div class="grid grid-3">
        <div>
          <small class="ellipsis p-2">Status</small>
        </div>
        <div class="grid-span-2">
          <label class="ellipsis p-2">{{ $task->results[count($task->results) - 1]->status_text ?? '-' }}</label>
        </div>
        <div>
          <small class="ellipsis p-2">Started At</small>
        </div>
        <div class="grid-span-2">
          <label class="ellipsis p-2">{{ $task->results[count($task->results) - 1]->started_at ?? '-' }}</label>
        </div>
        <div>
          <small class="ellipsis p-2">Ellapsed</small>
        </div>
        <div class="grid-span-2">
          <label class="ellipsis p-2">{{ $task->results[count($task->results) - 1]->ellapsed ?? '-' }}</label>
        </div>
        <div>
          <small class="ellipsis p-2">Completed At</small>
        </div>
        <div class="grid-span-2">
          <label class="ellipsis p-2">{{ $task->results[count($task->results) - 1]->completed_at ?? '-' }}</label>
        </div>
        <div>
          <small class="ellipsis p-2">PID</small>
        </div>
        <div class="grid-span-2">
          <label class="ellipsis p-2">{{ $task->results[count($task->results) - 1]->pid ?? '-' }}</label>
        </div>
        <div>
          <small class="ellipsis p-2">Verbose</small>
        </div>
        <div class="grid-span-2">
          <p class="p-2">{!! nl2br($task->results[count($task->results) - 1]->verbose) !!}</p>
        </div>
      </div>
    </div>
    @endif
  </div>
  <div class="modal-foot p-3 align-right">
    <button class="primary px-3 font-submit font-weight-600">Simpan</button>
    <button type="button" data-event data-click-close=":closest(.modal)">Tutup</button>
  </div>
  <input type="hidden" name="id" value="{{ $task->id ?? '' }}" />
</form>