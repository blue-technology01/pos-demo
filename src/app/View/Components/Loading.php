<?php

namespace App\View\Components;
use Illuminate\View\Component;
class Loading extends Component {
    public string $message;
    public function __construct(string  $message = 'Loading') {
        $this->message = $message;
    }
    public function render() {
        return view('components.loading');
    }
}
