<?php namespace Gzero\Core;

use Illuminate\Support\Facades\DB;

trait DBTransactionTrait {

    /**
     * @param \Closure $function Closure to run in transaction
     *
     * @return mixed
     */
    protected function dbTransaction(\Closure $function)
    {
        return config('gzero-core.db_transactions', true) ? DB::transaction($function) : $function();
    }

}
