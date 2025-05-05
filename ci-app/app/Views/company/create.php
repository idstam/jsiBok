<form class="container" action="/company/save" method="post">
    <?= csrf_field() ?>
    <legend><strong>Nytt företag</strong></legend>
    <label for="name">Namn
        <input type="text" id="name" name="name"/></label>

    <label for="org_no">Org.nr.
        <input type="text" id="org_no" name="org_no" placeholder="555555-5555"/></label>


    <label for="name">Bokföringsår</label>
    <div class="grid">
        <label for="from">Från</label>
        <label for="to">Till</label>
    </div>
    <div class="grid">
        <input type="date" id="booking_year_start" name="booking_year_start"/>
        <input type="date" id="booking_year_end" name="booking_year_end"/>
    </div>
    <input class="tertiary" type="submit" id="submit" name="submit" value="Lägg till"/>

</form>
<hr>