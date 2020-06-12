<?PHP

class RCMSTables {
    var $RCMS;
    var $table, $columns, $where, $order, $buttons, $dropdown, $settings;

    function __construct($RCMS) {
        $this->RCMS = $RCMS;
    }

    public function createRCMSTable($id, $table, $columns, $settings = array(), $where = array(), $order = null, $buttons = array(), $dropdown = null){
        $this->columns = array();
        $this->table = array();
        $this->where = array();
        $this->buttons = array();
        $this->settings = array();

        if ($where == null)
            $where = array();

        $this->columns[$id] = $columns;
        $this->table[$id] = $table;
        $this->where[$id] = $where;
        $this->buttons[$id] = $buttons;
        $this->dropdown[$id] = $dropdown;
        $this->order[$id] = $order;

        if (empty($settings['searchbar']))
            $settings['searchbar'] = false;

        if (empty($settings['pages']))
            $settings['pages'] = false;


        $settings['pageLimit'] = 10;

        $this->settings[$id] = $settings;

        $this->createHTML($id, $columns);


        if (isset($_POST['RCMSTable']) || isset($_GET['RCMSTable']))
            $this->loadAjax();
    }

    private function createJS($id, $columns){
        echo '<script>'.
            '$(document).ready(function() {'.
            "$('#" . $id . "').DataTable( {".
            '"processing": true,'.
            '"serverSide": true,'.
            '"ajax": {'.
            '"type": "POST",'.
            '"data": { ' . $id . '_ajax: "yes", id: "' . $id . '" }'.
            '}'.
            '});'.
            '} );'.
            '</script>';
    }

    private function createHTML($id, $columns){
        $table = $this->table[$id];
        $columns = $this->columns[$id];
        $where = $this->where[$id];
        $order = $this->order[$id];
        $buttons = $this->buttons[$id];
        $settings = $this->settings[$id];

        $hasSorting = '';
        if(!empty($settings['ajaxsort']) && $settings['ajaxsort']) {
            $hasSorting = ' has-sorting';
        }

        echo '<table id="' . $id . '" class="RCMSTable' . $hasSorting . '" style="width:100%">';
        if(!empty($settings['searchbar']) && $settings['searchbar']){
            echo '<tr>';
            echo '<td class="searchtd" colspan="' . (count($columns) + count($buttons)) . '"><input type="text" class="searchbar" placeholder="Søgefelt..." /></td>';
            echo '</tr>';
        }
        echo '<tr>';
        foreach ($columns as $column){
            $sortKey = $column['column'] ?? '';
            $initialSortDir = $column['initial_sort_dir'] ?? '';
            $initialSortDir = $initialSortDir ? "data-initial-sort-dir='$initialSortDir'" : '';

            if(!empty($settings['ajaxsort']) && $settings['ajaxsort']) {
                if (!empty($column['enable_ajax_sort']) && $column['enable_ajax_sort']) {
                    echo "<th class='th-can-sort' $initialSortDir data-sort-key=\"$sortKey\">" . $column['label'] . "</th>";
                } else {
                    echo "<th>" . $column['label'] . "</th>";
                }
            } else {
                echo "<th>" . $column['label'] . "</th>";
            }
        }
        foreach ($buttons as $button){
            echo "<th></th>";
        }
        echo '</tr>';

        $rows = $this->retrieveData($table, $columns, $where, $order, $settings);

        $this->buildRows($id, $rows);

        echo '</table>';
    }

