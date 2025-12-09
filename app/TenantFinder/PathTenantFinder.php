<?php

namespace App\TenantFinder;

use Illuminate\Http\Request;
use Spatie\Multitenancy\TenantFinder\TenantFinder;
use App\Models\Platform\Tenant;

class PathTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?Tenant
    {
        // /customer/{slug}/login ou /workspace/{slug}/dashboard
        // segment(1) = customer ou workspace
        // segment(2) = {slug}

        $segment1 = $request->segment(1);
        
        // Verifica se é um dos novos prefixos comerciais
        if (!in_array($segment1, ['customer', 'workspace'])) {
            return null;
        }

        $slug = $request->segment(2);

        if (!$slug) {
            return null;
        }

        return Tenant::where('subdomain', $slug)->first();
    }
}
