<?phpdeclare(strict_types=1);/** * @var RCMS $this */?><style>    .brand-logo {        min-width: fit-content !important;        display: flex !important;        justify-content: center !important;        align-items: center !important;    }    .brand-logo img {        max-height: 31.5px;        margin-right: 7px;    }</style><nav id="header" role="navigation">    <div class="nav-wrapper container">        <!-- Burger icon mobile trigger -->        <a href="#" data-target="mobile-menu" class="sidenav-trigger"><i class="material-icons">menu</i></a>        <a href="<?= $this->getHomeFolder() ?>" class="brand-logo center"><img src="/template/tectools/images/logo.svg"> <?= SITE_NAME ?></a>        <!-- Login button with icon -->        <ul class="right hide-on-med-and-down">            <?php if (!$this->Login->isLoggedIn()): ?>                <li>                    <a href="<?= $this->getHomeFolder() ?>register">Opret</a>                </li>                <li>                    <a href="<?= $this->getHomeFolder() ?>login">Log ind</a>                </li>            <?php endif; ?>            <?php if ($this->Login->isLoggedIn()): ?>                <li>                    <a href="<?= $this->getHomeFolder() ?>dashboard">Dashboard <i class="material-icons right">settings</i></a>                </li>                <?php if ($this->Login->isAdmin()): ?>                <li>                    <a href="<?= $this->getHomeFolder() ?>scan">Tjek Ud <i class="fal fa-scanner right"></i></a>                </li>                <?php else: ?>                <li>                    <a href="<?= $this->getHomeFolder() ?>scan">Scan <i class="fal fa-scanner right"></i></a>                </li>                <?php endif; ?>                <li>                    <a href="?log_out=1">Log ud</a>                </li>            <?php endif; ?>        </ul>    </div></nav><!-- Mobile sidenav --><ul class="sidenav" id="mobile-menu">    <?php if (!$this->Login->isLoggedIn()): ?>        <li>            <a href="<?= $this->getHomeFolder() ?>register">Opret</a>        </li>        <li>            <a href="<?= $this->getHomeFolder() ?>login">Log ind</a>        </li>    <?php endif; ?>    <?php if ($this->Login->isLoggedIn()): ?>        <li>            <a href="<?= $this->getHomeFolder() ?>dashboard">Dashboard <i class="material-icons right">settings</i></a>        </li>        <?php if ($this->Login->isAdmin()): ?>            <li>                <a href="<?= $this->getHomeFolder() ?>scan">Tjek Ud <i class="fal fa-scanner right"></i></a>            </li>        <?php else: ?>            <li>                <a href="<?= $this->getHomeFolder() ?>scan">Scan <i class="fal fa-scanner right"></i></a>            </li>        <?php endif; ?>        <li>            <a href="?log_out=1">Log ud</a>        </li>    <?php endif; ?></ul><?php if($this->Login->isLoggedIn()): ?><div class="fixed-action-btn tec-floating-container">    <a href="/scan" class="btn-floating btn-large tec-floating">        <i class="fal fa-scanner large"></i>    </a></div><?php else: ?><div class="fixed-action-btn tec-floating-container">    <a href="/register" class="btn-floating btn-large tec-floating">        <i class="fad fa-user-plus large"></i>    </a></div><?php endif; ?><style>    .tec-floating {        background-color: #0c8dff !important;    }    @media all and (min-width: 992px) {        .tec-floating-container {            display: none;        }    }</style><script>    $(document).ready(function(){        $('.fixed-action-btn').floatingActionButton();    });</script>