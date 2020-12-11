<?php

declare(strict_types=1);

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

/**
 * @var GlobalHandlers $GlobalHandlers
 */
$GlobalHandlers = $GLOBALS['GlobalHandlers'];

$categories = $TecTools->Categories->getAllCategories();
$tools = $TecTools->getAllToolsWithFilters();

$carouselTools = $TecTools->getNewestTools(10);

?>

<style>
    .hero-container:after {
        background-image: url(<?= $this->RCMS->getTemplateFolder() ?>/images/tools-small.jpg);
    }
</style>

<div class="hero-container">
    <h3>Hvilket værktøj har du brug for?</h3>
<!--    <h5>Find det her!</h5>-->
    <h5>Find det her og afhent i en af vores <a href="/stores">butikker</a>!</h5>

    <div class="container">
        <div class="row search-row">
            <form action="/tools" method="get">
                <div class="col s12 m12 l4 xl4">
                    <input value="<?= isset($_GET['search-text']) ? $_GET['search-text'] : '' ?>" name="search-text" type="text" placeholder="Søg efter...">
                </div>

                <div id="category-select-col" class="col s12 m12 l4 xl4">
                    <select multiple name="categories[]" id="category-select">
                        <option disabled="disabled" value="">Vælg kategori</option>
                        <?php foreach ($categories as $category): ?>
                            <option <?= isset($_GET['categories']) && in_array($category['CategoryID'], $_GET['categories'], false) ? 'selected' : '' ?> value="<?= $category['CategoryID'] ?>"><?= $category['CategoryName'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="justify-content: flex-end" class="col s12 m12 l4 xl4 valign-wrapper">
                    <div id="only_in_stock_container">
                        <input type="hidden" name="only_in_stock" value="0" />
                        <input value="1" <?= isset($_GET['only_in_stock']) ? ($_GET['only_in_stock'] == '1' ? 'checked' : '') : 'checked' ?> id="only_in_stock" name="only_in_stock" type="checkbox">
                        <label for="only_in_stock">Kun på lager</label>
                    </div>

                    <button style="width: 50%;" onclick="location.href='/tools'" id="filter-tools-btn" type="submit" class="btn green-btn">Søg</button>
                </div>
            </form>
        </div>
    </div>

</div>

<div class="row how-to-tectools">
    <div class="col s12 center">
        <h3>Okay, hvad er TecTools?</h3>
    </div>

    <div class="col s12 l4">
        <div class="content">
            <i class="fad fa-tools"></i>
            <h6>TecTools er en platform for udlejning af værktøj til private. Du scanner selv, med din smartphone, det værktøj du vil låne, i en af vores <a href="/stores">fysiske butikker</a></h6>
        </div>
    </div>
    <div class="col s12 l4">
        <div class="content">
            <i class="fad fa-piggy-bank"></i>
            <h6>Det er til dig, som mangler et professionelt stykke værktøj, men ikke har lyst til at tømme sparegrisen. Det kan jo være, at du kun skal bruge værktøjet én gang, ikke?</h6>
        </div>
    </div>
    <div class="col s12 l4">
        <div class="content">
            <i class="fad fa-recycle"></i>
            <!--            <h6>Hos TecTools gør vi lån af værktøj nemt, billigt og sikkert - og samtidigt er du med til at reducere CO<sub>2</sub> udledningen; dig kan vi godt lide ❤</h6>-->
            <!--            <h6>Hos TecTools gør vi lån af værktøj nemt, billigt og sikkert - og som medlem er du med til at reducere CO<sub>2</sub> udledningen❤</h6>-->
            <h6>Hos TecTools gør vi lån af værktøj nemt, billigt og sikkert. Som medlem kan du være stolt af, at du er med til at reducere CO<sub>2</sub> udledningen❤</h6>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col s12 center">
            <h4 style="margin-top: 4rem" class="center">Nyeste værktøj i sortimentet</h4>
            <div class="carousel">
                <?php foreach ($carouselTools as $tool): ?>

                <a class="carousel-item" href="/tools/view?toolid=<?= $tool['ToolID'] ?>">
                    <span class="center tool-name"><?= $tool['ManufacturerName'] . ' ' . $tool['ToolName'] ?></span>
                    <img src="<?= $tool['Image'] ?>">
                </a>

                <?php endforeach; ?>
            </div>

            <button onclick="location.href= '/tools'" class="btn view-all-btn">Se alt værktøj</button>

        </div>
    </div>


</div>



<div class="row how-to-tectools how-to-steps">
    <div class="col s12 center">
        <h3>Perfekt, hvordan kommer jeg i gang?</h3>
<!--        <p style="margin-bottom: 0">Spoiler Alert: Det er lige så nemt, som at tælle til 4😉</p>-->
    </div>

    <div class="col s12 l3">
        <div class="content">
            <div class="inner-content">
                <div class="step-container">
                    <p class="step">1</p>
                </div>
                <h6><a style="text-decoration: underline" href="/register">Opret en konto</a></h6>
            </div>
        </div>
    </div>
    <div class="col s12 l3">
        <div class="content">
            <div class="inner-content">
                <div class="step-container">
                    <p class="step">2</p>
                </div>
                <h6><a style="text-decoration: underline" href="/subscriptions">Vælg et abonnement</a></h6>
            </div>
        </div>
    </div>
    <div class="col s12 l3">
        <div class="content">
            <div class="inner-content">
                <div class="step-container">
                    <p class="step">3</p>
                </div>
                <h6>Find, scan og lån det værktøj du mangler</h6>
            </div>
        </div>
    </div>
    <div class="col s12 l3">
        <div class="content">
            <div class="inner-content">
                <div class="step-container">
                    <p class="step">4</p>
                </div>
                <h6>Aflever værktøjet tilbage, når du ikke længere har brug for det</h6>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col s12 center">
        <h3 class="mt0 mb2">Stadig i tvivl?</h3>
    </div>
    <div class="col s12 center">
        <button onclick="location.href = '/faq'" class="btn view-all-btn">Læs vores FAQ</button>
    </div>
</div>

<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/frontpage.css">

<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/frontpage/frontpage.js"></script>




