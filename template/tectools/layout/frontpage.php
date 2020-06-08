<div class="container">
    <div class="row" id="tools-row">

        <input type="text" placeholder="Fritekst...">



        <?php
        /**
         * @var $TecTools TecTools
         */
        $TecTools = $GLOBALS['TecTools'];

        /**
         * @var $GlobalHandlers GlobalHandlers
         */
        $GlobalHandlers = $GLOBALS['GlobalHandlers'];

        $tools = $TecTools->getAllTools();

        foreach ($tools as $tool): ?>

        <div class="col s12 m6 l3 xl3">
            <div class="card">
                <div class="card-image">
                    <img src="<?= $TecTools->RELATIVE_TOOL_IMAGE_FOLDER . '/' . $tool['Image'] ?>" alt="">
                </div>
                <div class="card-content">
                    <span class="card-title black-text"><?= $tool['ToolName'] ?></span>
                    <p><?= $tool['ManufacturerName'] ?></p>
                    <br>
                    <p><i class="fas fa-cubes cubes-icon <?= $tool['Status'] !== 1 ? 'not-available' : '' ?>"></i> <?= $GlobalHandlers->formatStatus([$tool['Status']]) ?></p>

                    <br>
                </div>
                <div class="card-action">
                    <ul>
                        <?php foreach ($tool['Categories'] as $category): ?>
                            <li><?= $category['CategoryName'] ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <?php endforeach; ?>

    </div>
</div>


<style>
    #tools-row {
        /*position: relative;*/
        /*bottom: 65px;*/
        margin-top: 2rem;
    }

    #tools-row .card-image img {
        width: 65%;
        margin: 0 auto;
    }

    div.card-action a {
        display: inline-block;
        vertical-align: sub;
    }
</style>



<?php
//
//$name = 'Virtus';
//
//$query = 'SELECT getTestName(?)';
//
//$result = $this->RCMS->execute($query, array('s', &$name))->fetch_assoc();
//
//var_dump($result);