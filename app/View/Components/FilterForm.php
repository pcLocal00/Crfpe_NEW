<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FilterForm extends Component
{
    public $type; 

    public $datafilter; 

    public function __construct($type, $datafilter=null)
    {
        $this->type = $type;
        $this->datafilter = $datafilter;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.filter-form');
    }
}
