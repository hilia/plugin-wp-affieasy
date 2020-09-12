<?php
    require_once dirname(__DIR__) . '/classes/class-table-list.php';

    $tableList = new TableList();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Affiliation tables</h1>

    <a href="admin.php?page=affiliationTableAdmin&action=edit-table" class="page-title-action">
        Add new table
    </a>
    <a href="admin.php?page=affiliationTableAdmin&action=edit-advertising-agencies" class="page-title-action">
        Edit advertising agency ids
    </a>

    <form method="GET">
        <?php
            $tableList->prepare_items();
            $tableList->display();
        ?>
    </form>
</div>