    private function buildRows($id, $rows){
        if (!empty($rows))
            $columnCount = count($rows[0]);

        $table = $this->table[$id];
        $columns = $this->columns[$id];
        $where = $this->where[$id];
        $order = $this->order[$id];
        $buttons = $this->buttons[$id];
        $dropdown = $this->dropdown[$id];
        $settings = $this->settings[$id];


        $addClass = "";
        if (!empty($dropdown))
            $addClass .= "RCMSTableExpand";

        foreach ($rows as $row){
            echo '<tr class="dataRow ' . $addClass . '">';
            foreach ($columns as $column){
                $tdClass = $column['tdclass'] ?? '';
                $tdAttributes = $column['tdattributes'] ?? [];
                $attributesString = '';

                foreach ($tdAttributes as $tdAttribute) {
                    if (isset($tdAttribute['value'])) {
                        $attributesString .= $tdAttribute['name'] . '="' . $tdAttribute['value'] . '" ';
                    } else if (isset($tdAttribute['valuefromcolumn'])) {
                        $columnName = $tdAttribute['valuefromcolumn'];

                        $attributesString .= $tdAttribute['name'] . '="' . $row[$columnName] . '" ';
                    }
                }

                if (!empty($column['function'])){
                    echo "<td $attributesString class='$tdClass'>" . $GLOBALS['GlobalHandlers']->callFunction($column['function'], array($row[$column['column']], $row)) . '</td>';
                }else{
                    echo "<td $attributesString class='$tdClass'>" . $row[$column['column']] . '</td>';
                }


            }
            if (!empty($buttons)){
                foreach ($buttons as $button){
                    $showButton = str_replace('?', $row[$button['value']], $button['button']);
                    echo '<td>' . $showButton . '</td>';
                }
            }
            echo '</tr>';
            if (!empty($dropdown)){
                echo '<tr class="dataRow hidedtrow">';
                echo '<td colspan="' . (count($columns) + count($buttons)) . '">' . $GLOBALS['GlobalHandlers']->callFunction($dropdown['function'], array($row[$dropdown['value']], $row)) . '</td>';
                echo '</tr>';
            }
        }

        $rowCount = $this->countRows($table, $columns, $where, $order, $settings);
        $pages = ceil($rowCount / $settings['pageLimit']);
        echo '<tr class="dataRow">';
        echo '<td class="pagestd" colspan="' . (count($columns) + count($buttons)) . '">Side';
        for ($i = 1; $i <= $pages; $i++){
            if ((isset($settings['pageNum']) && $settings['pageNum'] == $i) || (!isset($settings['pageNum']) && $i == 1)){
                echo ' <a class="pageSel" href="' . $i . '">' . $i . '</a>';
            }else{
                echo ' <a class="pageNorm" href="' . $i . '">' . $i . '</a>';
            }
        }
        echo '</td>';
        echo '</tr>';
    }

