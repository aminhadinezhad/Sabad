<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Makes an admin a super admin, or takes it back. A super admin always gets into the panel and
 * may do everything, and no other admin can edit, delete or reset them. This command, on the
 * server, is the only way in or out: the panel never shows or changes it.
 *
 *   php artisan sabad:super-admin someone@example.com            make them one
 *   php artisan sabad:super-admin someone@example.com --remove   take it back
 */
class SuperAdmin extends Command
{
    protected $signature = 'sabad:super-admin {email : the admin\'s email} {--remove : take super admin away}';

    protected $description = 'مدیر کل کردن یک ادمین (یا برداشتن آن)';

    public function handle(): int
    {
        $user = User::where('email', trim($this->argument('email')))->first();

        if (! $user) {
            $this->error('ادمینی با این ایمیل نیست. ادمین ها: '.User::orderBy('id')->pluck('email')->implode('، '));

            return self::FAILURE;
        }

        $make = ! $this->option('remove');

        if ($user->is_super_admin === $make) {
            $this->info($make ? "{$user->name} ({$user->email}) همین حالا مدیر کل است." : "{$user->name} ({$user->email}) مدیر کل نیست.");

            return self::SUCCESS;
        }

        if (! $this->confirm($make ? "{$user->name} ({$user->email}) مدیر کل شود؟" : "مدیر کل بودن {$user->name} ({$user->email}) برداشته شود؟", true)) {
            return self::FAILURE;
        }

        $user->forceFill(['is_super_admin' => $make])->save();

        $this->info($make ? "{$user->name} ({$user->email}) مدیر کل شد." : "{$user->name} ({$user->email}) دیگر مدیر کل نیست.");

        return self::SUCCESS;
    }
}
