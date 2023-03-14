<?php
namespace ToolboxSync;

use ToolboxSync\Rest;
use ToolboxSync\Helpers\Ajax;
use ToolboxSync\Dashboard\Dashboard;
use ToolboxSync\Helpers\Sync;

class Init {

    public function __construct() {

        new Rest();
        new Ajax();
        new Dashboard();
        new Sync();

    }
}