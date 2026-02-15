<?php
namespace App\Swagger;

/**
 * @OA\Info(
 *     title="PGoldapp API",
 *     version="1.0.0",
 *     description="Cryptocurrency Trading Platform API",
 *     contact={"email":"support@pgoldapp.com"}
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Development Server"
 * )
 * @OA\Server(
 *     url="https://pgoldapi.onrender.com/api",
 *     description="Production Server"
 * )
 */
class SwaggerDefinition
{
}
