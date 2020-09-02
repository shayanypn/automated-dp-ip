<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SyncStatus extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    	"type",
    	"status",
    	"file_name",
    	"url",
    	"rows",
    	"created_at",
    	"updated_at"
    ];

    public const FETCHED = 1;
    public const UNZIPPED = 2;
    public const SYNCED = 3;
}
