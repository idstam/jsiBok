
<h1>Gratis, eller billig, bokföring online..</h1>

<p>
Det här är en tjänst för dig som vill hantera din egen bokföring, men inte riktigt vill betala för de stora drakarna ännu.
</p>

<br>

<?php if(env('app.user.selfRegistration') == 'on') { ?>
<nav>
    <ul><li><h3><a class="outline" href="/user/create">Skapa konto</a></h3></li></ul>
</nav>

<?php  } ?>