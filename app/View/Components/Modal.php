<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Modal extends Component
{
    public $id;
    public $content;
    public $dialogstyle;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id, $content, $dialogstyle = '')
    {
        $this->id = $id;
        $this->content = $content;
        $this->dialogstyle = $dialogstyle;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.modal');
    }
}
