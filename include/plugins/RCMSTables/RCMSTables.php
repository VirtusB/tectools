<?php

declare(strict_types=1);

class RCMSTables {
    /**
     * @var RCMS $RCMS
     */
    public RCMS $RCMS;

    /**
     * @var array $table
     * @var array $columns
     * @var array $where
     * @var array $order
     * @var array $buttons
     * @var array $dropdown
     * @var array $settings
     */
    private array $table, $columns, $where, $order, $buttons, $dropdown, $settings;

    public function __construct(RCMS $RCMS) {
        $this->RCMS = $RCMS;
    }

    /**
     * Denne metode laver data-tabellen og er den eneste metode som man er nød til at kalde manuelt
     *
     * Den opsætter konfiguration, udskriver HTML og håndterer AJAX
     * @param string $id
     * @param string $table
     * @param array $columns
     * @param array $settings
     * @param array $where
     * @param null|string $order
     * @param array $buttons
     * @param null|array $dropdown
     * @return void
     */
    public function createRCMSTable(string $id, string $table, array $columns, ?array $settings = array(), ?array $where = array(), ?string $order = null, ?array $buttons = array(), ?array $dropdown = null): void {
        $this->columns = array();
        $this->table = array();
        $this->where = array();
        $this->buttons = array();
        $this->settings = array();

        if ($where === null) {
            $where = array();
        }

        $this->columns[$id] = $columns;
        $this->table[$id] = $table;
        $this->where[$id] = $where;
        $this->buttons[$id] = $buttons;
        $this->dropdown[$id] = $dropdown;
        $this->order[$id] = $order;

        if (empty($settings['searchbar'])) {
            $settings['searchbar'] = false;
        }

        if (empty($settings['pages'])) {
            $settings['pages'] = false;
        }

        if (empty($settings['pageLimit'])) {
            $settings['pageLimit'] = 10;
        }

        $this->settings[$id] = $settings;

        $this->createHTML($id);

        if (isset($_POST['RCMSTable']) || isset($_GET['RCMSTable'])) {
            $this->loadAjax();
        }
    }

    /**
     * Denne metode udskriver HTML tabellen
     * @param string $id
     * @return void
     */
    private function createHTML(string $id): void {
        $table = $this->table[$id];
        $columns = $this->columns[$id];
        $where = $this->where[$id];
        $order = $this->order[$id];
        $buttons = $this->buttons[$id];
        $settings = $this->settings[$id];

        // Tilføj HTML klasse til tabellen hvis sortering er aktiveret
        $hasSorting = '';
        if(!empty($settings['ajaxsort']) && $settings['ajaxsort']) {
            $hasSorting = ' has-sorting';
        }

        // Start udskrivning af tabel og udskriv søgefelt hvis det er aktiveret
        echo "<table id='$id' class='RCMSTable $hasSorting' style='width: 100%'>";
        if(!empty($settings['searchbar']) && $settings['searchbar']) {
            echo '<tr class="search-tr">';
            echo '<td class="searchtd" colspan="' . (count($columns) + count($buttons)) . '"><input type="text" class="searchbar" placeholder="Søgefelt..." /></td>';
            echo '</tr>';
        }

        // Udskriv tabel hovedet
        echo '<tr class="table-head">';
        foreach ($columns as $column) {
            $sortKey = $column['column'] ?? '';
            $initialSortDir = $column['initial_sort_dir'] ?? '';
            $initialSortDir = $initialSortDir ? "data-initial-sort-dir='$initialSortDir'" : '';

            // Tjek om sortering er aktiveret og udskriv derefter
            if(!empty($settings['ajaxsort']) && $settings['ajaxsort']) {
                if (!empty($column['enable_ajax_sort']) && $column['enable_ajax_sort']) {
                    echo "<th class='th-can-sort' $initialSortDir data-sort-key=\"$sortKey\">" . $column['label'] . "</th>";
                } else {
                    echo '<th>' . $column['label'] . '</th>';
                }
            } else {
                echo '<th>' . $column['label'] . '</th>';
            }
        }
        foreach ($buttons as $button) {
            echo "<th></th>";
        }

        echo '</tr>';

        $rows = $this->retrieveData($table, $columns, $where, $order, $settings);

        // Udskriv alle rækkerne
        $this->buildRows($id, $rows);

        echo '</table>';
    }

