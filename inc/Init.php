<?php
namespace ToolboxSync;

use ToolboxSync\Rest;
use ToolboxSync\Helpers\Ajax;
use ToolboxSync\Dashboard\Dashboard;

class Init {

    public function __construct() {

        new Rest();
        new Ajax();
        new Dashboard();

    }
}