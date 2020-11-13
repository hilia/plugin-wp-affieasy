<?php

class GenerationUtils
{
    // extract background color and color from affiliate link if parameters exists and return style="color:.." or empty
    static function getAffiliateLinkStyle($affiliateLink) {
        $style = '';
        if (!empty($affiliateLink->background)) {
            $style = 'background:' . $affiliateLink->background;
        }

        if (!empty($affiliateLink->color)) {
            $style = $style . (empty($style) ? '' : ';') . 'color:' . $affiliateLink->color;
        }

        return empty($style) ? '' : 'style="' . $style . '"';
    }
}