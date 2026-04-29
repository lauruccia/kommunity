<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CacheController extends Controller
{
    private function authorizeAdmin(): void
    {
        $user = auth()->user();
        abort_unless(
            $user && $user->hasAnyRole(['super-admin', 'admin-community', 'leader-capitolo']),
            403,
            'Accesso riservato agli amministratori.'
        );
    }

    public function index()
    {
        $this->authorizeAdmin();

        return view('admin.cache');
    }

    public function clear(Request $request)
    {
        $this->authorizeAdmin();

        $results = [];

        // 1. View cache — storage/framework/views/*.php
        $viewPath = storage_path('framework/views');
        $viewFiles = File::files($viewPath);
        $deleted = 0;
        foreach ($viewFiles as $file) {
            if ($file->getExtension() === 'php') {
                File::delete($file->getPathname());
                $deleted++;
            }
        }
        $results[] = "✅ View cache: eliminati {$deleted} file compilati";

        // 2. Config cache — bootstrap/cache/config.php
        $configCache = base_path('bootstrap/cache/config.php');
        if (File::exists($configCache)) {
            File::delete($configCache);
            $results[] = '✅ Config cache: eliminata';
        } else {
            $results[] = 'ℹ️ Config cache: non presente';
        }

        // 3. Route cache — bootstrap/cache/routes-v7.php
        $routeCache = base_path('bootstrap/cache/routes-v7.php');
        if (File::exists($routeCache)) {
            File::delete($routeCache);
            $results[] = '✅ Route cache: eliminata';
        } else {
            $results[] = 'ℹ️ Route cache: non presente';
        }

        // 4. Services / packages cache
        foreach (['services.php', 'packages.php'] as $file) {
            $path = base_path("bootstrap/cache/{$file}");
            if (File::exists($path)) {
                File::delete($path);
                $results[] = "✅ Bootstrap cache ({$file}): eliminata";
            }
        }

        // 5. Application cache (cache/data — file driver)
        $cachePath = storage_path('framework/cache/data');
        if (File::isDirectory($cachePath)) {
            $cacheFiles = File::allFiles($cachePath);
            foreach ($cacheFiles as $file) {
                File::delete($file->getPathname());
            }
            $results[] = '✅ Application cache: svuotata (' . count($cacheFiles) . ' file)';
        }

        return back()->with('cache_results', $results);
    }
}
