<?php
//Den här filen lyder under en MIT-Licens.

//Copyright 2025 Johan Idstam
//
//Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
//The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
//THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.


?>
<div class="container">
<p>Det här är en instans av jsiBok. Ett redovisningssystem som utvecklas med öppen källkod.</p>
<p>Om man vill hjälpa till, eller bara ta reda på hur systemet fungerar, finns källkoden här: <a href="https://codeberg.org/idstam/jsiBok">codeberg.org/idstam/jsiBok</a>.
</p>

<p>Den här instansen drivs av <?=getEnv('app.hostingCompany') ?>.</p>
<p> Kontakt: <a href="mailto:<?=getEnv('app.supportEmail') ?>"><?=getEnv('app.supportEmail') ?></a></p>

<h2>Cookies</h2>
<p>Tjänsten använder cookies för att hantera sina administrativa funktioner.</p>
<p>Tjänsten använder inga cookies från tredje part.</p>
<p>Tjänsten lämnar inte ut några användaruppgifter till tredje part, förutom vid eventuella förfrågningar från myndigheter.</p>

<h2>Datalagring</h2>
<p>Tjänsten sparar den information som efterfrågas för att hantera redovisning åt anslutna företag.</p>
<p>Användares epostadresser används enbart för kommunikation som rör tjänstens funktionalitet.</p>
<p>Tjänstens driftsmiljö och/eller infrastruktur kan lagra ip-adresser. I den mån tjänsten har kontroll över uppgifterna kan de lämnas ut till efterfrågande myndigheter.</p>

<h2>Funktionalitet</h2>
<p>Ambitionen är att detta ska vara ett redovisningssystem för den lilla företagaren som sköter sin egen bokföring.</p>

<h2>Kända brister</h2>
<p>...</p>

<h2>Ansvar för bokföringen</h2>
<p><?=getEnv('app.hostingCompany') ?> tar inte över något ansvar för redovisningen.</p>

<!--<h2>Betalning</h2>-->
<!--<p>...</p>-->

<h2>Uppsägning och avslut</h2>
<p>...</p>
<br><hr>
<!-- Skatteverkets informationskrav, som förhoppningsvis fylls av den synliga texten:
    
Programvaruföretaget ska informera Slutanvändaren enligt
följande
a) vid tillhandahållande av tjänsten samt därefter löpande,
informera om förutsättningarna för att använda API:et. I
informationen ska följande punkter framhävas:
b) att Auditloggning sker hos Skatteverket av inkomna
förfrågningar via API:et och att dessa bevaras i upp till 5
år. Auditloggar används av Skatteverket för att förebygga
och utreda säkerhetsincidenter
-->

</div>