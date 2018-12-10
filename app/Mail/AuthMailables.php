<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AuthMailables extends Mailable
{
    use Queueable, SerializesModels;

	public $subject = "SportyPeople";
	public $data = array('title' => 'SportyPeople');
	public $tpl = "test";
	
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject = "", $tpl = "", $data = array())
    {
		$this->subject = ! empty($subject) ? $subject : $this->subject;
		$this->tpl = ! empty($tpl) ? $tpl : $this->tpl;
		$this->data = ! empty($data) ? array_merge($data) : $this->data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
			->view('emails.' . $this->tpl)
			->subject($this->subject)
			->with($this->data);
    }
}
