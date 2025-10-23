<?php

namespace App\View\Components\Backend\Setting;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ImageUpload extends Component
{
    public $title;
    public $name;
    public $imageUrl;
    public $description;
    /**
     * Create a new component instance.
     */
    public function __construct($title, $name, $imageUrl, $description = '')
    {
        $this->title = $title;
        $this->name = $name;
        $this->imageUrl = $imageUrl;
        $this->description = $description;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.backend.setting.image-upload');
    }
}
