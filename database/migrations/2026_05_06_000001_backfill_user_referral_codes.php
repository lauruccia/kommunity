<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $usedCodes = DB::table('users')
            ->whereNotNull('referral_code')
            ->where('referral_code', '!=', '')
            ->pluck('referral_code')
            ->all();

        $usedCodes = array_fill_keys($usedCodes, true);

        DB::table('users')
            ->whereNull('referral_code')
            ->orWhere('referral_code', '')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->each(function (object $user) use (&$usedCodes): void {
                $base = Str::slug($user->name, '');

                if ($base === '') {
                    $base = 'membro';
                }

                $code = $base;
                $index = 2;

                while (isset($usedCodes[$code])) {
                    $code = $base.$index;
                    $index++;
                }

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'referral_code' => $code,
                        'updated_at' => now(),
                    ]);

                $usedCodes[$code] = true;
            });
    }
};
