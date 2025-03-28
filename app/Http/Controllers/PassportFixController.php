<?php


namespace App\Http\Controllers; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Controller;


class PassportFixController extends Controller
{
    public function fixOAuthTables()
    {
        try {
            // ✅ Check if `oauth_auth_codes` table exists
            if (!Schema::hasTable('oauth_auth_codes')) {
                DB::statement("
                    CREATE TABLE `oauth_auth_codes` (
                        `id` VARCHAR(100) PRIMARY KEY,
                        `user_id` BIGINT UNSIGNED NOT NULL,
                        `client_id` BIGINT UNSIGNED NOT NULL,
                        `scopes` TEXT NULL,
                        `revoked` TINYINT(1) NOT NULL DEFAULT 0,
                        `expires_at` DATETIME NULL
                    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
                ");
            }

            // ✅ Check if `oauth_clients` table exists
            if (!Schema::hasTable('oauth_clients')) {
                DB::statement("
                    CREATE TABLE `oauth_clients` (
                        `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        `user_id` BIGINT UNSIGNED NULL,
                        `name` VARCHAR(255) NOT NULL,
                        `secret` VARCHAR(100) NOT NULL,
                        `redirect` TEXT NOT NULL,
                        `personal_access_client` TINYINT(1) NOT NULL DEFAULT 0,
                        `password_client` TINYINT(1) NOT NULL DEFAULT 0,
                        `revoked` TINYINT(1) NOT NULL DEFAULT 0,
                        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                        `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
                ");
            }

            // ✅ Check if personal access client exists
            $personalClient = DB::table('oauth_clients')->where('personal_access_client', 1)->first();
            if (!$personalClient) {
                DB::table('oauth_clients')->insert([
                    'name' => 'Laravel Personal Access Client',
                    'secret' => bin2hex(random_bytes(40)),
                    'redirect' => 'http://localhost',
                    'personal_access_client' => 1,
                    'password_client' => 0,
                    'revoked' => 0,
                ]);
            }

            return response()->json([
                'message' => 'OAuth tables verified and fixed!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fixing OAuth tables',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
