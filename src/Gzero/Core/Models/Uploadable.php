<?php namespace Gzero\Core\Models;

interface Uploadable {

    /**
     * Files relation
     *
     * @param bool $active is active
     *
     * @return mixed
     */
    public function files($active = true);
}
