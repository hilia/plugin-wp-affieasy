<?php

wp_enqueue_style(
    'edit-advertising-agencies-style',
    plugins_url('/affiliation-table/css/edit-advertising-agencies.css'),
    array(),
    time());

$dbManager = new DbManager();
$advertisingAgencies = $dbManager->get_advertising_agencies();

$isUpdate = false;

if (isset($_POST['submit'])) {
    $isUpdate = true;

    foreach ($advertisingAgencies as $advertisingAgency) {
        $advertisingAgency->setValue($_POST[$advertisingAgency->getName()]);
    }

    $dbManager->save_advertising_agency_ids($advertisingAgencies);
}

?>

<div class="wrap">
    <h1>Edit advertising agency ids</h1>
    <?php if ($isUpdate) { ?>
        <div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible">
            <p><strong>Advertising agency ids saved</strong></p>
            <button type="button" class="notice-dismiss"></button>
        </div>
    <?php } ?>
    <form class="validate" method="post">
        <table class="form-table" role="presentation">
            <?php foreach ($advertisingAgencies as $advertisingAgency) {
                $name = $advertisingAgency->getName();
                ?>
                <tr class="form-field">
                    <th scope="row">
                        <label for="<?php echo $name ?>">
                            <?php echo $advertisingAgency->getLabel() ?>
                        </label>
                    </th>
                    <td>
                        <input
                                type="text"
                                name="<?php echo $name ?>"
                                id="<?php echo $name ?>"
                                class="advertising-agency-id-input"
                                maxlength="255"
                                value="<?php echo $advertisingAgency->getValue() ?>">
                    </td>
                </tr>
            <?php } ?>
        </table>
        <p class="submit">
            <button
                    type="submit"
                    name="submit"
                    id="submit"
                    class="button button-primary"
                    value="edit-advertising-agencies">
                Save advertising agency ids
            </button>
        </p>
    </form>
</div>