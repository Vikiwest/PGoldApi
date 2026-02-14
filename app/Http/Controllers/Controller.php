<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="PGoldapp API",
 *     version="1.0.0",
 *     description="Cryptocurrency Trading Platform API",
 *     @OA\Contact(
 *         email="chidiolorunda@gmail.com"
 *     )
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local Development Server"
 * )
 * @OA\Server(
 *     url="https://api.pgoldapp.com/api",
 *     description="Production Server"
 * )
 * @OA\Components(
 *     @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT"
 *     )
 * )
 * @OA\PathItem(
 *     path="/",
 *     description="API Root"
 * ) 
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication endpoints"
 * )
 * @OA\Tag(
 *     name="Wallet",
 *     description="Wallet management endpoints"
 * )
 * @OA\Tag(
 *     name="Trading",
 *     description="Cryptocurrency trading endpoints"
 * )
 * @OA\Tag(
 *     name="Transactions",
 *     description="Transaction history endpoints"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}