    /**
     * Udskriver HTML tabel rækkerne
     * @param string $id
     * @param array $rows
     * @return void
     */
    private function buildRows(string $id, array $rows): void {
        $table = $this->table[$id];
        $columns = $this->columns[$id];
        $where = $this->where[$id];
        $order = $this->order[$id];
        $buttons = $this->buttons[$id];
        $dropdown = $this->dropdown[$id];
        $settings = $this->settings[$id];

        // Hvis dropdown er aktiveret tilføjes der en HTML klasse
        $addClass = "";
        if (!empty($dropdown)) {
            $addClass .= "RCMSTableExpand";
        }

        foreach ($rows as $row) {
            echo "<tr class='dataRow $addClass'>";

            foreach ($columns as $column) {
                $tdClass = $column['tdclass'] ?? '';
                $tdAttributes = $column['tdattributes'] ?? [];
                $attributesString = '';
                $conditionalAttributes = $column['conditional_attributes'] ?? [];
                $conditionalAttributesString = '';

                // Tjek om der er sat attributter som skal udskrives på kolonnen
                foreach ($tdAttributes as $tdAttribute) {
                    if (isset($tdAttribute['value'])) {
                        $attributesString .= $tdAttribute['name'] . '="' . $tdAttribute['value'] . '" ';
                    } else if (isset($tdAttribute['valuefromcolumn'])) {
                        $columnName = $tdAttribute['valuefromcolumn'];

                        $attributesString .= $tdAttribute['name'] . '="' . $row[$columnName] . '" ';
                    }
                }

                // Tjek om der er attributter med betingelser
                foreach ($conditionalAttributes as $conditionalAttribute) {
                    $columnName = $conditionalAttribute['valuefromcolumn'];
                    $columnValue = $row[$columnName];

                    $conditionResult = (string) $conditionalAttribute['condition']($columnValue);
                    $conditionalAttributesString .= $conditionalAttribute['name'] . '="' . $conditionResult . '" ';
                }

                // Tjek om der skal køres en metode på kolonnen
                if (!empty($column['function'])) {
                    echo "<td $conditionalAttributesString $attributesString class='$tdClass'>" . $GLOBALS['GlobalHandlers']->callFunction($column['function'], array($row[$column['column']], $row)) . '</td>';
                } else {
                    echo "<td $conditionalAttributesString $attributesString class='$tdClass'>" . $row[$column['column']] . '</td>';
                }
            }

            // Tjek om der skal udskrives knapper
            if (!empty($buttons)) {
                foreach ($buttons as $button) {
                    $showButton = str_replace('?', $row[$button['value']], $button['button']);
                    echo '<td>' . $showButton . '</td>';
                }
            }
            echo '</tr>';
            if (!empty($dropdown)) {
                echo '<tr class="dataRow hidedtrow">';
                echo '<td colspan="' . (count($columns) + count($buttons)) . '">' . $GLOBALS['GlobalHandlers']->callFunction($dropdown['function'], array($row[$dropdown['value']], $row)) . '</td>';
                echo '</tr>';
            }
        }

        $rowCount = $this->countRows($table, $columns, $where, $order, $settings);
        $pages = ceil($rowCount / $settings['pageLimit']);

        echo '<tr class="dataRow pagination-tr">';
        echo '<td class="pagestd" colspan="' . (count($columns) + count($buttons)) . '">Side';
        for ($i = 1; $i <= $pages; $i++) {
            if ((isset($settings['pageNum']) && (int) $settings['pageNum'] === $i) || (!isset($settings['pageNum']) && $i === 1)) {
                echo ' <a class="pageSel" href="' . $i . '">' . $i . '</a>';
            } else {
                echo ' <a class="pageNorm" href="' . $i . '">' . $i . '</a>';
            }
        }
        echo '</td>';
        echo '</tr>';
    }

