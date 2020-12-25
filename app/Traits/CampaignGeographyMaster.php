<?php

namespace App\Traits;

use App\Models\Campaign;

trait CampaignGeographyMaster
{
    /**
     * @param Campaign $campaign
     * @return array
     */
    function getCampaignGeoMaster(Campaign $campaign): array
    {
        
        if (!empty($campaign->geo_master)) {
            $geoCampaignMaster = $campaign->geo_master;
        } else {
            $geoCampaignMaster = NULL;
        }

        $geographyMaster = [];
        if($geoCampaignMaster) {
            foreach ($geoCampaignMaster as $geoMaster) {
                $temp = [];
                $getMaster = \App\Models\GeographyMaster::where(['id' => $geoMaster['id']])->get(['id','name'])->first();

                if($geoMaster) {
                    $temp['id'] = $geoMaster['id'];
                    $temp['name'] = $getMaster->name;
                    $temp['label'] = $geoMaster['label'] ?? $getMaster->name;
    
                    array_push($geographyMaster, $temp);
                }
            }
        }

        return $geographyMaster;
    }
}