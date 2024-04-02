<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Scheduleform extends Component
{
    public $key;
    public $sd;
    public $sessionid;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($key,$sd,$sessionid)
    {
        $this->key = $key;
        $this->sd = $sd;
        $this->sessionid = $sessionid;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.scheduleform');
    }
}
