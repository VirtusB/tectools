<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

/**
 * @var RCMSTables $RCMSTables
 */
$RCMSTables = $GLOBALS['RCMSTables'];

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

?>

<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/dashboard.css">

<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <h1 class="header center orange-text mt0">Dashboard</h1>
        <hr class="mb2">

        <?php if ($this->RCMS->Login->isAdmin()): ?>
            <div class="row">
                <div class="col s12 m8 xl6">
                    <div class="card-panel teal">
                        <span style="display: block; margin-bottom: 24px" class="white-text">
                                    Velkommen, <?= $this->RCMS->Login->getFirstName() ?><br>Du er personale
                        </span>

                        <a class="btn tec-btn" href="/categories/create">Opret kategori</a>
                        <a class="btn tec-btn" href="/manufacturers/create">Opret producent</a>
                        <a class="btn tec-btn" href="/tools/create">Opret værktøj</a>
                        <a class="btn tec-btn" href="/activity-center">Aktivitetscenter</a>
                    </div>
                </div>
            </div>

            <!-- region Værktøj tabel -->
            <div class="row dashboard-row responsive-table-container">
                <h3 class="mt0">Værktøj</h3>

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
                        'column' => "Barcode",
                        'label' => "Stregkode",
                        'function' => 'showToolBarcode'
                    )
                );

                $order = "ORDER BY ToolID DESC";
                $settings = array('searchbar' => true, 'pageLimit' => 5);

                $buttons = array(
                    array(
                        "button" => '<input type="button" class="btn tec-btn" onclick="location.pathname = `/tools/editQMARKtoolid=?`" value="Rediger værktøj" />',
                        "value" => "ToolID"
                    ),
                    array(
                        "button" => '<input type="button" class="btn tec-btn red" onclick="deleteTool(?, this)" value="Slet værktøj" />',
                        "value" => "ToolID"
                    )
                );

                $RCMSTables->createRCMSTable("tools_table", "Tools p1 LEFT JOIN Manufacturers p2 ON p1.FK_ManufacturerID = p2.ManufacturerID LEFT JOIN Statuses p3 ON p3.StatusID = p1.FK_StatusID", $columns, $settings, null, $order, $buttons, null);
                ?>
            </div>
            <!-- endregion -->

            <!-- region Bruger tabel -->
            <div class="row dashboard-row responsive-table-container">
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
                $settings = array('searchbar' => true, 'pageLimit' => 5);

                $buttons = array(
                    array(
                        "button" => '<input type="button" class="btn tec-btn" onclick="location.pathname = `/users/editQMARKuserid=?`" value="Rediger bruger" />',
                        "value" => "UserID"
                    )
                );

                $RCMSTables->createRCMSTable("users_table", "Users", $columns, $settings, null, $order, $buttons, null);
                ?>

            </div>
            <!-- endregion -->

            <!-- region Kategori tabel -->
            <div class="row dashboard-row responsive-table-container">
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
                $settings = array('searchbar' => true, 'pageLimit' => 5);

                $buttons = array(
                    array(
                        "button" => '<input type="button" class="btn tec-btn" onclick="location.pathname = `/categories/editQMARKcategoryid=?`" value="Rediger kategori" />',
                        "value" => "CategoryID"
                    ),
                    array(
                        "button" => '<input type="button" class="btn tec-btn red" onclick="deleteCategory(?, this)" value="Slet kategori" />',
                        "value" => "CategoryID"
                    )
                );

                $RCMSTables->createRCMSTable("categories_table", "Categories p1 LEFT JOIN CategoryTools p2 ON p2.FK_CategoryID = p1.CategoryID GROUP BY p1.CategoryID, p1.CategoryName", $columns, $settings, null, $order, $buttons, null);
                ?>
            </div>
            <!-- endregion -->

            <!-- region Producent tabel -->
            <div class="row dashboard-row responsive-table-container">
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
                $settings = array('searchbar' => true, 'pageLimit' => 5);

                $buttons = array(
                    array(
                        "button" => '<input type="button" class="btn tec-btn" onclick="location.pathname = `/manufacturers/editQMARKmanufacturerid=?`" value="Rediger producent" />',
                        "value" => "ManufacturerID"
                    ),
                    array(
                        "button" => '<input type="button" class="btn tec-btn red" onclick="deleteManufacturer(?, this)" value="Slet producent" />',
                        "value" => "ManufacturerID"
                    )
                );

                $RCMSTables->createRCMSTable("manufacturers_table", "Manufacturers p1 LEFT JOIN Tools p2 ON p2.FK_ManufacturerID = p1.ManufacturerID GROUP BY p1.ManufacturerID, p1.ManufacturerName", $columns, $settings, null, $order, $buttons, null);
                ?>
            </div>
            <!-- endregion -->

            <!-- region Kommentar modal for personale til aktive og afsluttede udlejninger -->
            <div id="comment-modal" class="modal">
                <div class="modal-content">
                    <h4>Kommentar til udlejning</h4>

                    <label for="comment-textarea">Kommentar</label>
                    <textarea readonly="readonly" class="materialize-textarea" id="comment-textarea" cols="30" rows="10"></textarea>

                    <button class="btn tec-btn right modal-close">Luk</button>
                </div>
            </div>
            <!-- endregion -->

            <!-- region Tjek Ud modal for personale til aktive  udlejninger -->
            <div id="check-out-modal" class="modal">
                <div class="modal-content">
                    <h4>Tjek Ud - Vælg status</h4>

                    <label for="check-out-status-select">Status</label>
                    <select id="check-out-status-select" class="mat-select">
                        <option selected value="1">På lager</option>
                        <option value="4">Ikke på lager</option>
                        <option value="5">Beskadiget</option>
                    </select>

                    <button onclick="checkOut(this.getAttribute('data-checkin-id'), this)" class="btn tec-btn right modal-close mt2 mb2">Tjek Ud</button>
                </div>
            </div>
         <!-- endregion -->

            <!-- region Aktive udlejninger, personale tabel -->
            <div class="row dashboard-row responsive-table-container">
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
                        'tdclass' => 'render-datetime',
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
                        'tdclass' => 'render-datetime',
                        'tdattributes' => [
                            [
                                'name' => 'datetime',
                                'valuefromcolumn' => 'EndDate'
                            ]
                        ],
                        'conditional_attributes' => [
                            [
                                'name' => 'data-exceeded-date',
                                'valuefromcolumn' => 'EndDate',
                                'condition' => static function ($valuefromcolumn) {
                                    return new DateTime() > new DateTime($valuefromcolumn);
                                }
                            ]
                        ]
                    )
                );

                $order = "ORDER BY EndDate DESC, CheckedOut DESC";
                $settings = array('searchbar' => true, 'pageLimit' => 5);

                $where = [
                    [
                        'column' => 'CheckedOut',
                        'eq' => 0,
                        'type' => 'i'
                    ]
                ];

                $buttons = [
                    [
                        'button' => '<button onclick="showCommentCheckIn(?, this)" class="btn tec-btn">Vis kommentar</button>',
                        'value' => 'CheckInID'
                    ],
                    [
                        'button' => '<button onclick="showCheckOutModal(?, this)" class="btn tec-btn">Tjek Ud</button>',
                        'value' => 'CheckInID'
                    ]
                ];

                $RCMSTables->createRCMSTable("active_checkins_table", "CheckIns c LEFT JOIN Tools t ON t.ToolID = c.FK_ToolID LEFT JOIN Manufacturers m ON m.ManufacturerID = t.FK_ManufacturerID", $columns, $settings, $where, $order, $buttons, null);
                ?>
            </div>
            <!-- endregion -->

            <!-- region Afsluttede udlejninger, personale tabel -->
            <div class="row dashboard-row responsive-table-container mb4">
                <h3>Afsluttede udlejninger</h3>

                <?php
                $columns = array(
                    array(
                        'column' => 'ToolName',
                        'label' => 'Navn'
                    ),
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
                        'tdclass' => 'render-datetime',
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
                        'tdclass' => 'render-datetime',
                        'tdattributes' => [
                            [
                                'name' => 'datetime',
                                'valuefromcolumn' => 'EndDate'
                            ]
                        ]
                    )
                );

                $order = "ORDER BY EndDate DESC, CheckedOut DESC";
                $settings = array('searchbar' => true, 'pageLimit' => 5);

                $where = [
                    [
                        'column' => 'CheckedOut',
                        'eq' => 1,
                        'type' => 'i'
                    ]
                ];

                $buttons = [
                    [
                        'button' => '<button onclick="showCommentCheckIn(?, this)" class="btn tec-btn">Vis kommentar</button>',
                        'value' => 'CheckInID'
                    ]
                ];

                $RCMSTables->createRCMSTable("ended_checkins_table", "CheckIns c LEFT JOIN Tools t ON t.ToolID = c.FK_ToolID LEFT JOIN Manufacturers m ON m.ManufacturerID = t.FK_ManufacturerID", $columns, $settings, $where, $order, $buttons, null);
                ?>
            </div>
            <!-- endregion -->

        <?php elseif ($this->RCMS->Login->isAdmin() === false && $this->RCMS->Login->isLoggedIn() === true): ?>
            <div class="row">
                <div class="col s12">
                    <div class="row">
                        <div class="col s12 m5">
                            <div class="card-panel teal">
                                <span style="display: block; margin-bottom: 24px" class="white-text">
                                    Velkommen, <?= $this->RCMS->Login->getFirstName() ?>
                                    <br>
                                    <?= empty($TecTools->Users->getSubName()) ? 'Du har ikke noget abonnement' : "Du er {$TecTools->Users->getSubName()} bruger" ?>
                                </span>

                                <a class="btn tec-btn xl-up-mb0" href="/my-subscription">Mit abonnement</a>
                                <a class="btn tec-btn xl-up-mb0" href="/users/edit?userid=<?= $this->RCMS->Login->getUserID() ?>">Rediger bruger</a>

                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- region Kommentar modal for brugere til aktive og afsluttede udlejninger -->
            <div id="comment-modal" class="modal">
                <div class="modal-content">
                    <h4>Kommentar til udlejning</h4>

                    <label for="comment-textarea">Din kommentar</label>
                    <textarea class="materialize-textarea" id="comment-textarea" cols="30" rows="10"></textarea>

                    <button onclick="saveCheckInComment(this.getAttribute('data-checkin-id'), this)" class="btn tec-btn right modal-close">Gem</button>
                </div>
            </div>
            <!-- endregion -->

            <!-- region Aktive udlejninger, bruger tabel -->
            <div class="row responsive-table-container">
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
                        'tdclass' => 'render-datetime',
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
                        'tdclass' => 'render-datetime',
                        'tdattributes' => [
                            [
                                'name' => 'datetime',
                                'valuefromcolumn' => 'EndDate'
                            ]
                        ],
                        'conditional_attributes' => [
                            [
                                'name' => 'data-exceeded-date',
                                'valuefromcolumn' => 'EndDate',
                                'condition' => static function ($valuefromcolumn) {
                                    return new DateTime() > new DateTime($valuefromcolumn);
                                }
                            ]
                        ]
                    )
                );

                $order = "ORDER BY EndDate DESC, CheckedOut DESC";
                $settings = array('searchbar' => true, 'pageLimit' => 5);

                $where = [
                    [
                        'column' => 'FK_UserID',
                        'eq' => $this->RCMS->Login->getUserID(),
                        'type' => 'i'
                    ],
                    [
                        'column' => 'CheckedOut',
                        'eq' => 0,
                        'type' => 'i'
                    ]
                ];

                $buttons = [
                    [
                        'button' => '<button onclick="showCommentCheckIn(?, this)" class="btn tec-btn">Kommentar</button>',
                        'value' => 'CheckInID'
                    ]
                ];

                $RCMSTables->createRCMSTable("active_checkins_table", "CheckIns c LEFT JOIN Tools t ON t.ToolID = c.FK_ToolID LEFT JOIN Manufacturers m ON m.ManufacturerID = t.FK_ManufacturerID", $columns, $settings, $where, $order, $buttons, null);
                ?>
            </div>
            <!-- endregion -->

            <!-- region Reservationer tabel -->
            <div class="row responsive-table-container">
                <h3>Dine reservationer</h3>

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
                        'prefix' => 'r.StartDate AS ',
                        'order_subq' => 'r',
                        'label' => "Reservation start",
                        'tdclass' => 'render-datetime',
                        'tdattributes' => [
                            [
                                'name' => 'datetime',
                                'valuefromcolumn' => 'StartDate'
                            ]
                        ]
                    ),
                    array(
                        'column' => "EndDate",
                        'prefix' => 'r.EndDate AS ',
                        'order_subq' => 'r',
                        'label' => "Reservation slut",
                        'tdclass' => 'render-datetime',
                        'tdattributes' => [
                            [
                                'name' => 'datetime',
                                'valuefromcolumn' => 'EndDate'
                            ]
                        ]
                    )
                );

                $order = "ORDER BY r.EndDate DESC";
                $settings = array('searchbar' => true, 'pageLimit' => 5, 'querylogger' => true, 'queryloggerpath' => '/home/virtusbc/tectools.virtusb.com/querylogger.txt');

                $where = [
                    [
                        'column' => 'FK_UserID',
                        'eq' => $this->RCMS->Login->getUserID(),
                        'type' => 'i'
                    ],
                    [
                        'column' => 'EndDate',
                        'direct_gteq' => 'NOW()'
                    ]
                ];

                $buttons = [
                    [
                        'button' => '<button onclick="deleteReservation(?, this)" class="btn tec-btn red">Slet</button>',
                        'value' => 'ReservationID'
                    ]
                ];

                $RCMSTables->createRCMSTable("reservations_table", "Reservations r LEFT JOIN Tools t ON t.ToolID = r.FK_ToolID LEFT JOIN Manufacturers m ON m.ManufacturerID = t.FK_ManufacturerID LEFT JOIN Statuses s ON s.StatusID = t.FK_StatusID", $columns, $settings, $where, $order, $buttons, null);
                ?>
            </div>
            <!-- endregion -->

            <!-- region Afsluttede udlejninger, bruger tabel -->
            <div class="row responsive-table-container">
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
                        'prefix' => 'c.StartDate AS ',
                        'order_subq' => 'c',
                        'label' => "Udlejning start",
                        'tdclass' => 'render-datetime',
                        'tdattributes' => [
                            [
                                'name' => 'datetime',
                                'valuefromcolumn' => 'StartDate'
                            ]
                        ]
                    ),
                    array(
                        'column' => "EndDate",
                        'prefix' => 'c.EndDate AS ',
                        'order_subq' => 'c',
                        'label' => "Udlejning slut",
                        'tdclass' => 'render-datetime',
                        'tdattributes' => [
                            [
                                'name' => 'datetime',
                                'valuefromcolumn' => 'EndDate'
                            ]
                        ]
                    )
                );

                $order = "ORDER BY EndDate DESC, CheckedOut DESC";
                $settings = array('searchbar' => true, 'pageLimit' => 5);

                $where = [
                    [
                        'column' => 'FK_UserID',
                        'eq' => $this->RCMS->Login->getUserID(),
                        'type' => 'i'
                    ],
                    [
                        'column' => 'CheckedOut',
                        'eq' => 1,
                        'type' => 'i'
                    ]
                ];

                $buttons = [
                    [
                        'button' => '<button onclick="showCommentCheckIn(?, this)" class="btn tec-btn">Vis Kommentar</button>',
                        'value' => 'CheckInID'
                    ]
                ];

                $RCMSTables->createRCMSTable("ended_checkins_table", "CheckIns c LEFT JOIN Tools t ON t.ToolID = c.FK_ToolID LEFT JOIN Manufacturers m ON m.ManufacturerID = t.FK_ManufacturerID", $columns, $settings, $where, $order, $buttons, null);
                ?>
            </div>
            <!-- endregion -->

        <?php endif; ?>
    </div>
</div>

<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/dashboard/dashboard.js"></script>

