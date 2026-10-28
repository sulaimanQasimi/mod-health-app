<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        $hosts = [
            $this->allSubdomainsOfApplicationUrl(),
            ...config('security.trusted_hosts', []),
        ];

        // Keep local/dev access working on common loopback hosts.
        if (app()->environment(['local', 'testing'])) {
            $hosts[] = 'localhost';
            $hosts[] = '127.0.0.1';
            $hosts[] = '\[::1\]';
        }

        return array_values(array_filter($hosts));
    }
}
