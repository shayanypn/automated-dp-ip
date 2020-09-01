<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ip extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "ip_start",
        "ip_end",
        "continent",
        "country",
        "stateprov",
        "district",
        "city",
        "zipcode",
        "latitude",
        "longitude",
        "geoname_id",
        "timezone_offset",
        "timezone_name",
        "weather_code",
        "isp_name",
        "as_number",
        "connection_type",
        "organization_name"
    ];
}
