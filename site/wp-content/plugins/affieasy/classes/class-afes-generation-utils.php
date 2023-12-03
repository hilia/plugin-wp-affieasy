<?php

namespace affieasy;

class AFES_GenerationUtils
{
    // extract background color and color from affiliate link if parameters exists and return style="color:.." or empty
    static function get_affiliate_link_style($affiliateLink)
    {
        $style = '';
        if (!empty($affiliateLink->background)) {
            $style = 'background:' . $affiliateLink->background;
        }

        if (!empty($affiliateLink->color)) {
            $style = $style . (empty($style) ? '' : ';') . 'color:' . $affiliateLink->color;
        }

        return empty($style) ? '' : 'style="' . $style . '"';
    }

    // Generate tables (responsive and not)
    static function generate_table($table)
    {
        $tableId = $table->getId();
        $isTableWithColumnHeader = in_array($table->getHeaderType(), array('COLUMN_HEADER', 'BOTH'));
        $isTableWithRowHeader = in_array($table->getHeaderType(), array('ROW_HEADER', 'BOTH'));
        $isTableWithBothHeader = $table->getHeaderType() === 'BOTH';

        $columnCount = count($table->getContent()[0]) + ($isTableWithBothHeader ? 1 : 0);

        $columnHeaderStyle = $isTableWithColumnHeader ?
            AFES_GenerationUtils::get_header_style(AFES_Constants::COLUMN, $table->getHeaderOptions()) :
            null;

        $rowHeaderStyle = $isTableWithRowHeader ?
            AFES_GenerationUtils::get_header_style(AFES_Constants::ROW, $table->getHeaderOptions()) :
            null;

        $responsiveBreakpoint = $table->getResponsiveBreakpoint();
        $isResponsiveTable = is_numeric($responsiveBreakpoint) && $responsiveBreakpoint > 0;
        if ($isResponsiveTable) { ?>
            <style>
                @media screen and (max-width: <?php echo $responsiveBreakpoint; ?>px) {
                    #affieasy-table-<?php echo $tableId; ?> {
                        display: none !important;
                    }
                }

                @media screen and (min-width: <?php echo $responsiveBreakpoint + 1; ?>px) {
                    #affieasy-table-responsive-<?php echo $tableId; ?> {
                        display: none !important;
                    }
                }
            </style>
        <?php }
        AFES_GenerationUtils::generate_main_table(
            $table,
            $isTableWithBothHeader,
            $isTableWithRowHeader,
            $isTableWithColumnHeader,
            $columnHeaderStyle,
            $rowHeaderStyle,
            $columnCount);
        if ($isResponsiveTable) {
            AFES_GenerationUtils::generate_responsive_table($table, $columnHeaderStyle, $rowHeaderStyle);
        }
    }

    // Generate link
    static function generate_link($link)
    {
        if (isset($link) && is_numeric($link->getId())) { ?>

            <?php // Ancien lien : ?>
            <a href="<?php echo '/?' . AFES_Constants::SHORT_LINK_SLUG . '=' . $link->getId(); ?>" <?php echo $link->isNoFollow() === "1" ? 'rel=nofollow' : ''; ?> <?php echo $link->isOpenInNewTab() ? 'target="_blank"' : ''; ?>><?php echo sanitize_text_field($link->getLabel()); ?></a>
            <?php /*
            <hr />
            Nouveau lien : 
            <a href="<?php echo AFES_Constants::LINK_REGIE . '' . $link->getUrl(); ?>" <?php echo $link->isNoFollow() === "1" ? 'rel=nofollow' : ''; ?> <?php echo $link->isOpenInNewTab() ? 'target="_blank"' : ''; ?>><?php echo sanitize_text_field($link->getLabel()); ?></a>
            */?>
        <?php }
    }

    private static function generate_main_table(
        $table,
        $isTableWithBothHeader,
        $isTableWithRowHeader,
        $isTableWithColumnHeader,
        $columnHeaderStyle,
        $rowHeaderStyle,
        $columnCount)
    { ?>
        <div
                id="affieasy-table-<?php echo $table->getId(); ?>"
                class="affieasy-table"
            <?php echo AFES_GenerationUtils::get_table_style($table->getMaxWidth(), $columnCount); ?>>
            <?php if ($isTableWithBothHeader) { ?>
                <div class="affieasy-table-cell" <?php echo $columnHeaderStyle; ?>></div>
            <?php } ?>

            <?php
            $backgroundColor = $table->getBackgroundColor();
            for ($i = 0; $i < count($table->getContent()); $i++) {
                $rowContent = $table->getContent()[$i];

                for ($j = 0; $j < count($rowContent); $j++) {
                    $cellContent = $rowContent[$j];
                    $cellType = isset($cellContent->type) ? $cellContent->type : null;
                    $cellValue = isset($cellContent->value) ? AFES_GenerationUtils::format_html_content($cellContent->value) : null;

                    if ($cellType === AFES_Constants::AFFILIATION && ($j !== 0 || !$isTableWithRowHeader)) {
                        AFES_GenerationUtils::generate_affiliate_links_cell_content($cellValue, AFES_GenerationUtils::get_background_color_style_or_empty($backgroundColor), false);
                        ?>
                    <?php } else { ?>
                        <div class="affieasy-table-cell" <?php echo AFES_GenerationUtils::get_style_to_apply(
                            $i,
                            $j,
                            $backgroundColor,
                            $isTableWithColumnHeader,
                            $isTableWithRowHeader,
                            $columnHeaderStyle,
                            $rowHeaderStyle) ?>>
                            <?php echo str_replace('&amp;NewLine;', '', $cellValue); ?>
                        </div>
                    <?php }
                }
            } ?>
        </div> <?php
    }

    private static function generate_responsive_table($table, $columnHeaderStyle, $rowHeaderStyle)
    {
        switch ($table->getHeaderType()) {
            case 'ROW_HEADER':
                AFES_GenerationUtils::generate_responsive_table_row($table, $rowHeaderStyle);
                break;
            case 'BOTH':
                AFES_GenerationUtils::generate_responsive_table_both($table, $columnHeaderStyle, $rowHeaderStyle);
                break;
            default :
                AFES_GenerationUtils::generate_responsive_table_column_or_none($table, $columnHeaderStyle);
                break;
        }
    }

    private static function generate_responsive_table_row($table, $rowHeaderStyle)
    {
        $tableContent = $table->getContent();
        ?>
        <div id="affieasy-table-responsive-<?php echo $table->getId(); ?>" class="affieasy-table-responsive-row">
            <?php for ($i = 1; $i < count($tableContent[0]); $i++) {
                for ($j = 0; $j < count($tableContent); $j++) { ?>
                    <div class="affieasy-table-cell affieasy-table-responsive-both-row-header" <?php echo $rowHeaderStyle; ?>>
                        <?php echo AFES_GenerationUtils::format_html_content($tableContent[$j][0]->value); ?>
                    </div>

                    <?php
                    $cellContent = $tableContent[$j][$i];
                    $cellType = isset($cellContent->type) ? $cellContent->type : null;
                    $cellValue = isset($cellContent->value) ? AFES_GenerationUtils::format_html_content($cellContent->value) : null;
                    $backgroundColor = AFES_GenerationUtils::get_background_color_style_or_empty($table->getBackgroundColor());

                    if ($cellType === AFES_Constants::AFFILIATION) {
                        AFES_GenerationUtils::generate_affiliate_links_cell_content($cellValue, $backgroundColor, true);
                    } else { ?>
                        <div class="affieasy-table-cell affieasy-table-responsive-both-row-content" <?php echo $backgroundColor; ?>>
                            <?php echo $cellValue ?>
                        </div>
                    <?php }
                }
            } ?>
        </div>
        <?php
    }

    private static function generate_responsive_table_both($table, $columnHeaderStyle, $rowHeaderStyle)
    {
        $tableContent = $table->getContent();
        ?>
        <div id="affieasy-table-responsive-<?php echo $table->getId(); ?>" class="affieasy-table-responsive-both">
            <?php for ($i = 1; $i < count($tableContent[1]); $i++) { ?>
                <div class="affieasy-table-cell affieasy-table-responsive-both-title" <?php echo $columnHeaderStyle; ?>>
                    <?php echo AFES_GenerationUtils::format_html_content($tableContent[0][$i - 1]->value); ?>
                </div>

                <?php for ($j = 1; $j < count($tableContent); $j++) { ?>
                    <div class="affieasy-table-cell affieasy-table-responsive-both-row-header" <?php echo $rowHeaderStyle; ?>>
                        <?php echo AFES_GenerationUtils::format_html_content($tableContent[$j][0]->value); ?>
                    </div>

                    <?php
                    $cellContent = $tableContent[$j][$i];
                    $cellType = isset($cellContent->type) ? $cellContent->type : null;
                    $cellValue = isset($cellContent->value) ? AFES_GenerationUtils::format_html_content($cellContent->value) : null;
                    $backgroundColor = AFES_GenerationUtils::get_background_color_style_or_empty($table->getBackgroundColor());

                    if ($cellType === AFES_Constants::AFFILIATION) {
                        AFES_GenerationUtils::generate_affiliate_links_cell_content($cellValue, $backgroundColor, true);
                    } else { ?>
                        <div
                                class="affieasy-table-cell affieasy-table-responsive-both-row-content"
                            <?php echo AFES_GenerationUtils::get_background_color_style_or_empty($table->getBackgroundColor()); ?>>
                            <?php echo $cellValue; ?>
                        </div>
                    <?php } ?>
                <?php }
            } ?>
        </div>
        <?php
    }

    private static function generate_responsive_table_column_or_none($table, $columnHeaderStyle)
    {
        $tableContent = $table->getContent();
        $isColumnTable = $table->getHeaderType() === 'COLUMN_HEADER';
        ?>
        <div id="affieasy-table-responsive-<?php echo $table->getId(); ?>"
             class="affieasy-table-responsive-column-none">
            <?php for ($i = 0; $i < count($tableContent[0]); $i++) {
                if ($isColumnTable) { ?>
                    <div class="affieasy-table-cell" <?php echo $columnHeaderStyle; ?>>
                        <?php echo AFES_GenerationUtils::format_html_content($tableContent[0][$i]->value); ?>
                    </div>
                <?php }

                for ($j = ($isColumnTable ? 1 : 0); $j < count($tableContent); $j++) {
                    $cellContent = $tableContent[$j][$i];
                    $cellType = isset($cellContent->type) ? $cellContent->type : null;
                    $cellValue = isset($cellContent->value) ? AFES_GenerationUtils::format_html_content($cellContent->value) : null;
                    $backgroundColor = AFES_GenerationUtils::get_background_color_style_or_empty($table->getBackgroundColor());

                    if ($cellType === AFES_Constants::AFFILIATION) {
                        AFES_GenerationUtils::generate_affiliate_links_cell_content($cellValue, $backgroundColor, false);
                    } else {
                        ?>
                        <div class="affieasy-table-cell" <?php echo $backgroundColor; ?>>
                            <?php echo $cellValue; ?>
                        </div>
                    <?php }
                }
            } ?>
        </div>
        <?php
    }

    private static function generate_affiliate_links_cell_content($cellValue, $backgroundColor, $forBothOrRow)
    {
        $affiliateLinks = json_decode($cellValue);
        $isFirst = true;
        ?>
        <div class="affieasy-table-cell affieasy-table-cell-links <?php echo $forBothOrRow ? 'affieasy-table-responsive-both-row-content' : '' ?>" <?php echo $backgroundColor; ?>>
            <?php foreach ($affiliateLinks as $affiliateLink) { ?>

                <?php 
                $urlAffiliateLink = $affiliateLink->url;
                // W-prog encoder url si check dans boutique
                $dbManager = new AFES_DbManager();
                $webshop = $dbManager->get_webshop_by_id($affiliateLink->webshopId);
                $encodeUrl = $webshop->getEncodeUrl();
                if ($encodeUrl=="1"){
                    $urlAffiliateLink = str_replace($affiliateLink->product_url, urlencode($affiliateLink->product_url), $urlAffiliateLink );
                }
                // Fin w-prog
                ?>

                <a href="<?php echo $urlAffiliateLink ?>"
                    <?php echo AFES_GenerationUtils::get_affiliate_link_style($affiliateLink); ?>
                        class="affieasy-table-cell-link <?php echo $isFirst ? '' : 'affieasy-table-cell-link-with-margin'; ?>"
                        target="_blank"
                        rel="nofollow">
                    <span class="dashicons dashicons-cart affieasy-table-cell-link-icon"></span>
                    <span class="affieasy-table-cell-link-text"><?php echo $affiliateLink->linkText; ?></span>
                </a>
                <?php
                $isFirst = false;
            } ?>
        </div>
        <?php
    }

    private static function get_table_style($maxWidth, $columnCount)
    {
        $style = '';
        if (is_numeric($maxWidth) && $maxWidth > 0) {
            $style = 'max-width: ' . $maxWidth . 'px!important;';
        }

        return 'style="' . $style . 'grid-template-columns: repeat(' . $columnCount . ', auto);"';
    }

    private static function get_header_style($headerType, $headerOptions)
    {
        $headerStyle = '';

        foreach ((array)$headerOptions as $key => $value) {
            if (!empty($value) && preg_match('#^' . $headerType . '#i', $key) === 1) {
                $headerStyle .= (empty($headerStyle) ? '' : ';') . str_replace(strtolower($headerType) . '-', '', $key) . ':' . $value;
            }
        }

        return $headerStyle === '' ? '' : 'style="' . $headerStyle . '"';
    }

    private static function get_style_to_apply(
        $i,
        $j,
        $backgroundColor,
        $isTableWithColumnHeader,
        $isTableWithRowHeader,
        $columnHeaderStyle,
        $rowHeaderStyle)
    {
        if ($isTableWithColumnHeader && $i === 0) {
            return $columnHeaderStyle;
        }

        if ($j === 0 && ($isTableWithRowHeader && $i !== 0 || $isTableWithRowHeader && !$isTableWithColumnHeader)) {
            return $rowHeaderStyle;
        }

        return AFES_GenerationUtils::get_background_color_style_or_empty($backgroundColor);
    }

    private static function get_background_color_style_or_empty($backgroundColor)
    {
        return empty($backgroundColor) ? '' : 'style="background:' . $backgroundColor . '!important"';
    }

    private static function format_html_content($content)
    {
        return AFES_GenerationUtils::replace_placeholders(str_replace('&amp;NewLine;', '', str_replace('&quot;', '"', $content)));
    }

    private static function replace_placeholders($content)
    {
        foreach (AFES_Constants::AVAILABLE_ICONS as $key => $value) {
            $content = str_replace($key, '<span class="dashicons dashicons-' . $value . '"></span>', $content);
        }

        return $content;
    }
}