    private function loadAjax(){
        if (isset($_POST['RCMSTable']) || isset($_GET['RCMSTable'])){
            if (isset($_POST['RCMSTable']))
                $id = $_POST['RCMSTable'];
            if (isset($_GET['RCMSTable']))
                $id = $_GET['RCMSTable'];
        }else{
            return;
        }

        if (!isset($this->table[$id])){
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

        if (isset($_POST['pageNum']))
            $settings['pageNum'] = $_POST['pageNum'];

        if (isset($_POST['searchTxt']))
            $settings['searchTxt'] = $_POST['searchTxt'];

        if (isset($_POST['sortKey']))
            $settings['sortKey'] = $_POST['sortKey'];

        if (isset($_POST['sortDir']))
            $settings['sortDir'] = $_POST['sortDir'];

        $this->settings[$id] = $settings;

        $rows = $this->retrieveData($table, $columns, $where, $order, $settings);

        $this->buildRows($id, $rows);

        exit;
    }

    private function countRows($table, $columns, $where, $order, $settings){
        $settings['noLimit'] = true;

        if (isset($_POST['pageNum']))
            $settings['pageNum'] = $_POST['pageNum'];

        if (isset($_POST['searchTxt']))
            $settings['searchTxt'] = $_POST['searchTxt'];

        return count($this->retrieveData($table, $columns, $where, $order, $settings));
    }

    // Hvis prefix f.eks. er "p1.datetime AS" får man returneret "p1"
    private function getPrefixSubQ($column) {
        if (isset($column['prefix'])) {
            $splitPrefix = explode(' ', $column['prefix'])[0];
            $splitPrefix = explode('.', $splitPrefix)[0]; // 0 => p1 1 => datetime
            return $splitPrefix;
        }
        return '';
    }

    private function retrieveData($table, $columns, $where, $order, $settings) {
        $selectColumns = "*";

        $result = "";


        if ($order == null) {
            $order = "";
        } else {
            if (isset($settings['sortKey'], $settings['sortDir'])) {
                $splitOrder = explode(' ', $order);
                $splitCol = explode('.', $splitOrder[2]); // f.eks. "p1.datetime" bliver til et array med 2 elementer. Hvis det bare f.eks. er "datetime" bliver det til et array med ét element

                $order = ' ' . $splitOrder[0] . ' ' . $splitOrder[1];

                $prefixSubQ = "";

                foreach ($columns as $column) {
                    if (isset($column['order_subq']) && $column['column'] === $settings['sortKey']) {
                        $prefixSubQ = $column['order_subq'] . '.';
                    }
                }

                if (count($splitCol)  === 2) {
                    $order .= ' ' . $prefixSubQ . $settings['sortKey'] . ' ' . $settings['sortDir'];
                } else if (count($splitCol) === 1) {
                    $order .= ' ' . $settings['sortKey'] . ' ' . $settings['sortDir'];
                }
            } else {
                $order = " " . $order; // standard $order
            }
        }

        if (!empty($columns)){
            foreach ($columns as $c){
                if (isset($c['prefix'])){
                    $selectColumns .= ", " . $c['prefix'] . $c['column'];
                }else{
                    $selectColumns .= ", " . $c['column'];
                }
            }
        }

        $limit = " LIMIT 0, " . $settings['pageLimit'];

        if (isset($settings['pageNum']))
            $limit = " LIMIT " . ($settings['pageNum'] - 1) * $settings['pageLimit'] . ", " . $settings['pageLimit'];

        if (isset($settings['noLimit']))
            $limit = "";

        $whereTypes = array();
        $whereTypes[] = implode("", self::pluck($where, 'type'));

        $operators = [
            'eq' => '=',        // equal
            'not_eq' => '!=',   // not equal
            'lt' => '<',        // lower than
            'gt' => '>',        // greater than
            'lteq' => '<=',     // lower than or equal to
            'gteq' => '>=',     // greater than or equal to
            'direct_eq' => '=', // direkte SQL equal, bliver ikke bindet/prepared
            'direct_in' => 'IN' // direkte SQL IN, bliver ikke bindet/prepared
        ];

        $whereClause = "";

        $whereCount = count($where);
        $i = 0;

        $whereArr = array();
        foreach ($where as $key => $w){
            $operator = '';

            // f.eks: $where = array(array("column" => "battery", "lt" => 12.6, "type" => "d")); - d er for float, lt er for lower than

            foreach ($operators as $k => $op) {
                if (isset($w[$k])) {
                    //$operator = $op;
                    $operator = [$k => $op];
                }
            }

            $operatorKey = (string) array_keys($operator)[0];

            $directs = ['direct_eq', 'direct_in'];
            if (in_array($operatorKey, $directs, true)) {
                $whereClause .= $w['column'] . ' ' . $operator[$operatorKey] . ' ' . $w[$operatorKey] . ' ';
            } else {
                $whereClause .= $w['column'] . ' ' . $operator[$operatorKey] . ' ? ';
                $whereArr[] = &$w[$operatorKey];
            }

            if (++$i != $whereCount){
                $whereClause .= "AND ";
            }
        }

        if (isset($settings['searchTxt']) && $settings['searchTxt'] != ""){
            if (!empty($columns)){
                $columnsCount = count($columns);
                $i = 0;

                if ($whereCount != 0)
                    $whereClause .= 'AND ';

                if ($columnsCount > 0)
                    $whereClause .= '(';

                foreach ($columns as $c){
                    $searchTxt = '%' . $settings['searchTxt'] . '%';

                    if ((isset($c['like']) && $c['like'] != 'ignore') OR !isset($c['like'])){
                        if (isset($c['like']) && $c['like'] != 'ignore'){
                            $whereClause .= $c['like'] . " LIKE ? ";
                        }else{
                            $subQ = '';
                            if (isset($c['order_subq']) && $c['order_subq'] !== '') {
                                $subQ = $c['order_subq'] . '.';
                            }
                            $whereClause .= $subQ . $c['column'] . " LIKE ? ";
                        }
                        $whereArr[] = &$searchTxt;

                        $whereTypes[] = 's';

                        if (++$i != $columnsCount){
                            $whereClause .= "OR ";
                        }
                    }else{
                        $i++;
                    }

                }


                if ($columnsCount > 0)
                    $whereClause .= ') ';
            }
        }

        $types = "";

        foreach ($whereTypes as $type){
            $types .= $type;
        }

        array_unshift($whereArr, $types);

        $query = "";

        if ($whereClause != "" && !empty($whereArr)){
            $query = "SELECT $selectColumns FROM $table WHERE $whereClause" . $order . $limit;
            $this->logQuery($settings, $query, $whereArr);
            $result = $this->RCMS->execute($query, $whereArr);
        }else{
            $query = "SELECT $selectColumns FROM $table" . $order . $limit;
            $this->logQuery($settings, $query, $whereArr);
            $result = $this->RCMS->execute($query);
        }


        if ($result->num_rows > 0){
            $rows = array();

            while ($row = $result->fetch_assoc()){
                $rows[] = $row;
            }
            return $rows;
        }else{
            return [];
        }
    }

    private function logQuery($settings, $query, $whereArr) {
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

    private function pluck ( $a, $prop )
    {
        $out = array();

        for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
            if (isset($a[$i][$prop])) {
                $out[] = $a[$i][$prop];
            }
        }

        return $out;
    }
}
