<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <h1 class="header center orange-text">Dashboard</h1>

        <?php if ($this->RCMS->Login->isAdmin()): ?>
            <div class="row">
                <div class="col s6">
                    <a class="btn" href="/createtool">Opret værktøj</a>
                </div>
            </div>

            <div class="row">
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
                        'label' => "Stregkode"
                    )
                );

                $order = "ORDER BY ToolID DESC";
                $settings = array('searchbar' => true);

                $GLOBALS['RCMSTables']->createRCMSTable("tools_table", "Tools p1 LEFT JOIN Manufacturers p2 ON p1.FK_ManufacturerID = p2.ManufacturerID", $columns, $settings, null, $order, [], null);
                ?>
            </div>

            <div class="row">
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

                $GLOBALS['RCMSTables']->createRCMSTable("users_table", "Users", $columns, $settings, null, $order, [], null);
                ?>
            </div>
        <?php elseif ($this->RCMS->Login->isAdmin() === false && $this->RCMS->Login->isLoggedIn() === true): ?>
            <div class="row">
                <p>Du er standard bruger</p>
            </div>
        <?php endif; ?>
    </div>
</div>



