<?php

namespace Andiwijaya\WebApp\Traits;

use Andiwijaya\WebApp\Imports\GenericImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

trait ImportDialog
{
  public function import(Request $request)
  {
    if(true){
      return htmlresponse()
        ->modal(
          'import-dialog',
          view('andiwijaya::sections.import-dialog')->render(),
          [
            'width'=>600,
            'height'=>400
          ]
        );
    }
  }

  /**
   * @param Request $request
   * @return \Andiwijaya\WebApp\Responses\HTMLResponse
   * @throws \Andiwijaya\WebApp\Exceptions\UserException
   * @throws \Throwable
   * @ajax true
   */
  public function importAnalyse(Request $request)
  {
    
  }

  public function importStart(Request $request)
  {
    return htmlresponse()
      ->html('#import-dialog', view('andiwijaya::sections.import-dialog-completed')->render())
      ->script("ui('#import-dialog').modal_resize()");
  }
}