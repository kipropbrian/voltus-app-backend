<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ImageDetect extends Component
{

    public $image;

    /**
     * Format URL into c_thub, g_faces cloudinary transformation
     *
     */
    public $formatedImageUrl;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($image)
    {
        //
        $this->image = $image;
        $this->formatedImageUrl = $this->formatImageUrl();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.image-detect');
    }

    public function formatImageUrl(){
        $url = parse_url($this->image->image_url_secure);
        return $url['scheme'] . "://" . $url['host'] . "/voltus/image/upload/w_200,h_200,c_thumb,g_faces/" . $this->image->publicId . ".jpg" ;
    }
}
