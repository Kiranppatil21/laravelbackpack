<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\User;
use App\Mail\AgencyCredentials;
use Illuminate\Support\Facades\Mail;

class AgencyUserProvisioner
{
    /**
     * Provision or sync a User record for an Agency and send credentials email.
     */
    public static function provision(Agency $agency, ?string $password, bool $sendVerification = false): ?User
    {
        try {
            if (! $agency->email) {
                return null;
            }

            $user = User::firstOrNew(['email' => $agency->email]);
            $user->name = $agency->contact_person_name ?: $agency->name;
            $user->email = $agency->email;
            $user->tenant_id = $agency->tenant_id ?? null;

            if ($password) {
                $user->password = $password; // hashed in User model
            }

            $user->save();

            if (method_exists($user, 'assignRole')) {
                try {
                    $user->assignRole('Agency Owner');
                } catch (\Throwable $e) {
                    // ignore if role missing
                }
            }

            // Send credentials email if password provided
            if ($password) {
                try {
                    Mail::to($user->email)->send(new AgencyCredentials($user, $password));
                } catch (\Throwable $e) {
                    if (function_exists('logger')) {
                        logger()->error('Failed to send agency credentials email: '.$e->getMessage());
                    }
                }
            }

            // Optionally send email verification
            if ($sendVerification && method_exists($user, 'sendEmailVerificationNotification')) {
                try {
                    $user->sendEmailVerificationNotification();
                } catch (\Throwable $e) {
                    if (function_exists('logger')) {
                        logger()->error('Failed to send email verification: '.$e->getMessage());
                    }
                }
            }

            return $user;
        } catch (\Throwable $e) {
            if (function_exists('logger')) {
                logger()->error('AgencyUserProvisioner error: '.$e->getMessage());
            }
            return null;
        }
    }
}
