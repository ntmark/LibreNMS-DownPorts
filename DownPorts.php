<?php

namespace LibreNMS\Plugins;

class DownPorts
{
    public static function menu()
    {
        echo '<li><a href="plugin/p=DownPorts">DownPorts</a></li>';
    }//end menu()

}
/*
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default panel-condensed">
            <div class="panel-heading">
                <strong>{{ $title }}</strong>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-12">
                         {{ $device->hostname }}
                 <!-- Do you stuff here -->
                    </div>
        </div>
        </div>
    </div>
    </div>
    </div>
 */
?>
