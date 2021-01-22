<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\Role;
use Illuminate\Support\Facades\Route;
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $routeName=Route::getFacadeRoot()->current()->uri();
        $route=explode('/', $routeName);
        //يجيبش المكرر يجيب واحد
        $routeRoutes=Role::distinct()->whereNotNull('allowed_route')->pluck('allowed_route')->toArray();
        if(auth()->check()){
if(!in_array($route[0], $routeRoutes)){
  return $next($request);
}else{
if($route[0]!=auth()->user()->roles->first()->allowed_route){
    $path=$route[0]!=auth()->user()->roles[0]->allowed_route ?$route[0].'.show_login_form' :''.auth()->user()->roles->first()->allowed_route.'.index';
return redirect()->route($path);
}else{
        return $next($request);
}
        
    }
}else{
$routeDistination=in_array($route[0], $routeRoutes)?$route[0].'.show_login_form':'frontend.show_login_form';
$path=$route[0]!=''?$routeDistination:auth()->user()->roles[0]->allowed_route.'.index';
return redirect()->route($path);
}}
}
