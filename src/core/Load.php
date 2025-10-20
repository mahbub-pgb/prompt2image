<?php
namespace Prompt2Image\Core;

class Load {
    public function init() {
        $this->load_classes();
    }

    private function load_classes() {
        new Settings();
        new Admin();
        new Ajax();
    }
}
