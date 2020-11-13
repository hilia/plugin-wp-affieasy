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

    // Generate the html table
    static function generateTable($table)
    {
        $isWithHeader = $table->isWithHeader();
        $tableContent = $table->getContent();

        $headerStyle = '';
        foreach ((array)$table->getHeaderOptions() as $key => $value) {
            if (!empty($value)) {
                $headerStyle .= (empty($headerStyle) ? '' : ';') . $key . ':' . $value;
            }
        }

        ?>
        <table>
            <?php if ($isWithHeader) { ?>
                <thead>
                <tr>
                    <?php $header = $tableContent[0];
                    for ($i = 0; $i < count($tableContent[0]); $i++) { ?>
                        <th <?php echo empty($headerStyle) ? "" : ('style="' . $headerStyle . '"') ?>>
                            <?php echo str_replace('&quot;', '"', $header[$i]->value); ?>
                        </th>
                    <?php } ?>
                </tr>
                </thead>
            <?php } ?>
            <tbody>
            <?php for ($i = $isWithHeader ? 1 : 0; $i < count($tableContent); $i++) { ?>
                <tr>
                    <?php $row = $tableContent[$i];
                    for ($j = 0; $j < count($row); $j++) {
                        $cellType = $row[$j]->type;
                        $cellValue = str_replace('&quot;', '"', $row[$j]->value);
                        if (in_array($cellType, array(Constants::HTML, Constants::IMAGE))) { ?>
                            <td><?php echo $cellValue; ?></td>
                        <?php } else if ($cellType === Constants::AFFILIATION) {
                            $affiliateLinks = json_decode($cellValue);
                            ?>
                            <td>
                                <?php foreach ($affiliateLinks as $affiliateLink) { ?>
                                    <a href="<?php echo $affiliateLink->url; ?>" class="button button-primary">
                                        <span class="dashicons dashicons-cart cell-content-link-list-icon"></span>
                                        <span><?php echo $affiliateLink->linkText; ?></span>
                                    </a>
                                <?php } ?>
                            </td>
                        <?php }
                    } ?>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php
    }
}