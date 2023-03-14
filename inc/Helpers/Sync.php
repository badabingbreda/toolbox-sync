<?php
namespace ToolboxSync\Helpers;

use ToolboxSync\Helpers\Sync\Diff;
use ToolboxSync\Helpers\Sync\Local;
use ToolboxSync\Helpers\Sync\Remote;
use ToolboxSync\Helpers\Sync\PostField;
use ToolboxSync\Helpers\Sync\Meta;

class Sync {

    public function __construct() {
        
        new Diff();
        new Local();
        new Remote();
        new PostField();
        new Meta();

    }

}