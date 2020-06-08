<style>
    .dashboard-row {
        margin-top: 4rem;
    }
</style>

<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <h1 class="header center orange-text">Dashboard</h1>
        <hr style="margin-bottom: 2rem">

        <?php if ($this->RCMS->Login->isAdmin()): ?>
            <div class="row">
                <div class="col s12">
                    <a class="btn" href="/createtool">Opret værktøj</a>
                    <a class="btn" href="/createcategory">Opret kategori</a>
                    <a class="btn" href="/createmanufacturer">Opret producent</a>

                    <p style="margin: 0" class="right">Velkommen, <?= $this->RCMS->Login->getFirstName() ?><br>Du er administrator</p>
                </div>
            </div>

            <div class="row dashboard-row">
                <h3>Værktøj</h3>

                <?php
                $columns = array(
                    array(
                        'column' => 'ToolID',
                        'label' => 'ID'
                    ),
                    array(
                        'column' => "Image",
                        'label' => "Billede",
                        'function' => 'showToolImage'
                    ),
                    array(
                        'column' => "ToolName",
                        'label' => "Navn"
                    ),
                    array(
                        'column' => "ManufacturerName",
                        'label' => "Producent"
                    ),
                    array(
                        'column' => "Status",
                        'label' => "Status",
                        'function' => 'formatStatus'
                    ),
                    array(
                        'column' => "Description",
                        'label' => "Beskrivelse"
                    ),
                    array(
                        'column' => "Barcode",
                        'label' => "Stregkode",
                        'function' => 'showToolBarcode'
                    )
                );

                $order = "ORDER BY ToolID DESC";
                $settings = array('searchbar' => true);

                $buttons = array(
                    array(
                        "button" => '<input type="button" class="btn" onclick="location.pathname = \'/edittoolQMARKtoolid=?\'" value="Rediger værktøj" />',
                        "value" => "ToolID"
                    )
                );

                $GLOBALS['RCMSTables']->createRCMSTable("tools_table", "Tools p1 LEFT JOIN Manufacturers p2 ON p1.FK_ManufacturerID = p2.ManufacturerID", $columns, $settings, null, $order, $buttons, null);
                ?>
            </div>

            <div class="row dashboard-row">
                <h3>Brugere</h3>

                <?php
                $columns = array(
                    array(
                        'column' => 'UserID',
                        'label' => 'ID'
                    ),
                    array(
                        'column' => "FirstName",
                        'label' => "Fornavn"
                    ),
                    array(
                        'column' => "LastName",
                        'label' => "Efternavn"
                    ),
                    array(
                        'column' => "Phone",
                        'label' => "Tlf. nr."
                    ),
                    array(
                        'column' => "ZipCode",
                        'label' => "Postnr."
                    ),
                    array(
                        'column' => "City",
                        'label' => "By"
                    ),
                    array(
                        'column' => "Level",
                        'label' => "Niveau",
                        'function' => 'formatUserLevel'
                    )
                );

                $order = "ORDER BY UserID DESC";
                $settings = array('searchbar' => true);

                $buttons = array(
                    array(
                        "button" => '<input type="button" class="btn" onclick="location.pathname = \'/edituserQMARKuserid=?\'" value="Rediger bruger" />',
                        "value" => "UserID"
                    )
                );

                $GLOBALS['RCMSTables']->createRCMSTable("users_table", "Users", $columns, $settings, null, $order, $buttons, null);
                ?>

                <script src="<?= $this->RCMS->getTemplateFolder() ?>/js/dashboard/dashboard.js"></script>
            </div>

            <div class="row dashboard-row">
                <h3>Kategorier</h3>

                <?php
                $columns = array(
                    array(
                        'column' => 'CategoryID',
                        'label' => 'ID',
                        'prefix' => 'p1.CategoryID AS'
                    ),
                    array(
                        'column' => "CategoryName",
                        'label' => "Navn",
                        'prefix' => 'p1.CategoryName AS'
                    ),
                    array(
                        'column' => "toolCount",
                        'prefix' => 'COUNT(p2.FK_CategoryID) AS ',
                        'label' => "Antal værktøj"
                    )
                );

                $order = "ORDER BY p1.CategoryID DESC";
                $settings = array('searchbar' => true);

                $buttons = array(
                    array(
                        "button" => '<input type="button" class="btn" onclick="location.pathname = \'/editcategoryQMARKcategoryid=?\'" value="Rediger kategori" />',
                        "value" => "CategoryID"
                    )
                );

                $GLOBALS['RCMSTables']->createRCMSTable("categories_table", "Categories p1 LEFT JOIN CategoryTools p2 ON p2.FK_CategoryID = p1.CategoryID GROUP BY p1.CategoryID, p1.CategoryName", $columns, $settings, null, $order, $buttons, null);
                ?>
            </div>

            <div class="row dashboard-row">
                <h3>Producenter</h3>

                <?php
                $columns = array(
                    array(
                        'column' => 'ManufacturerID',
                        'label' => 'ID',
                        'prefix' => 'p1.ManufacturerID AS'
                    ),
                    array(
                        'column' => "ManufacturerName",
                        'label' => "Navn",
                        'prefix' => 'p1.ManufacturerName AS'
                    ),
                    array(
                        'column' => "toolCount",
                        'prefix' => 'COUNT(p2.FK_ManufacturerID) AS ',
                        'label' => "Antal værktøj"
                    )
                );

                $order = "ORDER BY p1.ManufacturerID DESC";
                $settings = array('searchbar' => true, 'querylogger' => true, 'queryloggerpath' => '/home/virtusbc/tectools.virtusb.com/querylogger.txt');

                $buttons = array(
                    array(
                        "button" => '<input type="button" class="btn" onclick="location.pathname = \'/editmanufacturerQMARKmanufacturerid=?\'" value="Rediger producent" />',
                        "value" => "ManufacturerID"
                    )
                );

                $GLOBALS['RCMSTables']->createRCMSTable("manufacturers_table", "Manufacturers p1 LEFT JOIN Tools p2 ON p2.FK_ManufacturerID = p1.ManufacturerID GROUP BY p1.ManufacturerID, p1.ManufacturerName", $columns, $settings, null, $order, $buttons, null);
                ?>
            </div>
        <?php elseif ($this->RCMS->Login->isAdmin() === false && $this->RCMS->Login->isLoggedIn() === true): ?>
            <div class="row">
                <p>Du er standard bruger</p>
            </div>
        <?php endif; ?>
    </div>
</div>



