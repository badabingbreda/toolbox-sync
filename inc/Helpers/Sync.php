<?php
namespace ToolboxSync\Helpers;

use ToolboxSync\Helpers\Sync\Diff;
use ToolboxSync\Helpers\Sync\Local;
use ToolboxSync\Helpers\Sync\Remote;
use ToolboxSync\Helpers\Sync\PostField;
use ToolboxSync\Helpers\Sync\Meta;
use ToolboxSync\Helpers\Sync\Tax;

class Sync {

    public function __construct() {
        
        new Diff();
        new Local();
        new Remote();
        new PostField();
        new Meta();
        new Tax();

    }

}