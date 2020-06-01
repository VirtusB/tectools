<div class="row">
    <div class="col s12 m8 l4 xl2 offset-m2 offset-l4 offset-xl5">
        <div id="">
            <h1>Opret</h1>

            <?php
            if (isset($_GET['emailtaken'])) {
                $this->RCMS->Functions->outputError('Bruger med den email eksisterer allerede', 'h6');
            }
            ?>

            <form action="" method="POST">
                <input autocomplete="off" name="username" type="email" placeholder="E-mail"><br>
                <input autocomplete="off" name="password" type="password" placeholder="Password"><br>
                <input name="create_new_user" type="hidden" value="1"><br>
                <input class="btn" type="submit" value="Opret">
            </form>
            <br><br><br>
        </div>
    </div>
</div>