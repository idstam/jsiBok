<!-- -->


<form class="container" action="/login" method="post">
    <?= csrf_field() ?>
    <fieldset>
        <legend><strong>Logga in</strong></legend>
        <label for="user">E-postadress/Användarnamn</label>
        <input type="text" id="user" name="user" style="padding: calc(var(--universal-padding) / 2);"/>
        <label for="password">Lösenord</label>
        <input type="password" id="password" name="password" style="padding: calc(var(--universal-padding) / 2);"/>
        <input class="tertiary" type="submit" id="submit" name="submit" value="Logga in"/>

    </fieldset>
    <br>
    <input class="secondary" type="submit" id="pwdreset" name="pwdreset" value="Nytt lösenord"/>

</form>
