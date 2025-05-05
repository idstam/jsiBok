<?php
/** @var string $name */
/** @var string $email */
?>

<p><b>Ändra på ditt konto.</b></p>
<p>När du loggat in med ditt nya konto kan du skapa nya företag i applikationen.</p>

<form action="/user/save" method="post">
    <?= csrf_field() ?>
    
    <fieldset>
        
        <div class="row">
            <div class="col-sm-2">
                <label for="email">Epost</label>
            </div>
            <div class="col-sm-5
            ">
                <input type="text" id="email" name="email" value="<?= esc($email)?>" />
            </div>
        </div>

        <div class="row">
            <div class="col-sm-2">
                <label for="name">Namn</label>
            </div>
            <div class="col-sm-3">
                <input type="text" id="name" name="name" value="<?= esc($name)?>" />
            </div>
        </div>

        <div class="row">
            <div class="col-sm-2">
                <label for="password1">Lösenord</label>
            </div>
            <div class="col-sm-3">
                <input type="password" id="password1" name="password1" value="" />
            </div>
        </div>

        <div class="row">
            <div class="col-sm-2">
                <label for="password2">Repetera lösenord</label>
            </div>
            <div class="col-sm-3">
                <input type="password" id="password2" name="password2" value="" />
            </div>
        </div>

        <div class="row">
            <div class="col-sm-1">
                <input class="tertiary" type="submit" id="submit" name="submit" value="Spara" />
            </div>

        </div>

    </fieldset>
</form>