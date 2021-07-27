<?php

namespace Andiwijaya\WebApp\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SlackNotification extends Notification
{
  use Queueable;

  protected $message;
  protected $type;
  protected $detail;

  public function __construct($message, $type = 'info', $detail = '')
  {
    $this->message = $message;
    $this->type = $type;
    $this->detail = $detail;
  }

  public function via($notifiable)
  {
    return ['slack'];
  }

  public function toSlack($notifiable)
  {
    $message = (new SlackMessage)
      ->content($this->message);

    switch($this->type){
      case 'info': $message->info(); break;
      case 'error': $message->error(); break;
      case 'warning': $message->warning(); break;
    }

    if($this->detail)
      $message
        ->attachment(function($attachment){
          $attachment->title('Detail');

          if(is_array($this->detail)){
            $fields = [];
            foreach($this->detail as $key=>$value){
              if(is_array($value)) $value = json_encode($value);
              $fields[$key] = $value;
            }
            $attachment->fields($fields);
          }
          else
            $attachment->content($this->detail);
        });

    return $message;
  }
}
