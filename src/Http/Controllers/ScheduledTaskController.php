<?php

namespace Andiwijaya\WebApp\Http\Controllers;

use Andiwijaya\WebApp\Http\Controllers\TableView1Controller;
use Andiwijaya\WebApp\Models\ScheduledTask;
use App\Http\Controllers\Admin\Traits\AdminTraits;
use Illuminate\Http\Request;

class ScheduledTaskController extends TableView1Controller
{
  use AdminTraits;

  protected $extends = 'admin.layouts.default';

  protected $view = 'andiwijaya::tableview1';

  protected $title = "Scheduled Task";

  protected $model = ScheduledTask::class;

  protected $searchable = false;

  protected $columns = [
    [ 'text'=>'', 'name'=>'options', 'width'=>60 ],
    [ 'text'=>'Status', 'name'=>'status', 'width'=>100 ],
    [ 'text'=>'Description', 'name'=>'description', 'width'=>300, 'sortable'=>true ],
    [ 'text'=>'Repeat', 'name'=>'repeat_text', 'width'=>200, 'sortable'=>true ],
    [ 'text'=>'Last Run', 'name'=>'last_run_at', 'width'=>160, 'datatype'=>'datetime', 'class'=>'font-size-2'],
    [ 'text'=>'Count', 'name'=>'count', 'width'=>100, 'datatype'=>'number' ],
  ];

  protected $filters = [
    [ 'name'=>'name' ],
  ];

  protected $request_inputs = [
    'start'=>'datetime'
  ];

  protected function columnOptions($obj, $column)
  {
    return <<<EOF
<div class="align-center py-1">
  <a href="/scheduled-task/{$obj['id']}" class="async" data-history="none"><span class="fa fa-bars cl-gray-400 p-1"></span></a>
  <a href="/scheduled-task/{$obj['id']}" class="async" data-history="none" data-method="DELETE" data-confirm="Hapus?"><span class="fa fa-times cl-gray-300 p-1"></span></a>
</div>
EOF;
  }

  protected function columnStatus($obj, $column)
  {
    $html = [];
    switch($obj->status)
    {
      case ScheduledTask::STATUS_DISABLED:
      case ScheduledTask::STATUS_ERROR:
        $html[] = "<div class='status-indicator--1 my-1 ellipsis'>{$obj->status_text}</div>";
        break;
      case ScheduledTask::STATUS_ACTIVE:
      case ScheduledTask::STATUS_RUNNING:
      case ScheduledTask::STATUS_COMPLETED:
        $html[] = "<div class='status-indicator-1 my-1 ellipsis'>{$obj->status_text}</div>";
        break;
    }
    return implode('', $html);
  }

  public function create(Request $request)
  {
    return $this->open($request);
  }

  public function open(Request $request, $id = null)
  {
    $task = ScheduledTask::find($id);

    return htmlresponse()
      ->modal(
        'scheduled-task-edit',
        view('andiwijaya::sections.scheduled-task-edit', compact('task'))->render(),
        [
          'width'=>400,
          'height'=>600
        ]
      );
  }

  public function save(Request $request)
  {
    $request->merge([ 'creator_id'=>$this->user->id ]);

    $task = $request->input('id') > 0 ? ScheduledTask::findOrFail($request->input('id')) : new ScheduledTask();
    $task->fill($request->all());
    $task->save();

    return htmlresponse()
      ->replaceOrPrepend("#{$this->id}", $this->renderItem($task))
      ->close('#scheduled-task-edit')
      ->toast('Data saved successfully');
  }

  public function destroy(Request $request, $id)
  {
    $task = ScheduledTask::findOrFail($id);
    $task->delete();

    return htmlresponse()
      ->remove("#{$this->id} [data-id='{$id}']")
      ->toast('Data removed');
  }
}