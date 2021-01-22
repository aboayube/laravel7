<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
        protected $guarded = [];
        public function permission(){
        	return $this->belongsToMany(permission::class,'permission_id');
        }
        public function user(){
        	return $this->belongsToMany(permission::class,'id','user_id');
        }
}
