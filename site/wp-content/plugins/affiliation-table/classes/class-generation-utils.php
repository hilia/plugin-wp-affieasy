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

    // Generate the html table
    static function generate_table($table)
    {
        $isTableWithColumnHeader = in_array($table->getHeaderType(), array('COLUMN_HEADER', 'BOTH'));
        $isTableWithRowHeader = in_array($table->getHeaderType(), array('ROW_HEADER', 'BOTH'));
        $isTableWithBothHeader = $table->getHeaderType() === 'BOTH';

        $tableContent = $table->getContent();
        $columnCount = count($table->getContent()[0]) + ($isTableWithBothHeader ? 1 : 0);

        $columnHeaderStyle = GenerationUtils::get_header_style(Constants::COLUMN, $table->getHeaderOptions());
        $rowHeaderStyle = GenerationUtils::get_header_style(Constants::ROW, $table->getHeaderOptions())
        ?>

        <div class="affieasy-table" <?php echo GenerationUtils::get_table_style($table->getMaxWidth(), $columnCount); ?>>
            <?php if ($isTableWithBothHeader) { ?>
                <div class="affieasy-table-cell" <?php echo $columnHeaderStyle; ?>></div>
            <?php } ?>

            <?php for ($i = 0; $i < count($tableContent); $i++) {
                $rowContent = $tableContent[$i];

                for ($j = 0; $j < count($rowContent); $j++) {
                    $cellContent = $rowContent[$j];
                    $cellValue = str_replace('&quot;', '"', $cellContent->value);

                    if ($cellContent->type === Constants::AFFILIATION && ($j !== 0 || !$isTableWithRowHeader)) {
                        $affiliateLinks = json_decode($cellValue);
                        $isFirst = true;
                        ?>
                        <div class="affieasy-table-cell affieasy-table-cell-links"  <?php echo GenerationUtils::get_style_to_apply(
                            $i,
                            $j,
                            $table->getBackgroundColor(),
                            $isTableWithColumnHeader,
                            $isTableWithRowHeader,
                            $columnHeaderStyle,
                            $rowHeaderStyle) ?>>
                            <?php foreach ($affiliateLinks as $affiliateLink) {?>
                                <a
                                        href="<?php echo $affiliateLink->url; ?>"
                                    <?php echo GenerationUtils::get_affiliate_link_style($affiliateLink); ?>
                                        class="affieasy-table-cell-link <?php echo $isFirst ? '' : 'affieasy-table-cell-link-with-margin';?>"
                                        rel="nofollow">
                                    <span class="dashicons dashicons-cart affieasy-table-cell-link-icon"></span>
                                    <span><?php echo $affiliateLink->linkText; ?></span>
                                </a>
                            <?php
                                $isFirst = false;
                            } ?>
                        </div>
                    <?php } else { ?>
                        <div class="affieasy-table-cell" <?php echo GenerationUtils::get_style_to_apply(
                            $i,
                            $j,
                            $table->getBackgroundColor(),
                            $isTableWithColumnHeader,
                            $isTableWithRowHeader,
                            $columnHeaderStyle,
                            $rowHeaderStyle) ?>>
                            <?php echo $cellValue; ?>
                        </div>
                    <?php }
                }
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

        return empty($backgroundColor) ? '' : 'style="background:' . $backgroundColor . '!important"';
    }
}