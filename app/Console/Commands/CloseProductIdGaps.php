<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Closes the gaps a deleted product leaves in the products' ids, so they run 1, 2, 3 … like a
 * price list numbered by hand. Each product (or bundle) after a gap moves down into it, taking
 * everything with it (name, price, code, picture); the bundles' lines follow both the bundle and
 * the products in it. Orders and carts name their products and never point at an id, so they are
 * untouched.
 *
 * Without --apply it only says what it would do.
 */
class CloseProductIdGaps extends Command
{
    protected $signature = 'products:close-id-gaps {--apply : make the change; without it, only show it}';

    protected $description = 'Renumber the products so their ids have no gaps (a dry run unless --apply)';

    public function handle(): int
    {
        if (DB::getDriverName() !== 'sqlite') {
            $this->error('Only written for the SQLite database sabad uses.');

            return self::FAILURE;
        }

        $moves = $this->plan();

        if ($moves === []) {
            $this->info('No gaps: every product id already follows the one before it.');

            return self::SUCCESS;
        }

        $this->table(['id now', 'id after', 'product'], array_map(
            fn (array $move) => [$move['from'], $move['to'], $move['name']],
            $moves,
        ));

        if (! $this->option('apply')) {
            $this->warn('Nothing changed. Run again with --apply to make this change.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($moves) {
            // a product moves while its bundle lines still point at its old id: let the database
            // check the links once, when everything has moved
            DB::statement('PRAGMA defer_foreign_keys = ON');

            // from the lowest up, so each product moves into a place already free
            foreach ($moves as $move) {
                DB::table('products')->where('id', $move['from'])->update(['id' => $move['to']]);
                DB::table('bundle_items')->where('product_id', $move['from'])->update(['product_id' => $move['to']]);
                DB::table('bundle_items')->where('bundle_id', $move['from'])->update(['bundle_id' => $move['to']]);
            }

            $broken = DB::select('PRAGMA foreign_key_check');
            if ($broken !== []) {
                throw new RuntimeException('A link between tables would break; nothing was changed.');
            }
        });

        $this->info(count($moves).' products renumbered.');

        return self::SUCCESS;
    }

    /**
     * Every product, bundles too, in id order, takes the next id: 1, 2, 3 … with no gap.
     *
     * @return list<array{from: int, to: int, name: string}>
     */
    private function plan(): array
    {
        $moves = [];
        $next = 1;

        foreach (Product::query()->orderBy('id')->get(['id', 'name']) as $product) {
            if ($product->id !== $next) {
                $moves[] = ['from' => $product->id, 'to' => $next, 'name' => $product->name];
            }

            $next++;
        }

        return $moves;
    }
}
