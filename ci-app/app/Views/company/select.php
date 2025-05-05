
<form class="container">
    <?= csrf_field() ?>
    <fieldset>

    <legend><strong>Välj vilket företag du vill jobba med</strong></legend>
<table>
  <thead>
    <tr>
      <th>Namn</th>
      <th>Organisationsnummer</th>
    </tr>
  </thead>
  <tbody>

<?php /** @var array $companies */
foreach ($companies as $company) { ?>
    <tr>
      <td data-label="Namn"><a href="/company/select/<?= esc($company->number) ?>"><?= esc($company->name) ?></a></td>
      <td data-label="Organisationsnummer"><a href="/company/select/<?= esc($company->number) ?>"><?= esc($company->org_no) ?></a></td>
      
    </tr>
<?php } ?>
  </tbody>
</table>
    </fieldset>

</form>
<hr>