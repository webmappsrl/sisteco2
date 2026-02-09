<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HikingRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'cai_id','ref','geometry',
    ];

    public static function getListFromCai():array {
        // TODO: generalize for different bbox taken from catalog
        // $bbox='10.384123,43.679826,10.643226,43.847591';
        $bbox='10.3,43.5,10.7,43.9';
        $api = "https://osm2cai.cai.it/api/v2/hiking-routes/bb/$bbox/3,4";
        $list = json_decode(file_get_contents($api),TRUE);
        return $list;
    }

    public static function importFromCai($cai_id) {
        $data = json_decode(file_get_contents("https://osm2cai.cai.it/api/v2/hiking-route/$cai_id"),TRUE);
        $data = self::removeZFromGeoJSON($data);
        if(!is_null($data) && count($data)>0 && isset($data['properties']) && isset($data['geometry'])) {
            $geometry = json_encode($data['geometry']);
            return self::updateOrCreate(
                [
                    'cai_id'=> $cai_id
                ],
                [
                    'ref'=>$data['properties']['ref'],
                    'geometry'=>DB::raw("ST_GeomFromGeoJSON('{$geometry}')"),

                ]
                );
        }
    }

    public static function removeZFromGeoJSON($geoJSON) {

    // Controlla se esiste 'geometry' e se il tipo è MultiLineString
    if (isset($geoJSON['geometry']) && $geoJSON['geometry']['type'] === 'MultiLineString') {
        // Itera su ogni LineString nel MultiLineString
        foreach ($geoJSON['geometry']['coordinates'] as &$lineString) {
            // Itera su ogni coordinata del LineString
            foreach ($lineString as &$coordinate) {
                // Rimuovi la coordinata Z se esiste (assumendo che ci siano 3 elementi per coordinata)
                if (count($coordinate) === 3) {
                    array_pop($coordinate); // Rimuove l'ultimo elemento (coordinata Z)
                }
            }
        }
    }

    // Ritorna il GeoJSON modificato come stringa
    return $geoJSON;
  }


}
