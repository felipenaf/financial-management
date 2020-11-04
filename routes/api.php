<?php

use App\Http\Requests\UserRequest;
use App\Produto;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::get('/login', 'UserController@login');

Route::post('login', function (UserRequest $request) {
    $validator = Validator::make($request->only('email', 'password'), $request->loginRules());

    if ($validator->fails()) {
        return response([
            'code' => Response::HTTP_BAD_REQUEST,
            'data' => $validator->errors()
        ], Response::HTTP_BAD_REQUEST);

    }

    $token = JWTAuth::attempt($request->only('email', 'password'));

    if ($token === false) {
        return response([
            'status' => Response::HTTP_NOT_FOUND,
            'error' => 'NOT FOUND.'
        ], Response::HTTP_NOT_FOUND);
    }

    return response([
        'status' => Response::HTTP_OK,
        'data' => [
            'token' => $token,
        ]
    ]);

});

Route::apiResource('users', UserController::class);

Route::middleware(['jwt.auth'])->group(function () {
    Route::apiResources([
        'grupos' => 'GrupoController',
        'produtos' => 'ProdutoController',
    ]);

});

################################################################################

Route::get('/mutators', function () {
    $product = Produto::find(1);

    $desc1 = $product->descricao;
    $product->descricao = "PÃOZINHO";
    $desc2 = $product->descricao;

    return response([
        'product01' => $desc1,
        'product02' => $desc2
    ]);

});

Route::get('/factory', function () {
    // Esse apenas cria os objetos
    $products = Produto::factory()->count(5)->make();
    // Esse além de criar salva na base
    // $products = factory(Produto::class, 2)->create();

    // Esse além de salvar sobrescreve algum atributo
    // $products = factory(Produto::class, 5)->create([
    //     'descricao' => 'bolacha'
    // ]);

    return response([
        'produtos' => $products
    ]);

});

Route::get('notifications/', function () {
    $notifications = User::find(1)->unreadNotifications;

    $datas = [];
    foreach ($notifications as $not) {
        array_push($datas, [$not->id, $not->data]);
    }

    return $datas;
});

Route::put('notifications/{id}', function (Request $request, $id) {
    User::find($id)->unreadNotifications
        ->where('id', $request->id)->markAsRead();

    return User::find($id)->unreadNotifications;
});
