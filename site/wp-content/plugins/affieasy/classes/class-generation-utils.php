<?php

class GenerationUtils
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
            GenerationUtils::get_header_style(Constants::COLUMN, $table->getHeaderOptions()) :
            null;

        $rowHeaderStyle = $isTableWithRowHeader ?
            GenerationUtils::get_header_style(Constants::ROW, $table->getHeaderOptions()) :
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
        GenerationUtils::generate_main_table(
            $table,
            $isTableWithBothHeader,
            $isTableWithRowHeader,
            $isTableWithColumnHeader,
            $columnHeaderStyle,
            $rowHeaderStyle,
            $columnCount);
        if ($isResponsiveTable) {
            GenerationUtils::generate_responsive_table($table, $columnHeaderStyle, $rowHeaderStyle);
        }
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
            <?php echo GenerationUtils::get_table_style($table->getMaxWidth(), $columnCount); ?>>
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
                    $cellValue = isset($cellContent->value) ? str_replace('&quot;', '"', $cellContent->value) : null;

                    if ($cellType === Constants::AFFILIATION && ($j !== 0 || !$isTableWithRowHeader)) {
                        GenerationUtils::generate_affiliate_links_cell_content($cellValue, GenerationUtils::get_background_color_style_or_empty($backgroundColor), false);
                        ?>
                    <?php } else { ?>
                        <div class="affieasy-table-cell" <?php echo GenerationUtils::get_style_to_apply(
                            $i,
                            $j,
                            $backgroundColor,
                            $isTableWithColumnHeader,
                            $isTableWithRowHeader,
                            $columnHeaderStyle,
                            $rowHeaderStyle) ?>>
                            <?php echo $cellValue; ?>
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
                GenerationUtils::generate_responsive_table_row($table, $rowHeaderStyle);
                break;
            case 'BOTH':
                GenerationUtils::generate_responsive_table_both($table, $columnHeaderStyle, $rowHeaderStyle);
                break;
            default :
                GenerationUtils::generate_responsive_table_column_or_none($table, $columnHeaderStyle);
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
                        <?php echo str_replace('&quot;', '"', $tableContent[$j][0]->value); ?>
                    </div>

                    <?php
                    $cellContent = $tableContent[$j][$i];
                    $cellType = isset($cellContent->type) ? $cellContent->type : null;
                    $cellValue = isset($cellContent->value) ? str_replace('&quot;', '"', $cellContent->value) : null;
                    $backgroundColor = GenerationUtils::get_background_color_style_or_empty($table->getBackgroundColor());

                    if ($cellType === Constants::AFFILIATION) {
                        GenerationUtils::generate_affiliate_links_cell_content($cellValue, $backgroundColor, true);
                    } else { ?>
                        <div class="affieasy-table-cell affieasy-table-responsive-both-row-content" <?php echo $backgroundColor; ?>>
                            <?php echo str_replace('&quot;', '"', $tableContent[$j][$i]->value); ?>
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
                    <?php echo str_replace('&quot;', '"', $tableContent[0][$i - 1]->value); ?>
                </div>

                <?php for ($j = 1; $j < count($tableContent); $j++) { ?>
                    <div class="affieasy-table-cell affieasy-table-responsive-both-row-header" <?php echo $rowHeaderStyle; ?>>
                        <?php echo str_replace('&quot;', '"', $tableContent[$j][0]->value); ?>
                    </div>

                    <?php
                    $cellContent = $tableContent[$j][$i];
                    $cellType = isset($cellContent->type) ? $cellContent->type : null;
                    $cellValue = isset($cellContent->value) ? str_replace('&quot;', '"', $cellContent->value) : null;
                    $backgroundColor = GenerationUtils::get_background_color_style_or_empty($table->getBackgroundColor());

                    if ($cellType === Constants::AFFILIATION) {
                        GenerationUtils::generate_affiliate_links_cell_content($cellValue, $backgroundColor, true);
                    } else { ?>
                        <div
                                class="affieasy-table-cell affieasy-table-responsive-both-row-content"
                            <?php echo GenerationUtils::get_background_color_style_or_empty($table->getBackgroundColor()); ?>>
                            <?php echo str_replace('&quot;', '"', $tableContent[$j][$i]->value); ?>
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
                        <?php echo str_replace('&quot;', '"', $tableContent[0][$i]->value); ?>
                    </div>
                <?php }

                for ($j = ($isColumnTable ? 1 : 0); $j < count($tableContent); $j++) {
                    $cellContent = $tableContent[$j][$i];
                    $cellType = isset($cellContent->type) ? $cellContent->type : null;
                    $cellValue = isset($cellContent->value) ? str_replace('&quot;', '"', $cellContent->value) : null;
                    $backgroundColor = GenerationUtils::get_background_color_style_or_empty($table->getBackgroundColor());

                    if ($cellType === Constants::AFFILIATION) {
                        GenerationUtils::generate_affiliate_links_cell_content($cellValue, $backgroundColor, false);
                    } else {
                        ?>
                        <div class="affieasy-table-cell" <?php echo $backgroundColor; ?>>
                            <?php echo str_replace('&quot;', '"', $cellValue); ?>
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
                <a
                        href="<?php echo $affiliateLink->url; ?>"
                    <?php echo GenerationUtils::get_affiliate_link_style($affiliateLink); ?>
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

        return GenerationUtils::get_background_color_style_or_empty($backgroundColor);
    }

    private static function get_background_color_style_or_empty($backgroundColor)
    {
        return empty($backgroundColor) ? '' : 'style="background:' . $backgroundColor . '!important"';
    }
}