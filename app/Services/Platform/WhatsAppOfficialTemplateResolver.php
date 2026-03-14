<?php

namespace App\Services\Platform;

use App\Models\Platform\WhatsAppOfficialTemplate;

class WhatsAppOfficialTemplateResolver
{
    public function resolveApprovedByKey(string $key): ?WhatsAppOfficialTemplate
    {
        return WhatsAppOfficialTemplate::query()
            ->officialProvider()
            ->byKey($key)
            ->approved()
            ->orderByDesc('version')
            ->first();
    }

    public function resolveMetaTemplateNameByKey(string $key): ?string
    {
        $template = $this->resolveApprovedByKey($key);
        return $template?->meta_template_name;
    }
}

