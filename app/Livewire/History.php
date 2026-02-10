<?php

namespace App\Livewire;

use Livewire\Component;

class History extends Component
{
    public $orders;

    public function mount($orders = null)
    {
      $this->orders = $orders;
    }

    public function render()
    {
        return view('livewire.history');
    }
}