    /**
     * Loader data og udskriver tabellen som HTML men bruges til AJAX requests
     * @return void
     */
    private function loadAjax(): void {
        // ID på tabellen skal være sat enten i $_GET eller $_POST
        if (isset($_POST['RCMSTable']) || isset($_GET['RCMSTable'])) {
            $id = $_POST['RCMSTable'] ?? $_GET['RCMSTable'];
        } else {
            return;
        }

        if (!isset($this->table[$id])) {
            return;
        }

        ob_get_clean();
        ob_start();
        header("Content-Type: application/json");

        $table = $this->table[$id];
        $columns = $this->columns[$id];
        $where = $this->where[$id];
        $order = $this->order[$id];
        $settings = $this->settings[$id];

        if (isset($_POST['pageNum'])) {
            $settings['pageNum'] = $_POST['pageNum'];
        }

        if (isset($_POST['searchTxt'])) {
            $settings['searchTxt'] = $_POST['searchTxt'];
        }

        if (isset($_POST['sortKey'])) {
            $settings['sortKey'] = $_POST['sortKey'];
        }

        if (isset($_POST['sortDir'])) {
            $settings['sortDir'] = $_POST['sortDir'];
        }

        $this->settings[$id] = $settings;

        $rows = $this->retrieveData($table, $columns, $where, $order, $settings);

        $this->buildRows($id, $rows);

        exit;
    }

    /**
     * Returnerer hvor mange rækker data der er i databasen, hvor søgetekst og sidenummer er taget i mente hvis de er sat
     * @param string $table
     * @param array $columns
     * @param array $where
     * @param string $order
     * @param array $settings
     * @return int
     */
    private function countRows(string $table, array $columns, array $where, string $order, array $settings): int {
        $settings['noLimit'] = true;

        if (isset($_POST['pageNum'])) {
            $settings['pageNum'] = $_POST['pageNum'];
        }

        if (isset($_POST['searchTxt'])) {
            $settings['searchTxt'] = $_POST['searchTxt'];
        }

        return count($this->retrieveData($table, $columns, $where, $order, $settings));
    }

