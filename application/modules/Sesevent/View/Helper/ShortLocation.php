<?php
class Sesevent_View_Helper_ShortLocation {
    function shortLocation($location){
        $splitLocation = explode(",",$location);
        $locationLength = count($splitLocation);
        if(count($splitLocation) === 1){
          return trim($splitLocation[0]);
        } else {
          return trim($splitLocation[$locationLength - 2]) . ", " . trim($splitLocation[$locationLength-1]);
        }
    }
}