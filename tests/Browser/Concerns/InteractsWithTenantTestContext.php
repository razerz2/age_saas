<?php

namespace Tests\Browser\Concerns;

use Tests\Browser\Support\TenantTestContext;

trait InteractsWithTenantTestContext
{
    private ?TenantTestContext $tenantTestContext = null;

    protected function tenantTestContext(): TenantTestContext
    {
        return $this->tenantTestContext ??= TenantTestContext::fromEnvironment();
    }
}
