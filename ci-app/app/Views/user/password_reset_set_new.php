<!-- -->



<?php
    $headline = "Byt lösenord";
    ?>
<p>
<?= $headline ; ?>
<p>

<form action="/reset-password" method="post">

    <?= csrf_field() ?>
    <input type='hidden' id='reset_id' name='reset_id' value='<?= /** @var string $reset_id */
    esc($reset_id)  ?>' />
    <fieldset>
        <div class="row">
            <div class="col-sm-2">
                <label for="password">Lösenord</label>
            </div>
            <div class="col-sm-2">
                <input type="password" id="password" name="password" />
            </div>
        </div>
        <div class="row">
            <div class="col-sm-2">
                <label for="password2" >Repetera lösenord</label>
            </div>
            <div class="col-sm-2">
                <input type="password" id="password2" name="password2"  />
            </div>
        </div>
        <div class="row">
            <div class="col-sm-2">
                &nbsp;
            </div>
            <div class="col-sm-1">
                <input class="tertiary" type="submit" id="submit" name="submit" value="Spara"  />
            </div>
    </div>

    </fieldset>
</form>