    /**
     * Denne metode bygger den komplette SQL query op, der skal køres, på den specifikke tabel i DB med WHERE, ORDER, LIMIT osv. taget i mente
     * @param string $table
     * @param array $columns
     * @param array $where
     * @param string $order
     * @param array $settings
     * @return array
     */
    private function retrieveData(string $table, array $columns, array $where, string $order, array $settings): array {
        $selectColumns = "*";

        if ($order === null) {
            $order = "";
        } else {
            if (isset($settings['sortKey'], $settings['sortDir'])) {
                // Da sortering er aktiveret her, er vi nød til at gå i dybden med hvordan ORDER delen af query'en skal laves
                // Vi bruger $order = "ORDER BY p1.datetime DESC" som eksempel
                // $settings['sortKey'] => "datetime", $settings['sortDir'] => "DESC" bruges som eksempel

                $splitOrder = explode(' ', $order); // [0] => "ORDER", [1] => "BY", [2] => "p1.datetime", [3] => "DESC"

                // Hvis $splitOrder[2] er lig med "p1.datetime" bliver $splitCol til et array med 2 elementer.
                // Hvis $splitOrder[2] er lig med "datetime" bliver $splitCol til et array med ét element
                $splitCol = explode('.', $splitOrder[2]);

                $order = ' ' . $splitOrder[0] . ' ' . $splitOrder[1]; // sammensæt "ORDER" og "BY" igen, de strings behøver vi ikke at gøre mere med

                // Håndter sub-queries / prefix
                $prefixSubQ = "";

                foreach ($columns as $column) {
                    if (isset($column['order_subq']) && $column['column'] === $settings['sortKey']) {
                        // Her er vi på den $column som vi gerne vil sortere på
                        // Vi sætter $prefixSubQ til at være $column['order_subq'] og ligger et punktum til, det skal jo bruges til SQL
                        // Som eksempel er $column['order_subq'] => "p1"
                        // "p1" er navnet på vores SQL sub query
                        $prefixSubQ = $column['order_subq'] . '.';
                    }
                }

                if (count($splitCol)  === 2) {
                    // $order bliver her sat til "p1.datetime DESC"
                    $order .= ' ' . $prefixSubQ . $settings['sortKey'] . ' ' . $settings['sortDir'];
                } else if (count($splitCol) === 1) {
                    // $order bliver her sat til "datetime DESC"
                    $order .= ' ' . $settings['sortKey'] . ' ' . $settings['sortDir'];
                }
            } else {
                $order = " " . $order; // standard $order, ingen sortering, gør ingenting
            }
        }

        if (!empty($columns)) {
            foreach ($columns as $c) {
                if (isset($c['prefix'])) {
                    // $c['prefix'] => "p1.datetime AS " som eksempel
                    // prefix gør så kolonnen hedder "datetime" i stedet for "p1.datetime" når vi får kolonnen ud af databasen
                    $selectColumns .= ", " . $c['prefix'] . $c['column'];
                }else {
                    $selectColumns .= ", " . $c['column'];
                }
            }
        }

        $limit = " LIMIT 0, " . $settings['pageLimit'];

        if (isset($settings['pageNum'])) {
            $limit = " LIMIT " . ($settings['pageNum'] - 1) * $settings['pageLimit'] . ", " . $settings['pageLimit'];
        }

        if (isset($settings['noLimit'])) {
            $limit = "";
        }

        // Opbyg WHERE delen af SQL query'en
        $whereTypes = array();
        $whereTypes[] = implode("", self::pluck($where, 'type')); // array med typer, f.eks. [0] => "i", [1] => "s", [2] => "d"

        $operators = [
            'eq' => '=',        // equal
            'not_eq' => '!=',   // not equal
            'lt' => '<',        // lower than
            'gt' => '>',        // greater than
            'lteq' => '<=',     // lower than or equal to
            'gteq' => '>=',     // greater than or equal to
            'direct_eq' => '=', // direkte SQL equal =, bliver ikke bindet/prepared
            'direct_in' => 'IN' // direkte SQL IN, bliver ikke bindet/prepared
        ];

        $whereClause = "";

        $whereCount = count($where);
        $i = 0;

        $whereArr = array();
        foreach ($where as $key => $w) {
            $operator = '';

            // f.eks: $where = array(array("column" => "battery", "lt" => 12.6, "type" => "d"));
            // d er for float, lt er for lower than

            foreach ($operators as $k => $op) {
                if (isset($w[$k])) {
                    // F.eks. $operator => ["eq" => "="]
                    $operator = [$k => $op];
                }
            }

            // F.eks. $operatorKey = "eq"
            $operatorKey = (string) array_keys($operator)[0];

            // direct_eq eller direct_in kan bruges til at slippe uden om prepared statements, så vi kan tjekke på værdier eller køre en IN direkte
            // Hvis direct_eq eller direct_in ikke bruges, bliver parametrene bundet med spørgsmålstegn, ?
            $directs = ['direct_eq', 'direct_in'];
            if (in_array($operatorKey, $directs, true)) {
                $whereClause .= $w['column'] . ' ' . $operator[$operatorKey] . ' ' . $w[$operatorKey] . ' ';
            } else {
                $whereClause .= $w['column'] . ' ' . $operator[$operatorKey] . ' ? ';
                $whereArr[] = $w[$operatorKey];
            }

            if (++$i !== $whereCount) {
                $whereClause .= "AND ";
            }
        }

        // Hvis $settings['searchTxt'] er sat, betyder det at brugeren gerne vil søge
        if (isset($settings['searchTxt']) && $settings['searchTxt'] !== "" && !empty($columns)) {
            $columnsCount = count($columns);
            $i = 0;

            if ($whereCount !== 0) {
                $whereClause .= 'AND ';
            }

            if ($columnsCount > 0) {
                $whereClause .= '(';
            }

            // Søg på hver kolonne
            foreach ($columns as $c) {
                $searchTxt = '%' . $settings['searchTxt'] . '%';

                if ((isset($c['like']) && $c['like'] !== 'ignore') || !isset($c['like'])) {
                    if (isset($c['like']) && $c['like'] !== 'ignore') {
                        $whereClause .= $c['like'] . " LIKE ? ";
                    } else {
                        // Husk at bruge prefix'et til subquery'en hvis det er er et
                        $subQ = '';
                        if (isset($c['order_subq']) && $c['order_subq'] !== '') {
                            $subQ = $c['order_subq'] . '.';
                        }
                        $whereClause .= $subQ . $c['column'] . " LIKE ? ";
                    }
                    $whereArr[] = $searchTxt;

                    $whereTypes[] = 's';

                    if (++$i !== $columnsCount) {
                        $whereClause .= "OR ";
                    }
                }else {
                    $i++;
                }

            }


            if ($columnsCount > 0) {
                $whereClause .= ') ';
            }
        }

        $types = "";

        foreach ($whereTypes as $type) {
            $types .= $type;
        }

        array_unshift($whereArr, $types);

        $query = "";

        if ($whereClause !== "" && !empty($whereArr)) {
            $query = "SELECT $selectColumns FROM $table WHERE $whereClause" . $order . $limit;
            $this->logQuery($settings, $query, $whereArr);
            $result = $this->RCMS->execute($query, $whereArr);
        } else {
            $query = "SELECT $selectColumns FROM $table" . $order . $limit;
            $this->logQuery($settings, $query, $whereArr);
            $result = $this->RCMS->execute($query);
        }


        if ($result->num_rows > 0) {
            $rows = array();

            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            return $rows;
        }

        return [];
    }

