<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Sessioncard extends Component
{
    public $session;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.sessioncard');
    }
}
