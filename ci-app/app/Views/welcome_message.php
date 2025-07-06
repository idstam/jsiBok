
<h1>Gratis, eller billig, bokföring online..</h1>

<p>
Det här är en tjänst för dig som vill hantera din egen bokföring, men inte riktigt vill betala för de stora drakarna ännu.
</p>

<br>
<p>Systemet utvecklas med AGPL som licens och koden finns hos <a href="https://codeberg.org/idstam/jsiBok">Codeberg</a> </p>

<?php if(env('app.user.selfRegistration') == 'on') { ?>
<nav>
    <ul><li><h3><a class="outline" href="/user/create">Skapa konto</a></h3></li></ul>
</nav>

<?php  } ?>