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

                    <p style="margin: 0" class="right">Velkommen, <?= $this->RCMS->Login->getFirstName() ?><br>Du er personale</p>
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
                        'column' => "StatusName",
                        'label' => "Status"
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

                $GLOBALS['RCMSTables']->createRCMSTable("tools_table", "Tools p1 LEFT JOIN Manufacturers p2 ON p1.FK_ManufacturerID = p2.ManufacturerID LEFT JOIN Statuses p3 ON p3.StatusID = p1.FK_StatusID", $columns, $settings, null, $order, $buttons, null);
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
                $settings = array('searchbar' => true);

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
                <div class="col s12">
                    <a class="btn" href="/my-subscription">Mit abonnement</a>
                    <!-- TODO: skriv hvilket abonnement brugeren har -->
                    <p style="margin: 0" class="right">Velkommen, <?= $this->RCMS->Login->getFirstName() ?><br>Du er standard bruger</p>
                </div>
            </div>

            <div class="row">
                <h3>Aktive udlejninger</h3>

                <?php
                $columns = array(
                    array(
                        'column' => 'Image',
                        'label' => 'Billede',
                        'function' => 'showToolImage'
                    ),
                    array(
                        'column' => 'ToolName',
                        'label' => 'Navn'
                    ),
                    array(
                        'column' => "ManufacturerName",
                        'label' => "Producent"
                    ),
                    array(
                        'column' => "StartDate",
                        'label' => "Udlejning start",
                        'tdclass' => 'check-in-out-date',
                        'tdattributes' => [
                            [
                                'name' => 'datetime',
                                'valuefromcolumn' => 'StartDate'
                            ]
                        ]
                    ),
                    array(
                        'column' => "EndDate",
                        'label' => "Udlejning slut",
                        'tdclass' => 'check-in-out-date',
                        'tdattributes' => [
                            [
                                'name' => 'datetime',
                                'valuefromcolumn' => 'EndDate'
                            ]
                        ]
                    )
                );

                $order = "ORDER BY EndDate DESC, CheckedOut DESC";
                $settings = array('searchbar' => true);

                $where = [
                    [
                        'column' => 'FK_UserID',
                        'eq' => $userID = $this->RCMS->Login->getUserID(),
                        'type' => 'i'
                    ],
                    [
                        'column' => 'CheckedOut',
                        'eq' => 0,
                        'type' => 'i'
                    ]
                ];

                $GLOBALS['RCMSTables']->createRCMSTable("manufacturers_table", "CheckIns c LEFT JOIN Tools t ON t.ToolID = c.FK_ToolID LEFT JOIN Manufacturers m ON m.ManufacturerID = t.FK_ManufacturerID", $columns, $settings, $where, $order, [], null);
                ?>
            </div>

            <div class="row">
                <h3>Dine reservationer</h3>
            </div>

            <div class="row">
                <h3>Afsluttede udlejninger</h3>

                <?php
                $columns = array(
                    array(
                        'column' => 'Image',
                        'label' => 'Billede',
                        'function' => 'showToolImage'
                    ),
                    array(
                        'column' => 'ToolName',
                        'label' => 'Navn'
                    ),
                    array(
                        'column' => "ManufacturerName",
                        'label' => "Producent"
                    ),
                    array(
                        'column' => "StartDate",
                        'label' => "Udlejning start",
                        'tdclass' => 'check-in-out-date',
                        'tdattributes' => [
                            [
                                'name' => 'datetime',
                                'valuefromcolumn' => 'StartDate'
                            ]
                        ]
                    ),
                    array(
                        'column' => "EndDate",
                        'label' => "Udlejning slut",
                        'tdclass' => 'check-in-out-date',
                        'tdattributes' => [
                            [
                                'name' => 'datetime',
                                'valuefromcolumn' => 'EndDate'
                            ]
                        ]
                    )
                );

                $order = "ORDER BY EndDate DESC, CheckedOut DESC";
                $settings = array('searchbar' => true);

                $where = [
                    [
                        'column' => 'FK_UserID',
                        'eq' => $userID = $this->RCMS->Login->getUserID(),
                        'type' => 'i'
                    ],
                    [
                        'column' => 'CheckedOut',
                        'eq' => 1,
                        'type' => 'i'
                    ]
                ];

                $GLOBALS['RCMSTables']->createRCMSTable("manufacturers_table", "CheckIns c LEFT JOIN Tools t ON t.ToolID = c.FK_ToolID LEFT JOIN Manufacturers m ON m.ManufacturerID = t.FK_ManufacturerID", $columns, $settings, $where, $order, [], null);
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/libs/timeago/timeago.min.js"></script>



<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/dashboard/dashboard.js"></script>

