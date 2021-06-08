<?php

namespace Andiwijaya\WebApp\Services;

use Illuminate\Support\Facades\DB;
use Ixudra\Curl\Facades\Curl;

class GoogleMapService {

  public function reverseGeocode($lat, $lng){

    $lat = round($lat, 8);
    $lng = round($lng, 8);

    $row = DB::table('google_maps_reverse_geocode')->where([
      'latitude'=>$lat,
      'longitude'=>$lng
    ])
      ->first();

    if(!$row){

      $response = Curl::to("https://maps.googleapis.com/maps/api/geocode/json?latlng={$lat},{$lng}&key=" . env('GOOGLE_MAPS_API'))
        ->asJson(true)
        ->get();

      DB::table('google_maps_reverse_geocode')
        ->insert([
          'latitude'=>$lat,
          'longitude'=>$lng,
          'data'=>json_encode($response)
        ]);
    }
    else
      $response = json_decode($row->data, 1);

    $street_address = null;
    foreach($response['results'] ?? [] as $result){
      if($street_address == null && in_array('street_address', $result['types'] ?? [])){
        $street_address = $result;
      }
    }

    $location = [
      'latitude'=>$lat,
      'longitude'=>$lng
    ];
    if($street_address != null){
      foreach($street_address['address_components'] ?? [] as $component){

        if(in_array('administrative_area_level_4', $component['types'] ?? []))
          $location['village'] = $location['kelurahan'] = $component['long_name'];
        else if(in_array('administrative_area_level_3', $component['types'] ?? []))
          $location['district'] = $location['kecamatan'] = $component['long_name'];
        else if(in_array('administrative_area_level_2', $component['types'] ?? []))
          $location['city'] = $component['long_name'];
        else if(in_array('administrative_area_level_1', $component['types'] ?? []))
          $location['province'] = $component['long_name'];
        else if(in_array('route', $component['types'] ?? []))
          $location['street_name'] = $component['long_name'];
        else if(in_array('street_number', $component['types'] ?? []))
          $location['street_number'] = $component['long_name'];
        else if(in_array('postal_code', $component['types'] ?? []))
          $location['postal_code'] = $component['long_name'];
      }
      $location['address'] = $street_address['formatted_address'];
    }

    return $location;
  }

}