<?php

/**
 * @OA\Info(
 *     title="PGoldapp API",
 *     version="1.0.0",
 *     description="Cryptocurrency Trading Platform API"
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api"
 * )
 */

/**
 * @OA\Post(
 *     path="/register",
 *     summary="Register a new user",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","email","password"},
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123")
 *         )
 *     ),
 *     @OA\Response(response=201, description="User registered successfully")
 * )
 */

/**
 * @OA\Post(
 *     path="/login",
 *     summary="Login user",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email","password"},
 *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Login successful")
 * )
 */

/**
 * @OA\Get(
 *     path="/wallet",
 *     summary="Get wallet balance",
 *     tags={"Wallet"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(response=200, description="Wallet details")
 * )
 */

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer"
 * )
 */