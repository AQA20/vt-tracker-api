<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "VT Tracker API",
    description: "API documentation for VT Tracker Application",
    contact: new OA\Contact(email: "admin@vttracker.com")
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "sanctum",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
abstract class Controller
{
    //
}
