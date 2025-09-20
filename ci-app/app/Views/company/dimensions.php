<?php
/** @var array $rows */
/** @var int $page */
/** @var int $totalPages */
/** @var int $perPage */
/** @var int $total */
/** @var string $type */
/** @var int $dimNumber */
?>
<section class="container">
    <h2>Hantera <?= $type === 'project' ? 'Projekt' : 'Kostnadsställen' ?></h2>

<!--    <nav style="margin-bottom: .5rem">-->
<!--        <a class="button --><?php //= $type !== 'project' ? 'primary' : 'secondary' ?><!--" href="/company/dimensions?type=kostnadsstalle">Kostnadsställen</a>-->
<!--        <a class="button --><?php //= $type === 'project' ? 'primary' : 'secondary' ?><!--" href="/company/dimensions?type=project">Projekt</a>-->
<!--    </nav>-->

    <p>Visar <?= count($rows) ?> av totalt <?= esc($total) ?> rader.</p>

    <?php if ($totalPages > 1): ?>
        <nav aria-label="Sidor">
            <ul class="pagination">
                <?php $base = '/company/dimensions?type=' . urlencode($type); ?>
                <?php $prev = max(1, $page - 1); $next = min($totalPages, $page + 1); ?>
                <li><a class="button secondary" href="<?= $base ?>&page=1" <?= $page === 1 ? 'aria-disabled="true"' : '' ?>>Första</a></li>
                <li><a class="button secondary" href="<?= $base ?>&page=<?= $prev ?>" <?= $page === 1 ? 'aria-disabled="true"' : '' ?>>Föregående</a></li>
                <li><span>Sida <?= esc($page) ?> av <?= esc($totalPages) ?></span></li>
                <li><a class="button secondary" href="<?= $base ?>&page=<?= $next ?>" <?= $page >= $totalPages ? 'aria-disabled="true"' : '' ?>>Nästa</a></li>
                <li><a class="button secondary" href="<?= $base ?>&page=<?= $totalPages ?>" <?= $page >= $totalPages ? 'aria-disabled="true"' : '' ?>>Sista</a></li>
            </ul>
        </nav>
    <?php endif; ?>

    <form action="/company/dimensions?type=<?= esc($type) ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="type" value="<?= esc($type) ?>" />
        <table role="grid">
            <thead>
            <tr>
                <th>Kod</th>
                <th>Namn</th>
                <th>Ta bort</th>
            </tr>
            </thead>
            <tbody id="dim-rows">
            <?php if (!empty($rows)) : ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td>
                            <input type="hidden" name="rows[<?= esc($r->id) ?>][id]" value="<?= esc($r->id) ?>" />
                            <input type="number" name="rows[<?= esc($r->id) ?>][dim_code]" value="<?= esc($r->dim_code) ?>" min="0" />
                        </td>
                        <td>
                            <input type="text" name="rows[<?= esc($r->id) ?>][title]" value="<?= esc($r->title) ?>" />
                        </td>
                        <td class="center">
                            <input type="checkbox" name="deletes[]" value="<?= esc($r->id) ?>" />
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="3">Inga rader.</td></tr>
            <?php endif; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="3">
                    <button class="secondary" type="button" id="add-dim-row">Lägg till rad</button>
                </td>
            </tr>
            </tfoot>
        </table>
        <div>
            <input class="tertiary" type="submit" value="Spara" />
        </div>

        <template id="dim-row-template">
            <tr>
                <td>
                    <input type="hidden" name="__NAME__[id]" value="0" />
                    <input type="number" name="__NAME__[dim_code]" value="" min="0" />
                </td>
                <td>
                    <input type="text" name="__NAME__[title]" value="" />
                </td>
                <td class="center">
                    <button type="button" class="remove-dim-row">Ta bort</button>
                </td>
            </tr>
        </template>
    </form>

    <?php if ($totalPages > 1): ?>
        <nav aria-label="Sidor">
            <ul class="pagination">
                <?php $base = '/company/dimensions?type=' . urlencode($type); ?>
                <?php $prev = max(1, $page - 1); $next = min($totalPages, $page + 1); ?>
                <li><a class="button secondary" href="<?= $base ?>&page=1" <?= $page === 1 ? 'aria-disabled="true"' : '' ?>>Första</a></li>
                <li><a class="button secondary" href="<?= $base ?>&page=<?= $prev ?>" <?= $page === 1 ? 'aria-disabled="true"' : '' ?>>Föregående</a></li>
                <li><span>Sida <?= esc($page) ?> av <?= esc($totalPages) ?></span></li>
                <li><a class="button secondary" href="<?= $base ?>&page=<?= $next ?>" <?= $page >= $totalPages ? 'aria-disabled="true"' : '' ?>>Nästa</a></li>
                <li><a class="button secondary" href="<?= $base ?>&page=<?= $totalPages ?>" <?= $page >= $totalPages ? 'aria-disabled="true"' : '' ?>>Sista</a></li>
            </ul>
        </nav>
    <?php endif; ?>
</section>
