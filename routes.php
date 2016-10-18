<?php


//use Illuminate\Support\Facades\Cookie;

Route::get('/info', function (\Illuminate\Http\Request $request) {
    phpinfo();
});
//
//
//
//
//    $cookies = $_COOKIE;
//    foreach($_COOKIE as $cookieName => $cookie) {
//        if($cookieName != ""){
//            echo "removing " . $cookieName;
//            Cookie::queue($cookieName, "", -10);
//
////            unset($_COOKIE[$cookieName]);
////            setcookie($cookieName, "", time()-999999, "/", ".domain");
////            setcookie(uniqid(), "", time()+10, "/", ".domain");
//
//        }
//    }
//
//});