    /**
     * Kan gemme de queries der bliver kørt i en tekstfil
     * Bruges til at debugge problemer
     * @param array $settings
     * @param string $query
     * @param array $whereArr
     * @return void
     */
    private function logQuery(array $settings, string $query, array $whereArr): void {
        if (isset($settings['querylogger'])) {
            $dt = new DateTime();
            $dt = $dt->format('d-m-Y H:i:s');

            $log = [
                'time' => $dt,
                'query' => $query,
                'whereArr' => $whereArr
            ];
            file_put_contents($settings['queryloggerpath'], print_r($log, true), FILE_APPEND);
        }
    }

    /**
     * Kan plukke en bestemt egenskab fra hvert associativ array i et numerisk array
     *
     * Returnerer et array af egenskabsværdierne fra hvert element
     *
     * Bruges f.eks. i retrieveData metoden
     *
     * Reference: linje 515 i https://github.com/DataTables/DataTables/blob/master/examples/server_side/scripts/ssp.class.php
     *
     * @param array $a Array som der skal plukkes data fra
     * @param string $prop Den property/key/egenskab der skal plukkes
     * @return array Array af egenskabsværdierne
     */
    private static function pluck (array $a, string $prop ): array {
        $out = array();
        $arrayLength = count($a);

        for ( $i = 0; $i < $arrayLength; $i++ ) {
            if (isset($a[$i][$prop])) {
                $out[] = $a[$i][$prop];
            }
        }

        return $out;
    }
}
