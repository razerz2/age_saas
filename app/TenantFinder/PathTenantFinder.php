<?php

namespace App\TenantFinder;

use Illuminate\Http\Request;
use Spatie\Multitenancy\TenantFinder\TenantFinder;
use App\Models\Platform\Tenant;

class PathTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?Tenant
    {
        // /t/{tenant}/login
        // segment(1) = t
        // segment(2) = {tenant}

        $subdomain = $request->segment(2);

        if (!$subdomain) {
            return null;
        }

        return Tenant::where('subdomain', $subdomain)->first();
    }
}
