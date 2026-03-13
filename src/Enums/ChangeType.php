<?php

declare(strict_types=1);

namespace SEOJuice\Enums;

enum ChangeType: string
{
    case InternalLink = 'internal_link';
    case MetaDescription = 'meta_description';
    case MetaKeywords = 'meta_keywords';
    case OgTitle = 'og_title';
    case OgDescription = 'og_description';
    case OgImage = 'og_image';
    case TitleTag = 'title_tag';
    case StructuredData = 'structured_data';
    case ImageAlt = 'image_alt';
    case Accessibility = 'accessibility';
    case LocalSchema = 'local_schema';
    case NapFix = 'nap_fix';
}
