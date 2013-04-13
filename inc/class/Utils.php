<?php
/**
 * Created by Antonio Laguna Matías.
 * Date: 24/11/12
 * Time: 16:24
 */

class Utils
{
    public static function getDistance($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'Km'){
        $theta = $longitude1 - $longitude2;
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2)))  +
                    (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;
        switch($unit) {
            case 'Mi':
                break;
            case 'Km' :
                $distance = $distance * 1.609344;
                break;
            case 'm' :
                $distance = $distance * 1.609344 * 1000;
                break;
        }
        return $distance;
    }
}
