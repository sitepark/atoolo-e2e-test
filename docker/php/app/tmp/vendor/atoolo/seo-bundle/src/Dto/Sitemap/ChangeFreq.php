<?php

declare(strict_types=1);

namespace Atoolo\Seo\Dto\Sitemap;

/**
 * https://www.sitemaps.org/protocol.html
 */
enum ChangeFreq: string
{
    case ALWAYS = 'always';
    case HOURLY = 'hourly';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case NEVER = 'never';
}
