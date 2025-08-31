<?php
/** @var array $accounts */
/** @var int $page */
/** @var int $totalPages */
/** @var int $perPage */
/** @var int $total */
?>
<section class="container">
    <h2>Kontoplan (moms/SRU)</h2>
    <p>Visar <?= count($accounts) ?> av totalt <?= esc($total) ?> konton.</p>

    <?php if ($totalPages > 1): ?>
        <nav aria-label="Sidor">
            <ul class="pagination">
                <?php $base = '/company/accounts'; ?>
                <?php $prev = max(1, $page - 1); $next = min($totalPages, $page + 1); ?>
                <li><a class="button secondary" href="<?= $base ?>?page=1" <?= $page === 1 ? 'aria-disabled="true"' : '' ?>>Första</a></li>
                <li><a class="button secondary" href="<?= $base ?>?page=<?= $prev ?>" <?= $page === 1 ? 'aria-disabled="true"' : '' ?>>Föregående</a></li>
                <li><span>Sida <?= esc($page) ?> av <?= esc($totalPages) ?></span></li>
                <li><a class="button secondary" href="<?= $base ?>?page=<?= $next ?>" <?= $page >= $totalPages ? 'aria-disabled="true"' : '' ?>>Nästa</a></li>
                <li><a class="button secondary" href="<?= $base ?>?page=<?= $totalPages ?>" <?= $page >= $totalPages ? 'aria-disabled="true"' : '' ?>>Sista</a></li>
            </ul>
        </nav>
    <?php endif; ?>

    <form action="/company/accounts" method="post">
        <?= csrf_field() ?>
        <table role="grid">
            <thead>
            <tr>
                <th>Konto</th>
                <th>Namn</th>
                <th>VAT</th>
                <th>SRU</th>
                <th>Ta bort</th>
            </tr>
            </thead>
            <tbody id="account-rows">
            <?php if (!empty($accounts)) : ?>
                <?php foreach ($accounts as $row): ?>
                    <tr>
                        <td>
                            <input type="number" name="rows[<?= esc($row->account_id) ?>][account_id]" value="<?= esc($row->account_id) ?>" min="1000" max="9999" required />
                            <input type="hidden" name="rows[<?= esc($row->account_id) ?>][orig_account_id]" value="<?= esc($row->account_id) ?>" />
                        </td>
                        <td>
                            <input type="text" name="rows[<?= esc($row->account_id) ?>][name]" value="<?= esc($row->name ?? '') ?>" />
                            <input type="hidden" name="rows[<?= esc($row->account_id) ?>][orig_name]" value="<?= esc($row->name ?? '') ?>" />
                        </td>
                        <td>
                            <input type="number" name="rows[<?= esc($row->account_id) ?>][vat]" value="<?= isset($row->vat) ? esc($row->vat) : '' ?>" />
                            <input type="hidden" name="rows[<?= esc($row->account_id) ?>][orig_vat]" value="<?= isset($row->vat) ? esc($row->vat) : '' ?>" />
                        </td>
                        <td>
                            <input type="number" name="rows[<?= esc($row->account_id) ?>][sru]" value="<?= isset($row->sru) ? esc($row->sru) : '' ?>" />
                            <input type="hidden" name="rows[<?= esc($row->account_id) ?>][orig_sru]" value="<?= isset($row->sru) ? esc($row->sru) : '' ?>" />
                        </td>
                        <td class="center">
                            <input type="checkbox" name="deletes[]" value="<?= esc($row->account_id) ?>" />
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">Inga konton hittades.</td></tr>
            <?php endif; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="5">
                    <button class="secondary" type="button" id="add-row">Lägg till rad</button>
                </td>
            </tr>
            </tfoot>
        </table>
        <div>
            <input class="tertiary" type="submit" value="Spara ändringar" />
        </div>

        <template id="row-template">
            <tr>
                <td>
                    <input type="number" name="__NAME__[account_id]" value="" min="1000" max="9999" required />
                    <input type="hidden" name="__NAME__[orig_account_id]" value="" />
                </td>
                <td>
                    <input type="text" name="__NAME__[name]" value="" />
                    <input type="hidden" name="__NAME__[orig_name]" value="" />
                </td>
                <td>
                    <input type="number" name="__NAME__[vat]" value="" />
                    <input type="hidden" name="__NAME__[orig_vat]" value="" />
                </td>
                <td>
                    <input type="number" name="__NAME__[sru]" value="" />
                    <input type="hidden" name="__NAME__[orig_sru]" value="" />
                </td>
                <td class="center">
                    <button type="button" class="remove-row">Ta bort</button>
                </td>
            </tr>
        </template>
    </form>


    <?php if ($totalPages > 1): ?>
        <nav aria-label="Sidor">
            <ul class="pagination">
                <?php $base = '/company/accounts'; ?>
                <?php $prev = max(1, $page - 1); $next = min($totalPages, $page + 1); ?>
                <li><a class="button secondary" href="<?= $base ?>?page=1" <?= $page === 1 ? 'aria-disabled="true"' : '' ?>>Första</a></li>
                <li><a class="button secondary" href="<?= $base ?>?page=<?= $prev ?>" <?= $page === 1 ? 'aria-disabled="true"' : '' ?>>Föregående</a></li>
                <li><span>Sida <?= esc($page) ?> av <?= esc($totalPages) ?></span></li>
                <li><a class="button secondary" href="<?= $base ?>?page=<?= $next ?>" <?= $page >= $totalPages ? 'aria-disabled="true"' : '' ?>>Nästa</a></li>
                <li><a class="button secondary" href="<?= $base ?>?page=<?= $totalPages ?>" <?= $page >= $totalPages ? 'aria-disabled="true"' : '' ?>>Sista</a></li>
            </ul>
        </nav>
    <?php endif; ?>
</section>
