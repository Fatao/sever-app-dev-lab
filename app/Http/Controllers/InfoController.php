<?php

namespace App\Http\Controllers;

use App\DTO\ServerInfoDTO;
use App\DTO\ClientInfoDTO;
use App\DTO\DatabaseInfoDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    public function serverInfo()
    {
        $dto = new ServerInfoDTO(
            phpVersion: phpversion(),
            serverSoftware: $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
        );

        return response()->json($dto->toArray());
    }

    public function clientInfo(Request $request)
    {
        $dto = new ClientInfoDTO(
            ipAddress: $request->ip(),
            userAgent: $request->userAgent() ?? 'Unknown'
        );

        return response()->json($dto->toArray());
    }

    public function databaseInfo()
    {
        $connection = DB::connection();
        $pdo = $connection->getPdo();

        $dto = new DatabaseInfoDTO(
            driver: $connection->getDriverName(),
            database: $connection->getDatabaseName(),
            version: $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION)
        );

        return response()->json($dto->toArray());
    }
}