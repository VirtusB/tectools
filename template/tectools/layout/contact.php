<?php

declare(strict_types=1);

$randNum1 = random_int(5, 20);
$randNum2 = random_int(5, 20);
$result = $randNum1 + $randNum2;
$_SESSION['contact_page_captcha'] = $result;

?>

<!-- Start of  Zendesk Widget script -->
<script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key=7f328083-80f6-430e-8542-26dea49d5906"> </script>
<!-- End of  Zendesk Widget script -->

<div class="container">
    <div class="row">
        <div class="col s12">
            <?php if (isset($_GET['sent'])): ?>
            <div class="row">
                <h2 class="center mt2">Besked sendt</h2>

                <div class="row" style="display: flex">
                    <div class="col s12 m5" style="margin: 0 auto;">
                        <div class="card-panel teal">
                <span class="white-text">
                    Din besked blev sendt!<br>Vi svarer tilbage hurtigst muligt
                </span>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <h2 class="center mt2">Hvad kan vi hjælpe med?</h2>

            <div class="row">

                <form method="post" id="contact-form" class="col s12 m8 offset-m2">
                    <div class="row">
                        <div class="input-field col m6 s12">
                            <input required id="first_name" value="<?= $_POST['firstname'] ?? '' ?>" name="firstname" type="text" class="validate">
                            <label for="first_name" class="">Fornavn</label>
                        </div>
                        <div class="input-field col m6 s12">
                            <input required id="last_name" value="<?= $_POST['lastname'] ?? '' ?>" name="lastname" type="text" class="validate">
                            <label for="last_name">Efternavn</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col m6 s12">
                            <input required id="email" value="<?= $_POST['email'] ?? '' ?>" name="email" type="email" class="validate">
                            <label for="email" class="">Email</label>
                        </div>
                        <div class="input-field col m6 s12">
                            <input pattern="\d*" minlength="8" required id="phone_number" value="<?= $_POST['phone'] ?? '' ?>" name="phone" type="text" class="validate">
                            <label for="phone_number" class="">Telefon nr.</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-field col s12">
                            <textarea required id="message" name="message" class="materialize-textarea"><?= $_POST['message'] ?? '' ?></textarea>
                            <label for="message" class="">Besked</label>
                        </div>
                    </div>

                    <input type="hidden" name="post_endpoint" value="contactCustomerService">

                    <div class="row">
                        <div class="input-field col m6 s12">
                            <input required id="verification" name="verification" type="text" class="validate">
                            <label for="verification" class="">Hvad er <?= $randNum1 ?> plus <?= $randNum2 ?>?</label>
                        </div>
                        <div class="col m6">
                            <p class="right-align">
                                <button id="contact-submit" class="btn tec-btn" type="submit" name="contact-submit">Send Besked</button>
                            </p>
                        </div>
                    </div>

                </form>

                <div class="col s12 m10 offset-m2">
                    <p class="center orange-text info-paragraph">
                        <i class="fas fa-info" style="float: left; color: #2196F3;"></i>
                        Du kan også benytte dig af vores Live Chat, ude i højre side <br>
                        Vores eksperter er klar til at hjælpe dig, med ethvert spørgsmål du måtte have
                    </p>
                </div>

            </div>
            <?php endif; ?>


        </div>
    </div>
</div>

<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/contact.css">